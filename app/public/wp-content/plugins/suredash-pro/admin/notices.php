<?php
/**
 * Admin notices.
 *
 * @package SureDashboardPro
 * @since 1.0.0
 */

namespace SureDashboardPro\Admin;

use SureDashboardPro\Inc\Traits\Get_Instance;

/**
 * Notices
 *
 * @since 1.0.0
 */
class Notices {
	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_notices', [ $this, 'minimum_free_version_requirement' ] );
	}

	/**
	 * Check if the current screen is the admin screen to display the notice.
	 *
	 * @return bool
	 */
	public function should_notice_be_visible(): bool {
		if ( ! current_user_can( 'activate_plugins' ) || ! current_user_can( 'install_plugins' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Display admin notice if free version is not installed
	 *
	 * @since 1.0.0
	 */
	public function fails_to_load(): void {
		if ( ! $this->should_notice_be_visible() ) {
			return;
		}

		$plugin   = get_suredash_plugin_path();
		$cta_text = is_suredash_installed() ? __( 'Activate', 'suredash-pro' ) : __( 'Install', 'suredash-pro' );
		$cta_url  = is_suredash_installed() ? wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin ) : wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=suredash' ), 'install-plugin_suredash' );

		/* translators: %s: html tags */
		$notice_message = sprintf( __( 'The %1$s %7$s %2$s plugin requires %1$s %6$s %2$s plugin to be activated! %3$s %4$s %5$s', 'suredash-pro' ), '<strong>', '</strong>', '<p> <a class="button-primary" href="' . esc_url( $cta_url ) . '">', esc_html( $cta_text ) . ' SureDash', '</a> </p>', 'SureDash', 'SureDash Pro' );

		\Astra_Notices::add_notice(
			[
				'id'                         => 'suredash-free-activation-notice',
				'type'                       => 'error',
				/* translators: %s: html tags */
				'message'                    => sprintf(
					'<div class="notice-content" style="margin: 0;">
						%1$s
					</div>',
					$notice_message
				),
				'repeat-notice-after'        => false,
				'priority'                   => 18,
				'display-with-other-notices' => true,
				'is_dismissible'             => false,
			]
		);
	}

	/**
	 * Display admin notice if free incompatible version is activated.
	 *
	 * @since 0.0.2
	 */
	public function minimum_free_version_requirement(): void {
		if ( ! $this->should_notice_be_visible() ) {
			return;
		}

		if ( ! defined( 'SUREDASHBOARD_VER' ) ) {
			return;
		}

		if ( version_compare( SUREDASHBOARD_VER, SUREDASH_FREE_MINIMUM_VER, '<' ) ) {
			/* translators: %s: html tags */
			$notice_message = sprintf( __( 'The %1$s %2$s %3$s plugin requires %1$s %4$s %3$s plugin to be updated to version %5$s or higher!', 'suredash-pro' ), '<strong>', SUREDASH_PRO_PRODUCT, '</strong>', 'SureDash', SUREDASH_FREE_MINIMUM_VER );

			\Astra_Notices::add_notice(
				[
					'id'                         => 'suredash-free-version-requirement-notice',
					'type'                       => 'warning',
					/* translators: %s: html tags */
					'message'                    => sprintf(
						'<div class="notice-content" style="margin: 0;">
							%1$s
						</div>',
						$notice_message
					),
					'repeat-notice-after'        => false,
					'priority'                   => 18,
					'display-with-other-notices' => true,
					'is_dismissible'             => false,
				]
			);
		}
	}
}
