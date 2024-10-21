<?php
/**
 * Builder Custom post type
 *
 * @package RadiusTheme\SB
 */

namespace RadiusTheme\SB\Controllers\Builder;

defined( 'ABSPATH' ) || exit();

use RadiusTheme\SB\Models\TemplateSettings;
use WP_Query;
use RadiusTheme\SB\Helpers\Fns;
use RadiusTheme\SB\Helpers\BuilderFns;
use RadiusTheme\SB\Traits\SingletonTrait;

/**
 * Builder Custom post type
 */
class BuilderCpt {
	/**
	 * Singleton
	 */
	use SingletonTrait;

	/**
	 * Final constructor.
	 */
	private function __construct() {
		add_action( 'init', [ $this, 'template_builder_post_type' ] );
		add_filter( 'post_row_actions', [ $this, 'filter_post_row_actions' ], 11, 1 );
		// Admin column.
		add_filter( 'manage_edit-' . BuilderFns::$post_type_tb . '_columns', [ $this, 'add_new_columns' ] );
		add_action( 'manage_' . BuilderFns::$post_type_tb . '_posts_custom_column', [ $this, 'custom_columns' ], 10, 2 );
		add_filter( 'manage_edit-' . BuilderFns::$post_type_tb . '_sortable_columns', [ $this, 'register_sortable_columns' ] );
		add_action( 'pre_get_posts', [ $this, 'sortable_columns_query' ] );
		add_filter( 'template_include', [ $this, 'builder_template' ], 99 );
		add_filter( 'template_redirect', [ $this, 'restrict_preview_access' ], 99 );

		add_filter( 'parse_query', [ $this, 'query_filter' ] );
		// add filter for search.
		add_action( 'restrict_manage_posts', [ $this, 'add_filter' ] );

		add_action( 'in_admin_header', [ $this, 'remove_all_notices' ], 1000 );
	}


	/**
	 * Public function add_filter.
	 * Added search filter for type of template
	 *
	 * @since 1.0.0
	 */
	public function add_filter() {

		global $typenow;

		if ( BuilderFns::$post_type_tb != $typenow ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$selected           = isset( $_GET['type'] ) ? sanitize_key( $_GET['type'] ) : '';
		$builder_page_types = BuilderFns::builder_page_types();
		?>
		<select name="template_type" id="type">
			<option value="all" <?php selected( 'all', $selected ); ?>><?php esc_html_e( 'Template Type ', 'shopbuilder' ); ?></option>
			<?php foreach ( $builder_page_types as $key => $value ) { ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $selected ); ?>><?php echo esc_html( $value ); ?></option>
			<?php } ?>
		</select>
		<?php
	}

	/**
	 * Post type function
	 *
	 * @return void
	 */
	public function template_builder_post_type() {
		/**
		 * Template Builder Post type
		 */
		$tb_labels = [
			'name'                  => esc_html_x( 'WooCommerce Page Builder', 'Post Type General Name', 'shopbuilder' ),
			'singular_name'         => esc_html_x( 'Templates Builder', 'Post Type Singular Name', 'shopbuilder' ),
			'menu_name'             => esc_html__( 'Templates Builder', 'shopbuilder' ),
			'name_admin_bar'        => esc_html__( 'Templates Builder', 'shopbuilder' ),
			'archives'              => esc_html__( 'WC Page Archives', 'shopbuilder' ),
			'attributes'            => esc_html__( 'Page Attributes', 'shopbuilder' ),
			'parent_item_colon'     => esc_html__( 'Parent Item:', 'shopbuilder' ),
			'all_items'             => esc_html__( 'Templates Builder', 'shopbuilder' ),
			'add_new_item'          => esc_html__( 'Add New Page', 'shopbuilder' ),
			'add_new'               => esc_html__( 'Add New', 'shopbuilder' ),
			'new_item'              => esc_html__( 'New Page', 'shopbuilder' ),
			'edit_item'             => esc_html__( 'Edit Page', 'shopbuilder' ),
			'update_item'           => esc_html__( 'Update Page', 'shopbuilder' ),
			'view_item'             => esc_html__( 'View Page', 'shopbuilder' ),
			'view_items'            => esc_html__( 'View Pages', 'shopbuilder' ),
			'search_items'          => esc_html__( 'Search Pages', 'shopbuilder' ),
			'not_found'             => esc_html__( 'Not found', 'shopbuilder' ),
			'not_found_in_trash'    => esc_html__( 'Not found in Trash', 'shopbuilder' ),
			'featured_image'        => esc_html__( 'Featured Image', 'shopbuilder' ),
			'set_featured_image'    => esc_html__( 'Set featured image', 'shopbuilder' ),
			'remove_featured_image' => esc_html__( 'Remove featured image', 'shopbuilder' ),
			'use_featured_image'    => esc_html__( 'Use as featured image', 'shopbuilder' ),
			'insert_into_item'      => esc_html__( 'Insert into page', 'shopbuilder' ),
			'uploaded_to_this_item' => esc_html__( 'Uploaded to this page', 'shopbuilder' ),
			'items_list'            => esc_html__( 'Pages list', 'shopbuilder' ),
			'items_list_navigation' => esc_html__( 'Pages list navigation', 'shopbuilder' ),
			'filter_items_list'     => esc_html__( 'Filter from list', 'shopbuilder' ),
		];

		$tb_args = [
			'label'              => esc_html__( 'Templates Builder', 'shopbuilder' ),
			'description'        => esc_html__( 'Shopbuilder Template', 'shopbuilder' ),
			'labels'             => $tb_labels,
			'supports'           => [ 'title', 'editor', 'elementor', 'author', 'permalink', 'comments' ],
			'hierarchical'       => false,
			'public'             => true,
			'show_ui'            => true,
			'show_in_menu'       => 'rtsb',
			'show_in_admin_bar'  => false,
			'show_in_nav_menus'  => true,
			'can_export'         => true,
			'has_archive'        => false,
			'rewrite'            => [
				'slug'       => 'rtsb-template',
				'pages'      => false,
				'with_front' => true,
				'feeds'      => false,
			],
			'query_var'          => true,
			'publicly_queryable' => true,
			// 'capability_type'    => 'product', Will Remove this line in the future.
			'show_in_rest'       => true,
			'rest_base'          => BuilderFns::$post_type_tb,
		];

		$tb_args = apply_filters( 'rtsb/builder/template_args', $tb_args );

		register_post_type( BuilderFns::$post_type_tb, $tb_args );

		// Flash rewrite rules.
		$this->flush_rewrite_rules();
	}

