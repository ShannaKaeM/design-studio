<?php
/**
 * Markup functions.
 *
 * @package SureDash
 * @since 0.0.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SureDashboard\Inc\Utils\Helper;

/**
 * Check if a post exists.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function sd_post_exists( $post_id ) {
	return boolval( sd_query()->select( 'ID' )->from( 'posts' )->where( 'ID', $post_id )->get() );
}

/**
 * Check if a post is published.
 *
 * @param int $post_id Post ID.
 * @return bool
 * @since 1.0.0
 */
function sd_is_post_publish( $post_id ) {
	return boolval( sd_query()->select( 'post_status' )->from( 'posts' )->where( 'ID', $post_id )->where( 'post_status', 'publish' )->get() );
}

/**
 * Check if a specific comment exists and is not deleted.
 *
 * @param int $comment_id Comment ID.
 * @return bool
 */
function sd_specific_comment_exists( $comment_id ) {
	return (bool) sd_query()
		->select( 'comment_ID' )
		->from( 'comments' )
		->where( 'comment_ID', $comment_id )
		->where( 'comment_approved', '!=', 'trash' )
		->limit( 1 )
		->get();
}

/**
 * Get all space data.
 *
 * @param int $space_id Space ID.
 * @return object
 * @since 0.0.2
 */
function sd_get_all_post_data( $space_id ) {
	$result = sd_query()->select( 'p.ID,p.post_title,p.post_content,p.post_excerpt,p.post_name,p.post_date,p.post_modified,p.post_author,p.comment_count,p.post_status,p.guid,JSON_OBJECTAGG(pm.meta_key, pm.meta_value) AS metadata' )->from( 'posts AS p' )->leftJoin( 'postmeta AS pm', 'p.ID', '=', 'pm.post_id' )->where( 'p.ID', $space_id )->group_by( 'p.ID' )->get( ARRAY_A );

	// Extract the first result (if any).
	$response = is_array( $result ) && isset( $result[0] ) ? $result[0] : [];

	if ( ! empty( $response ) && is_array( $response ) ) {
		$metadata             = [];
		$response['metadata'] = json_decode( $response['metadata'] ?? '{}', true );

		if ( ! empty( $response['metadata'] ) ) {
			foreach ( $response['metadata'] as $meta_key => $meta_value ) {
				$metadata[ $meta_key ] = maybe_unserialize( $meta_value );
			}

			$response['metadata'] = $metadata;
		}
	}

	return $response;
}

/**
 * Get a post instance.
 *
 * @param int $post_id Post ID.
 * @return \WP_Post|object|WP_Error|null
 * @since 0.0.2
 */
function sd_get_post( $post_id ) {
	if ( ! Helper::bypass_wp_interfere() ) {
		return get_post( $post_id );
	}

	$post_exists = sd_post_exists( $post_id );

	if ( ! $post_exists ) {
		return null;
	}

	$post = sd_query()->select( '*' )->from( 'posts' )->where( 'ID', $post_id )->get();
	return is_array( $post ) && isset( $post[0] ) ? $post[0] : null;
}

/**
 * Get posts.
 *
 * @param array<string, mixed> $args Query arguments.
 *
 * @return array<int, int|WP_Post>.
 * @since 1.0.0
 */
