<?php
/**
 * Themes Page
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

$themes = [
	[
		'theme_name'    => 'EasyShop - Woocommerce WordPress Theme',
		'thumbnail'     => esc_url( rtsb()->get_assets_uri( 'images/admin-themes/easyshop-theme.jpg' ) ),
		'purchase_link' => 'https://shopbuilderwp.com/theme/easyshop/',
		'demo_link'     => 'https://www.radiustheme.com/demo/shopbuilder/easyshop/',
	],
	[
		'theme_name'    => 'Decor - Furniture Store Woocommerce WordPress Theme',
		'thumbnail'     => esc_url( rtsb()->get_assets_uri( 'images/admin-themes/decor-theme.jpg' ) ),
		'purchase_link' => 'https://shopbuilderwp.com/theme/decor/',
		'demo_link'     => 'https://www.radiustheme.com/demo/shopbuilder/decor/',
	],
	[
		'theme_name'    => 'Eleganz– Fashion Woocommerce WordPress Theme',
		'thumbnail'     => esc_url( rtsb()->get_assets_uri( 'images/admin-themes/eleganz-theme.jpg' ) ),
		'purchase_link' => 'https://shopbuilderwp.com/theme/eleganz/',
		'demo_link'     => 'https://www.radiustheme.com/demo/shopbuilder/eleganz/',
	],
	[
		'theme_name'    => 'PartShop – Auto Parts Woocommerce WordPress Theme',
		'thumbnail'     => esc_url( rtsb()->get_assets_uri( 'images/admin-themes/partshop-theme.jpg' ) ),
		'purchase_link' => 'https://shopbuilderwp.com/theme/partshop/',
		'demo_link'     => 'https://www.radiustheme.com/demo/shopbuilder/partshop/',
	],
	[
		'theme_name'    => 'Markeet - Woocommerce WordPress Theme',
		'thumbnail'     => esc_url( rtsb()->get_assets_uri( 'images/admin-themes/markeet-theme.jpg' ) ),
		'purchase_link' => 'https://shopbuilderwp.com/theme/markeet/',
		'demo_link'     => 'https://www.radiustheme.com/demo/shopbuilder/markeet/',
	],
	[
		'theme_name' => 'More Themes are Coming Soon',
		'thumbnail'  => esc_url( rtsb()->get_assets_uri( 'images/admin-themes/coming-soon.jpg' ) ),
	],
];

?>
<div class="wrap rtsb-themes-wrap">
	<div class="rtsb-settings-page-wrapper">
		<div class="rtsb-header-area">
			<div class="rtsb-header-logo-wrap">
				<img src="<?php echo esc_url( rtsb()->get_assets_uri( 'images/icon/ShopBuilder-Logo.svg' ) ); ?>" alt="ShopBuilder" loading="lazy">
			</div>
			<div class="rtsb-header-title-wrap">
				<h1 class="rtsb-title"><?php echo esc_html__( 'ShopBuilder Themes', 'shopbuilder' ); ?></h1>
			</div>
		</div>
		<div class="rtsb-settings-tabs-wrap">
			<div class="rtsb-settings-tab-content">
				<div class="rtsb-ss-wrap">
					<div class="rtsb-ss-header-area">
						<div class="rtsb-ss-header">
							<h2 class="rtsb-title"><?php echo esc_html__( 'WooCommerce Themes with ShopBuilder', 'shopbuilder' ); ?></h2>
							<p class="rtsb-description"><?php echo esc_html__( 'Discover our beautiful WooCommerce themes, expertly crafted for easy setup and customization with the Shopbuilder plugin.', 'shopbuilder' ); ?></p>
						</div>
					</div>

					<div class="rtsb-ss-item-list-wrap">
						<div class="rtsb-themes-container">
							<?php
							foreach ( $themes as $theme ) {
								?>
								<div class="rtsb-ss-item">
									<div class="rtsb-ss-item-box">
										<div class="rtsb-ss-item-thumbnail">
											<img src="<?php echo esc_url( $theme['thumbnail'] ); ?>" alt="ShopBuilder">
										</div>
										<div class="rtsb-ss-item-content">
											<h3 class="rtsb-ss-item-title"><?php echo esc_html( $theme['theme_name'] ); ?></h3>
											<div class="rtsb-ss-item-buttons">
												<?php
												if ( ! empty( $theme['demo_link'] ) || ! empty( $theme['purchase_link'] ) ) {
													?>
													<a href="<?php echo esc_url( $theme['demo_link'] ); ?>" class="rtsb-button rtsb-button-demo" target="_blank"><?php echo esc_html__( 'View Demo', 'shopbuilder' ); ?></a>
													<a href="<?php echo esc_url( $theme['purchase_link'] ); ?>" class="rtsb-button rtsb-button-purchase" target="_blank"><?php echo esc_html__( 'Buy Now', 'shopbuilder' ); ?></a>
													<?php
												}
												?>
											</div>
										</div>
									</div>
								</div>
								<?php
							}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
