<?php
/**
 * Template variables:
 *
 * @var $controllers  array Widgets/Addons Settings
 * @var $product_type  array Widgets/Addons Settings
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

global $product;
if ( empty( $product ) ) {
	return;
}

$ajax_class    = ! empty( $controllers['enable_single_ajax_add_to_cart'] ) ? ' has-ajax-cart' : ' no-ajax-cart';
$wrapper_class = 'rtsb-product-add-to-cart has-cart-button quantity-' . $controllers['quantity_style'] . $controllers['visibility'] . $ajax_class;
?>
<div class="<?php echo esc_attr( $wrapper_class ); ?>" data-cart-icon="<?php echo esc_attr( $controllers['cart_icon_html'] ); ?>" data-product-type='<?php echo esc_attr( $product_type ); ?>'>
	<?php woocommerce_template_single_add_to_cart(); ?>
</div>
