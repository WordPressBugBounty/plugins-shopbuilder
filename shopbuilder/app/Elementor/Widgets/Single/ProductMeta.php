<?php
/**
 * Main ProductMeta class.
 *
 * @package RadiusTheme\SB
 */

namespace RadiusTheme\SB\Elementor\Widgets\Single;

use Elementor\Controls_Manager;
use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Abstracts\ElementorWidgetBase;
use RadiusTheme\SB\Elementor\Widgets\Controls\MetaSettings;
use RadiusTheme\SB\Elementor\Widgets\Controls\ProductTaxSettings;
use WC_Product;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Product Meta class
 */
class ProductMeta extends ElementorWidgetBase {
	/**
	 * Construct function
	 *
	 * @param array $data default array.
	 * @param mixed $args default arg.
	 */
	public function __construct( $data = [], $args = null ) {
		$this->rtsb_name = esc_html__( 'Product Meta', 'shopbuilder' );
		$this->rtsb_base = 'rtsb-product-meta';
		parent::__construct( $data, $args );
	}
	/**
	 * Widget Field
	 *
	 * @return array
	 */
	public function widget_fields() {
		$fields                              = MetaSettings::widget_fields( $this );
		$tax_settings                        = ProductTaxSettings::widget_fields( $this );
		$tax_settings['meta_content']['tab'] = 'style';
		$fields                              = $fields + $tax_settings;
		unset( $fields['show_label'] );
		unset( $fields['meta_label_heading']['condition'] );
		unset( $fields['label_typo']['condition'] );
		unset( $fields['meta_label_color']['condition'] );
		return $fields;
	}
	/**
	 * Set Widget Keyword.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return [ 'SKU', 'Category', 'Tag' ] + parent::get_keywords();
	}
	/**
	 * Widget Field.
	 *
	 * @param array $controllers Control.
	 *
	 * @return void
	 */
	public function apply_hooks() {
		add_action( 'woocommerce_product_meta_end', [ $this, 'show_brand' ], 99 );
	}
	/**
	 * Displays brand.
	 */
	public function show_brand() {
		$product = Fns::get_product();
		if ( ! $product instanceof WC_Product ) {
			$product = wc_get_product();
		}
		$terms       = get_the_terms( $product->get_id(), 'product_brand' );
		$brand_count = is_array( $terms ) ? count( $terms ) : 0;

		$taxonomy = get_taxonomy( 'product_brand' );
		$labels   = $taxonomy->labels;

		/* translators: %s - Label name */
		echo wc_get_brands( $product->get_id(), ', ', ' <span class="posted_in">' . sprintf( _n( '%s: ', '%s: ', $brand_count, 'shopbuilder' ), $labels->singular_name, $labels->name ), '</span>' ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
	/**
	 * Render Function
	 *
	 * @return void
	 */
	protected function render() {
		global $product;
		$_product    = $product;
		$product     = Fns::get_product();
		$controllers = $this->get_settings_for_display();
		if ( $this->is_builder_mode() ) {
			$this->apply_hooks();
		}
		$this->theme_support();
		// show_sku.
		$classes = '';
		if ( ! empty( $controllers['show_sku'] ) ) {
			$classes .= 'rtsb-show-sku ';
		}
		if ( ! empty( $controllers['show_cat'] ) ) {
			$classes .= 'rtsb-show-cat ';
		}
		if ( ! empty( $controllers['show_tag'] ) ) {
			$classes .= 'rtsb-show-tag ';
		}
		if ( ! empty( $controllers['show_brand'] ) ) {
			$classes .= 'rtsb-show-brand ';
		}
		$data = [
			'template'    => 'elementor/single-product/meta',
			'controllers' => $controllers,
			'classes'     => $classes,
		];
		Fns::load_template( $data['template'], $data );
		$this->theme_support( 'render_reset' );
		$product = $_product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	}
}
