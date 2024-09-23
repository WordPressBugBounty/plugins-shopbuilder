<?php
/**
 * BuilderFns class
 *
 * The  builder.
 *
 * @package  RadiusTheme\SB
 * @since    1.0.0
 */

namespace RadiusTheme\SB\Elementor\Helper;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * BuilderFns class
 */
class ControlHelper {
	/**
	 * Widget alignment.
	 *
	 * @return array
	 */
	public static function alignment() {
		return [
			'left'    => [
				'title' => esc_html__( 'Left', 'shopbuilder' ),
				'icon'  => 'eicon-text-align-left',
			],
			'center'  => [
				'title' => esc_html__( 'Center', 'shopbuilder' ),
				'icon'  => 'eicon-text-align-center',
			],
			'right'   => [
				'title' => esc_html__( 'Right', 'shopbuilder' ),
				'icon'  => 'eicon-text-align-right',
			],
			'justify' => [
				'title' => esc_html__( 'Justified', 'shopbuilder' ),
				'icon'  => 'eicon-text-align-justify',
			],
		];
	}
	/**
	 * Widget alignment.
	 *
	 * @return array
	 */
	public static function flex_alignment() {
		return [
			'start'  => [
				'title' => esc_html__( 'Start', 'shopbuilder' ),
				'icon'  => 'eicon-text-align-left',
			],
			'center' => [
				'title' => esc_html__( 'Center', 'shopbuilder' ),
				'icon'  => 'eicon-text-align-center',
			],
			'end'    => [
				'title' => esc_html__( 'End', 'shopbuilder' ),
				'icon'  => 'eicon-text-align-right',
			],
		];
	}
	/**
	 * Grid Layout Columns
	 *
	 * @return array
	 */
	public static function layout_columns() {
		return [
			0 => esc_html__( 'Layout Default', 'shopbuilder' ),
			1 => esc_html__( '1 Column', 'shopbuilder' ),
			2 => esc_html__( '2 Columns', 'shopbuilder' ),
			3 => esc_html__( '3 Columns', 'shopbuilder' ),
			4 => esc_html__( '4 Columns', 'shopbuilder' ),
			5 => esc_html__( '5 Columns', 'shopbuilder' ),
			6 => esc_html__( '6 Columns', 'shopbuilder' ),
			7 => esc_html__( '7 Columns', 'shopbuilder' ),
			8 => esc_html__( '8 Columns', 'shopbuilder' ),
		];
	}

	/**
	 * Grid Layouts
	 *
	 * @return array
	 */
	public static function grid_layouts() {
		$status = ! rtsb()->has_pro();

		return apply_filters(
			'rtsb/elements/elementor/grid_layouts',
			[
				'grid-layout1' => [
					'title' => esc_html__( 'Grid Layout 1', 'shopbuilder' ),
					'url'   => esc_url( rtsb()->get_assets_uri( 'images/layout/grid-Layout-1.png' ) ),
				],

				'grid-layout2' => [
					'title' => esc_html__( 'Grid Layout 2', 'shopbuilder' ),
					'url'   => esc_url( rtsb()->get_assets_uri( 'images/layout/grid-Layout-2.png' ) ),
				],

				'grid-layout3' => [
					'title'  => esc_html__( 'Grid Layout 3', 'shopbuilder' ),
					'url'    => esc_url( rtsb()->get_assets_uri( 'images/layout/grid-style-3.png' ) ),
					'is_pro' => $status,
				],
				'grid-layout4' => [
					'title'  => esc_html__( 'Grid Layout 4', 'shopbuilder' ),
					'url'    => esc_url( rtsb()->get_assets_uri( 'images/layout/grid-style-4.png' ) ),
					'is_pro' => $status,
				],
				'grid-layout5' => [
					'title'  => esc_html__( 'Grid Layout 5', 'shopbuilder' ),
					'url'    => esc_url( rtsb()->get_assets_uri( 'images/layout/grid-style-5.png' ) ),
					'is_pro' => $status,
				],
				'grid-layout6' => [
					'title'  => esc_html__( 'Grid Layout 6', 'shopbuilder' ),
					'url'    => esc_url( rtsb()->get_assets_uri( 'images/layout/grid-style-6.png' ) ),
					'is_pro' => $status,
				],
				'grid-layout7' => [
					'title'  => esc_html__( 'Grid Layout 7', 'shopbuilder' ),
					'url'    => esc_url( rtsb()->get_assets_uri( 'images/layout/grid-style-7.png' ) ),
					'is_pro' => $status,
				],
				'grid-layout8' => [
					'title'  => esc_html__( 'Grid Layout 8', 'shopbuilder' ),
					'url'    => esc_url( rtsb()->get_assets_uri( 'images/layout/grid-style-8.png' ) ),
					'is_pro' => $status,
				],
				'grid-layout9' => [
					'title'  => esc_html__( 'Special Layout 1', 'shopbuilder' ),
					'url'    => esc_url( rtsb()->get_assets_uri( 'images/layout/grid-style-9a.png' ) ),
					'is_pro' => $status,
				],
			]
		);
	}

