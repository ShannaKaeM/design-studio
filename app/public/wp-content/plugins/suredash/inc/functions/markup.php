<?php
/**
 * Markup functions.
 *
 * @package SureDash
 * @since 0.0.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SureDashboard\Inc\Utils\Helper;

/**
 * Get image uploader field.
 *
 * @param string $title The field title.
 * @param string $option The option name.
 * @param bool   $only_input_field Whether to show only the input field.
 * @param bool   $hidden_at_first Whether the field should be hidden at first.
 * @return void
 *
 * @since 0.0.1
 */
function suredash_image_uploader_field( $title, $option, $only_input_field = false, $hidden_at_first = false ): void {
	$image_supports  = apply_filters( 'suredashboard_topic_image_supports', '.jpg, .jpeg, .gif, .png' );
	$extra_class     = $hidden_at_first ? 'portal-hidden-field' : '';
	$max_upload_size = Helper::get_option( 'user_upload_limit' );

	if ( $only_input_field ) {
		?>
			<span class="suredash-upload-block profile-pic-uploader">
				<input class="suredash-upload-size" value="<?php echo esc_attr( $max_upload_size ); ?>" type="hidden"/>
				<input class="suredash-input-upload portal_feed_input" name="<?php echo esc_attr( $option ); ?>" type="file" aria-required="false" accept="<?php echo esc_attr( $image_supports ); ?>">
			</span>
		<?php
		return;
	}

	?>
	<div class="portal-custom-topic-field portal-extended-linked-field portal-featured-image-field <?php echo esc_attr( $extra_class ); ?>">
		<?php if ( ! empty( $title ) ) { ?>
			<label for="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $title ); ?></label>
		<?php } ?>

		<div class="suredash-upload-block">
			<div class="suredash-block-wrap sd-bg-custom sd-relative sd-transition sd-text-center sd-flex sd-items-center sd-justify-center sd-flex-col sd-gap-12 sd-border-dashed sd-hover-border-custom sd-focus-border-custom sd-font-16 sd-font-normal sd-px-16 sd-py-24 sd-text-color sd-radius-6 sd-outline-0"
			style="--sd-bg-custom: hsl( from #1e1e1e h s l / 0.02 ); --sd-hover-border-custom: var(--portal-link-active-color); --sd-focus-border-custom: var(--portal-link-active-color)">
				<span class="suredash-upload-icon">
					<?php Helper::get_library_icon( 'Upload', true, 'lg' ); ?>
				</span>

				<div class="suredash-upload-wrap">
					<input class="suredash-upload-size" value="<?php echo esc_attr( $max_upload_size ); ?>" type="hidden">
					<label class="suredash-classic-upload-label sd-flex sd-flex-col" for="<?php echo esc_attr( $option ); ?>">
						<?php
							esc_html_e( 'Click to upload or drag and drop', 'suredash' );
							echo sprintf(
								'<p class="portal-help-description">%s %s</p>',
								esc_html__( 'Allowed file formats:', 'suredash' ),
								esc_attr( $image_supports )
							);
						?>
						<input class="suredash-input-upload portal_feed_input" name="<?php echo esc_attr( $option ); ?>" type="file" aria-required="false" accept="<?php echo esc_attr( $image_supports ); ?>">
					</label>
				</div>
			</div>

			<div class="suredash-upload-data"></div>

			<div class="suredash-error-wrap sd-font-12"><div class="suredash-error-message" data-error-msg="<?php echo esc_attr__( 'This field is required.', 'suredash' ); ?>"></div></div>
		</div>
	</div>
	<?php
}

/**
 * Get post likes list markup.
 *
 * @param int $post_id Post ID.
 * @return void
 * @since 0.0.1
 */
function suredash_likes_list_markup( $post_id ): void {
	$likes_count = sd_get_post_meta( $post_id, 'portal_post_likes', true );
	$likes_count = is_array( $likes_count ) ? $likes_count : [];

	ob_start();

	if ( count( $likes_count ) ) {
		?>
		<div class="portal-likes-list">
		<?php
		foreach ( $likes_count as $like_user_id ) {
			if ( $like_user_id ) {
				$like_user = get_user_by( 'ID', $like_user_id );
				$user_view = suredash_user_view_link( $like_user_id );
				?>
					<a href="<?php echo esc_url( $user_view ); ?>" target="_blank" class="portal-likes-list-item">
						<?php echo wp_kses_post( (string) suredash_get_user_avatar( $like_user_id ) ); ?>
						<span class="like-user"><?php echo esc_html( is_object( $like_user ) ? $like_user->display_name : '' ); ?></span>
					</a>
				<?php
			}
		}
		?>
		</div>
		<?php
	}

	echo do_shortcode( (string) ob_get_clean() );
}

/**
 * Get Likes Wrapper.
 *
 * @param int $post_id Post ID.
 * @return void
 * @since 0.0.1
 */
function suredash_quick_view_likes_wrapper( $post_id ): void {
	?>
	<div class="portal-post-qv-reaction-wrap">
		<div class="portal-likes-area portal-hidden-field">
			<?php suredash_likes_list_markup( $post_id ); ?>
		</div>
	<?php
}

/**
 * Get Comments List.
 *
 * @param int          $post_id Post ID.
 * @param string       $order Order of comments.
 * @param array<mixed> $params Array of parameters.
 * @return void
 * @since 0.0.1
 */
