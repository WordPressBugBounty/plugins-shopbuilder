<?php
/**
 * Elementor Render Class.
 *
 * This class contains render logics & output. It utilizes WC_Products_Query().
 *
 * @package RadiusTheme\SB
 */

namespace RadiusTheme\SB\Elementor\Render;

use WC_Query;
use WP_Query;
use WC_Product;
use Elementor\Utils;
use WC_Product_Query;
use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Models\QueryArgs;
use RadiusTheme\SB\Helpers\BuilderFns;
use RadiusTheme\SB\Traits\SingletonTrait;
use RadiusTheme\SB\Traits\ActionBtnTraits;
use RadiusTheme\SB\Elementor\Helper\RenderHelpers;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Elementor Render Class.
 */
class Render {
	/**
	 * Singleton Trait.
	 */
	use SingletonTrait;

	/**
	 * Action button trait.
	 */
	use ActionBtnTraits;

	/**
	 * HTML.
	 *
	 * @var string
	 */
	private $html = null;

	/**
	 * Settings array
	 *
	 * @var array
	 */
	public $get_settings;

	/**
	 * Element attributes.
	 *
	 * @var array
	 */
	private $attributes = [];

	/**
	 * Get elementor settings.
	 *
	 * @return array
	 */
	public function get_settings_for_display() {
		return $this->get_settings;
	}

	/**
	 * Container Wrapper HTML.
	 *
	 * @param string $content The content.
	 * @param array  $settings Settings.
	 * @param string $class Class.
	 * @param string $template Template name.
	 * @param bool   $fullwidth Fullwidth check.
	 * @param bool   $ajax Ajax attributes.
	 *
	 * @return string
	 */
	public function container( $content, $settings, $class = '', $template = '', $fullwidth = false, $ajax = true ) {
		$rand         = wp_rand();
		$layout_id    = 'rtsb-container-' . $rand;
		$metas        = RenderHelpers::meta_dataset( $settings, $template );
		$raw_layout   = esc_html( $metas['layout'] );
		$layout       = RenderHelpers::filter_layout( $raw_layout, $template );
		$style_attr   = '--rtsb-default-columns: ' . esc_attr( RenderHelpers::default_columns( $layout ) );
		$view_mode    = $settings['view_mode'] ?? 'grid';
		$view_mode    = isset( $_GET['displayview'] ) ? sanitize_text_field( wp_unslash( $_GET['displayview'] ) ) : $view_mode; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tooltip_attr = 'list' == $view_mode && ! empty( $metas['tooltip_position_list'] ) ? esc_attr( $metas['tooltip_position_list'] ) : $metas['tooltip_position'];
		$has_pro      = rtsb()->has_pro();
		$has_filters  = $has_pro && ! empty( $settings['tax_filter'] );

		// Set default layout.
		if ( ! $has_pro && ! in_array( $layout, array_keys( Fns::free_layouts() ), true ) ) {
			$layout = RenderHelpers::set_default_layout( $layout );
		}

		// Container classes.
		$container_classes = RenderHelpers::prepare_container_classes( $metas, $settings, $class );

		// Pagination attributes.
		$pagination_attributes = RenderHelpers::prepare_pagination_attr( $layout, $has_filters, $template, $settings, $ajax );

		// Attributes for the container.
		$container_attributes = [
			'id'                    => $layout_id,
			'class'                 => $container_classes,
			'style'                 => apply_filters( 'rtsb/product/container/attr', $style_attr, $settings ),
			'data-layout'           => $layout,
			'data-tooltip-position' => $tooltip_attr,
		];

		if ( ! empty( $pagination_attributes ) ) {
			$container_attributes['data-rtsb-ajax'] = $pagination_attributes;
		}

		// Adding render attributes.
		$this->add_attribute( 'rtsb_container_attr_' . $rand, $container_attributes );

		// Start rendering.
		$this->html = '<div ' . $this->get_attribute_string( 'rtsb_container_attr_' . $rand ) . '>';

		// Section title.
		$this->html .= $this->render_section_title( $settings );

		// Content.
		$this->html .= $content;

		// End rendering.
		$this->html .= '</div><!-- .rtsb-elementor-container -->';

		// Action button icon reset.
		$this->action_button_icon_set_default();

		// Script generator.
		$script_generator               = [];
		$script_generator['layout']     = $layout_id;
		$script_generator['rand']       = absint( $rand );
		$script_generator['isCarousel'] = preg_match( '/slider/', $layout );

		// Register scripts in the wp_footer action.
		add_action(
			'wp_footer',
			static function () use ( $script_generator ) {
				RenderHelpers::register_scripts( $script_generator );
			}
		);

		return $this->html;
	}

	/**
	 * Row Wrapper HTML.
	 *
	 * @param string $content The content.
	 * @param array  $settings Settings.
	 * @param string $hook_html Hook HTML.
	 * @param array  $raw_settings Raw Settings.
	 *
	 * @return string
	 */
	public function row( $content, $settings, $hook_html = '', $raw_settings = [] ) {
		$raw_layout = esc_html( $settings['layout'] );
		$layout     = RenderHelpers::filter_layout( $raw_layout );

		$rand        = wp_rand();
		$is_carousel = preg_match( '/slider/', $raw_layout );

		if ( preg_match( '/category/', $raw_layout ) && ( ! empty( $settings['active_cat_slider'] ) ) ) {
			$is_carousel = true;
		}

		// Row classes.
		$row_classes = RenderHelpers::prepare_row_classes( $settings );

		// Adding render attributes.
		$this->add_attribute( 'rtsb_row_attr_' . $rand, 'class', $row_classes );

		// Start rendering.
		$row_wrapper   = '<div ' . $this->get_attribute_string( 'rtsb_row_attr_' . $rand ) . '>';
		$start_wrapper = apply_filters( 'rtsb/elementor/row/start', $row_wrapper, $raw_settings );

		$this->html = ! empty( $settings['show_filter'] ) ? $hook_html . $start_wrapper : $start_wrapper;

		if ( $is_carousel ) {
			$slider_data = RenderHelpers::slider_data( $settings, $raw_settings );

			// Adding slider render attributes.
			$this->add_attribute(
				'rtsb_slider_attr_' . $rand,
				[
					'class'        => 'rtsb-col-grid ' . $slider_data['class'],
					'data-options' => $slider_data['data'],
				]
			);

			$this->html .= '<div ' . $this->get_attribute_string( 'rtsb_slider_attr_' . $rand ) . '>';
			$this->html .= '<div class="swiper-wrapper">';
		}

		$this->html .= $content;

		if ( $is_carousel ) {
			$this->html .= '</div><!-- .swiper-wrapper -->';

			// Slider buttons.
			$this->html .= $this->render_slider_buttons( $settings );
			$this->html .= '</div><!-- .rtsb-carousel-slider -->';
		}

		$row_end = '</div><!-- .rtsb-row -->';

		$this->html .= apply_filters( 'rtsb/elementor/row/end', $row_end );

		// Preloader.
		$this->html .= RenderHelpers::pre_loader( $layout );

		return $this->html;
	}