	/**
	 * Single Product Tab Layouts
	 *
	 * @return array
	 */
	public static function single_product_tab_layouts() {
		$status = ! rtsb()->has_pro();

		return apply_filters(
			'rtsb/elements/elementor/single_product_tab_layouts',
			[
				'default'        => [
					'title' => esc_html__( 'Default Layout', 'shopbuilder' ),
					'url'   => esc_url( rtsb()->get_assets_uri( 'images/layout/tab-01.png' ) ),
				],
				'custom-layout1' => [
					'title'  => esc_html__( 'Accordion Layout', 'shopbuilder' ),
					'url'    => esc_url( rtsb()->get_assets_uri( 'images/layout/tab-02.png' ) ),
					'is_pro' => $status,
				],
				'custom-layout2' => [
					'title'  => esc_html__( 'Tab Layout', 'shopbuilder' ),
					'url'    => esc_url( rtsb()->get_assets_uri( 'images/layout/tab-03.png' ) ),
					'is_pro' => $status,
				],
			]
		);
	}

	/**
	 * Multi Step Checkout Layouts
	 *
	 * @return array
	 */
	public static function multi_step_checkout_layouts() {
		$status = ! rtsb()->has_pro();

		return apply_filters(
			'rtsb/elements/elementor/single_product_tab_layouts',
			[
				'layout1'        => [
					'title' => esc_html__( 'Layout 1', 'shopbuilder' ),
					'url'   => esc_url( rtsb()->get_assets_uri( 'images/layout/tab-01.png' ) ),
				],

			]
		);
	}

	/**
	 * Single Advanced Product Tab Layouts
	 *
	 * @return array
	 */
	public static function single_advanced_product_tab_layouts() {
		// TODO:: Check If the FUnction Used Or Not.
		return apply_filters(
			'rtsb/elements/elementor/single_advanced_product_tab_layouts',
			[
				'default'        => [
					'title' => esc_html__( 'Default Layout', 'shopbuilder' ),
					'url'   => esc_url( rtsb()->get_assets_uri( 'images/layout/tab-01.png' ) ),
				],
				'custom-layout1' => [
					'title' => esc_html__( 'Accordion Layout', 'shopbuilder' ),
					'url'   => esc_url( rtsb()->get_assets_uri( 'images/layout/tab-02.png' ) ),
				],
				'custom-layout2' => [
					'title' => esc_html__( 'Tab Layout', 'shopbuilder' ),
					'url'   => esc_url( rtsb()->get_assets_uri( 'images/layout/tab-03.png' ) ),
				],
			]
		);
	}

