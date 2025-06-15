<?php
/**
 * Settings helpers.
 *
 * @package suremembers
 * @since 1.0.0
 */

namespace SureMembers\Inc;

use SureMembers\Admin\Templates;

/**
 * Settings helper class.
 *
 * @since 1.0.0
 */
class Settings {

	/**
	 * Returns default value for global settings.
	 *
	 * @return array
	 * @since 1.4.0
	 */
	private static function global_defaults() {
		return [
			'suremembers_admin_settings'              => [
				'enable_gutenberg_icon'     => true,
				'decline_admin_screen'      => [],
				'registration_access_group' => [],
				'enable_search_restriction' => false,
				'hide_woocommerce_coupon'   => false,
			],
			'suremembers_redirect_rules'              => [
				'login_redirect'  => '',
				'logout_redirect' => '',
			],
			'suremembers_custom_content'              => [
				'login_link'              => 'Login',
				'custom_template_heading' => 'This content is restricted',
				'loop_content'            => 'Restricted content',
				'login_popup_title'       => 'Login',
				'login_popup_username'    => 'Email or Username',
				'login_popup_password'    => 'Password',
				'login_popup_remember'    => 'Remember Me',
				'login_popup_forgot'      => 'Forgot password?',
				'login_popup_submit'      => 'Login',
				'login_limit_exceeded'    => 'Maximum number of allowed active logins has been exceeded for your account. Please logout from another device to continue.',
				'login_limit_reset'       => 'Click here to Logout from other devices.',
			],
			'suremembers_login_form_settings'         => [
				'primary_color'           => '',
				'secondary_color'         => '',
				'text_color'              => '',
				'link_color'              => '',
				'logo_width'              => '',
				'logo_height'             => '',
				'disable_logo'            => false,
				'custom_logo'             => false,
				'logo_image'              => '',
				'enable_transparent_form' => false,
				'login_form_background'   => '',
				'login_form_border'       => '',
				'enable_background_image' => false,
				'background_repeat'       => 'no-repeat',
				'background_position'     => 'center',
				'background_size'         => 'cover',
				'background_image'        => '',
				'background_color'        => 'f0f0f1',
				'login_url'               => 'login',
				'enable_login_url'        => false,
				'login_redirect_url'      => '404',
			],
			'suremembers_email_template_settings'     => [
				'form_name'                        => get_bloginfo( 'name' ),
				'from_email'                       => get_bloginfo( 'admin_email' ),

				// Reset password notifications settings options.
				'enable_reset_password'            => false,
				'reset_email_use_woo_template'     => false,
				'reset_email_subject'              => 'Password Reset Request',
				'reset_email_content'              => '<p>Hello {$user_display_name}, </p><p>You requested a password reset. Please click the following link to reset your password: </p><p>{$reset_password_link}</p>',

				// New user registration/onboarding settings options.
				'enable_user_onboarding'           => false,
				'user_onboarding_use_woo_template' => false,
				'user_onboarding_subject'          => 'Welcome to our Site!',
				'user_onboarding_content'          => '<p>Hello {$user_display_name},</p> <p>Welcome to our site! We are excited to have you on board.</p><p>Best regards,<br/>{$site_name}</p>',

				// Access expiration notification settings options.
				'enable_access_exp'                => false,
				'access_exp_use_woo_template'      => false,
				'access_exp_subject'               => 'Congratulation!! You have been added to the Access Group',
				'access_exp_content'               => '<p>Hello {$user_display_name},</p><p>You have been added to a site access group. Your access will expire on {$sm_access_group_expiration}.</p><p>Best regards,<br/>{$site_name}</p>',

			],
			'suremembers_login_restrictions_settings' => [],
			'suremembers_webhook_endpoints'           => [],
		];
	}

