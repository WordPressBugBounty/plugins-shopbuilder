<?php
/**
 * Shortcodes Class.
 *
 * This class contains all the Shortcodes.
 *
 * @package RadiusTheme\SB
 */

namespace RadiusTheme\SB\Helpers;

use RadiusTheme\SB\Traits\SingletonTrait;
use W3TC\Dispatcher;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * CacheController Class.
 */
class Cache {

	/**
	 * Clear the template cache.
	 *
	 * @since 4.3.0
	 */
	public static function clear_all_cache() {
		self::clear_data_cache();
		self::clear_template_cache();
		self::clear_plugins_cache();
		self::clear_transient_cache();
	}
	/**
	 * Clear the template cache.
	 *
	 * @since 4.3.0
	 */
	public static function clear_plugins_cache() {
		// Clear W3 Total Cache.
		if ( function_exists( 'w3tc_flush_all' ) ) {
			w3tc_flush_all();
		}
		// Clear WP Super Cache.
		if ( function_exists( 'wp_cache_clear_cache' ) ) {
			wp_cache_clear_cache();
		}
		// Clear WP Rocket cache.
		if ( function_exists( 'rocket_clean_domain' ) ) {
			rocket_clean_domain();
		}
		if ( method_exists( 'LiteSpeed_Cache_API', 'purge_all' ) ) {
			\LiteSpeed_Cache_API::purge_all();
		}
		if ( class_exists( 'Endurance_Page_Cache' ) ) {
			$epc = new \Endurance_Page_Cache();
			$epc->purge_all();
		}
		if ( class_exists( 'SG_CachePress_Supercacher' ) && method_exists( 'SG_CachePress_Supercacher', 'purge_cache' ) ) {
			\SG_CachePress_Supercacher::purge_cache( true );
		}
		if ( class_exists( 'SiteGround_Optimizer\Supercacher\Supercacher' ) ) {
			\SiteGround_Optimizer\Supercacher\Supercacher::purge_cache();
		}
		if ( isset( $GLOBALS['wp_fastest_cache'] ) && method_exists( $GLOBALS['wp_fastest_cache'], 'deleteCache' ) ) {
			$GLOBALS['wp_fastest_cache']->deleteCache( true );
		}
		if ( is_callable( [ 'Swift_Performance_Cache', 'clear_all_cache' ] ) ) {
			\Swift_Performance_Cache::clear_all_cache();
		}
		if ( is_callable( [ 'Hummingbird\WP_Hummingbird', 'flush_cache' ] ) ) {
			\Hummingbird\WP_Hummingbird::flush_cache( true, false );
		}
		if ( class_exists( 'WP_Optimize' ) ) {
			\WP_Optimize()->get_page_cache()->purge();
		}

		// Purge WP Engine.
		if ( class_exists( 'WpeCommon' ) ) {
			if ( method_exists( 'WpeCommon', 'purge_memcached' ) ) {
				\WpeCommon::purge_memcached();
			}
			if ( method_exists( 'WpeCommon', 'clear_maxcdn_cache' ) ) {
				\WpeCommon::clear_maxcdn_cache();
			}
			if ( method_exists( 'WpeCommon', 'purge_varnish_cache' ) ) {
				\WpeCommon::purge_varnish_cache();
			}
		}
		// Purge Kinsta.
		global $kinsta_cache;
		if ( isset( $kinsta_cache ) && class_exists( '\\Kinsta\\CDN_Enabler' ) ) {
			if ( ! empty( $kinsta_cache->kinsta_cache_purge ) && is_callable( [ $kinsta_cache->kinsta_cache_purge, 'purge_complete_caches' ] ) ) {
				$kinsta_cache->kinsta_cache_purge->purge_complete_caches();
			}
		}
		// Purge Pagely.
		if ( class_exists( 'PagelyCachePurge' ) ) {
			$purge_pagely = new \PagelyCachePurge();
			if ( is_callable( [ $purge_pagely, 'purgeAll' ] ) ) {
				$purge_pagely->purgeAll();
			}
		}
		// Purge Pressidum.
		if ( defined( 'WP_NINUKIS_WP_NAME' ) && class_exists( 'Ninukis_Plugin' ) && is_callable( [ 'Ninukis_Plugin', 'get_instance' ] ) ) {
			$purge_pressidum = \Ninukis_Plugin::get_instance();
			if ( is_callable( [ $purge_pressidum, 'purgeAllCaches' ] ) ) {
				$purge_pressidum->purgeAllCaches();
			}
		}
		// Purge Savvii.
		if ( defined( '\Savvii\CacheFlusherPlugin::NAME_DOMAINFLUSH_NOW' ) ) {
			$purge_savvii = new \Savvii\CacheFlusherPlugin();
			if ( is_callable( [ $purge_savvii, 'domainflush' ] ) ) {
				$purge_savvii->domainflush();
			}
		}
		// Purge Hyper Cache.
		if ( class_exists( 'HyperCache' ) ) {
			do_action( 'autoptimize_action_cachepurged' );
		}
		// purge cache enabler.
		if ( has_action( 'ce_clear_cache' ) ) {
			do_action( 'ce_clear_cache' );
		}
		// When plugins have a simple method, add them to the array ('Plugin Name' => 'method_name').
		$others = [
			'WP Fastest Cache'  => 'wpfc_clear_all_cache',
			'Cachify'           => 'cachify_flush_cache',
			'Comet Cache'       => [ 'comet_cache', 'clear' ],
			'SG Optimizer'      => 'sg_cachepress_purge_cache',
			'Pantheon'          => 'pantheon_wp_clear_edge_all',
			'Zen Cache'         => [ 'zencache', 'clear' ],
			'Breeze'            => [ 'Breeze_PurgeCache', 'breeze_cache_flush' ],
			'Swift Performance' => [ 'Swift_Performance_Cache', 'clear_all_cache' ],
		];
		foreach ( $others as $plugin => $method ) {
			if ( is_callable( $method ) ) {
				call_user_func( $method );
			}
		}
		// Purge Godaddy Managed WordPress Hosting (Varnish + APC).
		if ( class_exists( 'WPaaS\Plugin' ) ) {
			self::godaddy_request( 'BAN' );
		}

		wp_cache_flush();
	}


