<?php
/**
 * Main ActionHooks class.
 *
 * @package RadiusTheme\SB
 */

namespace RadiusTheme\SB\Controllers\Hooks;

use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Models\GeneralList;
use RadiusTheme\SB\Helpers\BuilderFns;
use RadiusTheme\SB\Models\TemplateSettings;
use RadiusTheme\SB\Elementor\Render\Render;

defined( 'ABSPATH' ) || exit();

/**
 * Main ActionHooks class.
 */
class ActionHooks {
	/**
	 * Init Hooks.
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_action( 'wp_head', [ __CLASS__, 'og_metatags_for_sharing' ], 1 );
		add_action( 'woocommerce_share', [ __CLASS__, 'shopbuilder_share' ], 20 );
		add_action( 'rtsb/wcqv/product/summary', [ __CLASS__, 'shopbuilder_share' ], 40 );
		add_action( 'rtsb/shop/product/image', [ __CLASS__, 'render_image' ], 10, 2 );

		add_action(
			'admin_init',
			function () {
				remove_post_type_support( BuilderFns::$post_type_tb, 'revisions' );
			}
		);
		add_action( 'deleted_post', [ __CLASS__, 'deleted_post' ], 10, 2 );
		add_action( 'rtsb/elements/render/before_query', [ __CLASS__, 'modify_wc_query_args' ] );
		add_action( 'rtsb/elements/render/after_query', [ __CLASS__, 'reset_wc_query_args' ] );
		// Cart Table.
		remove_action( 'woocommerce_cart_is_empty', 'wc_empty_cart_message', 10 );
		add_action( 'woocommerce_cart_is_empty', [ __CLASS__, 'cart_is_empty' ], 10 );
		/**
		 * Notices.
		 */
		remove_action( 'woocommerce_cart_is_empty', 'woocommerce_output_all_notices', 5 );
		remove_action( 'woocommerce_shortcode_before_product_cat_loop', 'woocommerce_output_all_notices', 10 );
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_output_all_notices', 10 );
		remove_action( 'woocommerce_before_single_product', 'woocommerce_output_all_notices', 10 );
		remove_action( 'woocommerce_before_cart', 'woocommerce_output_all_notices', 10 );
		remove_action( 'woocommerce_before_checkout_form_cart_notices', 'woocommerce_output_all_notices', 10 );
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_output_all_notices', 10 );
		remove_action( 'woocommerce_account_content', 'woocommerce_output_all_notices', 5 );
		remove_action( 'woocommerce_before_customer_login_form', 'woocommerce_output_all_notices', 10 );
		remove_action( 'woocommerce_before_lost_password_form', 'woocommerce_output_all_notices', 10 );
		remove_action( 'before_woocommerce_pay', 'woocommerce_output_all_notices', 10 );
		remove_action( 'woocommerce_before_reset_password_form', 'woocommerce_output_all_notices', 10 );

		/**
		 * Notices.
		 */
		// add_action( 'woocommerce_cart_is_empty', [ Fns::class, 'woocommerce_output_all_notices' ], 5 );
		add_action( 'woocommerce_shortcode_before_product_cat_loop', [ Fns::class, 'woocommerce_output_all_notices' ], 10 );
		add_action( 'woocommerce_before_shop_loop', [ Fns::class, 'woocommerce_output_all_notices' ], 10 );
		add_action( 'woocommerce_before_single_product', [ Fns::class, 'woocommerce_output_all_notices' ], 10 );
		add_action( 'woocommerce_before_cart', [ Fns::class, 'woocommerce_output_all_notices' ], 10 );
		add_action( 'woocommerce_before_checkout_form_cart_notices', [ Fns::class, 'woocommerce_output_all_notices' ], 10 );
		add_action( 'woocommerce_before_checkout_form', [ Fns::class, 'woocommerce_output_all_notices' ], 10 );
		add_action( 'woocommerce_account_content', [ Fns::class, 'woocommerce_output_all_notices' ], 5 );
		add_action( 'woocommerce_before_customer_login_form', [ Fns::class, 'woocommerce_output_all_notices' ], 10 );
		add_action( 'woocommerce_before_lost_password_form', [ Fns::class, 'woocommerce_output_all_notices' ], 10 );
		add_action( 'before_woocommerce_pay', [ Fns::class, 'woocommerce_output_all_notices' ], 10 );
		add_action( 'woocommerce_before_reset_password_form', [ Fns::class, 'woocommerce_output_all_notices' ], 10 );

