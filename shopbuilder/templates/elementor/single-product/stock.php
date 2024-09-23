<?php
/**
 * Template variables:
 *
 * @var $controllers  array settings as array
 * @var $is_builder  bool
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

global $product;
if ( empty( $product ) ) {
	return;
}
?>
<div class="rtsb-product-stock">
	<?php echo wc_get_stock_html( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>
