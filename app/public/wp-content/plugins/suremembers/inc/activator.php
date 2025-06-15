<?php
/**
 * Suremembers Activator.
 *
 * @package Suremembers.
 */

namespace SureMembers\Inc;

use SureMembers\Inc\Downloads;

/**
 * Activation Class.
 *
 * @since 1.3.0
 */
class Activator {
	/**
	 * Activation handler function.
	 *
	 * @return void
	 */
	public static function activate() {

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
