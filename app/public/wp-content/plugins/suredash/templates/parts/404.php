<?php
/**
 * The template for displaying content area view.
 *
 * @see     https://developer.wordpress.org/themes/basics/template-hierarchy/
 * @package SureDash\Templates
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

use SureDashboard\Inc\Utils\Labels;

$default_args = [
	'404_heading'    => Labels::get_label( '404_heading' ),
	'not_found_text' => Labels::get_label( 'no_posts_found' ),
];

if ( ! isset( $args ) ) {
	$args = [];
}

$args = wp_parse_args( $args, $default_args );

$heading     = $args['404_heading'];
$description = $args['not_found_text'];

echo do_shortcode(
	apply_filters(
		'suredashboard_404_content_output',
		sprintf(
			'
				<section class="portal-content-area sd-flex sd-justify-center sd-items-center portal-not-found-wrapper sd-box-shadow">
					%1$s
				</section>
			',
			sprintf(
				'
					<div class="portal-content sd-flex sd-flex-col sd-gap-16 sd-max-w-custom sd-mx-auto sd-mt-32 sd-mb-32 sd-text-center sd-items-center sd-p-custom" style="--sd-max-w-custom: 600px; --sd-p-custom: 40px; margin: 32px auto;">
						<img src="%1$s" alt="%2$s" class="portal-404-image sd-max-w-100 sd-w-full sd-h-full" />
						<h4 class="portal-item-title sd-no-space"> %3$s </h4>
						%4$s
						<a href="%5$s" class="portal-button button-primary sd-w-fit sd-flex sd-self-center">%6$s</a>
					</div>
				',
				esc_url( SUREDASHBOARD_URL . 'assets/images/404.svg' ),
				$heading,
				$heading,
				$description,
				esc_url( home_url( '/' . SUREDASHBOARD_SLUG ) ),
				Labels::get_label( 'back_to_home' )
			)
		)
	)
);
