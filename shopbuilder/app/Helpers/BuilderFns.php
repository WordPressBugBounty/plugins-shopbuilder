<?php
/**
 * BuilderFns class
 *
 * The  builder.
 *
 * @package  RadiusTheme\SB
 * @since    1.0.0
 */

namespace RadiusTheme\SB\Helpers;

// Do not allow directly accessing this file.
use RadiusTheme\SB\Models\TemplateSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * BuilderFns class
 */
class BuilderFns {
	/**
	 * Local Cache.
	 *
	 * @var array
	 */
	private static $cache = [];

	/**
	 * Template builder post type
	 *
	 * @var string
	 */
	public static $post_type_tb = 'rtsb_builder';
	/**
	 * Template builder
	 *
	 * @var string
	 */
	public static $template_meta = 'rtsb_tb_template';
	/**
	 * Template builder
	 *
	 * @var string
	 */
	public static $product_template_meta = 'rtsb_selected_product_template';
	/**
	 * Template builder
	 *
	 * @var string
	 */
	public static $view_template_for_products_meta = 'rtsb_template_for_selected_products';
	/**
	 * Template builder
	 *
	 * @var string
	 */
	public static $view_template_for_archive_meta = 'rtsb_template_for_selected_category';

	/**
	 * Template builder
	 *
	 * @var string
	 */
	public static $categories_product_page_template = 'rtsb_categories_product_page_template';
	/**
	 * Template builder
	 *
	 * @var string
	 */
	public static $tags_product_page_template = 'rtsb_tags_product_page_template';
	/**
	 * Page builder id.
	 *
	 * @return int
	 */
	public static function builder_page_id_by_type( $type ) {
		global $post;

		if ( ! array_key_exists( $type, self::builder_page_types() ) ) {
			return 0;
		}
		// Wpml Supported.
		$options = self::option_name( $type );
		$post_id = TemplateSettings::instance()->get_option( $options );
		if ( empty( $post ) ) {
			return 0;
		}
		if ( empty( $post_id ) ) {
			$options = self::option_name( $type, true );
			$post_id = TemplateSettings::instance()->get_option( $options );
		}

		$cache_key = 'rtsb_builder_page_id_by_type_' . $type;
		if ( isset( self::$cache[ $cache_key ] ) ) {
			return self::$cache[ $cache_key ];
		}

		switch ( $type ) {
			case 'product':
				if ( 'product' !== $post->post_type ) {
					break;
				}
				$cache_key_product = 'template_for_product_page_' . get_the_ID();
				$template          = get_transient( $cache_key_product );
				if ( $template && 'publish' === get_post_status( $template ) ) {
					$post_id = $template;
					break;
				}
				Cache::set_transient_cache_key( $cache_key_product );
				$builder_id = get_post_meta( get_the_ID(), self::$view_template_for_products_meta, true );

				if ( self::get_specific_product_as_default( $builder_id ) && 'publish' === get_post_status( $builder_id ) ) {
					$post_id = $builder_id;
					set_transient( $cache_key_product, $post_id, 12 * HOUR_IN_SECONDS ); // Cache duration: 12 hours.
					break;
				}

				$terms = get_the_terms( get_the_ID(), 'product_cat' );
				if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
					foreach ( $terms as $term ) {
						$template_id = get_term_meta( $term->term_id, self::$categories_product_page_template, true );
						if ( empty( $template_id ) || 'publish' !== get_post_status( $template_id ) ) {
							continue;
						}
						$set_default_option_name = self::option_name_product_page_specific_cat_set_default( $template_id );
						if ( TemplateSettings::instance()->get_option( $set_default_option_name ) ) {
							$post_id = $template_id;
							set_transient( $cache_key_product, $post_id, 12 * HOUR_IN_SECONDS ); // Cache duration: 12 hours.
							break 2;
						}
					}
				}
				$tags = get_the_terms( get_the_ID(), 'product_tag' );
				if ( ! is_wp_error( $tags ) && ! empty( $tags ) ) {
					foreach ( $tags as $term ) {
						$template_id = get_term_meta( $term->term_id, self::$tags_product_page_template, true );
						if ( empty( $template_id ) || 'publish' !== get_post_status( $template_id ) ) {
							continue;
						}
						$set_default_option_name = self::option_name_product_page_specific_tag_set_default( $template_id );
						if ( TemplateSettings::instance()->get_option( $set_default_option_name ) ) {
							$post_id = $template_id;
							set_transient( $cache_key_product, $post_id, 12 * HOUR_IN_SECONDS ); // Cache duration: 12 hours.
							break 2;
						}
					}
				}
				set_transient( $cache_key_product, $post_id, 12 * HOUR_IN_SECONDS );
				break;
			case 'archive':
				$queried_object = get_queried_object();
				if ( ! empty( $queried_object->taxonomy ) && 'product_cat' === $queried_object->taxonomy ) {
					$cache_key_archive = 'template_for_archive_page_' . $queried_object->term_id;
					$template          = get_transient( $cache_key_archive );
					if ( $template && 'publish' === get_post_status( $template ) ) {
						$post_id = $template;
						break;
					}
					Cache::set_transient_cache_key( $cache_key_archive );
					$builder_id = get_term_meta( $queried_object->term_id, self::$view_template_for_archive_meta, true );
					if ( self::get_specific_category_as_default( $builder_id ) && 'publish' === get_post_status( $builder_id ) ) {
						$post_id = $builder_id;
						set_transient( $cache_key_archive, $post_id, 12 * HOUR_IN_SECONDS ); // Cache duration: 12 hours.
					}
				}
				break;

			default:
		}
		if ( 'publish' === get_post_status( $post_id ) && self::builder_type( $post_id ) === $type ) {
			self::$cache[ $cache_key ] = $post_id;
			return $post_id;
		}

