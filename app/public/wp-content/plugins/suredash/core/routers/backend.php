<?php
/**
 * Post Router Initialize.
 *
 * @package SureDash
 */

namespace SureDashboard\Core\Routers;

use SureDashboard\Core\Models\Controller;
use SureDashboard\Core\Notifier\Base as Notifier_Base;
use SureDashboard\Core\Shortcodes\Content_Header;
use SureDashboard\Core\Shortcodes\Navigation;
use SureDashboard\Inc\Traits\Get_Instance;
use SureDashboard\Inc\Traits\Rest_Errors;
use SureDashboard\Inc\Utils\Helper;
use SureDashboard\Inc\Utils\PostMeta;
use SureDashboard\Inc\Utils\Sanitizer;
use SureDashboard\Inc\Utils\Settings;
use SureDashboard\Inc\Utils\TermMeta;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Post Router.
 */
class Backend {
	use Get_Instance;
	use Rest_Errors;

	/**
	 * Handler to update docs position.
	 *
	 * @param \WP_REST_Request $request  Request object.
	 *
	 * @since 0.0.2
	 * @return void
	 */
	public function update_item_order_by_group( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$term_id        = ! empty( $_POST['list_term_id'] ) ? absint( $_POST['list_term_id'] ) : 0;
		$reordered_data = ! empty( $_POST['items_ordering_data'] ) ? implode( ',', filter_var_array( json_decode( $_POST['items_ordering_data'], true ), FILTER_SANITIZE_NUMBER_INT ) ) : ''; // phpcs:ignore -- Data is sanitized in the filter_var_array() method.

		if ( ! $term_id ) {
			wp_send_json_error( __( 'Invalid term ID.', 'suredash' ) );
		}

		if ( update_term_meta( $term_id, '_link_order', $reordered_data ) ) {
			wp_send_json_success( __( 'Successfully updated.', 'suredash' ) );
		}

		wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'default' ) ] );
	}

	/**
	 * Handler to update taxonomy order.
	 *
	 * @param \WP_REST_Request $request  Request object.
	 *
	 * @since 0.0.2
	 * @return void
	 */
	public function update_group_order( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$base_index = ! empty( $_POST['base_index'] ) ? filter_var( wp_unslash( $_POST['base_index'] ), FILTER_SANITIZE_NUMBER_INT ) : 0;

		if ( ! empty( $_POST['taxonomy_ordering_data'] ) ) {
			$decoded_data = json_decode( wp_unslash( $_POST['taxonomy_ordering_data'] ), true ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Data is sanitized further down the line with array_map.

			if ( is_array( $decoded_data ) ) {
				$taxonomy_ordering_data = array_map(
					static function( $item ) {
						return [
							'term_id' => isset( $item['term_id'] ) ? intval( $item['term_id'] ) : 0,
							'order'   => isset( $item['order'] ) ? intval( $item['order'] ) : 0,
						];
					},
					$decoded_data
				);

				foreach ( $taxonomy_ordering_data as $order_data ) {
					/**
					 * In order to account for how WordPress displays parent categories across various pages, we need to ensure that we check whether the parent category's position needs to be adjusted. If the current position of the category is lower than the base index (meaning it shouldn't appear on this page), then there's no need to update it.
					 */
					if ( $base_index > 0 ) {
						$current_position = get_term_meta( $order_data['term_id'], 'group_tax_position', true );
						if ( (int) intval( $current_position ) < (int) $base_index ) {
							continue;
						}
					}

					if ( ! empty( $order_data['term_id'] ) ) {
						update_term_meta( $order_data['term_id'], 'group_tax_position', (int) $order_data['order'] + (int) $base_index );
					}
				}

				do_action( 'portal_taxonomy_order_updated', $taxonomy_ordering_data, $base_index );
			}
		}

		wp_send_json_success();
	}

	/**
	 * Create a new category.
	 *
	 * @since 0.0.2
	 * @param string $category_name Category name.
	 * @param int    $hide_label    Hide label.
	 * @return int
	 */
	public function create_portal_group( $category_name, $hide_label = 0 ) {
		$term = term_exists( $category_name, SUREDASHBOARD_TAXONOMY );

		if ( is_array( $term ) ) {
			return $term['term_id'];
		}

		$term = \wp_insert_term( $category_name, SUREDASHBOARD_TAXONOMY );

		if ( ! is_wp_error( $term ) ) {
			\update_term_meta( $term['term_id'], 'hide_label', $hide_label );
			return $term['term_id'];
		}

		return 0;
	}

	/**
	 * Update a category.
	 *
	 * @since 0.0.2
	 * @param int    $term_id       Term ID.
	 * @param string $category_name Category name.
	 * @param int    $hide_label    Hide label.
	 * @return int
	 */
	public function update_portal_group( $term_id, $category_name, $hide_label = 0 ) {

		$term = \wp_update_term(
			$term_id,
			SUREDASHBOARD_TAXONOMY,
			[
				'name' => $category_name,
				'slug' => sanitize_title( (string) $category_name ),
			]
		);

		if ( ! is_wp_error( $term ) ) {
			\update_term_meta( $term['term_id'], 'hide_label', $hide_label );
			return $term['term_id'];
		}

		return 0;
	}

	/**
	 * Create a doc with category selected.
	 *
	 * @param \WP_REST_Request $request  Request object.
	 *
	 * @since 0.0.2
	 * @return void
	 */
	public function create_space( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$space_data = ! empty( $_POST['formData'] ) ? Sanitizer::sanitize_meta_data( json_decode( wp_unslash( $_POST['formData'] ), true ), 'metadata' ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Data is sanitized in the Sanitizer::sanitize_meta_data() method.

		$term_id   = 0;
		$post_attr = [
			'post_title'  => 'Untitled',
			'post_type'   => SUREDASHBOARD_POST_TYPE,
			'post_status' => 'draft',
			'post_author' => get_current_user_id(),
		];

		if ( is_array( $space_data ) && ! empty( $space_data['category'] ) ) {
			$value = $space_data['category'];

			if ( $value === 'create' ) {
				$custom_category_name = ! empty( $space_data['custom_category_title'] ) ? $space_data['custom_category_title'] : 'Untitled';
				$term_id              = $this->create_portal_group( $custom_category_name );
			} else {
				$term_id = absint( $value );
			}

			if ( ! $term_id ) {
				wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'default' ) ] );
			}
		}

		if ( is_array( $space_data ) && ! empty( $space_data['item_title'] ) ) {
			$post_attr['post_title'] = strval( $space_data['item_title'] );
			$post_name               = preg_replace( '/[^\x00-\x7F]/u', '', $post_attr['post_title'] );
			$post_attr['post_name']  = strval( $post_name );
		}

		if ( is_array( $space_data ) && ( ! empty( $space_data['space_status'] ) ) ) {
			$post_attr['post_status'] = strval( $space_data['space_status'] );
		}

		if ( $term_id ) {
			$item_id = sd_wp_insert_post(
				$post_attr,
			);

			if ( is_wp_error( $item_id ) ) {
				wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'default' ) ] );
			}

			// Instead of using 'tax_input' used 'wp_set_post_terms', because ‘tax_input’ requires ‘assign_terms’ access to the taxonomy.
			wp_set_post_terms( $item_id, [ absint( $term_id ) ], SUREDASHBOARD_TAXONOMY );

			if ( $item_id ) {
				$this->update_link_order_term( $item_id, $term_id );

				foreach ( $space_data as $key => $value ) {
					if ( $key === 'category' || $key === 'item_title' || $key === 'space_status' ) {
						continue;
					}

					sd_update_post_meta( $item_id, $key, $value );
				}

				// If integration of the space is posts_discussion then create a category for the SUREDASHBOARD_FEED_TAXONOMY with title as the space title, to be used in the feed.
				if ( ! empty( $space_data['integration'] ) && $space_data['integration'] === 'posts_discussion' ) {
					$category_name = $post_attr['post_title'];
					$feed_group_id = $this->create_forum_category( $item_id, $category_name );
					sd_update_post_meta( $item_id, 'feed_group_id', $feed_group_id );
				}

				if ( method_exists( Notifier_Base::get_instance(), 'dispatch_common_notification' ) ) {
					// Dispatch notification for a new space.
					Notifier_Base::get_instance()->dispatch_common_notification( 'suredashboard_new_space', [ 'space_id' => $item_id ] );
				}

				$post_meta = PostMeta::get_all_post_meta_values( $item_id );
				$meta_set  = [
					'post_id'          => $item_id,
					'permalink'        => get_permalink( $item_id, ),
					'post_status'      => get_post_status( $item_id, ),
					'edit_post_link'   => get_edit_post_link( $item_id, '' ),
					'delete_post_link' => get_delete_post_link( $item_id, ),
					'is_restricted'    => suredash_get_post_backend_restriction( $item_id ),
				];

				$post_meta = array_merge( $post_meta, $meta_set );

				wp_send_json_success(
					[
						'space_id'      => $item_id,
						'message'       => $this->get_rest_event_error( 'success' ),
						'edit_doc_link' => get_edit_post_link( $item_id, '' ),
						'meta'          => $post_meta,
					]
				);
			}
		}

		wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'default' ) ] );
	}

	/**
	 * Create a SD group.
	 *
	 * @param int    $space_id      Space ID.
	 * @param string $category_name Category name.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function create_forum_category( $space_id, $category_name ) {
		$term = term_exists( $category_name, SUREDASHBOARD_FEED_TAXONOMY );

		if ( is_array( $term ) ) {
			update_term_meta( absint( $term['term_id'] ), 'space_id', $space_id );
			return absint( $term['term_id'] );
		}

		$term = \wp_insert_term(
			__( 'Forum:', 'suredash' ) . ' ' . $category_name,
			SUREDASHBOARD_FEED_TAXONOMY,
			[
				'description' => 'This forum group is created for the space: ' . $category_name,
			]
		);

		if ( ! is_wp_error( $term ) ) {
			update_term_meta( absint( $term['term_id'] ), 'space_id', $space_id );
			return absint( $term['term_id'] );
		}

		return 0;
	}

	/**
	 * Update a space.
	 *
	 * @param int $item_id  Item ID.
	 * @param int $term_id  Term ID.
	 *
	 * @since 0.0.5
	 * @return void
	 */
	public function update_link_order_term( $item_id, $term_id ): void {
		$link_order    = get_term_meta( $term_id, '_link_order', true );
		$string_doc_id = (string) $item_id;
		$link_order    = ! empty( $link_order ) ? $link_order . ',' . $string_doc_id : $string_doc_id;
		update_term_meta( $term_id, '_link_order', $link_order );
	}

	/**
	 * Create a sub-content.
	 *
	 * Usecase: Lesson creation in a course.
	 *
	 * @param \WP_REST_Request $request  Request object.
	 *
	 * @since 0.0.2
	 * @return void
	 */
	public function create_community_content( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$community_content_data = ! empty( $_POST['subContentData'] ) ? Sanitizer::sanitize_meta_data( json_decode( wp_unslash( $_POST['subContentData'] ), true ), 'metadata' ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Data is sanitized in the Sanitizer::sanitize_meta_data() method.

		$post_attr = [
			'post_title'  => 'Untitled',
			'post_type'   => SUREDASHBOARD_SUB_CONTENT_POST_TYPE,
			'post_status' => 'publish',
			'post_author' => get_current_user_id(),
		];

		if ( is_array( $community_content_data ) && ! empty( $community_content_data['post_title'] ) ) {
			$post_attr['post_title'] = $community_content_data['post_title'];
		}

		$community_content_id = sd_wp_insert_post(
			$post_attr,
		);

		if ( is_wp_error( $community_content_id ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'default' ) ] );
		}

		if ( $community_content_id ) {
			foreach ( $community_content_data as $key => $value ) {
				if ( $key === 'post_title' ) {
					continue;
				}

				sd_update_post_meta( $community_content_id, $key, $value );
			}

			wp_send_json_success(
				[
					'message' => $this->get_rest_event_error( 'success' ),
					'data'    => [
						'label' => $post_attr['post_title'],
						'value' => $community_content_id,
					],
				]
			);
		}

		wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'default' ) ] );
	}

	/**
	 * Delete a sub-content.
	 *
	 * Usecase: Lesson creation in a course.
	 *
	 * @param \WP_REST_Request $request  Request object.
	 *
	 * @since 0.0.2
	 * @return void
	 */
	public function delete_community_content( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$community_content_data = ! empty( $_POST['subContentData'] ) ? Sanitizer::sanitize_meta_data( json_decode( wp_unslash( $_POST['subContentData'] ), true ), 'metadata' ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Data is sanitized in the Sanitizer::sanitize_meta_data() method.

		$post_id = is_array( $community_content_data ) && ! empty( $community_content_data['post_id'] ) ? absint( $community_content_data['post_id'] ) : 0;

		if ( ! $post_id ) {
			wp_send_json_error( [ 'message' => __( 'Invalid post ID.', 'suredash' ) ] );
		}

		$post_type = get_post_type( $post_id );

		if ( $post_type !== SUREDASHBOARD_SUB_CONTENT_POST_TYPE ) {
			wp_send_json_error( [ 'message' => __( 'Invalid post type.', 'suredash' ) ] );
		}

		$deleted = \wp_delete_post( $post_id, true );

		if ( $deleted ) {
			wp_send_json_success(
				[
					'message' => __( 'Successfully deleted.', 'suredash' ),
				]
			);
		}

		wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'default' ) ] );
	}

	/**
	 * Delete a space.
	 *
	 * Usecase: Lesson creation in a course.
	 *
	 * @param \WP_REST_Request $request  Request object.
	 *
	 * @since 0.0.2
	 * @return void
	 */
	public function delete_space( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$post_id = ! empty( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

		if ( ! $post_id ) {
			wp_send_json_error( [ 'message' => __( 'Invalid post ID.', 'suredash' ) ] );
		}

		$post_type = get_post_type( $post_id );

		if ( $post_type !== SUREDASHBOARD_POST_TYPE ) {
			wp_send_json_error( [ 'message' => __( 'Invalid post type.', 'suredash' ) ] );
		}

		$integration = sd_get_post_meta( $post_id, 'integration', true );

		// If space is of posts_discussion type, delete all posts in the forum.
		if ( $integration === 'posts_discussion' ) {
			$forum = absint( sd_get_post_meta( $post_id, 'feed_group_id', true ) );
			if ( $forum ) {
				$forum_posts = sd_get_posts(
					[
						'post_type'      => [ SUREDASHBOARD_FEED_POST_TYPE ],
						'posts_per_page' => -1,
						'tax_query'      => [
							[
								'taxonomy' => SUREDASHBOARD_FEED_TAXONOMY,
								'field'    => 'term_id',
								'terms'    => $forum,
							],
						],
						'select'         => 'ID, post_type',
					]
				);

				if ( ! empty( $forum_posts ) ) {
					foreach ( $forum_posts as $post ) {
						$post = (array) $post;
						if ( isset( $post['post_type'] ) && $post['post_type'] !== SUREDASHBOARD_FEED_POST_TYPE ) {
							continue;
						}
						\wp_delete_post( $post['ID'] ?? 0, true );
					}
				}

				\wp_delete_term( $forum, SUREDASHBOARD_FEED_TAXONOMY );
			}
		}

		// If space is of course type, delete all lessons.
		if ( $integration === 'course' ) {
			$course_sections = PostMeta::get_post_meta_value( $post_id, 'pp_course_section_loop' );
			$all_lessons_id  = [];
			if ( ! empty( $course_sections ) ) {
				foreach ( $course_sections as $section ) {
					foreach ( $section['section_medias'] as $lesson ) {
						$all_lessons_id[] = $lesson['value'];
					}
				}

				if ( ! empty( $all_lessons_id ) ) {
					foreach ( $all_lessons_id as $lesson_id ) {
						$lesson_post_type = get_post_type( $lesson_id );
						if ( $lesson_post_type !== SUREDASHBOARD_SUB_CONTENT_POST_TYPE ) {
							continue;
						}
						\wp_delete_post( $lesson_id, true );
					}
				}
			}
		}

		$deleted = \wp_delete_post( $post_id, true );

		if ( $deleted ) {
			wp_send_json_success(
				[
					'message' => __( 'Successfully deleted.', 'suredash' ),
				]
			);
		}

		wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'default' ) ] );
	}

	/**
	 * Create a space group.
	 *
	 * @param \WP_REST_Request $request  Request object.
	 *
	 * @since 0.0.2
	 * @return void
	 */
	public function create_space_group( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$custom_category_name = ! empty( $_POST['category_name'] ) ? sanitize_text_field( wp_unslash( $_POST['category_name'] ) ) : '';
		$hide_label           = ! empty( $_POST['hide_label'] ) ? sanitize_text_field( wp_unslash( $_POST['hide_label'] ) ) : '';
		$hide_label           = $hide_label === 'true' ? 1 : 0;

		$space_group = $this->create_portal_group( $custom_category_name, $hide_label );
		$space_group = absint( $space_group );

		$group_meta = TermMeta::get_all_group_meta_values( $space_group );
		$term_meta  = [
			'term_id'          => $space_group,
			'isCategory'       => true,
			'edit_term_link'   => get_edit_term_link( $space_group, SUREDASHBOARD_TAXONOMY ),
			'view_term_link'   => get_term_link( $space_group, SUREDASHBOARD_TAXONOMY ),
			'query_posts'      => [],
			'posts_count'      => 0,
			'delete_term_link' => str_replace( '&amp;', '&', admin_url( wp_nonce_url( 'edit-tags.php?action=delete&taxonomy=' . SUREDASHBOARD_TAXONOMY . "&tag_ID={$space_group}", 'delete-tag_' . $space_group ) ) ),
		];

		$group_meta = array_merge( $group_meta, $term_meta );

		if ( $space_group ) {
			wp_send_json_success(
				[
					'space_group_id' => $space_group,
					'message'        => $this->get_rest_event_error( 'success' ),
					'meta'           => $group_meta,
				]
			);
		}

		wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'default' ) ] );
	}

	/**
	 * Update a space group.
	 *
	 * @param \WP_REST_Request $request  Request object.
	 *
	 * @since 0.0.2
	 * @return void
	 */
	public function update_space_group( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$term_id    = ! empty( $_POST['term_id'] ) ? absint( $_POST['term_id'] ) : 0;
		$term_name  = ! empty( $_POST['category_name'] ) ? sanitize_text_field( wp_unslash( $_POST['category_name'] ) ) : '';
		$hide_label = ! empty( $_POST['hide_label'] ) ? sanitize_text_field( wp_unslash( $_POST['hide_label'] ) ) : '';
		$hide_label = $hide_label === 'true' ? 1 : 0;
		if ( empty( $term_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid group ID.', 'suredash' ) ] );
		}

		$space_group = $this->update_portal_group( $term_id, $term_name, $hide_label );
		$space_group = absint( $space_group );

		if ( $space_group ) {
			wp_send_json_success(
				[
					'space_group_id' => $space_group,
					'message'        => $this->get_rest_event_error( 'success' ),
				]
			);
		}

		wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'default' ) ] );
	}

	/**
	 * Delete a space group.
	 *
	 * @param \WP_REST_Request $request  Request object.
	 *
	 * @since 0.0.2
	 * @return void
	 */
	public function delete_space_group( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$term_id = ! empty( $_POST['term_id'] ) ? absint( $_POST['term_id'] ) : '';

		if ( empty( $term_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid group ID.', 'suredash' ) ] );
		}

		$deleted = wp_delete_term( $term_id, SUREDASHBOARD_TAXONOMY );

		if ( $deleted ) {
			wp_send_json_success( [ 'message' => __( 'Successfully deleted.', 'suredash' ) ] );
		}

		wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'default' ) ] );
	}

	/**
	 * Get the list of posts for selection.
	 *
	 * @param \WP_REST_Request $request  Request object.
	 *
	 * @since 0.0.2
	 * @return void
	 */
	public function get_posts_list( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$skip_type_check = false;
		$search_string   = ! empty( $_POST['q'] ) ? sanitize_text_field( wp_unslash( $_POST['q'] ) ) : '';
		$post_type       = ! empty( $_POST['post_type'] ) ? sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) : '';
		$per_page        = ! empty( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : null;
		$category_id     = ! empty( $_POST['category_id'] ) ? absint( $_POST['category_id'] ) : 0;
		$taxonomy        = ! empty( $_POST['taxonomy'] ) ? sanitize_text_field( wp_unslash( $_POST['taxonomy'] ) ) : '';
		$order_by        = ! empty( $_POST['orderBy'] ) && sanitize_text_field( $_POST['orderBy'] ) === 'latest' ? true : false;
		$data            = [];
		$result          = [];

		if ( ! empty( $post_type ) ) {
			$post_types = [ $post_type => $post_type ];
		} else {
			$skip_type_check = true;

			$args = [
				'public'   => true,
				'_builtin' => false,
			];

			$output     = 'names'; // names or objects, note names is the default.
			$operator   = 'and'; // also supports 'or' if needed.
			$post_types = get_post_types( $args, $output, $operator );

			$post_types['Posts'] = 'post';
			$post_types['Pages'] = 'page';

			// Remove the portal post type from the list.
			unset( $post_types[ SUREDASHBOARD_POST_TYPE ] );

			// Remove the WordPress default wp_block post type to avoid PHP DB fetching errors.
			unset( $post_types['wp_block'] );
		}

		foreach ( $post_types as $key => $post_type ) {
			// Skip some post types.
			$skip_post_types = apply_filters(
				'suredash_skip_post_types',
				[
					'attachment',
					'revision',
					'nav_menu_item',
				]
			);
			if ( $skip_type_check && in_array( $post_type, $skip_post_types, true ) ) {
				continue;
			}

			$data = [];

			// Use Helper's static 'search_only_titles' callback to search only in post titles.
			add_filter( 'posts_search', [ Helper::class, 'search_only_titles' ], 10, 2 );

			$posts = Controller::get_query_post_data(
				'Backend_Feeds',
				[
					's'              => $search_string,
					'post_type'      => $post_type,
					'posts_per_page' => $per_page,
					'is_tax_query'   => $category_id ? true : false,
					'taxonomy'       => $taxonomy,
					'category'       => $category_id,
				]
			);

			if ( is_array( $posts ) && ! empty( $posts ) ) {

				if ( $order_by ) {
					usort(
						$posts,
						static function( $a, $b ) {
							return strtotime( $b['post_date'] ) - strtotime( $a['post_date'] );
						}
					);
				}

				foreach ( $posts as $post ) {
					$title = $post['post_title'];

					// Check if the post has a parent and append its title.
					if ( isset( $post['post_parent'] ) && ! empty( $post['post_parent'] ) ) {
						$parent_title = get_the_title( $post['post_parent'] );
						$title       .= " ({$parent_title})";
					}

					$id = $post['ID'];

					$data[] = [
						'label' => $title,
						'value' => $id,
					];
				}
			}

			if ( is_array( $data ) && ! empty( $data ) ) {
				$result[] = [
					'label'   => $key,
					'options' => $data,
				];
			}
		}

		wp_reset_postdata();

		// return the result in json.
		wp_send_json_success( $result );
	}

	/**
	 * Get the list of community posts for selection.
	 *
	 * @param \WP_REST_Request $request  Request object.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function get_community_posts_list( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$category = ! empty( $_POST['category_id'] ) ? absint( $_POST['category_id'] ) : 0;

		$posts = Helper::get_community_posts(
			[
				'category' => $category,
			]
		);

		wp_send_json_success( $posts );
	}

	/**
	 * AJAX Handler to update groups position.
	 *
	 * @param \WP_REST_Request $request  Request object.
	 *
	 * @since 0.0.2
	 */
	public function update_group_term( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$object_id    = absint( ! empty( $_POST['object_id'] ) ? $_POST['object_id'] : 0 );
		$term_id      = absint( ! empty( $_POST['list_term_id'] ) ? $_POST['list_term_id'] : 0 );
		$prev_term_id = absint( ! empty( $_POST['prev_term_id'] ) ? $_POST['prev_term_id'] : 0 );

		if ( ! $term_id || ! $object_id ) {
			wp_send_json_error( __( 'Invalid object or term ID.', 'suredash' ) );
		}

		global $wpdb;

		if ( $prev_term_id ) {
			wp_remove_object_terms( $object_id, $prev_term_id, 'portal_group' );
		}

		$terms_added = wp_set_object_terms( $object_id, $term_id, 'portal_group' );

		if ( ! is_wp_error( $terms_added ) ) {
			wp_send_json_success( __( 'Successfully updated.', 'suredash' ) );
		}

		wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'default' ) ] );
	}

	/**
	 * Get the post meta data.
	 *
	 * @param \WP_REST_Request $request  Request object.
	 *
	 * @since 0.0.2
	 * @return void
	 */
	public function get_post_meta( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : '';

		if ( empty( $post_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid post ID.', 'suredash' ) ] );
		}

		$post_meta = PostMeta::get_all_post_meta_values( $post_id );
		$meta_set  = [
			'post_id'          => $post_id,
			'permalink'        => get_permalink( $post_id ),
			'post_status'      => get_post_status( $post_id ),
			'edit_post_link'   => get_edit_post_link( $post_id, '' ),
			'delete_post_link' => get_delete_post_link( $post_id ),
			'is_restricted'    => suredash_get_post_backend_restriction( $post_id ),
		];

		$post_meta = array_merge( $post_meta, $meta_set );

		$meta_set = [
			'post_id'          => $post_id,
			'permalink'        => get_permalink( $post_id, ),
			'post_status'      => get_post_status( $post_id, ),
			'edit_post_link'   => get_edit_post_link( $post_id, '' ),
			'delete_post_link' => get_delete_post_link( $post_id, ),
			'is_restricted'    => suredash_get_post_backend_restriction( $post_id ),
		];

		if ( ! empty( $post_meta['single_post_id'] ) ) {
			$post_meta['wp_post'] = [
				'value' => $post_meta['single_post_id'],
				'label' => get_the_title( $post_meta['single_post_id'] ),
			];
		}

		if ( $post_meta['integration'] === 'single_post' ) {
			$post_meta['comments'] = get_post_field( 'comment_status', $post_meta['post_id'] ) === 'open' ? true : false;
		}

		if ( suredash_is_pro_active() && is_array( $post_meta['pp_course_section_loop'] ) && ! empty( $post_meta['pp_course_section_loop'] ) ) {
			foreach ( $post_meta['pp_course_section_loop'] as $key => $section ) {
				if ( ! empty( $section['section_medias'] ) ) {
					foreach ( $section['section_medias'] as $key2 => $media ) {
						$post_meta['pp_course_section_loop'][ $key ]['section_medias'][ $key2 ]['comment_status'] = sd_get_post_field( $media['value'], 'comment_status' );
					}
				}
			}
		}

		$post_meta = array_merge( $post_meta, $meta_set );

		wp_send_json_success( $post_meta );
	}

	/**
	 * Get the post content.
	 *
	 * @param \WP_REST_Request $request  Request object.
	 *
	 * @since 0.0.2
	 * @return void
	 */
	public function get_post_content( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : '';

		if ( empty( $post_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid post ID.', 'suredash' ) ] );
		}

		$post_data = Helper::get_post_content( $post_id, 'full_content' );
		wp_send_json_success( $post_data );
	}

	/**
	 * Get the group meta data.
	 *
	 * @param \WP_REST_Request $request  Request object.
	 *
	 * @since 0.0.2
	 * @return void
	 */
	public function get_group_meta( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$term_id = isset( $_POST['term_id'] ) ? absint( $_POST['term_id'] ) : '';

		if ( empty( $term_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid post ID.', 'suredash' ) ] );
		}

		$term_meta = TermMeta::get_all_group_meta_values( $term_id );

		wp_send_json_success( $term_meta );
	}

	/**
	 * Get the list of internal categories.
	 *
	 * @param \WP_REST_Request $request  Request object.
	 *
	 * @since 0.0.2
	 * @return void
	 */
	public function get_internal_categories_list( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$search_string = ! empty( $_POST['q'] ) ? sanitize_text_field( wp_unslash( $_POST['q'] ) ) : '';
		$post_type     = ! empty( $_POST['post_type'] ) ? sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) : '';
		$data          = [];
		$result        = [];
		$taxonomy      = SUREDASHBOARD_FEED_TAXONOMY;

		$terms = get_terms(
			[
				'taxonomy'   => $taxonomy,
				'orderby'    => 'count',
				'hide_empty' => false,
				'name__like' => $search_string,
			]
		);

		if ( ! is_array( $terms ) || empty( $terms ) ) {
			wp_send_json_error( [ 'message' => __( 'No store categories found.', 'suredash' ) ] );
		}

		$label = $post_type === 'content' ? __( 'Content Groups', 'suredash' ) : __( 'Topic Forums', 'suredash' );

		foreach ( $terms as $term ) {
			$data[] = [
				'label' => $term->name,
				'value' => $term->term_id,
			];
		}

		if ( is_array( $data ) ) {
			$result[] = [
				'label'   => $label,
				'options' => $data,
			];
		}

		wp_reset_postdata();

		// return the result in json.
		wp_send_json_success( $result );
	}

	/**
	 * Get the list of particular internal category's posts.
	 *
	 * @param \WP_REST_Request $request  Request object.
	 *
	 * @since 0.0.2
	 * @return void
	 */
	public function get_internal_category_posts( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$category      = ! empty( $_POST['category'] ) ? absint( $_POST['category'] ) : '';
		$search_string = ! empty( $_POST['q'] ) ? sanitize_text_field( wp_unslash( $_POST['q'] ) ) : '';
		$post_type     = ! empty( $_POST['post_type'] ) ? sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) : '';
		$data          = [];
		$result        = [];

		$taxonomy  = SUREDASHBOARD_FEED_TAXONOMY;
		$post_type = SUREDASHBOARD_FEED_POST_TYPE;

		if ( empty( $category ) ) {
			wp_send_json_error( [ 'message' => __( 'Category Invalid.', 'suredash' ) ] );
		}

		$term   = get_term( $category, $taxonomy );
		$prefix = $post_type === 'content' ? __( 'Content Group -', 'suredash' ) : __( 'Topic Forum -', 'suredash' ); // @phpstan-ignore-line
		$label  = $prefix . ' ' . is_object( $term ) ? $term->name : ''; // @phpstan-ignore-line

		// Use Helper's static 'search_only_titles' callback to search only in post titles.
		add_filter( 'posts_search', [ Helper::class, 'search_only_titles' ], 10, 2 );

		$posts = Controller::get_query_post_data(
			'Backend_Feeds',
			[
				's'              => $search_string,
				'post_type'      => $post_type,
				'posts_per_page' => null,
				'is_tax_query'   => true,
				'taxonomy'       => $taxonomy,
				'category'       => $category,
			]
		);

		if ( is_array( $posts ) && ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$title = $post['post_title'];

				// Check if the post has a parent and append its title.
				if ( isset( $post['post_parent'] ) && ! empty( $post['post_parent'] ) ) {
					$parent_title = get_the_title( $post['post_parent'] );
					$title       .= " ({$parent_title})";
				}

				$id = $post['ID'];

				$data[] = [
					'label' => $title,
					'value' => $id,
				];
			}
		}

		if ( is_array( $data ) && ! empty( $data ) ) {
			$result[] = [
				'label'   => $label,
				'options' => $data,
			];
		}

		wp_reset_postdata();

		// return the result in json.
		wp_send_json_success( $result );
	}

	/**
	 * Save the admin settings.
	 *
	 * @param \WP_REST_Request $request  Request object.
	 *
	 * @since 0.0.2
	 * @return void
	 */
	public function save_settings( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$settings = ! empty( $_POST['settings'] ) ? Sanitizer::sanitize_settings_data( json_decode( wp_unslash( $_POST['settings'] ), true ) ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Data is sanitized in the Sanitizer::sanitize_settings_data() method.

		$settings = $this->update_bsf_analytics_settings( $settings );

		if ( ! is_array( $settings ) && empty( $settings ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid settings data.', 'suredash' ) ] );
		}

		if ( is_array( $settings ) ) {
			Settings::update_suredash_settings( $settings );
		}

		wp_send_json_success( [ 'message' => __( 'Settings saved successfully.', 'suredash' ) ] );
	}

	/**
	 * Content Action.
	 *
	 * @param \WP_REST_Request $request  Request object.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function content_action( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$action    = ! empty( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
		$post_id   = ! empty( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$post_type = ! empty( $_POST['post_type'] ) ? sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) : '';

		if ( $post_type !== SUREDASHBOARD_FEED_POST_TYPE ) {
			wp_send_json_error( [ 'message' => __( 'Invalid post type.', 'suredash' ) ] );
		}

		if ( empty( $action ) || empty( $post_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid action or post ID.', 'suredash' ) ] );
		}

		$post = sd_get_post( $post_id );

		if ( empty( $post ) || ! is_object( $post ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid post ID.', 'suredash' ) ] );
		}

		$result          = $this->handle_content_action( $action, $post_id, $post, $post_type );
		$updated         = $result['updated'] ?? false;
		$success_message = $result['success_message'] ?? '';
		$error_message   = $result['error_message'] ?? __( 'Failed to update.', 'suredash' );

		if ( $updated ) {

			$response = [
				'message' => $success_message,
			];

			if ( $action === 'duplicate' ) {
				$group = get_the_terms( $updated, SUREDASHBOARD_FEED_TAXONOMY );

				if ( is_array( $group ) ) {
					$group = $group[0];
				}

				$response['post'] = [
					'id'       => $updated,
					'name'     => html_entity_decode( get_the_title( $updated ) ),
					'author'   => [
						'id'   => get_post_field( 'post_author', $updated ),
						'name' => get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $updated ) ),
					],
					'view_url' => get_permalink( $updated ),
					'edit_url' => get_edit_post_link( $updated, '' ),
					'group'    => [
						'id'   => $group->term_id ?? 0,
						'name' => $group->name ?? '',
					],
					'status'   => get_post_status( $updated ),
				];
			}

			wp_send_json_success( $response );
		}

		wp_send_json_error( [ 'message' => $error_message ] );
	}

	/**
	 * Handle the content action.
	 *
	 * @param string $action   Action to perform.
	 * @param int    $post_id  Post ID.
	 * @param object $post     Post object.
	 * @param string $post_type Post type.
	 *
	 * @since 1.0.0
	 * @return array<string, mixed>
	 */
	public function handle_content_action( $action, $post_id, $post, $post_type ) {

		$success_message = '';
		$error_message   = '';

		switch ( $action ) {
			case 'publish':
				$success_message = __( 'Successfully published.', 'suredash' );
				$error_message   = __( 'Failed to publish.', 'suredash' );
				$updated         = \wp_update_post(
					[
						'ID'          => $post_id,
						'post_status' => 'publish',
					]
				);
				break;
			case 'draft':
				$success_message = __( 'Successfully drafted.', 'suredash' );
				$error_message   = __( 'Failed to draft.', 'suredash' );
				$updated         = \wp_update_post(
					[
						'ID'          => $post_id,
						'post_status' => 'draft',
					]
				);
				break;
			case 'trash':
				$success_message = __( 'Successfully trashed.', 'suredash' );
				$error_message   = __( 'Failed to trash.', 'suredash' );
				$updated         = \wp_trash_post( $post_id );
				break;
			case 'restore':
				$success_message = __( 'Successfully restored.', 'suredash' );
				$error_message   = __( 'Failed to restore.', 'suredash' );
				$updated         = \wp_untrash_post( $post_id );    // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
				break;
			case 'delete':
				$success_message = __( 'Successfully deleted.', 'suredash' );
				$error_message   = __( 'Failed to delete.', 'suredash' );
				$updated         = \wp_delete_post( $post_id, true );
				break;
			case 'duplicate':
				$success_message = __( 'Successfully duplicated.', 'suredash' );
				$error_message   = __( 'Failed to duplicate.', 'suredash' );
				$updated         = \wp_insert_post(
					[
						'post_title'   => isset( $post->post_title ) ? $post->post_title . ' - Copy' : '',
						'post_type'    => $post_type,
						'post_status'  => 'draft',
						'post_author'  => get_current_user_id(),
						'post_content' => $post->post_content ?? '',
					]
				);

				$taxonomies = get_object_taxonomies( $post->post_type ?? '' );
				foreach ( $taxonomies as $taxonomy ) {
					$terms = wp_get_object_terms( $post_id, $taxonomy, [ 'fields' => 'slugs' ] );
					if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
						wp_set_object_terms( $updated, $terms, $taxonomy );
					}
				}
				break;
			default:
				$updated = false;

		}

		return [
			'updated'         => $updated,
			'success_message' => $success_message,
			'error_message'   => $error_message,
		];
	}

	/**
	 * Get the list of internal categories.
	 *
	 * @param object $request Request object.
	 * @since 0.0.6
	 * @return void
	 */
	public function get_navigation_markup( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' ); // @phpstan-ignore-line
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$navigation_data = '';
		if ( method_exists( Navigation::get_instance(), 'render_navigation' ) ) {
			$navigation_data = Navigation::get_instance()->render_navigation(
				[
					'show_only_navigation' => true,
				]
			);
		}

		wp_send_json_success( $navigation_data );
	}

	/**
	 * Get the portal header.
	 *
	 * @param object $request Request object.
	 *
	 * @since 0.0.6
	 * @return void
	 */
	public function get_header_markup( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' ); // @phpstan-ignore-line
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$menu_data = [];
		if ( method_exists( Content_Header::get_instance(), 'render_content_header' ) ) {
			$menu_data = Content_Header::get_instance()->render_content_header( [] );
		}

		wp_send_json_success( $menu_data );
	}

	/**
	 * Update the BSF Analytics settings.
	 *
	 * @param array<string, mixed> $settings Settings data.
	 *
	 * @since 0.0.6
	 * @return array<string, mixed>
	 */
	public function update_bsf_analytics_settings( $settings ) {
		if ( ! is_array( $settings ) || empty( $settings ) ) {
			return $settings;
		}

		$usage_tracking = $settings['usage_tracking'] ? 'yes' : 'no';
		update_option( 'suredash_analytics_optin', $usage_tracking );

		return $settings;
	}

	/**
	 * Content Bulk Action.
	 *
	 * @param \WP_REST_Request $request  Request object.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function content_bulk_action( $request ): void {

		$nonce = (string) $request->get_header( 'X-WP-Nonce' );

		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$action   = ! empty( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
		$post_ids = ! empty( $_POST['post_ids'] ) ? json_decode( $_POST['post_ids'], true ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( empty( $action ) || empty( $post_ids ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid action or post IDs.', 'suredash' ) ] );
		}

		foreach ( $post_ids as $post_id ) {
			$post = sd_post_exists( $post_id );

			if ( ! $post ) {
				continue;
			}

			switch ( $action ) {
				case 'publish':
					wp_update_post(
						[
							'ID'          => $post_id,
							'post_status' => 'publish',
						]
					);
					break;
				case 'draft':
					wp_update_post(
						[
							'ID'          => $post_id,
							'post_status' => 'draft',
						]
					);
					break;
				case 'trash':
					wp_trash_post( $post_id );
					break;
				case 'restore':
					wp_untrash_post( $post_id ); // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
					break;
				case 'delete':
					wp_delete_post( $post_id, true );
					break;
				default:
					break;
			}
		}

		wp_send_json_success( [ 'message' => __( 'Bulk action completed successfully.', 'suredash' ) ] );
	}

	/**
	 * Hide welcome card.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function hide_welcome_card( $request ): void {

		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		update_option( 'suredash_hide_welcome_card', 'yes' );

		wp_send_json_success( [ 'message' => __( 'Successfully updated.', 'suredash' ) ] );
	}

	/**
	 * Get dashboard data.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function get_dashboard_data( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$dashboard_data                     = [];
		$dashboard_data['dashboard-data']   = $this->get_member_chart_data();
		$dashboard_data['top_contributors'] = $this->get_top_contributors();

		wp_send_json_success( $dashboard_data );
	}

	/**
	 * Get member stats.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function get_member_stats( $request ): void {

		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$start_date = $request->get_param( 'start_date' );
		$end_date   = $request->get_param( 'end_date' );

		$member_stats = $this->get_member_chart_data( $start_date, $end_date );

		wp_send_json_success( $member_stats );
	}

	/**
	 * Get chart data.
	 *
	 * @param string|null $start_date Start date.
	 * @param string|null $end_date   End date.
	 *
	 * @since 1.0.0
	 * @return array<string, mixed>
	 */
	public function get_member_chart_data( $start_date = null, $end_date = null ) {
		global $wpdb;

		$current_date = new \DateTime();

		// Handle default date range: last 30 days.
		if ( ! $start_date && ! $end_date ) {
			$end_date   = clone $current_date;
			$start_date = clone $current_date;
			$start_date->modify( '-30 days' );
		} else {
			$start_date = $start_date ? new \DateTime( $start_date ) : ( clone $current_date )->modify( '-30 days' );
			$end_date   = $end_date ? new \DateTime( $end_date ) : clone $current_date;
		}

		// Format datetime range.
		$start_date_formatted = $start_date->format( 'Y-m-d 00:00:00' );
		$end_date_formatted   = $end_date->format( 'Y-m-d 23:59:59' );

		// Get daily new member counts.
		// phpcs:disable WordPressVIPMinimum.Variables.RestrictedVariables.user_meta__wpdb__users
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT
					DATE(user_registered) AS registered_date,
					COUNT(ID) AS new_members
				FROM {$wpdb->users}
				WHERE user_registered BETWEEN %s AND %s
				GROUP BY DATE(user_registered)
				ORDER BY DATE(user_registered) ASC
				",
				$start_date_formatted,
				$end_date_formatted
			),
			ARRAY_A
		);

		// Total members before start date.
		$total_members = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(ID) FROM {$wpdb->users} WHERE user_registered < %s", // phpcs:ignore
				$start_date_formatted
			)
		);
		// phpcs:enable WordPressVIPMinimum.Variables.RestrictedVariables.user_meta__wpdb__users

		$chart_data = [];
		$daily_map  = [];

		foreach ( $results as $row ) {
			$daily_map[ $row['registered_date'] ] = (int) $row['new_members'];
		}

		$period = new \DatePeriod(
			$start_date,
			new \DateInterval( 'P1D' ),
			$end_date->modify( '+1 day' )
		);

		foreach ( $period as $date ) {
			$date_str       = $date->format( 'Y-m-d' );
			$new_members    = $daily_map[ $date_str ] ?? 0;
			$total_members += $new_members;

			if ( $total_members === 0 && $new_members === 0 ) {
				continue;
			}

			$chart_data[] = [
				'date'          => $date_str,
				'new_members'   => $new_members,
				'total_members' => $total_members,
			];
		}

		return [
			'chart_data'        => $chart_data,
			'total_members'     => $total_members,
			'total_new_members' => array_sum( array_column( $chart_data, 'new_members' ) ),
		];
	}

	/**
	 * Get top contributors.
	 *
	 * @since 1.0.0
	 * @return array<int, array<string, bool|int|string>>
	 */
	public function get_top_contributors() {

		// Get all admin user IDs.
		$admin_ids = get_users(
			[
				'role'   => 'Administrator',
				'fields' => 'ID',
			]
		);

		$post_types = get_post_types(
			[
				'public' => true,
			],
			'names'
		);

		$contributions = [];
		$post_data     = [];
		// Get latest 5 posts from any post type excluding admin authors.
		$latest_posts = sd_get_posts(
			[
				'post_type'      => array_keys( $post_types ),
				'numberposts'    => 5,
				'post_status'    => 'publish',
				'orderby'        => 'post_date',
				'order'          => 'DESC',
				'author__not_in' => $admin_ids,
				'fields'         => 'all',
				'select'         => '*',
			]
		);

		foreach ( $latest_posts as $post ) {
			$post = (array) $post;
			$user = get_userdata( absint( $post['post_author'] ?? 0 ) );
			if ( ! $user ) {
				continue;
			}

			$post_data[] = [
				'user_id'          => $user->ID,
				'name'             => $user->display_name,
				'email'            => $user->user_email,
				'type'             => 'post',
				'title_or_content' => $post['post_title'] ?? '',
				'date'             => $post['post_date'] ?? '',
				'post_permalink'   => isset( $post['ID'] ) ? get_permalink( absint( $post['ID'] ) ) : '',
			];

			if ( count( array_filter( $contributions, static fn( $c ) => $c['type'] === 'post' ) ) >= 4 ) {
				break;
			}
		}

		$post_data = array_slice( $post_data, 0, 4 );

		// Get latest 6 comments excluding admin users.
		$comment_data    = [];
		$latest_comments = get_comments(
			[
				'number'    => 15,
				'status'    => 'approve',
				'orderby'   => 'comment_date_gmt',
				'order'     => 'DESC',
				'post_type' => array_keys( $post_types ),
			]
		);

		if ( is_array( $latest_comments ) && ! empty( $latest_comments ) ) {
			foreach ( $latest_comments as $comment ) {

				if ( ! is_object( $comment ) ) {
					continue;
				}

				$user = get_userdata( absint( $comment->user_id ) );
				if ( ! $user ) {
					continue;
				}

				if ( in_array( $comment->user_id, $admin_ids, true ) ) {
					continue;
				}

				$post = sd_get_post( absint( $comment->comment_post_ID ) );

				$comment_data[] = [
					'user_id'          => $user->ID,
					'name'             => $user->display_name,
					'email'            => $user->user_email,
					'type'             => 'comment',
					'comment'          => wp_trim_words( $comment->comment_content, 10 ),
					'title_or_content' => isset( $post->ID ) ? get_the_title( absint( $post->ID ) ) : '',
					'date'             => $comment->comment_date,
					'comment_link'     => get_comment_link( $comment ),
				];

				if ( count( array_filter( $comment_data, static fn( $c ) => $c['type'] === 'comment' ) ) >= 4 ) {
					break;
				}
			}
		}

		$comment_data = array_slice( $comment_data, 0, 4 );

		$contributions = array_merge( $post_data, $comment_data );

		// Sort all by most recent date.
		usort(
			$contributions,
			static function( $a, $b ) {
				return strtotime( $b['date'] ) - strtotime( $a['date'] );
			}
		);

		return $contributions;
	}

	/**
	 * Update comment status for the post.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function update_comment_status( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$post_id        = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : '';
		$comment_status = isset( $_POST['comment_status'] ) ? sanitize_text_field( $_POST['comment_status'] ) : 'closed';

		if ( empty( $post_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid post ID.', 'suredash' ) ] );
		}

		if ( $comment_status === 'closed' ) {
			wp_update_post(
				[
					'ID'             => $post_id,
					'comment_status' => 'closed',
				]
			);
			wp_send_json_success( [ 'message' => __( 'Comments status updated to closed.', 'suredash' ) ] );
		} else {
			wp_update_post(
				[
					'ID'             => $post_id,
					'comment_status' => 'open',
				]
			);
			wp_send_json_success( [ 'message' => __( 'Comments status updated to open.', 'suredash' ) ] );
		}
	}
}
