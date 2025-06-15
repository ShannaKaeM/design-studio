<?php
/**
 * Portals Query Backend_Feeds Initialize.
 *
 * @package SureDash
 */

namespace SureDashboard\Core\Models;

use PhpParser\Node\Expr\Cast\Array_;
use SureDashboard\Inc\Traits\Get_Instance;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Backend_Feeds Query Model.
 */
class Backend_Feeds {
	use Get_Instance;

	/**
	 * Get queried data.
	 *
	 * @param array<mixed> $args Args.
	 *
	 * @return mixed
	 */
	public static function get_query_post_data( $args ) {
		$search_string = $args['s'] ?? '';
		$post_type     = $args['post_type'] ?? '';
		$is_tax_query  = $args['is_tax_query'] ?? false;
		$post_status   = $args['post_status'] ?? 'publish';
		$no_of_posts   = $args['posts_per_page'] ?? null;
		$taxonomy      = $args['taxonomy'] ?? '';
		$category      = $args['category'] ?? '';

		$query = sd_query()
			->select( '*' )
			->from( 'posts AS p' )
			->where( 'p.post_type', '=', $post_type )
			->where( 'p.post_status', '=', $post_status )
			->whereRaw( "(p.post_title LIKE '%{$search_string}%' OR p.post_content LIKE '%{$search_string}%')" )
			->limit( $no_of_posts );

		// Add taxonomy condition if $is_tax_query is true.
		if ( $is_tax_query ) {
			$query->join( 'term_relationships AS tr', 'p.ID', '=', 'tr.object_id' )
				->join( 'term_taxonomy AS tt', 'tr.term_taxonomy_id', '=', 'tt.term_taxonomy_id' )
				->where( 'tt.taxonomy', '=', $taxonomy )
				->where( 'tt.term_id', '=', $category );
		}

		return $query->get( ARRAY_A );
	}

	/**
	 * Get uncategorized items data
	 *
	 * @param array<mixed> $args Args.
	 *
	 * @return mixed
	 */
	public static function get_query_uncategorized_items( $args = [] ) {

		$term_taxonomy_ids = sd_query()
			->select( 'GROUP_CONCAT(term_taxonomy_id) AS ids' )
			->from( 'term_taxonomy' )
			->where( 'taxonomy', '=', SUREDASHBOARD_TAXONOMY )->get( ARRAY_A );

		$term_taxonomy_ids = is_array( $term_taxonomy_ids ) && isset( $term_taxonomy_ids[0]['ids'] ) ? explode( ',', $term_taxonomy_ids[0]['ids'] ) : [];

		$object_ids = sd_query()
			->select( 'GROUP_CONCAT(object_id) AS ids' )
			->from( 'term_relationships' )
			->whereIn(
				'term_taxonomy_id',
				$term_taxonomy_ids
			)->get( ARRAY_A );

		$object_ids = is_array( $object_ids ) && isset( $object_ids[0]['ids'] ) ? explode( ',', $object_ids[0]['ids'] ) : [];

		$uncategorized_items = sd_query()
			->select( 'GROUP_CONCAT(ID) as ids' )
			->from( 'posts' )
			->where( 'post_type', '=', SUREDASHBOARD_POST_TYPE )
			->where( 'post_status', '!=', 'trash' )
			->whereNotIn(
				'ID',
				$object_ids
			)
			->get( ARRAY_A );

		$uncategorized_items = is_array( $uncategorized_items ) && isset( $uncategorized_items[0]['ids'] ) ? explode( ',', $uncategorized_items[0]['ids'] ) : [];
		return sd_query()
			->select( '*' )
			->from( 'posts' )
			->where( 'post_type', '=', SUREDASHBOARD_POST_TYPE )
			->where( 'post_status', '!=', 'trash' )
			->where( 'ID', 'IN', $uncategorized_items )
			->get( ARRAY_A );
	}
}
