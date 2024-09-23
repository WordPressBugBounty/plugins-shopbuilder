<?php
/**
 * Add to compare list template
 *
 * @author  RadiusTheme
 * @package RTSB_ABSPATH\Templates\Compare\Button
 * @version 1.0.0
 */


/**
 * Template variables:
 *
 * @var $exists                    bool Whether current product is already in Compare list
 * @var $product_id                int Current product id
 * @var $parent_product_id         int Parent product id
 * @var $button_text               string Button text
 * @var $show_button_text               string Button text
 * @var $icon_html                 string Icon for Add to Compare button
 * @var $classes                   array Classed for Add to Compare button
 * @var $left_text                 string Html allowed
 * @var $right_text                string Html allowed
 */

defined( 'ABSPATH' ) || die( 'Keep Silent' );

use RadiusTheme\SB\Helpers\Fns;

?>

<a class="rtsb-compare-btn tipsy <?php echo esc_attr( implode( ' ', $classes ) ) ?>" rel="nofollow"
	data-product_id="<?php echo esc_attr( $product_id ); ?>"
	title="<?php echo esc_attr( $button_text ); ?>"
	data-title="<?php echo esc_attr( $button_text ); ?>"
	data-original-product_id="<?php echo esc_attr( $parent_product_id ); ?>" href="#" aria-label="<?php echo esc_attr__('Compare', 'shopbuilder');?>">
	<span class="icon">
		<?php echo wp_kses( $left_text, Fns::get_kses_array() ); ?>
		<?php Fns::print_html( $icon_html ); ?>
		<?php echo wp_kses( $right_text, Fns::get_kses_array() ); ?>
	</span>
    <span class="icon-with-text button-text">
            <?php Fns::print_html( $button_text, true ); ?>
    </span>
</a>
