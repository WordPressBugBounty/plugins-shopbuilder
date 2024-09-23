<?php
/**
 * Template variables:
 *
 * @var $controllers  array settings as array
 * @var $is_builder  bool
 */

use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Elementor\Helper\RenderHelpers;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

global $product;

if ( empty( $product ) ) {
	return;
}

?>

<div class="rtsb-product-onsale<?php echo esc_html( $controllers['visibility'] ); ?>">
	<?php
	if ( 'preset-default' === $controllers['custom_badge_preset'] ) {
		woocommerce_show_product_sale_flash();
	} else {
		$badge_class  = RenderHelpers::badge_class( $controllers['custom_badge_preset'] );
		$badge_module = ! empty( $controllers['enable_badges_module'] );
		$p_badge      = Fns::is_module_active( 'product_badges' ) && $badge_module ? 'rtsb_yes' : Fns::get_sale_badge(
			$controllers['sale_badges_type'],
			$controllers['sale_badges_text'],
			$controllers['stock_badges_text']
		);
		?>
		<div class="rtsb-promotion">
			<?php Fns::get_badge_html( $p_badge, $badge_class ); ?>
		</div>
		<?php
	}
	?>
</div>
