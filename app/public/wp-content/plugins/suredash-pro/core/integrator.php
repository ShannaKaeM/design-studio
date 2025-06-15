<?php
/**
 * Portals Integrator Initialize.
 *
 * @package SureDashboardPro
 */

namespace SureDashboardPro\Core;

use SureDashboardPro\Inc\Traits\Get_Instance;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Integrator.
 *
 * @since 1.0.0
 */
class Integrator {
	use Get_Instance;

	/**
	 * List of all integrations.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	public $all_integrations = [];

	/**
	 * List of active integrations.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	public $active_integrations = [];

	/**
	 * List of inactive integrations.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	public $inactive_integrations = [];

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->initialize_hooks();
	}

	/**
	 * Init Hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function initialize_hooks(): void {
		$this->register_all_integrations();
	}

	/**
	 * Register Integrations routes.
	 *
	 * @since 1.0.0
	 */
	public function register_all_integrations(): void {
		$codes_namespace = 'SureDashboardPro\Core\Integrations\\';

		$controllers = [
			$codes_namespace . 'Course',
		];

		foreach ( $controllers as $controller ) {
			$controller_slug = $controller::get_instance()->get_slug();

			if ( $controller::get_instance()->is_active() ) {
				$this->active_integrations[ $controller_slug ] = $controller::get_instance();
			} else {
				$this->inactive_integrations[ $controller_slug ] = $controller::get_instance();
			}
		}

		$this->all_integrations = array_merge( $this->active_integrations, $this->inactive_integrations );
	}

	/**
	 * Get all integrations.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_all_integrations() {
		return $this->all_integrations;
	}

	/**
	 * Get active integrations.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_active_integrations() {
		return $this->active_integrations;
	}

	/**
	 * Get inactive integrations.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_inactive_integrations() {
		return $this->inactive_integrations;
	}

	/**
	 * Get integration by slug.
	 *
	 * @param string $slug Integration slug.
	 * @since 1.0.0
	 * @return object
	 */
	public function get_integration_by_slug( $slug ) {
		foreach ( $this->all_integrations as $integration_slug => $integration ) {
			if ( $slug === $integration_slug ) {
				return $integration;
			}
		}

		return false;
	}
}
