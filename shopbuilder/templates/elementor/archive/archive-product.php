<?php
/**
 * Template variables:
 *
 * @var $controllers   array Widgets/Addons Settings
 * @var $archive_data  array Archive Settings
 * @var $parent_class  string Settings
 * @var $icon          string Settings
 */

use RadiusTheme\SB\Helpers\Fns;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( Fns::product_has_applied_filters( 'shop' ) || Fns::product_has_applied_filters( 'archive' ) || 'rtsb_builder' === get_post_type( get_the_ID() ) ) {
	echo '<div class="rtsb-active-filters-wrapper"></div>';
}

$parent_class .= ! empty( $controllers['show_pagination'] ) ? ' rtsb-has-pagination' : ' rtsb-no-pagination';
?>
<div class="rtsb-product-catalog has-cart-button <?php echo esc_attr( $parent_class ); ?>" data-cart-icon="<?php echo esc_attr( $icon ); ?>" <?php Fns::print_html( $archive_data, true ); ?>>
	<?php
	// column_per_row.
	if ( woocommerce_product_loop() ) {
		do_action( 'woocommerce_before_shop_loop' );
		woocommerce_product_loop_start();
		if ( wc_get_loop_prop( 'total' ) ) {
			while ( have_posts() ) {
				the_post();
				/**
				 * Hook: woocommerce_shop_loop.
				 */
				do_action( 'woocommerce_shop_loop' );
				wc_get_template_part( 'content', 'product' );
			}
		}
		woocommerce_product_loop_end();
		do_action( 'woocommerce_after_shop_loop' );
	} else {
		/**
		 * Hook: woocommerce_no_products_found.
		 *
		 * @hooked wc_no_products_found - 10
		 */
		do_action( 'woocommerce_no_products_found' );
	}
	?>
</div>
