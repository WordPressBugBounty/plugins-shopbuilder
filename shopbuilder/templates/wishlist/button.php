<?php
/**
 * Add to wishlist template
 *
 * @author  RadiusTheme
 * @package RTSB_ABSPATH\Templates\Wishlist\Button
 * @version 1.0.0
 */


/**
 * Template variables:
 *
 * @var $exists                    bool Whether current product is already in wishlist
 * @var $product_id                int Current product id
 * @var $parent_product_id         int Parent product id
 * @var $button_text               string Button text
 * @var $icon_html                 string Icon for Add to Wishlist button
 * @var $show_button_text                 string Icon for Add to Wishlist button
 * @var $classes                   string Classed for Add to Wishlist button
 * @var $left_text                 string Html allowed
 * @var $right_text                string Html allowed
 */

use RadiusTheme\SB\Helpers\Fns;

defined( 'ABSPATH' ) || die( 'Keep Silent' );

?>

<a class="rtsb-wishlist-btn tipsy <?php echo esc_attr( implode( ' ', $classes ) ); ?>" rel="nofollow"
   data-product_id="<?php echo esc_attr( $product_id ); ?>"
   title="<?php echo esc_attr( $button_text ); ?>"
   data-title="<?php echo esc_attr( $button_text ); ?>"
   data-original-product_id="<?php echo esc_attr( $parent_product_id ); ?>"
   href="#" aria-label="<?php echo esc_attr__( 'Wishlist', 'shopbuilder' ); ?>">

	<span class="icon">
		<?php echo wp_kses( $left_text, Fns::get_kses_array() ); ?>
		<?php Fns::print_html( $icon_html, true ); ?>
		<?php echo wp_kses( $right_text, Fns::get_kses_array() ); ?>
	</span>
    <span class="button-text">
        <?php echo esc_attr( $button_text ); ?>
    </span>

</a>
