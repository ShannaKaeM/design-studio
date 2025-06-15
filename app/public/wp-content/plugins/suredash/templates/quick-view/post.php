<?php
/**
 * The template for displaying space content area view.
 *
 * @see     https://developer.wordpress.org/themes/basics/template-hierarchy/
 * @package SureDash\Templates
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

use SureDashboard\Inc\Utils\Helper;

$p_id       = absint( ! empty( $_GET['post_id'] ) ? $_GET['post_id'] : 0 ); // phpcs:ignore
$p_comments = absint( ! empty( $_GET['comments'] ) ? $_GET['comments'] : 0 ); // phpcs:ignore

if ( ! $p_id ) {
	return;
}

$bookmarked = suredash_is_item_bookmarked( $p_id );
$bookmarked = $bookmarked ? 'bookmarked' : '';
$post_date  = get_the_date( '', $p_id );
$author_id  = absint( get_post_field( 'post_author', $p_id ) );
$permalink  = (string) get_permalink( $p_id );
$user_view  = suredash_user_view_link( $author_id );

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="suredash-quick-post">
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<?php do_action( 'suredash_enqueue_scripts' ); ?>
		<?php wp_head(); ?>
	</head>

	<body <?php body_class( 'suredash-quick-view-content' ); ?>>
		<?php wp_body_open(); ?>

		<div id="portal-post-<?php echo esc_attr( (string) $p_id ); ?>" <?php post_class( 'portal-qv-post portal-content portal-container' ); ?>>
			<div class="portal-quick-view-header portal-content">
				<div class="portal-qv-author-header">
					<div class="portal-store-post-author-wrap sd-w-full sd-flex sd-items-center">
						<?php suredash_get_user_avatar( $author_id ); ?>

						<div class="portal-post-author sd-flex-col sd-font-base sd-line-20">
							<a href="<?php echo esc_url( $user_view ); ?>"> <span class="portal-store-post-author"><?php echo esc_html( suredash_get_author_name( get_the_author_meta( 'display_name', $author_id ) ) ); ?></span> </a>
							<a href="<?php echo esc_url( $permalink ); ?>" target="_self">
								<span class="portal-store-post-publish-date sd-font-12 sd-line-16">
									<?php echo esc_html( (string) date_i18n( get_option( 'date_format' ), strtotime( (string) $post_date ) ) ); ?>
								</span>
							</a>
						</div>
					</div>
				</div>

				<div class="portal-qv-triggers">
					<div class="portal-store-post-actions sd-flex sd-relative sd-gap-8 sd-items-center">
						<span class="portal-post-bookmark-trigger <?php echo esc_attr( $bookmarked ); ?>" data-item_id="<?php echo esc_attr( (string) $p_id ); ?>" title="<?php esc_attr_e( 'Bookmark Post', 'suredash' ); ?>">
							<?php Helper::get_library_icon( 'Bookmark', true, 'sm' ); ?>
						</span>
					</div>
				</div>
			</div>
			<?php do_action( 'suredashboard_quick_view_post_content', $p_id, $p_comments ); ?>
		</div>

		<?php if ( ! is_user_logged_in() ) { ?>
			<div class="sd-pt-16">
				<div class="comment-modal-login-notice sd-w-full sd-flex sd-items-center sd-justify-center sd-p-8 sd-radius-6">
					<?php Helper::get_login_notice( 'comment' ); ?>
				</div>
			</div>
		<?php } ?>

		<?php wp_footer(); ?>
	</body>
</html>

<?php wp_reset_postdata(); ?>
