<?php
/**
 * Define the REST API routes.
 *
 * @package SureDashboard
 */

namespace SureDashboardPro\Core;

use SureDashboardPro\Core\Routers\Misc as MiscRoute;
use SureDashboardPro\Inc\Traits\Get_Instance;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * REST API routes.
 *
 * @since 0.0.2
 */
class Routes {
	use Get_Instance;

	/**
	 *  Constructor
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize the class.
	 *
	 * @since 0.0.2
	 */
	public function init(): void {
		add_filter( 'suredash_rest_routes', [ $this, 'extend_pro_routes' ] );
	}

	/**
	 * Extend Pro Routes
	 *
	 * @param array $routes Array of routes.
	 * @return array<string, array<string, array<int, callable>>>
	 * @since 0.0.2
	 */
	public function extend_pro_routes( $routes ) {
		return array_merge(
			$routes,
			[
				'mark-lesson-complete' => [
					'method'              => 'POST',
					'callback'            => [ MiscRoute::get_instance(), 'mark_lesson_complete' ],
					'permission_callback' => 'user',
				],
			]
		);
	}
}