	/**
	 * Slider Layouts
	 *
	 * @return array
	 */
	public static function slider_layouts() {
		$status = ! rtsb()->has_pro();

		return apply_filters(
			'rtsb/elements/elementor/slider_layouts',
			[
				'slider-layout1' => [
					'title' => esc_html__( 'Slider Layout 1', 'shopbuilder' ),
					'url'   => esc_url( rtsb()->get_assets_uri( 'images/layout/grid-Layout-1.png' ) ),
				],

				'slider-layout2' => [
					'title' => esc_html__( 'Slider Layout 2', 'shopbuilder' ),
					'url'   => esc_url( rtsb()->get_assets_uri( 'images/layout/grid-Layout-2.png' ) ),
				],

				'slider-layout3' => [
					'title'  => esc_html__( 'Slider Layout 3', 'shopbuilder' ),
					'url'    => esc_url( rtsb()->get_assets_uri( 'images/layout/slider-style-3.png' ) ),
					'is_pro' => $status,
				],

				'slider-layout4' => [
					'title'  => esc_html__( 'Slider Layout 4', 'shopbuilder' ),
					'url'    => esc_url( rtsb()->get_assets_uri( 'images/layout/slider-style-4.png' ) ),
					'is_pro' => $status,
				],
				'slider-layout5' => [
					'title'  => esc_html__( 'Slider Layout 5', 'shopbuilder' ),
					'url'    => esc_url( rtsb()->get_assets_uri( 'images/layout/slider-style-5.png' ) ),
					'is_pro' => $status,
				],
				'slider-layout6' => [
					'title'  => esc_html__( 'Slider Layout 6', 'shopbuilder' ),
					'url'    => esc_url( rtsb()->get_assets_uri( 'images/layout/slider-style-6.png' ) ),
					'is_pro' => $status,
				],
				'slider-layout7' => [
					'title'  => esc_html__( 'Slider Layout 7', 'shopbuilder' ),
					'url'    => esc_url( rtsb()->get_assets_uri( 'images/layout/slider-style-7.png' ) ),
					'is_pro' => $status,
				],
				'slider-layout8' => [
					'title'  => esc_html__( 'Slider Layout 8', 'shopbuilder' ),
					'url'    => esc_url( rtsb()->get_assets_uri( 'images/layout/list-style-6.png' ) ),
					'is_pro' => $status,
				],
				'slider-layout9' => [
					'title'  => esc_html__( 'Slider Layout 9', 'shopbuilder' ),
					'url'    => esc_url( rtsb()->get_assets_uri( 'images/layout/grid-style-8.png' ) ),
					'is_pro' => $status,
				],
			]
		);
	}

	/**
	 * List Layouts
	 *
	 * @return array
	 */
	public static function list_layouts() {
		$status = ! rtsb()->has_pro();

		return apply_filters(
			'rtsb/elements/elementor/list_layouts',
			[
				'list-layout1' => [
					'title' => esc_html__( 'List Layout 1', 'shopbuilder' ),
					'url'   => esc_url( rtsb()->get_assets_uri( 'images/layout/list-Layout-1.png' ) ),
				],
				'list-layout2' => [
					'title' => esc_html__( 'List Layout 2', 'shopbuilder' ),
					'url'   => esc_url( rtsb()->get_assets_uri( 'images/layout/list-Layout-2.png' ) ),
				],
				'list-layout3' => [
					'title'  => esc_html__( 'List Layout 3', 'shopbuilder' ),
					'url'    => esc_url( rtsb()->get_assets_uri( 'images/layout/list-style-3.png' ) ),
					'is_pro' => $status,
				],
				'list-layout4' => [
					'title'  => esc_html__( 'List Layout 4', 'shopbuilder' ),
					'url'    => esc_url( rtsb()->get_assets_uri( 'images/layout/list-style-4.png' ) ),
					'is_pro' => $status,
				],
				'list-layout5' => [
					'title'  => esc_html__( 'List Layout 5', 'shopbuilder' ),
					'url'    => esc_url( rtsb()->get_assets_uri( 'images/layout/list-style-5.png' ) ),
					'is_pro' => $status,
				],
				'list-layout6' => [
					'title'  => esc_html__( 'List Layout 6', 'shopbuilder' ),
					'url'    => esc_url( rtsb()->get_assets_uri( 'images/layout/list-style-6.png' ) ),
					'is_pro' => $status,
				],
				'list-layout7' => [
					'title'  => esc_html__( 'List Layout 7', 'shopbuilder' ),
					'url'    => esc_url( rtsb()->get_assets_uri( 'images/layout/list-style-7.png' ) ),
					'is_pro' => $status,
				],
			]
		);
	}

	/**
	 * Category Layouts
	 *
	 * @return array
	 */
	public static function category_layouts() {
		$status = ! rtsb()->has_pro();

		return apply_filters(
			'rtsb/elements/elementor/category_layouts',
			[
				'category-layout1' => [
					'title' => esc_html__( 'Category Layout 1', 'shopbuilder' ),
					'url'   => esc_url( rtsb()->get_assets_uri( 'images/layout/categories-1.png' ) ),
				],

				'category-layout2' => [
					'title' => esc_html__( 'Category Layout 2', 'shopbuilder' ),
					'url'   => esc_url( rtsb()->get_assets_uri( 'images/layout/categories-2.png' ) ),
				],
				'category-layout3' => [
					'title'  => esc_html__( 'Category Layout 3', 'shopbuilder' ),
					'url'    => esc_url( rtsb()->get_assets_uri( 'images/layout/categories-3.png' ) ),
					'is_pro' => $status,
				],
				'category-layout4' => [
					'title'  => esc_html__( 'Category Layout 4', 'shopbuilder' ),
					'url'    => esc_url( rtsb()->get_assets_uri( 'images/layout/categories-3.png' ) ),
					'is_pro' => $status,
				],

			]
		);
	}

