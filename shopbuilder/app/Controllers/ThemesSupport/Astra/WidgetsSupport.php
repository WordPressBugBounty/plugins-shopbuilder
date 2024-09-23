<?php

/**
 * Main ProductDescription class.
 *
 * @package RadiusTheme\SB
 */

namespace RadiusTheme\SB\Controllers\ThemesSupport\Astra;

// Do not allow directly accessing this file.
use Rtwpvsp\Controllers\ShopPage;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Product Description class
 */
class WidgetsSupport {

	/**
	 * The single instance of the class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * The single instance of the class.
	 *
	 * @var object
	 */
	private $widgets;

	/**
	 * Construct function
	 */
	private function __construct() {
	}

	/**
	 * Get class instance.
	 *
	 * @return object Instance.
	 */
	public static function instance( $widgets ) {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		self::$instance->widgets = $widgets;

		return self::$instance;
	}

	/**
	 * @param $html
	 *
	 * @return string
	 */
	public function astra_flash_sale_html_remove( $html ) {
		return '';
	}

	/**
	 * Astra Wrapper
	 *
	 * @return void
	 */
	public function astra_woo_shop_thumbnail_wrap_start() {
		echo '<div class="astra-shop-thumbnail-wrap rtsb-image-wrapper">';
	}

	/**
	 * Product Loop content Modify.
	 *
	 * @return void
	 */
	public function astra_product_loop() {
		$controllers = $this->widgets->get_settings_for_display();

		if ( ! apply_filters( 'astra_woo_shop_product_structure_override', false ) ) {
			// Remove some hooks.
			remove_action( 'woocommerce_before_shop_loop_item', 'astra_woo_shop_thumbnail_wrap_start', 6 );
			remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
			remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
			remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
			remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

			// Adding some hooks.
			add_action( 'woocommerce_before_shop_loop_item', [ $this, 'astra_woo_shop_thumbnail_wrap_start' ], 6 );
			add_action( 'woocommerce_after_shop_loop_item', 'astra_woo_shop_thumbnail_wrap_end', 8 );
			add_action( 'woocommerce_after_shop_loop_item', 'astra_woo_woocommerce_shop_product_content' );
			add_action( 'astra_woo_shop_before_summary_wrap', [ $this->widgets, 'product_content_wrapper' ], 6 );
			add_action( 'astra_woo_shop_after_summary_wrap', [ $this->widgets, 'div_close' ], 6 );

			if ( empty( $controllers['show_flash_sale'] ) ) {
				if ( ! defined( 'ASTRA_EXT_VER' ) || ( defined( 'ASTRA_EXT_VER' ) && ! \Astra_Ext_Extension::is_active( 'woocommerce' ) ) ) {
					add_filter( 'astra_addon_shop_cards_buttons_html', [ $this, 'astra_flash_sale_html_remove' ] );
				}
			}

		}
		$this->widgets->apply_hooks_set_default();

		// Variation Swatch fix
		if ( class_exists( 'WooProductVariationSwatches' ) ) {
			$swatches_position = rtwpvs()->get_option( 'archive_swatches_position' );
			if ( 'before_title_and_price' == $swatches_position && class_exists( ShopPage::class ) ) {
				remove_action( 'woocommerce_before_shop_loop_item_title', [
					ShopPage::class,
					'archive_variation_swatches',
				], 35 );
				add_action( 'astra_woo_shop_title_before', [
					ShopPage::class,
					'archive_variation_swatches',
				], 35 );
			}

		}

		// Remove Action Button.
		add_filter( 'rtsb/module/wishlist/show_button', '__return_false' );
		add_filter( 'rtsb/module/quick_view/show_button', '__return_false' );
		add_filter( 'rtsb/module/compare/show_button', '__return_false' );

	}

	/**
	 * Product Content Reset.
	 *
	 * @return void
	 */
	public function astra_product_loop_reset_default() {
		$controllers = $this->widgets->get_settings_for_display();

		if ( ! apply_filters( 'astra_woo_shop_product_structure_override', false ) ) {
			// Remove Product Content Wrapper.
			remove_action( 'astra_woo_shop_before_summary_wrap', [ $this->widgets, 'product_content_wrapper' ], 6 );
			remove_action( 'astra_woo_shop_after_summary_wrap', [ $this->widgets, 'div_close' ], 6 );
			if ( empty( $controllers['show_flash_sale'] ) ) {
				if ( ! defined( 'ASTRA_EXT_VER' ) || ( defined( 'ASTRA_EXT_VER' ) && ! \Astra_Ext_Extension::is_active( 'woocommerce' ) ) ) {
					remove_filter( 'astra_addon_shop_cards_buttons_html', [ $this, 'astra_flash_sale_html_remove' ] );
				}
			}
		}
	}

	/**
	 * The function name comes from the widget base name "rtsb-products-archive". This is for theme support prefix with render.
	 *
	 * @return void
	 */
	public function render_rtsb_products_archive() {
		$this->astra_product_loop();
	}

	/**
	 * .
	 *
	 * @return void
	 */
	public function render_reset_rtsb_products_archive() {
		$this->astra_product_loop_reset_default();
	}

	/**
	 * The function name comes from the widget base name "rtsb-products-archive". This is for theme support prefix with render.
	 *
	 * @return void
	 */
	public function render_rtsb_upsells_product() {
		$this->astra_product_loop();
	}

