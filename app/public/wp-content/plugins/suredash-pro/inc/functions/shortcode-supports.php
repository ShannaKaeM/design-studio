<?php
/**
 * Extend the shortcode functionalities.
 *
 * @package SureDashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SureDashboard\Inc\Utils\Helper;
use SureDashboardPro\Core\Integrations\Course;

/**
 * Get the course content.
 *
 * @param int $post_id The post ID.
 * @return string
 * @since 0.0.1-alpha.3
 */
function suredash_pro_course_integration_content( $post_id ) {
	return is_callable( [ Course::get_instance(), 'get_integration_content' ] ) ? Course::get_instance()->get_integration_content( $post_id ) : '';
}

/**
 * Get the portal lesson view.
 *
 * @param string               $endpoint      The endpoint.
 * @param array<string, mixed> $endpoint_data The endpoint data.
 * @param array<string, mixed> $atts          The attributes.
 * @return string
 * @since 0.0.1-alpha.3
 */
function suredash_pro_lesson_view_content( $endpoint, $endpoint_data, $atts = [] ) {
	return is_callable( [ Course::get_instance(), 'get_portal_lesson_view' ] ) ? Course::get_instance()->get_portal_lesson_view( $endpoint, $endpoint_data, $atts ) : '';
}

/**
 * Get the lesson progress.
 *
 * @param array<string, int> $lesson_data The lesson data.
 * @param array<int>         $lessons_completed The lessons completed.
 * @return string|false
 * @since 0.0.6
 */
function suredash_pro_get_course_progress( $lesson_data, $lessons_completed ) {
	$all_lessons_count = absint( $lesson_data['all_lessons_count'] ?? 0 );
	$lessons_completed = count( ! empty( $lessons_completed ) ? $lessons_completed : [] );

	$percentage_covered = 0;
	if ( $all_lessons_count ) {
		$percentage_covered = round( $lessons_completed / $all_lessons_count * 100 );
	}

	ob_start();
	?>
		<div class="portal-course-progress-wrap" style="--course-percent: <?php echo esc_attr( strval( $percentage_covered ) ) . '%'; ?>">
			<div class="portal-course-percent-inner">
				<div class="pp-percent-content">
					<span class="portal-course-range-wrapper">
						<span class="portal-course-range-completion"></span>
					</span>
					<span class="portal-course-percentage"><?php echo esc_html( strval( $percentage_covered ) ); ?>%</span>
				</div>
			</div>
		</div>
	<?php
	return ob_get_clean();
}

/**
 * Render lesson navigation.
 *
 * @param array<mixed> $endpoint_data Array of endpoint data.
 *
 * @since 1.0.0-rc.3
 * @return void
 */
function suredash_pro_render_lesson_navigation( $endpoint_data ): void {
	$lesson_id   = get_the_ID();
	$course_loop = ! empty( $endpoint_data['course_loop'] ) ? $endpoint_data['course_loop'] : [];
	$endpoint    = ! empty( $endpoint_data['endpoint'] ) ? $endpoint_data['endpoint'] : '';

	if ( is_array( $course_loop ) && ! empty( $course_loop ) ) {
		foreach ( $course_loop as $index => $loop_data ) {
			$section_title  = is_array( $loop_data ) && ! empty( $loop_data['section_title'] ) ? $loop_data['section_title'] : __( 'Section', 'suredash-pro' ) . ' ' . ( $index + 1 );
			$section_medias = is_array( $loop_data ) && ! empty( $loop_data['section_medias'] ) ? $loop_data['section_medias'] : [];

			if ( empty( $section_medias ) ) {
				continue;
			}

			?>
				<div class="portal-aside-group">
					<div class="portal-aside-group-header">
						<a class="portal-aside-group-title-link sd-no-space" href="#" tabindex="0">
							<h5 class="portal-aside-group-title"><?php echo esc_html( $section_title ); ?></h5>
						</a>
						<span class="pfd-aside-doc-trigger" tabindex="0">
							<?php class_exists( 'SureDashboard\Inc\Utils\Helper' ) ? Helper::get_library_icon( 'ChevronDown' ) : ''; ?>
						</span>
					</div>
					<div class="portal-aside-group-body">
						<?php
						if ( is_array( $section_medias ) ) {
							?>
							<ul role="list" class="portal-aside-group-list sd-no-space">
							<?php
							$emoji = class_exists( 'SureDashboard\Inc\Utils\Helper' ) ? Helper::get_library_icon( 'CirclePlay', false ) : '';

							foreach ( $section_medias as $media_data ) {
								$item_id         = absint( ! empty( $media_data['value'] ) ? $media_data['value'] : 0 );
								$item_title      = ! empty( $media_data['label'] ) ? $media_data['label'] : '';
								$post_id         = ! empty( $endpoint_data['media'] ) ? absint( $endpoint_data['media'] ) : 0;
								$lesson_duration = sd_get_post_meta( $item_id, 'lesson_duration', true );
								$lesson_duration = ! empty( $lesson_duration ) ? $lesson_duration : '';

								if ( ! $item_id || empty( $item_title ) || ! $post_id ) {
									continue;
								}

								$endpoint_indicator = suredash_get_endpoint_indicator( $endpoint, absint( $lesson_id ), $item_id );
								$indicator_type     = $post_id === $item_id ? 'active' : $endpoint_indicator;
								$has_indicator      = ! empty( $indicator_type )
								? sprintf( '<span class="portal-indicator" data-indicator="%s"> </span>', esc_attr( strval( $indicator_type ) ) ) : '';
								$active_class       = $post_id === $item_id ? ' portal-active-sub-link active' : '';
								$link               = get_permalink( $item_id );

								echo do_shortcode(
									'<li class="tooltip-trigger" data-tooltip-title="' . esc_attr( $item_title ) . '"' .
									( ! empty( $lesson_duration ) ? ' data-tooltip-description="' . esc_attr( strval( $lesson_duration ) ) . ' ' .
										esc_html( __( ' runtime', 'suredash-pro' ) ) . '"' : '' ) .
									'> <a class="portal-aside-group-link' . esc_attr( $active_class ) .
									'" data-post_id="' . esc_attr( (string) $item_id ) . '" href="' . esc_url( (string) $link ) .
									'"> ' . $has_indicator . ' <span class="portal-aside-lesson-title sd-flex sd-justify-center sd-items-center sd-overflow-hidden">' .
									$emoji . '<span class="portal-aside-lesson-text">' . esc_html( $item_title ) . '</span> </span> </a> </li>'
								);
							}
							?>
							</ul>
							<?php
						} else {
							esc_html_e( 'No items found.', 'suredash-pro' );
						}
						?>
					</div>
				</div>
			<?php
			$index++;
		}
	} else {
		esc_html_e( 'Invalid endpoint data.', 'suredash-pro' );
	}
}
