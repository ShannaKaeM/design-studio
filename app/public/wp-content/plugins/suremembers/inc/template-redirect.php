<?php
/**
 * Template Redirect.
 *
 * @package suremembers
 * @since 1.0.0
 */

namespace SureMembers\Inc;

use SureMembers\Inc\Traits\Get_Instance;
use SureMembers\Inc\Access;
use SureMembers\Inc\Access_Groups;
use SureMembers\Inc\Utils;


/**
 * Template Redirect
 *
 * @since 0.0.1
 */
class Template_Redirect {

	use Get_Instance;

	/**
	 * Stores restricted data
	 *
	 * @var array
	 * @since 1.2.0
	 */
	public $restriction_data = [];

	/**
	 * Stores drip string
	 *
	 * @var string
	 * @since 1.4.0
	 */
	public $drip_string = '';

	/**
	 * Constructor
	 *
	 * @since  0.0.1
	 */
	public function __construct() {
		add_action( 'template_redirect', [ $this, 'processed_content' ], 999 ); // Priority modified to execute at the very last.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'suremembers_before_check_user_access', [ $this, 'handle_access_group_expiration' ], 10, 2 );
	}

	/**
	 * Filter access groups rules and redirect if need.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function processed_content() {
		// un-restricting everything for site admins.
		if ( is_user_logged_in() && current_user_can( 'administrator' ) ) {
			return;
		}

		// un-restricting for elementor edit post.
		if ( isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		// breaks redirection loop.
		if ( ! empty( $_COOKIE['suremembers_timestamp'] ) ) {
			$time = sanitize_text_field( $_COOKIE['suremembers_timestamp'] ); //phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___COOKIE
			$urls = get_transient( 'suremembers_redirection_' . $time );

			$current_url = home_url();
			if ( isset( $_SERVER['REQUEST_URI'] ) ) {
				$current_url = home_url( esc_url_raw( $_SERVER['REQUEST_URI'] ) );
			}
			if ( ! empty( $urls ) && is_array( $urls ) && in_array( $current_url, array_keys( $urls ), true ) && $urls[ $current_url ] >= 1 ) {
				setcookie( 'suremembers_timestamp', '', time() - 60, COOKIEPATH, COOKIE_DOMAIN ); //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.cookies_setcookie
				delete_transient( 'suremembers_redirection_' . $time );
				return;
			}
		}

		global $user_ID, $post;
		$post_id = isset( $post->ID ) ? intval( $post->ID ) : false;

		/**
		 * Added filter to handle special cases where $post_id is not required.
		 *
		 * @filter suremembers_filter_template_control_requires_post_id
		 * @hooked BuddyBoss/filter_required_post_id
		 * @since 1.8.0
		 */
		$requires_post_id = apply_filters( 'suremembers_filter_template_control_requires_post_id', true );

		if ( $requires_post_id && ! $post_id ) {
			return;
		}

		$post_id = apply_filters( 'suremembers_filter_redirection_post_id', $post_id );

