<?php
/**
 * Main FilterHooks class.
 *
 * @package RadiusTheme\SB
 */

namespace RadiusTheme\SB\Modules\VariationGallery;

use RadiusTheme\SB\Helpers\BuilderFns;
use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit();

/**
 * Main FilterHooks class.
 */
class GalleryFrontEnd {

	/**
	 * Singleton Trait.
	 */
	use SingletonTrait;

	/**
	 * Module Class Constructor.
	 */
	private function __construct() {
		add_filter( 'woocommerce_locate_template', [ $this, 'override_templates' ], 10, 2 );
		add_action( 'wp_footer', [ $this, 'slider_and_thumbnail_template_js' ] );
		add_filter( 'woocommerce_available_variation', [ $this, 'available_variation_gallery' ], 90, 3 );

		add_action( 'wp_ajax_rtsb_vg_get_default_gallery_images', [ $this, 'get_default_gallery_images' ] );
		add_action( 'wp_ajax_nopriv_rtsb_vg_get_default_gallery_images', [ $this, 'get_default_gallery_images' ] );

		// On-demand single-variation gallery. Nonce-less / read-only by design,
		// mirroring WooCommerce core's own `woocommerce_get_variation` AJAX: a
		// required nonce breaks on full-page-cached pages because the embedded
		// nonce goes stale, and the payload is public, read-only product images.
		add_action( 'wp_ajax_rtsb_get_variation_gallery', [ $this, 'get_variation_gallery' ] );
		add_action( 'wp_ajax_nopriv_rtsb_get_variation_gallery', [ $this, 'get_variation_gallery' ] );
	}
	/**
	 * @param array  $available_variation Available variation.
	 * @param object $variationProductObject object.
	 * @param object $variation variation.
	 *
	 * @return array
	 */
	public function available_variation_gallery( $available_variation, $variationProductObject, $variation ) {
		$variation_id = absint( $variation->get_id() );

		// On single product pages galleries are fetched on-demand over AJAX (see
		// get_variation_gallery()), so we intentionally do NOT embed per-variation image
		// props in the variation JSON — that keeps the page lean and ensures each
		// variation's gallery is fetched only when it is selected. WooCommerce already
		// includes `variation_id`, which is all the frontend needs to request it.
		//
		// In other contexts (e.g. the archive/shop variation image swap) the inline
		// data is still consumed, so expose an already-cached gallery if one exists.
		// This is a cheap get_transient() — no prop building.
		if ( ! is_product() && ! BuilderFns::is_product() ) {
			$transient_name = GalleryFns::get_transient_name( $variation_id, 'variation-images' );
			$images         = $transient_name ? get_transient( $transient_name ) : false;
			if ( ! empty( $images ) && is_array( $images ) ) {
				$available_variation['variation_gallery_images'] = array_values( $images );
			}
		}

		return apply_filters( 'rtsb/vg/available/variation/gallery', $available_variation, $variation, $variation_id );
	}
	/**
	 * @return void
	 */
	public function get_default_gallery_images() {
		if ( ! wp_verify_nonce( Fns::get_nonce(), rtsb()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Something Went Wrong', 'shopbuilder' ) );
		}
		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;

		if ( ! $product_id ) {
			wp_send_json_error( esc_html__( 'Invalid product ID.', 'shopbuilder' ) );
		}

		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			wp_send_json_error( esc_html__( 'Product not found.', 'shopbuilder' ) );
		}

		if ( ! $this->can_user_view_product( $product ) ) {
			wp_send_json_error( esc_html__( 'You do not have permission to view this product.', 'shopbuilder' ) );
		}

		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$images     = GalleryFns::get_gallery_images_and_props( $product_id );
		wp_send_json_success( $images );
	}

	/**
	 * AJAX: Return the gallery image props for a single selected variation.
	 *
	 * Read-only / nonce-less by design, mirroring WooCommerce core's own
	 * `woocommerce_get_variation` endpoint. A required nonce would break on
	 * full-page-cached pages (stale embedded nonce); the response is public,
	 * read-only product image data, so there is no CSRF concern.
	 *
	 * @return void
	 */
	public function get_variation_gallery() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$variation_id = isset( $_POST['variation_id'] ) ? absint( wp_unslash( $_POST['variation_id'] ) ) : 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;

		if ( ! $variation_id ) {
			wp_send_json_error( esc_html__( 'Invalid variation ID.', 'shopbuilder' ) );
		}

		$variation = wc_get_product( $variation_id );

		if ( ! $variation || 'variation' !== $variation->get_type() ) {
			wp_send_json_error( esc_html__( 'Variation not found.', 'shopbuilder' ) );
		}

		if ( ! $product_id ) {
			$product_id = absint( $variation->get_parent_id() );
		}

		$product = $product_id ? wc_get_product( $product_id ) : false;

		if ( ! $product || ! $this->can_user_view_product( $product ) ) {
			wp_send_json_error( esc_html__( 'You do not have permission to view this product.', 'shopbuilder' ) );
		}

		$images = GalleryFns::get_variation_gallery( $product_id, $variation_id );
		wp_send_json_success( $images );
	}

	/**
	 * Determines if the current user has permission to view a product.
	 *
	 * Checks product status and visibility settings against user capabilities.
	 *
	 * @param \WC_Product $product The product to check access for.
	 * @return bool True if user can view the product, false otherwise.
	 */
	private function can_user_view_product( $product ) {
		$product_id = $product->get_id();
		$status     = get_post_status( $product->get_id() );
		$visibility = $product->get_catalog_visibility();

		if ( 'publish' === $status && 'visible' === $visibility ) {
			return true;
		}

		return current_user_can( 'edit_post', $product_id );
	}
	/**
	 * @param string $template template.
	 * @param string $template_name template name.
	 * @return mixed|null
	 */
	public function override_templates( $template, $template_name ) {
		// List of templates you want to override.
		if ( 'single-product/product-image.php' === $template_name ) {
			$galleryStyle    = GalleryFns::get_options( 'gallery_style' );
			$galleryStyle    = apply_filters( 'rtsb/vg/thumbnails/position', $galleryStyle );
			$temp            = rtsb()->has_pro() && 'grid' === $galleryStyle ? 'variation-gallery/vg-grid-view' : 'variation-gallery/product-image';
			$custom_template = Fns::locate_template( $temp );
			if ( file_exists( $custom_template ) ) {
				return $custom_template;
			}
		}
		return $template;
	}
	/**
	 * @return void
	 */
	public function slider_and_thumbnail_template_js() {
		wp_enqueue_script( 'underscore' );
		Fns::load_template( 'variation-gallery/vg-slider-template', [] );
		Fns::load_template( 'variation-gallery/vg-thumbnail-template', [] );
	}
}
