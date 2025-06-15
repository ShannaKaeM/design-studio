<?php
/**
 * Frontend Assets.
 *
 * @package SureDashboardPro
 * @since 1.0.0
 */

namespace SureDashboardPro\Core;

use SureDashboardPro\Inc\Traits\Enqueue;
use SureDashboardPro\Inc\Traits\Get_Instance;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Assets setup
 *
 * @since 1.0.0
 */
class Assets {
	use Enqueue;
	use Get_Instance;

	/**
	 * Enqueue course assets.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function course_assets(): void {
		$this->register_enqueue_style(
			'course',
			SUREDASHBOARD_PRO_CSS_ASSETS_FOLDER . ( is_rtl() ? 'course-rtl' : 'course' ) . SUREDASHBOARD_CSS_SUFFIX,
			[]
		);

		$localized_data = apply_filters(
			'portal_pro_localized_frontend_data',
			[
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			]
		);

		$this->register_enqueue_localize_script(
			'course',
			SUREDASHBOARD_PRO_JS_ASSETS_FOLDER . 'course' . SUREDASHBOARD_JS_SUFFIX,
			[ 'portal-global' ],
			$localized_data
		);
	}
}
