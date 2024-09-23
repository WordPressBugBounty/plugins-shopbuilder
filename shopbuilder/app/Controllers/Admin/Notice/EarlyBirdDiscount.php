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
class EarlyBirdDiscount extends Discount {

	/**
	 * Singleton Trait.
	 */
	use SingletonTrait;

	/**
	 * @return array
	 */
	public function the_options(): array {
		return [
			'option_name'    => 'woobundle-release-notice',
			'global_check'   => isset( $GLOBALS['woobundle_release_notice'] ),
			'plugin_name'    => 'ShopBuilder',
			'notice_for'     => 'Elementor Addons Pro is now available!!',
			'download_link'  => 'https://www.radiustheme.com/downloads/woocommerce-bundle/',
			'start_date'     => '10 December 2023',
			'end_date'       => '30 January 2025',
			'notice_message' => 'Acquire our WooCommerce bundled <b>ShopBuilder Elementor Addon</b>, <b>Variation Swatches</b>, and <b>Variation Gallery</b> plugin to enjoy <b>savings of up to 30%!</b>',
		];
	}
}
