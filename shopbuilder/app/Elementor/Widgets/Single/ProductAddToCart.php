<?php
/**
 * Main ProductDescription class.
 *
 * @package RadiusTheme\SB
 */

namespace RadiusTheme\SB\Elementor\Widgets\Single;

use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Abstracts\ElementorWidgetBase;
use RadiusTheme\SB\Elementor\Widgets\Controls\AddToCartSettings;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Product Description class
 */
class ProductAddToCart extends ElementorWidgetBase {
	/**
	 * Construct function
	 *
	 * @param array $data default array.
	 * @param mixed $args default arg.
	 */
	public function __construct( $data = [], $args = null ) {
		$this->rtsb_name = esc_html__( 'Product add to cart', 'shopbuilder' );
		$this->rtsb_base = 'rtsb-product-add-to-cart';
		parent::__construct( $data, $args );
	}
	/**
	 * Widget Field
	 *
	 * @return array
	 */
	public function widget_fields() {
		return AddToCartSettings::widget_fields( $this );
	}
	/**
	 * Set Widget Keyword.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return [ 'Cart' ] + parent::get_keywords();
	}
	/**
	 * Widget Field.
	 *
	 * @param array $controllers Control.
	 *
	 * @return void
	 */
	public function the_hooks( $controllers = null ) {
		if ( ! $controllers ) {
			return;
		}

		add_filter( 'rtsb/module/flash_sale_countdown/show_counter', '__return_false', 99 );
		$product        = Fns::get_product();
		$is_visible_qty = Fns::is_visible_qty_input( $product );
		if ( ! empty( $controllers['quantity_style'] ) && $is_visible_qty ) {
			if ( in_array( $controllers['quantity_style'], [ 'style-1', 'style-2' ] ) ) {
				add_action(
					'woocommerce_before_quantity_input_field',
					function () use ( $controllers ) {
						?>
					<!-- Quantity Wrapper Start -->
					<div class="rtsb-quantity-box-group rtsb-quantity-box-group-<?php echo esc_attr( $controllers['quantity_style'] ); ?>">
					<button type="button" class="rtsb-quantity-btn rtsb-quantity-minus">
						<?php
						Fns::print_html( $controllers['decrement_icon_html'], true );
						?>
					</button>
						<?php
					}
				);
				add_action(
					'woocommerce_after_quantity_input_field',
					function () use ( $controllers ) {
						?>
					<button type="button" class="rtsb-quantity-btn rtsb-quantity-plus">
						<?php
						Fns::print_html( $controllers['increment_icon_html'], true );
						?>
					</button>
					</div>
					<!-- Quantity Wrapper End -->
						<?php
					}
				);
			}
			if ( in_array( $controllers['quantity_style'], [ 'style-3', 'style-4' ] ) && $is_visible_qty ) {
				add_action(
					'woocommerce_before_quantity_input_field',
					function () use ( $controllers ) {
						$inner_border = ! empty( $controllers['show_inner_border'] ) ? 'show-inner-border' : '';
						?>
					<!-- Quantity Wrapper Start -->
					<div class="rtsb-quantity-box-group rtsb-quantity-box-group-<?php echo esc_attr( $controllers['quantity_style'] . ' ' . $inner_border ); ?>">
						<div class="rtsb-qty-btns-group">
							<button type="button" class="rtsb-quantity-btn rtsb-quantity-plus">
								<?php
								Fns::print_html( $controllers['increment_icon_html'], true );
								?>
							</button>
							<button type="button" class="rtsb-quantity-btn rtsb-quantity-minus">
								<?php
								Fns::print_html( $controllers['decrement_icon_html'], true );
								?>
							</button>
						</div>
						<?php
					}
				);
				add_action(
					'woocommerce_after_quantity_input_field',
					function () {
						?>
						</div>
						<!-- Quantity Wrapper End -->
						<?php
					}
				);
			}
		}

		if ( $this->is_edit_mode() ) {
			if ( class_exists( Rtwpvs\Controllers\Hooks::class ) ) {
				remove_filter( 'woocommerce_ajax_variationX_threshold', [ Rtwpvs\Controllers\Hooks::class, 'ajax_variation_threshold' ], 99 );
			}

			add_filter(
				'woocommerce_ajax_variation_threshold',
				function () {
					return 1;
				},
				99
			);

		}
	}
	/**
	 * Render Function
	 *
	 * @return void
	 */
	protected function render() {
		global $product;
		$_product                           = $product;
		$product                            = Fns::get_product();
		$controllers                        = $this->get_settings_for_display();
		$controllers['increment_icon_html'] = Fns::icons_manager( $controllers['increment_icon'] );
		$controllers['decrement_icon_html'] = Fns::icons_manager( $controllers['decrement_icon'] );
		$controllers['cart_icon_html']      = Fns::icons_manager( $controllers['cart_icon'] );
		$add_to_cart_visibility             = rtsb()->has_pro() && Fns::is_guest_feature_disabled( 'hide_add_to_cart', '' );
		$controllers['visibility']          = $add_to_cart_visibility ? ' rtsb-not-visible' : ' rtsb-is-visible';

		$this->the_hooks( $controllers );
		$this->theme_support();

		$data = [
			'template'     => 'elementor/single-product/add-to-cart',
			'controllers'  => $controllers,
			'product_type' => $product ? $product->get_type() : '',
		];

		Fns::load_template( $data['template'], $data );

		$this->editor_cart_icon_script();
		add_filter( 'rtsb/module/flash_sale_countdown/show_counter', '__return_true', 99 );
		$this->theme_support( 'render_reset' );

		$product = $_product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	}
}
