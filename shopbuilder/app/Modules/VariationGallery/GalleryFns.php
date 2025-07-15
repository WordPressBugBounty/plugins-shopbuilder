<?php
/**
 * Sticky add-to-cart Functions Class.
 *
 * @package Rse\SB
 */

namespace RadiusTheme\SB\Modules\VariationGallery;

use RadiusTheme\SB\Helpers\Fns;

defined( 'ABSPATH' ) || exit();

/**
 * Sticky add-to-cart Functions Class.
 */
class GalleryFns {
	/**
	 * @param string $key Default Attribute.
	 * @return array|string
	 */
	public static function get_options( $key = null ) {
		$options = Fns::get_options( 'modules', 'variation_gallery' );
		if ( $key && isset( $options[ $key ] ) ) {
			return $options[ $key ];
		}
		return $options;
	}
}
