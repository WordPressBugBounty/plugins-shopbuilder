<?php
/**
 * Template variables:
 *
 * @var $controllers  array settings as array
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

global $product, $post;

if ( empty( $product ) ) {
	return;
}
$parent_class  = ! empty( $controllers['sale_flash_badge'] ) ? 'rtsb-sale-flash-position-' . $controllers['flash_sale_position'] : '';
$lightbox_icon = ! empty( $controllers['show_zoom'] ) ? $controllers['lightbox_icon'] : '';
$gallery_class = $product && $product->get_gallery_image_ids() ? ' has-product-gallery' : ' no-product-gallery';

?>
<div class="rtsb-product-images <?php echo esc_attr( $parent_class . $gallery_class ); ?>" data-zoom-icon="<?php echo esc_attr( $lightbox_icon ); ?>">
	<?php if ( ! empty( $controllers['sale_flash_badge'] ) ) { ?>
		<?php woocommerce_show_product_sale_flash(); ?>
	<?php } ?>
	<?php woocommerce_show_product_images(); ?>
</div>



