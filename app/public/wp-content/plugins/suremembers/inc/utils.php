<?php
/**
 * Utils.
 *
 * @package suremembers
 * @since 1.0.0
 */

namespace SureMembers\Inc;

use SureCart\Licensing\Client;
use SureMembers\Inc\Settings;
/**
 * Utils
 *
 * @since 0.0.1
 */
class Utils {

	/**
	 * This function performs array_map for multi dimensional array
	 *
	 * @param string $function function name to be applied on each element on array.
	 * @param array  $data_array array on which function needs to be performed.
	 * @return array
	 * @since 1.0.0
	 */
	public static function sanitize_recursively( $function, $data_array ) {
		$response = [];
		foreach ( $data_array as $key => $data ) {
			$val              = is_array( $data ) ? self::sanitize_recursively( $function, $data ) : $function( $data );
			$response[ $key ] = $val;
		}

		return $response;
	}

	/**
	 * Returns the instance of license's client library.
	 *
	 * @return \SureCart\Licensing\Client
	 * @since 1.9.3
	 */
	public static function get_license_client() {
		return new Client( 'SureMembers', SUREMEMBERS_PUBLIC_KEY, SUREMEMBERS_FILE );
	}

	/**
	 * Returns array in format required by select2 dropdown
	 * passed array should have id in key and label in value.
	 *
	 * @param array $access_groups array to be converted in select2 input array format.
	 * @return array
	 * @since 1.0.0
	 */
	public static function get_select2_format( $access_groups = [] ) {
		$response = [];
		if ( empty( $access_groups ) ) {
			return $response;
		}

		foreach ( $access_groups as $id => $title ) {
			$response[] = [
				'id'   => $id,
				'text' => $title,
			];
		}

		return $response;
	}

	/**
	 * Returns array in format required by React Select dropdown
	 * passed array should have id in key and label in value.
	 *
	 * @param array $data_array The data array to be converted to React Select format.
	 * @return array
	 */
	public static function get_react_select_format( $data_array = [] ) {
		$response = [];
		if ( empty( $data_array ) ) {
			return $response;
		}

		foreach ( $data_array as $id => $title ) {
			$response[] = [
				'label' => $title,
				'value' => $id,
			];
		}

		return $response;
	}

	/**
	 * Check if suremembers license is activated.
	 *
	 * @return boolean Activation status.
	 * @since 1.0.0
	 */
	public static function is_license_activated() {

		$client     = self::get_license_client();
		$activation = $client->settings()->get_activation();

		return isset( $activation->id ) && ! empty( $activation->id );
	}

	/**
	 * Activate License.
	 *
	 * @param string $key License Key.
	 * @return bool|\WP_Error Activation status.
	 * @since 1.8.1
	 */
	public static function activate_license( $key ) {

		$client = self::get_license_client();
		return $client->license()->activate( $key );
	}

	/**
	 * Deactivate License.
	 *
	 * @return bool|\WP_Error Deactivation status.
	 * @since 1.8.1
	 */
	public static function deactivate_license() {

		$client = self::get_license_client();
		return $client->license()->deactivate();
	}

	/**
	 * Remove blank array.
	 *
	 * @param array $array It is important to variable should be array.
	 * @return array|string
	 * @since  1.1.0
	 */
	public static function remove_blank_array( $array ) {
		if ( ! is_array( $array ) ) {
			return $array;
		}
		foreach ( $array as $key => &$value ) {
			if ( is_array( $value ) ) {
				if ( ! empty( $value ) ) {
					$value = self::remove_blank_array( $value );
					if ( empty( $value ) ) {
						unset( $array[ $key ] );
					}
				} else {
					unset( $array[ $key ] );
				}
			} elseif ( is_string( $value ) && '' === trim( $value ) ) {
				unset( $array[ $key ] );
			}
		}
		return $array;
	}

	/**
	 * Converts content metadata slug to text
	 *
	 * @param array $data array to convert data from.
	 * @return array
	 * @since 1.1.0
	 */
	public static function convert_slug_to_text( $data ) {
		$response = [];
		foreach ( $data as $option ) {
			$params = explode( '-', $option );

			if ( count( $params ) <= 1 ) {
				return [];
			}

			switch ( $params[0] ) {
				case 'tax':
					$temp = [];
					$term = get_term( intval( $params[1] ) );
					if ( ! empty( $term->name ) ) {
						/* translators: %s term name. */
						$temp['label'] = sprintf( __( 'All singulars from %s', 'suremembers' ), $term->name );
						$temp['value'] = $option;
						$response[]    = $temp;
					}
					break;
				case 'postchild':
					$temp  = [];
					$title = get_the_title( intval( $params[1] ) );
					if ( ! empty( $title ) ) {
						/* translators: %s title. */
						$temp['label'] = sprintf( __( 'Child of %s', 'suremembers' ), $title );
						$temp['value'] = $option;
						$response[]    = $temp;
					}
					break;
				case 'post':
				default:
					$temp  = [];
					$title = get_the_title( intval( $params[1] ) );
					if ( ! empty( $title ) ) {
						$temp['label'] = $title;
						$temp['value'] = $option;
						$response[]    = $temp;
					}
					break;
			}
		}

		return $response;
	}

