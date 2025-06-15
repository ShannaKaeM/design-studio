<?php
/**
 * Handles Login Restriction Options.
 *
 * @package SureMembers.
 * @since 1.7.0
 */

namespace SureMembers\Inc;

use SureMembers\Inc\Traits\Get_Instance;
use SureMembers\Inc\Settings;
use WP_Session_Tokens;

/**
 * Handle Concurrent Logins.
 *
 * @package SureMembers/Inc
 * @since 1.7.0
 */
class Login_Restriction {

	use Get_Instance;

	/**
	 * Class Constructor.
	 *
	 * @access public
	 * @return void
	 * @since 1.7.0
	 */
	public function __construct() {
		add_action( 'wp_loaded', [ $this, 'logout_all_action' ] );
		// Authentication filter to check allow login logic.
		add_filter( 'wp_authenticate_user', [ $this, 'validate_block_logic' ] );
		// Check Password filter to check block login logic.
		add_filter( 'check_password', [ $this, 'validate_allow_logic' ], 10, 4 );
	}

	/**
	 * Validate if the maximum active logins limit reached.
	 *
	 * This check happens only after authentication happens and
	 * the login logic is "Block".
	 *
	 * @param \WP_User|\WP_Error $user User Object/WPError.
	 *
	 * @access public
	 * @return object User object or error object.
	 * @since 1.7.0
	 */
	public function validate_block_logic( $user ) {
		// If login validation failed already, return that error.
		if ( is_wp_error( $user ) ) {
			return $user;
		}

		if ( ! is_array( $user->roles ) ) {
			return $user;
		}

		$restriction = $this->get_restriction_by_user( $user );

		if ( empty( $restriction ) ) {
			return $user;
		}

		if ( 'block' === $restriction[0]['login_logic'] ) {
			if ( $this->reached_limit( $user->ID ) ) {
				return new \WP_Error( 'sm_reached_login_limit', $this->error_message( $user ) );
			}
		}

		return $user;
	}

	/**
	 * Get Restriction setting by user.
	 *
	 * @param \WP_User|false $user User Object.
	 * @return array $restriction Array.
	 * @since 1.7.0
	 */
	public function get_restriction_by_user( $user ) {
		if ( ! isset( $user->roles ) ) {
			return [];
		}

		$restriction_settings = Settings::get_setting( SUREMEMBERS_LOGIN_RESTRICTIONS_SETTINGS );

		foreach ( $restriction_settings as $restrict ) {
			$user_roles = isset( $restrict['user_roles'] ) ? array_column( $restrict['user_roles'], 'value' ) : [ 'all' ];
			if ( ! empty( array_intersect( $user_roles, $user->roles ) ) || in_array( 'all', $user_roles, true ) ) {
				return [ $restrict ];
			}
		}

		return [];
	}

	/**
	 * Validate if the maximum active logins limit reached.
	 *
	 * This check happens only after authentication happens and
	 * the login logic is "Allow".
	 *
	 * @param boolean $check    User Object/WPError.
	 * @param string  $password Plaintext user's password.
	 * @param string  $hash     Hash of the user's password to check against.
	 * @param int     $user_id  User ID.
	 *
	 * @access public
	 * @return bool
	 * @since  1.7.0
	 */
	public function validate_allow_logic( $check, $password, $hash, $user_id ) {
		// If the validation failed already, bail.
		if ( ! $check ) {
			return false;
		}

		$user = get_user_by( 'ID', $user_id );

		// Get restriction settings.
		$restriction_settings = $this->get_restriction_by_user( $user );

		if ( empty( $restriction_settings ) || ! is_array( $restriction_settings ) ) {
			return true;
		}

		// Allow new logins.
		if ( 'allow' === $restriction_settings[0]['login_logic'] ) {
			// Check if limit exceed.
			if ( $this->reached_limit( $user_id ) ) {
				$manager = WP_Session_Tokens::get_instance( $user_id );
				// Destroy all others logins.
				$manager->destroy_all();
			}
		}

		return true;
	}

