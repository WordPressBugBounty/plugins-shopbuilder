<?php
/**
 * AdvancedHeading class.
 *
 * @package RadiusTheme\SB
 */

namespace RadiusTheme\SB\Elementor\Widgets\General\ShopBuilderFaq;

use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Abstracts\ElementorWidgetBase;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Dropcaps class.
 *
 * @package RadiusTheme\SB
 */
class ShopBuilderFaq extends ElementorWidgetBase {
	/**
	 * Construct function
	 *
	 * @param array $data default array.
	 * @param mixed $args default arg.
	 */
	public function __construct( $data = [], $args = null ) {
		$this->rtsb_name = esc_html__( 'FAQ', 'shopbuilder' );
		$this->rtsb_base = 'rtsb-shopbuilder-faq';

		parent::__construct( $data, $args );

		$this->rtsb_category = 'rtsb-shopbuilder-general';
	}
	/**
	 * Whether the element returns dynamic content.
	 *
	 * @return bool
	 */
	protected function is_dynamic_content(): bool {
		return false;
	}
	/**
	 * Widget Field
	 *
	 * @return array
	 */
	public function widget_fields() {
		return array_merge(
			Controls::content( $this ),
			Controls::style( $this )
		);
	}
	/**
	 * Set Widget Keyword.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return [ 'ShopBuilder Accordion','Accordion' ] + parent::get_keywords();
	}

	/**
	 * Style dependencies.
	 *
	 * @return array
	 */
	public function get_style_depends(): array {
		return [
			'rtsb-general-addons',
			'elementor-icons-shared-0',
			'elementor-icons-fa-solid',
		];
	}


	/**
	 * Addon Render.
	 *
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		switch ( $settings['layout_style'] ) {
			case 'rtsb-sb-faq-layout2':
			case 'rtsb-sb-faq-layout4':
			case 'rtsb-sb-faq-layout6':
				$template = 'layout2';
				break;
			default:
				$template = 'layout1';
				break;
		}
		$template = apply_filters( 'rtsb/general_widget/shopbuilder_faq/template', $template, $settings );
		$data     = [
			'template'        => 'elementor/general/shopbuilder-faq/' . $template,
			'id'              => $this->get_id(),
			'unique_name'     => $this->get_unique_name(),
			'layout'          => $settings['layout_style'],
			'settings'        => $settings,
			'is_carousel'     => false,
			'container_class' => '',
			'content_class'   => '',
		];

		// Render initialization.
		$this->theme_support();

		// Call the template rendering method.
		Fns::print_html( ( new Render() )->display_content( $data, $settings ), true );
		$this->edit_mode_accordion_script();
		$this->theme_support( 'render_reset' );
	}
	private function edit_mode_accordion_script() {
		if ( ! $this->is_edit_mode() ) {
			return;
		}
		?>
		<script>
			setTimeout(function() {
				rtsbAccordionInit();
			}, 1000);
		</script>

		<?php
	}
}
