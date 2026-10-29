<?php
/**
 * Class AssetRegistry
 *
 * Gathers and processes frontend JS and CSS assets based on active ShopBuilder modules.
 *
 * @package RadiusTheme\SB\Services
 * @since   1.0.0
 */

namespace RadiusTheme\SB\Controllers;

use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Helpers\Cache;
use RadiusTheme\SB\Helpers\BuilderFns;
use RadiusTheme\SB\Helpers\ElementorDataMap;
use RadiusTheme\SB\Models\AssetBundler;
use RadiusTheme\SB\Traits\SingletonTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AssetRegistry
 */
class AssetRegistry {
	use SingletonTrait;

	/**
	 * List of enabled module slugs.
	 *
	 * @var array
	 */
	protected $enabled_modules = [];

	/**
	 * List of enabled modules JS.
	 *
	 * @var array
	 */
	protected $enabled_modules_js = [];

	/**
	 * List of enabled modules CSS.
	 *
	 * @var array
	 */
	protected $enabled_modules_css = [];

	/**
	 * List of enabled Elementor widgets.
	 *
	 * @var array
	 */
	protected $enabled_el_widgets = [];

	/**
	 * List of enabled Elementor widgets.
	 *
	 * @var array
	 */
	protected $enabled_widgets = [];

	/**
	 * Full paths to JS assets.
	 *
	 * @var array
	 */
	protected $js_files = [];

	/**
	 * Full paths to CSS assets.
	 *
	 * @var array
	 */
	protected $css_files = [];

	/**
	 * Cached bundled JS asset.
	 *
	 * @var array|null
	 */
	protected $bundled_js = null;

	/**
	 * Cached bundled CSS asset.
	 *
	 * @var array|null
	 */
	protected $bundled_css = null;

	/**
	 * RTL suffix.
	 *
	 * @var string
	 */
	private $suffix = '';

	/**
	 * Directory path.
	 *
	 * @var string
	 */
	private $dir;

	/**
	 * Cached bundled assets.
	 *
	 * @var array|null
	 */
	private $bundled_assets = null;

	/**
	 * List of loaded asset types.
	 *
	 * @var array
	 */
	private static $loaded_types = [];

	/**
	 * List of loaded shared assets.
	 *
	 * @var array
	 */
	private $shared_loaded = [];

	/**
	 * Contextual loading.
	 *
	 * @var bool
	 */
	private $contextual_loading;

	/**
	 * Asset contexts this request has to serve, memoised.
	 *
	 * @var array|null
	 */
	private $required_contexts = null;

	/**
	 * Widget type to asset-context list, memoised.
	 *
	 * @var array|null
	 */
	private $widget_type_map = null;

	/**
	 * Widget types found in the documents this request renders, memoised.
	 *
	 * @var array|null
	 */
	private $template_widget_types = null;

	/**
	 * Class constructor.
	 */
	private function __construct() {
		$this->enabled_modules     = $this->get_enabled_modules();
		$this->enabled_modules_js  = $this->get_enabled_modules_js_with_context();
		$this->enabled_modules_css = $this->get_enabled_modules_css_with_context();
		$this->enabled_el_widgets  = $this->get_enabled_el_widgets();
		$this->enabled_widgets     = $this->get_enabled_widgets_with_context();
		$this->suffix              = is_rtl() ? '-rtl' : '';
		$this->dir                 = is_rtl() ? trailingslashit( 'rtl' ) : trailingslashit( 'css' );
		$this->contextual_loading  = Fns::is_contextual_loading();
	}

	/**
	 * Get bundled JS assets by context.
	 *
	 * @param bool $all Build every materialised context rather than only the one
	 *                  this request serves. Used by the manual regeneration path.
	 *
	 * @return array
	 */
	public function get_bundled_js_by_context( $all = false ) {
		$js_files_by_context = $this->get_contextual_assets( 'js' );
		$bundled_contexts    = [];
		$wanted              = $all ? array_keys( $js_files_by_context ) : $this->contexts_to_build( $js_files_by_context );

		foreach ( $js_files_by_context as $context => $files ) {
			if ( empty( $files ) || ! in_array( $context, $wanted, true ) ) {
				continue;
			}

			$bundler = new AssetBundler( "rtsb-bundled-$context", $files, 'js' );
			$src     = $this->build_bundle( $bundler, $context );
			$deps    = [ 'jquery', 'rtsb-tipsy' ];

			$builder_page_id = BuilderFns::builder_page_id_by_page();

			if ( Fns::is_elementor_page( $builder_page_id ) ) {
				$deps[] = 'imagesloaded';
			}

			$bundled_contexts[ $context ] = [
				'handle' => "rtsb-bundled-$context",
				'src'    => $src,
				'deps'   => apply_filters( 'rtsb/optimizer/scripts/deps', $deps ),
				'footer' => true,
			];
		}

		return $bundled_contexts;
	}

	/**
	 * Returns assets grouped by context with `global` inherited for each.
	 *
	 * @param string $type 'css' or 'js'.
	 *
	 * @return array
	 */
	public function get_contextual_assets( $type ) {
		$all_contexts = 'js' === $type ? $this->get_scripts_by_context() : $this->get_styles_by_context();

		$normalized = array_map(
			function ( $paths ) {
				return array_unique( $paths );
			},
			$all_contexts
		);

		$global = $normalized['global'] ?? [];

		foreach ( $normalized as $context => $paths ) {
			if ( 'global' === $context ) {
				continue;
			}

			$normalized[ $context ] = array_unique(
				array_merge(
					$global,
					$paths
				)
			);
		}

		/*
		 * The bucket this request actually serves carries every category its
		 * page needs, not just the one its page type maps to.
		 *
		 * Assets are filed under the widget's declared ElementList category
		 * while the served bucket is chosen by page type, so a Template Builder
		 * template holding widgets from another category - a product widget in a
		 * Shop template, say - rendered with its CSS/JS left in a bucket this
		 * request never loads. required_contexts() resolves what the template on
		 * screen really needs; merging here keeps the existing "one bundle per
		 * request" shape rather than enqueuing several.
		 *
		 * Writing the key unconditionally also guarantees the served context
		 * exists in both the CSS and the JS map, so the two never resolve to
		 * different bundles and Fns::optimized_handle() can never name a handle
		 * that was not enqueued.
		 */
		$served = $this->served_context();
		$merged = array_merge( $global, $this->template_module_assets( $type ) );

		foreach ( $this->required_contexts() as $context ) {
			if ( ! empty( $normalized[ $context ] ) ) {
				$merged = array_merge( $merged, $normalized[ $context ] );
			}
		}

		$normalized[ $served ] = array_values( array_unique( $merged ) );

		return $normalized;
	}

	/**
	 * The single context key this request serves.
	 *
	 * @return string
	 */
	public function served_context() {
		return Fns::detect_context();
	}