	/**
	 * Product loop.
	 *
	 * @param object $products Products.
	 * @param array  $settings Settings.
	 * @param string $template Template.
	 *
	 * @return string|null
	 */
	public function product_loop( $products, $settings, $template ) {
		$html = null;

		if ( empty( $products ) ) {
			return null;
		}

		$i = 0;

		foreach ( $products as $product ) {
			$i++;
			$GLOBALS['product'] = $product;
			$layout             = RenderHelpers::filter_layout( esc_html( $settings['layout'] ), $template );

			/**
			 * Before product template render hook.
			 */
			do_action( 'rtsb_before_product_template_render' );

			// Loop arg.
			$arg = $this->arg_dataset( $settings, $product, $settings['lazy_load'], $i );

			// Get template.
			$html .= Fns::load_template( $template . $layout, $arg, true );

			/**
			 * After product template render hook.
			 */
			do_action( 'rtsb_after_product_template_render' );

			unset( $GLOBALS['product'] );
		}

		return $html;
	}

	/**
	 * WC Product loop.
	 *
	 * @param object $query The query.
	 * @param array  $settings Settings.
	 * @param string $template Template.
	 *
	 * @return string|null
	 */
	public function wc_product_loop( $query, $settings, $template ) {
		$html = null;

		if ( empty( $query ) ) {
			return null;
		}

		$i = 0;

		while ( $query->have_posts() ) {
			$query->the_post();
			$i++;

			// Loop arg.
			$arg = $this->arg_dataset( $settings, wc_get_product( get_the_ID() ), $settings['lazy_load'], $i );

			// Get template.
			$html .= Fns::load_template( $template . $settings['layout'], $arg, true );
		}

		return $html;
	}

	/**
	 * Category loop.
	 *
	 * @param object $query The query.
	 * @param array  $settings Settings.
	 * @param string $template Template.
	 * @param bool   $is_single Single category.
	 *
	 * @return string|null
	 */
	public function category_loop( $query, $settings, $template, $is_single = false ) {
		$html          = null;
		$category_loop = false;

		if ( 'category-layout3' === $settings['raw_settings']['layout'] || 'category-single-layout2' === $settings['raw_settings']['layout'] ) {
			$category_loop = true;
		}

		if ( $is_single ) {
			// Loop arg.
			$arg = $this->cat_arg_dataset( $settings, $query, true, false, $category_loop );

			// Get template.
			$html .= Fns::load_template( $template . $settings['layout'], $arg, true );
		} else {
			foreach ( $query as $term ) {
				// Loop arg.
				$arg = $this->cat_arg_dataset( $settings, $term, false, false, $category_loop );

				// Get template.
				$html .= Fns::load_template( $template . $settings['layout'], $arg, true );
			}
		}

		return $html;
	}

	/**
	 * Render Product View.
	 *
	 * @param string $template Template name.
	 * @param array  $settings Control settings.
	 *
	 * @return string
	 */
	public function product_view( $template, $settings ) {
		$this->get_settings = $settings;

		$metas       = RenderHelpers::meta_dataset( $settings, $template, $this->get_settings_for_display() );
		$is_carousel = preg_match( '/slider/', $metas['layout'] );
		$pagination  = $metas['pagination'];
		$hook_html   = '';

		// Query Args.
		$args = ( new QueryArgs() )->buildArgs( $metas, 'product', $is_carousel );

		$temp_args    = $args;
		$default_args = [
			'visibility' => 'visible',
			'status'     => 'publish',
		];
		$custom_args  = apply_filters( 'rtsb/elementor/args_before_loop', $args, $metas );
		$args         = wp_parse_args( $custom_args, $default_args );

		/**
		 * Before Product query hook.
		 *
		 * @hooked RadiusTheme\SB\Controllers\Hooks\ActionHooks::modify_wc_query_args 10
		 */
		do_action( 'rtsb/elements/render/before_query', $settings, $args );

		// Query.
		$product_query = new WC_Product_Query( $args );

		/**
		 * Product query hook.
		 */
		do_action( 'rtsb/elements/render/product_query', $product_query, $this );

		$products = $pagination ? $product_query->get_products()->products : $product_query->get_products();

		if ( ! empty( $products ) ) {
			// Start rendering.
			$hook_html .= $this->render_start( $metas, $settings, $temp_args );

			// Row.
			$this->row( $this->product_loop( $products, $metas, $template ), $metas, $hook_html, $settings );

			// Pagination.
			if ( $pagination ) {
				$pagination_args = [
					'query'    => $product_query,
					'meta'     => $metas,
					'limit'    => $settings['posts_limit'],
					'per_page' => $settings['pagination_per_page'],
				];

				$this->html .= $this->render_products_pagination( $pagination_args );
			}

			// End rendering.
			$this->html .= $this->render_end( $metas, $settings, $products );
		} else {
			$this->no_products_msg( $metas );
		}

		wp_reset_postdata();

		unset( $args['tax_query'] );

		/**
		 * After Product query hook.
		 *
		 * @hooked RadiusTheme\SB\Controllers\Hooks\ActionHooks::reset_wc_query_args 10
		 */
		do_action( 'rtsb/elements/render/after_query', $settings, $args );

		// Container.
		$this->container( $this->html, $settings, 'rtsb-products-container', $template, $is_carousel );

		return $this->html;
	}

