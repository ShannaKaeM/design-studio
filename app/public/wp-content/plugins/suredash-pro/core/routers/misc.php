<?php
/**
 * Misc Router Initialize.
 *
 * @package SureDashboardPro
 */

namespace SureDashboardPro\Core\Routers;

use SureDashboardPro\Inc\Traits\Get_Instance;
use SureDashboardPro\Inc\Traits\Rest_Errors;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Misc Router.
 */
class Misc {
	use Get_Instance;
	use Rest_Errors;

	/**
	 * Handler to count lesson as complete.
	 *
	 * @param \WP_REST_Request $request The request object.
	 *
	 * @since 0.0.1
	 * @return void
	 * @phpstan-ignore-next-line
	 */
	public function mark_lesson_complete( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		// Verify the nonce.
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$course_id      = ! empty( $_POST['course_id'] ) ? absint( $_POST['course_id'] ) : 0;
		$lesson_id      = ! empty( $_POST['lesson_id'] ) ? absint( $_POST['lesson_id'] ) : 0;
		$next_lesson_id = ! empty( $_POST['next_lesson_id'] ) ? absint( $_POST['next_lesson_id'] ) : 0;

		if ( ! $course_id || ! $lesson_id ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'default' ) ] );
		}

		$current_user = get_current_user_id();

		$completed_lessons = is_callable( 'suredash_get_all_completed_lessons' ) ? suredash_get_all_completed_lessons( $course_id ) : [];

		if ( ! in_array( $lesson_id, $completed_lessons, true ) ) {
			$completed_lessons[] = $lesson_id;
		}

		update_user_meta( $current_user, 'portal_course_' . $course_id . '_completed_lessons', $completed_lessons );

		wp_send_json_success(
			[
				'message'          => __( 'Lesson marked as complete.', 'suredash-pro' ),
				'next_lesson_link' => $next_lesson_id ? get_permalink( $next_lesson_id ) : '',
				'course_link'      => get_permalink( $course_id ),
			]
		);
	}
}
