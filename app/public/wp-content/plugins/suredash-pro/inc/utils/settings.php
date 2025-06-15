<?php
/**
 * Settings.
 *
 * @package SureDashboardPro
 * @since 0.0.1-alpha.3
 */

namespace SureDashboardPro\Inc\Utils;

use SureDashboardPro\Inc\Traits\Get_Instance;

defined( 'ABSPATH' ) || exit;

/**
 * This class will holds the code related to the managing of settings of the plugin.
 *
 * @class Settings
 */
class Settings {
	use Get_Instance;

	/**
	 * Constructor.
	 *
	 * @since 0.0.1-alpha.3
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize Hooks.
	 *
	 * @since 1.0.0
	 */
	public function init_hooks(): void {
		add_filter( 'suredashboard_settings_dataset', [ $this, 'update_pro_settings' ] );
	}

	/**
	 * Update Pro Settings.
	 *
	 * @since 0.0.1-alpha.3
	 *
	 * @param array $settings Settings.
	 * @return array
	 */
	public function update_pro_settings( $settings ) {
		$pro_set = [
			'course_container_width' => [
				'default' => 800,
				'type'    => 'number',
			],
		];

		return array_merge( $settings, $pro_set );
	}
}
