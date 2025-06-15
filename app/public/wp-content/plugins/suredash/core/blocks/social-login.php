<?php
/**
 * SocialLogin AJAX.
 *
 * @package SureDash
 * @since 1.0.0
 */

namespace SureDashboard\Core\Blocks;

use ParagonIE\Sodium\Core\Curve25519\Ge\P2;
use SureDashboard\Inc\Traits\Get_Instance;
use SureDashboard\Inc\Utils\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Social login class.
 *
 * @class SocialLogin
 */
class SocialLogin {
	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
	}

	/**
	 * Handle Facebook oauth login/register.
	 *
	 * @param array<string, string> $data Form Data.
	 * @param bool                  $is_login check login or register.
	 * @return array<string, mixed>
	 * @since 1.0.0
	 */
	public function facebook_login_register( $data, $is_login = true ) {
		if ( is_string( $data ) ) {
			$data = json_decode( stripslashes( $data ), true );
		}

		if ( ! is_array( $data ) ) {
			wp_send_json_error( __( 'Invalid Data', 'suredash' ) );
		}

		$name                = sanitize_user( $data['name'] );
		$first_name          = sanitize_user( $data['first_name'] );
		$last_name           = sanitize_user( $data['last_name'] );
		$fb_user_id          = $data['user_id'] ?? filter_input( INPUT_POST, 'userID', FILTER_SANITIZE_STRING );
		$access_token        = $data['access_token'] ?? filter_input( INPUT_POST, 'security_string', FILTER_SANITIZE_STRING );
		$facebook_app_id     = Helper::get_option( 'facebook_token_id' );
		$facebook_app_secret = Helper::get_option( 'facebook_token_secret' );

		if ( ! is_string( $access_token ) || ! is_string( $facebook_app_id ) || ! is_string( $facebook_app_secret ) ) {
			wp_send_json_error( __( 'Invalid Authorization', 'suredash' ) );
		}

		$rest_data = $this->get_user_profile_info_facebook( $access_token, $facebook_app_id, $facebook_app_secret );
		if ( empty( $fb_user_id ) || empty( $rest_data ) || ! is_array( $rest_data ) || ( $fb_user_id !== $rest_data['data']['user_id'] ) || ( $facebook_app_id !== $rest_data['data']['app_id'] ) || ( ! $rest_data['data']['is_valid'] ) ) {
			wp_send_json_error( __( 'Invalid Authorization', 'suredash' ) );
		}

		$verified_email = $this->get_user_email_facebook( $rest_data['data']['user_id'], $access_token );

		if ( is_array( $verified_email ) && array_key_exists( 'email', $data ) ) {
			if ( $data['email'] === $verified_email['email'] ) {
				$email = sanitize_email( $verified_email['email'] );
			} else {
				wp_send_json_error( __( 'Invalid Authorization', 'suredash' ) );
			}
		} else {
			$email = $rest_data['data']['user_id'] . '@facebook.com';
		}

		$user_data = get_user_by( 'email', $email );

		if ( ! empty( $user_data ) && $user_data !== false ) {
			$this->set_user_auth_cookie( $user_data );

			$message = '';
			if ( ! $is_login ) {
				$message = __( 'You are already registered. Successfully logged in.', 'suredash' );
			}

			return [
				'success' => true,
				'message' => $message ? $message : __( 'Successfully logged in.', 'suredash' ),
			];
		}

		if ( get_option( 'users_can_register' ) === true ) {
			$password       = wp_generate_password( 12, true, false );
			$facebook_array = [
				'user_login' => $name,
				'user_pass'  => $password,
				'user_email' => $email,
				'first_name' => ! empty( $first_name ) ? $first_name : $name,
				'last_name'  => $last_name,
			];

			if ( username_exists( $name ) ) {
				// Generate something unique to append to the username in case of a conflict with another user.
				$suffix = '-' . zeroise( wp_rand( 0, 9999 ), 4 );
				$name  .= $suffix;

				$facebook_array['user_login'] = strtolower( (string) preg_replace( '/\s+/', '', $name ) );
			}

			add_action( 'user_register', 'suredash_grant_capabilities_to_user', 10, 1 );

			wp_insert_user( $facebook_array );

			remove_action( 'user_register', 'suredash_grant_capabilities_to_user', 10 );

			$user_data = get_user_by( 'email', $email );

			if ( $user_data ) {
				$user_ID = $user_data->ID;

				$user_meta = [
					'login_source' => 'facebook',
				];

				sd_update_user_meta( $user_ID, 'uag_pro_login_form', $user_meta );

				if ( wp_check_password( $password, $user_data->user_pass, $user_data->ID ) ) {
					$this->set_user_auth_cookie( $user_data );
					return [
						'success' => true,
						'email'   => $facebook_array,
						'message' => __( 'successfully logged in', 'suredash' ),
					];
				}
			}
		}

		return [
			'success' => false,
			'message' => __( 'You are not registered. please register first then try to login.', 'suredash' ),
		];
	}

	/**
	 * Register user.
	 *
	 * @param array<string, string> $payload Google Payload.
	 */
	public function register_user( $payload ): void {
		$username = explode( '@', $payload['email'] );
		$username = reset( $username );

		$username_prefix = apply_filters( 'suredashboard_username_prefix', '' );
		if ( $username_prefix ) {
			$username = sanitize_text_field( $username_prefix ) . $username;
		}

		$suffix = 1;
		while ( username_exists( $username ) ) {
			$username .= $suffix;
			$suffix++;
		}

		$new_user_id = register_new_user( sanitize_user( $username ), $payload['email'] );

		if ( is_wp_error( $new_user_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Something went wrong while creating the new user!', 'suredash' ) ] );
		}

		$this->login_user( $new_user_id, $payload );
	}

	/**
	 * Login user.
	 *
	 * @param int                   $id user id.
	 * @param array<string, string> $payload google Payload.
	 */
	public function login_user( $id, $payload ): void {
		$user_data                 = [];
		$user_data['ID']           = $id;
		$user_data['first_name']   = $payload['given_name'];
		$user_data['last_name']    = $payload['family_name'];
		$user_data['display_name'] = $payload['name'];

		sd_wp_update_user( $user_data );

		sd_update_user_meta( $id, 'nickname', $payload['given_name'] );

		$email     = $payload['email'];
		$user_data = get_user_by( 'email', $email );
		if ( $user_data ) {
			$this->set_user_auth_cookie( $user_data );
		}
	}

	/**
	 * Function that authenticates Facebook user.
	 *
	 * @since 1.0.0
	 * @param string $access_token Access Token.
	 * @param string $facebook_app_id App ID.
	 * @param string $facebook_app_secret Secret token.
	 *
	 * @return mixed
	 */
	private function get_user_profile_info_facebook( $access_token, $facebook_app_id, $facebook_app_secret ) {
		$fb_url = 'https://graph.facebook.com/oauth/access_token';
		$fb_url = add_query_arg(
			[
				'client_id'     => $facebook_app_id,
				'client_secret' => $facebook_app_secret,
				'grant_type'    => 'client_credentials',
			],
			$fb_url
		);

		$fb_response = wp_remote_get( $fb_url );

		if ( is_wp_error( $fb_response ) ) {
			return wp_send_json_error( $fb_response );
		}

		$fb_app_response = json_decode( wp_remote_retrieve_body( $fb_response ), true );

		$app_token = is_array( $fb_app_response ) && $fb_app_response['access_token'] ? $fb_app_response['access_token'] : '';

		$url = 'https://graph.facebook.com/debug_token';
		$url = add_query_arg(
			[
				'input_token'  => $access_token,
				'access_token' => $app_token,
			],
			$url
		);

		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			return wp_send_json_error( $response );
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}

	/**
	 * Function that retrieves authenticated Facebook email.
	 *
	 * @since 1.0.0
	 * @param string $user_id User ID.
	 * @param string $access_token User Access Token.
	 *
	 * @return mixed
	 */
	private function get_user_email_facebook( $user_id, $access_token ) {
		$fb_email_url = 'https://graph.facebook.com/' . $user_id;
		$fb_email_url = add_query_arg(
			[
				'fields'       => 'email',
				'access_token' => $access_token,
			],
			$fb_email_url
		);

		$email_response = wp_remote_get( $fb_email_url );

		if ( is_wp_error( $email_response ) ) {
			return wp_send_json_error( $email_response );
		}

		return json_decode( wp_remote_retrieve_body( $email_response ), true );
	}

	/**
	 * Set User auth cookie
	 *
	 * @param \WP_User $user_data User Object.
	 * @return void
	 * @since 1.0.0
	 */
	private function set_user_auth_cookie( $user_data ): void {

		if ( is_object( $user_data ) ) {
			$user_ID = $user_data->ID;
			wp_clear_auth_cookie();
			wp_set_current_user( $user_ID, $user_data->user_login );
			wp_set_auth_cookie( $user_ID );
			do_action( 'wp_login', $user_data->user_login, $user_data );
		}
	}
}
