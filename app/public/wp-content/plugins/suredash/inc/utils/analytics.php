<?php
/**
 * Analytics.
 *
 * @package SureDashboard
 * @since 0.0.6
 */

namespace SureDashboard\Inc\Utils;

use SureDashboard\Inc\Traits\Get_Instance;

defined( 'ABSPATH' ) || exit;

/**
 * Update Compatibility
 *
 * @package SureDashboard
 */
class Analytics {
	use Get_Instance;

	/**
	 *  Constructor
	 */
	public function __construct() {
		$this->set_bsf_analytics_entity();
		add_filter( 'bsf_core_stats', [ $this, 'add_suredash_analytics_data' ] );
	}

	/**
	 * Set BSF Analytics Entity.
	 *
	 * @since 0.0.5
	 */
	public function set_bsf_analytics_entity(): void {
		$sd_bsf_analytics = \BSF_Analytics_Loader::get_instance(); // @phpstan-ignore-line

		$sd_bsf_analytics->set_entity(
			[
				'suredash' => [
					'product_name'        => 'SureDash',
					'path'                => SUREDASHBOARD_DIR . 'inc/lib/bsf-analytics',
					'author'              => 'SureDash',
					'time_to_display'     => '+24 hours',
					'deactivation_survey' => apply_filters(
						'suredash_deactivation_survey_data',
						[
							[
								'id'                => 'deactivation-survey-suredash',
								'popup_logo'        => SUREDASHBOARD_URL . 'assets/icons/icon.svg',
								'plugin_slug'       => 'suredash',
								'popup_title'       => 'Quick Feedback',
								'support_url'       => 'https://suredash.com/contact/',
								'popup_description' => 'If you have a moment, please share why you are deactivating SureDash:',
								'show_on_screens'   => [ 'plugins' ],
								'plugin_version'    => SUREDASHBOARD_VER,
							],
						]
					),
				],
			]
		);
	}

	/**
	 * Callback function to add SureDash specific analytics data.
	 *
	 * @param array<mixed> $stats_data existing stats_data.
	 * @return array<mixed> $stats_data modified stats_data.
	 * @since 0.0.5
	 */
	public function add_suredash_analytics_data( $stats_data ) {

		$settings = Settings::get_suredash_settings();

		$stats_data['plugin_data']['suredash'] = [
			'free_version'           => SUREDASHBOARD_VER,
			'site_language'          => get_locale(),
			'bypass_wp_interactions' => $settings['bypass_wp_interactions'] ?? '',
			'content_cpt'            => $settings['content_cpt'] ?? '',
			'topic_cpt'              => $settings['topic_cpt'] ?? '',
			'hidden_community'       => $settings['hidden_community'] ?? '',
		];

		return $stats_data;
	}
}
