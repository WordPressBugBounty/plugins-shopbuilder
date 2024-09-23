<?php
/**
 * Quick view button
 *
 * @author  RadiusTheme
 * @package ShopBuilder QuickView
 * @version 1.0.0
 */


/**
 * Template variables:
 *
 * @var $product_id                int Current product id
 * @var $button_text               string Button text
 * @var $icon_html                 string Icon for Add to Wishlist button
 * @var $classes                   string Classed for Add to Wishlist button
 * @var $left_text                 string Html allowed
 * @var $right_text                string Html allowed
 */

defined( 'ABSPATH' ) || die( 'Keep Silent' );

use RadiusTheme\SB\Helpers\Fns;
?>

<a class="rtsb-quick-view-btn tipsy <?php echo esc_attr( implode( ' ', $classes ) ); ?>" rel="nofollow"
	data-product_id="<?php echo esc_attr( $product_id ); ?>"
	title="<?php echo esc_attr( $button_text ); ?>"
	data-title="<?php echo esc_attr( $button_text ); ?>" href="#" aria-label="<?php echo esc_attr__( 'Quick view', 'shopbuilder' ); ?>" >
	<span class="icon">
		<?php echo wp_kses( $left_text, Fns::get_kses_array() ); ?>
		<?php Fns::print_html( $icon_html ); ?>
		<?php echo wp_kses( $right_text, Fns::get_kses_array() ); ?>
	</span>
	<span class="button-text">
		<?php echo esc_attr( $button_text ); ?>
	</span>
</a>
