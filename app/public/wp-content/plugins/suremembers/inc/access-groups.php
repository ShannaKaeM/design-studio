<?php
/**
 * Access Groups.
 *
 * @package suremembers
 * @since 1.0.0
 */

namespace SureMembers\Inc;

/**
 * Access Groups
 *
 * @since 0.0.1
 */
class Access_Groups {

	/**
	 * Gets all the published access groups available on this website.
	 *
	 * @param array $args extra params to be passed to get_post query.
	 * @return array
	 * @since 1.0.0
	 */
	public static function get_active( $args = [] ) {
		$plans_array = [];
		$plans_args  = apply_filters(
			'suremembers_get_access_groups',
			[
				'post_type'   => SUREMEMBERS_POST_TYPE,
				'post_status' => 'publish',
				'numberposts' => -1,
				'order'       => 'ASC',
			]
		);
		if ( is_array( $args ) && ! empty( $args ) ) {
			$args       = Utils::sanitize_recursively( 'sanitize_text_field', $args );
			$plans_args = array_merge( $plans_args, $args );
		}

		$plans = get_posts( $plans_args );

		// If no plans found returns empty array.
		if ( empty( $plans ) ) {
			return $plans_array;
		}

		foreach ( $plans as $plan ) {
			if ( empty( $plan->ID ) || empty( $plan->post_title ) ) {
				continue;
			} else {
				$plans_array[ $plan->ID ] = $plan->post_title;
			}
		}

		return $plans_array;
	}

	/**
	 * Get access groups URL.
	 *
	 * @param array $args URL arguments.
	 * @return string URL of the access groups as per $args.
	 * @since 1.0.0
	 */
	public static function get_admin_url( $args = [] ) {
		$url_args = wp_parse_args(
			$args,
			[
				'post_type' => SUREMEMBERS_POST_TYPE,
			]
		);

		return add_query_arg(
			$url_args,
			admin_url( 'edit.php' )
		);
	}

	/**
	 * Check block restriction.
	 *
	 * @param array $access_group_ids access group ids to be checked.
	 * @return boolean
	 */
	public static function check_if_user_has_access( $access_group_ids ) {
		$access_group_ids = self::filter_active_access_groups( $access_group_ids );
		if ( empty( $access_group_ids ) ) {
			return true;
		}
		$user_id = intval( get_current_user_id() );
		if ( ! $user_id ) {
			return false;
		}
		$user_plan = get_user_meta( $user_id, SUREMEMBERS_USER_META, true );
		if ( empty( $user_plan ) || ! is_array( $user_plan ) ) {
			return false;
		}
		$array_intersect_common_id = array_intersect( $user_plan, $access_group_ids );
		if ( empty( $array_intersect_common_id ) ) {
			return false;
		}
		return self::check_plan_active( $user_id, $array_intersect_common_id );
	}

	/**
	 * Check plan status.
	 *
	 * @param int       $user_id user id.
	 * @param array|int $plan_ids Array of plan ids or single plan id can also be provided.
	 * @return boolean
	 */
	public static function check_plan_active( $user_id, $plan_ids ) {
		if ( empty( $plan_ids ) ) {
			return false;
		}
		$unrestrict_block = false;
		if ( is_array( $plan_ids ) ) {
			foreach ( $plan_ids as $value ) {
				$value = intval( $value );
				if ( ! $value ) {
					continue;
				}
				$check_plan_validity = get_user_meta( $user_id, SUREMEMBERS_USER_META . "_$value", true );
				if ( is_array( $check_plan_validity ) && isset( $check_plan_validity['status'] ) && 'active' === $check_plan_validity['status'] ) {
					$unrestrict_block = true;
					break;
				}
			}
		} else {
			$check_plan_validity = get_user_meta( $user_id, SUREMEMBERS_USER_META . "_$plan_ids", true );
			if ( is_array( $check_plan_validity ) && isset( $check_plan_validity['status'] ) && 'active' === $check_plan_validity['status'] ) {
				$unrestrict_block = true;
			}
		}
		return $unrestrict_block;
	}