	/**
	 * Asset contexts this request has to cover.
	 *
	 * The page-type context, plus every context holding an asset that a
	 * ShopBuilder widget in the resolved Template Builder template needs.
	 * Returns just the page-type context when no template is resolved (an
	 * ordinary WordPress or Elementor page) or when the template holds no
	 * ShopBuilder widgets, so nothing extra is bundled for pages that do not
	 * need it.
	 *
	 * @return array
	 */
	public function required_contexts() {
		if ( null !== $this->required_contexts ) {
			return $this->required_contexts;
		}

		$contexts = array_merge( [ $this->served_context() ], $this->template_widget_contexts() );

		$this->required_contexts = array_values( array_unique( $contexts ) );

		return $this->required_contexts;
	}

	/**
	 * Contexts holding assets for the widgets used by the resolved template.
	 *
	 * @return array
	 */
	protected function template_widget_contexts() {
		$map = $this->widget_asset_contexts_map();

		if ( empty( $map ) ) {
			return [];
		}

		$contexts = [];

		foreach ( $this->template_widget_types() as $widget_type ) {
			if ( empty( $map[ $widget_type ] ) ) {
				continue;
			}

			$contexts = array_merge( $contexts, $map[ $widget_type ] );
		}

		return array_values( array_unique( $contexts ) );
	}

	/**
	 * Widget types present in the documents this request can render.
	 *
	 * The builder template on screen, plus the Quick View template when that
	 * module is active: its modal is injected by AJAX after this page's assets
	 * are already printed, so whatever it contains has to be covered now or not
	 * at all.
	 *
	 * @return array
	 */
	protected function template_widget_types() {
		if ( null !== $this->template_widget_types ) {
			return $this->template_widget_types;
		}

		$types = [];

		foreach ( $this->render_template_ids() as $template_id ) {
			$types = array_merge( $types, ElementorDataMap::instance()->get_widget_types( $template_id ) );
		}

		$this->template_widget_types = array_values( array_unique( $types ) );

		return $this->template_widget_types;
	}

	/**
	 * Template IDs whose widgets this request may render.
	 *
	 * @return array
	 */
	protected function render_template_ids() {
		$ids = [ BuilderFns::current_template_id() ];

		if ( Fns::is_module_active( 'quick_view' ) ) {
			$ids[] = BuilderFns::current_template_id( 'quick-view' );
		}

		return array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
	}

	/**
	 * Module assets required by the widgets this request renders.
	 *
	 * A widget can own no assets at all and still need a module's stylesheet or
	 * script - the Size Chart widget's template is nothing but a wrapper around
	 * the module's action. The module files are pulled in individually rather
	 * than by adding the module's whole context, so a Cart page gains the one
	 * script its widget needs instead of every shop and product asset.
	 *
	 * Purely additive: a module already required by its own hooks, shortcodes or
	 * AJAX keeps its declared contexts untouched.
	 *
	 * @param string $type 'css' or 'js'.
	 *
	 * @return array Absolute asset paths.
	 */
	protected function template_module_assets( $type ) {
		$widgets = $this->template_widget_types();

		if ( empty( $widgets ) ) {
			return [];
		}

		$modules = [];

		foreach ( $this->widget_module_dependency_map() as $group ) {
			if ( empty( $group['widgets'] ) || empty( $group['modules'] ) ) {
				continue;
			}

			// Typed by asset kind: several widgets need a module's CSS but have no
			// use for its JS, and one of them has no JS to ask for.
			$kinds = (array) ( $group['assets'] ?? [ 'css', 'js' ] );

			if ( ! in_array( $type, $kinds, true ) ) {
				continue;
			}

			if ( ! array_intersect( (array) $group['widgets'], $widgets ) ) {
				continue;
			}

			$modules = array_merge( $modules, (array) $group['modules'] );
		}

		$paths = [];

		foreach ( array_unique( $modules ) as $module ) {
			if ( ! Fns::is_module_active( $module ) ) {
				continue;
			}

			$slug = apply_filters( 'rtsb/optimizer/module/remap', str_replace( '_', '-', sanitize_key( $module ) ) );
			$path = Fns::locate_asset( 'js' === $type ? "js/modules/$slug.js" : "{$this->dir}modules/{$slug}{$this->suffix}.css" );

			if ( $path ) {
				$paths[] = $path;
			}
		}

		return array_values( array_unique( $paths ) );
	}

	/**
	 * Widgets whose output is produced by a module, typed by asset kind.
	 *
	 * Shared group files are deliberately absent: the shared registry already
	 * resolves those for both modules and widgets, and restating them here would
	 * create a second, competing source of truth. Only a module's own
	 * `modules/<slug>` files belong in this map.
	 *
	 * Filterable so Pro and third parties declare their own edges instead of
	 * this list naming them.
	 *
	 * @return array
	 */
	protected function widget_module_dependency_map() {
		return apply_filters(
			'rtsb/optimizer/widgets/module_dependencies',
			[
				[
					// The Quick View entries reuse these same widget classes, so this
					// one type covers both the product page and the modal.
					'widgets' => [ 'rtsb-product-size-chart' ],
					'modules' => [ 'product_size_chart' ],
					'assets'  => [ 'css', 'js' ],
				],
				[
					'widgets' => [ 'rtsb-multi-step-checkout' ],
					'modules' => [ 'multi_step_checkout' ],
					'assets'  => [ 'css', 'js' ],
				],
				[
					'widgets' => [ 'rtsb-save-for-buy-later', 'rtsb-save-for-later-preview' ],
					'modules' => [ 'save_for_buy_later' ],
					'assets'  => [ 'css', 'js' ],
				],
				[
					'widgets' => [ 'rtsb-coupon-list' ],
					'modules' => [ 'smart_coupons' ],
					'assets'  => [ 'css', 'js' ],
				],
				[
					'widgets' => [ 'rtsb-wishlist' ],
					'modules' => [ 'wishlist' ],
					'assets'  => [ 'css', 'js' ],
				],
				[
					'widgets' => [ 'rtsb-product-quick-checkout' ],
					'modules' => [ 'quick_checkout' ],
					'assets'  => [ 'css', 'js' ],
				],
				[
					// Pre-Order filters `rtsb/elementor/product_stock_icons`, which only
					// this widget applies, and styles the `.rtsb-stock-text` it emits.
					// Its script binds `.rtsb-product-add-to-cart` alone, so the stock
					// widget has no use for it.
					'widgets' => [ 'rtsb-product-stock' ],
					'modules' => [ 'pre_order' ],
					'assets'  => [ 'css' ],
				],
				[
					// The buttons these widgets render exist only while the module does.
					'widgets' => [ 'rtsb-actions-button' ],
					'modules' => [ 'wishlist', 'compare', 'quick_view' ],
					'assets'  => [ 'css', 'js' ],
				],
				[
					// Account navigation and custom endpoints are styled by the module;
					// it ships no script.
					'widgets' => [ 'rtsb-account-navigation', 'rtsb-endpoint-dynamic-content' ],
					'modules' => [ 'customize_my_account' ],
					'assets'  => [ 'css' ],
				],
				[
					// Countdown styling lives in a shared group the shared registry
					// already resolves; the module itself owns no files.
					'widgets' => [ 'rtsb-product-flash-sale-countdown' ],
					'modules' => [ 'flash_sale_countdown' ],
					'assets'  => [ 'css' ],
				],
			]
		);
	}