function sd_get_posts( $args = [] ) {

	if ( ! Helper::bypass_wp_interfere() ) {
		return get_posts( $args );
	}

	$defaults = [
		'post_type'   => 'post',
		'post_status' => 'publish',
		'numberposts' => 5,
		'orderby'     => 'post_date',
		'order'       => 'DESC',
		'tax_query'   => [],
		'select'      => '*',
	];

	$args = wp_parse_args( $args, $defaults );

	$query = sd_query()
		->select( $args['select'] )
		->from( 'posts AS p' )
		->where( 'p.post_status', '=', $args['post_status'] );

	// Handle post type(s).
	if ( is_array( $args['post_type'] ) ) {
		$query->where( 'p.post_type', 'IN', $args['post_type'] );
	} else {
		$query->where( 'p.post_type', '=', $args['post_type'] );
	}

	// Optional author exclusion.
	if ( ! empty( $args['author__not_in'] ) && is_array( $args['author__not_in'] ) ) {
		$query->where( 'p.post_author', 'NOT IN', $args['author__not_in'] );
	}

	// Handle tax query.
	if ( ! empty( $args['tax_query'] ) && is_array( $args['tax_query'] ) ) {
		foreach ( $args['tax_query'] as $tax_query ) {
			if ( ! empty( $tax_query['taxonomy'] ) && ! empty( $tax_query['field'] ) && ! empty( $tax_query['terms'] ) ) {
				$query->join( 'term_relationships AS tr', 'p.ID', '=', 'tr.object_id' )
					->join( 'term_taxonomy AS tt', 'tr.term_taxonomy_id', '=', 'tt.term_taxonomy_id' )
					->where( 'tt.taxonomy', '=', $tax_query['taxonomy'] )
					->where( 'tt.term_id', '=', $tax_query['terms'] );
			}
		}
	}

	// Handle meta query.
	if ( ! empty( $args['meta_query'] ) && is_array( $args['meta_query'] ) ) {
		foreach ( $args['meta_query'] as $meta_query ) {
			if ( ! empty( $meta_query['key'] ) && ! empty( $meta_query['value'] ) ) {
				$query->join( 'postmeta AS pm', 'p.ID', '=', 'pm.post_id' )
					->where( 'pm.meta_key', '=', $meta_query['key'] )
					->where( 'pm.meta_value', $meta_query['compare'], $meta_query['value'] );
			}
		}
	}

	// Optional order and limit.
	$query->order_by( $args['orderby'], $args['order'] );
	$query->limit( $args['numberposts'] );

	return $query->get( ARRAY_A );
}

/**
 * Get post meta.
 *
 * @param int    $post_id Post ID.
 * @param string $key Meta key.
 * @param bool   $single Return single value.
 * @return mixed
 * @since 0.0.2
 */
function sd_get_post_meta( $post_id, $key = '', $single = false ) {
	if ( ! Helper::bypass_wp_interfere() ) {
		return get_post_meta( $post_id, $key, $single );
	}

	$meta = sd_query()->select( 'meta_value' )->from( 'postmeta' )->where( 'post_id', $post_id )->matchWhere( 'meta_key', $key )->get( ARRAY_A );

	if ( ! $meta || ! is_array( $meta ) ) {
		return '';
	}

	$meta = reset( $meta );

	if ( $single && is_array( $meta ) ) {
		return maybe_unserialize( $meta['meta_value'] );
	}

	return array_map( 'maybe_unserialize', is_array( $meta ) ? $meta : [] );
}

/**
 * Update post meta.
 *
 * @param int    $post_id Post ID.
 * @param string $meta_key Meta key.
 * @param mixed  $meta_value Meta value.
 * @return bool|int
 * @since 0.0.2
 */
function sd_update_post_meta( $post_id, $meta_key, $meta_value ) {
	if ( ! Helper::bypass_wp_interfere() ) {
		return update_post_meta( $post_id, $meta_key, $meta_value );
	}

	$meta_key   = wp_unslash( $meta_key );
	$meta_value = wp_unslash( $meta_value );
	$meta_value = sanitize_meta( $meta_key, $meta_value, 'post' );

	$meta = sd_query()->select( 'meta_value' )->from( 'postmeta' )->where( 'post_id', $post_id )->matchWhere( 'meta_key', $meta_key )->get( ARRAY_A );

	if ( ! $meta ) {
		return add_post_meta( $post_id, $meta_key, $meta_value );
	}

	$meta_value  = maybe_serialize( $meta_value );
	$update_data = compact( 'meta_value' );

	return sd_query()->table( 'postmeta' )->where( 'post_id', $post_id )->matchWhere( 'meta_key', $meta_key )->update( [ 'meta_value' => $update_data ] );
}

/**
 * Create a post.
 *
 * @param array<mixed> $post_data Post data.
 * @return int|WP_Error
 * @since 0.0.2
 * @phpstan-ignore-next-line
 */
function sd_wp_insert_post( $post_data ) {
	// Using native WordPress function because this function is performing multiple for managing post data, guid, post meta, etc.
	return wp_insert_post( $post_data );
}

/**
 * Update a post.
 *
 * @param array $post_data Post data.
 * @return int|WP_Error|bool
 * @since 0.0.2
 * @phpstan-ignore-next-line
 */
