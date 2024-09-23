<?php
/**
 * Elementor Style Fields Class.
 *
 * This class contains all the common fields for Style tab.
 *
 * @package RadiusTheme\SB
 */

namespace RadiusTheme\SB\Elementor\Widgets\Controls;

use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Elementor\Helper\ControlHelper;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Elementor Style Fields Class.
 */
class StyleFields {
	/**
	 * Tab name.
	 *
	 * @access private
	 * @static
	 *
	 * @var string
	 */
	private static $tab = 'style';

	/**
	 * Color Scheme Section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return array
	 */
	public static function color_scheme( $obj ): array {
		$css_selectors = $obj->selectors['color_scheme'];

		$fields['color_scheme_section'] = $obj->start_section( esc_html__( 'Color Scheme', 'shopbuilder' ), self::$tab );

		$fields['primary_color'] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Primary Color', 'shopbuilder' ),
			'default'   => '#0066ff',
			'selectors' => [
				$css_selectors['primary_color'] => '--rtsb-color-primary: {{VALUE}}',
			],
		];

		$fields['secondary_color'] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Secondary Color', 'shopbuilder' ),
			'default'   => '#111',
			'selectors' => [
				$css_selectors['secondary_color'] => '--rtsb-color-secondary: {{VALUE}}',
			],
		];

		$fields['loader_color'] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Pre-Loader Color', 'shopbuilder' ),
			'default'   => '#111',
			'selectors' => [
				$css_selectors['secondary_color'] => '--rtsb-color-loader: {{VALUE}}',
			],
		];

		$fields['color_scheme_section_end'] = $obj->end_section();

		return apply_filters( 'rtsb/elements/elementor/color_style_control', $fields, $obj );
	}

	/**
	 * Layout Design Section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return array
	 */
	public static function layout_design( $obj ): array {
		$css_selectors = $obj->selectors['advanced'];

		$fields['layout_design_section'] = $obj->start_section( esc_html__( 'Layout Design', 'shopbuilder' ), self::$tab );

		$fields['layout_design_note'] = $obj->el_heading( esc_html__( 'Layout', 'shopbuilder' ) );

		$fields['grid_gap'] = [
			'type'        => 'slider',
			'mode'        => 'responsive',
			'label'       => esc_html__( 'Grid Gap / Spacing (px)', 'shopbuilder' ),
			'size_units'  => [ 'px' ],
			'range'       => [
				'px' => [
					'min'  => 0,
					'max'  => 100,
					'step' => 1,
				],
			],
			'default'     => [
				'unit' => 'px',
				'size' => 30,
			],
			'description' => esc_html__( 'Please select the grid gap in px.', 'shopbuilder' ),
			'selectors'   => [
				$obj->selectors['columns']['grid_gap']['padding'] => 'padding-left: calc({{SIZE}}{{UNIT}} / 2); padding-right: calc({{SIZE}}{{UNIT}} / 2);',
				$obj->selectors['columns']['grid_gap']['margin'] => 'margin-left: calc(-{{SIZE}}{{UNIT}} / 2); margin-right: calc(-{{SIZE}}{{UNIT}} / 2);',
				$obj->selectors['columns']['grid_gap']['slider_layout3'] => '--rtsb-slider-layout3-spacing: {{SIZE}}{{UNIT}};',
				$obj->selectors['columns']['grid_gap']['slider_layout9'] => '--rtsb-slider-layout9-spacing: {{SIZE}}{{UNIT}};',
				$css_selectors['element_margin'] => 'margin-bottom: {{SIZE}}{{UNIT}};',
			],
		];

		$fields['grid_alignment'] = [
			'mode'      => 'responsive',
			'type'      => 'choose',
			'label'     => esc_html__( 'Alignment', 'shopbuilder' ),
			'options'   => [
				'left'   => [
					'title' => esc_html__( 'Left', 'shopbuilder' ),
					'icon'  => 'eicon-text-align-left',
				],
				'center' => [
					'title' => esc_html__( 'Center', 'shopbuilder' ),
					'icon'  => 'eicon-text-align-center',
				],
				'right'  => [
					'title' => esc_html__( 'Right', 'shopbuilder' ),
					'icon'  => 'eicon-text-align-right',
				],
			],
			'selectors' => [
				$obj->selectors['columns']['grid_alignment']['text_align']      => 'text-align: {{VALUE}};',
				$obj->selectors['columns']['grid_alignment']['justify_content'] => 'justify-content: {{VALUE}};',
			],
		];

		$fields['wrapper_color_note'] = $obj->el_heading( esc_html__( 'Colors & Shadow', 'shopbuilder' ), 'before' );

		$fields['advanced_color_tabs'] = $obj->start_tab_group();
		$fields['advanced_color_tab']  = $obj->start_tab( esc_html__( 'Normal', 'shopbuilder' ) );

		$fields['content_bg_color'] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Content Background', 'shopbuilder' ),
			'selectors' => [ $css_selectors['content_bg_color'] => 'background-color: {{VALUE}};' ],
		];

		$fields['element_bg_color'] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Element Background', 'shopbuilder' ),
			'selectors' => [ $css_selectors['element_bg_color'] => 'background-color: {{VALUE}};' ],
		];

		$fields['rtsb_el_element_shadow'] = [
			'type'     => 'box-shadow',
			'mode'     => 'group',
			'label'    => esc_html__( 'Element Drop Shadow', 'shopbuilder' ),
			'selector' => $css_selectors['rtsb_el_element_shadow'],
		];

		$fields['advanced_color_tab_end']   = $obj->end_tab();
		$fields['advanced_hover_color_tab'] = $obj->start_tab( esc_html__( 'Hover', 'shopbuilder' ) );

		$fields['content_hover_bg_color'] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Content Hover Background', 'shopbuilder' ),
			'selectors' => [ $css_selectors['content_hover_bg_color'] => 'background-color: {{VALUE}};' ],
		];

		$fields['element_hover_bg_color'] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Element Hover Background', 'shopbuilder' ),
			'selectors' => [ $css_selectors['element_hover_bg_color'] => 'background-color: {{VALUE}};' ],
		];

		$fields['rtsb_el_element_hover_shadow'] = [
			'type'     => 'box-shadow',
			'mode'     => 'group',
			'label'    => esc_html__( 'Element Hover Drop Shadow', 'shopbuilder' ),
			'selector' => $css_selectors['rtsb_el_element_hover_shadow'],
		];

		$fields['advanced_hover_color_tab_end'] = $obj->end_tab();
		$fields['advanced_color_tabs_end']      = $obj->end_tab_group();

		$fields['wrapper_note'] = $obj->el_heading( esc_html__( 'Border', 'shopbuilder' ), 'before' );

		$fields['rtsb_el_wrapper_border'] = [
			'mode'           => 'group',
			'type'           => 'border',
			'label'          => esc_html__( 'Wrapper Border', 'shopbuilder' ),
			'fields_options' => [
				'border' => [
					'label'       => esc_html__( 'Wrapper Border Type', 'shopbuilder' ),
					'label_block' => true,
				],
				'color'  => [
					'label' => esc_html__( 'Border Color', 'shopbuilder' ),
				],
			],
			'selector'       => $css_selectors['rtsb_el_wrapper_border'],
		];

		$fields['wrapper_border_radius'] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'label'      => esc_html__( 'Wrapper Border Radius', 'shopbuilder' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [ $css_selectors['wrapper_border_radius'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;' ],
		];

		$fields['rtsb_el_element_border'] = [
			'mode'           => 'group',
			'type'           => 'border',
			'label'          => esc_html__( 'Element Border', 'shopbuilder' ),
			'fields_options' => [
				'border' => [
					'label'       => esc_html__( 'Element Border Type', 'shopbuilder' ),
					'label_block' => true,
				],
				'color'  => [
					'label' => esc_html__( 'Border Color', 'shopbuilder' ),
				],
			],
			'separator'      => 'before',
			'selector'       => $css_selectors['rtsb_el_element_border'],
		];

		$fields['element_border_hover_color'] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Hover Border Color', 'shopbuilder' ),
			'condition' => [ 'rtsb_el_element_border_border!' => [ '' ] ],
			'selectors' => [ $css_selectors['element_border_hover_color'] => 'border-color: {{VALUE}};' ],
		];

		$fields['element_border_radius'] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'label'      => esc_html__( 'Element Border Radius', 'shopbuilder' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [ $css_selectors['element_border_radius'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;' ],
		];

		$fields['rtsb_el_content_border'] = [
			'mode'           => 'group',
			'type'           => 'border',
			'label'          => esc_html__( 'Content Border', 'shopbuilder' ),
			'fields_options' => [
				'border' => [
					'label'       => esc_html__( 'Content Border Type', 'shopbuilder' ),
					'label_block' => true,
				],
				'color'  => [
					'label' => esc_html__( 'Border Color', 'shopbuilder' ),
				],
			],
			'separator'      => 'before',
			'selector'       => $css_selectors['rtsb_el_content_border'],
		];

		$fields['content_border_hover_color'] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Hover Border Color', 'shopbuilder' ),
			'condition' => [ 'rtsb_el_content_border_border!' => [ '' ] ],
			'selectors' => [ $css_selectors['content_border_hover_color'] => 'border-color: {{VALUE}};' ],
		];

		$fields['content_border_radius'] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'label'      => esc_html__( 'Content Border Radius', 'shopbuilder' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [ $css_selectors['content_border_radius'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		];

		$fields['advanced_spacing_note'] = $obj->el_heading( esc_html__( 'Spacing', 'shopbuilder' ), 'before' );

		$fields['wrapper_padding'] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'label'      => esc_html__( 'Wrapper Padding', 'shopbuilder' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [ $css_selectors['wrapper_padding'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ],
		];

		$fields['element_padding'] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'label'      => esc_html__( 'Element Padding', 'shopbuilder' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [ $css_selectors['element_padding'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ],
		];

		$fields['content_padding'] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'label'      => esc_html__( 'Content Padding', 'shopbuilder' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [ $css_selectors['content_padding'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ],
		];

		$fields['layout_design_section_end'] = $obj->end_section();

		return apply_filters( 'rtsb/elements/elementor/color_style_control', $fields, $obj );
	}

	/**
	 * Product Title Section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return array
	 */
	public static function product_title( $obj ): array {
		$css_selectors = $obj->selectors['product_title'];
		$title         = esc_html__( 'Product Title', 'shopbuilder' );
		$condition     = [
			'show_title' => [ 'yes' ],
		];
		$selectors     = self::title_selectors( $css_selectors );

		$fields = ControlHelper::general_elementor_style( 'product_title', $title, $obj, $condition, $selectors );

		return apply_filters( 'rtsb/elements/elementor/title_style_control', $fields, $obj );
	}

	/**
	 * Section Title Section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return array
	 */
	public static function section_title( $obj ): array {
		$css_selectors = $obj->selectors['section_title'];
		$title         = esc_html__( 'Section Title', 'shopbuilder' );
		$condition     = [
			'show_section_title' => [ 'yes' ],
		];
		$selectors     = self::title_selectors( $css_selectors );

		$fields = ControlHelper::general_elementor_style( 'section_title', $title, $obj, $condition, $selectors );

		return apply_filters( 'rtsb/elements/elementor/section_title_style_control', $fields, $obj );
	}

	/**
	 * Category Title Section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return array
	 */
	public static function cat_title( $obj ): array {
		$css_selectors = $obj->selectors['cat_title'];
		$title         = esc_html__( 'Category Title', 'shopbuilder' );
		$condition     = [
			'show_title' => [ 'yes' ],
		];
		$selectors     = self::title_selectors( $css_selectors );

		$fields = ControlHelper::general_elementor_style( 'category_title', $title, $obj, $condition, $selectors );

		return apply_filters( 'rtsb/elements/elementor/cat_title_style_control', $fields, $obj );
	}

	/**
	 * Short Description Section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return array
	 */
	public static function short_description( $obj ): array {
		$css_selectors = $obj->selectors['short_description'];
		$title         = esc_html__( 'Short Description', 'shopbuilder' );
		$condition     = [
			'show_short_desc' => [ 'yes' ],
		];
		$selectors     = self::excerpt_selectors( $css_selectors );

		$fields = ControlHelper::general_elementor_style( 'short_description', $title, $obj, $condition, $selectors );

		unset(
			$fields['short_description_color_tabs'],
			$fields['short_description_color_tab'],
			$fields['short_description_color_tab_end'],
			$fields['short_description_hover_color_tab'],
			$fields['short_description_hover_color'],
			$fields['short_description_hover_bg_color'],
			$fields['short_description_hover_color_tab_end'],
			$fields['short_description_color_tabs_end'],
			$fields['short_description_border_hover_color']
		);

		return apply_filters( 'rtsb/elements/elementor/excerpt_style_control', $fields, $obj );
	}

	/**
	 * Category Description Section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return array
	 */
	public static function cat_description( $obj ): array {
		$css_selectors = $obj->selectors['cat_description'];
		$title         = esc_html__( 'Category Description', 'shopbuilder' );
		$condition     = [
			'show_short_desc' => [ 'yes' ],
		];
		$selectors     = self::excerpt_selectors( $css_selectors );

		$fields = ControlHelper::general_elementor_style( 'category_description', $title, $obj, $condition, $selectors );

		unset(
			$fields['category_description_color_tabs'],
			$fields['category_description_color_tab'],
			$fields['category_description_color_tab_end'],
			$fields['category_description_hover_color_tab'],
			$fields['category_description_hover_color'],
			$fields['category_description_hover_bg_color'],
			$fields['category_description_hover_color_tab_end'],
			$fields['category_description_color_tabs_end'],
			$fields['category_description_border_hover_color']
		);

		return apply_filters( 'rtsb/elements/elementor/cat_excerpt_style_control', $fields, $obj );
	}

	/**
	 * Count Section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return array
	 */
	public static function count( $obj ): array {
		$css_selectors = $obj->selectors['count'];
		$title         = esc_html__( 'Product Count', 'shopbuilder' );
		$condition     = [
			'show_count' => [ 'yes' ],
		];
		$selectors     = [
			'typography'         => $css_selectors['typography'],
			'alignment'          => [ $css_selectors['alignment'] => 'text-align: {{VALUE}};' ],
			'color'              => [ $css_selectors['color'] => 'color: {{VALUE}};' ],
			'bg_color'           => [ $css_selectors['bg_color'] => 'background-color: {{VALUE}};' ],
			'hover_color'        => [ $css_selectors['hover_color'] => 'color: {{VALUE}};' ],
			'hover_bg_color'     => [ $css_selectors['hover_bg_color'] => 'background-color: {{VALUE}};' ],
			'border'             => $css_selectors['border'],
			'border_hover_color' => [ $css_selectors['border_hover_color'] => 'border-color: {{VALUE}};' ],
			'border_radius'      => [ $css_selectors['border_radius'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			'padding'            => [ $css_selectors['padding'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			'margin'             => [ $css_selectors['margin'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		];

		$fields = ControlHelper::general_elementor_style( 'product_count', $title, $obj, $condition, $selectors );

		$extra_controls = [];

		$extra_controls['product_count_radius'] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'label'      => esc_html__( 'Border Radius', 'shopbuilder' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => $selectors['border_radius'],
		];

		$fields = Fns::insert_controls( 'product_count_border_hover_color', $fields, $extra_controls, true );

		return apply_filters( 'rtsb/elements/elementor/cat_count_style_control', $fields, $obj );
	}

	/**
	 * Product Price Section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return array
	 */
	public static function product_price( $obj ): array {
		$css_selectors = $obj->selectors['product_price'];
		$title         = esc_html__( 'Product Price', 'shopbuilder' );
		$condition     = [
			'show_price' => [ 'yes' ],
		];
		$selectors     = [
			'typography'            => $css_selectors['typography'],
			'sale_typography'       => $css_selectors['sale_typography'],
			'alignment'             => [ $css_selectors['alignment'] => 'text-align: {{VALUE}};' ],
			'color'                 => [ $css_selectors['color'] => 'color: {{VALUE}};' ],
			'regular_color'         => [ $css_selectors['regular_color'] => 'color: {{VALUE}};' ],
			'crossed_regular_color' => [ $css_selectors['crossed_regular_color'] => 'color: {{VALUE}};' ],
			'border'                => $css_selectors['border'],
			'border_hover_color'    => [ $css_selectors['border_hover_color'] => 'border-color: {{VALUE}};' ],
			'padding'               => [ $css_selectors['padding'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			'margin'                => [ $css_selectors['margin'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		];

		$fields = ControlHelper::general_elementor_style( 'product_price', $title, $obj, $condition, $selectors );

		unset(
			$fields['product_price_color_tabs'],
			$fields['product_price_color_tab'],
			$fields['product_price_color_tab_end'],
			$fields['product_price_hover_color_tab'],
			$fields['product_price_hover_color'],
			$fields['product_price_hover_bg_color'],
			$fields['product_price_hover_color_tab_end'],
			$fields['product_price_color_tabs_end'],
			$fields['product_price_border_hover_color']
		);

		$fields['rtsb_el_product_price_typography']['label'] = esc_html__( 'Regular Price Typography', 'shopbuilder' );
		$fields['product_price_color']['label']              = esc_html__( 'Sale Price Color', 'shopbuilder' );
		$fields['product_price_bg_color']['label']           = esc_html__( 'Regular Price Color', 'shopbuilder' );
		$fields['product_price_bg_color']['selectors']       = $selectors['regular_color'];

		$extra_controls['rtsb_el_product_sale_price_typography'] = [
			'mode'     => 'group',
			'type'     => 'typography',
			'label'    => esc_html__( 'Sale Price Typography', 'shopbuilder' ),
			'selector' => $selectors['sale_typography'],
		];

		$fields = Fns::insert_controls( 'rtsb_el_product_price_typography', $fields, $extra_controls );

		$extra_controls['crossed_regular_color'] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Crossed Out Regular Price Color', 'shopbuilder' ),
			'selectors' => ! empty( $selectors['crossed_regular_color'] ) ? $selectors['crossed_regular_color'] : [],
		];

		$fields = Fns::insert_controls( 'product_price_border_note', $fields, $extra_controls );

		return apply_filters( 'rtsb/elements/elementor/price_style_control', $fields, $obj );
	}

	/**
	 * Product Rating Section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return array
	 */
	public static function product_rating( $obj ): array {
		$css_selectors = $obj->selectors['product_rating'];
		$title         = esc_html__( 'Product Rating', 'shopbuilder' );
		$condition     = [
			'show_rating' => [ 'yes' ],
		];
		$selectors     = [
			'typography' => [ $css_selectors['typography'] => 'font-size: {{SIZE}}{{UNIT}};' ],
			'alignment'  => [ $css_selectors['alignment'] => 'justify-content: {{VALUE}};' ],
			'color'      => [ $css_selectors['color'] => 'color: {{VALUE}} !important;' ],
			'bg_color'   => [ $css_selectors['bg_color'] => 'color: {{VALUE}} !important;' ],
			'padding'    => [ $css_selectors['padding'] => 'letter-spacing: {{SIZE}}{{UNIT}};' ],
			'margin'     => [ $css_selectors['margin'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		];

		$fields = ControlHelper::general_elementor_style( 'product_rating', $title, $obj, $condition, $selectors );

		unset(
			$fields['product_rating_color_tabs'],
			$fields['product_rating_color_tab'],
			$fields['product_rating_color_tab_end'],
			$fields['product_rating_hover_color_tab'],
			$fields['product_rating_hover_color'],
			$fields['product_rating_hover_bg_color'],
			$fields['product_rating_hover_color_tab_end'],
			$fields['product_rating_color_tabs_end'],
			$fields['product_rating_border_note'],
			$fields['rtsb_el_product_rating_border'],
			$fields['product_rating_border_hover_color']
		);

		$fields['rtsb_el_product_rating_typography'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'Star Size', 'shopbuilder' ),
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 0,
					'max' => 100,
				],
			],
			// 'default'    => [
			// 'unit' => 'px',
			// 'size' => 14,
			// ],
			'selectors'  => $selectors['typography'],
		];

		$fields['product_rating_bg_color']['label'] = esc_html__( 'Star Border Color', 'shopbuilder' );

		$fields['product_rating_padding'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'Spacing', 'shopbuilder' ),
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => -10,
					'max' => 50,
				],
			],
			'default'    => [
				'unit' => 'px',
				'size' => 3,
			],
			'selectors'  => $selectors['padding'],
		];

		return apply_filters( 'rtsb/elements/elementor/rating_style_control', $fields, $obj );
	}

	/**
	 * Product Badges Section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return array
	 */
	public static function product_badges( $obj ): array {
		$css_selectors = $obj->selectors['product_badges'];
		$title         = esc_html__( 'Badges', 'shopbuilder' );
		$condition     = [
			'show_badges' => [ 'yes' ],
			'layout!'     => [ 'category-layout1', 'category-layout2' ],
		];
		$selectors     = [
			'typography'        => $css_selectors['typography'],
			'color'             => [ $css_selectors['color'] => 'color: {{VALUE}};' ],
			'bg_color'          => [ $css_selectors['bg_color'] => 'background-color: {{VALUE}};' ],
			'border_color'      => [ $css_selectors['border_color'] => 'border-color: {{VALUE}};' ],
			'border_radius'     => [ $css_selectors['border_radius'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			'padding'           => [ $css_selectors['padding'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ],
			'margin'            => [ $css_selectors['margin'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ],
			'position_x'        => [ $css_selectors['position_x'] => 'left: {{SIZE}}{{UNIT}};' ],
			'position_x_right'  => [ $css_selectors['position_x'] => 'right: {{SIZE}}{{UNIT}}; left: auto;' ],
			'position_y'        => [ $css_selectors['position_y'] => 'top: {{SIZE}}{{UNIT}};' ],
			'position_y_bottom' => [ $css_selectors['position_y'] => 'bottom: {{SIZE}}{{UNIT}}; top:auto;' ],
		];

		$fields = ControlHelper::general_elementor_style( 'badges', $title, $obj, $condition, $selectors );

		unset(
			$fields['badges_alignment'],
			$fields['badges_color_tabs'],
			$fields['badges_color_tab'],
			$fields['badges_color_tab_end'],
			$fields['badges_hover_color_tab'],
			$fields['badges_hover_color'],
			$fields['badges_hover_bg_color'],
			$fields['badges_hover_color_tab_end'],
			$fields['badges_color_tabs_end'],
			$fields['badges_border_note'],
			$fields['rtsb_el_badges_border'],
			$fields['badges_border_hover_color']
		);

		$fields['badges_alignment']['label'] = esc_html__( 'Horizontal Alignment', 'shopbuilder' );

		$extra_controls = [];

		$extra_controls['badges_alignment'] = [
			'type'        => 'select2',
			'label'       => esc_html__( 'Horizontal Alignment', 'shopbuilder' ),
			'options'     => [
				'left'   => esc_html__( 'Left', 'shopbuilder' ),
				'center' => esc_html__( 'Center', 'shopbuilder' ),
				'right'  => esc_html__( 'Right', 'shopbuilder' ),
			],
			'label_block' => true,
			'default'     => 'left',
		];

		$extra_controls['badges_position'] = [
			'type'        => 'select2',
			'label'       => esc_html__( 'Vertical Alignment', 'shopbuilder' ),
			'options'     => [
				'top'    => esc_html__( 'Top', 'shopbuilder' ),
				'middle' => esc_html__( 'Middle', 'shopbuilder' ),
				'bottom' => esc_html__( 'Bottom', 'shopbuilder' ),
			],
			'label_block' => true,
			'default'     => 'top',
		];

		$extra_controls['badge_x_position'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'Position Offset (Horizontal)', 'shopbuilder' ),
			'size_units' => [ 'px', '%' ],
			'range'      => [
				'px' => [
					'min'  => 0,
					'max'  => 300,
					'step' => 1,
				],
				'%'  => [
					'min'  => 0,
					'max'  => 100,
					'step' => 1,
				],
			],
			'condition'  => [
				'badges_alignment'  => 'left',
				'badges_alignment!' => 'center',
			],
			'selectors'  => $selectors['position_x'],
		];

		$extra_controls['badge_x_position_right'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'Position Offset (Horizontal)', 'shopbuilder' ),
			'size_units' => [ 'px', '%' ],
			'range'      => [
				'px' => [
					'min'  => 0,
					'max'  => 300,
					'step' => 1,
				],
				'%'  => [
					'min'  => 0,
					'max'  => 100,
					'step' => 1,
				],
			],
			'condition'  => [ 'badges_alignment' => 'right' ],
			'selectors'  => $selectors['position_x_right'],
		];

		$extra_controls['badge_y_position'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'Position Offset (Vertical)', 'shopbuilder' ),
			'size_units' => [ 'px', '%' ],
			'range'      => [
				'px' => [
					'min'  => 0,
					'max'  => 300,
					'step' => 1,
				],
				'%'  => [
					'min'  => 0,
					'max'  => 100,
					'step' => 1,
				],
			],
			'condition'  => [
				'badges_position'  => 'top',
				'badges_position!' => 'center',
			],
			'selectors'  => $selectors['position_y'],
		];

		$extra_controls['badge_y_position_bottom'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'Position Offset (Vertical)', 'shopbuilder' ),
			'size_units' => [ 'px', '%' ],
			'range'      => [
				'px' => [
					'min'  => 0,
					'max'  => 300,
					'step' => 1,
				],
				'%'  => [
					'min'  => 0,
					'max'  => 100,
					'step' => 1,
				],
			],
			'condition'  => [
				'badges_position'  => 'bottom',
				'badges_position!' => 'center',
			],
			'selectors'  => $selectors['position_y_bottom'],
		];

		$fields = Fns::insert_controls( 'badges_color_note', $fields, $extra_controls );

		$extra_controls['badges_border_color'] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Border Color', 'shopbuilder' ),
			'selectors' => ! empty( $selectors['border_color'] ) ? $selectors['border_color'] : [],
		];

		$extra_controls['badges_border_radius'] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'label'      => esc_html__( 'Border Radius', 'shopbuilder' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => $selectors['border_radius'],
		];

		$fields = Fns::insert_controls( 'badges_bg_color', $fields, $extra_controls, true );

		return apply_filters( 'rtsb/elements/elementor/badges_style_control', $fields, $obj );
	}

	/**
	 * Product Categories Section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return array
	 */
	public static function product_categories( $obj ): array {
		$css_selectors = $obj->selectors['product_categories'];
		$title         = esc_html__( 'Product Categories', 'shopbuilder' );
		$condition     = [
			'show_categories' => [ 'yes' ],
		];
		$selectors     = [
			'typography'         => $css_selectors['typography'],
			'alignment'          => [ $css_selectors['alignment'] => 'justify-content: {{VALUE}};' ],
			'color'              => [ $css_selectors['color'] => 'color: {{VALUE}};' ],
			'bg_color'           => [ $css_selectors['bg_color'] => 'background-color: {{VALUE}};' ],
			'hover_color'        => [ $css_selectors['hover_color'] => 'color: {{VALUE}};' ],
			'hover_bg_color'     => [ $css_selectors['hover_bg_color'] => 'background-color: {{VALUE}};' ],
			'border'             => $css_selectors['typography'],
			'border_hover_color' => [ $css_selectors['border_hover_color'] => 'border-color: {{VALUE}};' ],
			'border_radius'      => [ $css_selectors['border_radius'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			'padding'            => [ $css_selectors['padding'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			'margin'             => [ $css_selectors['margin'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			'wrapper_margin'     => [ $css_selectors['wrapper_margin'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		];

		$fields = ControlHelper::general_elementor_style( 'product_categories', $title, $obj, $condition, $selectors );

		$fields['product_categories_alignment']['options'] = [
			'flex-start' => [
				'title' => esc_html__( 'Left', 'shopbuilder' ),
				'icon'  => 'eicon-text-align-left',
			],
			'center'     => [
				'title' => esc_html__( 'Center', 'shopbuilder' ),
				'icon'  => 'eicon-text-align-center',
			],
			'flex-end'   => [
				'title' => esc_html__( 'Right', 'shopbuilder' ),
				'icon'  => 'eicon-text-align-right',
			],
		];

		$extra_controls = [];

		$extra_controls['product_categories_radius'] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'label'      => esc_html__( 'Border Radius', 'shopbuilder' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => $selectors['border_radius'],
		];

		$fields = Fns::insert_controls( 'product_categories_border_hover_color', $fields, $extra_controls, true );

		$extra_controls['product_categories_wrapper_margin'] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'label'      => esc_html__( 'Wrapper Margin', 'shopbuilder' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => $selectors['wrapper_margin'],
		];

		$fields = Fns::insert_controls( 'product_categories_margin', $fields, $extra_controls, true );

		return apply_filters( 'rtsb/elements/elementor/categories_style_control', $fields, $obj );
	}

	/**
	 * Image Section
	 *
	 * @param object $obj Reference object.
	 * @param array  $conditions Conditions.
	 *
	 * @return array
	 */
	public static function image( $obj, $conditions ): array {
		$css_selectors = $obj->selectors['image'];
		$title         = esc_html__( 'Image Styles', 'shopbuilder' );
		$selectors     = [
			'overlay'              => $css_selectors['overlay'],
			'hover_overlay'        => $css_selectors['hover_overlay'],
			'border'               => $css_selectors['border'],
			'width'                => [ $css_selectors['width'] => 'width: {{SIZE}}{{UNIT}};' ],
			'img_bg_color'         => [ $css_selectors['img_bg_color'] => 'background-color: {{VALUE}};' ],
			'img_wrapper_bg_color' => [ $css_selectors['img_wrapper_bg_color'] => 'background-color: {{VALUE}};' ],
			'max_width'            => [ $css_selectors['max_width'] => 'max-width: {{SIZE}}{{UNIT}};' ],
			'border_radius'        => [ $css_selectors['border_radius'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			'margin'               => [ $css_selectors['margin'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ],
		];

		$fields = ControlHelper::general_elementor_style( 'image_styles', $title, $obj, [], $selectors, $conditions );

		unset(
			$fields['rtsb_el_image_styles_typography'],
			$fields['image_styles_alignment'],
			$fields['image_styles_color_tabs'],
			$fields['image_styles_color_tab'],
			$fields['image_styles_color_note'],
			$fields['image_styles_color'],
			$fields['image_styles_bg_color'],
			$fields['image_styles_color_tab_end'],
			$fields['image_styles_hover_color_tab'],
			$fields['image_styles_hover_color'],
			$fields['image_styles_hover_bg_color'],
			$fields['image_styles_hover_color_tab_end'],
			$fields['image_styles_color_tabs_end'],
			$fields['image_styles_border_hover_color'],
			$fields['image_styles_padding']
		);

		$fields['image_styles_typo_note']['raw']         = sprintf(
			'<h3 class="rtsb-elementor-group-heading">%s</h3>',
			esc_html__( 'Overlay', 'shopbuilder' )
		);
		$fields['image_styles_border_note']['separator'] = 'before-short';
		$fields['image_styles_typo_note']['separator']   = 'before';
		$fields['image_styles_typo_note']['condition']   = [ 'show_overlay' => [ 'yes' ] ];

		$extra_controls = [];

		$extra_controls['image_styles_dimension_note'] = $obj->el_heading( esc_html__( 'Dimension', 'shopbuilder' ) );

		$extra_controls['image_styles_width'] = [
			'type'       => 'slider',
			'label'      => esc_html__( 'Image Width', 'shopbuilder' ),
			'size_units' => [ '%', 'px' ],
			'range'      => [
				'%'  => [
					'min'  => 0,
					'max'  => 100,
					'step' => 1,
				],
				'px' => [
					'min'  => 0,
					'max'  => 500,
					'step' => 1,
				],
			],
			'selectors'  => $selectors['width'],
		];

		$extra_controls['image_styles_max_width'] = [
			'type'       => 'slider',
			'label'      => esc_html__( 'Image Max-Width', 'shopbuilder' ),
			'size_units' => [ '%', 'px' ],
			'range'      => [
				'%'  => [
					'min'  => 0,
					'max'  => 100,
					'step' => 1,
				],
				'px' => [
					'min'  => 0,
					'max'  => 500,
					'step' => 1,
				],

			],
			'selectors'  => $selectors['max_width'],
		];

		$extra_controls['image_styles_color_note'] = $obj->el_heading( esc_html__( 'Colors', 'shopbuilder' ), 'before' );

		$extra_controls['image_styles_color'] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Image Background Color', 'shopbuilder' ),
			'selectors' => $selectors['img_bg_color'],
		];

		$extra_controls['image_wrapper_styles_color'] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Wrapper Background Color', 'shopbuilder' ),
			'selectors' => $selectors['img_wrapper_bg_color'],
		];

		$fields = Fns::insert_controls( 'image_styles_typo_note', $fields, $extra_controls );

		$extra_controls['rtsb_el_image_styles_overlay'] = [
			'mode'           => 'group',
			'type'           => 'background',
			'label'          => esc_html__( 'Image Overlay', 'shopbuilder' ),
			'types'          => [ 'classic', 'gradient' ],
			'exclude'        => [ 'image' ], // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
			'fields_options' => [
				'background' => [
					'label' => esc_html__( 'Overlay Background Type', 'shopbuilder' ),
				],
				'color'      => [
					'label' => 'Background',
				],
				'color_b'    => [
					'label' => 'Background 2',
				],
			],
			'selector'       => $selectors['overlay'],
			'condition'      => [
				'show_overlay' => [ 'yes' ],
			],
		];

		$extra_controls['rtsb_el_image_styles_hover_overlay'] = [
			'mode'           => 'group',
			'type'           => 'background',
			'label'          => esc_html__( 'Hover Image Overlay', 'shopbuilder' ),
			'types'          => [ 'classic', 'gradient' ],
			'exclude'        => [ 'image' ], // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
			'fields_options' => [
				'background' => [
					'label' => esc_html__( 'Hover Overlay Background Type', 'shopbuilder' ),
				],
				'color'      => [
					'label' => 'Hover Background',
				],
				'color_b'    => [
					'label' => 'Hover Background 2',
				],
			],
			'selector'       => $selectors['hover_overlay'],
			'condition'      => [
				'show_overlay' => [ 'yes' ],
			],
		];

		$fields = Fns::insert_controls( 'image_styles_typo_note', $fields, $extra_controls, true );

		$extra_controls['image_styles_radius'] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'label'      => esc_html__( 'Border Radius', 'shopbuilder' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => $selectors['border_radius'],
		];

		$fields = Fns::insert_controls( 'rtsb_el_image_styles_border', $fields, $extra_controls, true );

		return apply_filters( 'rtsb/elements/elementor/image_style_control', $fields, $obj );
	}

	/**
	 * Product Image Section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return array
	 */
	public static function product_image( $obj ): array {
		$conditions = [
			'relation' => 'and',
			'terms'    => [
				[
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => 'show_featured_image',
							'operator' => '==',
							'value'    => 'yes',
						],
					],
				],
			],
		];

		return self::image( $obj, $conditions );
	}

	/**
	 * Category Image Section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return array
	 */
	public static function category_image( $obj ): array {
		$conditions = [
			'relation' => 'and',
			'terms'    => [
				[
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => 'show_cat_image',
							'operator' => '==',
							'value'    => 'yes',
						],
						[
							'name'     => 'show_custom_image',
							'operator' => '==',
							'value'    => 'yes',
						],
					],
				],
			],
		];

		return self::image( $obj, $conditions );
	}

	/**
	 * Pagination Section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return array
	 */
	public static function pagination( $obj ): array {
		$css_selectors = $obj->selectors['pagination'];
		$title         = esc_html__( 'Pagination Buttons', 'shopbuilder' );
		$condition     = [ 'show_pagination' => [ 'yes' ] ];
		$selectors     = [
			'typography'          => $css_selectors['typography'],
			'alignment'           => [ $css_selectors['alignment'] => 'justify-content: {{VALUE}};' ],
			'width'               => [ $css_selectors['width'] => 'width: {{SIZE}}{{UNIT}};' ],
			'height'              => [ $css_selectors['height'] => 'height: {{SIZE}}{{UNIT}};' ],
			'color'               => [ $css_selectors['color'] => 'color: {{VALUE}};' ],
			'bg_color'            => [ $css_selectors['bg_color'] => 'background-color: {{VALUE}};' ],
			'active_color'        => [ $css_selectors['active_color'] => 'color: {{VALUE}};' ],
			'active_bg_color'     => [ $css_selectors['active_bg_color'] => 'background-color: {{VALUE}};' ],
			'hover_color'         => [ $css_selectors['hover_color'] => 'color: {{VALUE}};' ],
			'hover_bg_color'      => [ $css_selectors['hover_bg_color'] => 'background-color: {{VALUE}};' ],
			'border'              => $css_selectors['border'],
			'border_hover_color'  => [ $css_selectors['border_hover_color'] => 'border-color: {{VALUE}};' ],
			'border_active_color' => [ $css_selectors['border_active_color'] => 'border-color: {{VALUE}};' ],
			'border_radius'       => [ $css_selectors['border_radius'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			'spacing'             => [ $css_selectors['spacing'] => 'gap: {{SIZE}}{{UNIT}};' ],
			'padding'             => [ $css_selectors['padding'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			'margin'              => [ $css_selectors['margin'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		];

		$fields = ControlHelper::general_elementor_style( 'pagination_buttons', $title, $obj, $condition, $selectors );

		unset( $fields['pagination_buttons_padding'] );

		$fields['pagination_buttons_alignment']['options'] = [
			'flex-start' => [
				'title' => esc_html__( 'Left', 'shopbuilder' ),
				'icon'  => 'eicon-text-align-left',
			],
			'center'     => [
				'title' => esc_html__( 'Center', 'shopbuilder' ),
				'icon'  => 'eicon-text-align-center',
			],
			'flex-end'   => [
				'title' => esc_html__( 'Right', 'shopbuilder' ),
				'icon'  => 'eicon-text-align-right',
			],
		];

		$extra_controls = [];

		$extra_controls['pagination_buttons_width'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'Width', 'shopbuilder' ),
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 0,
					'max' => 100,
				],
			],
			'selectors'  => $selectors['width'],
		];

		$extra_controls['pagination_buttons_height'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'Height', 'shopbuilder' ),
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 0,
					'max' => 100,
				],
			],
			'selectors'  => $selectors['height'],
		];

		$fields = Fns::insert_controls( 'pagination_buttons_color_note', $fields, $extra_controls );

		$extra_controls['pagination_buttons_active_color'] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Active Color', 'shopbuilder' ),
			'selectors' => $selectors['active_color'],
			'condition' => [
				'pagination_type!' => 'load_more',
			],
		];

		$extra_controls['pagination_buttons_active_bg_color'] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Active Background Color', 'shopbuilder' ),
			'selectors' => $selectors['active_bg_color'],
			'condition' => [
				'pagination_type!' => 'load_more',
			],
		];

		$fields = Fns::insert_controls( 'pagination_buttons_bg_color', $fields, $extra_controls, true );

		$extra_controls['pagination_buttons_active_border_color'] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Active Border Color', 'shopbuilder' ),
			'condition' => [ 'rtsb_el_pagination_buttons_border_border!' => [ '' ] ],
			'selectors' => $selectors['border_active_color'],
		];

		$extra_controls['pagination_buttons_radius'] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'label'      => esc_html__( 'Border Radius', 'shopbuilder' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => $selectors['border_radius'],
		];

		$fields = Fns::insert_controls( 'pagination_buttons_border_hover_color', $fields, $extra_controls, true );

		$extra_controls['pagination_buttons_spacing'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'Spacing', 'shopbuilder' ),
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 0,
					'max' => 100,
				],
			],
			'selectors'  => $selectors['spacing'],
			'condition'  => [
				'pagination_type!' => 'load_more',
			],
		];

		$extra_controls['pagination_buttons_padding'] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'label'      => esc_html__( 'Padding', 'shopbuilder' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => ! empty( $selectors['padding'] ) ? $selectors['padding'] : [],
			'condition'  => [
				'pagination_type' => 'load_more',
			],
		];

		$fields = Fns::insert_controls( 'pagination_buttons_spacing_note', $fields, $extra_controls, true );

		return apply_filters( 'rtsb/elements/elementor/pagination_style_control', $fields, $obj );
	}

	/**
	 * Product Add to Cart Section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return array
	 */
	public static function product_add_to_cart( $obj ): array {
		$css_selectors = $obj->selectors['product_add_to_cart'];
		$title         = esc_html__( 'Add to Cart Button', 'shopbuilder' );
		$condition     = [
			'show_add_to_cart' => [ 'yes' ],
		];
		$selectors     = [
			'typography'         => $css_selectors['typography'],
			'icon_size'          => [
				$css_selectors['icon_size']['font_size'] => 'font-size: {{SIZE}}{{UNIT}};',
				$css_selectors['icon_size']['width']     => 'height: {{SIZE}}{{UNIT}};',
			],
			'cart_icon_spacing'  => [
				$css_selectors['cart_icon_spacing']['margin_left'] => 'margin-left: {{SIZE}}{{UNIT}};',
				$css_selectors['cart_icon_spacing']['margin_right'] => 'margin-right: {{SIZE}}{{UNIT}};',
			],
			'cart_width'         => [ $css_selectors['cart_width'] => 'width: {{SIZE}}{{UNIT}} !important;' ],
			'cart_height'        => [ $css_selectors['cart_height'] => 'height: {{SIZE}}{{UNIT}} !important;' ],
			'color'              => [ $css_selectors['color'] => 'color: {{VALUE}};' ],
			'bg_color'           => [ $css_selectors['bg_color'] => 'background-color: {{VALUE}};' ],
			'icon_color'         => [ $css_selectors['icon_color'] => 'color: {{VALUE}};' ],
			'hover_color'        => [ $css_selectors['hover_color'] => 'color: {{VALUE}};' ],
			'hover_bg_color'     => [ $css_selectors['hover_bg_color'] => 'background-color: {{VALUE}};' ],
			'hover_icon_color'   => [ $css_selectors['hover_icon_color'] => 'color: {{VALUE}};' ],
			'border'             => $css_selectors['border'],
			'border_hover_color' => [ $css_selectors['border_hover_color'] => 'border-color: {{VALUE}};' ],
			'border_radius'      => [ $css_selectors['border_radius'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ],
			'padding'            => [ $css_selectors['padding'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ],
			'margin'             => [ $css_selectors['margin'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		];

		$fields = ControlHelper::general_elementor_style( 'add_to_cart_button', $title, $obj, $condition, $selectors );

		$fields['add_to_cart_button_alignment'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'Icon Size', 'shopbuilder' ),
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 0,
					'max' => 100,
				],
			],
			'condition'  => [
				'show_cart_icon' => [ 'yes' ],
			],
			'selectors'  => $selectors['icon_size'],
		];

		$extra_controls = [];

		$extra_controls['cart_icon_alignment'] = [
			'type'      => 'choose',
			'label'     => esc_html__( 'Icon Alignment', 'shopbuilder' ),
			'options'   => [
				'left'  => [
					'title' => esc_html__( 'Left', 'shopbuilder' ),
					'icon'  => 'eicon-arrow-left',
				],
				'right' => [
					'title' => esc_html__( 'Right', 'shopbuilder' ),
					'icon'  => 'eicon-arrow-right',
				],
			],
			'default'   => 'left',
			'toggle'    => true,
			'condition' => [
				'show_cart_text' => [ 'yes' ],
				'show_cart_icon' => [ 'yes' ],
			],
		];

		$extra_controls['cart_icon_spacing'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'Icon Spacing', 'shopbuilder' ),
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 0,
					'max' => 100,
				],
			],
			'default'    => [
				'unit' => 'px',
				'size' => 8,
			],
			'condition'  => [
				'show_cart_text' => [ 'yes' ],
				'show_cart_icon' => [ 'yes' ],
			],
			'selectors'  => $selectors['cart_icon_spacing'],
		];

		$extra_controls['cart_width'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'Width', 'shopbuilder' ),
			'size_units' => [ 'px', '%' ],
			'range'      => [
				'px' => [
					'min' => 0,
					'max' => 1000,
				],
			],
			'selectors'  => $selectors['cart_width'],
		];

		$extra_controls['cart_height'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'height', 'shopbuilder' ),
			'size_units' => [ 'px', '%' ],
			'range'      => [
				'px' => [
					'min' => 0,
					'max' => 1000,
				],
			],
			'selectors'  => $selectors['cart_height'],
		];

		$fields = Fns::insert_controls( 'add_to_cart_button_alignment', $fields, $extra_controls, true );

		$extra_controls['add_to_cart_button_icon_color'] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Icon Color', 'shopbuilder' ),
			'selectors' => $selectors['icon_color'],
		];

		$fields = Fns::insert_controls( 'add_to_cart_button_bg_color', $fields, $extra_controls, true );

		$extra_controls['add_to_cart_button_hover_icon_color'] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Hover Icon Color', 'shopbuilder' ),
			'selectors' => $selectors['hover_icon_color'],
		];

		$fields = Fns::insert_controls( 'add_to_cart_button_hover_bg_color', $fields, $extra_controls, true );

		$extra_controls['add_to_cart_button_radius'] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'label'      => esc_html__( 'Border Radius', 'shopbuilder' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => $selectors['border_radius'],
		];

		$fields = Fns::insert_controls( 'add_to_cart_button_border_hover_color', $fields, $extra_controls, true );

		return apply_filters( 'rtsb/elements/elementor/add_to_cart_style_control', $fields, $obj );
	}

	/**
	 * Product Compare Section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return array
	 */
	public static function product_compare( $obj ): array {
		$css_selectors = $obj->selectors['product_compare'];
		$title         = esc_html__( 'Compare Button', 'shopbuilder' );
		$condition     = [
			'show_compare' => [ 'yes' ],
		];
		$selectors     = self::action_btn_selectors( $css_selectors );

		$fields = ControlHelper::general_elementor_style( 'compare_button', $title, $obj, $condition, $selectors );

		unset( $fields['compare_button_alignment'] );

		$fields['rtsb_el_compare_button_typography'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'Icon Size', 'shopbuilder' ),
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 0,
					'max' => 100,
				],
			],
			'condition'  => [
				'show_cart_icon' => [ 'yes' ],
			],
			'selectors'  => $selectors['icon_size'],
		];

		$extra_controls = [];

		$extra_controls['compare_width'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'Width', 'shopbuilder' ),
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 0,
					'max' => 1000,
				],
			],
			'selectors'  => $selectors['width'],
		];

		$extra_controls['compare_height'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'height', 'shopbuilder' ),
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 0,
					'max' => 100,
				],
			],
			'selectors'  => $selectors['height'],
		];

		$fields = Fns::insert_controls( 'compare_button_color_note', $fields, $extra_controls );

		$extra_controls['compare_button_radius'] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'label'      => esc_html__( 'Border Radius', 'shopbuilder' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => $selectors['border_radius'],
		];

		$fields = Fns::insert_controls( 'compare_button_border_hover_color', $fields, $extra_controls, true );

		return apply_filters( 'rtsb/elements/elementor/compare_style_control', $fields, $obj );
	}

	/**
	 * Product Wishlist Section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return array
	 */
	public static function product_wishlist( $obj ): array {
		$css_selectors = $obj->selectors['product_wishlist'];
		$title         = esc_html__( 'Wishlist Button', 'shopbuilder' );
		$condition     = [
			'show_wishlist' => [ 'yes' ],
		];
		$selectors     = self::action_btn_selectors( $css_selectors );

		$fields = ControlHelper::general_elementor_style( 'wishlist_button', $title, $obj, $condition, $selectors );

		unset( $fields['wishlist_button_alignment'] );

		$fields['rtsb_el_wishlist_button_typography'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'Icon Size', 'shopbuilder' ),
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 0,
					'max' => 100,
				],
			],
			'condition'  => [
				'show_cart_icon' => [ 'yes' ],
			],
			'selectors'  => $selectors['icon_size'],
		];

		$extra_controls = [];

		$extra_controls['wishlist_width'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'Width', 'shopbuilder' ),
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 0,
					'max' => 1000,
				],
			],
			'selectors'  => $selectors['width'],
		];

		$extra_controls['wishlist_height'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'height', 'shopbuilder' ),
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 0,
					'max' => 100,
				],
			],
			'selectors'  => $selectors['height'],
		];

		$fields = Fns::insert_controls( 'wishlist_button_color_note', $fields, $extra_controls );

		$extra_controls['wishlist_button_radius'] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'label'      => esc_html__( 'Border Radius', 'shopbuilder' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => $selectors['border_radius'],
		];

		$fields = Fns::insert_controls( 'wishlist_button_border_hover_color', $fields, $extra_controls, true );

		return apply_filters( 'rtsb/elements/elementor/wishlist_style_control', $fields, $obj );
	}

	/**
	 * Product Quick View Section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return array
	 */
	public static function product_quick_view( $obj ): array {
		$css_selectors = $obj->selectors['product_quick_view'];
		$title         = esc_html__( 'Quick View Button', 'shopbuilder' );
		$condition     = [
			'show_quick_view' => [ 'yes' ],
		];
		$selectors     = self::action_btn_selectors( $css_selectors );

		$fields = ControlHelper::general_elementor_style( 'quick_view_button', $title, $obj, $condition, $selectors );

		unset( $fields['quick_view_button_alignment'] );

		$fields['rtsb_el_quick_view_button_typography'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'Icon Size', 'shopbuilder' ),
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 0,
					'max' => 100,
				],
			],
			'condition'  => [
				'show_cart_icon' => [ 'yes' ],
			],
			'selectors'  => $selectors['icon_size'],
		];

		$extra_controls = [];

		$extra_controls['quick_view_width'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'Width', 'shopbuilder' ),
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 0,
					'max' => 1000,
				],
			],
			'selectors'  => $selectors['width'],
		];

		$extra_controls['quick_view_height'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'height', 'shopbuilder' ),
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 0,
					'max' => 100,
				],
			],
			'selectors'  => $selectors['height'],
		];

		$fields = Fns::insert_controls( 'quick_view_button_color_note', $fields, $extra_controls );

		$extra_controls['quick_view_button_radius'] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'label'      => esc_html__( 'Border Radius', 'shopbuilder' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => $selectors['border_radius'],
		];

		$fields = Fns::insert_controls( 'quick_view_button_border_hover_color', $fields, $extra_controls, true );

		return apply_filters( 'rtsb/elements/elementor/quick_view_style_control', $fields, $obj );
	}

	/**
	 * Hover Icon Button Section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return array
	 */
	public static function hover_icon_button( $obj ): array {
		$css_selectors = $obj->selectors['hover_icon_button'];
		$title         = esc_html__( 'Hover Icon Button', 'shopbuilder' );
		$condition     = [
			'layout' => [ 'grid-layout2', 'slider-layout2' ],
		];
		$selectors     = [
			'typography'         => $css_selectors['typography'],
			'icon_size'          => [ $css_selectors['icon_size'] => 'font-size: {{SIZE}}{{UNIT}}' ],
			'hover_icon_spacing' => [ $css_selectors['hover_icon_spacing'] => 'margin-left: {{SIZE}}{{UNIT}}' ],
			'color'              => [ $css_selectors['color'] => 'color: {{VALUE}}' ],
			'bg_color'           => [ $css_selectors['bg_color'] => 'background-color: {{VALUE}}' ],
			'icon_color'         => [ $css_selectors['icon_color'] => 'color: {{VALUE}}' ],
			'hover_color'        => [ $css_selectors['hover_color'] => 'color: {{VALUE}}' ],
			'hover_bg_color'     => [ $css_selectors['hover_bg_color'] => 'background-color: {{VALUE}}' ],
			'hover_icon_color'   => [ $css_selectors['hover_icon_color'] => 'color: {{VALUE}}' ],
			'border'             => $css_selectors['border'],
			'border_hover_color' => [ $css_selectors['border_hover_color'] => 'border-color: {{VALUE}}' ],
			'border_radius'      => [ $css_selectors['border_radius'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}' ],
			'padding'            => [ $css_selectors['padding'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			'margin'             => [ $css_selectors['margin'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		];

		$fields = ControlHelper::general_elementor_style( 'hover_icon_button', $title, $obj, $condition, $selectors );

		$fields['hover_icon_button_alignment'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'Icon Size', 'shopbuilder' ),
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 0,
					'max' => 100,
				],
			],
			'default'    => [
				'unit' => 'px',
				'size' => 14,
			],
			'condition'  => [
				'show_hover_btn_icon' => [ 'yes' ],
			],
			'selectors'  => $selectors['icon_size'],
		];

		$extra_controls = [];

		$extra_controls['hover_icon_button_spacing'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'Icon Spacing', 'shopbuilder' ),
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 0,
					'max' => 100,
				],
			],
			'default'    => [
				'unit' => 'px',
				'size' => 3,
			],
			'condition'  => [
				'show_hover_btn_icon' => [ 'yes' ],
			],
			'selectors'  => $selectors['hover_icon_spacing'],
		];

		$fields = Fns::insert_controls( 'hover_icon_button_alignment', $fields, $extra_controls, true );

		$extra_controls['hover_icon_button_icon_color'] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Icon Color', 'shopbuilder' ),
			'condition' => [
				'show_hover_btn_icon' => [ 'yes' ],
			],
			'selectors' => $selectors['icon_color'],
		];

		$fields = Fns::insert_controls( 'hover_icon_button_bg_color', $fields, $extra_controls, true );

		$extra_controls['hover_icon_button_hover_icon_color'] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Hover Icon Color', 'shopbuilder' ),
			'condition' => [
				'show_hover_btn_icon' => [ 'yes' ],
			],
			'selectors' => $selectors['hover_icon_color'],
		];

		$fields = Fns::insert_controls( 'hover_icon_button_hover_bg_color', $fields, $extra_controls, true );

		$extra_controls['hover_icon_button_radius'] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'label'      => esc_html__( 'Border Radius', 'shopbuilder' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => $selectors['border_radius'],
		];

		$fields = Fns::insert_controls( 'hover_icon_button_border_hover_color', $fields, $extra_controls, true );

		return apply_filters( 'rtsb/elements/elementor/hover_icon_btn_style_control', $fields, $obj );
	}

	/**
	 * Share Items Section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return array
	 */
	public static function share_items( $obj ): array {
		$css_selectors = $obj->selectors['share_items'];
		$title         = esc_html__( 'Share Items', 'shopbuilder' );
		$condition     = [
			'layout!' => [ 'default' ],
		];
		$selectors     = [
			'share_icons_spacing'         => [ $css_selectors['share_icons_spacing'] => 'gap: {{SIZE}}{{UNIT}}' ],
			'share_items_min_width'       => [ $css_selectors['share_items_min_width'] => 'min-width: {{SIZE}}{{UNIT}}' ],
			'social_items_color'          => [ $css_selectors['social_items_color'] => 'fill: {{VALUE}}; color: {{VALUE}}' ],
			'social_items_bg_color'       => [ $css_selectors['social_items_bg_color'] => 'background-color: {{VALUE}}' ],
			'facebook_color'              => [ $css_selectors['facebook_color'] => 'fill: {{VALUE}}; color: {{VALUE}}' ],
			'twitter_color'               => [ $css_selectors['twitter_color'] => 'fill: {{VALUE}}; color: {{VALUE}}' ],
			'linkedin_color'              => [ $css_selectors['linkedin_color'] => 'fill: {{VALUE}}; color: {{VALUE}}' ],
			'pinterest_color'             => [ $css_selectors['pinterest_color'] => 'fill: {{VALUE}}; color: {{VALUE}}' ],
			'skype_color'                 => [ $css_selectors['skype_color'] => 'fill: {{VALUE}}; color: {{VALUE}}' ],
			'whatsapp_color'              => [ $css_selectors['whatsapp_color'] => 'fill: {{VALUE}}; color: {{VALUE}}' ],
			'reddit_color'                => [ $css_selectors['reddit_color'] => 'fill: {{VALUE}}; color: {{VALUE}}' ],
			'telegram_color'              => [ $css_selectors['telegram_color'] => 'fill: {{VALUE}}; color: {{VALUE}}' ],
			'facebook_bg_color'           => [ $css_selectors['facebook_bg_color'] => 'background-color: {{VALUE}}' ],
			'twitter_bg_color'            => [ $css_selectors['twitter_bg_color'] => 'background-color: {{VALUE}}' ],
			'linkedin_bg_color'           => [ $css_selectors['linkedin_bg_color'] => 'background-color: {{VALUE}}' ],
			'pinterest_bg_color'          => [ $css_selectors['pinterest_bg_color'] => 'background-color: {{VALUE}}' ],
			'skype_bg_color'              => [ $css_selectors['skype_bg_color'] => 'background-color: {{VALUE}}' ],
			'whatsapp_bg_color'           => [ $css_selectors['whatsapp_bg_color'] => 'background-color: {{VALUE}}' ],
			'reddit_bg_color'             => [ $css_selectors['reddit_bg_color'] => 'background-color: {{VALUE}}' ],
			'telegram_bg_color'           => [ $css_selectors['telegram_bg_color'] => 'background-color: {{VALUE}}' ],
			'social_items_hover_color'    => [ $css_selectors['social_items_hover_color'] => 'fill: {{VALUE}}; color: {{VALUE}}' ],
			'social_items_hover_bg_color' => [ $css_selectors['social_items_hover_bg_color'] => 'background-color: {{VALUE}}' ],
			'facebook_hover_color'        => [ $css_selectors['facebook_hover_color'] => 'fill: {{VALUE}}; color: {{VALUE}}' ],
			'twitter_hover_color'         => [ $css_selectors['twitter_hover_color'] => 'fill: {{VALUE}}; color: {{VALUE}}' ],
			'linkedin_hover_color'        => [ $css_selectors['linkedin_hover_color'] => 'fill: {{VALUE}}; color: {{VALUE}}' ],
			'pinterest_hover_color'       => [ $css_selectors['pinterest_hover_color'] => 'fill: {{VALUE}}; color: {{VALUE}}' ],
			'skype_hover_color'           => [ $css_selectors['skype_hover_color'] => 'fill: {{VALUE}}; color: {{VALUE}}' ],
			'whatsapp_hover_color'        => [ $css_selectors['whatsapp_hover_color'] => 'fill: {{VALUE}}; color: {{VALUE}}' ],
			'reddit_hover_color'          => [ $css_selectors['reddit_hover_color'] => 'fill: {{VALUE}}; color: {{VALUE}}' ],
			'telegram_hover_color'        => [ $css_selectors['telegram_hover_color'] => 'fill: {{VALUE}}; color: {{VALUE}}' ],
			'facebook_hover_bg_color'     => [ $css_selectors['facebook_hover_bg_color'] => 'background-color: {{VALUE}}' ],
			'twitter_hover_bg_color'      => [ $css_selectors['twitter_hover_bg_color'] => 'background-color: {{VALUE}}' ],
			'linkedin_hover_bg_color'     => [ $css_selectors['linkedin_hover_bg_color'] => 'background-color: {{VALUE}}' ],
			'pinterest_hover_bg_color'    => [ $css_selectors['pinterest_hover_bg_color'] => 'background-color: {{VALUE}}' ],
			'skype_hover_bg_color'        => [ $css_selectors['skype_hover_bg_color'] => 'background-color: {{VALUE}}' ],
			'whatsapp_hover_bg_color'     => [ $css_selectors['whatsapp_hover_bg_color'] => 'background-color: {{VALUE}}' ],
			'reddit_hover_bg_color'       => [ $css_selectors['reddit_hover_bg_color'] => 'background-color: {{VALUE}}' ],
			'telegram_hover_bg_color'     => [ $css_selectors['telegram_hover_bg_color'] => 'background-color: {{VALUE}}' ],
			'border'                      => $css_selectors['border'],
			'border_hover_color'          => [ $css_selectors['border_hover_color'] => 'border-color: {{VALUE}}' ],
			'border_radius'               => [ $css_selectors['border_radius'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}' ],
			'padding'                     => [ $css_selectors['padding'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			'margin'                      => [ $css_selectors['margin'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		];

		$fields = ControlHelper::general_elementor_style( 'share_items', $title, $obj, $condition, $selectors );

		unset(
			$fields['rtsb_el_share_items_typography'],
			$fields['share_items_alignment'],
			$fields['share_items_hover_color'],
			$fields['share_items_hover_bg_color'],
			$fields['share_items_color'],
			$fields['share_items_bg_color']
		);

		$extra_controls = [];

		$extra_controls['share_items_spacing'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'Items Spacing', 'shopbuilder' ),
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 0,
					'max' => 100,
				],
			],
			'selectors'  => $selectors['share_icons_spacing'],
		];

		$extra_controls['share_items_min_width'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'Items Min Width', 'shopbuilder' ),
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 0,
					'max' => 300,
				],
			],
			'selectors'  => $selectors['share_items_min_width'],
		];

		$fields = Fns::insert_controls( 'share_items_typo_note', $fields, $extra_controls, true );

		$share_platforms = ControlHelper::sharing_settings();

		$extra_controls['social_items_color'] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Items Color', 'shopbuilder' ),
			'selectors' => $selectors['social_items_color'],
		];

		$extra_controls['social_items_bg_color'] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Items Background Color', 'shopbuilder' ),
			'selectors' => $selectors['social_items_bg_color'],
		];

		foreach ( $share_platforms as $share_platform ) {
			$extra_controls[ $share_platform . '_color' ] = [
				'type'      => 'color',
				'label'     => ucfirst( $share_platform ) . esc_html__( ' Color', 'shopbuilder' ),
				'selectors' => $selectors[ $share_platform . '_color' ],
			];

			$extra_controls[ $share_platform . '_bg_color' ] = [
				'type'      => 'color',
				'label'     => ucfirst( $share_platform ) . esc_html__( ' Background', 'shopbuilder' ),
				'selectors' => $selectors[ $share_platform . '_bg_color' ],
			];

			$fields = Fns::insert_controls( 'share_items_color_tab', $fields, $extra_controls, true );
		}

		$extra_controls['social_items_hover_color'] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Items Hover Color', 'shopbuilder' ),
			'selectors' => $selectors['social_items_hover_color'],
		];

		$extra_controls['social_items_hover_bg_color'] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Items Background Hover Color', 'shopbuilder' ),
			'selectors' => $selectors['social_items_hover_bg_color'],
		];

		foreach ( $share_platforms as $share_platform ) {
			$extra_controls[ $share_platform . '_hover_color' ] = [
				'type'      => 'color',
				'label'     => ucfirst( $share_platform ) . esc_html__( ' Hover Color', 'shopbuilder' ),
				'selectors' => $selectors[ $share_platform . '_hover_color' ],
			];

			$extra_controls[ $share_platform . '_hover_bg_color' ] = [
				'type'      => 'color',
				'label'     => ucfirst( $share_platform ) . esc_html__( ' Hover Background', 'shopbuilder' ),
				'selectors' => $selectors[ $share_platform . '_hover_bg_color' ],
			];

			$fields = Fns::insert_controls( 'share_items_hover_color_tab', $fields, $extra_controls, true );
		}

		$extra_controls['share_items_radius'] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'label'      => esc_html__( 'Border Radius', 'shopbuilder' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => $selectors['border_radius'],
		];

		$fields = Fns::insert_controls( 'share_items_border_hover_color', $fields, $extra_controls, true );

		return apply_filters( 'rtsb/elements/elementor/share_icons_style_control', $fields, $obj );
	}

	/**
	 * Share Icons Section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return array
	 */
	public static function share_icons( $obj ): array {
		$css_selectors = $obj->selectors['share_icons'];
		$title         = esc_html__( 'Share Icons', 'shopbuilder' );
		$condition     = [
			'layout!' => [ 'default' ],
		];
		$selectors     = [
			'share_icons_width'  => [ $css_selectors['share_icons_width'] => 'width: {{SIZE}}{{UNIT}}' ],
			'share_icons_height' => [ $css_selectors['share_icons_height'] => 'height: {{SIZE}}{{UNIT}}' ],
			'color'              => [ $css_selectors['color'] => 'fill: {{VALUE}}' ],
			'bg_color'           => [ $css_selectors['bg_color'] => 'background-color: {{VALUE}}' ],
			'hover_color'        => [ $css_selectors['hover_color'] => 'fill: {{VALUE}}' ],
			'hover_bg_color'     => [ $css_selectors['hover_bg_color'] => 'background-color: {{VALUE}}' ],
			'border'             => $css_selectors['border'],
			'border_hover_color' => [ $css_selectors['border_hover_color'] => 'border-color: {{VALUE}}' ],
			'padding'            => [ $css_selectors['padding'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			'margin'             => [ $css_selectors['margin'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		];

		$fields = ControlHelper::general_elementor_style( 'share_icons', $title, $obj, $condition, $selectors );

		unset(
			$fields['rtsb_el_share_icons_typography'],
			$fields['share_icons_alignment'],
		);

		$extra_controls = [];

		$extra_controls['share_icons_width'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'Icon Width', 'shopbuilder' ),
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 0,
					'max' => 100,
				],
			],
			'selectors'  => $selectors['share_icons_width'],
		];

		$extra_controls['share_icons_height'] = [
			'type'       => 'slider',
			'mode'       => 'responsive',
			'label'      => esc_html__( 'Icon Height', 'shopbuilder' ),
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 0,
					'max' => 100,
				],
			],
			'selectors'  => $selectors['share_icons_height'],
		];

		$fields = Fns::insert_controls( 'share_icons_typo_note', $fields, $extra_controls, true );

		$fields['share_icons_typo_note']['raw'] = sprintf(
			'<h3 class="rtsb-elementor-group-heading">%s</h3>',
			esc_html__( 'Dimension', 'shopbuilder' )
		);

		return apply_filters( 'rtsb/elements/elementor/share_icons_style_control', $fields, $obj );
	}

	/**
	 * Share Text Section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return array
	 */
	public static function share_text( $obj ): array {
		$css_selectors = $obj->selectors['share_text'];
		$title         = esc_html__( 'Share Text', 'shopbuilder' );
		$condition     = [
			'layout!' => [ 'default' ],
		];
		$selectors     = [
			'typography'         => $css_selectors['typography'],
			'alignment'          => [ $css_selectors['alignment'] => 'text-align: {{VALUE}};' ],
			'color'              => [ $css_selectors['color'] => 'color: {{VALUE}};' ],
			'bg_color'           => [ $css_selectors['bg_color'] => 'background-color: {{VALUE}}' ],
			'hover_color'        => [ $css_selectors['hover_color'] => 'color: {{VALUE}}' ],
			'hover_bg_color'     => [ $css_selectors['hover_bg_color'] => 'background-color: {{VALUE}}' ],
			'border'             => $css_selectors['border'],
			'border_hover_color' => [ $css_selectors['border_hover_color'] => 'border-color: {{VALUE}}' ],
			'padding'            => [ $css_selectors['padding'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			'margin'             => [ $css_selectors['margin'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		];

		$fields = ControlHelper::general_elementor_style( 'share_text', $title, $obj, $condition, $selectors );

		return apply_filters( 'rtsb/elements/elementor/share_text_style_control', $fields, $obj );
	}

	/**
	 * Share Text Section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return array
	 */
	public static function share_header( $obj ): array {
		$css_selectors = $obj->selectors['share_header'];
		$title         = esc_html__( 'Share Header', 'shopbuilder' );
		$condition     = [
			'show_share_pre_text' => [ 'yes' ],
		];
		$selectors     = [
			'typography'        => $css_selectors['typography'],
			'share_header_type' => [ $css_selectors['share_header_type'] => 'display: {{VALUE}};' ],
			'color'             => [ $css_selectors['color'] => 'color: {{VALUE}};' ],
			'bg_color'          => [ $css_selectors['bg_color'] => 'background-color: {{VALUE}}' ],
			'border'            => $css_selectors['border'],
			'padding'           => [ $css_selectors['padding'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			'margin'            => [ $css_selectors['margin'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ],
		];

		$fields = ControlHelper::general_elementor_style( 'share_header', $title, $obj, $condition, $selectors );

		unset(
			$fields['share_header_alignment'],
			$fields['share_header_color_tabs'],
			$fields['share_header_color_tab'],
			$fields['share_header_color_tab_end'],
			$fields['share_header_hover_color_tab'],
			$fields['share_header_hover_color'],
			$fields['share_header_hover_bg_color'],
			$fields['share_header_hover_color_tab_end'],
			$fields['share_header_color_tabs_end'],
			$fields['share_header_border_hover_color'],
		);

		$extra_controls = [];

		$extra_controls['share_header_type'] = [
			'type'      => 'choose',
			'label'     => esc_html__( 'Display Type', 'shopbuilder' ),
			'options'   => [
				'flex'  => [
					'title' => esc_html__( 'Inline', 'shopbuilder' ),
					'icon'  => 'eicon-h-align-right',
				],
				'block' => [
					'title' => esc_html__( 'New Line', 'shopbuilder' ),
					'icon'  => 'eicon-v-align-bottom',
				],
			],
			'default'   => 'flex',
			'toggle'    => true,
			'selectors' => $selectors['share_header_type'],
		];

		return Fns::insert_controls( 'share_header_color_note', $fields, $extra_controls );
	}

	/**
	 * Advanced section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return array
	 */
	public static function advanced( $obj ): array {

		$fields['advanced_section'] = $obj->start_section( esc_html__( 'Advanced', 'shopbuilder' ), self::$tab );

		$fields['gutter_section_end'] = $obj->end_section();

		return apply_filters( 'rtsb/elements/elementor/advanced_control', $fields, $obj );
	}

	/**
	 * Slider buttons section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return array
	 */
	public static function slider_buttons( $obj ): array {
		$css_selectors = $obj->selectors['slider_buttons'];
		$title         = esc_html__( 'Slider Buttons', 'shopbuilder' );
		$selectors     = [
			'arrow_size'          => [ $css_selectors['arrow_size'] => 'font-size: {{SIZE}}{{UNIT}};' ],
			'arrow_width'         => [ $css_selectors['arrow_width'] => 'width: {{SIZE}}{{UNIT}};' ],
			'arrow_height'        => [ $css_selectors['arrow_height'] => 'height: {{SIZE}}{{UNIT}};' ],
			'arrow_line_height'   => [ $css_selectors['arrow_line_height'] => 'line-height: {{SIZE}}{{UNIT}};' ],
			'dot_width'           => [ $css_selectors['dot_width'] => 'width: {{SIZE}}{{UNIT}};' ],
			'dot_height'          => [ $css_selectors['dot_height'] => 'height: {{SIZE}}{{UNIT}};' ],
			'dot_spacing'         => [ $css_selectors['dot_spacing'] => 'margin-left: calc({{SIZE}}{{UNIT}} / 2); margin-right: calc({{SIZE}}{{UNIT}} / 2);' ],
			'color'               => [ $css_selectors['color'] => 'color: {{VALUE}};' ],
			'bg_color'            => [ $css_selectors['bg_color'] => 'background-color: {{VALUE}};' ],
			'hover_color'         => [ $css_selectors['hover_color'] => 'color: {{VALUE}};' ],
			'hover_bg_color'      => [ $css_selectors['hover_bg_color'] => 'background-color: {{VALUE}};' ],
			'dot_color'           => [ $css_selectors['dot_color'] => 'background-color: {{VALUE}};' ],
			'dot_active_color'    => [ $css_selectors['dot_active_color'] => 'background-color: {{VALUE}};' ],
			'border'              => $css_selectors['border'],
			'border_hover_color'  => [ $css_selectors['border_hover_color'] => 'border-color: {{VALUE}};' ],
			'active_border_color' => [ $css_selectors['active_border_color'] => 'border-color: {{VALUE}};' ],
			'border_radius'       => [ $css_selectors['border_radius'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			'wrapper_padding'     => [ $css_selectors['wrapper_padding'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		];

		$conditions = [
			'relation' => 'or',
			'terms'    => [
				[
					'name'     => 'slider_nav',
					'operator' => '==',
					'value'    => 'yes',
				],
				[
					'name'     => 'slider_pagi',
					'operator' => '==',
					'value'    => 'yes',
				],
			],
		];

		$fields = ControlHelper::general_elementor_style( 'slider_buttons', $title, $obj, [], $selectors, $conditions );

		unset(
			$fields['rtsb_el_slider_buttons_typography'],
			$fields['slider_buttons_alignment'],
			$fields['slider_buttons_padding'],
			$fields['slider_buttons_margin']
		);

		$extra_controls  = [];
		$arrow_condition = [ 'slider_nav' => [ 'yes' ] ];
		$dot_condition   = [ 'slider_pagi' => [ 'yes' ] ];

		$fields['slider_buttons_typo_note']['raw'] = sprintf(
			'<h3 class="rtsb-elementor-group-heading">%s</h3>',
			esc_html__( 'Arrow Size (px)', 'shopbuilder' )
		);

		$fields['slider_buttons_typo_note']['condition'] = $arrow_condition;

		$extra_controls['slider_buttons_arrow_size'] = [
			'type'      => 'slider',
			'mode'      => 'responsive',
			'label'     => esc_html__( 'Arrow Size', 'shopbuilder' ),
			'range'     => [
				'px' => [
					'min'  => 1,
					'max'  => 100,
					'step' => 1,
				],
			],
			'condition' => $arrow_condition,
			'selectors' => $selectors['arrow_size'],
		];

		$extra_controls['slider_buttons_arrow_width'] = [
			'type'      => 'slider',
			'mode'      => 'responsive',
			'label'     => esc_html__( 'Arrow Width', 'shopbuilder' ),
			'range'     => [
				'px' => [
					'min'  => 1,
					'max'  => 100,
					'step' => 1,
				],
			],
			'condition' => $arrow_condition,
			'selectors' => $selectors['arrow_width'],
		];

		$extra_controls['slider_buttons_arrow_height'] = [
			'type'      => 'slider',
			'mode'      => 'responsive',
			'label'     => esc_html__( 'Arrow Height', 'shopbuilder' ),
			'range'     => [
				'px' => [
					'min'  => 1,
					'max'  => 100,
					'step' => 1,
				],
			],
			'condition' => $arrow_condition,
			'selectors' => $selectors['arrow_height'],
		];

		$extra_controls['slider_buttons_arrow_line_height'] = [
			'type'      => 'slider',
			'mode'      => 'responsive',
			'label'     => esc_html__( 'Arrow Line Height', 'shopbuilder' ),
			'range'     => [
				'px' => [
					'min'  => 1,
					'max'  => 100,
					'step' => 1,
				],
			],
			'condition' => $arrow_condition,
			'selectors' => $selectors['arrow_line_height'],
		];

		$extra_controls['slider_buttons_dot_size_note'] = $obj->el_heading(
			esc_html__( 'Dot Size (px)', 'shopbuilder' ),
			'before',
			[],
			$dot_condition
		);

		$extra_controls['slider_buttons_dot_width'] = [
			'type'      => 'slider',
			'mode'      => 'responsive',
			'label'     => esc_html__( 'Dot Width', 'shopbuilder' ),
			'range'     => [
				'px' => [
					'min'  => 1,
					'max'  => 100,
					'step' => 1,
				],
			],
			'selectors' => $selectors['dot_width'],
			'condition' => $dot_condition,
		];

		$extra_controls['slider_buttons_dot_height'] = [
			'type'      => 'slider',
			'mode'      => 'responsive',
			'label'     => esc_html__( 'Dot Height', 'shopbuilder' ),
			'range'     => [
				'px' => [
					'min'  => 1,
					'max'  => 100,
					'step' => 1,
				],
			],
			'selectors' => $selectors['dot_height'],
			'condition' => $dot_condition,
		];

		$extra_controls['slider_buttons_dot_spacing'] = [
			'type'      => 'slider',
			'mode'      => 'responsive',
			'label'     => esc_html__( 'Dot Spacing', 'shopbuilder' ),
			'range'     => [
				'px' => [
					'min'  => 1,
					'max'  => 100,
					'step' => 1,
				],
			],
			'selectors' => $selectors['dot_spacing'],
			'condition' => $dot_condition,
		];

		$fields = Fns::insert_controls( 'slider_buttons_typo_note', $fields, $extra_controls, true );

		$extra_controls['slider_buttons_active_color_tab'] = $obj->start_tab( esc_html__( 'Dot', 'shopbuilder' ) );

		$extra_controls['slider_buttons_active_color'] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Dot Color', 'shopbuilder' ),
			'selectors' => $selectors['dot_color'],
		];

		$extra_controls['slider_buttons_active_bg_color'] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Dot Active Color', 'shopbuilder' ),
			'selectors' => $selectors['dot_active_color'],
		];

		$extra_controls['slider_buttons_active_color_tab_end'] = $obj->end_tab();

		$fields = Fns::insert_controls( 'slider_buttons_hover_color_tab_end', $fields, $extra_controls, true );

		$extra_controls['slider_buttons_border_active_color'] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Active Color', 'shopbuilder' ),
			'condition' => [ 'rtsb_el_slider_buttons_border_border!' => [ '' ] ],
			'selectors' => $selectors['active_border_color'],
		];

		$extra_controls['slider_buttons_buttons_radius'] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'label'      => esc_html__( 'Border Radius', 'shopbuilder' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => $selectors['border_radius'],
		];

		$fields = Fns::insert_controls( 'slider_buttons_border_hover_color', $fields, $extra_controls, true );

		$extra_controls['slider_buttons_wrapper_padding'] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'label'      => esc_html__( 'Wrapper Margin', 'shopbuilder' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => $selectors['wrapper_padding'],
		];

		$fields = Fns::insert_controls( 'slider_buttons_spacing_note', $fields, $extra_controls, true );

		return apply_filters( 'rtsb/elements/elementor/slider_buttons_style_control', $fields, $obj );
	}

	/**
	 * Not Found Notice Section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return array
	 */
	public static function not_found_notice( $obj ): array {
		$css_selectors = $obj->selectors['not_found_notice'];
		$title         = esc_html__( 'Not Found Notice', 'shopbuilder' );
		$selectors     = [
			'typography' => $css_selectors['typography'],
			'alignment'  => [ $css_selectors['alignment'] => 'text-align: {{VALUE}}; justify-content: {{VALUE}};' ],
			'color'      => [ $css_selectors['color'] => 'color: {{VALUE}};' ],
			'bg_color'   => [ $css_selectors['bg_color'] => 'background-color: {{VALUE}};' ],
			'border'     => $css_selectors['border'],
			'padding'    => [ $css_selectors['padding'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			'margin'     => [ $css_selectors['margin'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ],
		];

		$fields = ControlHelper::general_elementor_style( 'not_found_notice', $title, $obj, [], $selectors );

		unset(
			$fields['not_found_notice_color_tabs'],
			$fields['not_found_notice_color_tab'],
			$fields['not_found_notice_color_tab_end'],
			$fields['not_found_notice_hover_color_tab'],
			$fields['not_found_notice_hover_color'],
			$fields['not_found_notice_hover_bg_color'],
			$fields['not_found_notice_hover_color_tab_end'],
			$fields['not_found_notice_color_tabs_end'],
			$fields['not_found_notice_border_hover_color']
		);

		return $fields;
	}

	/**
	 * Title Selectors.
	 *
	 * @param array $selectors Selector array.
	 *
	 * @return array
	 */
	public static function title_selectors( $selectors ): array {
		return [
			'typography'         => $selectors['typography'],
			'alignment'          => [ $selectors['alignment'] => 'text-align: {{VALUE}};' ],
			'color'              => [ $selectors['color'] => 'color: {{VALUE}};' ],
			'bg_color'           => [ $selectors['bg_color'] => 'background-color: {{VALUE}};' ],
			'hover_color'        => [ $selectors['hover_color'] => 'color: {{VALUE}};' ],
			'hover_bg_color'     => [ $selectors['hover_bg_color'] => 'background-color: {{VALUE}};' ],
			'border'             => $selectors['border'],
			'border_hover_color' => [ $selectors['border_hover_color'] => 'border-color: {{VALUE}};' ],
			'padding'            => [ $selectors['padding'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			'margin'             => [ $selectors['margin'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ],
		];
	}

	/**
	 * Excerpt Selectors.
	 *
	 * @param array $selectors Selector array.
	 *
	 * @return array
	 */
	private static function excerpt_selectors( $selectors ): array {
		return [
			'typography' => $selectors['typography'],
			'alignment'  => [ $selectors['alignment'] => 'text-align: {{VALUE}};' ],
			'color'      => [ $selectors['color'] => 'color: {{VALUE}};' ],
			'bg_color'   => [ $selectors['bg_color'] => 'background-color: {{VALUE}};' ],
			'border'     => $selectors['border'],
			'padding'    => [ $selectors['padding'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			'margin'     => [ $selectors['margin'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		];
	}

	/**
	 * Action Button Selectors.
	 *
	 * @param array $selectors Selector array.
	 *
	 * @return array
	 */
	public static function action_btn_selectors( $selectors ): array {
		return [
			'icon_size'          => [
				$selectors['icon_size']['font_size'] => 'font-size: {{SIZE}}{{UNIT}} !important;',
				$selectors['icon_size']['width']     => 'width: {{SIZE}}{{UNIT}} !important;',
			],
			'width'              => [ $selectors['width'] => 'width: {{SIZE}}{{UNIT}} !important;' ],
			'height'             => [ $selectors['height'] => 'height: {{SIZE}}{{UNIT}} !important;' ],
			'color'              => [ $selectors['color'] => 'color: {{VALUE}};' ],
			'bg_color'           => [ $selectors['bg_color'] => 'background-color: {{VALUE}} !important;' ],
			'hover_color'        => [ $selectors['hover_color'] => 'color: {{VALUE}};' ],
			'hover_bg_color'     => [ $selectors['hover_bg_color'] => 'background-color: {{VALUE}} !important;' ],
			'border'             => $selectors['border'],
			'border_hover_color' => [ $selectors['border_hover_color'] => 'border-color: {{VALUE}};' ],
			'border_radius'      => [ $selectors['border_radius'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ],
			'padding'            => [ $selectors['padding'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ],
			'margin'             => [ $selectors['margin'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		];
	}
}
