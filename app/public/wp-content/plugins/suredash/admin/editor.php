<?php
/**
 * Admin Editor Init.
 *
 * @package powerful-docs
 *
 * @since 1.0.0
 */

namespace SureDashboard\Admin;

use SureDashboard\Core\Renderer;
use SureDashboard\Inc\Traits\Get_Instance;
use SureDashboard\Inc\Utils\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * This class sets up admin init
 *
 * @class Editor
 */
class Editor {
	use Get_Instance;

	/**
	 * Store Json variable
	 *
	 * @var array<string, string> $icon_json JSON variable.
	 * @since 1.8.1
	 */
	private static array $icon_json = [];

	/**
	 * Number of icon chunks
	 *
	 * @var int $number_of_icon_chunks Number of icon chunks.
	 * @since 2.7.0
	 */
	private static int $number_of_icon_chunks = 4;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'enqueue_block_editor_assets', [ $this, 'block_editor_assets' ] );

		if ( version_compare( get_bloginfo( 'version' ), '5.8', '>=' ) ) {
			add_filter( 'block_categories_all', [ $this, 'register_block_category' ], 10, 2 );
		} else {
			add_filter( 'block_categories', [ $this, 'register_block_category' ], 10, 2 );
		}
	}

	/**
	 * Editor assets routes.
	 *
	 * @since 1.0.0
	 */
	public function block_editor_assets(): void {
		wp_enqueue_style(
			'portal-blocks',
			esc_url( SUREDASHBOARD_CSS_ASSETS_FOLDER . ( is_rtl() ? 'blocks-rtl' : 'blocks' ) . SUREDASHBOARD_CSS_SUFFIX ),
			[],
			SUREDASHBOARD_VER
		);

		$screen       = get_current_screen();
		$script_asset = SUREDASHBOARD_DIR . 'assets/build/editor-app.asset.php';
		$handle       = 'portal_editor_scripts';
		$build_path   = SUREDASHBOARD_URL . 'assets/build/';
		$post_id      = get_the_ID();

		$script_info = file_exists( $script_asset ) ? include $script_asset : [
			'dependencies' => [],
			'version'      => SUREDASHBOARD_VER,
		];

		$script_dep = array_merge( $script_info['dependencies'], [ 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor' ] );

		$user_is_admin = false;
		$current_user  = wp_get_current_user();
		if ( $current_user instanceof \WP_User ) {
			$user_roles = $current_user->roles;
			if ( in_array( 'administrator', $user_roles, true ) ) {
				$user_is_admin = true;
			}
		}

		// Add SVG icons in chunks.
		$icon_chunks   = $this->add_svg_icon_assets();
		$logo_url      = strval( Helper::get_option( 'logo_url' ) );
		$profile_photo = sd_get_user_meta( $current_user->ID, 'user_profile_photo', true );
		if ( empty( $profile_photo ) ) {
			$profile_photo = get_avatar_url( $current_user->ID );
		}

		$localized_data = apply_filters(
			'portal_localized_editor_data',
			[
				'admin_base_url'          => admin_url(),
				'project_url'             => SUREDASHBOARD_URL,
				'category'                => 'suredash',
				'ID'                      => $post_id,
				'is_allow_registration'   => (bool) get_option( 'users_can_register' ),
				'version'                 => SUREDASHBOARD_VER,
				'svg_confirmation_nonce'  => wp_create_nonce( 'svg_confirmation_nonce' ),
				'ajax_url'                => admin_url( 'admin-ajax.php' ),
				'is_rtl'                  => is_rtl(),
				'admin_home_url'          => admin_url( 'admin.php?page=portal' ),
				'settings_url'            => admin_url( 'admin.php?page=' . SUREDASHBOARD_SETTINGS ),
				'is_site_editor'          => $screen->id ?? '',
				'is_customize_preview'    => is_customize_preview(),
				'font_awesome_5_polyfill' => [],
				'anyone_can_register'     => admin_url( 'options-general.php#users_can_register' ),
				'login_url'               => esc_url( suredash_get_login_page_url() ),
				'user_can_adjust_role'    => apply_filters( 'suredash_registration_form_role_manager', $user_is_admin ),
				'icon_chunks'             => $this->get_number_of_icon_chunks(),
				'svg_confirmation'        => current_user_can( 'edit_posts' ),
				'blocks_required_configs' => [
					'search'            => Helper::get_library_icon( 'Search', false ),
					'EllipsisVertical'  => Helper::get_library_icon( 'EllipsisVertical', false ),
					'portal_name'       => Helper::get_option( 'portal_name' ),
					'portal_logo'       => ! empty( $logo_url ) ? esc_url( $logo_url ) : '',
					'user_display_name' => $current_user->display_name,
					'user_email'        => $current_user->user_email,
					'user_avatar'       => $profile_photo,
					'menus_page'        => admin_url( 'nav-menus.php' ),
				],
			]
		);

		$localized_data = array_merge( $localized_data, $icon_chunks );

		wp_enqueue_script( $handle, esc_url( $build_path . 'editor-app.js' ), $script_dep, $script_info['version'], true );
		wp_localize_script( $handle, 'portal_blocks', $localized_data );

		wp_enqueue_style(
			$handle,
			esc_url( is_rtl() ? $build_path . 'editor-app-rtl.css' : $build_path . 'editor-app.css' ),
			[ 'wp-edit-blocks' ],
			$script_info['version']
		);

		// Internal CPT meta setup.
		$post_type = strval( get_post_type() );
		$is_portal = $post_type === SUREDASHBOARD_POST_TYPE;

		$is_community_content = $post_type === SUREDASHBOARD_SUB_CONTENT_POST_TYPE;
		$is_sd_post           = $post_type === SUREDASHBOARD_FEED_POST_TYPE;
		$is_site_editor       = isset( $screen->id ) ? $screen->id === 'site-editor' : false;

		if ( $is_sd_post || $is_community_content || $is_portal || $is_site_editor ) {
			$localize_data = apply_filters(
				'portal_localized_meta_editor_data',
				[
					'ajax_url'                       => admin_url( 'admin-ajax.php' ),
					'content_id'                     => $post_id,
					'placeholder_slug'               => __( 'Lesson Duration', 'suredash' ),
					'lesson_duration_val'            => sd_get_post_meta( absint( $post_id ), 'lesson_duration', true ),
					'is_internal_single_cpt'         => true,
					'is_community_content_post_type' => $is_community_content,
				]
			);

			wp_enqueue_style(
				'portal-font',
				SUREDASHBOARD_CSS_ASSETS_FOLDER . ( is_rtl() ? 'font-rtl' : 'font' ) . SUREDASHBOARD_CSS_SUFFIX,
				[],
				$script_info['version']
			);

			wp_enqueue_style(
				'portal-global',
				esc_url( SUREDASHBOARD_CSS_ASSETS_FOLDER . ( is_rtl() ? 'global-rtl' : 'global' ) . SUREDASHBOARD_CSS_SUFFIX ),
				[ 'portal-font' ],
				$script_info['version']
			);

			wp_enqueue_style(
				'portal-meta-editor',
				esc_url( SUREDASHBOARD_CSS_ASSETS_FOLDER . ( is_rtl() ? 'meta-editor-rtl' : 'meta-editor' ) . SUREDASHBOARD_CSS_SUFFIX ),
				[],
				$script_info['version']
			);

			wp_enqueue_script(
				'portal-meta-editor',
				esc_url( SUREDASHBOARD_JS_ASSETS_FOLDER . 'meta-editor' . SUREDASHBOARD_JS_SUFFIX ),
				[ 'wp-data', 'wp-element', 'wp-editor', 'wp-util', 'wp-hooks', 'wp-blocks' ],
				$script_info['version'],
				true
			);

			wp_localize_script( 'portal-meta-editor', 'portal_meta', $localize_data );

			if ( $is_sd_post ) {
				$localize_data = apply_filters(
					'portal_localized_core_editor_data',
					[
						'ajax_url'         => admin_url( 'admin-ajax.php' ),
						'content_id'       => $post_id,
						'cover_image_text' => __( 'Cover Image', 'suredash' ),
						'media_embed_text' => __( 'Media Embeds', 'suredash' ),
						'cover_image_val'  => sd_get_post_meta( absint( $post_id ), 'custom_post_cover_image', true ),
						'media_embed_val'  => sd_get_post_meta( absint( $post_id ), 'custom_post_embed_media', true ),
					]
				);

				wp_enqueue_script(
					'portal-core-editor',
					esc_url( SUREDASHBOARD_JS_ASSETS_FOLDER . 'core-editor' . SUREDASHBOARD_JS_SUFFIX ),
					[ 'wp-data', 'wp-element', 'wp-editor', 'wp-util', 'wp-hooks', 'wp-blocks' ],
					$script_info['version'],
					true
				);

				wp_localize_script( 'portal-core-editor', 'portal_editor', $localize_data );
			}
		}

		// Global necessary assets.
		if ( method_exists( Renderer::get_instance(), 'suredash_enqueue_scripts' ) ) {
			Renderer::get_instance()->suredash_enqueue_scripts();
		}
	}

	/**
	 * Localize SVG icon scripts in chunks.
	 * Ex - if 1800 icons available, we will localize 4 variables for it.
	 *
	 * @since 0.0.1
	 *
	 * @return array<string, string>
	 */
	public function add_svg_icon_assets() {
		$localize_icon_chunks = $this->backend_load_font_awesome_icons();

		if ( ! $localize_icon_chunks ) {
			return [];
		}

		$chunk_wise_data = [];

		foreach ( $localize_icon_chunks as $chunk_index => $value ) {
			$chunk_wise_data[ "uagb_svg_icons_{$chunk_index}" ] = $value;
		}

		return $chunk_wise_data;
	}

	/**
	 * Get JSON Data.
	 * Customize and add icons via 'uagb_icons_chunks' filter.
	 *
	 * @since 1.8.1
	 *
	 * @return array<string, string>
	 */
	public function backend_load_font_awesome_icons() {
		if ( ! empty( self::$icon_json ) ) {
			return self::$icon_json;
		}

		$icons_chunks = [];
		$icons_dir    = SUREDASHBOARD_DIR . 'assets/icon-library';

		$file = "{$icons_dir}/lucide-icons.php";

		if ( file_exists( $file ) ) {
			$icons_chunks[] = include_once $file;
		}

		$icons_chunks = apply_filters( 'suredashboard_icons_chunks', $icons_chunks );

		if ( ! is_array( $icons_chunks ) || empty( $icons_chunks ) ) {
			$icons_chunks = [];
		}

		self::$icon_json = $icons_chunks;
		return self::$icon_json;
	}

	/**
	 * Get the number of icon chunks.
	 *
	 * @since 0.0.1
	 */
	public function get_number_of_icon_chunks(): int {
		return self::$number_of_icon_chunks;
	}

	/**
	 * Gutenberg block category for SD.
	 *
	 * @param array<int, array<string, string>> $categories Block categories.
	 * @param object                            $post Post object.
	 *
	 * @since 1.0.0
	 *
	 * @return array<int, array<string, string>>
	 */
	public function register_block_category( $categories, $post ) {
		return array_merge(
			$categories,
			[
				[
					'slug'  => 'suredash',
					'title' => 'SureDash',
				],
			]
		);
	}
}
