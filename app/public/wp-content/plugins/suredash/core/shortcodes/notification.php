<?php
/**
 * Portals notification Shortcode Initialize.
 *
 * @package SureDash
 */

namespace SureDashboard\Core\Shortcodes;

use SureDashboard\Core\Notifier\Base as Notifier_Base;
use SureDashboard\Inc\Traits\Get_Instance;
use SureDashboard\Inc\Traits\Shortcode;
use SureDashboard\Inc\Utils\Helper;
use SureDashboard\Inc\Utils\Labels;

/**
 * Class Notification Shortcode.
 */
class Notification {
	use Shortcode;
	use Get_Instance;

	/**
	 * Register_shortcode_event.
	 *
	 * @return void
	 */
	public function register_shortcode_event(): void {
		$this->add_shortcode( 'notification' );
	}

	/**
	 * Display notification.
	 *
	 * @param array<mixed> $atts Array of attributes.
	 * @since 1.0.0
	 * @return string|false
	 */
	public function render_notification( $atts ) {
		$atts = apply_filters(
			'suredash_notification_attributes',
			shortcode_atts(
				[],
				$atts
			)
		);

		ob_start();
		$this->render_notification_markup( $atts );
		return ob_get_clean();
	}

	/**
	 * Shortcode callback.
	 *
	 * @param array<mixed> $atts Array of attributes.
	 * @return void
	 */
	public function render_notification_markup( $atts ): void {
		$highlighter = '<span class="notification-unread-count sd-absolute sd-flex sd-items-center sd-justify-center sd-font-12 sd-px-8 sd-max-h-20 sd-font-medium sd-bg-danger sd-color-primary sd-min-w-20 sd-nowrap sd-radius-9999"></span>';
		?>
		<a href="<?php echo esc_url( home_url() ); ?>" class="portal-notification-trigger" title="<?php Labels::get_label( 'notifications', true ); ?>">
			<?php Helper::get_library_icon( 'Bell', true, 'md' ); ?>
			<?php echo wp_kses_post( $highlighter ); ?>
		</a>
		<?php

		echo do_shortcode( $this->get_user_notification_drawer( $atts ) );
	}

	/**
	 * Display notifications drawer.
	 *
	 * @param array<mixed> $atts Array of attributes.
	 * @since 1.0.0
	 * @return string
	 */
	public function get_user_notification_drawer( $atts ) {
		ob_start();

		$notification_block_css = '';
		if ( isset( $atts['draweropenverposition'] ) ) {
			$notification_block_css .= $atts['draweropenverposition'] . ':' . $atts['drawerverpositionoffset'] . ';';
		}
		if ( isset( $atts['draweropenhorposition'] ) ) {
			$notification_block_css .= $atts['draweropenhorposition'] . ':' . $atts['drawerhorpositionoffset'] . ';';
		}

		$notifications = [];
		// @phpstan-ignore-next-line.
		$class_wrap  = ! empty( $notifications ) ? 'no-notifications' : 'has-notifications';
		$html_markup = method_exists( Notifier_Base::get_instance(), 'get_notifications_markup' ) ? Notifier_Base::get_instance()->get_notifications_markup() : '';
		?>
		<div class="portal-notification-drawer portal-content sd-absolute sd-flex sd-flex-col sd-bg-content sd-radius-12 sd-shadow-lg sd-border sd-overflow-hidden sd-hidden" style="<?php echo do_shortcode( $notification_block_css ); ?>">
			<div class="portal-notification-drawer-header sd-p-16 sd-bg-content sd-flex sd-items-center sd-justify-between sd-font-18 sd-font-semibold sd-top-0">
				<span class="portal-notification-header-title sd-font-semibold"><?php Labels::get_label( 'notifications', true ); ?></span>
				<span class="portal-notification-drawer-close sd-flex sd-pointer">
					<?php Helper::get_library_icon( 'X', true, 'md' ); ?>
				</span>
			</div>

			<div class="portal-notification-drawer-content-type sd-flex sd-justify-between sd-px-16 sd-font-14">
				<div class="notification-title-wrap sd-relative sd-w-full sd-flex sd-justify-between sd-gap-12 sd-font-14 sd-font-medium sd-border-b">
					<span>
						<span class="notification-subtitle notification-all active sd-px-8 sd-py-4 sd-pointer sd-relative sd-transition sd-text-color">
							<?php Labels::get_label( 'all_notifications', true ); ?>
						</span>
						<span class="notification-subtitle notification-unread sd-px-8 sd-py-4 sd-pointer sd-relative sd-transition sd-text-color">
							<span class="notification-unread-text">
								<?php Labels::get_label( 'unread', true ); ?>
							</span>
							<span class="notification-unread-count sd-font-medium sd-font-12 sd-radius-9999 sd-px-6 sd-py-2 sd-ml-2 sd-bg-danger sd-color-light">
								<!-- this count will be updated via JS -->
							</span>
						</span>
					</span>
					<span class="notification-mark-all-read sd-flex sd-items-center sd-p-4 sd-gap-8 sd-font-semibold">
						<span class="notification-mark-all-read-icon sd-flex sd-items-center sd-p-4 sd-gap-8 sd-font-semibold">
							<?php Helper::get_library_icon( 'CheckCheck', true, 'md' ); ?>
						</span>
						<span class="notification-mark-all-read-text">
							<?php echo esc_html__( 'Mark All as Read', 'suredash' ); ?>
						</span>
					</span>
				</div>
			</div>
			<div class="portal-notification-drawer-content sd-py-12 sd-px-8 sd-overflow-y-auto sd-bg-content <?php echo esc_attr( $class_wrap ); ?>">
				<?php
				if ( empty( $html_markup ) ) {
					?>
					<div class="no-notification sd-flex sd-flex-col sd-gap-8 sd-w-full sd-justify-center sd-items-center sd-p-20">
						<?php
						Helper::get_library_icon( 'Bell', true, 'md' );
						echo '<div class="no-notification-text">' . esc_html( Labels::get_label( 'no_notifications_title' ) ) . '</div>';
						Labels::get_label( 'no_notifications', true );
						?>
					</div>
					<?php
				} else {
					echo do_shortcode( $html_markup );
				}
				?>
			</div>
		</div>
		<?php

		return ob_get_clean(); // @phpstan-ignore-line
	}
}
