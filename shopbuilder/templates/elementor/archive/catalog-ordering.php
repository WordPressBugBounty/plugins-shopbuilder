<?php
/**
 * Template variables:
 *
 * @var $controllers  array Widgets/Addons Settings
 */

use RadiusTheme\SB\Helpers\Fns;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

$ajax_filter = Fns::product_filters_has_ajax( apply_filters( 'rtsb/builder/set/current/page/type', '' ) );
$class       = rtsb()->has_pro() && $ajax_filter ? ' has-ajax-filter' : ' no-ajax-filter';
?>
<div class="rtsb-archive-catalog-ordering<?php echo esc_attr( $class ); ?>">
	<?php woocommerce_catalog_ordering(); ?>
</div>
