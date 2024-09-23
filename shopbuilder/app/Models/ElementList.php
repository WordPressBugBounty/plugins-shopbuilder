<?php

namespace RadiusTheme\SB\Models;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

use RadiusTheme\SB\Elementor\Widgets\Archive;
use RadiusTheme\SB\Elementor\Widgets\Cart;
use RadiusTheme\SB\Elementor\Widgets\Checkout;
use RadiusTheme\SB\Elementor\Widgets\General;
use RadiusTheme\SB\Elementor\Widgets\Single;
use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Models\Base\ListModel;
use RadiusTheme\SB\Traits\SingletonTrait;
use RadiusTheme\SBPRO\Elementor\Widgets\Checkout as CheckoutPro;

/**
 * Elementor Element List
 */
class ElementList extends ListModel {
	/**
	 * Singleton
	 */
	use SingletonTrait;

	/**
	 * List id
	 *
	 * @var string
	 */
	protected $list_id = 'elements';

	/**
	 * Elementor Elements
	 */
	public function __construct() {
		parent::__construct();
		$this->title       = esc_html__( 'Elementor Widgets', 'shopbuilder' );
		$this->short_title = esc_html__( 'Elements', 'shopbuilder' );
		$this->description = esc_html__( 'Here you can find the list of all Elementor widgets. You can individually enable or disable the elements. Or you can do that by one click.', 'shopbuilder' );
		$this->categories  = apply_filters(
			'rtsb/elements/list/categories',
			[
				'general'             => [
					'title' => esc_html__( 'General', 'shopbuilder' ),
				],
				'shop'                => [
					'title' => esc_html__( 'Shop / Archive', 'shopbuilder' ),
				],
				'product'             => [
					'title' => esc_html__( 'Single', 'shopbuilder' ),
				],
				'quick_view'          => [
					'title' => esc_html__( 'Quick View', 'shopbuilder' ),
				],
				'cart'                => [
					'title' => esc_html__( 'Cart', 'shopbuilder' ),
				],
				'checkout'            => [
					'title' => esc_html__( 'Checkout', 'shopbuilder' ),
				],
				'thank_you'           => [
					'title' => esc_html__( 'Order Received', 'shopbuilder' ),
				],
				'myaccount_dashboard' => [
					'title' => esc_html__( 'My Account', 'shopbuilder' ),
				],
				'others_widget'       => [
					'title' => esc_html__( 'Others', 'shopbuilder' ),
				],
			]
		);
	}


	/**
	 * Widget List.
	 *
	 * @return array
	 */
	protected function raw_list() {
		$list = $this->product_page_widget_list()
				+ $this->general_widget_list()
				+ $this->product_quick_view()
				+ $this->shop_archive_page_widget_list()
				+ $this->cart_page_widget_list()
				+ $this->checkout_page_widget_list()
				+ $this->thankyou_page_widget_list()
				+ $this->my_account_page_widget_list()
				+ $this->myaccount_auth_page_widget_list();

		return apply_filters( 'rtsb/core/elements/raw_list', $list );
	}