	/**
	 * Map of Elementor widget type name to every context its assets live in.
	 *
	 * The widget type stored in `_elementor_data` comes from the widget object's
	 * `rtsb_base`, which is only set in its constructor, so the authoritative
	 * link back to the ElementList entry is the class name of the registered
	 * instance. Elementor has already built those instances on any page that
	 * renders a builder template, so this reuses them rather than constructing
	 * anything, and the result is cached for the same lifetime as the widget
	 * list itself.
	 *
	 * A widget's own CSS/JS is filed under its ElementList category, but its
	 * shared files are filed under contexts the shared maps declare
	 * independently, and the two do not always agree - `shared/order-received`
	 * sits in `account` while its widgets are categorised `thank_you`. Routing
	 * on the category alone therefore misses assets, so the map unions both
	 * sources and is derived from the shared maps rather than naming any widget.
	 *
	 * @return array
	 */
	protected function widget_asset_contexts_map() {
		if ( null !== $this->widget_type_map ) {
			return $this->widget_type_map;
		}

		$cache_key = 'rtsb_widget_asset_contexts_map';
		$cached    = wp_cache_get( $cache_key, Cache::group() );

		if ( is_array( $cached ) ) {
			$this->widget_type_map = $cached;

			return $this->widget_type_map;
		}

		$registered = $this->registered_widget_classes();

		if ( empty( $registered ) ) {
			// Elementor is unavailable or has registered nothing yet. Do not cache
			// an empty map: fall back to the page-type context for this request
			// and try again on the next one.
			return [];
		}

		$shared = $this->shared_contexts_by_widget();
		$map    = [];

		foreach ( Fns::get_widgets_list() as $slug => $info ) {
			if ( empty( $info['base_class'] ) ) {
				continue;
			}

			if ( ! isset( $info['active'] ) || 'on' !== $info['active'] ) {
				continue;
			}

			$class = ltrim( $info['base_class'], '\\' );

			if ( ! isset( $registered[ $class ] ) ) {
				continue;
			}

			$clean    = str_replace( '_', '-', sanitize_key( $slug ) );
			$own      = ! empty( $info['category'] ) ? $info['category'] : 'global';
			$contexts = array_merge( [ self::normalize_context( $own ) ], $shared[ $clean ] ?? [] );
			$name     = $registered[ $class ];

			/*
			 * Several ElementList entries can share one widget class, and so one
			 * widget type: the Quick View entries reuse the single-product widgets
			 * under a `quick_view` category. Merge rather than assign, or whichever
			 * entry is read last silently discards the other's contexts.
			 */
			$map[ $name ] = array_values(
				array_unique( array_merge( $map[ $name ] ?? [], $contexts ) )
			);
		}

		wp_cache_set( $cache_key, $map, Cache::group(), 12 * HOUR_IN_SECONDS );
		Cache::set_data_cache_key( $cache_key );

		$this->widget_type_map = $map;

		return $this->widget_type_map;
	}

	/**
	 * Widget slug to the contexts its shared asset files are filed under.
	 *
	 * Read straight off the same shared maps the bundler files those files with,
	 * so membership stays correct when a map entry is edited or a third party
	 * filters one.
	 *
	 * @return array
	 */
	protected function shared_contexts_by_widget() {
		$contexts = [];

		$groups = array_merge(
			array_values( $this->get_elementor_shared_styles_map() ),
			array_values( $this->get_shared_style_components() )
		);

		foreach ( $groups as $group ) {
			if ( empty( $group['widgets'] ) ) {
				continue;
			}

			foreach ( (array) ( $group['context'] ?? [ 'global' ] ) as $context ) {
				$context = self::normalize_context( $context );

				foreach ( (array) $group['widgets'] as $widget_slug ) {
					$contexts[ $widget_slug ][ $context ] = $context;
				}
			}
		}

		return array_map( 'array_values', $contexts );
	}

	/**
	 * Resolve a declared category to the bucket key the bundler actually uses.
	 *
	 * @param string $context Declared category or context.
	 *
	 * @return string
	 */
	protected static function normalize_context( $context ) {
		return 'general' === $context ? 'global' : $context;
	}

	/**
	 * Class name to widget type name for everything Elementor has registered.
	 *
	 * @return array
	 */
	private function registered_widget_classes() {
		if ( ! class_exists( '\Elementor\Plugin' ) || empty( \Elementor\Plugin::$instance->widgets_manager ) ) {
			return [];
		}

		$classes = [];

		try {
			$types = \Elementor\Plugin::$instance->widgets_manager->get_widget_types();
		} catch ( \Throwable $e ) {
			return [];
		}

		foreach ( (array) $types as $name => $instance ) {
			if ( is_object( $instance ) ) {
				$classes[ get_class( $instance ) ] = $name;
			}
		}

		return $classes;
	}