	/**
	 * Flush rewrite
	 *
	 * @return void
	 */
	public function flush_rewrite_rules() {
		if ( get_option( 'shopbuilder_permalinks_flushed', 'no' ) !== 'yes' ) {
			flush_rewrite_rules();
			update_option( 'shopbuilder_permalinks_flushed', 'yes' );
		}
	}

	/**
	 * Add new columns to the post table
	 *
	 * @param array $columns - Current columns on the list post.
	 */
	public function add_new_columns( $columns ) {
		$column_meta = [
			'type'        => esc_html__( 'Template Type', 'shopbuilder' ),
			'edit_with'   => esc_html__( 'Editor Type', 'shopbuilder' ),
			'set_default' => esc_html__( 'Set as Active', 'shopbuilder' ),
		];

		unset( $columns['comments'] );

		$columns = array_merge(
			array_slice( $columns, 0, 2 ),
			$column_meta,
			array_slice( $columns, 2 )
		);

		return $columns;
	}

	/**
	 * Display data in new columns
	 *
	 * @param string $column table column.
	 * @param string $post_id Post id.
	 *
	 * @return void
	 */
	public function custom_columns( $column, $post_id ) {
		$post_id       = absint( $post_id );
		$types         = BuilderFns::builder_page_types();
		$template_type = BuilderFns::builder_type( $post_id ) ? BuilderFns::builder_type( $post_id ) : array_key_first( $types );

		switch ( $column ) {
			case 'type':
				$the_type = ! empty( $types[ $template_type ] ) ? '<b>' . esc_html( ucwords( $types[ $template_type ] ) ) . '</b>' : '';
				Fns::print_html( apply_filters( 'rtsb/columns/template/types/text', $the_type, $template_type, $post_id ) );
				break;
			case 'edit_with':
				$edit_by = Fns::page_edit_with( $post_id );
				echo esc_html( ucfirst( $edit_by ) );
				break;
			case 'set_default':
				$is_set_default = absint( BuilderFns::builder_page_id_by_type( $template_type ) );
				$is_published   = 'publish' === get_post_status( $post_id );
				$page_type_for  = $template_type;
				if ( 'product' === $template_type ) {
					$product_page_for = get_post_meta( $post_id, '_is_product_page_template_for', true );
					switch ( $product_page_for ) {
						case 'specific_products':
							$page_type_for = 'template-' . $post_id . '-specific-products';
							$set_default   = BuilderFns::get_specific_product_as_default( $post_id );
							if ( $set_default ) {
								$is_set_default = $post_id;
							}
							break;
						case 'product_cats':
							$page_type_for           = 'product-page-template-' . $post_id . '-specific-category';
							$set_default_option_name = BuilderFns::option_name_product_page_specific_cat_set_default( $post_id );
							$pp_cat_set_default      = TemplateSettings::instance()->get_option( $set_default_option_name );
							if ( $pp_cat_set_default ) {
								$is_set_default = $post_id;
							}
							break;
						case 'product_tags':
							$page_type_for           = 'product-page-template-' . $post_id . '-specific-tag';
							$set_default_option_name = BuilderFns::option_name_product_page_specific_tag_set_default( $post_id );
							$pp_tag_set_default      = TemplateSettings::instance()->get_option( $set_default_option_name );
							if ( $pp_tag_set_default ) {
								$is_set_default = $post_id;
							}
							break;
						default:
							break;
					}
				} elseif ( 'archive' === $template_type ) {
					$categories_name = apply_filters( 'rtsb/template/builder/selected/categories', [], $post_id, $template_type );
					$set_default     = BuilderFns::get_specific_category_as_default( $post_id );
					if ( ! empty( $categories_name ) && $set_default ) {
						$is_set_default = $post_id;
						$page_type_for  = 'template-' . $post_id . '-specific-category';
					}
				}
				?>
				<span class="rtsb-switch-wrapper page-type-<?php echo esc_attr( $page_type_for ); ?>" <?php echo ! $is_published ? 'style="pointer-events: none;"' : null; ?> title="<?php esc_html_e( 'Only publish post can set default ', 'shopbuilder' ); ?>">
					<label class="rtsb-switch">
						<?php do_action( 'rtsb/set/default/column/extra/field', $post_id, $template_type, $is_set_default, $is_published ); ?>
						<div class="rtsb_template_type" data-template_type="<?php echo esc_attr( $template_type ); ?>" style='display:none;'></div>
						<input disabled="disabled" value="<?php echo absint( $post_id ); ?>" class="rtsb_set_default" name="set_default" type="checkbox" <?php echo esc_attr( $post_id === $is_set_default ? 'checked' : '' ); ?> >
						<span class="rtsb-slider rtsb-round ">
							<span class="rtsb-loader"></span>
						</span>
					</label>
				</span>
				<?php
				break;
		}
	}

