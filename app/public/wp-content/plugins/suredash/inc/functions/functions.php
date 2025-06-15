<?php
/**
 * Plugin functions.
 *
 * @package SureDash
 * @since 0.0.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SureDashboard\Inc\Services\Query;
use SureDashboard\Inc\Services\Router;
use SureDashboard\Inc\Utils\Helper;
use SureDashboard\Inc\Utils\Labels;
use SureDashboard\Inc\Utils\PostMeta;

/**
 * Check if pro version is active.
 *
 * @return bool
 * @since 0.0.1
 */
function suredash_is_pro_active() {
	return defined( 'SUREDASH_PRO_VER' );
}

/**
 * Clean variables using sanitize_text_field.
 *
 * @param mixed $var Data to sanitize.
 * @return mixed
 *
 * @since 0.0.1
 */
function suredash_clean_data( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'suredash_clean_data', $var );
	}
	return is_scalar( $var ) ? sanitize_text_field( (string) $var ) : $var;
}

/**
 * Get template part implementation for Portals.
 *
 * @param string       $slug Template slug.
 * @param string       $name Template name.
 * @param array<mixed> $args Template passing data.
 * @param bool         $return Flag for return with ob_start.
 *
 * @return string Return html file.
 * @since 0.0.1
 */
function suredash_get_template_part( $slug, $name = '', $args = [], $return = false ) {
	if ( $args && is_array( $args ) ) {
		extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
	}

	$template = '';

	// Maybe in yourtheme/suredash/slug-name.php and yourtheme/suredash/slug.php.
	$template_path = ! empty( $name ) ? "{$slug}/{$name}.php" : "{$slug}.php";
	$template      = locate_template( [ 'suredash/' . $template_path ] );

	/**
	 * Change template directory path filter.
	 *
	 * @since 0.0.1
	 */
	$template_path = apply_filters( 'suredash_set_template_path', untrailingslashit( SUREDASHBOARD_DIR ) . '/templates', $template, $args );

	// Get default slug-name.php.
	if ( ! $template && $name && file_exists( $template_path . "/{$slug}/{$name}.php" ) ) {
		$template = $template_path . "/{$slug}/{$name}.php";
	}

	if ( ! $template && ! $name && file_exists( $template_path . "/{$slug}.php" ) ) {
		$template = $template_path . "/{$slug}.php";
	}

	if ( $template ) {
		if ( $return ) {
			ob_start();
			require $template;
			return (string) ob_get_clean();
		}
		require $template;
		return '';
	}

	return '';
}

/**
 * Get template part implementation for restricted content.
 *
 * @param int          $post_id Post ID.
 * @param string       $slug Template slug.
 * @param string       $name Template slug.
 * @param array<mixed> $args Template passing data.
 * @param bool         $return Flag for return with ob_start.
 *
 * @return mixed
 * @since 1.0.0
 */
function suredash_get_restricted_template_part( $post_id, $slug, $name = '', $args = [], $return = false ) {
	$restriction_details = Helper::maybe_third_party_restricted( $post_id );
	$may_restricted      = $restriction_details['status'] ?? false;
	$restriction_content = $restriction_details['content'] ?? false;

	if ( $may_restricted && $restriction_content ) {
		echo do_shortcode( $restriction_content );
	} else {
		suredash_get_template_part(
			$slug,
			$name,
			[
				'icon'        => 'Lock',
				'label'       => 'restricted_content',
				'description' => 'restricted_content_description',
			],
			$return
		);
	}
}

/**
 * Foreground Color
 *
 * @param string $hex Color code in HEX format.
 * @return string      Return foreground color depend on input HEX color.
 */
function suredash_get_foreground_color( $hex ) {
	$hex = apply_filters( 'suredashboard_before_foreground_color_generation', $hex );

	// bail early if color's not set.
	if ( $hex === 'transparent' || $hex === 'false' || $hex === '#' || empty( $hex ) ) {
		return 'transparent';
	}

	// Get clean hex code.
	$hex = str_replace( '#', '', $hex );

	if ( strlen( $hex ) === 3 ) {
		$hex = str_repeat( substr( $hex, 0, 1 ), 2 ) . str_repeat( substr( $hex, 1, 1 ), 2 ) . str_repeat( substr( $hex, 2, 1 ), 2 );
	}

	if ( strpos( $hex, 'rgba' ) !== false ) {
		$rgba = preg_replace( '/[^0-9,]/', '', $hex );
		$rgba = explode( ',', is_string( $rgba ) ? $rgba : '' );

		$hex = sprintf( '#%02x%02x%02x', $rgba[0], $rgba[1], $rgba[2] );
	}

	// Return if non hex.
	if ( function_exists( 'ctype_xdigit' ) && is_callable( 'ctype_xdigit' ) ) {
		if ( ! ctype_xdigit( $hex ) ) {
			return $hex;
		}
	} else {
		if ( ! preg_match( '/^[a-f0-9]{2,}$/i', $hex ) ) {
			return $hex;
		}
	}

	// Get r, g & b codes from hex code.
	$r   = hexdec( substr( $hex, 0, 2 ) );
	$g   = hexdec( substr( $hex, 2, 2 ) );
	$b   = hexdec( substr( $hex, 4, 2 ) );
	$hex = ( ( $r * 299 ) + ( $g * 587 ) + ( $b * 114 ) ) / 1000;

	return 128 <= $hex ? '#000000' : '#ffffff';
}

