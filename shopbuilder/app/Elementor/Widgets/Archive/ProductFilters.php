<?php

namespace RadiusTheme\SB\Elementor\Widgets\Archive;

// Do not allow directly accessing this file.
use RadiusTheme\SB\Abstracts\ElementorWidgetBase;
use RadiusTheme\SB\Elementor\Helper\RenderHelpers;
use RadiusTheme\SB\Elementor\Widgets\Controls\ProductFilterSettings;
use RadiusTheme\SB\Helpers\Fns;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}
class ProductFilters extends ElementorWidgetBase {
	/**
	 * Construct function
	 *
	 * @param array $data default array.
	 * @param mixed $args default arg.
	 */
	public function __construct( $data = [], $args = null ) {
		$this->rtsb_name = esc_html__( 'Product Filters', 'shopbuilder' );
		$this->rtsb_base = 'rtsb-product-filters';
		parent::__construct( $data, $args );
	}
	/**
	 * Keywords
	 *
	 * @return array
	 */
	public function get_keywords() {
		return [ 'filters', 'product filters', 'archive filters', 'shop filters' ] + parent::get_keywords();
	}
	/**
	 * Widget Field
	 *
	 * @return array
	 */
	public function widget_fields() {
		return ProductFilterSettings::widget_fields( $this );
	}
	/**
	 * Render Function
	 *
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$this->theme_support();

		$layout    = 'products-filters';
		$templates = [
			'layout'   => 'elementor/archive/default-product-filters/' . $layout,
			'checkbox' => 'elementor/archive/default-product-filters/checkbox',
			'radio'    => 'elementor/archive/default-product-filters/radio',
			'color'    => 'elementor/archive/default-product-filters/color',
			'button'   => 'elementor/archive/default-product-filters/button',
			'image'    => 'elementor/archive/default-product-filters/image',
			'rating'   => 'elementor/archive/default-product-filters/rating',
			'price'    => 'elementor/archive/default-product-filters/price',
		];

		Fns::print_html( RenderHelpers::filters_view( $templates, $settings ), true );
		$this->edit_mode_default_filter_script();
		$this->theme_support( 'render_reset' );
	}
	private function edit_mode_default_filter_script() {
		if ( ! $this->is_edit_mode() ) {
			return;
		}
		?>
		<script>
			setTimeout(function() {
				rtsbProductDefaultFilterInit();
			}, 1000);
		</script>

		<?php
	}
}
