<?php
/**
 * Admin menu.
 *
 * @package SureDashboardPro
 * @since 1.0.0
 */

namespace SureDashboardPro\Admin;

use SureDashboardPro\Inc\Traits\Get_Instance;

/**
 * Menu
 *
 * @since 1.0.0
 */
class Menu {
	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter( 'portal_localized_admin_data', [ $this, 'add_admin_data' ] );
		add_filter( 'suredash_integrations', [ $this, 'add_premium_integrations' ] );
	}

	/**
	 * Add admin data for SureDash Pro.
	 *
	 * @param array $data Data.
	 * @since 1.0.0
	 * @return array
	 */
	public function add_admin_data( $data ) {
		$data['pro_plugin_name']     = defined( 'SUREDASH_PRO_PRODUCT' ) ? SUREDASH_PRO_PRODUCT : '';
		$data['license_status']      = get_option( 'suredash_pro_license_status' );
		$data['pro_licensing_nonce'] = wp_create_nonce( 'suredash_pro_licensing_nonce' );
		return $data;
	}

	/**
	 * Add premium integrations
	 *
	 * @param array $integrations Integrations.
	 * @since 1.0.0
	 * @return array
	 */
	public function add_premium_integrations( $integrations ) {
		$integrations['course'] = __( 'Course', 'suredash-pro' );
		return $integrations;
	}
}
