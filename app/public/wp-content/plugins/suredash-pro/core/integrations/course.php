<?php
/**
 * Course Integration.
 *
 * @package SureDashboardPro
 */

namespace SureDashboardPro\Core\Integrations;

use SureDashboard\Inc\Utils\Helper;
use SureDashboard\Inc\Utils\Labels as FreeLabels;
use SureDashboard\Inc\Utils\PostMeta;
use SureDashboard\Inc\Utils\WpPost;
use SureDashboardPro\Inc\Traits\Get_Instance;
use SureDashboardPro\Inc\Utils\Labels;

/**
 * Course Integration.
 *
 * @since 1.0.0
 */
class Course extends Base {
	use Get_Instance;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name        = 'Course';
		$this->slug        = 'course';
		$this->description = __( 'Course Integration', 'suredash-pro' );
		$this->is_active   = true;

		parent::__construct( $this->name, $this->slug, $this->description, $this->is_active );
	}

	/**
	 * Get item single content.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public function get_integration_content( $post_id ) {
		ob_start();

		$course_content  = get_the_content( null, false, $post_id );
		$course_sections = PostMeta::get_post_meta_value( $post_id, 'pp_course_section_loop' );
		$course_name     = get_the_title( $post_id );

		// Calculate the course progress percentage.
		$lessons_completed_ids = get_user_meta( get_current_user_id(), 'portal_course_' . $post_id . '_completed_lessons', true );
		$lessons_completed_ids = ! empty( $lessons_completed_ids ) ? $lessons_completed_ids : [];
		// @phpstan-ignore-next-line
		$lessons_completed = count( $lessons_completed_ids );
		// Count all lessons from course_sections.
		$all_lessons_count = 0;
		$all_lessons_id    = [];
		foreach ( $course_sections as $section ) {
			$all_lessons_count += count( $section['section_medias'] );
			foreach ( $section['section_medias'] as $lesson ) {
				$all_lessons_id[] = $lesson['value'];
			}
		}
		$resume_lesson_id = 0;
		if ( isset( $lessons_completed ) && $all_lessons_count !== $lessons_completed ) {
			if ( isset( $all_lessons_id[ $lessons_completed ] ) ) {
				$resume_lesson_id = $all_lessons_id[ $lessons_completed ];
			}
		}
		$start_lesson_id            = $all_lessons_id[0] ?? 0; // Get the first lesson id.
		$course_progress_percentage = $all_lessons_count ? round( $lessons_completed / $all_lessons_count * 100 ) : 0;

		do_action( 'suredashboard_before_course_content_load', $post_id );

		$media_list_atts = apply_filters(
			'suredashboard_pp_media_list_shortcodes_atts',
			[
				'listTextSingular'   => Labels::get_label( 'course_singular_text' ),
				'listTextPlural'     => Labels::get_label( 'course_plural_text' ),
				'description'        => $course_content,
				'transitionDuration' => 10,
			]
		);
		?>
		<div class="portal-pp-course-playlist-wrap sd-flex sd-flex-col sd-gap-20">
			<div class="course-title-progress sd-flex sd-justify-between">
				<h2 class="portal-pp-course-playlist-title sd-no-space"><?php echo esc_html( $course_name ); ?></h2>
				<?php
				if ( is_callable( 'SureDashboard\Inc\Utils\Helper::show_badge' ) ) {
					$course_completed = $all_lessons_count === $lessons_completed;
					Helper::show_badge(
						$course_completed ? 'success' : 'primary',
						$course_completed ? 'CircleCheckBig' : 'info',
						$course_completed ? __( 'Completed', 'suredash-pro' ) : $course_progress_percentage . '% ' . __( 'Completed', 'suredash-pro' ),
						'sm',
						'course-playlist-badge sd-hide-mobile',
						[
							'resume-lesson'   => $resume_lesson_id,
							'start-lesson'    => $start_lesson_id,
							'course-progress' => $course_progress_percentage,
						]
					);
				}
				?>
			</div>
			<?php if ( ! empty( $media_list_atts['description'] ) ) { ?>
				<div class="sd-pb-20 sd-border-b">
					<?php echo do_shortcode( $media_list_atts['description'] ); ?>
				</div>
			<?php } ?>
			<div class="portal-course--lesson-items sd-flex sd-flex-col sd-gap-24 sd-px-12">
				<?php
				if ( ! empty( $course_sections ) ) {
					foreach ( $course_sections as $index => $loop_data ) {
						$section_title  = ! empty( $loop_data['section_title'] ) ? $loop_data['section_title'] : __( 'Section', 'suredash-pro' ) . ' ' . ( $index + 1 );
						$section_medias = ! empty( $loop_data['section_medias'] ) ? $loop_data['section_medias'] : [];
						$toggled_class  = $index ? 'pp-course-section-toggled' : '';

						if ( empty( $section_medias ) ) {
							continue;
						}

						?>
						<div class="pp-course--section sd-flex-col <?php echo esc_attr( $toggled_class ); ?>">
							<div class="pp-course--section-header sd-flex sd-pointer sd-items-center sd-justify-between">
								<h4 class="pp-course--section-title sd-no-space">
									<?php echo esc_attr( $index + 1 ) . '.'; ?>
									<?php echo esc_html( $section_title ); ?>
								</h4>
								<span class="pp-course--section-trigger" tabindex="0">
									<?php Helper::get_library_icon( 'ChevronDown' ); ?>
								</span>
							</div>
							<div class="pp-course--section-content sd-flex-col sd-opacity-100 sd-pt-20 sd-gap-20 sd-visible sd-transition">
								<?php
								foreach ( $section_medias as $media_data ) {
									$lesson_id       = absint( ! empty( $media_data['value'] ) ? $media_data['value'] : 0 );
									$lesson_label    = ! empty( $media_data['label'] ) ? $media_data['label'] : __( 'Media', 'suredash-pro' ) . ' ' . ( $index + 1 );
									$lesson_duration = get_post_meta( $lesson_id, 'lesson_duration', true );

									$media_thumbnail = get_the_post_thumbnail( $lesson_id, 'large' );
									$poster_image    = ! empty( $media_thumbnail ) ? $media_thumbnail : '<img src="' . Helper::get_placeholder_image() . '" alt="' . $lesson_label . '" />';

									$excerpt    = get_the_excerpt( $lesson_id );
									$lesson_url = get_permalink( $lesson_id );

									if ( $lesson_id ) {
										?>
										<a href="<?php echo esc_url( $lesson_url ); ?>" class="portal-course--lesson-item sd-relative sd-flex sd-items-center sd-gap-32 sd-border sd-radius-6">
											<?php
											if ( is_callable( 'SureDashboard\Inc\Utils\Helper::show_badge' ) ) {
												$is_completed = in_array( $lesson_id, $lessons_completed_ids );
												Helper::show_badge(
													$is_completed ? 'success' : 'primary',
													$is_completed ? 'CircleCheckBig' : 'CircleEllipsis',
													$is_completed ? 'Completed' : 'Upcoming', // Empty text since only icon is needed.
													'sm',
													'course-playlist-item-badge sd-absolute sd-p-4',
													[
														'type' => $is_completed ? 'success' : 'primary',
														'tooltip-title' => $is_completed ? 'Completed!' : 'Upcoming..',
														'tooltip-position' => 'right',
													]
												);
											}
											?>
											<div class="portal-course-featured sd-relative sd-shrink-0 sd-w-full sd-max-w-custom" style="--sd-max-w-custom: 35%;">
												<?php echo wp_kses_post( $poster_image ); ?>
												<?php if ( $lesson_duration ) { ?>
													<span class="sd-absolute sd-font-12 sd-left-8 sd-bottom-8 sd-z-4 sd-px-8 sd-py-2 sd-border-0 sd-radius-9999 sd-color-white sd-bg-custom" style="--sd-bg-custom: #1f2937cc; line-height: 1.5;"><?php echo esc_html( $lesson_duration ); ?></span>
												<?php } ?>
												<div class="portal-course-thumbnail-icon sd-absolute sd-font-12 sd-left-custom sd-bottom-custom sd-translate-xy-half sd-px-8 sd-py-2 sd-border-0"
													style="--sd-left-custom: 50%; --sd-bottom-custom: 50%; line-height: 1.5;">
													<?php Helper::get_library_icon( 'CirclePlay', true, 'lg' ); ?>
												</div>
											</div>
											<div class="portal-course--lesson-item-content sd-flex-col sd-gap-12 sd-py-8">
												<h5 class="portal-pp-playlist-item-title sd-no-space"><?php echo esc_html( $lesson_label ); ?> </h5>
												<?php if ( $excerpt ) { ?>
													<div class="portal-pp-playlist-item-content"><?php echo do_shortcode( $excerpt ); ?> </div>
												<?php } ?>
											</div>
										</a>
										<?php
									}
								}
								?>
							</div>
						</div>
						<?php
					}
				}
				?>
			</div>
		</div>
		<?php

		do_action( 'suredashboard_after_course_content_load', $post_id );

		return ob_get_clean();
	}

	/**
	 * Get Single Endpoint Header.
	 *
	 * @param array  $lesson_data   Lesson Data.
	 * @param int    $course_id     Course ID.
	 * @param int    $lesson_id     Lesson ID.
	 * @param int    $previous_lesson_id Previous Lesson ID.
	 * @param string $previous_lesson_link Previous Lesson Link.
	 * @param int    $next_lesson_id Next Lesson ID.
	 * @param string $nav_button_text Navigation Button Text.
	 * @param string $bookmarked Bookmarked.
	 * @since 0.0.6
	 * @return void
	 */
	public function get_endpoint_header( $lesson_data, $course_id, $lesson_id, $previous_lesson_id, $previous_lesson_link, $next_lesson_id, $nav_button_text, $bookmarked ): void {
		$is_course_restricted = $this->is_course_restricted( absint( $course_id ) );

		if ( $is_course_restricted ) {
			$back_text = class_exists( 'SureDashboard\Inc\Utils\Labels' ) ? FreeLabels::get_label( 'back_to_portal' ) : '';
			?>
				<div class="portal-item-title-area portal-content sd-flex sd-justify-between sd-items-center sd-gap-8 sd-w-full">
					<a href="<?php echo esc_url( home_url( '/' . SUREDASHBOARD_SLUG . '/' ) ); ?>" class="portal-sub-item-link">
						<?php Helper::get_library_icon( 'ChevronLeft', true ); ?>
						<?php echo esc_html( $back_text ); ?>
					</a>
				</div>
			<?php
			return;
		}

		$lessons_completed = sd_get_user_meta( get_current_user_id(), 'portal_course_' . $course_id . '_completed_lessons', true );
		$back_text         = Labels::get_label( 'back_to_course' );

		?>
		<div class="portal-item-title-area portal-content sd-flex sd-justify-between sd-items-center sd-gap-8 sd-w-full">
			<a href="<?php echo esc_url( (string) get_permalink( $course_id ) ); ?>" class="portal-sub-item-link">
				<?php Helper::get_library_icon( 'ChevronLeft', true ); ?>
				<?php echo esc_html( $back_text ); ?>
			</a>
			<div class="portal-lesson-content-triggers">
				<div class="portal-responsive-progress-wrapper portal-content sd-hide-desktop-lg">
					<?php
					if ( is_user_logged_in() ) {
						echo do_shortcode( (string) suredash_pro_get_course_progress( $lesson_data, $lessons_completed ) );
					} else {
						?>
							<a href="<?php echo esc_url( suredash_get_login_page_url() ); ?>" class="portal-user-menu-link portal-logout-url sd-justify-end sd-font-14" title="<?php echo esc_attr__( 'Login', 'suredash-pro' ); ?>">
								<?php echo esc_attr__( 'Login', 'suredash-pro' ); ?>
							</a>
						<?php
					}
					?>
				</div>
				<div class="lesson-triggers-outside-content sd-items-center sd-gap-12">
					<span id="portal-lesson-bookmark" class="portal-post-bookmark-trigger sd-flex sd-cursor-pointer <?php echo esc_attr( $bookmarked ); ?>" data-course_id="<?php echo esc_attr( $course_id ); ?>" data-item_id="<?php echo esc_attr( $lesson_id ); ?>" data-item_type="lesson">
						<?php Helper::get_library_icon( 'Bookmark', true ); ?>
					</span>

					<?php
					if ( $previous_lesson_id ) {
						?>
						<a class="portal-button button-secondary sd-flex sd-cursor-pointer " href="<?php echo esc_url( $previous_lesson_link ); ?>">
							<?php Helper::get_library_icon( 'ArrowLeft', true ); ?>
						</a>
						<?php
					}
					?>

					<button id="portal-lesson-complete" class="portal-button button-primary sd-flex sd-cursor-pointer" data-course_id="<?php echo esc_attr( $course_id ); ?>" data-lesson_id="<?php echo esc_attr( $lesson_id ); ?>" data-previous_lesson_id="<?php echo esc_attr( $previous_lesson_id ); ?>" data-next_lesson_id="<?php echo esc_attr( $next_lesson_id ); ?>">
						<span class="portal-lesson-complete-text"><?php echo esc_html( $nav_button_text ); ?></span>
						<?php Helper::get_library_icon( 'ArrowRight', true ); ?>
					</button>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get Single Endpoint Content.
	 *
	 * @param string $endpoint     Endpoint.
	 * @param int    $course_id     Course ID.
	 * @param int    $lesson_id     Lesson ID.
	 * @param int    $previous_lesson_id Previous Lesson ID.
	 * @param string $previous_lesson_link Previous Lesson Link.
	 * @param int    $next_lesson_id Next Lesson ID.
	 * @param string $nav_button_text Navigation Button Text.
	 * @param string $bookmarked Bookmarked.
	 * @since 0.0.6
	 * @return void
	 */
	public function get_endpoint_content( $endpoint, $course_id, $lesson_id, $previous_lesson_id, $previous_lesson_link, $next_lesson_id, $nav_button_text, $bookmarked ): void {
		$bookmark_lesson      = Labels::get_label( 'bookmark_lesson_text' );
		$course_ruleset       = $this->is_course_restricted( absint( $course_id ), false );
		$is_course_restricted = $course_ruleset['status'] ?? false;
		$restriction_content  = $course_ruleset['content'] ?? '';

		?>
		<div class="portal-content-area portal-content-type-<?php echo esc_attr( $endpoint ); ?>">
			<?php
			if ( $is_course_restricted ) {
				?>
						<div class="portal-lesson-content portal-content">
						<?php echo do_shortcode( $restriction_content ); ?>
						</div>
				<?php
				return;
			}
			?>

			<div class="lesson-triggers-inside-content sd-display-none sd-mb-20 sd-justify-between sd-items-center sd-gap-12">
				<div id="portal-lesson-bookmark" class="portal-post-bookmark-trigger portal-button button-secondary <?php echo esc_attr( $bookmarked ); ?>" data-course_id="<?php echo esc_attr( $course_id ); ?>" data-item_id="<?php echo esc_attr( $lesson_id ); ?>" data-item_type="lesson">
					<?php Helper::get_library_icon( 'Bookmark', true ); ?>
					<?php echo esc_html( $bookmark_lesson ); ?>
				</div>

				<div class="lesson-navigation-buttons sd-flex sd-gap-12">
					<?php
					if ( $previous_lesson_id ) {
						?>
							<a class="portal-button button-secondary" href="<?php echo esc_url( $previous_lesson_link ); ?>">
							<?php Helper::get_library_icon( 'ArrowLeft', true ); ?>
							</a>
							<?php
					}
					?>

					<button id="portal-lesson-complete" class="portal-button button-primary" data-course_id="<?php echo esc_attr( $course_id ); ?>" data-lesson_id="<?php echo esc_attr( $lesson_id ); ?>" data-previous_lesson_id="<?php echo esc_attr( $previous_lesson_id ); ?>" data-next_lesson_id="<?php echo esc_attr( $next_lesson_id ); ?>">
						<span class="portal-lesson-complete-text"><?php echo esc_html( $nav_button_text ); ?></span>
						<?php class_exists( 'SureDashboard\Inc\Utils\Helper' ) ? Helper::get_library_icon( 'ArrowRight', true ) : ''; ?>
					</button>
				</div>
			</div>

			<div class="portal-lesson-header portal-content">
				<h4 class="portal-item-title sd-no-space"><?php echo esc_html( get_the_title( $lesson_id ) ); ?></h4>
			</div>

			<div class="portal-lesson-content portal-content">
				<?php
					$remote_wp_post = new WpPost( $lesson_id );
					$remote_wp_post->enqueue_assets();
					remove_filter( 'the_content', 'wpautop' );
					echo do_shortcode( apply_filters( 'the_content', $remote_wp_post->render_content() ) );
					add_filter( 'the_content', 'wpautop' );
				?>
			</div>

			<?php
			if ( ! $is_course_restricted ) { // @phpstan-ignore-line
				echo do_shortcode( '[portal_single_comments item_id=' . $lesson_id . ' comments="true"]' );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Check if the course is restricted.
	 *
	 * @param int  $course_id Course ID.
	 * @param bool $get_only_status Get only status.
	 * @return bool
	 * @since 1.0.0-rc.3
	 */
	public function is_course_restricted( $course_id, $get_only_status = true ) {
		$protection_defaults = [
			'status'  => false,
			'content' => '',
		];

		if ( is_callable( 'suredash_restriction_defaults' ) ) {
			$protection_defaults = suredash_restriction_defaults();
		}

		return function_exists( 'suredash_is_post_protected' ) ? suredash_is_post_protected( $course_id, $get_only_status ) : $protection_defaults;
	}

	/**
	 * Get Single Lesson View.
	 *
	 * @param string $endpoint      Endpoint.
	 * @param array  $endpoint_data Endpoint Data.
	 * @param array  $atts          Attributes.
	 * @since 1.0.0
	 * @return void
	 */
	public function get_portal_lesson_view( $endpoint, $endpoint_data, $atts = [] ): void {
		$course_loop = ! empty( $endpoint_data['course_loop'] ) ? $endpoint_data['course_loop'] : [];
		$lesson_id   = absint( get_the_ID() );
		$course_id   = sd_get_post_meta( $lesson_id, 'belong_to_course', true );

		if ( ! $lesson_id || empty( $course_loop ) || ! $course_id ) {
			return;
		}

		$bookmarked = is_callable( 'suredash_is_item_bookmarked' ) ? suredash_is_item_bookmarked( $lesson_id ) : false;
		$bookmarked = $bookmarked ? 'bookmarked' : '';

		$lesson_data = is_callable( 'suredash_get_lesson_oriented_data' ) ? suredash_get_lesson_oriented_data( $lesson_id, $course_loop ) : [];

		if ( empty( $lesson_data ) ) {
			return;
		}

		$previous_lesson_id   = $lesson_data['previous_lesson_id'];
		$next_lesson_id       = $lesson_data['next_lesson_id'];
		$previous_lesson_link = get_permalink( $previous_lesson_id );

		$lessons_completed = get_user_meta( get_current_user_id(), 'portal_course_' . $course_id . '_completed_lessons', true );

		$mark_complete_text = Labels::get_label( 'mark_as_complete' );
		$continue_text      = Labels::get_label( 'continue' );

		// First get the completed lessons for the current user and course.
		$lessons_completed_ids = ! empty( $lessons_completed ) ? $lessons_completed : [];

		// Check if current lesson is completed.
		$is_lesson_completed = in_array( $lesson_id, $lessons_completed_ids );

		// Get button text based on completion status.
		$nav_button_text = $is_lesson_completed ? $continue_text : $mark_complete_text;

		do_action( 'suredashboard_before_single_content_load', $lesson_id );

		if ( ! $lesson_id ) {
			return;
		}

		$get_only_header  = ! empty( $atts['get_only_header'] ) && $atts['get_only_header'] === 'true' ? true : false;
		$get_only_content = ! empty( $atts['get_only_content'] ) && $atts['get_only_content'] === 'true' ? true : false;

		if ( $get_only_header ) {
			$this->get_endpoint_header( $lesson_data, $course_id, $lesson_id, $previous_lesson_id, $previous_lesson_link, $next_lesson_id, $nav_button_text, $bookmarked );
			return;
		}

		if ( $get_only_content ) {
			$this->get_endpoint_content( $endpoint, $course_id, $lesson_id, $previous_lesson_id, $previous_lesson_link, $next_lesson_id, $nav_button_text, $bookmarked );
			return;
		}

		$this->get_endpoint_header( $lesson_data, $course_id, $lesson_id, $previous_lesson_id, $previous_lesson_link, $next_lesson_id, $nav_button_text, $bookmarked );

		$this->get_endpoint_content( $endpoint, $course_id, $lesson_id, $previous_lesson_id, $previous_lesson_link, $next_lesson_id, $nav_button_text, $bookmarked );

		do_action( 'suredashboard_after_single_content_load', $lesson_id );
	}
}
