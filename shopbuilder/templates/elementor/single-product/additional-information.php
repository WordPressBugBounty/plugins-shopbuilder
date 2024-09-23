<?php
/**
 * Template variables:
 *
 * @var $controllers  array Widgets/Addons Settings
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}
use RadiusTheme\SB\Helpers\Fns;

global $product;
if ( empty( $product ) ) {
	return;
}

ob_start();
do_action( 'woocommerce_product_additional_information', $product );
$content = ob_get_clean();

if ( empty( $content ) ) {
	 return;
}

?>
<div class="rtsb-product-additional-information">
	<?php $heading = apply_filters( 'woocommerce_product_additional_information_heading', __( 'Additional information', 'shopbuilder' ) ); ?>
	<?php if ( $heading ) : ?>
		<h2><?php echo esc_html( $heading ); ?></h2>
	<?php endif; ?>
	<?php Fns::print_html( $content, true ); ?>
</div>



