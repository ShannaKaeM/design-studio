<?php
/**
 * Portals TermMeta Initialize.
 *
 * @package SureDashboardPro
 */

namespace SureDashboardPro\Inc\Utils;

use SureDashboardPro\Inc\Traits\Get_Instance;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class TermMeta.
 *
 * @since 1.0.0
 */
class TermMeta {
	use Get_Instance;

	/**
	 * Constructor.
	 *
	 * @since 0.0.1-alpha.3
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize Hooks.
	 *
	 * @since 1.0.0
	 */
	public function init_hooks(): void {
	}
}
