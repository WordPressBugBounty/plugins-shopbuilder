<?php
/**
 * @wordpress-plugin
 * Plugin Name:                ShopBuilder - Elementor WooCommerce Builder Addons
 * Plugin URI:                 https://shopbuilderwp.com/
 * Description:                ShopBuilder is the ultimate WooCommerce design solution for Elementor, letting you build and customize shop, product, cart, checkout, and account pages with ease while offering a wide range of features like Quick View, Wishlist, Compare, Variation Swatches, Mini Cart, Product Add-Ons, and more to create stunning online stores.
 * Version:                    3.1.0
 * Author:                     RadiusTheme
 * Author URI:                 https://radiustheme.com
 * Text Domain:                shopbuilder
 * Domain Path:                /languages
 * WC requires at least:       3.2
 * WC tested up to:            10.0
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
define( 'RTSB_VERSION', '3.1.0' );
define( 'RTSB_FILE', __FILE__ );
define( 'RTSB_PATH', plugin_dir_path( __FILE__ ) );
define( 'RTSB_ACTIVE_FILE_NAME', plugin_basename( __FILE__ ) );

/**
 * App Init.
 */
require_once RTSB_PATH . 'vendor/autoload.php';

/**
 * @return ShopBuilder
 */
function rtsb() {
	static $cached_instance;
	if ( null !== $cached_instance ) {
		return $cached_instance;
	}
	$cached_instance = ShopBuilder::instance();
	return $cached_instance;
}

rtsb();
