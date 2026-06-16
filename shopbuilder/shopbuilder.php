<?php
/**
 * @wordpress-plugin
 * Plugin Name:                ShopBuilder - WooCommerce Builder For Elementor
 * Plugin URI:                 https://shopbuilderwp.com/
 * Description:                ShopBuilder is the ultimate WooCommerce design solution for Elementor, letting you build and customize shop, product, cart, checkout, and account pages with ease while offering a wide range of features like Quick View, Wishlist, Compare, Variation Swatches, Mini Cart, Product Add-Ons, and more to create stunning online stores.
 * Version:                    3.3.0
 * Author:                     RadiusTheme
 * Author URI:                 https://radiustheme.com
 * Text Domain:                shopbuilder
 * Domain Path:                /languages
 * WC requires at least:       3.2
 * WC tested up to:            10.4
 * Elementor tested up to:     3.30
 * Elementor Pro tested up to: 3.30
 * License:                    GPLv3
 * License URI:                http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package RadiusTheme\SB
 */

use RadiusTheme\SB\ShopBuilder;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Define Constants.
 */
define( 'RTSB_VERSION', '3.3.0' );
define( 'RTSB_FILE', __FILE__ );
define( 'RTSB_PATH', plugin_dir_path( __FILE__ ) );
define( 'RTSB_ACTIVE_FILE_NAME', plugin_basename( __FILE__ ) );

/**
 * App Init.
 */
require_once RTSB_PATH . 'vendor/autoload.php';

/**
 * @return ShopBuilder|null
 */
function rtsb() {
	static $cached_instance;
	if ( null !== $cached_instance ) {
		return $cached_instance ?: null;
	}

	try {
		$cached_instance = ShopBuilder::instance();
	} catch ( \Throwable $e ) {
		$cached_instance = false;
		error_log( 'ShopBuilder fatal: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		add_action(
			'admin_notices',
			function () use ( $e ) {
				printf(
					'<div class="notice notice-error"><p><strong>ShopBuilder</strong> encountered an error and has been temporarily disabled. Check the error log for details.</p><p><code>%s</code></p></div>',
					esc_html( $e->getMessage() )
				);
			}
		);
		return null;
	}

	return $cached_instance;
}

rtsb();