	/**
	 * Render Product View.
	 *
	 * @param string $template Template name.
	 * @param array  $settings Control settings.
	 * @param object $widget Reference widget.
	 *
	 * @return string
	 */
	public function wc_loop_for_product_view( $template, $settings, $widget ) {
		global $wp_query, $post;

		$this->get_settings = $settings;
		$rand               = wp_rand();
		$metas              = RenderHelpers::meta_dataset( $settings, $template, $widget->get_settings_for_display() );

		$settings['rtsb_order']   = ! empty( $wp_query->query_vars['order'] ) ? $wp_query->query_vars['order'] : 'ASC';
		$settings['rtsb_orderby'] = ! empty( $wp_query->query_vars['orderby'] ) ? $wp_query->query_vars['orderby'] : 'menu_order';

		$pagination = $metas['pagination'];

		$page_type      = BuilderFns::builder_type( get_the_ID() );
		$is_preview     = BuilderFns::is_builder_preview() && array_key_exists( $page_type, BuilderFns::builder_page_types() );
		$posts_per_page = wc_get_default_products_per_row() * wc_get_default_product_rows_per_page();

		if ( $is_preview ) {
			$main_query = clone $wp_query;
			$main_post  = clone $post;

			$ordering      = ( new WC_Query() )->get_catalog_ordering_args();
			$wp_query_args = [
				'post_type'      => 'product',
				'posts_per_page' => $posts_per_page,
			];

			if ( ! empty( $ordering['orderby'] ) ) {
				$wp_query_args['orderby'] = $ordering['orderby'];
			}

			if ( ! empty( $ordering['order'] ) ) {
				$wp_query_args['order'] = $ordering['order'];
			}

			$wp_query = new WP_Query( $wp_query_args ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

			wc_set_loop_prop( 'total', $wp_query->found_posts );
			wc_set_loop_prop( 'total_pages', $wp_query->max_num_pages );
		}

		if ( Fns::product_has_applied_filters( 'shop' ) || Fns::product_has_applied_filters( 'archive' ) || 'rtsb_builder' === get_post_type( get_the_ID() ) ) {
			echo '<div class="rtsb-active-filters-wrapper"></div>';
		}

		// column_per_row.
		if ( woocommerce_product_loop() ) {
			if ( wc_get_loop_prop( 'total' ) ) {

				// Row.
				$this->row( $this->wc_product_loop( $wp_query, $metas, $template ), $metas, '', $settings );
			}

			// Pagination.
			$this->html .= '<div class="rtsb-archive-pagination-wrap">';

			if ( $pagination ) {
				if ( Fns::product_filters_has_ajax( apply_filters( 'rtsb/builder/set/current/page/type', '' ) ) ) {
					if ( $wp_query->max_num_pages > 1 ) {
						$this->add_attribute(
							'rtsb_load_more_btn_attr_' . $rand,
							[
								'data-paged'    => '2',
								'data-max-page' => $wp_query->max_num_pages,
							]
						);

						$this->html .=
						"<div class='rtsb-load-more rtsb-pos-r'>
							<button {$this->get_attribute_string( 'rtsb_load_more_btn_attr_' . $rand )}>
								<span>" . esc_html__( 'Load More', 'shopbuilder' ) . "</span>
							</button>
							<div class='rtsb-loadmore-loading rtsb-ball-clip-rotate'><div></div></div>
						</div>";
					}
				} else {
					$pagination_args = [
						'query'    => $wp_query,
						'meta'     => $metas,
						'limit'    => wc_get_loop_prop( 'total' ),
						'per_page' => $posts_per_page,
					];

					$this->html .= $this->render_products_pagination( $pagination_args, 'wp' );
				}
			}

			$this->html .= '</div>';
		} else {
			$this->no_products_msg( $metas );
		}

		if ( $is_preview ) {
			// phpcs:disable
			$wp_query = $main_query;
			$post     = $main_post;

			wp_reset_query();
			wp_reset_postdata();
			// phpcs:enable
		}

		// Container.
		$this->container( $this->html, $settings, 'rtsb-products-container', $template );

		return $this->html;
	}

	/**
	 * Loop Setup for Product View.
	 *
	 * @param string $template Template name.
	 * @param array  $settings Control settings.
	 * @param object $widget Widget object.
	 *
	 * @return string
	 */
	public function setup_loop_for_product_view( $template, $settings, $widget ) {
		if ( empty( $settings['query_products'] ) ) {
			return $this->html;
		}

		$html = null;

		$metas = RenderHelpers::meta_dataset( $settings, '', $widget->get_settings_for_display() );

		foreach ( $settings['query_products'] as $_product ) {
			$post_object = get_post( $_product->get_id() );

			setup_postdata( $GLOBALS['post'] =& $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found

			// Loop arg.
			$arg = $this->arg_dataset( $metas, $_product );
			// Get template.
			$html .= Fns::load_template( $template . $settings['layout'], $arg, true );

			$this->row( $html, $metas, '', $settings );
		}

		// Container.
		$this->container( $this->html, $settings, 'rtsb-products-container', '', false, false );

		wp_reset_postdata();

		return $this->html;
	}

	/**
	 * Render Category View.
	 *
	 * @param string $template Template name.
	 * @param array  $settings Control settings.
	 * @param bool   $is_single Single category.
	 *
	 * @return string
	 */
	public function category_view( $template, $settings, $is_single = false ) {
		$this->get_settings = $settings;

		$metas    = RenderHelpers::meta_dataset( $settings, $template, $this->get_settings_for_display() );
		$taxonomy = $metas['category_source'];
		$term     = $metas['category_term'];

		if ( rtsb()->has_pro() && ( ! empty( $metas['tag_term'] ) ) ) {
			$term = $metas['tag_term'];
		}

		// Query.
		if ( $is_single ) {
			if ( empty( $term ) ) {
				return '<p>' . esc_html__( 'Please select a category.', 'shopbuilder' ) . '</p>';
			}

			$taxonomy_query = get_term( $term, $taxonomy );
		} else {
			// Query Args.
			$args = ( new QueryArgs() )->buildArgs( $metas, 'category' );

			$taxonomy_query = get_terms( $args );
		}

		if ( ! is_wp_error( $taxonomy_query ) && ! empty( $taxonomy_query ) ) {
			// Row.
			$this->row( $this->category_loop( $taxonomy_query, $metas, $template, $is_single ), $metas );
		} else {
			$this->html .= '<p>' . esc_html__( 'No categories found', 'shopbuilder' ) . '</p>';
		}

		// Container.
		$this->container( $this->html, $settings, 'rtsb-categories-container', $is_single );

		return $this->html;
	}

	/**
	 * Render Category View.
	 *
	 * @param string $template Template name.
	 * @param array  $settings Control settings.
	 *
	 * @return string
	 */
	public function social_share_view( $template, $settings ) {
		$this->get_settings = $settings;
		$share_items        = [];

		foreach ( $settings['share_platforms'] as $platform ) {
			$share_items[] = [
				'share_items' => $platform['share_items'],
				'share_text'  => $platform['share_text'],
			];
		}

		if ( ! empty( $share_items ) && is_array( $share_items ) ) {
			$arg['p_id']           = get_the_ID();
			$arg['share_items']    = $share_items;
			$arg['preset']         = $settings['layout'];
			$arg['direction']      = ! empty( $settings['layout_direction'] ) ? $settings['layout_direction'] : 'horizontal';
			$arg['show_icon']      = ! empty( $settings['show_share_icon'] );
			$arg['show_text']      = ! empty( $settings['show_share_text'] );
			$arg['show_pre_text']  = ! empty( $settings['show_share_pre_text'] );
			$arg['share_pre_text'] = ! empty( $settings['share_pre_text'] ) ? $settings['share_pre_text'] : '';
			$arg['raw_settings']   = $this->get_settings_for_display();
			$this->html            = Fns::load_template( $template, $arg, true );
		} else {
			$this->html = '<p>' . esc_html__( 'Please select a social sharing platform.', 'shopbuilder' ) . '</p>';
		}

		return $this->html;
	}

	/**
	 * Builds an array with meta values.
	 *
	 * @param array  $meta Meta values.
	 * @param object $product Product object.
	 * @param bool   $lazy_load Lazy Load.
	 * @param int    $i Integer.
	 *
	 * @return array
	 */
	public function arg_dataset( array $meta, object $product, bool $lazy_load = false, $i = 0 ) {
		$post_id = $product->get_id();
		$arg     = $this->build_product_arg(
			[
				'product'   => $product,
				'id'        => $product->get_id(),
				'type'      => $product->get_type(),
				'meta'      => $meta,
				'lazy_load' => $lazy_load,
				'i'         => $i,
			]
		);

		return apply_filters( 'rtsb/elementor/render/arg_dataset', $arg, $post_id, $meta, $lazy_load );
	}

	/**
	 * Builds an array with meta values for WP_Query.
	 *
	 * @param array $meta Meta values.
	 * @param int   $post_id Post ID.
	 * @param bool  $lazy_load Lazy Load.
	 * @param int   $i Integer.
	 *
	 * @return array
	 */
	public function wp_arg_dataset( array $meta, int $post_id, bool $lazy_load = false, $i = 0 ) {
		if ( ( empty( $meta ) && ! $post_id ) || ! in_array( get_post_type( $post_id ), [ 'product_variation', 'product' ] ) ) {
			return [];
		}

		global $product;

		if ( ! $product instanceof WC_Product && $post_id ) {
			$product = wc_get_product( $post_id );
		}

		$post_id = $product->get_id();
		$arg     = $this->build_product_arg(
			[
				'product'   => $product,
				'id'        => $post_id,
				'type'      => $product->get_type(),
				'meta'      => $meta,
				'lazy_load' => $lazy_load,
				'i'         => $i,
			]
		);

		return apply_filters( 'rtsb/elementor/render/arg_dataset', $arg, $post_id, $meta, $lazy_load );
	}

	/**
	 * Builds an array with meta values.
	 *
	 * @param array  $meta Meta values.
	 * @param Object $query Category object.
	 * @param bool   $fullwidth Fullwidth check.
	 * @param bool   $lazy_load Lazy Load.
	 * @param bool   $category_loop Category loop.
	 *
	 * @return array
	 */
	public function cat_arg_dataset( $meta, $query = null, $fullwidth = false, $lazy_load = false, $category_loop = false ) {
		if ( empty( $meta ) ) {
			return [];
		}

		$arg = [];

		$products                = null;
		$arg['class']            = null;
		$arg['grid']             = null;
		$arg['anchor_class']     = null;
		$arg['p_sale']           = null;
		$arg['count']            = max( $query->count, 0 );
		$arg['count_position']   = $meta['count_position'];
		$arg['excerpt_position'] = $meta['cat_excerpt_position'];
		$arg['cat_link']         = get_term_link( $query );
		$arg['target']           = '_self';
		$arg['raw_settings']     = ! empty( $meta['raw_settings'] ) ? $meta['raw_settings'] : [];

		if ( ! empty( $meta['tag_term'] ) ) {
			$arg['tag_term'] = $meta['tag_term'];
		}

		if ( ! empty( $meta['custom_link']['url'] ) ) {
			$arg['cat_link'] = $meta['custom_link']['url'];
			$arg['target']   = $meta['custom_link']['is_external'] ? '_blank' : '_self';
		}

		$title   = $query->name;
		$excerpt = $query->description;

		if ( in_array( 'badges', $meta['visibility'], true ) ) {
			$arg['p_sale'] = $meta['custom_badge_text'];
		}

		$arg['badge_class'] = RenderHelpers::badge_class( $meta['badge_preset'] );

		if ( $arg['count'] >= 0 ) {
			$prefix = ! empty( $meta['before_count'] ) ? $meta['before_count'] : '';
			$suffix = ! empty( $meta['after_count'] ) ? $meta['after_count'] : '';

			$arg['count'] = $prefix . $arg['count'] . $suffix;
		}

		if ( $meta['show_custom_title'] && ! empty( $meta['cat_custom_title'] ) ) {
			$title = $meta['cat_custom_title'];
		}

		if ( $meta['show_custom_excerpt'] && ! empty( $meta['cat_custom_excerpt'] ) ) {
			$excerpt = $meta['cat_custom_excerpt'];
		}

		$arg['c_img']         = $meta['c_img'];
		$arg['items']         = $meta['visibility'];
		$arg['title_tag']     = $meta['title_tag'];
		$arg['title_class']   = 'category-title rtsb-text-limit limit-' . $meta['title_limit'] . ( ! $meta['title_link'] ? ' no-link' : '' );
		$arg['title']         = Fns::text_truncation( $title, $meta['title_limit_custom'] );
		$arg['excerpt_limit'] = $meta['excerpt_limit'];
		$arg['excerpt']       = wpautop( Fns::text_truncation( $excerpt, $meta['excerpt_limit_custom'] ) );
		$arg['title_link']    = $meta['title_link'];
		$arg['image_link']    = $meta['image_link'];
		$arg['grid']         .= 'rtsb-col-grid ';

		if ( ! $fullwidth ) {
			$arg['class'] .= ' even-grid-item ';
		}

		if ( ! empty( $meta['active_cat_slider'] ) ) {
			$arg['class'] .= ' rtsb-slide-item swiper-slide animated rtFadeIn ';
		}

		$arg['class'] .= 'rtsb-category-grid';

		ob_start();
		do_action( 'rtsb/categories/category_image_override', $meta, $query->term_id );
		$override_category_image = ob_get_clean();

		$arg['img_html'] = $meta['c_img'] ? Fns::get_product_image_html(
			'category',
			$query->term_id,
			$meta['f_img_size'],
			$meta['default_img_id'],
			$meta['custom_img_size'],
			$lazy_load,
			false,
			false,
			$override_category_image
		) : null;

		if ( $meta['show_custom_image'] && ! empty( $meta['custom_image'] ) ) {
			$arg['img_html'] = Fns::get_product_image_html(
				'custom',
				null,
				$meta['f_img_size'],
				$meta['custom_image'],
				$meta['custom_img_size'],
				$lazy_load
			);
		}

		$arg['excerpt_class'] = 'category-info' . ( 'block' === $meta['count_position'] ? ' block-count' : ' inline-count' ) . ( 'above' === $meta['cat_excerpt_position'] ? ' excerpt-above' : '' ) . ( empty( $arg['excerpt'] ) ? ' no-excerpt' : '' );

		if ( $category_loop ) {
			$query_args = [
				'product_category_id' => $query->term_id,
				'limit'               => -1,
				'order'               => 'DESC',
				'orderby'             => 'date',
			];

			$products_query = new WC_Product_Query( $query_args );
			$products       = $products_query->get_products();
		}

		return apply_filters( 'rtsb/elementor/render/cat_arg_dataset', $arg, $meta, $query, $fullwidth, $lazy_load, $products );
	}

	/**
	 * Building product args.
	 *
	 * @param array $args Args.
	 * @return array
	 */
	public function build_product_arg( $args ) {
		list(
			'product'   => $product,
			'id'        => $post_id,
			'type'      => $p_type,
			'meta'      => $meta,
			'lazy_load' => $lazy_load,
			'i'         => $i,
		) = $args;

		$arg                   = [];
		$arg['class']          = null;
		$arg['grid']           = null;
		$arg['anchor_class']   = null;
		$arg['hover_img_html'] = null;
		$arg['s_link']         = [];
		$arg['add_to_cart']    = '';
		$arg['p_counter']      = absint( $i );
		$arg['p_id']           = $post_id;
		$arg['product']        = $product;
		$arg['p_link']         = $product->get_permalink();
		$arg['p_sale']         = '';
		$arg['rating_args']    = [];
		$arg['has_attributes'] = $product->is_type( 'variable' ) && $product->get_attributes();
		$arg['raw_settings']   = ! empty( $meta['raw_settings'] ) ? $meta['raw_settings'] : [];
		$cart_icon_html        = '';
		$layout                = $meta['layout'];

		// Building the arg.
		$arg['target']              = '_self';
		$arg['f_img']               = $meta['f_img'];
		$arg['items']               = $meta['visibility'];
		$arg['title_tag']           = $meta['title_tag'];
		$arg['title_class']         = 'product-title rtsb-text-limit limit-' . $meta['title_limit'] . ( ! $meta['title_link'] ? ' no-link' : '' );
		$arg['title']               = Fns::text_truncation( $product->get_title(), $meta['title_limit_custom'] );
		$arg['excerpt_limit']       = $meta['excerpt_limit'];
		$arg['excerpt']             = wpautop( Fns::text_truncation( do_shortcode( get_post_field( 'post_excerpt', $post_id ) ), $meta['excerpt_limit_custom'] ) );
		$arg['title_link']          = $meta['title_link'];
		$arg['image_link']          = $meta['image_link'];
		$arg['out_of_stock']        = $meta['out_of_stock_badge_text'];
		$arg['action_btn_preset']   = $meta['btn_preset'];
		$arg['action_btn_position'] = $meta['btn_position'];
		$arg['swatch_position']     = $meta['swatch_position'];
		$arg['swatch_type']         = $meta['swatch_type'];
		$arg['tooltip_position']    = $meta['tooltip_position'];

		$is_list     = preg_match( '/list/', $layout );
		$is_carousel = preg_match( '/slider/', $layout );

		if ( ! $is_carousel ) {
			$arg['grid']  .= 'rtsb-col-grid ';
			$arg['class'] .= ' even-grid-item ';
		} else {
			$arg['grid'] .= 'rtsb-col-grid rtsb-col-full rtsb-slide-item swiper-slide ';
		}

		$d_cols = 0 === $meta['d_cols'] ? RenderHelpers::default_columns( $layout ) : $meta['d_cols'];
		$t_cols = 0 === $meta['t_cols'] ? 2 : $meta['t_cols'];
		$m_cols = 0 === $meta['m_cols'] ? 1 : $meta['m_cols'];

		$arg['grid'] .= 0 === $i % $d_cols ? ' row-last desktop-row-last' : '';
		$arg['grid'] .= 0 === $i % $t_cols ? ' row-last tablet-row-last' : '';
		$arg['grid'] .= 0 === $i % $m_cols ? ' row-last mobile-row-last' : '';

		$arg['class'] .= 'rtsb-product';

		if ( $is_list ) {
			$arg['class'] .= ' rtsb-product-list';
		}

		$arg['class'] .= ' animated rtFadeIn';

		if ( in_array( 'badges', $meta['visibility'], true ) ) {
			$badge_module  = ! empty( $meta['enable_badges_module'] );
			$arg['p_sale'] = Fns::is_module_active( 'product_badges' ) && $badge_module ? 'rtsb_yes' : Fns::get_promo_badge(
				$product,
				$meta['sale_badge_type'],
				$meta['sale_badge_text'],
				apply_filters( 'rtsb/elementor/render/out_of_stock_badge_text', $meta['out_of_stock_badge_text'] )
			);
		}

		$arg['badge_class'] = RenderHelpers::badge_class( $meta['badge_preset'] );

		$meta['add_to_cart_text'] = apply_filters( 'rtsb/elementor/render/add_to_cart_text', $meta['add_to_cart_text'], $product );

		if ( ! $meta['show_cart_text'] ) {
			$meta['add_to_cart_text']         = '';
			$meta['add_to_cart_success_text'] = '';
		}

		if ( in_array( 'add_to_cart', $meta['visibility'], true ) ) {
			if ( ! empty( $meta['add_to_cart_icon'] ) ) {
				$cart_icon_html .= Fns::icons_manager( $meta['add_to_cart_icon'], 'cart-icon' );
			}

			if ( ! empty( $meta['add_to_cart_success_icon'] ) ) {
				$cart_icon_html .= Fns::icons_manager( $meta['add_to_cart_success_icon'], 'cart-success-icon' );
			}

			$cart_icon = ! empty( $cart_icon_html ) ? '<span class="icon">' . $cart_icon_html . '</span>' : '';

			if ( $product->is_purchasable() || 'grouped' === $p_type || 'external' === $p_type ) {
				if ( $product->is_in_stock() ) {
					$arg['add_to_cart'] = $this->render_add_to_cart_button(
						[
							'link'      => $product->get_permalink(),
							'id'        => $post_id,
							'type'      => $p_type,
							'text'      => $meta['add_to_cart_text'],
							'success'   => $meta['add_to_cart_success_text'],
							'icon_html' => $cart_icon,
							'alignment' => $meta['add_to_cart_alignment'],
							'product'   => $product,
						]
					);
				}
			}
		}

		if ( in_array( 'quick_view', $meta['visibility'], true ) ) {
			add_filter( 'rtsb/module/quick_view/button_params', [ $this, 'button_classes' ] );
			add_filter( 'rtsb/module/quick_view/icon_html', [ $this, 'quick_view_icon' ] );
		}

		if ( in_array( 'compare', $meta['visibility'], true ) ) {
			add_filter( 'rtsb/module/compare/button_params', [ $this, 'button_classes' ] );
			add_filter( 'rtsb/module/compare/icon_html', [ $this, 'compare_icon' ] );
		}

		if ( in_array( 'wishlist', $meta['visibility'], true ) ) {
			add_filter( 'rtsb/module/wishlist/button_params', [ $this, 'button_classes' ] );
			add_filter( 'rtsb/module/wishlist/icon_html', [ $this, 'wishlist_icon' ] );
		}

		$arg['img_html'] = $meta['f_img'] ? Fns::get_product_image_html(
			'product',
			$post_id,
			$meta['f_img_size'],
			$meta['default_img_id'],
			$meta['custom_img_size'],
			$lazy_load
		) : null;

		if ( ! ( function_exists( 'rtwpvsp' ) && $product->is_type( 'variable' ) && $product->get_attributes() ) ) {
			$gallery_ids = $product->get_gallery_image_ids();

			if ( $meta['hover_img'] && ! empty( $gallery_ids ) ) {
				$arg['class'] .= ! $meta['gallery_img'] ? ' rtsb-double-img' : '';

				if ( ! rtsb()->has_pro() ) {
					$arg['class'] .= ' rtsb-double-img';
				}

				$arg['hover_img_html'] = Fns::get_product_image_html(
					'product',
					$gallery_ids[0],
					$meta['f_img_size'],
					null,
					$meta['custom_img_size'],
					$lazy_load,
					true
				);
			}
		}

		$arg['hover_btn_text'] = $meta['hover_btn_text'];

		if ( $meta['show_hover_btn_icon'] && ! empty( $meta['hover_btn_icon'] ) ) {
			$arg['hover_btn_icon'] = '<span class="icon">' . Fns::icons_manager( $meta['hover_btn_icon'], 'hover-btn-icon' ) . '</span>';
		}

		$arg['img_args'] = [
			'p_id'           => $arg['p_id'],
			'title'          => get_the_title(),
			'p_link'         => $arg['p_link'],
			'image_link'     => $arg['image_link'],
			'img_html'       => $arg['img_html'],
			'hover_img_html' => $arg['hover_img_html'],
			'meta'           => $meta,
		];

		return $arg;
	}

	/**
	 * Renders add to cart button.
	 *
	 * @param array $args Cart args.
	 *
	 * @return string
	 */
	public function render_add_to_cart_button( $args ) {
		list(
			'link'      => $link,
			'id'        => $id,
			'type'      => $type,
			'text'      => $text,
			'success'   => $success,
			'icon_html' => $icon_html,
			'alignment' => $alignment,
			'product'   => $product
		) = $args;

		$content  = null;
		$rand     = wp_rand();
		$ext_link = get_post_meta( $id, '_product_url', true );
		$btn_txt  = get_post_meta( $id, '_button_text', true );

		ob_start();
		do_action( 'rtsb/before/cart/button' );
		$content .= ob_get_clean();

		$cart_attr = [
			'class'           => 'rtsb-action-btn ' . $type . '-product tipsy icon-' . $alignment . ' ' . ( empty( $text ) ? 'no-text' : 'has-text' ),
			'href'            => esc_url( $link ),
			'rel'             => 'nofollow',
			'data-quantity'   => '1',
			'data-product_id' => absint( $id ),
			'data-id'         => absint( $id ),
		];

		if ( empty( $text ) ) {
			$tooltip_mapping = [
				'simple'   => esc_attr__( 'Add to Cart', 'shopbuilder' ),
				'variable' => esc_attr__( 'Select Options', 'shopbuilder' ),
				'grouped'  => esc_attr__( 'View Products', 'shopbuilder' ),
				'external' => ! empty( $btn_txt ) ? esc_html( $btn_txt ) : esc_html__( 'Buy Product', 'shopbuilder' ),
			];

			$cart_attr['title'] = $tooltip_mapping[ $type ];
		}

		$content .= '<div class="rtsb-wc-add-to-cart-wrap">';

		if ( 'variable' === $type || 'grouped' === $type ) {
			$cart_attr = apply_filters( 'rtsb/elementor/render/cart_attributes', $cart_attr, $product );

			$this->add_attribute( 'rtsb_add_to_cart_button_' . $id . $rand, $cart_attr );

			$text_btn = 'grouped' === $type ? __( 'View Products', 'shopbuilder' ) : __( 'Select Options', 'shopbuilder' );

			$content .= '<a ' . $this->get_attribute_string( 'rtsb_add_to_cart_button_' . $id . $rand ) . '>';
			$content .= $icon_html;
			$content .= '<span class="text ' . ( ! empty( $success ) ? 'has-success-text' : 'no-success-text' ) . '" data-success="' . esc_html( $success ) . '" data-cart-text="' . ( ! empty( $text ) ? esc_attr( $text ) : $tooltip_mapping['simple'] ) . '" data-variable-text="' . esc_attr( $text_btn ) . '">' . esc_html( $text_btn ) . '</span>';
			$content .= '<span></span>';
		} elseif ( 'external' === $type ) {
			if ( ! empty( $ext_link ) ) {
				$cart_attr['target'] = '_blank';
			}

			$cart_attr['href'] = ! empty( $ext_link ) ? esc_url( $ext_link ) : esc_url( $link );

			$this->add_attribute( 'rtsb_add_to_cart_button_' . $id . $rand, $cart_attr );

			$content .= '<a ' . $this->get_attribute_string( 'rtsb_add_to_cart_button_' . $id . $rand ) . '>';
			$content .= $icon_html;
			$content .= '<span class="text">' . ( ! empty( $btn_txt ) ? esc_html( $btn_txt ) : esc_html__( 'Buy Product', 'shopbuilder' ) ) . '</span>';
		} else {
			$cart_attr['href']      = $cart_attr['href'] . '?add-to-cart=' . absint( $id );
			$cart_attr['class']    .= ' rtsb-add-to-cart-btn';
			$cart_attr['data-type'] = $type;
			$cart_attr              = apply_filters( 'rtsb/elementor/render/cart_attributes', $cart_attr, $product );

			$this->add_attribute( 'rtsb_add_to_cart_button_' . $id . $rand, $cart_attr );

			$content .= '<a ' . $this->get_attribute_string( 'rtsb_add_to_cart_button_' . $id . $rand ) . '>';
			$content .= $icon_html;
			$content .= '<span class="text ' . ( ! empty( $success ) ? 'has-success-text' : 'no-success-text' ) . '" data-success="' . esc_html( $success ) . '">' . esc_html( $text ) . '</span>';
			$content .= '<span></span>';
		}

		$content .= '</a>';
		$content .= '</div>';

		ob_start();
		do_action( 'rtsb/after/cart/button' );
		$content .= ob_get_clean();

		return apply_filters( 'rtsb/render/cart/button', $content, $args );
	}

	/**
	 * Button classes.
	 *
	 * @param array $params Button params.
	 *
	 * @return array
	 */
	public function button_classes( $params ) {
		$params['classes'] = array_merge( $params['classes'], [ 'rtsb-action-btn', 'tipsy' ] );

		return $params;
	}

	/**
	 * Render the section title.
	 *
	 * @param array $settings The Elementor settings.
	 *
	 * @return string
	 */
	public function render_section_title( $settings ) {
		$html = '';
		$rand = wp_rand();

		if ( empty( $settings['show_section_title'] ) || ( empty( $settings['query_products'] ) && 0 === count( $settings['query_products'] ) ) ) {
			return $html;
		}

		// Set attributes for the section title.
		$this->add_attribute( 'rtsb_section_title_wrapper_' . $rand, 'class', 'rtsb-section-title-wrapper' );
		$this->add_attribute( 'rtsb_section_title_' . $rand, 'class', 'rtsb-section-title' );

		// Start rendering.
		$html .= '<div ' . $this->get_attribute_string( 'rtsb_section_title_wrapper_' . $rand ) . '>';
		$html .= '<' . esc_attr( $settings['section_title_tag'] ) . ' ' . $this->get_attribute_string( 'rtsb_section_title_' . $rand ) . '>';
		$html .= esc_html( $settings['section_title_text'] );
		$html .= '</' . esc_attr( $settings['section_title_tag'] ) . '>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Renders products pagination
	 *
	 * @param array  $args Pagination args.
	 * @param string $query_type Query type.
	 *
	 * @return string
	 */
	public function render_products_pagination( $args, $query_type = 'wc' ) {
		list(
			'query'    => $query,
			'meta'     => $meta,
			'limit'    => $limit,
			'per_page' => $per_page
		) = $args;

		$html_utility   = null;
		$html           = null;
		$ajax           = false;
		$is_grid        = preg_match( '/grid-layout/', $meta['layout'] ) || preg_match( '/list-layout/', $meta['layout'] );
		$posts_per_page = 'wp' === $query_type ? $query->query_vars['posts_per_page'] : null;
		$total_page     = 'wp' === $query_type ? $query->max_num_pages : $query->get_products()->max_num_pages;

		if ( $limit ) {
			if ( 'wc' === $query_type ) {
				$found_post = $query->get_products()->total;
			} elseif ( 'wp' === $query_type && ( empty( $wp_query->query['tax_query'] ) ) ) {
				$found_post = $query->found_posts;
			}

			if ( $per_page && $found_post > $per_page ) {
				$found_post = $limit;
				$total_page = ceil( $found_post / $per_page );
			}
		}

		$total_page = absint( $total_page );

		$args = [
			'total_page'     => $total_page,
			'posts_per_page' => 'wc' === $query_type ? $per_page : $posts_per_page,
			'ajax'           => $ajax,
		];

		if ( 'pagination' === $meta['posts_loading_type'] ) {
			if ( $is_grid ) {
				$html_utility .= Fns::custom_pagination(
					$total_page,
					'wc' === $query_type ? $per_page : $posts_per_page
				);
			}
		} else {
			ob_start();
			do_action( 'rtsb/elementor/render/pagination', $meta, $query, $args );
			$html_utility .= ob_get_clean();
		}

		if ( $html_utility ) {
			$rand = wp_rand();

			// Adding pagination render attributes.
			$this->add_attribute(
				'rtsb_pagination_attr_' . $rand,
				[
					'class'               => 'rtsb-pagination-wrap element-loading',
					'data-total-pages'    => $total_page,
					'data-posts-per-page' => 'wc' === $query_type ? $per_page : $posts_per_page,
					'data-type'           => $meta['posts_loading_type'],
				]
			);

			// Start rendering.
			$html  = '<div ' . $this->get_attribute_string( 'rtsb_pagination_attr_' . $rand ) . '>';
			$html .= $html_utility;
			$html .= '</div><!-- .rtsb-pagination-wrap -->';
		}

		return $html;
	}

	/**
	 * Renders slider buttons
	 *
	 * @param array $settings Settings array.
	 *
	 * @return string
	 */
	public function render_slider_buttons( $settings ) {
		$html       = '';
		$left_icon  = $settings['left_arrow_icon'];
		$right_icon = $settings['right_arrow_icon'];

		if ( ! empty( $settings['slider_nav'] ) ) {
			$html .=
				'<div class="swiper-nav">
					<div class="swiper-arrow swiper-button-next">
						' . Fns::icons_manager( $right_icon ) . '
					</div>
					<div class="swiper-arrow swiper-button-prev">
						' . Fns::icons_manager( $left_icon ) . '
					</div>
				</div>';
		}

		$html .= ! empty( $settings['slider_dot'] ) ? '<div class="swiper-pagination rtsb-pos-s"></div>' : '';

		return $html;
	}

	/**
	 * No products message.
	 *
	 * @param array $metas Meta data.
	 *
	 * @return void
	 */
	public function no_products_msg( $metas ) {
		$content =
			'<div class="rtsb-notice woocommerce-no-products-found">
				<div class="woocommerce-info">' . esc_html__( 'No products were found matching your selection.', 'shopbuilder' ) . '</div>
			</div>';

		$this->row( $content, $metas );
	}

	/**
	 * Start the rendering.
	 *
	 * @param array $metas      Meta Data.
	 * @param array $settings   Widget settings.
	 * @param array $temp_args  Additional temporary arguments for rendering.
	 *
	 * @return string
	 */
	public function render_start( $metas, $settings, $temp_args ) {
		ob_start();

		/**
		 * Before render row hook.
		 */
		do_action( 'rtsb/elementor/render/before_row', $metas, $settings, $temp_args, $this );

		return ob_get_clean();
	}

	/**
	 * End the rendering.
	 *
	 * @param array $metas      Meta Data.
	 * @param array $settings   Widget settings.
	 * @param array $products   Array of products being rendered.
	 *
	 * @return string
	 */
	public function render_end( $metas, $settings, $products ) {
		ob_start();

		/**
		 * After render row hook.
		 */
		do_action( 'rtsb/elementor/render/after_row', $metas, $settings, $products );

		return ob_get_clean();
	}

	/**
	 * Add render attribute.
	 *
	 * @param array|string $element   The HTML element.
	 * @param array|string $key       Optional. Attribute key. Default is null.
	 * @param array|string $value     Optional. Attribute value. Default is null.
	 * @param bool         $overwrite Optional. Whether to overwrite existing.
	 *
	 * @return self
	 */
	public function add_attribute( $element, $key = null, $value = null, $overwrite = false ) {
		if ( is_array( $element ) ) {
			foreach ( $element as $element_key => $attributes ) {
				$this->add_attribute( $element_key, $attributes, null, $overwrite );
			}

			return $this;
		}

		if ( is_array( $key ) ) {
			foreach ( $key as $attribute_key => $attributes ) {
				$this->add_attribute( $element, $attribute_key, $attributes, $overwrite );
			}

			return $this;
		}

		if ( empty( $this->attributes[ $element ][ $key ] ) ) {
			$this->attributes[ $element ][ $key ] = [];
		}

		settype( $value, 'array' );

		if ( $overwrite ) {
			$this->attributes[ $element ][ $key ] = $value;
		} else {
			$this->attributes[ $element ][ $key ] = array_merge( $this->attributes[ $element ][ $key ], $value );
		}

		return $this;
	}

	/**
	 * Get render attribute string.
	 *
	 * @param string $element The element.
	 *
	 * @return string
	 */
	public function get_attribute_string( $element ) {
		if ( empty( $this->attributes[ $element ] ) ) {
			return '';
		}

		return Utils::render_html_attributes( $this->attributes[ $element ] );
	}
}
