<?php
/**
 * Frontend Renderer.
 *
 * @package SureDash
 * @since 1.0.0
 */

namespace SureDashboard\Core;

use SureDashboard\Inc\Traits\Get_Instance;
use SureDashboard\Inc\Utils\Helper;
use SureDashboard\Inc\Utils\Labels;
use SureDashboard\Inc\Utils\PostMeta;

defined( 'ABSPATH' ) || exit;

/**
 * Frontend Compatibility
 *
 * @package SureDash
 */

/**
 * Renderer setup
 *
 * @since 1.0.0
 */
class Renderer {
	use Get_Instance;

	/**
	 *  Constructor
	 */
	public function __construct() {
		add_action( 'suredash_enqueue_scripts', [ $this, 'suredash_enqueue_scripts' ] );
		add_filter( 'suredash_page_heading', [ $this, 'update_queried_heading' ] );
		add_filter( 'suredash_title_block_set', [ $this, 'update_title_block_set' ] );

		add_action( 'wp', [ $this, 'update_recently_viewed_items' ] );
		add_action( 'template_redirect', [ $this, 'redirect_to_login' ] );
		add_filter( 'template_include', [ $this, 'update_templates' ], 999 );
		add_filter( 'body_class', [ $this, 'add_body_class' ] );

		// Hide admin bar as it is not required.
		add_filter( 'show_admin_bar', [ $this, 'adjust_admin_bar' ] );

		// Update hardcoded static things with dynamic within content.
		add_filter( 'the_content', 'suredash_dynamic_content_support' );

		// Add the shortcut for SureDash admin dashboard.
		add_action( 'admin_bar_menu', [ $this, 'add_custom_admin_bar_items' ], 100 );
		add_action( 'wp_head', [ $this, 'admin_bar_styles' ] );
	}

	/**
	 * Enqueue SureDash Assets
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function suredash_enqueue_scripts(): void {
		if ( is_admin() ) {
			return;
		}

		// Global necessary assets.
		if ( method_exists( Assets::get_instance(), 'enqueue_global_assets' ) ) {
			Assets::get_instance()->enqueue_global_assets();
		}

		if ( method_exists( Assets::get_instance(), 'enqueue_search_assets' ) ) {
			Assets::get_instance()->enqueue_search_assets();
		}

		// Type wise assets.
		if ( method_exists( Assets::get_instance(), 'enqueue_archive_group_assets' ) ) {
			Assets::get_instance()->enqueue_archive_group_assets();
		}

		if ( method_exists( Assets::get_instance(), 'enqueue_single_item_assets' ) ) {
			Assets::get_instance()->enqueue_single_item_assets();
		}
	}

	/**
	 * Adjust admin bar.
	 *
	 * @param bool $status admin bar status.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function adjust_admin_bar( $status ) {
		if ( suredash_frontend() || suredash_simply_content() ) {
			$show_content_only = absint( $_GET['show_content_only'] ?? false ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$simply_content    = absint( $_GET['simply_content'] ?? false ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( $show_content_only || $simply_content ) {
				return false;
			}

			// Show admin bar only for administrators.
			if ( current_user_can( 'administrator' ) ) {
				return true;
			}

			return false;
		}

		return $status;
	}

	/**
	 * Add shortcut to SureDash admin dashboard.
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar WP Admin Bar object.
	 *
	 * @since 1.0.0
	 */
	public function add_custom_admin_bar_items( $wp_admin_bar ): void {
		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$post_id     = get_queried_object_id();
		$admin_url   = false;
		$group_terms = wp_get_post_terms( $post_id, SUREDASHBOARD_TAXONOMY );

		if ( ! empty( $group_terms ) && ! is_wp_error( $group_terms ) ) {
			$admin_url = admin_url( 'admin.php?page=' . SUREDASHBOARD_SLUG . '&tab=spaces&section=space&group=' . absint( $group_terms[0]->term_id ) . '&space=' . absint( $post_id ) );
		}

		$logo_url = SUREDASHBOARD_URL . 'assets/icons/admin-icon.svg';

		if ( ! is_callable( [ $wp_admin_bar, 'add_node' ] ) ) {
			return;
		}

		$wp_admin_bar->add_node(
			[
				'id'    => 'suredash_admin_link',
				'title' => '<img src="' . esc_url( $logo_url ) . '" style="height:18px;" alt="SureDash" />',
				'href'  => '',
				'meta'  => [
					'title' => __( 'SureDash Menu', 'suredash' ),
				],
			]
		);

		if ( $admin_url ) {
			$wp_admin_bar->add_node(
				[
					'id'     => 'suredash_current_space',
					'parent' => 'suredash_admin_link',
					'title'  => __( 'Edit Space', 'suredash' ),
					'href'   => esc_url( $admin_url ),
					'meta'   => [
						'title' => __( 'Go to Space Editor', 'suredash' ),
					],
				]
			);
		}
		$wp_admin_bar->add_node(
			[
				'id'     => 'suredash_dashboard',
				'parent' => 'suredash_admin_link',
				'title'  => __( 'Dashboard', 'suredash' ),
				'href'   => esc_url( admin_url( 'admin.php?page=' . SUREDASHBOARD_SLUG . '&tab=home' ) ),
				'meta'   => [
					'title' => __( 'Go to Dashboard Page', 'suredash' ),
				],
			]
		);

		$wp_admin_bar->add_node(
			[
				'id'     => 'suredash_spaces',
				'parent' => 'suredash_admin_link',
				'title'  => __( 'All Spaces', 'suredash' ),
				'href'   => esc_url( admin_url( 'admin.php?page=' . SUREDASHBOARD_SLUG . '&tab=spaces' ) ),
				'meta'   => [
					'title' => __( 'Go to All Spaces Page', 'suredash' ),
				],
			]
		);

		$wp_admin_bar->add_node(
			[
				'id'     => 'suredash_posts',
				'parent' => 'suredash_admin_link',
				'title'  => __( 'All Posts', 'suredash' ),
				'href'   => esc_url( admin_url( 'admin.php?page=' . SUREDASHBOARD_SLUG . '&tab=posts' ) ),
				'meta'   => [
					'title' => __( 'Go to All Posts Page', 'suredash' ),
				],
			]
		);

		$wp_admin_bar->add_node(
			[
				'id'     => 'suredash_settings',
				'parent' => 'suredash_admin_link',
				'title'  => __( 'Settings', 'suredash' ),
				'href'   => esc_url( admin_url( 'admin.php?page=' . SUREDASHBOARD_SLUG . '&tab=settings&section=branding' ) ),
				'meta'   => [
					'title' => __( 'Go to Settings Page', 'suredash' ),
				],
			]
		);
	}

