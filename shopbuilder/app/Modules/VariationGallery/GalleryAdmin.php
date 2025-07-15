<?php
/**
 * Main FilterHooks class.
 *
 * @package RadiusTheme\SB
 */

namespace RadiusTheme\SB\Modules\VariationGallery;

use RadiusTheme\SB\Helpers\BuilderFns;
use RadiusTheme\SB\Helpers\Cache;
use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit();

/**
 * Main FilterHooks class.
 */
class GalleryAdmin {

	/**
	 * Singleton Trait.
	 */
	use SingletonTrait;

	/**
	 * Module Class Constructor.
	 */
	private function __construct() {
		add_action( 'admin_footer', [ $this, 'admin_template_js' ] );
		add_action( 'woocommerce_product_after_variable_attributes', [ $this, 'gallery_admin_html' ], 10, 3 );
		add_action( 'woocommerce_save_product_variation', [ $this, 'save_variation_gallery' ] );
		add_action( 'add_meta_boxes', [ $this, 'add_metabox' ] );
		add_action( 'save_post', [ $this, 'save_metabox' ] );
	}
	/**
	 * Adds a custom meta box to the WooCommerce product edit page.
	 */
	public function add_metabox() {
		add_meta_box(
			'custom_checkbox_metabox', // Metabox ID.
			__( 'Variation gallery', 'shopbuilder' ), // Title.
			[ $this, 'metabox_callback' ], // Callback function.
			'product', // Post type (WooCommerce product).
			'side', // Position.
			'high' // Priority.
		);
	}
	/**
	 * Callback function to display the checkbox in the meta box.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function metabox_callback( $post ) {
		$value = get_post_meta( $post->ID, '_rtsb_vg_disable_valiation_gallery', true );
		?>
		<p>
			<label for="custom_checkbox">
				<input type="checkbox" id="rtsb_vg_disable_valiation_gallery" name="rtsb_vg_disable_valiation_gallery" value="yes" <?php checked( $value, 'yes' ); ?> />
				<?php esc_html_e( 'Disable Variation Gallery', 'shopbuilder' ); ?>
			</label><br/>
			<span><?php esc_html_e( 'Disable variation gallery for this product', 'shopbuilder' ); ?> </span>
		</p>
		<?php
	}

	/**
	 * Saves the checkbox value when the product is updated.
	 *
	 * @param int $post_id The ID of the product being saved.
	 */
	public function save_metabox( $post_id ) {
		// Verify nonce for security.
		if ( empty( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['woocommerce_meta_nonce'] ), 'woocommerce_save_data' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return;
		}
		// Prevent autosave from overwriting.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		// Check if the user has permission to edit the product.
		if ( ! current_user_can( 'edit_product', $post_id ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
			return;
		}
		// Save the checkbox value as 'yes' or 'no'.
		$value = isset( $_POST['rtsb_vg_disable_valiation_gallery'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_rtsb_vg_disable_valiation_gallery', $value );
	}
	/**
	 * Saves variation gallery image IDs from the variation admin panel.
	 *
	 * @param int $variation_id The ID of the product variation.
	 *
	 * @return void
	 */
	public function save_variation_gallery( $variation_id ) {
		check_ajax_referer( 'save-variations', 'security' );

		// Check permissions again and make sure we have what we need.
		if ( ! current_user_can( 'edit_products' ) || empty( $_POST ) || empty( $_POST['product_id'] ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
			wp_die( -1 );
		}

		// Sanitize and save or delete meta.
		if ( isset( $_POST['rtsb_vg'][ $variation_id ] ) && is_array( $_POST['rtsb_vg'][ $variation_id ] ) ) {
			$rtsb_vg_ids = array_map( 'absint', $_POST['rtsb_vg'][ $variation_id ] );
			$rtsb_vg_ids = array_values( array_unique( $rtsb_vg_ids ) );
			update_post_meta( $variation_id, 'rtsb_vg_images', $rtsb_vg_ids );
		} else {
			delete_post_meta( $variation_id, 'rtsb_vg_images' );
		}
	}
	/**
	 * @return void
	 */
	public function admin_template_js() {
		require_once RTSB_PATH . '/app/Modules/VariationGallery/view/template-admin-thumbnail.php';
	}

	/**
	 * Outputs the variation gallery image section in the product variation admin panel.
	 *
	 * Enqueues the necessary admin script and renders the gallery UI for a specific
	 * variation, allowing admin users to add, preview, and remove multiple images
	 * associated with a WooCommerce product variation.
	 *
	 * @param int     $loop            The index of the current variation loop.
	 * @param array   $variation_data  Array of variation data.
	 * @param WP_Post $variation     The variation object (as a WP_Post).
	 *
	 * @return void
	 */
	public function gallery_admin_html( $loop, $variation_data, $variation ) {
		$variation_id   = absint( $variation->ID );
		$gallery_images = get_post_meta( $variation_id, 'rtsb_vg_images', true );
		?>
		<div class="form-row form-row-full rtsb-vg-gallery-wrapper">
			<h4><?php esc_html_e( 'Variation Image Gallery', 'shopbuilder' ); ?></h4>
			<div class="rtsb-vg-image-container">
				<ul class="rtsb-vg-images">
					<?php
					if ( is_array( $gallery_images ) && ! empty( $gallery_images ) ) {
						$gallery_images = array_values( array_unique( $gallery_images ) );
						foreach ( $gallery_images as $image_id ) :
							$image = wp_get_attachment_image_src( $image_id );
							$video = false;
							/**
							 * Functions::gallery_has_video( $image_id );.
							 */
							if ( empty( $image[0] ) ) {
								continue;
							}
							$add_video_class = $video ? ' video' : '';
							?>
							<li class="image<?php echo esc_html( $add_video_class ); ?>">
								<input type="hidden" name="rtsb_vg[<?php echo absint( $variation_id ); ?>][]" value="<?php echo absint( $image_id ); ?>">
								<img src="<?php echo esc_url( $image[0] ); ?>">
								<a href="#" class="delete rtsb-vg-remove-image">
									<span  class="dashicons dashicons-dismiss"></span>
								</a>
							</li>
							<?php
						endforeach;
					}
					?>
				</ul>
			</div>
			<p class="rtsb-vg-add-image-wrapper hide-if-no-js">
				<a href="#" data-product_variation_loop="<?php echo absint( $loop ); ?>"
				   data-product_variation_id="<?php echo esc_attr( $variation_id ); ?>"
				   class="button rtsb-vg-add-image"><?php esc_html_e( 'Add Gallery Images', 'shopbuilder' ); ?></a>
			</p>
		</div>
		<?php
	}
}