<?php
/**
 * Misc Router Initialize.
 *
 * @package SureDash
 */

namespace SureDashboard\Core\Routers;

use SureDashboard\Core\Models\Controller;
use SureDashboard\Core\Notifier\Base as Notifier_Base;
use SureDashboard\Inc\Traits\Get_Instance;
use SureDashboard\Inc\Traits\Rest_Errors;
use SureDashboard\Inc\Utils\Helper;
use SureDashboard\Inc\Utils\Labels;
use SureDashboard\Inc\Utils\Sanitizer;
use SureDashboard\Inc\Utils\Uploader;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Misc Router.
 */
class Misc {
	use Get_Instance;
	use Rest_Errors;

	// Default dimensions for uploaded images (width, height) in pixels.
	private const DEFAULT_IMAGE_DIMENSIONS = [ 1000, 1000 ];

	// Default maximum file size for uploaded images in bytes (2 MB).
	private const DEFAULT_MAX_FILE_SIZE = 2 * 1024 * 1024; // 2 MB

	/**
	 * Handler to get topic submitted.
	 *
	 * @param \WP_REST_Request $request The request object.
	 * @since 0.0.1
	 * @return void
	 */
	public function submit_topic( $request ): void {

		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$submitted_data = ! empty( $_POST['formData'] ) ? json_decode( wp_unslash( $_POST['formData'] ), true ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Data is sanitized in the Sanitizer::sanitize_meta_data() method.

		if ( empty( $submitted_data ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'default' ) ] );
		}

		$comment_data    = stripslashes($submitted_data['custom_post_content']); // phpcs:ignore -- Data is sanitized in the wp_kses_post() method.
		$current_user_id = get_current_user_id();
		$dimensions      = apply_filters( 'suredash_comment_image_dimensions', self::DEFAULT_IMAGE_DIMENSIONS );
		$max_file_size   = apply_filters( 'suredash_comment_max_file_size', self::DEFAULT_MAX_FILE_SIZE );

		$allowed_types   = [ 'image/gif', 'image/png', 'image/jpeg', 'image/jpg' ];
		$uploaded_images = [];
		$cover_image_url = '';

		// Handle uploaded images.
		foreach ( $_FILES as $key => $file ) {
			if ( $file['error'] === UPLOAD_ERR_OK ) {

				$file['name'] = uniqid( 'image_' ) . '.' . pathinfo( $file['name'], PATHINFO_EXTENSION );

				$this->validate_uploaded_image( $file, $allowed_types, $dimensions, $max_file_size );

				if ( method_exists( Uploader::get_instance(), 'process_media' ) ) {
					$uploaded_url = Uploader::get_instance()->process_media(
						$file['name'],
						$file,
						$current_user_id,
						'assets'
					);

					if ( ! empty( $uploaded_url ) ) {
						$uploaded_images[] = $uploaded_url;

						if ( preg_match( '/image_(\d+)/', $key, $matches ) ) {
							$image_index  = $matches[1]; // This will be "0", "1", etc.
							$comment_data = preg_replace(
								'/<img([^>]*?)src=""([^>]*?)data-image-index="' . $image_index . '"([^>]*?)>/',
								'<img$1src="' . esc_url( $uploaded_url ) . '"$2data-image-index="' . $image_index . '"$3>',
								strval( $comment_data )
							);
						}

						if ( $key === 'custom_post_cover_image' ) {
							$cover_image_url = $uploaded_url;
						}
					}
				}
			}
		}
		$submitted_data['custom_post_content'] = $comment_data;

