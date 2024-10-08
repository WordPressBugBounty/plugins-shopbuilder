<?php

namespace RadiusTheme\SB\Modules\Compare;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Traits\SingletonTrait;
use WP_Post;

class CompareAdmin {

	use SingletonTrait;

	public function __construct() {
		add_filter( 'display_post_states', [ $this, 'add_display_post_states' ], 10, 2 );
	}

	/**
	 * Add a post display state for special WC pages in the page list table.
	 *
	 * @param array   $post_states An array of post display states.
	 * @param WP_Post $post        The current post object.
	 */
	public function add_display_post_states( $post_states, $post ) {
        if ( (int) Fns::get_option( 'modules', 'compare', 'page' ) === $post->ID ) {
            $post_states['rtsb_page_for_compare'] = esc_html__( 'Compare Page', 'shopbuilder' );
        }

		return $post_states;
	}
}