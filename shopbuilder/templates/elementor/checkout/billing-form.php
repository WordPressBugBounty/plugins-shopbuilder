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
$wrapper_class = ! empty( $controllers['fields_width_100'] ) ? 'rtsb-form-fields-width-100' : '';
?>
<div class="rtsb-form-billing <?php echo esc_attr( $wrapper_class ); ?>">
	<?php
		wc_get_template(
			'checkout/form-billing.php',
			[
				'checkout' => WC()->checkout(),
			]
		);
		?>
</div>