	/**
	 * Returns default value for global settings.
	 *
	 * @param int|string $user_id       User ID/User Email.
	 * @param string     $template_type Template type.
	 * @param array      $access_group_data The access group data.
	 * @param bool       $send_test Send the test email.
	 *
	 * @return bool|void
	 * @since 1.10.0
	 */
	public static function send_email_template( $user_id, $template_type, $access_group_data = array(), $send_test = false ) {

		// Return if no user ID or email is provided.
		if ( empty( $user_id ) ) {
			wp_send_json_error( 400 );
		}

		// Get email notification settings.
		$settings = self::get_setting( SUREMEMBERS_EMAIL_TEMPLATE_SETTINGS );

		// Get the email notification message, subject and message content.
		switch ( $template_type ) {
			case 'user_onboarding':
				$use_woo_template = $settings['user_onboarding_use_woo_template'];
				$subject          = $settings['user_onboarding_subject'];
				$message          = $settings['user_onboarding_content'];
				break;
			case 'access_exp':
				$use_woo_template = $settings['access_exp_use_woo_template'];
				$subject          = $settings['access_exp_subject'];
				$message          = $settings['access_exp_content'];
				break;
			case 'reset_password':
				$use_woo_template = $settings['reset_email_use_woo_template'];
				$subject          = $settings['reset_email_subject'];
				$message          = $settings['reset_email_content'];
				break;
			default:
				return;
		}

		// Prepare the user's data to which we needs to send the email.
		$to = '';

		if ( ! empty( $send_test ) && is_email( $user_id ) ) {
			// Assign the email ID directly if the user_id is email.
			$user = array();
			$to   = $user_id;
		} else {
			// Get the user's ID if the user_id is integer or not an email.
			$user = get_userdata( intval( $user_id ) );
			$to   = ! empty( $user ) ? $user->user_email : '';
		}

		// Return if the send to email ID is not found.
		if ( empty( $to ) ) {
			return; // Return early if user data is not found.
		}

		$message = $send_test ? $message : self::replace_placeholders( $message, $user, $access_group_data );
		$subject = $send_test ? $subject : self::replace_placeholders( $subject, $user, $access_group_data );

		$from_mail = isset( $settings['from_email'] ) ? sanitize_email( $settings['from_email'] ) : '';

		// Use default WordPress email address if $from_mail is empty.
		if ( empty( $from_mail ) ) {
			$server_name       = isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( $_SERVER['SERVER_NAME'] ) : '';
			$default_from_mail = 'wordpress@' . preg_replace( '#^www\.#', '', strtolower( $server_name ) );
			$from_mail         = $default_from_mail;
		}

		$from_name = isset( $settings['form_name'] ) ? sanitize_text_field( $settings['form_name'] ) : '';

		$headers = array(
			'Reply-To: ' . $from_name . ' <' . $from_mail . '>',
			'Content-Type: text/html; charset=UTF-8',
			'Content-Transfer-Encoding: quoted-printable',
			'From: ' . $from_name . ' <' . $from_mail . '>',
		);

		$body = '';

		if ( $use_woo_template && Helper::is_woocommerce_active() ) {
			// Send the email only if the WooCommerce is active and template override is enabled.
			$body = Templates::prepare_woo_email_content( $from_name, $subject, $message );
		} else {
			$body = Templates::prepare_email_content( $from_name, $message );
		}

		return self::send_email( $to, $subject, $body, $headers, $use_woo_template );
	}