		$this->check_rules_engine( $user_ID, $post_id );
	}

	/**
	 * Check Rules Engine for current content
	 *
	 * @param int $user_id current user id.
	 * @param int $post_id current post id.
	 * @return bool
	 * @since 1.0.0
	 */
	public function check_rules_engine( $user_id, $post_id ) {
		global $post;
		$option = array(
			'include'         => SUREMEMBERS_PLAN_INCLUDE,
			'exclusion'       => SUREMEMBERS_PLAN_EXCLUDE,
			'priority'        => SUREMEMBERS_PLAN_PRIORITY,
			'current_post_id' => $post_id,
		);

		$restricting_rules = Restricted::by_access_groups( SUREMEMBERS_POST_TYPE, $option );

		// Fetch the access groups in which the user is already added.
		$user_ags = get_user_meta( $user_id, SUREMEMBERS_USER_META, true );

		// Check user role access.
		if ( empty( $user_ags ) && $this->provide_access_by_user_role( $user_id, $restricting_rules ) ) {
			return true;
		}

		$rule = false;

		if ( empty( $restricting_rules[ SUREMEMBERS_POST_TYPE ] ) ) {
			return true;
		}

		/**
		 * Handle actions before user access is determined.
		 *
		 * @hooked $this->handle_access_group_expiration()
		 * @param int $user_id Current User ID.
		 * @param array $restricting_rules Current content's restricting rules.
		 * @since 1.6.0
		 */
		do_action( 'suremembers_before_check_user_access', $user_id, $restricting_rules[ SUREMEMBERS_POST_TYPE ] );

		if ( empty( $user_ags ) ) {
			$rule = Access_Groups::get_priority_id( array_keys( $restricting_rules[ SUREMEMBERS_POST_TYPE ] ) );
		} else {
			$rules_keys      = array_keys( $restricting_rules[ SUREMEMBERS_POST_TYPE ] );
			$connecting_rule = is_array( $user_ags ) ? array_intersect( $rules_keys, $user_ags ) : $rules_keys;

			if ( empty( $connecting_rule ) ) {
				$rule = Access_Groups::get_priority_id( $rules_keys );
			} else {
				foreach ( $connecting_rule as $id ) {
					$access_group_detail = get_user_meta( $user_id, SUREMEMBERS_USER_META . "_$id", true );
					$drip_data           = get_post_meta( absint( $id ), SUREMEMBERS_PLAN_DRIPS, true );

					if ( is_array( $access_group_detail ) && 'active' === $access_group_detail['status'] ) {
						if ( ! empty( $drip_data ) && is_array( $drip_data ) ) {
							$delay = $this->check_user_drip_rules( $drip_data );
							if ( true !== $delay && is_array( $delay ) ) {
								if ( isset( $delay['time'] ) ) {
									$r_date     = intval( gmdate( 'd', $access_group_detail['created'] ) );
									$r_month    = gmdate( 'm', $access_group_detail['created'] );
									$r_year     = gmdate( 'Y', $access_group_detail['created'] );
									$r_hour     = intval( gmdate( 'G', $access_group_detail['created'] ) );
									$r_minute   = intval( gmdate( 'i', $access_group_detail['created'] ) );
									$r_time     = $r_hour + $r_minute / 60;
									$prime_time = floatval( $delay['time'] );
									if ( ! empty( intval( $delay['periods'] ) ) ) {
										$prime_time += intval( $delay['periods'] );
									}

									$next_day = 0;
									if ( $r_time >= $prime_time && 0 === intval( $delay['delay'] ) ) {
										$next_day = 1;
									}

									if ( $prime_time > floor( $prime_time ) ) {
										$p_time = floor( $prime_time ) . ':30';
									} else {
										$p_time = $prime_time . ':00';
									}
									/**
									 * Check if $delay contains 'drip_date' for specific date.
									 * Added for support of specific drip date.
									 *
									 * @since 1.8.0
									 */
									if ( ! empty( $delay['drip_date'] ) ) {
										$prime_time_format = gmdate( 'd.m.Y', strtotime( $delay['drip_date'] ) ) . ' ' . $p_time;
										$display_date      = intval( strtotime( $prime_time_format ) );
									} else {
										$prime_time_format = ( $r_date + $next_day ) . '.' . $r_month . '.' . $r_year . ' ' . $p_time;
										$date              = '+' . $delay['delay'] . ' day';
										$prime_timestamp   = strtotime( $prime_time_format );
										$display_date      = 0;
										if ( false !== $prime_timestamp ) {
											$display_date = strtotime( $date, $prime_timestamp );
										}
									}
								} else {
									/**
									 * Check if $delay contains 'drip_date' for specific date.
									 * Added for support of specific drip date.
									 *
									 * @since 1.8.0
									 */
									if ( ! empty( $delay['drip_date'] ) ) {
										$date         = gmdate( 'd.m.Y', strtotime( $delay['drip_date'] ) );
										$display_date = strtotime( $date );
									} else {
										$date         = '+' . $delay['delay'] . ' day';
										$display_date = intval( strtotime( $date, $access_group_detail['created'] ) );
									}
								}
								$current_time = intval( current_time( 'timestamp' ) ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
								if ( $current_time < $display_date ) {
									// translators: HTML tags.
									$this->drip_string                     = apply_filters( 'suremembers_restricted_dripped_message', '<p>' . sprintf( __( 'This content will be available in %1$s%2$s', 'suremembers' ), '<br/>', esc_html( $this->display_readable_time( $display_date - $current_time ) ) ) . '</p>', $this->display_readable_time( $display_date - $current_time ) );
									$this->restriction_data['drip_string'] = $this->drip_string;
									if ( $this->redirect_to_custom_template() ) {
										$this->execute_template_filters();
										return true;
									}

									$post->suremembers_content_restricted = 1;
									add_filter( 'post_thumbnail_id', '__return_false' );
									add_filter( 'comments_open', '__return_false' );
									add_filter( 'get_comments_number', '__return_false' );
									add_filter( 'the_content', [ $this, 'drip_content' ], 99 );
								}
							}
						}

						return true;
					}
				}
				$rule = array_shift( $connecting_rule );
			}
		}

		$action              = ! is_string( $rule ) ? get_post_meta( $rule, SUREMEMBERS_PLAN_RULES, true ) : [];
		$restrict            = is_array( $action ) && isset( $action['restrict'] ) ? $action['restrict'] : [];
		$unauthorized_action = ! empty( $restrict['unauthorized_action'] ) ? $restrict['unauthorized_action'] : '';

		if ( apply_filters( 'suremembers_only_process_redirection', false ) ) {
			if ( 'redirect' === $unauthorized_action ) {
				$redirect_url = Utils::maybe_append_url_params( esc_url( trim( $restrict['redirect_url'] ) ) );
				if ( ! empty( $redirect_url ) ) {
					Utils::stop_infinite_redirect( $redirect_url );
					wp_redirect( $redirect_url ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
					exit;
				}
			}
			return true;
		}

		switch ( $restrict['unauthorized_action'] ) {
			case 'redirect':
				$redirect_url = Utils::maybe_append_url_params( esc_url( trim( $restrict['redirect_url'] ) ) );
				if ( ! empty( $redirect_url ) ) {
					Utils::stop_infinite_redirect( $redirect_url );
					wp_redirect( $redirect_url ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
					exit;
				}
				$post->post_content = '<p>' . esc_html__( 'This content is restricted', 'suremembers' ) . '</p>';
				add_filter( 'comments_open', '__return_false' );
				add_filter( 'get_comments_number', '__return_false' );
				break;
			case 'preview':
				$this->restriction_data = $restrict;
				if ( $this->redirect_to_custom_template() || empty( $restrict['in_content'] ) ) {
					$this->execute_template_filters();
					return true;
				}
				$post->post_content                   = Restricted::get_unauthorized_message( $restrict );
				$post->suremembers_content_restricted = 1;
				$post->restriction_data               = $restrict;
				$allow_featured_image                 = apply_filters( 'suremembers_allow_restricted_post_featured_image', false );

				if ( ! $allow_featured_image ) {
					add_filter( 'post_thumbnail_id', '__return_false' );
				}
				add_filter( 'comments_open', '__return_false' );
				add_filter( 'get_comments_number', '__return_false' );
				add_filter( 'the_content', [ $this, 'restricted_content' ], 99 );
				break;
			default:
				break;
		}

		return true;
	}

	/**
	 * Loads template for restricted content
	 *
	 * @param string $template current template.
	 * @return string|void
	 * @since 1.2.0
	 */
	public function restricted_page_template( $template ) { //phpcs:ignore WordPressVIPMinimum.Hooks.AlwaysReturnInFilter.VoidReturn
		$path = SUREMEMBERS_DIR . 'inc/restricted-template.php';
		if ( file_exists( $path ) ) {
			load_template( $path, true, $this->restriction_data );
			return;
		}
		return $template;
	}

	/**
	 * Get user drip rules.
	 *
	 * @param array|mixed $drips array of drip from user meta.
	 * @return bool|array
	 * @since 1.10.8
	 */
	public function verify_user_drip_rules( $drips ) {
		return $this->check_user_drip_rules( $drips );
	}

	/**
	 * Check drips rules of current user.
	 *
	 * @param array|mixed $drips array of drip from user meta.
	 * @return bool|array
	 * @since 1.0.0
	 */
	private function check_user_drip_rules( $drips ) {
		if ( empty( $drips ) || ! is_array( $drips ) ) {
			return true;
		}

		$meta_values = Restricted::get_content_meta_values();
		if ( empty( $meta_values ) ) {
			return true;
		}

		$delays = [];
		foreach ( $drips as $i => $data ) {
			if ( ! isset( $data['rules'] ) || ! is_array( $data['rules'] ) ) {
				continue;
			}
			if ( array_intersect( $data['rules'], $meta_values ) ) {
					$delays[ $i ]['delay'] = isset( $data['delay'] ) ? $data['delay'] : 0;

				if ( isset( $data['time'] ) && $data['time'] >= 0 ) {
					$delays[ $i ]['time'] = $data['time'];
				}

				if ( isset( $data['periods'] ) && $data['periods'] >= 0 ) {
					$delays[ $i ]['periods'] = $data['periods'];
				}

				if ( ! empty( $data['drip_date'] ) ) {
					$delays[ $i ]['drip_date'] = $data['drip_date'];
				}
			}
		}

		if ( empty( $delays ) ) {
			return true;
		}

		$col = array_column( $delays, 'delay' );
		array_multisort( $col, SORT_ASC, $delays );

		return $delays[0];
	}

	/**
	 * Shows drip time in readable format.
	 *
	 * @param int $time_diff difference in current time and access time.
	 * @return string
	 * @since 1.0.0
	 */
	public function display_readable_time( $time_diff ) {

		$params = [
			__( 'Day', 'suremembers' )    => 86400,
			__( 'Hour', 'suremembers' )   => 3600,
			__( 'Minute', 'suremembers' ) => 60,
		];

		$time_string = '';

		foreach ( $params as $key => $param ) {
			$count     = floor( $time_diff / $param );
			$time_diff = $time_diff % $param;
			if ( $count ) {
				$time_string .= ' ' . $count . ' ' . $key;
			}
			if ( $count > 1 ) {
				$time_string .= 's';
			}
		}

		return $time_string;
	}

	/**
	 * Login form scripts.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function enqueue_scripts() {

		// Load the script for logged in and logged out user.
		wp_register_script( 'suremembers-front-script', SUREMEMBERS_URL . 'assets/js/script.js', [ 'jquery' ], SUREMEMBERS_VER, true );
		wp_localize_script(
			'suremembers-front-script',
			'suremembers_login',
			[
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			]
		);

		wp_enqueue_script( 'suremembers-front-script' );

		if ( ! is_user_logged_in() ) {
			wp_enqueue_style( 'suremembers-front-style', SUREMEMBERS_URL . 'assets/css/style.css', [ 'dashicons' ], SUREMEMBERS_VER );
		}

		wp_enqueue_script( 'suremembers-restricted-template' );
		wp_enqueue_style( 'suremembers-restricted-template-style', SUREMEMBERS_URL . 'assets/css/restricted-template.css', [], SUREMEMBERS_VER );
	}

	/**
	 * Check user role access.
	 *
	 * @param int   $user_id Current user id.
	 * @param array $rules Access rules array.
	 * @since 1.1.0
	 * @return boolean
	 */
	public function provide_access_by_user_role( $user_id, $rules ) {
		$return = false;

		if ( empty( $user_id ) || empty( $rules[ SUREMEMBERS_POST_TYPE ] ) ) {
			return $return;
		}

		$user_meta = get_userdata( $user_id );
		if ( empty( $user_meta->roles ) ) {
			return $return;
		}

		foreach ( $rules[ SUREMEMBERS_POST_TYPE ] as $restricting_rule_value ) {
			$access_group_id = ! empty( $restricting_rule_value['id'] ) ? intval( $restricting_rule_value['id'] ) : false;

			if ( ! $access_group_id ) {
				continue;
			}

			$get_user_role = Access_Groups::get_selected_user_roles( $access_group_id );

			if ( ! empty( $get_user_role ) && ! empty( array_intersect( $get_user_role, $user_meta->roles ) ) ) {
				$return = true;
				break;
			}
		}
		return $return;
	}

	/**
	 * Returns updated restricted content in post content
	 *
	 * @return string
	 * @since 1.3.1
	 */
	public function restricted_content() {
		return wpautop( Restricted::get_unauthorized_message( $this->restriction_data ) );
	}

	/**
	 * Undocumented function
	 *
	 * @return string
	 * @since 1.4.0
	 */
	public function drip_content() {
		return wpautop( $this->drip_string );
	}

	/**
	 * Checks whether current page should be redirected to custom template or not
	 *
	 * @return bool
	 * @since 1.3.1
	 */
	public function redirect_to_custom_template() {
		global $post;
		if ( apply_filters( 'suremembers_should_redirect_to_custom_template', is_archive() || is_home() || is_front_page() || empty( $post->post_type ) || 'post' !== $post->post_type ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Handles the access group expiration status by access group id.
	 *
	 * @param int   $user_id Current user ID.
	 * @param array $restricting_rules Restricting rules array.
	 * @return void
	 * @since 1.6.0
	 */
	public function handle_access_group_expiration( $user_id, $restricting_rules ) {
		if ( empty( $restricting_rules ) || ! is_array( $restricting_rules ) ) {
			return;
		}

		foreach ( $restricting_rules as $id => $rule ) {
			// Check if access group is expired.
			if ( Access_Groups::is_expired( $id, $user_id ) ) {
				Access::revoke( $user_id, $id );
			}
		}

	}

	/**
	 * Get Restricted page template action priority
	 *
	 * @return int
	 */
	public function get_restricted_page_template_action_priority() {
		return intval( apply_filters( 'suremembers_restricted_page_template_action_priority', 999 ) );
	}

	/**
	 * Function to group the template include or template override actions and filters.
	 * From this function, a filter is used to include the restricted content message template.
	 *
	 * @since 1.10.1
	 * @return void
	 */
	public function execute_template_filters() {

		/**
		 * Disable the Astra's site editor template include to solve the multiple template display on one page.
		 */
		add_filter( 'astra_addon_render_custom_template_content', '__return_false' );

		/**
		 * Load the custom template to display the restricted message and required data.
		 * Such as: Drip countdown and restricted message, header and footer.
		 */
		if ( apply_filters( 'suremembers_load_restricted_page_template', true ) ) {
			add_filter( 'template_include', [ $this, 'restricted_page_template' ], $this->get_restricted_page_template_action_priority() );
		}
	}
}
