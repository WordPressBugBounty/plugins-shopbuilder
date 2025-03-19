<?php
/**
 * Template variables:
 *
 * @var $controllers  array Widgets/Addons Settings
 */

use RadiusTheme\SB\Helpers\Fns;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

$ajax_filter = Fns::product_filters_has_ajax( apply_filters( 'rtsb/builder/set/current/page/type', '' ) );
$class       = rtsb()->has_pro() && $ajax_filter ? ' has-ajax-filter' : ' no-ajax-filter';
?>
<div class="rtsb-archive-catalog-ordering<?php echo esc_attr( $class ); ?>">
	<?php

	$show_default_orderby = 'menu_order' === apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', 'menu_order' ) );
    // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
	$catalog_orderby_options = apply_filters(
		'woocommerce_catalog_orderby',
		[
			'menu_order' => __( 'Default sorting', 'woocommerce' ),
			'popularity' => __( 'Sort by popularity', 'woocommerce' ),
			'rating'     => __( 'Sort by average rating', 'woocommerce' ),
			'date'       => __( 'Sort by latest', 'woocommerce' ),
			'price'      => __( 'Sort by price: low to high', 'woocommerce' ),
			'price-desc' => __( 'Sort by price: high to low', 'woocommerce' ),
		]
	);
	$default_orderby = wc_get_loop_prop( 'is_search' ) ? 'relevance' : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', '' ) );
    // phpcs:disable WordPress.Security.NonceVerification.Recommended
	$orderby = isset( $_GET['orderby'] ) ? wc_clean( wp_unslash( $_GET['orderby'] ) ) : $default_orderby;
    // phpcs:enable WordPress.Security.NonceVerification.Recommended
	if ( wc_get_loop_prop( 'is_search' ) ) {
		$catalog_orderby_options = array_merge( [ 'relevance' => __( 'Relevance', 'woocommerce' ) ], $catalog_orderby_options );

		unset( $catalog_orderby_options['menu_order'] );
	}

	if ( ! $show_default_orderby ) {
		unset( $catalog_orderby_options['menu_order'] );
	}

	if ( ! wc_review_ratings_enabled() ) {
		unset( $catalog_orderby_options['rating'] );
	}

	if ( ! array_key_exists( $orderby, $catalog_orderby_options ) ) {
		$orderby = current( array_keys( $catalog_orderby_options ) );
	}

	wc_get_template(
		'loop/orderby.php',
		[
			'catalog_orderby_options' => $catalog_orderby_options,
			'orderby'                 => $orderby,
			'show_default_orderby'    => $show_default_orderby,
			'use_label'               => false,
		]
	);
	?>
</div>