	/**
	 * Register sortable columns
	 *
	 * @param [type] $columns column list.
	 *
	 * @return array
	 */
	public function register_sortable_columns( $columns ) {
		$columns['type'] = 'type';

		return $columns;
	}

	/**
	 * Meta sortable function.
	 *
	 * @param object $query query object.
	 *
	 * @return void
	 */
	public function sortable_columns_query( $query ) {
		if ( ! is_admin() || ! $this->is_current_screen() ) {
			return;
		}
		$orderby = $query->get( 'orderby' );
		if ( 'type' === $orderby ) {
			$query->set( 'meta_key', BuilderFns::template_type_meta_key() );
			$query->set( 'orderby', 'meta_value' );
		}
	}

	/**
	 * Check template screen
	 *
	 * @return boolean
	 */
	public function is_current_screen() {
		global $pagenow, $typenow;

		return 'edit.php' === $pagenow && BuilderFns::$post_type_tb === $typenow;
	}

	/**
	 * Manage Template filter by template type
	 *
	 * @param WP_Query $query WordPress main query.
	 *
	 * @return void
	 */
	public function query_filter( WP_Query $query ) {

		if ( ! is_admin() || ! $this->is_current_screen() || ! empty( $query->query_vars['meta_key'] ) ) {
			return;
		}
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$type = isset( $_GET['template_type'] ) ? sanitize_key( $_GET['template_type'] ) : '';

		if ( '' != $type && 'all' != $type ) {  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$query->query_vars['meta_key']     = BuilderFns::template_type_meta_key(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$query->query_vars['meta_value']   = $type; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			$query->query_vars['meta_compare'] = '=';
		}
	}

	/**
	 * Load Template.
	 *
	 * @param string $template Template File.
	 *
	 * @return string
	 */
	public function builder_template( $template ) {
		$builder_page_id = BuilderFns::is_builder_preview() ? get_the_ID() : BuilderFns::builder_page_id_by_page();
		if ( $builder_page_id ) {
			$page_template = get_post_meta( $builder_page_id, '_wp_page_template', true );
			$new_template  = RTSB_ABSPATH . 'page-templates/builder-template.php';
			if ( 'elementor_canvas' === $page_template ) {
				$new_template = RTSB_ABSPATH . 'page-templates/elementor-canvas.php';
			} elseif ( 'elementor_header_footer' === $page_template ) {
				$new_template = RTSB_ABSPATH . 'page-templates/builder-template.php';
			}
			if ( file_exists( $new_template ) ) {
				$template = $new_template;
			}
		}

		return $template;
	}

	/**
	 * Restrict the preview access.
	 *
	 * @return void
	 */
	public function restrict_preview_access() {
		if ( ! apply_filters( 'rtsb/builder/preview/access', true ) ) {
			return;
		}

		if ( is_singular( 'rtsb_builder' ) ) {
			if ( ! is_user_logged_in() ) {
				wp_safe_redirect( home_url() );
			}
		}
	}

	/**
	 * Add/Remove edit link in dashboard.
	 *
	 * Add or remove an edit link to the post/page action links on the post/pages list table.
	 *
	 * Fired by `post_row_actions` and `page_row_actions` filters.
	 *
	 * @access public
	 *
	 * @param array $actions An array of row action links.
	 *
	 * @return array An updated array of row action links.
	 */
	public function filter_post_row_actions( $actions ) {
		if ( $this->is_current_screen() ) {
			// unset( $actions['view'] );
			unset( $actions['inline hide-if-no-js'] );

			if ( isset( $actions['view'] ) ) {
				$actions['view'] = str_replace( __( 'View', 'shopbuilder' ), __( 'Preview', 'shopbuilder' ), $actions['view'] );
			}
		}

		return $actions;
	}

	/**
	 * Remove admin notices
	 */
	public function remove_all_notices() {
		$screen = get_current_screen();
		if ( 'edit-rtsb_builder' === $screen->id ) {
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
		}
	}
}
