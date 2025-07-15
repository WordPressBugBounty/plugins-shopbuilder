<?php
/**
 * Main FilterHooks class.
 *
 * @package RadiusTheme\SB
 */

namespace RadiusTheme\SB\Modules\VariationGallery;

use RadiusTheme\SB\Helpers\BuilderFns;
use RadiusTheme\SB\Helpers\Cache;
use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit();

/**
 * Main FilterHooks class.
 */
class GalleryFrontEnd {

	/**
	 * Singleton Trait.
	 */
	use SingletonTrait;

	/**
	 * Module Class Constructor.
	 */
	private function __construct() {
		add_filter( 'woocommerce_locate_template', [ $this, 'override_templates' ], 10, 2 );
	}

	/**
	 * @param string $template template.
	 * @param string $template_name template name.
	 * @return mixed|null
	 */
	public function override_templates( $template, $template_name ) {
		// List of templates you want to override.
		if ( 'single-product/product-image.php' === $template_name ) {
			$temp            = 'variation-gallery/product-image';
			$custom_template = Fns::locate_template( $temp );
			if ( file_exists( $custom_template ) ) {
				return $custom_template;
			}
		} elseif ( 'single-product/product-thumbnails.php' === $template_name ) {
			$temp            = 'variation-gallery/product-thumbnails';
			$custom_template = Fns::locate_template( $temp );
			if ( file_exists( $custom_template ) ) {
				return $custom_template;
			}
		}

		return $template;
	}
}
