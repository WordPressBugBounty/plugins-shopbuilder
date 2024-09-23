<?php

namespace RadiusTheme\SB\Resources;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

class Options {

	/**
	 * @return array
	 */
	public static function settings_options() {
		$options = [
			'general'    => [],
			'gutenberg'  => [],
			'elements'   => [],
			'modules'    => [
				'id'          => 'modules',
				'title'       => esc_html__( 'Modules', 'shopbuilder' ),
				'description' => esc_html__( 'Modules', 'shopbuilder' ),
				'items'       => [
					'quick_view' => self::module_quick_view_options(),
					'compare'    => self::module_compare_options(),
					'wishlist'   => self::module_wishlist_options(),
				],
			],
			'extensions' => [],
		];

		return apply_filters( 'rtsb_settings_options', $options );
	}

	public static function module_quick_view_options() {
		$options = [
			'id'          => 'quick_view',
			'title'       => esc_html__( 'Quick View', 'shopbuilder' ),
			'has_options' => true,
		];

		return apply_filters( 'rtsb_module_quick_view_options', $options );
	}

	public static function module_compare_options() {
		$options = [
			'id'          => 'compare',
			'title'       => esc_html__( 'Compare', 'shopbuilder' ),
			'has_options' => true,
		];

		return apply_filters( 'rtsb_module_compare_options', $options );
	}

	public static function module_wishlist_options() {
		$options = [
			'id'          => 'wishlist',
			'title'       => esc_html__( 'Wishlist', 'shopbuilder' ),
			'has_options' => true,
		];

		return apply_filters( 'rtsb_module_wishlist_options', $options );
	}
}
