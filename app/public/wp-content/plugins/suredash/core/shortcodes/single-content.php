<?php
/**
 * Portals Single Content Shortcode Initialize.
 *
 * @package SureDash
 */

namespace SureDashboard\Core\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use SureDashboard\Core\Integrations\Feeds;
use SureDashboard\Core\Integrations\SinglePost;
use SureDashboard\Inc\Traits\Get_Instance;
use SureDashboard\Inc\Traits\Shortcode;
use SureDashboard\Inc\Utils\Helper;
use SureDashboard\Inc\Utils\PostMeta;

/**
 * Class SingleContent Shortcode.
 */
class SingleContent {
	use Shortcode;
	use Get_Instance;

	/**
	 * Register_shortcode_event.
	 *
	 * @return void
	 */
	public function register_shortcode_event(): void {
		$this->add_shortcode( 'single_content' );
	}

	/**
	 * Load integration type wise content.
	 *
	 * @param array<mixed> $atts Array of attributes.
	 * @since 1.0.0
	 */
	public function render_single_content_markup( $atts ): void {
		$post_id     = absint( $atts['post_id'] );
		$integration = $atts['integration'];
		$layout      = strval( $atts['layout'] );
		$style       = strval( $atts['layout_style'] );

		$layout_details = Helper::get_layout_details( $layout, $style );
		$layout         = $layout_details['layout'];
		$layout_style   = $layout_details['style'];

		$is_preview = is_singular( SUREDASHBOARD_FEED_POST_TYPE );

		remove_filter( 'the_content', 'wpautop' );

		switch ( $integration ) {
			case 'course':
				if ( is_callable( 'suredash_pro_course_integration_content' ) ) {
					echo '<div id="portal-post-' . esc_attr( (string) $post_id ) . '" class="portal-content-area sd-box-shadow portal-content portal-content-type-' . esc_attr( $integration ) . '">';
					echo do_shortcode( apply_filters( 'the_content', suredash_course_integration_content( $post_id ), $post_id ) );
				} else {
					suredash_get_template_part(
						'parts',
						'404',
						[
							'404_heading'    => __( 'Premium Integration', 'suredash' ),
							'not_found_text' => __( 'This feature is available in the premium version of SureDash.', 'suredash' ),
						]
					);
					return;
				}
				break;

			case 'single_post': // phpcs:ignore -- Spell auto-corrects to 'WordPress' which is not intended here.
				$wrapper_class = $layout === 'full_width' && $layout_style === 'unboxed' ? '' : 'portal-content-area sd-' . esc_attr( $layout_style ) . '-post'; // If layout is full width and layout style is unboxed then let user design their own content-section.
				echo '<div id="portal-post-' . esc_attr( (string) $post_id ) . '" class="' . esc_attr( $wrapper_class ) . '">';
				if ( method_exists( SinglePost::get_instance(), 'get_integration_content' ) ) {
					echo do_shortcode( apply_filters( 'the_content', SinglePost::get_instance()->get_integration_content( $post_id ), $post_id ) );
				}
				break;

			case 'posts_discussion':
				echo '<div id="portal-post-' . esc_attr( (string) $post_id ) . '" class="portal-content-area portal-content-type-' . esc_attr( $integration ) . '">';
				if ( method_exists( Feeds::get_instance(), 'get_integration_content' ) ) {
					echo do_shortcode( apply_filters( 'the_content', Feeds::get_instance()->get_integration_content( $post_id ), $post_id ) );
				}
				break;

			default:
				echo '<div id="portal-post-' . esc_attr( (string) $post_id ) . '" class="portal-content-area portal-content sd-' . esc_attr( $layout_style ) . '-post">';
				echo do_shortcode( Helper::suredash_featured_cover( $post_id ) ); // @phpstan-ignore-line
				if ( suredash_cpt() ) {
					echo wp_kses_post( '<h1 class="portal-store-post-title sd-title-border"> ' . get_the_title( $post_id ) . ' </h1>' );
				}
				echo do_shortcode( apply_filters( 'the_content', get_the_content(), $post_id ) );
				break;
		}

		$space_id    = sd_get_space_id_by_post( $post_id );
		$id          = $space_id ? $space_id : $post_id;
		$integration = $space_id ? sd_get_post_meta( (int) $space_id, 'integration', true ) : $integration;
		$is_comment  = $integration === 'single_post' ? sd_get_post_meta( (int) $id, 'comments', true ) : sd_get_post_field( (int) $id, 'comment_status' ) === 'open';

		if ( $is_comment && ( $integration === 'single_post' || $is_preview ) ) {
			echo do_shortcode( '[portal_single_comments comments="' . esc_attr( $is_comment ) . '"]' );
		}
		echo '</div>'; // End of portal-content-area.

		add_filter( 'the_content', 'wpautop' );
	}

	/**
	 * Display Single Post Content.
	 *
	 * @param array<mixed> $atts Array of attributes.
	 * @since 1.0.0
	 *
	 * @return string|null
	 */
	public function render_single_content( $atts ) {
		if ( ! empty( $atts['post_id'] ) ) {
			$post_id = absint( $atts['post_id'] );
		} else {
			global $post;
			$post_id = ! empty( $post->ID ) ? absint( $post->ID ) : 0;
		}

		$integration  = PostMeta::get_post_meta_value( $post_id, 'integration' );
		$emoji        = PostMeta::get_post_meta_value( $post_id, 'item_emoji' );
		$layout       = PostMeta::get_post_meta_value( $post_id, 'layout' );
		$layout_style = PostMeta::get_post_meta_value( $post_id, 'layout_style' );

		$defaults = [
			'integration'        => $integration,
			'emoji'              => $emoji,
			'post_id'            => $post_id,
			'use_passed_post_id' => false,
			'skip_comments'      => $integration === 'course' ? true : false,
			'skip_header'        => false,
			'layout'             => $layout,
			'layout_style'       => Helper::get_layout_style( $layout_style ),
		];

		$atts        = shortcode_atts( $defaults, $atts );
		$skip_header = boolval( $atts['skip_header'] );
		$emoji       = $atts['emoji'];
		$post_id     = absint( $atts['post_id'] );

		if ( ! $post_id ) {
			return null;
		}

		// If its singular post get the post title or consider as archive tax title.
		$post_title = is_singular() ? get_the_title( $post_id ) : single_term_title( '', false );

		ob_start();

		if ( ! $skip_header ) {
			echo do_shortcode( '[portal_content_header emoji="' . $emoji . '" title="' . $post_title . '"]' );
		}

		do_action( 'suredashboard_before_single_content_load', $post_id );

		$featured_image = PostMeta::get_post_meta_value( $post_id, 'image_url' );
		if ( ! empty( $featured_image ) ) {
			echo sprintf(
				'<div class="portal-item-featured-image-wrap"> %1$s </div>',
				wp_kses_post( Helper::get_space_banner_image( $post_id, false ) )
			);
		}

		if ( suredash_is_post_protected( $post_id ) ) {
			suredash_get_restricted_template_part(
				$post_id,
				'parts',
				'restricted',
				[
					'icon'        => 'Lock',
					'label'       => 'restricted_content',
					'description' => 'restricted_content_description',
				]
			);
		} else {
			$this->render_single_content_markup( $atts );
		}

		$content = apply_filters( 'suredashboard_single_view_content', ob_get_clean() );

		do_action( 'suredashboard_after_single_content_load', $post_id );

		return $content;
	}
}