	/**
	 * Add menu styles.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function admin_bar_styles(): void {
		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<style type="text/css" media="screen">
			#wp-admin-bar-suredash_admin_link .ab-item {
				display: flex !important;
				align-items: center;
			}
			#wp-admin-bar-suredash_admin_link ul li:first-child:after {
				border-bottom: 1px solid hsla(0, 0%, 100%, .2);
				display: block;
				margin: 5px -15px 5px;
				content: "";
				width: calc(100% + 26px);
			}
			#wp-admin-bar-suredash_admin_link ul li:last-child:before {
				border-top: 1px solid hsla(0, 0%, 100%, .2);
				display: block;
				margin: 5px -15px 5px;
				content: "";
				width: calc(100% + 26px);
			}
		</style>
		<?php
	}

	/**
	 * Update Docs Views count.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function update_recently_viewed_items(): void {
		if ( ! ( is_singular( SUREDASHBOARD_POST_TYPE ) || is_singular( SUREDASHBOARD_FEED_POST_TYPE ) ) ) {
			return;
		}

		if ( ! empty( $_SERVER['HTTP_PURPOSE'] ) && $_SERVER['HTTP_PURPOSE'] === 'prefetch' ) {
			return;
		}

		global $post;
		$cookie_duration = get_option( 'portal-items-cookie-duration', 1 );

		$ids = isset( $_COOKIE['portal_recently_viewed'] ) ? explode( 'portal', sanitize_text_field( wp_unslash( $_COOKIE['portal_recently_viewed'] ) ) ) : [];

		$domain_path = ! empty( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$domain_path = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : $domain_path;

		if ( ! in_array( strval( $post->ID ), $ids, true ) ) {
			$ids[] = $post->ID;
			$ids   = implode( 'portal', $ids );

			if ( $cookie_duration !== '' ) {
				$item_cookie_time = 60 * 60 * 24 * $cookie_duration;
				setcookie( 'portal_recently_viewed', $ids, time() + $item_cookie_time, '/', $domain_path, suredash_site_is_https() && is_ssl(), true );
			}
		}
	}

	/**
	 * Redirect to login page if not logged in.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function redirect_to_login(): void {
		if ( suredash_frontend() ) {
			$is_public_portal = Helper::get_option( 'hidden_community' );

			if ( ! is_user_logged_in() && $is_public_portal ) {
				wp_safe_redirect( suredash_get_login_page_url() );
				exit;
			}
		}
	}

	/**
	 * Update home page heading as per sub queried item.
	 *
	 * @param string $title Page title.
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function update_queried_heading( $title ) {
		if ( suredash_is_sub_queried_page() ) {
			$title = Labels::get_label( suredash_get_sub_queried_page() );
		}

		return $title;
	}

	/**
	 * Update space title heading text & emoji as per type.
	 *
	 * @param array<string, string> $emoji_n_title Emoji and title.
	 * @since 0.0.6
	 *
	 * @return array<string, string>
	 */
	public function update_title_block_set( $emoji_n_title ) {
		global $post;
		$caught = false;

		switch ( true ) {
			case is_singular() && ! empty( $post->ID ):
				$caught  = true;
				$post_id = ! empty( $post->ID ) ? absint( $post->ID ) : 0;
				$emoji   = PostMeta::get_post_meta_value( $post_id, 'item_emoji' );
				$title   = get_the_title( $post_id );
				break;
			case get_queried_object():
				$caught = true;
				$emoji  = '';
				$title  = single_term_title( '', false );
				break;
			case suredash_is_sub_queried_page():
				$caught = true;
				$emoji  = '';
				$title  = Labels::get_label( suredash_get_sub_queried_page() );
				break;
		}

		if ( $caught ) {
			return [
				'emoji' => $emoji,
				'title' => $title,
			];
		}

		return $emoji_n_title;
	}

