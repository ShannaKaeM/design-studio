<?php
/**
 * SureMembers Integration.
 *
 * @package SureDash
 */

namespace SureDashboard\Core\Integrations;

use SureDashboard\Inc\Traits\Get_Instance;
use SureDashboard\Inc\Utils\Helper;
use SureDashboard\Inc\Utils\Labels;

defined( 'ABSPATH' ) || exit;

/**
 * SureMembers Integration.
 *
 * @since 1.0.0
 */
class SureMembers extends Base {
	use Get_Instance;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name        = 'SureMembers';
		$this->slug        = 'sure-members';
		$this->description = __( 'SureMembers Integration', 'suredash' );
		$this->is_active   = defined( 'SUREMEMBERS_VER' ) && SUREMEMBERS_VER; // @phpstan-ignore-line

		parent::__construct( $this->name, $this->slug, $this->description, $this->is_active ); // @phpstan-ignore-line

		if ( ! $this->is_active ) {
			return;
		}

		add_filter( 'suremembers_login_wrapper_class', [ $this, 'add_portal_content_wrapper' ], 10, 1 );

		add_action( 'suredash_before_title_block', [ $this, 'check_restriction_navigation_space_icon' ], 10, 1 );
		add_action( 'suredash_after_title_block', [ $this, 'revert_navigation_space_icon' ], 10, 1 );
		add_action( 'suredash_before_aside_navigation_item', [ $this, 'check_restriction_navigation_space_icon' ], 10, 1 );
		add_action( 'suredash_after_aside_navigation_item', [ $this, 'revert_navigation_space_icon' ], 10, 1 );

		add_filter( 'suredash_post_backend_restriction_details', [ $this, 'check_suremembers_restriction_status' ], 10, 2 );

		add_action( 'template_redirect', [ $this, 'init_suremembers_integration' ], 1 );

