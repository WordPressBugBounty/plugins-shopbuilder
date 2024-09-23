<?php

namespace RadiusTheme\SB\Modules\QuickView;

use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Traits\SingletonTrait;
use WC_Product;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

class QuickViewFrontEnd {
	use SingletonTrait;

	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ] );

		// Gallery Plugin Support rtwpvg_disable_enqueue_scripts.
		add_filter( 'rtwpvg_disable_enqueue_scripts', '__return_false', 11 );

		// Template.

		add_action( 'rtsb/modules/quick_view/frontend/display', [ $this, 'button_hook' ] );
		// Add do_action( 'rtsb/modules/quick_view/frontend/display' ); for display anywhere.

		add_action( 'rtsb/modules/quick_view/print_button', [ $this, 'print_button' ] );

		// Load action for product template.
		$this->quick_view_action_template();

		// ShortCode.
		add_shortcode( 'rtsb_quick_view_button', [ $this, 'button_shortcode' ] );

		// Quick view AJAX.
		add_action( 'wp_ajax_rtsb_load_product_quick_view', [ $this, 'product_quick_view_ajax' ] );

		add_action( 'wp_ajax_nopriv_rtsb_load_product_quick_view', [ $this, 'product_quick_view_ajax' ] );

		$this->attach_button();
	}


	/**
	 * Load wc action for quick view product template
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function quick_view_action_template() {

		// Image.
		add_action( 'rtsb/wcqv/product/image', 'woocommerce_show_product_sale_flash', 10 );
		add_action( 'rtsb/wcqv/product/image', 'woocommerce_show_product_images', 20 );

		// Summary.
		add_action( 'rtsb/wcqv/product/summary', 'woocommerce_template_single_title', 5 );
		add_action( 'rtsb/wcqv/product/summary', 'woocommerce_template_single_rating', 10 );
		add_action( 'rtsb/wcqv/product/summary', 'woocommerce_template_single_price', 15 );
		add_action( 'rtsb/wcqv/product/summary', 'woocommerce_template_single_excerpt', 20 );
		add_action( 'rtsb/wcqv/product/summary', 'woocommerce_template_single_add_to_cart', 25 );
		add_action( 'rtsb/wcqv/product/summary', 'woocommerce_template_single_meta', 30 );
	}

	public function product_quick_view_ajax() {
		$product_id = isset( $_REQUEST['product_id'] ) ? absint( $_REQUEST['product_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( empty( $product_id ) || 'publish' !== get_post_status( $product_id ) ) {
			wp_send_json_error( esc_html__( 'Product Id not found', 'shopbuilder' ) );
		}

		global $sitepress;

		$lang = isset( $_REQUEST['lang'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['lang'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( defined( 'ICL_LANGUAGE_CODE' ) && $lang && isset( $sitepress ) ) {
			$sitepress->switch_lang( $lang, true );
		}

		// Set the main wp query for the product.
		wp( 'p=' . $product_id . '&post_type=product' );

		// Remove product thumbnails gallery.
		if ( defined( 'RTWPVG_VERSION' ) ) {
			remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
		}
		if ( ! defined( 'RTWPVG_VERSION' ) ) {
			//add_action( 'woocommerce_product_thumbnails', [ $this, 'woocommerce_product_thumbnails_before' ], 1 );
			//add_action( 'woocommerce_product_thumbnails', [ $this, 'woocommerce_product_thumbnails_after' ], 25 );
		}

		ob_start();
		Fns::load_template( 'quick-view/content' );
		$html = ob_get_contents();  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		ob_end_clean();

		wp_send_json_success( [ 'html' => $html ] );
	}

	/**
	 * Add the "Add to Wishlist" button. Needed to use in wp_head hook.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function attach_button() {

		$positions = apply_filters(
			'rtsb/module/quick_view/loop_btn_position',
			[

				'before_add_to_cart' => [
					'hook'     => 'woocommerce_after_shop_loop_item',
					'priority' => 7,
				],
				'after_add_to_cart'  => [
					'hook'     => 'woocommerce_after_shop_loop_item',
					'priority' => 15,
				],
				'custom'             => [
					'hook'     => Fns::get_option( 'modules', 'quick_view', 'loop_custom_hook_name', '' ),
					'priority' => Fns::get_option( 'modules', 'quick_view', 'loop_custom_hook_priority', '' ),
				],
			]
		);

		// Add the link "Add to wishlist" in the loop.

		$loop_btn_position = Fns::get_option( 'modules', 'quick_view', 'loop_btn_position', 'after_add_to_cart' );

		if ( 'shortcode' !== $loop_btn_position && isset( $positions[ $loop_btn_position ]['hook'] ) ) {
			add_action(
				$positions[ $loop_btn_position ]['hook'],
				[
					$this,
					'button_hook',
				],
				isset( $positions[ $loop_btn_position ]['priority'] ) ? $positions[ $loop_btn_position ]['priority'] : ''
			);
		}
		// TODO:: Maybe this hooks is not working. Need to Check gutenberg compatibility.
		// Add the link "Quick view" for Gutenberg blocks.
		add_filter( 'woocommerce_blocks_product_grid_item_html', [ $this, 'add_button_for_block' ], 10, 3 );
	}

	/**
	 * Print "Add to compare" button
	 *
	 * @return void
	 */
	public function woocommerce_product_thumbnails_before() {
		?>
		<div class="rtsb-gallery-images-wrapper">
		<?php
	}
	/**
	 * Print "Add to compare" button
	 *
	 * @return void
	 */
	public function woocommerce_product_thumbnails_after() {

		?>
		</div>
		<?php
	}

	/**
	 * Print "Add to compare" button
	 *
	 * @return void
	 */
	public function button_hook() {
		if ( ! apply_filters( 'rtsb/module/quick_view/show_button', true ) ) {
			return;
		}
		global $product;
		if ( $product instanceof WC_Product ) {
			do_action( 'rtsb/modules/quick_view/print_button', $product->get_id() );
		}

	}


	/**
	 * @return string|void
	 */
	public function print_button( $product_id = 0 ) {
		global $product;

		if ( ! $product instanceof WC_Product && $product_id ) {
			$product = wc_get_product( $product_id );
		}

		// if ( ! $current_product instanceof WC_Product ) {
		// return '';
		// }

		$classes     = []; // button class.
		$button_text = Fns::get_option( 'modules', 'quick_view', 'button_text', esc_html__( 'Quick View', 'shopbuilder' ) );

		$icon_html = '<i class="rtsb-icon rtsb-icon-eye"></i>';
		// get product type.
		$product_type = $product->get_type();
		$params       = [
			'classes'      => $classes,
			'product_id'   => $product->get_id(),
			'product_type' => $product_type,
			'button_text'  => $button_text,
			'left_text'    => apply_filters( 'rtsb/module/quick_view/button_left_text', '' ),
			'right_text'   => apply_filters( 'rtsb/module/quick_view/button_right_text', '' ),
		];
		// let third party developer filter options.
		$atts = apply_filters( 'rtsb/module/quick_view/button_params', $params );
		// set fragment options.
		$atts['icon_html'] = apply_filters( 'rtsb/module/quick_view/icon_html', $icon_html, $atts );

		Fns::load_template( 'quick-view/button', $atts );
	}


	/**
	 * Wishlist button Shortcode callable function
	 *
	 * @param array  $atts
	 * @param string $content
	 *
	 * @return string [HTML]
	 */
	public function button_shortcode( $atts, $content = '' ) {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return '';
		}
		ob_start();

		do_action( 'rtsb/modules/quick_view/print_button', $product->get_id() );

		return ob_get_clean();
	}

	public function enqueue() {

        wp_enqueue_style( 'elementor-icons-fa-regular' );

         wp_enqueue_style( 'elementor-icons-fa-solid' );
         wp_enqueue_style( 'elementor-icons-shared-0' );
        // wp_enqueue_style( 'elementor-frontend' );

        if ( ! class_exists('WooProductVariationGallery') && current_theme_supports( 'wc-product-gallery-slider' )  ) {
			wp_enqueue_script( 'flexslider' );
		}

		wp_enqueue_script( 'wc-add-to-cart-variation' );
		if ( version_compare( WC()->version, '3.0.0', '>=' ) ) {
			if ( current_theme_supports( 'wc-product-gallery-zoom' ) ) {
				wp_enqueue_script( 'zoom' );
			}
			if ( current_theme_supports( 'wc-product-gallery-lightbox' ) ) {
				wp_enqueue_script( 'photoswipe-ui-default' );
				wp_enqueue_style( 'photoswipe-default-skin' );
				if ( has_action( 'wp_footer', 'woocommerce_photoswipe' ) === false ) {
					add_action( 'wp_footer', 'woocommerce_photoswipe', 15 );
				}
			}
			wp_enqueue_script( 'wc-single-product' );
		}

		wp_register_style( 'rtsb-modules', rtsb()->get_assets_uri( 'modules/modules.css' ), [], RTSB_VERSION );
		wp_register_script(
			'rtsb-quick-view',
			rtsb()->get_assets_uri( 'modules/quick-view.js' ),
			[
				'jquery',
				'rtsb-public',
			],
			RTSB_VERSION,
			true
		);
        wp_enqueue_style( 'rtsb-modules' );
		wp_enqueue_script( 'rtsb-quick-view' );

		// Allow user to load custom style and scripts!
		do_action( 'rtsb/modules/quick_view/custom_scripts' );

		$params = apply_filters(
			'rtsb/module/quick_view/js_params',
			[
				'ajaxurl'    => admin_url( 'admin-ajax.php', 'relative' ),
				'lang'       => defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : '',
				'isLoggedIn' => is_user_logged_in(),
			]
		);

		wp_localize_script( 'rtsb-quick-view', 'rtsbQvParams', $params );
	}


	/**
	 * Add ATW button to Products block item
	 *
	 * @param string     $item_html HTML of the single block item.
	 * @param array      $data      Data used to render the item.
	 * @param WC_Product $product   Current product.
	 *
	 * @return string Filtered HTML.
	 */
	public function add_button_for_block( $item_html, $data, $product ) {
		// Add the link "Add to wishlist" in the loop.
		$shop_btn_position = Fns::get_option( 'modules', 'quick_view', 'loop_btn_position', 'after_add_to_cart' );
		ob_start();
		$this->print_button( $product->get_id() );
		$button = ob_get_clean();
		$parts  = [];

		preg_match( '/(<li class=".*?">)[\S|\s]*?(<a .*?>[\S|\s]*?<\/a>)([\S|\s]*?)(<\/li>)/', $item_html, $parts );

		if ( ! $parts || count( $parts ) < 5 ) {
			return $item_html;
		}

		// removes first match (entire match).
		array_shift( $parts );

		// removes empty parts.
		$parts = array_filter( $parts );

		// searches for index to cut parts array.
		switch ( $shop_btn_position ) {

			case 'before_add_to_cart':
				$index = 2;
				break;
			case 'after_add_to_cart':
				$index = 3;
				break;
			default:
				$index = 0;
				break;
		}

		// if index is found, stitch button in correct position.
		if ( $index ) {
			$first_set  = array_slice( $parts, 0, $index );
			$second_set = array_slice( $parts, $index );

			$parts = array_merge(
				$first_set,
				(array) $button,
				$second_set
			);

			// replace li classes.
			$parts[0] = preg_replace( '/class="(.*)"/', 'class="$1 rtsb-quick-view-btn-' . $shop_btn_position . '"', $parts[0] );
		}

		// join all parts together and return item.
		return implode( '', $parts );
	}
}
