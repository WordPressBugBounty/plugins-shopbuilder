<?php

namespace RadiusTheme\SB\Modules\CheckoutEditor;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

use RadiusTheme\SB\Traits\SingletonTrait;

/**
 * CheckoutEditorInit
 */
class GeneralCheckout extends CheckoutEditorBase {
	/**
	 * Singleton Trait.
	 */
	use SingletonTrait;

	/**
	 * Notifications hooks.
	 */
	private function __construct() {
		parent::__construct();
		add_filter( 'woocommerce_get_country_locale', [ $this, 'get_country_locale' ] );
		add_filter( 'woocommerce_default_address_fields', [ $this, 'default_address_fields' ], 9999 );
		add_filter( 'woocommerce_form_field_args', [ $this, 'customize_billing_state' ], 10, 3 ); // The hook will work for initial page load.
	}
	/**
	 * Customize the billing state field arguments on the initial page load.
	 *
	 * @param array  $args  The field arguments.
	 * @param string $key   The field key.
	 * @param mixed  $value The field value.
	 * @return array Modified field arguments.
	 */
	public function customize_billing_state( $args, $key, $value ) {
		if ( 'billing_state' !== $key ) {
			return $args;
		}
		$args['label']       = $this->billing_settings['billing_state']['label'] ?? esc_html__( 'State / Region', 'shopbuilder' );
		$args['placeholder'] = $this->billing_settings['billing_state']['placeholder'] ?? esc_html__( 'State / Region', 'shopbuilder' );
		return $args;
	}
	/**
	 * Field keys WooCommerce re-applies on the client from the country locale.
	 *
	 * `address-i18n.js` rewrites these fields on page load and on every country
	 * change, taking label, required and hidden straight from the locale it was
	 * handed. Whatever `woocommerce_billing_fields` produced for them is
	 * overwritten in the browser, so the editor's settings have to reach the
	 * locale as well or they only survive server side. Phone joined this list in
	 * recent WooCommerce releases, which is how a phone marked required in the
	 * editor still rendered as optional.
	 *
	 * `country` is deliberately excluded: WooCommerce forces it visible and
	 * required after every locale filter has run, so feeding settings into it
	 * changes nothing.
	 *
	 * @return array
	 */
	private function locale_managed_keys() {
		$keys = [ 'address_1', 'address_2', 'state', 'postcode', 'city', 'phone' ];

		if ( function_exists( 'WC' ) && WC()->countries && method_exists( WC()->countries, 'get_country_locale_field_selectors' ) ) {
			$keys = array_keys( WC()->countries->get_country_locale_field_selectors() );
		}

		return array_values( array_diff( $keys, [ 'country' ] ) );
	}

	/**
	 * Saved editor settings for one locale-managed key.
	 *
	 * The default address fields and the locale are shared by both forms, so the
	 * billing side wins and shipping only fills in what billing does not define.
	 *
	 * @param string $key Unprefixed field key, e.g. `phone`.
	 *
	 * @return array Saved field settings, empty when the field is not in use.
	 */
	private function locale_field_settings( $key ) {
		if ( ! empty( $this->billing_settings[ 'billing_' . $key ] ) ) {
			return $this->billing_settings[ 'billing_' . $key ];
		}

		if ( ! empty( $this->shipping_settings[ 'shipping_' . $key ] ) ) {
			return $this->shipping_settings[ 'shipping_' . $key ];
		}

		return [];
	}

	/**
	 * Applies the editor's settings to WooCommerce's default address fields.
	 *
	 * This array is also what becomes the default country locale, so it is the
	 * only place a locale-managed field can be brought in line for both the
	 * server and the browser.
	 *
	 * @param array $fields fields.
	 * @return array
	 */
	public function default_address_fields( $fields ) {
		// Inject priority from saved settings order so WC's address-i18n.js does not re-sort locale-managed fields with core defaults.
		$billing_keys  = array_keys( $this->billing_settings );
		$shipping_keys = array_keys( $this->shipping_settings );

		foreach ( $this->locale_managed_keys() as $key ) {
			if ( empty( $fields[ $key ] ) ) {
				continue;
			}

			$settings = $this->locale_field_settings( $key );

			if ( ! empty( $settings ) ) {
				$fields[ $key ] = wp_parse_args( $settings, $fields[ $key ] );
			}

			$billing_index  = array_search( 'billing_' . $key, $billing_keys, true );
			$shipping_index = array_search( 'shipping_' . $key, $shipping_keys, true );

			if ( false !== $billing_index ) {
				$fields[ $key ]['priority'] = ( absint( $billing_index ) + 1 ) * 10;
			} elseif ( false !== $shipping_index ) {
				$fields[ $key ]['priority'] = ( absint( $shipping_index ) + 1 ) * 10;
			}
		}

		return $fields;
	}

	/**
	 * Applies the editor's settings to one country's locale overrides.
	 *
	 * @param array $fields fields.
	 * @return array
	 */
	public function country_locale( $fields ) {
		foreach ( $this->locale_managed_keys() as $key ) {
			if ( empty( $fields[ $key ] ) ) {
				continue;
			}

			$settings = $this->locale_field_settings( $key );

			if ( empty( $settings ) ) {
				continue;
			}

			// Only keys the country actually declares are touched. Anything it
			// leaves out falls through to the default locale, which
			// default_address_fields() has already brought in line.
			if ( isset( $fields[ $key ]['label'] ) && ! empty( $settings['label'] ) ) {
				$fields[ $key ]['label'] = $settings['label'];
			}

			// A field the country hides has no answer to give: WooCommerce pairs
			// `hidden` with `required => false` for exactly that reason, and
			// address-i18n.js hides the row without clearing `validate-required`.
			// Forcing required here is what left 14 countries unable to check out,
			// blocked on a postcode field they could not see.
			if ( ! empty( $fields[ $key ]['hidden'] ) ) {
				continue;
			}

			if ( isset( $fields[ $key ]['required'], $settings['required'] ) ) {
				$fields[ $key ]['required'] = $settings['required'];
			}
		}

		return $fields;
	}

	/**
	 * @param array $country local.
	 * @return array
	 */
	public function get_country_locale( $country ) {
		foreach ( $country as $key => $fields ) {
			 $country[ $key ] = $this->country_locale( $fields );
		}
		return $country;
	}
}