/**
 * Trim CSS
 *
 * @param string $css CSS content to trim.
 * @return string
 *
 * @since 0.0.1
 */
function suredash_trim_css( $css = '' ) {
	// Trim white space for faster page loading.
	if ( is_string( $css ) && ! empty( $css ) ) {
		$css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );
		$css = str_replace( [ "\r\n", "\r", "\n", "\t", '  ', '    ', '    ' ], '', (string) $css );
		$css = str_replace( ', ', ',', (string) $css );
	}

	return $css;
}

/**
 * Clean text - Remove HTML tags.
 *
 * @param string $content Text to clean.
 * @return string
 *
 * @since 0.0.1
 */
function suredash_clean_text( $content ) {
	$content = html_entity_decode( $content );
	$content = str_replace( [ '</p>', '</div>', '</li>' ], ' ', $content );
	$content = str_replace( [ '<br>', '<br/>', '<br />' ], ' ', $content );
	return wp_strip_all_tags( $content );
}

/**
 * Parse CSS
 *
 * @param array<mixed> $css_output Array of CSS.
 * @param mixed        $min_media Min Media breakpoint.
 * @param mixed        $max_media Max Media breakpoint.
 * @return string             Generated CSS.
 *
 * @since 0.0.1
 */
function suredash_parse_css( $css_output = [], $min_media = '', $max_media = '' ) {
	$parse_css = '';
	if ( is_array( $css_output ) && count( $css_output ) > 0 ) {
		foreach ( $css_output as $selector => $properties ) {
			if ( $properties === null ) {
				break;
			}

			if ( ! count( $properties ) ) {
				continue;
			}

			$temp_parse_css   = $selector . '{';
			$properties_added = 0;

			foreach ( $properties as $property => $value ) {
				if ( $value === '' && $value !== 0 ) {
					continue;
				}

				$properties_added++;
				$temp_parse_css .= $property . ':' . $value . ';';
			}

			$temp_parse_css .= '}';

			if ( $properties_added > 0 ) {
				$parse_css .= $temp_parse_css;
			}
		}

		if ( $parse_css !== '' && ( $min_media !== '' || $max_media !== '' ) ) {
			$media_css       = '@media ';
			$min_media_css   = '';
			$max_media_css   = '';
			$media_separator = '';

			if ( $min_media !== '' ) {
				$min_media_css = '(min-width:' . $min_media . 'px)';
			}
			if ( $max_media !== '' ) {
				$max_media_css = '(max-width:' . $max_media . 'px)';
			}
			if ( $min_media !== '' && $max_media !== '' ) {
				$media_separator = ' and ';
			}

			return $media_css . $min_media_css . $media_separator . $max_media_css . '{' . $parse_css . '}';
		}
	}

	return $parse_css;
}

/**
 * Get all internal sub queries.
 *
 * @return array<int, string>
 * @since 0.0.1
 */
function suredash_sub_queries() {
	return apply_filters(
		'suredashboard_portal_sub_queries',
		[
			'user-profile',
			'bookmarks',
			'user-view',
			'feeds',
		]
	);
}

/**
 * Validate if current page is of Portal's sub queried page.
 *
 * @return bool Return endpoint if true else false.
 * @since 0.0.1
 * @package SureDash
 */
function suredash_is_sub_queried_page() {
	$portal_sub_query = get_query_var( 'portal_subpage' );

	return $portal_sub_query && in_array( $portal_sub_query, suredash_sub_queries(), true );
}

/**
 * Get current sub queried page.
 *
 * @return string
 * @since 0.0.1
 */
function suredash_get_sub_queried_page() {
	$portal_sub_query = '';

	if ( suredash_is_sub_queried_page() ) {
		return get_query_var( 'portal_subpage' );
	}

	return $portal_sub_query;
}

/**
 * Validate if current page is of Portal's home.
 *
 * @since 0.0.1
 * @package SureDash
 *
 * @return bool
 */
function suredash_is_home() {
	return is_post_type_archive( SUREDASHBOARD_POST_TYPE );
}

/**
 * Validate if current page is of Portals type.
 *
 * @package SureDash
 * @since 0.0.1
 *
 * @return bool
 */
function suredash_portal() {
	if ( is_tax( SUREDASHBOARD_TAXONOMY ) || suredash_is_home() || is_singular( SUREDASHBOARD_POST_TYPE ) || suredash_is_sub_queried_page() ) {
		return true;
	}

	return false;
}

/**
 * Validate if current page is of SureDash CPT.
 *
 * @package SureDash
 * @since 0.0.1
 *
 * @return bool
 */