	/**
	 * Returns scripts grouped by context.
	 *
	 * @return array
	 */
	public function get_scripts_by_context() {
		$context_scripts = [];

		// Add core-init globally.
		$core_path                   = $this->context()->get_assets_path( 'js/frontend/core-init.js' );
		$context_scripts['global'][] = $core_path;

		$shared_registry = apply_filters(
			'rtsb/optimizer/scripts/shared/components',
			[
				'coupon'   => [
					'path'    => rtsb()->get_assets_path( 'js/modules/shared/coupon.js' ),
					'modules' => [ 'shopify-checkout', 'quick-checkout' ],
					'widgets' => [ 'cart-coupon-form', 'coupon-form' ],
					'context' => Fns::is_module_active( 'quick_checkout' ) ? [ 'global' ] : [ 'cart', 'checkout' ],
				],
				'quantity' => [
					'path'    => rtsb()->get_assets_path( 'js/modules/shared/cart-quantity-handler.js' ),
					'modules' => [ 'sticky-add-to-cart' ],
					'widgets' => [ 'cart-table', 'product-add-to-cart' ],
					'context' => Fns::is_module_active( 'quick_view' ) ? [ 'global' ] : [ 'product', 'cart' ],
				],
			]
		);

		$shared_loaded = [];

		// Load module-based shared scripts.
		foreach ( $this->enabled_modules_js as $module_slug => $module ) {
			if ( ! $module['enabled'] ) {
				continue;
			}

			foreach ( $shared_registry as $key => $data ) {
				if ( in_array( $module_slug, $data['modules'], true ) ) {
					$shared_loaded[ $key ] = $data;
				}
			}

			$module_slug = apply_filters( 'rtsb/optimizer/module/remap', $module_slug );
			$path        = Fns::locate_asset( "js/modules/$module_slug.js" );

			if ( $path ) {
				$category = ! empty( $module['category'] ) ? $module['category'] : [ 'global' ];

				foreach ( $category as $cat ) {
					$context_scripts[ $cat ][] = $path;
				}
			}
		}

		// Elementor-specific widget scripts.
		if ( defined( 'ELEMENTOR_VERSION' ) && Fns::should_load_elementor_scripts() ) {
			foreach ( $this->enabled_widgets as $slug => $widget ) {
				if ( ! $widget['enabled'] ) {
					continue;
				}

				foreach ( $shared_registry as $key => $data ) {
					if ( isset( $shared_loaded[ $key ] ) ) {
						continue;
					}

					if ( in_array( $slug, $data['widgets'], true ) ) {
						$shared_loaded[ $key ] = $data;
					}
				}

				$path = Fns::locate_asset( "js/frontend/elementor/$slug.js" );

				if ( $path ) {
					$category = ! empty( $widget['category'] ) ? $widget['category'] : 'global';

					if ( 'general' === $category ) {
						$category = 'global';
					}

					$context_scripts[ $category ][] = $path;
				}
			}

			$core_el = Fns::locate_asset( 'js/frontend/elementor/el-frontend-core.js' );

			if ( $core_el ) {
				$context_scripts['global'][] = $core_el;
			}
		}

		foreach ( $shared_loaded as $data ) {
			if ( empty( $data['path'] ) ) {
				continue;
			}

			$contexts = $data['context'] ?? [ 'global' ];

			foreach ( (array) $contexts as $ctx ) {
				$context_scripts[ $ctx ][] = $data['path'];
			}
		}

		$context_scripts = apply_filters(
			'rtsb/optimizer/scripts/shared',
			$context_scripts,
			$this->enabled_modules,
			$this->enabled_widgets
		);
		return apply_filters( 'rtsb/optimizer/scripts', $context_scripts );
	}

	/**
	 * Get structured JS scripts.
	 *
	 * @return array[]
	 */
	public function get_scripts() {
		$scripts        = [];
		$shared_scripts = [];
		$shared_loaded  = [];

		// Load core.
		$scripts[] = $this->context()->get_assets_path( 'js/frontend/core-init.js' );

		$shared_registry = apply_filters(
			'rtsb/optimizer/scripts/shared/components',
			[
				'coupon'   => [
					'path'    => rtsb()->get_assets_path( 'js/modules/shared/coupon.js' ),
					'modules' => [ 'shopify-checkout', 'quick-checkout' ],
					'widgets' => [ 'cart-coupon-form', 'coupon-form' ],
				],
				'quantity' => [
					'path'    => rtsb()->get_assets_path( 'js/modules/shared/cart-quantity-handler.js' ),
					'modules' => [ 'sticky-add-to-cart' ],
					'widgets' => [ 'cart-table', 'product-add-to-cart' ],
				],
			]
		);

		foreach ( $this->enabled_modules as $module ) {
			foreach ( $shared_registry as $key => $data ) {
				if ( in_array( $module, $data['modules'], true ) ) {
					$shared_loaded[ $key ] = true;
				}
			}

			$module = apply_filters( 'rtsb/optimizer/module/remap', $module );
			$path   = Fns::locate_asset( "js/modules/$module.js" );

			if ( $path ) {
				$scripts[] = $path;
			}
		}

		if ( defined( 'ELEMENTOR_VERSION' ) && Fns::should_load_elementor_scripts() ) {
			foreach ( $this->enabled_el_widgets as $widget ) {
				foreach ( $shared_registry as $key => $data ) {
					if ( isset( $shared_loaded[ $key ] ) ) {
						continue;
					}

					if ( in_array( $widget, $data['widgets'], true ) ) {
						$shared_loaded[ $key ] = true;
					}
				}

				$path = Fns::locate_asset( "js/frontend/elementor/$widget.js" );

				if ( $path ) {
					$scripts[] = $path;
				}
			}

			$scripts[] = Fns::locate_asset( 'js/frontend/elementor/el-frontend-core.js' );
		}

		foreach ( $shared_loaded as $key => $_ ) {
			if ( isset( $shared_registry[ $key ]['path'] ) ) {
				$shared_scripts[] = $shared_registry[ $key ]['path'];
			}
		}

		$shared_scripts = apply_filters(
			'rtsb/optimizer/scripts/shared',
			$shared_scripts,
			$this->enabled_modules,
			$this->enabled_el_widgets
		);

		$scripts = array_merge( $scripts, array_unique( $shared_scripts ) );

		return array_unique( apply_filters( 'rtsb/optimizer/scripts', $scripts ) );
	}

	/**
	 * Get structured CSS styles.
	 *
	 * @return array[]
	 */
	public function get_styles() {
		$elementor_styles = defined( 'ELEMENTOR_VERSION' ) && Fns::should_load_elementor_scripts()
			? $this->get_elementor_styles() : [];

		return apply_filters(
			'rtsb/optimizer/styles',
			array_merge(
				$this->get_base_styles(),
				$this->get_module_styles(),
				$elementor_styles
			)
		);
	}

	/**
	 * Get structured CSS styles.
	 *
	 * @return array[]
	 */
	public function get_styles_by_context() {
		$core      = $this->get_base_styles_by_context();
		$modules   = $this->get_module_styles_by_context();
		$elementor = defined( 'ELEMENTOR_VERSION' ) && Fns::should_load_elementor_scripts() ? $this->get_elementor_styles_by_context() : [];

		$merged = [];

		foreach ( [ $core, $modules, $elementor ] as $group ) {
			foreach ( $group as $context => $paths ) {
				foreach ( $paths as $file ) {
					$merged[ $context ][] = $file;
				}
			}
		}

		return apply_filters( 'rtsb/optimizer/styles/by_context', $merged );
	}

	/**
	 * Get base styles.
	 *
	 * @return array
	 */
	protected function get_base_styles() {
		return apply_filters(
			'rtsb/optimizer/styles/base',
			[
				rtsb()->get_assets_path( 'css/frontend/rtsb-fonts.css' ),
				Fns::locate_asset( $this->dir . 'frontend/frontend-core' . $this->suffix . '.css' ),
			]
		);
	}

