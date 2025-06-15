<?php
/**
 * Trait.
 *
 * @package SureDashboardPro
 * @since 1.0.0
 */

namespace SureDashboardPro\Inc\Traits;

/**
 * Trait Get_Instance.
 *
 * @since 1.0.0
 */
trait Get_Instance {
	/**
	 * Instance object.
	 *
	 * @var object Class Instance.
	 */
	private static $instance = null;

	/**
	 * Initiator
	 *
	 * @since 1.0.0
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