	/**
	 * Category Layouts
	 *
	 * @return array
	 */
	public static function share_layouts() {
		$status = ! rtsb()->has_pro();

		return apply_filters(
			'rtsb/elements/elementor/share_layouts',
			[
				'share-layout1' => [
					'title' => esc_html__( 'Social Share Preset 1', 'shopbuilder' ),
					'url'   => esc_url( rtsb()->get_assets_uri( 'images/layout/share-preset-1.png' ) ),
				],

				'share-layout2' => [
					'title' => esc_html__( 'Social Share Preset 2', 'shopbuilder' ),
					'url'   => esc_url( rtsb()->get_assets_uri( 'images/layout/share-preset-2.png' ) ),
				],

				// TODO: Will be activated later.
				// 'share-layout3' => [
				// 'title'  => esc_html__( 'Social Share Preset 3', 'shopbuilder' ),
				// 'url'    => esc_url( rtsb()->get_assets_uri( 'images/layout/share-preset-1.png' ) ),
				// 'is_pro' => $status,
				// ],
			]
		);
	}

	/**
	 * Action Button Presets
	 *
	 * @return array
	 */
	public static function action_btn_presets() {

		return apply_filters(
			'rtsb/elements/elementor/action_btn_presets',
			[
				'preset1' => [
					'title' => esc_html__( 'Preset 1', 'shopbuilder' ),
					'url'   => esc_url( rtsb()->get_assets_uri( 'images/layout/action-preset-1.jpg' ) ),
				],

				'preset2' => [
					'title' => esc_html__( 'Preset 2', 'shopbuilder' ),
					'url'   => esc_url( rtsb()->get_assets_uri( 'images/layout/action-preset-2.jpg' ) ),
				],

				'preset3' => [
					'title' => esc_html__( 'Preset 3', 'shopbuilder' ),
					'url'   => esc_url( rtsb()->get_assets_uri( 'images/layout/action-preset-3.jpg' ) ),
				],
				'preset4' => [
					'title' => esc_html__( 'Preset 4', 'shopbuilder' ),
					'url'   => esc_url( rtsb()->get_assets_uri( 'images/layout/action-preset-4.jpg' ) ),
				],
				'preset5' => [
					'title'  => esc_html__( 'Preset 5', 'shopbuilder' ),
					'url'    => esc_url( rtsb()->get_assets_uri( 'images/layout/action-preset-5.jpg' ) ),
					'is_pro' => ! rtsb()->has_pro(),
				],
				'preset6' => [
					'title'  => esc_html__( 'Preset 6', 'shopbuilder' ),
					'url'    => esc_url( rtsb()->get_assets_uri( 'images/layout/action-preset-6.jpg' ) ),
					'is_pro' => ! rtsb()->has_pro(),
				],
			]
		);
	}

	/**
	 * Badge Presets
	 *
	 * @return array
	 */
	public static function badge_presets() {
		$status = ! rtsb()->has_pro();

		return apply_filters(
			'rtsb/elements/elementor/badge_presets',
			[
				'preset1' => [
					'title' => esc_html__( 'Preset 1', 'shopbuilder' ),
					'url'   => esc_url( rtsb()->get_assets_uri( 'images/layout/badge-preset-1.png' ) ),
				],

				'preset2' => [
					'title' => esc_html__( 'Preset 2', 'shopbuilder' ),
					'url'   => esc_url( rtsb()->get_assets_uri( 'images/layout/badge-preset-2.png' ) ),
				],

				'preset3' => [
					'title' => esc_html__( 'Preset 3', 'shopbuilder' ),
					'url'   => esc_url( rtsb()->get_assets_uri( 'images/layout/badge-preset-3.png' ) ),
				],

				'preset4' => [
					'title' => esc_html__( 'Preset 4', 'shopbuilder' ),
					'url'   => esc_url( rtsb()->get_assets_uri( 'images/layout/badge-preset-4.png' ) ),
				],
			]
		);
	}

