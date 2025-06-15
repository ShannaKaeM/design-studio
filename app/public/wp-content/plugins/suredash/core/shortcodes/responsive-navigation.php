<?php
/**
 * Portals Docs ResponsiveNavigation Shortcode Initialize.
 *
 * @package SureDash
 */

namespace SureDashboard\Core\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use SureDashboard\Inc\Traits\Get_Instance;
use SureDashboard\Inc\Traits\Shortcode;
use SureDashboard\Inc\Utils\Helper;

/**
 * Class ResponsiveNavigation Shortcode.
 */
class ResponsiveNavigation {
	use Shortcode;
	use Get_Instance;

	/**
	 * Set status for aside navigation markup loaded.
	 *
	 * @var bool
	 */
	private $aside_navigation_markup_loaded = false;

	/**
	 * Register_shortcode_event.
	 *
	 * @return void
	 */
	public function register_shortcode_event(): void {
		$this->add_shortcode( 'responsive_navigation' );
	}

	/**
	 * Display docs menu.
	 *
	 * @param array<mixed> $atts Array of attributes.
	 * @since 1.0.0
	 * @return string|false
	 */
	public function render_responsive_navigation( $atts ) {
		$atts = shortcode_atts(
			[],
			$atts
		);

		ob_start();

		?>
			<div class="sd-resp-docs-navigation-wrap portal-hide-on-desktop">
				<label class="sd-portal-navigation-toggle pfd-svg-icon" tabindex="0">
					<span class="portal-svg-icon portal-icon-lg">
						<svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M5 5v1.5h14V5H5zm0 7.8h14v-1.5H5v1.5zM5 19h14v-1.5H5V19z"></path></svg>
					</span>
				</label>
				<label class="sd-portal-navigation-close pfd-svg-icon portal-hide" tabindex="0">
					<?php Helper::get_library_icon( 'X', true ); ?>
				</label>
			</div>
		<?php

		add_action( 'wp_footer', [ $this, 'process_global_portal_query' ] );

		return ob_get_clean();
	}

	/**
	 * Get the global docs query.
	 *
	 * @since 1.0.0
	 */
	public function process_global_portal_query(): void {
		if ( $this->aside_navigation_markup_loaded ) {
			return;
		}

		?>
			<div class="portal-bg-overlay"></div>
			<!-- Have markup from navigation using JS. -->
			<div class="portal-aside-list-wrapper portal-footer-resp-nav sd-custom-scroll portal-content wp-block-suredash-navigation"></div>
		<?php

		$this->aside_navigation_markup_loaded = true;
	}
}