	/**
	 * Reset Hooks.
	 *
	 * @return void
	 */
	public function render_reset_rtsb_upsells_product() {
		$this->astra_product_loop_reset_default();
	}

	/**
	 * Related Product.
	 *
	 * @return void
	 */
	public function render_rtsb_related_product() {
		$this->astra_product_loop();
	}

	/**
	 * Related Product.
	 *
	 * @return void
	 */
	public function render_reset_rtsb_related_product() {
		$this->astra_product_loop_reset_default();
	}

	/**
	 * Cross Sell Product.
	 *
	 * @return void
	 */
	public function render_rtsb_cross_sells() {
		$this->astra_product_loop();
	}

	/**
	 * Cross Sell Product.
	 *
	 * @return void
	 */
	public function render_reset_rtsb_cross_sells() {
		$this->astra_product_loop_reset_default();
	}

	/**
	 * Cross Sell Product.
	 *
	 * @return void
	 */
	public function render_rtsb_product_onsale() {
		add_filter( 'woocommerce_sale_flash', array( \Astra_Woocommerce::get_instance(), 'get_sale_flash_markup' ), 10, 3 );
	}
	/**
	 * Cross Sell Product.
	 *
	 * @return void
	 */
	public function render_rtsb_product_stock() {
		remove_filter( 'woocommerce_get_stock_html', 'astra_woo_product_in_stock', 10, 2 );
	}

	/**
	 * The function name comes from the widget base name "rtsb-products-archive". This is for theme support prefix with widget_controls.
	 *
	 * @return void
	 */
	public function widget_controls_rtsb_product_tabs() {
		add_filter( 'rtsb/elements/elementor/widgets/controls/rtsb-product-tabs', [ $this, 'product_tabs_widget_controls_support' ] );
	}

	/**
	 * Widget Field.
	 *
	 * @return array
	 */
	public function product_tabs_widget_controls_support( $fields ) {
		$fields['nav_active_border_color']['selectors']       = array_merge(
			$fields['nav_active_border_color']['selectors'],
			[
				'{{WRAPPER}} .woocommerce-tabs ul.tabs li.active:before' => 'background: {{VALUE}}',
			]
		);
		$fields['nav_active_hover_border_color']['selectors'] = array_merge(
			$fields['nav_active_hover_border_color']['selectors'],
			[
				'{{WRAPPER}} .woocommerce-tabs ul.tabs li.active:hover:before' => 'background: {{VALUE}}',
			]
		);

		return $fields;
	}

	/**
	 * The function name comes from the widget base name "rtsb-products-archive". This is for theme support prefix with "widget_controls".
	 *
	 * @return void
	 */
	public function widget_controls_rtsb_products_archive() {
		add_filter( 'rtsb/elements/elementor/widgets/controls/rtsb-products-archive', [ $this, 'product_loop_widget_controls_support' ], 11 );
	}

	/**
	 * Widget Field.
	 *
	 * @return array
	 */
	public function product_loop_widget_controls_support( $fields ) {
		if ( $this->widgets->has_pagination ) {
			$fields['prev_icon']['default'] = [
				'value'   => 'fas fa-long-arrow-alt-left',
				'library' => 'fa-solid',
			];
			$fields['next_icon']['default'] = [
				'value'   => 'fas fa-long-arrow-alt-right',
				'library' => 'fa-solid',
			];
		}

		return $fields;
	}

	/**
	 * The function name comes from the widget base name "rtsb-products-archive". This is for theme support prefix with widget_controls.
	 *
	 * @return void
	 */
	public function widget_controls_rtsb_related_product() {
		add_filter( 'rtsb/elements/elementor/widgets/controls/rtsb-related-product', [ $this, 'widget_controls_flash_sale' ] );
	}

	/**
	 * @return void
	 */
	public function widget_controls_rtsb_upsells_product() {
		add_filter( 'rtsb/elements/elementor/widgets/controls/rtsb-upsells-product', [ $this, 'widget_controls_flash_sale' ] );
	}
	/**
	 * @return void
	 */
	public function widget_controls_rtsb_cross_sells() {
		add_filter( 'rtsb/elements/elementor/widgets/controls/rtsb-cross-sells', [ $this, 'widget_controls_flash_sale' ] );
	}
	/**
	 * Widget Field.
	 *
	 * @return array
	 */
	public function widget_controls_flash_sale( $fields ) {
		$selector                                                           = '{{WRAPPER}} .products .product .ast-onsale-card';
		$fields['flash_sale_typography']['selector']                        = $selector;
		$fields['product_flash_sale_color']['selectors'][ $selector ]       = 'color: {{VALUE}};';
		$fields['flash_sale_bg_color']['selectors'][ $selector ]            = 'background-color: {{VALUE}};';
		$fields['flash_sale_badge_width']['selectors'][ $selector ]         = 'width: {{SIZE}}{{UNIT}};display:flex; justify-content: center;';
		$fields['flash_sale_badge_height']['selectors'][ $selector ]        = 'height: {{SIZE}}{{UNIT}};align-items:center;';
		$fields['flash_sale_badge_border_radius']['selectors'][ $selector ] = 'border-radius: {{SIZE}}{{UNIT}};';

		return $fields;
	}


}

