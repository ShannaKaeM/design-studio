<?php
/**
 * Global Settings class.
 *
 * @package suremembers
 * @since 1.0.0
 */

namespace SureMembers\Admin;

use SureMembers\Inc\Traits\Get_Instance;
use SureMembers\Inc\Utils;
use SureMembers\Inc\Access_Groups;
use SureMembers\Inc\Settings;
use SureMembers\Inc\Access;

/**
 * Global settings class.
 *
 * @since 1.0.0
 */
class Settings_Screen {

	use Get_Instance;

	/**
	 * Class Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'settings_page_assets' ] );
		add_action( 'wp_ajax_suremembers_get_global_settings', [ $this, 'get_global_settings' ] );
		add_action( 'wp_ajax_suremembers_update_global_settings', [ $this, 'update_global_settings' ] );
		add_action( 'wp_ajax_suremembers_email_template_global_settings', [ $this, 'update_email_global_template_settings' ] );
		// Custom user roles.
		add_action( 'wp_ajax_suremembers_add_user_roles', [ $this, 'suremembers_add_user_roles' ] );
		add_action( 'wp_ajax_suremembers_remove_user_roles', [ $this, 'suremembers_remove_user_roles' ] );
		add_action( 'wp_ajax_suremembers_update_user_roles', [ $this, 'suremembers_update_user_roles' ] );
		add_action( 'wp_ajax_suremembers_search_access_groups', [ $this, 'search_access_groups' ] );
		// Redirection rules.
		add_action( 'wp_ajax_suremembers_redirection_rules', [ $this, 'save_redirection_rules' ] );
		// Licensing.
		add_action( 'wp_ajax_suremembers_license_activation', array( $this, 'suremembers_license_activation' ) );
		add_action( 'wp_ajax_suremembers_license_deactivation', array( $this, 'suremembers_license_deactivation' ) );

		add_action( 'wp_ajax_suremembers_import_users', array( $this, 'suremembers_import_users' ) );
		add_action( 'wp_ajax_suremembers_email_template_send_test_mail', array( $this, 'send_email_template_test_mail' ) );
	}

	/**
	 * Update email template settings.
	 *
	 * @return void
	 * @since 1.10.0
	 */
	public function update_email_global_template_settings() {

		check_ajax_referer( 'suremembers_global_settings_nonce', 'security' );

		// Check user permission.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Current user does not have the required permission.', 'suremembers' ) ) );
		}

		// Verify the presence of setting data.
		if ( ! isset( $_POST['settings_data'] ) || empty( $_POST['settings_data'] ) ) {
			wp_send_json_error( [ 'message' => __( 'Settings cannot be empty.', 'suremembers' ) ] );
		}

		if ( ! isset( $_POST['setting_key'] ) || empty( $_POST['setting_key'] ) ) {
			wp_send_json_error( [ 'message' => __( 'Setting key is required to save data.', 'suremembers' ) ] );
		}

		// Ignoring sanitization as it it done below with custom function.
		$settings_data = json_decode( stripslashes_deep( $_POST['settings_data'] ), true, 512, JSON_OBJECT_AS_ARRAY ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		// Sending an array of the keys which we need to allow the basic HTML in the database allowed by the WordPress.
		$data_to_allow_wp_kses = array( 'user_onboarding_content', 'reset_email_content', 'access_exp_content' );

		$settings_data = is_array( $settings_data ) ? $this->sanitize_settings( $settings_data, $data_to_allow_wp_kses ) : [];
		$key           = sanitize_text_field( $_POST['setting_key'] );

		Settings::update_setting( $key, $settings_data );

		wp_send_json_success(
			[
				'message'       => __( 'Email template updated successfully.', 'suremembers' ),
				'settings_data' => $settings_data,
			]
		);
	}