	/**
	 * Get base styles.
	 *
	 * @return array
	 */
	protected function get_base_styles_by_context() {
		$styles = array_filter(
			[
				rtsb()->get_assets_path( 'css/frontend/rtsb-fonts.css' ),
				Fns::locate_asset( $this->dir . 'frontend/frontend-core' . $this->suffix . '.css' ),
			]
		);

		return [
			'global' => $styles,
		];
	}

	/**
	 * Get module styles.
	 *
	 * @return array
	 */
	protected function get_module_styles() {
		$styles = [];

		if ( empty( $this->enabled_modules ) ) {
			return $styles;
		}

		$styles = array_merge( $styles, $this->get_shared_module_styles() );

		foreach ( $this->enabled_modules as $module ) {
			$module = apply_filters( 'rtsb/optimizer/module/remap', $module );
			$path   = Fns::locate_asset( "{$this->dir}modules/$module$this->suffix.css" );

			if ( $path ) {
				$styles[] = $path;
			}
		}

		$styles = apply_filters( 'rtsb/optimizer/styles/module', $styles );

		return array_unique( $styles );
	}

	/**
	 * Get module styles.
	 *
	 * @return array
	 */
	protected function get_module_styles_by_context() {
		$content_styles = [];

		if ( empty( $this->enabled_modules_css ) ) {
			return $content_styles;
		}

		$content_styles = array_merge( $content_styles, $this->get_shared_module_styles() );

		foreach ( $this->enabled_modules_css as $module_slug => $module ) {
			if ( ! $module['enabled'] ) {
				continue;
			}

			$module_slug = apply_filters( 'rtsb/optimizer/module/remap', $module_slug );
			$path        = Fns::locate_asset( "{$this->dir}modules/$module_slug$this->suffix.css" );

			if ( $path ) {
				$category = ! empty( $module['category'] ) ? $module['category'] : [ 'global' ];

				foreach ( $category as $cat ) {
					$content_styles[ $cat ][] = $path;
				}
			}
		}

		return apply_filters( 'rtsb/optimizer/styles/module', $content_styles );
	}

	/**
	 * Get Elementor styles.
	 *
	 * @return array
	 */
	protected function get_elementor_styles() {
		$styles = [ Fns::locate_asset( $this->dir . 'frontend/elementor/el-frontend-core' . $this->suffix . '.css' ) ];
		$shared = $this->contextual_loading ? $this->get_elementor_shared_widget_styles_by_context() : $this->get_elementor_shared_widget_styles();
		$styles = array_merge( $styles, $shared );

		foreach ( $this->enabled_el_widgets as $widget ) {
			$path = Fns::locate_asset( "{$this->dir}frontend/elementor/$widget$this->suffix.css" );

			if ( $path ) {
				$styles[] = $path;
			}
		}

		return array_unique( $styles );
	}

	/**
	 * Get Elementor styles.
	 *
	 * @return array
	 */
	protected function get_elementor_styles_by_context() {
		$styles = [];

		$styles['global'] = [
			Fns::locate_asset( $this->dir . 'frontend/elementor/el-frontend-core' . $this->suffix . '.css' ),
		];

		$shared_styles = $this->get_elementor_shared_widget_styles_by_context();

		foreach ( $shared_styles as $context => $context_styles ) {
			foreach ( (array) $context_styles as $style ) {
				$styles[ $context ][] = $style;
			}
		}

		foreach ( $this->enabled_widgets as $widget_slug => $info ) {
			if ( empty( $info['enabled'] ) ) {
				continue;
			}

			$contexts = $info['category'] ?? [ 'global' ];

			foreach ( (array) $contexts as $ctx ) {
				if ( 'general' === $ctx ) {
					$ctx = 'global';
				}

				$path = Fns::locate_asset( "{$this->dir}frontend/elementor/{$widget_slug}{$this->suffix}.css" );

				if ( $path ) {
					$styles[ $ctx ][] = $path;
				}
			}
		}

		return $styles;
	}

	/**
	 * Get shared styles based on enabled modules.
	 *
	 * @return array
	 */
	protected function get_shared_module_styles() {
		$shared_styles_map = apply_filters(
			'rtsb/optimizer/styles/modules/shared',
			[
				[
					'modules'   => [ 'wishlist', 'compare' ],
					'file_name' => 'shared/module-buttons',
					'context'   => [ 'global' ],
				],
				[
					'modules'   => [ 'quick-view', 'product-size-chart', 'quick-checkout' ],
					'file_name' => 'shared/modal-layouts',
					'context'   => [ 'global' ],
				],
			]
		);

		// Add global shared.
		$shared_by_components = $this->get_shared_style_components();

		if ( ! $this->contextual_loading ) {
			foreach ( $shared_by_components as $key => $data ) {
				if ( ! empty( $data['modules'] ) && array_intersect( $data['modules'], $this->enabled_modules ) ) {
					$shared_styles_map[]         = [
						'modules'   => $data['modules'],
						'file_name' => $data['file_name'],
					];
					$this->shared_loaded[ $key ] = true;
				}
			}

			$styles = [];

			if ( empty( $this->enabled_modules ) ) {
				return $styles;
			}

			foreach ( $shared_styles_map as $group ) {
				if ( array_intersect( $group['modules'], $this->enabled_modules ) ) {
					$styles[] = Fns::locate_asset( "{$this->dir}modules/{$group['file_name']}{$this->suffix}.css" );
				}
			}

			return array_unique( $styles );
		}

		$enabled_widget_slugs = array_keys(
			array_filter( $this->enabled_widgets, fn( $w ) => ! empty( $w['enabled'] ) )
		);

		$enabled_module_slugs = array_keys(
			array_filter( $this->enabled_modules_css ?? [], fn( $m ) => ! empty( $m['enabled'] ) )
		);

		foreach ( $shared_by_components as $key => $data ) {
			$modules = $data['modules'] ?? [];
			$widgets = $data['widgets'] ?? [];

			if (
				array_intersect( $modules, $enabled_module_slugs ) ||
				array_intersect( $widgets, $enabled_widget_slugs )
			) {
				$shared_styles_map[]         = [
					'modules'   => $modules,
					'widgets'   => $widgets,
					'file_name' => $data['file_name'],
					'context'   => $data['context'] ?? [ 'global' ],
				];
				$this->shared_loaded[ $key ] = true;
			}
		}

		$styles = [];

		foreach ( $shared_styles_map as $group ) {
			$modules = $group['modules'] ?? [];
			$widgets = $group['widgets'] ?? [];

			$has_enabled_module = array_intersect( $modules, $enabled_module_slugs );
			$has_enabled_widget = array_intersect( $widgets, $enabled_widget_slugs );

			if ( ! $has_enabled_module && ! $has_enabled_widget ) {
				continue;
			}

			foreach ( (array) $group['context'] as $ctx ) {
				$path = Fns::locate_asset( "{$this->dir}modules/{$group['file_name']}{$this->suffix}.css" );

				if ( $path ) {
					$styles[ $ctx ][] = $path;
				}
			}
		}

		return $styles;
	}