		// Checkout Page.
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
		add_action( 'woocommerce_before_checkout_form', [ __CLASS__, 'woocommerce_checkout_login_form' ], 10 );
		add_action( 'woocommerce_before_checkout_form', [ __CLASS__, 'woocommerce_checkout_coupon_form' ], 10 );
		// My Account Order Page.
		add_action( 'woocommerce_before_account_orders', [ __CLASS__, 'before_account_orders' ], 10 );
		add_action( 'woocommerce_after_account_orders', [ __CLASS__, 'after_account_orders' ], 10 );
	}


	/**
	 * @param boolen $has_orders Order exist or not.
	 *
	 * @return void
	 */
	public static function after_account_orders( $has_orders ) {
		if ( ! $has_orders ) {
			echo '</div>';
		}
	}

	/**
	 * @param boolen $has_orders Order exist or not.
	 *
	 * @return void
	 */
	public static function before_account_orders( $has_orders ) {
		if ( ! $has_orders ) {
			echo '<div class="rtsb-notice">';
		}
	}


	/**
	 * @return void
	 */
	public static function woocommerce_checkout_coupon_form() {
		echo '<div class="rtsb-notice">';
			woocommerce_checkout_coupon_form();
		echo '</div>';
	}

	/**
	 * @return void
	 */
	public static function woocommerce_checkout_login_form() {
		echo '<div class="rtsb-notice">';
		woocommerce_checkout_login_form();
		echo '</div>';
	}

	/**
	 * @return void
	 */
	public static function cart_is_empty() {
		echo '<div class="rtsb-notice">';
		wc_empty_cart_message();
		echo '</div>';
	}

	/**
	 * @param int    $post_id Post Ids.
	 * @param object $post Post Object.
	 *
	 * @return void
	 */
	public static function deleted_post( $post_id, $post ) {
		if ( BuilderFns::$post_type_tb !== $post->post_type ) {
			return;
		}

		$options = BuilderFns::option_name_for_specific_product_set_default( $post_id );
		TemplateSettings::instance()->delete_option( $options );

		$template_options = BuilderFns::option_name_by_template_id( $post_id );
		TemplateSettings::instance()->delete_option( $template_options );

		$archive_options = BuilderFns::archive_option_name_by_template_id( $post_id );
		TemplateSettings::instance()->delete_option( $archive_options );

		$category_options = BuilderFns::option_name_for_specific_category_set_default( $post_id );
		TemplateSettings::instance()->delete_option( $category_options );

		$selected_category_options = BuilderFns::archive_option_name_by_template_id( $post_id );
		TemplateSettings::instance()->delete_option( $selected_category_options );

		// Delete Data From Options.
		$options_product_selected_cat = BuilderFns::option_name_product_page_selected_cat( $post_id );
		$cat_list                     = TemplateSettings::instance()->get_option( $options_product_selected_cat );
		if ( ! empty( $cat_list ) ) {
			foreach ( $cat_list as $old_slug ) {
				$OldcatList = get_term_by( 'slug', $old_slug, 'product_cat' );
				if ( ! empty( $OldcatList ) && ! is_wp_error( $OldcatList ) ) {
					delete_term_meta( $OldcatList->term_id, BuilderFns::$categories_product_page_template, $post_id );
				}
			}
		}
		TemplateSettings::instance()->delete_option( $options_product_selected_cat );

		$set_default_option = BuilderFns::option_name_product_page_specific_cat_set_default( $post_id );
		TemplateSettings::instance()->delete_option( $set_default_option );

		// Tags Remove.
		$options_product_selected_tag = BuilderFns::option_name_product_page_selected_tag( $post_id );
		$tag_list                     = TemplateSettings::instance()->get_option( $options_product_selected_tag );
		if ( ! empty( $tag_list ) ) {
			foreach ( $tag_list as $old_slug ) {
				$OldTagList = get_term_by( 'slug', $old_slug, 'product_tag' );
				if ( ! empty( $OldTagList ) && ! is_wp_error( $OldTagList ) ) {
					delete_term_meta( $OldTagList->term_id, BuilderFns::$tags_product_page_template, $post_id );
				}
			}
		}
		TemplateSettings::instance()->delete_option( $options_product_selected_tag );

		// Tag Set Default Option.
		$tag_set_default_option = BuilderFns::option_name_product_page_specific_tag_set_default( $post_id );
		TemplateSettings::instance()->delete_option( $tag_set_default_option );

		global $wpdb;
		// Prepare and execute the SQL query.
		$sql = $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %d", BuilderFns::$view_template_for_products_meta, $post_id );
		$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching

		// Prepare and execute the SQL query.
		$sql = $wpdb->prepare( "DELETE FROM $wpdb->termmeta WHERE meta_key = %s AND meta_value = %d", BuilderFns::$view_template_for_archive_meta, $post_id );
		$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	/**
	 * Meta tags for social sharing.
	 *
	 * @return void
	 */
	public static function shopbuilder_share() {
		$generalList = GeneralList::instance()->get_data();

		if (
			empty( $generalList['social_share']['share_platforms_to_product_page'] ) ||
			'on' !== $generalList['social_share']['share_platforms_to_product_page'] ||
			empty( $generalList['social_share']['share_platforms'] )
		) {
			return;
		}

		$share_platforms = [];

		foreach ( $generalList['social_share']['share_platforms'] as $value ) {
			$share_platforms[ $value ] = [
				'share_items' => $value,
				'share_text'  => ucwords( $value ),
			];
		}

		$settings['share_platforms'] = $share_platforms;
		$settings['layout']          = ! empty( $generalList['social_share']['share_icon_layout'] ) ? $generalList['social_share']['share_icon_layout'] : 1;
		$settings['show_share_icon'] = 1;

		// TODO:: Implement Later ! empty( $generalList['social_share']['share_icon_show_to_product_page'] ) && 'on' === $generalList['social_share']['share_icon_show_to_product_page'];
		Fns::print_html( Render::instance()->social_share_view( 'elementor/general/social-share', $settings ), true );
	}

	/**
	 * Meta tags for social sharing.
	 *
	 * @return void
	 */
	public static function og_metatags_for_sharing() {
		global $post;

		if ( ! isset( $post ) ) {
			return;
		}

		if ( ! is_singular( 'product' ) ) {
			return;
		}

		Fns::print_html( '<meta property="og:url" content="' . get_the_permalink() . '" />', true );
		Fns::print_html( '<meta property="og:type" content="article" />', true );
		Fns::print_html( '<meta property="og:title" content="' . $post->post_title . '" />', true );
		Fns::print_html( '<meta property="og:description" content="' . wp_trim_words( $post->post_content, 150 ) . '" />' );

		$attachment = get_the_post_thumbnail_url();

		if ( ! empty( $attachment ) ) {
			Fns::print_html( '<meta property="og:image" content="' . $attachment . '" />', true );
		}

		Fns::print_html( '<meta property="og:site_name" content="' . get_bloginfo( 'name' ) . '" />', true );
		Fns::print_html( '<meta name="twitter:card" content="summary" />', true );
	}

	/**
	 * Render image.
	 *
	 * @param array $args Image args.
	 * @param array $settings Settings array.
	 *
	 * @return void
	 */
	public static function render_image( $args, $settings ) {
		if ( $args['image_link'] ) {
			$aria_label = esc_attr(
			/* translators: Product Name */
				sprintf( __( 'Image link for Product: %s', 'shopbuilder' ), $args['title'] )
			);
			?>
			<figure>
				<a class="woocommerce-LoopProduct-link rtsb-img-link" href="<?php echo esc_url( $args['p_link'] ); ?>" aria-label="<?php echo esc_attr( $aria_label ); ?>">
					<?php
					Fns::get_product_image( $args['img_html'], $args['hover_img_html'] );
					?>
				</a>
			</figure>
			<?php
		} else {
			echo '<figure class="rtsb-img-link">';
			Fns::get_product_image( $args['img_html'], $args['hover_img_html'] );
			echo '</figure>';
		}
	}

	/**
	 * Modify WooCommerce query arguments for product retrieval.
	 *
	 * @param array $settings The settings array.
	 *
	 * @return void
	 */
	public static function modify_wc_query_args( $settings ) {
		if ( ( isset( $settings['tax_relation'] ) && 'OR' === $settings['tax_relation'] ) ||
			( isset( $settings['relation'] ) && 'OR' === $settings['relation'] ) ) {
			add_filter( 'woocommerce_product_data_store_cpt_get_products_query', [ FilterHooks::class, 'extra_query_args' ], 10, 2 );
		}

		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			add_filter( 'woocommerce_product_data_store_cpt_get_products_query', [ FilterHooks::class, 'extra_query_args_outofstock' ] );
		}
	}

	/**
	 * Reset WooCommerce query arguments for product retrieval.
	 *
	 * @param array $settings The settings array.
	 *
	 * @return void
	 */
	public static function reset_wc_query_args( $settings ) {
		if ( ( isset( $settings['tax_relation'] ) && 'OR' === $settings['tax_relation'] ) ||
			( isset( $settings['relation'] ) && 'OR' === $settings['relation'] ) ) {
			remove_filter( 'woocommerce_product_data_store_cpt_get_products_query', [ FilterHooks::class, 'extra_query_args' ] );
		}

		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			remove_filter( 'woocommerce_product_data_store_cpt_get_products_query', [ FilterHooks::class, 'extra_query_args_outofstock' ] );
		}
	}
}