	/**
	 * Check if override template.
	 *
	 * @since 0.0.3
	 * @return bool
	 */
	public function check_if_override_template() {
		$status = true;

		/* Breakdance & Bricks Builders compatibility */
		if ( ! empty( $_GET['breakdance'] ) || ( ! empty( $_GET['bricks'] ) && $_GET['bricks'] === 'run' ) ) { // phpcs:ignore
			$status = false;
		}

		return apply_filters( 'suredash_perform_template_include', $status );
	}

	/**
	 * Callback function for override templates.
	 *
	 * @param string $template override single templates.
	 * @since 1.0.0
	 * @return mixed
	 */
	public function update_templates( $template ) {
		if ( ! $this->check_if_override_template() ) {
			return $template;
		}

		if ( suredash_simply_content() ) {
			$show_content_only = absint( $_GET['show_content_only'] ?? false ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			return suredash_get_template_part(
				'quick-view',
				$show_content_only ? 'content' : 'post'
			);
		}

		if ( ( is_front_page() && Helper::get_option( 'portal_as_homepage' ) ) || suredash_is_sub_queried_page() || suredash_portal() || suredash_cpt() ) {
			do_action( 'suredash_enqueue_scripts' );

			if ( wp_is_block_theme() ) {
				global $_wp_current_template_id, $_wp_current_template_content;

				$_wp_current_template_id = 'suredash/suredash//portal';
				$block_template          = get_block_template( 'suredash/suredash//portal' );

				if ( ! empty( $block_template ) ) {
					$_wp_current_template_content = $block_template->content;
				}
			} else {
				$template = SUREDASHBOARD_DIR . 'templates/pages/template-suredash-portal.php';
			}

			add_action( 'wp_footer', [ $this, 'render_suredash_footer_compat' ] );
		}

		return $template;
	}

	/**
	 * Execute SureDash Footer.
	 *
	 * @since 0.0.6
	 * @return void
	 */
	public function render_suredash_footer_compat(): void {
		do_action( 'suredash_footer' );
	}

	/**
	 * Add body class.
	 *
	 * @param array<int, string> $classes body classes.
	 * @since 1.0.0
	 * @return array<int, string>
	 */
	public function add_body_class( $classes ) {
		// Assign version class for reference.
		$classes[] = 'suredash-' . SUREDASHBOARD_VER;

		if ( ! is_user_logged_in() ) {
			$classes[] = 'suredash-guest-user';
		}

		return $classes;
	}
}
