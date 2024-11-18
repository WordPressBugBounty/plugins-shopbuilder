<?php
/**
 * Special Offer.
 *
 * @package RadiusTheme\SB
 */

namespace RadiusTheme\SB\Controllers\Admin\Notice;

// Do not allow directly accessing this file.
use RadiusTheme\SB\Abstracts\Discount;
use RadiusTheme\SB\Traits\SingletonTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Black Friday Offer.
 */
class BFDiscount extends Discount {

	/**
	 * Singleton Trait.
	 */
	use SingletonTrait;

	/**
	 * @return array
	 */
	public function the_options(): array {
		return [
			'option_name'    => 'woobundle_black_friday_offer_2024',
			'global_check'   => 'woobundle_notice' ,
			'plugin_name'    => 'ShopBuilder',
			'notice_for'     => 'WooCommerce Bundle [Black Friday <img style="width: 40px;position: relative;" src="' . rtsb()->get_assets_uri( 'images/deal.gif' ) . '" />]',
			'download_link'  => 'https://www.radiustheme.com/downloads/woocommerce-bundle/',
			'start_date'     => '18 November 2024',
			'end_date'       => '15 January 2025',
			'notice_message' => 'Enjoy savings of up to 50% with our <b>ShopBuilder Elementor Addon</b>, <b>Variation Swatches</b>, <b>Variation Gallery</b>, and <b>Themes</b>!',
		];
	}
}
