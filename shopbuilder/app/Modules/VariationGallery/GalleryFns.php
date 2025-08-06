<?php
/**
 * Sticky add-to-cart Functions Class.
 *
 * @package Rse\SB
 */

namespace RadiusTheme\SB\Modules\VariationGallery;

use RadiusTheme\SB\Helpers\Cache;
use RadiusTheme\SB\Helpers\Fns;
use WC_Product;

defined( 'ABSPATH' ) || exit();

/**
 * Sticky add-to-cart Functions Class.
 */
class GalleryFns {
	/**
	 * @param string       $key Default Attribute.
	 * @param array|string $default Default Attribute.
	 * @return array|string
	 */
	public static function get_options( $key = null, $default = '' ) {
		$options = Fns::get_options( 'modules', 'variation_gallery' );
		if ( $key ) {
			if ( isset( $options[ $key ] ) ) {
				return $options[ $key ];
			} else {
				return $default;
			}
		}
		return $options;
	}

	/**
	 * Output the HTML image tag for a product attachment.
	 *
	 * Retrieves the image properties using `product_attachment_props()` and outputs
	 * the image using `wp_get_attachment_image()`. Intended for variation galleries
	 * or product thumbnails.
	 *
	 * @param int         $image_id The attachment/image ID.
	 * @param \WC_Product $product  The WooCommerce product object.
	 * @param string      $size     The image size to output. Default 'woocommerce_thumbnail'.
	 *
	 * @return string The HTML.
	 */
	public static function get_main_attachment_html( $image_id, $product, $size = 'woocommerce_thumbnail' ) {
		$props = self::product_attachment_props( $image_id, $product );
		$html  = self::wc_get_gallery_image_html( $image_id, true );
		return apply_filters( 'rtsb/vg/get/main/attachment/html', $html, $props, $image_id, $product, $size );
	}

	/**
	 * Get HTML for a gallery image.
	 *
	 * Hooks: woocommerce_gallery_thumbnail_size, woocommerce_gallery_image_size and woocommerce_gallery_full_size accept name based image sizes, or an array of width/height values.
	 *
	 * @since 3.3.2
	 * @param int  $attachment_id Attachment ID.
	 * @param bool $main_image Is this the main image or a thumbnail?.
	 * @param int  $image_index The image index in the gallery.
	 * @return string
	 */
	public static function wc_get_gallery_image_html( $attachment_id, $main_image = false, $image_index = -1 ) {
		global $product;

		$flexslider        = (bool) apply_filters( 'woocommerce_single_product_flexslider_enabled', get_theme_support( 'wc-product-gallery-slider' ) );
		$gallery_thumbnail = wc_get_image_size( 'gallery_thumbnail' );
		$thumbnail_size    = apply_filters( 'woocommerce_gallery_thumbnail_size', [ $gallery_thumbnail['width'], $gallery_thumbnail['height'] ] );
		$image_size        = apply_filters( 'woocommerce_gallery_image_size', $flexslider || $main_image ? 'woocommerce_single' : $thumbnail_size );
		$full_size         = apply_filters( 'woocommerce_gallery_full_size', apply_filters( 'woocommerce_product_thumbnails_large_size', 'full' ) );
		$thumbnail_src     = wp_get_attachment_image_src( $attachment_id, $thumbnail_size );
		$thumbnail_srcset  = wp_get_attachment_image_srcset( $attachment_id, $thumbnail_size );
		$thumbnail_sizes   = wp_get_attachment_image_sizes( $attachment_id, $thumbnail_size );
		$full_src          = wp_get_attachment_image_src( $attachment_id, $full_size );
		$alt_text          = trim( wp_strip_all_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) );
		$alt_text          = ( empty( $alt_text ) && ( $product instanceof WC_Product ) ) ? woocommerce_get_alt_from_product_title_and_position( $product->get_title(), $main_image, $image_index ) : $alt_text;

		/**
		 * Filters the attributes for the image markup.
		 *
		 * @since 3.3.2
		 *
		 * @param array $image_attributes Attributes for the image markup.
		 */
		$image_params = apply_filters(
			'woocommerce_gallery_image_html_attachment_image_params',
			[
				'title'                   => _wp_specialchars( get_post_field( 'post_title', $attachment_id ), ENT_QUOTES, 'UTF-8', true ),
				'data-caption'            => _wp_specialchars( get_post_field( 'post_excerpt', $attachment_id ), ENT_QUOTES, 'UTF-8', true ),
				'data-src'                => esc_url( $full_src[0] ),
				'data-large_image'        => esc_url( $full_src[0] ),
				'data-large_image_width'  => esc_attr( $full_src[1] ),
				'data-large_image_height' => esc_attr( $full_src[2] ),
				'class'                   => esc_attr( $main_image ? 'wp-post-image' : '' ),
				'alt'                     => esc_attr( $alt_text ),
			],
			$attachment_id,
			$image_size,
			$main_image
		);

		if ( isset( $image_params['title'] ) ) {
			unset( $image_params['title'] );
		}

		$image = wp_get_attachment_image(
			$attachment_id,
			$image_size,
			false,
			$image_params
		);

