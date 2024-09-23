<?php
/**
 * Main ProductDescription class.
 *
 * @package RadiusTheme\SB
 */

namespace RadiusTheme\SB\Elementor\Widgets\Single;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Abstracts\ElementorWidgetBase;
use RadiusTheme\SB\Elementor\Helper\RenderHelpers;
use RadiusTheme\SB\Elementor\Widgets\Controls\StyleFields;
use RadiusTheme\SB\Elementor\Widgets\Controls\SettingsFields;
use RadiusTheme\SB\Elementor\Widgets\Controls\ProductFlashSale;

/**
 * Product Description class
 */
class ProductBadges extends ElementorWidgetBase {
	/**
	 * Construct function
	 *
	 * @param array $data default array.
	 * @param mixed $args default arg.
	 */
	public function __construct( $data = [], $args = null ) {
		$this->rtsb_name = esc_html__( 'Product Badges', 'shopbuilder' );
		$this->rtsb_base = 'rtsb-product-onsale';
		parent::__construct( $data, $args );
	}
	/**
	 * Widget Field
	 *
	 * @return array
	 */
	public function widget_fields() {

		$fields = array_merge(
			$this->badge(),
			$this->flash_sale_style_settings(),
			$this->badge_style()
		);

		return $fields;
	}

	public function flash_sale_style_settings(): array {
		$style_settings = ProductFlashSale::flash_sale( $this );

		$style_settings['flash_sale_section_start']['condition'] = [
			// 'custom_badge_preset' => 'preset-default',
			'enable_badges_module!' => 'yes',
		];

		$style_settings['flash_sale_bg_color']['condition'] = [
			'custom_badge_preset' => [ 'preset-default', 'preset1', 'preset2' ],
		];

		$new = [
			'flash_sale_border_color' => [
				'label'     => esc_html__( 'Border Color', 'shopbuilder' ),
				'type'      => 'color',
				'selectors' => [
					$this->selectors['flash_sale_border_color'] => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'custom_badge_preset' => [ 'preset3', 'preset4' ],
				],
			],
		];

		$fields = Fns::insert_controls( 'flash_sale_bg_color', $style_settings, $new, true );
		return $fields;
	}
	public function badge(): array {
		$badge_settings = SettingsFields::badges( $this );

		unset( $badge_settings['badges_section']['condition'] );
		unset( $badge_settings['badges_section']['conditions'] );
		unset( $badge_settings['enable_badges_module']['condition'] );

		unset( $badge_settings['sale_badges_text']['condition']['layout!'] );
		unset( $badge_settings['stock_badges_text']['condition']['layout!'] );
		unset( $badge_settings['custom_badge_text']['condition']['layout!'] );

		$badge_settings['sale_badges_type']['condition'] = [
			'enable_badges_module!' => 'yes',
			'custom_badge_preset!'  => 'preset-default',
		];

		$badge_settings['sale_badges_text']['condition']['custom_badge_preset!']  = 'preset-default';
		$badge_settings['stock_badges_text']['condition']['custom_badge_preset!'] = 'preset-default';
		$badge_settings['custom_badge_text']['condition']['custom_badge_preset!'] = 'preset-default';

		$badge_settings['custom_badge_preset']['options'] = array_merge(
			[
				'preset-default' => [
					'title' => esc_html__( 'Preset Default', 'shopbuilder' ),
					'url'   => esc_url( rtsb()->get_assets_uri( 'images/layout/badge-preset-default.png' ) ),
				],
			],
			$badge_settings['custom_badge_preset']['options']
		);

		$badge_settings['badges_section']['tab'] = 'layout';

		$badge_settings['custom_badge_preset']['default'] = 'preset-default';
		if ( ! empty( $badge_settings['badges_module_alignment'] ) ) {
			 $badge_settings['badges_module_alignment']['selectors'] = [
				 $this->selectors['badges_module']['alignment'] => 'align-items: {{VALUE}} !important; justify-content: {{VALUE}} !important;',
			 ];
		}
		return $badge_settings;
	}

	public function badge_style(): array {
		$style_settings = StyleFields::product_badges( $this );

		unset( $style_settings['badges_style_section']['condition']['layout!'] );
		unset( $style_settings['badges_style_section']['condition']['show_badges'] );

		unset( $style_settings['badges_position'] );
		unset( $style_settings['badge_x_position'] );
		unset( $style_settings['badge_y_position'] );
		unset( $style_settings['badges_alignment'] );

		// $style_settings['badges_style_section']['condition']['custom_badge_preset!'] = 'preset-default';
		 $style_settings['badges_style_section']['condition']['enable_badges_module'] = 'yes';

		return $style_settings;
	}
	/**
	 * Set Widget Keyword.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return [ 'On Sale', 'sale flash' ] + parent::get_keywords();
	}
	/**
	 * Set icons function
	 *
	 * @param [type] $availability status.
	 * @param [type] $obj  WC_Product class object.
	 * @return string
	 */

	/**
	 * Render Function
	 *
	 * @return void
	 */
	protected function render() {
		global $product, $post;
		$_product    = $product;
		$_post       = $post;
		$product     = Fns::get_product();
		$controllers = $this->get_settings_for_display();
		$this->theme_support();

		$data = [
			'template'    => 'elementor/single-product/product-badges',
			'controllers' => $controllers,
		];

		$badges_visibility                 = rtsb()->has_pro() && Fns::is_guest_feature_disabled( 'hide_badges', '' );
		$data['controllers']['visibility'] = $badges_visibility ? ' rtsb-not-visible' : ' rtsb-is-visible';

		Fns::load_template( $data['template'], $data );

		$this->theme_support( 'render_reset' );
		$product = $_product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$post    = $_post;
	}
}