	/**
	 * Import user CSV.
	 *
	 * @Hooked - wp_ajax_suremembers_import_users
	 *
	 * @return void
	 * @since 1.9.0
	 */
	public static function suremembers_import_users() {
		// Verify the nonce.
		if ( ! check_ajax_referer( 'suremembers_global_settings_nonce', 'security', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'suremembers' ) ) );
		}

		// Check user permission.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Current user does not have the required permission.', 'suremembers' ) ) );
		}

		// Verify the presence of user data.
		if ( ! isset( $_POST['userData'] ) ) {
			wp_send_json_error( array( 'message' => __( 'No user data found.', 'suremembers' ) ) );
		}

		// Decode and sanitize the user data.
		$user_data = json_decode( sanitize_text_field( wp_unslash( $_POST['userData'] ) ), true );
		if ( ! is_array( $user_data ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid user data.', 'suremembers' ) ) );
		}

		// Prepare user data.
		$user = array(
			'user_login'                           => '',
			'user_email'                           => '',
			'user_pass'                            => '',
			'role'                                 => 'subscriber',
			'first_name'                           => '',
			'last_name'                            => '',
			'user_url'                             => '',
			'user_registered'                      => '',
			'suremembers_access_groups'            => '',
			'suremembers_access_groups_expiration' => '',
		);

		$send_onboarding_email = isset( $_POST['sendOnboardingEmail'] ) && true === filter_var( $_POST['sendOnboardingEmail'], FILTER_VALIDATE_BOOLEAN ) ? true : false;
		$send_reset_pass_email = isset( $_POST['sendResetEmail'] ) && true === filter_var( $_POST['sendResetEmail'], FILTER_VALIDATE_BOOLEAN ) ? true : false;

		$is_onboarding_email_sent = false;
		$is_reset_pass_email_sent = false;

		$is_onboarding_email_sent_msg = false;
		$is_reset_pass_email_sent_msg = false;

		// Map user data to the prepared user array.
		foreach ( $user_data as $col => $cell ) {

			// Remove the `*` and space with `_` from the headers.
			$col = strtolower( str_replace( [ ' ', '*' ], [ '_', '' ], $col ) );

			switch ( $col ) {
				case 'username':
					$user['user_login'] = sanitize_user( $cell );
					break;
				case 'email':
					$user['user_email'] = sanitize_email( $cell );
					break;
				case 'password':
					$user['user_pass'] = sanitize_text_field( $cell );
					break;
				case 'role':
					$user['role'] = sanitize_text_field( $cell );
					break;
				case 'generate_password':
					$user['user_pass'] = sanitize_text_field( $cell );
					break;
				case 'first_name':
					$user['first_name'] = sanitize_text_field( $cell );
					break;
				case 'last_name':
					$user['last_name'] = sanitize_text_field( $cell );
					break;
				case 'website':
					$user['user_url'] = esc_url_raw( $cell );
					break;
				case 'registered':
					$user['user_registered'] = sanitize_text_field( $cell );
					break;
				case 'suremembers_access_groups':
					$user['suremembers_access_groups'] = sanitize_text_field( $cell );
					break;
				case 'suremembers_access_groups_expiration':
					$user['suremembers_access_groups_expiration'] = sanitize_text_field( $cell );
					break;
			}
		}

		// Check if the username already exists.
		$exists = username_exists( $user['user_login'] );

		if ( $exists ) {
			wp_send_json_error(
				array(
					'message' => sprintf(
						/* translators:  %s The username of the user. */
						__( 'Username %s already exists.', 'suremembers' ),
						sanitize_text_field( $user['user_login'] )
					),
				)
			);
		}

		// Insert or update the user accordingly.
		$user_id = wp_insert_user( $user );
		if ( is_wp_error( $user_id ) ) {
			wp_send_json_error(
				array(
					'message' => sprintf(
						/* translators:  %1$s: The user's username and %2$s: the error message. */
						__( 'Username %1$s is not imported due to error occurred. Error: %2$s', 'suremembers' ),
						sanitize_text_field( $user['user_login'] ),
						$user_id->get_error_message()
					),
				)
			);
		}

		// Grant access groups if provided.
		if ( ! empty( $user['suremembers_access_groups'] ) ) {
			$get_access_groups = Access_Groups::get_active();
			$strings           = explode( ',', $user['suremembers_access_groups'] );

			foreach ( $strings as $group_id ) {

				// Set the default expiry date to blank.
				$expiration_date = array();

				if ( in_array( intval( $group_id ), array_keys( $get_access_groups ), true ) ) {

					$posted_expiration_date = ! empty( $user['suremembers_access_groups_expiration'] ) ? sanitize_text_field( $user['suremembers_access_groups_expiration'] ) : '';

					// Update the access group expiry date for the user if the access group has the expiration type relative date.
					if ( ! empty( $posted_expiration_date ) && 'relative_date' === Access::get_access_group_expiry_data( $group_id, 'type' ) ) {
						$expiration_date[ $group_id ] = $posted_expiration_date;
					}

					Access::grant( intval( $user_id ), $group_id, 'suremembers', $expiration_date, false );
				}
			}
		}

		// Send onboarding email if requested.
		if ( $send_onboarding_email ) {
			$is_onboarding_email_sent_msg = false === Utils::send_email_notification( $user_id, 'user_onboarding' ) ? sprintf(
				/* translators:  %s: The username of the current user */
				__( 'Onboarding email to username %s not sent.', 'suremembers' ),
				sanitize_text_field( $user['user_login'] )
			) : '';
		}

		// Send reset email if requested.
		if ( $send_reset_pass_email ) {
			$email_template = Settings::get_setting( SUREMEMBERS_EMAIL_TEMPLATE_SETTINGS );
			if ( $email_template['enable_reset_password'] ) {
				$is_reset_pass_email_sent_msg = false === Utils::send_email_notification( $user_id, 'reset_password' ) ? sprintf(
					/* translators:  %s: The username of the processing user */
					__( 'Reset password email to username %s not sent.', 'suremembers' ),
					sanitize_text_field( $user['user_login'] )
				) : '';
			} else {
				wp_new_user_notification( intval( $user_id ), null, 'both' );
			}
		}


		// Return JSON response.
		if ( $user_id ) {
			wp_send_json_success(
				array(
					'email_sent' => $is_onboarding_email_sent_msg ? $is_onboarding_email_sent_msg : $is_reset_pass_email_sent_msg,
					'message'    => __( 'User Created Successfully', 'suremembers' ),
				)
			);
		}
	}

	/**
	 * Send the test email for testing.
	 *
	 * @Hooked - wp_ajax_suremembers_email_template_send_test_mail
	 *
	 * @return void
	 * @since 1.10.0
	 */
	public function send_email_template_test_mail() {

		// Verify the nonce.
		if ( ! check_ajax_referer( 'suremembers_global_settings_nonce', 'security', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'suremembers' ) ) );
		}

		// Check user permission.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Current user does not have the required permission.', 'suremembers' ) ) );
		}

		// Verify the presence of user data.
		if ( ! isset( $_POST['templateData'] ) ) {
			wp_send_json_error( array( 'message' => __( 'No email template data found.', 'suremembers' ) ) );
		}

		// Decode and sanitize the user data.
		$template_data = json_decode( sanitize_text_field( wp_unslash( $_POST['templateData'] ) ), true );
		if ( ! is_array( $template_data ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid template data.', 'suremembers' ) ) );
		}

		$email_template = isset( $template_data['email_type'] ) ? sanitize_text_field( $template_data['email_type'] ) : '';
		$send_to_email  = isset( $template_data['send_to_email'] ) ? sanitize_email( $template_data['send_to_email'] ) : '';

		if ( empty( $email_template ) || empty( $send_to_email ) ) {
			wp_send_json_error( array( 'message' => __( 'No email template type or email ID found.', 'suremembers' ) ) );
		}

		if ( Settings::send_email_template( $send_to_email, $email_template, array(), true ) ) {
			wp_send_json_success(
				array(
					'message' => __( 'Test Email Sent Successfully', 'suremembers' ),
				)
			);
		} else {
			wp_send_json_error( array( 'message' => __( 'No email notification is send.', 'suremembers' ) ) );
		}
	}


	/**
	 * Add Global settings menu page.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function add_settings_page() {
		add_submenu_page(
			'edit.php?post_type=' . SUREMEMBERS_POST_TYPE,
			__( 'SureMembers Settings', 'suremembers' ),
			__( 'Settings', 'suremembers' ),
			'manage_options',
			'suremembers_settings',
			[ $this, 'render_settings_page' ],
			50
		);
	}

	/**
	 * Settings page assets.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function settings_page_assets() {
		if ( ! $this->check_user_cap() ) {
			return;
		}

		$screen = get_current_screen();
		if ( is_null( $screen ) || SUREMEMBERS_POST_TYPE . '_page_suremembers_settings' !== $screen->id ) {
			return;
		}


		$asset_handle = 'globalsettings';

		$script_asset_path = SUREMEMBERS_DIR . 'admin/assets/build/' . $asset_handle . '.asset.php';
		$script_info       = file_exists( $script_asset_path )
			? include $script_asset_path
			: [
				'dependencies' => [],
				'version'      => SUREMEMBERS_VER,
			];
		wp_register_script( 'suremembers-' . $asset_handle, SUREMEMBERS_URL . 'admin/assets/build/' . $asset_handle . '.js', $script_info['dependencies'], SUREMEMBERS_VER, true );

		wp_localize_script(
			'suremembers-' . $asset_handle,
			'suremembers_settings',
			[
				'post_type'                 => SUREMEMBERS_POST_TYPE,
				'user_roles'                => $this->get_formated_user_roles(),
				'ajax_nonce'                => current_user_can( 'manage_options' ) ? wp_create_nonce( 'suremembers_global_settings_nonce' ) : '',
				'redirect_rules'            => $this->get_redirection_rules(),
				'login_form_settings'       => Settings::get_setting( SUREMEMBERS_LOGIN_FORM_SETTINGS ),
				'login_restrictions'        => Settings::get_setting( SUREMEMBERS_LOGIN_RESTRICTIONS_SETTINGS ),
				'email_settings'            => Settings::get_setting( SUREMEMBERS_EMAIL_TEMPLATE_SETTINGS ),
				'registration_access_group' => $this->get_selected_registration_access_group(),
				'custom_content'            => Settings::get_custom_content_data(),
				'woocommerce_active'        => function_exists( 'WC' ),
				'home_url'                  => home_url( '/' ),
				'license_status'            => Utils::is_license_activated(),
			]
		);

		wp_localize_script( 'suremembers-' . $asset_handle, 'scIcons', [ 'path' => SUREMEMBERS_URL . 'admin/assets/build/icon-assets' ] );
		wp_localize_script( 'suremembers-' . $asset_handle, 'suremembers_posts', [ 'list_url' => Access_Groups::get_admin_url() ] );

		wp_enqueue_script( 'suremembers-' . $asset_handle );

		wp_enqueue_style( 'suremembers-' . $asset_handle, SUREMEMBERS_URL . 'admin/assets/build/' . $asset_handle . '.css', [ 'wp-components' ], SUREMEMBERS_VER );
	}

	/**
	 * Get user roles array.
	 *
	 * @return array array of user roles.
	 * @since 1.0.0
	 */
	public function get_formated_user_roles() {
		global $wp_roles;

		if ( ! isset( $wp_roles ) ) {
			return [];
		}

		$available_roles_names = $wp_roles->get_names();
		$excluded_roles        = apply_filters( 'suremembers_settings_excluded_roles', [ 'administrator' => esc_html__( 'Administrator', 'suremembers' ) ] );

		$included_roles = array_diff( $available_roles_names, $excluded_roles );
		$formated_roles = Utils::get_react_select_format( $included_roles );

		return $formated_roles;
	}

	/**
	 * Return redirection rules
	 *
	 * @return array
	 * @since 1.3.0
	 */
	public function get_redirection_rules() {
		return Settings::get_setting( SUREMEMBERS_REDIRECT_RULES );
	}

	/**
	 * Get global settings AJAX.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public static function get_global_settings() {
		check_ajax_referer( 'suremembers_global_settings_nonce', 'security' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Current user does not have required permission.', 'suremembers' ) ] );
		}

		if ( ! isset( $_POST['setting_key'] ) || empty( $_POST['setting_key'] ) ) {
			wp_send_json_error( [ 'message' => __( 'Setting key is required to retrieve data.', 'suremembers' ) ] );
		}

		$key  = sanitize_text_field( $_POST['setting_key'] );
		$data = Settings::get_setting( $key );

		wp_send_json_success( $data );
	}

	/**
	 * Sanitize global settings array.
	 *
	 * @param array $settings Array of settings to sanitize.
	 * @param array $keys_to_wp_kses The keys which needs to allow some HTML tags to be saved.
	 * @return array Array of settings sanitized.
	 * @since 1.0.0
	 */
	public function sanitize_settings( $settings, $keys_to_wp_kses = array() ) {
		$response = [];
		foreach ( $settings as $key => $data ) {
			if ( is_array( $data ) ) {
				$val = $this->sanitize_settings( $data, $keys_to_wp_kses );
			} elseif ( is_bool( $data ) ) {
				$val = rest_sanitize_boolean( $data );
			} else {
				if ( ! empty( $keys_to_wp_kses ) && in_array( $key, $keys_to_wp_kses ) ) {
					$val = wp_kses_post( $data );
				} else {
					$val = sanitize_text_field( $data );
				}
			}
			$response[ $key ] = $val;
		}

		return $response;
	}

	/**
	 * Update global settings AJAX.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function update_global_settings() {
		check_ajax_referer( 'suremembers_global_settings_nonce', 'security' );

		if ( ! $this->check_user_cap() ) {
			wp_send_json_error( [ 'message' => __( 'Current user does not have required permission.', 'suremembers' ) ] );
		}

		if ( ! isset( $_POST['setting_key'] ) || empty( $_POST['setting_key'] ) ) {
			wp_send_json_error( [ 'message' => __( 'Setting key is required to save data.', 'suremembers' ) ] );
		}

		if ( ! isset( $_POST['settings_data'] ) || empty( $_POST['settings_data'] ) ) {
			wp_send_json_error( [ 'message' => __( 'Settings cannot be empty.', 'suremembers' ) ] );
		}

		// Ignoring sanitization as it it done below with custom function.
		$settings_data = json_decode( stripslashes_deep( $_POST['settings_data'] ), true, 512, JSON_OBJECT_AS_ARRAY ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$settings_data = is_array( $settings_data ) ? $this->sanitize_settings( $settings_data ) : [];
		$key           = sanitize_text_field( $_POST['setting_key'] );

		Settings::update_setting( $key, $settings_data );

		if ( SUREMEMBERS_CUSTOM_CONTENT === $key ) {
			$data = Settings::get_custom_content_data();
		} else {
			$data = Settings::get_setting( $key );
		}
		wp_send_json_success(
			[
				'message'       => __( 'Settings Saved', 'suremembers' ),
				'settings_data' => $data,
			]
		);
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function render_settings_page() {
		echo '<div id="suremembers-global-settings"></div>';
	}

	/**
	 * Check if user can manage settings.
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	private function check_user_cap() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Add User role.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function suremembers_add_user_roles() {
		check_ajax_referer( 'suremembers_global_settings_nonce', 'security' );

		if ( ! $this->check_user_cap() ) {
			wp_send_json_error( [ 'message' => __( 'Current user does not have required permission.', 'suremembers' ) ] );
		}

		if ( empty( $_POST['slug'] ) || empty( $_POST['title'] ) ) {
			wp_send_json_error();
		}

		$slug   = sanitize_text_field( $_POST['slug'] );
		$title  = sanitize_text_field( $_POST['title'] );
		$result = $this->create_user_role( $slug, $title );
		if ( ! empty( $result ) ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( [ 'message' => __( 'User role addition failed', 'suremembers' ) ] );
		}
	}

	/**
	 * Create user role.
	 *
	 * @param string $slug Slug will be the id of role.
	 * @param string $title User role title.
	 * @since 1.1.0
	 * @return \WP_Role|void
	 */
	private function create_user_role( $slug, $title ) {
		$result = add_role(
			$slug,
			$title
		);
		return $result;
	}

	/**
	 * Update user role.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function suremembers_update_user_roles() {
		check_ajax_referer( 'suremembers_global_settings_nonce', 'security' );

		if ( ! $this->check_user_cap() ) {
			wp_send_json_error( [ 'message' => __( 'Current user does not have required permission.', 'suremembers' ) ] );
		}

		if ( empty( $_POST['id'] ) || empty( $_POST['title'] ) || empty( $_POST['slug'] ) ) {
			wp_send_json_error();
		}
		$id    = sanitize_text_field( $_POST['id'] );
		$title = sanitize_text_field( $_POST['title'] );
		$slug  = sanitize_text_field( $_POST['slug'] );

		remove_role( $id );
		$result = $this->create_user_role( $slug, $title );
		if ( $result ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( [ 'message' => __( 'User role update failed', 'suremembers' ) ] );
		}
	}

	/**
	 * Remove role.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function suremembers_remove_user_roles() {
		check_ajax_referer( 'suremembers_global_settings_nonce', 'security' );

		if ( ! $this->check_user_cap() ) {
			wp_send_json_error( [ 'message' => __( 'Current user does not have required permission.', 'suremembers' ) ] );
		}

		if ( empty( $_POST['id'] ) ) {
			wp_send_json_error();
		}

		$id = sanitize_text_field( $_POST['id'] );
		// Replace three backslashes with a single backslash.
		$replaced_id = str_replace( '\\\\\\', '\\', $id );

		remove_role( $replaced_id );

		wp_send_json_success();
	}

	/**
	 * Updates redirection data
	 *
	 * @return void
	 * @since 1.3.0
	 */
	public function save_redirection_rules() {
		check_ajax_referer( 'suremembers_global_settings_nonce', 'security' );
		if ( ! $this->check_user_cap() ) {
			wp_send_json_error( [ 'message' => 'unauthorized access' ] );
		}

		$settings = [];
		if ( isset( $_POST['login_redirect'] ) ) {
			$settings['login_redirect'] = sanitize_text_field( $_POST['login_redirect'] );
		}

		if ( isset( $_POST['logout_redirect'] ) ) {
			$settings['logout_redirect'] = sanitize_text_field( $_POST['logout_redirect'] );
		}

		Settings::update_setting( SUREMEMBERS_REDIRECT_RULES, $settings );
		wp_send_json_success( [ 'message' => 'settings saved' ] );
	}

	/**
	 * Returns active access groups matching search term
	 *
	 * @return void
	 * @since 1.4.0
	 */
	public function search_access_groups() {
		check_ajax_referer( 'suremembers_global_settings_nonce', 'security' );
		if ( ! $this->check_user_cap() ) {
			wp_send_json_error( [ 'message' => 'unauthorized access' ] );
		}

		if ( empty( $_POST['search'] ) ) {
			wp_send_json_error( [ 'message' => __( 'No search result found', 'suremembers' ) ] );
		}

		$filter_args = [
			's' => ! empty( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '',
		];

		$access_groups = Access_Groups::get_active( $filter_args );
		$access_groups = Utils::get_react_select_format( $access_groups );
		wp_send_json_success( $access_groups );
	}

	/**
	 * Return selected access group in format required for react select, empty if not access group is selected.
	 *
	 * @return array
	 * @since 1.4.0
	 */
	private function get_selected_registration_access_group() {
		$settings = Settings::get_setting( SUREMEMBERS_ADMIN_SETTINGS );

		$access_group_ids = isset( $settings['registration_access_group'] ) ? Utils::sanitize_recursively( 'absint', $settings['registration_access_group'] ) : [];
		if ( empty( $access_group_ids ) ) {
			return [];
		}

		$access_groups = Access_Groups::get_active( [ 'post__in' => $access_group_ids ] );
		if ( empty( $access_groups ) ) {
			return [];
		}

		return Utils::get_react_select_format( $access_groups );
	}

	/**
	 * Add URLs.
	 *
	 * @since 1.7.1
	 * @return void
	 */
	public function suremembers_add_webhook_endpoint() {
		check_ajax_referer( 'suremembers_global_settings_nonce', 'security' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Current user does not have required permission.', 'suremembers' ) ] );
		}

		if ( empty( $_POST['reference'] ) ) {
			wp_send_json_error();
		}

		$reference        = sanitize_text_field( $_POST['reference'] );
		$saved_references = Settings::get_setting( SUREMEMBERS_WEBHOOK_ENDPOINTS );
		$hash             = substr( md5( strval( time() ) ), 5, 10 );
		$ref_data         = [
			'ref'  => $reference,
			'hash' => $hash,
		];
		if ( empty( $saved_references ) ) {
			$saved_references[] = $ref_data;
		} else {
			if ( ! in_array( $reference, array_column( $saved_references, 'ref' ), true ) ) {
				$saved_references = array_merge( $saved_references, [ $ref_data ] );
			} else {
				wp_send_json_error( [ 'message' => __( 'Duplicate Connection name. Connection name should be unique.', 'suremembers' ) ] );
			}
		}

		Settings::update_setting( SUREMEMBERS_WEBHOOK_ENDPOINTS, $saved_references );
		$response = self::generate_auth_keys( $saved_references );
		wp_send_json_success( $response );
	}

	/**
	 * Remove webhook.
	 *
	 * @since 1.7.1
	 * @return void
	 */
	public function suremembers_remove_webhook_endpoint() {
		check_ajax_referer( 'suremembers_global_settings_nonce', 'security' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Current user does not have required permission.', 'suremembers' ) ] );
		}

		if ( empty( $_POST['reference'] ) ) {
			wp_send_json_error();
		}
		$reference = sanitize_text_field( $_POST['reference'] );

		$saved_references = Settings::get_setting( SUREMEMBERS_WEBHOOK_ENDPOINTS );
		if ( empty( $saved_references ) ) {
			wp_send_json_error( [ 'message' => __( 'No such connection found.', 'suremembers' ) ] );
		} else {
			$key = array_search( $reference, array_column( $saved_references, 'ref' ), true );
			if ( false !== $key ) {
				unset( $saved_references[ $key ] );
				$saved_references = array_values( $saved_references );
				Settings::update_setting( SUREMEMBERS_WEBHOOK_ENDPOINTS, $saved_references );
				$response = self::generate_auth_keys( $saved_references );
				wp_send_json_success( $response );
			} else {
				wp_send_json_error( [ 'message' => __( 'No such connection found.', 'suremembers' ) ] );
			}
		}
	}

	/**
	 * Regenerate webhook token.
	 *
	 * @since 1.7.1
	 * @return void
	 */
	public function suremembers_regenerate_hash() {
		check_ajax_referer( 'suremembers_global_settings_nonce', 'security' );
		if ( empty( $_POST['reference'] ) ) {
			wp_send_json_error();
		}
		$reference = sanitize_text_field( $_POST['reference'] );

		$saved_references = Settings::get_setting( SUREMEMBERS_WEBHOOK_ENDPOINTS );
		if ( empty( $saved_references ) ) {
			wp_send_json_error( [ 'message' => __( 'No such connection found.', 'suremembers' ) ] );
		} else {
			$key = array_search( $reference, array_column( $saved_references, 'ref' ), true );
			if ( false !== $key ) {
				$saved_references[ $key ]['hash'] = substr( md5( strval( time() ) ), 5, 10 );
				Settings::update_setting( SUREMEMBERS_WEBHOOK_ENDPOINTS, $saved_references );
				wp_send_json_success( self::generate_auth_keys( $saved_references ) );
			} else {
				wp_send_json_error( [ 'message' => __( 'No such connection found.', 'suremembers' ) ] );
			}
		}
	}

	/**
	 * Adds authorization token to webhook endpoint data
	 *
	 * @param array $data webhook endpoints array.
	 * @return array
	 * @since 1.7.1
	 */
	public static function generate_auth_keys( $data ) {
		if ( ! is_array( $data ) || empty( $data ) ) {
			return [];
		}

		$data = Utils::sanitize_recursively( 'sanitize_text_field', $data );

		foreach ( $data as $key => $ref ) {
			$data[ $key ]['token'] = md5( $ref['ref'] . '-suremembers-token-' . $ref['hash'] );
			unset( $data[ $key ]['hash'] );
		}

		return $data;
	}

	/**
	 * License Activation AJAX
	 *
	 * @Hooked - wp_ajax_suremembers_license_activation
	 *
	 * @return void
	 * @since 1.8.1
	 */
	public static function suremembers_license_activation() {

		// Verify the nonce.
		if ( ! check_ajax_referer( 'suremembers_global_settings_nonce', 'security', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'suremembers' ) ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Current user does not have required permission.', 'suremembers' ) ) );
		}

		$result = false; // Default value.

		if ( ! empty( $_POST['key'] ) ) {
			$key    = sanitize_text_field( $_POST['key'] );
			$result = Utils::activate_license( $key );
		}

		if ( ! is_bool( $result ) ) {
			wp_send_json_error(
				array(
					'success' => false,
					'message' => $result->get_error_message(),
				)
			);
		}

		wp_send_json_success(
			array(
				'success' => true,
				'message' => __( 'License Activated Successfully', 'suremembers' ),
			)
		);
	}

	/**
	 * License Deactivation AJAX
	 *
	 * @Hooked - wp_ajax_suremembers_license_deactivation
	 *
	 * @return void
	 * @since 1.8.1
	 */
	public static function suremembers_license_deactivation() {

		// Verify the nonce.
		if ( ! check_ajax_referer( 'suremembers_global_settings_nonce', 'security', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'suremembers' ) ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Current user does not have required permission.', 'suremembers' ) ) );
		}
		$result = Utils::deactivate_license();

		if ( ! is_bool( $result ) ) {
			wp_send_json_error(
				array(
					'success' => false,
					'message' => $result->get_error_message(),
				)
			);
		}

		wp_send_json_success(
			array(
				'success' => true,
				'message' => __( 'License Deactivated Successfully', 'suremembers' ),
			)
		);
	}

}
