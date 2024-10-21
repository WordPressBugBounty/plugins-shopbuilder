<?php
/**
 * ShopBuilder Module List.
 *
 * @package  RadiusTheme\SB
 */

namespace RadiusTheme\SB\Models;

use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Models\Base\ListModel;
use RadiusTheme\SB\Modules\CheckoutEditor\CheckoutEditorInit;
use RadiusTheme\SB\Modules\CheckoutEditor\CheckoutFns;
use RadiusTheme\SB\Traits\SingletonTrait;
use RadiusTheme\SB\Modules\Compare\Compare;
use RadiusTheme\SB\Modules\WishList\Wishlist;
use RadiusTheme\SB\Modules\Compare\CompareFns;
use RadiusTheme\SB\Modules\QuickView\QuickView;
use RadiusTheme\SB\Modules\WishList\WishlistFns;

defined( 'ABSPATH' ) || exit;

/**
 * ShopBuilder Module List.
 */
class ModuleList extends ListModel {

	use SingletonTrait;

	/**
	 * List ID.
	 *
	 * @var string
	 */
	protected $list_id = 'modules';

	/**
	 * Class constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->title       = esc_html__( 'Modules', 'shopbuilder' );
		$this->description = esc_html__( 'Here you can find the list of all the modules. You can individually enable or disable the modules. Or you can do that by one click.', 'shopbuilder' );
	}

	/**
	 * Module list.
	 *
	 * @return mixed|null
	 */
	protected function raw_list() {
		$list = [
			'quick_view'             => apply_filters(
				'rtsb/module/quick_view/options',
				[
					'id'           => 'quick_view',
					'title'        => esc_html__( 'Quick View', 'shopbuilder' ),
					'base_class'   => QuickView::class,
					'category'     => 'general',
					'active'       => 'on',
					'package'      => 'free',
					'active_field' => [
						'label' => esc_html__( 'Enable Quick View?', 'shopbuilder' ),
						'help'  => esc_html__( 'Switch on to enable quick view module.', 'shopbuilder' ),
					],
					'tabs'         => [
						'general' => [
							'title' => esc_html__( 'General', 'shopbuilder' ),
						],
						'style'   => [
							'title' => esc_html__( 'Style', 'shopbuilder' ),
						],
					],
					'fields'       => apply_filters(
						'rtsb/module/quick_view/fields',
						[
							'loop_btn_position'           => [
								'id'      => 'loop_btn_position',
								'type'    => 'select',
								'value'   => 'custom',
								'label'   => esc_html__( 'Shop Page Button Position', 'shopbuilder' ),
								'help'    => esc_html__( 'You can manage quick view button position in shop page.', 'shopbuilder' ),
								'tab'     => 'general',
								'options' => [
									'before_add_to_cart' => esc_html__( 'Before Add To Cart', 'shopbuilder' ),
									'after_add_to_cart'  => esc_html__( 'After Add To Cart', 'shopbuilder' ),
									'shortcode'          => esc_html__( 'Use Shortcode', 'shopbuilder' ),
									'custom'             => esc_html__( 'Custom Position', 'shopbuilder' ),
								],

							],
							'loop_btn_position_shortcode' => [
								'type'       => 'raw',
								'label'      => ' ',
								'html'       => sprintf(
								/* translators: 1: The shortcode.*/
									esc_html__( 'Choose where to show button in WooCommerce product\'s loop. Copy this shortcode %1$s and paste it where you want to show the button.', 'shopbuilder' ),
									'<code>[rtsb_quick_view_button]</code>'
								),
								'tab'        => 'general',
								'dependency' => [
									'rules' => [
										[
											'item'     => 'modules.quick_view.loop_btn_position',
											'value'    => 'shortcode',
											'operator' => '==',
										],
									],
								],
							],
							'loop_custom_hook_name'       => [
								'id'         => 'loop_custom_hook_name',
								'type'       => 'text',
								'label'      => esc_html__( 'Enter Hook Name', 'shopbuilder' ),
								'help'       => sprintf(
								/* translators: 1: The shortcode.*/
									esc_html__( 'Or Copy the php code %1$s and paste it in your product query where you want to show the button.', 'shopbuilder' ),
									"<br /><code>&lt;?php do_action( 'rtsb/modules/quick_view/frontend/display' ); ?&gt;</code><br />"
								),
								'tab'        => 'general',
								'dependency' => [
									'rules' => [
										[
											'item'     => 'modules.quick_view.loop_btn_position',
											'value'    => 'custom',
											'operator' => '==',
										],
									],
								],
							],
							'loop_custom_hook_priority'   => [
								'id'         => 'loop_custom_hook_priority',
								'type'       => 'number',
								'value'      => 10,
								'size'       => 'small',
								'min'        => 0,
								'max'        => 999,
								'label'      => esc_html__( 'Hook Priority', 'shopbuilder' ),
								'tab'        => 'general',
								'dependency' => [
									'rules' => [
										[
											'item'     => 'modules.quick_view.loop_btn_position',
											'value'    => 'custom',
											'operator' => '==',
										],
									],
								],
							],
							'button_text'                 => [
								'id'          => 'button_text',
								'label'       => esc_html__( 'Quick View Text', 'shopbuilder' ),
								'help'        => esc_html__( 'Enter your quick view button text.', 'shopbuilder' ),
								'type'        => 'text',
								'value'       => 'Quick View',
								'placeholder' => 'Quick View',
								'tab'         => 'general',
							],

							// Module Style.
							'modal_width'                 => [
								'id'    => 'modal_width',
								'label' => esc_html__( 'Modal Width (in px)', 'shopbuilder' ),
								'help'  => esc_html__( 'Quick view button modal width. Example: 950', 'shopbuilder' ),
								'type'  => 'number',
								'value' => '',
								'tab'   => 'style',
							],

							'modal_height'                => [
								'id'    => 'modal_height',
								'label' => esc_html__( 'Modal Height', 'shopbuilder' ),
								'help'  => esc_html__( 'Quick view button modal height. Example: 450px', 'shopbuilder' ),
								'type'  => 'text',
								'value' => '',
								'tab'   => 'style',
							],

							'modal_wrapper_padding'       => [
								'id'    => 'modal_wrapper_padding',
								'label' => esc_html__( 'Modal Wrapper Padding', 'shopbuilder' ),
								'help'  => esc_html__( 'Quick view modal wrapper padding. Example 1: 20px, Example 2: 20px 20px 20px 20px', 'shopbuilder' ),
								'type'  => 'text',
								'value' => '',
								'tab'   => 'style',
							],
							'modal_bg_color'              => [
								'id'    => 'modal_bg_color',
								'label' => esc_html__( 'Modal Backgroud Color', 'shopbuilder' ),
								'help'  => esc_html__( 'Quick view modal background color. Example: #fff', 'shopbuilder' ),
								'type'  => 'color',
								'value' => '',
								'tab'   => 'style',
							],
							'modal_box_shadow_color'      => [
								'id'    => 'modal_box_shadow_color',
								'label' => esc_html__( 'Modal Box Shadow Color', 'shopbuilder' ),
								'help'  => esc_html__( 'Quick view modal box shadow. Example: #000', 'shopbuilder' ),
								'type'  => 'color',
								'value' => '',
								'tab'   => 'style',
							],

							'modal_overly_color'          => [
								'id'    => 'modal_overly_color',
								'label' => esc_html__( 'Modal Overly Color', 'shopbuilder' ),
								'help'  => esc_html__( 'Quick view modal overly. Example: #000', 'shopbuilder' ),
								'type'  => 'color',
								'value' => '',
								'tab'   => 'style',
							],

						]
					),
				]
			),
			'wishlist'               => apply_filters(
				'rtsb/module/wishlist/options',
				[
					'id'           => 'wishlist',
					'title'        => esc_html__( 'Wishlist', 'shopbuilder' ),
					'package'      => 'free',
					'active'       => 'on',
					'base_class'   => Wishlist::class,
					'category'     => 'general',
					'active_field' => [
						'label' => esc_html__( 'Enable Wishlist?', 'shopbuilder' ),
						'help'  => esc_html__( 'Switch on to enable wishlist module.', 'shopbuilder' ),
					],
					'fields'       => apply_filters(
						'rtsb/module/wishlist/fields',
						[
							'enable_login_limit'           => [
								'id'    => 'enable_login_limit',
								'type'  => 'switch',
								'label' => esc_html__( 'Limit Wishlist Use', 'shopbuilder' ),
								'help'  => esc_html__( 'Enable this option to allow only the logged-in users to use the Wishlist feature.', 'shopbuilder' ),
								'tab'   => 'general',
							],
							'hide_wishlist_non_logged_in'  => [
								'id'         => 'hide_wishlist_non_logged_in',
								'type'       => 'switch',
								'label'      => esc_html__( 'Hide Wishlist Button', 'shopbuilder' ),
								'help'       => esc_html__( 'Hide Wishlist Button For non logged-in users (guest users).', 'shopbuilder' ),
								'dependency' => [
									'rules' => [
										[
											'item'     => 'modules.wishlist.enable_login_limit',
											'value'    => 'on',
											'operator' => '==',
										],
									],
								],
								'tab'        => 'general',
							],
							'wishlist_shop_wrapper_heading' => [
								'id'    => 'wishlist_shop_wrapper_heading',
								'type'  => 'title',
								'label' => esc_html__( 'Shop Page Settings', 'shopbuilder' ),
								'tab'   => 'button',
							],
							'show_btn_on_loop'             => [
								'id'    => 'show_btn_on_loop',
								'value' => 'on',
								'type'  => 'switch',
								'label' => esc_html__( 'Show in Shop Page', 'shopbuilder' ),
								'tab'   => 'button',
							],
							'loop_btn_position'            => [
								'id'         => 'loop_btn_position',
								'type'       => 'select',
								'value'      => 'custom',
								'label'      => esc_html__( 'Shop Page Button Position', 'shopbuilder' ),
								'help'       => esc_html__( 'You can manage wishlist button position in shop page.', 'shopbuilder' ),
								'options'    => [
									'before_add_to_cart' => esc_html__( 'Before Add To Cart', 'shopbuilder' ),
									'after_add_to_cart'  => esc_html__( 'After Add To Cart', 'shopbuilder' ),
									'shortcode'          => esc_html__( 'Use Shortcode', 'shopbuilder' ),
									'custom'             => esc_html__( 'Custom Position', 'shopbuilder' ),
								],
								'dependency' => [
									'rules' => [
										[
											'item'     => 'modules.wishlist.show_btn_on_loop',
											'value'    => 'on',
											'operator' => '==',
										],
									],
								],
								'tab'        => 'button',
							],
							'loop_btn_position_shortcode'  => [
								'type'       => 'raw',
								'label'      => ' ',
								'html'       => sprintf(
								/* translators: 1: The shortcode.*/
									esc_html__( 'Choose where to show button in WooCommerce product\'s loop. Copy this shortcode %1$s and paste it where you want to show the button.', 'shopbuilder' ),
									'<code>[rtsb_wishlist_button]</code>'
								),
								'dependency' => [
									'rules' => [
										[
											'item'     => 'modules.wishlist.show_btn_on_loop',
											'value'    => 'on',
											'operator' => '==',
										],
										[
											'item'     => 'modules.wishlist.loop_btn_position',
											'value'    => 'shortcode',
											'operator' => '==',
										],
									],
								],
								'tab'        => 'button',
							],
							'loop_custom_hook_name'        => [
								'id'         => 'loop_custom_hook_name',
								'type'       => 'text',
								'label'      => esc_html__( 'Enter Hook Name', 'shopbuilder' ),
								'tab'        => 'button',
								'help'       => sprintf(
								/* translators: 1: The shortcode.*/
									esc_html__( 'Or Copy the php code %1$s and paste it in your product query where you want to show the button.', 'shopbuilder' ),
									"<br /><code>&lt;?php do_action('rtsb/modules/wishlist/frontend/display' ); ?&gt;</code><br />"
								),
								'dependency' => [
									'rules' => [
										[
											'item'     => 'modules.wishlist.show_btn_on_loop',
											'value'    => 'on',
											'operator' => '==',
										],
										[
											'item'     => 'modules.wishlist.loop_btn_position',
											'value'    => 'custom',
											'operator' => '==',
										],
									],
								],
							],
							'loop_custom_hook_priority'    => [
								'id'         => 'loop_custom_hook_priority',
								'type'       => 'number',
								'value'      => 10,
								'size'       => 'small',
								'min'        => 0,
								'max'        => 999,
								'label'      => esc_html__( 'Hook Priority', 'shopbuilder' ),
								'tab'        => 'button',
								'dependency' => [
									'rules'    => [
										[
											'item'     => 'modules.wishlist.show_btn_on_loop',
											'value'    => 'on',
											'operator' => '==',
										],
										[
											'item'     => 'modules.wishlist.loop_btn_position',
											'value'    => 'custom',
											'operator' => '==',
										],
									],
									'relation' => 'and',
								],
							],
							'wishlist_single_wrapper_heading' => [
								'id'    => 'wishlist_single_wrapper_heading',
								'type'  => 'title',
								'label' => esc_html__( 'Product Page Settings', 'shopbuilder' ),
								'tab'   => 'button',
							],
							'show_btn_product_page'        => [
								'id'    => 'show_btn_product_page',
								'value' => 'on',
								'type'  => 'switch',
								'label' => esc_html__( 'Show in Product Page', 'shopbuilder' ),
								'tab'   => 'button',
							],
							'product_btn_position'         => [
								'id'         => 'product_btn_position',
								'label'      => esc_html__( 'Product Page Button Position', 'shopbuilder' ),
								'help'       => esc_html__( 'You can manage compare button position in single product page.', 'shopbuilder' ),
								'type'       => 'select',
								'value'      => 'custom',
								'options'    => [
									'before_add_to_cart' => esc_html__( 'Before Add To Cart', 'shopbuilder' ),
									'after_add_to_cart'  => esc_html__( 'After Add To Cart', 'shopbuilder' ),
									'after_thumbnail'    => esc_html__( 'After Image', 'shopbuilder' ),
									'after_summary'      => esc_html__( 'After Summary', 'shopbuilder' ),
									'shortcode'          => esc_html__( 'Use Shortcode', 'shopbuilder' ),
									'custom'             => esc_html__( 'Custom Position', 'shopbuilder' ),
								],
								'dependency' => [
									'rules' => [
										[
											'item'     => 'modules.wishlist.show_btn_product_page',
											'value'    => 'on',
											'operator' => '==',
										],
									],
								],
								'tab'        => 'button',
							],
							'product_btn_position_shortcode' => [
								'type'       => 'raw',
								'label'      => ' ',
								'html'       => sprintf(
								/* translators: 1: The shortcode.*/
									esc_html__( 'Choose where to show button on the product page. Copy this shortcode %1$s and paste it where you want to show the button.', 'shopbuilder' ),
									'<code>[rtsb_wishlist_button]</code>'
								),
								'dependency' => [
									'rules' => [
										[
											'item'     => 'modules.wishlist.show_btn_product_page',
											'value'    => 'on',
											'operator' => '==',
										],
										[
											'item'     => 'modules.wishlist.product_btn_position',
											'value'    => 'shortcode',
											'operator' => '==',
										],
									],
								],
								'tab'        => 'button',
							],
							'product_custom_hook_name'     => [
								'id'         => 'product_custom_hook_name',
								'type'       => 'text',
								'label'      => esc_html__( 'Product Hook Name', 'shopbuilder' ),
								'tab'        => 'button',
								'help'       => sprintf(
								/* translators: 1: The shortcode.*/
									esc_html__( 'Or Copy the php code %1$s and paste it in your product query where you want to show the button.', 'shopbuilder' ),
									"<br /><code>&lt;?php do_action('rtsb/modules/wishlist/frontend/display' ); ?&gt;</code><br />"
								),
								'dependency' => [
									'rules' => [
										[
											'item'     => 'modules.wishlist.show_btn_product_page',
											'value'    => 'on',
											'operator' => '==',
										],
										[
											'item'     => 'modules.wishlist.product_btn_position',
											'value'    => 'custom',
											'operator' => '==',
										],
									],
								],
							],
							'product_custom_hook_priority' => [
								'id'         => 'product_custom_hook_priority',
								'type'       => 'number',
								'value'      => 10,
								'size'       => 'small',
								'min'        => 0,
								'max'        => 999,
								'label'      => esc_html__( 'Product Hook Priority', 'shopbuilder' ),
								'tab'        => 'button',
								'dependency' => [
									'rules'    => [
										[
											'item'     => 'modules.wishlist.show_btn_product_page',
											'value'    => 'on',
											'operator' => '==',
										],
										[
											'item'     => 'modules.wishlist.product_btn_position',
											'value'    => 'custom',
											'operator' => '==',
										],
									],
									'relation' => 'and',
								],
							],
							'button_text'                  => [
								'id'          => 'button_text',
								'label'       => esc_html__( 'Wishlist Text', 'shopbuilder' ),
								'help'        => esc_html__( 'Enter your wishlist button text.', 'shopbuilder' ),
								'type'        => 'text',
								'value'       => 'Add to Wishlist',
								'placeholder' => 'Add to Wishlist',
								'tab'         => 'button',
							],
							'notice_added_text'            => [
								'id'          => 'notice_added_text',
								'label'       => esc_html__( 'Product Added Text', 'shopbuilder' ),
								'help'        => esc_html__( 'Enter the product added text.', 'shopbuilder' ),
								'type'        => 'text',
								'value'       => esc_html__( 'Product added!', 'shopbuilder' ),
								'placeholder' => esc_html__( 'Product added!', 'shopbuilder' ),
								'tab'         => 'general',
							],
							'notice_removed_text'          => [
								'id'          => 'notice_removed_text',
								'label'       => esc_html__( 'Product Removed Text', 'shopbuilder' ),
								'help'        => esc_html__( 'Enter the product removed text.', 'shopbuilder' ),
								'type'        => 'text',
								'value'       => esc_html__( 'Product removed!', 'shopbuilder' ),
								'placeholder' => esc_html__( 'Product removed!', 'shopbuilder' ),
								'tab'         => 'general',
							],
							'browse_list_text'             => [
								'id'          => 'browse_list_text',
								'label'       => esc_html__( 'Browse Wishlist Text', 'shopbuilder' ),
								'help'        => esc_html__( 'Enter a text for the "Browse wishlist" link on the product page', 'shopbuilder' ),
								'type'        => 'text',
								'value'       => esc_html__( 'Browse Wishlist', 'shopbuilder' ),
								'placeholder' => esc_html__( 'Browse Wishlist', 'shopbuilder' ),
								'tab'         => 'general',
							],
							/*
							 * TODO:: Will Implement Later.
							'redirect_cart'                => array(
								'id'    => 'redirect_cart',
								'type'  => 'switch',
								'help'  => esc_html__( 'Redirect users to the cart page when they add a product to the cart from the wishlist page', 'shopbuilder' ),
								'label' => esc_html__( 'Redirect to Cart', 'shopbuilder' ),
								'tab'   => 'page',
							),
							'remove_after_add_to_cart'     => array(
								'value' => 'on',
								'id'    => 'remove_after_add_to_cart',
								'type'  => 'switch',
								'help'  => esc_html__( 'Remove the product from the wishlist after it has been added to the cart', 'shopbuilder' ),
								'label' => esc_html__( 'Remove if Added to Cart', 'shopbuilder' ),
								'tab'   => 'page',
							),
							*/
							'page'                         => [
								'id'      => 'page',
								'type'    => 'select',
								'label'   => esc_html__( 'Select Wishlist Page', 'shopbuilder' ),
								'help'    => sprintf(
									/* translators: 1: The shortcode.*/
									esc_html__( 'Select a page for wishlist page and make sure you add the shortcode %1$s into the page content', 'shopbuilder' ),
									'<code>[rtsb_wishlist]</code>'
								),
								'options' => Fns::get_pages(),
								'tab'     => 'page',
							],
							'page_show_fields'             => [
								'value'       => WishlistFns::instance()->get_field_ids(),
								'id'          => 'page_show_fields',
								'multiple'    => true,
								'checkbox'    => true,
								'sanitize_fn' => 'sanitize_text_field',
								'type'        => 'sortable',
								'options'     => WishlistFns::instance()->get_default_fields(),
								'label'       => esc_html__( 'Wishlist Table Fields Visibility', 'shopbuilder' ),
								'tab'         => 'page',
							],

						]
					),
					'tabs'         => [
						'general' => [
							'title' => esc_html__( 'General', 'shopbuilder' ),
						],
						'button'  => [
							'title' => esc_html__( 'Button', 'shopbuilder' ),
						],
						'page'    => [
							'title' => esc_html__( 'Page', 'shopbuilder' ),
						],
					],
				]
			),
			'compare'                => apply_filters(
				'rtsb/module/compare/options',
				[
					'id'           => 'compare',
					'base_class'   => Compare::class,
					'category'     => 'general',
					'title'        => esc_html__( 'Product Compare', 'shopbuilder' ),
					'package'      => 'free',
					'active'       => 'on',
					'active_field' => [
						'label' => esc_html__( 'Enable Product Comparison?', 'shopbuilder' ),
						'help'  => esc_html__( 'Switch on to enable product comparison module.', 'shopbuilder' ),
					],
					'fields'       => apply_filters(
						'rtsb/module/compare/fields',
						[
							'compare_shop_wrapper_heading' => [
								'id'    => 'compare_shop_wrapper_heading',
								'type'  => 'title',
								'label' => esc_html__( 'Shop Page Settings', 'shopbuilder' ),
								'tab'   => 'button',
							],
							'show_btn_on_loop'             => [
								'id'    => 'show_btn_on_loop',
								'value' => 'on',
								'type'  => 'switch',
								'label' => esc_html__( 'Show in Shop Page', 'shopbuilder' ),
								'tab'   => 'button',
							],
							'loop_btn_position'            => [
								'id'         => 'loop_btn_position',
								'type'       => 'select',
								'value'      => 'custom',
								'label'      => esc_html__( 'Shop Page Button Position', 'shopbuilder' ),
								'help'       => esc_html__( 'You can manage wishlist button position in shop page.', 'shopbuilder' ),
								'options'    => [
									'before_add_to_cart' => esc_html__( 'Before Add To Cart', 'shopbuilder' ),
									'after_add_to_cart'  => esc_html__( 'After Add To Cart', 'shopbuilder' ),
									'shortcode'          => esc_html__( 'Use Shortcode', 'shopbuilder' ),
									'custom'             => esc_html__( 'Custom Position', 'shopbuilder' ),
								],
								'dependency' => [
									'rules' => [
										[
											'item'     => 'modules.compare.show_btn_on_loop',
											'value'    => 'on',
											'operator' => '==',
										],
									],
								],
								'tab'        => 'button',
							],
							'loop_btn_position_shortcode'  => [
								'type'       => 'raw',
								'label'      => ' ',
								'html'       => sprintf(
								/* translators: 1: The shortcode.*/
									esc_html__( 'Choose where to show button in WooCommerce product\'s loop. Copy this shortcode %1$s and paste it where you want to show the button.', 'shopbuilder' ),
									'<code>[rtsb_compare_button]</code>'
								),
								'dependency' => [
									'rules' => [
										[
											'item'     => 'modules.compare.show_btn_on_loop',
											'value'    => 'on',
											'operator' => '==',
										],
										[
											'item'     => 'modules.compare.loop_btn_position',
											'value'    => 'shortcode',
											'operator' => '==',
										],
									],
								],
								'tab'        => 'button',
							],
							'loop_custom_hook_name'        => [
								'id'         => 'product_custom_hook_name',
								'type'       => 'text',
								'label'      => esc_html__( 'Enter Hook Name', 'shopbuilder' ),
								'tab'        => 'button',
								'help'       => sprintf(
								/* translators: 1: The shortcode.*/
									esc_html__( 'Or Copy the php code %1$s and paste it in your product query where you want to show the button.', 'shopbuilder' ),
									"<br /><code>&lt;?php do_action('rtsb/modules/compare/frontend/display' ); ?&gt;</code><br />"
								),
								'dependency' => [
									'rules' => [
										[
											'item'     => 'modules.compare.show_btn_on_loop',
											'value'    => 'on',
											'operator' => '==',
										],
										[
											'item'     => 'modules.compare.loop_btn_position',
											'value'    => 'custom',
											'operator' => '==',
										],
									],
								],
							],
							'loop_custom_hook_priority'    => [
								'id'         => 'product_custom_hook_priority',
								'type'       => 'number',
								'value'      => 10,
								'size'       => 'small',
								'min'        => 0,
								'max'        => 999,
								'label'      => esc_html__( 'Hook Priority', 'shopbuilder' ),
								'tab'        => 'button',
								'dependency' => [
									'rules'    => [
										[
											'item'     => 'modules.compare.show_btn_on_loop',
											'value'    => 'on',
											'operator' => '==',
										],
										[
											'item'     => 'modules.compare.loop_btn_position',
											'value'    => 'custom',
											'operator' => '==',
										],
									],
									'relation' => 'and',
								],
							],
							'compare_single_wrapper_heading' => [
								'id'    => 'compare_single_wrapper_heading',
								'type'  => 'title',
								'label' => esc_html__( 'Product Page Settings', 'shopbuilder' ),
								'tab'   => 'button',
							],
							'show_btn_product_page'        => [
								'id'    => 'show_btn_product_page',
								'type'  => 'switch',
								'value' => 'on',
								'label' => esc_html__( 'Show Button in Product Page', 'shopbuilder' ),
								'tab'   => 'button',
							],
							'product_btn_position'         => [
								'id'         => 'product_btn_position',
								'label'      => esc_html__( 'Product Page Button Position', 'shopbuilder' ),
								'help'       => esc_html__( 'You can manage compare button position in single product page.', 'shopbuilder' ),
								'type'       => 'select',
								'value'      => 'custom',
								'options'    => [
									'before_add_to_cart' => esc_html__( 'Before Add To Cart', 'shopbuilder' ),
									'after_add_to_cart'  => esc_html__( 'After Add To Cart', 'shopbuilder' ),
									'after_thumbnail'    => esc_html__( 'After Image', 'shopbuilder' ),
									'after_summary'      => esc_html__( 'After Summary', 'shopbuilder' ),
									'shortcode'          => esc_html__( 'Use Shortcode', 'shopbuilder' ),
									'custom'             => esc_html__( 'Custom Position', 'shopbuilder' ),
								],
								'dependency' => [
									'rules' => [
										[
											'item'     => 'modules.compare.show_btn_product_page',
											'value'    => 'on',
											'operator' => '==',
										],
									],
								],
								'tab'        => 'button',
							],
							'product_btn_position_shortcode' => [
								'type'       => 'raw',
								'label'      => ' ',
								'html'       => sprintf(
								/* translators: 1: The shortcode.*/
									esc_html__( 'Choose where to show button on the product page. Copy this shortcode %1$s and paste it where you want to show the button.', 'shopbuilder' ),
									'<code>[rtsb_wishlist_button]</code> '
								),
								'dependency' => [
									'rules' => [
										[
											'item'     => 'modules.wishlist.show_btn_product_page',
											'value'    => 'on',
											'operator' => '==',
										],
										[
											'item'     => 'modules.wishlist.product_btn_position',
											'value'    => 'shortcode',
											'operator' => '==',
										],
									],
								],
								'tab'        => 'button',
							],
							'product_custom_hook_name'     => [
								'id'         => 'product_custom_hook_name',
								'type'       => 'text',
								'label'      => esc_html__( 'Product Hook Name', 'shopbuilder' ),
								'tab'        => 'button',
								'help'       => sprintf(
								/* translators: 1: The shortcode.*/
									esc_html__( 'Or Copy the php code %1$s and paste it in your product query where you want to show the button.', 'shopbuilder' ),
									"<br /><code>&lt;?php do_action('rtsb/modules/compare/frontend/display' ); ?&gt;</code><br />"
								),
								'dependency' => [
									'rules' => [
										[
											'item'     => 'modules.compare.show_btn_product_page',
											'value'    => 'on',
											'operator' => '==',
										],
										[
											'item'     => 'modules.compare.product_btn_position',
											'value'    => 'custom',
											'operator' => '==',
										],
									],
								],
							],
							'product_custom_hook_priority' => [
								'id'         => 'product_custom_hook_priority',
								'type'       => 'number',
								'value'      => 10,
								'size'       => 'small',
								'min'        => 0,
								'max'        => 999,
								'label'      => esc_html__( 'Product Hook Priority', 'shopbuilder' ),
								'tab'        => 'button',
								'dependency' => [
									'rules'    => [
										[
											'item'     => 'modules.compare.show_btn_product_page',
											'value'    => 'on',
											'operator' => '==',
										],
										[
											'item'     => 'modules.compare.product_btn_position',
											'value'    => 'custom',
											'operator' => '==',
										],
									],
									'relation' => 'and',
								],
							],
							'button_text'                  => [
								'id'          => 'button_text',
								'label'       => esc_html__( 'Compare Button Text', 'shopbuilder' ),
								'help'        => esc_html__( 'Enter your compare button text.', 'shopbuilder' ),
								'type'        => 'text',
								'value'       => esc_html__( 'Compare', 'shopbuilder' ),
								'placeholder' => esc_html__( 'Compare', 'shopbuilder' ),
								'tab'         => 'button',
							],
							'notice_added_text'            => [
								'id'          => 'notice_added_text',
								'label'       => esc_html__( 'Product Added Text', 'shopbuilder' ),
								'help'        => esc_html__( 'Enter the product added text.', 'shopbuilder' ),
								'type'        => 'text',
								'value'       => esc_html__( 'Product added!', 'shopbuilder' ),
								'placeholder' => esc_html__( 'Product added!', 'shopbuilder' ),
								'tab'         => 'general',
							],
							'notice_removed_text'          => [
								'id'          => 'notice_removed_text',
								'label'       => esc_html__( 'Product Removed Text', 'shopbuilder' ),
								'help'        => esc_html__( 'Enter the product removed text.', 'shopbuilder' ),
								'type'        => 'text',
								'value'       => esc_html__( 'Product removed!', 'shopbuilder' ),
								'placeholder' => esc_html__( 'Product removed!', 'shopbuilder' ),
								'tab'         => 'general',
							],
							'browse_list_text'             => [
								'id'          => 'browse_list_text',
								'label'       => esc_html__( 'Browse Compare Text', 'shopbuilder' ),
								'help'        => esc_html__( 'Enter a text for the "Browse compare" link on the compare page', 'shopbuilder' ),
								'type'        => 'text',
								'value'       => esc_html__( 'Browse Compare', 'shopbuilder' ),
								'placeholder' => esc_html__( 'Browse Compare', 'shopbuilder' ),
								'tab'         => 'general',
							],
							'page'                         => [
								'id'         => 'page',
								'type'       => 'select',
								'help'       => sprintf(
								/* translators: 1: The shortcode.*/
									esc_html__( 'Select a page for compare page and make sure you add the shortcode %1$s into the page content', 'shopbuilder' ),
									'<code>[rtsb_compare_list]</code>'
								),
								'label'      => esc_html__( 'Select Compare Page', 'shopbuilder' ),
								'options'    => Fns::get_pages(),
								'empty'      => esc_html__( 'Select a page', 'shopbuilder' ),
								'searchable' => true,
								'tab'        => 'page',
							],
							'max_limit'                    => [
								'id'          => 'page',
								'type'        => 'number',
								'sanitize_fn' => 'absint',
								'value'       => 10,
								'help'        => esc_html__( 'You can manage your maximum compare quantity from here.', 'shopbuilder' ),
								'label'       => esc_html__( 'Maximum Compare Limit', 'shopbuilder' ),
								'tab'         => 'page',
							],
							'page_show_fields'             => [
								'value'    => CompareFns::get_list_field_ids(),
								'id'       => 'page_show_fields',
								'multiple' => true,
								'checkbox' => true,
								'type'     => 'sortable',
								'options'  => CompareFns::get_available_list_fields(),
								'label'    => esc_html__( 'Compare Table Fields Visibility', 'shopbuilder' ),
								'tab'      => 'page',
							],

						]
					),
					'tabs'         => [
						'general' => [
							'title' => esc_html__( 'General', 'shopbuilder' ),
						],
						'button'  => [
							'title' => esc_html__( 'Button', 'shopbuilder' ),
						],
						'page'    => [
							'title' => esc_html__( 'Page', 'shopbuilder' ),
						],

					],
				]
			),
			'variation_swatches'     => apply_filters(
				'rtsb/module/variation_swatches/options',
				[
					'id'                => 'variation_swatches',
					'external'          => true,
					'category'          => 'general',
					'title'             => esc_html__( 'Variation Swatches', 'shopbuilder' ),
					'pluginSlug'        => 'woo-product-variation-swatches',
					'pluginIsInstalled' => Fns::check_plugin_installed( 'woo-product-variation-swatches/woo-product-variation-swatches.php' ),
					'pluginIsActive'    => Fns::check_plugin_active( 'woo-product-variation-swatches/woo-product-variation-swatches.php' ),
					'pluginActiveUrl'   => add_query_arg(
						[
							'_wpnonce' => wp_create_nonce( 'activate-plugin_woo-product-variation-swatches/woo-product-variation-swatches.php' ),
							'action'   => 'activate',
							'plugin'   => 'woo-product-variation-swatches/woo-product-variation-swatches.php',
						],
						admin_url( 'plugins.php' )
					),
					'help'              => esc_html__( 'This module requires: Variation Swatches for WooCommerce Plugin.', 'shopbuilder' ),
					'package'           => 'free',
					'active_field'      => [
						'disable' => false,
					],
					'fields'            => [],
				]
			),
			'variation_gallery'      => apply_filters(
				'rtsb/module/variation_gallery/options',
				[
					'id'                => 'variation_gallery',
					'external'          => true,
					'category'          => 'general',
					'title'             => esc_html__( 'Variation Gallery', 'shopbuilder' ),
					'pluginSlug'        => 'woo-product-variation-gallery',
					'pluginIsInstalled' => Fns::check_plugin_installed( 'woo-product-variation-gallery/woo-product-variation-gallery.php' ),
					'pluginIsActive'    => Fns::check_plugin_active( 'woo-product-variation-gallery/woo-product-variation-gallery.php' ),
					'pluginActiveUrl'   => add_query_arg(
						[
							'_wpnonce' => wp_create_nonce( 'activate-plugin_woo-product-variation-gallery/woo-product-variation-gallery.php' ),
							'action'   => 'activate',
							'plugin'   => 'woo-product-variation-gallery/woo-product-variation-gallery.php',
						],
						admin_url( 'plugins.php' )
					),
					'help'              => esc_html__( 'This module requires: Variation Images Gallery for WooCommerce Plugin', 'shopbuilder' ),
					'package'           => 'free',
					'active_field'      => [
						'disable' => false,
					],
					'fields'            => [],
				]
			),
			'mini_cart'              => apply_filters(
				'rtsb/module/mini_cart/options',
				[
					'id'           => 'mini_cart',
					'active'       => '',
					'title'        => esc_html__( 'Mini Cart', 'shopbuilder' ),
					'package'      => $this->pro_package(),
					'active_field' => [
						'label' => esc_html__( 'Enable Mini Cart?', 'shopbuilder' ),
						'help'  => esc_html__( 'Switch on to enable mini cart module.', 'shopbuilder' ),
					],
					'is_active'    => true,
					'fields'       => [],
				]
			),
			'product_size_chart'     => apply_filters(
				'rtsb/module/product_size_chart/options',
				[
					'id'           => 'product_size_chart',
					'active'       => '',
					'title'        => esc_html__( 'Product Size Chart', 'shopbuilder' ),
					'package'      => $this->pro_package(),
					'active_field' => [
						'label' => esc_html__( 'Enable Product Size Chart?', 'shopbuilder' ),
						'help'  => esc_html__( 'Switch on to enable Product Size Chart module.', 'shopbuilder' ),
					],
					'fields'       => [],
				]
			),
			'product_badges'         => apply_filters(
				'rtsb/module/product_badges/options',
				[
					'id'           => 'product_badges',
					'active'       => '',
					'title'        => esc_html__( 'Product Badges', 'shopbuilder' ),
					'package'      => $this->pro_package(),
					'active_field' => [
						'label' => esc_html__( 'Enable Product Badges?', 'shopbuilder' ),
						'help'  => esc_html__( 'Switch on to enable product badges module.', 'shopbuilder' ),
					],
					'fields'       => [],
				]
			),
			'customize_my_account'   => apply_filters(
				'rtsb/module/customize_my_account/options',
				[
					'id'           => 'customize_my_account',
					'active'       => '',
					'title'        => esc_html__( 'Customize My Account', 'shopbuilder' ),
					'package'      => $this->pro_package(),
					'active_field' => [
						'label' => esc_html__( 'Enable Customize My Account?', 'shopbuilder' ),
						'help'  => esc_html__( 'Switch on to enable customize my account module.', 'shopbuilder' ),
					],
					'fields'       => [],
				]
			),
			'pre_order'              => apply_filters(
				'rtsb/module/pre_order/options',
				[
					'id'           => 'pre_order',
					'active'       => '',
					'title'        => esc_html__( 'Pre-Order', 'shopbuilder' ),
					'package'      => $this->pro_package(),
					'active_field' => [
						'label' => esc_html__( 'Enable Pre-Order?', 'shopbuilder' ),
						'help'  => esc_html__( 'Switch on to enable pre-order module.', 'shopbuilder' ),
					],
					'fields'       => [],
				]
			),
			'currency_switcher'      => apply_filters(
				'rtsb/module/currency_switcher/options',
				[
					'id'           => 'currency_switcher',
					'active'       => '',
					'title'        => esc_html__( 'Currency Switcher', 'shopbuilder' ),
					'package'      => $this->pro_package(),
					'active_field' => [
						'label' => esc_html__( 'Enable Currency Switcher?', 'shopbuilder' ),
						'help'  => esc_html__( 'Switch on to enable currency switcher module.', 'shopbuilder' ),
					],
					'is_active'    => true,
					'fields'       => [],
				]
			),
			'product_add_ons'        => apply_filters(
				'rtsb/module/product_add_ons/options',
				[
					'id'           => 'product_add_ons',
					'active'       => '',
					'title'        => esc_html__( 'Product Add-Ons', 'shopbuilder' ),
					'package'      => $this->pro_package(),
					'active_field' => [
						'label' => esc_html__( 'Enable Product Add-Ons?', 'shopbuilder' ),
						'help'  => esc_html__( 'Switch on to enable product add-ons module.', 'shopbuilder' ),
					],
					'is_active'    => true,
					'fields'       => [],
				]
			),
			// Checkout Fields Manager.
			'checkout_fields_editor' => apply_filters(
				'rtsb/module/checkout_fields_editor/options',
				[
					'id'           => 'checkout_fields_editor',
					'active'       => '',
					'title'        => esc_html__( 'Checkout Fields Editor', 'shopbuilder' ),
					'base_class'   => CheckoutEditorInit::class,
					'active_field' => [
						'label' => esc_html__( 'Enable Checkout Fields Editor?', 'shopbuilder' ),
						'help'  => esc_html__( 'Switch on to enable Checkout Fields Editor module.', 'shopbuilder' ),
					],
					'fields'       => [
						'checkout_billing_intro'          => [
							'id'   => 'checkout_billing_intro',
							'type' => 'description',
							'text' => esc_html__( 'Billing Fields Editor allows you to customize the default billing fields on your checkout page.', 'shopbuilder' ),
							'tab'  => 'billing_fields',
						],
						'modify_billing_form'             => [
							'id'    => 'modify_billing_form',
							'type'  => 'switch',
							'label' => esc_html__( 'Customize Billing Form?', 'shopbuilder' ),
							'help'  => esc_html__( 'Enable this option to edit billing Form.', 'shopbuilder' ),
							'tab'   => 'billing_fields',
						],

						'checkout_billing_fields_info'    => [
							'id'         => 'checkout_billing_fields_info',
							'type'       => 'title',
							'label'      => esc_html__( 'Billing Fields', 'shopbuilder' ),
							'tab'        => 'billing_fields',
							'dependency' => [
								'rules' => [
									[
										'item'     => 'modules.checkout_fields_editor.modify_billing_form',
										'value'    => 'on',
										'operator' => '==',
									],
								],
							],
						],
						'checkout_billing_fields'         => [
							'id'         => 'checkout_billing_fields',
							'type'       => 'checkout_fields',
							'tab'        => 'billing_fields',
							'value'      => wp_json_encode( CheckoutFns::default_billing_fields() ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'modules.checkout_fields_editor.modify_billing_form',
										'value'    => 'on',
										'operator' => '==',
									],
								],
							],
						],
						'checkout_shipping_intro'         => [
							'id'   => 'checkout_shipping_intro',
							'type' => 'description',
							'text' => esc_html__( 'Shipping Fields Editor allows you to customize the default shipping fields on your checkout page.', 'shopbuilder' ),
							'tab'  => 'shipping_fields',
						],
						'modify_shipping_form'            => [
							'id'    => 'modify_billing_form',
							'type'  => 'switch',
							'label' => esc_html__( 'Modify Shipping Form?', 'shopbuilder' ),
							'help'  => esc_html__( 'Enable this option to edit shipping Form.', 'shopbuilder' ),
							'tab'   => 'shipping_fields',
						],
						'checkout_shipping_fields_info'   => [
							'id'         => 'checkout_shipping_fields_info',
							'type'       => 'title',
							'label'      => esc_html__( 'Shipping Fields', 'shopbuilder' ),
							'tab'        => 'shipping_fields',
							'dependency' => [
								'rules' => [
									[
										'item'     => 'modules.checkout_fields_editor.modify_shipping_form',
										'value'    => 'on',
										'operator' => '==',
									],
								],
							],
						],
						'checkout_shipping_fields'        => [
							'id'         => 'checkout_shipping_fields',
							'type'       => 'checkout_fields',
							'tab'        => 'shipping_fields',
							'value'      => wp_json_encode( CheckoutFns::default_shipping_fields() ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'modules.checkout_fields_editor.modify_shipping_form',
										'value'    => 'on',
										'operator' => '==',
									],
								],
							],
						],
						'checkout_additional_intro'       => [
							'id'   => 'checkout_additional_intro',
							'type' => 'description',
							'text' => esc_html__( 'Additional Fields Editor allows you to customize the extra fields on your checkout page.', 'shopbuilder' ),
							'tab'  => 'additional_fields',
						],
						'modify_additional_form'          => [
							'id'    => 'modify_additional_form',
							'type'  => 'switch',
							'label' => esc_html__( 'Modify Additional Form Field?', 'shopbuilder' ),
							'help'  => esc_html__( 'Enable this option to edit Additional Form.', 'shopbuilder' ),
							'tab'   => 'additional_fields',
						],
						'checkout_additional_fields_info' => [
							'id'         => 'checkout_additional_fields_info',
							'type'       => 'title',
							'label'      => esc_html__( 'Additional Fields', 'shopbuilder' ),
							'tab'        => 'additional_fields',
							'dependency' => [
								'rules' => [
									[
										'item'     => 'modules.checkout_fields_editor.modify_additional_form',
										'value'    => 'on',
										'operator' => '==',
									],
								],
							],
						],
						'checkout_additional_fields'      => [
							'id'         => 'checkout_additional_fields',
							'type'       => 'checkout_fields',
							'tab'        => 'additional_fields',
							'value'      => wp_json_encode( CheckoutFns::checkout_additional_fields() ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'modules.checkout_fields_editor.modify_additional_form',
										'value'    => 'on',
										'operator' => '==',
									],
								],
							],
						],
					],
					'tabs'         => [
						'general'           => [
							'title' => esc_html__( 'General', 'shopbuilder' ),
						],
						'billing_fields'    => [
							'title' => esc_html__( 'Billing Fields', 'shopbuilder' ),
						],
						'shipping_fields'   => [
							'title' => esc_html__( 'Shipping Fields', 'shopbuilder' ),
						],
						'additional_fields' => [
							'title' => esc_html__( 'Additional Fields', 'shopbuilder' ),
						],
					],
				]
			),
			'sales_notification'     => apply_filters(
				'rtsb/module/sales_notification/options',
				[
					'id'           => 'sales_notification',
					'active'       => '',
					'title'        => esc_html__( 'Sales Notification', 'shopbuilder' ),
					'package'      => $this->pro_package(),
					'active_field' => [
						'label' => esc_html__( 'Enable Sales Notification?', 'shopbuilder' ),
						'help'  => esc_html__( 'Switch on to enable Sales Notification module.', 'shopbuilder' ),
					],
					'fields'       => [],
				]
			),
			'flash_sale_countdown'   => apply_filters(
				'rtsb/module/flash_sale_countdown/options',
				[
					'id'           => 'flash_sale_countdown',
					'active'       => '',
					'title'        => esc_html__( 'Flash Sale Countdown', 'shopbuilder' ),
					'package'      => $this->pro_package(),
					'active_field' => [
						'label' => esc_html__( 'Enable Flash Sale Countdown?', 'shopbuilder' ),
						'help'  => esc_html__( 'Switch on to enable Flash Sale Countdown module.', 'shopbuilder' ),
					],
					'fields'       => [],
				]
			),
			'quick_checkout'         => apply_filters(
				'rtsb/module/quick_checkout/options',
				[
					'id'           => 'quick_checkout',
					'active'       => '',
					'title'        => esc_html__( 'Quick Checkout', 'shopbuilder' ),
					'package'      => $this->pro_package(),
					'active_field' => [
						'label' => esc_html__( 'Enable Quick Checkout?', 'shopbuilder' ),
						'help'  => esc_html__( 'Switch on to enable quick checkout module. Note: Currently, this is not suitable for variable products.', 'shopbuilder' ),
					],
					'fields'       => [],
				]
			),
			'multi_step_checkout'    => apply_filters(
				'rtsb/module/multi_step_checkout/options',
				[
					'id'           => 'multi_step_checkout',
					'active'       => '',
					'title'        => esc_html__( 'Multi-Step Checkout', 'shopbuilder' ),
					'package'      => $this->pro_package(),
					'active_field' => [
						'label' => esc_html__( 'Enable Multi-Step Checkout?', 'shopbuilder' ),
						'help'  => esc_html__( 'Switch on to enable Multi-step Checkout module.', 'shopbuilder' ),
					],
					'fields'       => [],
				]
			),
			'back_order'             => apply_filters(
				'rtsb/module/back_order/options',
				[
					'id'           => 'back_order',
					'active'       => '',
					'title'        => esc_html__( 'Back-Order', 'shopbuilder' ),
					'package'      => $this->pro_package(),
					'active_field' => [
						'label' => esc_html__( 'Enable Back-Order?', 'shopbuilder' ),
						'help'  => esc_html__( 'Switch on to enable back-order module.', 'shopbuilder' ),
					],
					'fields'       => [],
				]
			),
			'sticky_add_to_cart'     => apply_filters(
				'rtsb/module/sticky_add_to_cart/options',
				[
					'id'           => 'sticky_add_to_cart',
					'active'       => '',
					'title'        => esc_html__( 'Sticky Add-To-Cart', 'shopbuilder' ),
					'package'      => $this->pro_package(),
					'active_field' => [
						'label' => esc_html__( 'Enable Sticky Add-To-Cart?', 'shopbuilder' ),
						'help'  => esc_html__( 'Switch on to enable sticky add-to-cart module.', 'shopbuilder' ),
					],
					'fields'       => [],
				]
			),
		];
		return apply_filters( 'rtsb/core/modules/raw_list', $list );
	}
}
