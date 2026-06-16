<?php
/**
 * Main ProductImages class.
 *
 * @package RadiusTheme\SB
 */

namespace RadiusTheme\SB\Elementor\Widgets\Single;

use RadiusTheme\SB\Helpers\BuilderFns;
use RadiusTheme\SB\Helpers\ElementorDataMap;
use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Abstracts\ElementorWidgetBase;
use RadiusTheme\SB\Elementor\Widgets\Controls\ProductImagesSettings;
use RadiusTheme\SBPRO\Helpers\BuilderFunPro;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Product Images class
 */
class ProductImages extends ElementorWidgetBase {
	/**
	 * Construct function
	 *
	 * @param array $data default array.
	 * @param mixed $args default arg.
	 */
	public function __construct( $data = [], $args = null ) {
		$this->rtsb_name = esc_html__( 'Product Images', 'shopbuilder' );
		$this->rtsb_base = 'rtsb-product-image';
		parent::__construct( $data, $args );
		$this->the_hooks();
	}
	/**
	 * Widget Field
	 *
	 * @return array
	 */
	public function widget_fields() {
		return $this->modify_widget_fields();
	}
	/**
	 * Widget Field
	 *
	 * @return array
	 */
	public function modify_widget_fields() {
		$fields                         = ProductImagesSettings::widget_fields( $this );
		$extra_controls['image_border'] = [
			'mode'           => 'group',
			'type'           => 'border',
			'selector'       => $this->selectors['image_border'],
			'size_units'     => [ 'px' ],
			'fields_options' => [
				'border' => [
					'label'       => esc_html__( 'Border', 'shopbuilder' ),
					'label_block' => true,
				],
				'color'  => [
					'label' => esc_html__( 'Border Color', 'shopbuilder' ),
				],
			],
		];
		$fields                         = Fns::insert_controls( 'image_width', $fields, $extra_controls, true );
		return $fields;
	}

	/**
	 * Widget Field.
	 *
	 * @return void
	 */
	public function init_scripts() {
		?>
		<script type="text/javascript">
			// Initialize the gallery's Swiper sliders. In the Elementor editor the
			// widget renders/re-renders after the slider script's one-time init has
			// already run, so the main + thumbnail Swipers need to be (re)initialized
			// here — otherwise the thumbnails stack and the gallery looks broken.
			window.rtsbInitGallerySliders = window.rtsbInitGallerySliders || function ($scope) {
				var $ctx = ($scope && $scope.length) ? $scope : jQuery(document);
				// Elementor's AJAX re-render can run outside edit-mode detection, which
				// leaves the gallery wrapper in the hidden `rtsb-content-loading` (opacity:0)
				// state. Reveal it so the editor never shows a blank/hidden gallery.
				$ctx.find('.rtsb-content-loading').removeClass('rtsb-content-loading');
				// Hide the thumbnail column when the current view has no thumbnails (the
				// runtime variation JS that normally does this doesn't run in the editor).
				$ctx.find('.rtsb-vg-main-slider-wrapper').each(function () {
					var $wrap = jQuery(this);
					if ($wrap.find('.rtsb-vg-thumb-slider .swiper-slide').length < 1) {
						$wrap.addClass('rtsb-vg-thumbs-hidden');
					}
				});
				$ctx.find('.rtsb-carousel-slider').each(function () {
					var $slider = jQuery(this);
					if (typeof $slider.rtsb_slider !== 'function') {
						return;
					}
					// The runtime height-sync (syncThumbHeight) only runs on WooCommerce's
					// wc_variation_form event, which doesn't fire in the editor — so the
					// vertical thumbnail Swiper has no definite height and grows unbounded.
					// Replicate the sync here once the slider has loaded.
					$slider.off('rtsb_slider_loaded.rtsbEditor').on('rtsb_slider_loaded.rtsbEditor', function () {
						setTimeout(function () {
							window.rtsbEditorSyncThumbHeight($slider);
						}, 400);
					});
					$slider.rtsb_slider();
				});
			};
			// Size the thumbnail column (and its slots) to the main image height, the
			// same way the frontend module does, so the editor preview isn't unbounded.
			window.rtsbEditorSyncThumbHeight = window.rtsbEditorSyncThumbHeight || function ($slider) {
				var $wrap = $slider.closest('.rtsb-vg-main-slider-wrapper');
				if (!$wrap.length || !$wrap.is('.rtsb-thumbnails-position-left, .rtsb-thumbnails-position-right')) {
					return;
				}
				var $thumb = $wrap.find('.rtsb-vg-thumb-slider').first();
				if (!$thumb.length) {
					return;
				}
				var mainH = Math.round($slider.outerHeight() || 0);
				if (mainH <= 0) {
					return;
				}
				var opts = $thumb.data('options') || {};
				var perView = parseInt(opts.slidesPerView, 10) || 4;
				var gap = parseInt(opts.spaceBetween, 10) || 0;
				var slotH = Math.floor((mainH - gap * (perView - 1)) / perView);
				$thumb.css('height', mainH + 'px');
				if (slotH > 0) {
					$thumb.find('.swiper-slide').css('height', slotH + 'px');
				}
				var thumbEl = $thumb.get(0);
				if (thumbEl && thumbEl.swiper) {
					thumbEl.swiper.update();
				}
			};
			if (!'<?php echo esc_attr( Fns::is_optimization_enabled() ); ?>') {
				setTimeout(function() {
					window.rtsbInitGallerySliders();
					if (typeof window.rtsbVariationGallery === 'function') {
						window.rtsbVariationGallery();
					}
				}, 600);
			} else {
				if (typeof elementorFrontend !== 'undefined') {
					elementorFrontend.hooks.addAction(
						'frontend/element_ready/<?php echo esc_attr( $this->rtsb_base ); ?>.default',
						( $scope ) => {
							// Init the gallery slider immediately — it only needs jQuery and
							// the rtsb_slider plugin, not the RTSB module system, so it must
							// not be gated behind waitForRTSB (which may not resolve here).
							window.rtsbInitGallerySliders($scope);
							window.waitForRTSB((RTSB) => {
								RTSB.modules.get('variationGallery')?.refresh();
							});
						}
					);
				}
			}
			/*
			* Initialize all galleries on page.
			*/
			jQuery('.woocommerce-product-gallery').each(function () {
				const that = this;

				setTimeout(function () {
					jQuery(this).trigger('wc-product-gallery-before-init', [this, wc_single_product_params]);
					jQuery(that).wc_product_gallery(wc_single_product_params);
					jQuery(that).trigger('wc-product-gallery-after-init', [this, wc_single_product_params]);
				}, 600);

			});
			<?php if ( function_exists( 'rtwpvg' ) ) { ?>
			setTimeout(function () {
				jQuery('.rtwpvg-wrapper, .rtwpvg-grid-wrapper').rtWpVGallery();
			}, 600);
			<?php } ?>


			if (!'<?php echo esc_attr( Fns::is_optimization_enabled() ); ?>') {
				jQuery(document).ready(function ($) {
					setTimeout(function () {
						const zoomIcon = $('.rtsb-product-images').attr('data-zoom-icon');
						if (!zoomIcon) {
							$('.rtsb-product-images')
								.find('.woocommerce-product-gallery__trigger,.rtwpvg-trigger')
								.remove();
						}
						$('.rtsb-product-images')
							.find('.woocommerce-product-gallery__trigger,.rtwpvg-trigger')
							.html(zoomIcon);
					}, 50);
				});
			} else {
				if (typeof elementorFrontend !== 'undefined') {
					elementorFrontend.hooks.addAction(
						'frontend/element_ready/<?php echo esc_attr( $this->rtsb_base ); ?>.default',
						($scope) => {
							window.waitForRTSB((RTSB) => {
								RTSB.modules.get('productImage')?.refresh?.($, $scope);
							});
						}
					);
				}
			}

		</script>
		<?php
	}

