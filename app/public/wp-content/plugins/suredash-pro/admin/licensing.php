<?php
/**
 * Licensing Class
 *
 * This class handles all licensing related stuff.
 *
 * @package Suredash Pro
 * @since 0.0.1-alpha.3
 */

namespace SureDashboardPro\Admin;

use SureDashboardPro\Inc\Traits\Get_Instance;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Licensing handler class.
 *
 * @since 0.0.1-alpha.3
 */
class Licensing {
	use Get_Instance;

	/**
	 * Error messages.
	 *
	 * @var array
	 */
	public $error_messages = [];

	/**
	 * Class constructor
	 *
	 * @since 0.0.1-alpha.3
	 * @return void
	 */
	public function __construct() {
		if ( ! class_exists( 'SureCart\Licensing\Client' ) ) {
			require_once SUREDASHBOARD_PRO_DIR . '/licensing/Client.php';
		}

		$this->set_error_messages();

		add_action( 'init', self::class . '::init_licensing' );
		add_action( 'admin_notices', [ $this, 'license_activation_notice' ] );

		add_action( 'wp_ajax_suredash_activate_license', [ $this, 'activate_license' ] );
		add_action( 'wp_ajax_suredash_deactivate_license', [ $this, 'deactivate_license' ] );
	}

	/**
	 * Licensing setup.
	 * Creates a client object for SureCart licensing.
	 *
	 * @since 0.0.1-alpha.3
	 * @return \SureCart\Licensing\Client
	 */
	public static function licensing_setup() {
		$client = new \SureCart\Licensing\Client( SUREDASH_PRO_PRODUCT, SUREDASH_PRO_PUBLIC_TOKEN, SUREDASH_PRO_FILE );
		$client->set_textdomain( 'suredash-pro' );
		return $client;
	}

	/**
	 * Licensing setup.
	 * Creates a client object for SureCart licensing.
	 *
	 * @since 0.0.1-alpha.3
	 * @return void
	 */
	public static function init_licensing(): void {
		self::licensing_setup();
	}

	/**
	 * Activate license
	 *
	 * @hooked wp_ajax_suredash_activate_license
	 * @since 0.0.1-alpha.3
	 * @return void
	 */
	public function activate_license(): void {
		if ( ! check_ajax_referer( 'suredash_pro_licensing_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => $this->error_messages['nonce'] ] );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => $this->error_messages['permission'] ] );
		}

		$license_key = ! empty( $_POST['license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['license_key'] ) ) : '';

		if ( empty( $license_key ) ) {
			wp_send_json_error( [ 'message' => $this->error_messages['invalid_license'] ] );
		}

		$client = self::licensing_setup();

		$get_license = $client->license()->retrieve( $license_key );

		if ( ! empty( $get_license->product ) && $get_license->product !== SUREDASH_PRO_PRODUCT_ID ) {
			wp_send_json_error( [ 'message' => __( 'Incorrect License key for this product.', 'suredash-pro' ) ] );
		}

		$response = $client->license()->activate( $license_key );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( [ 'message' => $response->get_error_message() ] );
		}

		// Update the license status in the database after activating the license.
		update_option( 'suredash_pro_license_status', 'licensed' );
		wp_send_json_success( [ 'message' => __( 'License activated successfully.', 'suredash-pro' ) ] );
	}

	/**
	 * Deactivate license.
	 *
	 * @hooked wp_ajax_suredash_deactivate_license
	 * @since 0.0.1-alpha.3
	 * @return void
	 */
	public function deactivate_license(): void {
		if ( ! check_ajax_referer( 'suredash_pro_licensing_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => $this->error_messages['nonce'] ] );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => $this->error_messages['permission'] ] );
		}

		$client = self::licensing_setup();

		$response = $client->license()->deactivate();

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( [ 'message' => $response->get_error_message() ] );
		}

		// Update the license status in the database after deactivating the license.
		update_option( 'suredash_pro_license_status', 'unlicensed' );
		wp_send_json_success( [ 'message' => __( 'License deactivated successfully.', 'suredash-pro' ) ] );
	}

	/**
	 * Checks if license is active.
	 *
	 * @since 0.0.1-alpha.3
	 * @return bool
	 */
	public static function is_license_active() {
		$client = self::licensing_setup();

		// getting license key from settings.
		// We want to determine if the saved license key is valid for this product.
		$license_key = $client->settings()->license_key;

		if ( empty( $license_key ) ) {
			return false;
		}

		// retrieve the license from the server.
		$get_license = $client->license()->retrieve( $license_key );

		// if the license is not valid for this product, return false.
		if ( ! empty( $get_license->product ) && $get_license->product !== SUREDASH_PRO_PRODUCT_ID ) {
			return false;
		}

		$activation = $client->settings()->get_activation();
		return ! empty( $activation->id );
	}

	/**
	 * Display admin notice to activate license
	 *
	 * @since 1.0.0
	 */
	public function license_activation_notice(): void {
		$screen    = get_current_screen();
		$screen_id = ! empty( $screen->id ) ? $screen->id : '';

		if ( $screen_id !== 'plugins' ) {
			return;
		}

		if ( ! current_user_can( 'activate_plugins' ) || ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		if ( ! is_suredash_installed() ) {
			return;
		}

		$license_status = get_option( 'suredash_pro_license_status', '' );
		/**
		 * If the license status is not set then get the license status and update the option accordingly.
		 * This will be executed only once. Subsequently, the option status is updated by the licensing class on license activation or deactivation.
		 */
		if ( empty( $license_status ) ) {
			$license_status = Licensing::is_license_active() ? 'licensed' : 'unlicensed';
			update_option( 'suredash_pro_license_status', $license_status );
		}

		if ( $license_status === 'licensed' ) {
			return;
		}

		$cta_url = admin_url( 'admin.php?page=' . SUREDASHBOARD_SETTINGS );

		/* translators: %s: html tags */
		$notice_message = sprintf( __( 'Please %1$s activate %2$s your copy of %4$s %3$s %5$s to get new features, access support, receive update notifications, and more.', 'suredash-pro' ), '<a href="' . esc_url( $cta_url ) . '">', '</a>', SUREDASH_PRO_PRODUCT, '<em>', '</em>' );

		\Astra_Notices::add_notice(
			[
				'id'                         => 'suredash-pro-activation-notice',
				'type'                       => 'error',
				/* translators: %s: html tags */
				'message'                    => sprintf(
					'<div class="notice-content" style="margin: 0;">
						%1$s
					</div>',
					$notice_message
				),
				'repeat-notice-after'        => false,
				'priority'                   => 10,
				'display-with-other-notices' => true,
				'is_dismissible'             => false,
			]
		);
	}

	/**
	 * Set error messages.
	 *
	 * @since 0.0.1-alpha.3
	 * @return void
	 */
	private function set_error_messages(): void {
		$this->error_messages = [
			'nonce'           => __( 'Invalid nonce.', 'suredash-pro' ),
			'permission'      => __( 'You do not have permission to activate license.', 'suredash-pro' ),
			'invalid_license' => __( 'Please enter a valid license key', 'suredash-pro' ),
		];
	}
}