	/**
	 * Logout from all devices.
	 *
	 * @access public
	 * @return bool True OR False
	 * @since 1.7.0
	 */
	public function logout_all_action() {

		if ( empty( $_GET['sm_security'] ) ) {
			return false;
		}

		wp_verify_nonce( sanitize_text_field( $_GET['sm_security'] ), 'suremembers-logout-from-all' );

		if ( empty( $_GET['sm_action'] ) ) {
			return false;
		}

		if ( empty( $_GET['user_id'] ) ) {
			return false;
		}

		$user_id = absint( $_GET['user_id'] );

		if ( ! $user_id ) {
			return false;
		}

		$manager = WP_Session_Tokens::get_instance( $user_id );
		// Destroy all others logins.
		$manager->destroy_all();

		return true;
	}

	/**
	 * Check if the current user is allowed for another login.
	 *
	 * Count all the active logins for the current user and
	 * check if that exceeds the maximum login limit set.
	 *
	 * @param int $user_id User ID.
	 *
	 * @access public
	 * @return boolean Limit reached or not
	 * @since  1.7.0
	 */
	private function reached_limit( $user_id ) {
		// Check for bypass_limit filter for this user.
		if ( $this->bypass_limit( $user_id ) ) {
			return false;
		}

		$user = get_user_by( 'ID', $user_id );

		$restriction_settings = $this->get_restriction_by_user( $user );

		// Get maximum active logins allowed.
		$maximum = intval( $restriction_settings[0]['max_active_logins'] );

		// Sessions token instance.
		$manager = WP_Session_Tokens::get_instance( $user_id );

		// Count sessions.
		$count   = count( $manager->get_all() );
		$reached = $count >= $maximum;

		/**
		 * Filter hook to change the limit condition.
		 *
		 * @param bool $reached Reached.
		 * @param int  $user_id User ID.
		 * @param int  $count   Active logins count.
		 *
		 * @since 1.7.0
		 */
		return apply_filters( 'sm_login_restriction_reached_limit', $reached, $user_id, $count );
	}

	/**
	 * Custom login limit bypassing.
	 *
	 * Filter to bypass login limit based on a condition.
	 * You can make use of this filter if you want to bypass
	 * some users or roles from limit limit.
	 *
	 * @param int $user_id User ID.
	 *
	 * @access private
	 * @return bool
	 * @since 1.7.0
	 */
	private function bypass_limit( $user_id ) {
		/**
		 * Filter hook to bypass the check.
		 *
		 * @param bool $bypass  Is Bypassed Limit.
		 * @param int  $user_id User ID.
		 *
		 * @since 1.7.0
		 */
		return (bool) apply_filters( 'sm_login_restriction_bypass_limit', false, $user_id );
	}

	/**
	 * Error message text if user active logins count is maximum
	 *
	 * @param \WP_User $user User.
	 *
	 * @access private
	 * @return string Error message
	 * @since 1.7.0
	 */
	private function error_message( $user ) {

		$restriction = $this->get_restriction_by_user( $user );

		// Get Settings.
		$settings = Settings::get_custom_content_data();
		// Error message.
		$message = ! empty( $settings['login_limit_exceeded']['value'] ) ? sanitize_text_field( $settings['login_limit_exceeded']['value'] ) : sanitize_text_field( $settings['login_limit_exceeded']['default'] );

		// Reset Device Message.
		$reset_device_message = ! empty( $settings['login_limit_reset']['value'] ) ? sanitize_text_field( $settings['login_limit_reset']['value'] ) : sanitize_text_field( $settings['login_limit_reset']['default'] );

		if ( isset( $restriction[0]['allow_logout_all'] ) && $restriction[0]['allow_logout_all'] ) {
			$message .= '<br/><br/>';
			$message .= '<a href="?sm_action=logout_all&user_id=' . $user->ID . '&sm_security=' . wp_create_nonce( 'suremembers-logout-from-all' ) . '">' . $reset_device_message . '</a>';
		}

		/**
		 * Filter hook to change the error message.
		 *
		 * @param string $message Message.
		 * @since 1.7.0
		 */
		return apply_filters( 'sm_login_restriction_error_message', $message );
	}

}
