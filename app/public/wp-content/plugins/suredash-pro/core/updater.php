<?php
/**
 * SD-Pro Updater Initialize.
 *
 * @package SureDashboardPro
 */

namespace SureDashboardPro\Core;

use SureDashboardPro\Inc\Traits\Get_Instance;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Updater.
 *
 * @since 1.0.0
 */
class Updater {
	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'add_suredash_pro_logo' ] );
	}

	/**
	 * Adds logo for SureDash Pro plugins on updater page
	 *
	 * @param object $transient Transient object.
	 * @since 1.0.0-rc.3
	 * @return object
	 */
	public function add_suredash_pro_logo( $transient ) {
		$logo_url    = SUREDASHBOARD_PRO_URL . 'assets/images/admin-icon.svg';
		$plugin_slug = 'suredash-pro/suredash-pro.php';

		if ( isset( $transient->response[ $plugin_slug ] ) ) {
			$plugin_data = $transient->response[ $plugin_slug ];

			// Only update the icons.
			$plugin_data->icons = [
				'1x' => $logo_url,
				'2x' => $logo_url,
			];

			$transient->response[ $plugin_slug ] = $plugin_data;
		}

		return $transient;
	}
}
