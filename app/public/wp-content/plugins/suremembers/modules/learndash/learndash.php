<?php
/**
 * LearnDash Integration.
 *
 * @package suremembers
 * @since 1.1.0
 * @since 1.5.0 Added Base_Module abstract class.
 */

namespace SureMembers\Modules\Learndash;

use SureMembers\Inc\Traits\Get_Instance;
use SureMembers\Modules\Base_Module;
use SureMembers\Inc\Utils;

/**
 * LearnDash Integration
 *
 * @since 1.1.0
 * @since 1.5.0 Added Base_Module abstract class.
 */
class Learndash extends Base_Module {

	use Get_Instance;

	/**
	 * Stores restricted data
	 *
	 * @var array Restriction data.
	 * @since 1.5.0
	 */
	public $restriction_data = [];

	/**
	 * Actions Constructor.
	 */
	public function add_actions() {
		add_filter( 'suremembers_filter_redirection_post_id', [ $this, 'filter_redirection_post_id' ] );
		add_filter( 'suremembers_get_content_meta_values', [ $this, 'filter_content_meta_values' ], 10, 2 );
		add_filter( 'suremembers_before_search_rules', [ $this, 'format_posttype_rules' ] );
	}

	/**
	 * Filter post id for LearnDash associated content.
	 *
	 * @param int $post_id The post id to be filtered.
	 * @return int|string $post_id or Associated post id.
	 * @since 1.5.0
	 */
	public function filter_redirection_post_id( $post_id ) {
		$restricting_rules = $this->get_restricting_rules( $post_id );

		if ( empty( $restricting_rules ) ) {
			$learndash_course_associates = $this->get_associated_post_types();
			if ( in_array( get_post_type( $post_id ), $learndash_course_associates, true ) ) {
				$post_id = learndash_get_course_id( $post_id );
			}
		}

		return $post_id;
	}

	/**
	 * Filter Content meta values for LearnDash.
	 *
	 * @param array  $meta_args array of arguments meta to filter.
	 * @param object $q_obj The post object.
	 * @return array Modified post meta values.
	 * @since 1.5.0
	 */
	public function filter_content_meta_values( $meta_args, $q_obj ) {
		if ( ! isset( $q_obj->post_type ) || ! isset( $q_obj->ID ) ) {
			return $meta_args;
		}

		$learndash_course_associates = $this->get_associated_post_types();
		if ( in_array( $q_obj->post_type, $learndash_course_associates, true ) ) {
			$associated_course = learndash_get_course_id( $q_obj->ID );
			if ( 'sfwd-topic' === $q_obj->post_type ) {
				$associated_lesson = learndash_get_lesson_id( $q_obj->ID );
				$meta_args[]       = "post-{$associated_lesson}-|";
			}
			$meta_args[] = "post-{$associated_course}-|";
		}
		return $meta_args;
	}

	/**
	 * Get Associated post types for LearnDash course.
	 *
	 * @return array Array of post types.
	 * @since 1.5.0
	 */
	public function get_associated_post_types() {
		return [ 'sfwd-lessons', 'sfwd-topic' ];
	}

	/**
	 * Exclude LearnDash supported post types.
	 *
	 * @param array  $post_types Excluded post types.
	 * @param string $context get post type context.
	 * @return array Post Types Array to exclude.
	 * @since 1.5.0
	 */
	public function exclude_post_types( $post_types, $context ) {
		if ( 'search' === $context ) {
			return $post_types;
		}
		if ( ! is_array( $post_types ) ) {
			return $post_types;
		}
		$post_types = array_merge( $post_types, [ 'sfwd-courses', 'groups', 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz', 'sfwd-question', 'sfwd-certificates', 'ld-exam', 'sfwd-assignment' ] );
		return $post_types;
	}

	/**
	 * Add LearnDash Rule Groups.
	 *
	 * @param array $locations Current rules locations.
	 * @return array Modified locations array.
	 * @since 1.5.0
	 */
	public function add_rule_groups( $locations ) {
		if ( ! is_array( $locations ) ) {
			return $locations;
		}

		$locations['learndash'] = [
			'label' => __( 'LearnDash', 'suremembers' ),
			'value' => [
				'sfwd-courses' => __( 'LearnDash Courses', 'suremembers' ),
				'groups|all'   => __( 'LearnDash Groups', 'suremembers' ),
			],
		];

		return apply_filters( 'suremembers_rules_engine_learndash_locations', $locations );
	}

	/**
	 * Add Access Group Data.
	 *
	 * @param array $localizations Access group localization data.
	 * @return array Localization Array.
	 * @since 1.5.0
	 */
	public function add_access_group_data( $localizations ) {
		// Ignored as the data is for localization.
		if ( empty( $_GET['post_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $localizations;
		}

		// Ignored as the data is for localization.
		$id = absint( $_GET['post_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( empty( $id ) ) {
			return $localizations;
		}

		$post = get_post( $id );

		if ( empty( $post ) || SUREMEMBERS_POST_TYPE !== $post->post_type ) {
			return $localizations;
		}

		$includes     = get_post_meta( $id, SUREMEMBERS_PLAN_INCLUDE, true );
		$courses_data = is_array( $includes ) && ! empty( $includes['learndash_courses'] ) ? $includes['learndash_courses'] : [];
		$groups_data  = is_array( $includes ) && ! empty( $includes['learndash_groups'] ) ? $includes['learndash_groups'] : [];

		$localizations['learndash_courses'] = Utils::convert_slug_to_text( $courses_data );
		$localizations['learndash_groups']  = Utils::convert_slug_to_text( $groups_data );

		return $localizations;
	}

	/**
	 * Save Settings data.
	 *
	 * @param array $include Include data.
	 * @param array $post_data Post Data.
	 * @return array Includes Array.
	 * @since 1.5.0
	 */
	public function save_access_group_data( $include, $post_data ) {
		if ( ! empty( $post_data['learndash_courses'] ) ) {
			$include['learndash_courses'] = $post_data['learndash_courses'];
			unset( $post_data['learndash_courses'] );
		}

		if ( ! empty( $post_data['learndash_groups'] ) ) {
			$include['learndash_groups'] = $post_data['learndash_groups'];
			unset( $post_data['learndash_groups'] );
		}

		return $include;
	}

	/**
	 * Filter Post Type rules string to match proper format.
	 *
	 * @param string $rules Rules string.
	 * @return string Formatted Rules string.
	 * @since 1.4.0
	 * @since 1.5.0 Function inside extended Base_Module.
	 */
	public function format_posttype_rules( $rules ) {
		$replacements = [
			'learndash-courses' => 'sfwd-courses',
			'learndash-groups'  => 'groups|all',
		];

		$rules = \str_replace( array_keys( $replacements ), $replacements, $rules );
		return $rules;
	}
}
