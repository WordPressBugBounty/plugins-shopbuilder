<?php

namespace RadiusTheme\SB\Modules;

defined( 'ABSPATH' ) || exit();

use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Models\ModuleList;

use RadiusTheme\SB\Traits\SingletonTrait;

class ModuleManager {

	use SingletonTrait;

	private $module_list;

	private $module_instances = [];
	private function __construct() {
		add_action( 'init', [ $this, 'load_modules' ], 15 );
	}

	public function load_modules() {
		if ( ! is_null( $this->module_list ) ) {
			return;
		}
		$this->module_list = ModuleList::instance()->get_list( true, 'active' );
		foreach ( $this->module_list as $module ) {
			if ( isset( $module['active'] ) && $module['active'] !== 'on' ) {
				continue;
			}

			if ( ! empty( $module['package'] ) && $module['package'] === 'pro-disabled' ) {
				continue;
			}

			if ( ! isset( $module['base_class'] ) ) {
				continue;
			}

			if ( isset( $this->module_instances[ $module['base_class'] ] ) ) {
				continue;
			}

			if ( ! class_exists( $module['base_class'] ) ) {
				continue;
			}

			$module['base_class']::instance();

		}
	}
}
