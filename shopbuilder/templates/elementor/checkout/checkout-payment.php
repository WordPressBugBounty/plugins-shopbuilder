<?php
/**
 * Template variables:
 *
 * @var $controllers  array Widgets/Addons Settings
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}
?>
<div class="rtsb-checkout-payment">
	<?php
	if ( ! empty( WC()->cart ) && ! WC()->cart->is_empty() ) {
		woocommerce_checkout_payment();
	}
	?>
</div>