	/**
	 * Purge GoDaddy Managed WordPress Hosting (Varnish)
	 *
	 * Source: https://github.com/wp-media/wp-rocket/blob/master/inc/3rd-party/hosting/godaddy.php
	 *
	 * @param String      $method
	 * @param String|Null $url
	 */
	public static function godaddy_request( $method, $url = null ) {
		$url  = empty( $url ) ? home_url() : $url;
		$host = wp_parse_url( $url, PHP_URL_HOST );
		$url  = set_url_scheme( str_replace( $host, \WPaas\Plugin::vip(), $url ), 'http' );
		wp_cache_flush();
		update_option( 'gd_system_last_cache_flush', time() ); // purge apc.
		wp_remote_request(
			esc_url_raw( $url ),
			[
				'method'   => $method,
				'blocking' => false,
				'headers'  => [ 'Host' => $host ],
			]
		);
	}

	/**
	 * Add a template to the template cache.
	 *
	 * @since 4.3.0
	 * @param string $cache_key Object cache key.
	 * @param string $template Located template.
	 */
	public static function set_template_cache( $cache_key, $template ) {
		wp_cache_set( $cache_key, $template, 'shopbuilder' );
		$cached_templates = wp_cache_get( 'shopbuilder_cached_templates', 'shopbuilder' );
		if ( is_array( $cached_templates ) ) {
			$cached_templates[] = $cache_key;
		} else {
			$cached_templates = [ $cache_key ];
		}
		wp_cache_set( 'shopbuilder_cached_templates', $cached_templates, 'shopbuilder' );
	}
	/**
	 * Clear the template cache.
	 *
	 * @since 4.3.0
	 */
	public static function clear_template_cache() {
		$cached_templates = wp_cache_get( 'shopbuilder_cached_templates', 'shopbuilder' );
		if ( is_array( $cached_templates ) ) {
			foreach ( $cached_templates as $cache_key ) {
				wp_cache_delete( $cache_key, 'shopbuilder' );
			}
			wp_cache_delete( 'shopbuilder_cached_templates', 'shopbuilder' );
		}
	}
	/**
	 * Added data cache.
	 *
	 * @since 4.3.0
	 * @param string $cache_key Object cache key.
	 * @param string $data Located template.
	 */
	public static function set_data_cache_key( $cache_key ) {
		$cached_data = wp_cache_get( 'shopbuilder_cached_data', 'shopbuilder' );
		if ( is_array( $cached_data ) ) {
			$cached_data[] = $cache_key;
		} else {
			$cached_data = [ $cache_key ];
		}
		wp_cache_set( 'shopbuilder_cached_data', $cached_data, 'shopbuilder' );
	}
	/**
	 * Clear data cache.
	 *
	 * @since 4.3.0
	 */
	public static function clear_data_cache() {
		$cached_templates = wp_cache_get( 'shopbuilder_cached_data', 'shopbuilder' );
		if ( is_array( $cached_templates ) ) {
			foreach ( $cached_templates as $cache_key ) {
				wp_cache_delete( $cache_key, 'shopbuilder' );
			}
			wp_cache_delete( 'shopbuilder_cached_data', 'shopbuilder' );
		}
	}


	/**
	 * Added data cache.
	 *
	 * @since 4.3.0
	 * @param string $cache_key Transient cache key.
	 */
	public static function set_transient_cache_key( $cache_key ) {
		$cached_data = get_transient( 'shopbuilder_transient_cached_data' );
		if ( is_array( $cached_data ) ) {
			$cached_data[] = $cache_key;
		} else {
			$cached_data = [ $cache_key ];
		}

		set_transient( 'shopbuilder_transient_cached_data', array_unique( $cached_data ) );
	}

	/**
	 * Clear data cache.
	 *
	 * @since 4.3.0
	 */
	public static function clear_transient_cache() {
		$cached_templates = get_transient( 'shopbuilder_transient_cached_data' );
		if ( is_array( $cached_templates ) ) {
			foreach ( $cached_templates as $cache_key ) {
				delete_transient( $cache_key );
			}
			delete_transient( 'shopbuilder_transient_cached_data' );
		}
	}
}
