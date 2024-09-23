<?php
/**
 * Elementor Render Helper Class.
 *
 * This class contains render helper logics.
 *
 * @package RadiusTheme\SB
 */

namespace RadiusTheme\SB\Elementor\Helper;

use RadiusTheme\SB\Helpers\BuilderFns;
use RadiusTheme\SB\Helpers\Fns;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * BuilderFns class
 */
class RenderHelpers {
	/**
	 * Get data.
	 *
	 * @param array|string $haystack The array or string to search for the value.
	 * @param string       $needle The key to look for in the array or the string itself.
	 * @param mixed        $default The default value to return if the key is not.
	 *
	 * @return mixed
	 */
	public static function get_data( $haystack, $needle, $default = null ) {
		if ( is_array( $haystack ) && ! empty( $haystack[ $needle ] ) ) {
			return $haystack[ $needle ];
		} elseif ( is_string( $haystack ) && ! empty( $haystack ) ) {
			return $haystack;
		} else {
			return $default;
		}
	}

	/**
	 * Builds an array with field values.
	 *
	 * @param array  $meta Field values.
	 * @param string $template Template name.
	 * @param array  $raw_settings Raw settings.
	 *
	 * @return array
	 */
	public static function meta_dataset( array $meta, $template = '', $raw_settings = [] ) {
		$c_image_size         = self::get_data( $meta, 'image_custom_dimension', [] );
		$c_image_size['crop'] = self::get_data( $meta, 'image_crop', [] );

		if ( ( ! empty( $_GET['displayview'] ) && 'list' === $_GET['displayview'] ) || ( empty( $_GET['displayview'] ) && ( ! empty( $meta['view_mode'] ) && 'list' === $meta['view_mode'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$meta['cols']        = self::get_data( $meta, 'list_cols', 1 );
			$meta['cols_tablet'] = self::get_data( $meta, 'list_cols_tablet', $meta['cols'] );
			$meta['cols_mobile'] = self::get_data( $meta, 'list_cols_mobile', $meta['cols'] );
		}

		$data = apply_filters(
			'rtsb/elementor/render/meta_dataset',
			[
				// Layout.
				'widget'                   => 'custom',
				'template'                 => self::get_data( $template, '', '' ),
				'raw_settings'             => $raw_settings,
				'layout'                   => self::get_data( $meta, 'layout', 'layout1' ),
				'grid_layout'              => self::get_data( $raw_settings, 'layout', 'grid-layout1' ),
				'list_layout'              => self::get_data( $raw_settings, 'list_layout', 'list-layout1' ),
				'd_cols'                   => self::get_data( $meta, 'cols', 0 ),
				't_cols'                   => self::get_data( $meta, 'cols_tablet', 2 ),
				'm_cols'                   => self::get_data( $meta, 'cols_mobile', 1 ),
				'grid_type'                => self::get_data( $meta, 'grid_style', 'even' ),

				// Query.
				'post_in'                  => self::get_data( $meta, 'include_posts', [] ),
				'post_not_in'              => self::get_data( $meta, 'exclude_posts', [] ),
				'limit'                    => ( empty( $meta['posts_limit'] ) || '-1' === $meta['posts_limit'] ) ? 10000000 : $meta['posts_limit'],
				'offset'                   => self::get_data( $meta, 'posts_offset', 0 ),
				'order_by'                 => self::get_data( $meta, 'posts_order_by', 'date' ),
				'order'                    => self::get_data( $meta, 'posts_order', 'DESC' ),
				'author_in'                => self::get_data( $meta, 'filter_author', [] ),
				'display_by'               => self::get_data( $meta, 'products_filter', 'date' ),
				'categories'               => self::get_data( $meta, 'filter_categories', [] ),
				'tags'                     => self::get_data( $meta, 'filter_tags', [] ),
				'attributes'               => self::get_data( $meta, 'filter_attributes', [] ),
				'relation'                 => self::get_data( $meta, 'tax_relation', 'OR' ),
				'category_source'          => self::get_data( $meta, 'select_source', 'product_cat' ),
				'category_term'            => self::get_data( $meta, 'select_cat' ),
				'display_cat_by'           => self::get_data( $meta, 'display_cat_by' ),
				'include_cats'             => self::get_data( $meta, 'include_cats', [] ),
				'exclude_cats'             => self::get_data( $meta, 'exclude_cats', [] ),
				'select_parent_cat'        => self::get_data( $meta, 'select_parent_cat' ),
				'select_cats'              => self::get_data( $meta, 'select_cats', [] ),
				'select_cat_ids'           => self::get_data( $meta, 'include_cat_ids', '' ),
				'cats_limit'               => ( empty( $meta['cats_limit'] ) || '-1' === $meta['cats_limit'] ) ? 10000000 : $meta['cats_limit'],
				'show_empty'               => ! empty( $meta['show_empty'] ),
				'show_uncategorized'       => ! empty( $meta['show_uncategorized'] ),
				'show_subcats'             => ! empty( $meta['show_subcats'] ),
				'show_top_level_cats'      => ! empty( $meta['show_top_level_cats'] ),
				'cats_order_by'            => self::get_data( $meta, 'cats_order_by', 'name' ),
				'cats_order'               => self::get_data( $meta, 'cats_order', 'DESC' ),
				'rtsb_order'               => self::get_data( $meta, 'rtsb_order', 'ASC' ),
				'rtsb_orderby'             => self::get_data( $meta, 'rtsb_orderby', 'menu_order' ),

				// Pagination.
				'pagination'               => ! empty( $meta['show_pagination'] ),
				'posts_loading_type'       => self::get_data( $meta, 'pagination_type', 'pagination' ),
				'posts_per_page'           => self::get_data( $meta, 'pagination_per_page', '' ),

				// Image.
				'f_img'                    => ! empty( $meta['show_featured_image'] ),
				'gallery_img'              => ! empty( $meta['show_product_gallery'] ),
				'c_img'                    => ! empty( $meta['show_cat_image'] ),
				'hover_img'                => ! empty( $meta['show_hover_image'] ),
				'f_img_size'               => self::get_data( $meta, 'image', 'medium' ),
				'custom_img_size'          => ! empty( $c_image_size ) && is_array( $c_image_size ) ? $c_image_size : [],
				'default_img_id'           => ! empty( $meta['default_preview_image']['id'] ) ? $meta['default_preview_image']['id'] : null,
				'show_overlay'             => ! empty( $meta['show_overlay'] ),
				'hover_animation'          => self::get_data( $meta, 'image_hover_animation', 'none' ),
				'show_custom_image'        => ! empty( $meta['show_custom_image'] ),
				'custom_image'             => ! empty( $meta['custom_image']['id'] ) ? $meta['custom_image']['id'] : null,

				// Visibility.
				'visibility'               => ! empty( self::content_visibility( $meta ) ) ? self::content_visibility( $meta ) : [],

				// Action Buttons.
				'btn_preset'               => self::get_data( $meta, 'action_btn_preset', 'preset1' ),
				'btn_position'             => self::get_data( $meta, 'action_btn_position', 'above' ),
				'tooltip_position'         => self::get_data( $meta, 'action_btn_tooltip_position', 'top' ),

				// Product Title.
				'title_tag'                => self::get_data( $meta, 'title_tag', 'h3' ),
				'title_hover'              => ! empty( $meta['title_hover'] ),
				'title_limit'              => self::get_data( $meta, 'title_limit', 'default' ),
				'title_limit_custom'       => self::get_data( $meta, 'title_limit_custom' ),
				'show_custom_title'        => ! empty( $meta['show_custom_title'] ),
				'cat_custom_title'         => self::get_data( $meta, 'custom_title', '' ),

				// Variation Swatches.
				'swatch_position'          => self::get_data( $meta, 'swatch_position', 'top' ),
				'swatch_type'              => self::get_data( $meta, 'swatch_type', 'circle' ),

				// Short Description.
				'show_custom_excerpt'      => ! empty( $meta['show_custom_description'] ),
				'cat_custom_excerpt'       => self::get_data( $meta, 'custom_description', '' ),
				'cat_excerpt_position'     => self::get_data( $meta, 'excerpt_position', 'below' ),
				'excerpt_limit'            => self::get_data( $meta, 'excerpt_limit', 'default' ),
				'excerpt_limit_custom'     => self::get_data( $meta, 'excerpt_limit_custom', 200 ),

				// Count.
				'count_position'           => self::get_data( $meta, 'count_display_type', 'flex' ),
				'before_count'             => self::get_data( $meta, 'before_count', '' ),
				'after_count'              => self::get_data( $meta, 'after_count', '' ),

				// Add to cart.
				'show_cart_icon'           => ! empty( $meta['show_cart_icon'] ),
				'show_cart_text'           => ! empty( $meta['show_cart_text'] ),
				'add_to_cart_icon'         => self::get_data( $meta, 'add_to_cart_icon', [] ),
				'add_to_cart_success_icon' => self::get_data( $meta, 'add_to_cart_success_icon', [] ),
				'add_to_cart_text'         => self::get_data( $meta, 'add_to_cart_text', esc_html__( 'Add to Cart', 'shopbuilder' ) ),
				'add_to_cart_success_text' => self::get_data( $meta, 'add_to_cart_success_text', '' ),
				'add_to_cart_alignment'    => self::get_data( $meta, 'cart_icon_alignment', 'left' ),

				// Action Button Icons.
				'quick_view_icon'          => self::get_data( $meta, 'quick_view_icon', [] ),
				'comparison_icon'          => self::get_data( $meta, 'comparison_icon', [] ),
				'comparison_icon_added'    => self::get_data( $meta, 'comparison_icon_added', [] ),
				'wishlist_icon'            => self::get_data( $meta, 'wishlist_icon', [] ),
				'wishlist_icon_added'      => self::get_data( $meta, 'wishlist_icon_added', [] ),

				// Badges.
				'sale_badge_type'          => self::get_data( $meta, 'sale_badges_type', 'percentage' ),
				'sale_badge_text'          => self::get_data( $meta, 'sale_badges_text', esc_html__( 'Sale', 'shopbuilder' ) ),
				'custom_badge_text'        => self::get_data( $meta, 'custom_badge_text', esc_html__( 'Sale', 'shopbuilder' ) ),
				'out_of_stock_badge_text'  => self::get_data( $meta, 'stock_badges_text', esc_html__( 'Out of Stock', 'shopbuilder' ) ),
				'badge_position'           => self::get_data( $meta, 'badges_position', 'top' ),
				'badge_alignment'          => self::get_data( $meta, 'badges_alignment', 'left' ),
				'badge_preset'             => self::get_data( $meta, 'custom_badge_preset', 'preset-1' ),
				'enable_badges_module'     => self::get_data( $meta, 'enable_badges_module', '' ),

				// Link.
				'image_link'               => ! empty( $meta['image_link'] ),
				'title_link'               => ! empty( $meta['title_link'] ),
				'hover_btn_text'           => self::get_data( $meta, 'hover_btn_text', esc_html__( 'See Details', 'shopbuilder' ) ),
				'show_hover_btn_icon'      => ! empty( $meta['show_hover_btn_icon'] ),
				'hover_btn_icon'           => self::get_data( $meta, 'hover_btn_icon', [] ),
				'custom_link'              => self::get_data( $meta, 'custom_link' ),

				// Slider.
				'd_group'                  => self::get_data( $meta, 'cols_group', 1 ),
				't_group'                  => self::get_data( $meta, 'cols_group_tablet', 1 ),
				'm_group'                  => self::get_data( $meta, 'cols_group_mobile', 1 ),
				'auto_play'                => ! empty( $meta['slide_autoplay'] ),
				'stop_on_hover'            => ! empty( $meta['pause_hover'] ),
				'slider_nav'               => ! empty( $meta['slider_nav'] ),
				'slider_dot'               => ! empty( $meta['slider_pagi'] ),
				'slider_dynamic_dot'       => ! empty( $meta['slider_dynamic_pagi'] ),
				'loop'                     => ! empty( $meta['slider_loop'] ),
				'lazy_load'                => ! empty( $meta['slider_lazy_load'] ),
				'auto_height'              => ! empty( $meta['slider_auto_height'] ),
				'speed'                    => self::get_data( $meta, 'slide_speed', 2000 ),
				'space_between'            => isset( $meta['grid_gap']['size'] ) && strlen( $meta['grid_gap']['size'] ) ? $meta['grid_gap']['size'] : 30,
				'auto_play_timeout'        => self::get_data( $meta, 'autoplay_timeout', 5000 ),
				'nav_position'             => self::get_data( $meta, 'slider_nav_position', 'top' ),
				'left_arrow_icon'          => self::get_data( $meta, 'slider_left_arrow_icon', [] ),
				'right_arrow_icon'         => self::get_data( $meta, 'slider_right_arrow_icon', [] ),
			],
			$meta
		);

		if ( ! empty( $meta['active_cat_slider'] ) ) {
			$data['active_cat_slider'] = true;
		}

		if ( ! empty( $meta['cols_widescreen'] ) ) {
			$data['w_cols'] = $meta['cols_widescreen'];
		}

		if ( ! empty( $meta['cols_laptop'] ) ) {
			$data['l_cols'] = $meta['cols_laptop'];
		}

		if ( ! empty( $meta['cols_tablet_extra'] ) ) {
			$data['te_cols'] = $meta['cols_tablet_extra'];
		}

		if ( ! empty( $meta['cols_mobile_extra'] ) ) {
			$data['me_cols'] = $meta['cols_mobile_extra'];
		}

		$queried_obj = get_queried_object();

		if ( is_shop() || is_product_taxonomy() ) {
			$data['posts_per_page'] = self::get_products_per_page();
			$data['order_by']       = self::get_data( $data, 'rtsb_orderby', $data['order_by'] );
			$data['order']          = self::get_data( $data, 'rtsb_order', $data['order'] );
		}

		if ( ! is_shop() && is_product_taxonomy() ) {
			if ( ! empty( $queried_obj->taxonomy ) ) {
				$data['queried_tax'] = esc_html( $queried_obj->taxonomy );
			}

			if ( ! empty( $queried_obj->term_id ) ) {
				$data['queried_term'] = esc_html( $queried_obj->term_id );
			}
		}

		return apply_filters( 'rtsb/elementor/render/meta_dataset_final', $data, $meta, $raw_settings );
	}

	/**
	 * Builds an array with field values.
	 *
	 * @param array  $meta Field values.
	 * @param string $template Template name.
	 *
	 * @return array
	 */
	public static function archive_meta_dataset( array $meta, $template = '' ) {
		$data = apply_filters(
			'rtsb/elementor/render/archive_meta_dataset',
			[
				// Layout.
				'widget'                => 'default',
				'template'              => self::get_data( $template, '', '' ),
				'view_mode'             => self::get_data( $meta, 'view_mode', 'grid' ),
				'show_flash_sale'       => ! empty( $meta['show_flash_sale'] ),
				'wishlist_button'       => ! empty( $meta['wishlist_button'] ),
				'comparison_button'     => ! empty( $meta['comparison_button'] ),
				'quick_view_button'     => ! empty( $meta['quick_view_button'] ),
				'show_rating'           => ! empty( $meta['show_rating'] ),
				'show_pagination'       => ! empty( $meta['show_pagination'] ),
				'cart_icon'             => self::get_data( $meta, 'cart_icon', [] ),
				'wishlist_icon'         => self::get_data( $meta, 'wishlist_icon', [] ),
				'wishlist_icon_added'   => self::get_data( $meta, 'wishlist_icon_added', [] ),
				'comparison_icon'       => self::get_data( $meta, 'comparison_icon', [] ),
				'comparison_icon_added' => self::get_data( $meta, 'comparison_icon_added', [] ),
				'quick_view_icon'       => self::get_data( $meta, 'quick_view_icon', [] ),
				'prev_icon'             => self::get_data( $meta, 'prev_icon', [] ),
				'next_icon'             => self::get_data( $meta, 'next_icon', [] ),
				'posts_per_page'        => self::get_products_per_page(),
				'rtsb_order'            => self::get_data( $meta, 'rtsb_order', 'ASC' ),
				'rtsb_orderby'          => self::get_data( $meta, 'rtsb_orderby', 'menu_order' ),
				'tooltip_position'      => self::get_data( $meta, 'action_btn_tooltip_position', 'top' ),
			],
			$meta
		);

		$queried_obj = get_queried_object();

		if ( ! is_shop() && BuilderFns::is_archive() ) {
			if ( ! empty( $queried_obj->taxonomy ) ) {
				$data['queried_tax'] = esc_html( $queried_obj->taxonomy );
			}

			if ( ! empty( $queried_obj->term_id ) ) {
				$data['queried_term'] = esc_html( $queried_obj->term_id );
			}
		}

		return apply_filters( 'rtsb/elementor/render/archive_meta_dataset_final', $data, $meta );
	}

	/**
	 * Setting up content visibility
	 *
	 * @param array $settings Elementor settings.
	 *
	 * @return array
	 */
	public static function content_visibility( $settings ) {
		$visibility = [];

		if ( ! empty( $settings['show_title'] ) ) {
			$visibility[] = 'title';
		}

		if ( ! empty( $settings['show_short_desc'] ) ) {
			$visibility[] = 'excerpt';
		}

		if ( ! empty( $settings['show_price'] ) ) {
			$visibility[] = 'price';
		}

		if ( ! empty( $settings['show_rating'] ) ) {
			$visibility[] = 'rating';
		}

		if ( ! empty( $settings['show_badges'] ) ) {
			$visibility[] = 'badges';
		}

		if ( ! empty( $settings['show_categories'] ) ) {
			$visibility[] = 'categories';
		}

		if ( ! empty( $settings['single_category'] ) ) {
			$visibility[] = 'single-cat';
		}

		if ( ! empty( $settings['show_swatches'] ) ) {
			$visibility[] = 'swatches';
		}

		if ( ! empty( $settings['show_vs_clear_btn'] ) ) {
			$visibility[] = 'clear-btn';
		}

		if ( ! empty( $settings['show_count'] ) ) {
			$visibility[] = 'count';
		}

		if ( ! empty( $settings['show_wishlist'] ) ) {
			$visibility[] = 'wishlist';
		}

		if ( ! empty( $settings['show_compare'] ) ) {
			$visibility[] = 'compare';
		}

		if ( ! empty( $settings['show_quick_view'] ) ) {
			$visibility[] = 'quick_view';
		}

		if ( ! empty( $settings['show_add_to_cart'] ) ) {
			$visibility[] = 'add_to_cart';
		}

		return $visibility;
	}

	/**
	 * Set Default Layout.
	 *
	 * @param string $layout Layout.
	 *
	 * @return string
	 */
	public static function set_default_layout( $layout ) {
		$is_list       = preg_match( '/list/', $layout );
		$is_cat_single = preg_match( '/category-single/', $layout );
		$is_cat        = preg_match( '/category/', $layout );
		$is_carousel   = preg_match( '/slider/', $layout );
		$default       = 'grid-layout1';

		if ( $is_list ) {
			$default = 'list-layout1';
		} elseif ( $is_cat_single ) {
			$default = 'category-single-layout1';
		} elseif ( $is_cat ) {
			$default = 'category-layout1';
		} elseif ( $is_carousel ) {
			$default = 'slider-layout1';
		}

		return $default;
	}

	/**
	 * Default layout columns.
	 *
	 * @param int $layout Layout.
	 *
	 * @return int
	 */
	public static function default_columns( $layout ) {
		switch ( $layout ) {
			case 'grid-layout2':
			case 'slider-layout2':
				$columns = 3;
				break;

			case 'list-layout1':
			case 'list-layout2':
			case 'list-layout3':
			case 'list-layout7':
			case 'list-layout5':
				$columns = 1;
				break;

			case 'list-layout4':
			case 'list-layout6':
			case 'slider-layout5':
			case 'slider-layout8':
				$columns = 2;
				break;

			case 'grid-layout6':
			case 'grid-layout9':
				$columns = 5;
				break;

			case 'category-layout1':
				$columns = 6;
				break;

			default:
				$columns = 4;
				break;
		}

		return apply_filters( 'rtsb/element/general/default_columns', $columns, $layout );
	}

	/**
	 * Pagination JSON data.
	 *
	 * @param array  $metas Data set.
	 * @param string $template Template name.
	 *
	 * @return string
	 */
	public static function pagination_data( $metas, $template ) {
		$el_data = self::meta_dataset( $metas, $template );

		return esc_js( wp_json_encode( $el_data, JSON_UNESCAPED_UNICODE ) );
	}

	/**
	 * Container classes
	 *
	 * @param array  $metas Meta data.
	 * @param array  $settings Settings array.
	 * @param string $custom_class Custom class.
	 *
	 * @return mixed|null
	 */
	public static function prepare_container_classes( $metas = [], $settings = [], $custom_class = '' ) {
		$classes  = 'rtsb-elementor-container products rtsb-pos-r ';
		$classes .= $custom_class;

		$raw_layout     = esc_html( $metas['layout'] );
		$layout         = self::filter_layout( $raw_layout );
		$is_cat         = preg_match( '/category/', $layout );
		$cart_txt_class = '';

		$animation = $metas['hover_animation'];

		if ( ! $is_cat ) {
			$cart_txt_class = $metas['show_cart_text'] ? ' has-cart-text' : ' no-cart-text';
			$animation     .= ' gallery-hover-' . ( ! empty( $settings['gallery_hover_animation'] ) ? $settings['gallery_hover_animation'] : 'fade' );
		}

		$badge_class = [
			'position'  => $metas['badge_position'],
			'alignment' => $metas['badge_alignment'],
		];

		if ( ! in_array( 'clear-btn', $metas['visibility'], true ) ) {
			$classes .= ' no-clear-btn';
		}

		$classes .= ! empty( $metas['pagination'] ) ? ' rtsb-has-pagination' : ' rtsb-no-pagination';
		$classes .= ' badge-' . esc_attr( $badge_class['position'] ) . ' badge-' . esc_attr( $badge_class['alignment'] ) . esc_attr( $cart_txt_class ) . ' img-hover-' . esc_attr( $animation ) . ' excerpt-' . esc_attr( $metas['cat_excerpt_position'] );

		if ( $metas['show_overlay'] ) {
			$classes .= ' has-overlay';
		}

		if ( in_array( 'single-cat', $metas['visibility'], true ) ) {
			$classes .= ' show-single-cat';
		}

		if ( ! $is_cat ) {
			$action_btn_classes = [
				'show_compare_text'        => 'has-compare-text',
				'show_quick_view_text'     => 'has-quick-view-text',
				'show_wishlist_text'       => 'has-wishlist-text',
				'show_compare_icon'        => 'no-compare-icon',
				'show_quick_view_icon'     => 'no-quick-view-icon',
				'show_wishlist_icon'       => 'no-wishlist-icon',
				'show_quick_checkout_icon' => 'no-quick_checkout-icon',
			];

			foreach ( $action_btn_classes as $setting_key => $class ) {
				if ( strpos( $setting_key, 'icon' ) !== false ) {
					$classes .= empty( $settings[ $setting_key ] ) ? ' ' . $class : '';
				} else {
					$classes .= ! empty( $settings[ $setting_key ] ) ? ' ' . $class : '';
				}
			}

			if ( rtsb()->has_pro() && ! empty( $settings['custom_ordering'] ) ) {
				$classes .= ' has-custom-ordering';
			}
		}
		if ( rtsb()->has_pro() && ! empty( $settings['slider_slide_animation'] ) ) {
			$classes .= ' has-slide-animation';
		}

		return apply_filters( 'rtsb/product/container/class', $classes, $settings );
	}

	/**
	 * Function to filter and validate the layout against allowed patterns.
	 *
	 * @param string $layout The layout string to be filtered.
	 * @param string $template The template to be checked.
	 *
	 * @return string
	 */
	public static function filter_layout( $layout, $template = '' ) {
		$allowed_patterns = apply_filters( 'rtsb/elementor/custom_layout', [ 'grid', 'list', 'slider', 'category', 'toyup', 'zilly' ] );
		$layout_part      = explode( '-', $layout );
		$default          = 'grid-layout1';

		if ( empty( $layout_part[0] ) ) {
			return $default;
		}

		if ( in_array( $layout_part[0], $allowed_patterns, true ) ) {
			return $layout;
		} else {
			if ( ! empty( $template ) ) {
				$grid     = preg_match( '/grid/', $template );
				$list     = preg_match( '/list/', $template );
				$slider   = preg_match( '/slider/', $template );
				$category = preg_match( '/category/', $template );

				if ( $grid ) {
					return $default;
				} elseif ( $list ) {
					return 'list-layout1';
				} elseif ( $slider ) {
					return 'slider-layout1';
				} elseif ( $category ) {
					return 'category-layout1';
				} else {
					return $default;
				}
			} else {
				return $default;
			}
		}
	}

	/**
	 * Row classes
	 *
	 * @param array $settings Settings array.
	 *
	 * @return mixed|null
	 */
	public static function prepare_row_classes( $settings = [] ) {
		$masonry_class = null;
		$classes       = 'rtsb-row rtsb-content-loader element-loading';
		$loader_class  = ' rtsb-pre-loader';

		$raw_layout  = esc_html( $settings['layout'] );
		$layout      = self::filter_layout( $raw_layout );
		$grid_type   = $settings['grid_type'];
		$is_carousel = preg_match( '/slider/', $layout );

		if ( preg_match( '/category/', $layout ) && ! empty( $settings['active_cat_slider'] ) ) {
			$classes .= ' rtsb-slider-layout';
		}

		if ( 'even' === $grid_type ) {
			$masonry_class = ' rtsb-even';
		} elseif ( 'masonry' === $grid_type ) {
			$masonry_class = ' rtsb-masonry';
		}

		if ( ! rtsb()->has_pro() || $is_carousel ) {
			$masonry_class = ' rtsb-even';
		}
		if ( $is_carousel && ! empty( $settings['raw_settings']['always_show_nav'] ) ) {
			$classes .= ' always-show-nav';
		}
		if ( ! empty( $settings['raw_settings']['inner_slider_always_show_nav'] ) ) {
			$classes .= ' inner-slider-always-show-nav';
		}

		$classes .= ' rtsb-' . $layout . $masonry_class . $loader_class;

		return apply_filters( 'rtsb/elementor/row/classes', $classes, $settings );
	}

	/**
	 * Prepare pagination attributes.
	 *
	 * @param string $layout The layout type.
	 * @param bool   $has_filters Whether the widget has filters.
	 * @param string $template The template name.
	 * @param array  $settings The widget settings.
	 * @param bool   $ajax Whether to enable AJAX pagination.
	 *
	 * @return string
	 */
	public static function prepare_pagination_attr( $layout, $has_filters, $template, $settings, $ajax ) {
		$is_cat      = preg_match( '/category/', $layout );
		$is_carousel = preg_match( '/slider/', $layout );

		if ( $is_carousel ) {
			return $has_filters ? self::pagination_data( $settings, $template ) : '';
		} elseif ( $is_cat ) {
			return '';
		} else {
			$ajax_pagination = ! empty( $settings['show_pagination'] ) && 'pagination' !== $settings['pagination_type'];
			$page            = Fns::product_filters_has_ajax( apply_filters( 'rtsb/builder/set/current/page/type', '' ) );
			$condition       = rtsb()->has_pro() && $ajax && ( $ajax_pagination || $has_filters || $page );

			return $condition ? self::pagination_data( $settings, $template ) : '';
		}
	}

	/**
	 * Archive JSON data.
	 *
	 * @param array  $metas Data set.
	 * @param string $template Template name.
	 *
	 * @return string
	 */
	public static function archive_data( $metas, $template ) {
		$el_data = self::archive_meta_dataset( $metas, $template );

		return ' data-rtsb-ajax=\'' . esc_js( wp_json_encode( $el_data ) ) . '\'';
	}

	/**
	 * Slider options.
	 *
	 * @param array $meta Meta values.
	 * @param array $settings Raw Settings.
	 *
	 * @return array
	 */
	public static function slider_data( array $meta, $settings = [] ) {
		$has_dots         = $meta['slider_dot'] ? ' has-dot' : ' no-dot';
		$has_dots        .= $meta['slider_nav'] ? ' has-nav' : ' no-nav';
		$has_dynamic_dots = $meta['slider_dynamic_dot'] ? true : false;
		$d_col            = 0 === $meta['d_cols'] ? self::default_columns( $meta['layout'] ) : $meta['d_cols'];
		$t_col            = 0 === $meta['t_cols'] ? 2 : $meta['t_cols'];
		$m_col            = 0 === $meta['m_cols'] ? 1 : $meta['m_cols'];

		if ( \Elementor\Plugin::$instance->breakpoints->has_custom_breakpoints() ) {
			// Widescreen.
			$w_col   = self::get_data( $settings, 'cols_widescreen' );
			$w_col   = 0 === $w_col ? self::default_columns( $meta['layout'] ) : $w_col;
			$w_group = self::get_data( $settings, 'cols_group_widescreen', 1 );

			// Laptop.
			$l_col   = self::get_data( $settings, 'cols_laptop' );
			$l_col   = 0 === $l_col ? self::default_columns( $meta['layout'] ) : $l_col;
			$l_group = self::get_data( $settings, 'cols_group_laptop', 1 );

			// Tablet Landscape.
			$te_col   = self::get_data( $settings, 'cols_tablet_extra' );
			$te_col   = 0 === $te_col ? 3 : $te_col;
			$te_group = self::get_data( $settings, 'cols_group_tablet_extra', 1 );

			// Mobile Landscape.
			$me_col   = self::get_data( $settings, 'cols_mobile_extra' );
			$me_col   = 0 === $me_col ? 2 : $me_col;
			$me_group = self::get_data( $settings, 'cols_group_mobile_extra', 1 );

			$el_breakpoints = Fns::get_elementor_breakpoints();

			foreach ( $el_breakpoints as $key => $value ) {
				if ( isset( $value['is_enabled'] ) && ! $value['is_enabled'] ) {
					unset( $el_breakpoints[ $key ] );
				}
			}

			$breakpoints = [
				0 => [
					'slidesPerView'  => absint( $m_col ),
					'slidesPerGroup' => absint( $meta['m_group'] ),
					'pagination'     => [
						'dynamicBullets' => $has_dynamic_dots,
					],
				],
			];

			$desktop = [
				'desktop' => [
					'label'      => 'Desktop',
					'value'      => 1920,
					'is_enabled' => 1,
				],
			];

			if ( ! empty( $el_breakpoints['laptop'] ) && $el_breakpoints['laptop']['is_enabled'] ) {
				$widescreen_position = array_search( 'widescreen', array_keys( $el_breakpoints ), true );
				$el_breakpoints      = array_merge(
					array_slice( $el_breakpoints, 0, $widescreen_position ),
					$desktop,
					array_slice( $el_breakpoints, $widescreen_position )
				);
			} else {
				$desktop['desktop']['value'] = 1366;
				$el_breakpoints['desktop']   = $desktop['desktop'];
			}

			foreach ( $el_breakpoints as $el_breakpoint => $value ) {
				$breakpoint_value = ! empty( $value['value'] ) ? $value['value'] : $value['default_value'];
				$dynamic_bullets  = $has_dynamic_dots;
				$slides_per_view  = $d_col;
				$slides_per_group = $meta['d_group'];

				switch ( $el_breakpoint ) {
					case 'widescreen':
						$slides_per_view  = $w_col;
						$slides_per_group = $w_group;

						break;

					case 'laptop':
						$slides_per_view  = $l_col;
						$slides_per_group = $l_group;

						break;

					case 'tablet_extra':
						$slides_per_view  = $l_col;
						$slides_per_group = $l_group;

						if ( empty( $el_breakpoints['laptop'] ) ) {
							$slides_per_view  = $d_col;
							$slides_per_group = $meta['d_group'];
						}

						break;

					case 'tablet':
						$slides_per_view  = $te_col;
						$slides_per_group = $te_group;

						if ( empty( $el_breakpoints['laptop'] ) && empty( $el_breakpoints['tablet_extra'] ) ) {
							$slides_per_view  = $d_col;
							$slides_per_group = $meta['d_group'];
						} elseif ( empty( $el_breakpoints['laptop'] ) && ! empty( $el_breakpoints['tablet_extra'] ) ) {
							$slides_per_view  = $te_col;
							$slides_per_group = $te_group;
						} elseif ( ! empty( $el_breakpoints['laptop'] ) && empty( $el_breakpoints['tablet_extra'] ) ) {
							$slides_per_view  = $l_col;
							$slides_per_group = $l_group;
						}

						break;

					case 'mobile_extra':
						$slides_per_view  = $t_col;
						$slides_per_group = $meta['t_group'];
						$dynamic_bullets  = $has_dynamic_dots;

						break;

					case 'mobile':
						$slides_per_view  = $t_col;
						$slides_per_group = $meta['t_group'];
						$dynamic_bullets  = $has_dynamic_dots;

						if ( ! empty( $el_breakpoints['mobile_extra'] ) ) {
							$slides_per_view  = $me_col;
							$slides_per_group = $me_group;
						}

						break;
				}

				if ( $value['is_enabled'] ) {
					$br = 'widescreen' !== $el_breakpoint ? $breakpoint_value + 1 : $breakpoint_value;

					$breakpoints[ absint( $br ) ] = [
						'slidesPerView'  => absint( $slides_per_view ),
						'slidesPerGroup' => absint( $slides_per_group ),
						'pagination'     => [
							'dynamicBullets' => $dynamic_bullets,
						],
					];
				}
			}
		} else {
			$breakpoints = [
				0    => [
					'slidesPerView'  => absint( $m_col ),
					'slidesPerGroup' => absint( $meta['m_group'] ),
					'pagination'     => [
						'dynamicBullets' => $has_dynamic_dots,
					],
				],
				768  => [
					'slidesPerView'  => absint( $t_col ),
					'slidesPerGroup' => absint( $meta['t_group'] ),
					'pagination'     => [
						'dynamicBullets' => $has_dynamic_dots,
					],
				],
				1025 => [
					'slidesPerView'  => absint( $d_col ),
					'slidesPerGroup' => absint( $meta['d_group'] ),
					'pagination'     => [
						'dynamicBullets' => $has_dynamic_dots,
					],
				],
			];
		}

		$slider_options = [
			'slidesPerView'  => absint( $d_col ),
			'slidesPerGroup' => absint( $meta['d_group'] ),
			'speed'          => absint( $meta['speed'] ),
			'loop'           => $meta['loop'],
			'autoHeight'     => $meta['auto_height'],
			'preloadImages'  => ! $meta['lazy_load'],
			'lazy'           => $meta['lazy_load'],
			'breakpoints'    => $breakpoints,
		];

		if ( ! empty( $meta['raw_settings']['slider_slide_animation'] ) ) {
			$slider_options['watchSlidesProgress'] = true;
		}
		if ( $meta['auto_play'] ) {
			$slider_options['autoplay'] = [
				'delay'                => absint( $meta['auto_play_timeout'] ),
				'pauseOnMouseEnter'    => $meta['stop_on_hover'],
				'disableOnInteraction' => false,
			];
		}

		$slider_options = apply_filters( 'rtsb/elementor/render/slider_dataset', $slider_options, $meta );
		$carouselClass  = 'rtsb-carousel-slider slider-loading rtsb-pos-s ' . $meta['nav_position'] . '-nav' . $has_dots;

		return [
			'data'  => esc_js( wp_json_encode( $slider_options ) ),
			'class' => $carouselClass,
		];
	}

	/**
	 * Preloader.
	 *
	 * @param string $layout Layout.
	 *
	 * @return string
	 */
	public static function pre_loader( $layout ) {
		$loader_class = '';

		$is_carousel = preg_match( '/slider/', $layout );

		if ( $is_carousel ) {
			$loader_class = ' full-op';
		}

		return '<div class="rtsb-elements-loading rtsb-ball-clip-rotate"><div></div></div>';
	}

	/**
	 * Badge class.
	 *
	 * @param string $preset Badge preset.
	 *
	 * @return string|null
	 */
	public static function badge_class( $preset ) {
		switch ( $preset ) {
			case 'preset-default':
				$class = 'default-badge';
				break;
			case 'preset2':
				$class = 'fill angle-right';
				break;
			case 'preset3':
				$class = 'outline';
				break;

			case 'preset4':
				$class = 'outline angle-right';
				break;

			default:
				$class = 'fill';
				break;
		}

		return $class;
	}

	/**
	 * Registers required scripts.
	 *
	 * @param array $scripts Scripts to register.
	 *
	 * @return void
	 */
	public static function register_scripts( $scripts ) {
		$caro   = false;
		$script = [];

		$script[] = 'jquery';

		foreach ( $scripts as $sc => $value ) {
			if ( ! empty( $sc ) ) {
				if ( 'isCarousel' === $sc ) {
					$caro = $value;
				}
			}
		}

		if ( count( $scripts ) ) {
			/**
			 * Scripts.
			 */
			if ( $caro ) {
				$script[] = 'swiper';
			}

			$script[] = 'rtsb-imagesloaded';
			$script[] = 'rtsb-public';

			foreach ( $script as $sc ) {
				wp_enqueue_script( $sc );
			}
		}
	}

	/**
	 * Check if a wrapper is needed based on the current theme.
	 *
	 * @return bool
	 */
	public static function is_wrapper_needed() {
		$theme_list = apply_filters(
			'rtsb/products/inner/wrapper/basedon/themes',
			[
				'twentytwentyone',
				'twentytwentytwo',
				'twentytwentythree',
				'storefront',
				'hello-elementor',
				'astra',
			],
			rtsb()->current_theme
		);

		if ( in_array( rtsb()->current_theme, $theme_list, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get the number of products per page based on WooCommerce settings.
	 *
	 * @return int
	 */
	public static function get_products_per_page() {
		$products_row      = absint( get_option( 'woocommerce_catalog_rows', 4 ) );
		$products_col      = absint( get_option( 'woocommerce_catalog_columns', 4 ) );
		$products_per_page = apply_filters( 'rtsb/elementor/archive/products_per_page', $products_row * $products_col );

		return ! empty( $products_per_page ) ? $products_per_page : 12;
	}
}
