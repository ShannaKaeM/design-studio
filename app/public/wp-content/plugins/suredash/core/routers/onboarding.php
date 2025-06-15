<?php
/**
 * Post Router Initialize.
 *
 * @package SureDashboard
 */

namespace SureDashboard\Core\Routers;

use SureDashboard\Inc\Traits\Get_Instance;
use SureDashboard\Inc\Traits\Rest_Errors;
use SureDashboard\Inc\Utils\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Onboarding.
 */
class Onboarding {
	use Get_Instance;
	use Rest_Errors;

	/**
	 * Save the onboarding settings.
	 *
	 * @param \WP_REST_Request $request  Request object.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function skip_onboarding( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		update_option( 'suredash_onboarding_skipped', 'yes' );

		wp_send_json_success( [ 'message' => __( 'Onboarding skipped successfully.', 'suredash' ) ] );
	}

	/**
	 * Save the onboarding settings.
	 *
	 * @param \WP_REST_Request $request  Request object.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function complete_onboarding( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		update_option( 'suredash_onboarding_completed', 'yes' );

		wp_send_json_success( [ 'message' => __( 'Onboarding completed successfully.', 'suredash' ) ] );
	}

	/**
	 * Save the onboarding settings.
	 *
	 * @param \WP_REST_Request $request  Request object.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function process_onboarding( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$action = ! empty( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';

		switch ( $action ) {
			case 'setup_community':
				$portal_name      = ! empty( $_POST['portal_name'] ) ? sanitize_text_field( wp_unslash( $_POST['portal_name'] ) ) : '';
				$hidden_community = ! empty( $_POST['hidden_community'] ) && sanitize_text_field( $_POST['hidden_community'] ) === 'on' ? true : false;
				$enable_feeds     = ! empty( $_POST['enable_feeds'] ) && sanitize_text_field( $_POST['enable_feeds'] ) === 'on' ? true : false;

				$settings                     = Settings::get_suredash_settings();
				$settings['portal_name']      = $portal_name;
				$settings['hidden_community'] = $hidden_community;
				$settings['enable_feeds']     = $enable_feeds;
				update_option( SUREDASHBOARD_SETTINGS, $settings );
				break;
			case 'setup_portal_spaces':
				$enable_feed_cpt   = ! empty( $_POST['feed_cpt'] ) && $_POST['feed_cpt'] === 'on' ? true : false;
				$enable_course_cpt = ! empty( $_POST['course_cpt'] ) && $_POST['course_cpt'] === 'on' ? true : false;

				$settings             = Settings::get_suredash_settings();
				$settings['feed_cpt'] = $enable_feed_cpt;

				if ( suredash_is_pro_active() ) {
					$settings['course_cpt'] = $enable_course_cpt;
				}

				update_option( SUREDASHBOARD_SETTINGS, $settings );
				$this->setup_dummy_space_groups( $settings );
				break;
			case 'plugin_integrations':
				$required_plugins_list = ! empty( $_POST['required_plugins'] ) ? json_decode( stripslashes( sanitize_text_field($_POST['required_plugins']) ), true ) : []; // phpcs:ignore
				$required_plugins      = $this->get_required_plugins( $required_plugins_list );
				wp_send_json_success( [ 'required_plugins' => $required_plugins ] );
				break; // @phpstan-ignore-line
			case 'optin':
				$this->subscribe_to_suredash();
				update_option( 'suredash_onboarding_completed', 'yes' );
				break;
			default:
				wp_send_json_success();
		}

		wp_send_json_success( [ 'message' => __( 'Onboarding data updated successfully.', 'suredash' ) ] );
	}

	/**
	 * Setup dummy space groups.
	 *
	 * @param array<string, bool> $settings Settings.
	 * @since 1.0.0
	 * @return void
	 */
	public function setup_dummy_space_groups( $settings ): void {

		$space_groups = [
			[
				'name'        => 'Community Hub',
				'description' => 'A shared space to post updates, spark discussions, and grow together as a community.',
				'spaces'      => [
					[
						'name'        => 'Updates, News & Interactions',
						'description' => 'A space to create, manage, and share posts. Keep your community informed and engaged!',
						'integration' => 'posts_discussion',
					],
				],
			],
			[
				'name'        => 'Learning & Development',
				'description' => 'A space to create, manage, and share courses. Empower your community with the knowledge they need to succeed!',
				'spaces'      => [
					[
						'name'        => 'Courses',
						'description' => 'A space to create, manage, and share courses. Empower your community with the knowledge they need to succeed!',
						'integration' => 'course',
					],
				],
			],
		];

		if ( isset( $settings['feed_cpt'] ) && ! $settings['feed_cpt'] ) {
			unset( $space_groups[0] );
		}

		if ( isset( $settings['course_cpt'] ) && ! $settings['course_cpt'] && suredash_is_pro_active() ) {
			unset( $space_groups[1] );
		}

		foreach ( $space_groups as $space_group ) {
			$space_group_id = 0;
			if ( is_callable( [ Backend::get_instance(), 'create_portal_group' ] ) ) {
				$space_group_id = Backend::get_instance()->create_portal_group( $space_group['name'], $space_group['description'] );
			}

			if ( $space_group_id ) {
				foreach ( $space_group['spaces'] as $space ) {
					$post_attr = [
						'post_title'  => $space['name'],
						'post_status' => 'publish',
						'post_type'   => SUREDASHBOARD_POST_TYPE,
						'post_author' => get_current_user_id(),
					];

					$item_id = sd_wp_insert_post(
						$post_attr,
					);

					if ( $item_id && is_int( $item_id ) ) {
						// Instead of using 'tax_input' used 'wp_set_post_terms', because ‘tax_input’ requires ‘assign_terms’ access to the taxonomy.
						wp_set_post_terms( $item_id, [ absint( $space_group_id ) ], SUREDASHBOARD_TAXONOMY );

						sd_update_post_meta( absint( $item_id ), 'integration', $space['integration'] );
						if ( is_callable( [ Backend::get_instance(), 'update_link_order_term' ] ) ) {
							Backend::get_instance()->update_link_order_term( $item_id, $space_group_id );
						}
					}
				}
			}
		}
	}