	/**
	 * Widget Field.
	 *
	 * @param array $control Control.
	 *
	 * @return void
	 */
	public function the_hooks( $control = [] ) {
		if ( empty( $control ) ) {
			return;
		}
		add_filter(
			'woocommerce_product_thumbnails_columns',
			function ( $col ) {
				$col = 0;

				return $col;
			}
		);
		if ( empty( $control['show_zoom'] ) ) {
			add_filter( 'rtwpvg_trigger_icon', '__return_false' );
		}
		if ( empty( $control['show_thumbnails'] ) ) {
			add_filter( 'rtwpvg_show_product_thumbnail_slider', '__return_false' );
			remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
		}
		add_filter( 'rtwpvg_thumbnail_slider_js_options', [ $this, 'thumbnail_slider_js_options' ], 50 );
		add_filter( 'rtwpvg_thumbnail_position', [ $this, 'thumbnail_position' ], 50 );
		add_filter( 'rtwpvg_thumbnails_columns', [ $this, 'thumbnails_columns' ] );
		add_filter( 'rtwpvg_sm_thumbnails_columns', [ $this, 'thumbnails_columns_sm' ] );
		add_filter( 'rtwpvg_xs_thumbnails_columns', [ $this, 'thumbnails_columns_xs' ] );
		// RTSB.
		add_filter( 'rtsb/vg/thumbnails/position', [ $this, 'vg_thumbnail_position' ], 50 );
		add_filter( 'rtsb/vg/lightbox', [ $this, 'vg_lightbox' ], 50 );
		add_filter( 'rtsb/vg/lightbox/trigger/icon', [ $this, 'vg_lightbox' ], 50 );
		add_filter( 'rtsb/vg/thumbnails/slider/options', [ $this, 'thumbnail_slider_js_options' ], 50, 2 );
		add_filter( 'rtsb/vg/lightbox/position', [ $this, 'vg_lightbox_position' ], 50, 2 );
		add_filter( 'rtsb/vg/gallery/columns', [ $this, 'vg_gallery_columns' ], 50, 2 );
	}
	/**
	 * Thumbnail Columns.
	 *
	 * @param int $col number.
	 *
	 * @return int
	 */
	public function vg_gallery_columns( $col ) {
		$controllers = $this->get_settings_for_display();
		if ( ! empty( $controllers['gallery_thumbs_column']['size'] ) ) {
			$col = absint( $controllers['gallery_thumbs_column']['size'] );
		}
		return $col;
	}
	/**
	 * Thumbnail slider js options.
	 *
	 * @param array $options Options.
	 *
	 * @return array
	 */
	public function thumbnail_slider_js_options( $options ) {
		$controllers = $this->get_settings_for_display();
		if ( ! empty( $controllers['gallery_thumbs_column']['size'] ) ) {
			$options['slidesPerView'] = absint( $controllers['gallery_thumbs_column']['size'] );
		}
		if ( ! empty( $controllers['gallery_thumbs_column_gap']['size'] ) ) {
			$options['spaceBetween'] = $controllers['gallery_thumbs_column_gap']['size'];
		}
		return $options;
	}

