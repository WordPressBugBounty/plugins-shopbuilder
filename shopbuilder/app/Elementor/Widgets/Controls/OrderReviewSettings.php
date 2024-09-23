<?php
/**
 * Main ProductDescription class.
 *
 * @package RadiusTheme\SB
 */

namespace RadiusTheme\SB\Elementor\Widgets\Controls;

use Elementor\Controls_Manager;
use RadiusTheme\SB\Helpers\Fns;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Product Description class
 */
class OrderReviewSettings {
	/**
	 * Widget Control Selectors
	 *
	 * @var object
	 */
	private static $widget = [];
	/**
	 * Widget Control Selectors
	 *
	 * @var array
	 */
	private static $selectors = [];

	/**
	 * Controls Settings
	 *
	 * @param object $widget widget object.
	 *
	 * @return array
	 */
	public static function widget_fields( $widget ) {
		self::$widget    = $widget;
		self::$selectors = self::$widget->selectors;

		return self::general_settings() + TitleSettings::title_settings( self::$widget ) + self::review_tables();
	}

	/**
	 * Widget Field
	 *
	 * @return array
	 */
	public static function general_settings() {
		// The function will Overwrite.
		$fields = TitleSettings::general_settings();

		$new_fields['table_horizontal_scroll_on_mobile'] = [
			'label'       => esc_html__( 'Show default Table View On Mobile?', 'shopbuilder' ),
			'type'        => 'switch',
			'description' => esc_html__( 'Show default Table View On Mobile?', 'shopbuilder' ),
			'default'     => '',
			'separator'   => rtsb()->has_pro() ? 'default' : 'before-short',
		];
		$new_fields['table_min_width']                   = [
			'mode'        => 'responsive',
			'label'       => esc_html__( 'Table Minimum Width', 'shopbuilder' ),
			'description' => esc_html__( 'Enter table min-width (in px),', 'shopbuilder' ),
			'type'        => 'slider',
			'size_units'  => [ 'px' ],
			'range'       => [
				'px' => [
					'min'  => 0,
					'max'  => 1000,
					'step' => 5,
				],
			],
			'condition'   => [
				'table_horizontal_scroll_on_mobile' => 'yes',
			],
			'selectors'   => [
				self::$selectors['table_min_width'] => 'min-width: {{SIZE}}{{UNIT}};',
			],
		];

		return Fns::insert_controls( 'show_title', $fields, $new_fields, true );
	}