		/**
		 * Process restriction content.
		 *
		 * @since 1.0.0
		 */
		if ( class_exists( '\SureMembers\Inc\Template_Redirect' ) && is_callable( [ \SureMembers\Inc\Template_Redirect::get_instance(), 'processed_content' ] ) ) {
			add_action( 'suredash_post_restriction_before_check', [ $this, 'check_post_restrictions' ], 10, 2 );
			add_action( 'suredash_post_restriction_after_check', [ $this, 'revert_post_restrictions' ], 10, 2 );
		}
	}

	/**
	 * Add portal content wrapper class.
	 *
	 * @param string $class Class.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function add_portal_content_wrapper( $class ) {
		return $class . ' portal-content';
	}

	/**
	 * Update navigation space icon base on following cases.
	 *
	 * 1. Padlock: If the post is restricted by SureMembers.
	 * 2. Clock: If the post is dripped by SureMembers.
	 * 3. Default: If the post is not restricted by SureMembers.
	 *
	 * @param string $icon Icon.
	 * @param int    $post_id Post ID.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function update_navigation_space_icon( $icon, $post_id ) {
		if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
			return $icon;
		}

		if ( ! class_exists( '\SureMembers\Inc\Restricted' ) ) {
			return $icon;
		}

		$user_id   = intval( get_current_user_id() );
		$post_type = sd_get_post_field( $post_id, 'post_type' );

		$option = [
			'include'           => SUREMEMBERS_PLAN_INCLUDE,
			'exclusion'         => SUREMEMBERS_PLAN_EXCLUDE,
			'priority'          => SUREMEMBERS_PLAN_PRIORITY,
			'current_post_type' => $post_type,
			'current_page_type' => 'is_singular',
			'current_post_id'   => $post_id,
		];

		$access_groups = \SureMembers\Inc\Restricted::by_access_groups( SUREMEMBERS_POST_TYPE, $option );
		if ( empty( $access_groups ) || empty( $access_groups[ SUREMEMBERS_POST_TYPE ] ) ) {
			return $icon;
		}

		$original_icon = $icon;
		$icon          = Helper::get_library_icon( 'Lock', false );

		if ( is_user_logged_in() && class_exists( '\SureMembers\Inc\Access_Groups' ) && is_callable( [ '\SureMembers\Inc\Access_Groups', 'check_user_has_post_access' ] ) ) {
			$has_access = \SureMembers\Inc\Access_Groups::check_user_has_post_access( $post_id, $access_groups, $user_id );
			if ( ! $has_access ) {
				$icon = Helper::get_library_icon( 'Lock', false );
			} else {
				$icon = $original_icon;
			}
		}

		$post_drip = false;
		if ( class_exists( '\SureMembers\Inc\Access_Groups' ) && is_callable( [ '\SureMembers\Inc\Access_Groups', 'check_is_post_is_dripping' ] ) ) {
			$post_drip = \SureMembers\Inc\Access_Groups::check_is_post_is_dripping( $post_id, $access_groups, $user_id );
			if ( $post_drip['status'] ?? false ) {
				$icon = Helper::get_library_icon( 'Clock', false );
			}
		}

		return $icon;
	}

	/**
	 * Check if the post is restricted by SureMembers.
	 *
	 * @param array<string, mixed> $dataset Dataset.
	 * @param int                  $post_id Post ID.
	 * @return array<string, mixed>
	 * @since 1.0.0
	 */
	public function is_restricted( $dataset, $post_id ) {
		if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
			return $dataset;
		}

		if ( ! class_exists( '\SureMembers\Inc\Restricted' ) ) {
			return $dataset;
		}

		$user_id   = intval( get_current_user_id() );
		$post_type = sd_get_post_field( $post_id, 'post_type' );

		$consider_post_types = [];
		if ( suredash_content_post() || is_singular( SUREDASHBOARD_SUB_CONTENT_POST_TYPE ) ) {
			$post_type             = SUREDASHBOARD_SUB_CONTENT_POST_TYPE;
			$consider_post_types[] = SUREDASHBOARD_SUB_CONTENT_POST_TYPE;
		}
		if ( is_singular( SUREDASHBOARD_FEED_POST_TYPE ) ) {
			$post_type             = SUREDASHBOARD_FEED_POST_TYPE;
			$consider_post_types[] = SUREDASHBOARD_FEED_POST_TYPE;
		}

		$option = [
			'include'           => SUREMEMBERS_PLAN_INCLUDE,
			'exclusion'         => SUREMEMBERS_PLAN_EXCLUDE,
			'priority'          => SUREMEMBERS_PLAN_PRIORITY,
			'current_post_type' => is_singular( $consider_post_types ) ? $post_type : 'is_singular',
			'current_page_type' => 'is_singular',
			'current_post_id'   => $post_id,
		];

		$access_groups = \SureMembers\Inc\Restricted::by_access_groups( SUREMEMBERS_POST_TYPE, $option );
		if ( empty( $access_groups ) || empty( $access_groups[ SUREMEMBERS_POST_TYPE ] ) ) {
			return $dataset;
		}

		$sm_restricted            = false;
		$considered_access_groups = [];
		$original_dataset         = $dataset;

		if ( is_array( $access_groups ) ) {
			if ( isset( $access_groups[ SUREMEMBERS_POST_TYPE ] ) && ! empty( $access_groups[ SUREMEMBERS_POST_TYPE ] ) ) {
				foreach ( $access_groups[ SUREMEMBERS_POST_TYPE ] as $id => $plan ) {
					$access_group_details = class_exists( '\SureMembers\Inc\Access_Groups' ) && is_callable( [ '\SureMembers\Inc\Access_Groups', 'get_restriction_detail' ] ) ? \SureMembers\Inc\Access_Groups::get_restriction_detail( $id ) : [];
					if ( ! $sm_restricted ) {
						$sm_restricted            = true;
						$considered_access_groups = $access_group_details;
						$dataset                  = [
							'status'  => true,
							'content' => $this->get_restricted_message( '', $access_group_details ),
						];
					}
				}
			}
		}

		if ( is_user_logged_in() && class_exists( '\SureMembers\Inc\Access_Groups' ) && is_callable( [ '\SureMembers\Inc\Access_Groups', 'check_user_has_post_access' ] ) ) {
			$has_access = \SureMembers\Inc\Access_Groups::check_user_has_post_access( $post_id, $access_groups, $user_id );
			if ( ! $has_access ) {
				$dataset = [
					'status'  => true,
					'content' => $this->get_restricted_message( '', $considered_access_groups ),
				];
			} else {
				$dataset = $original_dataset;
			}
		}

		$post_drip = false;
		if ( class_exists( '\SureMembers\Inc\Access_Groups' ) && is_callable( [ '\SureMembers\Inc\Access_Groups', 'check_is_post_is_dripping' ] ) ) {
			$post_drip = \SureMembers\Inc\Access_Groups::check_is_post_is_dripping( $post_id, $access_groups, $user_id );
			if ( $post_drip['status'] ?? false ) {
				$dataset = [
					'status'  => true,
					'content' => $this->get_dripped_message( '', $post_drip['time'] ?? '' ),
				];
			}
		}

		return $dataset;
	}

	/**
	 * Check restriction navigation space icon.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function check_restriction_navigation_space_icon( $post_id ): void {
		add_filter( 'suredash_aside_navigation_space_icon_' . $post_id, [ $this, 'update_navigation_space_icon' ], 10, 2 );
	}

	/**
	 * Revert navigation space icon.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function revert_navigation_space_icon( $post_id ): void {
		remove_filter( 'suredash_aside_navigation_space_icon_' . $post_id, [ $this, 'update_navigation_space_icon' ] );
	}

	/**
	 * Check post restrictions & return the restriction details with status, content.
	 *
	 * @param array<string, mixed> $dataset Dataset.
	 * @param int                  $post_id Post ID.
	 * @return void
	 * @since 1.0.0
	 */
	public function check_post_restrictions( $dataset, $post_id ): void {
		$restriction_details = $this->is_restricted( [], $post_id );
		$status              = $restriction_details['status'] ?? $dataset['status'] ?? false;

		if ( $status ) {
			$dataset            = [];
			$dataset['status']  = $status;
			$dataset['content'] = $restriction_details['content'] ?? '';

			add_filter(
				'suredash_post_restriction_ruleset',
				static function( $default_ruleset, $post_id ) use ( $dataset ) {
					return $dataset;
				},
				10,
				2
			);
		}
	}

	/**
	 * Add SureMembers sign to portal items.
	 *
	 * @param array<mixed> $dataset Status.
	 * @param int          $post_id Post ID.
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function check_suremembers_restriction_status( $dataset, $post_id ) {
		$option = [
			'include'           => SUREMEMBERS_PLAN_INCLUDE,
			'exclusion'         => SUREMEMBERS_PLAN_EXCLUDE,
			'priority'          => SUREMEMBERS_PLAN_PRIORITY,
			'current_post_id'   => $post_id,
			'current_post_type' => get_post_type( $post_id ),
			'current_page_type' => 'is_singular',
		];

		$access_groups = class_exists( '\SureMembers\Inc\Restricted' ) ? \SureMembers\Inc\Restricted::by_access_groups( SUREMEMBERS_POST_TYPE, $option ) : [];

		if ( ! empty( $access_groups && is_array( $access_groups ) ) ) {
			if ( isset( $access_groups[ SUREMEMBERS_POST_TYPE ] ) && ! empty( $access_groups[ SUREMEMBERS_POST_TYPE ] ) ) {
				foreach ( $access_groups[ SUREMEMBERS_POST_TYPE ] as $id => $plan ) {
					$access_group_instance = sd_get_post( $id );
					$post_title            = is_object( $access_group_instance ) && isset( $access_group_instance->post_title ) ? $access_group_instance->post_title : '';
					$redirection           = class_exists( '\SureMembers\Inc\Access_Groups' ) ? \SureMembers\Inc\Access_Groups::get_admin_url(
						[
							'page'    => 'suremembers_rules',
							'post_id' => $id,
						]
					) : '';
					$access_group_details  = class_exists( '\SureMembers\Inc\Access_Groups' ) && is_callable( [ '\SureMembers\Inc\Access_Groups', 'get_restriction_detail' ] ) ? \SureMembers\Inc\Access_Groups::get_restriction_detail( $id ) : [];
					$dataset               = [
						'status'              => true,
						'title'               => __( 'Access Group: ', 'suredash' ) . $post_title,
						'redirection'         => $redirection,
						'restriction_details' => $access_group_details,
					];
				}
			}
		}

		return $dataset;
	}

	/**
	 * Revert post restrictions.
	 *
	 * @param array<mixed> $dataset Status.
	 * @param int          $post_id Post ID.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function revert_post_restrictions( $dataset, $post_id ): void {
		$dataset            = [];
		$dataset['status']  = false;
		$dataset['content'] = '';

		remove_filter(
			'suredash_post_restriction_ruleset',
			static function( $default_ruleset, $post_id ) use ( $dataset ) {
				return $dataset;
			}
		);
	}

	/**
	 * Remove SureMembers template action. As we are handling their "processed_content()" separately. We need to remove their action.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function init_suremembers_integration(): void {
		if ( ! suredash_frontend() ) {
			return;
		}

		add_filter( 'suremembers_only_process_redirection', '__return_true' );
		add_filter( 'suremembers_load_restricted_page_template', '__return_false' );
	}

	/**
	 * Get the restricted message.
	 *
	 * @param string               $content Content.
	 * @param array<string, mixed> $restriction Restriction Rule.
	 * @since 1.0.0
	 * @return string|false
	 */
	public function get_restricted_message( $content, $restriction = [] ) {
		$is_in_content   = $restriction['in_content'] ?? true;
		$enable_login    = $restriction['enablelogin'] ?? false;
		$preview_button  = $restriction['preview_button'] ?? '';
		$redirect_url    = $restriction['redirect_url'] ?? '';
		$preview_content = $restriction['preview_content'] ?? Labels::get_label( 'restricted_content_notice' );
		$preview_heading = $restriction['preview_heading'] ?? Labels::get_label( 'restricted_content_heading' );

		if ( ! $is_in_content ) {
			return $content;
		}

		ob_start();

		suredash_get_template_part(
			'parts',
			'sm-restriction',
			[
				'icon'            => 'Lock',
				'heading'         => $preview_heading,
				'preview_button'  => $preview_button,
				'redirect_url'    => $redirect_url,
				'preview_content' => $preview_content,
				'enable_login'    => $enable_login,
			]
		);

		return ob_get_clean();
	}

	/**
	 * Get the restricted message.
	 *
	 * @param string $content Content.
	 * @param string $time Time.
	 * @since 1.0.0
	 * @return string|false
	 */
	public function get_dripped_message( $content, $time ) {
		ob_start();

		suredash_get_template_part(
			'parts',
			'restricted',
			[
				'icon'          => 'Clock',
				'label'         => 'dripped_content_heading',
				'description'   => 'dripped_content_notice',
				'extra_content' => $time,
			]
		);

		return shortcode_unautop( strval( ob_get_clean() ) );
	}
}