		$submitted_data = Sanitizer::sanitize_meta_data( $submitted_data, 'metadata' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Data is sanitized in the Sanitizer::sanitize_meta_data() method.

		$post_name     = '';
		$filtered_data = [];
		$category_id   = 0;

		// Cover media and embed media.
		$embed_url = '';

		foreach ( $submitted_data as $key => $value ) {
			if ( empty( $value ) ) {
				continue;
			}

			switch ( $key ) {
				case 'custom_post_title':
					$post_name                   = sanitize_title( $value );
					$filtered_data['post_title'] = strval( $value );
					break;

				case 'custom_post_content':
					$filtered_data['post_content'] = do_shortcode( $value );
					break;

				case 'custom_post_tax_id':
					$category_id = absint( $value );
					break;

				case 'custom_post_embed_media':
					$embed_url = esc_url_raw( $value );
					break;
			}
		}

		$other_defaults = [
			'post_name'   => $post_name,
			'post_type'   => SUREDASHBOARD_FEED_POST_TYPE,
			'post_status' => apply_filters( 'portal_inserting_default_post_status', 'publish' ),
			'post_author' => get_current_user_id(),
		];

		// Now its time to create a post with defaults & filtered data.
		$post_id = sd_wp_insert_post( array_merge( $other_defaults, $filtered_data ) );

		if ( is_wp_error( $post_id ) ) {
			foreach ( $uploaded_images as $image ) {
				// Delete the uploaded image.
				$upload_dir  = wp_upload_dir();
				$upload_path = $upload_dir['basedir'] . '/suredashboard/' . $current_user_id . '/assets/';
				$upload_url  = $upload_dir['baseurl'] . '/suredashboard/' . $current_user_id . '/assets/';
				$image_path  = str_replace( $upload_url, $upload_path, $image );
				unlink($image_path); // phpcs:ignore -- This is a safe operation.
			}
			wp_send_json_error( [ 'message' => $post_id->get_error_message() ] );
		}

		// Check if any user mentioned in the post content, having data-portal_mentioned_user="12" attribute.
		if ( ! empty( $filtered_data['post_content'] ) ) {
			$filtered_data['post_content'] = preg_replace_callback(
				'/data-portal_mentioned_user="(\d+)"/',
				// @phpstan-ignore-next-line
				static function( $matches ) use ( $post_id ): void {

					$tagged_id       = absint( $matches[1] );
					$current_user_id = get_current_user_id();
					$topic_id        = absint( $post_id );

					if ( method_exists( Notifier_Base::get_instance(), 'dispatch_user_notification' ) ) {
						// Dispatch mentioning notification.
						Notifier_Base::get_instance()->dispatch_user_notification(
							'suredashboard_user_mentioned',
							[
								'mentioned_user' => $tagged_id,
								'topic_id'       => $topic_id,
								'caller'         => $current_user_id,
							]
						);
					}
				},
				$filtered_data['post_content']
			);
		}

		// Update post meta for cover image.
		if ( ! empty( $cover_image_url ) ) {
			sd_update_post_meta( $post_id, 'custom_post_cover_image', $cover_image_url );
		}
		// Update embed media link.
		if ( ! empty( $embed_url ) ) {
			sd_update_post_meta( $post_id, 'custom_post_embed_media', $embed_url );
		}

		// Instead of using 'tax_input' used 'wp_set_post_terms', because ‘tax_input’ requires ‘assign_terms’ access to the taxonomy.
		if ( $category_id ) {
			wp_set_post_terms( $post_id, [ $category_id ], SUREDASHBOARD_FEED_TAXONOMY );
		}

		if ( method_exists( Notifier_Base::get_instance(), 'dispatch_admin_notification' ) ) {
			// Dispatch notification.
			Notifier_Base::get_instance()->dispatch_admin_notification( 'suredashboard_user_submitted_topic', [ 'topic_id' => $post_id ] );
		}

		wp_send_json_success( [ 'message' => Labels::get_label( 'post_submitted_successfully' ) ] );
	}

