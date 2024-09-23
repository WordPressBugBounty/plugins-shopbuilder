<?php

namespace RadiusTheme\SB\Models\Base;

defined( 'ABSPATH' ) || exit;

use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Models\DataModel;

abstract class ListModel {

	protected $list_id;

	protected $title;
	protected $short_title;
	protected $description;
	private $full_list     = [];
	private $active_list   = [];
	private $inactive_list = [];
	private $db_options    = [];
	protected $categories  = [];

	public function __construct() {
		$this->db_options = DataModel::source()->get_option( $this->list_id, [] );
		$raw_list         = apply_filters( 'rtsb/' . $this->list_id . '/list', $this->raw_list() );

		if ( ! empty( $raw_list ) ) {
			foreach ( $raw_list as $name => $item ) {

				if ( ! empty( $item['package'] ) && $item['package'] === 'pro-disabled' ) {
					$item['fields']               = [];
					$item['active']               = false;
					$this->full_list[ $name ]     = $item;
					$this->inactive_list[ $name ] = $item;
					continue;
				}

				if ( ! isset( $this->db_options[ $name ]['active'] ) && ! empty( $item['active'] ) ) {
					$item['active'] = 'on';
				} else {
					$item['active'] = isset( $this->db_options[ $name ]['active'] ) && $this->db_options[ $name ]['active'] === 'on' ? 'on' : '';
				}

				if ( ! empty( $item['fields'] ) ) {
					foreach ( $item['fields'] as $field_key => $field ) {
						$the_value = $this->db_options[ $name ][ $field_key ] ?? ( $item['fields'][ $field_key ]['value'] ?? '' );
						if ( 'repeaters' === $field['type'] && empty( $the_value ) && ! empty( $field['defaults'] ) ) {
							$repeater_defaults = array_values( $field['defaults'] );
							$the_value         = wp_json_encode( $repeater_defaults );
						} elseif ( 'repeaters' === $field['type'] && ! empty( $the_value ) ) {
							$the_value = json_decode( $the_value );
                        }
						$sanitized_value                       = Fns::stripslashes_value( $the_value );
						$item['fields'][ $field_key ]['value'] = $sanitized_value;
					}
				}
				$this->full_list[ $name ] = $item;

				if ( 'on' === $item['active'] ) {
					$this->active_list[ $name ] = $item;
				} else {
					$this->inactive_list[ $name ] = $item;
				}
			}
		}
	}

	/**
	 * @return string Pro Package
	 */
	protected function pro_package() {
		return rtsb()->has_pro() ? 'pro' : 'pro-disabled';
	}
	/**
	 * @param $key
	 *
	 * @return bool
	 */
	public function is_widget_active( $key ) {
		return isset( $this->active_list[ $key ] );
	}

	/**
	 * @param mixed  $list true | anything
	 * @param string $filter_type full|active|inactive
	 *
	 * @return mixed
	 */
	public function get_list( $list = true, $filter_type = 'full' ) {
		if ( $list !== true && isset( $this->full_list[ $list ] ) ) {
			return $this->full_list[ $list ];
		}

		return $this->{$filter_type . '_list'};
	}

	public function get_section() {
		return [
			'title'       => $this->title,
			'short_title' => ! empty( $this->short_title ) ? $this->short_title : $this->title,
			'description' => $this->description,
			'list'        => $this->get_list(),
			'id'          => $this->list_id,
			'categories'  => $this->categories,
		];
	}

	/**
	 * @param $key
	 *
	 * @return array|mixed
	 */
	public function get_fields( $key ) {
		return isset( $this->full_list[ $key ]['fields'] ) ? $this->full_list[ $key ]['fields'] : [];
	}

	public function get_data() {
		return $this->db_options;
	}

	abstract protected function raw_list();
}
