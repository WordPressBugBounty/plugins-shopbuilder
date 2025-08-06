<?php
/**
 * Single Product Image
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-image.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.7.0
 */

use Automattic\WooCommerce\Enums\ProductType;
use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Modules\VariationGallery\GalleryFns;

defined( 'ABSPATH' ) || exit;

// Note: `wc_get_gallery_image_html` was added in WC 3.3.2 and did not exist prior. This check protects against theme overrides being used on older versions of WC.
if ( ! function_exists( 'wc_get_gallery_image_html' ) ) {
	return;
}

global $product;
$post_thumbnail_id = $product->get_image_id();
$attachment_ids    = $product->get_gallery_image_ids();
$rand              = wp_rand();
$randThumbnail     = wp_rand();

$galleryStyle = 'bottom';
if ( rtsb()->has_pro() ) {
	$galleryStyle = GalleryFns::get_options( 'gallery_style' ) ?: $galleryStyle;
}
$image_zoom     = GalleryFns::get_options( 'image_zoom' ) ?: '';
$slider_options = [
	'spaceBetween'           => 0,
	'speed'                  => 500,
	'loop'                   => false,
	'autoHeight'             => false,
	'navigation'             => [
		'nextEl' => '.swiper-gallery-next.rtsb-random-id-' . esc_attr( $rand ),
		'prevEl' => '.swiper-gallery-prev.rtsb-random-id-' . esc_attr( $rand ),
	],
	'thumbsSelectorNoSlider' => '.rtsb-thumb', // If Swiper Is not active.
];
$slider_options = apply_filters( 'rtsb/vg/slider/options', $slider_options );
$images         = [];
if ( $post_thumbnail_id ) {
	$images[] = $post_thumbnail_id;
}
if ( is_array( $attachment_ids ) && count( $attachment_ids ) ) {
	$images = array_merge( $images, $attachment_ids );
}
$images          = array_unique( $images );
$wrapper_classes = apply_filters(
	'woocommerce_single_product_image_gallery_classes',
	[
		'woocommerce-product-gallery',
		'woocommerce-product-gallery--' . ( $post_thumbnail_id ? 'with-images' : 'without-images' ),
		'images',
	]
);
$custom_class    = [];
if ( rtsb()->has_pro() ) {
	$custom_class[] = 'rtsb-pro-active';
}
$position       = apply_filters( 'rtsb/vg/thumbnails/position', $galleryStyle );
$col            = GalleryFns::get_options( 'thumbnails_columns' ) ?: 4;
$col            = apply_filters( 'rtsb/vg/gallery/columns', $col );
$custom_class[] = 'rtsb-thumbnails-position-' . $position;
$icon_right     = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 320 512" aria-hidden="true"><path d="M285.5 273l-194 194c-9.4 9.4-24.6 9.4-33.9 0l-22.6-22.6c-9.4-9.4-9.4-24.6 0-33.9L184.6 256 35 106.6c-9.4-9.4-9.4-24.6 0-33.9L57.6 50c9.4-9.4 24.6-9.4 33.9 0l194 194c9.4 9.4 9.4 24.6 0 34z"/></svg>';
$icon_left      = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 320 512" aria-hidden="true"><path d="M34.5 239l194-194c9.4-9.4 24.6-9.4 33.9 0l22.6 22.6c9.4 9.4 9.4 24.6 0 33.9L135.4 256l150.6 149.4c9.4 9.4 9.4 24.6 0 33.9l-22.6 22.6c-9.4 9.4-24.6 9.4-33.9 0l-194-194c-9.4-9.4-9.4-24.6 0-34z"/></svg>';
?>
<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>">
	<div class="rtsb-vg-main-slider-wrapper rtsb-content-loading <?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $custom_class ) ) ); ?>" data-image-zoom="<?php echo esc_attr( boolval( $image_zoom ) ); ?>" >
		<div class="rtsb-carousel-slider product-vg-gallery"  data-options=<?php echo esc_js( wp_json_encode( $slider_options ) ); ?>>
			<!-- Main Slider -->
			<?php
			do_action( 'rtsb_product_badge' );
			$showLightBox = apply_filters( 'rtsb/vg/lightbox', 'on' === GalleryFns::get_options( 'lightBox' ) );
			if ( $post_thumbnail_id && $showLightBox ) :
				$zoomPosition     = apply_filters( 'rtsb/vg/lightbox/position', GalleryFns::get_options( 'lightBox_button_position' ) );
				$lightBoxPosition = $zoomPosition ?: 'top-right';
				?>
				<a href="#" class="rtsb-vg-trigger rtsb-vg-trigger-position-<?php echo esc_attr( $lightBoxPosition ); ?> rtsb-vg-image-trigger">
					<?php ob_start(); ?>
						<svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
							<path d="M12.9 14.32a8 8 0 1 1 1.41-1.41l4.39 4.39a1 1 0 0 1-1.41 1.41l-4.39-4.39ZM14 8a6 6 0 1 0-12 0 6 6 0 0 0 12 0Z"/>
						</svg>
					<?php
					$icon_html = ob_get_clean();
					Fns::print_html( apply_filters( 'rtsb/vg/lightbox/trigger/icon', $icon_html ), true );
					?>
					<span class="screen-reader-text"><?php echo esc_html( 'Zoom' ); ?></span>
				</a>
			<?php endif; ?>
			<?php if ( is_array( $images ) && count( $images ) ) { ?>
				<div class="swiper-wrapper">
					<?php foreach ( $images as $key => $attachment_id ) { ?>
						<div class="swiper-slide rtsb-vs-main-image-<?php echo esc_attr( $attachment_id ); ?>">
							<?php Fns::print_html( GalleryFns::get_main_attachment_html( $attachment_id, $product ), true ); ?>
						</div>
					<?php } ?>
				</div>
				<div class="swiper-nav <?php echo esc_attr( count( $images ) > 1 ? '' : 'rtsb-vg-swiper-nav-hide' ); ?>" >
					<!-- Navigation Arrows -->
					<div class="swiper-arrow swiper-gallery-next rtsb-random-id-<?php echo esc_attr( $rand ); ?>"> <?php Fns::print_html( $icon_right, true ); ?> </div>
					<div class="swiper-arrow swiper-gallery-prev rtsb-random-id-<?php echo esc_attr( $rand ); ?>"> <?php Fns::print_html( $icon_left, true ); ?> </div>
				</div>
			<?php } ?>
		</div>
		<?php
			$options = apply_filters(
				'rtsb/vg/thumbnails/slider/options',
				[],
				[
					'randDom'            => $randThumbnail,
					'thumbnailsPosition' => $position,
				]
			);
			?>
			<div class=" rtsb-vs-thumb-slider rtsb-vs-thumb-column-<?php echo esc_attr( $col ); ?>" data-options=<?php echo esc_js( wp_json_encode( $options ) ); ?> >
				<div class="swiper-wrapper">
					<?php if ( is_array( $attachment_ids ) && count( $attachment_ids ) ) { ?>
						<?php foreach ( $images as $key => $attachment_id ) { ?>
							<div class="swiper-slide rtsb-thumb rtsb-vs-thumb-item-<?php echo esc_attr( $attachment_id ); ?>" data-index="<?php echo esc_attr( $key ); ?>" >
								<?php Fns::print_html( GalleryFns::get_thumbnail_attachment_html( $attachment_id, $product ), true ); ?>
							</div>
						<?php } ?>
					<?php } ?>
				</div>
				<?php
				do_action(
					'rtsb/after/product/thumbnail/image',
					[
						'randDom'   => $randThumbnail,
						'iconLeft'  => $icon_left,
						'iconRight' => $icon_right,
						'images'    => $images,
						'col'       => $col,
					]
				);
				?>
			</div>


	</div>
</div>