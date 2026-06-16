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

		if ( ! is_array( $full_src ) ) {
			$full_src = [ '', '', '' ];
		}

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
	 * Build (and cache) the gallery image props for a single variation.
	 *
	 * Assembles the image-ID list for one variation only — the per-variation logic
	 * previously executed for every variation inside the
	 * `woocommerce_available_variation` filter — so galleries can be fetched
	 * on-demand over AJAX instead of being built in bulk on page load.
	 *
	 * The result is cached in a transient keyed by variation ID and is always a
	 * sequential array (built with `$images[]`) so it JSON-encodes to a JS array.
	 *
	 * @param int $product_id   Parent (variable) product ID.
	 * @param int $variation_id Variation ID whose gallery should be built.
	 *
	 * @return array Sequential array of image prop arrays.
	 */
	public static function get_variation_gallery( $product_id, $variation_id ) {
		$variation_id = absint( $variation_id );
		$product_id   = absint( $product_id );

		$transient_name = self::get_transient_name( $variation_id, 'variation-images' );
		$cached         = $transient_name ? get_transient( $transient_name ) : false;
		if ( ! empty( $cached ) && is_array( $cached ) ) {
			return apply_filters( 'rtsb/vg/get/variation/gallery', $cached, $product_id, $variation_id );
		}

		$variation = wc_get_product( $variation_id );
		if ( ! $variation ) {
			return [];
		}

		$parent_product = $product_id ? wc_get_product( $product_id ) : false;

		$has_variation_gallery_images = (bool) get_post_meta( $variation_id, 'rtsb_vg_images', true );
		if ( $has_variation_gallery_images ) {
			$gallery_images = (array) get_post_meta( $variation_id, 'rtsb_vg_images', true );
		} elseif ( $parent_product ) {
			$gallery_images = (array) $parent_product->get_gallery_image_ids();
		} else {
			$gallery_images = (array) $variation->get_gallery_image_ids();
		}

		$variation_image_id = absint( $variation->get_image_id() );
		if ( ! empty( $variation_image_id ) ) {
			array_unshift( $gallery_images, $variation_image_id );
		}

		$gallery_images = array_values( array_unique( array_filter( array_map( 'absint', $gallery_images ) ) ) );

		$images = [];
		foreach ( $gallery_images as $image_id ) {
			if ( $image_id ) {
				// Always append so the result stays a sequential (JS) array.
				$images[] = self::product_attachment_props( $image_id, $variation );
			}
		}

		if ( $transient_name ) {
			set_transient( $transient_name, $images, HOUR_IN_SECONDS * 12 );
			Cache::set_transient_cache_key( $transient_name );
		}

		return apply_filters( 'rtsb/vg/get/variation/gallery', $images, $product_id, $variation_id );
	}

	/**
	 * Resolve the default variation ID for a variable product.
	 *
	 * Returns the variation that WooCommerce's default attributes fully resolve to,
	 * so the matching gallery can be rendered server-side on initial page load.
	 *
	 * @param \WC_Product $product Variable product object.
	 *
	 * @return int Variation ID, or 0 when no single default variation resolves.
	 */
	public static function get_default_variation_id( $product ) {
		if ( ! ( $product instanceof WC_Product ) || ! $product->is_type( 'variable' ) ) {
			return 0;
		}

		$default_attributes = $product->get_default_attributes();
		if ( empty( $default_attributes ) ) {
			return 0;
		}

		$match_attributes = [];
		foreach ( $default_attributes as $key => $value ) {
			$match_attributes[ 'attribute_' . $key ] = $value;
		}

		$data_store   = \WC_Data_Store::load( 'product' );
		$variation_id = $data_store->find_matching_product_variation( $product, $match_attributes );

		return absint( $variation_id );
	}

	/**
	 * Get the attachment IDs to render for a product's initial gallery paint.
	 *
	 * When a default variation resolves, returns that variation's gallery image IDs
	 * (and warms its transient as a side effect of get_variation_gallery()). Otherwise
	 * falls back to the parent product's featured + gallery images.
	 *
	 * @param \WC_Product $product Product object.
	 *
	 * @return array {
	 *     @type int[]       $image_ids Sequential attachment IDs to render.
	 *     @type \WC_Product $context   Product/variation object to use as render context.
	 * }
	 */
	public static function get_initial_gallery_render( $product ) {
		$context   = $product;
		$image_ids = [];

		if ( $product instanceof WC_Product ) {
			$default_variation_id = self::get_default_variation_id( $product );
			if ( $default_variation_id ) {
				$variation_props = self::get_variation_gallery( $product->get_id(), $default_variation_id );
				foreach ( (array) $variation_props as $prop ) {
					if ( ! empty( $prop['image_id'] ) ) {
						$image_ids[] = absint( $prop['image_id'] );
					}
				}
				if ( ! empty( $image_ids ) ) {
					$variation = wc_get_product( $default_variation_id );
					if ( $variation ) {
						$context = $variation;
					}
				}
			}
		}

		if ( empty( $image_ids ) && ( $product instanceof WC_Product ) ) {
			$post_thumbnail_id = $product->get_image_id();
			$attachment_ids    = $product->get_gallery_image_ids();
			if ( $post_thumbnail_id ) {
				$image_ids[] = absint( $post_thumbnail_id );
			}
			if ( is_array( $attachment_ids ) && count( $attachment_ids ) ) {
				foreach ( $attachment_ids as $attachment_id ) {
					$image_ids[] = absint( $attachment_id );
				}
			}
		}

		$image_ids = array_values( array_unique( array_filter( $image_ids ) ) );

		return [
			'image_ids' => $image_ids,
			'context'   => $context,
		];
	}

	/**
	 * Flush all gallery transients (default + every variation) for a product.
	 *
	 * Used when a product or one of its variations is saved so stale gallery
	 * props are not served from cache.
	 *
	 * @param int $product_id Parent product ID.
	 *
	 * @return void
	 */
	public static function flush_product_gallery_transients( $product_id ) {
		$product_id = absint( $product_id );
		if ( ! $product_id ) {
			return;
		}

		self::delete_transients( $product_id, 'default-images' );

		$product = wc_get_product( $product_id );
		if ( ! $product || ! $product->is_type( 'variable' ) ) {
			return;
		}

		foreach ( $product->get_children() as $variation_id ) {
			self::delete_transients( $variation_id, 'variation-images' );
		}
	}

	/**
	 * Flush variation gallery transients that reference a given attachment.
	 *
	 * Invoked when an attachment is deleted so variations whose galleries include
	 * the removed image rebuild their props on next request.
	 *
	 * @param int $attachment_id Deleted attachment ID.
	 *
	 * @return void
	 */
	public static function flush_attachment_gallery_transients( $attachment_id ) {
		global $wpdb;

		$attachment_id = absint( $attachment_id );
		if ( ! $attachment_id ) {
			return;
		}

		// Flush the owning product (featured/parent) galleries when determinable.
		$parent_id = wp_get_post_parent_id( $attachment_id );
		if ( $parent_id ) {
			self::flush_product_gallery_transients( $parent_id );
		}

		// Flush any variation whose custom gallery meta references this attachment.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s",
				'rtsb_vg_images'
			)
		);

		if ( empty( $rows ) ) {
			return;
		}

		foreach ( $rows as $row ) {
			$image_ids = maybe_unserialize( $row->meta_value );
			if ( ! is_array( $image_ids ) ) {
				continue;
			}
			$image_ids = array_map( 'absint', $image_ids );
			// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			if ( in_array( $attachment_id, $image_ids, true ) ) {
				self::delete_transients( $row->post_id, 'variation-images' );
			}
		}
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
