<?php

namespace RadiusTheme\SB\Modules\VariationGallery;

use RadiusTheme\SB\Helpers\BuilderFns;
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
		add_filter( 'rtsb/optimizer/scripts/deps', [ $this, 'extend_shared_dependencies' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'frontend_assets' ], 99 );
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
	 * Shared dependencies.
	 *
	 * @param array $deps Dependencies.
	 *
	 * @return array
	 */
	public function extend_shared_dependencies( $deps ) {
		if ( is_product() || BuilderFns::is_product() ) {
			$deps[] = 'swiper';
		}
		return $deps;
	}

	/**
	 * Assets.
	 *
	 * @return void
	 */
	public function frontend_assets() {
		wp_dequeue_script( 'flexslider' );
		// Enqueue assets.
		$this->handle = Fns::enqueue_module_assets(
			$this->handle,
			'variation-gallery',
			[
				'context' => ( function_exists( 'rtsbpro' ) && rtsb()->has_pro() ) ? rtsbpro() : rtsb(),
				'version' => rtsb()->has_pro() ? RTSBPRO_VERSION : RTSB_VERSION,
				'type'    => 'css',
			]
		);
		$this->handle = Fns::enqueue_module_assets(
			$this->handle,
			'variation-gallery',
			[
				'context' => rtsb(),
				'version' => RTSB_VERSION,
				'type'    => 'js',
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
		$alignment    = 'on' === ( $options['main_slider_alignment'] ?? '' ) ? 'center' : 'flex-start';
		$col          = GalleryFns::get_options( 'thumbnails_columns', 3 );
		$dynamic_css .= '--vg-main-slider-v-alignment:' . $alignment . ';';
		$dynamic_css .= '--vg-grid-column:' . absint( $col ) . ';';
		$dynamic_css .= '--vg-main-slider-border-color:' . ( ! empty( $options['main_image_border_color'] ) ? $options['main_image_border_color'] : 'transparent' ) . ';';
		$dynamic_css .= '--vg-thumb-border-color:' . ( ! empty( $options['thumbnail_item_border_color'] ) ? $options['thumbnail_item_border_color'] : '#eee' ) . ';';
		$dynamic_css .= '--vg-thumb-item-inner-padding:' . ( ! empty( $options['thumbnail_item_inner_padding'] ) ? $options['thumbnail_item_inner_padding'] : '8' ) . 'px;';
		$dynamic_css .= '--vg-thumb-border-radius:' . ( ! empty( $options['thumbnail_item_border_radius'] ) ? absint( $options['thumbnail_item_border_radius'] ) : '3' ) . 'px;';
		$dynamic_css .= '--vg-thumb-gap:' . ( ! empty( $options['thumbnails_gap'] ) ? absint( $options['thumbnails_gap'] ) : '10' ) . 'px;';
		if ( ! empty( $options['main_image_section_width'] ) && absint( $options['image_section_height'] ) ) {
			$dynamic_css .= '--vg-image-slider-height:' . absint( $options['image_section_height'] ) . 'px;';
		}
		if ( ! empty( $options['thumb_image_section_width'] ) && absint( $options['thumb_image_section_width'] ) ) {
			$dynamic_css .= '--vg-thumb-slider-width:' . absint( $options['thumb_image_section_width'] ) . 'px;';
		}
		$dynamic_css .= '}';
		if ( ! empty( $dynamic_css ) ) {
			$dynamic_css = apply_filters( 'rtsb/variation/gallery/dynamic/css', $dynamic_css, $options );
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
