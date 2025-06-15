<?php
/**
 * User edit page access table.
 *
 * @package suremembers
 * @since 1.0.0
 */

namespace SureMembers\Admin;

use SureMembers\Inc\Traits\Get_Instance;
use SureMembers\Inc\Access;
use SureMembers\Inc\Access_Groups;
use SureMembers\Inc\Restricted;
use SureMembers\Inc\Utils;
use WP_List_Table;

/**
 * Create a new table class that will extend the WP_List_Table
 *
 * @package suremembers
 * @since 1.0.0
 */
class User_Access_Table extends WP_List_Table {

	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => esc_html__( 'Access Group', 'suremembers' ),
				'plural'   => esc_html__( 'Access Groups', 'suremembers' ),
			)
		);
	}

	/**
	 * Render column values.
	 *
	 * @param array  $item item.
	 * @param string $column_name column name.
	 * @return bool|string|void
	 * @since 1.0.0
	 */
	public function column_default( $item, $column_name ) {
		$user_id      = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : get_current_user_id();// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$plan_details = Restricted::get_plan_details( $user_id, $item['id'] );

		switch ( $column_name ) {
			case 'access_group':
				$edit_url = Access_Groups::get_admin_url(
					[
						'page'    => 'suremembers_rules',
						'post_id' => $item['id'],
					]
				);
				$actions  = [
					'edit' => '<a class="edit" href="' . esc_url( $edit_url ) . '">' . esc_html__( 'Edit', 'suremembers' ) . '</a>',
				];
				echo sprintf(
					'<a href="' . esc_url( $edit_url ) . '">%s %s',
					esc_html( $item['title'] ),
					wp_kses_post( $this->row_actions( $actions ) )
				);
				break;
			case 'status':
				$status = ! empty( $plan_details ) && is_array( $plan_details ) && isset( $plan_details['status'] ) ? $plan_details['status'] : '';
				echo esc_html( $status );
				break;
			case 'created_on':
				if ( ! empty( $plan_details ) && is_array( $plan_details ) ) {
					if ( isset( $plan_details['created'] ) && ! empty( $plan_details['created'] ) ) {
						/* translators:  %1$s Time created. */
						echo sprintf( esc_html__( 'Created %1$s ago', 'suremembers' ), esc_html( human_time_diff( $plan_details['created'], current_time( 'U' ) ) ) ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
					}
				}
				break;
			case 'updated_on':
				if ( ! empty( $plan_details ) && is_array( $plan_details ) ) {
					if ( isset( $plan_details['modified'] ) && ! empty( $plan_details['modified'] ) ) {
						/* translators:  %1$s Time modified. */
						echo sprintf( esc_html__( 'Updated %1$s ago', 'suremembers' ), esc_html( human_time_diff( $plan_details['modified'], current_time( 'U' ) ) ) ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
					}
				}
				break;
			case 'integration':
				if ( ! empty( $plan_details ) && is_array( $plan_details ) ) {
					if ( ! empty( $plan_details['integration'] ) ) {
						$logo_url = Utils::integration_icons( $plan_details['integration'] );
						if ( is_string( $logo_url ) && ! empty( $logo_url ) ) {
							$logo = file_get_contents( $logo_url );// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
							if ( $logo ) {
								$image = 'data:image/svg+xml;base64,' . base64_encode( $logo );// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
								echo '<img title="' . esc_attr( $plan_details['integration'] ) . '" style="height:24px;" src="' . esc_attr( $image ) . '">'; // phpcs:ignore WordPressVIPMinimum.Security.ProperEscapingFunction.hrefSrcEscUrl
								// Ignoring above as we need to display base64 image in src and data is local from plugin.
							}
						}
					}
				}
				break;
			case 'action':
				if ( Access_Groups::check_plan_active( $user_id, $item['id'] ) ) {
					$action = 'revoke_access';
					$label  = __( 'Revoke Access', 'suremembers' );
				} else {
					$action = 'grant_access';
					$label  = __( 'Grant Access', 'suremembers' );
				}
				?>
				<a href="#" class="suremembers-user-actions" data-action="<?php echo esc_attr( $action ); ?>" data-access="<?php echo esc_attr( strval( $item['id'] ) ); ?>" data-user="<?php echo esc_attr( strval( $user_id ) ); ?>"><?php echo esc_html( $label ); ?></a>
				<?php
				break;
			case 'expire_date':
				$expiration  = get_post_meta( $item['id'], SUREMEMBERS_PLAN_EXPIRATION, true );
				$user_expire = get_user_meta( $user_id, SUREMEMBERS_USER_EXPIRATION, true );

				if ( ! empty( $expiration ) && is_array( $expiration ) && isset( $expiration['type'] ) && isset( $expiration['delay'] ) ) {
					if ( 'relative_date' === ( $expiration['type'] ) ) {
						$current_date     = ( is_array( $plan_details ) && ! empty( $plan_details['modified'] ) ) ? $plan_details['modified'] : time();
						$future_date      = '';
						$future_timestamp = strtotime( '+' . $expiration['delay'] . ' days', $current_date );

						if ( false !== $future_timestamp ) {
							$future_date = date( 'Y-m-d', $future_timestamp ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
						}
						if ( isset( $user_expire ) && is_array( $user_expire ) && isset( $user_expire[ $item['id'] ] ) ) {
							$expire_date = sanitize_text_field( strval( $user_expire[ $item['id'] ] ) );
						} else {
							$expire_date = $future_date;
						}
						if ( current_user_can( 'manage_options' ) ) {
							?>
							<input type="date" class="suremembers-expire-date" value="<?php echo esc_attr( $expire_date ); ?>" data-access="<?php echo esc_attr( $item['id'] ); ?>"  data-user="<?php echo esc_attr( strval( $user_id ) ); ?>"/>
							<?php
						} else {
							echo esc_html( $expire_date );
						}
					} elseif ( 'specific_date' === ( $expiration['type'] ) ) {
						echo esc_html( $expiration['specific_date'] );
					}
				}
				break;

		}
	}

	/**
	 * Show columns.
	 *
	 * @return array Columns.
	 * @since 1.0.0
	 */
	public function get_columns() {
		$columns = [
			'access_group' => esc_html__( 'Access Group', 'suremembers' ),
			'status'       => esc_html__( 'Status', 'suremembers' ),
			'created_on'   => esc_html__( 'Created On', 'suremembers' ),
			'updated_on'   => esc_html__( 'Updated On', 'suremembers' ),
			'integration'  => esc_html__( 'Integration', 'suremembers' ),
			'action'       => esc_html__( 'Action', 'suremembers' ),
			'expire_date'  => esc_html__( 'Expiration', 'suremembers' ),
		];

		return $columns;
	}

	/**
	 * Get sortable columns.
	 *
	 * @return Array
	 * @since 1.0.0
	 */
	public function get_sortable_columns() {
		return [];
	}

	/**
	 * Add bulk operations.
	 *
	 * @return Array
	 * @since 1.0.0
	 */
	public function get_bulk_actions() {
		return [];
	}

	/**
	 * Get the table data
	 *
	 * @return Array Table Data.
	 * @since 1.0.0
	 */
	private function table_data() {
		$user_id           = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : get_current_user_id();// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$data              = [];
		$user_access_group = get_user_meta( $user_id, SUREMEMBERS_USER_META, true );

		if ( ! is_array( $user_access_group ) ) {
			return $data;
		}

		$user_access_group = array_filter(
			$user_access_group,
			function( $access_group ) {
				return Access_Groups::is_active_access_group( $access_group );
			}
		);

		if ( ! empty( $user_access_group ) && is_array( $user_access_group ) ) {
			foreach ( $user_access_group as $key => $access_group_id ) {
				$data[ $key ] = [
					'id'    => $access_group_id,
					'title' => get_the_title( $access_group_id ),
				];
			}
		}
		return $data;
	}


	/**
	 * Prepare items.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function prepare_items() {

		$columns  = $this->get_columns();
		$hidden   = [];
		$sortable = $this->get_sortable_columns();

		$data = $this->table_data();

		$per_page     = 10;
		$current_page = $this->get_pagenum();

		$this->set_pagination_args(
			[
				'total_items' => count( $data ),
				'per_page'    => $per_page,
			]
		);

		$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

		$this->_column_headers = [ $columns, $hidden, $sortable ];
		$this->items           = $data;
	}

	/**
	 * Displays the table.
	 *
	 * @since 1.0.1
	 * @return void
	 */
	public function display() {
		$singular = $this->_args['singular'];
		$this->screen->render_screen_reader_content( 'heading_list' );
		?>
		<table class="wp-list-table <?php echo esc_attr( implode( ' ', $this->get_table_classes() ) ); ?>">
			<thead>
				<tr>
					<?php $this->print_column_headers(); ?>
				</tr>
			</thead>
			<tbody id="the-list"
				<?php
				if ( $singular ) {
					echo " data-wp-lists='list:" . esc_attr( $singular ) . "'";
				}
				?>
				>
				<?php $this->display_rows_or_placeholder(); ?>
			</tbody>

			<tfoot>
			<tr>
				<?php $this->print_column_headers( false ); ?>
			</tr>
			</tfoot>

		</table>
		<?php
		$this->display_tablenav( 'bottom' );
	}
}
