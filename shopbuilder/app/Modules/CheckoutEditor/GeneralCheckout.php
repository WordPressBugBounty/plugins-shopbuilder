<?php

namespace RadiusTheme\SB\Modules\CheckoutEditor;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

use PhpParser\Node\Expr\Empty_;
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
	}
	/**
	 * @param array $fields fields.
	 * @return array
	 */
	public function default_address_fields( $fields ) {
		// address_1.
		if ( ! empty( $this->billing_settings['billing_address_1'] ) ) {
			$fields['address_1'] = wp_parse_args( $this->billing_settings['billing_address_1'], $fields['address_1'] );
		} elseif ( ! empty( $this->shipping_settings['shipping_address_1'] ) ) {
			$fields['address_1'] = wp_parse_args( $this->shipping_settings['shipping_address_1'], $fields['address_1'] );
		}
		// address_2.
		if ( ! empty( $this->billing_settings['billing_address_2'] ) ) {
			$fields['address_2'] = wp_parse_args( $this->billing_settings['billing_address_2'], $fields['address_2'] );
		} elseif ( ! empty( $this->shipping_settings['shipping_address_2'] ) ) {
			$fields['address_2'] = wp_parse_args( $this->shipping_settings['shipping_address_2'], $fields['address_2'] );
		}
		// city.
		if ( ! empty( $this->billing_settings['billing_city'] ) ) {
			$fields['city'] = wp_parse_args( $this->billing_settings['billing_city'], $fields['city'] );
		} elseif ( ! empty( $this->shipping_settings['shipping_city'] ) ) {
			$fields['city'] = wp_parse_args( $this->shipping_settings['shipping_city'], $fields['city'] );
		}
		// state.
		if ( ! empty( $this->billing_settings['billing_state'] ) ) {
			$fields['state'] = wp_parse_args( $this->billing_settings['billing_state'], $fields['state'] );
		} elseif ( ! empty( $this->shipping_settings['shipping_state'] ) ) {
			$fields['state'] = wp_parse_args( $this->shipping_settings['shipping_state'], $fields['state'] );
		}
		// State.
		if ( ! empty( $this->billing_settings['billing_postcode'] ) ) {
			$fields['postcode'] = wp_parse_args( $this->billing_settings['billing_postcode'], $fields['postcode'] );
		} elseif ( ! empty( $this->shipping_settings['shipping_postcode'] ) ) {
			$fields['postcode'] = wp_parse_args( $this->shipping_settings['shipping_postcode'], $fields['postcode'] );
		}
		return $fields;
	}

	/**
	 * @param array $fields fields.
	 * @return array
	 */
	public function country_locale( $fields ) {
		// State.
		if ( isset( $fields['postcode']['label'] ) ) {
			if ( ! empty( $this->billing_settings['billing_postcode']['label'] ) ) {
				$fields['postcode']['label'] = $this->billing_settings['billing_postcode']['label'];
			} elseif ( ! empty( $this->shipping_settings['shipping_postcode']['label'] ) ) {
				$fields['postcode']['label'] = $this->shipping_settings['shipping_postcode']['label'];
			}
		}
		if ( isset( $fields['postcode']['required'] ) ) {
			if ( isset( $this->billing_settings['billing_postcode']['required'] ) ) {
				$fields['postcode']['required'] = $this->billing_settings['billing_postcode']['required'];
			} elseif ( isset( $this->shipping_settings['shipping_postcode']['required'] ) ) {
				$fields['postcode']['required'] = $this->shipping_settings['shipping_postcode']['required'];
			}
		}
		// Billing state.
		if ( isset( $fields['state']['label'] ) ) {
			if ( ! empty( $this->billing_settings['billing_state'] ) ) {
				$fields['state']['label'] = $this->billing_settings['billing_state']['label'] ?? $fields['postcode']['label'];
			} elseif ( ! empty( $this->shipping_settings['shipping_state'] ) ) {
				$fields['state']['label'] = $this->shipping_settings['shipping_state']['label'] ?? $fields['postcode']['label'];
			}
		}
		if ( isset( $fields['state']['required'] ) ) {
			if ( isset( $this->billing_settings['billing_state']['required'] ) ) {
				$fields['state']['required'] = $this->billing_settings['billing_state']['required'];
			} elseif ( isset( $this->shipping_settings['shipping_state']['required'] ) ) {
				$fields['state']['required'] = $this->shipping_settings['shipping_state']['required'];
			}
		}

		// Billing city.
		if ( isset( $fields['city']['label'] ) ) {
			if ( ! empty( $this->billing_settings['billing_city'] ) ) {
				$fields['city']['label'] = $this->billing_settings['billing_city']['label'] ?? $fields['city']['label'];
			} elseif ( ! empty( $this->shipping_settings['shipping_city'] ) ) {
				$fields['city']['label'] = $this->shipping_settings['shipping_city'] ?? $fields['city']['label'];
			}
		}

		if ( isset( $fields['city']['required'] ) ) {
			if ( isset( $this->billing_settings['billing_city']['required'] ) ) {
				$fields['city']['required'] = $this->billing_settings['billing_city']['required'];
			} elseif ( isset( $this->shipping_settings['shipping_city']['required'] ) ) {
				$fields['city']['required'] = $this->shipping_settings['shipping_city']['required'];
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