	/**
	 * Handler to load more posts.
	 *
	 * @param \WP_REST_Request $request The request object.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function load_more_posts( $request ): void {

		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		ob_start();

		$base_id     = ! empty( $_POST['base_id'] ) ? absint( $_POST['base_id'] ) : 0;
		$taxonomy    = ! empty( $_POST['taxonomy'] ) ? sanitize_text_field( wp_unslash( $_POST['taxonomy'] ) ) : SUREDASHBOARD_FEED_TAXONOMY;
		$post_type   = ! empty( $_POST['post_type'] ) ? sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) : SUREDASHBOARD_FEED_POST_TYPE;
		$category_id = ! empty( $_POST['category'] ) ? absint( $_POST['category'] ) : 0;
		$paged       = ! empty( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
		$user_id     = ! empty( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;

		$queried_page            = ! empty( $_POST['sub_queried_page'] ) ? sanitize_text_field( wp_unslash( $_POST['sub_queried_page'] ) ) : '';
		$enforce_excerpt_content = $queried_page === 'user-view' || $queried_page === 'feeds';

		$pinned_posts = Helper::get_pinned_posts( $base_id );

		if ( $user_id ) {
			$result = Controller::get_user_query_data(
				'Feeds',
				apply_filters(
					'suredashboard_user_queried_post_args',
					[
						'post_types'     => [ $post_type ],
						'user_id'        => $user_id,
						'posts_per_page' => Helper::get_option( 'feeds_per_page', 5 ),
						'paged'          => $paged,
					]
				)
			);
		} else {
			$result = Controller::get_query_data(
				'Feeds',
				[
					'category_id'    => $category_id,
					'post_type'      => $post_type,
					'paged'          => $paged,
					'taxonomy'       => $taxonomy,
					'posts_per_page' => Helper::get_option( 'feeds_per_page', 5 ),
				]
			);
		}

		if ( ! empty( $result ) ) {
			foreach ( $result as $post ) {

				if ( empty( $post['ID'] ) ) {
					continue;
				}

				// If pinned post arrived, skip this post as it's already rendered at first.
				if ( in_array( absint( $post['ID'] ), $pinned_posts, true ) ) {
					continue;
				}

				/**
				 * Enforce excerpt content type for user-view & feeds page.
				 * Case: query_var reset after load more which fails the suredash_get_sub_queried_page call.
				 *
				 * @since 1.0.0
				 */
				if ( $enforce_excerpt_content ) {
					add_filter( 'suredash_post_enforce_excerpt_content', '__return_true' );
				}

				// Use the Helper function to render the post.
				Helper::render_post( $post, $base_id );