function suredash_cpt() {
	if ( is_singular( SUREDASHBOARD_FEED_POST_TYPE ) || is_post_type_archive( SUREDASHBOARD_FEED_POST_TYPE ) || is_tax( SUREDASHBOARD_FEED_TAXONOMY ) || is_singular( SUREDASHBOARD_SUB_CONTENT_POST_TYPE ) || is_post_type_archive( SUREDASHBOARD_SUB_CONTENT_POST_TYPE ) ) {
		return true;
	}

	return false;
}

/**
 * Validate if current page is of SureDash frontend.
 *
 * @package SureDash
 * @since 0.0.1
 *
 * @return bool
 */
function suredash_frontend() {
	if ( suredash_portal() || suredash_cpt() || ( is_front_page() && Helper::get_option( 'portal_as_homepage' ) ) ) {
		return true;
	}

	return false;
}

/**
 * Note: Fallback function as is_suredash_frontend() is deprecated.
 * Maintain only 2-3 updates.
 *
 * @package SureDash
 * @since 0.0.1
 *
 * @return bool
 */
function is_suredash_frontend() {
	return suredash_frontend();
}

/**
 * Validate if current page is showcasing only content.
 *
 * @return bool Return true if content is being showcased.
 * @since 0.0.2
 * @package SureDash
 */