	/**
	 * Single Category Layouts
	 *
	 * @return array
	 */
	public static function single_category_layouts() {
		$status = ! rtsb()->has_pro();

		return apply_filters(
			'rtsb/elements/elementor/category_single_layouts',
			[
				'category-single-layout1' => [
					'title' => esc_html__( 'Single Category Layout 1', 'shopbuilder' ),
					'url'   => esc_url( rtsb()->get_assets_uri( 'images/layout/single-category-1.png' ) ),
				],

				'category-single-layout2' => [
					'title'  => esc_html__( 'Single Category Layout 2', 'shopbuilder' ),
					'url'    => esc_url( rtsb()->get_assets_uri( 'images/layout/single-category-2.png' ) ),
					'is_pro' => $status,
				],
			]
		);
	}

	/**
	 * Posts Order By.
	 *
	 * @return array
	 */
	public static function posts_order_by() {
		return [
			'ID'         => esc_html__( 'Product ID', 'shopbuilder' ),
			'title'      => esc_html__( 'Product Title', 'shopbuilder' ),
			'price'      => esc_html__( 'Product Price', 'shopbuilder' ),
			'date'       => esc_html__( 'Date', 'shopbuilder' ),
			'menu_order' => esc_html__( 'Menu Order', 'shopbuilder' ),
			'rand'       => esc_html__( 'Random', 'shopbuilder' ),
		];
	}

	/**
	 * Categories Order By.
	 *
	 * @return array
	 */
	public static function cats_order_by() {
		return [
			'id'         => esc_html__( 'Category IDs', 'shopbuilder' ),
			'name'       => esc_html__( 'Category Name', 'shopbuilder' ),
			'count'      => esc_html__( 'Product Count', 'shopbuilder' ),
			'menu_order' => esc_html__( 'Menu Order', 'shopbuilder' ),
		];
	}

	/**
	 * Posts Order By.
	 *
	 * @return array
	 */
	public static function posts_order() {
		return [
			'ASC'  => esc_html__( 'Ascending', 'shopbuilder' ),
			'DESC' => esc_html__( 'Descending', 'shopbuilder' ),
		];
	}

	/**
	 * Filter products
	 *
	 * @return array
	 */
	public static function filter_products() {
		return [
			'recent'          => esc_html__( 'Default', 'shopbuilder' ),
			'featured'        => esc_html__( 'Featured Products', 'shopbuilder' ),
			'best-selling'    => esc_html__( 'Best Selling Products', 'shopbuilder' ),
			'sale'            => esc_html__( 'On Sale Products', 'shopbuilder' ),
			'top-rated'       => esc_html__( 'Top Rated Products', 'shopbuilder' ),
			'recently-viewed' => esc_html__( 'Recently Viewed Products', 'shopbuilder' ),
		];
	}

	/**
	 * Pagination options
	 *
	 * @return array
	 */
	public static function pagination_options() {
		return apply_filters(
			'rtsb/elementor/pagination_options',
			[
				'pagination' => esc_html__( 'Number Pagination', 'shopbuilder' ),
			]
		);
	}

	/**
	 * Heading Tags
	 *
	 * @return array
	 */
	public static function heading_tags() {
		return [
			'h1'   => esc_html__( 'H1', 'shopbuilder' ),
			'h2'   => esc_html__( 'H2', 'shopbuilder' ),
			'h3'   => esc_html__( 'H3', 'shopbuilder' ),
			'h4'   => esc_html__( 'H4', 'shopbuilder' ),
			'h5'   => esc_html__( 'H5', 'shopbuilder' ),
			'h6'   => esc_html__( 'H6', 'shopbuilder' ),
			'p'    => esc_html__( 'p', 'shopbuilder' ),
			'div'  => esc_html__( 'div', 'shopbuilder' ),
			'span' => esc_html__( 'span', 'shopbuilder' ),
		];
	}

