<?php
/**
 * Labels.
 *
 * @package SureDash
 * @since 1.0.0
 */

namespace SureDashboard\Inc\Utils;

use SureDashboard\Inc\Traits\Get_Instance;

defined( 'ABSPATH' ) || exit;

/**
 * Update Compatibility
 *
 * @package SureDash
 */
class Labels {
	use Get_Instance;

	/**
	 * All labels.
	 *
	 * @var array<string, string>
	 * @since 1.0.0
	 */
	private static $labels = [];

	/**
	 * Register all labels.
	 *
	 * @since 1.0.0
	 */
	public static function register_all_labels(): void {
		self::$labels = apply_filters(
			'portal_labels_list',
			[
				'welcome_text'                      => Helper::get_option( 'welcome_text' ),
				'home'                              => Helper::get_option( 'home_text' ),
				'feeds'                             => __( 'Latest Posts', 'suredash' ),

				'back_to_portal'                    => __( 'Back to', 'suredash' ) . ' ' . Helper::get_option( 'portal_name', __( 'Portal', 'suredash' ) ),
				'back_to_cpt'                       => __( 'Back to ', 'suredash' ),
				'portal_singular_text'              => __( 'Portal Item', 'suredash' ),
				'portal_plural_text'                => __( 'Portal Items', 'suredash' ),
				'user_profile'                      => __( 'User Profile', 'suredash' ),
				'user-profile'                      => __( 'User Profile', 'suredash' ),
				'profile_information'               => Helper::get_option( 'profile_information_text' ),

				'user-view'                         => Helper::get_option( 'profile_information_text' ),
				'posts'                             => __( 'Posts', 'suredash' ),
				'like'                              => __( 'Like', 'suredash' ),
				'liked'                             => __( 'Liked', 'suredash' ),
				'likes'                             => __( 'Likes', 'suredash' ),
				'comments'                          => __( 'Comments', 'suredash' ),
				'no_comments_found'                 => __( 'No comments yet.', 'suredash' ),
				'commented_on'                      => __( 'Commented on', 'suredash' ),
				'replied'                           => __( 'replied', 'suredash' ),
				'replies'                           => __( 'replies', 'suredash' ),

				'profile'                           => __( 'Profile', 'suredash' ),
				'password'                          => __( 'Password', 'suredash' ),
				'profile_photo'                     => __( 'Profile Photo', 'suredash' ),
				'first_name'                        => __( 'First Name', 'suredash' ),
				'last_name'                         => __( 'Last Name', 'suredash' ),
				'username'                          => __( 'Username', 'suredash' ),
				'email'                             => __( 'Email', 'suredash' ),
				'phone'                             => __( 'Phone', 'suredash' ),
				'address'                           => __( 'Address', 'suredash' ),
				'display_name'                      => __( 'Display Name', 'suredash' ),
				'bio'                               => __( 'Bio', 'suredash' ),
				'description'                       => __( 'Description', 'suredash' ),
				'save'                              => __( 'Save', 'suredash' ),
				'current_password'                  => __( 'Current Password', 'suredash' ),
				'new_password'                      => __( 'New Password', 'suredash' ),
				'confirm_new_password'              => __( 'Confirm New Password', 'suredash' ),
				'password_mismatch_message'         => __( 'Passwords do not match.', 'suredash' ),
				'profile_updated'                   => __( 'Profile updated successfully.', 'suredash' ),

				'bookmarks'                         => Helper::get_option( 'your_bookmarks_text' ),
				'misc_items_text'                   => __( 'Miscellaneous', 'suredash' ),

				'404_heading'                       => __( 'Oops, It\'s Empty in Here!', 'suredash' ),
				'bookmark_not_found_text'           => __( 'Seems like you haven\'t bookmarked anything yet. Start bookmarking and make this space your go-to!', 'suredash' ),
				'back_to_home'                      => __( 'Back to Home', 'suredash' ),

				'no_results_found'                  => __( 'No results found. Try again with different words?', 'suredash' ),
				'search_placeholder'                => _x( 'Search', 'Search global placeholder text!', 'suredash' ),
				'least_search_chars_require'        => __( 'Search must be at least 3 characters.', 'suredash' ),
				'end_point_error'                   => __( 'Error occurred while fetching data.', 'suredash' ),
				'insufficient_data_error'           => __( 'Data is insufficient.', 'suredash' ),

				'course_default_heading'            => __( 'Course Playlist', 'suredash' ),
				'course_default_description'        => __( 'If you want to sell products online, you need a sales funnel. A sales funnel is the easiest way to move a website visitor towards being a paying customer. A sales funnel builds wealth into your business.', 'suredash' ),
				'course_singular_text'              => __( 'Course', 'suredash' ),
				'course_plural_text'                => __( 'Courses', 'suredash' ),
				'lesson_singular_text'              => __( 'Lesson', 'suredash' ),
				'lesson_plural_text'                => __( 'Lessons', 'suredash' ),

				'start_writing_post'                => Helper::get_option( 'start_writing_post_text' ),
				'write_a_post'                      => Helper::get_option( 'write_a_post_text' ),
				'title'                             => __( 'Title', 'suredash' ),
				'content'                           => __( 'Content', 'suredash' ),
				'read_more'                         => __( 'See More', 'suredash' ),
				'submit_button'                     => __( 'Post', 'suredash' ),
				'cancel_button'                     => __( 'Cancel', 'suredash' ),
				'close_button'                      => __( 'Close', 'suredash' ),
				'pinned_post'                       => Helper::get_option( 'pinned_post_text' ),
				'no_posts_found'                    => Helper::get_option( 'no_posts_found' ),
				'no_more_posts_to_load'             => __( 'All caught up. No more posts to display.', 'suredash' ),
				'login_or_join'                     => Helper::get_option( 'login_or_join' ),

				'course_progress'                   => __( 'Course Progress', 'suredash' ),
				'mark_as_complete'                  => Helper::get_option( 'mark_as_complete_text' ),

				'share_on_facebook'                 => __( 'Share on Facebook', 'suredash' ),
				'share_on_twitter'                  => __( 'Share on Twitter', 'suredash' ),
				'share_on_linkedin'                 => __( 'Share on LinkedIn', 'suredash' ),
				'share_on_pinterest'                => __( 'Share on Pinterest', 'suredash' ),
				'copy_to_clipboard'                 => __( 'Copy to Clipboard', 'suredash' ),

				'presto_pro_required'               => __( 'PrestoPlayer Pro is Required!', 'suredash' ),
				'presto_pro_required_description'   => __( 'This feature requires PrestoPlayer Pro. Please upgrade to PrestoPlayer Pro to use this feature.', 'suredash' ),
				'post_submitted_successfully'       => __( 'Post submitted successfully and waiting for approval.', 'suredash' ),

				'restricted_content_heading'        => Helper::get_option( 'restricted_content_heading_text' ),
				'restricted_content_notice'         => Helper::get_option( 'restricted_content_notice_text' ),
				'dripped_content_heading'           => Helper::get_option( 'restricted_content_heading_text' ),
				'dripped_content_notice'            => __( 'The content will be available in', 'suredash' ),

				'notifications'                     => __( 'Notifications', 'suredash' ),
				'no_notifications_title'            => __( 'No New Notifications', 'suredash' ),
				'no_notifications'                  => __( 'Ta-da! You\'re up to date.', 'suredash' ),

				'comment_reply_message'             => '{{CALLER}} ' . __( 'has replied to your ', 'suredash' ) . '{{COMMENT}}.',
				'single_post_comment_message'       => '{{CALLER}} ' . __( 'has commented on your post ', 'suredash' ) . '"{{TOPIC}}".',
				'plural_post_comment_message'       => '{{CALLER}} ' . __( ' and ', 'suredash' ) . '{{COUNT}} ' . __( ' others have commented on your post ', 'suredash' ) . ' "{{TOPIC}}".',
				'entity_like'                       => '{{CALLER}}' . __( ' has liked your ', 'suredash' ) . '{{ENTITY}} {{TOPIC}}',
				'entity_likes'                      => '{{CALLER}}' . __( ' and ', 'suredash' ) . '{{COUNT}} ' . __( ' others have liked your ', 'suredash' ) . '{{ENTITY}} "{{TOPIC}}".',

				'all_notifications'                 => __( 'All', 'suredash' ),
				'unread'                            => __( 'Unread', 'suredash' ),

				'notification_success_message'      => __( 'Settings Saved Successfully.', 'suredash' ),
				'notification_error_message'        => __( 'Unable to Save Settings.', 'suredash' ),
				'notification_warning_message'      => __( 'Something Went Wrong!', 'suredash' ),
				'notification_info_message'         => __( 'Information Updated Successfully.', 'suredash' ),
				'notification_neutral_message'      => __( 'Notification Updated Successfully.', 'suredash' ),

				'notify_message_post_submitted'     => __( 'Your post has been submitted successfully.', 'suredash' ),
				'notify_message_please_fill_required_fields' => __( 'Please fill in all required fields.', 'suredash' ),
				'notify_message_item_bookmarked'    => __( 'Saved to bookmarks.', 'suredash' ),
				'notify_message_item_un_bookmarked' => __( 'Removed from bookmarks.', 'suredash' ),
				'notify_message_comment_liked'      => __( 'Comment liked successfully.', 'suredash' ),
				'notify_message_comment_disliked'   => __( 'Comment disliked successfully.', 'suredash' ),
				'notify_message_comment_posted'     => __( 'Comment posted successfully.', 'suredash' ),
				'notify_message_comment_invalid'    => __( 'Invalid comment.', 'suredash' ),
				'notify_message_comment_duplicate'  => __( 'Duplicate comment detected. Please try writing something new!', 'suredash' ),
				'notify_message_post_liked'         => __( 'Post liked successfully.', 'suredash' ),
				'notify_message_post_disliked'      => __( 'Post disliked successfully.', 'suredash' ),
				'notify_message_profile_updated'    => __( 'Profile updated successfully.', 'suredash' ),

				'restricted_content'                => __( 'Restricted Content', 'suredash' ),
				'restricted_content_description'    => __( 'This content is restricted and can only be accessed by authorized users.', 'suredash' ),

				'notify_message_error_occurred'     => __( 'Something went wrong! Please try again later.', 'suredash' ),

				'comment_box_placeholder'           => __( 'What are your thoughts?', 'suredash' ),
				'comment_reply_box_placeholder'     => __( 'Replying to', 'suredash' ),

				'create_post_placeholder'           => __( 'Start writing your post...', 'suredash' ),

				'jodit_search_user'                 => __( 'Search User...', 'suredash' ),
				'jodit_search_gif'                  => __( 'Search Gifs...', 'suredash' ),
				'jodit_mention_tooltip'             => __( 'Add Mention', 'suredash' ),
				'jodit_emoji_tooltip'               => __( 'Insert Emoji', 'suredash' ),
				'jodit_gif_tooltip'                 => __( 'Insert GIF', 'suredash' ),
				'jodit_no_gif_found'                => __( 'No GIF found', 'suredash' ),
				'jodit_no_user_found'               => __( 'No user found', 'suredash' ),
				'jodit_minimum_3_characters'        => __( 'Please enter at least 3 characters to search', 'suredash' ),
				'jodit_api_error'                   => __( 'API error occurred', 'suredash' ),
			]
		);
	}

	/**
	 * Get all labels.
	 *
	 * @since 1.0.0
	 * @return array<string, string>
	 */
	public static function get_labels() {
		if ( empty( self::$labels ) ) {
			self::register_all_labels();
		}

		return self::$labels;
	}

	/**
	 * Get a label.
	 *
	 * @param string $label_name Label name.
	 * @param bool   $echo       Echo or return.
	 *
	 * @return string Label.
	 * @since 1.0.0
	 */
	public static function get_label( $label_name, $echo = false ) {
		$labels        = self::get_labels();
		$default_label = $labels[ $label_name ] ?? '';

		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
		$translated_label = __( $default_label, 'suredash' );

		// Apply filter AFTER translation.
		$label = apply_filters( 'suredashboard_' . $label_name . '_text', $translated_label, $label_name );

		if ( $echo ) {
			echo esc_html( $label );
			return '';
		}
			return esc_html( $label );
	}
}
