<?php
/**
 * Update Compatibility
 *
 * @package suremembers
 */

namespace SureMembers\Inc;

use SureMembers\Inc\Traits\Get_Instance;
use SureMembers\Inc\Downloads;

/**
 * SureMembers Updates Class initial setup
 *
 * @since 1.3.0
 */
class Updates {

	use Get_Instance;

	/**
	 *  Class Constructor
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init' ) );
	}

	/**
	 * Init
	 *
	 * @return void
	 * @since 1.3.0
	 */
	public function init() {
		do_action( 'suremembers_updater_before_init' );

		// Get auto saved version number.
		$saved_version = get_option( 'suremembers-version', '' );

		$saved_version = is_string( $saved_version ) ? trim( $saved_version ) : '';

		// Update auto saved version number.
		if ( empty( $saved_version ) ) {
			update_option( 'suremembers-version', SUREMEMBERS_VER );
			$this->add_support_for_downloads();
			return;
		}

		// If equals then return.
		if ( version_compare( $saved_version, SUREMEMBERS_VER, '=' ) ) {
			return;
		}

		global $wp_filesystem;
		if ( is_null( $wp_filesystem ) ) {
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			\WP_Filesystem();
		}

		if ( version_compare( $saved_version, '1.3.0', '>=' ) ) {
			$downloads        = new Downloads();
			$downloads_folder = $downloads->get_directory_path();
			if ( ! $wp_filesystem->exists( $downloads_folder ) ) {
				$this->add_support_for_downloads();
			}
		}
		// update new version to the db.
		update_option( 'suremembers-version', SUREMEMBERS_VER );

		do_action( 'suremembers_updater_after_init' );
	}

	/**
	 * Add Support for restricted download.
	 *
	 * @return void
	 * @since 1.3.0
	 */
	public function add_support_for_downloads() {
		// Add support for downloads.
		$downloads = new Downloads();
		$downloads->add_private_folder();

		/**
		 * Reset rewrite rules to avoid go to permalinks page
		 * through deleting the database options to force WP to do it
		 * because of on activation not work well flush_rewrite_rules()
		 */
		delete_option( 'rewrite_rules' );
	}
}