function suredash_simply_content() {
	return ! empty( $_GET['simply_content'] ) && absint( $_GET['simply_content'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

/**
 * Get all end points.
 *
 * @return array<int, string>
 * @since 1.0.0
 */
function suredash_all_content_types() {
	return apply_filters(
		'suredashboard_all_endpoints',
		[
			'lesson',
		]
	);
}

/**
 * Validate if current page is of Portal's endpoint.
 *
 * @return string|bool Return endpoint if true else false.
 * @since 0.0.1
 * @package SureDash
 */
function suredash_content_post() {
	if ( is_singular( SUREDASHBOARD_SUB_CONTENT_POST_TYPE ) ) {
		return 'lesson'; // Update the condition later in future as per content_type usage.
	}

	return false;
}

/**
 * Get Portal's endpoint data.
 *
 * @param string $endpoint Endpoint.
 * @param mixed  $dataset  Dataset.
 * @param int    $base_id Base ID.
 *
 * @return array<mixed>
 * @since 0.0.1
 */
function suredash_endpoint_data( $endpoint = '', $dataset = '', $base_id = 0 ) {
	$args = [];

	if ( ! empty( $endpoint ) && $dataset && $base_id ) {
		$args = [ 'endpoint' => $endpoint ];

		switch ( $endpoint ) {
			case 'lesson':
				$course_loop         = PostMeta::get_post_meta_value( $base_id, 'pp_course_section_loop' );
				$args['media']       = $dataset;
				$args['course_loop'] = $course_loop;
				break;
		}
	}

	return apply_filters( 'suredashboard_' . $endpoint . '_endpoint_data', $args, $endpoint, $base_id );
}

/**
 * Update comments mention links.
 *
 * @param string          $comment_text Text of the comment.
 * @param WP_Comment|null $comment      The comment object. Null if not found.
 * @param array<mixed>    $args         An array of arguments.
 *
 * @return string Comment text with mention links.
 * @since 0.0.1
 */
function suredash_update_mention_links( $comment_text, $comment, $args ) {
	return suredash_dynamic_content_support( $comment_text );
}

/**
 * Get shorthand time format for a comment.
 *
 * @param int $comment_id The comment ID.
 * @return string The formatted time (e.g., 2h, 1d, 1w).
 */
function suredash_get_shorthand_comment_time( $comment_id ) {
	$comment_time      = get_comment_date( 'Y-m-d H:i:s', $comment_id ); // Get the comment time.
	$comment_timestamp = strtotime( $comment_time );
	$current_timestamp = suredash_get_timestamp();

	$time_difference = $current_timestamp - $comment_timestamp;
	$shorthand_time  = '';

	switch ( true ) {
		case $time_difference < MINUTE_IN_SECONDS:
			// Less than a minute ago.
			$shorthand_time = esc_html__( 'Just now', 'suredash' );
			break;

		case $time_difference < HOUR_IN_SECONDS:
			// Less than an hour ago.
			$minutes        = round( $time_difference / MINUTE_IN_SECONDS );
			$shorthand_time = $minutes . 'm';
			break;

		case $time_difference < DAY_IN_SECONDS:
			// Less than a day ago.
			$hours          = round( $time_difference / HOUR_IN_SECONDS );
			$shorthand_time = $hours . 'h';
			break;

		case $time_difference < WEEK_IN_SECONDS:
			// Less than a week ago.
			$days           = round( $time_difference / DAY_IN_SECONDS );
			$shorthand_time = $days . 'd';
			break;

		default:
			// More than a week ago.
			$weeks          = round( $time_difference / WEEK_IN_SECONDS );
			$shorthand_time = $weeks . 'w';
			break;
	}

	return $shorthand_time;
}

/**
 * Get the author name of a comment.
 *
 * @param string $author_name The author name.
 * @param int    $user_id The user ID.
 * @return string The author name.
 */
function suredash_get_author_name( $author_name, $user_id = 0 ) {
	// Check if $author_name is in mail format, if yes then get the user name.
	if ( $user_id && is_email( $author_name ) ) {
		$author_name = get_the_author_meta( 'display_name', $user_id );
	}

	// If the author name is still in mail format, then set take the name before the @.
	if ( is_email( $author_name ) ) {
		$fallback_name = $author_name;
		$author_name   = explode( '@', $author_name );
		$author_name   = ! empty( $author_name[0] ) ? $author_name[0] : $fallback_name;
	}

	return $author_name;
}

/**
 * Template for comments and pingbacks.
 *
 * To override this walker in a child theme without modifying the comments template
 * simply create your own suredash_comments_list_callback(), and that function will be used instead.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @param object       $comment Comment.
 * @param array<mixed> $args Comment arguments.
 * @param number       $depth Depth.
 * @return mixed          Comment markup.
 */
function suredash_comments_list_callback( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	$depth              = $args['depth'] ?? $depth;
	$reply_link_class   = $args['reply_link_class'] ?? 'portal-reply-link';

	if ( ! is_object( $comment ) || ! isset( $comment->comment_type ) ) {
		return;
	}

	switch ( $comment->comment_type ) {
		case 'pingback':
		case 'trackback':
			?>
			<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>" data-depth="<?php echo esc_attr( $depth ); ?>">
				<p><?php esc_html_e( 'Pingback:', 'suredash' ); ?><?php comment_author_link(); ?><?php edit_comment_link( __( '(Edit)', 'suredash' ), '<span class="edit-link">', '</span>' ); ?></p>
			</li>
			<?php
			break;

		default:
			$comment_id = absint( ! empty( $comment->comment_ID ) ? $comment->comment_ID : 0 );
			$author_id  = absint( ! empty( $comment->user_id ) ? $comment->user_id : 0 );
			if ( ! $comment_id || ! $author_id ) {
				return;
			}
			$time = suredash_get_shorthand_comment_time( $comment_id );
			/* translators: 1: date, 2: time */
			$title_time = esc_html( sprintf( __( '%1$s at %2$s', 'suredash' ), get_comment_date(), get_comment_time() ) );

			// Get child comments.
			$child_comments  = get_comments(
				[
					'parent' => $comment_id,
					'order'  => 'ASC',
				]
			);
			$comment_author  = $comment->comment_author ?? '';
			$comment_user_id = $comment->user_id ?? '';
			?>
			<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>" data-depth="<?php echo esc_attr( $depth ); ?>">
				<?php $comment_author = suredash_get_author_name( $comment_author, $comment_user_id ); ?>
				<article id="comment-<?php comment_ID(); ?>" class="portal-comment">
					<div class='portal-comment-info'>
						<?php suredash_get_user_avatar( $author_id ); ?>

						<div class="portal-comment-section">
						<?php if ( ! empty( $child_comments ) ) { ?>
								<div class="portal-comment-hidden-wrap"></div>
						<?php } ?>

							<div class="portal-comment-details">
								<span class="portal-user-commenter"> <?php echo esc_html( $comment_author ); ?> </span>
								<span class="portal-reaction-separator sd-no-space"></span>
								<span class="timendate">
									<time datetime="<?php echo esc_attr( get_comment_time( 'c' ) ); ?>" title="<?php echo esc_attr( $title_time ); ?>">
										<?php echo esc_attr( $time ); ?>
									</time>
								</span>
							</div>

							<section class="portal-comment-content comment">
								<?php
									add_filter( 'comment_text', 'suredash_update_mention_links', 10, 3 );
									comment_text();
									remove_filter( 'comment_text', 'suredash_update_mention_links' );
								?>
								<?php if ( isset( $comment->comment_approved ) && $comment->comment_approved === '0' ) { ?>
									<em class="comment-awaiting-moderation"><?php echo esc_html__( 'Your comment is awaiting moderation.', 'suredash' ); ?></em>
								<?php } ?>
							</section>

							<section class="portal-comment-meta portal-row portal-comment-author vcard capitalize">
								<div class="portal-comment-reactions-wrap">
									<?php
										$user_liked_comments = sd_get_user_meta( get_current_user_id(), 'portal_user_liked_comments', true );
										$user_liked_comments = ! empty( $user_liked_comments ) ? $user_liked_comments : [];
										$is_user_liked       = in_array( absint( $comment_id ), $user_liked_comments, true );
										$like_text           = $is_user_liked ? Labels::get_label( 'liked', false ) : Labels::get_label( 'like', false );
									?>
									<span class="sd-comment-like-reaction" data-entity="comment" data-comment_id="<?php echo esc_attr( (string) $comment_id ); ?>"> <?php echo esc_html( $like_text ); ?> </span>
									<?php
									if ( $depth < 5 ) {
										echo wp_kses_post(
											comment_reply_link(
												array_merge(
													$args,
													[
														'reply_text' => __( 'Reply', 'suredash' ),
														'add_below' => 'comment',
														'depth'  => $depth,
														'before' => '<span class="' . esc_attr( $reply_link_class ) . '">',
														'after'  => '</span>',
													]
												)
											)
										);
									}
									?>
								</div>
								<div class="portal-comment-reactions-set">
									<?php
										$user_list = suredash_get_comment_liked_users( $comment_id );

										$comment_likes = (string) $user_list['comment_likes'];
										// Output the like count with tooltip using `data-tooltip`.
										echo '<span
										class="tooltip-trigger sd-comment-like-count sd-flex sd-p-0 sd-font-14 sd-transition-fast sd-color-custom sd-font-semibold sd-gap-4 sd-pointer"
										data-tooltip-description="' . esc_attr( (string) $user_list['tooltip_content'] ) . '"
										data-count="' . esc_attr( $comment_likes ) . '">
										 <span class="counter">' . esc_html( $comment_likes ) . '</span> ' . esc_html( _n( 'Like', 'Likes', absint( $comment_likes ), 'suredash' ) ) . '</span>';
										echo '</span>';
									?>
								</div>
							</section>
						</div>
					</div>
				</article>

				<?php if ( is_array( $child_comments ) && ! empty( $child_comments ) ) { ?>
					<div class="portal-replies-wrapper">
						<div class="portal-view-replies-btn sd-border-none" data-comment-id="<?php echo esc_attr( (string) $comment_id ); ?>">

							<?php
								// Check if there are child comments.

								// Get the latest child comment.
								$latest_child_comment = end( $child_comments );

								// Skip if the child comment is not an object.
							if ( is_object( $latest_child_comment ) ) {
								// Get the name of the latest child commenter.
								$latest_child_commenter_name = $latest_child_comment->comment_author;

								echo '<span class="latest-replier-avatar">';
								suredash_get_user_avatar( absint( $latest_child_comment->user_id ), true, 32 );
								echo '</span>';

								// Display the name of the latest child commenter.
								echo '<span class="latest-replier">' . esc_html( $latest_child_commenter_name ) . ' ' . esc_html( Labels::get_label( 'replied' ) ) . '</span>';
							}
							?>
							<span>
								·
							</span>
							<span class="replies-count">
								<?php echo esc_html( (string) count( $child_comments ) ) . ' ' . esc_html( Labels::get_label( 'replies' ) ); ?>
							</span>
							</div>
						<ol class="children comment-replies" style="display: none;">
							<?php
							foreach ( $child_comments as $child_comment ) {
								// Skip if the child comment is not an object.
								if ( ! is_object( $child_comment ) ) {
									continue;
								}
								// Save current comment.
								$tmp_comment = $GLOBALS['comment'];
								// Call recursively with the child comment.
								suredash_comments_list_callback( $child_comment, $args, $depth + 1 );
								// Restore the previous comment context.
								$GLOBALS['comment'] = $tmp_comment; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
							}
							?>
						</ol>
					</div>
				<?php } ?>
			</li>
			<?php
			break;
	}
}

/**
 * Get the current timestamp.
 *
 * @param int $comment_id The comment ID.
 *
 * @return array<string, string|int>
 */
function suredash_get_comment_liked_users( $comment_id ) {
	$comment_likes       = get_comment_meta( $comment_id, 'portal_comment_likes', true );
	$comment_likes       = ! empty( $comment_likes ) ? $comment_likes : [];
	$comment_likes_users = array_values( $comment_likes );
	$tooltip_content     = '';
	$comment_likes_count = 0;

	if ( is_array( $comment_likes_users ) && ! empty( $comment_likes_users ) ) {
		$users             = [];
		$max_users_to_show = apply_filters( 'suredash_max_number_of_comment_likers', 5 );

		$total_likes = count( $comment_likes_users );
		$loop_limit  = $total_likes > $max_users_to_show ? $max_users_to_show : $total_likes; // Precompute the loop limit.
		// Iterate only up to some threshold.
		for ( $i = 0; $i < $loop_limit; $i++ ) {
			$user_id = $comment_likes_users[ $i ];
			$user    = get_user_by( 'id', $user_id );
			if ( $user && ! empty( $user->display_name ) ) {
				$users[] = $user->display_name;
			}
		}

		// Calculate the remaining count of users who liked the comment.
		$remaining_count = count( $comment_likes_users ) - $max_users_to_show;

		// Prepare tooltip content.
		$tooltip_content = implode( "\n", $users );
		if ( $remaining_count > 0 ) {
			$tooltip_content .= ' and ' . $remaining_count . ' more.';
		}

		// Total likes count.
		$comment_likes_count = (string) count( $comment_likes_users );
	}

	return [
		'tooltip_content' => $tooltip_content,
		'comment_likes'   => $comment_likes_count,
	];
}
/**
 * Get all completed lessons.
 *
 * @param int $course_id Course ID.
 * @return array<mixed>
 * @since 0.0.1
 */
function suredash_get_all_completed_lessons( $course_id ) {
	$completed_lessons = sd_get_user_meta( get_current_user_id(), 'portal_course_' . $course_id . '_completed_lessons', true );
	$completed_lessons = ! empty( $completed_lessons ) ? $completed_lessons : [];

	return apply_filters( 'suredashboard_completed_lessons', $completed_lessons, $course_id, get_current_user_id() );
}

/**
 * Get all bookmarked items.
 *
 * @return array<mixed>
 * @since 0.0.1
 */
function suredash_get_all_bookmarked_items() {
	$user_id          = get_current_user_id();
	$bookmarked_items = sd_get_user_meta( $user_id, 'portal_bookmarked_items', true );
	$bookmarked_items = ! empty( $bookmarked_items ) ? $bookmarked_items : [];

	return apply_filters( 'suredashboard_bookmarked_items', $bookmarked_items, $user_id );
}

/**
 * Check if passed item is bookmarked or not.
 *
 * @param int $item_id Item ID.
 * @return bool
 * @since 0.0.1
 */
function suredash_is_item_bookmarked( $item_id ) {
	$bookmarked_items = suredash_get_all_bookmarked_items();
	return isset( $bookmarked_items[ $item_id ] ) ? true : false;
}

/**
 * Get endpoint indicator.
 *
 * @param string $endpoint Endpoint.
 * @param int    $base_id Flag.
 * @param int    $item_id Item ID.
 * @return string
 * @since 0.0.1
 */
function suredash_get_endpoint_indicator( $endpoint, $base_id, $item_id ) {
	$endpoint_indicator = '';

	switch ( $endpoint ) {
		case 'lesson':
			$all_completed_lessons = suredash_get_all_completed_lessons( $base_id );
			$endpoint_indicator    = in_array( $item_id, $all_completed_lessons, true ) ? 'completed' : '';
			break;

		default:
			break;
	}

	return $endpoint_indicator;
}

/**
 * Checks whether the post content have internal block or have shortcode.
 *
 * @param string $tag Shortcode tag to check.
 * @return bool
 */
function suredash_check_block_presence( $tag = '' ) {
	global $post;

	if ( ! $post ) {
		return false;
	}

	$presence = false;

	if ( is_singular() && is_a( $post, 'WP_Post' ) ) {
		$presence = has_block( $tag, $post->post_content ) || has_shortcode( $post->post_content, $tag );
	}

	return apply_filters( 'suredashboard_check_block_presence', $presence, $tag );
}

/**
 * Check whether the active theme is Astra.
 *
 * @return bool
 * @since 0.0.1
 */
function suredash_is_on_astra_theme() {
	return defined( 'ASTRA_THEME_VERSION' ) && is_callable( 'astra_get_option' );
}

/**
 * Get Lesson Oriented Data.
 *
 * Data like: all_lessons_count, next_lesson_id, previous_lesson_id.
 *
 * @param int          $current_lesson_id Current Lesson ID.
 * @param array<mixed> $course_loop Course Loop.
 * @since 1.0.0
 * @return array<mixed> $lesson_data
 */
function suredash_get_lesson_oriented_data( $current_lesson_id, $course_loop ) {
	$previous_lesson_id = 0;
	$next_lesson_id     = 0;
	$lessons_dataset    = [];

	foreach ( $course_loop as $section ) {
		if ( ! empty( $section['section_medias'] ) ) {
			$lessons_dataset = array_merge( $lessons_dataset, $section['section_medias'] );
		}
	}

	if ( ! empty( $lessons_dataset ) ) {
		foreach ( $lessons_dataset as $index => $lesson ) {
			if ( isset( $lesson['value'] ) && absint( $lesson['value'] ) === $current_lesson_id ) {
				// Get previous lesson ID.
				$previous_lesson_id = absint( ! empty( $lessons_dataset[ $index - 1 ]['value'] ) ? $lessons_dataset[ $index - 1 ]['value'] : 0 );

				// Get next lesson ID.
				$next_lesson_id = absint( ! empty( $lessons_dataset[ $index + 1 ]['value'] ) ? $lessons_dataset[ $index + 1 ]['value'] : 0 );
			}
		}
	}

	return [
		'all_lessons_count'  => count( $lessons_dataset ),
		'previous_lesson_id' => $previous_lesson_id,
		'next_lesson_id'     => $next_lesson_id,
	];
}

/**
 * Grant capabilities to the user based on the selected roles.
 *
 * @param int $user_id The user ID.
 * @return void
 * @since 0.0.1
 */
function suredash_grant_capabilities_to_user( $user_id ): void {
	// Get the roles selected in the admin panel.
	$selected_roles = Helper::get_option( 'user_capability' );

	// Bail if no roles are selected.
	if ( ! is_array( $selected_roles ) || empty( $selected_roles ) ) {
		return;
	}

	// Get the user object.
	$user = new \WP_User( $user_id );

	// Iterate over the selected roles.
	foreach ( $selected_roles as $role => $role_data ) {
		$role_slug = $role_data['id'] ?? 0;

		// Get the role object and its capabilities.
		$role = get_role( $role_slug );

		if ( ! empty( $role ) && is_array( $role->capabilities ) ) {
			// Grant each capability to the user.
			foreach ( $role->capabilities as $cap => $grant ) {
				if ( $grant ) {
					$user->add_cap( $cap );
				}
			}
		}
	}
}

/**
 * Get the timestamp .
 *
 * @return int
 * @since 0.0.1
 */
function suredash_get_timestamp() {
	return current_time( 'timestamp' ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
}

/**
 * Check if the user is a manager.
 *
 * @param int $user_id The user ID.
 * @return bool
 * @since 0.0.1
 */
function suredash_is_user_manager( $user_id = 0 ) {
	$status = false;
	if ( ! is_user_logged_in() ) {
		return $status;
	}

	if ( $user_id ) {
		$current_user_id = absint( $user_id );
	} else {
		$current_user_id = get_current_user_id();
	}

	$portal_managers = Helper::get_option( 'portal_manager' );
	if ( ! empty( $portal_managers ) ) {
		foreach ( $portal_managers as $user_data ) {
			if ( is_array( $user_data ) && $current_user_id === absint( $user_data['id'] ) ) {
				$status = true;
				break;
			}
		}
	}

	return apply_filters( 'suredashboard_is_user_manager', $status, $current_user_id );
}

/**
 * Check if the post is restricted.
 *
 * @param int $post_id Post ID.
 *
 * @since 1.0.0
 * @return array<string, mixed>
 */
function suredash_get_post_backend_restriction( int $post_id ): array {
	$status_data = [
		'status'      => false,
		'redirection' => '',
		'title'       => '',
	];

	if ( is_admin() ) {
		return apply_filters(
			'suredash_post_backend_restriction_details',
			$status_data,
			$post_id
		);
	}

	// If current user is manager, then consider the post is for admin's view.
	if ( suredash_is_user_manager() ) {
		return $status_data;
	}

	return apply_filters(
		'suredash_post_backend_restriction_details',
		$status_data,
		$post_id
	);
}

/**
 * Get default restriction values.
 *
 * @return array<string, mixed>
 * @since 1.0.0
 */
function suredash_restriction_defaults() {
	return apply_filters(
		'suredashboard_restriction_defaults',
		[
			'status'  => false,
			'content' => '',
		]
	);
}

/**
 * Check if the post is private.
 *
 * @param int  $post_id The post ID.
 * @param bool $get_only_status Flag to get only status.
 * @return bool
 * @since 1.0.0
 */
function suredash_is_post_protected( $post_id, $get_only_status = true ) {
	$restriction  = suredash_restriction_defaults();
	$is_protected = $restriction['status'] ?? false;

	if ( is_user_logged_in() && current_user_can( 'administrator' ) ) {
		return $is_protected;
	}

	// Check if the post is restricted.
	$restriction  = Helper::maybe_third_party_restricted( $post_id );
	$is_protected = $restriction['status'] ?? false;
	if ( $is_protected ) {
		return apply_filters( 'suredash_post_protection', $get_only_status ? $is_protected : $restriction, $post_id );
	}

	// Check if the post's space is restricted.
	$post_type = strval( sd_get_post_field( $post_id, 'post_type' ) );
	if ( $post_type === SUREDASHBOARD_FEED_POST_TYPE ) {
		$space_id = absint( sd_get_space_id_by_post( $post_id ) );
		if ( $space_id ) {
			$restriction  = Helper::maybe_third_party_restricted( $space_id );
			$is_protected = $restriction['status'] ?? false;
			if ( $is_protected ) {
				return apply_filters( 'suredash_post_protection', $get_only_status ? $is_protected : $restriction, $post_id );
			}
		}
	}

	// Check if the post is private in the pro version.
	if ( is_callable( 'suredash_pro_is_post_protected' ) ) {
		$restriction  = suredash_pro_is_post_protected( $post_id );
		$is_protected = $restriction['status'] ?? false;
		if ( $is_protected ) {
			return apply_filters( 'suredash_post_protection', $get_only_status ? $is_protected : $restriction, $post_id );
		}
	}

	// Fallback to the default behavior.
	return apply_filters( 'suredash_post_protection', $get_only_status ? $is_protected : $restriction, $post_id );
}

/**
 * Get premium course integration content.
 *
 * @param int $post_id Post ID.
 * @return string
 * @since 0.0.1
 */
function suredash_course_integration_content( $post_id ) {
	$content = '';

	if ( is_callable( 'suredash_pro_course_integration_content' ) ) {
		$content = suredash_pro_course_integration_content( $post_id );
	}

	return $content;
}

/**
 * Get premium course's lesson view content.
 *
 * @param string       $endpoint      Endpoint.
 * @param array<mixed> $endpoint_data Endpoint data.
 * @param array<mixed> $atts          Attributes.
 * @return void
 * @since 0.0.1
 */
function suredash_lesson_view_content( $endpoint, $endpoint_data, $atts = [] ): void {
	if ( is_callable( 'suredash_pro_lesson_view_content' ) ) {
		suredash_pro_lesson_view_content( $endpoint, $endpoint_data, $atts );
	}
}

/**
 * Get premium course's progress bar.
 *
 * @param array<mixed> $lesson_data   Lesson data.
 * @param array<mixed> $lessons_completed The lessons completed.
 * @return string HTML markup for the progress bar.
 * @since 0.0.6
 */
function suredash_get_course_progress_bar( $lesson_data, $lessons_completed ) {
	if ( is_callable( 'suredash_pro_get_course_progress' ) ) {
		return suredash_pro_get_course_progress( $lesson_data, $lessons_completed );
	}

	return '';
}

/**
 * Get portal user's profile link.
 *
 * @param int $user_id User ID.
 * @return string
 */
function suredash_user_view_link( $user_id ) {
	return home_url( '/' . SUREDASHBOARD_SLUG . '/user-view/?id=' . $user_id . '/' );
}

/**
 * Return the caller detail for showcasing in notification.
 *
 * @param int $caller Caller ID.
 * @return string
 */
function suredash_get_notifier_caller( $caller ) {
	if ( empty( $caller ) ) {
		return __( 'Unknown', 'suredash' );
	}

	$caller_user = get_user_by( 'ID', $caller );
	$caller_name = $caller_user->display_name ?? __( 'Unknown', 'suredash' );
	$user_view   = suredash_user_view_link( $caller );

	return '<a href="' . esc_url( $user_view ) . '"><strong>' . esc_html( $caller_name ) . '</strong></a>';
}

/**
 * Check https status.
 *
 * @since  0.0.6
 * @return bool
 */
function suredash_site_is_https() {
	return strstr( get_option( 'home' ), 'https:' ) !== false;
}

/**
 * Get the referer post if available.
 *
 * @return array<mixed>
 * @since 0.0.5
 */
function suredash_get_referer_post() {
	if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
		$referer    = esc_url_raw( $_SERVER['HTTP_REFERER'] );
		$parsed_url = wp_parse_url( $referer );

		// Strip the slug and extract the post ID.
		if ( isset( $parsed_url['path'] ) ) {
			$path     = trim( $parsed_url['path'], '/' );
			$segments = explode( '/', $path );
			$slug     = end( $segments );

			// Fetch post dynamically using sd_query().
			$post = sd_query()
				->select( 'ID, post_type, post_title' )
				->from( 'posts' )
				->where( 'post_name', $slug )
				->where( 'post_status', 'publish' )
				->limit( 1 )
				->get( ARRAY_A );

			return $post[0] ?? [];
		}
	}

	return [];
}

/**
 * Get the login page URL.
 *
 * @return string
 */
function suredash_get_login_page_url() {
	$login_page    = Helper::get_option( 'login_page' );
	$login_page_id = is_array( $login_page ) && ! empty( $login_page['value'] ) ? $login_page['value'] : 0;

	return apply_filters( 'suredashboard_login_redirection', $login_page_id ? get_permalink( $login_page_id ) : wp_login_url() );
}

/**
 * Get the ORM query instance.
 *
 * @return Query
 */
function sd_query() {
	return Query::init(); // @phpstan-ignore-line
}

/**
 * Get the Router instance.
 *
 * @return Router
 */
function sd_route() {
	return Router::get_instance(); // @phpstan-ignore-line
}

/**
 * Returns the markup for the current template.
 *
 * @access private
 * @since 0.0.6
 *
 * @param  string $template_content The template content.
 * @global string   $_wp_current_template_content
 * @global WP_Embed $wp_embed
 *
 * @return string Block template markup.
 */
function suredash_get_the_block_template_html( $template_content ) {
	global $wp_embed;

	if ( ! $template_content ) {
		return is_user_logged_in() ? '<h1>' . esc_html__( 'No matching template found.', 'suredash' ) . '</h1>' : '';
	}

	$content = $wp_embed->run_shortcode( $template_content );
	$content = $wp_embed->autoembed( $content );
	$content = do_blocks( $content );
	$content = wptexturize( $content );
	$content = convert_smilies( $content );
	$content = shortcode_unautop( $content );
	$content = wp_filter_content_tags( $content, 'template' );
	$content = do_shortcode( $content );
	$content = str_replace( ']]>', ']]&gt;', $content );

	// Wrap block template in .wp-site-blocks to allow for specific descendant styles.
	// (e.g. `.wp-site-blocks > *`).
	return '<div class="wp-site-blocks portal-container">' . $content . '</div>';
}

/**
 * Get the portal_menu ID.
 *
 * @return int
 * @since 0.0.6
 */
function suredash_get_portal_menu_id() {
	if ( ! has_nav_menu( 'portal_menu' ) ) {
		return 1;
	}

	$menu_locations = get_nav_menu_locations();
	return $menu_locations['portal_menu'] ?? 1;
}

/**
 * Make content dynamic.
 *
 * Usecases:
 * {site_url} => Site URL.
 * {portal_slug} => SUREDASHBOARD_SLUG.
 * %7Bportal_slug%7D => SUREDASHBOARD_SLUG.
 *
 * @param string $content content.
 * @since 1.0.0
 * @return string
 */
function suredash_dynamic_content_support( $content ) {
	$site_url = esc_url( site_url() );

	return str_replace(
		[ '{site_url}', '{portal_slug}', '%7Bportal_slug%7D' ],
		[ $site_url, SUREDASHBOARD_SLUG, SUREDASHBOARD_SLUG ],
		$content
	);
}
