<?php
/**
 * Sanitizer.
 *
 * @package SureDash
 * @since 0.0.1
 */

namespace SureDashboard\Inc\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * This class setup all sanitization methods
 *
 * @class Sanitizer
 */
class Sanitizer {
	/**
	 * Settings sanitizer for portal settings.
	 *
	 * @access public
	 *
	 * @param mixed $dataset from AJAX.
	 * @since 1.0.0
	 * @return mixed Sanitized data.
	 */
	public static function sanitize_settings_data( $dataset ) {
		$output = '';

		if ( is_array( $dataset ) ) {
			$output = [];

			foreach ( $dataset as $key => $value ) {
				$datatype = Settings::get_setting_type( $key );

				switch ( $datatype ) {
					case 'html':
						$output[ $key ] = wp_kses_post( $value );
						break;

					case 'array':
						$output[ $key ] = is_array( $value ) ? suredash_clean_data( $value ) : [];
						break;

					case 'boolean':
						$output[ $key ] = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
						break;

					case 'integer':
					case 'number':
						$output[ $key ] = absint( $value );
						break;

					case 'email':
						$output[ $key ] = sanitize_email( $value );
						break;

					default:
					case 'string':
						$output[ $key ] = sanitize_text_field( $value );
						break;
				}

				do_action( "portal_sanitize_setting_{$key}", $output[ $key ], $key );
			}
		} else {
			$output = sanitize_text_field( $dataset );
		}

		return $output;
	}

	/**
	 * Data sanitizer for AJAX.
	 *
	 * @since 0.0.1
	 * @access public
	 *
	 * @param mixed  $value from AJAX.
	 * @param string $data_type to sanitize further.
	 *
	 * @return mixed Sanitized data.
	 */
	public static function sanitize_meta_data( $value, $data_type = 'default' ) {
		$output = '';

		switch ( $data_type ) {
			case 'bool':
			case 'boolean':
				$output = isset( $value ) && sanitize_text_field( $value ) === 'true' ? true : false;
				break;

			case 'email':
				$output = isset( $value ) ? sanitize_email( wp_unslash( $value ) ) : '';
				break;

			case 'int':
			case 'integer':
				$output = ! empty( $value ) ? absint( $value ) : '';
				break;

			case 'url':
				$output = ! empty( $value ) ? esc_url( $value ) : '';
				break;

			case 'html':
				$output = ! empty( $value ) ? wp_kses_post( wp_unslash( $value ) ) : '';
				break;

			case 'metadata':
				$output = ! empty( $value ) ? PostMeta::sanitize_data( $value ) : '';
				break;

			case 'array':
			case 'default':
			default:
				$output = ! empty( $value ) ? suredash_clean_data( wp_unslash( $value ) ) : '';
				break;
		}

		return $output;
	}

	/**
	 * Data sanitizer for AJAX group meta.
	 *
	 * @access public
	 *
	 * @param mixed $dataset from AJAX.
	 * @since 1.0.0
	 * @return array<string, mixed> Sanitized data.
	 */
	public static function sanitize_term_data( $dataset ) {
		$output = '';

		if ( is_array( $dataset ) ) {
			$output = [];

			foreach ( $dataset as $key => $value ) {
				$datatype = TermMeta::get_group_meta_type( $key );

				switch ( $datatype ) {
					case 'html':
						$output[ $key ] = wp_kses_post( $value );
						break;

					case 'array':
						$output[ $key ] = is_array( $value ) ? $value : [];
						break;

					case 'boolean':
						$output[ $key ] = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
						break;

					case 'integer':
						$output[ $key ] = absint( $value );
						break;

					default:
					case 'string':
						$output[ $key ] = sanitize_text_field( $value );
						break;
				}
			}
		} else {
			$output = sanitize_text_field( $dataset );
		}

		return $output; // @phpstan-ignore-line
	}
}