	/**
	 * Get shared Elementor widget styles based on enabled widgets.
	 *
	 * @return array
	 */
	protected function get_elementor_shared_widget_styles() {
		$shared_styles_map = $this->get_elementor_shared_styles_map();

		// Add global shared.
		$shared_by_components = $this->get_shared_style_components();
		$styles               = [];

		foreach ( $shared_by_components as $key => $data ) {
			if ( isset( $this->shared_loaded[ $key ] ) ) {
				continue;
			}

			if ( ! empty( $data['widgets'] ) && array_intersect( $data['widgets'], $this->enabled_el_widgets ) ) {
				$path = Fns::locate_asset( "{$this->dir}modules/{$data['file_name']}{$this->suffix}.css" );

				if ( $path ) {
					$styles[] = $path;
				}
			}
		}

		foreach ( $shared_styles_map as $group ) {
			if ( array_intersect( $group['widgets'], $this->enabled_el_widgets ) ) {
				$styles[] = Fns::locate_asset( "{$this->dir}frontend/elementor/{$group['file_name']}$this->suffix.css" );
			}
		}

		return array_unique( $styles );
	}

	/**
	 * Get shared Elementor widget styles based on enabled widgets.
	 *
	 * @return array
	 */
	protected function get_elementor_shared_widget_styles_by_context() {
		$shared_styles_map = $this->get_elementor_shared_styles_map();

		// Add global shared.
		$shared_by_components = $this->get_shared_style_components();
		$styles               = [];

		$enabled_widget_slugs = array_keys(
			array_filter(
				$this->enabled_widgets ?? [],
				fn( $widget ) => ! empty( $widget['enabled'] )
			)
		);

		foreach ( $shared_by_components as $key => $data ) {
			if ( isset( $this->shared_loaded[ $key ] ) ) {
				continue;
			}

			if (
				! empty( $data['widgets'] ) &&
				array_intersect( $data['widgets'], $enabled_widget_slugs )
			) {
				$contexts = $data['context'] ?? [ 'global' ];

				foreach ( (array) $contexts as $ctx ) {
					if ( 'general' === $ctx ) {
						$ctx = 'global';
					}

					$path = Fns::locate_asset( "{$this->dir}modules/{$data['file_name']}{$this->suffix}.css" );

					if ( $path ) {
						$styles[ $ctx ][] = $path;
					}
				}

				$this->shared_loaded[ $key ] = true;
			}
		}

		foreach ( $shared_styles_map as $group ) {
			if (
				! empty( $group['widgets'] ) &&
				array_intersect( $group['widgets'], $enabled_widget_slugs )
			) {
				$contexts = $group['context'] ?? [ 'global' ];

				if ( ! is_array( $contexts ) ) {
					$contexts = [ $contexts ];
				}

				foreach ( $contexts as $ctx ) {
					$path = Fns::locate_asset( "{$this->dir}frontend/elementor/{$group['file_name']}{$this->suffix}.css" );

					if ( $path ) {
						$styles[ $ctx ][] = $path;
					}
				}
			}
		}

		return $styles;
	}

	/**
	 * Get Elementor shared styles map.
	 *
	 * @return array
	 */
	private function get_elementor_shared_styles_map() {
		return apply_filters(
			'rtsb/optimizer/styles/elementor/shared',
			[
				[
					'widgets'   => [
						'products-grid',
						'products-list',
						'products-slider',
						'products-single-cateogory',
						'product-cateogories',
						'products-archive-catalog-custom',
						'advanced-heading',
						'dropcaps',
						'shopbuilder-button',
						'shopbuilder-cta',
						'shopbuilder-info-box',
						'shopbuilder-flip-box',
						'shopbuilder-pricing-table',
						'shopbuilder-counter',
						'shopbuilder-countdown',
						'shopbuilder-progress-bar',
						'image-accordion',
						'shopbuilder-faq',
						'logo-slider-and-grid',
						'testimonial',
						'team-member',
						'post-grid',
						'post-list',
						'upsells-product-custom',
						'upsells-products-slider-custom',
						'related-product-custom',
						'related-products-slider-custom',
						'cross-sells-custom',
						'cross-sells-custom-slider',
					],
					'file_name' => 'shared/el-grid',
					'context'   => [ 'global' ],
				],
				[
					'widgets'   => [
						'shopbuilder-button',
						'shopbuilder-cta',
						'shopbuilder-info-box',
						'shopbuilder-flip-box',
						'shopbuilder-pricing-table',
					],
					'file_name' => 'shared/shopbuilder-button',
					'context'   => [ 'global' ],
				],
				[
					'widgets'   => [
						'products-grid',
						'products-list',
						'products-slider',
						'products-single-cateogory',
						'product-cateogories',
						'products-archive-catalog-custom',
						'upsells-product-custom',
						'upsells-products-slider-custom',
						'related-product-custom',
						'related-products-slider-custom',
						'cross-sells-custom',
						'cross-sells-custom-slider',
					],
					'file_name' => 'shared/el-general-product',
					'context'   => [ 'global' ],
				],
				[
					'widgets'   => [
						'products-grid',
						'products-slider',
						'products-archive-catalog-custom',
					],
					'file_name' => 'shared/products-grid-slider',
					'context'   => [ 'global' ],
				],
				[
					'widgets'   => [
						'products-list',
						'products-archive-catalog-custom',
					],
					'file_name' => 'shared/products-list',
					'context'   => [ 'global' ],
				],
				[
					'widgets'   => [ 'post-grid', 'post-list' ],
					'file_name' => 'shared/post',
					'context'   => [ 'global' ],
				],
				[
					'widgets'   => [
						'products-archive-catalog',
						'products-archive-catalog-custom',
					],
					'file_name' => 'shared/products-archive',
					'context'   => [ 'shop' ],
				],
				[
					'widgets'   => [
						'product-title',
						'product-description',
						'product-short-description',
						'product-images',
						'product-onsale',
						'product-additional-info',
						'product-price',
						'product-meta',
						'product-categories',
						'product-rating',
						'product-tags',
						'product-sku',
						'product-stock',
						'actions-button',
						'product-add-to-cart',
						'product-share',
						'product-tabs',
						'product-reviews',
						'upsells-product',
						'related-product',
					],
					'file_name' => 'shared/single-product',
					'context'   => [ 'product' ],
				],
				[
					'widgets'   => [
						'cart-table',
						'cart-totals',
						'cross-sells',
					],
					'file_name' => 'shared/cart',
					'context'   => [ 'cart' ],
				],
			]
		);
	}