function sd_wp_update_post( $post_data ) {
	if ( ! Helper::bypass_wp_interfere() ) {
		return wp_update_post( $post_data );
	}

	$post_id = absint( $post_data['ID'] ?? 0 );

	if ( ! $post_id ) {
		return new \WP_Error( 'error', __( 'Invalid post ID.', 'suredash' ), [ 'status' => 400 ] );
	}

	if ( ! sd_post_exists( $post_id ) ) {
		return new \WP_Error( 'post_not_found', __( 'Post not found.', 'suredash' ), [ 'status' => 404 ] );
	}

	$result = sd_query()->table( 'posts' )->where( 'ID', $post_id )->update( $post_data );

	if ( ! $result ) {
		return new \WP_Error( 'error', __( 'Failed to update post.', 'suredash' ), [ 'status' => 400 ] );
	}

	// Update metadata.
	if ( ! empty( $post_data['meta_input'] ) ) {
		foreach ( $post_data['meta_input'] as $field => $value ) {
			sd_update_post_meta( $post_id, $field, sanitize_text_field( $value ) );
		}
	}

	return $result;
}

/**
 * Get user meta.
 *
 * @param int    $user_id User ID.
 * @param string $meta_key Meta key.
 * @param bool   $single Return single value.
 * @return mixed
 */
function sd_get_user_meta( $user_id, $meta_key, $single ) {
	// We are not replacing the native WordPress function because native function is already optimized and well performing.
	return get_user_meta( $user_id, $meta_key, $single );
}

/**
 * Update user meta.
 *
 * @param int    $user_id User ID.
 * @param string $meta_key Meta key.
 * @param mixed  $meta_value Meta value.
 * @return bool|int
 */
function sd_update_user_meta( $user_id, $meta_key, $meta_value ) {
	if ( ! Helper::bypass_wp_interfere() ) {
		return update_user_meta( $user_id, $meta_key, $meta_value );
	}

	$meta_key   = wp_unslash( $meta_key );
	$meta_value = wp_unslash( $meta_value );
	$meta_value = sanitize_meta( $meta_key, $meta_value, 'user' );

	$meta = sd_query()->select( 'meta_value' )->from( 'usermeta' )->where( 'user_id', $user_id )->matchWhere( 'meta_key', $meta_key )->get( ARRAY_A );

	if ( ! $meta ) {
		return add_user_meta( $user_id, $meta_key, $meta_value );
	}

	$meta_value  = maybe_serialize( $meta_value );
	$update_data = compact( 'meta_value' );

	return sd_query()->table( 'usermeta' )->where( 'user_id', $user_id )->where( 'meta_key', $meta_key )->update( [ 'meta_value' => $update_data ] );
}

/**
 * Update a user data.
 *
 * @param array{ID: int, user_pass?: string, user_nicename?: string, user_email?: string, display_name?: string, first_name?: string, last_name?: string, user_url?: string, description?: string, nickname?: string, role?: string, locale?: string, rich_editing?: string, syntax_highlighting?: string, comment_shortcuts?: string, admin_color?: string, show_admin_bar_front?: string, use_ssl?: bool} $user_data User data.
 * @return int|WP_Error|bool
 * @since 0.0.2
 */
function sd_wp_update_user( $user_data ) {
	if ( ! Helper::bypass_wp_interfere() ) {
		return wp_update_user( $user_data );
	}

	$user_id = $user_data['ID'];

	if ( ! $user_id ) {
		return new \WP_Error( 'error', __( 'Invalid user ID.', 'suredash' ), [ 'status' => 400 ] );
	}

	$users_table_keys = [ 'ID', 'user_login', 'user_pass', 'user_nicename', 'user_email', 'user_url', 'user_registered', 'user_activation_key', 'user_status', 'display_name' ];

	$users_table_data = array_intersect_key( $user_data, array_flip( $users_table_keys ) );

	$result = sd_query()->table( 'users' )->where( 'ID', $user_id )->update( $users_table_data );

	if ( $result === false ) {
		return new \WP_Error( 'error', __( 'User data not updated', 'suredash' ), [ 'status' => 400 ] );
	}

	$rest_users_data = array_diff_key( $user_data, array_flip( $users_table_keys ) );

	// Update metadata.
	if ( ! empty( $rest_users_data ) ) {
		foreach ( $rest_users_data as $field => $value ) {
			// @phpstan-ignore-next-line
			sd_update_user_meta( $user_id, $field, wp_unslash( sanitize_text_field( $value ) ) );
		}
	}

	return $result;
}

/**
 * Get the count of posts of a specific type.
 *
 * @param string $post_type Post type.
 * @return int
 * @since 1.0.0
 */
function sd_count_posts( $post_type = 'post' ) {
	if ( ! Helper::bypass_wp_interfere() ) {
		return wp_count_posts( $post_type )->publish;
	}

	$count = sd_query()->select( 'COUNT(*) AS count' )->from( 'posts' )->where( 'post_type', $post_type )->where( 'post_status', 'in', [ 'publish', 'draft', 'pending', 'private' ] )->get( ARRAY_A );

	if ( ! $count || ! is_array( $count ) ) {
		return 0;
	}

	return absint( reset( $count )['count'] ?? 0 );
}

