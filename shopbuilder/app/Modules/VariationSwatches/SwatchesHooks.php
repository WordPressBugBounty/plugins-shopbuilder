<?php
/**
 * Main FilterHooks class.
 *
 * @package RadiusTheme\SB
 */

namespace RadiusTheme\SB\Modules\VariationSwatches;

use RadiusTheme\SB\Helpers\BuilderFns;
use RadiusTheme\SB\Helpers\Cache;
use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit();

/**
 * Main FilterHooks class.
 */
class SwatchesHooks {
	/**
	 * Singleton Trait.
	 */
	use SingletonTrait;

	/**
	 * Module Class Constructor.
	 */
	private function __construct() {
		add_filter( 'post_class', [ $this, 'product_loop_post_class' ], 25 );
		add_filter( 'rtsb/builder/content/parent_class', [ $this, 'filter_parent_classes' ] );

		add_filter( 'woocommerce_available_variation', [ $this, 'available_variation' ], 100, 3 );
		add_filter( 'woocommerce_dropdown_variation_attribute_options_html', [ $this,  'variation_attribute_options_html' ], 200, 2 );
		if ( ! is_admin() ) {
			add_filter( 'woocommerce_ajax_variation_threshold', [ $this, 'ajax_variation_threshold' ], 99 );
		}
		add_action( 'wp_ajax_rtsb_load_product_variation', [ $this, 'load_product_variation' ] );
		add_action( 'wp_ajax_nopriv_rtsb_load_product_variation', [ $this, 'load_product_variation' ] );
	}

	/**
	 * Filter the parent wrapper CSS classes.
	 *
	 * @param array $classes Existing classes.
	 * @return array Modified classes.
	 */
	public function filter_parent_classes( $classes ) {
		if ( ! BuilderFns::is_product() ) {
			return $classes;
		}
		$_product = Fns::get_product();
		if ( $_product->is_type( 'variable' ) ) {
			$classes[] = 'rtsb-vs-product';
		}
		return $classes;
	}
	/**
	 *  Available Variation
	 *
	 *  @param array                $variation variation.
	 *  @param \WC_Product          $product product.
	 *  @param \WC_Product_Variable $variationObj variation object.
	 */
	public function available_variation( $variation, $product, $variationObj ) {
		if ( isset( $variation['image']['thumb_src'] ) && ! empty( $variation['image']['thumb_src'] ) ) {
			$attachment_id                      = $variationObj->get_image_id();
			$thumbnail_size                     = apply_filters( 'woocommerce_thumbnail_size', 'woocommerce_thumbnail' );
			$thumb_srcset                       = function_exists( 'wp_get_attachment_image_srcset' ) ? wp_get_attachment_image_srcset( $attachment_id, $thumbnail_size ) : false;
			$thumb_sizes                        = function_exists( 'wp_get_attachment_image_sizes' ) ? wp_get_attachment_image_sizes( $attachment_id, $thumbnail_size ) : false;
			$variation['image']['thumb_srcset'] = apply_filters( 'rtsb_thumb_srcset', $thumb_srcset, $variation, $product, $variationObj );
			$variation['image']['thumb_sizes']  = apply_filters( 'rtsb_thumb_sizes', $thumb_sizes, $variation, $product, $variationObj );
		}
		if ( 'on' === SwatchesFns::get_options( 'disable_out_of_stock' ) && ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ! is_admin() ) ) {
			return $variationObj->is_in_stock() ? $variation : false;
		}

		return $variation;
	}


	/**
	 * Add Product Class
	 *
	 * @param  string $classes product wrapper class.
	 * @return string
	 */
	public function product_loop_post_class( $classes ) {
		global $product;
		if ( ! $product instanceof \WC_Product ) {
			return $classes;
		}
		if ( wp_is_mobile() ) {
			$classes[] = 'rtsb-is-mobile';
		}
		if ( $product->is_type( 'variable' ) ) {
			$classes[] = 'rtsb-vs-product';
			$classes[] = 'rtsb-' . SwatchesFns::get_options( 'shape_style' );
			$classes[] = 'rtsb-attribute-behavior-' . SwatchesFns::get_options( 'disabled_attribute_behavior' );
			if ( 'on' === SwatchesFns::get_options( 'show_tooltip' ) ) {
				$classes[] = 'rtsb-tooltip';
			}
		}
		return $classes;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function load_product_variation() {
		if ( ! wp_verify_nonce( Fns::get_nonce(), rtsb()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Something Went Wrong', 'shopbuilder' ) );
		}
		$product_id = ! empty( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$product    = wc_get_product( $product_id );

		$available_variations = $product->get_available_variations();

		wp_send_json_success( $available_variations );
	}

	/**
	 * Ajax Variation Threshold.
	 *
	 * @return int
	 */
	public function ajax_variation_threshold() {
		return 1;
	}

	/**
	 * Variation Attribute Options Html.
	 *
	 * @param string $html Html.
	 * @param array  $args Args.
	 *
	 * @return string
	 */
	public function variation_attribute_options_html( $html, $args ) {
		if ( apply_filters( 'default_rtsb_variation_attribute_options_html', false, $args, $html ) ) {
			return $html;
		}
		// WooCommerce Product Bundle Fixing.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( 'woocommerce_configure_bundle_order_item' === wp_unslash( $_POST['action'] ?? '' ) ) {
			return $html;
		}
		if ( empty( $args['attribute'] ) ) {
			return $html;
		}

		if ( empty( $args['product'] ) ) {
			return $html;
		}
		return SwatchesFns::generate_variation_attribute_option_html( apply_filters( 'rtsb_vs_variation_attribute_options_args', $args ), $html );
	}
}
