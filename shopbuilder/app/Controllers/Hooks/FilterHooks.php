<?php
/**
 * Main FilterHooks class.
 *
 * @package RadiusTheme\SB
 */

namespace RadiusTheme\SB\Controllers\Hooks;

use RadiusTheme\SB\Helpers\BuilderFns;
use RadiusTheme\SB\Helpers\ElementorDataMap;
use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Models\GeneralList;

defined( 'ABSPATH' ) || exit();

/**
 * Main FilterHooks class.
 */
class FilterHooks {
	/**
	 * Init Hooks.
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_filter( 'wp_kses_allowed_html', [ __CLASS__, 'custom_wpkses_post_tags' ], 10, 2 );

		add_filter( 'woocommerce_get_country_locale', [ __CLASS__, 'change_country_locale' ] );
		add_filter( 'woocommerce_checkout_fields', [ __CLASS__, 'woocommerce_checkout_fields' ], 50 );
		add_filter( 'woocommerce_default_address_fields', [ __CLASS__, 'default_address_fields' ] );

		add_filter( 'woocommerce_add_to_cart_form_action', [ __CLASS__, 'preview_page_product_permalink' ] );
		add_filter( 'woocommerce_add_to_cart_redirect', [ __CLASS__, 'preview_page_product_add_to_cart_redirect' ], 10 );
		add_filter( 'woocommerce_product_get_rating_html', [ __CLASS__, 'get_star_rating_html' ], 11, 3 );
		add_filter( 'woocommerce_product_data_store_cpt_get_products_query', [ __CLASS__, 'custom_query_keys' ], 10, 2 );
		// Overriding Notice Template.
		add_filter( 'wc_get_template', [ __CLASS__, 'get_notice_template' ], 15, 2 );
	}

	/***
	 * @param string $template Template Path.
	 * @param string $template_name Template Name.
	 *
	 * @return mixed
	 */
	public static function get_notice_template( $template, $template_name ) {
		switch ( $template_name ) {
			case 'notices/success.php':
			case 'notices/error.php':
			case 'notices/notice.php':
				$rtsb_template = 'elementor/general/' . str_replace( '.php', '', $template_name );
				$template      = Fns::locate_template( $rtsb_template ) ?? $template;
				break;
		}
		return $template;
	}

	/**
	 * @param $url
	 * @param $product
	 * @return false|mixed|string
	 */
	public static function get_star_rating_html( $html, $rating, $count ) {
		if ( '0' === $rating ) {
			$html .= '<div class="star-rating empty">';
			$html .= wc_get_star_rating_html( $rating, $count );
			$html .= '</div>';
		}

		return '<div class="rtsb-star-rating">' . $html . '</div>';
	}

