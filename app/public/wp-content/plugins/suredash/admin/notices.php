<?php
/**
 * Admin notices.
 *
 * @package SureDash
 * @since 0.0.2
 */

namespace SureDashboard\Admin;

use SureDashboard\Inc\Traits\Get_Instance;

/**
 * Notices
 *
 * @since 0.0.2
 */
class Notices {
	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since 0.0.2
	 */
	public function __construct() {
		add_action( 'admin_notices', [ $this, 'minimum_pro_version_requirement' ] );
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
	 * Display admin notice if premium incompatible version is activated.
	 *
	 * @since 0.0.2
	 */
	public function minimum_pro_version_requirement(): void {
		if ( ! $this->should_notice_be_visible() ) {
			return;
		}

		if ( ! defined( 'SUREDASH_PRO_VER' ) ) {
			return;
		}

		if ( version_compare( SUREDASH_PRO_VER, SUREDASH_PRO_MINIMUM_VER, '<' ) ) {
			/* translators: %s: html tags */
			$notice_message = sprintf( __( 'The %1$s %2$s %3$s plugin requires %1$s %4$s %3$s plugin to be updated to version %5$s or higher!', 'suredash' ), '<strong>', 'SureDash', '</strong>', SUREDASH_PRO_PRODUCT, SUREDASH_PRO_MINIMUM_VER );

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
