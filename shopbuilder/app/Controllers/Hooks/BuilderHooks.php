<?php
/**
 * Main initialization class.
 *
 * @package RadiusTheme\SB
 */

namespace RadiusTheme\SB\Controllers\Hooks;

defined( 'ABSPATH' ) || exit();

use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Helpers\BuilderFns;
use RadiusTheme\SB\Traits\SingletonTrait;

/**
 * Builder Frontend.
 */
class BuilderHooks {
	/**
	 * Builder page id.
	 *
	 * @var integer
	 */
	private $builder_page_id = 0;

	/**
	 * Page Edit By.
	 *
	 * @var string
	 */
	private $page_edit_with = '';

	/**
	 * Singleton.
	 */
	use SingletonTrait;

	/**
	 * Final constructor.
	 */
	private function __construct() {
		// Template Redirect hook run after the global query.
		add_action( 'template_redirect', [ $this, 'frontend_init' ] );
		add_filter( 'rtsb/builder/set/current/page/type', [ $this, 'builder_page_type' ] );
		add_filter( 'body_class', [ $this, 'product_page_body_class' ] );
	}

	/**
	 * Builder page Body class.
	 *
	 * @param array $classes class list.
	 * @return array.
	 */
	public function product_page_body_class( $classes ) {
		$classes[] = 'rtsb-shopbuilder-plugin rtsb_theme_' . rtsb()->current_theme;
		if ( Fns::is_woocommerce() || BuilderFns::is_builder_preview() ) {
			$classes[] = 'woocommerce-page';
		}

		if ( BuilderFns::is_product() ) {
			$classes[] = 'single-product';
		}

		if ( BuilderFns::is_shop() ) {
			$classes[] = 'woocommerce-shop';
		}

		if ( BuilderFns::is_cart() ) {
			$classes[] = 'woocommerce-cart';
			// $indexToRemove = array_search( 'woocommerce' , $classes);
			// if ($indexToRemove !== false) {
			// 	unset($classes[$indexToRemove]);
			// }
		} else{
			$classes[] = 'woocommerce';
		}

		if ( BuilderFns::is_checkout() ) {
			$classes[] = 'woocommerce-checkout ';
		}

		if ( rtsb()->has_pro() ) {
			$disable_cart     = Fns::is_guest_feature_disabled( 'hide_cart_page', '' );
			$disable_checkout = Fns::is_guest_feature_disabled( 'hide_checkout_page', '' );

			if ( $disable_cart || $disable_checkout ) {
				$classes[] = 'rtsb-not-visible';
			}
		}

		$hide_notification = Fns::get_option( 'general', 'notification', 'hide_notification', '' );

		if ( 'on' === $hide_notification ) {
			$classes[] = 'rtsb-notifications-hidden';
		}

		return $classes;
	}
	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function frontend_init() {
		$this->builder_page_id = BuilderFns::builder_page_id_by_page();
		$this->page_edit_with  = Fns::page_edit_with( $this->builder_page_id );
		if ( $this->builder_page_id ) {
			if ( did_action( 'elementor/loaded' ) && 'elementor' === $this->page_edit_with ) {
				$this->elementor_frontend();
			} else {
				$this->gutenberg_frontend();
			}
		}
	}
	/**
	 * Appl for gutenberg.
	 *
	 * @return void
	 */
	public function elementor_frontend() {
		add_action( 'rtsb/builder/before/header', [ $this, 'el_body_class' ] );
		add_action( 'rtsb/builder/template/main/content', [ $this, 'elementor_template_main_content' ] );
	}

	/**
	 * Appl for Elementor.
	 *
	 * @return void
	 */
	public function gutenberg_frontend() {
		add_action( 'rtsb/builder/template/main/content', [ $this, 'gutenberg_template_main_content' ] );
	}
	/**
	 * Apply For the supported builder page.
	 *
	 * @param string $type string.
	 * @return string
	 */
	public function builder_page_type( $type ) {

		if ( BuilderFns::is_shop() ) {
			$type = 'shop';
		} elseif ( BuilderFns::is_archive() ) {
			$type = 'archive';
		} elseif ( BuilderFns::is_product() ) {
			$type = 'product';
		} elseif ( BuilderFns::is_checkout() ) {
			$type = 'checkout';
			wp_enqueue_script( 'wc-checkout' );
		} elseif ( BuilderFns::is_cart() ) {
			$type = 'cart';
		}
		wp_enqueue_style( 'rtsb-frontend' );
		wp_enqueue_script( 'rtsb-public' );

		return apply_filters( 'rtsb/builder/set/current/page/type/external', $type );
	}

	/**
	 * Appl for Elementor.
	 *
	 * @return void
	 */
	public function el_body_class() {
		$page_template = get_post_meta( $this->builder_page_id, '_wp_page_template', true );
		if ( 'elementor_canvas' === $page_template ) {
			\Elementor\Plugin::$instance->frontend->add_body_class( 'elementor-template-canvas' );
		} elseif ( 'elementor_header_footer' === $page_template ) {
			\Elementor\Plugin::$instance->frontend->add_body_class( 'elementor-template-full-width' );
		}
	}

	/**
	 * Apply for Elementor.
	 *
	 * @return void
	 */
	public function elementor_template_main_content() {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo BuilderFns::get_builder_content( $this->builder_page_id );
	}
	/**
	 * Apply for Gutenberg.
	 *
	 * @return void
	 */
	public function gutenberg_template_main_content() {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$output  = '';
		$content = get_the_content( null, false, $this->builder_page_id );
		if ( has_blocks( $content ) ) {
			$blocks = parse_blocks( $content );
			foreach ( $blocks as $block ) {
				$output .= render_block( $block );
			}
		} else {
			$content = apply_filters( 'the_content', $content );
			$output  = str_replace( ']]>', ']]&gt;', $content );
		}
		echo wp_kses_post( $output );
	}
}
