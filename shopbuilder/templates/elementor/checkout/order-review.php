<?php
/**
 * Template variables:
 *
 * @var $controllers  array Widgets/Addons Settings
 * @var $checkout  Object  WC()->checkout()
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

$wrapper_classes = ! empty( $controllers['table_horizontal_scroll_on_mobile'] ) ? ' rtsb-table-horizontal-scroll-on-mobile' : '';
?>
<div class="rtsb-checkout-order-review<?php echo esc_attr( $wrapper_classes ); ?>">
	<?php
	if ( ! empty( $controllers['show_title'] ) ) {
		?>
		<h3 id="order_review_heading"><?php esc_html_e( 'Your order', 'shopbuilder' ); ?></h3>
		<?php
	}
	?>

	<?php // do_action( 'woocommerce_checkout_before_order_review' ); ?>

	<div id="order_review" class="woocommerce-checkout-review-order">
		<?php woocommerce_order_review(); ?>
	</div>

	<?php // do_action( 'woocommerce_checkout_after_order_review' ); ?>

</div>
