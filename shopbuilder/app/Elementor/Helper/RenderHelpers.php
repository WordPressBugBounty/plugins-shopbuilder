<?php
/**
 * Elementor Render Helper Class.
 *
 * This class contains render helper logics.
 *
 * @package RadiusTheme\SB
 */

namespace RadiusTheme\SB\Elementor\Helper;

use Elementor\Plugin;
use RadiusTheme\SB\Helpers\Cache;
use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Helpers\BuilderFns;
use WC_Product_Query;
use WP_Term;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * BuilderFns class
 */
class RenderHelpers {
	/**
	 * @var array
	 */
	private static $cache = [];
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
				'brands'                   => self::get_data( $meta, 'filter_brands', [] ),
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
		if ( ! empty( $settings['show_brands'] ) ) {
			$visibility[] = 'brands';
		}

		if ( ! empty( $settings['single_brand'] ) ) {
			$visibility[] = 'single-brand';
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
			case 'rtsb-post-grid-layout1':
			case 'rtsb-post-grid-layout2':
			case 'rtsb-post-grid-layout3':
			case 'rtsb-team-layout1':
			case 'rtsb-team-layout2':
			case 'rtsb-testimonial-layout2':
			case 'grid-layout2':
			case 'slider-layout2':
			case 'rtsb-coupon-layout1':
			case 'rtsb-coupon-layout2':
			case 'rtsb-coupon-layout3':
				$columns = 3;
				break;
			case 'rtsb-post-list-layout1':
			case 'rtsb-post-list-layout2':
			case 'list-layout1':
			case 'list-layout2':
			case 'list-layout3':
			case 'list-layout7':
			case 'list-layout5':
				$columns = 1;
				break;
			case 'rtsb-team-layout4':
			case 'rtsb-testimonial-layout1':
			case 'rtsb-testimonial-layout3':
			case 'rtsb-testimonial-layout4':
			case 'rtsb-testimonial-layout5':
			case 'list-layout4':
			case 'list-layout6':
			case 'slider-layout5':
			case 'slider-layout8':
				$columns = 2;
				break;
			case 'rtsb-logo-layout1':
			case 'rtsb-logo-layout2':
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
		if ( in_array( 'single-brand', $metas['visibility'], true ) ) {
			$classes .= ' show-single-brand';
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
		$allowed_patterns = apply_filters( 'rtsb/elementor/custom_layout', [ 'grid', 'list', 'slider', 'category', 'toyup', 'zilly', 'rtsb' ] );
		$layout_part      = explode( '-', $layout );
		$default          = 'grid-layout1';

		if ( empty( $layout_part[0] ) ) {
			return $default;
		}

		if ( in_array( $layout_part[0], $allowed_patterns, true ) ) {
			if ( ! rtsb()->has_pro() && ! in_array( $layout, array_keys( Fns::free_layouts( $layout ) ), true ) ) {
				$layout = self::set_default_layout( $layout );
			}

			return $layout;
		} else {
			return ! empty( $template ) ? self::set_default_layout( $layout ) : $default;
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

		$loader_class = '';
		if ( Fns::enable_loader() ) {
			$loader_class = ' rtsb-pre-loader';
		}
		$raw_layout  = esc_html( $settings['layout'] );
		$layout      = self::filter_layout( $raw_layout );
		$grid_type   = $settings['grid_type'];
		$is_carousel = preg_match( '/slider/', $layout );

		if ( preg_match( '/category/', $layout ) && ! empty( $settings['active_cat_slider'] ) ) {
			$classes .= ' rtsb-slider-layout';
		}

		if ( 'equal' === $grid_type ) {
			$masonry_class = ' rtsb-equal';
		} elseif ( 'masonry' === $grid_type ) {
			$masonry_class = ' rtsb-masonry';
		} else {
			$masonry_class = ' rtsb-even';
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
		if ( empty( $settings['raw_settings']['cols_mobile'] ) ) {
			$classes .= ' rtsb-mobile-flex-row';
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

		if ( Plugin::$instance->breakpoints->has_custom_breakpoints() ) {
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
					'grid'           => [
						'rows' => 1,
					],
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
		if ( ! Fns::enable_loader() ) {
			return '';
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
		$caro    = false;
		$masonry = false;
		$script  = [];

		$script[] = 'jquery';

		foreach ( $scripts as $sc => $value ) {
			if ( ! empty( $sc ) ) {
				if ( 'isCarousel' === $sc ) {
					$caro = $value;
				}

				if ( 'isMasonry' === $sc ) {
					$masonry = $value;
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

			if ( $masonry ) {
				$script[] = 'masonry';
			}

			$script[] = 'imagesloaded';
			$script[] = Fns::optimized_handle( 'rtsb-public' );

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
	/**
	 * Render Filters View.
	 *
	 * @param array $templates Template name.
	 * @param array $settings Control settings.
	 *
	 * @return string
	 */
	public static function filters_view( $templates, $settings ) {
		global $wp;
		$filters            = [];
		$html               = null;
		$reset              = ! empty( $settings['reset_btn'] );
		$reset_mode         = 'show' === $settings['reset_btn_behavior'] ? ' show-reset' : ' ondemand-reset';
		$reset_text         = ! empty( $settings['reset_btn_text'] ) ? $settings['reset_btn_text'] : esc_html__( 'Reset Filter', 'shopbuilder' );
		$scroll_mode        = ! empty( $settings['enable_scroll'] ) ? ' has-scroll' : ' no-scroll';
		$scroll_height      = ! empty( $settings['enable_scroll'] ) ? $settings['scroll_height']['size'] : 300;
		$has_mobile_toggle  = ! empty( $settings['filter_mobile_toggle'] );
		$toggle_class       = $has_mobile_toggle ? ' default-filter-has-toggle' : ' default-filter-no-toggle';
		$mobile_toggle_text = ! empty( $settings['filter_mobile_toggle_text'] ) ? $settings['filter_mobile_toggle_text'] : esc_html__( 'Click to Filter', 'shopbuilder' );
		$scroll_attr        = '';

		if ( ! empty( $settings['enable_scroll'] ) ) {
			$scroll_attr = ' style="--rtsb-filter-scroll-height: ' . absint( $scroll_height ) . 'px;"';
		}

		foreach ( $settings['filter_types'] as $key => $item ) {
			if ( ! defined( 'RTWPVS_PLUGIN_FILE' ) && in_array( $item['input_type_all'], [ 'color', 'button', 'image' ], true ) ) {
				$item['input_type_all'] = 'checkbox';
			}
			$filters[] = [
				'filter_title' => $item['filter_title'],
				'filter_items' => $item['filter_items'],
				'filter_attr'  => $item['filter_attr'],
				'input_type'   => 'product_attr' === $item['filter_items'] ? $item['input_type_all'] : $item['input_type'],
			];

			if ( 'rating_filter' === $item['filter_items'] ) {
				$filters[ $key ]['template'] = $templates['rating'];
			} elseif ( 'price_filter' === $item['filter_items'] ) {

				$filters[ $key ]['input_type'] = 'slider';
				$filters[ $key ]['template']   = $templates['price'];
			} else {
				$filters[ $key ]['template'] = 'product_attr' === $item['filter_items'] ? $templates[ $item['input_type_all'] ] : $templates[ $item['input_type'] ];
			}
		}

		if ( '' === get_option( 'permalink_structure' ) ) {
			$form_action = remove_query_arg( [ 'page', 'paged', 'product-page' ], add_query_arg( $wp->query_string, '', home_url( $wp->request ) ) );
		} else {
			$form_action = preg_replace( '%\/page/[0-9]+%', '', home_url( trailingslashit( $wp->request ) ) );
		}
		$args = [
			'filters'      => $filters,
			'scroll_mode'  => $scroll_mode,
			'reset_mode'   => $reset_mode,
			'reset'        => $reset,
			'scroll_attr'  => $scroll_attr,
			'toggle_class' => $toggle_class,
			'reset_text'   => $reset_text,
			'settings'     => $settings,
			'form_action'  => $form_action,
		];

		$html .= '<div class="rtsb-default-archive-filters' . esc_attr( $toggle_class ) . '">';

		if ( $has_mobile_toggle ) {
			$html .= '<div class="rtsb-filter-mobile-toggle">
                            <button class="product-filter-toggle">
                                <span class="icon"><i aria-hidden="true" class="toggle-icon rtsb-icon rtsb-icon-filter"></i></span>
                                <span class="text reset-text">' . esc_html( $mobile_toggle_text ) . '</span>
                                <span></span>
                            </button>
                        </div>';
		}

		$html .= Fns::load_template( $templates['layout'], $args, true );
		$html .= '</div>';
		return $html;
	}
	/**
	 * Get taxonomy type.
	 *
	 * @param string $tax_type Taxonomy type.
	 * @param string $attr_type Attribute type.
	 *
	 * @return string
	 */
	public static function get_product_filters_tax_type( $tax_type, $attr_type = '' ) {
		if ( 'product_attr' !== $tax_type ) {
			return $tax_type;
		}

		if ( empty( $attr_type ) ) {
			return $tax_type;
		}

		return $attr_type;
	}
	/**
	 * Get attribute terms.
	 *
	 * @param string $tax Taxonomy type.
	 * @return int[]|string|string[]|\WP_Error|\WP_Term[]
	 */
	public static function get_product_filters_attribute_terms( $tax ) {
		$cache_key = 'get_default_filter_attribute_terms_' . $tax;
		if ( isset( self::$cache[ $cache_key ] ) ) {
			return self::$cache[ $cache_key ];
		}

		$attributes = wc_get_attribute_taxonomies();
		$att_name   = str_replace( 'pa_', '', $tax );
		$exist      = array_search( $att_name, array_column( $attributes, 'attribute_name' ), true );
		if ( false === $exist ) {
			self::$cache[ $cache_key ] = [];
			return self::$cache[ $cache_key ];
		}
		$attribute = $tax;

		if ( ! is_shop() && is_product_taxonomy() ) {
			$term = get_queried_object();

			if ( ! $term instanceof WP_Term ) {
				return [];
			}

			$args = [
				'status' => 'publish',
				'limit'  => -1,
				'return' => 'ids',
			];

			if ( 'product_cat' === $term->taxonomy ) {
				$args['category'] = [ $term->slug ];
			}

			if ( 'product_tag' === $term->taxonomy ) {
				$args['tag'] = [ $term->slug ];
			}

			if ( strpos( $term->taxonomy, 'pa_' ) !== false ) {
				$args['tax_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					[
						'taxonomy' => $term->taxonomy,
						'field'    => 'term_id',
						'terms'    => $term->term_id,
						'operator' => 'IN',
					],
				];
			}

			$query      = new WC_Product_Query( $args );
			$products   = $query->get_products();
			$attr_terms = [];

			foreach ( $products as $product_id ) {
				$product_attr = wp_get_post_terms( $product_id, $attribute );

				foreach ( $product_attr as $attr ) {
					$attr_terms[] = $attr;
				}
			}
			self::$cache[ $cache_key ] = self::filter_products_array_unique( $attr_terms );
			return self::$cache[ $cache_key ];
		} else {
			self::$cache[ $cache_key ] = get_terms(
				[
					'taxonomy'   => $attribute,
					'hide_empty' => false,
				]
			);
			return self::$cache[ $cache_key ];
		}
	}
	/**
	 * Remove duplicate values from an array.
	 *
	 * @param array $array          The input array.
	 * @param bool  $keep_key_assoc Whether to keep the key associations after removing duplicates.
	 * @return array The array with duplicates removed.
	 */
	public static function filter_products_array_unique( $array, $keep_key_assoc = false ) {
		$duplicate_keys = [];
		$tmp            = [];

		foreach ( $array as $key => $val ) {
			// convert objects to arrays, in_array() does not support objects.
			if ( is_object( $val ) ) {
				$val = (array) $val;
			}

			if ( ! in_array( $val, $tmp, true ) ) {
				$tmp[] = $val;
			} else {
				$duplicate_keys[] = $key;
			}
		}

		foreach ( $duplicate_keys as $key ) {
			unset( $array[ $key ] );
		}

		return $keep_key_assoc ? $array : array_values( $array );
	}
	/**
	 * Retrieve product categories (including hierarchical support)
	 *
	 * @param string $taxonomy Taxonomy name.
	 * @return int[]|string|string[]|\WP_Error|\WP_Term[]
	 */
	public static function get_all_terms( $taxonomy ) {
		$args = [
			'taxonomy'        => $taxonomy,
			'suppress_filter' => true,
			'hide_empty'      => false,
			'pad_counts'      => true,
			'hierarchical'    => true,
		];

		$cache_key      = 'rtsb_get_terms_filter_products' . $taxonomy;
		$taxonomy_terms = wp_cache_get( $cache_key, 'shopbuilder' );

		if ( false === $taxonomy_terms ) {
			$queried_object = get_queried_object();

			if ( 'archive' === apply_filters( 'rtsb/builder/set/current/page/type', '' ) && $queried_object instanceof WP_Term && $queried_object->taxonomy === $taxonomy ) {
				$term_id           = $queried_object->term_id;
				$taxonomy          = $queried_object->taxonomy;
				$archive_cache_key = 'rtsb_get_terms_filter_products_' . $taxonomy . $term_id;
				$taxonomy_terms    = wp_cache_get( $archive_cache_key, 'shopbuilder' );
				if ( false === $taxonomy_terms ) {
					$taxonomy_terms = [ $queried_object ];
					$child_args     = [
						'taxonomy'   => $taxonomy,
						'child_of'   => $term_id,
						'hide_empty' => false,
					];

					$child_terms = get_terms( $child_args );

					if ( ! empty( $child_terms ) ) {
						$taxonomy_terms = array_merge( $taxonomy_terms, $child_terms );
					}

					wp_cache_set( $archive_cache_key, $taxonomy_terms, 'shopbuilder' );
					Cache::set_data_cache_key( $archive_cache_key );
				}
			} else {
				$taxonomy_terms = get_terms( $args );
			}

			wp_cache_set( $cache_key, $taxonomy_terms, 'shopbuilder' );
			Cache::set_data_cache_key( $cache_key );
		}

		return $taxonomy_terms;
	}
	/**
	 * Generates an error message block.
	 *
	 * @param array  $title The title array of the error message block.
	 * @param string $wrapper Additional CSS class for the wrapper div.
	 *
	 * @return string The HTML representation of the error message block.
	 */
	public static function filter_error_message( $title, $wrapper ) {
		$html  = '<div class="rtsb-product-default-filters ' . esc_attr( $wrapper ) . '">';
		$html .= self::get_default_filter_title( $title );
		$html .= '<div class="default-filter-content">';
		$html .= '<p class="no-filter">' . esc_html__( 'Nothing found.', 'shopbuilder' ) . '</p>';
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}
	/**
	 * Get filter title.
	 *
	 * @param array $title Filter title.
	 * @return string
	 */
	public static function get_default_filter_title( $title ) {
		$html = '';

		if ( empty( $title['title'] ) ) {
			return $html;
		}

		$html .= '<div class="default-filter-title-wrapper">';

		$html .= '<h3 class="widget-title rtsb-d-flex rtsb-align-items-center"><span class="title">' . $title['title'] . '</span></h3>';

		$html .= '</div>';

		return apply_filters( 'rtsb/elementor/default/filter/title', $html, $title );
	}
	/**
	 * Recursive function to generate the filter list with hierarchy.
	 *
	 * @param array  $data Array of required data.
	 * @param string $input Type of input field for the filter (e.g., checkbox).
	 * @param array  $additional_data Data for additional filtering.
	 * @param int    $parent ID of the parent term (default: 0).
	 *
	 * @return string The generated HTML for the product filter list.
	 */
	public static function get_product_default_filter_list_html( $data, $input = 'checkbox', $additional_data = [], $parent = 0 ) {

		$html          = '';
		$default_tax   = [];
		$is_attribute  = ! empty( $data['is_attribute'] );
		$active_option = isset( $_GET[ $additional_data['taxonomy'] ] ) ? explode( ',', wc_clean( wp_unslash( $_GET[ $additional_data['taxonomy'] ] ) ) ) : []; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$counter       = 0;

		if ( $is_attribute ) {
			list(
				'filter_name' => $filter_name,
				'base_link' => $base_link
				) = self::get_product_filters_attribute_term_info( $additional_data['taxonomy'] );
		}

		$html .= '<ul class="product-default-filters input-type-' . esc_attr( $input ) . '">';

		foreach ( $data['taxonomy'] as $tax ) {
			if ( $tax->parent !== $parent ) {
				continue;
			}

			if ( $is_attribute ) {
				$tax_link      = remove_query_arg( $filter_name, $base_link );
				$tax_link      = add_query_arg( $filter_name, $tax->slug, $tax_link );
				$active_option = isset( $_GET[ $filter_name ] ) ? explode( ',', sanitize_text_field( wp_unslash( $_GET[ $filter_name ] ) ) ) : []; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			} else {
				$tax_link = get_term_link( $tax );
			}

			$unique_id = substr( uniqid(), -4 );

			$active_tax        = in_array( $tax->slug, $active_option, true ) ? ' checked' : '';
			$active_tax_parent = in_array( $tax->slug, $active_option, true ) ? ' active' : '';
			$term_children     = in_array( $tax->taxonomy, [ 'product_cat', 'product_brand' ], true ) ? get_term_children( $tax->term_id, $tax->taxonomy ) : [];
			$taxonomy          = $tax->taxonomy;
			$product_count     = $tax->count;

			// Show count.
			if ( 'archive' === apply_filters( 'rtsb/builder/set/current/page/type', '' ) && ! empty( $data['count_display'] ) && 'block' === $data['count_display'] ) {
				$product_count = self::count_tax_data( $tax, $tax->count );
			}

			// Show Empty Categories.
			if ( empty( $data['show_empty_terms'] ) && 0 === $product_count ) {
				continue;
			}

			$html .= '<li class="rtsb-default-filter-term-item term-item-' . absint( $tax->term_id ) . esc_attr( $term_children ? ' term-has-children' : '' ) . ( ! $data['show_child'] ? ' child-terms-hidden' : '' ) . '">
                <div class="rtsb-default-filter-group' . esc_attr( $active_tax_parent ) . '">
                     <input type="' . esc_attr( $input ) . '" class="rtsb-default-filter-trigger rtsb-' . esc_attr( $input . '-filter rtsb-term-' . $tax->slug . $active_tax ) . '"  name="rtsb-filter-' . esc_attr( $taxonomy ) . '[]" id="rtsb-default-term-filter-' . $unique_id . absint( $tax->term_id ) . '" value="' . esc_attr( $tax->slug ) . '" ' . esc_attr( $active_tax ) . '>
                    <label class="rtsb-default-' . esc_attr( $input ) . '-filter-label" for="rtsb-default-term-filter-' . $unique_id . absint( $tax->term_id ) . '">
                        <span class="name">' . esc_html( $tax->name ) . '</span>
                    </label>
                    <div class="rtsb-product-count">(' . esc_html( $product_count ) . ')</div>
                    ' . ( ! empty( $term_children ) && $data['show_child'] ? '<i class="rtsb-plus-icon"></i>' : '' ) . '
                </div>';

			// Show Sub-categories.
			if ( $data['show_child'] ) {
				$children = self::get_product_default_filter_list_html( $data, $input, $additional_data, $tax->term_id );

				if ( ! empty( $children ) ) {
					$html .= '<div class="filter-child">' . $children . '</div>';
				}
			}

			$counter++;

			$html .= '</li>';
		}

		$html .= '</ul>';

		// Check if the output contains any child elements.
		$has_children = strpos( $html, '<li class="rtsb-default-filter-term-item' ) !== false;

		if ( ! $has_children ) {
			// Remove the outer <ul> if there are no child elements.
			$html = '';
		}
		return $html;
	}
	/**
	 * Get attribute term info.
	 *
	 * @param string $tax Taxonomy type.
	 * @return array
	 */
	public static function get_product_filters_attribute_term_info( $tax ) {
		$result     = [];
		$attributes = wc_get_attribute_taxonomies();

		foreach ( $attributes as $attr ) {
			if ( str_replace( 'pa_', '', $tax ) === $attr->attribute_name ) {
				$term_type      = $attr->attribute_type;
				$attribute_slug = $attr->attribute_name;
				$attribute      = wc_attribute_taxonomy_name( $attr->attribute_name );
				break;
			}
		}

		$filter_name = 'filter_' . wc_attribute_taxonomy_slug( $attribute_slug );

		$result['attribute']      = $attribute ?? '';
		$result['term_type']      = $term_type ?? '';
		$result['attribute_slug'] = $attribute_slug ?? '';
		$result['filter_name']    = $filter_name;
		$result['base_link']      = self::get_base_url();

		return $result;
	}
	/**
	 * Get base URL.
	 *
	 * @return string|null
	 */
	public static function get_base_url() {
		global $wp;
		return home_url( add_query_arg( [], $wp->request ) );
	}
	/**
	 * Count the number of occurrences for a specific term in a taxonomy for the current queried object.
	 *
	 * @param object|array $tax   The term object for the taxonomy.
	 * @param int          $count The initial count value.
	 *
	 * @return int The updated count value.
	 */
	public static function count_tax_data( $tax, $count ) {
		$page = get_queried_object();

		if ( is_array( $tax ) && strpos( $tax['taxonomy'], 'attribute_' ) !== false ) {
			$taxonomy = str_replace( 'attribute_', '', $tax['taxonomy'] );
			$term_id  = $tax['term_id'];
		} else {
			$taxonomy = $tax->taxonomy;
			$term_id  = $tax->term_id;
		}

		if ( strpos( $page->taxonomy, 'pa_' ) !== false ) {
			$args['status']    = 'publish';
			$args['limit']     = -1;
			$args['return']    = 'ids';
			$args['tax_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				'relation' => 'AND',
				[
					'taxonomy' => $page->taxonomy,
					'field'    => 'term_id',
					'terms'    => $page->term_id,
					'operator' => 'IN',
				],
				[
					'taxonomy' => $tax->taxonomy,
					'field'    => 'term_id',
					'terms'    => $tax->term_id,
					'operator' => 'IN',
				],
			];

			$query    = new WC_Product_Query( $args );
			$products = $query->get_products();

			return count( $products );
		}

		if ( $taxonomy !== $page->taxonomy ) {
			$args = [
				'limit'     => -1,
				'return'    => 'ids',
				'status'    => 'publish',
				'tax_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					[
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $term_id,
					],
				],
			];

			if ( 'product_cat' === $page->taxonomy ) {
				$args['category'] = [ $page->slug ];
			}

			if ( 'product_tag' === $page->taxonomy ) {
				$args['tag'] = [ $page->slug ];
			}

			$query    = new WC_Product_Query( $args );
			$products = $query->get_products();

			$count = count( $products );
		}

		return $count;
	}
	/**
	 * Get filter HTML.
	 *
	 * @param string $filter_type Filter type.
	 * @param array  $options Options.
	 * @param array  $additional_data Data for additional filtering.
	 * @param string $input Input type.
	 *
	 * @return string The generated HTML for the attribute filter list.
	 */
	public static function get_default_filter_html( $filter_type, $options, $additional_data = [], $input = 'checkbox' ) {
		$html          = '<ul class="product-filters input-type-' . esc_attr( $input ) . '" ';
		$active_option = isset( $_GET[ $filter_type ] ) ? explode( ',', wc_clean( wp_unslash( $_GET[ $filter_type ] ) ) ) : []; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$option_link   = self::get_base_url() . '/' . $filter_type . '/';

		foreach ( $options as $option => $option_name ) {
			$unique_id    = substr( uniqid(), -6 );
			$active_tax   = in_array( $option, $active_option, true ) ? ' checked' : '';
			$active_group = in_array( $option, $active_option, true ) ? ' active' : '';

			$html .= '
					<li class="rtsb-term-item">
						<div class="rtsb-default-filter-group' . esc_attr( $active_group ) . '">
							<input type="' . esc_attr( $input ) . '" class="rtsb-default-filter-trigger rtsb-' . esc_attr( $input ) . '-filter rtsb-term-' . esc_attr( $option ) . esc_attr( $active_tax ) . '"  name="rtsb-filter-' . esc_attr( $filter_type ) . '[]" id="rtsb-term-default-filter-' . $unique_id . '" value="' . esc_attr( $option ) . '" ' . $active_tax . '>
							<label class="rtsb-default-' . esc_attr( $input ) . '-filter-label" for="rtsb-term-default-filter-' . $unique_id . '">
								<span class="name">' . esc_html( $option_name ) . '</span>
							</label>
						</div>
					</li>';
		}

		$html .= '</ul>';

		return $html;
	}
	/**
	 * Get the count of products with a specific star rating.
	 *
	 * @param int $star_rating The star rating value.
	 *
	 * @return int
	 */
	public static function get_product_rating_count( $star_rating ) {
		$args = [
			'status'         => 'publish',
			'limit'          => -1,
			'product_rating' => sprintf( '%.1f', $star_rating ),
		];

		if ( is_product_taxonomy() ) {
			$term      = get_queried_object();
			$term_type = 'product_cat' === $term->taxonomy ? 'product_category_id' : 'product_tag_id';

			if ( $term instanceof WP_Term ) {
				$args[ $term_type ] = [ $term->term_id ];
			}
		}

		$query    = new WC_Product_Query( $args );
		$products = $query->get_products();

		return count( $products );
	}
	/**
	 * Get attribute filter HTML.
	 *
	 * @param array  $data Array of required data.
	 * @param string $input Type of input field for the filter (e.g., color).
	 *
	 * @return string The generated HTML for the attribute filter list.
	 */
	public static function get_attribute_filter_list_html( $data, $input = 'color' ) {
		if ( ! function_exists( 'rtwpvs' ) ) {
			return '';
		}

		$terms            = $data['terms'];
		$term_info        = $data['term_info'];
		$show_tooltips    = $data['show_tooltips'];
		$show_empty_terms = $data['show_empty_terms'];
		$counter          = 0;

		$html = '<ul class="product-default-filters rtsb-terms-wrapper ' . esc_attr( $term_info['type'] ) . '-variable-wrapper' . esc_attr( $data['show_label'] ) . '">';

		foreach ( $terms as $attr_term ) {
			$count          = $attr_term->count;
			$term_link      = remove_query_arg( $term_info['filter_name'], $term_info['base_link'] );
			$term_link      = add_query_arg( $term_info['filter_name'], $attr_term->slug, $term_link );
			$unique_id      = substr( uniqid(), -6 );
			$name           = 'term-' . wc_variation_attribute_name( $term_info['attribute'] ) . '-' . $unique_id;
			$selected_class = in_array( $attr_term->slug, $term_info['current_filter'], true ) ? ' selected' : '';
			$selected_item  = in_array( $attr_term->slug, $term_info['current_filter'], true ) ? ' checked' : '';
			$active_class   = in_array( $attr_term->slug, $term_info['current_filter'], true ) ? ' active' : '';

			$args = [
				'id'             => $name,
				'name'           => $attr_term->name,
				'link'           => $term_link,
				'type'           => $term_info['type'],
				'term_id'        => $attr_term->term_id,
				'term_slug'      => $attr_term->slug,
				'taxonomy'       => wc_variation_attribute_name( $term_info['attribute'] ),
				'tooltips'       => $show_tooltips,
				'attribute_name' => $term_info['attribute'],
				'selected_item'  => $selected_item,
			];

			if ( 'archive' === apply_filters( 'rtsb/builder/set/current/page/type', '' ) && ! empty( $data['count_display'] ) && 'block' === $data['count_display'] ) {
				$count = self::count_tax_data( $args, $attr_term->count );
			}

			$args['count'] = $count;

			if ( ! $show_empty_terms && 0 === $count ) {
				continue;
			}

			$html .= '<li class="rtsb-default-filter-term-item term-item-' . esc_attr( $attr_term->term_id ) . ' rtsb-term rtsb-default-' . esc_attr( $input ) . '-term ' . esc_attr( $term_info['type'] ) . '-variable-term-' . esc_attr( $attr_term->slug ) . esc_attr( $selected_class ) . '">';
			$html .= '<div class="rtsb-default-filter-group' . esc_attr( $active_class ) . '">';
			$html .= self::get_input_type_html( $input, $args );

			$counter++;

			$html .= '</div>';
			$html .= '</li>';
		}

		$html .= '</ul>';

		return $html;
	}
	/**
	 * Get input type HTML.
	 *
	 * @param string $input Input type.
	 * @param array  $args Args.
	 * @return mixed|string|null
	 */
	public static function get_input_type_html( $input, $args ) {
		$html  = '';
		$count = $args['count'];

		switch ( $input ) {
			case 'color':
				$color = sanitize_hex_color( get_term_meta( $args['term_id'], 'product_attribute_color', true ) );

				$html .= sprintf(
					// phpcs:ignore Generic.Strings.UnnecessaryStringConcat.Found
					'<input type="checkbox"  id="' . esc_attr( $args['id'] ) . '" value="' . esc_attr( $args['term_slug'] ) . '" class="rtsb-attr-hidden-field" name="rtsb-filter-' . esc_attr( $args['attribute_name'] ) . '[]' . '" ' . esc_attr( $args['selected_item'] ) . '/><label for="%s" title="' . esc_attr( $args['name'] ) . '" class="rtsb-default-filter-trigger rtsb-attr-filter rtsb-color-filter rtsb-term-span rtsb-term-span-%s%s"><span class="default-filter-attr-color" style="background-color:%s;"></span><span class="default-filter-attr-name">%s</span></label><div class="rtsb-count">(%s)</div>',
					esc_attr( $args['id'] ),
					esc_attr( $args['type'] ),
					$args['tooltips'] ? esc_attr( ' tipsy' ) : '',
					esc_attr( $color ),
					esc_html( $args['name'] ),
					absint( $count )
				);
				break;

			case 'image':
				if ( ! function_exists( 'rtwpvs' ) ) {
					return $html;
				}

				$attachment_id = absint( get_term_meta( $args['term_id'], 'product_attribute_image', true ) );
				$image_size    = rtwpvs()->get_option( 'attribute_image_size' );
				$image_url     = wp_get_attachment_image_url( $attachment_id, apply_filters( 'rtwpvs_product_attribute_image_size', $image_size ) );

				$html .= sprintf(
					// phpcs:ignore Generic.Strings.UnnecessaryStringConcat.Found
					'<input type="checkbox" id="' . esc_attr( $args['id'] ) . '" value="' . esc_attr( $args['term_slug'] ) . '" class="rtsb-attr-hidden-field" name="rtsb-filter-' . esc_attr( $args['attribute_name'] ) . '[]' . '" ' . esc_attr( $args['selected_item'] ) . '/><label for="%s" title="' . esc_attr( $args['name'] ) . '" class="rtsb-default-filter-trigger rtsb-image-filter rtsb-term-span rtsb-term-span-%s%s"><img class="rtsb-default-attr-filter" alt="%s" src="%s" /></label>',
					esc_attr( $args['id'] ),
					esc_attr( $args['type'] ),
					$args['tooltips'] ? esc_attr( ' tipsy' ) : '',
					esc_attr( $args['name'] ),
					esc_url( $image_url )
				);
				break;

			case 'button':
				$html .= sprintf(
					// phpcs:ignore Generic.Strings.UnnecessaryStringConcat.Found
					'<input  id="' . esc_attr( $args['id'] ) . '" type="checkbox" value="' . esc_attr( $args['term_slug'] ) . '" class="rtsb-attr-hidden-field" name="rtsb-filter-' . esc_attr( $args['attribute_name'] ) . '[]' . '" ' . esc_attr( $args['selected_item'] ) . '/><label for="%s" title="' . esc_attr( $args['name'] ) . '" class="rtsb-default-filter-trigger rtsb-attr-filter rtsb-button-filter rtsb-term-span rtsb-term-span-%s%s"><span class="default-filter-attr-name">%s</span><div class="rtsb-count">(%s)</div></label>',
					esc_attr( $args['id'] ),
					esc_attr( $args['type'] ),
					$args['tooltips'] ? esc_attr( ' tipsy' ) : '',
					esc_html( $args['name'] ),
					absint( $count )
				);
				break;

			default:
		}

		return apply_filters( 'rtsb/elementor/default/filter/get_input_type_html', $html );
	}
	/**
	 * Get reset button.
	 *
	 * @param string $btn_text Button text.
	 * @param array  $settings Settings array.
	 *
	 * @return string
	 */
	public static function get_default_filter_reset_button( $btn_text, $settings ) {
		$html = '';

		ob_start();

		$active     = ! empty( $_SERVER['QUERY_STRING'] ) ? ' active' : '';
		$reset      = ! empty( $settings['reset_btn'] );
		$apply_text = ! empty( $settings['apply_filters_btn_text'] ) ? $settings['apply_filters_btn_text'] : esc_html__( 'Apply Filters', 'shopbuilder' );
		$apply_icon = ! empty( $settings['apply_filters_btn_icon'] ) ? $settings['apply_filters_btn_icon'] : [];
		echo '<div class="default-filter-btn-wrapper">';
		?>
			<?php if ( $settings['show_filter_btn'] ) { ?>
				<div class="rtsb-product-default-filters rtsb-apply-filters-btn">
					<div class="reset-btn-item">
						<button class="rtsb-apply-filters init">
							<span class="icon"><?php Fns::print_html( Fns::icons_manager( $apply_icon, 'icon-default' ) ); ?></span>
							<span class="text reset-text"><?php echo esc_html( $apply_text ); ?></span>
							<span></span>
						</button>
					</div>
				</div>
			<?php } ?>
			<?php
			if ( $reset ) {
				?>
				<div class="rtsb-product-default-filters rtsb-reset<?php echo esc_attr( $active ); ?>">
					<div class="reset-btn-item">
						<button class="product-default-filter-reset">
							<span class="text reset-text"><?php echo esc_html( $btn_text ); ?></span>
							<span></span>
						</button>
					</div>
				</div>
				<?php
			}
			echo '</div>';

			$html .= ob_get_clean();

			return $html;
	}
}
