<?php
/**
 * Portals Docs EndpointNavigation Shortcode Initialize.
 *
 * @package SureDash
 */

namespace SureDashboard\Core\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use SureDashboard\Inc\Traits\Get_Instance;
use SureDashboard\Inc\Traits\Shortcode;
use SureDashboard\Inc\Utils\Labels;
use SureDashboard\Inc\Utils\PostMeta;

/**
 * Class EndpointNavigation Shortcode.
 */
class EndpointNavigation {
	use Shortcode;
	use Get_Instance;

	/**
	 * Register_shortcode_event.
	 *
	 * @return void
	 */
	public function register_shortcode_event(): void {
		$this->add_shortcode( 'endpoint_navigation' );
	}

	/**
	 * Display docs endpoint navigation.
	 *
	 * @param array<mixed> $atts Array of attributes.
	 * @since 1.0.0
	 * @return string|false markup.
	 */
	public function render_endpoint_navigation( $atts ) {
		$defaults = [
			'endpoint' => '',
		];

		$atts = shortcode_atts( $defaults, $atts );

		if ( empty( $atts['endpoint'] ) ) {
			return '';
		}

		ob_start();

		$this->process_endpoint_nav_query( $atts['endpoint'] );

		return ob_get_clean();
	}

	/**
	 * Update the site navigation based on the endpoint data.
	 *
	 * @param string $endpoint Array of endpoint data.
	 * @since 1.0.0
	 * @return mixed
	 */
	public function process_endpoint_nav_query( $endpoint ) {
		if ( ! is_singular( SUREDASHBOARD_SUB_CONTENT_POST_TYPE ) ) {
			return;
		}

		$content_id             = get_the_ID();
		$base_id                = absint( sd_get_post_meta( (int) $content_id, 'belong_to_course', true ) );
		$endpoint_data          = suredash_endpoint_data( (string) $endpoint, $content_id, $base_id );
		$endpoint               = ! empty( $endpoint_data['endpoint'] ) ? $endpoint_data['endpoint'] : $endpoint;
		$collapsible_navigation = apply_filters( 'suredashboard_enable_collapsible_navigation', false );

		$course_loop = is_array( $endpoint_data ) && ! empty( $endpoint_data['course_loop'] ) ? $endpoint_data['course_loop'] : [];

		if ( ! $content_id || empty( $course_loop ) || ! $base_id ) {
			return;
		}

		$lessons_completed = sd_get_user_meta( get_current_user_id(), 'portal_course_' . $base_id . '_completed_lessons', true );
		$lesson_data       = suredash_get_lesson_oriented_data( $content_id, $course_loop );
		$course_thumbnail  = PostMeta::get_post_meta_value( $base_id, 'image_url' );

		?>
			<div class="portal-aside-list-wrapper portal-aside-endpoint-navigation">
				<div class="portal-aside-group-wrap <?php echo esc_attr( $collapsible_navigation ? 'pfd-collapsible-enabled' : '' ); ?>">
					<div class="portal-lesson-aside-header">
						<h4 class="portal-lesson-aside-title sd-no-space"><?php echo esc_html( get_the_title( $base_id ) ); ?></h4>
						<div class="portal-lesson-aside-image">
							<?php if ( ! empty( $course_thumbnail ) ) { ?>
								<img src="<?php echo esc_url( $course_thumbnail ); ?>" alt="<?php echo esc_attr( get_the_title( $base_id ) ); ?>" class="portal-item-featured-image"/>
							<?php } ?>
						</div>
					</div>
					<?php
					switch ( $endpoint ) {
						default:
						case 'lesson':
							$this->render_lesson_navigation( $endpoint_data );
							break;
					}
					?>
				</div>
			</div>

			<?php
			if ( ! is_user_logged_in() ) {
				?>
					<div class="portal-progress-wrapper portal-content">
					<?php
						echo do_shortcode( User_Profile::get_instance()->get_non_logged_in_user_view( [] ) ); // @phpstan-ignore-line
					?>
					</div>
				<?php
				return;
			}
			?>

			<div class="portal-progress-wrapper portal-content">
				<span class="sd-no-space"><?php Labels::get_label( 'course_progress', true ); ?></span>
				<?php echo do_shortcode( suredash_get_course_progress_bar( $lesson_data, $lessons_completed ) ); ?>
			</div>
		<?php
	}

	/**
	 * Render lesson navigation.
	 *
	 * @param array<mixed> $endpoint_data Array of endpoint data.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_lesson_navigation( $endpoint_data ): void {
		if ( function_exists( 'suredash_pro_render_lesson_navigation' ) ) {
			suredash_pro_render_lesson_navigation( $endpoint_data );
		}
	}
}
