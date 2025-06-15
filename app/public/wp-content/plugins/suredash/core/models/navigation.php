<?php
/**
 * Portals Query Navigation Initialize.
 *
 * @package SureDash
 */

namespace SureDashboard\Core\Models;

use SureDashboard\Inc\Traits\Get_Instance;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Navigation Query Model.
 */
class Navigation {
	use Get_Instance;

	/**
	 * Get queried data.
	 *
	 * @param array<mixed> $args Arguments.
	 *
	 * @return mixed
	 */
	public static function get_query_data( $args = [] ) {
		return sd_query()->select(
			"t.term_id, t.name, t.slug, tt.taxonomy, tt.description, tt.parent, tt.count, tr.object_id, p.ID, p.post_title, p.post_status, GROUP_CONCAT(DISTINCT CASE WHEN tm.meta_key = 'group_tax_position' THEN tm.meta_value END) AS space_group_position, GROUP_CONCAT(DISTINCT CASE WHEN tm.meta_key = '_link_order' THEN tm.meta_value END) AS space_position, GROUP_CONCAT(DISTINCT CASE WHEN tm.meta_key = 'hide_label' THEN tm.meta_value END) AS hide_label, GROUP_CONCAT(DISTINCT CASE WHEN pm.meta_key = 'integration' THEN pm.meta_value END) AS integration, GROUP_CONCAT(DISTINCT CASE WHEN pm.meta_key = 'layout' THEN pm.meta_value END) AS layout, GROUP_CONCAT(DISTINCT CASE WHEN pm.meta_key = 'image_url' THEN pm.meta_value END) AS image_url, GROUP_CONCAT(DISTINCT CASE WHEN pm.meta_key = 'item_emoji' THEN pm.meta_value END) AS item_emoji, GROUP_CONCAT(DISTINCT CASE WHEN pm.meta_key = 'link_url' THEN pm.meta_value END) AS link_url, GROUP_CONCAT(DISTINCT CASE WHEN pm.meta_key = 'link_target' THEN pm.meta_value END) AS link_target"
		)->from( 'terms AS t' )->join( 'term_taxonomy AS tt', 't.term_id', '=', 'tt.term_id' )->leftJoin( 'term_relationships AS tr', 'tr.term_taxonomy_id', '=', 'tt.term_taxonomy_id' )->leftJoin( 'posts AS p', 'p.ID', '=', 'tr.object_id' )->leftJoin(
			'termmeta AS tm',
			// @phpstan-ignore-next-line
			static function( $q ): void {
				$q->where( 't.term_id', '=', 'tm.term_id' )->whereIn( 'tm.meta_key', [ 'group_tax_position', '_link_order', 'hide_label' ] );
			}
		)->leftJoin(
			'postmeta AS pm',
			// @phpstan-ignore-next-line
			static function( $q ): void {

				$q->where( 'p.ID', '=', 'pm.post_id' )->whereIn( 'pm.meta_key', [ 'integration', 'layout', 'image_url', 'item_emoji', 'link_url', 'link_target' ] );
			}
		)->where( 'tt.taxonomy', '=', SUREDASHBOARD_TAXONOMY )->whereRaw( '(tm.meta_value IS NOT NULL OR tm.meta_key IS NULL)' )->group_by( 't.term_id, p.ID' )
		->get( ARRAY_A );
	}
}
