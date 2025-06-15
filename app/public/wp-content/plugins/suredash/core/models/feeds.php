<?php
/**
 * Portals Query Feeds Initialize.
 *
 * @package SureDash
 */

namespace SureDashboard\Core\Models;

use SureDashboard\Inc\Traits\Get_Instance;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Feeds Query Model.
 */
class Feeds {
	use Get_Instance;

	/**
	 * Get queried data.
	 *
	 * @param array<mixed> $args Args.
	 *
	 * @return mixed
	 */
	public static function get_query_data( $args ) {
		$category_id = $args['category_id'] ?? 0;
		$post_type   = $args['post_type'] ?? '';
		$taxonomy    = $args['taxonomy'] ?? '';
		$no_of_posts = $args['posts_per_page'] ?? 5;
		$paged       = $args['paged'] ?? 1;
		$offset      = ( $paged - 1 ) * $no_of_posts;

		$query = sd_query()->select(
			'p.ID, p.post_title, p.post_type, p.post_status, p.post_date, p.post_author'
		)->from( 'posts AS p' );

		if ( $category_id ) {
			$query->join( 'term_relationships AS tr', 'p.ID', '=', 'tr.object_id' )
				->join( 'term_taxonomy AS tt', 'tr.term_taxonomy_id', '=', 'tt.term_taxonomy_id' )
				->join( 'terms AS t', 'tt.term_id', '=', 't.term_id' ) // Ensure the join with 'terms' to filter based on terms.
				->where( 'tt.taxonomy', '=', $taxonomy ) // Match the taxonomy.
				->where( 'tt.term_id', '=', $category_id ); // Match the category ID.
		}

		$query->where( 'p.post_type', '=', $post_type ) // Match the post type.
			->where( 'p.post_status', '=', 'publish' ) // Filter only published posts.
			->limit( $no_of_posts ) // Limit the number of posts to match posts_per_page in WP_Query.
			->offset( $offset ) // Apply pagination using OFFSET.
			->order_by( 'p.post_date', 'DESC' ); // Order by post date.

		return $query->get( ARRAY_A );
	}

	/**
	 * Get user query data.
	 *
	 * @param array<mixed> $args Args.
	 *
	 * @return mixed
	 */
	public static function get_user_query_data( $args ) {
		$user_id     = $args['user_id'] ?? 0;
		$post_types  = $args['post_types'] ?? [];
		$no_of_posts = $args['posts_per_page'] ?? 5;
		$paged       = $args['paged'] ?? 1;
		$offset      = ( $paged - 1 ) * $no_of_posts;

		return sd_query()->select( 'p.ID, p.post_title, p.post_status, p.post_type, p.post_author, p.post_date' )
			->from( 'posts AS p' )
			->where( 'p.post_status', '=', 'publish' )  // Filter only published posts.
			->where( 'p.post_author', '=', $user_id )  // Filter by author.
			->whereIn( 'p.post_type', $post_types )  // Filter by post types (this handles multiple post types).
			->limit( $no_of_posts )  // Limit the number of posts as in posts_per_page.
			->offset( $offset ) // Apply pagination using OFFSET.
			->get( ARRAY_A );
	}
}
