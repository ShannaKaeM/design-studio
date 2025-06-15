<?php
/**
 * Menu
 *
 * This class will holds the code related to the admin area modification
 * along with the plugin functionalities.
 *
 * @package SureDash
 *
 * @since 1.0.0
 */

namespace SureDashboard\Admin;

use SureDashboard\Core\Models\Controller;
use SureDashboard\Inc\Traits\Get_Instance;
use SureDashboard\Inc\Utils\Helper;
use SureDashboard\Inc\Utils\PostMeta;
use SureDashboard\Inc\Utils\Settings;
use SureDashboard\Inc\Utils\TermMeta;

defined( 'ABSPATH' ) || exit;

/**
 * Menu
 *
 * @since 1.0.0
 */
class Menu {
	use Get_Instance;

	/**
	 * Settings page ID for Plugin settings.
	 */
	public const PAGE_ID = 'portal';

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		// The admin area actions.
		$this->initialize_hooks();

		add_action( 'admin_init', [ $this, 'settings_admin_scripts' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'load_site_editor_metabox' ] );
	}

	/**
	 * Function to load the admin area actions.
	 *
	 * @since 1.0.0
	 */
	public function initialize_hooks(): void {
		// Load the Plugin's main menus.
		add_action( 'admin_menu', [ $this, 'register_plugin_menus' ] );

		// Load the Plugin's main menu CSS for some custom design.
		add_action( 'admin_head', [ $this, 'admin_menu_css' ] );
	}

	/**
	 *  Initialize Admin Setup.
	 *
	 * @since 1.0.0
	 */
	public function settings_admin_scripts(): void {
		if ( ! empty( $_GET['page'] ) && ( wp_unslash( $_GET['page'] ) === self::PAGE_ID || strpos( sanitize_text_field( wp_unslash( $_GET['page'] ) ), self::PAGE_ID . '_' ) !== false ) ) { // phpcs:ignore -- Input var okay.
			add_action( 'admin_enqueue_scripts', [ $this, 'app_build_scripts' ] );
		}

		add_action( 'admin_head', [ $this, 'update_nav_menu_items' ], 999 );
	}

	/**
	 * Load the site editor metabox.
	 *
	 * @since 1.0.0
	 */
	public function load_site_editor_metabox(): void {
		if ( is_admin() && ! empty( get_current_screen()->base ) && get_current_screen()->base === 'site-editor' ) {
			$this->app_build_scripts();
		}
	}

	/**
	 * Update the nav menu items.
	 *
	 * @since 1.0.0
	 */
	public function update_nav_menu_items(): void {
		global $pagenow;
		global $typenow;

		if ( ( $pagenow === 'edit-tags.php' ) &&
			( in_array( $typenow, [ SUREDASHBOARD_FEED_POST_TYPE ], true ) )
		) {
			$localized_data = apply_filters(
				'portal_localized_admin_nav_menu_data',
				[
					'home_slug'         => self::PAGE_ID,
					'edit_feed_tax'     => 'taxonomy=' . SUREDASHBOARD_FEED_TAXONOMY . '&post_type=' . SUREDASHBOARD_FEED_POST_TYPE,
					'feed_post_type'    => SUREDASHBOARD_FEED_POST_TYPE,
					'primary_post_type' => SUREDASHBOARD_POST_TYPE,
				]
			);

			$handle = 'portal_admin_nav_menu_scripts';

			wp_enqueue_script(
				$handle,
				esc_url( SUREDASHBOARD_JS_ASSETS_FOLDER . 'nav-update' . SUREDASHBOARD_JS_SUFFIX ),
				[ 'jquery' ],
				SUREDASHBOARD_VER,
				true
			);

			wp_localize_script(
				$handle,
				'portal_admin_nav_menu_data',
				$localized_data
			);
		}
	}

	/**
	 * Add submenu to admin menu.
	 *
	 * @since 1.0.0
	 */
	public function register_plugin_menus(): void {
		if ( current_user_can( SUREDASHBOARD_CAPABILITY ) ) {
			global $submenu;
			$parent_slug   = self::PAGE_ID;
			$capability    = SUREDASHBOARD_CAPABILITY;
			$menu_priority = apply_filters( self::PAGE_ID . '_menu_priority', 40 );

			$logo = 'PHN2ZyB3aWR0aD0iMzYiIGhlaWdodD0iMzYiIHZpZXdCb3g9IjAgMCAxNDQgMTQ0IiBmaWxsPSIjYTdhYWFkIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPg0KPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0wIDBINzJDMTExLjc2NSAwIDE0NCAzMi4yMzU1IDE0NCA3MkMxNDQgMTExLjc2NSAxMTEuNzY1IDE0NCA3MiAxNDRIMFYwWk02OS45Mzc3IDYyLjIwNjhDNzMuMDk5OSA1OC42MzA4IDc1LjAzMDggNTMuODU4IDc1LjAzMDggNDguNjE2MUM3NS4wMzA4IDQzLjk5OTEgNzMuNTMyOSAzOS43NDYgNzEuMDE1IDM2LjM1NDFDNzMuOTUyNSAzMy45OTk3IDc3LjY4MSAzMi41OTEzIDgxLjczODQgMzIuNTkxM0M5MS4yMTQ4IDMyLjU5MTMgOTguODk3MiA0MC4yNzM2IDk4Ljg5NzIgNDkuNzUwMUM5OC44OTcyIDU5LjIyNjcgOTEuMjE0OCA2Ni45MDkgODEuNzM4NCA2Ni45MDlDNzcuMTY3IDY2LjkwOSA3My4wMTMxIDY1LjEyMTIgNjkuOTM3NyA2Mi4yMDY4Wk00OS43MjUxIDY3LjE0MzFDMzkuMDYzOSA2Ny4xNDMxIDMwLjQyMTQgNTguNTAwNiAzMC40MjE0IDQ3LjgzOTVDMzAuNDIxNCAzNy4xNzgzIDM5LjA2MzkgMjguNTM1OCA0OS43MjUxIDI4LjUzNThDNjAuMzg2MiAyOC41MzU4IDY5LjAyODcgMzcuMTc4MyA2OS4wMjg3IDQ3LjgzOTVDNjkuMDI4NyA1OC41MDA2IDYwLjM4NjIgNjcuMTQzMSA0OS43MjUxIDY3LjE0MzFaTTc4LjE2MDkgMTE1LjE3M1Y5Mi45MDY1Qzc4LjE2MDkgODMuNTI2MiA3Mi44MTY0IDc1LjQ4NyA2NS4yMiA3Mi4xMTA4SDgxLjYzMjNDOTMuNjA0IDcyLjExMDggMTAzLjMwOSA4MS44MTU4IDEwMy4zMDkgOTMuNzg3NUMxMDMuMzA5IDEwNS43NTkgOTMuNjA0MSAxMTUuNDY0IDgxLjYzMjMgMTE1LjQ2NEM4MC40MTk1IDExNS40NjQgNzkuMjYxOSAxMTUuMzY1IDc4LjE2MDkgMTE1LjE3M1pNMjguMDA4NSAxMTUuNDAyVjkzLjY4NTdDMjguMDA4NSA4MS42OTE4IDM3LjUyMjIgNzEuOTY5MSA0OS4yNTggNzEuOTY5MUM2MC45OTM4IDcxLjk2OTEgNzIuNzI1NyA4MS42OTE4IDcyLjcyNTcgOTMuNjg1N1YxMTUuNDAySDI4LjAwODVaIiBmaWxsPSIjYTdhYWFkIi8+DQo8L3N2Zz4NCg==';

			// Main Menu. Used to display the list of all portals.
			add_menu_page(
				'SureDash',
				'SureDash',
				$capability,
				$parent_slug,
				[ $this, 'render_main_page' ],
				'data:image/svg+xml;base64,' . $logo, //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				$menu_priority
			);

			add_submenu_page(
				$parent_slug,
				__( 'Spaces', 'suredash' ),
				__( 'Spaces', 'suredash' ),
				$capability,
				'admin.php?page=' . self::PAGE_ID . '&tab=spaces'
			);

			add_submenu_page(
				$parent_slug,
				__( 'Posts', 'suredash' ),
				__( 'Posts', 'suredash' ),
				$capability,
				'admin.php?page=' . self::PAGE_ID . '&tab=posts',
			);

			add_submenu_page(
				$parent_slug,
				__( 'Settings', 'suredash' ),
				__( 'Settings', 'suredash' ),
				$capability,
				'admin.php?page=' . self::PAGE_ID . '&tab=settings'
			);

			add_submenu_page(
				$parent_slug,
				__( 'Customize Portal', 'suredash' ),
				__( 'Customize Portal', 'suredash' ),
				$capability,
				wp_is_block_theme() ? admin_url( '/site-editor.php?postId=suredash%2Fsuredash%2F%2Fportal&postType=wp_template&canvas=edit' ) : admin_url( '/site-editor.php?postType=wp_template_part&postId=suredash%2Fsuredash%2F%2Fportal&canvas=edit' )
			);

			$submenu[ $parent_slug ][0][0] = esc_html__( 'Dashboard', 'suredash' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Required to rename the home menu.
		}
	}

	/**
	 * Get the pending count bubble.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_pending_count_bubble(): string {
		$pending_count = wp_count_posts( SUREDASHBOARD_FEED_POST_TYPE )->pending;

		if ( $pending_count > 0 ) {
			return ' <span class="awaiting-mod count-' . $pending_count . '"><span class="pending-count">' . $pending_count . '</span></span>';
		}

		return '';
	}

	/**
	 * Add the CSS to design the main side-bar menu of the plugin.
	 *
	 * @since 1.0.0
	 */
	public function admin_menu_css(): void {
		echo '<style>
			#toplevel_page_portal li {
				clear: both;
			}
			#toplevel_page_portal li:not(:last-child) a[href^="admin.php?page=portal"]:after,
			#toplevel_page_portal li:not(:last-child) a[href^="edit-tags.php?taxonomy=' . esc_attr( SUREDASHBOARD_FEED_TAXONOMY ) . '&post_type=' . esc_attr( SUREDASHBOARD_FEED_POST_TYPE ) . '"]:after {
				border-bottom: 1px solid hsla(0,0%,100%,.2);
				display: block;
				float: left;
				margin: 13px -15px 8px;
				content: "";
				width: calc(100% + 26px);
			}
			#toplevel_page_portal li:not(:last-child) a[href^="admin.php?page=portal&tab=spaces"]:after,
			#toplevel_page_portal li:not(:last-child) a[href^="admin.php?page=portal&tab=settings"]:after {
				content: none;
			}
		</style>';
	}

	/**
	 * Renders the portals screen canvas.
	 *
	 * @since 1.0.0
	 */
	public function render_main_page(): void {
		echo "<div id='portals-main-page--wrapper'></div>";
	}

	/**
	 * Enqueue the Admin's build files for plugin to work.
	 *
	 * @since 1.0.0
	 */
	public function app_build_scripts(): void {
		if ( is_customize_preview() ) {
			return;
		}

		global $pagenow;

		// Check weather the current page is portals or portal's child pages.
		if ( ( is_admin() && $pagenow === 'site-editor.php' ) || ( isset( $_GET['page'] ) && ( sanitize_text_field( wp_unslash( $_GET['page'] ) ) === self::PAGE_ID || strpos( sanitize_text_field( wp_unslash( $_GET['page'] ) ), 'portal_' ) !== false ) ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_enqueue_media();

			$items_dataset                = $this->get_all_group_n_items_dataset();
			$spaces_meta_set              = $items_dataset['portal_spaces_meta_set'];
			$portal_space_groups_meta_set = $items_dataset['portal_space_groups_meta_set'];
			$portal_dataset               = $items_dataset['portal_dataset'];
			$portal_items_count           = $items_dataset['portal_items_count'];
			$portal_name_n_id             = $items_dataset['portal_name_n_id'];
			$first_group_id               = key( $portal_name_n_id );

			$community_content_src = esc_url_raw(
				add_query_arg(
					[
						'post_type' => SUREDASHBOARD_SUB_CONTENT_POST_TYPE,
					],
					admin_url( 'post-new.php' )
				)
			);

			$notice_messages = [];
			if ( defined( 'SUREDASH_PRO_VER' ) ) {
				if ( version_compare( SUREDASH_PRO_VER, SUREDASH_PRO_MINIMUM_VER, '<' ) ) {
					$pro_notice_message     = sprintf( /* translators: %s: version number */ __( 'Version %1$s or higher required!', 'suredash' ), SUREDASH_PRO_MINIMUM_VER );
					$notice_messages['pro'] = $pro_notice_message;
				}

				if ( defined( 'SUREDASH_FREE_MINIMUM_VER' ) && version_compare( SUREDASHBOARD_VER, SUREDASH_FREE_MINIMUM_VER, '<' ) ) {
					$free_notice_message     = sprintf( /* translators: %s: version number */ __( 'Version %1$s or higher required!', 'suredash' ), SUREDASH_FREE_MINIMUM_VER );
					$notice_messages['free'] = $free_notice_message;
				}
			}

			$localized_data = apply_filters(
				'portal_localized_admin_data',
				[
					'dashboard_url'                => admin_url( 'admin.php?page=' . self::PAGE_ID ),
					'edit_post_link'               => admin_url( 'post.php?post={{POST_ID}}&action=edit' ),
					'ajax_url'                     => admin_url( 'admin-ajax.php' ),
					'version'                      => SUREDASHBOARD_VER,
					'notice'                       => $notice_messages,
					'update_nonce'                 => wp_create_nonce( 'portals_update_admin_setting' ),
					'home_slug'                    => self::PAGE_ID,
					'group_items_dataset'          => $portal_dataset,
					'group_items_count'            => $portal_items_count,
					'spaces_meta_set'              => $spaces_meta_set,
					'space_groups_meta_set'        => $portal_space_groups_meta_set,
					'group_name_ids'               => $portal_name_n_id,
					'first_group_id'               => $first_group_id,
					'icon_library'                 => Helper::get_icon_library(),
					'all_community_contents'       => admin_url( 'edit.php?post_type=' . SUREDASHBOARD_SUB_CONTENT_POST_TYPE ),
					'create_community_content'     => admin_url( 'post-new.php?post_type=' . SUREDASHBOARD_SUB_CONTENT_POST_TYPE ),
					'create_sd_post'               => admin_url( 'post-new.php?post_type=' . SUREDASHBOARD_FEED_POST_TYPE ),
					'settings'                     => Settings::get_suredash_settings(),
					'is_settings_page'             => isset( $_GET['page'] ) && $_GET['page'] === SUREDASHBOARD_SETTINGS, //phpcs:ignore WordPress.Security.NonceVerification.Recommended
					'user_roles'                   => $this->get_formatted_user_roles(),
					'capable_managers'             => $this->get_capable_managers(),
					'is_pro_available'             => suredash_is_pro_active(),
					'pro_version'                  => suredash_is_pro_active() ? SUREDASH_PRO_VER : 0,
					'color_presets'                => $this->get_compatible_color_presets(),
					'create_community_content_src' => $community_content_src,
					'feed_post_type'               => SUREDASHBOARD_FEED_POST_TYPE,
					'feed_forum_tax'               => SUREDASHBOARD_FEED_TAXONOMY,
					'community_content_post_type'  => SUREDASHBOARD_SUB_CONTENT_POST_TYPE,
					'pro_product_name'             => suredash_is_pro_active() ? SUREDASH_PRO_PRODUCT : '',
					'is_pp_free_available'         => defined( 'PRESTO_PLAYER_PLUGIN_FILE' ),
					'is_pp_pro_available'          => defined( 'PRESTO_PLAYER_PRO_PLUGIN_FILE' ),
					'upgrade_link'                 => SUREDASHBOARD_UPGRADE_LINK,
					'username'                     => wp_get_current_user()->display_name,
					'email'                        => wp_get_current_user()->user_email,
					'is_user_onboarded'            => get_option( 'suredash_onboarding_completed', false ) === 'yes' || get_option( 'suredash_onboarding_skipped' ) === 'yes' ? true : false,
					'portal_url'                   => get_home_url() . '/portal',
					'suremembers_status'           => $this->get_plugin_status( 'suremembers/suremembers.php' ),
					'surecart_status'              => $this->get_plugin_status( 'surecart/surecart.php' ),
					'suretriggers_status'          => $this->get_plugin_status( 'suretriggers/suretriggers.php' ),
					'sureforms_status'             => $this->get_plugin_status( 'sureforms/sureforms.php' ),
					'all_community_posts'          => Helper::get_community_posts(),
					'community_posts_count'        => sd_count_posts( SUREDASHBOARD_FEED_POST_TYPE ),
					'hide_welcome_card'            => get_option( 'suredash_hide_welcome_card', 'no' ),
					'view_members_link'            => admin_url( 'users.php' ),
					'is_site_editor_screen'        => $pagenow === 'site-editor.php',
					'user_notices'                 => [
						'posts_performance_notice'    => sd_get_user_meta( get_current_user_id(), 'posts_performance_notice', true ),
						'design_settings_info_notice' => sd_get_user_meta( get_current_user_id(), 'design_settings_info_notice', true ),
					],
				]
			);

			$handle            = 'portal_admin_scripts';
			$build_path        = SUREDASHBOARD_URL . 'assets/build/';
			$script_asset_path = SUREDASHBOARD_DIR . 'assets/build/portals-app.asset.php';

			$script_info = file_exists( $script_asset_path )
				? include $script_asset_path
				: [
					'dependencies' => [],
					'version'      => SUREDASHBOARD_VER,
				];

			if ( $pagenow === 'site-editor.php' ) {
				$script_dep = array_merge( $script_info['dependencies'], [ 'wp-plugins', 'wp-edit-site', 'wp-data', 'wp-components', 'wp-element', 'updates' ] );
			} else {
				$script_dep = array_merge( $script_info['dependencies'], [ 'wp-plugins', 'wp-edit-site', 'wp-data', 'updates' ] );
			}

			wp_enqueue_script(
				$handle,
				$build_path . 'portals-app.js',
				$script_dep,
				SUREDASHBOARD_VER,
				true
			);

			wp_localize_script( $handle, 'portal_admin_data', $localized_data );

			wp_set_script_translations( $handle, 'suredash', SUREDASHBOARD_DIR . 'languages' );

			wp_enqueue_style(
				'portal-font',
				esc_url( SUREDASHBOARD_CSS_ASSETS_FOLDER . ( is_rtl() ? 'font-rtl' : 'font' ) . SUREDASHBOARD_CSS_SUFFIX ),
				[],
				$script_info['version']
			);

			wp_enqueue_style( $handle, esc_url( is_rtl() ? $build_path . 'portals-app-rtl.css' : $build_path . 'portals-app.css' ), [ 'portal-font' ], SUREDASHBOARD_VER );
		}
	}

	/**
	 * Get plugin status
	 *
	 * @since 1.0.0
	 *
	 * @param  string $plugin_init_file plugin init file.
	 * @return string
	 */
	public function get_plugin_status( $plugin_init_file ) {

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$installed_plugins = get_plugins();

		if ( ! isset( $installed_plugins[ $plugin_init_file ] ) ) {
			return 'not-installed';
		}
		if ( is_plugin_active( $plugin_init_file ) ) {
			return 'active';
		}

		return 'inactive';
	}

	/**
	 * Get theme compatible or default color preset.
	 *
	 * @return array<int, string> colors.
	 *
	 * @since 0.0.1
	 */
	public function get_compatible_color_presets(): array {
		$color_preset = [ '#046bd2', '#045cb4', '#1e293b', '#334155', '#F0F5FA', '#FFFFFF', '#D1D5DB', '#111111' ];

		if ( suredash_is_on_astra_theme() ) {
			// Astra theme compatibility.
			$theme_colors = astra_get_option( 'global-color-palette' );
			$color_preset = ! empty( $theme_colors['palette'] ) ? $theme_colors['palette'] : $color_preset;
		}

		return apply_filters( 'suredashboard_settings_color_preset', $color_preset );
	}

	/**
	 * Get user roles array.
	 *
	 * @return array<mixed> array of user roles.
	 *
	 * @since 1.0.0
	 */
	public function get_formatted_user_roles(): array {
		global $wp_roles;

		if ( ! isset( $wp_roles ) ) {
			return [];
		}

		$available_roles_names = $wp_roles->get_names();

		$excluded_roles = apply_filters(
			'suredashboard_settings_excluded_roles',
			[
				'administrator' => esc_html__( 'Administrator', 'suredash' ),
			]
		);

		$included_roles = array_diff( $available_roles_names, $excluded_roles );
		return Helper::get_react_select_format( $included_roles );
	}

	/**
	 * Get all category and docs dataset.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, array<int|string, mixed>>
	 */
	public function get_all_group_n_items_dataset(): array {

		$portal_dataset               = [];
		$portal_items_count           = [];
		$portal_name_n_id             = [];
		$portal_spaces_meta_set       = [];
		$portal_space_groups_meta_set = [];

		$results = Controller::get_query_data(
			'Navigation',
		);

		if ( ! empty( $results ) ) {
			$space_groups = array_reduce(
				$results,
				static function( array $carry, $item ): array {
					if ( is_array( $item ) && isset( $item['space_group_position'] ) ) {
						$carry[ $item['space_group_position'] ][] = $item;
					}
					return $carry;
				},
				[]
			);

			ksort( $space_groups );

			foreach ( $space_groups as &$group ) {
				$id_sequence = array_unique( explode( ',', strval( $group[0]['space_position'] ) ) );
				usort(
					$group,
					static function ( $a, $b ) use ( $id_sequence ) {
						$a_index = array_search( $a['ID'], $id_sequence );
						$b_index = array_search( $b['ID'], $id_sequence );
						return $a_index - $b_index;
					}
				);
			}
			unset( $group );

			foreach ( $space_groups as $group ) {

				foreach ( $group as $post ) {
					$post_id   = absint( $post['ID'] );
					$term_id   = absint( $post['term_id'] );
					$term_name = $post['name'];
					$meta_set  = [];

					if ( $post_id ) {
						$post_title                         = $post['post_title'];
						$meta_set                           = [
							'post_id'          => $post_id,
							'post_title'       => $post_title,
							'permalink'        => get_permalink( $post_id ),
							'post_status'      => get_post_status( $post_id ),
							'edit_post_link'   => get_edit_post_link( $post_id, '' ),
							'delete_post_link' => get_delete_post_link( $post_id ),
							'is_restricted'    => suredash_get_post_backend_restriction( $post_id ),
						];
						$portal_spaces_meta_set[ $post_id ] = $meta_set;

						$meta_set = array_merge( $meta_set, PostMeta::get_all_post_meta_values( $post_id ) );
					}

					$query_posts = $meta_set;

					if ( ! isset( $portal_space_groups_meta_set[ $term_id ] ) ) {
						$term_meta                                = [
							'term_id'          => $term_id,
							'isCategory'       => true,
							'edit_term_link'   => get_edit_term_link( $term_id, SUREDASHBOARD_TAXONOMY ),
							'view_term_link'   => get_term_link( $term_id, SUREDASHBOARD_TAXONOMY ),
							'query_posts'      => [],
							'posts_count'      => 0,
							'delete_term_link' => str_replace( '&amp;', '&', admin_url( wp_nonce_url( 'edit-tags.php?action=delete&taxonomy=' . SUREDASHBOARD_TAXONOMY . "&tag_ID={$term_id}", 'delete-tag_' . $term_id ) ) ),
						];
						$portal_space_groups_meta_set[ $term_id ] = $term_meta;
					}

					if ( ! empty( $query_posts ) ) {
						$portal_space_groups_meta_set[ $term_id ]['query_posts'][] = $query_posts;
					}
					$portal_space_groups_meta_set[ $term_id ]['posts_count']++;

					$term_metadata = array_merge( $portal_space_groups_meta_set[ $term_id ], TermMeta::get_all_group_meta_values( absint( $term_id ) ) );

					$portal_dataset[ 'group-' . $term_id ] = $term_metadata;

					$portal_items_count[ $term_id ] = $portal_space_groups_meta_set[ $term_id ]['posts_count'];
					$portal_name_n_id[ $term_id ]   = $term_name;
				}
			}
		}

		// Prepare uncategorized items dataset.
		$uncategorized_items = Controller::get_query_uncategorized_items(
			'Backend_Feeds',
			[]
		);

		if ( ! empty( $uncategorized_items ) ) {
			$term_name   = __( 'Uncategorized', 'suredash' );
			$query_posts = [];

			foreach ( $uncategorized_items as $item ) {
				$post_id          = absint( $item['ID'] );
				$post_title       = $item['post_title'];
				$permalink        = get_permalink( $post_id );
				$post_status      = get_post_status( $post_id );
				$edit_post_link   = get_edit_post_link( $post_id, '' );
				$delete_post_link = get_delete_post_link( $post_id );

				$meta_set = [
					'post_id'          => $post_id,
					'post_title'       => $post_title,
					'permalink'        => $permalink,
					'post_status'      => $post_status,
					'edit_post_link'   => $edit_post_link,
					'delete_post_link' => str_replace( '&amp;', '&', (string) $delete_post_link ),
					'is_restricted'    => suredash_get_post_backend_restriction( $post_id ),
				];

				$portal_spaces_meta_set[ $post_id ] = $meta_set;

				// Get all post meta with values.
				$meta_set = array_merge( $meta_set, PostMeta::get_all_post_meta_values( $post_id ) );

				$query_posts[] = $meta_set;
			}

			$portal_dataset[ 'group-' . 0 ] = [
				'term_name'   => $term_name,
				'term_id'     => 0,
				'query_posts' => $query_posts,
				'posts_count' => count( $uncategorized_items ),
			];
			$portal_items_count[0]          = count( $uncategorized_items );
			$portal_name_n_id[0]            = $term_name;
		}

		return [
			'portal_dataset'               => $portal_dataset,
			'portal_items_count'           => $portal_items_count,
			'portal_name_n_id'             => $portal_name_n_id,
			'portal_spaces_meta_set'       => $portal_spaces_meta_set,
			'portal_space_groups_meta_set' => $portal_space_groups_meta_set,
		];
	}

	/**
	 * Get capable managers. User with Editor role or above.
	 *
	 * @since 1.0.0
	 *
	 * @return array<int, array<string, mixed>> $capable_managers Capable managers.
	 */
	public function get_capable_managers(): array {
		$capable_managers = [];

		$users = get_users(
			[
				'role__in' => apply_filters( 'suredashboard_manager_user_roles_within', [ 'administrator', 'editor' ] ),
			]
		);

		if ( empty( $users ) ) {
			return $capable_managers;
		}

		foreach ( $users as $user ) {
			$capable_managers[] = [
				'id'   => $user->ID,
				'name' => $user->display_name,
			];
		}

		return $capable_managers;
	}
}
