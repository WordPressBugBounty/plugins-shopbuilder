<?php
/**
 * Fns Helpers class
 *
 * @package  RadiusTheme\SB
 */

namespace RadiusTheme\SB\Helpers;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

use WC_Product;
use WC_Product_Query;
use Elementor\Icons_Manager;
use RadiusTheme\SB\Models\ReSizer;
use RadiusTheme\SB\Models\DataModel;
use RadiusTheme\SB\Models\ModuleList;
use RadiusTheme\SB\Models\Settings;

/**
 * Fns class
 */
class Fns {

	/**
	 * @var array
	 */
	private static $cache = [];

	/**
	 *  Verify nonce.
	 *
	 * @return bool
	 */
	public static function verify_nonce() {
		$nonce     = isset( $_REQUEST[ rtsb()->nonceId ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ rtsb()->nonceId ] ) ) : null;
		$nonceText = rtsb()->nonceText;
		if ( wp_verify_nonce( $nonce, $nonceText ) ) {
			return true;
		}

		return false;
	}

	public static function get_nonce() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		return isset( $_REQUEST[ rtsb()->nonceId ] ) ? sanitize_text_field( $_REQUEST[ rtsb()->nonceId ] ) : null;
	}


	/**
	 * Set Cookie or Session
	 *
	 * @param $name
	 * @param $value
	 */
	public static function setSession( $name, $value ) {
		if ( ! headers_sent() && session_status() == PHP_SESSION_NONE ) {
			session_start();
			$_SESSION[ $name ] = $value;
		} else {
			$_SESSION[ $name ] = $value;
		}
	}
	/**
	 * Get Cookie or Session
	 *
	 * @param $name
	 *
	 * @return bool
	 */
	public static function getSession( $name ) {
		if ( ! headers_sent() && session_status() == PHP_SESSION_NONE ) {
			session_start();
		}
		return $_SESSION[ $name ] ?? null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	}
	/**
	 * Remove Session Variable
	 *
	 * @param string $name The name of the session variable to remove.
	 */
	public static function removeSession( $name ) {
		if ( ! headers_sent() && session_status() == PHP_SESSION_NONE ) {
			session_start();
			unset( $_SESSION[ $name ] );
		} else {
			unset( $_SESSION[ $name ] );
		}
	}

	/**
	 * @param $plugin_slug
	 *
	 * @return bool
	 */
	public static function check_plugin_installed( $plugin_slug ): bool {
		$installed_plugins = get_plugins();

		return array_key_exists( $plugin_slug, $installed_plugins ) || in_array( $plugin_slug, $installed_plugins, true );
	}

	/**
	 * @param $plugin_slug
	 *
	 * @return bool
	 */
	public static function check_plugin_active( $plugin_slug ): bool {
		if ( is_plugin_active( $plugin_slug ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get all user roles.
	 *
	 * @return array|false|mixed
	 */
	public static function get_all_user_roles() {
		$cache_key = 'rtsb_get_user_roles';
		$roles     = wp_cache_get( $cache_key, 'shopbuilder' );

		if ( empty( $roles ) ) {
			if ( ! function_exists( 'get_editable_roles' ) ) {
				require_once ABSPATH . 'wp-admin/includes/user.php';
			}

			$user_roles = \get_editable_roles();
			$roles      = [];

			foreach ( $user_roles as $key => $role ) {
				if ( empty( $role['capabilities'] ) ) {
					continue;
				}
				$roles[ $key ] = $role['name'];
			}
		}
		wp_cache_set( $cache_key, $roles, 'shopbuilder' );
		Cache::set_data_cache_key( $cache_key );
		return $roles;
	}

	/**
	 * @param $data
	 *
	 * @return mixed|string
	 */
	public static function stripslashes_value( $data ) {
		if ( is_string( $data ) && strpos( $data, '\\' ) !== false ) {
			return stripslashes( $data );
		} elseif ( is_array( $data ) ) {
			foreach ( $data as $key => $value ) {
				$data[ $key ] = self::stripslashes_value( $value );
			}
		}

		return $data;
	}

	/**
	 * @param $string
	 *
	 * @return array
	 */
	public static function multiselect_settings_field_value( $string ) {

		$values = [];
		if ( empty( $string ) ) {
			return $values;
		}

		$stripslashes_data = self::stripslashes_value( $string );
		if ( is_array( $stripslashes_data ) && count( $stripslashes_data ) ) {
			foreach ( $stripslashes_data as $item ) {
				$item     = is_array( $item ) ? $item : json_decode( $item, true );
				$values[] = $item['value'];
			}
		}

		return $values;
	}

	/**
	 * @return bool
	 */
	public static function check_is_block_theme(): bool {
		global $wp_version;

		return version_compare( $wp_version, '5.9', '>=' ) && function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();
	}

	/**
	 * @param string $template_name Template name.
	 * @param string $template_path Template path. (default: '').
	 * @param string $plugin_path Plugin path. (default: ''). fallback file from plugin.
	 *
	 * @return mixed|void
	 */
	public static function locate_template( $template_name, $template_path = '', $plugin_path = '' ) {
		$template_name = self::sanitize_file_name( $template_name ) . '.php';

		if ( ! $template_path ) {
			$template_path = rtsb()->get_template_path();
		}
		if ( ! $plugin_path ) {
			$plugin_path = RTSB_ABSPATH . 'templates/';
		}

		$template_rtsb_path = trailingslashit( $template_path ) . $template_name;
		$template_path      = '/' . $template_name;
		$plugin_path        = $plugin_path . $template_name;
		$pro_plugin_path    = rtsb()->has_pro() ? RTSBPRO_PATH . 'templates/' . $template_name : '';

		$located = locate_template(
			apply_filters(
				'rtsb/core/locate_template_files',
				[
					$template_rtsb_path, // Search in <theme>/shopbuilder/.
					$template_path,             // Search in <theme>/.
				]
			)
		);

		if ( ! $located ) {
			if ( file_exists( $plugin_path ) ) {
				return apply_filters( 'rtsb/core/locate_template', $plugin_path, $template_name );
			} elseif ( $pro_plugin_path && rtsb()->has_pro() && file_exists( $pro_plugin_path ) ) {
				return apply_filters( 'rtsb/core/locate_template', $pro_plugin_path, $template_name );
			}
		}

		/**
		 * APPLY_FILTERS: rtsb/core/locate_template
		 *
		 * Filter the location of the templates.
		 *
		 * @param string $located Template found
		 * @param string $path Template path
		 *
		 * @return string
		 */
		return apply_filters( 'rtsb/core/locate_template', $located, $template_name );
	}

	/**
	 * Remove any character that is not alphanumeric, /, _, or -.
	 *
	 * @param string $name Name to sanitize.
	 *
	 * @return array|string|string[]|null
	 */
	private static function sanitize_file_name( $name ) {
		return preg_replace( '/[^a-zA-Z0-9\/_\-]/', '', $name );
	}

	/**
	 * Template Content
	 *
	 * @param string $template_name Template name.
	 * @param array  $args Arguments. (default: array).
	 * @param bool   $return Whether to return or print the template.
	 * @param string $template_path Template path. (default: '').
	 * @param string $plugin_path Fallback path from where file will load if fail to load from template. (default: '').
	 *
	 * @return false|string|void
	 */
	public static function load_template( $template_name, array $args = null, $return = false, $template_path = '', $plugin_path = '' ) {
		$cache_key = sanitize_key( implode( '-', [ 'template', $template_name, $template_path ] ) );
		$located   = (string) wp_cache_get( $cache_key, 'shopbuilder' );
		if ( ! $located ) {
			$located = self::locate_template( $template_name, $template_path, $plugin_path );
			// Don't cache the absolute path so that it can be shared between web servers with different paths.
			$cache_path = wc_tokenize_path( $located, wc_get_path_define_tokens() );
			Cache::set_template_cache( $cache_key, $cache_path );
		} else {
			// Make sure that the absolute path to the template is resolved.
			$located = wc_untokenize_path( $located, wc_get_path_define_tokens() );
		}
		if ( ! file_exists( $located ) ) {
			// translators: %s template.
			self::doing_it_wrong( __FUNCTION__, sprintf( __( '%s does not exist.', 'shopbuilder' ), '<code>' . $located . '</code>' ), '1.0' );

			return;
		}

		if ( ! empty( $args ) && is_array( $args ) ) {
			$atts = $args;
			extract( $args ); // @codingStandardsIgnoreLine
		}

		// Allow 3rd party plugin filter template file from their plugin.
		$located = apply_filters( 'rtsb/core/get_template', $located, $template_name, $args );

		if ( $return ) {
			ob_start();
		}

		do_action( 'rtsb/core/before_template_part', $template_name, $located, $args );
		include $located;

		do_action( 'rtsb/core/after_template_part', $template_name, $located, $args );

		if ( $return ) {
			return ob_get_clean();
		}
	}

	/**
	 *  Verify nonce.
	 *
	 * @return string
	 */
	public static function is_woocommerce() {
		return is_woocommerce() || is_cart() || is_checkout() || is_account_page();
	}

	/**
	 * Page builder
	 *
	 * @param int $post_id post id.
	 *
	 * @return string
	 */
	public static function page_edit_with( $post_id ) {
		if ( ! $post_id ) {
			return '';
		}

		$edit_with = get_post_meta( $post_id, '_elementor_edit_mode', true );

		if ( 'builder' === $edit_with ) {
			$edit_by = 'elementor';
		} else {
			$edit_by = 'gutenberg';
		}

		return $edit_by;
	}

	/**
	 * Returns default expiration for wishlist cookie
	 *
	 * @return int Number of seconds the cookie should last.
	 */
	public static function get_cookie_expiration() {
		return intval( apply_filters( 'rtsb/cookie_expiration', 60 * 60 * 24 * 30 ) );
	}

	/**
	 * Create a cookie.
	 *
	 * @param string $name Cookie name.
	 * @param mixed  $value Cookie value.
	 * @param int    $time Cookie expiration time.
	 * @param bool   $secure Whether cookie should be available to secured connection only.
	 * @param bool   $httponly Whether cookie should be available to HTTP request only (no js handling).
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public static function setcookie( $name, $value = [], $time = null, $secure = false, $httponly = false ) {

		if ( ! apply_filters( 'rtsb/set_cookie', true ) || empty( $name ) ) {
			return false;
		}

		$time = ! empty( $time ) ? $time : time() + self::get_cookie_expiration();

		$value      = wp_json_encode( stripslashes_deep( $value ) );
		$expiration = apply_filters( 'rtsb/cookie_expiration_time', $time ); // Default 30 days.

		$_COOKIE[ $name ] = $value;
		wc_setcookie( $name, $value, $expiration, $secure, $httponly );

		return true;
	}

	/**
	 * Retrieve the value of a cookie.
	 *
	 * @param string $name Cookie name.
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public static function getcookie( $name ) {
		if ( isset( $_COOKIE[ $name ] ) ) {
			return json_decode( sanitize_text_field( wp_unslash( $_COOKIE[ $name ] ) ), true );
		}

		return [];
	}

	/**
	 * Woocommerce Last product id return
	 */
	public static function get_prepared_product_id() {
		if ( is_singular( 'product' ) ) {
			return get_the_ID();
		}
		// Return the Builder Template id.

		if ( get_post_type( get_the_ID() ) == BuilderFns::$post_type_tb ) {
			$product_id = get_post_meta( get_the_ID(), BuilderFns::$product_template_meta, true );
			if ( $product_id && get_post_status( $product_id ) ) {
				return $product_id;
			}
		}

		global $wpdb;
		$cache_key = 'rtsb_prepared_product_id';
		$_post_id  = wp_cache_get( $cache_key, 'shopbuilder' );
		if ( false === $_post_id || 'publish' !== get_post_status( $_post_id ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$_post_id = $wpdb->get_var(
				$wpdb->prepare( "SELECT MAX(ID) FROM {$wpdb->prefix}posts WHERE post_type =  %s AND post_status IN('publish', 'draft')", 'product' )
			);
			wp_cache_set( $cache_key, $_post_id, 'shopbuilder', 12 * HOUR_IN_SECONDS );
			Cache::set_data_cache_key( $cache_key );
		}

		return $_post_id;
	}

	/**
	 * Get the product function. Only used in single page widgets.
	 *
	 * @return object
	 */
	public static function get_product() {
		global $product;

		if ( is_singular( 'product' ) && $product instanceof WC_Product ) {
			// do_action( 'rtsb_before_product_template_render' );.
			return $product;
		}
		$cache_key = 'prepared_product_for_preview';
		if ( isset( self::$cache[ $cache_key ] ) ) {
			return self::$cache[ $cache_key ];
		}
		$product                   = wc_get_product( self::get_prepared_product_id() );
		self::$cache[ $cache_key ] = $product;
		do_action( 'rtsb_before_product_template_render' );
		return $product;
	}

	/**
	 * Error function
	 *
	 * @param [type] $function function name.
	 * @param [type] $message message.
	 * @param [type] $version version.
	 *
	 * @return void
	 */
	public static function doing_it_wrong( $function, $message, $version ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_wp_debug_backtrace_summary
		$message .= ' Backtrace: ' . wp_debug_backtrace_summary();
		_doing_it_wrong( esc_html( $function ), wp_kses_post( $message ), esc_html( $version ) );
	}

	/**
	 * @return array
	 */
	public static function get_pages() {
		$pages    = [];
		$rawPages = get_pages();
		if ( ! empty( $rawPages ) ) {
			foreach ( $rawPages as $page ) {
				$pages[ $page->ID ] = $page->post_title;
			}
		}

		return $pages;
	}

	public static function get_section_items( $section_id ) {
		if ( ! $section_id ) {
			return [];
		}

		return DataModel::source()->get_option( $section_id, [] );
	}

	public static function get_options( $section_id, $item_id ) {
		if ( ! $section_id || ! $item_id ) {
			return [];
		}
		$sections = self::get_section_items( $section_id );

		return isset( $sections[ $item_id ] ) ? $sections[ $item_id ] : [];
	}

	/**
	 * Get options value with default value if options doesn't exist.
	 *
	 * @param $group
	 * @param $option_key
	 * @param $default_value
	 *
	 * @return mixed|string
	 */
	public static function get_options_by_default_val( $group, $option_key, $default_value = '' ) {

		if ( ! $option_key || ! isset( $group[ $option_key ] ) ) {
			return $default_value;
		}

		return $group[ $option_key ];
	}

	/**
	 * @param string $section_id
	 * @param string $item_id
	 * @param string $option_id
	 * @param null   $default EXCEPT multi_checkbox you can provide default value if given option does not set any value
	 * @param null   $type checkbox, multi_checkbox, number
	 *
	 * @return bool|int|mixed|null
	 */
	public static function get_option( $section_id, $item_id, $option_id, $default = null, $type = null ) {
		$options = self::get_options( $section_id, $item_id );

		if ( $type === 'checkbox' ) {
			if ( isset( $options[ $option_id ] ) ) {
				return $options[ $option_id ] === 'on';
			}

			return $default;
		} elseif ( $type === 'multi_checkbox' ) {
			return isset( $options[ $option_id ] ) && is_array( $options[ $option_id ] ) && in_array( $default, $options[ $option_id ] );
		} elseif ( $type === 'number' ) {
			return isset( $options[ $option_id ] ) ? absint( $options[ $option_id ] ) : absint( $default );
		}

		return isset( $options[ $option_id ] ) && ! empty( $options[ $option_id ] ) ? $options[ $option_id ] : $default;
	}


	/**
	 * Create a page and store the ID in an option.
	 *
	 * @param mixed  $slug Slug for the new page.
	 * @param array  $options ['section_id', 'item_id', 'option_id']Option name to store the page's ID.
	 * @param string $page_title (default: '') Title for the new page.
	 * @param string $page_content (default: '') Content for the new page.
	 * @param int    $post_parent (default: 0) Parent for the new page.
	 * @param string $post_status (default: publish) The post status of the new page.
	 *
	 * @return int page ID.
	 */
	public static function create_page( $slug, $options = '', $page_title = '', $page_content = '', $post_parent = 0, $post_status = 'publish' ) {
		global $wpdb;

		$option_value = 0;
		if ( ! empty( $options ) ) {
			if ( is_array( $options ) ) {
				$options      = wp_parse_args(
					$options,
					[
						'section_id' => '',
						'item_id'    => '',
						'option_id'  => '',
					]
				);
				$option_value = self::get_option( $options['section_id'], $options['item_id'], $options['option_id'], 0, 'number' );
			} else {
				$option_value = absint( get_option( $options ) );
			}
		}

		if ( $option_value > 0 ) {
			$page_object = get_post( $option_value );

			if ( $page_object && 'page' === $page_object->post_type && ! in_array(
				$page_object->post_status,
				[
					'pending',
					'trash',
					'future',
					'auto-draft',
				],
				true
			) ) {
				// Valid page is already in place.
				return $page_object->ID;
			}
		}

		if ( strlen( $page_content ) > 0 ) {
			// Search for an existing page with the specified page content (typically a shortcode).
			$shortcode = str_replace( [ '<!-- wp:shortcode -->', '<!-- /wp:shortcode -->' ], '', $page_content );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$valid_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' ) AND post_content LIKE %s LIMIT 1;", "%{$shortcode}%" ) );
		} else {
			// Search for an existing page with the specified page slug.
			$valid_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' )  AND post_name = %s LIMIT 1;", $slug ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		}

		$valid_page_found = apply_filters( 'rtsb/core/create_page_id', $valid_page_found, $slug, $page_content, $options );

		if ( $valid_page_found ) {
			if ( is_array( $options ) ) {
				if ( ! empty( $options['section_id'] ) && $options['item_id'] && $options['option_id'] ) {
					$section_items = DataModel::source()->get_option( $options['section_id'], [] );
					$section_items[ $options['item_id'] ][ $options['option_id'] ] = $valid_page_found;
					DataModel::source()->set_option( $options['section_id'], $section_items );
				}
			} else {
				if ( $options ) {
					update_option( $options, $valid_page_found );
				}
			}

			return $valid_page_found;
		}

		// Search for a matching valid trashed page.
		if ( strlen( $page_content ) > 0 ) {
			// Search for an existing page with the specified page content (typically a shortcode).
			$trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_content LIKE %s LIMIT 1;", "%{$page_content}%" ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		} else {
			// Search for an existing page with the specified page slug.
			$trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_name = %s LIMIT 1;", $slug ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		}

		if ( $trashed_page_found ) {
			$page_id   = $trashed_page_found;
			$page_data = [
				'ID'          => $page_id,
				'post_status' => $post_status,
			];
			wp_update_post( $page_data );
		} else {
			$page_data = [
				'post_status'    => $post_status,
				'post_type'      => 'page',
				'post_author'    => 1,
				'post_name'      => $slug,
				'post_title'     => $page_title,
				'post_content'   => $page_content,
				'post_parent'    => $post_parent,
				'comment_status' => 'closed',
			];
			$page_id   = wp_insert_post( $page_data );

			do_action( 'rtsb/core/page_created', $page_id, $page_data );
		}

		if ( is_array( $options ) ) {
			if ( ! empty( $options['section_id'] ) && $options['item_id'] && $options['option_id'] ) {
				$section_items = DataModel::source()->get_option( $options['section_id'], [] );
				$section_items[ $options['item_id'] ][ $options['option_id'] ] = $page_id;
				DataModel::source()->set_option( $options['section_id'], $section_items );
			}
		} else {
			if ( $options ) {
				update_option( $options, $page_id );
			}
		}

		return $page_id;
	}

	public static function get_kses_array() {
		return [
			'a'                             => [
				'class' => [],
				'href'  => [],
				'rel'   => [],
				'title' => [],
			],
			'abbr'                          => [
				'title' => [],
			],
			'b'                             => [],
			'blockquote'                    => [
				'cite' => [],
			],
			'cite'                          => [
				'title' => [],
			],
			'code'                          => [],
			'del'                           => [
				'datetime' => [],
				'title'    => [],
			],
			'dd'                            => [],
			'div'                           => [
				'class' => [],
				'title' => [],
				'style' => [],
			],
			'dl'                            => [],
			'dt'                            => [],
			'em'                            => [],
			'h1'                            => [
				'class' => [],
			],
			'h2'                            => [
				'class' => [],
			],
			'h3'                            => [
				'class' => [],
			],
			'h4'                            => [
				'class' => [],
			],
			'h5'                            => [
				'class' => [],
			],
			'h6'                            => [
				'class' => [],
			],
			'i'                             => [
				'class' => [],
			],
			'img'                           => [
				'alt'    => [],
				'class'  => [],
				'height' => [],
				'src'    => [],
				'width'  => [],
			],
			'li'                            => [
				'class' => [],
			],
			'ol'                            => [
				'class' => [],
			],
			'p'                             => [
				'class' => [],
			],
			'q'                             => [
				'cite'  => [],
				'title' => [],
			],
			'span'                          => [
				'class' => [],
				'title' => [],
				'style' => [],
			],
			'iframe'                        => [
				'width'       => [],
				'height'      => [],
				'scrolling'   => [],
				'frameborder' => [],
				'allow'       => [],
				'src'         => [],
			],
			'strike'                        => [],
			'br'                            => [],
			'strong'                        => [],
			'data-wow-duration'             => [],
			'data-wow-delay'                => [],
			'data-wallpaper-options'        => [],
			'data-stellar-background-ratio' => [],
			'ul'                            => [
				'class' => [],
			],
		];
	}


	/*
	 * Escape output of wishlist icon
	 *
	 * @param string $data Data to escape.
	 * @return string Escaped data
	 */
	public static function print_icon( $data ) {
		/**
		 * APPLY_FILTERS: rtsb/core/allowed_icon_html
		 *
		 * Filter the allowed HTML for the icons.
		 *
		 * @param array $allowed_icon_html Allowed HTML
		 *
		 * @return array
		 */
		$allowed_icon_html = apply_filters(
			'rtsb/core/allowed_icon_html',
			[
				'i'     => [
					'class' => true,
				],
				'img'   => [
					'src'    => true,
					'alt'    => true,
					'width'  => true,
					'height' => true,
				],
				'svg'   => [
					'class'           => true,
					'aria-hidden'     => true,
					'aria-labelledby' => true,
					'role'            => true,
					'xmlns'           => true,
					'width'           => true,
					'height'          => true,
					'viewbox'         => true,
					'stroke'          => true,
					'fill'            => true,
				],
				'g'     => [
					'fill' => true,
				],
				'title' => [
					'title' => true,
				],
				'path'  => [
					'd'               => true,
					'fill'            => true,
					'stroke'          => true,
					'stroke-width'    => true,
					'stroke-linecap'  => true,
					'stroke-linejoin' => true,
					'fill-rule'       => true,
					'clip-rule'       => true,
				],
			]
		);

		echo wp_kses( $data, $allowed_icon_html );
	}

	/**
	 * Prints HTMl.
	 *
	 * @param string $html HTML.
	 * @param bool   $allHtml All HTML.
	 *
	 * @return void
	 */
	public static function print_html( $html, $allHtml = false ) {
		if ( ! $html ) {
			return;
		}
		if ( $allHtml ) {
			echo stripslashes_deep( $html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo wp_kses_post( stripslashes_deep( $html ) );
		}
	}

	/**
	 * Allowed HTML for wp_kses.
	 *
	 * @param string $level Tag level.
	 *
	 * @return mixed
	 */
	public static function allowedHtml( $level = 'basic' ) {
		$allowed_html = [];
		// TODO:: Need Optimize.
		switch ( $level ) {
			case 'basic':
				$allowed_html = [
					'b'      => [
						'class' => [],
						'id'    => [],
					],
					'i'      => [
						'class' => [],
						'id'    => [],
					],
					'u'      => [
						'class' => [],
						'id'    => [],
					],
					'br'     => [
						'class' => [],
						'id'    => [],
					],
					'em'     => [
						'class' => [],
						'id'    => [],
					],
					'span'   => [
						'class' => [],
						'id'    => [],
					],
					'strong' => [
						'class' => [],
						'id'    => [],
					],
					'hr'     => [
						'class' => [],
						'id'    => [],
					],
					'div'    => [
						'class' => [],
						'id'    => [],
					],
					'a'      => [
						'href'   => [],
						'title'  => [],
						'class'  => [],
						'id'     => [],
						'target' => [],
					],
				];
				break;

			case 'advanced':
				$allowed_html = [
					'b'      => [
						'class' => [],
						'id'    => [],
					],
					'i'      => [
						'class' => [],
						'id'    => [],
					],
					'u'      => [
						'class' => [],
						'id'    => [],
					],
					'br'     => [
						'class' => [],
						'id'    => [],
					],
					'em'     => [
						'class' => [],
						'id'    => [],
					],
					'span'   => [
						'class' => [],
						'id'    => [],
					],
					'strong' => [
						'class' => [],
						'id'    => [],
					],
					'hr'     => [
						'class' => [],
						'id'    => [],
					],
					'a'      => [
						'href'   => [],
						'title'  => [],
						'class'  => [],
						'id'     => [],
						'target' => [],
					],
					'input'  => [
						'type'  => [],
						'name'  => [],
						'class' => [],
						'value' => [],
					],
				];
				break;

			case 'image':
				$allowed_html = [
					'img' => [
						'src'      => [],
						'data-src' => [],
						'alt'      => [],
						'height'   => [],
						'width'    => [],
						'class'    => [],
						'id'       => [],
						'style'    => [],
						'srcset'   => [],
						'loading'  => [],
						'sizes'    => [],
					],
					'div' => [
						'class' => [],
					],
				];
				break;

			case 'anchor':
				$allowed_html = [
					'a' => [
						'href'  => [],
						'title' => [],
						'class' => [],
						'id'    => [],
						'style' => [],
					],
				];
				break;

			default:
				// code...
				break;
		}

		return $allowed_html;
	}

	/**
	 * Safe get a validated HTML tag.
	 *
	 * @param string $tag HTML tag.
	 *
	 * @return string
	 */
	public static function get_validated_html_tag( $tag ) {
		$allowed_html_wrapper_tags = [
			'a',
			'article',
			'aside',
			'button',
			'div',
			'footer',
			'h1',
			'h2',
			'h3',
			'h4',
			'h5',
			'h6',
			'header',
			'main',
			'nav',
			'p',
			'section',
			'span',
		];

		return in_array( strtolower( $tag ), $allowed_html_wrapper_tags, true ) ? $tag : 'div';
	}

	/**
	 * Safe print a validated HTML tag.
	 *
	 * @param string $tag HTML tag.
	 *
	 * @return void
	 */
	public static function print_validated_html_tag( $tag ) {
		self::print_html( self::get_validated_html_tag( $tag ) );
	}

	/**
	 * Insert Some array element
	 *
	 * @param [type]  $key The elements will insert nearby this key.
	 * @param [type]  $main_array Original array.
	 * @param [type]  $insert_array some element will insert in original array.
	 * @param boolean $is_after array insert position base on the key.
	 *
	 * @return array
	 */
	public static function insert_controls( $key, $main_array, $insert_array, $is_after = false ) {
		$index = array_search( $key, array_keys( $main_array ), true );
		if ( 'integer' === gettype( $index ) ) {
			if ( $is_after ) {
				$index++;
			}
			$main_array = array_merge(
				array_slice( $main_array, 0, $index ),
				$insert_array,
				array_slice( $main_array, $index )
			);
		}

		return $main_array;
	}

	/**
	 * Image Sizes
	 *
	 * @return array
	 */
	public static function get_image_sizes() {
		global $_wp_additional_image_sizes;

		$sizes = [];

		foreach ( get_intermediate_image_sizes() as $_size ) {
			if ( in_array( $_size, [ 'thumbnail', 'medium', 'large' ], true ) ) {
				$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
				$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
				$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
				$sizes[ $_size ] = [
					'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
					'height' => $_wp_additional_image_sizes[ $_size ]['height'],
					'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
				];
			}
		}

		$imgSize = [];

		foreach ( $sizes as $key => $img ) {
			$imgSize[ $key ] = ucfirst( $key ) . " ({$img['width']}*{$img['height']})";
		}

		$imgSize['full']        = esc_html__( 'Full size', 'shopbuilder' );
		$imgSize['rtsb_custom'] = esc_html__( 'Custom image size', 'shopbuilder' );

		return $imgSize;
	}

	/**
	 * Free Layouts
	 *
	 * @return array
	 */
	public static function free_layouts() {
		$layouts = [
			'grid-layout1'            => esc_html__( 'Grid Layout 1', 'shopbuilder' ),
			'grid-layout2'            => esc_html__( 'Grid Layout 2', 'shopbuilder' ),
			'list-layout1'            => esc_html__( 'List Layout 1', 'shopbuilder' ),
			'list-layout2'            => esc_html__( 'List Layout 2', 'shopbuilder' ),
			'slider-layout1'          => esc_html__( 'Slider Layout 1', 'shopbuilder' ),
			'slider-layout2'          => esc_html__( 'Slider Layout 2', 'shopbuilder' ),
			'category-single-layout1' => esc_html__( 'Category Single Layout 1', 'shopbuilder' ),
			'category-layout1'        => esc_html__( 'Category Layout 1', 'shopbuilder' ),
			'category-layout2'        => esc_html__( 'Category Layout 2', 'shopbuilder' ),
		];

		return apply_filters( 'rtsb/elements/elementor/free_layouts', $layouts );
	}

	/**
	 * Get all terms by taxonomy
	 *
	 * @param string $taxonomy Taxonomy name.
	 * @param bool   $placeholder Placeholder..
	 *
	 * @return array
	 */
	public static function get_all_terms( $taxonomy, $placeholder = false ) {
		$terms = [];

		if ( empty( $taxonomy ) ) {
			return $terms;
		}
		$cache_key = 'rtsb_get_all_terms_by_tax_name_' . $taxonomy;
		if ( isset( self::$cache[ $cache_key ] ) ) {
			return self::$cache[ $cache_key ];
		}

		$termList = get_terms(
			[
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			]
		);

		if ( is_array( $termList ) && ! empty( $termList ) && empty( $termList['errors'] ) ) {
			if ( $placeholder ) {
				$terms = [
					'all' => __( 'All Products', 'shopbuilder' ),
				];
			}

			foreach ( $termList as $term ) {
				$terms[ $term->term_id ] = esc_html( $term->name );
			}
		}
		self::$cache[ $cache_key ] = $terms;
		return $terms;
	}

	/**
	 * Get all terms by attributes
	 *
	 * @return array
	 */
	public static function get_all_attributes() {
		$terms    = [];
		$termList = [];

		$attributes = wc_get_attribute_taxonomies();

		if ( $attributes ) {
			foreach ( $attributes as $tax ) {
				if ( taxonomy_exists( wc_attribute_taxonomy_name( $tax->attribute_name ) ) ) {
					$termList[ $tax->attribute_name ] = get_terms( wc_attribute_taxonomy_name( $tax->attribute_name ) );
				}
			}
		}

		if ( ! empty( $termList ) && empty( $termList['errors'] ) && is_array( $termList ) ) {
			foreach ( $termList as $name => $atts ) {
				foreach ( $atts as $term ) {
					$terms[ $term->term_id ] = esc_html( ucwords( str_replace( '-', ' ', $name ) ) . ' - ' . $term->name );
				}
			}
		}

		return $terms;
	}

	/**
	 * Get all attributes name
	 *
	 * @return array
	 */
	public static function get_all_attributes_name() {
		$attributes_name = [];

		$attributes = wc_get_attribute_taxonomies();

		if ( $attributes ) {
			foreach ( $attributes as $tax ) {
				if ( taxonomy_exists( wc_attribute_taxonomy_name( $tax->attribute_name ) ) ) {
					$attributes_name[ wc_attribute_taxonomy_name( $tax->attribute_name ) ] = ucwords( $tax->attribute_name );
				}
			}
		}

		return $attributes_name;
	}

	/**
	 * Get User list
	 *
	 * @return array
	 */
	public static function get_users() {
		$users = [];
		$u     = get_users();

		if ( ! empty( $u ) ) {
			foreach ( $u as $user ) {
				$users[ $user->ID ] = $user->display_name;
			}
		}

		return $users;
	}

	/**
	 * Get Taxonomy List.
	 *
	 * @return array
	 */
	public static function get_tax_list() {
		return apply_filters(
			'rtsb/elements/tax_list',
			[
				'product_cat' => esc_html__( 'Product Category', 'shopbuilder' ),
			]
		);
	}

	/**
	 * Get Category Query List.
	 *
	 * @return array
	 */
	public static function get_cat_list() {
		return [
			'all'             => esc_html__( 'All Categories', 'shopbuilder' ),
			'specific_parent' => esc_html__( 'Sub-Categories by Parent', 'shopbuilder' ),
			'cat_ids'         => esc_html__( 'Select by ID', 'shopbuilder' ),
			'selection'       => esc_html__( 'Manual Selection', 'shopbuilder' ),
		];
	}

	/**
	 * Get Term List.
	 *
	 * @param string $taxonomy Taxonomy.
	 * @param bool   $first_term First term.
	 * @param bool   $only_parents Only parent terms.
	 * @param bool   $return_slug Return with slug.
	 *
	 * @return int|array
	 */
	public static function get_terms( $taxonomy, $first_term = false, $only_parents = false, $return_slug = false ) {
		if ( ! is_admin() ) {
			return [];
		}

		// TODO:: Return Slug always true for all settings.
		$term_list = [];
		$args      = [
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		];

		if ( $only_parents ) {
			$args['parent'] = 0;
		}

		$terms = get_terms( $args );

		if ( empty( $terms ) ) {
			return [ esc_html__( 'Nothing found', 'shopbuilder' ) ];
		}

		foreach ( $terms as $term ) {
			if ( $return_slug ) {
				$term_list[ $term->slug ] = $term->name;
			} else {
				$term_list[ $term->term_id ] = $term->name;
			}
		}

		if ( $first_term ) {
			return array_keys( $term_list )[0];
		} else {
			return $term_list;
		}
	}

	/**
	 * Get product rating.
	 *
	 * @param array $args Arguments.
	 *
	 * @return string|void
	 */
	public static function get_product_rating_html( $args = [] ) {
		global $product;

		$html           = '';
		$rating_count   = $product->get_rating_count();
		$average_rating = $product->get_average_rating();

		if ( ! rtsb()->has_pro() || empty( $args ) ) {
			if ( ! $rating_count || empty( wc_get_rating_html( $average_rating, $rating_count ) ) ) {
				return $html;
			}

			$html .= '<div class="product-rating">';
			$html .= wc_get_rating_html( $average_rating, $rating_count );
			$html .= ! empty( $html ) ? '<span class="rtsb-count">(' . $average_rating . ')</span>' : '';
			$html .= '</div>';

			self::print_html( $html, true );

			return;
		}

		if ( empty( $rating_count ) && ! $args['show_empty_rating'] ) {
			return '';
		}

		$preset  = ! empty( $args['preset'] ) ? $args['preset'] : 'preset1';
		$average = $args['show_average_rating'] ? '<span class="rtsb-count">(' . $average_rating . ')</span>' : '';
		$count   = '';

		if ( $args['show_rating_count'] ) {
			$count .= '<div class="rtsb-count">';
			$count .= sprintf(
				/* translators: %s is the number of reviews */
				_n( '%s Review', '%s Reviews', $rating_count, 'shopbuilder' ),
				$rating_count
			);
			$count .= '</div>';
		}

		if ( 'preset2' === $preset ) {
			$average = $args['show_average_rating'] ? '<div class="inner-wrapper"><span class="star-icon"></span><span class="average-rating">' . $average_rating . '</span></div>' : '';
		}

		$html .= '<div class="product-rating ' . esc_attr( $preset ) . '">';

		if ( 'preset1' === $preset ) {
			$html .= wc_get_rating_html( $average_rating, $rating_count );
			$html .= $average;
			$html .= $count;
		} elseif ( 'preset2' === $preset ) {
			$html .= $average;
			$html .= $count;
		}

		$html .= '</div>';

		self::print_html( $html, true );
	}

	public static function get_product_simple_rating_html() {
		global $product;
		$html           = null;
		$rating_count   = $product->get_rating_count();
		$average_rating = $product->get_average_rating();
		$html          .= '<div class="product-rating">';
		$html          .= wc_get_rating_html( $average_rating, $rating_count );
		$html          .= ! empty( $html ) ? '<span class="rtsb-count">(' . $average_rating . ')</span>' : '';
		$html          .= '</div>';

		self::print_html( $html, true );
	}



	/**
	 * Text truncation.
	 *
	 * @param string $text_to_truncate Text.
	 * @param int    $limit Limit.
	 * @param string $after After text.
	 *
	 * @return string
	 */
	public static function text_truncation( $text_to_truncate, $limit, $after = '&#8230;' ) {
		if ( empty( $limit ) ) {
			return $text_to_truncate;
		}

		$limit++;

		$text = '';

		if ( mb_strlen( $text_to_truncate ) > $limit ) {
			$subex   = mb_substr( wp_strip_all_tags( $text_to_truncate ), 0, $limit );
			$exwords = explode( ' ', $subex );
			$excut   = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );

			if ( $excut < 0 ) {
				$text .= mb_substr( $subex, 0, $excut ) . $after;
			} else {
				$text .= $subex . $after;
			}
		} else {
			$text .= $text_to_truncate;
		}

		return $text;
	}

	/**
	 * Promo Badge HTML
	 *
	 * @param string $text Text.
	 * @param string $class Class.
	 *
	 * @return void
	 */
	public static function get_badge_html( $text, $class = 'fill' ) {
		if ( self::is_module_active( 'product_badges' ) && 'rtsb_yes' === $text ) {
			do_action( 'rtsb/modules/product_badges/frontend/display' );

			return;
		}

		if ( empty( $text ) ) {
			return;
		}
		ob_start();
		?>

		<ul class="rtsb-promotion-list">
			<li class="rtsb-promotion-list-item">
				<span class="rtsb-tag-<?php echo ! empty( $class ) ? esc_attr( $class ) : ''; ?>"><?php echo esc_html( $text ); ?></span>
			</li>
		</ul>

		<?php
		self::print_html( ob_get_clean() );
	}

	/**
	 * Categories HTML
	 *
	 * @param int    $id Product ID.
	 * @param string $class Custom class.
	 *
	 * @return void|string
	 */
	public static function get_categories_list( $id, $class = 'rtsb-category-outline' ) {
		if ( empty( $id ) ) {
			return '';
		}

		ob_start();
		?>

		<ul class="rtsb-category-list <?php echo esc_attr( $class ); ?>">
			<?php
			self::print_html(
				wc_get_product_category_list(
					$id,
					'</li><li class="rtsb-category-list-item">',
					'<li class="rtsb-category-list-item">',
					'</li>'
				)
			);
			?>
		</ul>

		<?php
		self::print_html( ob_get_clean() );
	}

	/**
	 * Get Featured Image HTML.
	 *
	 * @param string $type Image type.
	 * @param int    $post_id Post ID.
	 * @param string $f_img_size Image size.
	 * @param null   $default_img_id Default image ID.
	 * @param array  $custom_img_size Custom image size.
	 * @param bool   $lazy Lazy load check.
	 * @param bool   $hover Hover image.
	 * @param bool   $gallery Gallery image.
	 * @param bool   $custom_image_id Custom category image ID.
	 *
	 * @return string|null
	 */
	public static function get_product_image_html( $type = 'product', $post_id = null, $f_img_size = 'medium', $default_img_id = null, $custom_img_size = [], $lazy = false, $hover = false, $gallery = false, $custom_image_id = 0 ) {
		$img_html      = null;
		$attachment_id = null;
		$c_size        = false;
		$hover_class   = '';
		$post_title    = '';

		if ( 'rtsb_custom' === $f_img_size ) {
			$f_img_size = 'full';
			$c_size     = true;
		}

		if ( ! rtsb()->has_pro() ) {
			$gallery = false;
		}

		if ( $hover ) {
			$a_id        = $post_id;
			$hover_class = ' rtsb-img-hover';
		} elseif ( $gallery ) {
			$a_id = $post_id;
		} else {
			if ( 'product' === $type ) {
				$a_id       = get_post_thumbnail_id( $post_id );
				$post_title = get_the_title( $post_id );
			} elseif ( 'category' === $type ) {
				$a_id       = $custom_image_id ? $custom_image_id : get_term_meta( $post_id, 'thumbnail_id', true );
				$post_title = get_term( $post_id )->name;
			} else {
				$a_id = $default_img_id;
			}
		}

		$img_alt    = trim( wp_strip_all_tags( get_post_meta( $a_id, '_wp_attachment_image_alt', true ) ) );
		$alt_tag    = ! empty( $img_alt ) ? $img_alt : wp_strip_all_tags( $post_title );
		$lazy_class = $lazy ? ' swiper-lazy' : '';
		$attr       = [
			'class' => 'img-responsive rtsb-product-image' . $lazy_class . $hover_class,
			'alt'   => $alt_tag,
		];

		if ( $a_id ) {
			$img_html      = wp_get_attachment_image( $a_id, $f_img_size, false, $attr );
			$attachment_id = $a_id;
		}

		if ( ! $img_html && $default_img_id ) {
			$img_html      = wp_get_attachment_image( $default_img_id, $f_img_size, false, $attr );
			$attachment_id = $default_img_id;
		}

		if ( $img_html && $c_size ) {
			preg_match( '@src="([^"]+)"@', $img_html, $match );
			$img_src = array_pop( $match );
			$w       = ! empty( $custom_img_size['width'] ) ? absint( $custom_img_size['width'] ) : null;
			$h       = ! empty( $custom_img_size['height'] ) ? absint( $custom_img_size['height'] ) : null;
			$c       = ! empty( $custom_img_size['crop'] ) && 'soft' === $custom_img_size['crop'] ? false : true;

			if ( $w && $h ) {
				$image = self::image_resize( $img_src, $w, $h, $c, false );

				if ( ! empty( $image ) ) {
					[ $src, $width, $height ] = $image;

					$hwstring   = image_hwstring( $width, $height );
					$attachment = get_post( $attachment_id );
					$attr       = apply_filters( 'wp_get_attachment_image_attributes', $attr, $attachment, $f_img_size );

					if ( $lazy ) {
						$attr['data-src'] = $src;
					} else {
						$attr['src'] = $src;
					}

					$attr     = array_map( 'esc_attr', $attr );
					$img_html = rtrim( "<img $hwstring" );

					foreach ( $attr as $name => $value ) {
						$img_html .= " $name=" . '"' . $value . '"';
					}

					$img_html .= ' />';
				}
			}
		}

		if ( ! $img_html ) {
			$hwstring      = image_hwstring( 160, 160 );
			$attr          = isset( $attr['src'] ) ? apply_filters( 'wp_get_attachment_image_attributes', $attr, false, $f_img_size ) : [];
			$attr['class'] = 'default-img';
			$attr['src']   = esc_url( rtsb()->get_assets_uri( 'images/demo.png' ) );
			$attr['alt']   = esc_html__( 'Default Image', 'shopbuilder' );
			$img_html      = rtrim( "<img $hwstring" );

			foreach ( $attr as $name => $value ) {
				$img_html .= " $name=" . '"' . $value . '"';
			}

			$img_html .= ' />';
		}

		if ( $lazy ) {
			$img_html = $img_html . '<div class="swiper-lazy-preloader swiper-lazy-preloader"></div>';
		}

		return $img_html;
	}

	/**
	 * Call the Image resize model for resize function
	 *
	 * @param string     $url URL.
	 * @param int        $width Width.
	 * @param int        $height Height.
	 * @param string     $crop Crop.
	 * @param bool|true  $single Single.
	 * @param bool|false $upscale Upscale.
	 *
	 * @return array|bool|string
	 */
	public static function image_resize( $url, $width = null, $height = null, $crop = null, $single = true, $upscale = false ) {
		$rtResize = new ReSizer();

		return $rtResize->process( $url, $width, $height, $crop, $single, $upscale );
	}

	/**
	 * Get Product Image
	 *
	 * @param string $f_image Featured Image.
	 * @param string $h_image Hover Image.
	 *
	 * @return void
	 */
	public static function get_product_image( $f_image, $h_image = null ) {
		if ( empty( $f_image ) ) {
			return;
		}

		echo wp_kses( $f_image, self::allowedHtml( 'image' ) );

		if ( ! empty( $h_image ) ) {
			echo wp_kses( $h_image, self::allowedHtml( 'image' ) );
		}
	}

	/**
	 * Post Custom Field value.
	 *
	 * @param int    $post_id Post id.
	 * @param string $field_key Custom Field Key.
	 * @param string $field_fallback FallBack text.
	 *
	 * @return string|void
	 */
	public static function get_post_custom_field_value( $post_id, $field_key = '', $field_fallback = '' ) {
		if ( ! $post_id || ! $field_key ) {
			return;
		}
		$field_value = get_post_meta( $post_id, $field_key, true );
		if ( empty( $field_value ) ) {
			$field_value = ! empty( $field_fallback ) ? $field_fallback : $field_value;
		}
		$field_value = apply_filters( 'rtsb/get_post_custom_field_value/' . $field_key, $field_value, $field_key );

		return sprintf( '<span class="rtsb-woo-custom-field">%s</span>', $field_value );
	}

	/**
	 * Elementor Icon
	 *
	 * @param array  $control Elementor Control array.
	 * @param string $class Custom class.
	 * @param string $builder Builder name.
	 *
	 * @return string
	 */
	public static function icons_manager( $control, $class = '', $builder = 'elementor' ): string {
		if ( empty( $control['value'] ) ) {
			return '';
		}
		if ( is_array( $control['value'] ) ) {
			$cache_key = 'icons_managet_' . str_replace( ' ', '', md5( serialize( $control['value'] ) ) );
		} else {
			$cache_key = 'icons_managet_' . str_replace( ' ', '', $control['value'] );
		}
		if ( isset( self::$cache[ $cache_key ] ) ) {
			return self::$cache[ $cache_key ];
		}

		$attributes = [
			'aria-hidden' => 'true',
		];

		if ( ! empty( $class ) ) {
			$attributes['class'] = esc_attr( $class );
		}

		ob_start();

		if ( defined( 'ELEMENTOR_VERSION' ) && ! empty( $control ) && 'elementor' === $builder ) {
			Icons_Manager::render_icon( $control, $attributes );
		}

		$icons                     = ob_get_clean();
		self::$cache[ $cache_key ] = $icons;
		return $icons;
	}

	/**
	 * Get product swatches.
	 *
	 * @param string $type Swatch type.
	 *
	 * @return void
	 */
	public static function get_product_swatches( $type ) {
		?>
		<div class="rtsb-swatches <?php echo esc_attr( $type ); ?>-layout">
			<?php
			do_action( 'rtwpvs_show_archive_variation' );
			?>
		</div>
		<?php
	}

	/**
	 * Get sale badge
	 *
	 * @param string $type Badge type.
	 * @param string $text Badge text.
	 * @param string $out_of_stock_text Out of stock text.
	 *
	 * @return string
	 */
	public static function get_sale_badge( $type, $text, $out_of_stock_text ) {
		global $product;

		if ( ! $product->is_in_stock() ) {
			return $out_of_stock_text;
		}

		if ( ! $product->is_on_sale() ) {
			return '';
		}

		$badge_text = '';
		$percentage = self::calculate_sale_percentage( $product );

		if ( $percentage > 0 ) {
			$badge_text = '-' . round( $percentage ) . '%';
		}

		if ( 'text' === $type ) {
			$badge_text = $text;
		}

		return $badge_text;
	}

	/**
	 * Get sale badge
	 *
	 * @param object $product Product Object.
	 * @param string $type Badge type.
	 * @param string $text Badge text.
	 * @param string $out_of_stock_text Out of stock text.
	 *
	 * @return string
	 */
	public static function get_promo_badge( $product, $type, $text, $out_of_stock_text ) {
		$disable_badges    = self::get_option( 'general', 'guest_user', 'hide_badges', '' );
		$badges_visibility = rtsb()->has_pro() && ! is_user_logged_in() && $disable_badges;

		if ( $badges_visibility ) {
			return '';
		}

		if ( is_null( $product ) ) {
			global $product;
		}

		if ( ! $product instanceof WC_Product ) {
			return '';
		}

		if ( ! $product->is_in_stock() ) {
			return $out_of_stock_text;
		}

		if ( ! $product->is_on_sale() ) {
			return '';
		}

		$badge_text = '';
		$percentage = self::calculate_sale_percentage( $product );

		if ( $percentage > 0 ) {
			$badge_text = '-' . round( $percentage ) . '%';
		}

		if ( 'text' === $type ) {
			$badge_text = $text;
		}

		return $badge_text;
	}

	/**
	 * Calculate Sale Percentage
	 *
	 * @param object $product Product object.
	 *
	 * @return float|int|string
	 */
	public static function calculate_sale_percentage( $product = null ) {
		if ( is_null( $product ) ) {
			global $product;
		}

		if ( ! $product instanceof WC_Product ) {
			return '';
		}

		if ( ! $product->is_on_sale() ) {
			return '';
		}

		$percentage     = null;
		$max_percentage = '';

		if ( $product->is_type( 'simple' ) ) {
			$max_percentage = ( ( $product->get_regular_price() - $product->get_sale_price() ) / $product->get_regular_price() ) * 100;
		} elseif ( $product->is_type( 'variable' ) ) {
			$max_percentage = 0;

			foreach ( $product->get_children() as $child_id ) {

				$variation = wc_get_product( $child_id );

				$price = $variation->get_regular_price();
				$sale  = $variation->get_sale_price();

				if ( 0 !== $price && ! empty( $sale ) ) {
					$percentage = ( $price - $sale ) / $price * 100;
				}

				if ( $percentage > $max_percentage ) {
					$max_percentage = $percentage;
				}
			}
		}

		if ( $max_percentage > 0 ) {
			return round( $max_percentage );
		}

		return 0;
	}

	/**
	 * Pagination
	 *
	 * @param string  $pages Pages.
	 * @param integer $range Range.
	 * @param boolean $ajax Ajax.
	 *
	 * @return string
	 */
	public static function custom_pagination( $pages = '', $range = 4, $ajax = false ) {
		$html          = null;
		$visible_range = apply_filters( 'rtsb/elements/pagination/visible_range', ( $range * 2 ) + 1 );

		global $paged;

		if ( is_front_page() ) {
			$paged = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;
		} else {
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		}

		if ( empty( $paged ) ) {
			$paged = 1;
		}

		if ( '' === $pages ) {
			global $wp_query;

			$pages = $wp_query->max_num_pages;

			if ( ! $pages ) {
				$pages = 1;
			}
		}

		$ajaxClass = null;
		$dataAttr  = null;

		if ( $ajax ) {
			$ajaxClass = ' rtsb-pagination-ajax';
			$dataAttr  = "data-paged='1'";
			$dataAttr .= ' data-visible-range="' . esc_attr( $visible_range ) . '"';
		}

		if ( 1 !== $pages ) {
			$html .= '<div class="rtsb-pagination' . $ajaxClass . '" ' . $dataAttr . '>';
			$html .= '<ul class="pagination-list">';

			if ( $paged > 2 && $paged > $visible_range + 1 && $visible_range < $pages ) {
				$html .= "<li><a data-paged='1' href='" . get_pagenum_link( 1 ) . "' aria-label='First'>&laquo;</a></li>";
			}

			if ( $paged > 1 && $visible_range < $pages ) {
				$p     = $paged - 1;
				$html .= "<li><a data-paged='{$p}' href='" . get_pagenum_link( $p ) . "' aria-label='Previous'>&lsaquo;</a></li>";
			}

			for ( $i = 1; $i <= $pages; $i++ ) {
				if ( 1 != $pages && ( ! ( $i >= $paged + $visible_range + 1 || $i <= $paged - $visible_range - 1 ) || $pages <= $visible_range ) ) {
					$html .= ( $paged == $i ) ? '<li class="active"><span>' . $i . '</span></li>' : "<li><a data-paged='{$i}' href='" . get_pagenum_link( $i ) . "'>" . $i . '</a></li>';
				}
			}

			if ( $paged < $pages && $visible_range < $pages ) {
				$p     = $paged + 1;
				$html .= "<li><a data-paged='{$p}' href=\"" . get_pagenum_link( $paged + 1 ) . "\"  aria-label='Next'>&rsaquo;</a></li>";
			}

			if ( $paged < $pages - 1 && $paged + $range - 1 < $pages && $visible_range < $pages ) {
				$html .= "<li><a data-paged='{$pages}' href='" . get_pagenum_link( $pages ) . "' aria-label='Last'>&raquo;</a></li>";
			}

			$html .= '</ul>';
			$html .= '</div>';
		}

		return $html;
	}

	/**
	 * Action Buttons HTMl
	 *
	 * @param array  $items Items.
	 * @param array  $ajax_cart Ajax cart HTML.
	 * @param string $preset Button preset.
	 * @param array  $position Position.
	 * @param string $placement Placement.
	 *
	 * @return void|string
	 */
	public static function get_formatted_action_buttons( $items, $ajax_cart = '', $preset = 'preset1', $position = 'above', $placement = 'top' ) {
		if ( empty( $items ) ) {
			return;
		}

		$html  = '';
		$class = '';
		$args  = [ $items, $ajax_cart, $preset, $position, $placement ];

		if ( ( 'top' === $placement && 'after' === $position ) || ( 'bottom' === $placement && 'above' === $position ) ) {
			return apply_filters( 'rtsb/elements/elementor/formatted_action_buttons', $html, $args );
		}

		if ( 'preset2' === $preset ) {
			$class = 'rtsb-action-buttons-vertical vertical-delay-effect ' . $preset;
		} elseif ( 'preset4' === $preset ) {
			$class = 'rtsb-action-buttons-vertical rtsb-action-buttons-vertical-left vertical-delay-effect ' . $preset;
		} elseif ( 'preset1' === $preset || 'preset3' === $preset ) {
			$class = 'rtsb-action-buttons-cart-box-width-auto horizontal-floating-btn ' . $preset;
		}

		if ( 'after' === $position && 'preset1' === $preset ) {
			$class .= ' after-content';
		}

		if ( 'preset3' === $preset ) {
			$html .= '<div class="rtsb-action-buttons top-part ' . esc_attr( $preset ) . '">';
			$html .= '<ul class="rtsb-action-button-list">';

			ob_start();
			/**
			 * Additional formatted action buttons hook.
			 */
			do_action( 'rtsb/elements/elementor/additional_action_buttons', $items );
			$html .= ob_get_clean();

			$html .= self::get_action_button_by_type( $items, 'wishlist' );
			$html .= self::get_action_button_by_type( $items, 'compare' );
			$html .= self::get_action_button_by_type( $items, 'quick_view' );
			$html .= '</ul>';
			$html .= '</div>';
			$html .= '<div class="rtsb-action-buttons bottom-part ' . esc_attr( $preset ) . '">';
			$html .= '<ul class="rtsb-action-button-list">';
			$html .= self::get_action_button_by_type( $items, 'add_to_cart', $ajax_cart );
			$html .= '</ul>';
			$html .= '</div>';
		} elseif ( 'preset1' === $preset || 'preset2' === $preset || 'preset4' === $preset ) {
			$html .= '<div class="rtsb-action-buttons ' . esc_attr( $class ) . '">';
			$html .= '<ul class="rtsb-action-button-list">';
			$html .= self::get_action_button_by_type( $items, 'add_to_cart', $ajax_cart );

			ob_start();
			/**
			 * Additional formatted action buttons hook.
			 */
			do_action( 'rtsb/elements/elementor/additional_action_buttons', $items );
			$html .= ob_get_clean();

			$html .= self::get_action_button_by_type( $items, 'wishlist' );
			$html .= self::get_action_button_by_type( $items, 'compare' );
			$html .= self::get_action_button_by_type( $items, 'quick_view' );
			$html .= '</ul>';
			$html .= '</div>';
		}

		return apply_filters( 'rtsb/elements/elementor/formatted_action_buttons', $html, $args );
	}

	/**
	 * Get Action Button HTML
	 *
	 * @param array  $items Items.
	 * @param string $type Button type.
	 * @param string $cart_html Ajax cart HTML.
	 * @param string $wrapper Wrapper tag.
	 *
	 * @return void|string
	 */
	public static function get_action_button_by_type( $items, $type, $cart_html = '', $wrapper = 'li' ) {
		if ( ! in_array( $type, $items, true ) ) {
			return;
		}

		$html  = '';
		$class = 'rtsb-action-button-item';

		if ( 'add_to_cart' === $type ) {
			$class .= ' rtsb-cart' . ( empty( $cart_html ) ? esc_attr( ' no-cart-button' ) : '' );
		} else {
			$class .= ' rtsb-' . esc_attr( str_replace( '_', '-', $type ) );
		}

		$html .= '<' . esc_attr( $wrapper ) . ' class="' . esc_attr( $class ) . '">';

		if ( ( 'add_to_cart' === $type ) && ( ! empty( $cart_html ) ) ) {
			$html .= $cart_html;
		} else {
			$html .= shortcode_exists( 'rtsb_' . $type . '_button' ) ? do_shortcode( '[rtsb_' . $type . '_button]' ) : null;
		}

		$html .= '</' . esc_attr( $wrapper ) . '>';

		return apply_filters( 'rtsb/elements/elementor/get_action_button_by_type', $html );
	}

	/**
	 * Social Share Platforms.
	 *
	 * @return mixed|null
	 */
	public static function social_share_platforms_list() {
		return apply_filters(
			'rtsb/settings/social_share/platforms',
			[
				[
					'value' => 'facebook',
					'label' => esc_html__( 'Facebook', 'shopbuilder' ),
				],
				[
					'value' => 'twitter',
					'label' => esc_html__( 'Twitter', 'shopbuilder' ),
				],
				[
					'value' => 'linkedin',
					'label' => esc_html__( 'Linkedin', 'shopbuilder' ),
				],
				[
					'value' => 'pinterest',
					'label' => esc_html__( 'Pinterest', 'shopbuilder' ),
				],
				[
					'value' => 'skype',
					'label' => esc_html__( 'Skype', 'shopbuilder' ),
				],
				[
					'value' => 'whatsapp',
					'label' => esc_html__( 'Whatsapp', 'shopbuilder' ),
				],
				[
					'value' => 'reddit',
					'label' => esc_html__( 'Reddit', 'shopbuilder' ),
				],
				[
					'value' => 'telegram',
					'label' => esc_html__( 'Telegram', 'shopbuilder' ),
				],
			]
		);
	}

	/**
	 * Get Social Share link HTML.
	 *
	 * @param int     $id Post ID.
	 * @param array   $types Preset type.
	 * @param string  $preset Style type.
	 * @param boolean $show_icon Show icon.
	 * @param boolean $show_text Show text.
	 *
	 * @return string
	 */
	public static function get_social_share_html( int $id, array $types, string $preset = 'default', $show_icon = true, $show_text = true ) {
		$attr   = [ 'postid' => $id ];
		$output = '';

		if ( empty( $types ) ) {
			return $output;
		}

		foreach ( $types as $type ) {
			$link          = [];
			$link['type']  = $type['share_items'];
			$link['class'] = '';
			$link['img']   = apply_filters( 'rtsb/elements/share/default_img', '', $id, $link );

			if ( 'site' === $id ) {
				$link['url']   = home_url();
				$link['title'] = wp_strip_all_tags( get_bloginfo( 'name' ) );
			} elseif ( 0 === strpos( $id, 'http' ) ) {
				$link['url']   = $id;
				$link['title'] = '';
			} else {
				$link['url']   = get_permalink( $id );
				$link['title'] = wp_strip_all_tags( get_the_title( $id ) );

				if ( has_post_thumbnail( $id ) ) {
					$link['img'] = wp_get_attachment_image_url( get_post_thumbnail_id( $id ), 'full' );
				}

				$link['img'] = apply_filters( 'rtsb/elements/share/single_img', $link['img'], $id, $link );
			}

			$link['url'] = apply_filters( 'rtsb/elements/share/url', $link['url'], $link );

			switch ( $type['share_items'] ) {
				case 'facebook':
					$link['link']           = esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . $link['url'] . '&display=popup&ref=plugin&src=share_button' );
					$link['icon']           = '<svg xmlns="http://www.w3.org/2000/svg" width="18.8125" height="32" viewBox="0 0 602 1024"><path d="M548 6.857v150.857h-89.714q-49.143 0-66.286 20.571t-17.143 61.714v108h167.429l-22.286 169.143h-145.143v433.714h-174.857v-433.714h-145.714v-169.143h145.714v-124.571q0-106.286 59.429-164.857t158.286-58.571q84 0 130.286 6.857z"></path></svg>';
					$link['attr_title']     = esc_html__( 'Share on Facebook', 'shopbuilder' );
					$link['social_network'] = 'Facebook';
					$link['social_action']  = 'Share';
					break;
				case 'twitter':
					$link['link']           = esc_url( 'https://twitter.com/intent/tweet?text=' . htmlspecialchars( rawurlencode( html_entity_decode( $link['title'], ENT_COMPAT, 'UTF-8' ) ), ENT_COMPAT, 'UTF-8' ) . '&url=' . $link['url'] );
					$link['icon']           = '<svg width="24" height="24" viewBox="0 0 1200 1227" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M714.163 519.284L1160.89 0H1055.03L667.137 450.887L357.328 0H0L468.492 681.821L0 1226.37H105.866L515.491 750.218L842.672 1226.37H1200L714.137 519.284H714.163ZM569.165 687.828L521.697 619.934L144.011 79.6944H306.615L611.412 515.685L658.88 583.579L1055.08 1150.3H892.476L569.165 687.854V687.828Z" /></svg>';
					$link['attr_title']     = esc_html__( 'Share on Twitter', 'shopbuilder' );
					$link['social_network'] = 'Twitter';
					$link['social_action']  = 'Tweet';
					break;
				case 'pinterest':
					$link['link']           = esc_url( 'https://pinterest.com/pin/create/button/?url=' . $link['url'] . '&media=' . $link['img'] . '&description=' . $link['title'] );
					$link['icon']           = '<svg xmlns="http://www.w3.org/2000/svg" width="22.84375" height="32" viewBox="0 0 731 1024"><path d="M0 341.143q0-61.714 21.429-116.286t59.143-95.143 86.857-70.286 105.714-44.571 115.429-14.857q90.286 0 168 38t126.286 110.571 48.571 164q0 54.857-10.857 107.429t-34.286 101.143-57.143 85.429-82.857 58.857-108 22q-38.857 0-77.143-18.286t-54.857-50.286q-5.714 22.286-16 64.286t-13.429 54.286-11.714 40.571-14.857 40.571-18.286 35.714-26.286 44.286-35.429 49.429l-8 2.857-5.143-5.714q-8.571-89.714-8.571-107.429 0-52.571 12.286-118t38-164.286 29.714-116q-18.286-37.143-18.286-96.571 0-47.429 29.714-89.143t75.429-41.714q34.857 0 54.286 23.143t19.429 58.571q0 37.714-25.143 109.143t-25.143 106.857q0 36 25.714 59.714t62.286 23.714q31.429 0 58.286-14.286t44.857-38.857 32-54.286 21.714-63.143 11.429-63.429 3.714-56.857q0-98.857-62.571-154t-163.143-55.143q-114.286 0-190.857 74t-76.571 187.714q0 25.143 7.143 48.571t15.429 37.143 15.429 26 7.143 17.429q0 16-8.571 41.714t-21.143 25.714q-1.143 0-9.714-1.714-29.143-8.571-51.714-32t-34.857-54-18.571-61.714-6.286-60.857z"></path></svg>';
					$link['attr_title']     = esc_html__( 'Share on Pinterest', 'shopbuilder' );
					$link['social_network'] = 'Pinterest';
					$link['social_action']  = 'Pin';
					break;
				case 'linkedin':
					$link['link']           = esc_url( 'https://www.linkedin.com/shareArticle?url=' . $link['url'] . '&title=' . $link['title'] );
					$link['icon']           = '<svg xmlns="http://www.w3.org/2000/svg" width="27.4375" height="32" viewBox="0 0 878 1024"><path d="M199.429 357.143v566.286h-188.571v-566.286h188.571zM211.429 182.286q0.571 41.714-28.857 69.714t-77.429 28h-1.143q-46.857 0-75.429-28t-28.571-69.714q0-42.286 29.429-70t76.857-27.714 76 27.714 29.143 70zM877.714 598.857v324.571h-188v-302.857q0-60-23.143-94t-72.286-34q-36 0-60.286 19.714t-36.286 48.857q-6.286 17.143-6.286 46.286v316h-188q1.143-228 1.143-369.714t-0.571-169.143l-0.571-27.429h188v82.286h-1.143q11.429-18.286 23.429-32t32.286-29.714 49.714-24.857 65.429-8.857q97.714 0 157.143 64.857t59.429 190z"></path></svg>';
					$link['attr_title']     = esc_html__( 'Share on LinkedIn', 'shopbuilder' );
					$link['social_network'] = 'LinkedIn';
					$link['social_action']  = 'Share';
					break;
				case 'skype':
					$link['link']           = esc_url( 'https://web.skype.com/share?url=' . $link['url'] );
					$link['icon']           = '<svg id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="24px" height="24px" viewBox="0 0 24 24" enable-background="new 0 0 24 24" xml:space="preserve" class="eapps-social-share-buttons-item-icon"> <path d="M23.016,13.971c0.111-0.638,0.173-1.293,0.173-1.963 c0-6.213-5.014-11.249-11.199-11.249c-0.704,0-1.393,0.068-2.061,0.193C8.939,0.348,7.779,0,6.536,0C2.926,0,0,2.939,0,6.565 c0,1.264,0.357,2.443,0.973,3.445c-0.116,0.649-0.18,1.316-0.18,1.999c0,6.212,5.014,11.25,11.198,11.25 c0.719,0,1.419-0.071,2.099-0.201C15.075,23.656,16.229,24,17.465,24C21.074,24,24,21.061,24,17.435 C24,16.163,23.639,14.976,23.016,13.971z M12.386,19.88c-3.19,0-6.395-1.453-6.378-3.953c0.005-0.754,0.565-1.446,1.312-1.446 c1.877,0,1.86,2.803,4.85,2.803c2.098,0,2.814-1.15,2.814-1.95c0-2.894-9.068-1.12-9.068-6.563c0-2.945,2.409-4.977,6.196-4.753 c3.61,0.213,5.727,1.808,5.932,3.299c0.102,0.973-0.543,1.731-1.662,1.731c-1.633,0-1.8-2.188-4.613-2.188 c-1.269,0-2.341,0.53-2.341,1.679c0,2.402,9.014,1.008,9.014,6.295C18.441,17.882,16.012,19.88,12.386,19.88z"></path> </svg>';
					$link['attr_title']     = esc_html__( 'Share on Skype', 'shopbuilder' );
					$link['social_network'] = 'Skype';
					$link['social_action']  = 'Skype';
					break;
				case 'whatsapp':
					$link['link']           = esc_url( 'https://wa.me/?text=' . $link['title'] . ' ' . $link['url'] );
					$link['icon']           = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16"> <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/> </svg>';
					$link['attr_title']     = esc_html__( 'Share on Whatsapp', 'shopbuilder' );
					$link['social_network'] = 'Whatsapp';
					$link['social_action']  = 'Share';
					break;
				case 'reddit':
					$link['link']           = esc_url( 'https://reddit.com/submit?url=' . $link['url'] . '&title=' . $link['title'] );
					$link['icon']           = '<svg xmlns="http://www.w3.org/2000/svg" width="512" height="512" viewBox="0 0 512 512"><title>ionicons-v5_logos</title><path d="M324,256a36,36,0,1,0,36,36A36,36,0,0,0,324,256Z"/><circle cx="188" cy="292" r="36" transform="translate(-97.43 94.17) rotate(-22.5)"/><path d="M496,253.77c0-31.19-25.14-56.56-56-56.56a55.72,55.72,0,0,0-35.61,12.86c-35-23.77-80.78-38.32-129.65-41.27l22-79L363.15,103c1.9,26.48,24,47.49,50.65,47.49,28,0,50.78-23,50.78-51.21S441,48,413,48c-19.53,0-36.31,11.19-44.85,28.77l-90-17.89L247.05,168.4l-4.63.13c-50.63,2.21-98.34,16.93-134.77,41.53A55.38,55.38,0,0,0,72,197.21c-30.89,0-56,25.37-56,56.56a56.43,56.43,0,0,0,28.11,49.06,98.65,98.65,0,0,0-.89,13.34c.11,39.74,22.49,77,63,105C146.36,448.77,199.51,464,256,464s109.76-15.23,149.83-42.89c40.53-28,62.85-65.27,62.85-105.06a109.32,109.32,0,0,0-.84-13.3A56.32,56.32,0,0,0,496,253.77ZM414,75a24,24,0,1,1-24,24A24,24,0,0,1,414,75ZM42.72,253.77a29.6,29.6,0,0,1,29.42-29.71,29,29,0,0,1,13.62,3.43c-15.5,14.41-26.93,30.41-34.07,47.68A30.23,30.23,0,0,1,42.72,253.77ZM390.82,399c-35.74,24.59-83.6,38.14-134.77,38.14S157,423.61,121.29,399c-33-22.79-51.24-52.26-51.24-83A78.5,78.5,0,0,1,75,288.72c5.68-15.74,16.16-30.48,31.15-43.79a155.17,155.17,0,0,1,14.76-11.53l.3-.21,0,0,.24-.17c35.72-24.52,83.52-38,134.61-38s98.9,13.51,134.62,38l.23.17.34.25A156.57,156.57,0,0,1,406,244.92c15,13.32,25.48,28.05,31.16,43.81a85.44,85.44,0,0,1,4.31,17.67,77.29,77.29,0,0,1,.6,9.65C442.06,346.77,423.86,376.24,390.82,399Zm69.6-123.92c-7.13-17.28-18.56-33.29-34.07-47.72A29.09,29.09,0,0,1,440,224a29.59,29.59,0,0,1,29.41,29.71A30.07,30.07,0,0,1,460.42,275.1Z"/><path d="M323.23,362.22c-.25.25-25.56,26.07-67.15,26.27-42-.2-66.28-25.23-67.31-26.27h0a4.14,4.14,0,0,0-5.83,0l-13.7,13.47a4.15,4.15,0,0,0,0,5.89h0c3.4,3.4,34.7,34.23,86.78,34.45,51.94-.22,83.38-31.05,86.78-34.45h0a4.16,4.16,0,0,0,0-5.9l-13.71-13.47a4.13,4.13,0,0,0-5.81,0Z"/></svg>';
					$link['attr_title']     = esc_html__( 'Share on Reddit', 'shopbuilder' );
					$link['social_network'] = 'Reddit';
					$link['social_action']  = 'Share';
					break;
				case 'telegram':
					$link['link']           = esc_url( 'https://telegram.me/share/url?text=' . $link['title'] . '&url=' . $link['url'] );
					$link['icon']           = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telegram" viewBox="0 0 16 16"> <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.287 5.906c-.778.324-2.334.994-4.666 2.01-.378.15-.577.298-.595.442-.03.243.275.339.69.47l.175.055c.408.133.958.288 1.243.294.26.006.549-.1.868-.32 2.179-1.471 3.304-2.214 3.374-2.23.05-.012.12-.026.166.016.047.041.042.12.037.141-.03.129-1.227 1.241-1.846 1.817-.193.18-.33.307-.358.336a8.154 8.154 0 0 1-.188.186c-.38.366-.664.64.015 1.088.327.216.589.393.85.571.284.194.568.387.936.629.093.06.183.125.27.187.331.236.63.448.997.414.214-.02.435-.22.547-.82.265-1.417.786-4.486.906-5.751a1.426 1.426 0 0 0-.013-.315.337.337 0 0 0-.114-.217.526.526 0 0 0-.31-.093c-.3.005-.763.166-2.984 1.09z"/> </svg>';
					$link['attr_title']     = esc_html__( 'Share on Telegram', 'shopbuilder' );
					$link['social_network'] = 'Telegram';
					$link['social_action']  = 'Share';
					break;
			}

			$link['label']  = $type['share_text'];
			$link['target'] = '_blank';
			$link['rel']    = 'nofollow noopener noreferrer';

			$data       = '';
			$link       = apply_filters( 'rtsb/elements/share/share_link', $link, $id, $preset );
			$icon       = apply_filters( 'rtsb/elements/share/share_icon', $link['icon'], $preset );
			$target     = ! empty( $link['target'] ) ? ' target="' . esc_attr( $link['target'] ) . '" ' : '';
			$rel        = ! empty( $link['rel'] ) ? ' rel="' . esc_attr( $link['rel'] ) . '" ' : '';
			$attr_title = ! empty( $link['attr_title'] ) ? ' title="' . esc_attr( $link['attr_title'] ) . '" ' : '';
			$elements   = [];

			// Add classes.
			$css_classes = [
				'rtsb-share-btn',
				sanitize_html_class( $link['type'] ),
			];
			$css_classes = array_merge( $css_classes, explode( ' ', $link['class'] ) );
			$css_classes = array_map( 'sanitize_html_class', $css_classes );
			$css_classes = implode( ' ', array_filter( $css_classes ) );

			unset( $attr['pin-do'], $attr['action'] );

			if ( 'pinterest' === $type['share_items'] ) {
				$attr['pin-do'] = 'none';
			}

			if ( 'whatsapp' === $type['share_items'] ) {
				$attr['action'] = 'share/whatsapp/share';
			}

			$attr = apply_filters( 'rtsb/elements/share/link_data', $attr, $link, $id );

			if ( ! empty( $attr ) ) {
				foreach ( $attr as $key => $val ) {
					$data .= ' data-' . sanitize_html_class( $key ) . '="' . esc_attr( $val ) . '"';
				}
			}

			$additional_attr = apply_filters( 'rtsb/elements/share/additional_attr', [], $link, $id, $preset );

			if ( ! empty( $additional_attr ) ) {
				$attr_output = join( ' ', $additional_attr );

				if ( ! empty( $data ) ) {
					$attr_output = ' ' . $attr_output;
				}

				$data .= $attr_output;
			}

			$elements['wrapper_start'] = sprintf(
				'<li class="rtsb-share-item"><a href="%s"%s%s%s class="%s"%s>',
				! empty( $link['link'] ) ? esc_attr( $link['link'] ) : '',
				$attr_title,
				$target,
				$rel,
				$css_classes,
				$data
			);
			$elements['wrapper_end']   = '</a></li>';

			$elements['icon']       = $show_icon ? '<span class="rtsb-share-icon">' . ( ! empty( $icon ) ? $icon : null ) . '</span>' : null;
			$elements['label']      = $show_text && ! empty( $link['label'] ) ? '<span class="rtsb-share-label">' . $link['label'] . '</span>' : null;
			$elements['icon_label'] = '<span class="rtsb-share-icon-label">' . $elements['icon'] . $elements['label'] . '</span>';
			$elements               = apply_filters( 'rtsb/elements/share/output_elements', $elements, $link, $id );

			$output .= $elements['wrapper_start'] . $elements['icon_label'] . $elements['wrapper_end'];
		}

		return apply_filters( 'rtsb/elements/share/list_output', $output );
	}

	/**
	 * Social Share Platforms.
	 *
	 * @return mixed|null
	 */
	public static function is_module_active( $module ) {
		$modulelist = ModuleList::instance()->get_data();
		if ( ! empty( $modulelist[ $module ]['active'] ) ) {
			return true;
		}
	}

	/***
	 * Save Settings data
	 *
	 * @param $section_id
	 * @param $block_id
	 * @param $rawOptions
	 *
	 * @return array
	 */
	public static function set_options( $section_id = '', $block_id = '', $rawOptions = [] ) {
		$section_id = ! empty( $section_id ) ? sanitize_text_field( wp_unslash( $section_id ) ) : '';
		$block_id   = ! empty( $block_id ) ? sanitize_text_field( wp_unslash( $block_id ) ) : '';
		$rawOptions = ! empty( $rawOptions ) ? $rawOptions : [];
		$results    = [
			'status'  => true,
			'message' => '',
		];
		if ( ! $section_id || ! $block_id ) {
			$results['status']  = false;
			$results['message'] = esc_html__( 'Section , block or options may be empty', 'shopbuilder' );

			return $results;
		}
		$sections = Settings::instance()->get_sections();
		if ( empty( $sections[ $section_id ] ) || empty( $sections[ $section_id ]['list'][ $block_id ] ) ) {
			$results['status']  = false;
			$results['message'] = esc_html__( 'No section or block found with given data', 'shopbuilder' );

			return $results;
		}

		$options = DataModel::source()->get_option( $section_id, [], false );
		$changed = false;
		$fields  = [];
		if ( isset( $sections[ $section_id ]['list'][ $block_id ]['fields'] ) && ! empty( $sections[ $section_id ]['list'][ $block_id ]['fields'] ) ) {
			$fields = $sections[ $section_id ]['list'][ $block_id ]['fields'];
		}

		if ( empty( $fields ) ) {
			if ( isset( $rawOptions['active'] ) ) {
				$changed                        = true;
				$options[ $block_id ]['active'] = 'on' === $rawOptions['active'] ? 'on' : '';
			}
		} else {
			foreach ( $rawOptions as $raw_option_key => $raw_value ) {
				if ( $raw_option_key === 'active' ) {
					$changed                        = true;
					$options[ $block_id ]['active'] = $sections[ $section_id ]['list'][ $block_id ]['active'] = 'on' === $raw_value ? 'on' : '';
				} else {
					if ( isset( $fields[ $raw_option_key ] ) ) {
						$field = $fields[ $raw_option_key ];
						if ( $field['type'] === 'switch' ) {
							$value = 'on' === $raw_value ? 'on' : '';
						} elseif ( 'repeaters' === $field['type'] ) {
							$the_value = [];
							$rep_title = [];
							if ( ! empty( $raw_value ) && is_array( $raw_value ) ) {
								foreach ( $raw_value as $key => $value ) {
									$the_value_decoded = json_decode( stripslashes_deep( $value ) );
									$cr                = $the_value_decoded->title ?? '';
									if ( in_array( $cr, $rep_title, true ) ) {
										continue;
									}
									$rep_title[] = $cr;
									$the_value[] = $the_value_decoded;
								}
							} elseif ( ! empty( $raw_value ) && is_string( $raw_value ) ) {
								$the_value = $raw_value;
							}
							$value = wp_json_encode( $the_value );
						} elseif ( in_array( $field['type'], [ 'product_addons_special_settings', 'checkout_fields' ] ) ) {
							$manual_field_value = [];
							if ( is_array( $raw_value ) ) {
								foreach ( $raw_value as $key => $value ) {
									$manual_field_value[] = json_decode( stripslashes( $value ), true );
								}
								$value = wp_json_encode( $manual_field_value );
							} elseif ( is_string( $raw_value ) ) {
								$value = $raw_value;
							}
						} else {
							if ( ! empty( $field['multiple'] ) || in_array( $field['type'], [ 'checkbox', 'search_and_multi_select' ] ) ) {
								if ( isset( $raw_value ) && is_array( $raw_value ) ) {
									if ( ! empty( $field['sanitize_fn'] ) && is_callable( $field['sanitize_fn'] ) ) {
										$value = array_map( $field['sanitize_fn'], $raw_value );
									} else {
										$value = array_map( 'sanitize_text_field', $raw_value );
									}
								} else {
									$value = [];
								}
							} else {
								if ( ! empty( $field['sanitize_fn'] ) ) {
									if ( is_callable( $field['sanitize_fn'] ) ) {
										$value = $field['sanitize_fn']( $raw_value );
									} elseif ( 'pass_all' === $field['sanitize_fn'] ) {
										$value = $raw_value;
									} else {
										$value = $field['sanitize_fn']( $raw_value );
									}
								} else {
									$value = sanitize_text_field( $raw_value );
								}
							}
						}
						$options[ $block_id ][ $raw_option_key ] = $sections[ $section_id ]['list'][ $block_id ]['fields'][ $raw_option_key ]['value'] = $value ?? null;
						$changed                                 = true;
					}
				}
			}
		}
		if ( ! $changed ) {
			$results['status']  = false;
			$results['message'] = esc_html__( 'No changes found for update', 'shopbuilder' );

			return $results;
		}

		DataModel::source()->set_option( $section_id, $options );

		$results['status']   = true;
		$results['message']  = esc_html__( 'Successfully Saved', 'shopbuilder' );
		$results['sections'] = $sections;

		return $results;
	}

	/***
	 * @param $meta_key
	 * @param $meta_value
	 *
	 * @return mixed|void
	 *
	 *
	 * public static function get_post_id_by_meta_key_and_value( $meta_key, $meta_value ) {
	 * if( ! $meta_key || ! $meta_value ){
	 * return;
	 * }
	 * global $wpdb;
	 * $prepare_guery = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '%s' and meta_value = '%d'", $meta_key, $meta_value );
	 * $the_products = $wpdb->get_col( $prepare_guery );
	 * if( count( $the_products ) > 0 ){
	 * return $the_products[0];
	 * }
	 * return;
	 * }
	 */
	public static function count_products_by_taxonomies( $terms, $taxonomy = 'product_cat' ) {
		if ( empty( $terms ) || ! is_array( $terms ) ) {
			return;
		}

		$args = [
			'limit'  => -1,
			'return' => 'ids',
		];

		if ( 'product_cat' === $taxonomy ) {
			$args['product_category_id'] = $terms;
		} else {
			$args['product_tag_id'] = $terms;
		}

		$query = new WC_Product_Query( $args );
		return ! empty( $query->get_products() ) ? count( $query->get_products() ) : 0;
	}

	/**
	 * Check if product filters widget has ajax.
	 *
	 * @param string $page Page name.
	 *
	 * @return bool
	 */
	public static function product_filters_has_ajax( $page ) {
		if ( empty( $page ) ) {
			return false;
		}

		if ( 'page' === get_post_type() ) {
			return false;
		}

		$id = BuilderFns::is_builder_preview() ? get_the_ID() : BuilderFns::builder_page_id_by_type( $page );
		if ( ! $id ) {
			return false;
		}
		$cache_key = 'product_filters_has_ajax_' . $id;
		if ( isset( self::$cache[ $cache_key ] ) ) {
			return self::$cache[ $cache_key ];
		}
		$elmap = ElementorDataMap::instance();
		$ajax  = [];

		foreach ( $elmap->get_widget_data( 'rtsb-ajax-product-filters', [], $id ) as $data ) {
			$ajax[] = isset( $data['settings']['ajax_mode'] ) ? false : true;
		}

		self::$cache[ $cache_key ] = ! empty( $ajax[0] );
		return ! empty( $ajax[0] );
	}

	/**
	 * Check if product has applied filter.
	 *
	 * @param string $page Page name.
	 *
	 * @return bool
	 */
	public static function product_has_applied_filters( $page ) {
		if ( empty( $page ) ) {
			return false;
		}

		$id    = BuilderFns::is_builder_preview() ? get_the_ID() : BuilderFns::builder_page_id_by_type( $page );
		$elmap = ElementorDataMap::instance();
		$ajax  = [];
		if ( ! $id ) {
			return false;
		}
		foreach ( $elmap->get_widget_data( 'rtsb-ajax-product-filters', [], $id ) as $data ) {
			$ajax[] = ! isset( $data['settings']['active_filter'] );

		}

		return ! empty( $ajax[0] );
	}

	/**
	 * Get WooCommerce product categories.
	 *
	 * @param string|null $search_query The search string for category names.
	 *
	 * @return array An array of product categories with 'value' and 'label' keys.
	 */
	public static function products_category_query( $search_query = null ) {
		if ( ! is_admin() ) {
			return [];
		}

		$cache_key = 'rtsb_product_categories_' . md5( serialize( $search_query ) );

		if ( isset( self::$cache[ $cache_key ] ) ) {
			return self::$cache[ $cache_key ];
		}
		$results = wp_cache_get( $cache_key, 'shopbuilder' );
		if ( ! $results ) {
			global $wpdb;
			$sql = "SELECT t.term_id, t.name
            FROM {$wpdb->terms} t
            JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
            WHERE tt.taxonomy = 'product_cat'";

			if ( $search_query ) {
				$sql         .= ' AND t.name LIKE %s';
				$prepared_sql = $wpdb->prepare( $sql, '%' . $wpdb->esc_like( $search_query ) . '%' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			} else {
				$prepared_sql = $sql; // No need for placeholders.
			}

			$results = $wpdb->get_results( $prepared_sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
			// Cache the results in the object cache for future use.
			wp_cache_set( $cache_key, $results, 'shopbuilder' ); // Adjust the expiration time as needed.
			Cache::set_data_cache_key( $cache_key );
		}

		$cats = [];
		if ( ! is_array( $results ) && ! count( $results ) ) {
			return $cats;
		}
		foreach ( $results as $row ) {
			$category_id    = $row->term_id;
			$category_title = $row->name;

			$cats[] = [
				'value' => $category_id,
				'label' => $category_title,
			];
		}

		// Cache the results in a static variable for the next call.
		self::$cache[ $cache_key ] = $cats;
		return $cats;
	}

	/**
	 * @param string $search_query Search query.
	 *
	 * @return array
	 */
	public static function get_registered_post_types( $search_query = null ) {
		// Get all registered post types.
		$registered_post_types = get_post_types(
			[
				'public'   => true,
				'_builtin' => false,
			],
			'objects'
		);

		unset( $registered_post_types['elementor_library'] );
		unset( $registered_post_types['e-landing-page'] );
		unset( $registered_post_types['rtsb_builder'] );

		$post_types = [];

		// Check if a search query is provided.
		if ( ! empty( $search_query ) ) {
			// Filter post types based on the search query.
			$registered_post_types = array_filter(
				$registered_post_types,
				function ( $post_type ) use ( $search_query ) {
					return stripos( $post_type->labels->singular_name, $search_query ) !== false;
				}
			);
			// Check if 'post' match the search query and include them if they do.
			if ( stripos( 'post', $search_query ) !== false ) {
				$post_types[] = [
					'value' => 'post',
					'label' => 'Post',
				];
			}
		} else {
			$post_types[] = [
				'value' => 'post',
				'label' => 'Post',
			];
		}

		// Convert the filtered post types to an array.
		foreach ( $registered_post_types as $post_type ) {
			$post_types[] = [
				'value' => $post_type->name,
				'label' => $post_type->labels->singular_name,
			];
		}

		return $post_types;
	}

	/**
	 * Get Post Types.
	 *
	 * @param string $search_query Search query.
	 *
	 * @return array
	 */
	public static function get_post_types( $search_query = null, $args = [] ) {

		$args = wp_parse_args(
			$args,
			[
				'post_type'      => 'any',
				'posts_per_page' => 20,
				'orderby'        => 'ID',
				'order'          => 'DESC',
				// 'suppress_filters' => false, // WPML Support, Remove Others Languages.
			]
		);

		if ( 'archive' !== $args['post_type'] ) {
			if ( $search_query ) {
				$args['s'] = $search_query;
			}
			$posts         = [];
			$query_results = get_posts( $args );
			foreach ( $query_results as $post ) {
				$posts[] = [
					'value' => $post->ID,
					'label' => $post->post_title . ' ( ID ' . $post->ID . ')',
				];
			}

			if ( $search_query ) {
				if ( strpos( '-error 404', $search_query ) ) {
					$posts[] = [
						'value' => 'error',
						'label' => __( '404 Error', 'shopbuilder' ),
					];
				}
			} else {
				if ( 'page' === $args['post_type'] ) {
					$posts[] = [
						'value' => 'error',
						'label' => __( '404 Error', 'shopbuilder' ),
					];
				}
			}
		} else {
			$posts = self::get_registered_post_types( $search_query );
		}

		return $posts;
	}

	/**
	 * Get all Elementor breakpoints.
	 *
	 * @return array
	 */
	public static function get_elementor_breakpoints() {
		return ( new \Elementor\Core\Breakpoints\Manager() )->get_breakpoints_config();
	}

	/**
	 * Render view.
	 *
	 * @param string  $viewName View name.
	 * @param array   $args View args.
	 * @param boolean $return View return.
	 * @return string|void
	 */
	public static function renderView( $viewName, $args = [], $return = false ) {
		$viewName = str_replace( '.', '/', $viewName );

		if ( ! empty( $args ) && is_array( $args ) ) {
			extract( $args );
		}

		$view_file = rtsb()->plugin_path() . '/resources/' . $viewName . '.php';

		if ( ! file_exists( $view_file ) ) {
			_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', esc_html( $view_file ) ), '1.7.0' );

			return;
		}

		if ( $return ) {
			ob_start();
			include $view_file;

			return ob_get_clean();
		} else {
			include $view_file;
		}
	}

	/**
	 * Best selling product query.
	 *
	 * @param int $minimum_sale Minimum sale/
	 * @param int $limit Total limit.
	 *
	 * @return array|mixed
	 */
	public static function best_selling_products_ids( $minimum_sale = 1, $limit = 10 ) {
		$cache_key = 'rtsb_best_selling_products_' . $minimum_sale . '_' . $limit;
		// Check if the data is in the cache.
		if ( isset( self::$cache[ $cache_key ] ) ) {
			return self::$cache[ $cache_key ];
		}
		$cached_data = get_transient( $cache_key );
		if ( $cached_data ) {
			self::$cache[ $cache_key ] = $cached_data;
			return $cached_data;
		}
		global $wpdb;
		$sql          = $wpdb->prepare(
			"
            SELECT ID
            FROM {$wpdb->posts} AS p
            LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
            WHERE p.post_type = 'product'
            AND p.post_status = 'publish'
            AND pm.meta_key = 'total_sales'
            AND pm.meta_value >= %d
            ORDER BY pm.meta_value + 0 DESC
            LIMIT %d
        ",
			$minimum_sale,
			$limit
		);
		$best_selling = $wpdb->get_col( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, 	WordPress.DB.PreparedSQL.NotPrepared
		// Cache the result for future use.
		set_transient( $cache_key, $best_selling, DAY_IN_SECONDS );
		self::$cache[ $cache_key ] = $best_selling;
		return $best_selling;
	}

	/**
	 * @param null|Object $product Product Object.
	 * @param int         $days_threshold Date String.
	 *
	 * @return bool
	 */
	public static function is_new_product( $product = null, $days_threshold = 30 ) {
		if ( is_null( $product ) ) {
			global $product;
		}
		if ( ! $product instanceof WC_Product ) {
			return false;
		}

		$product_id     = $product->get_id();
		$days_threshold = ! empty( $days_threshold ) ? $days_threshold : 30;
		$cache_key      = 'is_new_product_' . $product_id . '_' . $days_threshold;

		if ( isset( self::$cache[ $cache_key ] ) ) {
			return self::$cache[ $cache_key ];
		}

		// Get the product publish date.
		$publish_timestamp = strtotime( $product->get_date_created()->date_i18n( 'Y-m-d H:i:s' ) );

		// Calculate the difference in days.
		$days_difference = abs( time() - $publish_timestamp ) / ( 60 * 60 * 24 );

		// Check if the product is considered new.
		$is_new = $days_difference <= $days_threshold;

		// Cache the result for a day.
		self::$cache[ $cache_key ] = $is_new;

		return $is_new;
	}

	/**
	 * Checks if a feature is disabled for guest users based on the provided option.
	 *
	 * @param string $option  The option to retrieve from the item.
	 * @param mixed  $default The default value if the option is not found.
	 *
	 * @return bool
	 */
	public static function is_guest_feature_disabled( $option, $default ) {
		$disabled = self::get_option( 'general', 'guest_user', $option, $default );

		return ! is_user_logged_in() && $disabled;
	}

	/**
	 * Checks if a feature is disabled for guest users based on the provided option.
	 *
	 * @param string $option  The option to retrieve from the item.
	 * @param mixed  $default The default value if the option is not found.
	 *
	 * @return bool
	 */
	public static function woocommerce_output_all_notices() {
		if ( function_exists( 'wc_print_notices' ) ) :
			echo '<div class="rtsb-notice">';
			woocommerce_output_all_notices();
			echo '</div>';
		endif;
	}

	/**
	 * @param object $product Product.
	 * @return bool
	 */
	public static function is_visible_qty_input( $product = null ) {
		$is_visible_qty = true;
		if ( $product instanceof \WC_Product ) {
			if ( $product->is_sold_individually() ) {
				$is_visible_qty = false;
			} elseif ( $product->managing_stock() ) {
				if ( in_array( $product->get_backorders(), [ 'notify' , 'yes' ], true ) ) {
					$is_visible_qty = true;
				} elseif ( $product->get_stock_quantity() < 2 ) {
					$is_visible_qty = false;
				}
			}
		}
		return $is_visible_qty;
	}

	/**
	 * @param int         $object_id Object id.
	 * @param string      $element_type element type.
	 * @param string|null $current_lang null for current language, default for default languages.
	 * @return false|mixed|null
	 */
	public static function wpml_object_id( $object_id, $element_type, $current_lang = 'default' ) {
		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return $object_id;
		}
		if ( ! $object_id || ! $element_type ) {
			return $object_id;
		}
		if ( 'default' === $current_lang ) {
			$lang = apply_filters( 'wpml_default_language', null );
		} else {
			$lang = null;
		}
		return apply_filters( 'wpml_object_id', $object_id, $element_type, false, $lang );
	}

	/**
	 * @return string
	 */
	public static function wpml_current_language() {
		$current = apply_filters( 'wpml_current_language', null );
		$default = apply_filters( 'wpml_default_language', null );
		return $default !== $current ? $current : null;
	}

	/**
	 * Custom icons.
	 *
	 * @return string[]
	 */
	public static function get_custom_icon_names() {
		return [
			'heart-empty',
			'heart',
			'eye',
			'exchange',
			'plus',
			'minus',
			'avatar',
			'pay',
			'share',
			'clock',
			'check-alt',
			'check',
			'delete',
			'marker',
			'list',
			'list-2',
			'power',
			'cart',
			'cart-2',
			'cart-3',
			'downloads',
			'zoom',
			'user-edit',
			'grid',
			'filter',
			'billing',
			'login',
			'payment',
			'search',
			'edit',
			'coupon',
			'arrows-cw',
			'trash-empty',
		];
	}

	/**
	 * Flushes rewrite rules.
	 *
	 * @return void
	 */
	public static function maybe_flush_rewrite_rules() {
		add_action( 'wp_loaded', [ __CLASS__, 'flush_rewrite_rules' ] );
		add_action( 'shutdown', [ __CLASS__, 'flush_rewrite_rules_once_more' ] );
	}

	/**
	 * Flushes rewrite rules if needed.
	 *
	 * @return void
	 */
	public static function flush_rewrite_rules() {
		if ( get_option( 'rtsb_permalinks_need_flush' ) === 'yes' ) {
			flush_rewrite_rules();
		}

		if ( get_option( 'rtsb_permalinks_flushed' ) === 'yes' ) {
			flush_rewrite_rules();
			delete_option( 'rtsb_permalinks_flushed' );
		}
	}

	/**
	 * Flushes rewrite rules if needed for second time.
	 *
	 * @return void
	 */
	public static function flush_rewrite_rules_once_more() {
		if ( get_option( 'rtsb_permalinks_need_flush' ) === 'yes' ) {
			flush_rewrite_rules();
			update_option( 'rtsb_permalinks_flushed', 'yes' );
			delete_option( 'rtsb_permalinks_need_flush' );
		}
	}

	/**
	 * Social Share Platforms.
	 *
	 * @return mixed|null
	 */
	public static function set_repeater_options( $section_id, $block_id, $option_key, $compare_key, $compare_value, $values ) {
		// $options = self::get_options( $section_id, $block_id );
		$modules = DataModel::source()->get_option( $section_id, [], false );
		if ( empty( $modules[ $block_id ] ) ) {
			return;
		}
		$options = $modules[ $block_id ];
		if ( empty( $options[ $option_key ] ) ) {
			return;
		}
		$repeater_options = json_decode( $options[ $option_key ], true );
		// Check if options are not empty and are in array format.
		if ( ! is_array( $repeater_options ) ) {
			return;
		}
		// Use array_column to get an array of values from $compare_key.
		$column_values = array_column( $repeater_options, $compare_key );
		// Use array_search to find the index of the $compare_value.
		$index = array_search( $compare_value, $column_values, true );
		if ( false === $index ) {
			return;
		}
		$repeater_options[ $index ] = wp_parse_args( $values, $repeater_options[ $index ] );
		$options[ $option_key ]     = wp_json_encode( $repeater_options );
		$modules[ $block_id ]       = $options;
		DataModel::source()->set_option( $section_id, $modules );
	}


	/**
	 * @param array $ids products id.
	 * @return array
	 */
	public static function get_available_products_by_ids( $ids = [] ) {
		$ids = array_filter(
			$ids,
			function ( $id ) {
				return 'product' === get_post_type( $id ) && 'publish' === get_post_status( $id );
			}
		);
		return $ids;
	}

	/**
	 * Generate and cache dynamic CSS styles.
	 *
	 * @param array  $options      CSS options to apply (e.g. padding, margin).
	 * @param string $cache_key    Cache key for the CSS.
	 * @param array  $css_properties An array of CSS properties with selectors and properties.
	 * @param string $style_handle  The handle for the inline styles.
	 *
	 * @return void
	 */
	public static function dynamic_styles( $options, $cache_key, $css_properties, $style_handle = 'rtsb-frontend' ) {
		$cached_css = wp_cache_get( $cache_key, 'shopbuilder' );

		if ( false !== $cached_css ) {
			wp_add_inline_style( $style_handle, $cached_css );
			return;
		}

		$css_rules         = [];
		$grouped_css_rules = [];

		foreach ( $css_properties as $option => $details ) {
			if ( ! empty( $options[ $option ] ) ) {
				$selector = $details['selector'] ?? '';
				$property = $details['property'] ?? '';
				$unit     = $details['unit'] ?? '';
				$value    = $options[ $option ];

				if ( empty( $grouped_css_rules[ $selector ] ) ) {
					$grouped_css_rules[ $selector ] = [];
				}

				if ( is_array( $property ) ) {
					foreach ( $property as $prop ) {
						$grouped_css_rules[ $selector ][] = $prop . ': ' . $value . $unit . ' !important';
					}
				} else {
					$grouped_css_rules[ $selector ][] = $property . ': ' . $value . $unit . ' !important';
				}
			}
		}

		foreach ( $grouped_css_rules as $selector => $properties ) {
			$css_rules[] = $selector . ' {' . implode( '; ', $properties ) . '}';
		}

		$dynamic_css = implode( ' ', $css_rules );

		wp_cache_set( $cache_key, $dynamic_css, 'shopbuilder', 12 * HOUR_IN_SECONDS );
		Cache::set_data_cache_key( $cache_key );

		if ( ! empty( $dynamic_css ) ) {
			wp_add_inline_style( $style_handle, $dynamic_css );
		}
	}
}
