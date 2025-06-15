<?php
/**
 * PHP file to use when rendering the block type on the server to show on the front end.
 *
 * The following variables are exposed to the file:
 *     $attributes (array): The block attributes.
 *     $content (string): The block default content.
 *     $block (WP_Block): The block instance.
 *
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 * @package suredash
 * @since 0.0.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SureDashboard\Inc\Utils\Helper;
use SureDashboard\Inc\Utils\Labels;

$content   = '';
$sub_query = suredash_get_sub_queried_page();

switch ( true ) {
	case is_front_page() && Helper::get_option( 'portal_as_homepage' ):
		if ( suredash_is_sub_queried_page() ) {
			$content = do_shortcode( '[portal_home_content type="' . $sub_query . '"]' );
		} else {
			$content = do_shortcode( '[portal_home_content]' );
		}
		break;

	case suredash_is_sub_queried_page():
		$content = do_shortcode( '[portal_home_content type="' . $sub_query . '"]' );
		break;

	case is_singular( SUREDASHBOARD_SUB_CONTENT_POST_TYPE ):
		$endpoint = suredash_content_post();
		$content  = do_shortcode( '[portal_single_endpoint_content get_only_content="true" endpoint="' . $endpoint . '"]' );
		break;

	case suredash_portal():
		if ( is_singular( SUREDASHBOARD_POST_TYPE ) ) {
			$content = do_shortcode( '[portal_single_content skip_header="true"]' );
		} else {
			$content = do_shortcode( '[portal_home_content]' );
		}
		break;

	case is_tax( SUREDASHBOARD_FEED_TAXONOMY ):
	case is_post_type_archive( SUREDASHBOARD_FEED_POST_TYPE ):
		$content = do_shortcode( '[portal_archive_content skip_header="true"]' );
		break;

	case is_post_type_archive( SUREDASHBOARD_SUB_CONTENT_POST_TYPE ):
		$content = '';
		break;

	case is_singular( SUREDASHBOARD_FEED_POST_TYPE ):
		$content = do_shortcode( '[portal_single_content skip_header="true"]' );
		break;
}

if ( empty( $content ) ) {
	$content = suredash_get_template_part(
		'parts',
		'404',
		[
			'not_found_text' => Labels::get_label( 'notify_message_error_occurred' ),
		],
		true
	);
}

?>
	<div <?php echo do_shortcode( get_block_wrapper_attributes( [ 'class' => '' ] ) ); ?>>
		<?php
			echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
	</div>
<?php
