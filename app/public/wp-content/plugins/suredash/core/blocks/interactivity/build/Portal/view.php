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

$content    = $content ?? '';
$attributes = $attributes ?? [];

?>
<div <?php echo do_shortcode( get_block_wrapper_attributes( [ 'class' => 'portal-body-container' ] ) ); ?>>
	<?php
		printf(
			'<style class="suredash-portal-main-block-css">
				.portal-sidebar {
					--portal-sidebar-top-offset: %1$s;
				}
			</style>',
			esc_attr( suredash_content_post() ? '0px' : ( $attributes['sidebartopoffset'] ?? '' ) )
		);

		echo do_blocks( $content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
</div>
