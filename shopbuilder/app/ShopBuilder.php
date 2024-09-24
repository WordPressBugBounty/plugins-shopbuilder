<?php
/**
 * Main initialization class.
 *
 * @package RadiusTheme\SB
 */

namespace RadiusTheme\SB;

use RadiusTheme\SB\Controllers\CacheController;
use RadiusTheme\SB\Helpers\Migration;
use RadiusTheme\SB\Helpers\Installation;
use RadiusTheme\SB\Modules\ModuleManager;
use RadiusTheme\SB\Traits\SingletonTrait;
use RadiusTheme\SB\Controllers\Shortcodes;
use RadiusTheme\SB\Controllers\Dependencies;
use RadiusTheme\SB\Controllers\Admin\AdminInit;
use RadiusTheme\SB\Controllers\AssetsController;
use RadiusTheme\SB\Controllers\BuilderController;
use RadiusTheme\SB\Controllers\SupportController;
use RadiusTheme\SB\Controllers\Hooks\FilterHooks;
use RadiusTheme\SB\Controllers\Hooks\ActionHooks;
use RadiusTheme\SB\Controllers\Frontend\Ajax\AddToCart;
use RadiusTheme\SB\Controllers\Frontend\Ajax\AjaxLogin;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Main initialization class.
 */
final class ShopBuilder {

	/**
	 * Nonce id
	 *
	 * @var string
	 */
	public $nonceId = '__rtsb_wpnonce';

	/**
	 * Nonce Text
	 *
	 * @var string
	 */
	public $nonceText = 'rtsb_nonce';

	/**
	 * Post Type.
	 *
	 * @var string
	 */
	public $post_type;

	/**
	 * Post Type.
	 *
	 * @var string
	 */
	public $current_theme;
	/**
	 * Singleton
	 */

	/**
	 * URL for fetch API
	 *
	 * @var string
	 */
	public $BASE_API = 'https://shopbuilderwp.com/wp-json/rtsb/v1/layouts/';

	use SingletonTrait;

	/**
	 * Class Constructor
	 */
	private function __construct() {
		$this->define_constants();
		$this->post_type     = 'product';
		$this->current_theme = wp_get_theme()->get( 'Template' ) ? wp_get_theme()->get( 'Template' ) : wp_get_theme()->get( 'TextDomain' );
		add_action( 'init', [ $this, 'language' ] );
		add_action( 'plugins_loaded', [ $this, 'init' ], 15 );

		// Register Plugin Active Hook.
		register_activation_hook( RTSB_FILE, [ Installation::class, 'activate' ] );

		// Register Plugin Deactivate Hook.
		register_deactivation_hook( RTSB_FILE, [ Installation::class, 'deactivation' ] );

		SupportController::instance();

		// HPOS Declare compatibility.
		add_action(
			'before_woocommerce_init',
			function () {
				if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', RTSB_FILE, true );
				}
			}
		);
	}

	/**
	 * Constants
	 *
	 * @return void
	 */
	private function define_constants() {
		if ( ! defined( 'RTSB_ABSPATH' ) ) {
			define( 'RTSB_ABSPATH', dirname( RTSB_FILE ) . '/' );
		}

		if ( ! defined( 'RTSB_URL' ) ) {
			define( 'RTSB_URL', plugins_url( '', RTSB_FILE ) );
		}
	}


	/**
	 * Assets url generate with given assets file
	 *
	 * @param string $file File.
	 *
	 * @return string
	 */
	public function get_assets_uri( $file ) {
		$file = ltrim( $file, '/' );

		return trailingslashit( RTSB_URL . '/assets' ) . $file;
	}

	/**
	 * Get the template path.
	 *
	 * @return string
	 */
	public function get_template_path() {
		return apply_filters( 'rtsb/template/path', 'shopbuilder/' );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( RTSB_FILE ) );
	}

	/**
	 * Load Text Domain
	 */
	public function language() {
		load_plugin_textdomain( 'shopbuilder', false, dirname( plugin_basename( RTSB_FILE ) ) . '/languages/' );
	}

	/**
	 * Init
	 *
	 * @return void
	 */
	public function init() {
		if ( ! Dependencies::instance()->check() ) {
			return;
		}

		do_action( 'rtsb/before/loaded' );
		CacheController::instance();
		// Include File.
		AssetsController::instance();
		ModuleManager::instance();

		// Ajax.
		AddToCart::instance();
		AjaxLogin::instance();

		BuilderController::instance();
		FilterHooks::init_hooks();
		ActionHooks::init_hooks();

		Installation::init();
		Migration::init();

		if ( is_admin() ) {
			AdminInit::instance();
		}

		Shortcodes::instance();

		do_action( 'rtsb/after/loaded' );
	}

	/**
	 * Checks if Pro version installed
	 *
	 * @return boolean
	 */
	public function has_pro() {
		return function_exists( 'rtsbpro' );
	}

	/**
	 * PRO Version URL.
	 *
	 * @return string
	 */
	public function pro_version_link() {
		return 'https://shopbuilderwp.com/';
	}
}

