<?php
/**
 * Plugin functions.
 *
 * @package SureDashboardPro
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SureDashboard\Inc\Utils\Helper;
use SureDashboard\Inc\Utils\PostMeta;

/**
 * Get SureDash plugin path.
 *
 * @since 1.0.0
 *
 * @access public
 */
function get_suredash_plugin_path() {
	return 'suredash/suredash.php';
}

/**
 * Is SureDash Free plugin installed.
 *
 * @since 1.0.0
 *
 * @access public
 */
function is_suredash_installed() {

	$path    = get_suredash_plugin_path();
	$plugins = get_plugins();

	return isset( $plugins[ $path ] );
}

/**
 * Check the SureDash community post restriction.
 *
 * @param int $post_id The post ID.
 * @param int $space_id The space base ID.
 * @return bool
 * @since 1.0.0-rc.3
 */
function suredash_pro_is_post_private( $post_id, $space_id = 0 ) {
	if ( ! is_user_logged_in() && class_exists( 'SureDashboard\Inc\Utils\Helper' ) && Helper::get_option( 'hidden_community' ) ) {
		return true;
	}

	if ( ! defined( 'SUREDASHBOARD_FEED_POST_TYPE' ) ) {
		return false;
	}

	if ( ! is_callable( 'sd_get_post_field' ) ) {
		return false;
	}

	$post_author = absint( sd_get_post_field( $post_id, 'post_author' ) );
	$post_type   = strval( sd_get_post_field( $post_id, 'post_type' ) );

	if ( function_exists( 'suredash_is_user_manager' ) ) {
		// If post author is manager, then consider the post is for everyone.
		if ( suredash_is_user_manager( $post_author ) ) {
			return false;
		}

		// If current user is manager, then consider the post is for admin's view.
		if ( suredash_is_user_manager() ) {
			return false;
		}
	}

	if ( $space_id ) {
		$is_private_forum = (bool) PostMeta::get_post_meta_value( $space_id, 'private_forum' ); // @phpstan-ignore-line
		if ( $is_private_forum ) {
			$current_user_id  = get_current_user_id();
			$is_personal_post = $post_type === SUREDASHBOARD_FEED_POST_TYPE && $current_user_id === absint( $post_author ) ? false : true;
			return apply_filters( 'suredash_post_protection', $is_personal_post, $post_id );
		}
	}

	return apply_filters( 'suredash_post_protection', false, $post_id );
}

/**
 * Get the course space ID by lesson ID.
 *
 * @param int $post_id Post ID.
 * @return array<int>
 * @since 1.0.0-rc.3
 */
function sd_pro_get_course_space_by_lesson( $post_id ) {
	$course_id = sd_get_post_meta( $post_id, 'belong_to_course', true );

	if ( ! $course_id ) {
		return [];
	}

	return [ $course_id ];
}

/**
 * Check if the topic is private.
 *
 * @param int $post_id The topic ID.
 * @return array<string, mixed>
 * @since 0.0.1-alpha.3
 */
function suredash_pro_is_post_protected( $post_id ) {
	$protection_details = [
		'status'  => false,
		'content' => '',
	];

	if ( is_callable( 'suredash_restriction_defaults' ) ) {
		$protection_details = suredash_restriction_defaults();
	}

	if ( ! $post_id ) {
		return $protection_details;
	}

	if ( ! defined( 'SUREDASHBOARD_FEED_POST_TYPE' ) || ! defined( 'SUREDASHBOARD_SUB_CONTENT_POST_TYPE' ) ) {
		return $protection_details;
	}

	if ( ! is_callable( 'sd_get_post_field' ) ) {
		return $protection_details;
	}

	$post_type = sd_get_post_field( $post_id, 'post_type' );
	if ( ! empty( $post_type ) && is_callable( 'sd_get_space_id_by_post' ) ) {
		if ( $post_type === SUREDASHBOARD_FEED_POST_TYPE ) {
			$space_id                     = sd_get_space_id_by_post( $post_id );
			$protection_details['status'] = suredash_pro_is_post_private( $post_id, $space_id );
		}
		if ( $post_type === SUREDASHBOARD_SUB_CONTENT_POST_TYPE ) {
			$space_id                     = sd_get_space_id_by_post( $post_id, 'sd_pro_get_course_space_by_lesson' );
			$protection_details['status'] = suredash_pro_is_post_private( $post_id, $space_id );
		}
	}

	return apply_filters( 'suredash_pro_post_protection', $protection_details, $post_id );
}
