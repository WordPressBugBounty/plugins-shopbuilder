<?php
/**
 *  WishlistSettings class.
 *
 * @package RadiusTheme\SB
 */

namespace RadiusTheme\SB\Elementor\Widgets\Controls;

use Elementor\Controls_Manager;
use RadiusTheme\SB\Elementor\Helper\ControlHelper;
use RadiusTheme\SB\Helpers\Fns;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Product Description class
 */
class WishlistSettings {
	/**
	 * Widget Field
	 *
	 * @return array
	 */
	public static function settings_field( $widget ) {
		$fields       = TableSettings::table_settings( $widget );
		$insert_array = [
			'header_cell_width' => [
				'label'     => esc_html__( 'Cell Width', 'shopbuilder' ),
				'type'      => 'slider',
				'separator' => 'default',
				'range'     => [
					'px' => [
						'min' => 200,
						'max' => 500,
					],
				],
				'selectors' => [
					$widget->selectors['header_cell_width'] => 'min-width: {{SIZE}}{{UNIT}};',
				],
			],
		];
		$fields       = Fns::insert_controls( 'table_header_bg_color', $fields, $insert_array, true );
		$insert_array = [
			'item_cell_width' => [
				'label'     => esc_html__( 'Cell Width', 'shopbuilder' ),
				'type'      => 'slider',
				'separator' => 'default',
				'range'     => [
					'px' => [
						'min' => 200,
						'max' => 500,
					],
				],
				'selectors' => [
					$widget->selectors['item_cell_width'] => 'min-width: {{SIZE}}{{UNIT}};',
				],
			],
		];
		$fields       = Fns::insert_controls( 'table_item_bg_color', $fields, $insert_array, true );
		return $fields;
	}
}
