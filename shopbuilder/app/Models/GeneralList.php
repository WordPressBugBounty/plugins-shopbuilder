<?php

namespace RadiusTheme\SB\Models;

use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Models\Base\ListModel;
use RadiusTheme\SB\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

/**
 * General Settings.
 */
class GeneralList extends ListModel {
	/**
	 * Singleton
	 */
	use SingletonTrait;

	/**
	 * List Id
	 *
	 * @var string
	 */
	protected $list_id = 'general';

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->title       = esc_html__( 'Global Settings', 'shopbuilder' );
		$this->description = esc_html__( 'ShopBuilder global settings for various elements.', 'shopbuilder' );
		$this->categories  = [
			'general'  => [
				'title' => esc_html__( 'General', 'shopbuilder' ),
			],

			'checkout' => [
				'title' => esc_html__( 'Checkout Page', 'shopbuilder' ),
			],

		];
	}

	/**
	 * Package
	 *
	 * @return array
	 */
	protected function raw_list() {
		$list = [
			'optimization'  => apply_filters(
				'rtsb/settings/optimization/options',
				[
					'id'           => 'optimization',
					'category'     => 'general',
					'title'        => esc_html__( 'Advanced Optimizations', 'shopbuilder' ),
					'package'      => $this->pro_version_checker(),
					'badge'        => 'new',
					'active_field' => [
						'disable' => true,
					],
					'fields'       => [
						'optimization_intro'     => [
							'id'   => 'optimization_intro',
							'type' => 'description',
							'text' => __( 'Optimize your website for maximum performance from these advanced options. <br /> <span style="color: #dc3545; display: block; margin-top: 10px;">⚠️ Note: After enabling or disabling any of these settings, clear the ShopBuilder cache to ensure changes are applied correctly.</span>', 'shopbuilder' ),
						],
						'enable_optimization'    => [
							'id'    => 'enable',
							'type'  => 'switch',
							'label' => esc_html__( 'Enable Asset Optimization?', 'shopbuilder' ),
							'help'  => esc_html__( 'Enable this option to dynamically load assets (styles, scripts) only when needed, helping to optimize your website’s performance', 'shopbuilder' ),
						],
						'load_elementor_scripts' => [
							'id'         => 'enable',
							'type'       => 'switch',
							'label'      => esc_html__( 'Load Elementor Assets?', 'shopbuilder' ),
							'help'       => __( 'Enable this option to load necessary Elementor assets on your website. <br /> <span style="color: #dc3545;">Turn this off if you are not using Elementor widgets to optimize site performance.</span>', 'shopbuilder' ),
							'value'      => 'on',
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.optimization.enable_optimization',
										'value'    => 'on',
										'operator' => '==',
									],
								],
							],
						],
						'context_asset_loading'  => [
							'id'         => 'enable',
							'type'       => 'switch',
							'label'      => esc_html__( 'Context-Aware Asset Loading?', 'shopbuilder' ),
							'help'       => esc_html__( 'Enable this option to dynamically Load only the necessary CSS and JS for each page type (e.g., product, cart, checkout). Disabling this option will include all assets globally on every page.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.optimization.enable_optimization',
										'value'    => 'on',
										'operator' => '==',
									],
								],
							],
						],
						/**
						'load_gutenberg_scripts' => [
							'id'    => 'enable',
							'type'  => 'switch',
							'label' => esc_html__( 'Load Gutenberg Scripts?', 'shopbuilder' ),
							'help'  => esc_html__( 'Switch on to load elementor scripts by dynamically loading scripts', 'shopbuilder' ),
						],
						*/
					],
				],
			),
			'notification'  => apply_filters(
				'rtsb/settings/notification/options',
				[
					'id'           => 'notification',
					'category'     => 'general',
					'title'        => esc_html__( 'Notification Settings', 'shopbuilder' ),
					'package'      => 'free',
					'active_field' => [
						'disable' => true,
					],
					'fields'       => [
						'notification_intro'     => [
							'id'   => 'notification_intro',
							'type' => 'description',
							'text' => esc_html__( 'Customize notifications for user actions such as adding items to cart, adding to wishlist, or comparing products.', 'shopbuilder' ),
						],
						'notification_position'  => [
							'id'      => 'notification_position',
							'value'   => 'center-center',
							'type'    => 'select',
							'label'   => esc_html__( 'Notification Position', 'shopbuilder' ),
							'options' => [
								'center-center' => esc_html__( 'Center Center', 'shopbuilder' ),
								'top-right'     => esc_html__( 'Top Right', 'shopbuilder' ),
								'bottom-right'  => esc_html__( 'Bottom Right', 'shopbuilder' ),
								'bottom-left'   => esc_html__( 'Bottom Left', 'shopbuilder' ),
								'top-left'      => esc_html__( 'Top Left', 'shopbuilder' ),
								'top-center'    => esc_html__( 'Top center', 'shopbuilder' ),
								'bottom-center' => esc_html__( 'Bottom center', 'shopbuilder' ),
							],
						],
						'notification_color'     => [
							'id'    => 'notification_color',
							'label' => esc_html__( 'Text Color', 'shopbuilder' ),
							'type'  => 'color',
							'tab'   => 'floating_button',
						],
						'notification_btn_color' => [
							'id'    => 'notification_color',
							'label' => esc_html__( 'Button Color', 'shopbuilder' ),
							'type'  => 'color',
							'tab'   => 'floating_button',
						],
						'notification_bg'        => [
							'id'    => 'notification_bg',
							'label' => esc_html__( 'Background Color', 'shopbuilder' ),
							'type'  => 'color',
							'tab'   => 'floating_button',
						],
						'hide_notification'      => [
							'id'    => 'hide_notification',
							'type'  => 'switch',
							'label' => esc_html__( 'Hide Notifications', 'shopbuilder' ),
							'help'  => esc_html__( 'Enable this option if you don\'t want the notifications to show.', 'shopbuilder' ),
						],
					],
				],
			),
			'social_share'  => apply_filters(
				'rtsb/settings/social_share/options',
				[
					'id'           => 'social_share',
					'category'     => 'general',
					'title'        => esc_html__( 'Social Share Settings', 'shopbuilder' ),
					'package'      => 'free',
					'active_field' => [
						'disable' => true,
					],
					'fields'       => [
						'social_share_intro'              => [
							'id'   => 'social_share_intro',
							'type' => 'description',
							'text' => esc_html__( 'Configure social sharing platforms for products sharing.', 'shopbuilder' ),
						],
						'share_platforms'                 => [
							'id'          => 'share_platforms',
							'label'       => esc_html__( 'Select Social Sharing Platforms', 'shopbuilder' ),
							'type'        => 'checkbox',
							'orientation' => 'vertical',
							'value'       => [ 'facebook', 'twitter', 'linkedin' ],
							'options'     => Fns::social_share_platforms_list(),
						],
						'share_platforms_to_product_page' => [
							'id'    => 'share_platforms_to_product_page',
							'type'  => 'switch',
							'label' => esc_html__( 'Sharing Button in Single Product Page', 'shopbuilder' ),
							'help'  => esc_html__( 'Enable this option to allow share a product from product page. Woocommerce Supported Sharing plugins added to Hook: woocommerce_share', 'shopbuilder' ),
						],
						'share_icon_layout'               => [
							'type'       => 'select',
							'value'      => 'share-layout1',
							'label'      => esc_html__( 'Share Button Layout', 'shopbuilder' ),
							'options'    => [
								'share-layout1' => esc_html__( 'Layout 1', 'shopbuilder' ),
								'share-layout2' => esc_html__( 'Layout 2', 'shopbuilder' ),
							],
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.social_share.share_platforms_to_product_page',
										'value'    => 'on',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
					],
				],
			),
			'guest_user'    => apply_filters(
				'rtsb/settings/guest_user/options',
				[
					'id'           => 'guest_user',
					'category'     => 'general',
					'title'        => esc_html__( 'Guest User Controls', 'shopbuilder' ),
					'package'      => $this->pro_package(),
					'active_field' => [
						'disable' => true,
					],
				],
			),
			'tooltip'       => apply_filters(
				'rtsb/settings/tooltip/options',
				[
					'id'           => 'tooltip',
					'category'     => 'general',
					'title'        => esc_html__( 'Tooltip Settings', 'shopbuilder' ),
					'package'      => $this->pro_package(),
					'active_field' => [
						'disable' => true,
					],
				],
			),
			'billing_form'  => apply_filters(
				'rtsb/settings/billing_form/options',
				[
					'id'           => 'billing_form',
					'category'     => 'checkout',
					'title'        => esc_html__( 'Billing Form Settings', 'shopbuilder' ),
					'package'      => 'free',
					'active_field' => [
						'label' => esc_html__( 'Customize Billing Form Fields?', 'shopbuilder' ),
						'help'  => esc_html__( 'Switch on to customize billing form fields.', 'shopbuilder' ),
					],
					'fields'       => [
						'checkout_billing_info'          => [
							'id'          => 'checkout_billing_info',
							'type'        => 'title',
							'label'       => __( 'The below form fields customization will be disabled when the <strong><u>Checkout Fields Editor</u></strong> module is activated.', 'shopbuilder' ),
							'customClass' => 'checkout-notice',
						],
						'billing_first_name_heading'     => [
							'id'    => 'billing_first_name_heading',
							'type'  => 'title',
							'label' => esc_html__( 'First Name', 'shopbuilder' ),
						],
						'billing_first_name'             => [
							'id'          => 'billing_first_name',
							'label'       => esc_html__( 'First name', 'shopbuilder' ),
							'type'        => 'checkbox',
							'orientation' => 'vertical',
							'value'       => [ 'show', 'required' ],
							'options'     => [
								[
									'value' => 'show',
									'label' => 'Show First name?',
								],
								[
									'value' => 'required',
									'label' => 'Required Field',
								],
							],
						],
						'billing_first_name_label'       => [
							'id'         => 'billing_first_name_label',
							'label'      => esc_html__( 'First name Label', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.billing_form.billing_first_name',
										'value'    => 'show',
										'operator' => '==',
									],
								],
							],
						],
						'billing_first_name_placeholder' => [
							'id'         => 'billing_first_name_placeholder',
							'label'      => esc_html__( 'First name Placeholder', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.billing_form.billing_first_name',
										'value'    => 'show',
										'operator' => '==',
									],
								],
							],
						],

						'billing_last_name_heading'      => [
							'id'    => 'billing_last_name_heading',
							'type'  => 'title',
							'label' => esc_html__( 'Last Name', 'shopbuilder' ),
						],
						'billing_last_name'              => [
							'id'          => 'billing_last_name',
							'label'       => esc_html__( 'Last name', 'shopbuilder' ),
							'type'        => 'checkbox',
							'orientation' => 'vertical',
							'value'       => [ 'show', 'required' ],
							'options'     => [
								[
									'value' => 'show',
									'label' => 'Show Last name?',
								],
								[
									'value' => 'required',
									'label' => 'Required Field',
								],
							],
						],

						'billing_last_name_label'        => [
							'id'         => 'billing_last_name_label',
							'label'      => esc_html__( 'Last name label', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.billing_form.billing_last_name',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'billing_last_name_placeholder'  => [
							'id'         => 'billing_last_name_placeholder',
							'label'      => esc_html__( 'Last name Placeholder', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.billing_form.billing_last_name',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],

						'billing_company_heading'        => [
							'id'    => 'billing_company_heading',
							'type'  => 'title',
							'label' => esc_html__( 'Company', 'shopbuilder' ),
						],
						'billing_company'                => [
							'id'          => 'billing_company',
							'label'       => esc_html__( 'Company name', 'shopbuilder' ),
							'type'        => 'checkbox',
							'orientation' => 'vertical',
							'value'       => [
								'show',
								'required' === get_option( 'woocommerce_checkout_company_field', 'optional' ) ? 'required' : 'optional',
							],
							'options'     => [
								[
									'value' => 'show',
									'label' => 'Show company name?',
								],
								[
									'value' => 'required',
									'label' => 'Required Field',
								],
							],
						],

						'billing_company_label'          => [
							'id'         => 'billing_company_label',
							'label'      => esc_html__( 'Company label', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.billing_form.billing_company',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'billing_company_placeholder'    => [
							'id'         => 'billing_company_placeholder',
							'label'      => esc_html__( 'Company Placeholder', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.billing_form.billing_company',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],

						'billing_country_heading'        => [
							'id'    => 'billing_country_heading',
							'type'  => 'title',
							'label' => esc_html__( 'Country', 'shopbuilder' ),
						],
						'billing_country'                => [
							'id'          => 'billing_country',
							'label'       => esc_html__( 'Country', 'shopbuilder' ),
							'type'        => 'checkbox',
							'orientation' => 'vertical',
							'value'       => [ 'show', 'required' ],
							'options'     => [
								[
									'value' => 'show',
									'label' => 'Show Country?',
								],
								[
									'value' => 'required',
									'label' => 'Required Field',
								],
							],
						],

						'billing_country_label'          => [
							'id'         => 'billing_country_label',
							'label'      => esc_html__( 'Country label', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.billing_form.billing_country',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'billing_address_1_heading'      => [
							'id'    => 'billing_address_1_heading',
							'type'  => 'title',
							'label' => esc_html__( 'Address 1', 'shopbuilder' ),
						],
						'billing_address_1'              => [
							'id'          => 'billing_address_1',
							'label'       => esc_html__( 'Address 1', 'shopbuilder' ),
							'type'        => 'checkbox',
							'orientation' => 'vertical',
							'value'       => [ 'show', 'required' ],
							'options'     => [
								[
									'value' => 'show',
									'label' => 'Show Address field?',
								],
								[
									'value' => 'required',
									'label' => 'Required Field',
								],
							],
						],

						'billing_address_1_label'        => [
							'id'         => 'billing_address_1_label',
							'label'      => esc_html__( 'Address 1 label', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.billing_form.billing_address_1',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'billing_address_1_placeholder'  => [
							'id'         => 'billing_address_1_placeholder',
							'label'      => esc_html__( 'Address 1 Placeholder', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.billing_form.billing_address_1',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'billing_address_2_heading'      => [
							'id'         => 'billing_address_2_heading',
							'type'       => 'title',
							'label'      => esc_html__( 'Address 2', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.billing_form.billing_address_1',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'billing_address_2'              => [
							'id'          => 'billing_address_2',
							'label'       => esc_html__( 'Address 2', 'shopbuilder' ),
							'type'        => 'checkbox',
							'orientation' => 'vertical',
							'value'       => [
								'show',
								'required' === get_option( 'woocommerce_checkout_address_2_field', 'optional' ) ? 'required' : 'optional',
							],
							'options'     => [
								[
									'value' => 'show',
									'label' => 'Show Address field?',
								],
							],
							'help'        => esc_html__( 'if "Address 1" is hidden then this field also hides.', 'shopbuilder' ),
							'dependency'  => [
								'rules' => [
									[
										'item'     => 'general.billing_form.billing_address_1',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],

						'billing_address_2_label'        => [
							'id'         => 'billing_address_2_label',
							'label'      => esc_html__( 'Address 2 label', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.billing_form.billing_address_1',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
									[
										'item'     => 'general.billing_form.billing_address_2',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'billing_address_2_placeholder'  => [
							'id'         => 'billing_address_2_placeholder',
							'label'      => esc_html__( 'Address 2 Placeholder', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.billing_form.billing_address_1',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
									[
										'item'     => 'general.billing_form.billing_address_2',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'billing_city_heading'           => [
							'id'    => 'billing_city_heading',
							'type'  => 'title',
							'label' => esc_html__( 'Town / City', 'shopbuilder' ),
						],
						'billing_city'                   => [
							'id'          => 'billing_city',
							'label'       => esc_html__( 'Town / City', 'shopbuilder' ),
							'type'        => 'checkbox',
							'orientation' => 'vertical',
							'value'       => [ 'show', 'required' ],
							'options'     => [
								[
									'value' => 'show',
									'label' => 'Show Town / City?',
								],
								[
									'value' => 'required',
									'label' => 'Required Field',
								],
							],

						],
						'billing_city_label'             => [
							'id'         => 'billing_city_label',
							'label'      => esc_html__( 'Town / City label', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.billing_form.billing_city',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'billing_city_placeholder'       => [
							'id'         => 'billing_city_placeholder',
							'label'      => esc_html__( 'City placeholder', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.billing_form.billing_city',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'billing_state_heading'          => [
							'id'    => 'billing_city_heading',
							'type'  => 'title',
							'label' => esc_html__( 'State', 'shopbuilder' ),
						],
						'billing_state'                  => [
							'id'          => 'billing_state',
							'label'       => esc_html__( 'State', 'shopbuilder' ),
							'type'        => 'checkbox',
							'orientation' => 'vertical',
							'value'       => [ 'show', 'required' ],
							'options'     => [
								[
									'value' => 'show',
									'label' => 'Show State?',
								],
								[
									'value' => 'required',
									'label' => 'Required Field',
								],
							],
						],

						'billing_state_label'            => [
							'id'         => 'billing_state_label',
							'label'      => esc_html__( 'State label', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.billing_form.billing_state',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'billing_postcode_heading'       => [
							'id'    => 'billing_postcode_heading',
							'type'  => 'title',
							'label' => esc_html__( 'Postcode / ZIP', 'shopbuilder' ),
						],
						'billing_postcode'               => [
							'id'          => 'billing_postcode',
							'label'       => esc_html__( 'Postcode / ZIP', 'shopbuilder' ),
							'type'        => 'checkbox',
							'orientation' => 'vertical',
							'value'       => [ 'show', 'required' ],
							'options'     => [
								[
									'value' => 'show',
									'label' => 'Show Postcode / ZIP?',
								],
								[
									'value' => 'required',
									'label' => 'Required Field',
								],
							],
						],
						'billing_postcode_label'         => [
							'id'         => 'billing_postcode_label',
							'label'      => esc_html__( 'Postcode / ZIP label', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.billing_form.billing_postcode',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'billing_postcode_placeholder'   => [
							'id'         => 'billing_postcode_placeholder',
							'label'      => esc_html__( 'Postcode / ZIP placeholder', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.billing_form.billing_postcode',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'billing_phone_heading'          => [
							'id'    => 'billing_phone_heading',
							'type'  => 'title',
							'label' => esc_html__( 'Phone', 'shopbuilder' ),
						],
						'billing_phone'                  => [
							'id'          => 'billing_phone',
							'label'       => esc_html__( 'Phone', 'shopbuilder' ),
							'type'        => 'checkbox',
							'orientation' => 'vertical',
							'value'       => [
								'show',
								'required' === get_option( 'woocommerce_checkout_phone_field', 'required' ) ? 'required' : 'optional',
							],
							'options'     => [
								[
									'value' => 'show',
									'label' => 'Show Phone Field?',
								],
								[
									'value' => 'required',
									'label' => 'Required Field',
								],
							],

						],
						'billing_phone_label'            => [
							'id'         => 'billing_phone_label',
							'label'      => esc_html__( 'Phone field label', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.billing_form.billing_phone',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'billing_phone_placeholder'      => [
							'id'         => 'billing_phone_placeholder',
							'label'      => esc_html__( 'Phone field placeholder', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.billing_form.billing_phone',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'billing_email_heading'          => [
							'id'    => 'billing_email_heading',
							'type'  => 'title',
							'label' => esc_html__( 'Email', 'shopbuilder' ),
						],
						'billing_email'                  => [
							'id'          => 'billing_email',
							'label'       => esc_html__( 'Email address', 'shopbuilder' ),
							'type'        => 'checkbox',
							'orientation' => 'vertical',
							'value'       => [ 'show', 'required' ],
							'options'     => [
								[
									'value' => 'show',
									'label' => 'Show Email address Field?',
								],
								[
									'value' => 'required',
									'label' => 'Required Field',
								],
							],
						],
						'billing_email_label'            => [
							'id'         => 'billing_email_label',
							'label'      => esc_html__( 'Email address field label', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.billing_form.billing_email',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'billing_email_placeholder'      => [
							'id'         => 'billing_email_placeholder',
							'label'      => esc_html__( 'Email address field placeholder', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.billing_form.billing_email',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
					],

				]
			),
			'shipping_form' => apply_filters(
				'rtsb/module/shipping_form/options',
				[
					'id'           => 'shipping_form',
					'category'     => 'checkout',
					'title'        => esc_html__( 'Shipping Form Settings', 'shopbuilder' ),
					'package'      => 'free',
					'active_field' => [
						'label' => esc_html__( 'Customize Shipping form Fields?', 'shopbuilder' ),
						'help'  => esc_html__( 'Switch on to customize shipping form fields.', 'shopbuilder' ),
					],
					'fields'       => [
						'checkout_shipping_info'          => [
							'id'          => 'checkout_shipping_info',
							'type'        => 'title',
							'label'       => __( 'The below form fields customization will be disabled when the <strong><u>Checkout Fields Editor</u></strong> module is activated.', 'shopbuilder' ),
							'customClass' => 'checkout-notice',
						],
						'shipping_first_name_heading'     => [
							'id'    => 'shipping_first_name_heading',
							'type'  => 'title',
							'label' => esc_html__( 'First Name', 'shopbuilder' ),
						],
						'shipping_first_name'             => [
							'id'          => 'shipping_first_name',
							'label'       => esc_html__( 'First name', 'shopbuilder' ),
							'type'        => 'checkbox',
							'orientation' => 'vertical',
							'value'       => [ 'show', 'required' ],
							'options'     => [
								[
									'value' => 'show',
									'label' => 'Show First name?',
								],
								[
									'value' => 'required',
									'label' => 'Required Field',
								],
							],
						],
						'shipping_first_name_label'       => [
							'id'         => 'shipping_first_name_label',
							'label'      => esc_html__( 'First name Label', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.shipping_form.shipping_first_name',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'shipping_first_name_placeholder' => [
							'id'         => 'shipping_first_name_placeholder',
							'label'      => esc_html__( 'First name Placeholder', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.shipping_form.shipping_first_name',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'shipping_first_last_heading'     => [
							'id'    => 'shipping_first_last_heading',
							'type'  => 'title',
							'label' => esc_html__( 'Last Name', 'shopbuilder' ),
						],
						'shipping_last_name'              => [
							'id'          => 'shipping_last_name',
							'label'       => esc_html__( 'Last name', 'shopbuilder' ),
							'type'        => 'checkbox',
							'orientation' => 'vertical',
							'value'       => [ 'show', 'required' ],
							'options'     => [
								[
									'value' => 'show',
									'label' => 'Show Last name?',
								],
								[
									'value' => 'required',
									'label' => 'Required Field',
								],
							],
						],

						'shipping_last_name_label'        => [
							'id'         => 'shipping_last_name_label',
							'label'      => esc_html__( 'Last name label', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.shipping_form.shipping_last_name',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'shipping_last_name_placeholder'  => [
							'id'         => 'shipping_last_name_placeholder',
							'label'      => esc_html__( 'Last name Placeholder', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.shipping_form.shipping_last_name',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'shipping_company_heading'        => [
							'id'    => 'shipping_company_heading',
							'type'  => 'title',
							'label' => esc_html__( 'Company', 'shopbuilder' ),
						],
						'shipping_company'                => [
							'id'          => 'shipping_company',
							'label'       => esc_html__( 'Company name', 'shopbuilder' ),
							'type'        => 'checkbox',
							'orientation' => 'vertical',
							'value'       => [ 'show', 'required' ],
							'options'     => [
								[
									'value' => 'show',
									'label' => 'Show company name?',
								],
								[
									'value' => 'required',
									'label' => 'Required Field',
								],
							],
						],

						'shipping_company_label'          => [
							'id'         => 'shipping_company_label',
							'label'      => esc_html__( 'Company label', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.shipping_form.shipping_company',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'shipping_company_placeholder'    => [
							'id'         => 'shipping_company_placeholder',
							'label'      => esc_html__( 'Shipping company Placeholder', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.shipping_form.shipping_company',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'shipping_country_heading'        => [
							'id'    => 'shipping_country_heading',
							'type'  => 'title',
							'label' => esc_html__( 'Country', 'shopbuilder' ),
						],
						'shipping_country'                => [
							'id'          => 'shipping_country',
							'label'       => esc_html__( 'Country', 'shopbuilder' ),
							'type'        => 'checkbox',
							'orientation' => 'vertical',
							'value'       => [ 'show', 'required' ],
							'options'     => [
								[
									'value' => 'show',
									'label' => 'Show Country?',
								],
								[
									'value' => 'required',
									'label' => 'Required Field',
								],
							],
						],

						'shipping_country_label'          => [
							'id'         => 'shipping_country_label',
							'label'      => esc_html__( 'Country label', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.shipping_form.shipping_country',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'shipping_address_1_heading'      => [
							'id'    => 'shipping_address_1_heading',
							'type'  => 'title',
							'label' => esc_html__( 'Address 1', 'shopbuilder' ),
						],
						'shipping_address_1'              => [
							'id'          => 'shipping_address_1',
							'label'       => esc_html__( 'Address 1', 'shopbuilder' ),
							'type'        => 'checkbox',
							'orientation' => 'vertical',
							'value'       => [ 'show', 'required' ],
							'options'     => [
								[
									'value' => 'show',
									'label' => 'Show Address field?',
								],
								[
									'value' => 'required',
									'label' => 'Required Field',
								],
							],
						],

						'shipping_address_1_label'        => [
							'id'         => 'shipping_address_1_label',
							'label'      => esc_html__( 'Address 1 label', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.shipping_form.shipping_address_1',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'shipping_address_1_placeholder'  => [
							'id'         => 'shipping_address_1_placeholder',
							'label'      => esc_html__( 'Address 1 Placeholder', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.shipping_form.shipping_address_1',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'shipping_address_2_heading'      => [
							'id'         => 'shipping_address_2_heading',
							'type'       => 'title',
							'label'      => esc_html__( 'Address 2', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.shipping_form.shipping_address_1',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'shipping_address_2'              => [
							'id'          => 'shipping_address_2',
							'label'       => esc_html__( 'Address 2', 'shopbuilder' ),
							'type'        => 'checkbox',
							'orientation' => 'vertical',
							'value'       => [ 'show' ],
							'options'     => [
								[
									'value' => 'show',
									'label' => 'Show Address field?',
								],
							],
							'help'        => esc_html__( 'if "Address 1" is hidden then this field also hides.', 'shopbuilder' ),
							'dependency'  => [
								'rules' => [
									[
										'item'     => 'general.shipping_form.shipping_address_1',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],

						'shipping_address_2_label'        => [
							'id'         => 'shipping_address_2_label',
							'label'      => esc_html__( 'Address 2 label', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.shipping_form.shipping_address_1',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
									[
										'item'     => 'general.shipping_form.shipping_address_2',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'shipping_address_2_placeholder'  => [
							'id'         => 'shipping_address_2_placeholder',
							'label'      => esc_html__( 'Address 2 Placeholder', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.shipping_form.shipping_address_1',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
									[
										'item'     => 'general.shipping_form.shipping_address_2',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'shipping_city_heading'           => [
							'id'    => 'shipping_city_heading',
							'type'  => 'title',
							'label' => esc_html__( 'Town / City', 'shopbuilder' ),
						],
						'shipping_city'                   => [
							'id'          => 'shipping_city',
							'label'       => esc_html__( 'Town / City', 'shopbuilder' ),
							'type'        => 'checkbox',
							'orientation' => 'vertical',
							'value'       => [ 'show', 'required' ],
							'options'     => [
								[
									'value' => 'show',
									'label' => 'Show Town / City?',
								],
								[
									'value' => 'required',
									'label' => 'Required Field',
								],
							],
						],
						'shipping_city_label'             => [
							'id'         => 'shipping_city_label',
							'label'      => esc_html__( 'Town / City label', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.shipping_form.shipping_city',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'shipping_city_placeholder'       => [
							'id'         => 'shipping_city_placeholder',
							'label'      => esc_html__( 'Shipping city placeholder', 'shopbuilder' ),
							'type'       => 'text',
							'help'       => esc_html__( 'Leave empty to set default.', 'shopbuilder' ),
							'dependency' => [
								'rules' => [
									[
										'item'     => 'general.shipping_form.shipping_city',
										'value'    => 'show',
										'operator' => '==', // 'operator' => 'any','in', !in' only valid for multiple value //
									],
								],
							],
						],
						'shipping_state_heading'          => [
							'id'    => 'shipping_state_heading',
							'type'  => 'title',
							'label' => esc_html__( 'Others', 'shopbuilder' ),
						],
						'shipping_state'                  => [
							'id'          => 'shipping_state',
							'label'       => esc_html__( 'State', 'shopbuilder' ),
							'type'        => 'checkbox',
							'orientation' => 'vertical',
							'value'       => [ 'show', 'required' ],
							'options'     => [
								[
									'value' => 'show',
									'label' => 'Show State?',
								],
								[
									'value' => 'required',
									'label' => 'Required Field',
								],
							],
						],
						'shipping_postcode'               => [
							'id'          => 'shipping_postcode',
							'label'       => esc_html__( 'Postcode / ZIP', 'shopbuilder' ),
							'type'        => 'checkbox',
							'orientation' => 'vertical',
							'value'       => [ 'show', 'required' ],
							'options'     => [
								[
									'value' => 'show',
									'label' => 'Show Postcode / ZIP?',
								],
							],
						],

					],
				]
			),
		];

		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			unset( $list['optimization']['fields']['load_elementor_scripts'] );
		}

		return apply_filters( 'rtsb/core/general_settings/raw_list', $list );
	}
}