	/**
	 * Get enabled modules. Filterable.
	 *
	 * @return array
	 */
	protected function get_enabled_modules() {
		$module_list = Fns::get_modules_list();
		$enabled     = [];

		foreach ( $module_list as $slug => $info ) {
			if ( defined( 'RTWPVG_VERSION' ) && 'variation_gallery' === $slug ) {
				continue;
			}
			if ( defined( 'RTWPVS_VERSION' ) && 'variation_swatches' === $slug ) {
				continue;
			}
			if ( ! empty( $info['active'] ) ) {
				$enabled[] = str_replace( '_', '-', sanitize_key( $slug ) );
			}
		}

		return $enabled;
	}

	/**
	 * Get enabled modules JS with context.
	 *
	 * @return array
	 */
	protected function get_enabled_modules_js_with_context() {
		$module_list = Fns::get_modules_list();
		$modules     = [];
		$context     = apply_filters(
			'rtsb/optimizer/modules/js_context',
			[
				'checkout_fields_editor' => [ 'checkout' ],
				'shopify_checkout'       => [ 'checkout' ],
			]
		);

		foreach ( $module_list as $slug => $info ) {
			if ( defined( 'RTWPVG_VERSION' ) && 'variation_gallery' === $slug ) {
				continue;
			}
			if ( defined( 'RTWPVS_VERSION' ) && 'variation_swatches' === $slug ) {
				continue;
			}
			$clean_slug = str_replace( '_', '-', sanitize_key( $slug ) );
			$is_enabled = isset( $info['active'] ) && 'on' === $info['active'];

			$modules[ $clean_slug ] = [
				'enabled'  => $is_enabled,
				'category' => ! empty( $context[ $slug ] ) ? $context[ $slug ] : [ 'global' ],
			];
		}

		return $modules;
	}

	/**
	 * Get enabled modules CSS with context.
	 *
	 * @return array
	 */
	protected function get_enabled_modules_css_with_context() {
		$module_list = Fns::get_modules_list();
		$modules     = [];
		$context     = apply_filters(
			'rtsb/optimizer/modules/css_context',
			[
				'checkout_fields_editor' => [ 'checkout' ],
				'shopify_checkout'       => [ 'checkout' ],
			]
		);

		foreach ( $module_list as $slug => $info ) {
			if ( defined( 'RTWPVG_VERSION' ) && 'variation_gallery' === $slug ) {
				continue;
			}
			if ( defined( 'RTWPVS_VERSION' ) && 'variation_swatches' === $slug ) {
				continue;
			}
			$clean_slug = str_replace( '_', '-', sanitize_key( $slug ) );
			$is_enabled = isset( $info['active'] ) && 'on' === $info['active'];

			$modules[ $clean_slug ] = [
				'enabled'  => $is_enabled,
				'category' => ! empty( $context[ $slug ] ) ? $context[ $slug ] : [ 'global' ],
			];
		}

		return $modules;
	}

	/**
	 * Get enabled modules.
	 *
	 * @return array
	 */
	protected function get_enabled_el_widgets() {
		$widget_data = Fns::get_widgets_list();
		$enabled     = [];

		foreach ( $widget_data as $slug => $info ) {
			if ( isset( $info['active'] ) && 'on' === $info['active'] ) {
				$enabled[] = str_replace( '_', '-', sanitize_key( $slug ) );
			}
		}

		return $enabled;
	}

	/**
	 * Get enabled widgets with categories.
	 *
	 * @return array
	 */
	protected function get_enabled_widgets_with_context() {
		$widget_list = Fns::get_widgets_list();
		$widgets     = [];

		foreach ( $widget_list as $slug => $info ) {
			$clean_slug = str_replace( '_', '-', sanitize_key( $slug ) );
			$is_enabled = isset( $info['active'] ) && 'on' === $info['active'];

			$widgets[ $clean_slug ] = [
				'enabled'  => $is_enabled,
				'category' => ! empty( $widget_list[ $slug ]['category'] ) ? $widget_list[ $slug ]['category'] : 'global',
			];
		}

		return $widgets;
	}

	/**
	 * Populate JS/CSS file arrays based on active modules.
	 *
	 * @return void
	 */
	protected function collect_assets() {
		static $ran = false;

		if ( $ran ) {
			return;
		}

		$ran = true;

		$this->js_files  = $this->contextual_loading ? $this->get_scripts_by_context() : array_unique( $this->get_scripts() );
		$this->css_files = $this->contextual_loading ? $this->get_styles_by_context() : array_unique( $this->get_styles() );
	}

	/**
	 * Build and return the final bundled and minified CSS and JS.
	 *
	 * @return array
	 */
	public function get_bundled_assets() {
		if ( null !== $this->bundled_assets ) {
			return $this->bundled_assets;
		}

		$this->collect_assets();

		$this->bundled_assets = [
			'js'  => $this->contextual_loading ? $this->get_bundled_js_by_context() : $this->get_bundled_js(),
			'css' => $this->contextual_loading ? $this->get_bundled_css_by_context() : $this->get_bundled_css(),
		];

		return $this->bundled_assets;
	}

	/**
	 * Load optimized assets and return them by type
	 *
	 * @param string $type 'css' or 'js'.
	 *
	 * @return array
	 */
	public function load_optimized_assets( $type = 'css' ) {
		if ( isset( self::$loaded_types[ $type ] ) ) {
			return [];
		}

		$assets  = [];
		$bundled = $this->get_bundled_assets();
		$context = Fns::detect_context();

		if ( $this->contextual_loading ) {
			if ( 'js' === $type && ! empty( $bundled['js'] ) ) {
				$ctx = isset( $bundled['js'][ $context ] ) ? $context : 'global';
				if ( $ctx ) {
					$assets = (array) $bundled['js'][ $ctx ];
				}
			}
			if ( 'css' === $type && ! empty( $bundled['css'] ) ) {
				$ctx = isset( $bundled['css'][ $context ] ) ? $context : 'global';
				if ( $ctx ) {
					$assets = (array) $bundled['css'][ $ctx ];
				}
			}
		} else {
			if ( 'js' === $type && ! empty( $bundled['js'] ) ) {
				$assets = (array) $this->bundled_assets['js'];
			}

			if ( 'css' === $type && ! empty( $bundled['css'] ) ) {
				$assets = (array) $this->bundled_assets['css'];
			}
		}

		self::$loaded_types[ $type ] = true;

		return $assets;
	}