	/**
	 * General Style Section
	 *
	 * @param string $id_prefix Section id prefix.
	 * @param string $title Section title.
	 * @param object $obj Reference object.
	 * @param array  $condition Condition.
	 * @param array  $selectors CSS electors.
	 * @param array  $conditions Conditions.
	 *
	 * @return array
	 */
	public static function general_elementor_style( $id_prefix, $title, $obj, $condition, $selectors, $conditions = [] ) {
		$prefix = str_replace( ' ', '_', strtolower( $id_prefix ) ) . '_';

		$fields[ $prefix . 'style_section' ] = $obj->start_section(
			esc_html( $title ),
			'style',
			$conditions,
			$condition
		);

		$fields[ $prefix . 'typo_note' ] = $obj->el_heading( esc_html__( 'Typography', 'shopbuilder' ), 'default' );

		$fields[ 'rtsb_el_' . $prefix . 'typography' ] = [
			'mode'     => 'group',
			'type'     => 'typography',
			'selector' => ! empty( $selectors['typography'] ) ? $selectors['typography'] : [],
		];

		$fields[ $prefix . 'alignment' ] = [
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
			'selectors' => ! empty( $selectors['alignment'] ) ? $selectors['alignment'] : [],
		];

		$fields[ $prefix . 'color_note' ] = $obj->el_heading( esc_html__( 'Colors', 'shopbuilder' ), 'before' );

		$fields[ $prefix . 'color_tabs' ] = $obj->start_tab_group();
		$fields[ $prefix . 'color_tab' ]  = $obj->start_tab( esc_html__( 'Normal', 'shopbuilder' ) );

		$fields[ $prefix . 'color' ] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Color', 'shopbuilder' ),
			'selectors' => ! empty( $selectors['color'] ) ? $selectors['color'] : [],
			'separator' => 'default',
		];

		$fields[ $prefix . 'bg_color' ] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Background Color', 'shopbuilder' ),
			'selectors' => ! empty( $selectors['bg_color'] ) ? $selectors['bg_color'] : [],
			'separator' => 'default',
		];

		$fields[ $prefix . 'color_tab_end' ]   = $obj->end_tab();
		$fields[ $prefix . 'hover_color_tab' ] = $obj->start_tab( esc_html__( 'Hover', 'shopbuilder' ) );

		$fields[ $prefix . 'hover_color' ] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Hover Color', 'shopbuilder' ),
			'selectors' => ! empty( $selectors['hover_color'] ) ? $selectors['hover_color'] : [],
			'separator' => 'default',
		];

		$fields[ $prefix . 'hover_bg_color' ] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Hover Background Color', 'shopbuilder' ),
			'selectors' => ! empty( $selectors['hover_bg_color'] ) ? $selectors['hover_bg_color'] : [],
			'separator' => 'default',
		];

		$fields[ $prefix . 'hover_color_tab_end' ] = $obj->end_tab();
		$fields[ $prefix . 'color_tabs_end' ]      = $obj->end_tab_group();

		$fields[ $prefix . 'border_note' ] = $obj->el_heading( esc_html__( 'Border', 'shopbuilder' ), 'before' );

		$fields[ 'rtsb_el_' . $prefix . 'border' ] = [
			'mode'           => 'group',
			'type'           => 'border',
			'label'          => esc_html__( 'Border', 'shopbuilder' ),
			'selector'       => ! empty( $selectors['border'] ) ? $selectors['border'] : [],
			'fields_options' => [
				'color' => [
					'label' => esc_html__( 'Border Color', 'shopbuilder' ),
				],
			],
			'separator'      => 'default',
		];

		$fields[ $prefix . 'border_hover_color' ] = [
			'type'      => 'color',
			'label'     => esc_html__( 'Hover Border Color', 'shopbuilder' ),
			'condition' => [ 'rtsb_el_' . $prefix . 'border_border!' => [ '' ] ],
			'selectors' => ! empty( $selectors['border_hover_color'] ) ? $selectors['border_hover_color'] : [],
			'separator' => 'default',
		];

		$fields[ $prefix . 'spacing_note' ] = $obj->el_heading( esc_html__( 'Spacing', 'shopbuilder' ), 'before' );

		$fields[ $prefix . 'padding' ] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'label'      => esc_html__( 'Padding', 'shopbuilder' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => ! empty( $selectors['padding'] ) ? $selectors['padding'] : [],
			'separator'  => 'default',
		];

		$fields[ $prefix . 'margin' ] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'label'      => esc_html__( 'Margin', 'shopbuilder' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => ! empty( $selectors['margin'] ) ? $selectors['margin'] : [],
		];

		$fields[ $prefix . 'style_section_end' ] = $obj->end_section();

		return $fields;
	}

	/**
	 * Sharing Settings.
	 *
	 * @return array
	 */
	public static function sharing_settings() {
		$settings       = get_option( 'rtsb_settings' );
		$share_settings = ! empty( $settings['general']['social_share']['share_platforms'] ) ? $settings['general']['social_share']['share_platforms'] : [];

		return ! empty( $share_settings ) && is_array( $share_settings ) ? $share_settings : [ 'facebook', 'twitter' ];
	}
}
