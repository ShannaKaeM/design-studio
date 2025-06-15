<?php
/**
 * Frontend Renderer.
 *
 * @package SureDashboardPro
 * @since 1.0.0
 */

namespace SureDashboardPro\Core;

use SureDashboardPro\Inc\Traits\Enqueue;
use SureDashboardPro\Inc\Traits\Get_Instance;
use SureDashboardPro\Inc\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Renderer setup
 *
 * @since 1.0.0
 */
class Renderer {
	use Enqueue;
	use Get_Instance;

	/**
	 *  Constructor
	 */
	public function __construct() {
		$this->init_actions();

		add_action( 'suredash_enqueue_scripts', [ $this, 'suredash_enqueue_scripts' ] );
		add_action( 'body_class', [ $this, 'add_body_class' ] );
	}

	/**
	 * Add body class.
	 *
	 * @param array $classes body classes.
	 * @since 1.0.0
	 * @return array
	 */
	public function add_body_class( $classes ) {
		// Assign version class for reference.
		$classes[] = 'suredash-pro-' . SUREDASH_PRO_VER;

		return $classes;
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function suredash_enqueue_scripts(): void {
		// Course assets.
		Assets::get_instance()->course_assets();
	}

	/**
	 * Initialize actions
	 *
	 * @since 1.0.0
	 */
	public function init_actions(): void {
		$hide_branding = boolval( Helper::get_option( 'hide_branding' ) );
		if ( $hide_branding ) {
			add_filter( 'suredash_footer_brand_output', '__return_empty_string' );
		}

		add_filter( 'portal_localized_frontend_data', [ $this, 'update_pro_localized_frontend_data' ] );
	}

	/**
	 * Update localized data.
	 *
	 * @param array<string, mixed> $localized_data Localized data.
	 * @since 1.0.0-rc.3
	 * @return array<string, mixed>
	 */
	public function update_pro_localized_frontend_data( $localized_data ): array {
		$localized_data['notification_messages'] = $localized_data['notification_messages'] ?? [];

		if ( ! is_array( $localized_data ) ) {
			return $localized_data;
		}

		if ( ! is_array( $localized_data['notification_messages'] ) ) {
			$localized_data['notification_messages'] = [];
		}
		$localized_data['notification_messages']['lesson_complete'] = __( 'Lesson marked as complete.', 'suredash-pro' );

		return $localized_data;
	}
}
