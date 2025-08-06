<?php
/**
 * Main FilterHooks class.
 *
 * @package RadiusTheme\SB
 */

namespace RadiusTheme\SB\Modules\VariationGallery;

use RadiusTheme\SB\Helpers\BuilderFns;
use RadiusTheme\SB\Helpers\Cache;
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
	}
	/**
	 * @param array  $available_variation Available variation.
	 * @param object $variationProductObject object.
	 * @param object $variation variation.
	 *
	 * @return array
	 */
	public function available_variation_gallery( $available_variation, $variationProductObject, $variation ) {
		$variation_id   = absint( $variation->get_id() );
		$transient_name = GalleryFns::get_transient_name( $variation_id, 'variation-images' );
		$images         = get_transient( $transient_name );
		if ( ! empty( $images ) && is_array( $images ) ) {
			$available_variation['variation_gallery_images'] = $images;
			return apply_filters( 'rtsb/vg/available/variation/gallery', $available_variation, $variation, $variation_id );
		}
		$has_variation_gallery_images = (bool) get_post_meta( $variation_id, 'rtsb_vg_images', true );
		if ( $has_variation_gallery_images ) {
			$gallery_images = (array) get_post_meta( $variation_id, 'rtsb_vg_images', true );
		} else {
			$gallery_images = $variationProductObject->get_gallery_image_ids();
		}
		$variation_image_id = absint( $variation->get_image_id() );
		if ( ! empty( $variation_image_id ) ) {
			array_unshift( $gallery_images, $variation_image_id );
		}
		$gallery_images = array_values( array_unique( $gallery_images ) );
		$images         = [];
		foreach ( $gallery_images as $i => $image_id ) {
			if ( $image_id ) {
				$images[ $i ] = GalleryFns::product_attachment_props( $image_id, $variation );
			}
		}
		set_transient( $transient_name, $images, HOUR_IN_SECONDS * 12 );
		Cache::set_transient_cache_key( $transient_name );
		$available_variation['variation_gallery_images'] = $images;
		return apply_filters( 'rtsb/vg/available/variation/gallery', $available_variation, $variation, $variation_id );
	}
	/**
	 * @return void
	 */
	public function get_default_gallery_images() {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$images     = GalleryFns::get_gallery_images_and_props( $product_id );
		wp_send_json_success( $images );
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
