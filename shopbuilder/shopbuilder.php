<?php
/**
 * @wordpress-plugin
 * Plugin Name:                ShopBuilder - Elementor WooCommerce Builder Addons
 * Plugin URI:                 https://shopbuilderwp.com/
 * Description:                WooCommerce Page Builder for Elementor
 * Version:                    2.3.0
 * Author:                     RadiusTheme
 * Author URI:                 https://radiustheme.com
 * Text Domain:                shopbuilder
 * Domain Path:                /languages
 * WC requires at least:       3.2
 * WC tested up to:            9.3
 * Elementor tested up to:     3.24
 * Elementor Pro tested up to: 3.24
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
define( 'RTSB_VERSION', '2.3.0' );
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
