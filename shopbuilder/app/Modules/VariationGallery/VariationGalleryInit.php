<?php

namespace RadiusTheme\SB\Modules\VariationGallery;

use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Traits\SingletonTrait;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}
/**
 * Variation Swatches
 */
final class VariationGalleryInit {

	/**
	 * SingleTon
	 */
	use SingletonTrait;

	/**
	 * Asset Handle
	 *
	 * @var string
	 */
	private $handle = 'rtsb-variation-gallery';

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	private function __construct() {
		$already_active = Fns::check_plugin_active( 'woo-product-variation-gallery/woo-product-variation-gallery.php' );
		if ( ! $already_active ) {
			$this->handle = Fns::optimized_handle( $this->handle );
			$this->scripts();
			$this->init();
			do_action( 'rtsb/variation/gallery/init' );
		}
	}
	/**
	 * Initialize Functionality.
	 *
	 * @return void
	 */
	private function scripts() {
		add_action( 'wp_enqueue_scripts', [ $this, 'frontend_assets' ], 20 );
		add_action( 'admin_enqueue_scripts', [ $this, 'register_backend_assets' ], 1 );
	}
	/**
	 * Initialize Functionality.
	 *
	 * @return void
	 */
	private function init() {
		GalleryAdmin::instance();
		GalleryFrontEnd::instance();
	}
	/**
	 * Assets.
	 *
	 * @return void
	 */
	public function frontend_assets() {
		// Enqueue assets.
		$this->handle = Fns::enqueue_module_assets(
			$this->handle,
			'variation-gallery',
			[
				'context' => ( function_exists( 'rtsbpro' ) && rtsb()->has_pro() ) ? rtsbpro() : rtsb(),
				'version' => rtsb()->has_pro() ? RTSBPRO_VERSION : RTSB_VERSION,
			]
		);
		$this->frontend_dynamic_css();
	}

	/**
	 * @return void
	 */
	private function frontend_dynamic_css() {
		$options      = GalleryFns::get_options();
		$dynamic_css  = ':root{';
		$dynamic_css .= '}';
		if ( ! empty( $dynamic_css ) ) {
			wp_add_inline_style( $this->handle, $dynamic_css );
		}
	}
	/**
	 * Assets.
	 *
	 * @return void
	 */
	public function register_backend_assets() {
		global $post;
		$current_screen = get_current_screen();
		$screen_id      = $current_screen ? $current_screen->id : '';
        // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		if ( in_array( 'sitepress-multilingual-cms/sitepress.php', get_option( 'active_plugins' ) ) ) {
			$ajaxurl = admin_url( 'admin-ajax.php?lang=' . ICL_LANGUAGE_CODE );
		} else {
			$ajaxurl = admin_url( 'admin-ajax.php' );
		}
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ( 'product' === sanitize_text_field( wp_unslash( $_GET['post_type'] ?? '' ) ) && isset( $_GET['taxonomy'] ) ) || 'product' === $screen_id ) {
			wp_register_style( 'rtsb-variation-gallery-admin', rtsb()->get_assets_uri( 'css/backend/variation-gallery-admin.css' ), '', '1.0' );
			wp_register_script( 'rtsb-variation-gallery-admin', rtsb()->get_assets_uri( 'js/backend/variation-gallery-admin.js' ), '', '1.0', true );
			wp_enqueue_script( 'rtsb-variation-gallery-admin' );
			wp_enqueue_style( 'rtsb-variation-gallery-admin' );
			wp_localize_script(
				'rtsb-variation-gallery-admin',
				'rtsbVgParams',
				[
					'ajaxurl'      => esc_url( $ajaxurl ),
					'media_title'  => esc_html__( 'Choose an Image', 'shopbuilder' ),
					'button_title' => esc_html__( 'Use Image', 'shopbuilder' ),
					'add_media'    => esc_html__( 'Add Media', 'shopbuilder' ),
					'add_video'    => esc_html__( 'Add Video', 'shopbuilder' ),
					'sure_txt'     => esc_html__( 'Are you sure to delete?', 'shopbuilder' ),
				]
			);
		}
	}
}
