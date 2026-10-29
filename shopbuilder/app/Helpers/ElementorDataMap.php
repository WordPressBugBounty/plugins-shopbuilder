<?php

namespace RadiusTheme\SB\Helpers;

use RadiusTheme\SB\Traits\SingletonTrait;

/**
 * Reads Elementor document data and answers questions about the elements a
 * document contains.
 */
class ElementorDataMap {
	/**
	 * Singleton
	 */
	use SingletonTrait;

	/**
	 * @var array
	 */
	private static $cache = [];

	/***
	 * Elementor Data key
	 */
	private const ELEMENTOR_DATA_KEY = '_elementor_data';
	/***
	 * @var array
	 */
	private array $eldata = [];

	/***
	 * Decoded `_elementor_data` for a post.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return array|null
	 */
	public function get_elementor_data( int $post_id ): ?array {
		$data = get_post_meta( $post_id, self::ELEMENTOR_DATA_KEY, true );

		return json_decode( $data, true );
	}

	/***
	 * Every instance of one widget type within a document.
	 *
	 * @param string     $widget_name Widget type name.
	 * @param array|null $data        Element tree, or null to read it from $post_id.
	 * @param int|null   $post_id     Post ID to read the element tree from.
	 *
	 * @return array
	 */
	public function get_widget_data( string $widget_name, ?array $data = null, ?int $post_id = null ): array {
		$cache_key = 'get_widget_data_' . $widget_name . '_' . $post_id;
		if ( isset( self::$cache[ $cache_key ] ) ) {
			return self::$cache[ $cache_key ];
		}
		if ( ! $data && $post_id ) {
			$data = $this->get_elementor_data( $post_id );
		}

		if ( empty( $data ) || ! is_array( $data ) ) {
			return [];
		}

		$this->search_el( $data, $widget_name );
		self::$cache[ $cache_key ] = $this->eldata;
		return $this->eldata;
	}

	/**
	 * Every distinct Elementor widget type used by a document.
	 *
	 * Reads `_elementor_data` once per post id and memoises the result for the
	 * rest of the request: the optimizer asks for this while building both the
	 * CSS and the JS bundle, and neither should re-parse the document.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return array List of widget type names, e.g. `rtsb-advance-product-tabs`.
	 */
	public function get_widget_types( int $post_id ): array {
		$cache_key = 'get_widget_types_' . $post_id;

		if ( isset( self::$cache[ $cache_key ] ) ) {
			return self::$cache[ $cache_key ];
		}

		$data  = $this->get_elementor_data( $post_id );
		$found = [];

		if ( ! empty( $data ) && is_array( $data ) ) {
			$this->collect_widget_types( $data, $found );
		}

		self::$cache[ $cache_key ] = array_values( array_unique( $found ) );

		return self::$cache[ $cache_key ];
	}

	/**
	 * Walks an Elementor element tree collecting widget type names.
	 *
	 * Unlike search_el() this descends into every node, because a container can
	 * hold both widgets and further containers.
	 *
	 * @param array $data  Element tree.
	 * @param array $found Collected widget types, by reference.
	 *
	 * @return void
	 */
	private function collect_widget_types( array $data, array &$found ): void {
		foreach ( $data as $value ) {
			if ( ! is_array( $value ) ) {
				continue;
			}

			if ( isset( $value['elType'] ) && 'widget' === $value['elType'] && ! empty( $value['widgetType'] ) ) {
				$found[] = $value['widgetType'];
			}

			if ( ! empty( $value['elements'] ) && is_array( $value['elements'] ) ) {
				$this->collect_widget_types( $value['elements'], $found );
			}
		}
	}

	/***
	 * Collects matching widgets from an element tree into $this->eldata.
	 *
	 * @param array  $data Element tree.
	 * @param string $name Widget type name to match.
	 *
	 * @return void
	 */
	private function search_el( array $data, string $name ): void {
		foreach ( $data as $value ) {
			if ( ! is_array( $value ) ) {
				continue;
			}

			if ( ! empty( $value['elements'] ) && is_array( $value['elements'] ) ) {
				$this->search_el( $value['elements'], $name );
			} else {
				if ( 'widget' === $value['elType'] && $value['widgetType'] === $name ) {
					$this->eldata[] = $value;
				}
			}
		}
	}
}