	/**
	 * Returns integration icons
	 *
	 * @param string $integration integration slug.
	 * @return array|string
	 * @since 1.1.0
	 */
	public static function integration_icons( $integration = '' ) {
		$icons_list = [
			'buddyboss'    => SUREMEMBERS_DIR . 'admin/assets/images/integrations/buddyboss.svg',
			'suremembers'  => SUREMEMBERS_DIR . 'admin/assets/images/icon.svg',
			'surecart'     => SUREMEMBERS_DIR . 'admin/assets/images/integrations/surecart.svg',
			'suretriggers' => SUREMEMBERS_DIR . 'admin/assets/images/integrations/suretriggers.svg',
			'woocommerce'  => SUREMEMBERS_DIR . 'admin/assets/images/integrations/woocommerce.svg',
			'webhook'      => SUREMEMBERS_DIR . 'admin/assets/images/integrations/webhook.svg',
		];

		if ( empty( $integration ) ) {
			return $icons_list;
		}

		if ( isset( $icons_list[ $integration ] ) ) {
			return $icons_list[ $integration ];
		}

		return [];
	}

	/**
	 * Send email notification to the users.
	 * Depending on Registration, Access Expiration Notification, Password Reset Notification.
	 *
	 * @param int    $user_id Current User's ID.
	 * @param string $template_key  The template key for which the email has to send.
	 *                              The template key would be as: enable_user_onboarding, enable_reset_password, enable_access_exp.
	 *                              These keys will be also used to fetch the saved settings for this specified key by excluding
	 *                              the 'enable_' string.
	 * @param int    $access_group_id  The Access group ID for which the email has to send.
	 * @param bool   $send_email    Flat to choose to send the email notification or not.
	 * @return bool|void
	 */
	public static function send_email_notification( $user_id, $template_key, $access_group_id = 0, $send_email = true ) {

		// Return if the opt-ed for not to send the email notification.
		if ( apply_filters( 'suremembers_send_email_notification', ! $send_email, $user_id, $template_key, $access_group_id ) ) {
			return;
		}

		// Fetch the user ID if not send.
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		// Return if template key is not set.
		if ( empty( $template_key ) ) {
			return;
		}

		$email_template = Settings::get_setting( SUREMEMBERS_EMAIL_TEMPLATE_SETTINGS );

		// Return if email template array is set or user id is empty.
		if ( empty( $email_template ) || empty( $user_id ) ) {
			return;
		}

		// Get user specific access group data.
		$access_group_data = self::get_user_specific_access_group_data( $user_id, $access_group_id );

		if ( $email_template[ 'enable_' . $template_key ] ) { // Check the template is enabled or not.
			return Settings::send_email_template( $user_id, $template_key, $access_group_data ); // Send the email. If enabled.
		}
	}

