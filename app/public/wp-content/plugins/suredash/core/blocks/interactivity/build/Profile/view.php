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

if ( ! suredash_content_post() ) {
	$attributes = $attributes ?? [];
	$block_atts = [
		'onlyavatar'                   => boolval( $attributes['onlyavatar'] ),
		'menuopenverposition'          => $attributes['menuopenverposition'] ?? 'top',
		'menuopenhorposition'          => $attributes['menuopenhorposition'] ?? 'left',
		'menuhorpositionoffset'        => $attributes['menuhorpositionoffset'] ?? '',
		'menuverpositionoffset'        => $attributes['menuverpositionoffset'] ?? '',
		'makefixed'                    => boolval( $attributes['makefixed'] ),
		'blockstickyverpositionoffset' => $attributes['blockstickyverpositionoffset'] ?? '',
		'blockmaxwidth'                => $attributes['blockmaxwidth'] ?? '',
		'stickyhorpositionoffset'      => $attributes['stickyhorpositionoffset'] ?? '',
		'stickyverpositionoffset'      => $attributes['stickyverpositionoffset'] ?? '',
	];

	add_filter(
		'suredash_user_profile_attributes',
		static function ( $atts ) use ( $block_atts ) {
			return $block_atts;
		}
	);

	$classes = $block_atts['makefixed'] ? 'suredash-profile--fixed portal-content' : 'portal-content';

	?>
		<div <?php echo do_shortcode( get_block_wrapper_attributes( [ 'class' => $classes ] ) ); ?>>
			<?php
				printf(
					'<style class="suredash-profile-block-css">
						.wp-block-suredash-profile img {
							max-width:%1$s !important;
							max-height:%1$s !important;
							width:%1$s !important;
							height:%1$s !important;
						}
						.suredash-profile--fixed .portal-user-profiles-wrap {
							bottom: %2$s;
							max-width: %3$s;
						}
					</style>',
					esc_attr( ! empty( $attributes['avatarsize'] ) ? $attributes['avatarsize'] : '40px' ),
					esc_attr( ! empty( $block_atts['blockstickyverpositionoffset'] ) ? $block_atts['blockstickyverpositionoffset'] : '0' ),
					esc_attr( ! empty( $block_atts['blockmaxwidth'] ) ? $block_atts['blockmaxwidth'] : '100%' )
				);

				echo do_shortcode( '[portal_user_profile]' );
			?>
		</div>
	<?php
}
