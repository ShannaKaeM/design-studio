<?php
/**
 * Plugin.
 *
 * @package SureDash
 * @since 0.0.1
 */

namespace SureDashboard\Inc\Compatibility;

defined( 'ABSPATH' ) || exit;

use SureDashboard\Inc\Traits\Get_Instance;
use SureDashboard\Inc\Utils\PostMeta;

/**
 * Have compatibility with active theme.
 *
 * @since 0.0.1
 */
class Plugin {
	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since 0.0.1
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_page_assets' ] );
		add_action( 'suredash_after_plugin_activation', [ $this, 'prevent_other_plugin_redirection' ], 10, 2 );
	}

	/**
	 * Prevent other plugin redirection.
	 *
	 * @param string $plugin_init Plugin init.
	 * @param string $plugin_slug Plugin slug.
	 *
	 * @since 1.0.0
	 */
	public function prevent_other_plugin_redirection( $plugin_init, $plugin_slug ): void {

		switch ( $plugin_init ) {
			case 'suretriggers/suretriggers.php':
				delete_transient( 'st-redirect-after-activation' );
				break;
			case 'surecart/surecart.php':
				update_option( 'surecart_source', 'suredash', false );
				break;

			default:
				break;
		}

		// Tracking BSF Analytics UTM.
		if ( class_exists( 'BSF_UTM_Analytics\Inc\Utils' ) && is_callable( '\BSF_UTM_Analytics\Inc\Utils::update_referer' ) ) {
			\BSF_UTM_Analytics\Inc\Utils::update_referer( 'suredash', $plugin_slug );
		}
	}

	/**
	 * Get WP Content assets.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_page_assets(): void {
		$post_id = absint( get_the_ID() );

		$content_type = PostMeta::get_post_meta_value( (int) $post_id, 'integration' );
		if ( $content_type !== 'single_post' ) {
			return;
		}

		$remote_post_data = PostMeta::get_post_meta_value( (int) $post_id, 'wp_post' );
		$remote_post_id   = absint( is_array( $remote_post_data ) && ! empty( $remote_post_data['value'] ) ? $remote_post_data['value'] : 0 );

		if ( ! $remote_post_id ) {
			return;
		}

		if ( ! method_exists( PageBuilder::get_instance(), 'enqueue_page_assets' ) ) {
			return;
		}

		PageBuilder::get_instance()->enqueue_page_assets( $remote_post_id );
	}

	/**
	 * Render PrestoPlayer block.
	 *
	 * @param array<string, mixed> $block Block data.
	 * @return string HTML content.
	 * @since 1.0.0
	 */
	public static function render_presto_player_block( $block ) {
		$html = '';

		if ( empty( $block['blockName'] ) ) {
			return $html;
		}

		if ( $block['blockName'] !== 'presto-player/playlist' ) {
			$media_id = $block['attrs']['id'] ?? 0;
			$media_id = absint( $media_id );
			$html    .= do_shortcode( '[presto_player id="' . $media_id . '"]' );
		} else {
			$html .= render_block( $block );
		}

		return $html;
	}
}
