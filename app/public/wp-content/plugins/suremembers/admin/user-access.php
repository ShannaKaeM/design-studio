<?php
/**
 * Handle user and access groups relationships.
 *
 * @package suremembers
 * @since 1.0.0
 */

namespace SureMembers\Admin;

use SureMembers\Inc\Traits\Get_Instance;
use SureMembers\Inc\Access;
use SureMembers\Inc\Access_Groups;
use SureMembers\Inc\Utils;
use SureMembers\Inc\Restricted;
use SureMembers\Admin\User_Access_Table;
use SureMembers\Admin\Templates;

/**
 * User Access control class.
 *
 * @since 1.0.0
 */
class User_Access {

	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		add_action( 'show_user_profile', [ $this, 'access_groups_selection' ] );
		add_action( 'edit_user_profile', [ $this, 'access_groups_selection' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_user_access_scripts' ] );
		add_action( 'manage_users_extra_tablenav', [ $this, 'filter_by_access_groups' ] );
		add_action( 'admin_footer', [ $this, 'table_row_template' ], 99 );

		// Filters for user access screens.
		add_filter( 'manage_users_columns', [ $this, 'add_user_column_headers' ] );
		add_filter( 'manage_users_custom_column', [ $this, 'add_user_columns' ], 10, 3 );
		add_filter( 'pre_get_users', [ $this, 'filter_users_by_access_groups' ], 99, 1 );
		add_filter( 'bulk_actions-users', [ $this, 'add_edit_in_bulk_actions' ] );

		// AJAX Calls for user table.
		add_action( 'wp_ajax_suremembers_users_edit_actions', [ $this, 'handle_user_edit_actions' ] );
		add_action( 'wp_ajax_suremembers_add_expire_date_to_user', [ $this, 'add_expire_date_to_user' ] );

		add_action( 'wp_ajax_get_access_groups_by_id', [ $this, 'get_access_groups_by_id' ] );
		add_action( 'wp_ajax_suremembers_users_edit_add_access_groups', [ $this, 'add_access_groups_to_user' ] );

		// AJAX Calls for Bulk edit users.
		add_action( 'wp_ajax_suremembers_handle_bulk_access_edit', [ $this, 'bulk_users_edit_action' ] );
	}

	/**
	 * Enqueue a user access script.
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function enqueue_user_access_scripts() {
		$current_screen = get_current_screen();

		wp_register_script( 'suremembers-jquery-select2', SUREMEMBERS_URL . 'admin/assets/js/select2.min.js', [ 'jquery' ], SUREMEMBERS_VER, true );
		wp_register_style( 'suremembers-jquery-select2', SUREMEMBERS_URL . 'admin/assets/css/select2.min.css', [], SUREMEMBERS_VER, 'all' );

		if ( isset( $current_screen->id ) && 'users' === $current_screen->id ) {
			wp_register_script( 'suremembers-user-bulk-edit', SUREMEMBERS_URL . 'admin/assets/js/user-bulk-actions.js', [ 'jquery', 'wp-util', 'suremembers-jquery-select2' ], SUREMEMBERS_VER, true );

			wp_localize_script(
				'suremembers-user-bulk-edit',
				'suremembers_menu_items',
				[
					'security' => current_user_can( 'manage_options' ) ? wp_create_nonce( 'suremembers_queried_access_groups' ) : '',
				]
			);

			wp_enqueue_script( 'suremembers-user-bulk-edit' );
			wp_enqueue_style( 'suremembers-jquery-select2' );
			wp_enqueue_style( 'suremembers-user-bulk-edit', SUREMEMBERS_URL . 'admin/assets/css/user-bulk-edit.css', [ 'suremembers-jquery-select2' ], SUREMEMBERS_VER );
		}

		if ( is_null( $current_screen ) || ! in_array( $current_screen->id, [ 'user-edit', 'profile' ], true ) ) {
			return;
		}

		$user_id = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : get_current_user_id();// phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$script_name = 'suremembers-useraccess';
		wp_enqueue_style( $script_name, SUREMEMBERS_URL . 'admin/assets/css/user-page.css', [ 'suremembers-jquery-select2' ], SUREMEMBERS_VER );
		wp_register_script( $script_name, SUREMEMBERS_URL . 'admin/assets/js/user-page.js', [ 'jquery', 'suremembers-jquery-select2' ], SUREMEMBERS_VER, true );

		$user_access_groups           = get_user_meta( $user_id, SUREMEMBERS_USER_META, true );
		$count                        = 0;
		$assigned_access_groups       = [];
		$published_access_group_count = wp_count_posts( SUREMEMBERS_POST_TYPE )->publish;

		if ( is_array( $user_access_groups ) ) {
			$assigned_access_groups = $user_access_groups;
			$count                  = count( $user_access_groups );
		}
		wp_localize_script(
			$script_name,
			'suremembers_menu_items',
			[
				'user_access_groups'            => $assigned_access_groups,
				'user_access_group_count'       => absint( $count ),
				'published_access_groups_count' => absint( $published_access_group_count ),
				'security'                      => current_user_can( 'manage_options' ) ? wp_create_nonce( 'suremembers_queried_access_groups' ) : '',
			]
		);

		wp_enqueue_script( $script_name );
	}

	/**
	 * Add column headers on user table.
	 *
	 * @param array $columns User column headers.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function add_user_column_headers( $columns ) {
		$new_columns = [];
		foreach ( $columns as $key => $title ) {
			$new_columns[ $key ] = $title;
			if ( 'role' === $key ) {
				$new_columns['access_groups'] = __( 'Active Access Groups', 'suremembers' );
			}
		}
		return $new_columns;
	}

	/**
	 * Add filter by access groups to users table.
	 *
	 * @param string $which Location of filter.
	 * @return void
	 * @since 1.0.0
	 */
	public function filter_by_access_groups( $which ) {
		$get_access_groups     = Access_Groups::get_active();
		$selected_access_group = '';
		$which_alternative     = 'top' === $which ? 'bottom' : 'top';

		$top_filter    = isset( $_GET['suremembers_access_group_top'] ) ? absint( $_GET['suremembers_access_group_top'] ) : '';        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$bottom_filter = isset( $_GET['suremembers_access_group_bottom'] ) ? absint( $_GET['suremembers_access_group_bottom'] ) : '';  // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! empty( $top_filter ) || ! empty( $bottom_filter ) ) {
			$selected_access_group = ! empty( $top_filter ) ? $top_filter : $bottom_filter;
		}
		?>
			<select name="suremembers_access_group_<?php echo esc_attr( $which ); ?>" id="suremembers_access_group_<?php echo esc_attr( $which ); ?>">
				<option value=""><?php esc_html_e( 'All Access Groups', 'suremembers' ); ?></option>
				<?php foreach ( $get_access_groups as $id => $access_group ) : ?>
					<option <?php selected( $selected_access_group, $id ); ?> value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $access_group ); ?></option>
				<?php endforeach; ?>
			</select>
			<script>
				var accessFilter = document.getElementById('suremembers_access_group_<?php echo esc_attr( $which ); ?>');
				if ( null !== accessFilter ) {
					accessFilter.addEventListener( 'change', function(event) {
						var alternateSelect = document.getElementById('suremembers_access_group_<?php echo esc_attr( $which_alternative ); ?>');
						if ( null !== alternateSelect ) {
							alternateSelect.value = event.currentTarget.value;
						}
					});
				}
			</script>
		<?php
		submit_button( __( 'Filter', 'suremembers' ), 'primary', $which, false );
	}

	/**
	 * Add columns on users table
	 *
	 * @param string $output column output.
	 * @param string $column_name column column_name.
	 * @param int    $user_id current user id.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function add_user_columns( $output, $column_name, $user_id ) {
		switch ( $column_name ) {
			case 'access_groups':
				$access_groups = get_user_meta( $user_id, SUREMEMBERS_USER_META, true );
				if ( empty( $access_groups ) || ! is_array( $access_groups ) ) {
					return $output;
				}

				$access_group_links = [];
				foreach ( $access_groups as $id => $access_id ) {
					$plan_active_for_user = Access_Groups::check_plan_active( $user_id, $access_id );
					// Displaying only active access groups.
					if ( ! Access_Groups::is_active_access_group( $access_id ) || ! $plan_active_for_user ) {
						continue;
					}
					$edit_url = Access_Groups::get_admin_url(
						[
							'page'    => 'suremembers_rules',
							'post_id' => $access_id,
						]
					);

					$link_class = Access_Groups::check_plan_active( $user_id, $access_id ) ? 'suremembers-plan-active' : 'suremembers-plan-inactive';

					$access_group_links[] = sprintf(
						'<a class="%1$s" href="%2$s">%3$s</a>',
						$link_class,
						esc_url( $edit_url ),
						get_the_title( $access_id )
					);
				}

				$output = implode( ', ', $access_group_links );
				break;
			default:
		}
		return $output;
	}

	/**
	 * Add field to add access groups to user.
	 *
	 * @param object $user User Object.
	 * @return void
	 * @since 1.0.0
	 */
	public function access_groups_selection( $user ) {

		if ( ! current_user_can( 'administrator' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_posts' ) || is_network_admin() ) {
			return;
		}

		$user_id                 = isset( $user->ID ) ? $user->ID : 0;
		$published_access_groups = wp_count_posts( SUREMEMBERS_POST_TYPE )->publish;
		$user_access_groups      = get_user_meta( $user_id, SUREMEMBERS_USER_META, true );

		$get_access_groups = Access_Groups::get_active();

		$archive_plans = apply_filters(
			'suremembers_get_access_groups',
			[
				'post_type'   => SUREMEMBERS_POST_TYPE,
				'post_status' => 'archive',
			]
		);
		$archive_plans = get_posts( $archive_plans );
		// Add archive plans to the list.
		foreach ( $archive_plans as $value ) {
			$get_access_groups[ $value->ID ] = $value->post_title;
		}
		// Check if user has access groups which is exists or not.
		if ( ! empty( $user_access_groups ) && is_array( $user_access_groups ) ) {
			foreach ( $user_access_groups as $key => $value ) {
				if ( ! isset( $get_access_groups[ $value ] ) ) {
					unset( $user_access_groups[ $key ] );
				}
			}
		}

		update_user_meta( $user_id, SUREMEMBERS_USER_META, $user_access_groups );

		$user_access_groups_count = is_array( $user_access_groups ) ? count( $user_access_groups ) : 0;

		if ( 0 !== absint( $published_access_groups ) && absint( $published_access_groups ) > $user_access_groups_count ) {
			Templates::access_group_selection_markup( $user );
		}

		$table = new User_Access_Table();
		$table->prepare_items();
		?>
		<div data-nonce="<?php echo esc_attr( wp_create_nonce( 'handle_user_edit_actions' ) ); ?>" id="suremembers-user-access-list"  method="POST">
			<input type="hidden" name="page" value="1"/>
			<?php $table->display(); ?>
		</div>
		<?php
	}

	/**
	 * Filter Users by access groups.
	 *
	 * @param \WP_User_Query $query The query object.
	 * @return object The filtered query object.
	 * @since 1.0.0
	 */
	public function filter_users_by_access_groups( $query ) {

		if ( ! is_admin() ) {
			return $query;
		}

		global $pagenow;

		if ( 'users.php' === $pagenow ) {
			$top_filter    = isset( $_GET['suremembers_access_group_top'] ) ? absint( $_GET['suremembers_access_group_top'] ) : null;// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$bottom_filter = isset( $_GET['suremembers_access_group_bottom'] ) ? absint( $_GET['suremembers_access_group_bottom'] ) : null;// phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( ! empty( $top_filter ) || ! empty( $bottom_filter ) ) {
				remove_filter( 'pre_get_users', [ $this, 'filter_users_by_access_groups' ], 99 );
				$users        = get_users( array( 'fields' => array( 'ID' ) ) );
				$users__in    = [];
				$access_group = ! empty( $top_filter ) ? $top_filter : $bottom_filter;

				if ( ! empty( $users ) ) {
					foreach ( $users as $key => $user ) {
						if ( ! Access_Groups::check_plan_active( $user->ID, $access_group ) ) {
							continue;
						}
						$users__in[ $user->ID ] = absint( $user->ID );
					}
				}

				if ( ! empty( $users__in ) ) {
					$query->set( 'include', implode( ',', $users__in ) );
				} else {
					// Set no results for active filter.
					$query->set( 'include', [ 0 ] );
				}

				add_filter( 'pre_get_users', [ $this, 'filter_users_by_access_groups' ], 99, 1 );
			}
		}

		return $query;
	}

	/**
	 * Get Row Template.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function table_row_template() {
		$current_screen = get_current_screen();
		if ( is_null( $current_screen ) ) {
			return;
		}
		if ( 'user-edit' === $current_screen->id || 'profile' === $current_screen->id ) {
			?>
				<script type="text/html" id="tmpl-suremembers-users-access-group-row">
					<# for ( access_group in data.access_groups ) {
						var current_access_group = data.access_groups[access_group];
						var rg_action = 'revoked' === current_access_group.status ? 'grant_access' : 'revoke_access';
					#>
						<tr id="suremembers-access-group-{{current_access_group.access_id}}">
							<td class="has-row-actions column-primary">
								<a
									href="{{current_access_group.edit_link}}">{{current_access_group.label}} <br>
								</a>
								<div class="row-actions"><a
										href="{{current_access_group.edit_link}}">
										<span class="edit">
										</span></a><a
										href="{{current_access_group.edit_link}}">Edit</a>
									|
								</div>
							</td>
							<td class="column-primary suremembers-access-group-status">
							{{current_access_group.status}} </td>
							<td class="column-primary">
							{{current_access_group.created}} </td>
							<td class="column-primary">
							{{current_access_group.modified}} </td>
							<td class="column-primary">
							<# if ( current_access_group.integration ) { #>
								<img style='height:24px;' src="{{current_access_group.integration}}">
							<# } #>
							</td>
							<td>
								<a href="#" class="suremembers-user-actions" data-action="{{rg_action}}" data-access="{{current_access_group.access_id}}" data-user="{{current_access_group.user_id}}">{{current_access_group.action}}</a>
							</td>
							<td>
							<#
								var expire_date = '';
								if ('true' === current_access_group.expire_date['enable']) {
									var delay = parseInt(current_access_group.expire_date['delay']);
									var currentDate = new Date();
									currentDate.setDate(currentDate.getDate() + delay);

									var new_date = currentDate.toISOString().substr(0, 10);

									if ('relative_date' === current_access_group.expire_date['type']) { #>
										<input type="date" class="suremembers-expire-date" value="{{new_date}}" data-access="{{current_access_group.access_id}}" data-user="{{current_access_group.user_id}}" />
									<#
									} else {
									#>
										{{ current_access_group.expire_date['specific_date'] }}
									<#
									}
								}
								#>
							</td>
						</tr>
					<# } #>
				</script>
			<?php
		}

		if ( 'users' === $current_screen->id ) {
			Templates::users_bulk_edit_template();
		}
	}

	/**
	 * AJAX handler for user edit page actions.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function handle_user_edit_actions() {
		if ( ! isset( $_POST['_ajax_nonce'] ) || empty( $_POST['_ajax_nonce'] ) ) {
			wp_send_json_error( [ 'message' => __( 'AJAX validation failed', 'suremembers' ) ] );
		}

		\check_ajax_referer( 'handle_user_edit_actions', sanitize_text_field( $_POST['_ajax_nonce'] ) );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Current user does not have required permission.', 'suremembers' ) ] );
		}

		$user_id   = isset( $_POST['data']['userID'] ) ? absint( $_POST['data']['userID'] ) : false;
		$action    = isset( $_POST['data']['action'] ) ? sanitize_title( $_POST['data']['action'] ) : false;
		$access_id = isset( $_POST['data']['access'] ) ? absint( $_POST['data']['access'] ) : false;

		if ( ! $user_id || ! $access_id || ! $action ) {
			wp_send_json_error( [ 'message' => __( 'Missing required parameters.', 'suremembers' ) ] );
		}

		if ( 'revoke_access' === $action ) {
			Access::revoke( $user_id, $access_id );
		} elseif ( 'grant_access' === $action ) {
			Access::grant( $user_id, $access_id, 'suremembers' );
		}

		wp_send_json_success();
	}

	/**
	 * Get access groups by user id.
	 *
	 * @param int $user_id User ID.
	 * @return void
	 * @since 1.0.0
	 */
	public function get_access_groups_by_id( $user_id ) {
		if ( ! isset( $_POST['_ajax_nonce'] ) || empty( $_POST['_ajax_nonce'] ) ) {
			wp_send_json_error( [ 'message' => __( 'AJAX validation failed', 'suremembers' ) ] );
		}
		\check_ajax_referer( 'handle_user_edit_actions', sanitize_text_field( $_POST['_ajax_nonce'] ) );

		$user_id = isset( $_POST['userID'] ) ? absint( $_POST['userID'] ) : false;

		if ( ! $user_id ) {
			wp_send_json_error( [ 'message' => __( 'Missing required parameters.', 'suremembers' ) ] );
		}

		$access_groups          = get_user_meta( $user_id, SUREMEMBERS_USER_META, true );
		$formated_access_groups = [];

		if ( ! empty( $access_groups ) && is_array( $access_groups ) ) {
			foreach ( $access_groups as $key => $id ) {
				// Check if access group is active.
				if ( ! Access_Groups::is_active_access_group( $id ) ) {
					continue;
				}
				$plan_details = Restricted::get_plan_details( $user_id, $id );
				$edit_url     = add_query_arg(
					[
						'post_type' => SUREMEMBERS_POST_TYPE,
						'page'      => 'suremembers_rules',
						'post_id'   => $id,
					],
					admin_url( 'edit.php' )
				);

				$access_group_expiration = get_post_meta( $id, SUREMEMBERS_PLAN_EXPIRATION, true );
				$integration_icon        = '';
				if ( is_array( $plan_details ) && ! empty( $plan_details['integration'] ) ) {
					$logo_url = Utils::integration_icons( $plan_details['integration'] );
					if ( is_string( $logo_url ) && ! empty( $logo_url ) ) {
						$logo = file_get_contents( $logo_url );// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
						if ( $logo ) {
							$integration_icon = 'data:image/svg+xml;base64,' . base64_encode( $logo ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
						}
					}
				}

				$created  = 0;
				$modified = 0;
				$status   = '';
				if ( is_array( $plan_details ) ) {
					$created  = isset( $plan_details['created'] ) ? $plan_details['created'] : $created;
					$modified = isset( $plan_details['modified'] ) ? $plan_details['modified'] : $modified;
					$status   = isset( $plan_details['status'] ) ? $plan_details['status'] : $status;
				}

				$formated_access_groups[ $key ] = [
					'access_id'   => $id,
					'label'       => get_the_title( $id ),
					/* translators:  %1$s Time created. */
					'created'     => sprintf( esc_html__( 'Created %1$s ago', 'suremembers' ), esc_html( human_time_diff( $created, current_time( 'U' ) ) ) ), // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
					/* translators:  %1$s Time modified. */
					'modified'    => ! empty( $modified ) ? sprintf( esc_html__( 'Updated %1$s ago', 'suremembers' ), esc_html( human_time_diff( $modified, current_time( 'U' ) ) ) ) : '', // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
					'status'      => $status,
					'action'      => 'revoked' === $status ? __( 'Grant Access', 'suremembers' ) : __( 'Revoke Access', 'suremembers' ),
					'user_id'     => $user_id,
					'edit_link'   => $edit_url,
					'integration' => $integration_icon,
					'expire_date' => $access_group_expiration,
				];
			}
		}

		wp_send_json_success( [ 'access_groups' => $formated_access_groups ] );

	}

	/**
	 * AJAX call to add user access groups.
	 *
	 * @return void
	 * @since 1.9.0
	 */
	public function add_expire_date_to_user() {
		if ( empty( $_POST['_ajax_nonce'] ) ) {
			wp_send_json_error( [ 'message' => __( 'AJAX validation failed', 'suremembers' ) ] );
		}

		\check_ajax_referer( 'handle_user_edit_actions', sanitize_text_field( $_POST['_ajax_nonce'] ) );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Current user does not have required permission.', 'suremembers' ) ] );
		}

		if ( isset( $_POST['data'] ) && is_array( $_POST['data'] ) ) {

			$user_id   = isset( $_POST['data']['userID'] ) ? absint( $_POST['data']['userID'] ) : '';
			$date      = isset( $_POST['data']['date'] ) ? sanitize_text_field( $_POST['data']['date'] ) : '';
			$access_id = isset( $_POST['data']['access'] ) ? absint( $_POST['data']['access'] ) : '';

			if ( ! $user_id || ! $access_id || ! $date ) {
				wp_send_json_error( [ 'message' => __( 'Missing required parameters.', 'suremembers' ) ] );
			}

			$expiration_date[ $access_id ] = $date;

			Access::grant( $user_id, $access_id, 'suremembers', $expiration_date );

			wp_send_json_success();

		} else {
			wp_send_json_error( [ 'message' => __( 'Invalid data format.', 'suremembers' ) ] );
		}

	}

	/**
	 * AJAX call to add user access groups.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function add_access_groups_to_user() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Current user does not have required permission.', 'suremembers' ) ] );
		}

		if ( ! isset( $_POST['_ajax_nonce'] ) || empty( $_POST['_ajax_nonce'] ) ) {
			wp_send_json_error( [ 'message' => __( 'AJAX validation failed', 'suremembers' ) ] );
		}
		\check_ajax_referer( 'handle_user_edit_actions', sanitize_text_field( $_POST['_ajax_nonce'] ) );

		$user_id = isset( $_POST['userID'] ) ? absint( $_POST['userID'] ) : false;
		// ignoring as sanitize is done with recursive function.
		$access_ids = isset( $_POST['accessIDs'] ) ? Utils::sanitize_recursively( 'absint', $_POST['accessIDs'] ) : false; //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( ! $user_id || ! $access_ids ) {
			wp_send_json_error( [ 'message' => __( 'Missing required parameters.', 'suremembers' ) ] );
		}

		Access::grant( $user_id, $access_ids, 'suremembers' );

		wp_send_json_success( [ 'added_ids' => $access_ids ] );
	}

	/**
	 * AJAX call to add users to access groups through bulk edit.
	 *
	 * @return void
	 * @since 1.2.0
	 */
	public function bulk_users_edit_action() {
		if ( ! isset( $_POST['nonce'] ) || empty( $_POST['nonce'] ) ) {
			wp_send_json_error( [ 'message' => __( 'AJAX validation failed', 'suremembers' ) ] );
		}
		if ( ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'suremembers_bulk_actions_nonce' ) ) {
			wp_send_json_error( [ 'message' => __( 'Nonce validation failed', 'suremembers' ) ] );
		};

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Current user does not have required permission.', 'suremembers' ) ] );
		}

		// Ignored as sanitization is done by recursive function.
		$user_ids   = isset( $_POST['user_ids'] ) && is_array( $_POST['user_ids'] ) ? Utils::sanitize_recursively( 'absint', $_POST['user_ids'] ) : false; //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$access_ids = isset( $_POST['access_groups'] ) ? Utils::sanitize_recursively( 'absint', $_POST['access_groups'] ) : false;//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$action     = isset( $_POST['user_action'] ) ? sanitize_text_field( $_POST['user_action'] ) : false;

		if ( ! $user_ids || ! $access_ids || ! $action ) {
			wp_send_json_error( [ 'message' => __( 'Missing required parameters.', 'suremembers' ) ] );
		}

		foreach ( $user_ids as $user_id ) {
			if ( 'suremembers_revoke_bulk_access' === $action ) {
				Access::revoke( $user_id, $access_ids );
			} elseif ( 'suremembers_grant_bulk_access' === $action ) {
				Access::grant( $user_id, $access_ids );
			}
		}

		wp_send_json_success( [ 'added_ids' => $access_ids ] );
	}

	/**
	 * Add Edit in bulk action for user table.
	 *
	 * @param array $bulk_array Bulk actions array.
	 * @return array Updated Array
	 * @since 1.2.0
	 */
	public function add_edit_in_bulk_actions( $bulk_array ) {
		$bulk_array['suremembers_edit_users'] = __( 'Bulk Edit', 'suremembers' );
		return $bulk_array;
	}
}
