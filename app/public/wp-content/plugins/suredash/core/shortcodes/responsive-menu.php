<?php
/**
 * Portals ResponsiveMenu Shortcode Initialize.
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
 * Class ResponsiveMenu Shortcode.
 */
class ResponsiveMenu {
	use Shortcode;
	use Get_Instance;

	/**
	 * Register_shortcode_event.
	 *
	 * @return void
	 */
	public function register_shortcode_event(): void {
		$this->add_shortcode( 'responsive_menu' );
	}

	/**
	 * Display menu.
	 *
	 * @param array<mixed> $atts Array of attributes.
	 * @since 1.0.0
	 * @return string|false
	 */
	public function render_responsive_menu( $atts ) {
		$atts = shortcode_atts(
			[],
			$atts
		);

		if ( ! has_nav_menu( 'portal_menu' ) ) {
			return false;
		}

		ob_start();
		?>
			<div class="portal-resp-header-menu-wrap">
				<div class="resp-menu-inner-wrap">
					<label class="portal-toggle-menu pfd-svg-icon" for="pfd-toggle" tabindex="0">
						<?php Helper::get_library_icon( 'EllipsisVertical', true ); ?>
					</label>
					<div class="pfd-menu-wrapper">
						<?php
							wp_nav_menu(
								[
									'theme_location'  => 'portal_menu',
									'menu_class'      => 'pfd-menu sd-no-space',
									'container'       => 'nav',
									'container_class' => 'sd-menu-container',
									'depth'           => 1,
								]
							);
						?>
					</div>
				</div>

			</div>
		<?php

		return ob_get_clean();
	}
}
