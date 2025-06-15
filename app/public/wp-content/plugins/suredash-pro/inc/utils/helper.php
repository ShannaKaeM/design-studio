<?php
/**
 * Helper.
 *
 * @package SureDashboardPro
 * @since 0.0.1-alpha.3
 */

namespace SureDashboardPro\Inc\Utils;

use SureDashboard\Inc\Utils\Settings;

/**
 * Initialize setup
 *
 * @since 0.0.1-alpha.3
 * @package SureDashboard
 */

defined( 'ABSPATH' ) || exit;

/**
 * This class setup all AJAX action
 *
 * @class Ajax
 */
class Helper {
	/**
	 * Returns an option from the database for the admin settings.
	 *
	 * @param  string $key     The option key.
	 * @param  mixed  $default Option default value if option is not available.
	 * @return mixed   Returns the option value
	 *
	 * @since 0.0.1-alpha.3
	 */
	public static function get_option( $key, $default = false ) {

		$portal_settings = Settings::get_portal_settings();

		if ( empty( $portal_settings ) || ! is_array( $portal_settings ) || ! array_key_exists( $key, $portal_settings ) ) {
			$portal_settings[ $key ] = '';
		}

		// Get the setting option if we're in the admin panel.
		$value = $portal_settings[ $key ];

		if ( $value === '' && $default !== false ) {
			return $default;
		}

		return $value;
	}

	/**
	 * Update option from the database for the admin settings.
	 *
	 * @param  string $key      The option key.
	 * @param  mixed  $value    Option value to update.
	 * @return string           Return the option value
	 *
	 * @since 0.0.1-alpha.3
	 */
	public static function update_option( $key, $value = true ) {

		$portal_settings = get_option( SUREDASHBOARD_SETTINGS );

		// If the value is same as default then remove it from the DB.
		// This will help in the translatable strings.
		if ( Settings::get_default_option( $key ) === $value ) {
			unset( $portal_settings[ $key ] );
		} else {
			$portal_settings[ $key ] = $value;
		}

		update_option( SUREDASHBOARD_SETTINGS, $portal_settings );

		return $value;
	}
}