	/**
	 * Get bundled JS, with caching to prevent multiple builds.
	 *
	 * @return array|null
	 */
	protected function get_bundled_js() {
		if ( null !== $this->bundled_js ) {
			return $this->bundled_js;
		}

		if ( empty( $this->js_files ) ) {
			return null;
		}

		$handle  = 'rtsb-bundled';
		$bundler = new AssetBundler( $handle, $this->js_files, 'js' );
		$src     = $this->build_bundle( $bundler );
		$deps    = [ 'jquery', 'rtsb-tipsy' ];

		$builder_page_id = BuilderFns::builder_page_id_by_page();

		if ( Fns::is_elementor_page( $builder_page_id ) ) {
			$deps[] = 'imagesloaded';
		}

		$this->bundled_js = [
			'handle' => $handle,
			'src'    => $src,
			'deps'   => apply_filters( 'rtsb/optimizer/scripts/deps', $deps ),
			'footer' => true,
		];

		return $this->bundled_js;
	}

	/**
	 * Get bundled CSS, with caching to prevent multiple builds.
	 *
	 * @return array|null
	 */
	protected function get_bundled_css() {
		if ( null !== $this->bundled_css ) {
			return $this->bundled_css;
		}

		if ( empty( $this->css_files ) ) {
			return null;
		}

		$bundler = new AssetBundler( 'rtsb-bundled', $this->css_files, 'css' );
		$src     = $this->build_bundle( $bundler );

		$this->bundled_css = [
			'handle' => 'rtsb-bundled',
			'src'    => $src,
		];

		return $this->bundled_css;
	}

	/**
	 * Get bundled CSS by context
	 *
	 * @param bool $all Build every materialised context rather than only the one
	 *                  this request serves. Used by the manual regeneration path.
	 *
	 * @return array|null
	 */
	public function get_bundled_css_by_context( $all = false ) {
		$css_files_by_context = $this->get_contextual_assets( 'css' );
		$bundled_contexts     = [];
		$wanted               = $all ? array_keys( $css_files_by_context ) : $this->contexts_to_build( $css_files_by_context );

		foreach ( $css_files_by_context as $context => $files ) {
			if ( empty( $files ) || ! in_array( $context, $wanted, true ) ) {
				continue;
			}

			$bundler = new AssetBundler( "rtsb-bundled-$context", $files, 'css' );
			$src     = $this->build_bundle( $bundler, $context );

			$bundled_contexts[ $context ] = [
				'handle' => "rtsb-bundled-$context",
				'src'    => $src,
			];
		}

		return $bundled_contexts;
	}

	/**
	 * The context or contexts this request actually needs built.
	 *
	 * A request serves exactly one bundle per type: load_optimized_assets() and
	 * enqueue_optimized_assets() both resolve the detected context, falling back
	 * to `global` when it has no mapping of its own. Building the others produces
	 * files this request will never reference.
	 *
	 * Only the selected context is returned, never context plus global: where a
	 * contextual bundle exists, get_contextual_assets() has already merged the
	 * global files into it, so global is not served alongside.
	 *
	 * Resolved separately for CSS and JS, because the two maps are built
	 * independently and a context can be mapped for one type but not the other.
	 *
	 * @param array $map Context to file-list map for a single asset type.
	 *
	 * @return array List of context keys to build.
	 */
	protected function contexts_to_build( array $map ) {
		$context = Fns::detect_context();

		if ( isset( $map[ $context ] ) ) {
			return [ $context ];
		}

		return isset( $map['global'] ) ? [ 'global' ] : [];
	}

	/**
	 * Build a bundle and report an unusable result to Fns.
	 *
	 * AssetBundler::build() returns an empty string when it cannot write a file,
	 * for example when the cache directory is not writable or when the scoped
	 * minifier is not loadable yet on the first request after an upgrade.
	 * Registering a handle with an empty source makes WordPress print nothing at
	 * all, so the request must fall back to the unbundled assets instead.
	 *
	 * @param AssetBundler $bundler Bundler instance.
	 * @param string|null  $context Context this bundle belongs to, null when not
	 *                              using contextual loading.
	 *
	 * @return string Bundle URL, or an empty string on failure.
	 */
	private function build_bundle( AssetBundler $bundler, $context = null ) {
		$src = $bundler->build();

		if ( '' !== $src ) {
			return $src;
		}

		// Only fall back when the bundle that would actually be served is the one
		// that failed. enqueue_optimized_assets() serves the detected context and
		// falls back to `global`, so a failure in any other context is harmless
		// and must not disable optimization for the whole request.
		if ( null === $context || 'global' === $context || Fns::detect_context() === $context ) {
			Fns::mark_optimization_unavailable();
		}

		return $src;
	}

	/**
	 * Forcefully regenerate bundled JS and CSS assets.
	 *
	 * Never runs in the request that replaced the plugin files. At that point the
	 * process holds classes from the release being replaced while everything
	 * autoloaded from here on comes from the new files, and a bundle build pulls
	 * in a long tail of both - a new class calling a method the old one does not
	 * have is fatal. Nothing is lost by skipping: a missing bundle is built on
	 * demand by the first request that needs it.
	 *
	 * Deliberately guarded here as well as in Cache::clear_asset_cache(), because
	 * an upgrade from an older release runs that caller from the old files.
	 *
	 * @return array
	 */
	public function regenerate_bundles() {
		if ( did_action( 'upgrader_process_complete' ) ) {
			return [
				'js'  => null,
				'css' => null,
			];
		}

		$this->bundled_js  = null;
		$this->bundled_css = null;
		$this->js_files    = [];
		$this->css_files   = [];

		$this->collect_assets();

		// Explicitly eager: this is the manual "Purge Asset Cache" path, which is
		// expected to rebuild every materialised context rather than only the one
		// the current request happens to serve.
		return [
			'js'  => $this->contextual_loading ? $this->get_bundled_js_by_context( true ) : $this->get_bundled_js(),
			'css' => $this->contextual_loading ? $this->get_bundled_css_by_context( true ) : $this->get_bundled_css(),
		];
	}

	/**
	 * Get context.
	 *
	 * @return object
	 */
	private function context() {
		return ( function_exists( 'rtsbpro' ) && rtsb()->has_pro() ) ? rtsbpro() : rtsb();
	}

	/**
	 * Get shared style mappings for modules and widgets.
	 *
	 * @return array
	 */
	private function get_shared_style_components() {
		return apply_filters(
			'rtsb/optimizer/styles/shared/components',
			[
				'checkout' => [
					'file_name' => 'shared/checkout',
					'modules'   => [ 'quick-checkout', 'shopify-checkout' ],
					'widgets'   => [
						'billing-form',
						'shipping-form',
						'order-notes',
						'order-review',
						'checkout-payment',
						'coupon-form',
						'checkout-login-form',
						'shipping-method',
					],
					'context'   => [ 'global' ],
				],
				'slider'   => [
					'file_name' => 'shared/slider-core',
					'modules'   => [ 'variation-gallery' ],
					'widgets'   => [
						'products-slider',
						'team-member',
						'upsells-products-slider-custom',
						'related-products-slider-custom',
						'cross-sells-custom-slider',
						'hero-slider',
					],
					'context'   => [ 'global' ],
				],
			]
		);
	}
}
