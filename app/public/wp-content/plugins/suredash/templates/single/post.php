<?php
/**
 * The template for displaying archive portals view.
 *
 * This template can be overridden by copying it to yourTheme/suredashboard/single/post.php.
 *
 * @see     https://developer.wordpress.org/themes/basics/template-hierarchy/
 * @package SureDash\Templates
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

use SureDashboard\Inc\Utils\Helper;
use SureDashboard\Inc\Utils\Labels;
use SureDashboard\Inc\Utils\PostMeta;

$default_args = [
	'post'         => null,
	'base_post_id' => 0,
	'is_pinned'    => false,
	'comments'     => true,
];

if ( ! isset( $args ) ) {
	$args = [];
}

$args = wp_parse_args( $args, $default_args );

if ( is_null( $args['post'] ) || ! isset( $post ) ) {
	return;
}

$author_id    = $post['post_author'] ?? '';
$post_title   = $post['post_title'] ?? '';
$p_id         = absint( $post['ID'] );
$p_type       = $post['post_type'] ?? '';
$post_date    = $post['post_date'] ?? '';
$p_comments   = $args['comments'] === 'open' ? true : false;
$permalink    = (string) get_the_permalink( $p_id );
$bookmarked   = suredash_is_item_bookmarked( $p_id );
$bookmarked   = $bookmarked ? 'bookmarked' : '';
$content_type = isset( $base_post_id ) && $base_post_id ? PostMeta::get_post_meta_value( $base_post_id, 'post_content_type' ) : 'full_content';
$user_view    = suredash_user_view_link( $author_id );
$queried_page = suredash_get_sub_queried_page();

// Enforce excerpt content type for user-view & feeds page.
$content_type = $queried_page === 'user-view' || $queried_page === 'feeds' ? 'excerpt' : $content_type;

if ( apply_filters( 'suredash_post_enforce_excerpt_content', false ) ) {
	$content_type = 'excerpt';
}

$post_content = Helper::get_post_content( $p_id, $content_type );

do_action( 'suredashboard_single_post_template', $p_id );

?>
<div id="portal-post-<?php echo esc_attr( (string) $p_id ); ?>" class="portal-store-list-post sd-box-shadow portal-content sd-relative sd-p-20 sd-bg-content sd-border sd-radius-8 sd-overflow-hidden sd-transition-fast">
	<section class="portal-store-post-header">
		<div class="portal-store-post-author-data sd-flex sd-justify-between sd-items-center">
			<div class="portal-store-post-author-wrap sd-w-full sd-flex sd-items-center">
				<?php suredash_get_user_avatar( $author_id ); ?>

				<div class="portal-post-author sd-flex-col sd-font-base sd-line-20">
					<a href="<?php echo esc_url( $user_view ); ?>"> <span class="portal-store-post-author"><?php echo esc_html( suredash_get_author_name( get_the_author_meta( 'display_name', $author_id ) ) ); ?></span> </a>
					<a href="<?php echo esc_url( $permalink ); ?>" target="_self">
						<span class="portal-store-post-publish-date sd-font-12 sd-line-16">
							<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $post_date ) ) ); ?>
						</span>
					</a>
				</div>
			</div>
			<div class="portal-store-post-actions sd-flex sd-relative sd-gap-8 sd-items-center">
				<?php
				if ( isset( $is_pinned ) && $is_pinned ) {
					?>
						<div class="portal-pinned-post-wrapper sd-nowrap">
							<?php
								Helper::show_badge(
									'neutral',
									'Pin',
									Labels::get_label( 'pinned_post' ),
									'sm',
								);
							?>
						</div>
					<?php
				}
				?>
				<span class="portal-post-bookmark-trigger sd-flex sd-items-center <?php echo esc_attr( $bookmarked ); ?>" data-item_id="<?php echo esc_attr( (string) $p_id ); ?>" title="<?php esc_attr_e( 'Bookmark Post', 'suredash' ); ?>">
					<?php Helper::get_library_icon( 'Bookmark', true ); ?>
				</span>
			</div>
		</div>
	</section>

	<div class="portal-content-post-content sd-border-t sd-pt-20 sd-mt-20">
		<h3 class="portal-store-post-title"><?php echo esc_html( $post_title ); ?></h3>

		<?php echo do_shortcode( wpautop( $post_content ) ); ?>

		<?php
		if ( $content_type === 'excerpt' ) {
			printf(
				'<a href="%1$s" data-post_id="%2$s" data-post_type="%3$s" data-comments="%4$s" class="portal-read-more-post more-link">%5$s</a>',
				esc_url( $permalink ),
				esc_attr( (string) $p_id ),
				esc_attr( $p_type ),
				esc_attr( (string) $p_comments ),
				sprintf( /* translators: %1$s Read More, %2$s Post title markup */'%1$s %2$s', esc_html( Labels::get_label( 'read_more' ) ), '<span class="screen-reader-text">' . esc_html( $post_title ) . '</span>' )
			);
		}
		?>
	</div>

	<?php Helper::suredash_featured_cover( $p_id ); ?>

	<div class="portal-comments-wrapper sd-w-full sd-mt-24 sd-border-t">
		<?php Helper::render_post_reaction( $p_id, 'portal-comments-block', $p_comments ); ?>
	</div>
</div>
<?php