				/**
				 * Remove the filter after rendering the post.
				 *
				 * @since 1.0.0
				 */
				if ( $enforce_excerpt_content ) {
					remove_filter( 'suredash_post_enforce_excerpt_content', '__return_true' );
				}
			}
		}

		wp_reset_postdata();

		$content = ob_get_clean();

		wp_send_json_success( [ 'content' => $content ] );
	}

	/**
	 * Bookmark an item.
	 *
	 * @param \WP_REST_Request $request The request object.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function bookmark_item( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$item_id   = ! empty( $_POST['item_id'] ) ? absint( $_POST['item_id'] ) : 0;
		$item_type = ! empty( $_POST['item_type'] ) ? sanitize_text_field( wp_unslash( $_POST['item_type'] ) ) : SUREDASHBOARD_POST_TYPE;

		if ( ! $item_id ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'default' ) ] );
		}

		$status           = 'none';
		$bookmarked_items = suredash_get_all_bookmarked_items();
		$bookmarked_items = ! empty( $bookmarked_items ) ? $bookmarked_items : [];

		if ( isset( $bookmarked_items[ $item_id ] ) ) {
			$status = 'un-bookmarked';
			unset( $bookmarked_items[ $item_id ] ); // Un-bookmark the item.
		} else {
			$status                       = 'bookmarked';
			$bookmarked_items[ $item_id ] = $item_type; // Bookmark the item.
		}

		sd_update_user_meta( get_current_user_id(), 'portal_bookmarked_items', $bookmarked_items );

		wp_send_json_success( [ 'status' => $status ] );
	}

	/**
	 * Handler to update user profile.
	 *
	 * @param \WP_REST_Request $request The request object.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function update_user_profile( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$first_name         = ! empty( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
		$last_name          = ! empty( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
		$display_name       = ! empty( $_POST['display_name'] ) ? sanitize_text_field( wp_unslash( $_POST['display_name'] ) ) : '';
		$description        = ! empty( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
		$user_banner_image  = ! empty( $_POST['user_banner_image'] ) ? sanitize_text_field( wp_unslash( $_POST['user_banner_image'] ) ) : '';
		$user_profile_photo = ! empty( $_FILES['user_profile_photo']['name'] ) ? sanitize_text_field( wp_unslash( $_FILES['user_profile_photo']['name'] ) ) : '';

		$isset_cover_image   = isset( $_FILES['user_banner_image'] ) && ! empty( $_FILES['user_banner_image']['name'] ) ? true : false;
		$sanitized_file_data = $isset_cover_image ? Sanitizer::sanitize_meta_data( $_FILES['user_banner_image'], 'array' ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Data is sanitized in the Sanitizer::sanitize_meta_data() method.

		$isset_profile_image    = isset( $_FILES['user_profile_photo'] ) && ! empty( $_FILES['user_profile_photo']['name'] ) ? true : false;
		$sanitized_profile_data = $isset_profile_image ? Sanitizer::sanitize_meta_data( $_FILES['user_profile_photo'], 'array' ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Data is sanitized in the Sanitizer::sanitize_meta_data() method.

		$user_id = get_current_user_id();

		$user_args = [
			'ID'           => $user_id,
			'first_name'   => $first_name,
			'last_name'    => $last_name,
			'display_name' => $display_name,
			'description'  => $description,
		];

		$result = sd_wp_update_user( $user_args );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result );
		}

		// Update cover image link.
		$cover_url = $isset_cover_image && method_exists( Uploader::get_instance(), 'process_media' ) ? Uploader::get_instance()->process_media( $user_banner_image, $sanitized_file_data, $user_id ) : '';
		if ( ! is_wp_error( $cover_url ) && ! empty( $cover_url ) ) {
			sd_update_user_meta( $user_id, 'user_banner_image', $cover_url );
		}

		// Update profile image link.
		$profile_url = $isset_profile_image && method_exists( Uploader::get_instance(), 'process_media' ) ? Uploader::get_instance()->process_media( $user_profile_photo, $sanitized_profile_data, $user_id ) : '';
		if ( ! is_wp_error( $profile_url ) && ! empty( $profile_url ) ) {
			sd_update_user_meta( $user_id, 'user_profile_photo', $profile_url );
		}

		// Update password if provided.
		if ( ! empty( $_POST['new_password'] ) ) {
			wp_set_password( $_POST['new_password'], $user_id ); // phpcs:ignore -- Data is sanitized in the wp_set_password() method.
		}

		wp_send_json_success(
			[
				'message' => Labels::get_label( 'profile_updated' ),
			]
		);
	}

	/**
	 * React with a post.
	 *
	 * @param \WP_REST_Request $request The request object.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function entity_reaction( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$entity          = ! empty( $_POST['entity'] ) ? sanitize_text_field( wp_unslash( $_POST['entity'] ) ) : 'post';
		$entity_id       = ! empty( $_POST['entity_id'] ) ? absint( $_POST['entity_id'] ) : 0;
		$current_user_id = get_current_user_id();
		$is_comment_ent  = $entity === 'comment';

		if ( ! $entity_id ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'default' ) ] );
		}

		// Gather the response data.
		$response = [];

		// Update the post/comment meta for likes.
		if ( $is_comment_ent ) {
			$entity_reactions = get_comment_meta( $entity_id, 'portal_comment_likes', true );
		} else {
			$entity_reactions = sd_get_post_meta( $entity_id, 'portal_post_likes', true );
		}
		$entity_reactions = is_array( $entity_reactions ) ? $entity_reactions : [];

		if ( in_array( $current_user_id, $entity_reactions, true ) ) {
			$like_status      = 'unliked';
			$entity_reactions = array_diff( $entity_reactions, [ $current_user_id ] );
		} else {
			$like_status        = 'liked';
			$entity_reactions[] = $current_user_id;

			$comment = get_comment( $entity_id );
			$author  = $is_comment_ent && is_object( $comment ) ? $comment->user_id : get_post_field( 'post_author', $entity_id );

			// Dispatch like notification only when liking (not on unlike) && Don't notify if user likes their own post.
			if ( $current_user_id !== absint( $author ) && method_exists( Notifier_Base::get_instance(), 'dispatch_common_notification' ) ) {
				Notifier_Base::get_instance()->dispatch_common_notification(
					'suredashboard_entity_like',
					[
						'caller'    => $current_user_id,
						'entity'    => $entity,
						'entity_id' => $entity_id,
						'author'    => $author,
						'count'     => count( $entity_reactions ),
					]
				);
			}
		}

		if ( $is_comment_ent ) {
			update_comment_meta( $entity_id, 'portal_comment_likes', $entity_reactions );
		} else {
			sd_update_post_meta( $entity_id, 'portal_post_likes', $entity_reactions );
		}

		// Update the post ID to user liked meta data.
		if ( $is_comment_ent ) {
			$user_liked_entities = sd_get_user_meta( $current_user_id, 'portal_user_liked_comments', true );
		} else {
			$user_liked_entities = sd_get_user_meta( $current_user_id, 'portal_user_liked_posts', true );
		}
		$user_liked_entities = is_array( $user_liked_entities ) ? $user_liked_entities : [];

		if ( $like_status === 'liked' ) {
			$user_liked_entities[] = $entity_id;
		} else {
			$user_liked_entities = array_diff( $user_liked_entities, [ $entity_id ] );
		}

		if ( $is_comment_ent ) {
			sd_update_user_meta( $current_user_id, 'portal_user_liked_comments', $user_liked_entities );
		} else {
			sd_update_user_meta( $current_user_id, 'portal_user_liked_posts', $user_liked_entities );
		}

		$response['like_status']          = $like_status;
		$response['like_count']           = count( $entity_reactions );
		$response['reacted_by_user_id']   = $current_user_id;
		$response['reacted_by_user_name'] = get_the_author_meta( 'display_name', $current_user_id );

		// Get list of users who liked the comment.
		$user_list                   = suredash_get_comment_liked_users( $entity_id );
		$response['tooltip_content'] = $user_list['tooltip_content'];

		wp_send_json_success( $response );
	}

	/**
	 * Search the users based on the search query.
	 * Usecase: User mentions.
	 *
	 * @param \WP_REST_Request $request The request object.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function search_user( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$search = ! empty( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
		$users  = new \WP_User_Query(
			[
				'search'         => '*' . esc_attr( $search ) . '*',
				'fields'         => [ 'ID', 'display_name' ],
				'search_columns' => [ 'display_name', 'user_email' ],
				'orderby'        => 'display_name',
				'count_total'    => false,
			]
		);

		$users    = $users->get_results();
		$response = [];

		if ( empty( $users ) ) {
			wp_send_json_success( $response );
		}

		foreach ( $users as $user ) {
			$response[] = [
				'id'           => ! empty( $user->ID ) ? $user->ID : '',
				'display_name' => ! empty( $user->display_name ) ? $user->display_name : __( 'Unnamed', 'suredash' ),
				'avatar'       => ! empty( $user->ID ) ? suredash_get_user_avatar( intval( $user->ID ), false, 24 ) : '',
			];
		}

		wp_send_json_success( $response );
	}

	/**
	 * Get the post reactor data: like/comment
	 *
	 * @param \WP_REST_Request $request The request object.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function post_reactor_data( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$post_id    = ! empty( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$react_type = ! empty( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'like';

		if ( ! $post_id ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'default' ) ] );
		}

		ob_start();

		if ( $react_type === 'like' ) {
			suredash_likes_list_markup( $post_id );
		} elseif ( $react_type === 'comment' ) {
			suredash_comments_markup( $post_id, true, [], 'sd-mt-20 sd-w-full', 'modal' );
		}

		$markup = ob_get_clean();

		wp_send_json_success( [ 'content' => $markup ] );
	}

	/**
	 * Handles the submission of a new comment.
	 *
	 * This function checks if the required data is present and if the user is logged in. If not, it returns an error.
	 * If the data is valid, it creates a new comment using the provided data and generates HTML for the new comment.
	 * The HTML is then sent back to the client as a JSON response.
	 *
	 * @param \WP_REST_Request $request The request object.
	 *
	 * @return void
	 * @since 0.0.1
	 */
	public function submit_comment( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		// Check if required data exists and if the user is logged in.
		if ( empty( $_POST['comment'] ) || empty( $_POST['comment_post_ID'] ) || ! is_user_logged_in() ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'default' ) ] );
		}

		$comment_data    = stripslashes( $_POST['comment'] ); // phpcs:ignore -- Data is sanitized in the wp_kses_post() method.
		$current_user_id = get_current_user_id();
		$dimensions      = apply_filters( 'suredash_comment_image_dimensions', self::DEFAULT_IMAGE_DIMENSIONS );
		$max_file_size   = apply_filters( 'suredash_comment_max_file_size', self::DEFAULT_MAX_FILE_SIZE );

		$allowed_types   = [ 'image/gif', 'image/png', 'image/jpeg', 'image/jpg' ];
		$uploaded_images = [];

		// Handle uploaded images.
		foreach ( $_FILES as $key => $file ) {
			if ( $file['error'] === UPLOAD_ERR_OK ) {

				$file['name'] = uniqid( 'image_' ) . '.' . pathinfo( $file['name'], PATHINFO_EXTENSION );

				$this->validate_uploaded_image( $file, $allowed_types, $dimensions, $max_file_size );

				if ( method_exists( Uploader::get_instance(), 'process_media' ) ) {
					$uploaded_url = Uploader::get_instance()->process_media(
						$file['name'],
						$file,
						$current_user_id,
						'assets'
					);

					if ( ! empty( $uploaded_url ) ) {
						$uploaded_images[] = $uploaded_url;

						if ( preg_match( '/image_(\d+)/', $key, $matches ) ) {
							$image_index  = $matches[1]; // This will be "0", "1", etc.
							$comment_data = preg_replace(
								'/<img([^>]*?)src=""([^>]*?)data-image-index="' . $image_index . '"([^>]*?)>/',
								'<img$1src="' . esc_url( $uploaded_url ) . '"$2data-image-index="' . $image_index . '"$3>',
								strval( $comment_data )
							);
						}
					}
				}
			}
		}

		$iframe_placeholders = [];
		$comment_data        = preg_replace_callback(
			'/<iframe[^>]*src=["\']([^"\']+)["\'][^>]*><\/iframe>/i',
			function ( $matches ) use ( &$iframe_placeholders ) {
				$src = $matches[1];

				// Validate the iframe src URL.
				if ( $this->validate_iframe_src( $src ) ) {
					$placeholder                         = '[iframe-placeholder-' . count( $iframe_placeholders ) . ']';
					$iframe_placeholders[ $placeholder ] = $matches[0]; // Store the original iframe tag.
					return $placeholder; // Replace iframe with placeholder.
				}

				// If the URL is invalid, remove the iframe.
				return '';
			},
			strval( $comment_data )
		);

		$filtered_comment  = wp_kses_post( strval( $comment_data ) ); // phpcs:ignore -- Data is sanitized in the wp_kses_post() method.

		// Replace placeholders with original iframe tags.
		foreach ( $iframe_placeholders as $placeholder => $iframe_tag ) {
			$filtered_comment = str_replace( $placeholder, $iframe_tag, $filtered_comment );
		}

		$comment_parent_id = isset( $_POST['comment_parent'] ) ? absint( $_POST['comment_parent'] ) : 0;
		$depth             = isset( $_POST['depth'] ) ? absint( $_POST['depth'] ) : 0;

		$comment_id = \wp_new_comment( // Prepare comment data.
			[
				'comment_post_ID'      => absint( $_POST['comment_post_ID'] ),
				'comment_author'       => wp_get_current_user()->display_name,
				'comment_author_email' => wp_get_current_user()->user_email,
				'comment_content'      => $filtered_comment,
				'comment_type'         => '',
				'comment_author_url'   => '',
				'user_id'              => $current_user_id,
				'comment_parent'       => $comment_parent_id,
			]
		);

		if ( is_wp_error( $comment_id ) ) {
			foreach ( $uploaded_images as $image ) {
				// Delete the uploaded image.
				$upload_dir  = wp_upload_dir();
				$upload_path = $upload_dir['basedir'] . '/suredashboard/' . $current_user_id . '/assets/';
				$upload_url  = $upload_dir['baseurl'] . '/suredashboard/' . $current_user_id . '/assets/';
				$image_path  = str_replace( $upload_url, $upload_path, $image );
				unlink( $image_path ); // phpcs:ignore -- This is a safe operation.
			}
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'default' ) ] );
		}

		// Generate HTML for the new comment.
		$comment = \get_comment( absint( $comment_id ) );

		ob_start();
		wp_list_comments(
			[
				'callback' => 'suredash_comments_list_callback',
				'style'    => 'ol',
				'depth'    => $depth + 1,
			],
			[ $comment ] // @phpstan-ignore-line
		);
		$comment_html = ob_get_clean();

		// If a comment is replying to another comment, then dispatch a notification to the parent comment author.
		if ( $comment_parent_id !== 0 ) {
			// Get the comment object.
			$parent_comment = get_comment( $comment_parent_id );

			// Check if the comment exists and has a user ID.
			if ( $parent_comment && $parent_comment->user_id ) {
				// Get the user object.
				$user = get_user_by( 'ID', $parent_comment->user_id );

				// Check if the user exists.
				if ( $user ) {
					// Access user information.
					$parent_comment_user_id = $user->ID;

					if ( method_exists( Notifier_Base::get_instance(), 'dispatch_common_notification' ) ) {
						// Dispatch mentioning notification.
						Notifier_Base::get_instance()->dispatch_common_notification(
							'suredashboard_comment_reply',
							[
								'mentioned_user' => $parent_comment_user_id,
								'comment_id'     => $comment_id,
								'caller'         => $current_user_id,
							]
						);
					}
				}
			}
		}

		// Check if the comment contains any user mentions.
		$filtered_comment = preg_replace_callback(
			'/data-portal_mentioned_user="(\d+)"/',
			// @phpstan-ignore-next-line
			static function( $matches ) use ( $comment_id, $current_user_id ): void {

				$tagged_id  = absint( $matches[1] );
				$comment_id = absint( $comment_id );

				if ( method_exists( Notifier_Base::get_instance(), 'dispatch_user_notification' ) ) {
					// Dispatch mentioning notification.
					Notifier_Base::get_instance()->dispatch_user_notification(
						'suredashboard_user_mentioned',
						[
							'mentioned_user' => $tagged_id,
							'comment_id'     => $comment_id,
							'caller'         => $current_user_id,
						]
					);
				}
			},
			$filtered_comment
		);

		// Dispatch comment submitted notification to the topic author.
		if ( absint( $_POST['comment_post_ID'] ) ) {
			if ( method_exists( Notifier_Base::get_instance(), 'dispatch_common_notification' ) ) {
				Notifier_Base::get_instance()->dispatch_common_notification(
					'suredashboard_topic_comment',
					[
						'comment_id'   => $comment_id,
						'caller'       => $current_user_id,
						'topic_id'     => absint( $_POST['comment_post_ID'] ),
						'topic_author' => get_post_field( 'post_author', absint( $_POST['comment_post_ID'] ) ),
					]
				);
			}
		}

		wp_send_json_success( [ 'comment_html' => $comment_html ] );
	}

	/**
	 * Validate iframe src URL to ensure it is from YouTube or Vimeo.
	 *
	 * @param string $url The iframe src URL.
	 * @return bool True if the URL is valid, false otherwise.
	 */
	private function validate_iframe_src( $url ) {
		$parsed_url = wp_parse_url( $url );

		// Check if the URL has a valid host.
		if ( ! isset( $parsed_url['host'] ) ) {
			return false;
		}

		// List of allowed domains.
		$allowed_domains = [
			'youtube.com',
			'www.youtube.com',
			'youtu.be',
			'vimeo.com',
			'www.vimeo.com',
			'player.vimeo.com',
		];

		// Check if the host matches any of the allowed domains.
		return in_array( $parsed_url['host'], $allowed_domains, true );
	}

	/**
	 * Validate the uploaded image.
	 *
	 * @param array<mixed> $file The uploaded file.
	 * @param array<mixed> $allowed_types The allowed file types.
	 * @param array<mixed> $dimensions The allowed dimensions.
	 * @param int          $max_file_size The maximum file size.
	 *
	 * @return mixed
	 * @since 0.0.6
	 */
	private function validate_uploaded_image( $file, $allowed_types, $dimensions, $max_file_size ) {
		if ( ! in_array( $file['type'], $allowed_types ) ) {
			wp_send_json_error( __( 'Invalid file type. Only GIF, PNG, JPEG, and JPG are allowed.', 'suredash' ) );
		}

		$image_info = getimagesize( $file['tmp_name'] );
		if ( $image_info === false ) {
			wp_send_json_error( __( 'Uploaded file is not a valid image.', 'suredash' ) );
		}

		if ( $file['size'] > $max_file_size ) {
			wp_send_json_error( __( 'File size larger than permissible.', 'suredash' ) );
		}

		return null;
	}
}
