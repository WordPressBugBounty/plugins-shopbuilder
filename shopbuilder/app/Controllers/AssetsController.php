<?php

namespace RadiusTheme\SB\Controllers;

use RadiusTheme\SB\Helpers\BuilderFns;
use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Models\Settings;
use RadiusTheme\SB\Traits\SingletonTrait;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

class AssetsController {

	use SingletonTrait;

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Ajax URL
	 *
	 * @var string
	 */
	private static $ajaxurl;

	/**
	 * Styles.
	 *
	 * @var array
	 */
	private $styles = [];

	/**
	 * Scripts.
	 *
	 * @var array
	 */
	private $scripts = [];

	/**
	 * Class Constructor
	 */
	public function __construct() {
		$this->version = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? time() : RTSB_VERSION;

		if ( in_array( 'sitepress-multilingual-cms/sitepress.php', get_option( 'active_plugins' ) ) ) {
			self::$ajaxurl = admin_url( 'admin-ajax.php?lang=' . ICL_LANGUAGE_CODE );
		} else {
			self::$ajaxurl = admin_url( 'admin-ajax.php' );
		}

		/**
		 * Admin scripts.
		 */
		add_action( 'admin_enqueue_scripts', [ $this, 'register_backend_assets' ], 1 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_backend_scripts' ], 15 );

		/**
		 * Public scripts.
		 */
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_public_scripts' ], 15 );
		/**
		 * General scripts.
		 */
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_general_public_scripts' ], 30 );
	}

	/**
	 * Get all frontend scripts.
	 *
	 * @return void
	 */
	private function get_public_assets() {
		$this->get_public_styles()->get_public_scripts();
	}

	/**
	 * Get public styles.
	 *
	 * @return object
	 */
	private function get_public_styles() {
		$rtl_suffix     = is_rtl() ? '-rtl' : '';
		$this->styles[] = [
			'handle' => 'rtsb-fonts',
			'src'    => rtsb()->get_assets_uri( 'css/frontend/rtsb-fonts.css' ),
		];

		if ( rtsb()->has_pro() ) {
			$this->styles[] = [
				'handle' => 'rtsb-noui-slider',
				'src'    => rtsbpro()->get_assets_uri( 'vendors/nouislider/nouislider.min.css' ),
			];
		}

		$this->styles[] = [
			'handle' => 'rtsb-frontend',
			'src'    => rtsb()->get_assets_uri( 'css/frontend/frontend' . $rtl_suffix . '.css' ),
		];

		$this->styles[] = [
			'handle' => 'swiper',
			'src'    => rtsb()->get_assets_uri( 'vendor/swiper/css/swiper-bundle.min.css' ),
		];

		if ( BuilderFns::is_builder_preview() && 'elementor' == Fns::page_edit_with( get_the_ID() ) ) {
			$this->styles[] = [
				'handle' => 'photoswipe',
				'src'    => plugins_url( 'assets/css/photoswipe/photoswipe.min.css', WC_PLUGIN_FILE ),
			];

			$this->styles[] = [
				'handle' => 'photoswipe-default-skin',
				'src'    => plugins_url( 'assets/css/photoswipe/default-skin/default-skin.min.css', WC_PLUGIN_FILE ),
			];

			// Load only for elementor editor page and fix some issue.
			$this->styles[] = [
				'handle' => 'elementor-editor-style-fix',
				'src'    => rtsb()->get_assets_uri( 'css/backend/elementor-editor-style-fix.css' ),
			];
		}

		return $this;
	}

	/**
	 * Get public scripts.
	 *
	 * @return object
	 */
	private function get_public_scripts() {

		$default_swiper_path = rtsb()->get_assets_uri( 'vendor/swiper/js/swiper-bundle.min.js' );

		if ( defined( 'ELEMENTOR_ASSETS_PATH' ) ) {
			$is_swiper8_enable = get_option( 'elementor_experiment-e_swiper_latest' );

			if ( 'active' === $is_swiper8_enable || 'default' === $is_swiper8_enable ) {
				$el_swiper_path = 'lib/swiper/v8/swiper.min.js';
			} else {
				$el_swiper_path = 'lib/swiper/swiper.min.js';
			}

			$elementor_swiper_path = ELEMENTOR_ASSETS_PATH . $el_swiper_path;

			if ( file_exists( $elementor_swiper_path ) ) {
				$default_swiper_path = ELEMENTOR_ASSETS_URL . $el_swiper_path;
			}
		}

		$this->scripts[] = [
			'handle' => 'swiper',
			'src'    => esc_url( $default_swiper_path ),
			'deps'   => [ 'jquery' ],
			'footer' => true,
		];

		$this->scripts[] = [
			'handle' => 'rtsb-imagesloaded',
			'src'    => rtsb()->get_assets_uri( 'vendor/isotope/imagesloaded.pkgd.min.js' ),
			'deps'   => [ 'jquery' ],
			'footer' => true,
		];

		$this->scripts[] = [
			'handle' => 'rtsb-tipsy',
			'src'    => rtsb()->get_assets_uri( 'vendor/tipsy/tipsy.min.js' ),
			'deps'   => [ 'jquery' ],
			'footer' => true,
		];

		if ( BuilderFns::is_builder_preview() && 'elementor' == Fns::page_edit_with( get_the_ID() ) ) {
			$this->scripts[] = [
				'handle' => 'flexslider',
				'src'    => plugins_url( 'assets/js/flexslider/jquery.flexslider.js', WC_PLUGIN_FILE ),
				'deps'   => [ 'jquery' ],
				'footer' => true,
			];

			$this->scripts[] = [
				'handle' => 'photoswipe',
				'src'    => plugins_url( 'assets/js/photoswipe/photoswipe.js', WC_PLUGIN_FILE ),
				'deps'   => [ 'jquery' ],
				'footer' => true,
			];

			$this->scripts[] = [
				'handle' => 'zoom',
				'src'    => plugins_url( 'assets/js/zoom/jquery.zoom.js', WC_PLUGIN_FILE ),
				'deps'   => [ 'jquery' ],
				'footer' => true,
			];

			$this->scripts[] = [
				'handle' => 'photoswipe-ui-default',
				'src'    => plugins_url( 'assets/js/photoswipe/photoswipe-ui-default.js', WC_PLUGIN_FILE ),
				'deps'   => [ 'jquery', 'photoswipe' ],
				'footer' => true,
			];

			$this->scripts[] = [
				'handle' => 'wc-single-product',
				'src'    => plugins_url( 'assets/js/frontend/single-product.js', WC_PLUGIN_FILE ),
				'deps'   => [ 'jquery', 'flexslider', 'photoswipe', 'photoswipe-ui-default', 'zoom' ],
				'footer' => true,
			];

		}

		if ( rtsb()->has_pro() ) {
			$this->scripts[] = [
				'handle' => 'rtsb-noui-slider',
				'src'    => rtsbpro()->get_assets_uri( 'vendors/nouislider/nouislider.min.js' ),
				'deps'   => [ 'jquery' ],
				'footer' => true,
			];

			$this->scripts[] = [
				'handle' => 'rtsb-sticky-sidebar',
				'src'    => rtsbpro()->get_assets_uri( 'vendors/sticky-sidebar/sticky-sidebar.min.js' ),
				'deps'   => [ 'jquery' ],
				'footer' => true,
			];
		}

		$this->scripts[] = [
			'handle' => 'rtsb-public',
			'src'    => rtsb()->get_assets_uri( 'js/frontend/frontend.js' ),
			'deps'   => [ 'jquery', 'rtsb-imagesloaded', 'swiper' ],
			'footer' => true,
		];

		return $this;
	}

	/**
	 * Register public scripts.
	 *
	 * @return void
	 */
	public function register_public_scripts() {
		$this->get_public_assets();

		// Register public styles.
		foreach ( $this->styles as $style ) {
			wp_register_style( $style['handle'], $style['src'], '', $this->version );
		}

		// Register public scripts.
		foreach ( $this->scripts as $script ) {
			wp_register_script( $script['handle'], $script['src'], $script['deps'], $this->version, $script['footer'] );
		}
	}

	/**
	 * Enqueues public scripts.
	 *
	 * @return void
	 */
	public function enqueue_public_scripts() {
		/**
		 * Register scripts.
		 */
		$this->register_public_scripts();
		/**
		 * Enqueue scripts.
		 */
		if ( BuilderFns::is_builder_preview() && 'elementor' === Fns::page_edit_with( get_the_ID() ) ) {
			/**
			 * Styles.
			 */
			wp_enqueue_style( 'swiper' );
			wp_enqueue_style( 'photoswipe' );
			wp_enqueue_style( 'photoswipe-default-skin' );
			wp_enqueue_style( 'elementor-editor-style-fix' );
			wp_enqueue_style( 'woocommerce-general' );

			/**
			 * Scripts.
			 */
			wp_enqueue_script( 'flexslider' );
			wp_enqueue_script( 'wc-single-product' );
			wp_dequeue_script( 'rtsb-public' );
			wp_enqueue_script( 'swiper' );
			wp_enqueue_script( 'rtsb-public' );
		}

		/**
		 * Styles.
		 */
		wp_enqueue_style( 'rtsb-fonts' );
		wp_enqueue_style( 'rtsb-frontend' );

		/**
		 * Scripts.
		 */
		wp_enqueue_script( 'rtsb-imagesloaded' );
		wp_enqueue_script( 'rtsb-tipsy' );
		wp_enqueue_script( 'rtsb-public' );

		/**
		 * Localize script.
		 */
		self::localizeData();
	}

	/**
	 * Localized Data.
	 *
	 * @static
	 * @return void
	 */
	public static function localizeData() {
		wp_localize_script(
			'rtsb-public',
			'rtsbPublicParams',
			[
				'ajaxUrl'              => esc_url( self::$ajaxurl ),
				'homeurl'              => home_url(),
				'wcCartUrl'            => wc_get_cart_url(),
				'addedToCartText'      => esc_html__( 'Product Added', 'shopbuilder' ),
				'singleCartToastrText' => esc_html__( 'Successfully Added', 'shopbuilder' ),
				'singleCartBtnText'    => apply_filters( 'rtsb/global/single_add_to_cart_success', esc_html__( 'Added to Cart', 'shopbuilder' ) ),
				'browseCartText'       => esc_html__( 'Browse Cart', 'shopbuilder' ),
				'noProductsText'       => apply_filters( 'rtsb/global/no_products_text', esc_html__( 'No more products to load', 'shopbuilder' ) ),
				'notice'               => [
					'position' => Fns::get_option( 'general', 'notification', 'notification_position', 'center-center' ),
				],
				rtsb()->nonceId        => wp_create_nonce( rtsb()->nonceText ),
			]
		);
	}

	/**
	 * Registers Admin scripts.
	 *
	 * @return void
	 */
	public function register_backend_assets() {
		/**
		 * Styles.
		 */
		wp_register_style( 'rtsb-admin-app', rtsb()->get_assets_uri( 'css/backend/admin-settings.css' ), '', $this->version );
		wp_register_style( 'rtsb-fonts', rtsb()->get_assets_uri( 'css/frontend/rtsb-fonts.css' ), '', $this->version );

		$current_screen = get_current_screen();

		if ( 'edit-rtsb_builder' === $current_screen->id ) {
			if ( ! function_exists( 'woocommerce_get_asset_url' ) ) {
				include_once WC_ABSPATH . 'includes/wc-template-functions.php';
			}

			wp_deregister_style( 'select2' );
			wp_register_style( 'select2', plugins_url( 'assets/css/select2.css', WC_PLUGIN_FILE ) ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		}
		wp_register_style( 'rtsb-templatebuilder', rtsb()->get_assets_uri( 'css/backend/template-builder.css' ), '', $this->version );

		/**
		 * Scripts.
		 */
		wp_register_script( 'rtsb-admin-app', rtsb()->get_assets_uri( 'js/backend/admin-settings.js' ), '', $this->version, true );
		wp_register_script( 'rtsb-templatebuilder', rtsb()->get_assets_uri( 'js/backend/template-builder.js' ), '', $this->version, true );
	}

	/**
	 * Enqueues admin scripts.
	 *
	 * @param string $hook Hooks.
	 *
	 * @return void
	 */
	public function enqueue_backend_scripts( $hook ) {
		ob_start(); ?>
			#adminmenu .toplevel_page_rtsb .wp-menu-image img {
				width: auto;
				height: 22px;
				padding: 4px 0;
			}
			.post-type-rtsb_builder li#wp-admin-bar-WPML_ALS_all,
			.post-type-rtsb_builder li.language_all{
				display: none;
			}

		<?php
		$admin_global_style = ob_get_clean();
		// Speed Optimization.
		wp_add_inline_style( 'admin-menu', $admin_global_style );
		// wp_enqueue_style( 'rtsb-admin-global' );

		global $pagenow;

		$whitelisted_pages = [ 'rtsb-settings', 'rtsb-get-help', 'rtsb-themes' ];
		$current_page      = ! empty( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( 'admin.php' === $pagenow && ! empty( $_GET['page'] ) && in_array( $current_page, $whitelisted_pages, true ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			/**
			 * Styles.
			 */
			wp_enqueue_style( 'rtsb-admin-app' );
			wp_enqueue_style( 'rtsb-fonts' );
		}

		if ( 'admin.php' === $pagenow && ! empty( $_GET['page'] ) && 'rtsb-settings' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			// Elementor Console Error Fixed For "rtsb-settings" Page.
			wp_dequeue_script( 'elementor-admin-top-bar' );
			wp_dequeue_script( 'elementor-common' );
			wp_dequeue_script( 'elementor-dev-tools' );
			wp_dequeue_script( 'elementor-web-cli' );
			wp_dequeue_script( 'elementor-import-export-admin' );
			wp_dequeue_script( 'elementor-app-loader' );
			wp_dequeue_script( 'elementor-admin-modules' );
			wp_dequeue_script( 'elementor-admin' );

			wp_dequeue_style( 'elementor-admin-top-bar' );
			wp_dequeue_style( 'elementor-admin' );
			wp_dequeue_style( 'e-theme-ui-light' );
			wp_dequeue_style( 'elementor-common' );

			/**
			 * Scripts.
			 */
			wp_enqueue_script( 'updates' );
			wp_enqueue_script( 'rtsb-admin-app' );
			wp_localize_script(
				'rtsb-admin-app',
				'rtsbParams',
				[
					'ajaxurl'     => esc_url( self::$ajaxurl ),
					'homeurl'     => home_url(),
					'adminLogo'   => rtsb()->get_assets_uri( 'images/icon/ShopBuilder-Logo.svg' ),
					'restApiUrl'  => esc_url_raw( rest_url() ),
					'rest_nonce'  => wp_create_nonce( 'wp_rest' ),
					'nonce'       => wp_create_nonce( rtsb()->nonceText ),
					'pages'       => Fns::get_pages(),
					'hasPro'      => rtsb()->has_pro() ? 'yes' : 'no',
					'updateRates' => esc_html__( 'Update All Rates', 'shopbuilder' ),
					'sections'    => Settings::instance()->get_sections(),
					'userRoles'   => Fns::get_all_user_roles(),
				]
			);
		} else {
			$current_screen = get_current_screen();

			if ( 'edit-rtsb_builder' === $current_screen->id ) {
				if ( ! function_exists( 'woocommerce_get_asset_url' ) ) {
					include_once WC_ABSPATH . 'includes/wc-template-functions.php';
				}
				/**
				 * Styles.
				 */
				wp_enqueue_style( 'rtsb-templatebuilder' );

				wp_enqueue_style( 'select2', plugins_url( 'assets/css/select2.css', WC_PLUGIN_FILE ) ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion

				wp_enqueue_script( 'selectWoo' );
				/**
				 * Scripts.
				 */
				wp_enqueue_script( 'rtsb-templatebuilder' );

				wp_localize_script(
					'rtsb-templatebuilder',
					'rtsbParams',
					[
						'ajaxurl'       => esc_url( self::$ajaxurl ),
						'homeurl'       => home_url(),
						rtsb()->nonceId => wp_create_nonce( rtsb()->nonceText ),
						'hasPro'        => rtsb()->has_pro() ? 'yes' : 'no',
					]
				);
			}
		}
	}

	/**
	 * Enqueues general public scripts.
	 *
	 * @return void
	 */
	public function enqueue_general_public_scripts() {
		$notification_color        = Fns::get_option( 'general', 'notification', 'notification_color', '#004BFF' );
		$notification_bgcolor      = Fns::get_option( 'general', 'notification', 'notification_bg', '#F5F8FF' );
		$notification_button_color = Fns::get_option( 'general', 'notification', 'notification_btn_color', '#0039C0' );

		$dynamic_css = '';

		if ( ! empty( $notification_color ) ) {
			$dynamic_css .= ".rtsb-shopbuilder-plugin #toast-container .toast-success{color:{$notification_color}}";
			$dynamic_css .= ".rtsb-shopbuilder-plugin #toast-container .toast-success:before{background-color:{$notification_color}}";
		}

		if ( ! empty( $notification_bgcolor ) ) {
			$dynamic_css .= ".rtsb-shopbuilder-plugin #toast-container .toast-success{background-color:{$notification_bgcolor}}";
		}

		if ( ! empty( $notification_button_color ) ) {
			$dynamic_css .= ".rtsb-shopbuilder-plugin #toast-container .toast-success a{color:{$notification_button_color}}";
		}

		if ( ! empty( $dynamic_css ) ) {
			wp_add_inline_style( 'rtsb-frontend', $dynamic_css );
		}
	}
}
