<?php
/**
 * Portal Admin Setup
 *
 * This class will holds the code related to the admin area modification.
 *
 * @package SureDash
 *
 * @since 1.0.0
 */

namespace SureDashboard\Admin;

use SureDashboard\Inc\Traits\Get_Instance;
use SureDashboard\Inc\Utils\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Setup
 *
 * @since 1.0.0
 */
class Setup {
	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->initialize_hooks();
	}

	/**
	 * Function to load the admin area actions.
	 *
	 * @since 1.0.0
	 */
	public function initialize_hooks(): void {
		add_filter( 'plugin_action_links_' . SUREDASHBOARD_BASE, [ $this, 'add_action_links' ] );
		add_action( 'admin_bar_menu', [ $this, 'dashboard_toolbar_menu' ], 32 );
		add_filter( 'display_post_states', [ $this, 'show_custom_post_statuses' ] );
	}

	/**
	 * Add Settings Link in Plugin page.
	 *
	 * @param array<int, string> $links - Array of links.
	 *
	 * @return array<int, string>
	 */
	public function add_action_links( array $links ): array {
		$url = admin_url() . 'admin.php?page=portal';
		return array_merge(
			[
				'<a href="' . esc_url( $url ) . '">' . __( 'Settings', 'suredash' ) . '</a>',
			],
			$links
		);
	}

	/**
	 * Function to show custom post statuses.
	 *
	 * @since 1.0.0
	 *
	 * @param array<int, string> $post_states Array of post states.
	 *
	 * @return array<int|string, string>
	 */
	public function show_custom_post_statuses( array $post_states ): array {
		global $post;

		if ( ! is_object( $post ) || ! isset( $post->post_content ) ) {
			return $post_states;
		}

		$login_page    = Helper::get_option( 'login_page' );
		$login_page_id = is_array( $login_page ) && ! empty( $login_page['value'] ) ? absint( $login_page['value'] ) : 0;

		$register_page    = Helper::get_option( 'register_page' );
		$register_page_id = is_array( $register_page ) && ! empty( $register_page['value'] ) ? absint( $register_page['value'] ) : 0;

		if ( ! isset( $post->ID ) ) {
			return $post_states;
		}

		// Check if the current post is the login page.
		if ( $post->ID === $login_page_id ) {
			$post_states['portal_login'] = __( 'Portal Login', 'suredash' );
		}

		// Check if the current post is the register page.
		if ( $post->ID === $register_page_id ) {
			$post_states['portal_register'] = __( 'Portal Register', 'suredash' );
		}

		return $post_states;
	}

	/**
	 * Add Docs link in admin bar.
	 *
	 * @param  object $admin_bar WP Admin Bar.
	 *
	 * @since 1.0.0
	 */
	public function dashboard_toolbar_menu( object $admin_bar ): void {
		if ( ! is_admin() || ! is_admin_bar_showing() ) {
			return;
		}

		// Showcase for site user or super admin.
		if ( ! is_user_member_of_blog() && ! is_super_admin() ) {
			return;
		}

		if ( ! is_a( $admin_bar, 'WP_Admin_Bar' ) ) {
			return;
		}

		$admin_bar->add_node(
			[
				'parent' => 'site-name',
				'id'     => 'view-portal',
				'title'  => __( 'Visit Portal', 'suredash' ),
				'href'   => '/' . SUREDASHBOARD_SLUG . '/',
			]
		);
	}
}
