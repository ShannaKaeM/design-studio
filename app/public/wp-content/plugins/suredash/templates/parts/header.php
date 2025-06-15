<?php
/**
 * The template for displaying portal header.
 *
 * @see     https://developer.wordpress.org/themes/basics/template-hierarchy/
 * @package SureDash\Templates
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

use SureDashboard\Inc\Utils\Helper;
use SureDashboard\Inc\Utils\Labels;

$portal_name = Helper::get_option( 'portal_name' );
$logo_url    = Helper::get_option( 'logo_url' );
$home_link   = Helper::get_option( 'portal_as_homepage' ) ? home_url() : '/' . SUREDASHBOARD_SLUG . '/';

// Show the notification indicator.
$highlighter = '<span class="notification-unread-count sd-absolute sd-flex sd-items-center sd-justify-center sd-font-12 sd-px-6 sd-max-h-20 sd-font-medium sd-bg-danger sd-color-light sd-min-w-20 sd-nowrap sd-radius-9999"></span>';

echo do_shortcode(
	apply_filters(
		'portal_single_header_output',
		sprintf(
			'
				<section class="portal-sec-header-container portal-content">
					<div class="portal-sec-header">
						<div class="left-portal-header">
							<div class="pfd-resp-navigation sd-hide-desktop-lg"> [portal_responsive_navigation] </div>
							<div class="portal-header-heading"> <%1$s class="portal-banner-heading sd-no-space"> <a href="/%4$s/" class="portal-site-identity">%2$s <span class="sd-flex sd-flex-wrap sd-wrap"> %3$s </span> </a> </%1$s> </div>
						</div>

						<div class="right-portal-header">
							<span class="portal-header-search-trigger"> %9$s </span>

							<div class="portal-user-settings-wrap sd-flex sd-gap-4 sd-items-center">
								<a href="#" class="portal-notification-trigger" title="%5$s">
									%7$s
									%8$s
								</a>
							</div>

							%10$s

							[portal_responsive_menu]
						</div>
					</div>
				</section>
			',
			apply_filters( 'suredashboard_header_heading_tag', 'h2' ),
			! empty( $logo_url ) ? sprintf( '<img src="%s" alt="%s" class="portal-logo sd-border-none sd-outline-none">', esc_url( $logo_url ), '<span>' . esc_html( $portal_name ) . '</span>' ) : '',
			apply_filters( 'suredashboard_header_heading', $portal_name ),
			$home_link,
			Labels::get_label( 'notifications', false ),
			Labels::get_label( 'bookmarks', false ),
			do_shortcode( Helper::get_library_icon( 'Bell', false, 'sm' ) ),
			wp_kses_post( $highlighter ),
			do_shortcode( Helper::get_library_icon( 'Search', false, 'sm' ) ),
			render_block( [ 'blockName' => 'suredash/profile' ] )
		)
	)
);
