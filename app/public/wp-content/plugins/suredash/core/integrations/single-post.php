<?php
/**
 * SinglePost Integration.
 *
 * @package SureDash
 */

namespace SureDashboard\Core\Integrations;

use SureDashboard\Inc\Traits\Get_Instance;
use SureDashboard\Inc\Utils\PostMeta;
use SureDashboard\Inc\Utils\WpPost;

defined( 'ABSPATH' ) || exit;

/**
 * SinglePost Integration.
 *
 * @since 1.0.0
 */
class SinglePost extends Base {
	use Get_Instance;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name        = 'SinglePost';
		$this->slug        = 'single-post';
		$this->description = __( 'SinglePost Integration', 'suredash' );
		$this->is_active   = true; // @phpstan-ignore-line

		parent::__construct( $this->name, $this->slug, $this->description, $this->is_active ); // @phpstan-ignore-line

		if ( ! $this->is_active ) {
			return;
		}
	}

	/**
	 * Get item single content.
	 *
	 * @param int  $post_id Post ID.
	 * @param bool $use_passed_post_id Use passed post ID.
	 * @return string|false
	 */
	public function get_integration_content( $post_id, $use_passed_post_id = false ) {
		/* Later check if this can be move on wp_enqueue_scripts hook. */
		if ( class_exists( 'UAGB_Post_Assets' ) && strpos( get_post_field( 'post_content', $post_id ), '<!-- wp:uagb/' ) !== false ) {
			$post_assets_instance = new \UAGB_Post_Assets( $post_id );
			$post_assets_instance->enqueue_scripts();
		}

		ob_start();

		if ( ! $this->is_active ) {
			return ob_get_clean();
		}

		if ( $use_passed_post_id ) {
			$remote_post_id = $post_id;
		} else {
			$render_type = PostMeta::get_post_meta_value( $post_id, 'post_render_type' );
			if ( $render_type === 'wordpress' ) {
				$remote_post_data = PostMeta::get_post_meta_value( $post_id, 'wp_post' );
				$remote_post_id   = absint( is_array( $remote_post_data ) && ! empty( $remote_post_data['value'] ) ? $remote_post_data['value'] : 0 );
			} else {
				$remote_post_id = $post_id;
			}
		}

		if ( $remote_post_id ) {
			$entry_content_classes = apply_filters( 'suredash_single_post_content_classes', 'sd-overflow-hidden suredash-single-content entry-content' );
			echo '<div class="' . esc_attr( $entry_content_classes ) . '">';

			do_action( 'suredashboard_before_wp_single_content_load', $remote_post_id );

			$remote_wp_post = new WpPost( $remote_post_id );
			$remote_wp_post->enqueue_assets();
			echo do_shortcode( apply_filters( 'the_content', $remote_wp_post->render_content() ) );

			do_action( 'suredashboard_after_wp_single_content_load', $remote_post_id );

			echo '</div>';
		}

		return ob_get_clean();
	}
}
