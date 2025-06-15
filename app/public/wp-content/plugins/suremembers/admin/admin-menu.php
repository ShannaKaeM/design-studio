<?php
/**
 * Admin menu.
 *
 * @package suremembers
 * @since 1.0.0
 */

namespace SureMembers\Admin;

use SureMembers\Inc\Traits\Get_Instance;
use SureMembers\Inc\Utils;
use SureMembers\Inc\Access_Groups;
use SureMembers\Inc\Restricted;

/**
 * Admin menu
 *
 * @since 0.0.1
 */
class Admin_Menu {

	use Get_Instance;

	/**
	 * Tailwind assets base url
	 *
	 * @var string
	 * @since  0.0.1
	 */
	private $tailwind_assets = SUREMEMBERS_URL . 'admin/assets/build/';

	/**
	 * Constructor
	 *
	 * @since  0.0.1
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'suremembers_page' ], 99 );
		add_action( 'admin_enqueue_scripts', [ $this, 'settings_page_scripts' ] );
		add_action( 'admin_init', [ $this, 'check_status_transition' ] );
		add_action( 'admin_footer', [ $this, 'add_license_popup' ], 999 );

		// Ajax calls for settings.
		add_action( 'wp_ajax_suremembers_fetch_posts', [ $this, 'fetch_posts' ] );
		add_action( 'wp_ajax_suremembers_search_post_by_query', [ $this, 'get_posts_by_query' ] );

		// Adding manage_options check to ajax actions related to Access Groups Edit.
		if ( current_user_can( 'manage_options' ) ) {
			add_action( 'wp_ajax_suremembers_update_status', [ $this, 'update_access_group_status' ] );
			add_action( 'wp_ajax_suremembers_submit_form', [ $this, 'submit_form' ] );
			add_action( 'wp_ajax_suremembers_save_downloads', [ $this, 'save_downloads' ] );
		}

		add_action( 'in_admin_header', [ $this, 'custom_banner' ] );
		add_action( 'admin_head', [ $this, 'admin_menu_css' ] );
		add_action( 'admin_footer', [ $this, 'remove_extra_content' ], 10, 1 );
		add_action( 'manage_' . SUREMEMBERS_POST_TYPE . '_posts_custom_column', [ $this, 'column_content' ], 10, 2 );

		// Filters.
		add_filter( 'manage_' . SUREMEMBERS_POST_TYPE . '_posts_columns', [ $this, 'column_headings' ] );
		add_filter( 'views_edit-' . SUREMEMBERS_POST_TYPE, [ $this, 'update_views' ] );
		add_filter( 'get_edit_post_link', [ $this, 'edit_post_link' ], 10, 2 );
		add_filter( 'admin_url', [ $this, 'update_add_new_link' ], 100, 2 );
		add_filter( 'post_row_actions', [ $this, 'change_action_links' ], 10, 2 );
		add_filter( 'bulk_actions-edit-' . SUREMEMBERS_POST_TYPE, '__return_empty_array' );
		add_filter( 'months_dropdown_results', [ $this, 'remove_month_dropdown' ] );
		add_filter( 'suremembers_get_access_groups_data', [ $this, 'get_access_group_data' ] );
		add_filter( 'suremembers_filter_access_group_url_args', [ $this, 'check_iframe_mode' ] );
	}

	/**
	 * Updates edit post link to react app
	 *
	 * @param string $url default edit url.
	 * @param int    $post_id current post id.
	 * @return string
	 * @since 1.0.0
	 */
	public function edit_post_link( $url, $post_id ) {
		// Ignored nonce verification as we are getting post_type from URL.
		if ( empty( $_GET['post_type'] ) || SUREMEMBERS_POST_TYPE !== sanitize_text_field( $_GET['post_type'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $url;
		}

		$url_args = apply_filters(
			'suremembers_filter_access_group_url_args',
			[
				'post_type' => SUREMEMBERS_POST_TYPE,
				'page'      => 'suremembers_rules',
				'post_id'   => $post_id,
			]
		);

		$url = add_query_arg(
			$url_args,
			admin_url( 'edit.php' )
		);
		return $url;
	}

	/**
	 * Check if edit link is loaded from iframe.
	 *
	 * @param array $args URL arguments.
	 * @return array $args Updated URL arguments.
	 * @since 1.0.0
	 */
	public function check_iframe_mode( $args ) {
		// Ignoring this as we are getting the data from URL and using further.
		if ( isset( $_GET['suremembers_view'] ) && 'iframe' === $_GET['suremembers_view'] ) {// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$args['suremembers_view'] = 'iframe';
		}
		return $args;
	}

	/**
	 * Replacing link for add new Access Group
	 *
	 * @param string $url default url.
	 * @param string $path default path.
	 * @return string
	 * @since 1.0.0
	 */
	public function update_add_new_link( $url, $path ) {
		if ( 'post-new.php?post_type=' . SUREMEMBERS_POST_TYPE === $path ) {
			$url = Access_Groups::get_admin_url( [ 'page' => 'suremembers_rules' ] );
		}
		return $url;
	}

	/**
	 * Adds admin menu for settings page
	 *
	 * @return void
	 * @since  0.0.1
	 */
	public function suremembers_page() {
		global $submenu;

		add_submenu_page(
			'edit.php?post_type=' . SUREMEMBERS_POST_TYPE,
			__( 'Access Group', 'suremembers' ),
			__( 'Add Access Group', 'suremembers' ),
			'manage_options',
			'suremembers_rules',
			[ $this, 'render' ],
			50
		);
		unset( $submenu[ 'edit.php?post_type=' . SUREMEMBERS_POST_TYPE ][10] ); // Removes 'Add New'.
		unset( $submenu[ 'edit.php?post_type=' . SUREMEMBERS_POST_TYPE ][12] ); // Removes 'Add Access Group Menu'.
	}

	/**
	 * Renders main div to implement tailwind UI
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function render() {
		?>
		<div class="suremembers-page" id="suremembers-page"></div>
		<?php
	}

	/**
	 * Add modal to show license.
	 *
	 * @return void
	 */
	public function add_license_popup() {
		$screen = get_current_screen();

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( is_null( $screen ) || 'edit-' . SUREMEMBERS_POST_TYPE !== $screen->id ) {
			return;
		}
		$license_url = Access_Groups::get_admin_url(
			[
				'page'             => 'suremembers-manage-license',
				'suremembers_view' => 'iframe',
			]
		)
		?>
			<div class="suremembers-modal">
				<div class="modal-content">
					<div class="modal-header">
						<h2 class="modal-title"><?php esc_html_e( 'Suremembers License', 'suremembers' ); ?></h2>
						<span class="sm-close-button" aria-label="<?php esc_attr_e( 'Close dialog', 'suremembers' ); ?>">
							<span class="dashicons dashicons-no-alt"></span>
						</span>
					</div>
					<div class="modal-body">
						<iframe width="100%" height="100%" src="<?php echo esc_url( $license_url ); ?>"></iframe>
					</div>
				</div>
			</div>
		<?php
	}

	/**
	 * Enqueue settings page script and style
	 *
	 * @param string $hook current page hook.
	 * @return void
	 * @since  1.0.0
	 */
	public function settings_page_scripts( $hook ) {
		// Ignoring this as we are getting the data from URL and using further.
		if ( isset( $_GET['suremembers_view'] ) && 'iframe' === $_GET['suremembers_view'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_enqueue_style( 'suremembers-admin-frame-popup', SUREMEMBERS_URL . 'admin/assets/build/adminbarpopup.css', [], SUREMEMBERS_VER );
		}

		$screen = get_current_screen();

		if ( ! is_null( $screen ) && 'edit-' . SUREMEMBERS_POST_TYPE === $screen->id ) {
			wp_enqueue_style( 'suremembers-license-popup', SUREMEMBERS_URL . 'admin/assets/css/license-popup.css', [], SUREMEMBERS_VER );
		}

		if ( is_null( $screen ) || ! in_array( $screen->id, [ SUREMEMBERS_POST_TYPE . '_page_suremembers_rules' ], true ) ) {
			return;
		}

		$app = 'settings';

		$script_asset_path = SUREMEMBERS_DIR . 'admin/assets/build/' . $app . '.asset.php';
		$script_info       = file_exists( $script_asset_path )
			? include $script_asset_path
			: array(
				'dependencies' => [],
				'version'      => SUREMEMBERS_VER,
			);

		$external_deps = [ 'updates' ];

		/**
		 * Check for modules dependencies.
		 * `suremembers-modules` is the handle of the script JS generated when
		 * external modules are loaded.
		 */
		if ( wp_script_is( 'suremembers-modules', 'registered' ) ) {
			array_push( $external_deps, 'suremembers-modules' );
		}

		$script_dep = array_merge( $script_info['dependencies'], $external_deps );

		wp_register_script( 'suremembers_posts', $this->tailwind_assets . $app . '.js', $script_dep, SUREMEMBERS_VER, true );
		wp_enqueue_script( 'suremembers_posts' );
		$suremembers_post_types = Restricted::get_post_types( 'object' );
		$locations              = $this->get_location_selections();
		$drips                  = $this->get_drip_data();

		$list_url_args = [];
		// Ignoring this as we are getting the data from URL and using further.
		if ( isset( $_GET['suremembers_view'] ) && 'iframe' === $_GET['suremembers_view'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$list_url_args['suremembers_view'] = 'iframe';
		}

		$list_archive_url = Access_Groups::get_admin_url( $list_url_args );

		$localize_data = [
			'ajax_url'               => admin_url( 'admin-ajax.php' ),
			'post_url'               => admin_url( 'edit.php?post_type=' . SUREMEMBERS_POST_TYPE . '&page=suremembers_rules' ),
			'posts_nonce'            => current_user_can( 'manage_options' ) ? wp_create_nonce( 'suremembers_posts_nonce' ) : '',
			'submit_nonce'           => current_user_can( 'manage_options' ) ? wp_create_nonce( 'suremembers_submit_nonce' ) : '',
			'search_nonce'           => current_user_can( 'edit_posts' ) ? wp_create_nonce( 'suremembers_search_post_nonce' ) : '',
			'status_nonce'           => wp_create_nonce( 'suremembers_status_nonce' ),
			'list_url'               => $list_archive_url,
			'post_types'             => array_combine( array_keys( $suremembers_post_types ), array_column( array_column( $suremembers_post_types, 'labels' ), 'name' ) ),
			'locations'              => $locations,
			'selected_locations'     => $this->get_selected_locations(),
			'specific_locations'     => $this->get_individual_data( 'specific' ),
			'exclude_locations'      => $this->get_individual_data(),
			'drip_data'              => $drips,
			'restricted_url'         => $this->get_restricted_url_data(),
			'user_roles_choises'     => $this->get_user_roles_with_custom_roles(),
			'user_roles_selected'    => Access_Groups::get_selected_user_roles(),
			'access_group_downloads' => Access_Groups::get_downloads(),
		];

		wp_localize_script(
			'suremembers_posts',
			'suremembers_posts',
			apply_filters( 'suremembers_get_access_groups_data', $localize_data )
		);

		wp_localize_script( 'suremembers_posts', 'scIcons', [ 'path' => SUREMEMBERS_URL . 'admin/assets/build/icon-assets' ] );

		wp_register_style( 'suremembers_posts', $this->tailwind_assets . $app . '.css', [ 'wp-components' ], SUREMEMBERS_VER );
		wp_enqueue_style( 'suremembers_posts' );
	}

	/**
	 * Get user roles array also available in settings-screen.php.
	 *
	 * @return array array of user roles.
	 * @since 1.1.0
	 */
	public function get_user_roles_with_custom_roles() {
		global $wp_roles;

		if ( ! isset( $wp_roles ) ) {
			return [];
		}

		$available_roles_names = $wp_roles->get_names();
		$excluded_roles        = apply_filters( 'suremembers_settings_excluded_roles', [ 'administrator' => esc_html__( 'Administrator', 'suremembers' ) ] );
		$included_roles        = array_diff( $available_roles_names, $excluded_roles );
		$formated_roles        = Utils::get_react_select_format( $included_roles );
		return $formated_roles;
	}

	/**
	 * Updates links label
	 *
	 * @param array $views existing labels listing.
	 * @return array
	 * @since 1.0.0
	 */
	public function update_views( $views ) {
		$new_views = [];
		if ( isset( $views['publish'] ) ) {
			$new_views['publish'] = str_replace( 'Published', esc_html__( 'Active', 'suremembers' ), $views['publish'] );
		}

		if ( isset( $views['trash'] ) ) {
			$new_views['trash'] = $views['trash'];
		}

		if ( isset( $views[ SUREMEMBERS_ARCHIVE ] ) ) {
			$new_views[ SUREMEMBERS_ARCHIVE ] = $views[ SUREMEMBERS_ARCHIVE ];
		}

		if ( isset( $views['all'] ) ) {
			$new_views['all'] = $views['all'];
		}

		return $new_views;
	}

	/**
	 * Adds heading to custom column in listing table
	 *
	 * @param array $cols array of existing columns.
	 * @return array
	 * @since 1.0.0
	 */
	public function column_headings( $cols ) {
		$date = $cols['date'];
		unset( $cols['date'] );
		unset( $cols['cb'] );
		$cols['title']    = __( 'Access Groups', 'suremembers' );
		$cols['includes'] = __( 'Includes', 'suremembers' );
		$cols['excludes'] = __( 'Excludes', 'suremembers' );
		$cols['priority'] = __( 'Priority', 'suremembers' );
		$cols['users']    = __( 'Users', 'suremembers' );
		$cols['date']     = $date;
		return $cols;
	}

	/**
	 * Adds custom content to access groups table
	 *
	 * @param string $column current column key.
	 * @param int    $post_id current post id.
	 * @return void
	 * @since 1.0.0
	 */
	public function column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'includes':
				$selected           = [];
				$locations          = $this->get_location_selections();
				$selected_locations = $this->get_selected_locations( $post_id );
				$key                = array_search( 'specifics', $selected_locations, true );
				if ( false !== $key ) {
					unset( $selected_locations[ $key ] );
					$specifics = $this->get_individual_data( 'specific', $post_id );
				}

				$key = array_search( 'restricted_url', $selected_locations, true );
				if ( false !== $key ) {
					unset( $selected_locations[ $key ] );
					$restricted_url_content = get_post_meta( $post_id, SUREMEMBERS_RESTRICTED_URL, true );
					$keyword_string         = is_array( $restricted_url_content ) && isset( $restricted_url_content['restricted_url'] ) ? $restricted_url_content['restricted_url'] : '';
					if ( ! empty( trim( $keyword_string ) ) ) {
						$keywords = preg_split( '/\r\n|\r|\n|,/', $keyword_string );
						if ( ! empty( $keywords ) ) {
							if ( count( $keywords ) > 1 ) {
								/* translators: %s array count */
								$restriction_string = sprintf( __( '%1s Rules', 'suremembers' ), count( $keywords ) );
							} else {
								$restriction_string = $keywords[0];
							}
						}
						if ( ! empty( $restriction_string ) ) {
							/* translators: %s html tags */
							$restricted_url = sprintf( __( '%1$sURL containing:%2$s %3$s', 'suremembers' ), '<strong>', '</strong>', $restriction_string );
						}
					}
				}
				foreach ( $locations as $category ) {
					$data = array_intersect( array_keys( $category['value'] ), $selected_locations );
					if ( ! empty( $data ) ) {
						foreach ( $data as $key ) {
							$selected[] = $category['value'][ $key ];
						}
					}
				}

				if ( ! empty( $selected ) ) {
					echo esc_html( implode( ', ', $selected ) ) . '<br/>';
				}

				if ( isset( $specifics ) ) {
					$specifics = array_column( $specifics, 'label' );
					echo esc_html( implode( ', ', $specifics ) ) . '<br/>';
				}

				if ( isset( $restricted_url ) ) {
					echo wp_kses_post( $restricted_url );
				}

				break;

			case 'excludes':
				$excludes = $this->get_individual_data( 'exclude', $post_id );
				if ( ! empty( $excludes ) ) {
					$excludes = array_column( $excludes, 'label' );
					echo esc_html( implode( ', ', $excludes ) );
				}
				break;

			case 'priority':
				$priority = get_post_meta( $post_id, SUREMEMBERS_PLAN_PRIORITY, true );
				if ( is_string( $priority ) && ! empty( $priority ) ) {
					echo esc_html( $priority );
				}
				break;

			case 'users':
				$required_fetch = get_post_meta( $post_id, SUREMEMBERS_REQUIRES_QUERY, true );
				if ( ! empty( $required_fetch ) ) {
					$users_in_access_group = Access_Groups::get_users_count( $post_id );
				} else {
					$users_in_access_group = absint( get_post_meta( $post_id, SUREMEMBERS_PLAN_ACTIVE_USERS, true ) );
				}
				if ( $users_in_access_group > 0 ) {
					$filter_url = add_query_arg(
						[
							'suremembers_access_group_top' => $post_id,
							'suremembers_access_group_bottom' => $post_id,
						],
						admin_url( 'users.php' )
					);

					$filter = sprintf(
						'<a target="_blank" href="%1$s">%2$s</a>',
						esc_url( $filter_url ),
						$users_in_access_group
					);

					echo wp_kses_post( $filter );
				}
				break;
			default:
				// code...
				break;
		}
	}

	/**
	 * Changing labels of action links
	 *
	 * @param array  $links array of action links.
	 * @param object $post current post object.
	 * @return array
	 * @since 1.0.0
	 */
	public function change_action_links( $links, $post ) {
		$screen = get_current_screen();
		if ( is_null( $screen ) || ! in_array( $screen->id, [ 'edit-' . SUREMEMBERS_POST_TYPE ], true ) ) {
			return $links;
		}
		unset( $links['inline hide-if-no-js'] );
		unset( $links['delete'] );
		unset( $links['untrash'] );

		if ( isset( $post->post_status ) && SUREMEMBERS_ARCHIVE !== $post->post_status ) {
			$archive_url = add_query_arg(
				apply_filters(
					'suremembers_filter_access_group_url_args',
					[
						'post'   => isset( $post->ID ) ? absint( $post->ID ) : 0,
						'action' => SUREMEMBERS_ARCHIVE,
					]
				),
				wp_nonce_url( admin_url( 'post.php' ), SUREMEMBERS_ARCHIVE )
			);
			$url         = "<a href='" . esc_url( $archive_url ) . "'>" . esc_html__( 'Archive', 'suremembers' ) . '</a>';
		} else {
			$unarchive_url = add_query_arg(
				apply_filters(
					'suremembers_filter_access_group_url_args',
					[
						'post'   => isset( $post->ID ) ? absint( $post->ID ) : 0,
						'action' => SUREMEMBERS_ARCHIVE . '_revert',
					]
				),
				wp_nonce_url( admin_url( 'post.php' ), SUREMEMBERS_ARCHIVE . '_revert' )
			);
			$url           = "<a href='" . esc_url( $unarchive_url ) . "'>" . esc_html__( 'Un-Archive', 'suremembers' ) . '</a>';
		}

		$links['trash'] = $url;

		if ( isset( $post->ID ) ) {
			// Add group ID in the action menu section to add ease of use for the users.
			$links['group-id'] = sprintf(
				/* translators: %s Access Group ID */
				__( 'Group ID: %s', 'suremembers' ),
				absint( $post->ID )
			);
		}

		return $links;
	}

	/**
	 * Adds custom banner for listing page
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function custom_banner() {
		$screen = get_current_screen();
		if ( is_null( $screen ) || ! in_array( $screen->id, [ 'edit-' . SUREMEMBERS_POST_TYPE ], true ) ) {
			return;
		}
		$activation_class   = 'license-inactive';
		$activation_message = __( 'Unlicensed', 'suremembers' );
		if ( Utils::is_license_activated() ) {
			$activation_class   = 'license-active';
			$activation_message = __( 'Licensed', 'suremembers' );
		}
		$settings_link = Access_Groups::get_admin_url(
			[
				'page' => 'suremembers_settings',
				'tab'  => 'admin-settings',
			]
		);
		?>
		<style>
			#sc-admin-header {
				background-color: #fff;
				width: 100%;
				margin-left: -20px;
				padding-right: 20px;
			}

			#sc-admin-container {
				padding: 20px;
				display: flex;
				align-items: center;
				justify-content: space-between;
			}

			#sc-admin-title {
				margin: 0;
				font-size: var(--sc-font-size-large);
			}

			.sc-breadcrumb-separator {
				display: inline-flex;
				align-self: center;
				font-size: 14px;
				margin: 0 5px;
			}

			sc-breadcrumbs {
				display: inline-flex;
			}

			sc-breadcrumb {
				display: inline-flex;
				align-self: center;
				font-weight: 600;
			}

			.suremembers-settings {
				text-decoration: none;
			}
		</style>

		<div id="sc-admin-header">
			<div id="sc-admin-container">
					<sc-breadcrumbs style="font-size: 14px">
						<sc-breadcrumb>
							<img style="display: block" src="<?php echo esc_url( SUREMEMBERS_URL . 'admin/assets/images/logo.svg' ); ?>" alt="SureMembers" width="153">
						</sc-breadcrumb>
						<div part="base" class="icon sc-breadcrumb-separator" role="img" aria-label="chevron right">
							<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<polyline points="9 18 15 12 9 6"></polyline>
							</svg>
						</div>
						<sc-breadcrumb><?php echo esc_html( __( 'Access Groups', 'suremembers' ) ); ?></sc-breadcrumb>
					</sc-breadcrumbs>
					<?php if ( current_user_can( 'manage_options' ) ) : ?>
						<div class="suremembers-header-actions">
							<a href="<?php echo esc_url( add_query_arg( 'tab', 'licensing', $settings_link ) ); ?>" class="sm-modal-trigger sm-license-tag <?php echo esc_attr( $activation_class ); ?>"><?php echo esc_html( $activation_message ); ?></a>
							<a class="suremembers-settings" href="<?php echo esc_url( $settings_link ); ?>">
								<span class="dashicons dashicons-admin-settings"></span>
							</a>
							<a class="suremembers-settings" target="_blank" href="<?php echo esc_url( 'https://suremembers.com/docs/' ); ?>">
								<span class="dashicons dashicons-editor-help"></span>
							</a>
						</div>
					<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Removes month filter
	 *
	 * @param array $data months data array.
	 * @return array
	 * @since 1.0.0
	 */
	public function remove_month_dropdown( $data ) {
		$screen = get_current_screen();
		if ( is_null( $screen ) || ! in_array( $screen->id, [ 'edit-' . SUREMEMBERS_POST_TYPE ], true ) ) {
			return $data;
		}
		return [];
	}

	/**
	 * Add the CSS to design the main side-bar menu of the plugin.
	 *
	 * @since 1.10.0
	 *
	 * @return void
	 */
	public function admin_menu_css() {
		echo '<style>
			#menu-posts-wsm_access_group li {
				clear: both;
			}
			#menu-posts-wsm_access_group li:not(:last-child) a[href^="edit.php?post_type=wsm_access_group"]:after {
				border-bottom: 1px solid hsla(0,0%,100%,.2);
				display: block;
				float: left;
				margin: 13px -15px 8px;
				content: "";
				width: calc(100% + 26px);
			}
		</style>';
	}

