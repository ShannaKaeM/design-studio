<?php
/**
 * Maintenance.
 *
 * @package SureDash
 * @since 1.0.0
 */

namespace SureDashboard\Inc\Utils;

use SureDashboard\Inc\Traits\Get_Instance;

defined( 'ABSPATH' ) || exit;

/**
 * Update Compatibility
 *
 * @package SureDash
 */
class Maintenance {
	use Get_Instance;

	/**
	 *  Constructor
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'admin_init', self::class . '::init' );
		} else {
			add_action( 'init', self::class . '::init' );
		}
	}

	/**
	 * Init
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function init(): void {
		do_action( 'suredash_update_before' );

		// Get auto saved version number.
		$saved_version = get_option( 'suredash_saved_version', false );

		// Update auto saved version number.
		if ( ! $saved_version ) {
			update_option( 'suredash_saved_version', SUREDASHBOARD_VER );
		}

		// If equals then return.
		if ( version_compare( $saved_version, SUREDASHBOARD_VER, '=' ) ) {
			return;
		}

		// Update auto saved version number.
		update_option( 'suredash_saved_version', SUREDASHBOARD_VER );

		do_action( 'suredash_update_after' );
	}
}
