<?php
/**
 * Template: Post List
 *
 * @package RadiusTheme\SB
 */

/**
 * Template variables:
 *
 * @var $grid_classes           string
 * @var $title                  string
 * @var $title_link             string
 * @var $excerpt                string
 * @var $title_tag              string
 * @var $title_limit            string
 * @var $excerpt_limit          string
 * @var $button_text            string
 * @var $show_title             boolean
 * @var $show_categories        boolean
 * @var $show_tags              boolean
 * @var $show_dates             boolean
 * @var $show_author            boolean
 * @var $show_post_thumbnail    boolean
 * @var $show_short_desc        boolean
 * @var $image_link             boolean
 * @var $show_read_more_btn     boolean
 * @var $img_html               string
 * @var $button_icon            array
 * @var $button_icon_position   string
 * @var $item_class             string
 * array
 */

// Do not allow directly accessing this file.


use RadiusTheme\SB\Elementor\Helper\PostHelpers;
use RadiusTheme\SB\Elementor\Widgets\General\PostList\Render;
use RadiusTheme\SB\Helpers\Fns;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}
$date_icon = '<svg xmlns="https://www.w3.org/2000/svg" viewBox="0 0 448 512" width="1em" height="1em" fill="currentColor"><path d="M0 464c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V192H0v272zm320-196c0-6.6 5.4-12 12-12h40c6.6 0 12 5.4 12 12v40c0 6.6-5.4 12-12 12h-40c-6.6 0-12-5.4-12-12v-40zm0 128c0-6.6 5.4-12 12-12h40c6.6 0 12 5.4 12 12v40c0 6.6-5.4 12-12 12h-40c-6.6 0-12-5.4-12-12v-40zM192 268c0-6.6 5.4-12 12-12h40c6.6 0 12 5.4 12 12v40c0 6.6-5.4 12-12 12h-40c-6.6 0-12-5.4-12-12v-40zm0 128c0-6.6 5.4-12 12-12h40c6.6 0 12 5.4 12 12v40c0 6.6-5.4 12-12 12h-40c-6.6 0-12-5.4-12-12v-40zM64 268c0-6.6 5.4-12 12-12h40c6.6 0 12 5.4 12 12v40c0 6.6-5.4 12-12 12H76c-6.6 0-12-5.4-12-12v-40zm0 128c0-6.6 5.4-12 12-12h40c6.6 0 12 5.4 12 12v40c0 6.6-5.4 12-12 12H76c-6.6 0-12-5.4-12-12v-40zM400 64h-48V16c0-8.8-7.2-16-16-16h-32c-8.8 0-16 7.2-16 16v48H160V16c0-8.8-7.2-16-16-16h-32c-8.8 0-16 7.2-16 16v48H48C21.5 64 0 85.5 0 112v48h448v-48c0-26.5-21.5-48-48-48z"></path></svg>';
?>
<div class="<?php echo esc_attr( $grid_classes ); ?>">
	<div class="<?php echo esc_attr( $item_class ); ?>">
		<article>
			<?php if ( $show_post_thumbnail ) { ?>
				<div class="rtsb-post-img rtsb-img-wrap">
					<?php
					if ( $image_link ) {
						$aria_label = esc_attr(
						/* translators: Post Name */
							sprintf( __( 'Image link for Post: %s', 'shopbuilder' ), $title )
						);
						?>
						<figure>
							<a class="rtsb-img-link" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( $aria_label ); ?>">
								<?php
								Fns::print_html( $img_html );
								?>
							</a>
						</figure>
						<?php
					} else {
						echo '<figure class="rtsb-img-link">';
						Fns::print_html( $img_html );
						echo '</figure>';
					}
					?>
					<?php
					if ( $show_categories || $show_tags ) {
						?>
						<ul class="rtsb-post-taxonomy-list">
							<?php
							if ( $show_categories ) {
								Fns::print_html( '<li class="rtsb-tax-item category">' );
								Fns::print_html( PostHelpers::rtsb_posted_taxonomy() );
								Fns::print_html( '</li>' );
							}
							if ( $show_tags ) {
								Fns::print_html( '<li class="rtsb-tax-item tag">' );
								Fns::print_html( PostHelpers::rtsb_posted_taxonomy( 'tag' ) );
								Fns::print_html( '</li>' );
							}
							?>

						</ul>
						<?php
					}
					?>
				</div>
			<?php } ?>
			<div class="rtsb-post-content">

				<?php
				if ( $show_title ) {
					?>
					<<?php Fns::print_validated_html_tag( $title_tag ); ?> class="rtsb-post-title limit-<?php echo esc_attr( $title_limit ); ?>">
						<?php
						if ( $title_link ) {
							?>
							<a class="rtsb-title-link" href="<?php the_permalink(); ?>"><?php Fns::print_html( $title ); ?></a>
							<?php
						} else {
							Fns::print_html( $title );
						}
						?>
					</<?php Fns::print_validated_html_tag( $title_tag ); ?>>
					<?php
				}
				if ( $show_dates || $show_author ) {
					?>
					<ul class="rtsb-post-meta">
						<?php
						if ( $show_author ) {
							$prefix = esc_html__( 'By ', 'shopbuilder' );
							$prefix = apply_filters( 'rtsb/general_widgets/posts_author_prefix', $prefix );
							Fns::print_html( '<li class="rtsb-meta-item author">' );
							Fns::print_html( PostHelpers::rtsb_posted_by( $prefix ) );
							Fns::print_html( '</li>' );
						}
						if ( $show_dates ) {
							Fns::print_html( '<li class="rtsb-meta-item date">' );
							Fns::print_html( $date_icon );
							Fns::print_html( PostHelpers::rtsb_posted_date() );
							Fns::print_html( '</li>' );
						}
						?>
					</ul>
					<?php
				}
				?>
				<?php
				if ( $show_short_desc ) {
					?>
					<div class="rtsb-post-excerpt limit-<?php Fns::print_html( $excerpt_limit ); ?>">
						<?php Fns::print_html( $excerpt ); ?>
					</div>
					<?php
				}
				?>
				<?php
				/**
				 * Button.
				 */
				if ( $show_read_more_btn ) {
					?>
					<div class="rtsb-button-wrapper">
						<a class="rtsb-readmore-btn" href="<?php the_permalink(); ?>">
							<span class="text-wrap">
								<?php
								Fns::print_html( Render::render_icon( 'left', $button_icon, $button_icon_position ) );
								Fns::print_html( $button_text );
								Fns::print_html( Render::render_icon( 'right', $button_icon, $button_icon_position ) );
								?>
							</span>
						</a>
					</div>
				<?php } ?>
			</div>
		</article>
	</div>
</div>