	/**
	 * GLobal Widget List.
	 *
	 * @return array
	 */
	protected function general_widget_list() {
		$list = [
			'products_grid'             => apply_filters(
				'rtsb/elements/products_grid/options',
				[
					'id'         => 'products_grid',
					'title'      => esc_html__( 'Products - Grid Layouts', 'shopbuilder' ),
					'base_class' => General\ProductsGrid::class,
					'category'   => 'general',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'products_list'             => apply_filters(
				'rtsb/elements/products_list/options',
				[
					'id'         => 'products_list',
					'title'      => esc_html__( 'Products - List Layouts', 'shopbuilder' ),
					'base_class' => General\ProductsList::class,
					'category'   => 'general',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),

			'products_slider'           => apply_filters(
				'rtsb/elements/products_slider/options',
				[
					'id'         => 'products_slider',
					'title'      => esc_html__( 'Products - Slider Layouts', 'shopbuilder' ),
					'base_class' => General\ProductsSlider::class,
					'category'   => 'general',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),

			'products_single_cateogory' => apply_filters(
				'rtsb/elements/products_single_cateogory/options',
				[
					'id'         => 'products_single_cateogory',
					'title'      => esc_html__( 'Single Category', 'shopbuilder' ),
					'base_class' => General\ProductsSingleCategory::class,
					'category'   => 'general',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'product_cateogories'       => apply_filters(
				'rtsb/elements/product_cateogories/options',
				[
					'id'         => 'product_cateogories',
					'title'      => esc_html__( 'Product Categories', 'shopbuilder' ),
					'base_class' => General\ProductCategories::class,
					'category'   => 'general',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'social_share'              => apply_filters(
				'rtsb/elements/social_share/options',
				[
					'id'         => 'social_share',
					'title'      => esc_html__( 'Social Share', 'shopbuilder' ),
					'base_class' => General\SocialShare::class,
					'category'   => 'general',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'wc_breadcrumbs'            => apply_filters(
				'rtsb/elements/wc_breadcrumbs/options',
				[
					'id'         => 'wc_breadcrumbs',
					'title'      => esc_html__( 'Breadcrumbs', 'shopbuilder' ),
					'base_class' => General\ProductBreadcrumbs::class,
					'category'   => 'general',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'product_notice'            => apply_filters(
				'rtsb/elements/product_notice/options',
				[
					'id'         => 'product_notice',
					'title'      => esc_html__( 'Notice', 'shopbuilder' ),
					'base_class' => General\Notice::class,
					'category'   => 'general',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'rtsb_wishlist'             => apply_filters(
				'rtsb/elements/rtsb_wishlist/options',
				[
					'id'         => 'rtsb_wishlist',
					'title'      => esc_html__( 'Wishlist Table', 'shopbuilder' ),
					'base_class' => General\Wishlist::class,
					'category'   => 'general',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'highlighted_product'       => apply_filters(
				'rtsb/elements/highlighted_product/options',
				[
					'id'       => 'highlighted_product',
					'title'    => esc_html__( 'Highlighted Product', 'shopbuilder' ),
					'category' => 'general',
					'active'   => '',
					'package'  => $this->pro_package(),
					'fields'   => [],
				]
			),

		];

		return apply_filters( 'rtsb/core/elements/general/widget_list', $list );
	}

	/**
	 * Product Single Widget List.
	 *
	 * @return array
	 */
	protected function product_page_widget_list() {

		$list = [
			'product_title'                  => apply_filters(
				'rtsb/elements/product_title/options',
				[
					'id'         => 'product_title',
					'title'      => esc_html__( 'Product Title', 'shopbuilder' ),
					'base_class' => Single\ProductTitle::class,
					'category'   => 'product',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'product_description'            => apply_filters(
				'rtsb/elements/product_description/options',
				[
					'id'         => 'product_description',
					'title'      => esc_html__( 'Product Description', 'shopbuilder' ),
					'base_class' => Single\ProductDescription::class,
					'category'   => 'product',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'product_short_description'      => apply_filters(
				'rtsb/elements/product_short_description/options',
				[
					'id'         => 'product_short_description',
					'title'      => esc_html__( 'Short Description', 'shopbuilder' ),
					'base_class' => Single\ShortDescription::class,
					'category'   => 'product',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'product_images'                 => apply_filters(
				'rtsb/elements/product_images/options',
				[
					'id'         => 'product_images',
					'title'      => esc_html__( 'Product Images', 'shopbuilder' ),
					'base_class' => Single\ProductImages::class,
					'category'   => 'product',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'product_onsale'                 => apply_filters(
				'rtsb/elements/product_onsale/options',
				[
					'id'         => 'product_onsale',
					'title'      => esc_html__( 'Product Badges', 'shopbuilder' ),
					'base_class' => Single\ProductBadges::class,
					'category'   => 'product',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'product_additional_info'        => apply_filters(
				'rtsb/elements/product_additional_info/options',
				[
					'id'         => 'product_additional_info',
					'title'      => esc_html__( 'Additional Information', 'shopbuilder' ),
					'base_class' => Single\AdditionalInformation::class,
					'category'   => 'product',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'product_price'                  => apply_filters(
				'rtsb/elements/product_price/options',
				[
					'id'         => 'product_price',
					'title'      => esc_html__( 'Product Price', 'shopbuilder' ),
					'base_class' => Single\ProductPrice::class,
					'category'   => 'product',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'product_meta'                   => apply_filters(
				'rtsb/elements/product_meta/options',
				[
					'id'         => 'product_meta',
					'title'      => esc_html__( 'Product Meta', 'shopbuilder' ),
					'base_class' => Single\ProductMeta::class,
					'category'   => 'product',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'product_categories'             => apply_filters(
				'rtsb/elements/product_categories/options',
				[
					'id'         => 'product_categories',
					'title'      => esc_html__( 'Product Categories', 'shopbuilder' ),
					'base_class' => Single\ProductCats::class,
					'category'   => 'product',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'product_rating'                 => apply_filters(
				'rtsb/elements/product_rating/options',
				[
					'id'         => 'product_rating',
					'title'      => esc_html__( 'Product Rating', 'shopbuilder' ),
					'base_class' => Single\ProductRating::class,
					'category'   => 'product',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'product_tags'                   => apply_filters(
				'rtsb/elements/product_tags/options',
				[
					'id'         => 'product_tags',
					'title'      => esc_html__( 'Product Tags', 'shopbuilder' ),
					'base_class' => Single\ProductTags::class,
					'category'   => 'product',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'product_sku'                    => apply_filters(
				'rtsb/elements/product_sku/options',
				[
					'id'         => 'product_sku',
					'title'      => esc_html__( 'Product SKU', 'shopbuilder' ),
					'base_class' => Single\ProductSKU::class,
					'category'   => 'product',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'product_stock'                  => apply_filters(
				'rtsb/elements/product_stock/options',
				[
					'id'         => 'product_stock',
					'title'      => esc_html__( 'Product Stock', 'shopbuilder' ),
					'base_class' => Single\ProductStock::class,
					'category'   => 'product',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'actions_button'                 => apply_filters(
				'rtsb/elements/actions_button/options',
				[
					'id'         => 'actions_button',
					'title'      => esc_html__( 'Action Buttons', 'shopbuilder' ),
					'base_class' => Single\ActionsButton::class,
					'category'   => 'product',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'product_add_to_cart'            => apply_filters(
				'rtsb/elements/product_add_to_cart/options',
				[
					'id'         => 'product_add_to_cart',
					'title'      => esc_html__( 'Add to Cart', 'shopbuilder' ),
					'base_class' => Single\ProductAddToCart::class,
					'category'   => 'product',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'product_share'                  => apply_filters(
				'rtsb/elements/product_share/options',
				[
					'id'         => 'product_share',
					'title'      => esc_html__( 'Product Share', 'shopbuilder' ),
					'base_class' => Single\ProductShare::class,
					'category'   => 'product',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'product_sales_count'            => apply_filters(
				'rtsb/elements/product_sales_count/options',
				[
					'id'       => 'product_sales_count',
					'title'    => esc_html__( 'Sales Count', 'shopbuilder' ),
					'category' => 'product',
					'active'   => '',
					'package'  => $this->pro_package(),
					'fields'   => [],
				]
			),
			'product_stock_counter'          => apply_filters(
				'rtsb/elements/product_stock_counter/options',
				[
					'id'       => 'product_stock_counter',
					'title'    => esc_html__( 'Product Stock Count', 'shopbuilder' ),
					'category' => 'product',
					'active'   => '',
					'package'  => $this->pro_package(),
					'fields'   => [],
				]
			),
			'flash_sale_countdown'           => apply_filters(
				'rtsb/elements/flash_sale_countdown/options',
				[
					'id'       => 'flash_sale_countdown',
					'title'    => esc_html__( 'Flash Sale Countdown', 'shopbuilder' ),
					'category' => 'product',
					'active'   => '',
					'package'  => $this->pro_package(),
					'fields'   => [],
				]
			),
			'product_size_chart'             => apply_filters(
				'rtsb/elements/product_size_chart/options',
				[
					'id'       => 'product_size_chart',
					'title'    => esc_html__( 'Size Chart', 'shopbuilder' ),
					'category' => 'product',
					'active'   => '',
					'package'  => $this->pro_package(),
					'fields'   => [],
				]
			),
			'product_quick_checkout'         => apply_filters(
				'rtsb/elements/product_quick_checkout/options',
				[
					'id'       => 'product_quick_checkout',
					'title'    => esc_html__( 'Quick Checkout', 'shopbuilder' ),
					'category' => 'product',
					'active'   => '',
					'package'  => $this->pro_package(),
					'fields'   => [],
				]
			),
			'product_tabs'                   => apply_filters(
				'rtsb/elements/product_tabs/options',
				[
					'id'         => 'product_tabs',
					'title'      => esc_html__( 'Product Tabs', 'shopbuilder' ),
					'base_class' => Single\ProductTabs::class,
					'category'   => 'product',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'advance_product_tabs'           => apply_filters(
				'rtsb/elements/advance_product_tabs/options',
				[
					'id'       => 'advance_product_tabs',
					'title'    => esc_html__( 'Advanced Product Tabs', 'shopbuilder' ),
					'category' => 'product',
					'active'   => '',
					'package'  => $this->pro_package(),
					'fields'   => [],
				]
			),
			'product_reviews'                => apply_filters(
				'rtsb/elements/product_reviews/options',
				[
					'id'         => 'product_reviews',
					'title'      => esc_html__( 'Product Reviews', 'shopbuilder' ),
					'base_class' => Single\ProductReviews::class,
					'category'   => 'product',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'upsells_product'                => apply_filters(
				'rtsb/elements/upsells_product/options',
				[
					'id'         => 'upsells_product',
					'title'      => esc_html__( 'Upsell - Default Layout', 'shopbuilder' ),
					'base_class' => Single\UpsellsProduct::class,
					'category'   => 'product',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'upsells_product_custom'         => apply_filters(
				'rtsb/elements/upsells_product_custom/options',
				[
					'id'         => 'upsells_product_custom',
					'title'      => esc_html__( 'Upsell - Custom Layouts', 'shopbuilder' ),
					'base_class' => Single\UpsellsProductsCustom::class,
					'category'   => 'product',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'upsells_products_slider_custom' => apply_filters(
				'rtsb/elements/upsells_products_slider_custom/options',
				[
					'id'         => 'upsells_products_slider_custom',
					'title'      => esc_html__( 'Upsell - Slider Layouts', 'shopbuilder' ),
					'base_class' => Single\UpsellsProductsSlider::class,
					'category'   => 'product',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'related_product'                => apply_filters(
				'rtsb/elements/related_product/options',
				[
					'id'         => 'related_product',
					'title'      => esc_html__( 'Related - Default Layout', 'shopbuilder' ),
					'base_class' => Single\RelatedProduct::class,
					'category'   => 'product',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'related_product_custom'         => apply_filters(
				'rtsb/elements/related_product_custom/options',
				[
					'id'         => 'related_product_custom',
					'title'      => esc_html__( 'Related - Custom Layouts', 'shopbuilder' ),
					'base_class' => Single\RelatedProductsCustom::class,
					'category'   => 'product',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'related_products_slider_custom' => apply_filters(
				'rtsb/elements/related_products_slider_custom/options',
				[
					'id'         => 'related_products_slider_custom',
					'title'      => esc_html__( 'Related -  Slider Layouts', 'shopbuilder' ),
					'base_class' => Single\RelatedProductsSlider::class,
					'category'   => 'product',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
		];

		return apply_filters( 'rtsb/core/elements/product_page/widget_list', $list );
	}

	/**
	 * Product Single Widget List.
	 *
	 * @return array
	 */
	protected function product_quick_view() {

		$list = [
			'qv_product_title'             => apply_filters(
				'rtsb/elements/qv_product_title/options',
				[
					'id'            => 'qv_product_title',
					'title'         => esc_html__( 'Product Title', 'shopbuilder' ),
					'base_class'    => Single\ProductTitle::class,
					'category'      => 'quick_view',
					'is_front_page' => 'quick_view',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'qv_product_description'       => apply_filters(
				'rtsb/elements/qv_product_description/options',
				[
					'id'            => 'qv_product_description',
					'title'         => esc_html__( 'Product Description', 'shopbuilder' ),
					'base_class'    => Single\ProductDescription::class,
					'category'      => 'quick_view',
					'is_front_page' => 'quick_view',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'qv_product_short_description' => apply_filters(
				'rtsb/elements/qv_product_short_description/options',
				[
					'id'            => 'qv_product_short_description',
					'title'         => esc_html__( 'Short Description', 'shopbuilder' ),
					'base_class'    => Single\ShortDescription::class,
					'category'      => 'quick_view',
					'is_front_page' => 'quick_view',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'qv_product_images'            => apply_filters(
				'rtsb/elements/qv_product_images/options',
				[
					'id'            => 'qv_product_images',
					'title'         => esc_html__( 'Product Images', 'shopbuilder' ),
					'base_class'    => Single\ProductImages::class,
					'category'      => 'quick_view',
					'is_front_page' => 'quick_view',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'qv_product_badges'            => apply_filters(
				'rtsb/elements/qv_product_badges/options',
				[
					'id'            => 'qv_product_badges',
					'title'         => esc_html__( 'Product Badges', 'shopbuilder' ),
					'base_class'    => Single\ProductBadges::class,
					'category'      => 'quick_view',
					'is_front_page' => 'quick_view',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'qv_product_additional_info'   => apply_filters(
				'rtsb/elements/qv_product_additional_info/options',
				[
					'id'            => 'qv_product_additional_info',
					'title'         => esc_html__( 'Additional Information', 'shopbuilder' ),
					'base_class'    => Single\AdditionalInformation::class,
					'category'      => 'quick_view',
					'is_front_page' => 'quick_view',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'qv_product_price'             => apply_filters(
				'rtsb/elements/qv_product_price/options',
				[
					'id'            => 'qv_product_price',
					'title'         => esc_html__( 'Product Price', 'shopbuilder' ),
					'base_class'    => Single\ProductPrice::class,
					'category'      => 'quick_view',
					'is_front_page' => 'quick_view',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'qv_product_meta'              => apply_filters(
				'rtsb/elements/qv_product_meta/options',
				[
					'id'            => 'qv_product_meta',
					'title'         => esc_html__( 'Product Meta', 'shopbuilder' ),
					'base_class'    => Single\ProductMeta::class,
					'category'      => 'quick_view',
					'is_front_page' => 'quick_view',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'qv_product_categories'        => apply_filters(
				'rtsb/elements/qv_product_categories/options',
				[
					'id'            => 'qv_product_categories',
					'title'         => esc_html__( 'Product Categories', 'shopbuilder' ),
					'base_class'    => Single\ProductCats::class,
					'category'      => 'quick_view',
					'is_front_page' => 'quick_view',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'qv_product_rating'            => apply_filters(
				'rtsb/elements/qv_product_rating/options',
				[
					'id'            => 'qv_product_rating',
					'title'         => esc_html__( 'Product Rating', 'shopbuilder' ),
					'base_class'    => Single\ProductRating::class,
					'category'      => 'quick_view',
					'is_front_page' => 'quick_view',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'qv_product_tags'              => apply_filters(
				'rtsb/elements/qv_product_tags/options',
				[
					'id'            => 'qv_product_tags',
					'title'         => esc_html__( 'Product Tags', 'shopbuilder' ),
					'base_class'    => Single\ProductTags::class,
					'category'      => 'quick_view',
					'is_front_page' => 'quick_view',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'qv_product_sku'               => apply_filters(
				'rtsb/elements/qv_product_sku/options',
				[
					'id'            => 'qv_product_sku',
					'title'         => esc_html__( 'Product SKU', 'shopbuilder' ),
					'base_class'    => Single\ProductSKU::class,
					'category'      => 'quick_view',
					'is_front_page' => 'quick_view',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'qv_product_stock'             => apply_filters(
				'rtsb/elements/qv_product_stock/options',
				[
					'id'            => 'qv_product_stock',
					'title'         => esc_html__( 'Product Stock', 'shopbuilder' ),
					'base_class'    => Single\ProductStock::class,
					'category'      => 'quick_view',
					'is_front_page' => 'quick_view',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'qv_actions_button'            => apply_filters(
				'rtsb/elements/qv_actions_button/options',
				[
					'id'            => 'qv_actions_button',
					'title'         => esc_html__( 'Action Buttons', 'shopbuilder' ),
					'base_class'    => Single\ActionsButton::class,
					'category'      => 'quick_view',
					'is_front_page' => 'quick_view',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'qv_product_add_to_cart'       => apply_filters(
				'rtsb/elements/qv_product_add_to_cart/options',
				[
					'id'            => 'qv_product_add_to_cart',
					'title'         => esc_html__( 'Add to Cart', 'shopbuilder' ),
					'base_class'    => Single\ProductAddToCart::class,
					'category'      => 'quick_view',
					'is_front_page' => 'quick_view',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'qv_product_sales_count'       => apply_filters(
				'rtsb/elements/qv_product_sales_count/options',
				[
					'id'            => 'qv_product_sales_count',
					'title'         => esc_html__( 'Sales Count', 'shopbuilder' ),
					'category'      => 'quick_view',
					'is_front_page' => 'quick_view',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'qv_social_share'              => apply_filters(
				'rtsb/settings/qv_social_share/options',
				[
					'id'            => 'qv_social_share',
					'title'         => esc_html__( 'Social Share', 'shopbuilder' ),
					'base_class'    => General\SocialShare::class,
					'active'        => 'on',
					'category'      => 'quick_view',
					'is_front_page' => 'quick_view',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'qv_product_quick_checkout'    => apply_filters(
				'rtsb/elements/qv_product_quick_checkout/options',
				[
					'id'            => 'qv_product_quick_checkout',
					'title'         => esc_html__( 'Quick Checkout', 'shopbuilder' ),
					'category'      => 'quick_view',
					'is_front_page' => 'quick_view',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),

			'qv_product_size_chart'        => apply_filters(
				'rtsb/elements/qv_product_size_chart/options',
				[
					'id'            => 'qv_product_size_chart',
					'title'         => esc_html__( 'Size Chart', 'shopbuilder' ),
					'category'      => 'quick_view',
					'is_front_page' => 'quick_view',
					'active'        => '',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),

		];

		return apply_filters( 'rtsb/core/elements/quick_view/widget_list', $list );
	}

	/**
	 * Product Archive Widget List.
	 *
	 * @return array
	 */
	protected function shop_archive_page_widget_list() {
		$list = [
			'archive_title'                   => apply_filters(
				'rtsb/elements/archive_title/options',
				[
					'id'         => 'archive_title',
					'title'      => esc_html__( 'Archive Title', 'shopbuilder' ),
					'base_class' => Archive\ArchiveTitle::class,
					'category'   => 'shop',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),

			'archive_description'             => apply_filters(
				'rtsb/elements/archive_description/options',
				[
					'id'         => 'archive_description',
					'title'      => esc_html__( 'Archive Description', 'shopbuilder' ),
					'base_class' => Archive\ArchiveDescription::class,
					'category'   => 'shop',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),

			'products_archive_catalog'        => apply_filters(
				'rtsb/elements/products_archive_catalog/options',
				[
					'id'         => 'products_archive_catalog',
					'title'      => esc_html__( 'Products - Default Layout', 'shopbuilder' ),
					'base_class' => Archive\ProductsArchive::class,
					'category'   => 'shop',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),

			'products_archive_catalog_custom' => apply_filters(
				'rtsb/elements/products_archive_catalog_custom/options',
				[
					'id'         => 'products_archive_catalog_custom',
					'title'      => esc_html__( 'Products - Custom Layouts', 'shopbuilder' ),
					'base_class' => Archive\ProductsArchiveCustom::class,
					'category'   => 'shop',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),

			'ajax_product_filters'            => apply_filters(
				'rtsb/elements/ajax_product_filters/options',
				[
					'id'       => 'ajax_product_filters',
					'title'    => esc_html__( 'Ajax Product Filters', 'shopbuilder' ),
					'category' => 'shop',
					'active'   => 'on',
					'package'  => $this->pro_package(),
					'fields'   => [],
				]
			),

			'archive_result_count'            => apply_filters(
				'rtsb/elements/archive_result_count/options',
				[
					'id'         => 'archive_result_count',
					'title'      => esc_html__( 'Result Count', 'shopbuilder' ),
					'base_class' => Archive\ArchiveResultCount::class,
					'category'   => 'shop',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'archive_products_ordering'       => apply_filters(
				'rtsb/elements/archive_products_ordering/options',
				[
					'id'         => 'archive_products_ordering',
					'title'      => esc_html__( 'Products Ordering', 'shopbuilder' ),
					'base_class' => Archive\ProductsOrdering::class,
					'category'   => 'shop',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'archive_product_mode'            => apply_filters(
				'rtsb/elements/archive_product_mode/options',
				[
					'id'         => 'archive_product_mode',
					'title'      => esc_html__( 'View Mode', 'shopbuilder' ),
					'base_class' => Archive\ArchiveProductMode::class,
					'category'   => 'shop',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
		];

		return apply_filters( 'rtsb/core/elements/shop/widget_list', $list );
	}

	/**
	 * Cart Page Widget List.
	 *
	 * @return array
	 */
	protected function cart_page_widget_list() {
		$list = [
			'cart_table'                => apply_filters(
				'rtsb/elements/cart_table/options',
				[
					'id'         => 'cart_table',
					'title'      => esc_html__( 'Cart Table', 'shopbuilder' ),
					'base_class' => Cart\CartTable::class,
					'category'   => 'cart',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'cart_totals'               => apply_filters(
				'rtsb/elements/cart_totals/options',
				[
					'id'         => 'cart_totals',
					'title'      => esc_html__( 'Cart Totals', 'shopbuilder' ),
					'base_class' => Cart\CartTotals::class,
					'category'   => 'cart',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'cart_coupon_form'          => apply_filters(
				'rtsb/elements/cart_coupon_form/options',
				[
					'id'       => 'cart_coupon_form',
					'title'    => esc_html__( 'Coupon Form', 'shopbuilder' ),
					'category' => 'cart',
					'active'   => 'on',
					'package'  => $this->pro_package(),
					'fields'   => [],
				]
			),
			'cross_sells'               => apply_filters(
				'rtsb/elements/cross_sells/options',
				[
					'id'         => 'cross_sells',
					'title'      => esc_html__( 'Cross Sell - Default Layout', 'shopbuilder' ),
					'base_class' => Cart\CrossSellProduct::class,
					'category'   => 'cart',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'cross_sells_custom'        => apply_filters(
				'rtsb/elements/cross_sells_custom/options',
				[
					'id'         => 'cross_sells_custom',
					'title'      => esc_html__( 'Cross Sell - Custom Layouts', 'shopbuilder' ),
					'base_class' => Cart\CrossSellsProductsCustom::class,
					'category'   => 'cart',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'cross_sells_custom_slider' => apply_filters(
				'rtsb/elements/cross_sells_custom_slider/options',
				[
					'id'         => 'cross_sells_custom_slider',
					'title'      => esc_html__( 'Cross Sell - Slider Layouts', 'shopbuilder' ),
					'base_class' => Cart\CrossSellsProductsSlider::class,
					'category'   => 'cart',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
		];

		return apply_filters( 'rtsb/core/elements/cart/widget_list', $list );
	}

	/**
	 * Checkout Page Widget List.
	 *
	 * @return array
	 */
	protected function checkout_page_widget_list() {
		$list = [
			'billing_form'               => apply_filters(
				'rtsb/elements/billing_form/options',
				[
					'id'         => 'billing_form',
					'title'      => esc_html__( 'Billing Form', 'shopbuilder' ),
					'base_class' => Checkout\BillingForm::class,
					'category'   => 'checkout',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'shipping_form'              => apply_filters(
				'rtsb/elements/shipping_form/options',
				[
					'id'         => 'shipping_form',
					'title'      => esc_html__( 'Shipping Form', 'shopbuilder' ),
					'base_class' => Checkout\ShippingForm::class,
					'category'   => 'checkout',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'order_notes'                => apply_filters(
				'rtsb/elements/order_notes/options',
				[
					'id'         => 'order_notes',
					'title'      => esc_html__( 'Order Notes', 'shopbuilder' ),
					'base_class' => Checkout\OrderNotes::class,
					'category'   => 'checkout',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'order_review'               => apply_filters(
				'rtsb/elements/order_review/options',
				[
					'id'         => 'order_review',
					'title'      => esc_html__( 'Order Review', 'shopbuilder' ),
					'base_class' => Checkout\OrderReview::class,
					'category'   => 'checkout',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'checkout_payment'           => apply_filters(
				'rtsb/elements/checkout_payment/options',
				[
					'id'         => 'checkout_payment',
					'title'      => esc_html__( 'Checkout Payment Method', 'shopbuilder' ),
					'base_class' => Checkout\CheckoutPayment::class,
					'category'   => 'checkout',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'coupon_form'                => apply_filters(
				'rtsb/elements/coupon_form/options',
				[
					'id'         => 'coupon_form',
					'title'      => esc_html__( 'Coupon Form', 'shopbuilder' ),
					'base_class' => Checkout\CouponForm::class,
					'category'   => 'checkout',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'checkout_login_form'        => apply_filters(
				'rtsb/elements/checkout_login_form/options',
				[
					'id'         => 'checkout_login_form',
					'title'      => esc_html__( 'Login Form', 'shopbuilder' ),
					'base_class' => Checkout\CheckoutLoginForm::class,
					'category'   => 'checkout',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'shipping_method'            => apply_filters(
				'rtsb/elements/shipping_method/options',
				[
					'id'         => 'shipping_method',
					'title'      => esc_html__( 'Shipping Method', 'shopbuilder' ),
					'base_class' => Checkout\ShippingMethod::class,
					'category'   => 'checkout',
					'active'     => 'on',
					'package'    => 'free',
					'fields'     => [],
				]
			),
			'multi_step_checkout_widget' => apply_filters(
				'rtsb/elements/multi_step_checkout_widget/options',
				[
					'id'         => 'multi_step_checkout_widget',
					'title'      => esc_html__( 'Multi Step Checkout', 'shopbuilder' ),
					'base_class' => CheckoutPro\MultiStepCheckout::class,
					'category'   => 'checkout',
					'active'     => '',
					'package'    => $this->pro_package(),
					'fields'     => [],
				]
			),

		];

		return apply_filters( 'rtsb/core/elements/checkout/widget_list', $list );
	}

	/**
	 * Checkout Page Widget List.
	 *
	 * @return array
	 */
	protected function thankyou_page_widget_list() {
		return [
			'order_received_text'    => apply_filters(
				'rtsb/elements/order_received_text/options',
				[
					'id'            => 'order_received_text',
					'title'         => esc_html__( 'Order Received Text', 'shopbuilder' ),
					'category'      => 'thank_you',
					'is_front_page' => 'thank_you',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'order_details_summary'  => apply_filters(
				'rtsb/elements/order_details_summary/options',
				[
					'id'            => 'order_details_summary',
					'title'         => esc_html__( 'Order Details Summary', 'shopbuilder' ),
					'category'      => 'thank_you',
					'is_front_page' => 'thank_you',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'order_details_table'    => apply_filters(
				'rtsb/elements/order_details_table/options',
				[
					'id'            => 'order_details_table',
					'title'         => esc_html__( 'Order Details Table', 'shopbuilder' ),
					'category'      => 'thank_you',
					'is_front_page' => 'thank_you',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'downloadable_products'  => apply_filters(
				'rtsb/elements/downloadable_products/options',
				[
					'id'            => 'downloadable_products',
					'title'         => esc_html__( 'Downloadable Products', 'shopbuilder' ),
					'category'      => 'thank_you',
					'is_front_page' => 'thank_you',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'order_billing_address'  => apply_filters(
				'rtsb/elements/order_billing_address/options',
				[
					'id'            => 'order_billing_address',
					'title'         => esc_html__( 'Order Billing Address', 'shopbuilder' ),
					'category'      => 'thank_you',
					'is_front_page' => 'thank_you',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'order_shipping_address' => apply_filters(
				'rtsb/elements/order_shipping_address/options',
				[
					'id'            => 'order_shipping_address',
					'title'         => esc_html__( 'Order Shipping Address', 'shopbuilder' ),
					'category'      => 'thank_you',
					'is_front_page' => 'thank_you',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
		];
	}

	/**
	 * Checkout Page Widget List.
	 *
	 * @return array
	 */
	protected function my_account_page_widget_list() {
		$widgets = [
			'account_navigation_edit_shipping'     => apply_filters(
				'rtsb/elements/account_navigation_edit_shipping/options',
				[
					'id'            => 'account_navigation_edit_shipping',
					'title'         => esc_html__( 'Account Navigation', 'shopbuilder' ),
					'category'      => 'myaccount_dashboard',
					'is_front_page' => 'all_myaccount_dashboard_inner',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'account_dashboard'                    => apply_filters(
				'rtsb/elements/account_dashboard/options',
				[
					'id'            => 'account_dashboard',
					'title'         => esc_html__( 'Account Dashboard', 'shopbuilder' ),
					'category'      => 'myaccount_dashboard',
					'is_front_page' => 'all_myaccount_dashboard_inner',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'account_orders'                       => apply_filters(
				'rtsb/elements/account_orders/options',
				[
					'id'            => 'account_orders',
					'title'         => esc_html__( 'Account Orders', 'shopbuilder' ),
					'category'      => 'myaccount_dashboard',
					'is_front_page' => 'all_myaccount_dashboard_inner',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'account_downloads'                    => apply_filters(
				'rtsb/elements/account_downloads/options',
				[
					'id'            => 'account_downloads',
					'title'         => esc_html__( 'Account Downloads', 'shopbuilder' ),
					'category'      => 'myaccount_dashboard',
					'is_front_page' => 'all_myaccount_dashboard_inner',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'account_billing_address'              => apply_filters(
				'rtsb/elements/account_billing_address/options',
				[
					'id'            => 'account_billing_address',
					'title'         => esc_html__( 'Account Billing Address', 'shopbuilder' ),
					'category'      => 'myaccount_dashboard',
					'is_front_page' => 'all_myaccount_dashboard_inner',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'account_shipping_address'             => apply_filters(
				'rtsb/elements/account_shipping_address/options',
				[
					'id'            => 'account_shipping_address',
					'title'         => esc_html__( 'Account Shipping Address', 'shopbuilder' ),
					'category'      => 'myaccount_dashboard',
					'is_front_page' => 'all_myaccount_dashboard_inner',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'account_address_description'          => apply_filters(
				'rtsb/elements/account_address_description/options',
				[
					'id'            => 'account_address_description',
					'title'         => esc_html__( 'Account Address Description', 'shopbuilder' ),
					'category'      => 'myaccount_dashboard',
					'is_front_page' => 'all_myaccount_dashboard_inner',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'account_details'                      => apply_filters(
				'rtsb/elements/account_details/options',
				[
					'id'            => 'account_details',
					'title'         => esc_html__( 'Account Edit / Details', 'shopbuilder' ),
					'category'      => 'myaccount_dashboard',
					'is_front_page' => 'all_myaccount_dashboard_inner',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'account_order_status'                 => apply_filters(
				'rtsb/elements/account_order_status/options',
				[
					'id'            => 'account_order_status',
					'title'         => esc_html__( 'Account Order Status', 'shopbuilder' ),
					'category'      => 'myaccount_dashboard',
					'is_front_page' => 'all_myaccount_dashboard_inner',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'account_order_details_download'       => apply_filters(
				'rtsb/elements/account_order_details_download/options',
				[
					'id'            => 'account_order_details_download',
					'title'         => esc_html__( 'Account Order Download', 'shopbuilder' ),
					'category'      => 'myaccount_dashboard',
					'is_front_page' => 'all_myaccount_dashboard_inner',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'account_order_details_note'           => apply_filters(
				'rtsb/elements/account_order_details_note/options',
				[
					'id'            => 'account_order_details_note',
					'title'         => esc_html__( 'Account Order Note', 'shopbuilder' ),
					'category'      => 'myaccount_dashboard',
					'is_front_page' => 'all_myaccount_dashboard_inner',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'account_order_details_table'          => apply_filters(
				'rtsb/elements/account_order_details_table/options',
				[
					'id'            => 'account_order_details_table',
					'title'         => esc_html__( 'Account Order Table', 'shopbuilder' ),
					'category'      => 'myaccount_dashboard',
					'is_front_page' => 'all_myaccount_dashboard_inner',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'account_order_details_order_again'    => apply_filters(
				'rtsb/elements/account_order_details_order_again/options',
				[
					'id'            => 'account_order_details_order_again',
					'title'         => esc_html__( 'Account Order Again Button', 'shopbuilder' ),
					'category'      => 'myaccount_dashboard',
					'is_front_page' => 'all_myaccount_dashboard_inner',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'account_order_details_order_shipping' => apply_filters(
				'rtsb/elements/account_order_details_order_shipping/options',
				[
					'id'            => 'account_order_details_order_shipping',
					'title'         => esc_html__( 'Account Order Shipping', 'shopbuilder' ),
					'category'      => 'myaccount_dashboard',
					'is_front_page' => 'all_myaccount_dashboard_inner',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'account_order_details_order_billing'  => apply_filters(
				'rtsb/elements/account_order_details_order_billing/options',
				[
					'id'            => 'account_order_details_order_billing',
					'title'         => esc_html__( 'Account Order Billing', 'shopbuilder' ),
					'category'      => 'myaccount_dashboard',
					'is_front_page' => 'all_myaccount_dashboard_inner',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'account_edit_billing_address'         => apply_filters(
				'rtsb/elements/account_edit_billing_address/options',
				[
					'id'            => 'account_edit_billing_address',
					'title'         => esc_html__( 'Edit Billing Address', 'shopbuilder' ),
					'category'      => 'myaccount_dashboard',
					'is_front_page' => 'all_myaccount_dashboard_inner',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'account_edit_shipping_address'        => apply_filters(
				'rtsb/elements/account_edit_shipping_address/options',
				[
					'id'            => 'account_edit_shipping_address',
					'title'         => esc_html__( 'Edit Shipping Address', 'shopbuilder' ),
					'category'      => 'myaccount_dashboard',
					'is_front_page' => 'all_myaccount_dashboard_inner',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
		];

		return apply_filters( 'rtsb/core/elements/my_account/widget_list', $widgets );
	}

	/***
	 * @return array
	 */
	protected function myaccount_auth_page_widget_list() {
		return [
			'account_login'             => apply_filters(
				'rtsb/elements/account_login/options',
				[
					'id'            => 'account_login',
					'title'         => esc_html__( 'Login Register Form', 'shopbuilder' ),
					'category'      => 'others_widget',
					'is_front_page' => 'myaccount_auth',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'account_login_form'        => apply_filters(
				'rtsb/elements/account_login_form/options',
				[
					'id'            => 'account_login_form',
					'title'         => esc_html__( 'Login Form', 'shopbuilder' ),
					'category'      => 'others_widget',
					'is_front_page' => 'myaccount_auth',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'account_registration_form' => apply_filters(
				'rtsb/elements/account_registration_form/options',
				[
					'id'            => 'account_registration_form',
					'title'         => esc_html__( 'Registration Form', 'shopbuilder' ),
					'category'      => 'others_widget',
					'is_front_page' => 'myaccount_auth',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
			'account_lost_password'     => apply_filters(
				'rtsb/elements/account_lost_password/options',
				[
					'id'            => 'account_lost_password',
					'title'         => esc_html__( 'Lost Password Form', 'shopbuilder' ),
					'category'      => 'others_widget',
					'is_front_page' => 'myaccount_auth',
					'active'        => 'on',
					'package'       => $this->pro_package(),
					'fields'        => [],
				]
			),
		];
	}
}
