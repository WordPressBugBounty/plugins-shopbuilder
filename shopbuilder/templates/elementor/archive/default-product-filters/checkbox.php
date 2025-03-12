<?php
/**
 * Template variables:
 *
 * @var $tax_type          string        Taxonomy type.
 * @var $attr_type         string        Attribute type.
 * @var $input             string        Input type.
 * @var $title             array         Filter title.
 * @var $template          string        Filter template.
 * @var $raw_settings      array         Widgets/Addons Settings
 * @var $repeater_settings array         Repeater Settings
 */

use RadiusTheme\SB\Elementor\Helper\RenderHelpers;
use RadiusTheme\SB\Helpers\Fns;


// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}
$tax_type     = RenderHelpers::get_product_filters_tax_type( $tax_type, $attr_type );
$sale_options = [
	'onsale'  => ! empty( $repeater_settings['onsale_title'] ) ? esc_html( $repeater_settings['onsale_title'] ) : esc_html__( 'On Sale Products', 'shopbuilder' ),
	'regular' => ! empty( $repeater_settings['regular_title'] ) ? esc_html( $repeater_settings['regular_title'] ) : esc_html__( 'Regular Products', 'shopbuilder' ),
];


$tax_data     = RenderHelpers::get_products( $tax_type );
$is_attribute = strpos( $tax_type, 'pa_' ) !== false;

if ( is_wp_error( $tax_data ) && 'sale_filter' !== $tax_type ) {
	Fns::print_html( RenderHelpers::filter_error_message( $title, 'rtsb-categories' ) );
	return;
}

if ( $is_attribute && empty( RenderHelpers::get_product_filters_attribute_terms( $tax_type ) ) ) {
	return;
}
$data            = [
	'taxonomy'         => $is_attribute & ! $repeater_settings['show_empty'] ? RenderHelpers::get_product_filters_attribute_terms( $tax_type ) : $tax_data,
	'is_attribute'     => $is_attribute,
	'show_empty_terms' => $repeater_settings['show_empty'],
	'show_child'       => ! empty( $repeater_settings['include_child_cats'] ),
];
$additional_data = [
	'taxonomy' => $tax_type,
	'input'    => $input,
];
$additional_args = [
	'total_count' => 'sale_filter' === $tax_type ? 2 : count( $is_attribute ? RenderHelpers::get_product_filters_attribute_terms( $tax_type ) : $tax_data ),
	'tax_data'    => $tax_data,
];
?>
<div class="rtsb-product-default-filters rtsb-categories <?php echo 'sale_filter' === $tax_type ? 'rtsb-sale-filter' : ''; ?>">
	<?php
	/**
	 * Filter title.
	 */
	Fns::print_html( RenderHelpers::get_default_filter_title( $title ) );

	/**
	 * Filter content.
	 */
	echo '<div class="default-filter-content">';

	if ( ! empty( $tax_data ) && ! is_wp_error( $tax_data ) && 'sale_filter' !== $tax_type ) {
		Fns::print_html( RenderHelpers::get_product_default_filter_list_html( $data, $input, $additional_data ), true );
	} else {
		if ( 'sale_filter' === $tax_type ) {
			 Fns::print_html( RenderHelpers::get_default_filter_html( $tax_type, $sale_options, $additional_data ), true );
		} else {
			echo '<p class="no-filter">' . esc_html__( 'Nothing found.', 'shopbuilder' ) . '</p>';
		}
	}

	echo '</div>';
	?>
</div>