function suredash_get_comments_list( $post_id, $order = 'ASC', $params = null ): void {
	?>
	<ol class="portal-comment-list sd-no-space">
	<?php
	if ( $params === null || empty( $params ) ) {
		$params = [
			[
				'post_id' => $post_id,
				'order'   => $order,
				'parent'  => 0, // Only get top-level comments.

			],
		];
	}

	$all_comments = []; // Initialize an empty array to store all comments.
	foreach ( $params as $param ) {
		$comments = get_comments( $param );
		if ( is_array( $comments ) ) {
			$existing_comment_ids = array_column( $all_comments, 'comment_ID' );
			foreach ( $comments as $comment ) {
				if ( isset( $comment->comment_ID ) && ! in_array( $comment->comment_ID, $existing_comment_ids ) ) {
					$all_comments[] = $comment;
				}
			}
		}
	}

	wp_list_comments(
		[
			'callback' => 'suredash_comments_list_callback',
			'style'    => 'ol',
		],
		$all_comments
	);
	?>
	</ol>
	<?php
}

/**
 * Comments Markup.
 *
 * @param int          $post_id Post ID.
 * @param bool         $comment_box Whether to show the comment box.
 * @param array<mixed> $params Array of parameters.
 * @param string       $comment_form_class Comment form class.
 * @param string       $comment_box_id_suffix Comment box ID suffix.
 * @return void
 * @since 0.0.1
 */
function suredash_comments_markup( $post_id, $comment_box = false, $params = null, $comment_form_class = '', $comment_box_id_suffix = '' ): void {
	ob_start();

	?>
		<div class="portal-content">
			<?php
			do_action( 'suredashboard_single_comments_before' );
			suredash_get_comments_list( $post_id, 'ASC', $params );
			?>

			<?php
			if ( is_user_logged_in() ) {
				do_action( 'suredashboard_single_comments_before_form' );

				if ( $comment_box ) {
					suredash_comment_box_markup( $post_id, false, $comment_form_class, $comment_box_id_suffix );
				}
				suredash_comment_box_markup( $post_id, true, $comment_form_class );

				do_action( 'suredashboard_single_comments_after_form' );

				do_action( 'suredashboard_single_comments_after' );
			}
			?>
		</div>
	<?php

	echo do_shortcode( (string) ob_get_clean() );
}

/**
 * Get user avatar.
 *
 * @param int  $user_id User ID.
 * @param bool $echo Whether to echo or return the avatar.
 * @param int  $size Avatar size.
 * @return string Avatar markup.
 */
function suredash_get_user_avatar( $user_id, $echo = true, $size = 40 ) {
	$profile_photo = sd_get_user_meta( $user_id, 'user_profile_photo', true );
	$size_class    = 'portal-avatar-' . $size;
	$markup        = wp_kses_post( (string) get_avatar( absint( $user_id ) ) );

	if ( ! empty( $profile_photo ) ) {
		$user_display_name = sd_get_user_meta( absint( $user_id ), 'display_name', true );
		$markup            = wp_kses_post( '<img class="portal-user-avatar ' . esc_attr( $size_class ) . '" src="' . esc_url( $profile_photo ) . '" alt="' . esc_attr( $user_display_name ) . '" />' );
	}

	if ( $echo ) {
		echo do_shortcode( $markup );
		return '';
	}

	return $markup;
}

/**
 * Comment Box Markup.
 *
 * @param int    $post_id Post ID.
 * @param bool   $hidden Whether the comment box should be hidden.
 * @param string $comment_form_class Comment form class.
 * @param string $comment_box_id_suffix Comment box ID suffix.
 * @return void
 * @since 0.0.6
 */
function suredash_comment_box_markup( $post_id, $hidden = false, $comment_form_class = '', $comment_box_id_suffix = '' ): void {
	$comment_box_final_id = $comment_box_id_suffix ? 'jodit-comment-' . $comment_box_id_suffix . '-' . $post_id : 'jodit-comment-' . $post_id;
	ob_start();
	?>
	<div class="sd-flex sd-justify-between sd-items-start sd-gap-8 comment-markup sd-display-none <?php echo esc_attr( $hidden ? ' hidden-comment-markup ' : ' ' ); ?> <?php echo esc_attr( $comment_form_class ); ?>
	" id="inline-comment-box">
		<?php suredash_get_user_avatar( get_current_user_id() ); ?>

		<form action="" method="post" class="jodit-comment-box-wrapper sd-flex sd-flex-col sd-flex-1 sd-justify-center sd-w-full" id="postcommentform">
			<!-- Required fields -->
			<input type="hidden" name="comment_post_ID" value="<?php echo esc_attr( strval( $post_id ) ); ?>" />
			<input type="hidden" name="comment_parent" value="0" />

			<!-- The actual comment input box -->
			<textarea
				class="<?php echo esc_attr( $hidden ? 'hidden-jodit-comment' : 'jodit-comment' ); ?>"
				name="comment"
				id="<?php echo esc_attr( $comment_box_final_id ); ?>"
				autocomplete="off"
			></textarea>

			<button type="submit" class="post-comment-box-submit">
				<?php Helper::get_library_icon( 'SendHorizontal', true, 'sm' ); ?>
				<?php Helper::get_library_icon( 'LoaderCircle', true, 'sm', 'sd-display-none' ); ?>
			</button>
		</form>
		</form>
	</div>

	<?php
	echo do_shortcode( (string) ob_get_clean() );
}
