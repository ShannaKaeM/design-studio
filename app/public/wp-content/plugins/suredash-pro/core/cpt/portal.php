<?php
/**
 * Portal CPT
 *
 * This class will holds the Portal related to the admin area modification
 * along with the plugin functionalities.
 *
 * @package SureDashboard
 * @since 1.0.0
 */

namespace SureDashboardPro\Core\CPT;

use SureDashboardPro\Inc\Traits\Get_Instance;

/**
 * Account Portal CPT
 *
 * @since 1.0.0
 */
class Portal {
	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize Hooks
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_hooks(): void {
	}
}