	/**
	 * Get top priority access group's id.
	 *
	 * @param array $access_group_ids access group ids.
	 * @return integer
	 */
	public static function get_priority_id( $access_group_ids ) {
		if ( 1 === count( $access_group_ids ) ) {
			$valid_access_id = intval( $access_group_ids[0] );
			if ( ! $valid_access_id ) {
				return 0;
			}
			$access_group_id = self::is_active_access_group( $valid_access_id );
			if ( ! is_int( $access_group_id ) ) {
				$access_group_id = 0;
			}
			return $access_group_id;
		}
		$sort_ids_as_prior = [];
		foreach ( $access_group_ids as $value ) {
			$value = intval( $value );
			if ( ! $value ) {
				continue;
			}
			$check_status = self::is_active_access_group( $value );
			if ( ! $check_status ) {
				continue;
			}
			$priority            = intval( get_post_meta( $value, SUREMEMBERS_PLAN_PRIORITY, true ) );
			$sort_ids_as_prior[] = [
				'id'       => $value,
				'priority' => $priority,
			];
		}
		usort(
			$sort_ids_as_prior,
			function ( $a, $b ) {
				return $b['priority'] - $a['priority'];
			}
		);
		return empty( $sort_ids_as_prior ) ? 0 : $sort_ids_as_prior[0]['id'];
	}

	/**
	 * Checks whether provided access group is having status 'publish' or not
	 *
	 * @param int $access_group_id current access group id.
	 * @return boolean|int
	 * @since 1.0.0
	 */
	public static function is_active_access_group( $access_group_id ) {
		$status = get_post_status( $access_group_id );
		if ( 'publish' === $status ) {
			return intval( $access_group_id );
		}
		return false;
	}

	/**
	 * Returns active access groups
	 *
	 * @param array $access_group_ids array of access group ids.
	 * @return array
	 * @since 1.0.0
	 */
	public static function filter_active_access_groups( $access_group_ids ) {
		$result = [];
		if ( empty( $access_group_ids ) || ! is_array( $access_group_ids ) ) {
			return $result;
		}
		foreach ( $access_group_ids as $id ) {
			$active_id = self::is_active_access_group( $id );
			if ( $active_id ) {
				$result[] = $active_id;
			}
		}
		return $result;
	}

	/**
	 * Returns active access groups which are not expired.
	 *
	 * @param array $access_group_ids array of access group ids.
	 * @return array
	 * @since 1.10.8
	 */
	public static function filter_not_expired_groups( $access_group_ids ) {
		$result = [];
		if ( empty( $access_group_ids ) || ! is_array( $access_group_ids ) ) {
			return $result;
		}
		foreach ( $access_group_ids as $id ) {
			$is_expired = self::is_expired( $id, get_current_user_id() );
			if ( ! $is_expired ) {
				$result[] = $id;
			}
		}
		return $result;
	}

	/**
	 * Get the count of users in a access group.
	 *
	 * @param int $access_group_id Access Group ID.
	 * @return int Number of users.
	 * @since 1.0.0
	 */
	public static function get_users_count( $access_group_id ) {
		global $wpdb;
		$meta_key = SUREMEMBERS_USER_META . '_' . $access_group_id;

		$meta_value = '%active%';
		$results    = wp_cache_get( 'suremembers_active_users_var_' . $access_group_id );

		if ( false === $results ) {
			// Ignored due to functionality requirements.
			$results = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}usermeta as um WHERE um.meta_key = %s AND um.meta_value LIKE %s", $meta_key, $meta_value ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			wp_cache_set( 'suremembers_active_users_var_' . $access_group_id, $results );
		}

		if ( is_null( $results ) ) {
			$results = get_post_meta( $access_group_id, SUREMEMBERS_PLAN_ACTIVE_USERS, true );
			if ( ! is_int( $results ) ) {
				$results = 0;
			}
		}

		update_post_meta( $access_group_id, SUREMEMBERS_PLAN_ACTIVE_USERS, absint( $results ) );
		delete_post_meta( $access_group_id, SUREMEMBERS_REQUIRES_QUERY );

