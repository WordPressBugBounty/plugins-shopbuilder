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

global $product;
if ( empty( $product ) ) {
	return;
}

?>
<div class="rtsb-product-meta rtsb-is-sku rtsb-is-cat rtsb-is-tag <?php echo esc_attr( $classes ); ?>">
	<?php woocommerce_template_single_meta(); ?>
</div>
