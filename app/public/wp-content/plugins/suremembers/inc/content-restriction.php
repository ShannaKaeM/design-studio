<?php
/**
 * Content Restriction.
 *
 * @package suremembers
 * @since 1.0.0
 */

namespace SureMembers\Inc;

use SureMembers\Inc\Traits\Get_Instance;


/**
 * Content Restriction
 *
 * @since 0.0.1
 */
class Content_Restriction {

	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since  0.0.1
	 */
	public function __construct() {
		add_shortcode( 'suremembers_restrict', [ $this, 'restricted_content' ] );
	}

	/**
	 * Generates content from shortcode 'suremembers_restrict' as per users plan.
	 *
	 * @param array  $atts shortcode attributes.
	 * @param string $content shortcode content.
	 * @return string
	 * @since 1.0.0
	 */
	public function restricted_content( $atts, $content ) {
		$user_id = intval( get_current_user_id() );
		if ( ! $user_id ) {
			return '';
		}
		array_map( 'sanitize_text_field', $atts );
		extract( $atts ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		$content_access_groups = ! empty( $access_group_ids ) ? explode( ',', str_replace( ' ', '', trim( $access_group_ids ) ) ) : [];
		$has_access            = Access_Groups::check_if_user_has_access( $content_access_groups );
		return $has_access ? "<p>$content</p>" : '';
	}
}