		return $results;
	}

	/**
	 * Get saved user roles
	 *
	 * @param mixed $post_id post id to retrieve data from.
	 * @return array
	 * @since 1.1.0
	 */
	public static function get_selected_user_roles( $post_id = false ) {
		if ( empty( $post_id ) ) {
			if ( empty( $_GET['post_id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return [];
			}
			$post_id = absint( $_GET['post_id'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$id = absint( $post_id );

		$roles = get_post_meta( $id, SUREMEMBERS_USER_ROLES, true );
		$roles = ! empty( $roles ) && is_array( $roles ) ? $roles : [];
		return $roles;
	}

	/**
	 * Get downloads associated with access group.
	 *
	 * @param boolean $post_id Access Group ID.
	 * @return string Download IDs or empty string if no downloads are available.
	 * @since 1.3.0
	 */
	public static function get_downloads( $post_id = false ) {
		if ( empty( $post_id ) ) {
			// Ignored as we are getting values from URL.
			if ( empty( $_GET['post_id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return '';
			}
			$post_id = absint( $_GET['post_id'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$id = absint( $post_id );

		$downloads = get_post_meta( $id, SUREMEMBERS_ACCESS_GROUP_DOWNLOADS, true );

		$downloads = is_string( $downloads ) && ! empty( $downloads ) ? $downloads : '';
		return $downloads;
	}

	/**
	 * Get Access Groups by Download ID.
	 *
	 * @param int $download_id Download ID to get access groups.
	 * @return array Array of Access Groups matching the Download ID.
	 * @since 1.3.0
	 */
	public static function by_download_id( $download_id ) {
		global $wpdb;

		$access_groups = [];
		$meta_value    = '%' . $download_id . '%';
		$meta_key      = SUREMEMBERS_ACCESS_GROUP_DOWNLOADS;

		$results = wp_cache_get( 'suremembers_access_groups_by_download_id_' . $download_id );
		if ( false === $results ) {
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT p.ID FROM {$wpdb->prefix}postmeta as pm INNER JOIN {$wpdb->prefix}posts as p ON pm.post_id = p.ID WHERE pm.meta_key = %s AND pm.meta_value LIKE %s", $meta_key, $meta_value ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			wp_cache_set( 'suremembers_access_groups_by_download_id_' . $download_id, $results );
		}

		if ( ! empty( $results ) ) {
			$access_groups = array_column( $results, 'ID' );
		}

		return $access_groups;
	}

	/**
	 * Check is access group is expired.
	 *
	 * @param int $access_group_id Access Group ID.
	 * @param int $user_id Current user ID.
	 * @return boolean
	 * @since 1.6.0
	 */
	public static function is_expired( $access_group_id, $user_id ) {
		$expiration = get_post_meta( $access_group_id, SUREMEMBERS_PLAN_EXPIRATION, true );
		if ( ! is_array( $expiration ) || empty( $expiration ) ) {
			return false;
		}

		if ( ! isset( $expiration['enable'] ) || 'true' !== $expiration['enable'] ) {
			return false;
		}

		if ( 'relative_date' === $expiration['type'] ) {
			if ( empty( $expiration['delay'] ) ) {
				return false;
			}

			$access_group_detail     = get_user_meta( $user_id, SUREMEMBERS_USER_META . "_$access_group_id", true );
			$user_expiration_details = get_user_meta( $user_id, SUREMEMBERS_USER_EXPIRATION, true );

			if ( ! is_array( $access_group_detail ) || ! isset( $access_group_detail['created'] ) ) {
				return false;
			}

			$current_time    = intval( current_time( 'timestamp' ) ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
			$expiration_date = '';

			if ( is_array( $user_expiration_details ) && isset( $user_expiration_details[ $access_group_id ] ) ) {
				$expiration_date = $user_expiration_details[ $access_group_id ];

				// Get modified access group modified date timestamp.
				$access_group_date = isset( $access_group_detail['modified'] ) ? $access_group_detail['modified'] : $access_group_detail['created'];

				// convert it into the date to time to compare.
				$expiration_date = strtotime( $expiration_date, intval( $access_group_date ) );

			} else {
				// Get updated date if available.
				$access_group_date = isset( $access_group_detail['modified'] ) ? $access_group_detail['modified'] : $access_group_detail['created'];
				$date              = '+' . intval( $expiration['delay'] ) . ' day';
				$expiration_date   = strtotime( $date, intval( $access_group_date ) );

			}

			if ( $current_time > $expiration_date ) {
				return true;
			}
		}

		if ( 'specific_date' === $expiration['type'] ) {
			if ( empty( $expiration['specific_date'] ) ) {
				return false;
			}
			$current_time    = intval( current_time( 'timestamp' ) ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
			$expiration_time = strtotime( $expiration['specific_date'] );

			if ( $current_time > $expiration_time ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if user has access to the post.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $access_groups Access Groups.
	 * @param int   $user_id User ID.
	 * @return boolean
	 * @since 1.10.8
	 */
	public static function check_user_has_post_access( $post_id, $access_groups, $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = intval( get_current_user_id() );
		}

		$access_group_ids = array_keys( $access_groups[ SUREMEMBERS_POST_TYPE ] );
		$access_group_ids = self::filter_not_expired_groups( $access_group_ids );
		$access_group_ids = array_map( 'intval', $access_group_ids );
		if ( ! is_array( $access_group_ids ) || empty( $access_group_ids ) ) {
			return true;
		}

		if ( self::check_if_user_has_access( $access_group_ids ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get restriction detail of a specific access group.
	 *
	 * @param int $access_group Access Group ID.
	 * @return array
	 * @since 1.10.8
	 */
	public static function get_restriction_detail( $access_group ) {
		$action   = get_post_meta( $access_group, SUREMEMBERS_PLAN_RULES, true );
		$restrict = is_array( $action ) && isset( $action['restrict'] ) ? $action['restrict'] : [];
		return $restrict;
	}

	/**
	 * Check if post is about to drip.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $access_groups Access Groups.
	 * @param int   $user_id User ID.
	 * @return array
	 * @since 1.10.8
	 */
	public static function check_is_post_is_dripping( $post_id, $access_groups, $user_id = 0 ) {
		$status = false;
		$time   = '';

		if ( ! $user_id ) {
			$user_id = intval( get_current_user_id() );
		}

		// Fetch the access groups in which the user is already added.
		$user_ags = get_user_meta( $user_id, SUREMEMBERS_USER_META, true );

		/**
		 * Handle actions before user access is determined.
		 *
		 * @hooked Template_Redirect::get_instance()->handle_access_group_expiration()
		 * @param int $user_id Current User ID.
		 * @param array $access_groups Current content's restricting rules.
		 * @since 1.6.0
		 */
		do_action( 'suremembers_before_check_user_access', $user_id, $access_groups[ SUREMEMBERS_POST_TYPE ] );

		if ( ! empty( $user_ags ) ) {
			$caught_in       = false;
			$rules_keys      = array_keys( $access_groups[ SUREMEMBERS_POST_TYPE ] );
			$connecting_rule = is_array( $user_ags ) ? array_intersect( $rules_keys, $user_ags ) : $rules_keys;

			if ( ! empty( $connecting_rule ) ) {
				foreach ( $connecting_rule as $id ) {
					if ( $caught_in ) {
						break;
					}

					$access_group_detail = get_user_meta( $user_id, SUREMEMBERS_USER_META . "_$id", true );
					$drip_data           = get_post_meta( absint( $id ), SUREMEMBERS_PLAN_DRIPS, true );

					if ( is_array( $access_group_detail ) && 'active' === $access_group_detail['status'] ) {
						if ( ! empty( $drip_data ) && is_array( $drip_data ) ) {
							add_filter(
								'suremembers_get_content_meta_values_option',
								static function ( $meta_args ) use ( $post_id ) {
									$meta_args['current_page_type'] = 'is_singular';
									$meta_args['current_post_type'] = strval( get_post_type( $post_id ) );
									$meta_args['current_post_id']   = strval( $post_id );
									return $meta_args;
								},
							);

							$delay = Template_Redirect::get_instance()->verify_user_drip_rules( $drip_data );
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
									$caught_in = true;
									$time      = esc_html( Template_Redirect::get_instance()->display_readable_time( $display_date - $current_time ) );
								}
							}

							remove_all_filters( 'suremembers_get_content_meta_values_option' );
						}
					}
				}
			}

			if ( $caught_in ) {
				$status = true;
			}
		}

		// If no drip rules found, return false.
		return [
			'status' => $status,
			'time'   => $time,
		];
	}
}