/**
 * Get a single post field (column) from the DB.
 *
 * @param int    $post_id Post ID.
 * @param string $field   The post column to retrieve (e.g. 'post_title', 'post_content').
 * @return mixed          The raw column value, or empty string if not found.
 * @since 1.0.0
 */
function sd_get_post_field( $post_id, $field = 'post_title' ) {
	// If we’re not bypassing WP’s default, just use get_post_field().
	if ( ! Helper::bypass_wp_interfere() ) {
		return get_post_field( $field, $post_id );
	}

	// Otherwise query the posts table directly.
	$row = sd_query()
		->select( $field )
		->from( 'posts' )
		->where( 'ID', '=', $post_id )
		->get( ARRAY_A );

	if ( ! $row || ! is_array( $row ) ) {
		return '';
	}

	// Return the first row's value for the specified field.
	$row = reset( $row );

	// If the field is not found, return an empty string.
	if ( ! isset( $row[ $field ] ) ) {
		return '';
	}

	return $row[ $field ];
}

/**
 * Get post categories of passed taxonomy, associated with a post.
 *
 * @param int    $post_id Post ID.
 * @param string $taxonomy Taxonomy name.
 * @return array<int>
 * @since 1.0.0
 */
function sd_get_post_categories( $post_id, $taxonomy ) {
	if ( ! Helper::bypass_wp_interfere() ) {
		$terms = get_the_terms( $post_id, $taxonomy );
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return [];
		}
		return array_map(
			static function ( $term ) {
				return absint( $term->term_id );
			},
			$terms
		);
	}

	$terms = sd_query()->select( 't.term_id' )->from( 'terms AS t' )->join( 'term_taxonomy AS tt', 't.term_id', '=', 'tt.term_id' )->join( 'term_relationships AS tr', 'tr.term_taxonomy_id', '=', 'tt.term_taxonomy_id' )->where( 'tr.object_id', $post_id )->where( 'tt.taxonomy', $taxonomy )->get( ARRAY_A );

	$terms = is_array( $terms ) ? $terms : [];
	$terms = array_map(
		static function ( $term ) {
			return absint( $term['term_id'] );
		},
		$terms
	);

	$terms = array_values( $terms );
	return array_map( 'absint', $terms );
}

/**
 * Get the space ID by post ID.
 *
 * @param int $post_id Post ID.
 * @return bool|array<int>
 * @since 1.0.0
 */
function sd_get_feed_space_by_post( $post_id ) {
	$post_type = sd_get_post_field( $post_id, 'post_type' );
	if ( $post_type !== SUREDASHBOARD_FEED_POST_TYPE ) {
		return false;
	}

	$forums_assigned = sd_get_post_categories( $post_id, SUREDASHBOARD_FEED_TAXONOMY );

	if ( empty( $forums_assigned ) || ! is_array( $forums_assigned ) ) {
		return false;
	}

	$posts = [];

	foreach ( $forums_assigned as $forum_id ) {
		$forum_spaces = sd_get_posts(
			[
				'post_type'  => [ SUREDASHBOARD_POST_TYPE ],
				'select'     => 'p.ID',
				'meta_query' => [
					[
						'key'     => 'feed_group_id',
						'value'   => $forum_id,
						'compare' => '=',
					],
				],
			]
		);

		if ( ! empty( $forum_spaces ) && is_array( $forum_spaces ) ) {
			foreach ( $forum_spaces as $post ) {
				$post = (array) $post;
				if ( empty( $post['ID'] ) ) {
					continue;
				}
				$posts[] = absint( $post['ID'] );
			}
		}
	}

	return $posts;
}

/**
 * Check the specific post belongs to which space.
 *
 * @param int    $post_id Post ID.
 * @param string $func_caller Function name to call.
 * @return bool|int
 * @since 1.0.0
 */
function sd_get_space_id_by_post( $post_id, $func_caller = 'sd_get_feed_space_by_post' ) {
	if ( ! $post_id || ! is_callable( $func_caller ) ) {
		return false;
	}

	$posts = call_user_func( $func_caller, $post_id );
	if ( ! $posts || ! is_array( $posts ) ) {
		return false;
	}

	$posts = array_values( $posts );
	$posts = array_unique( $posts );
	$posts = array_map( 'absint', $posts );

	return $posts[0] ?? 0;
}