	/**
	 * @param string $url Cart Page Url.
	 * @return false|mixed|string
	 */
	public static function preview_page_product_add_to_cart_redirect( $url ) {
		if ( ! empty( $_GET['demopage'] ) ) {  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$demo_id = absint( $_GET['demopage'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( get_post_type( $demo_id ) === BuilderFns::$post_type_tb && 'product' === BuilderFns::builder_type( $demo_id ) ) {
				 $url = get_the_permalink( $demo_id );
			}
		}
		return $url;
	}

	/**
	 * @param string $permalink Permalink.
	 * @return string
	 */
	public static function preview_page_product_permalink( $permalink ) {
		$global_query_id = get_queried_object_id();
		if ( BuilderFns::is_builder_preview() && 'product' === BuilderFns::builder_type( $global_query_id ) ) {
			$permalink = esc_url(
				add_query_arg(
					[
						'demopage' => $global_query_id,
					],
					$permalink
				)
			);
		}
		return $permalink;
	}

	/**
	 * Localize Scripts
	 *
	 * @param array $locale locale variable.
	 *
	 * @return array
	 */
	public static function change_country_locale( $locale ) {
		if ( Fns::is_module_active( 'checkout_fields_editor' ) ) {
			return $locale;
		}
		$elementor_list = GeneralList::instance()->get_data();

		if ( ! array_key_exists( 'billing_form', $elementor_list ) ) {
			return $locale;
		}

		$billing_settings = $elementor_list['billing_form'];

		if ( empty( $billing_settings['active'] ) ) {
			return $locale;
		}

		if ( ! empty( $billing_settings['billing_state_label'] ) ) {
			$locale[ WC()->countries->get_base_country() ]['state']['label'] = $billing_settings['billing_state_label'];
		}

		if ( ! empty( $billing_settings['billing_postcode_label'] ) ) {
			$locale[ WC()->countries->get_base_country() ]['postcode']['label'] = $billing_settings['billing_postcode_label'];
		}

		return $locale;
	}
	/**
	 * Add exceptions in wp_kses_post tags.
	 *
	 * @param array  $tags Allowed tags, attributes, and/or entities.
	 * @param string $context Context to judge allowed tags by. Allowed values are 'post'.
	 *
	 * @return array
	 */
	public static function custom_wpkses_post_tags( $tags, $context ) {
		if ( 'post' === $context ) {
			$tags['iframe'] = [
				'src'             => true,
				'height'          => true,
				'width'           => true,
				'frameborder'     => true,
				'allowfullscreen' => true,
			];

			$tags['svg'] = [
				'class'           => true,
				'aria-hidden'     => true,
				'aria-labelledby' => true,
				'role'            => true,
				'xmlns'           => true,
				'width'           => true,
				'height'          => true,
				'viewbox'         => true,
				'stroke'          => true,
				'fill'            => true,
			];

			$tags['g']     = [ 'fill' => true ];
			$tags['title'] = [ 'title' => true ];
			$tags['path']  = [
				'd'               => true,
				'fill'            => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
				'fill-rule'       => true,
				'clip-rule'       => true,
				'stroke'          => true,
			];
		}

		return $tags;
	}

	/**
	 * Set field Keyword.
	 *
	 * @return array
	 */
	private static function checkout_field( $form_name, $billing_settings, $fields, $field_id = 'billing_field_name' ) {

		if ( ! isset( $billing_settings[ $field_id ] ) ) {
			return $fields;
		}

		$settings_value = $billing_settings[ $field_id ];
		if ( ! in_array( 'show', $settings_value, true ) ) {
			unset( $fields[ $form_name ][ $field_id ] );
			if ( $form_name . '_address_1' === $field_id ) {
				unset( $fields[ $form_name ][ $form_name . '_address_2' ] );
			}
			return $fields;
		}

		if ( $form_name . '_address_2' !== $field_id ) {
			$fields[ $form_name ][ $field_id ]['required'] = in_array( 'required', $settings_value, true );
		}

		$labels = [
			'label'       => $field_id . '_label',
			'placeholder' => $field_id . '_placeholder',
		];

		foreach ( $labels as $key => $value ) {
			if ( ! empty( $billing_settings[ $value ] ) ) {
				$fields[ $form_name ][ $field_id ][ $key ] = $billing_settings[ $value ];
			}
		}

		return $fields;
	}


	/**
	 * Set Widget Keyword.
	 *
	 * @return array
	 */
	public static function woocommerce_checkout_fields( $fields ) {
		// Only on checkout page.
		if ( Fns::is_module_active( 'checkout_fields_editor' ) ) {
			return $fields;
		}
		$elementor_list = GeneralList::instance()->get_data();
		$fields_key     = [];

		$forms = [ 'billing_form', 'shipping_form' ];

		foreach ( $forms as $form ) {
			if ( ! array_key_exists( $form, $elementor_list ) || empty( $elementor_list[ $form ]['active'] ) ) {
				continue;
			}
			$form_name                = str_replace( '_form', '', $form );
			$fields_key[ $form_name ] = [
				$form_name . '_first_name',
				$form_name . '_last_name',
				$form_name . '_company',
				$form_name . '_country',
				$form_name . '_postcode',
				$form_name . '_address_2',
				$form_name . '_address_1',
				$form_name . '_state',
				$form_name . '_city',
			];

			if ( 'billing_form' === $form ) {
				$fields_key[ $form_name ][] = $form_name . '_phone';
				$fields_key[ $form_name ][] = $form_name . '_email';
			}
		}
		foreach ( $fields_key as $form_name => $form_fields ) {
			foreach ( $form_fields as $field_id ) {
				$fields = self::checkout_field( $form_name, $elementor_list[ $form_name . '_form' ], $fields, $field_id );
			}
		}

		return $fields;
	}

	/**
	 * Set Widget Keyword.
	 *
	 * @return array
	 */
	public static function default_address_fields( $fields ) {
		// Only on checkout page.
		if ( Fns::is_module_active( 'checkout_fields_editor' ) ) {
			return $fields;
		}
		$elementor_list = GeneralList::instance()->get_data();

		if ( ! array_key_exists( 'billing_form', $elementor_list ) ) {
			return $fields;
		}

		$billing_settings = $elementor_list['billing_form'];

		if ( empty( $billing_settings['active'] ) ) {
			return $fields;
		}

		$fields_key = [
			'address_2',
			'address_1',
			'state',
			'city',
			'postcode',
		];
		foreach ( $fields_key as $value ) {
			$fields = self::checkout_billing_address_field( $billing_settings, $fields, $value );
		}
		return $fields;
	}

	/**
	 * Set field Keyword.
	 *
	 * @return array
	 */
	private static function checkout_billing_address_field( $billing_settings, $fields, $field_id = '' ) {
		$settings_field_id = 'billing_' . $field_id;
		if ( ! isset( $billing_settings[ $settings_field_id ] ) ) {
			return $fields;
		}

		$settings_value = $billing_settings[ $settings_field_id ];

		$fields[ $field_id ]['required'] = in_array( 'required', $settings_value, true );

		$labels = [
			'label'       => $settings_field_id . '_label',
			'placeholder' => $settings_field_id . '_placeholder',
		];

		foreach ( $labels as $key => $value ) {
			if ( ! empty( $billing_settings[ $value ] ) ) {
				$fields[ $field_id ][ $key ] = $billing_settings[ $value ];
			}
		}

		return $fields;
	}

	/**
	 * Handle a custom query var.
	 *
	 * @param array $wp_query_args - Args for WP_Query.
	 * @param array $query_vars - Query vars from WC_Product_Query.
	 *
	 * @return array
	 */
	public static function custom_query_keys( $wp_query_args, $query_vars ) {
		$tax_key    = 'product_attribute_id';
		$rating_key = 'product_rating';

		if ( ! empty( $query_vars[ $tax_key ] ) ) {
			foreach ( $query_vars[ $tax_key ] as $atts ) {
				$attribute_tax = get_term( $atts )->taxonomy;

				$wp_query_args['tax_query'][] = [
					'taxonomy' => $attribute_tax,
					'field'    => 'term_id',
					'terms'    => [ $atts ],
					'operator' => 'IN',
				];
			}
		}

		if ( ! empty( $query_vars[ $rating_key ] ) ) {
			$wp_query_args['meta_query'][] = [
				'key'     => '_wc_average_rating',
				'value'   => esc_attr( $query_vars[ $rating_key ] ),
				'type'    => 'numeric',
				'compare' => 'IN',
			];
		}

		return $wp_query_args;
	}

	/**
	 * Define extra query arguments for WooCommerce product retrieval.
	 *
	 * @param array $wp_query_args The original WooCommerce product query arguments.
	 * @param array $query_vars    The query variables for the product query.
	 *
	 * @return array
	 */
	public static function extra_query_args( $wp_query_args, $query_vars ) {
		$type_condition = $query_vars['type'];

		foreach ( $wp_query_args['tax_query'] as $key => $tax_query ) {
			if ( isset( $tax_query['taxonomy'], $tax_query['field'], $tax_query['terms'] ) &&
				'product_type' === $tax_query['taxonomy'] &&
				'slug' === $tax_query['field'] &&
				$tax_query['terms'] === $type_condition ) {

				unset( $wp_query_args['tax_query'][ $key ] );
				break;
			}
		}

		$wp_query_args['tax_query']['relation'] = 'OR';

		return $wp_query_args;
	}

	/**
	 * Define extra query arguments for out of stock visibility.
	 *
	 * @param array $wp_query_args The original WooCommerce product query arguments.
	 *
	 * @return array
	 */
	public static function extra_query_args_outofstock( $wp_query_args ) {
		$product_visibility_terms     = wc_get_product_visibility_term_ids();
		$wp_query_args['tax_query'][] = [
			'taxonomy' => 'product_visibility',
			'field'    => 'term_taxonomy_id',
			'terms'    => [ $product_visibility_terms['outofstock'] ],
			'operator' => 'NOT IN',
		];

		return $wp_query_args;
	}
}
