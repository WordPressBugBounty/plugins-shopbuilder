<?php

/**
 * Add to wishlist template
 *
 * @author  RadiusTheme
 * @package RTSB_ABSPATH\Templates\Wishlist\counter
 * @version 1.0.0
 */


/**
 * Template variables:
 *
 * @var $page_url                    string Wishlist page url
 * @var $item_count                  int Total number of product of wishlist
 */

defined( 'ABSPATH' ) || die( 'Keep Silent' );
?>
<a href="<?php echo esc_url( $page_url ); ?>" class="rtsb-wishlist-counter-wrap">
    <span class="rtsb-wishlist-counter"><?php echo esc_html( $item_count ); ?></span>
</a>