	/**
	 * Thumbnails columns.
	 *
	 * @param int $column Column.
	 *
	 * @return mixed
	 */
	public function thumbnails_columns( $column ) {
		$controllers = $this->get_settings_for_display();
		$column      = ! empty( $controllers['gallery_thumbs_column']['size'] ) ? $controllers['gallery_thumbs_column']['size'] : $column;

		return $column;
	}

	/**
	 * Thumbnails columns.
	 *
	 * @param int $column Column.
	 *
	 * @return mixed
	 */
	public function thumbnails_columns_sm( $column ) {
		$controllers = $this->get_settings_for_display();
		$column      = ! empty( $controllers['gallery_thumbs_column_tablet']['size'] ) ? $controllers['gallery_thumbs_column_tablet']['size'] : $column;

		return $column;
	}

	/**
	 * Thumbnails columns.
	 *
	 * @param int $column Column.
	 *
	 * @return mixed
	 */
	public function thumbnails_columns_xs( $column ) {
		$controllers = $this->get_settings_for_display();
		$column      = ! empty( $controllers['gallery_thumbs_column_mobile']['size'] ) ? $controllers['gallery_thumbs_column_mobile']['size'] : $column;

		return $column;
	}
	/**
	 * Thumbnail position.
	 *
	 * @param string $position Position.
	 *
	 * @return string
	 */
	public function thumbnail_position( $position ) {
		if ( ! function_exists( 'rtwpvg' ) || ! rtwpvg()->active_pro() || ! ( BuilderFns::is_product() || BuilderFns::is_quick_views_page() ) ) {
			return $position;
		}

		$controllers     = $this->get_settings_for_display();
		$show_thumbnails = ! empty( $controllers['show_thumbnails'] ) ? $controllers['show_thumbnails'] : false;
		if ( ! $show_thumbnails && 'grid' !== ( $controllers['image_layout'] ?? '' ) ) {
			add_filter( 'rtwpvg_show_product_thumbnail_slider', '__return_false', 20 );
			return 'bottom';
		}
		return ! empty( $controllers['image_layout'] ) ? $controllers['image_layout'] : 'bottom';
	}
	/**
	 * Thumbnail position.
	 *
	 * @param string $position Position.
	 *
	 * @return string
	 */
	public function vg_thumbnail_position( $position ) {
		if ( ! rtsb()->has_pro() ) {
			return $position;
		}
		$controllers = $this->get_settings_for_display();
		return ! empty( $controllers['image_layout'] ) ? $controllers['image_layout'] : 'bottom';
	}

	/**
	 * @param bool $lightbox Lightbox.
	 * @return mixed
	 */
	public function vg_lightbox( $lightbox ) {
		$controllers = $this->get_settings_for_display();
		return ! empty( $controllers['lightbox_icon'] ) ? Fns::icons_manager( $controllers['lightbox_icon'] ) : $lightbox;
	}
	/**
	 * @param string $position position text.
	 * @return mixed
	 */
	public function vg_lightbox_position( $position ) {
		$controllers = $this->get_settings_for_display();
		return ! empty( $controllers['vg_lightbox_position'] ) ? $controllers['vg_lightbox_position'] : $position;
	}

	/**
	 * Render Function
	 *
	 * @return void
	 */
	protected function render() {
		add_action( 'wp_footer', 'woocommerce_photoswipe' );
		global $product, $post;
		$_product    = $product;
		$product     = Fns::get_product();
		$controllers = $this->get_settings_for_display();
		if ( empty( $controllers['show_module_badges'] ) ) {
			add_filter( 'rtsb/module/badges/show', '__return_false', 99 );
		}

		$this->theme_support();
		$controllers['lightbox_icon'] = Fns::icons_manager( $controllers['lightbox_icon'] );
		$data                         = [
			'template'    => 'elementor/single-product/product-images',
			'controllers' => $controllers,
		];
		$this->the_hooks( $controllers );

		Fns::load_template( $data['template'], $data );

		if ( $this->is_builder_mode() ) {
			$this->init_scripts();
		}

		if ( empty( $controllers['show_module_badges'] ) ) {
			remove_filter( 'rtsb/module/badges/show', '__return_false', 99 );
		}

		$this->theme_support( 'render_reset' );
		$product = $_product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	}
}