	/**
	 * Subscribe to SureDash.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function subscribe_to_suredash(): void {

		// phpcs:disable WordPress.Security.NonceVerification
		$user_email   = isset( $_POST['user_email'] ) ? sanitize_email( $_POST['user_email'] ) : '';
		$first_name   = isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
		$last_name    = isset( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '';
		$is_subscribe = isset( $_POST['subscribe_to_newsletter'] ) && sanitize_text_field( $_POST['subscribe_to_newsletter'] ) === 'on' ? true : false;
		$share_data   = isset( $_POST['share_non_sensitive_data'] ) && sanitize_text_field( $_POST['share_non_sensitive_data'] ) === 'on' ? true : false;
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		// Enable BSF analytics optin.
		if ( $share_data ) {
			update_option( '$suredash_analytics_optin', 'yes' );
		}

		if ( ! $is_subscribe ) {
			return;
		}

		$url  = 'https://websitedemos.net/wp-json/suredash/v1/subscribe/';
		$args = [
			'body' => [
				'EMAIL'      => $user_email,
				'FIRST_NAME' => $first_name,
				'LAST_NAME'  => $last_name,
			],
		];

		$args = [
			'body' => $args,
		];

		$response = wp_safe_remote_post( $url, $args );

		if ( ! is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) === 200 ) {
			$response = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( isset( $response['success'] ) && $response['success'] ) {
				update_user_meta( get_current_user_ID(), 'suredash-subscribed', 'yes' );
			}
		}
		wp_send_json_success( $response );
	}

	/**
	 * Get the list of required plugins.
	 *
	 * @param array<array<string, string>> $required_plugins_list List of required plugins.
	 *
	 * @since 1.0.0
	 * @return array<string, array<int<0, max>, array<string, string>>>
	 */
	public function get_required_plugins( $required_plugins_list ): array {

		$required_plugins = [
			'installed'     => [],
			'not_installed' => [],
			'inactive'      => [],
		];

		if ( is_array( $required_plugins_list ) && ! empty( $required_plugins_list ) ) {
			foreach ( $required_plugins_list as $plugin ) {

				if ( $this->is_plugin_installed( $plugin ) ) {
					if ( $this->check_is_plugin_active( $plugin ) ) {
						$required_plugins['installed'][] = $plugin;
					} else {
						$required_plugins['inactive'][] = $plugin;
					}
				} else {
					$required_plugins['not_installed'][] = $plugin;
				}
			}
		}

		return $required_plugins;
	}

	/**
	 * Check if a plugin is installed.
	 *
	 * @param array<string, string> $plugin_path Plugin path.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_plugin_installed( $plugin_path ): bool {

		if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin_path['init'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if a plugin is active.
	 *
	 * @param array<string, string> $plugin_path Plugin path.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function check_is_plugin_active( $plugin_path ): bool {

		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( is_plugin_active( $plugin_path['init'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Activate plugin.
	 *
	 * @param \WP_REST_Request $request  Request object.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function activate_plugin( $request ): void {

		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$plugin_path = ! empty( $_POST['plugin_init'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin_init'] ) ) : '';
		$plugin_slug = ! empty( $_POST['plugin_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin_slug'] ) ) : '';

		if ( empty( $plugin_path ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid plugin.', 'suredash' ) ] );
		}

		if ( ! function_exists( 'activate_plugin' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$activate = activate_plugin( $plugin_path );

		do_action( 'suredash_after_plugin_activation', $plugin_path, $plugin_slug );

		if ( is_wp_error( $activate ) ) {
			wp_send_json_error( [ 'message' => $activate->get_error_message() ] );
		}

		wp_send_json_success( [ 'message' => __( 'Plugin activated successfully.', 'suredash' ) ] );
	}
}