	/**
	 * Hides content as per UI requirement
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function remove_extra_content() {
		$screen = get_current_screen();
		if ( is_null( $screen ) || ! in_array( $screen->id, [ 'edit-' . SUREMEMBERS_POST_TYPE ], true ) ) {
			return;
		}
		?>
		<style type="text/css">
			.search-box {
				display: none
			}

			.tablenav.top {
				display: inline;
			}

			#delete_all {
				display: none;
			}
		</style>
		<?php
	}

	/**
	 * Access plan.
	 *
	 * @return array|boolean
	 * @since 1.0.0
	 */
	public function get_block_restriction_access_groups() {
		$get_access_groups = Access_Groups::get_active();
		if ( empty( $get_access_groups ) ) {
			return false;
		}
		$return = [];
		foreach ( $get_access_groups as $key => $value ) {
			$return[] = [
				'id'    => $key,
				'title' => $value,
			];
		}
		return $return;
	}

	/**
	 * Return data for edit Access Group
	 *
	 * @param array $data existing data.
	 * @return array
	 * @since 1.0.0
	 */
	public function get_access_group_data( $data ) {
		// Ignored as we are using this to localize data.
		if ( empty( $_GET['post_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $data;
		}
		// Ignored as we are using this to localize data.
		$id = absint( $_GET['post_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( empty( $id ) ) {
			return $data;
		}

		$post = get_post( $id );

		if ( empty( $post ) || SUREMEMBERS_POST_TYPE !== $post->post_type ) {
			return $data;
		}

		$data['post_data']['title']      = $post->post_title;
		$data['post_id']                 = $id;
		$data['post_status']             = get_post_status( $id );
		$data['post_data']['priority']   = get_post_meta( $id, SUREMEMBERS_PLAN_PRIORITY, true );
		$data['post_data']['expiration'] = get_post_meta( $id, SUREMEMBERS_PLAN_EXPIRATION, true );

		$meta = get_post_meta( $id, SUREMEMBERS_PLAN_RULES, true );
		if ( empty( $meta ) ) {
			return $data;
		}

		$data['post_data']['meta'] = $meta;
		return $data;
	}

	/**
	 * Updates status of access group
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function update_access_group_status() {
		check_ajax_referer( 'suremembers_status_nonce', 'security' );

		if ( ! isset( $_POST['id'] ) ) {
			wp_send_json_error( [ 'message' => __( 'Missing Post ID.', 'suremembers' ) ] );
		}

		if ( ! isset( $_POST['status'] ) ) {
			wp_send_json_error( [ 'message' => __( 'Missing Post status data.', 'suremembers' ) ] );
		}

		if ( ! isset( $_POST['status'] ) || ! isset( $_POST['id'] ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid data.', 'suremembers' ) ] );
		}
		check_ajax_referer( 'suremembers_status_nonce', 'security' );
		$status = sanitize_text_field( $_POST['status'] );
		if ( empty( $status ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid data.', 'suremembers' ) ] );
		}

		$id = intval( sanitize_text_field( $_POST['id'] ) );
		if ( empty( $status ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid post id.', 'suremembers' ) ] );
		}
		$message  = '';
		$response = false;
		switch ( $status ) {
			case 'publish':
				wp_publish_post( $id );
				$response = true;
				$message  = __( 'Published successfully', 'suremembers' );
				$action   = $status;
				break;
			case 'archive':
				$response = wp_update_post(
					array(
						'ID'          => $id,
						'post_status' => SUREMEMBERS_ARCHIVE,
					)
				);
				$status   = 'suremembers_archive';
				if ( ! is_int( $response ) ) {
					$message = __( 'Updating failed', 'suremembers' );
				} else {
					$message = __( 'Archived successfully', 'suremembers' );
				}
				break;
			case 'delete':
				$response = wp_delete_post( $id );
				if ( $response ) {
					$message = __( 'Deleted successfully', 'suremembers' );
				} else {
					$message = __( 'Delete operation failed', 'suremembers' );
				}
				break;
			default:
				$response = false;
				$message  = __( 'Invalid status.', 'suremembers' );
				break;
		}
		if ( $response ) {
			wp_send_json_success(
				[
					'message' => $message,
					'action'  => $status,
				]
			);
		}
		wp_send_json_error(
			[
				'message' => __( 'Status updating failed', 'suremembers' ),
				'action'  => $status,
			]
		);
	}

	/**
	 * Fetches all the posts belonging to selected post time in ( Rules Engine)
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function fetch_posts() {
		check_ajax_referer( 'suremembers_posts_nonce', 'security' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Current user does not have required permission.', 'suremembers' ) ] );
		}

		if ( empty( $_POST['postType'] ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid data.', 'suremembers' ) ] );
		}

		$post_type = sanitize_text_field( $_POST['postType'] );

		$args = [
			'post_type'   => $post_type,
			'post_status' => 'publish',
		];

		$posts = get_posts( $args );

		if ( empty( $posts ) ) {
			wp_send_json_error( [ 'message' => __( 'No post available for this post type.', 'suremembers' ) ] );
		}

		$response = [];
		foreach ( $posts as $post ) {
			$temp          = [];
			$temp['value'] = $post->ID;
			$temp['label'] = $post->post_title;
			$response[]    = $temp;
		}

		wp_send_json_success( [ 'posts' => $response ] );
	}

	/**
	 * Saves Sure Members rules and updates meta accordingly.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function submit_form() {
		check_ajax_referer( 'suremembers_submit_nonce', 'security' );

		if ( empty( $_POST['suremembers_post'] ) ) {
			wp_send_json_error( [ 'message' => __( 'No data received.', 'suremembers' ) ] );
		}

		// Ignored sanitization as we need HTML markup support here.
		$restrict_preview_content = isset( $_POST['suremembers_post']['restrict']['preview_content'] ) ? wp_filter_post_kses( $_POST['suremembers_post']['restrict']['preview_content'] ) : '';//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		// Ignored sanitization as we need HTML markup support here.
		$redirect_url_text = isset( $_POST['suremembers_post']['restricted_url'] ) ? wp_filter_post_kses( $_POST['suremembers_post']['restricted_url'] ) : '';//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		$_POST['suremembers_post']['restrict']['redirect_url'] = isset( $_POST['suremembers_post']['restrict']['redirect_url'] ) ? urldecode( $_POST['suremembers_post']['restrict']['redirect_url'] ) : '';//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		// Ignored sanitization as we have used recursive sanitization function.
		$post_data = Utils::sanitize_recursively( 'sanitize_text_field', $_POST['suremembers_post'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$post_data = Utils::remove_blank_array( $post_data );

		if ( empty( $post_data['title'] ) ) {
			wp_send_json_error( [ 'message' => __( 'Title can\'t be empty.', 'suremembers' ) ] );
		}

		$suremembers_post_id = isset( $_POST['suremembers_post_id'] ) ? absint( sanitize_text_field( $_POST['suremembers_post_id'] ) ) : 0;
		if ( empty( $suremembers_post_id ) ) {
			$new_post = array(
				'post_title'   => $post_data['title'],
				'post_content' => '',
				'post_status'  => 'publish',
				'post_type'    => SUREMEMBERS_POST_TYPE,
			);

			// Insert the post into the database.
			$suremembers_post_id = wp_insert_post( $new_post );
		} else {
			$post_array          = array(
				'post_title' => $post_data['title'],
				'ID'         => $suremembers_post_id,
			);
			$suremembers_post_id = wp_update_post( $post_array );
		}

		if ( ! is_int( $suremembers_post_id ) || is_wp_error( $suremembers_post_id ) ) {
			wp_send_json_error( $suremembers_post_id->get_error_message() );
		}

		unset( $post_data['title'] );

		$include = [];
		if ( ! empty( $post_data['rules'] ) ) {
			$include['rules'] = $post_data['rules'];
			unset( $post_data['rules'] );
		}

		if ( ! empty( $post_data['specifics'] ) ) {
			$include['specifics'] = $post_data['specifics'];
			unset( $post_data['specifics'] );
		}

		$include = apply_filters( 'suremembers_access_group_edit_metadata', $include, $post_data );

		update_post_meta( $suremembers_post_id, SUREMEMBERS_PLAN_INCLUDE, $include );

		// Add restriction url.
		$save_restrict_url = [];
		if ( ! empty( $post_data['restricted_url'] ) ) {
			$redirect_url_text                   = str_replace( ',', "\n", $redirect_url_text );
			$save_restrict_url['restricted_url'] = $redirect_url_text;
			if ( ! empty( $post_data['restricted_url_reg_exp'] ) ) {
				$save_restrict_url['regex'] = sanitize_text_field( $post_data['restricted_url_reg_exp'] );
			}
			update_post_meta( $suremembers_post_id, SUREMEMBERS_RESTRICTED_URL, $save_restrict_url );

		} else {
			update_post_meta( $suremembers_post_id, SUREMEMBERS_RESTRICTED_URL, '' );
		}

		$exclude = [];
		if ( ! empty( $post_data['exclude'] ) ) {
			$exclude['rules'] = $post_data['exclude'];
			unset( $post_data['exclude'] );
		}
		update_post_meta( $suremembers_post_id, SUREMEMBERS_PLAN_EXCLUDE, $exclude );

		$priority = ! empty( $post_data['priority'] ) ? $post_data['priority'] : '';
		unset( $post_data['priority'] );
		update_post_meta( $suremembers_post_id, SUREMEMBERS_PLAN_PRIORITY, $priority );

		// Update expiration.
		$expiration = ! empty( $post_data['expiration'] ) ? Utils::sanitize_recursively( 'sanitize_text_field', $post_data['expiration'] ) : '';
		unset( $post_data['expiration'] );
		update_post_meta( $suremembers_post_id, SUREMEMBERS_PLAN_EXPIRATION, $expiration );

		$drips = ! empty( $post_data['drips'] ) ? Utils::sanitize_recursively( 'sanitize_text_field', $post_data['drips'] ) : '';
		unset( $post_data['drips'] );
		update_post_meta( $suremembers_post_id, SUREMEMBERS_PLAN_DRIPS, $drips );

		// Save user roles.
		$roles = ! empty( $post_data['suremembers_user_roles'] ) ? $post_data['suremembers_user_roles'] : '';
		unset( $post_data['suremembers_user_roles'] );
		update_post_meta( $suremembers_post_id, SUREMEMBERS_USER_ROLES, $roles );

		// save download files.
		$downloads = ! empty( $post_data['download_files'] ) ? $post_data['download_files'] : '';
		unset( $post_data['download_files'] );
		update_post_meta( $suremembers_post_id, SUREMEMBERS_ACCESS_GROUP_DOWNLOADS, $downloads );

		$post_data['restrict']['preview_content'] = $restrict_preview_content;
		update_post_meta( $suremembers_post_id, SUREMEMBERS_PLAN_RULES, $post_data );

		$suremembers_mode = 'admin';

		if ( isset( $_POST['mode'] ) && 'iframe' === $_POST['mode'] ) {
			$suremembers_mode = 'iframe';
		}

		// Access group data can be access via $_POST variable.
		do_action( 'suremembers_after_submit_form', $suremembers_post_id );

		wp_send_json_success(
			[
				'message' => __( 'Rule saved successfully.', 'suremembers' ),
				'id'      => $suremembers_post_id,
				'mode'    => $suremembers_mode,
			]
		);
	}

	/**
	 * Saves the downloads data in an Access Group.
	 *
	 * @return void
	 * @since 1.3.1
	 */
	public function save_downloads() {
		check_ajax_referer( 'suremembers_submit_nonce', 'security' );

		if ( empty( $_POST['suremembers_post'] ) ) {
			wp_send_json_error( [ 'message' => __( 'No data received.', 'suremembers' ) ] );
		}

		$suremembers_post_id = isset( $_POST['suremembers_post_id'] ) ? absint( sanitize_text_field( $_POST['suremembers_post_id'] ) ) : false;

		if ( ! $suremembers_post_id ) {
			wp_send_json_error( [ 'message' => __( 'Save Access Group to protect downloads.', 'suremembers' ) ] );
		}

		// Ignored sanitization as we have used recursive sanitization function.
		$post_data = Utils::sanitize_recursively( 'sanitize_text_field', $_POST['suremembers_post'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$post_data = Utils::remove_blank_array( $post_data );

		// save download files.
		$downloads = ! empty( $post_data['download_files'] ) ? $post_data['download_files'] : '';
		unset( $post_data['download_files'] );
		update_post_meta( $suremembers_post_id, SUREMEMBERS_ACCESS_GROUP_DOWNLOADS, $downloads );

		wp_send_json_success( [ 'message' => __( 'Changes updated.', 'suremembers' ) ] );
	}

	/**
	 * Get location selection options.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_location_selections() {
		$post_types = Restricted::get_post_types( 'object' );

		$selection_options = array(
			'basic' => array(
				'label' => __( 'Basic', 'suremembers' ),
				'value' => array(
					'basic-global' => __( 'Entire Website', 'suremembers' ),
				),
			),
		);

		$args = array(
			'public' => true,
		);

		$taxonomies = get_taxonomies( $args, 'objects' );
		unset( $taxonomies['post_format'] );

		if ( ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomy ) {

				$attached_post_types = $this->get_post_types_by_taxonomy( $taxonomy->name );

				foreach ( $post_types as $post_type ) {

					$post_opt = $this->get_post_target_rule_options( $post_type, $taxonomy, $attached_post_types );

					if ( isset( $selection_options[ $post_opt['post_key'] ] ) ) {

						if ( ! empty( $post_opt['value'] ) && is_array( $post_opt['value'] ) ) {

							foreach ( $post_opt['value'] as $key => $value ) {

								if ( ! in_array( $value, $selection_options[ $post_opt['post_key'] ]['value'], true ) ) {
									$selection_options[ $post_opt['post_key'] ]['value'][ $key ] = $value;
								}
							}
						}
					} else {
						$selection_options[ $post_opt['post_key'] ] = array(
							'label' => $post_opt['label'],
							'value' => $post_opt['value'],
						);
					}
				}
			}
		}

		$selection_options['specific-target'] = array(
			'label' => __( 'Specific Target', 'suremembers' ),
			'value' => array(
				'specifics' => __( 'Specific Pages / Posts / Taxonomies, etc.', 'suremembers' ),
			),
		);

		// Restrict by specific Url.
		$selection_options['specific-url'] = array(
			'label' => __( 'Specific Url', 'suremembers' ),
			'value' => array(
				'restricted_url' => __( 'URL matching', 'suremembers' ),
			),
		);

		return apply_filters( 'suremembers_location_selection_options', $selection_options );
	}

	/**
	 * Get post type by taxonomy
	 *
	 * @param string $taxonomy taxonomy slug.
	 * @return array
	 * @since 1.0.0
	 */
	public function get_post_types_by_taxonomy( $taxonomy = '' ) {
		global $wp_taxonomies;
		if ( isset( $wp_taxonomies[ $taxonomy ] ) ) {
			return $wp_taxonomies[ $taxonomy ]->object_type;
		}
		return array();
	}

	/**
	 * Fetches posts related options for select array
	 *
	 * @param object $post_type post object.
	 * @param object $taxonomy taxonomy object.
	 * @param array  $attached_post_types posts attached to current taxonomy.
	 * @return array
	 * @since 1.0.0
	 */
	public function get_post_target_rule_options( $post_type, $taxonomy, $attached_post_types ) {
		$label       = isset( $post_type->label ) ? $post_type->label : '';
		$post_key    = str_replace( ' ', '-', strtolower( $label ) );
		$post_label  = ucwords( $label );
		$post_name   = isset( $post_type->name ) ? $post_type->name : '';
		$post_option = [];

		/* translators: %s post label */
		$all_posts                          = sprintf( __( 'All %s', 'suremembers' ), $post_label );
		$post_option[ $post_name . '|all' ] = $all_posts;

		if ( in_array( $post_name, $attached_post_types, true ) ) {
			$tax_label = ! empty( $taxonomy->label ) ? ucwords( $taxonomy->label ) : '';
			$tax_name  = ! empty( $taxonomy->name ) ? $taxonomy->name : '';

			/* translators: %s taxonomy label */
			$tax_archive = sprintf( __( 'All %s Archive', 'suremembers' ), $tax_label );

			$post_option[ $post_name . '|all|taxarchive|' . $tax_name ] = $tax_archive;
		}

		$post_output['post_key'] = $post_key;
		$post_output['label']    = $post_label;
		$post_output['value']    = $post_option;

		return $post_output;
	}

	/**
	 * Returns content as per search string
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function get_posts_by_query() {
		check_ajax_referer( 'suremembers_search_post_nonce', 'security' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Current user does not have required permission.', 'suremembers' ) ] );
		}

		$search_string      = isset( $_POST['q'] ) ? sanitize_text_field( $_POST['q'] ) : '';
		$data               = array();
		$result             = array();
		$include            = ! empty( $_POST['include'] ) ? sanitize_text_field( $_POST['include'] ) : '';
		$context            = ! empty( $_POST['context'] ) ? sanitize_text_field( $_POST['context'] ) : 'search';
		$includes_array     = explode( ',', $include );
		$post_types         = [];
		$include_taxonomies = [];
		if ( ! empty( $includes_array ) ) {
			foreach ( $includes_array as $rules ) {
				/**
				 * Added filter for learndash legacy value of `learndash-courses` and `learndash-groups`.
				 * Using this filter is discouraged, it is used for backward compatibilty and will be removed in future
				 *
				 * @since 1.4.0
				 */
				$rules  = apply_filters( 'suremembers_before_search_rules', $rules );
				$option = explode( '|', $rules );
				if ( count( $option ) > 1 ) {
					$temp                     = get_post_type_object( $option[0] );
					$post_types[ $option[0] ] = [ 'label' => isset( $temp->label ) ? $temp->label : '' ];
				}
				if ( 4 === count( $option ) ) {
					$post_types[ $option[0] ]['taxonomy'][] = $option[3];
					$include_taxonomies[]                   = $option[3];
				}
			}
		}

		if ( empty( $post_types ) ) {
			$post_types = Restricted::get_post_types( 'object', $context );
		}

		foreach ( $post_types as $key => $post_type ) {

			$data       = [];
			$child_data = [];

			add_filter( 'posts_search', [ $this, 'search_only_titles' ], 10, 2 );

			$query = new \WP_Query(
				array(
					's'              => $search_string,
					'post_type'      => $key,
					'posts_per_page' => -1,
				)
			);

			if ( $query->have_posts() ) {
				// Check post is hierarchical or not.
				$check_hierarchical = is_post_type_hierarchical( $key );
				while ( $query->have_posts() ) {
					$query->the_post();
					$title  = get_the_title();
					$id     = get_the_id();
					$data[] = [
						'value' => 'post-' . $id . '-|',
						'label' => $title,
					];

					if ( $check_hierarchical ) {
						/* translators: %s title. */
						$children_title = sprintf( __( 'Child of %s', 'suremembers' ), $title );
						$child_data[]   = [
							'value' => 'postchild-' . $id . '-|',
							'label' => $children_title,
						];
					}
				}
			}

			$post_type = (array) $post_type;

			if ( ! empty( $data ) ) {
				$result[] = array(
					'label'   => $post_type['label'],
					'options' => $data,
				);
			}
			if ( ! empty( $child_data ) ) {
				$result[] = [
					/* translators: %s label. */
					'label'   => sprintf( __( 'Child of %s', 'suremembers' ), $post_type['label'] ),
					'options' => $child_data,
				];
			}
		}

		$data = array();

		wp_reset_postdata();

		$args = array(
			'public' => true,
		);

		$output     = 'objects'; // names or objects, note names is the default.
		$operator   = 'and';
		$taxonomies = get_taxonomies( $args, $output, $operator );

		foreach ( $taxonomies as $tax => $taxonomy ) {
			if ( ! empty( $include_taxonomies ) && ! in_array( $tax, $include_taxonomies, true ) ) {
				continue;
			}

			if ( empty( $include_taxonomies ) ) {
				$attached_post_types = $this->get_post_types_by_taxonomy( $taxonomy->name );

				if ( empty( array_intersect( $attached_post_types, array_keys( $post_types ) ) ) ) {
					continue;
				}
			}

			$terms = get_terms(
				[
					'taxonomy'   => $taxonomy->name,
					'orderby'    => 'count',
					'hide_empty' => 0,
					'name__like' => $search_string,
				]
			);

			$data = array();

			$label = ucwords( $taxonomy->label );

			if ( ! empty( $terms ) && is_array( $terms ) ) {

				foreach ( $terms as $term ) {

					$term_taxonomy_name = ucfirst( str_replace( '_', ' ', $taxonomy->name ) );

					$data[] = array(
						'value' => 'tax-' . $term->term_id . '-single-' . $taxonomy->name,
						'label' => 'All singulars from ' . $term->name,
					);
				}
			}

			if ( is_array( $data ) && ! empty( $data ) ) {
				$result[] = array(
					'label'   => $label,
					'options' => $data,
				);
			}
		}
		// return the result in json.
		wp_send_json_success( $result );
	}

	/**
	 * Modifies search query to search only title
	 *
	 * @param string $search search string.
	 * @param object $wp_query WP_QUERY object.
	 * @return string
	 * @since 1.0.0
	 */
	public function search_only_titles( $search, $wp_query ) {
		if ( ! empty( $search ) && ! empty( $wp_query->query_vars['search_terms'] ) ) {
			global $wpdb;

			$q = $wp_query->query_vars;
			$n = ! empty( $q['exact'] ) ? '' : '%';

			$search = [];

			foreach ( $q['search_terms'] as $term ) {
				$search[] = $wpdb->prepare( "$wpdb->posts.post_title LIKE %s", $n . $wpdb->esc_like( $term ) . $n );
			}

			if ( ! is_user_logged_in() ) {
				$search[] = "$wpdb->posts.post_password = ''";
			}

			$search = ' AND ' . implode( ' AND ', $search );
		}

		return $search;
	}

	/**
	 * Get user selected values
	 *
	 * @param mixed $post_id post id to retrieve data from.
	 * @return array
	 * @since 1.0.0
	 */
	public function get_selected_locations( $post_id = false ) {
		if ( empty( $post_id ) ) {
			// Ignored nonce verification as we are getting post_id from URL.
			if ( empty( $_GET['post_id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return [];
			}
			// Ignored nonce verification as we are getting post_id from URL.
			$post_id = absint( $_GET['post_id'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$id = absint( $post_id );

		$includes = get_post_meta( $id, SUREMEMBERS_PLAN_INCLUDE, true );
		$rules    = is_array( $includes ) && ! empty( $includes['rules'] ) ? Utils::sanitize_recursively( 'sanitize_text_field', $includes['rules'] ) : [];

		/**
		 * Added filter for learndash legacy value of `learndash-courses` and `learndash-groups`.
		 * Using this filter is discouraged, it is used for backward compatibilty and will be removed in future
		 *
		 * @since 1.4.0
		 */
		$rules = apply_filters( 'suremembers_before_search_rules', $rules );
		return $rules;
	}

	/**
	 * Returns data of specific location i.e. specific, excluded.
	 *
	 * @param string $type type of data to be retrieved 'specific | excluded'.
	 * @param mixed  $post_id post id to retrieve data for.
	 * @return array
	 * @since 1.0.0
	 */
	public function get_individual_data( $type = 'exclude', $post_id = false ) {
		if ( empty( $post_id ) ) {
			// Ignored nonce verification as we are getting post_id from URL.
			if ( empty( $_GET['post_id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return [];
			}

			// Ignored nonce verification as we are getting post_id from URL.
			$post_id = absint( $_GET['post_id'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$id = absint( $post_id );

		switch ( $type ) {
			case 'specific':
				$includes = get_post_meta( $id, SUREMEMBERS_PLAN_INCLUDE, true );
				$data     = is_array( $includes ) && ! empty( $includes['specifics'] ) ? Utils::sanitize_recursively( 'sanitize_text_field', $includes['specifics'] ) : [];
				break;

			default:
				$exclude = get_post_meta( $id, SUREMEMBERS_PLAN_EXCLUDE, true );
				$data    = is_array( $exclude ) && ! empty( $exclude['rules'] ) ? Utils::sanitize_recursively( 'sanitize_text_field', $exclude['rules'] ) : [];
				break;
		}

		if ( empty( $data ) ) {
			return $data;
		}
		return Utils::convert_slug_to_text( $data );
	}

	/**
	 * Fetches current posts drip data
	 *
	 * @param mixed $post_id either provide post id to fetch drip data or by default will fetch data of current post.
	 * @return array
	 * @since 1.0.0
	 */
	public function get_drip_data( $post_id = false ) {
		if ( empty( $post_id ) ) {
			// Ignored nonce verification as we are getting post_id from URL.
			if ( empty( $_GET['post_id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return [];
			}

			// Ignored nonce verification as we are getting post_id from URL.
			$post_id = absint( $_GET['post_id'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$id = absint( $post_id );

		$drips_array = get_post_meta( $id, SUREMEMBERS_PLAN_DRIPS, true );

		if ( empty( $drips_array ) || ! is_array( $drips_array ) ) {
			return [];
		}

		$response = [];
		$i        = 0;
		foreach ( $drips_array as $drip ) {
			if ( empty( $drip['rules'] ) ) {
				continue;
			}

			$texts = Utils::convert_slug_to_text( $drip['rules'] );
			if ( empty( $texts ) ) {
				continue;
			}

			$response[ $i ]['rules']     = $texts;
			$response[ $i ]['date_type'] = ! empty( $drip['date_type'] ) ? $drip['date_type'] : 'after_duration';
			$response[ $i ]['drip_date'] = ! empty( $drip['drip_date'] ) ? $drip['drip_date'] : '';
			$response[ $i ]['delay']     = ! empty( $drip['delay'] ) ? intval( $drip['delay'] ) : 0;
			$response[ $i ]['time']      = isset( $drip['time'] ) && $drip['time'] >= 0 ? floatval( $drip['time'] ) : '';
			$response[ $i ]['periods']   = isset( $drip['periods'] ) && $drip['periods'] >= 0 ? intval( $drip['periods'] ) : '';
			$i++;
		}

		$delays = array_column( $response, 'delay' );
		array_multisort( $delays, SORT_ASC, $response );

		return $response;
	}

	/**
	 * Check for post status change
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function check_status_transition() {
		if ( ! empty( $_GET['post'] ) && ! empty( $_GET['action'] ) && in_array( sanitize_text_field( $_GET['action'] ), [ SUREMEMBERS_ARCHIVE, SUREMEMBERS_ARCHIVE . '_revert' ], true ) && ! empty( $_GET['_wpnonce'] ) ) {
			$post_id = absint( $_GET['post'] );
			$action  = sanitize_text_field( $_GET['action'] );

			if ( ! wp_verify_nonce( sanitize_text_field( $_GET['_wpnonce'] ), sanitize_text_field( $action ) ) ) {
				return;
			}

			if ( SUREMEMBERS_ARCHIVE . '_revert' === $action ) {
				$status = 'publish';
			} else {
				$status = SUREMEMBERS_ARCHIVE;
			}

			$response = wp_update_post(
				array(
					'ID'          => $post_id,
					'post_status' => $status,
				)
			);

			$query_args = [ 'post_type' => SUREMEMBERS_POST_TYPE ];

			if ( isset( $_GET['suremembers_view'] ) && 'iframe' === $_GET['suremembers_view'] ) {
				$query_args['suremembers_view'] = 'iframe';
			}

			if ( $response ) {
				wp_safe_redirect( add_query_arg( $query_args, admin_url( 'edit.php' ) ) );
				exit();
			}
		}
	}

	/**
	 * Url restriction data.
	 *
	 * @param int $post_id Current post id.
	 * @since 1.1.0
	 * @return array
	 */
	public function get_restricted_url_data( $post_id = 0 ) {
		if ( empty( $post_id ) ) {
			// Ignored nonce verification as we are getting post_id from URL.
			if ( empty( $_GET['post_id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return [];
			}

			// Ignored nonce verification as we are getting post_id from URL.
			$post_id = absint( $_GET['post_id'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$get_restricted_url = get_post_meta( $post_id, SUREMEMBERS_RESTRICTED_URL, true );
		$result             = [];
		if ( is_array( $get_restricted_url ) && ! empty( $get_restricted_url['restricted_url'] ) ) {
			$result['restricted_url'] = wp_kses_post( $get_restricted_url['restricted_url'] );
			if ( ! empty( $get_restricted_url['regex'] ) ) {
				$result['restricted_url_reg_exp'] = true;
			}
		}
		return $result;
	}
}
