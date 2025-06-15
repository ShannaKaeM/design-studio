<?php
/**
 * Labels.
 *
 * @package SureDashboardPro
 * @since 1.0.0
 */

namespace SureDashboardPro\Inc\Utils;

use SureDashboardPro\Inc\Traits\Get_Instance;

/**
 * Labels Compatibility
 *
 * @package SureDashboardPro
 */
class Labels {
	use Get_Instance;

	/**
	 * All labels.
	 *
	 * @var array
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
				'course_default_heading' => __( 'Course Playlist', 'suredash-pro' ),
				'course_singular_text'   => __( 'Course', 'suredash-pro' ),
				'course_plural_text'     => __( 'Courses', 'suredash-pro' ),
				'lesson_singular_text'   => __( 'Lesson', 'suredash-pro' ),
				'lesson_plural_text'     => __( 'Lessons', 'suredash-pro' ),
				'bookmark_lesson_text'   => __( 'Bookmark Lesson', 'suredash-pro' ),
				'continue'               => __( 'Continue', 'suredash-pro' ),

				'course_progress'        => __( 'Course Progress', 'suredash-pro' ),
				'back_to_course'         => Helper::get_option( 'back_to_course_text' ),
				'mark_as_complete'       => Helper::get_option( 'mark_as_complete_text' ),
			]
		);
	}

	/**
	 * Get all labels.
	 *
	 * @since 1.0.0
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
		$labels = self::get_labels();
		$label  = apply_filters( 'suredashboard_' . $label_name . '_text', $labels[ $label_name ] ?? '', $label_name );

		if ( $echo ) {
			echo esc_html( $label );
		} else {
			return esc_html( $label );
		}
	}
}
