<?php
/**
 * This is the interface for all integrations.
 *
 * @package SureDashboardPro
 * @since 0.0.2
 */

namespace SureDashboardPro\Inc\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Interface for all integrations.
 *
 * @since 0.0.2
 */
interface Integration {
	/**
	 * Get the name of the integration.
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Get the slug of the integration.
	 *
	 * @return string
	 */
	public function get_slug();

	/**
	 * Get the description of the integration.
	 *
	 * @return string
	 */
	public function get_description();

	/**
	 * Get the condition of the integration, if it is active or not.
	 *
	 * @return string
	 */
	public function is_active();
}
