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
<div class="rtsb-archive-result-count">
	<?php
	$args = [
		'total'    => wc_get_loop_prop( 'total' ),
		'per_page' => wc_get_loop_prop( 'per_page' ),
		'current'  => wc_get_loop_prop( 'current_page' ),
	];

	wc_get_template( 'loop/result-count.php', $args );
	?>
</div>
