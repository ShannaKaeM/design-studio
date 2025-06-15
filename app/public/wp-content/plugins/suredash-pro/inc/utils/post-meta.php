<?php
/**
 * Portals PostMeta Initialize.
 *
 * @package SureDashboardPro
 */

namespace SureDashboardPro\Inc\Utils;

use SureDashboardPro\Inc\Traits\Get_Instance;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class PostMeta.
 *
 * @since 1.0.0
 */
class PostMeta {
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
		add_filter( 'suredashboard_post_meta_dataset', [ $this, 'update_pro_post_dataset' ] );
	}

	/**
	 * Update Pro Post Dataset.
	 *
	 * @since 0.0.1-alpha.3
	 *
	 * @param array $dataset Post Dataset.
	 * @return array
	 */
	public function update_pro_post_dataset( $dataset ) {
		$pro_post_meta_set = [
			'private_forum' => [
				'default' => false,
				'type'    => 'boolean',
			],
		];

		return array_merge( $dataset, $pro_post_meta_set );
	}
}