		return 0;
	}

	/**
	 * Page builder id.
	 *
	 * @return init
	 */
	public static function builder_page_id_by_page() {
		$type = apply_filters( 'rtsb/builder/set/current/page/type', '' );
		if ( ! $type ) {
			return 0;
		}

		return self::builder_page_id_by_type( $type );
	}

	/**
	 * Page builder Page for.
	 *
	 * @return array
	 */
	public static function builder_page_types() {
		$page_types = [
			'shop'     => esc_html__( 'Shop', 'shopbuilder' ),
			'archive'  => esc_html__( 'Category Archive', 'shopbuilder' ),
			'product'  => esc_html__( 'Product Page', 'shopbuilder' ),
			'cart'     => esc_html__( 'Cart', 'shopbuilder' ),
			'checkout' => esc_html__( 'Checkout', 'shopbuilder' ),
		];

		return apply_filters( 'rtsb/builder/register/page/types', $page_types );
	}

	/**
	 * Option name.
	 *
	 * @param string $type Builder type.
	 *
	 * @return string
	 */
	public static function option_name( $type, $default_lang = false ) {

		$type   = str_replace( [ '-', ' ' ], '_', $type );
		$suffix = $type;

		if ( $default_lang ) {
			return self::$template_meta . '_default_' . $suffix;
		}
		$current_lang = Fns::wpml_current_language();

		if ( ! empty( $current_lang ) ) {
			$suffix = $type . '_' . $current_lang;
		}

		return self::$template_meta . '_default_' . $suffix;
	}

	/**
	 * @param $post_id
	 *
	 * @return string|void
	 */
	public static function option_name_for_specific_product_set_default( $post_id ) {
		if ( ! $post_id ) {
			return;
		}

		return 'rtsb_template_specific_product_set_default_' . $post_id;
	}

	/**
	 * @param $post_id
	 *
	 * @return false|mixed|void|null
	 */
	public static function get_specific_product_as_default( $post_id ) {
		if ( ! $post_id ) {
			return;
		}
		$options = self::option_name_for_specific_product_set_default( $post_id );

		return TemplateSettings::instance()->get_option( $options );
	}

	/**
	 * @param $post_id
	 *
	 * @return string|void
	 */
	public static function option_name_for_specific_category_set_default( $post_id ) {
		if ( ! $post_id ) {
			return;
		}

		return 'rtsb_template_specific_category_set_default_' . $post_id;
	}

	/**
	 * @param $post_id
	 *
	 * @return false|mixed|void|null
	 */
	public static function get_specific_category_as_default( $post_id ) {
		if ( ! $post_id ) {
			return;
		}
		$options = self::option_name_for_specific_category_set_default( $post_id );

		return TemplateSettings::instance()->get_option( $options ); // get_option( $options );
	}

	/**
	 * @param $template_id
	 *
	 * @return string
	 */
	public static function option_name_by_template_id( $template_id ) {
		$_suff = null;
		if ( $template_id ) {
			$_suff = '_' . $template_id;
		}

		return self::$view_template_for_products_meta . $_suff;
	}

	/**
	 * @param $template_id
	 *
	 * @return string
	 */
	public static function option_name_product_page_selected_cat( $template_id ) {
		$_suff = null;
		if ( $template_id ) {
			$_suff = '_' . $template_id;
		}

		return self::$categories_product_page_template . $_suff;
	}

	/**
	 * @param $post_id
	 *
	 * @return string|void
	 */
	public static function option_name_product_page_specific_cat_set_default( $post_id ) {
		if ( ! $post_id ) {
			return;
		}

		return 'rtsb_template_product_page_specific_category_set_default_' . $post_id;
	}

	/**
	 * @param $template_id
	 *
	 * @return string
	 */
	public static function option_name_product_page_selected_tag( $template_id ) {
		$_suff = null;
		if ( $template_id ) {
			$_suff = '_' . $template_id;
		}

		return self::$tags_product_page_template . $_suff;
	}
	/**
	 * @param $post_id
	 *
	 * @return string|void
	 */
	public static function option_name_product_page_specific_tag_set_default( $post_id ) {
		if ( ! $post_id ) {
			return;
		}

		return 'rtsb_template_product_page_specific_tag_set_default_' . $post_id;
	}
	/**
	 * @param $template_id
	 *
	 * @return string
	 */
	public static function archive_option_name_by_template_id( $template_id ) {
		$_suff = null;
		if ( $template_id ) {
			$_suff = '_' . $template_id;
		}

		return self::$view_template_for_archive_meta . $_suff;
	}

	/**
	 * Template builder
	 *
	 * @var string
	 */
	public static function template_type_meta_key() {
		return self::$template_meta . '_type';
	}

	/**
	 * Get builder type.
	 *
	 * @param [type] $post_id Post id.
	 *
	 * @return string
	 */
	public static function builder_type( $post_id ) {
		$post_id = Fns::wpml_object_id( $post_id, self::$post_type_tb, 'default' );

		if ( ! $post_id ) {
			return;
		}

		$cache_key = 'rtsb_template_type_' . $post_id;
		if ( ! empty( self::$cache[ $cache_key ] ) ) {
			return self::$cache[ $cache_key ];
		}

		$type = get_post_meta( $post_id, self::template_type_meta_key(), true );

		// Cache the results in a static variable for the next call.
		self::$cache[ $cache_key ] = $type;

		return $type;
	}

	/**
	 * Template builder
	 *
	 * @var boolean
	 */
	public static function is_builder_preview() {
		return is_singular( self::$post_type_tb );
	}

	/**
	 * Template builder
	 *
	 * @var boolean
	 */
	public static function is_archive() {
		return self::is_page_builder( 'archive', is_product_taxonomy() );
	}

	/**
	 * Template builder
	 *
	 * @var boolean
	 */
	public static function is_shop() {
		return self::is_page_builder( 'shop', is_shop() );
	}

	/**
	 * Template builder
	 *
	 * @var boolean
	 */
	public static function is_cart() {
		return self::is_page_builder( 'cart', is_cart() );
	}

	/**
	 * Template builder
	 *
	 * @var boolean
	 */
	public static function is_checkout() {
		return self::is_page_builder( 'checkout', is_checkout() && ! is_wc_endpoint_url() && ! is_order_received_page() );
	}

	/**
	 * Single Page builder Detection
	 *
	 * @return boolean
	 */
	public static function is_product() {
		return self::is_page_builder( 'product', is_product() );
	}

	/**
	 * @param $is_quick_view
	 *
	 * @return bool
	 */
	public static function is_quick_views_page( $is_quick_view = false ) {
		return rtsb()->has_pro() && self::is_page_builder( 'quick-view', $is_quick_view );
	}

	/**
	 * Builder page detector.
	 *
	 * @param string  $type builder Page type.
	 * @param boolean $is_page page status.
	 *
	 * @return boolean
	 */
	public static function is_page_builder( $type, $is_page, $is_require_auth = false ) {
		if ( self::builder_type( get_the_ID() ) === $type ) {
			return true;
		}

		if ( $is_require_auth && ! is_user_logged_in() ) {
			$type = 'myaccount-auth';
		}

		$builder_id = self::builder_page_id_by_type( $type );
		if ( ! $builder_id ) {
			return false;
		}

		$builder_type = self::builder_type( $builder_id );
		if ( $type === $builder_type && $is_page ) {
			return true;
		}

		return false;
	}

	/**
	 * Get builder content function
	 *
	 * @param [type]  $template_id builder Template id.
	 * @param boolean $with_css with css.
	 *
	 * @return mixed
	 */
	public static function get_builder_content( $template_id, $with_css = false ) {
		// Don't Use cache: tickets ID -> 75376.
		return \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $template_id, $with_css );
	}
}
