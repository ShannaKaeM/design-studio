<?php
/**
 * The template for displaying archive portals view.
 *
 * This template can be overridden by copying it to yourTheme/suredashboard/comments.php.
 *
 * @see     https://developer.wordpress.org/themes/basics/template-hierarchy/
 * @package SureDash\Templates
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

use SureDashboard\Inc\Utils\Helper;

if ( ! is_user_logged_in() ) {
	return;
}

$current_post_id = absint( ! empty( $post_id ) ? $post_id : get_the_ID() );
$in_qv           = $in_qv ?? false;
$p_comments      = boolval( $comments ?? true );
$comments_wrap   = $in_qv ? 'portal-qv-reaction-wrapper' : '';
$comments_wrap   = $p_comments ? $comments_wrap : $comments_wrap . ' portal-comments-disabled';

if ( ! $current_post_id ) {
	return;
}

?>

<div id="portal-comment" class="portal-comments-wrapper portal-container portal-content <?php echo esc_attr( $comments_wrap ); ?>">
	<?php
	if ( $in_qv ) {
		Helper::render_post_reaction( $current_post_id, 'portal-comments-trigger', $in_qv === true ? false : $p_comments );
		suredash_quick_view_likes_wrapper( $current_post_id );
		if ( ! $p_comments ) {
			?>
			</div>
			<?php
			return;
		}
	} else {
		Helper::render_post_reaction( $current_post_id, 'portal-comments-trigger' );
		?>
		</div>
		<?php
		return;
	}

	do_action( 'suredashboard_single_comments_before' );
	?>

	<div class="portal-comments-inner-wrap portal-content">
		<div class="portal-comments-area">
			<?php
			suredash_comments_markup( $current_post_id, true, null, '', 'qv' );
			?>
		</div>
	</div>

	<?php
	// @phpstan-ignore-next-line.
	if ( $in_qv ) {
		?>
		</div> <!-- .portal-post-qv-reaction-wrap ends. -->
	<?php } ?>
</div>
