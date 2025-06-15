<?php
/**
 * Comment Management Stuff
 *
 * This class will holds the Comment related handling.
 *
 * @package SureDash
 * @since 0.0.6
 */

namespace SureDashboard\Inc\Compatibility;

use SureDashboard\Inc\Traits\Get_Instance;

defined( 'ABSPATH' ) || exit;

/**
 * Comment & Post Handler
 *
 * @since 0.0.6
 */
class Comment {
	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since 0.0.6
	 * @return void
	 */
	public function __construct() {
		add_action(
			'deleted_comment',
			function ( $comment_id, $comment ): void {
				if ( $comment_id && $comment ) {
					$this->delete_related_media( $comment->comment_content );
				}
			},
			10,
			2
		);

		add_action(
			'deleted_post',
			function ( $post_id, $post ): void {
				if ( $post_id && $post && $post->post_type === SUREDASHBOARD_FEED_POST_TYPE ) {
					$this->delete_related_media( $post->post_content );
				}
			},
			10,
			2
		);
	}

	/**
	 * Delete media files associated with content.
	 *
	 * @param string $content The content containing media URLs.
	 * @since 0.0.6
	 * @return void
	 */
	public function delete_related_media( $content ): void {
		// Extract image URLs from the content.
		preg_match_all( '/<img[^>]+src="([^">]+)"/', $content, $matched_images );

		if ( ! empty( $matched_images[1] ) ) {
			// Get the upload directory paths.
			$upload_dir  = wp_upload_dir();
			$upload_path = $upload_dir['basedir'];
			$upload_url  = $upload_dir['baseurl'];

			// Loop through each image URL and delete the corresponding file.
			foreach ( $matched_images[1] as $image_url ) {
				$image_path = str_replace( $upload_url, $upload_path, $image_url );
				if ( file_exists( $image_path ) ) {
					unlink( $image_path ); // phpcs:ignore -- This is a safe operation.
				}
			}
		}
	}
}
