<?php
/**
 * Admin bar handler.
 *
 * @package suremembers
 * @since 1.0.0
 */

namespace SureMembers\Inc;

use SureMembers\Inc\Traits\Get_Instance;

/**
 * Admin bar handler class.
 *
 * @since 1.0.0
 */
class Admin_Bar {

	use Get_Instance;

	/**
	 * Node menu ID
	 *
	 * @var string
	 * @since 1.0.0
	 */
	private $node_menu_id = 'suremembers-admin-menu-bar';

	/**
	 * Node access level
	 *
	 * @var string
	 * @since 1.0.0
	 */
	private $node_access_level = 'levels';

	/**
	 * Class Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_bar_menu', [ $this, 'add_admin_menu' ], 99 );
		add_action( 'wp_head', [ $this, 'menu_styles' ] );
		add_action( 'admin_head', [ $this, 'menu_styles' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'admin_bar_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_bar_scripts' ] );
		add_action( 'wp_footer', [ $this, 'admin_bar_template' ] );

		// Admin Bar Ajax calls.
		add_action( 'wp_ajax_suremembers_fetch_adminbar_groups', [ $this, 'fetch_adminbar_access_groups' ] );
		add_action( 'wp_ajax_nopriv_suremembers_fetch_adminbar_groups', [ $this, 'fetch_adminbar_access_groups' ] );
	}

	/**
	 * Load Admin Bar Scripts
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function admin_bar_scripts() {
		// Check if current user can create access groups.
		if ( ! $this->check_user_cap() ) {
			return;
		}

		if ( is_admin() ) {
			$screen = get_current_screen();
			// Bail if not add/edit post page.
			if ( ! isset( $screen->base ) || 'post' !== $screen->base ) {
				return;
			}
		}

		global $post;

		$post_object = [];
		if ( isset( $post->post_type ) && is_singular() ) {
			$post_object = get_post_type_object( $post->post_type );
		}

		$current_content_type = Restricted::get_current_content_type();

		$app               = 'adminbar';
		$script_asset_path = SUREMEMBERS_DIR . 'admin/assets/build/' . $app . '.asset.php';
		$script_info       = file_exists( $script_asset_path )
			? include $script_asset_path
			: [
				'dependencies' => [],
				'version'      => SUREMEMBERS_VER,
			];
		$script_dep        = array_merge( $script_info['dependencies'], [ 'wp-util' ] );
		wp_register_script( 'suremembers-admin-bar-script', SUREMEMBERS_URL . 'admin/assets/build/adminbar.js', $script_dep, SUREMEMBERS_VER, true );
		$localizations_array = [
			/* translators: %1$s Current content type. */
			'modal_title'       => ! empty( $post_object ) ? sprintf( __( 'Restrict this %1$s', 'suremembers' ), $post_object->labels->singular_name ) : __( 'Restrict this content', 'suremembers' ),
			'nonce'             => wp_create_nonce( 'suremembers_adminbar_nonce' ),
			'ajax_url'          => \admin_url( 'admin-ajax.php' ),
			'current_page_type' => $current_content_type,
		];
		if ( isset( $post->ID ) ) {
			$localizations_array['current_post_id'] = absint( $post->ID );
		}
		wp_localize_script(
			'suremembers-admin-bar-script',
			'suremembers_adminbar',
			$localizations_array
		);
		wp_enqueue_script( 'suremembers-admin-bar-script' );

		wp_enqueue_style( 'suremembers-admin-bar-script', SUREMEMBERS_URL . 'admin/assets/build/adminbar.css', [ 'wp-components' ], SUREMEMBERS_VER );
	}

	/**
	 * Ajax call to get active access groups.
	 *
	 * @return void
	 */
	public function fetch_adminbar_access_groups() {
		check_ajax_referer( 'suremembers_adminbar_nonce', 'security' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Current user does not have required permission.', 'suremembers' ) ] );
		}

		$options_array     = [];
		$current_post_id   = isset( $_POST['current_post_id'] ) ? absint( $_POST['current_post_id'] ) : false;
		$current_page_type = isset( $_POST['current_page_type'] ) ? sanitize_text_field( $_POST['current_page_type'] ) : false;
		if ( $current_post_id ) {
			$options_array['current_post_id']   = $current_post_id;
			$options_array['current_post_type'] = get_post_type( $current_post_id );
		}
		if ( $current_page_type ) {
			$options_array['current_page_type'] = $current_page_type;
		}
		wp_send_json_success( [ 'access_groups' => $this->get_active_access_groups( $options_array ) ] );
	}

	/**
	 * Add menu styles.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function menu_styles() {
		// Check if current user can create access groups.
		if ( ! $this->check_user_cap() ) {
			return;
		}

		if ( is_admin() ) {
			$screen = get_current_screen();
			// Bail if not add/edit post page.
			if ( ! isset( $screen->base ) || 'post' !== $screen->base ) {
				return;
			}
		}

		$logo = file_get_contents( SUREMEMBERS_DIR . 'admin/assets/images/adminbar-icon.svg' ); //phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$logo = is_string( $logo ) && ! empty( $logo ) ? $logo : '';
		echo '<style type="text/css" media="screen">' . "\n"; ?>
			#wp-admin-bar-suremembers-admin-menu-bar {
				color: #ffffff;
			}
			#wp-admin-bar-<?php echo esc_html( $this->node_menu_id ); ?> .suremembers-admin-logo {
				float: left;
				width: 20px;
				height: 30px;
				background-repeat: no-repeat;
				background-position: center;
				background-size: 20px auto;
				color: #f0f0f1;
				background-image: url("<?php echo 'data:image/svg+xml;base64,' . esc_attr( base64_encode( $logo ) );//phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode ?>");
			}
		<?php
		echo '</style>';
	}

	/**
	 * Add Admin menu item
	 *
	 * @param \WP_Admin_Bar $admin_bar Admin bar.
	 * @return void
	 * @since 1.0.0
	 */
	public function add_admin_menu( $admin_bar ) {
		global $post;

		if ( is_admin() ) {
			$screen = get_current_screen();
			// Bail if not add/edit post page.
			if ( ! isset( $screen->base ) || 'post' !== $screen->base ) {
				return;
			}
		}

		$post_object = [];
		if ( isset( $post->post_type ) && is_singular() ) {
			$post_object = get_post_type_object( $post->post_type );
		}

		$post_type_object = get_post_type_object( SUREMEMBERS_POST_TYPE );
		// Check if current user can create access groups.
		if ( ! $this->check_user_cap() ) {
			return;
		}

		// Check if post->ID exists.
		if ( ! isset( $post->ID ) ) {
			return;
		}

		$active_access_groups = $this->get_active_access_groups();

		$this->add_node(
			$admin_bar,
			[
				'id'    => $this->node_menu_id,
				'title' => '<span class="ab-item suremembers-admin-logo"></span>',
				'meta'  => [
					'title' => __( 'SureMembers Access Groups', 'suremembers' ),
					'class' => ! empty( $active_access_groups ) ? 'suremembers-has-restrictions' : '',
				],
			]
		);

		$admin_bar->add_group(
			[
				'id'     => $this->node_menu_id . '-' . $this->node_access_level,
				'parent' => $this->node_menu_id,
				'meta'   => [
					'class' => 'ab-sub-secondary',
				],
			]
		);

		$this->add_nodes( $admin_bar, $active_access_groups, $this->node_access_level );

		if ( empty( $active_access_groups ) ) {
			$active_access_groups = [
				'id'    => 'no_levels',
				/* translators: %1$s Post type singular label. */
				'title' => ! empty( $post_object ) ? sprintf( __( '%1$s is not restricted', 'suremembers' ), $post_object->labels->singular_name ) : __( 'Page is Not Restricted', 'suremembers' ),
			];
			$this->add_node( $admin_bar, $active_access_groups, $this->node_access_level );
		}

		$access_group_label = isset( $post_type_object->labels->all_items ) ? $post_type_object->labels->all_items : __( 'All Access Groups', 'suremembers' );
		$this->add_node(
			$admin_bar,
			[
				'id'    => 'all_access_groups',
				'title' => $access_group_label,
				'href'  => Access_Groups::get_admin_url(),
				'meta'  => [
					'title' => __( 'All Access Groups', 'suremembers' ),
					'class' => 'suremembers_adbar_itm',
				],
			]
		);

		$this->add_node(
			$admin_bar,
			[
				'id'    => 'new_access_group',
				'title' => __( 'New Access Group', 'suremembers' ),
				'href'  => Access_Groups::get_admin_url( [ 'page' => 'suremembers_rules' ] ),
				'meta'  => [
					'title' => __( 'Add New Access Group', 'suremembers' ),
					'class' => 'suremembers_adbar_itm',
				],
			]
		);

		$this->add_node(
			$admin_bar,
			[
				'id'    => 'suremembers_admin_bar_menu_hldr',
				'title' => __( 'Popup Holder', 'suremembers' ),
				'href'  => '#',
			]
		);
	}

	/**
	 * Get active access groups for current page.
	 *
	 * @param array $options_array Array of Options.
	 * @return array $active_access_groups Active access groups.
	 */
	public function get_active_access_groups( $options_array = [] ) {
		global $post;
		$defaults_array = [
			'include'   => SUREMEMBERS_PLAN_INCLUDE,
			'exclusion' => SUREMEMBERS_PLAN_EXCLUDE,
			'priority'  => SUREMEMBERS_PLAN_PRIORITY,
		];
		if ( isset( $post->ID ) ) {
			$defaults_array['current_post_id'] = absint( $post->ID );
		}
		$option = wp_parse_args(
			$options_array,
			$defaults_array
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
		return $active_access_groups;
	}

	/**
	 * Admin bar list template to show active access groups.
	 *
	 * @return void
	 */
	public function admin_bar_template() {
		// Check if user has access.
		if ( ! $this->check_user_cap() ) {
			return;
		}
		?>
			<script type="text/html" id="tmpl-suremembers-admin-bar-access-list">
				<# for ( access_group in data.access_groups ) {
					var current_access_group = data.access_groups[access_group];
				#>
					<li id="wp-admin-bar-suremembers-admin-menu-bar-levels-{{current_access_group.id}}" class="{{current_access_group.meta.class}}">
						<a class="ab-item" href="{{current_access_group.href}}" title="{{current_access_group.meta.title}}">{{current_access_group.title}}</a>
					</li>
				<# } #>
			</script>
		<?php
	}

	/**
	 * Add Node to Admin Menu.
	 *
	 * @param \WP_Admin_Bar $admin_bar WP_Admin_Bar Admin Bar.
	 * @param array         $args Arguments array.
	 * @param string        $parent Parent node id.
	 * @return self
	 * @since 1.0.0
	 */
	private function add_node( $admin_bar, $args, $parent = null ) {
		if ( $args['id'] !== $this->node_menu_id ) {
			$args['parent'] = $this->node_menu_id . ( ! is_null( $parent ) ? '-' . $parent : '' );
			$args['id']     = $args['parent'] . '-' . $args['id'];
		}
		$admin_bar->add_node( $args );

		return $this;
	}

	/**
	 * Add nodes multiple.
	 *
	 * @param \WP_Admin_Bar $admin_bar Admin bar.
	 * @param array         $nodes nodes.
	 * @param string|null   $parent parent node.
	 * @return void
	 * @since 1.0.0
	 */
	private function add_nodes( $admin_bar, $nodes, $parent = null ) {
		usort( $nodes, [ $this, 'sort_nodes' ] );
		foreach ( $nodes as $node_args ) {
			$this->add_node( $admin_bar, $node_args, $parent );
		}
	}

	/**
	 * Sort nodes.
	 *
	 * @param array $a first.
	 * @param array $b second.
	 * @return int
	 * @since 1.0.0
	 */
	private function sort_nodes( $a, $b ) {
		return strcasecmp( $a['id'], $b['id'] );
	}

	/**
	 * Check if user can create Access Groups
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	private function check_user_cap() {
		return current_user_can( 'administrator' );
	}
}
