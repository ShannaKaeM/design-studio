<?php
/**
 * Content restrictions.
 *
 * @package suremembers
 * @since 1.0.0
 */

namespace SureMembers\Admin;

use SureMembers\Inc\Traits\Get_Instance;
use SureMembers\Inc\Access_Groups;

/**
 * Content restrictions.
 *
 * @since 1.0.0
 */
class Restrictions {

	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'block_restriction_enqueue_scripts' ] );
		add_action( 'wp_ajax_suremembers_postmeta_search', [ $this, 'get_access_group_search' ] );
	}

	/**
	 * Enqueue scripts and style admin editor.
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function block_restriction_enqueue_scripts() {
		$screen          = get_current_screen();
		$allowed_screens = apply_filters( 'suremembers_blocks_restriction_allowed_screens', [ 'post', 'widgets', 'customize' ] );
		if ( ! isset( $screen->base ) || ! \in_array( $screen->base, $allowed_screens, true ) ) {
			return;
		}
		$script_dep_path = SUREMEMBERS_DIR . 'admin/assets/restriction-build/restrict_block.asset.php';
		$script_info     = file_exists( $script_dep_path ) ? include $script_dep_path : [
			'dependencies' => [],
			'version'      => SUREMEMBERS_VER,
		];
		wp_register_script(
			'sure-cart-editor-script',
			SUREMEMBERS_URL . 'admin/assets/restriction-build/restrict_block.js',
			array_merge(
				$script_info['dependencies'],
				[
					'wp-blocks',
					'wp-element',
					'wp-editor',
					'wp-components',
					'wp-data',
					'wp-i18n',
				]
			),
			SUREMEMBERS_VER,
			true
		);
		wp_register_style( 'suremembers-block-restriction-style', SUREMEMBERS_URL . 'admin/assets/restriction-build/restrict_block.css', [], SUREMEMBERS_VER );
		wp_enqueue_style( 'suremembers-block-restriction-style' );

		wp_localize_script(
			'sure-cart-editor-script',
			'suremembers_global',
			$this->localize_data()
		);
		wp_enqueue_script( 'sure-cart-editor-script' );
	}

	/**
	 * Access plan.
	 *
	 * @return array
	 * @since  1.0.0
	 */
	public function localize_data() {
		$get_access_groups = Access_Groups::get_active();
		$return            = [];
		foreach ( $get_access_groups as $key => $value ) {
			$return[] = [
				'id'    => $key,
				'title' => $value,
			];
		}
		if ( ! empty( $return ) ) {
			$localize_array['ajax_url']                      = admin_url( 'admin-ajax.php' );
			$localize_array['sure_member_access_groups']     = $return;
			$localize_array['suremembers_postmeta_security'] = current_user_can( 'edit_posts' ) ? wp_create_nonce( 'suremembers_postmeta_security' ) : '';
			return $localize_array;
		}
		$localize_array['sure_member_create_group'] = Access_Groups::get_admin_url( [ 'page' => 'suremembers_rules' ] );
		return $localize_array;
	}

	/**
	 * Get access group.
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function get_access_group_search() {
		$check_request_elementor = isset( $_POST['elementor_security'] ) ? true : false;

		if ( $check_request_elementor ) {
			check_ajax_referer( 'suremembers_erb_security', 'elementor_security' );
		} else {
			check_ajax_referer( 'suremembers_postmeta_security', 'security' );
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => __( 'Current user does not have required permission.', 'suremembers' ) ] );
		}

		$access_group_args = [
			'numberposts' => 10,
		];
		if ( ! empty( $_POST['selected_ids'] ) ) {
			$exclude_ids = explode( ',', sanitize_text_field( $_POST['selected_ids'] ) );
			// Ignored in favor of functionality.
			$access_group_args['exclude'] = $exclude_ids; //phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
		}
		// For search title.
		if ( ! empty( $_POST['search_title'] ) ) {
			$access_group_args['s'] = sanitize_text_field( $_POST['search_title'] );
		}
		$access_group_array = [];

		if ( ! empty( $_POST['include_ids'] ) ) {
			$include_ids         = explode( ',', sanitize_text_field( $_POST['include_ids'] ) );
			$args                = [ 'include' => $include_ids ];
			$get_selected_groups = $this->get_queried_access_groups( $args );
			if ( is_array( $get_selected_groups ) && ! empty( $get_selected_groups ) ) {
				$access_group_array = $get_selected_groups;
			}
			if ( ! empty( $access_group_args['exclude'] ) ) {
				// Ignored in favor of functionality.
				$access_group_args['exclude'] = array_merge( $include_ids, $access_group_args['exclude'] ); //phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
			} else {
				// Ignored in favor of functionality.
				$access_group_args['exclude'] = $include_ids; //phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
			}
		}

		$access_groups = $this->get_queried_access_groups( $access_group_args );
		if ( ! is_array( $access_groups ) || empty( $access_groups ) ) {
			if ( ! empty( $access_group_array ) ) {
				wp_send_json_success( $access_group_array );
			}
			$message = isset( $access_group_args['s'] ) ? __( 'No post available for this keyword.', 'suremembers' ) : __( 'No access group available.', 'suremembers' );
			wp_send_json_error( [ 'message' => $message ] );
		}

		wp_send_json_success( array_merge( $access_groups, $access_group_array ) );
	}

	/**
	 * Get selected group title and id.
	 *
	 * @param array $args Get post query.
	 * @return array|boolean
	 * @since  1.0.0
	 */
	public function get_queried_access_groups( $args ) {
		$post_types       = Access_Groups::get_active( $args );
		$return_ids_title = [];
		if ( empty( $post_types ) ) {
			return false;
		}
		foreach ( $post_types as $key => $value ) {
			$return_ids_title[] = [
				'id'    => $key,
				'title' => $value,
			];
		}
		return $return_ids_title;
	}
}