	/**
	 * Widget Field
	 *
	 * @return array
	 */
	public static function review_tables(): array {
		// The function will Overwrite.
		return [
			'review_table_start'          => [
				'mode'  => 'section_start',
				'tab'   => 'style',
				'label' => esc_html__( 'Review Table', 'shopbuilder' ),
			],

			'table_wrapper_border'        => [
				'mode'           => 'group',
				'type'           => 'border',
				'selector'       => self::$selectors['table_wrapper_border'],
				'size_units'     => [ 'px' ],
				'separator'      => 'default',
				'fields_options' => [
					'border' => [
						'label'       => esc_html__( 'Table Wrapper Border', 'shopbuilder' ),
						'label_block' => true,
						'default'     => 'solid',
					],
					'width'  => [
						'default' => [
							'top'      => '0',
							'right'    => '0',
							'bottom'   => '0',
							'left'     => '0',
							'isLinked' => true,
						],
					],
					'color'  => [
						'label' => esc_html__( 'Border Color', 'shopbuilder' ),
					],
				],
			],

			'table_border'                => [
				'mode'           => 'group',
				'type'           => 'border',
				'selector'       => self::$selectors['table_border'],
				'size_units'     => [ 'px' ],
				'separator'      => 'default',
				'fields_options' => [
					'border' => [
						'label'       => esc_html__( 'Table Cell Border', 'shopbuilder' ),
						'label_block' => true,
					],
					'color'  => [
						'label' => esc_html__( 'Border Color', 'shopbuilder' ),
					],
				],
			],

			'table_wrapper_padding'       => [
				'label'      => esc_html__( 'Wrapper Padding (px)', 'shopbuilder' ),
				'type'       => 'dimensions',
				'mode'       => 'responsive',
				'size_units' => [ 'px' ],
				'default'    => [
					'top'      => '0',
					'right'    => '0',
					'bottom'   => '0',
					'left'     => '0',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'  => [
					self::$selectors['table_wrapper_padding'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			],
			'table_wrapper_margin'        => [
				'label'      => esc_html__( 'Wrapper Margin (px)', 'shopbuilder' ),
				'type'       => 'dimensions',
				'mode'       => 'responsive',
				'size_units' => [ 'px' ],
				'selectors'  => [
					self::$selectors['table_wrapper_margin'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			],

			'table_label'                 => [
				'type'            => 'html',
				'raw'             => sprintf(
					'<h3 class="rtsb-elementor-group-heading">%s</h3>',
					esc_html__( 'Label', 'shopbuilder' )
				),
				'content_classes' => 'elementor-panel-heading-title',
				'separator'       => 'default',
			],
			'label_typo'                  => [
				'mode'     => 'group',
				'type'     => 'typography',
				'label'    => esc_html__( 'Label Typography', 'shopbuilder' ),
				'selector' => self::$selectors['label_typo'],
			],
			'label_color'                 => [
				'label'     => esc_html__( 'Label Color', 'shopbuilder' ),
				'type'      => 'color',
				'selectors' => [
					self::$selectors['label_color'] => 'color: {{VALUE}} !important;',
				],
			],
			'label_item_padding'          => [
				'label'      => esc_html__( 'Item Padding (px)', 'shopbuilder' ),
				'type'       => 'dimensions',
				'mode'       => 'responsive',
				'size_units' => [ 'px' ],
				'selectors'  => [
					self::$selectors['label_item_padding'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			],

			'label_bg_color'              => [
				'label'     => esc_html__( 'Background Color', 'shopbuilder' ),
				'type'      => 'color',
				'alpha'     => true,
				'selectors' => [
					self::$selectors['label_bg_color'] => 'background-color: {{VALUE}};',
				],
			],
			'table_body'                  => [
				'type'            => 'html',
				'raw'             => sprintf(
					'<h3 class="rtsb-elementor-group-heading">%s</h3>',
					esc_html__( 'Table Body', 'shopbuilder' )
				),
				'content_classes' => 'elementor-panel-heading-title',
				'separator'       => 'default',
			],
			'body_text_typo'              => [
				'mode'     => 'group',
				'type'     => 'typography',
				'label'    => esc_html__( 'Typography', 'shopbuilder' ),
				'selector' => self::$selectors['body_text_typo'],
			],
			'table_text_color'            => [
				'label'     => esc_html__( 'Body Text Color', 'shopbuilder' ),
				'type'      => 'color',
				'selectors' => [
					self::$selectors['body_text_color'] => 'color: {{VALUE}} !important;',
				],
			],
			'table_body_bg_color'         => [
				'label'     => esc_html__( 'Table Body Background Color', 'shopbuilder' ),
				'type'      => 'color',
				'alpha'     => true,
				'selectors' => [
					self::$selectors['table_body_bg_color'] => 'background-color: {{VALUE}};',
				],
			],
			'body_item_padding'           => [
				'label'      => esc_html__( 'Item Padding (px)', 'shopbuilder' ),
				'type'       => 'dimensions',
				'mode'       => 'responsive',
				'size_units' => [ 'px' ],
				'selectors'  => [
					self::$selectors['body_item_padding'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			],
			'counter_text_color'          => [
				'label'     => esc_html__( 'Counter Text Color', 'shopbuilder' ),
				'type'      => 'color',
				'selectors' => [
					self::$selectors['counter_text_color'] => 'color: {{VALUE}} !important;',
				],
			],
			'counter_style'               => [
				'label'     => esc_html__( 'Counter Style', 'shopbuilder' ),
				'type'      => 'choose',
				'default'   => 'column',
				'options'   => [
					'auto' => [
						'title' => esc_html__( 'Inline', 'shopbuilder' ),
						'icon'  => 'eicon-ellipsis-h',
					],
					'100%' => [
						'title' => esc_html__( 'New Line', 'shopbuilder' ),
						'icon'  => 'eicon-editor-list-ul',
					],
				],
				'selectors' => [
					self::$selectors['counter_style'] => 'width:{{VALUE}};',
				],
			],
			'table_footer'                => [
				'type'            => 'html',
				'raw'             => sprintf(
					'<h3 class="rtsb-elementor-group-heading">%s</h3>',
					esc_html__( 'Table Footer', 'shopbuilder' )
				),
				'content_classes' => 'elementor-panel-heading-title',
				'separator'       => 'default',
			],
			'table_footer_label_typo'     => [
				'mode'     => 'group',
				'type'     => 'typography',
				'label'    => esc_html__( 'Footer Typography', 'shopbuilder' ),
				'selector' => self::$selectors['table_footer_label_typo'],
			],
			'table_footer_label_color'    => [
				'label'     => esc_html__( 'Label Color', 'shopbuilder' ),
				'type'      => 'color',
				'selectors' => [
					self::$selectors['table_footer_label_color'] => 'color: {{VALUE}} !important;',
				],
			],

			'table_footer_text_color'     => [
				'label'     => esc_html__( 'Value Color', 'shopbuilder' ),
				'type'      => 'color',
				'selectors' => [
					self::$selectors['table_footer_text_color'] => 'color: {{VALUE}} !important;',
				],
			],
			'table_footer_label_bg_color' => [
				'label'     => esc_html__( 'Footer Background Color', 'shopbuilder' ),
				'type'      => 'color',
				'alpha'     => true,
				'selectors' => [
					self::$selectors['table_footer_label_bg_color'] => 'background-color: {{VALUE}};',
				],
			],
			'table_footer_item_padding'   => [
				'label'      => esc_html__( 'Item Padding (px)', 'shopbuilder' ),
				'type'       => 'dimensions',
				'mode'       => 'responsive',
				'size_units' => [ 'px' ],
				'selectors'  => [
					self::$selectors['table_footer_item_padding'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			],
			'review_table_end'            => [
				'mode' => 'section_end',
			],
		];
	}
}
