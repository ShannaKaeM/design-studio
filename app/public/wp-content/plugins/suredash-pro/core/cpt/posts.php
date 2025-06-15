<?php
/**
 * Posts CPT
 *
 * This class will holds the Posts related CPT data.
 *
 * @package SureDashboardPro
 * @since 1.0.0
 */

namespace SureDashboardPro\Core\CPT;

use SureDashboardPro\Inc\Traits\Get_Instance;

/**
 * Account Posts CPT
 *
 * @since 1.0.0
 */
class Posts {
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
