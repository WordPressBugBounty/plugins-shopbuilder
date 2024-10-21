<?php

namespace RadiusTheme\SB\Controllers\Admin;

defined( 'ABSPATH' ) || exit();

use RadiusTheme\SB\Controllers\Admin\Ajax as Ajax;
use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Traits\SingletonTrait;


class AdminInit {
	/**
	 * Parent Menu Page Slug
	 */
	const MENU_PAGE_SLUG = 'rtsb';

	/**
	 * Menu capability
	 */
	const MENU_CAPABILITY = 'manage_options';

	/**
	 * Parent Menu Hook
	 *
	 * @var string
	 */
	static $parent_menu_hook = '';

	// private $menu_link_part;

	use SingletonTrait;

	public function __construct() {
		// $this->menu_link_part = admin_url( 'admin.php?page=rtsb' );
		$this->remove_all_notices();
		$this->init();
		$this->ajax_actions();
		$this->upgrade();
	}

	/**
	 * Admin Ajax hooks.
	 *
	 * @return void
	 */
	public function ajax_actions() {
		Ajax\DefaultTemplate::instance();
		Ajax\ModalTemplate::instance();
		Ajax\CreateTemplate::instance();
		Ajax\AdminSettings::instance();
	}

	/**
	 * Upgrade Notice.
	 *
	 * @return void
	 */
	public function upgrade() {
		Notice\Upgrade::instance();
		Notice\Review::instance();
		Notice\EarlyBirdDiscount::instance();
	}

	public function init() {
		add_action( 'admin_menu', [ $this, 'add_menu' ], 25 );
		PluginRow::instance();
	}

	public function add_menu() {
		self::$parent_menu_hook = add_menu_page(
			esc_html__( 'ShopBuilder', 'shopbuilder' ),
			esc_html__( 'ShopBuilder', 'shopbuilder' ),
			self::MENU_CAPABILITY,
			self::MENU_PAGE_SLUG,
			null,
			RTSB_URL . '/assets/images/icon/shopbuilder-logo-white.svg',
			'55.6'
		);

		add_submenu_page(
			self::MENU_PAGE_SLUG,
			esc_html__( 'Settings', 'shopbuilder' ),
			esc_html__( 'Settings', 'shopbuilder' ),
			self::MENU_CAPABILITY,
			'rtsb-settings',
			[ $this, 'settings_page' ],
		);
		add_submenu_page(
			self::MENU_PAGE_SLUG,
			esc_html__( 'Get Help', 'shopbuilder' ),
			esc_html__( 'Get Help', 'shopbuilder' ),
			self::MENU_CAPABILITY,
			'rtsb-get-help',
			[ $this, 'get_help_page' ],
		);
		add_submenu_page(
			self::MENU_PAGE_SLUG,
			esc_html__( 'Themes', 'shopbuilder' ),
			esc_html__( 'Themes', 'shopbuilder' ),
			self::MENU_CAPABILITY,
			'rtsb-themes',
			[ $this, 'get_themes_page' ],
		);
		do_action( 'rtsb/add/more/submenu', self::MENU_PAGE_SLUG, self::MENU_CAPABILITY );

		// Remove Parent Submenu
		remove_submenu_page( self::MENU_PAGE_SLUG, self::MENU_PAGE_SLUG );
	}

	function redirect_to_content() {
		wp_redirect( admin_url( 'admin.php?page=rtsb-settings' ) );
	}


	public function settings_page() {
		?>
		<div class="wrap rtsb-admin-wrap">
			<div id="rtsb-admin-app"></div>
		</div>
		<?php
	}

	public function get_help_page() {
		Fns::renderView( 'help' );
	}

	public function get_themes_page() {
		Fns::renderView( 'themes' );
	}

	/**
	 * Remove admin notices
	 */
	public function remove_all_notices() {
		add_action(
			'in_admin_header',
			function () {
				$screen = get_current_screen();

				if ( in_array(
					$screen->base,
					[ 'shopbuilder_page_rtsb-settings', 'shopbuilder_page_rtsb-license', 'shopbuilder_page_rtsb-themes' ],
					true
				) ) {
					remove_all_actions( 'admin_notices' );
					remove_all_actions( 'all_admin_notices' );
				}
			},
			1000
		);
	}
}
