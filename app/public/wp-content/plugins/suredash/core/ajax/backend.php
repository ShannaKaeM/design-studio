<?php
/**
 * Backend AJAX.
 *
 * @package SureDash
 * @since 1.0.0
 */

namespace SureDashboard\Core\Ajax;

use SureDashboard\Inc\Traits\Ajax;
use SureDashboard\Inc\Traits\Get_Instance;
use SureDashboard\Inc\Utils\Sanitizer;

defined( 'ABSPATH' ) || exit;

/**
 * This class setup all admin AJAX action
 *
 * @class Ajax
 */
class Backend {
	use Ajax;
	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->ajax_events = [
			'update_a_space',
			'update_a_space_group',
		];

		$this->initiate_ajax_events();
		$this->create_ajax_nonces();
	}

	/**
	 * Update a item with dataset forwarded.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function update_a_space(): void {
		if ( ! check_ajax_referer( 'portal_update_a_space', 'security', false ) ) {
			wp_send_json_error( [ 'message' => $this->get_ajax_event_error( 'nonce' ) ] );
		}

		$post_id   = ! empty( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$post_data = ! empty( $_POST['formData'] ) ? Sanitizer::sanitize_meta_data( json_decode( wp_unslash( $_POST['formData'] ), true ), 'metadata' ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Data is sanitized in the Sanitizer::sanitize_meta_data() method.
		if ( empty( $post_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid item ID.', 'suredash' ) ] );
		}
		if ( $post_id !== 0 && is_array( $post_data ) ) {
			foreach ( $post_data as $key => $value ) {
				// Skipping post status as its managed with space_status.
				if ( $key === 'post_status' ) {
					continue;
				}
				switch ( $key ) {
					case 'post_title':
						$post_name = preg_replace( '/[^\x00-\x7F]/u', '', $value );
						$post_name = sanitize_title( (string) $post_name );
						wp_update_post(
							[
								'ID'         => $post_id,
								'post_title' => $value,
								'post_name'  => $post_name,
							]
						);
						break;
					case 'space_status':
						wp_update_post(
							[
								'ID'          => $post_id,
								'post_status' => $value,
							]
						);
						break;
					case 'comments':
						update_post_meta( $post_id, (string) $key, $value );
						$comments_status = boolval( $value ) ? 'open' : 'closed';
						wp_update_post(
							[
								'ID'             => $post_id,
								'comment_status' => $comments_status,
							]
						);
						break;
					case 'image_url':
						update_post_meta( $post_id, (string) $key, $value );
						$attachment_id = attachment_url_to_postid( (string) $value );
						if ( $attachment_id ) {
							update_post_meta( $post_id, '_thumbnail_id', $attachment_id );
						}
						break;
					case 'banner_url':
						update_post_meta( $post_id, (string) $key, $value );
						$attachment_id = attachment_url_to_postid( (string) $value );
						if ( $attachment_id ) {
							update_post_meta( $post_id, '_banner_id', $attachment_id );
						}
						break;
					case 'wp_post':
						if ( ! empty( $value['value'] ) ) {
							update_post_meta(
								$post_id,
								'wp_post',
								[
									'value' => $value['value'],
									'label' => get_the_title( $value['value'] ) ?? '',
								]
							);
						}
						break;
					default:
						update_post_meta( $post_id, (string) $key, $value );
						break;
				}
			}

			wp_send_json_success(
				[
					'message' => $this->get_ajax_event_error( 'success' ),
				]
			);
		}
	}

	/**
	 * Update a group with dataset forwarded.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function update_a_space_group(): void {
		if ( ! check_ajax_referer( 'portal_update_a_space_group', 'security', false ) ) {
			wp_send_json_error( [ 'message' => $this->get_ajax_event_error( 'nonce' ) ] );
		}

		$term_id   = ! empty( $_POST['term_id'] ) ? absint( $_POST['term_id'] ) : 0;
		$term_data = ! empty( $_POST['formData'] ) ? Sanitizer::sanitize_meta_data( json_decode( wp_unslash( $_POST['formData'] ), true ), 'metadata' ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Data is sanitized in the Sanitizer::sanitize_meta_data() method.
		if ( empty( $term_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid group ID.', 'suredash' ) ] );
		}
		if ( $term_id !== 0 && is_array( $term_data ) ) {
			foreach ( $term_data as $key => $value ) {
				switch ( $key ) {
					case 'term_name':
						$term_slug = sanitize_title( (string) $value );
						wp_update_term(
							$term_id,
							SUREDASHBOARD_TAXONOMY,
							[
								'name' => $value,
								'slug' => $term_slug,
							]
						);
						break;
					case 'term_description':
						wp_update_term(
							$term_id,
							SUREDASHBOARD_TAXONOMY,
							[
								'description' => (string) $value,
							]
						);
						break;
					default:
						update_term_meta( $term_id, (string) $key, $value );
						break;
				}
			}

			wp_send_json_success(
				[
					'message' => $this->get_ajax_event_error( 'success' ),
				]
			);
		}
	}
}
