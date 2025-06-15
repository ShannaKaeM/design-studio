<?php
/**
 * Gutenberg integrations class.
 *
 * @package suremembers
 * @since 1.0.0
 */

namespace SureMembers\Admin;

use SureMembers\Inc\Settings;
use SureMembers\Inc\Traits\Get_Instance;
use SureMembers\Inc\Access_Groups;
use SureMembers\Inc\Restricted;

/**
 * Gutenberg integration handler class.
 *
 * @since 1.0.0
 */
class Gutenberg_Admin_Bar {

	use Get_Instance;

	/**
	 * Class Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'enqueue_block_editor_assets', [ $this, 'editor_bar_scripts' ] );
		// Ajax calls for admin bar.
		add_action( 'wp_ajax_suremembers_edit_get_active_access_groups', [ $this, 'get_active_access_groups' ] );
	}

	/**
	 * Add JS for edit bar Gutenberg.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function editor_bar_scripts() {
		global $post;

		// Check if current user can create access groups.
		if ( ! current_user_can( 'administrator' ) ) {
			return;
		}

		if ( ! $this->is_icon_enabled() ) {
			return;
		}

		if ( ! isset( $post->ID ) ) {
			return;
		}
		$script_name       = 'suremembers-blockedit';
		$script_asset_path = SUREMEMBERS_DIR . 'admin/assets/build/' . $script_name . '.asset.php';
		$script_info       = file_exists( $script_asset_path )
			? include $script_asset_path
			: array(
				'dependencies' => [],
				'version'      => SUREMEMBERS_VER,
			);
		wp_register_script( $script_name, SUREMEMBERS_URL . 'admin/assets/build/blockedit.js', $script_info['dependencies'], SUREMEMBERS_VER, true );

		$option = array(
			'include'         => SUREMEMBERS_PLAN_INCLUDE,
			'exclusion'       => SUREMEMBERS_PLAN_EXCLUDE,
			'priority'        => SUREMEMBERS_PLAN_PRIORITY,
			'current_post_id' => $post->ID,
		);

		wp_localize_script(
			$script_name,
			'suremembers_edit',
			[
				'ajax_url'       => admin_url( 'admin-ajax.php' ),
				'all_access_url' => Access_Groups::get_admin_url( [ 'suremembers_view' => 'iframe' ] ),
				'new_access_url' => Access_Groups::get_admin_url(
					[
						'page'             => 'suremembers_rules',
						'suremembers_view' => 'iframe',
					]
				),
				'nonce'          => current_user_can( 'manage_options' ) ? wp_create_nonce( 'suremembers_edit_get_access_groups' ) : '',
			]
		);
		wp_enqueue_script( $script_name );
		wp_enqueue_style( 'suremembers-admin-bar-script', SUREMEMBERS_URL . 'admin/assets/build/adminbar.css', array( 'wp-components' ), SUREMEMBERS_VER );
	}

	/**
	 * Get active access groups
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function get_active_access_groups() {
		check_ajax_referer( 'suremembers_edit_get_access_groups', 'security' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Current user does not have required permission.', 'suremembers' ) ] );
		}

		$post_id           = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : null;
		$current_post_type = isset( $_POST['current_post_type'] ) ? sanitize_text_field( $_POST['current_post_type'] ) : null;

		if ( ! $post_id ) {
			wp_send_json_error( [ 'message' => __( 'Post ID missing', 'suremembers' ) ] );
		}

		if ( ! $current_post_type ) {
			wp_send_json_error( [ 'message' => __( 'Post Type missing', 'suremembers' ) ] );
		}

		$option = array(
			'include'           => SUREMEMBERS_PLAN_INCLUDE,
			'exclusion'         => SUREMEMBERS_PLAN_EXCLUDE,
			'priority'          => SUREMEMBERS_PLAN_PRIORITY,
			'current_post_id'   => absint( $post_id ),
			'current_post_type' => $current_post_type,
			'current_page_type' => 'is_singular',
		);

		$access_groups        = Restricted::by_access_groups( SUREMEMBERS_POST_TYPE, $option );
		$active_access_groups = [];

		if ( ! empty( $access_groups && is_array( $access_groups ) ) ) {
			if ( isset( $access_groups['wsm_access_group'] ) && ! empty( $access_groups['wsm_access_group'] ) ) {
				foreach ( $access_groups['wsm_access_group'] as $id => $plan ) {
					$access_group           = get_post( $id );
					$post_title             = isset( $access_group->post_title ) ? $access_group->post_title : '';
					$active_access_groups[] = [
						'id'    => $id,
						'title' => $post_title,
						'href'  => Access_Groups::get_admin_url(
							[
								'page'    => 'suremembers_rules',
								'post_id' => $id,
							]
						),
						'meta'  => [
							'title' => __( 'Edit Access Group ', 'suremembers' ) . $post_title,
							'class' => 'suremembers_adbar_itm',
						],
					];
				}
			}
		}

		wp_send_json_success(
			[
				'message' => __( 'Access groups found', 'suremembers' ),
				'data'    => $active_access_groups,
			]
		);
	}

	/**
	 * Check if icon display is enabled in settings page.
	 *
	 * @return boolean
	 */
	public function is_icon_enabled() {
		$get_settings = Settings::get_setting( 'suremembers_admin_settings' );
		return isset( $get_settings['enable_gutenberg_icon'] ) && $get_settings['enable_gutenberg_icon'];
	}

}
