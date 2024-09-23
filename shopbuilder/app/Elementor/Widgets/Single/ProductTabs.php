<?php
/**
 * Main ProductDescription class.
 *
 * @package RadiusTheme\SB
 */

namespace RadiusTheme\SB\Elementor\Widgets\Single;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

use RadiusTheme\SB\Abstracts\ElementorWidgetBase;
use RadiusTheme\SB\Elementor\Widgets\Controls\ProductTabsSettings;
use RadiusTheme\SB\Helpers\BuilderFns;
use RadiusTheme\SB\Helpers\ElementorDataMap;
use RadiusTheme\SB\Helpers\Fns;

/**
 * Product Description class
 */
class ProductTabs extends ElementorWidgetBase {
	/**
	 * Construct function
	 *
	 * @param array $data default array.
	 * @param mixed $args default arg.
	 */
	public function __construct( $data = [], $args = null ) {
		$this->rtsb_name = esc_html__( 'Product Tabs', 'shopbuilder' );
		$this->rtsb_base = 'rtsb-product-tabs';
		parent::__construct( $data, $args );
	}

	/**
	 * Widget Field
	 *
	 * @return array
	 */
	public function widget_fields() {
		return ProductTabsSettings::widget_fields( $this );
	}

	/**
	 * Set Widget Keyword.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return [ 'Tabs' ] + parent::get_keywords();
	}

	/**
	 * Widget Field.
	 *
	 * @param array $controllers Control.
	 *
	 * @return void
	 */
	public function apply_hooks() {
		add_filter( 'woocommerce_product_tabs', [ $this, 'woocommerce_product_tabs' ], 20 );
		add_filter( 'woocommerce_product_additional_information_heading', [ $this, 'additional_information_title_text' ], 20 );
		add_filter( 'woocommerce_product_description_heading', [ $this, 'woocommerce_product_description_heading' ], 20 );
	}

	/**
	 * @return void
	 */
	public function remove_hooks() {
		remove_filter( 'woocommerce_product_tabs', [ $this, 'woocommerce_product_tabs' ], 20 );
		remove_filter( 'woocommerce_product_additional_information_heading', [ $this, 'additional_information_title_text' ], 20 );
		remove_filter( 'woocommerce_product_description_heading', [ $this, 'woocommerce_product_description_heading' ], 20 );
	}

	/**
	 * @param $title
	 *
	 * @return mixed|string
	 */
	public function woocommerce_product_description_heading( $title ) {
		$controllers = $this->get_settings_for_display();

		return $controllers['description_title_text'] ?? '';
	}


	/**
	 * @param $title
	 *
	 * @return mixed|string
	 */
	public function additional_information_title_text( $title ) {
		$controllers = $this->get_settings_for_display();

		return $controllers['additional_information_title_text'] ?? '';
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function product_page_script() {
		if ( ! $this->is_edit_mode() ) {
			return;
		}
		?>
		<script type="text/javascript">
			window.rtsbProductPageInit();
		</script>
		<?php
	}

	/**
	 * @param $tabs
	 *
	 * @return mixed
	 */
	public function woocommerce_product_tabs( $tabs ) {
		$controllers = $this->get_settings_for_display();
		if ( $this->is_edit_mode() ) {
			add_filter(
				'the_content',
				function ( $content ) {
					return "This is only for preview text. Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text.";
				},
				99
			);
		}
		if ( empty( $controllers['description'] ) ) {
			unset( $tabs['description'] );
		} elseif ( ! empty( $controllers['description_nav_text'] ) ) {
			$tabs['description']['title'] = $controllers['description_nav_text'];
		}
		if ( empty( $controllers['additional_information'] ) ) {
			unset( $tabs['additional_information'] );
		} elseif ( ! empty( $tabs['additional_information'] ) && ! empty( $controllers['additional_information_nav_text'] ) ) {
			$tabs['additional_information']['title'] = $controllers['additional_information_nav_text'];
		}

		if ( empty( $controllers['reviews'] ) ) {
			unset( $tabs['reviews'] );
		} elseif ( ! empty( $controllers['reviews_nav_text'] ) ) {
			$tabs['reviews']['title'] = $controllers['reviews_nav_text'];
		}

		return $tabs;
	}

	/**
	 * Render Function
	 *
	 * @return void
	 */
	protected function render() {
		global $product, $post;
		$_post       = $post;
		$_product    = $product;
		$product     = Fns::get_product();
		$controllers = $this->get_settings_for_display();
		if ( $this->is_builder_mode() ) {
			// Overriding Global.
			$this->product_page_script();
			$post = get_post( Fns::get_prepared_product_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
		if ( ! $this->is_edit_mode() && BuilderFns::is_builder_preview() ) {
			add_filter(
				'the_content',
				function ( $content ) use ( $product ) {
					$content = $product->get_description();
					return $content;
				},
				1
			);
		}

        // wp_enqueue_style( 'woodmart-style' );

		$this->apply_hooks();
		$this->theme_support();

		$data = [
			'template'    => 'elementor/single-product/product-tabs',
			'controllers' => $controllers,
		];
		$data = apply_filters( 'rtsb/elements/elementor/render/' . $this->rtsb_base, $data, $this );
		Fns::load_template( $data['template'], $data );

		$this->remove_hooks();

		$this->theme_support( 'render_reset' );

		$post    = $_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$product = $_product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	}
}