	/**
	 * Get the user specific access group details.
	 *
	 * @param int $user_id              The Current user ID.
	 * @param int $access_group_id      The Current access group ID.
	 * @return array $access_group_data The array of access group data.
	 *
	 * @since 1.10.0
	 */
	public static function get_user_specific_access_group_data( $user_id, $access_group_id ) {

		// Return if access group id is not set.
		if ( empty( $access_group_id ) ) {
			return array();
		}

		// Get user expiration details for the access group.
		$expiration = get_post_meta( $access_group_id, SUREMEMBERS_PLAN_EXPIRATION, true );

		// Get the expiry date of the user for the access group.
		$user_expire = get_user_meta( $user_id, SUREMEMBERS_USER_EXPIRATION, true );

		// Get the access group's setting data for the provided access group id.
		$access_group_data = get_user_meta( $user_id, SUREMEMBERS_USER_META . "_$access_group_id", true );
		$expiry_date       = '';

		// Set default and required data.
		$new_access_group_data = array(
			'id'          => intval( $access_group_id ),
			'title'       => esc_html( get_the_title( intval( $access_group_id ) ) ),
			'expiry_date' => '',
		);

		// Prepare and extract the access expiry date for the given access group and user.
		if ( ! empty( $expiration ) && is_array( $expiration ) && isset( $expiration['type'] ) && isset( $expiration['delay'] ) ) {
			if ( 'relative_date' === ( $expiration['type'] ) ) {
				$current_date     = ! empty( $access_group_data ) && is_array( $access_group_data ) ? $access_group_data['created'] : time();
				$future_date      = '';
				$future_timestamp = strtotime( '+' . $expiration['delay'] . ' days', $current_date );

				if ( false !== $future_timestamp ) {
					$future_date = date( 'Y-m-d', $future_timestamp ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				}
				if ( isset( $user_expire ) && is_array( $user_expire ) && isset( $user_expire[ $access_group_id ] ) ) {
					$expiry_date = sanitize_text_field( strval( $user_expire[ $access_group_id ] ) );
				} else {
					$expiry_date = $future_date;
				}
			} elseif ( 'specific_date' === ( $expiration['type'] ) ) {
				$expiry_date = esc_html( $expiration['specific_date'] );
			}

			// Set the expiry date of for the user.
			$new_access_group_data['expiry_date'] = $expiry_date;
		}

		return $new_access_group_data;
	}

	/**
	 * Append the URL params to the redirect URL before redirecting.
	 *
	 * @param string $url URL to redirect.
	 * @return string $url URL to redirect with URL params.
	 * @since 1.9.4
	 */
	public static function maybe_append_url_params( $url ) {

		/**
		 * A filter to enable/disable the appending the URL params feature.
		 *
		 * @since 1.9.4
		 */
		if ( ! apply_filters( 'suremembers_maybe_append_url_params', true ) ) {
			return $url;
		}

		if ( empty( $_SERVER['HTTP_REFERER'] ) ) {
			$current_page_url = self::prepare_page_url();
		} else {
			$current_page_url = esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) );
		}

		if ( empty( $current_page_url ) ) {
			return $url;
		}

		// Get the current page URL and parse it to explode the URL in different URL components.
		$url_params_components = wp_parse_url( esc_url_raw( wp_unslash( $current_page_url ) ) );

		// Process only if the URL components is not empty and query i:e query strings are not empty.
		if ( is_array( $url_params_components ) && ! empty( $url_params_components['query'] ) ) {

			// Convert the string query from string to array format.
			parse_str( $url_params_components['query'], $parsed_query_string );

			// Merge the new and already existing query strings.
			$url = add_query_arg( $parsed_query_string, $url );
		}

		return $url;
	}

	/**
	 * Function to prepare the current page URL.
	 *
	 * @return string $url Current page URL.
	 * @since 1.9.4
	 */
	public static function prepare_page_url() {

		$url = '';

		// Check if HTTP_REFERER is set and fetch its query strings.
		if ( isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ) {
			$url .= 'https://';
		} else {
			$url .= 'http://';
		}

		// Append the host(domain name, ip) to the URL.
		$url .= ! empty( $_SERVER['HTTP_HOST'] ) ? esc_url_raw( $_SERVER['HTTP_HOST'] ) : '';

		// Append the requested resource location to the URL.
		$url .= ! empty( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( $_SERVER['REQUEST_URI'] ) : '';

		return $url;
	}

	/**
	 * Function to stop the infinite redirect to the URL added in the access group for restriction.
	 *
	 * @param string $redirect_url The URL to redirect if the restriction is set.
	 * @return void
	 * @since 1.10.3
	 */
	public static function stop_infinite_redirect( $redirect_url ) {

		// Return if redirect URL is empty.
		if ( empty( $redirect_url ) ) {
			return;
		}

		// Check the redirect URL and the current home URL is same.
		$pos = strpos( $redirect_url, home_url() );

		if ( 0 === $pos ) {
			$time = isset( $_COOKIE['suremembers_timestamp'] ) ? sanitize_text_field( $_COOKIE['suremembers_timestamp'] ) : ''; // phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___COOKIE
			if ( empty( $time ) ) {
				$time = time();
				setcookie( 'suremembers_timestamp', $time, time() + 10, COOKIEPATH, COOKIE_DOMAIN ); //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.cookies_setcookie
			}
			$urls = get_transient( 'suremembers_redirection_' . $time );
			if ( empty( $urls ) || ! is_array( $urls ) ) {
				$urls = [];
			}

			if ( isset( $urls[ $redirect_url ] ) ) {
				$count                 = $urls[ $redirect_url ] + 1;
				$urls[ $redirect_url ] = $count;
			} else {
				$urls[ $redirect_url ] = 0;
			}

			set_transient( 'suremembers_redirection_' . $time, $urls, time() + 10 );
		} elseif ( false === $pos ) {
			setcookie( 'suremembers_timestamp', '', time() - 60, COOKIEPATH, COOKIE_DOMAIN ); //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.cookies_setcookie
		}
	}
}