		return $image;
	}

	/**
	 * Get the HTML for a variation gallery thumbnail image.
	 *
	 * Wraps the image in a container div and optionally adds a video-specific class
	 * if a video link is associated with the image. The final HTML is passed through
	 * a filter for customization.
	 *
	 * @param int         $image_id The attachment/image ID.
	 * @param \WC_Product $product  The WooCommerce product object.
	 * @param string      $size     The image size to output. Default 'thumbnail'.
	 *
	 * @return string Filtered HTML for the thumbnail image.
	 */
	public static function get_thumbnail_attachment_html( $image_id, $product, $size = 'thumbnail' ) {
		$props           = self::product_attachment_props( $image_id, $product );
		$add_video_class = ! empty( $props['rtsb_vg_video_link'] ) ? ' rtsb-vs-video' : '';
		ob_start();
		?>
		<div class="rtsb-vs-thumb-item <?php echo esc_attr( $add_video_class ); ?>">
			<?php Fns::print_html( wp_get_attachment_image( $image_id, $size ), true ); ?>
		</div>
		<?php
		$html = ob_get_clean();
		return apply_filters( 'rtsb/vg/get/thumbnail/attachment/html', $html, $props, $image_id, $product, $size );
	}
	/**
	 * @param int    $image_id image id.
	 * @param object $product WC_Product Object.
	 * @return array
	 */
	public static function product_attachment_props( $image_id, $product ) {
		$props             = wc_get_product_attachment_props( $image_id, $product );
		$props['image_id'] = $image_id;
		return apply_filters( 'rtsb/product/attachment/props', $props, $image_id, $product );
	}
	/**
	 * @param int $product_id product id.
	 *
	 * @return mixed|void
	 */
	public static function get_gallery_images_and_props( $product_id ) {
		$transient_name = self::get_transient_name( $product_id, 'default-images' );
		$images         = get_transient( $transient_name );
		if ( false !== $images ) {
			 return apply_filters( 'rtsb/vg/get/gallery/images', $images, $product_id );
		}
		$product           = wc_get_product( $product_id );
		$product_id        = $product->get_id();
		$attachment_ids    = $product->get_gallery_image_ids();
		$post_thumbnail_id = $product->get_image_id();
		$images            = [];
		$post_thumbnail_id = (int) apply_filters( 'rtsb/vg/post/thumbnail/id', $post_thumbnail_id, $attachment_ids, $product );
		$attachment_ids    = (array) apply_filters( 'rtsb/vg/attachment/ids', $attachment_ids, $post_thumbnail_id, $product );
		if ( ! empty( $post_thumbnail_id ) ) {
			array_unshift( $attachment_ids, $post_thumbnail_id );
		}
		if ( is_array( $attachment_ids ) && ! empty( $attachment_ids ) ) {
			foreach ( $attachment_ids as $i => $image_id ) {
				if ( $image_id ) {
					$images[ $i ] = self::product_attachment_props( $image_id, $product );
				}
			}
		}
		set_transient( $transient_name, $images, 12 * HOUR_IN_SECONDS );
		Cache::set_transient_cache_key( $transient_name );
		return apply_filters( 'rtsb/vg/get/gallery/images', $images, $product_id );
	}
	/**
	 * Helper: Get all images transient name for specific variation/product
	 *
	 * @param int    $id unique id.
	 * @param string $type transient type.
	 *
	 * @return string
	 */
	public static function get_transient_name( $id, $type ) {
		if ( 'default-images' === $type ) {
			$id             = self::wpml_get_original_variation_id( $id );
			$transient_name = sprintf( 'rtsb_vg_default_images_%d', $id );
		} elseif ( 'variation-images' === $type ) {
			$id             = self::wpml_get_original_variation_id( $id );
			$transient_name = sprintf( 'rtsb_vg_variation_images_%d', $id );
		} elseif ( 'sizes' === $type ) {
			$transient_name = sprintf( 'rtsb_vg_variation_image_sizes_%d', $id );
		} elseif ( 'variation' === $type ) {
			$transient_name = sprintf( 'rtsb_vg_variation_%d', $id );
		} else {
			$transient_name = false;
		}
		return apply_filters( 'rtsb/vg/transient/name', $transient_name, $type, $id );
	}
	/**
	 * Helper: Delete all transient
	 *
	 * @param int    $product_id Product id.
	 * @param string $type Transient type.
	 */
	public static function delete_transients( $product_id = false, $type = '' ) {
		if ( $product_id ) {
			if ( $type ) {
				$default_transient_name = self::get_transient_name( $product_id, $type );
				delete_transient( $default_transient_name );
			} else {
				$default_transient_name = self::get_transient_name( $product_id, 'default-images' );
				delete_transient( $default_transient_name );
			}
		}
	}

	/**
	 * Helper: WPML - Get original variation ID
	 *
	 * If WPML is active and this is a translated variaition, get the original ID.
	 *
	 * @param int $id object id.
	 *
	 * @return int
	 */
	public static function wpml_get_original_variation_id( $id ) {
		$wpml_original_variation_id = get_post_meta( $id, '_wcml_duplicate_of_variation', true );
		if ( $wpml_original_variation_id ) {
			$id = $wpml_original_variation_id;
		}
		return $id;
	}
}