	/**
	 * Replace placeholders with user values.
	 *
	 * @param string   $content Email content with placeholders.
	 * @param \WP_User $user User object.
	 * @param array    $access_group_data The access group data of user.
	 *
	 * @return string Email content with replaced values.
	 * @since 1.10.0
	 */
	public static function replace_placeholders( $content, $user, $access_group_data ) {
		// Generate a password reset key for the user.
		$key = get_password_reset_key( $user );

		// Construct the password reset link.
		$reset_link = esc_url(
			add_query_arg(
				array(
					'action' => 'rp',
					'key'    => $key,
					'login'  => rawurlencode( $user->user_login ),
				),
				wp_login_url()
			)
		);

		$user_id            = ! empty( $user->ID ) ? intval( $user->ID ) : 0;
		$user_first_name    = ! empty( $user->first_name ) ? sanitize_text_field( $user->first_name ) : '';
		$user_last_name     = ! empty( $user->last_name ) ? sanitize_text_field( $user->last_name ) : '';
		$user_user_email    = ! empty( $user->user_email ) ? sanitize_email( $user->user_email ) : '';
		$user_user_login    = ! empty( $user->user_login ) ? $user->user_login : '';
		$user_display_name  = ! empty( $user->display_name ) ? sanitize_text_field( $user->display_name ) : '';
		$user_user_nicename = ! empty( $user->user_nicename ) ? sanitize_text_field( $user->user_nicename ) : '';

		$placeholders = array(
			'{$user_first_name}'            => $user_first_name,
			'{$user_last_name}'             => $user_last_name,
			'{$user_full_name}'             => $user_first_name . ' ' . $user_last_name,
			'{$user_email}'                 => $user_user_email,
			'{$user_login}'                 => $user_user_login,
			'{$user_display_name}'          => $user_display_name,
			'{$user_nicename}'              => $user_user_nicename,
			'{$user_id}'                    => $user_id,
			'{$user_url}'                   => get_author_posts_url( $user_id ),
			'{$user_avatar}'                => get_avatar( $user_id ),
			'{$site_name}'                  => get_bloginfo( 'name' ),
			'{$site_url}'                   => site_url(),
			'{$site_admin_email}'           => get_option( 'admin_email' ),
			'{$reset_password_link}'        => $reset_link,
			'{$login_url}'                  => wp_login_url(),
			'{$user_registered}'            => $user->user_registered,
			'{$user_address}'               => get_user_meta( $user_id, 'address', true ),
			'{$username}'                   => $user->user_login,
			'{$sm_access_group}'            => ! empty( $access_group_data['title'] ) ? esc_html( $access_group_data['title'] ) : '<br/>',
			'{$sm_access_group_expiration}' => ! empty( $access_group_data['expiry_date'] ) ? esc_html( $access_group_data['expiry_date'] ) : '<br/>',
			'\n'                            => '<br/>',
		);

		$replaced_content = strtr( $content, $placeholders );

		return $replaced_content;
	}

	/**
	 * Trigger Mail.
	 *
	 * @param string $to      Recipient email address.
	 * @param string $subject Email subject.
	 * @param string $body    Email body.
	 * @param string $headers Email headers.
	 * @param bool   $use_woo Weather to use the WooCommerce email function.
	 *
	 * @return bool
	 * @since 1.10.0
	 */
	public static function send_email( $to, $subject, $body, $headers, $use_woo = false ) {

		if ( $use_woo && Helper::is_woocommerce_active() ) {
			$successful_mail = wc_mail( sanitize_email( $to ), $subject, stripslashes( $body ), $headers ); // phpcs:ignore
		} else {
			$successful_mail = wp_mail( sanitize_email( $to ), $subject, stripslashes( $body ), $headers ); // phpcs:ignore
		}

		return $successful_mail ? true : false;
	}

	/**
	 * Get Settings option.
	 *
	 * @return mixed Settings array or JSON string.
	 * @since 1.0.0
	 */
	public static function get_settings() {
		$response = [];
		foreach ( self::global_defaults() as $key => $default_data ) {
			$response[ $key ] = self::get_setting( $key );
		}
		return apply_filters( 'suremembers_global_settings', $response );
	}

	/**
	 * Get value of setting option
	 *
	 * @param string $key value of global setting.
	 * @return array
	 * @since 1.0.0
	 */
	public static function get_setting( $key ) {
		$db_data         = get_option( $key, [] );
		$global_defaults = self::global_defaults();

		$setting = self::parse_args( $db_data, $global_defaults[ $key ] );
		return ! empty( $setting ) ? $setting : [];
	}

	/**
	 * Merge user defined arguments into defaults array.
	 * Similar to wp_parse_args() just a bit extended to work with multidimensional arrays.
	 *
	 * @param array $args      (Required) Value to merge with $defaults.
	 * @param array $defaults  Array that serves as the defaults. Default value: ''.
	 * @return array Array of parsed values.
	 * @since 1.5.0
	 */
	public static function parse_args( &$args, $defaults = [] ) {
		$args     = (array) $args;
		$defaults = (array) $defaults;
		$result   = $defaults;

		foreach ( $args as $key => &$value ) {
			if ( is_array( $value ) && ! empty( $value ) && isset( $result[ $key ] ) ) {
				$result[ $key ] = self::parse_args( $value, $result[ $key ] );
			} else {
				$result[ $key ] = $value;
			}
		}
		return $result;
	}

	/**
	 * Update setting by key.
	 *
	 * @param string $key Option key to update.
	 * @param array  $data Array of options data to update.
	 * @return void
	 * @since 1.0.0
	 */
	public static function update_setting( $key, $data ) {
		update_option( $key, $data );
	}

	/**
	 * Returns data for custom content tab
	 *
	 * @param string $key Option key to retrieve values.
	 * @return array
	 * @since 1.4.0
	 */
	public static function get_custom_content_data( $key = '' ) {
		$values          = self::get_setting( SUREMEMBERS_CUSTOM_CONTENT );
		$global_defaults = self::global_defaults();
		$defaults        = $global_defaults[ SUREMEMBERS_CUSTOM_CONTENT ];
		$default_data    = [
			'login_link'              => [
				'label'       => __( 'Login link label', 'suremembers' ),
				'description' => __( 'This text will appear as Login link on protected content.', 'suremembers' ),
			],
			'custom_template_heading' => [
				'label'       => __( 'Custom template heading', 'suremembers' ),
				'description' => __( 'This text will be used as heading for custom template.', 'suremembers' ),
			],
			'loop_content'            => [
				'label'       => __( 'Content for Archive Page / Search Result', 'suremembers' ),
				'description' => __( 'This text will replace content on Archive page / Search Result for protected content.', 'suremembers' ),
			],
			'login_popup_title'       => [
				'label'       => __( 'Login popup label', 'suremembers' ),
				'description' => __( 'This text will appear as title for login popup form.', 'suremembers' ),
			],
			'login_popup_username'    => [
				'label'       => __( 'Login popup username label', 'suremembers' ),
				'description' => __( 'This text will appear as label for username input field.', 'suremembers' ),
			],
			'login_popup_password'    => [
				'label'       => __( 'Login popup password label', 'suremembers' ),
				'description' => __( 'This text will appear as label for password input field.', 'suremembers' ),
			],
			'login_popup_remember'    => [
				'label'       => __( 'Login popup remember me label', 'suremembers' ),
				'description' => __( 'This text will appear as text for remember me label.', 'suremembers' ),
			],
			'login_popup_forgot'      => [
				'label'       => __( 'Login popup forgot password link', 'suremembers' ),
				'description' => __( 'This text will appear as text for forgot password link.', 'suremembers' ),
			],
			'login_popup_submit'      => [
				'label'       => __( 'Login popup submit button label', 'suremembers' ),
				'description' => __( 'This text will appear as label for submit button.', 'suremembers' ),
			],
			'login_limit_exceeded'    => [
				'label'       => __( 'Login limit exceeded error message', 'suremembers' ),
				'description' => __( 'This text will appear as an error message when login limit is exceeded when prevent login sharing is used.', 'suremembers' ),
			],
			'login_limit_reset'       => [
				'label'       => __( 'Login limit reset error message', 'suremembers' ),
				'description' => __( 'This text will appear in the link to reset devices when login limit is exceeded.', 'suremembers' ),
			],
		];
		foreach ( $defaults as $index => $default ) {
			$default_data[ $index ]['value']   = isset( $values[ $index ] ) ? $values[ $index ] : $default;
			$default_data[ $index ]['default'] = $default;
		}

		if ( empty( $key ) ) {
			return $default_data;
		}

		if ( isset( $default_data[ $key ] ) ) {
			return $default_data[ $key ];
		}

		return $default_data;
	}

}
