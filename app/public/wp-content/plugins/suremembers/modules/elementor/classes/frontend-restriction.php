<?php
/**
 * Frontend Restriction.
 *
 * @package suremembers
 * @since 1.0.0
 */

namespace SureMembers\Modules\Elementor\Classes;

use SureMembers\Inc\Traits\Get_Instance;
use SureMembers\Inc\Access_Groups;

/**
 * Elementor widget, column and sections restriction.
 *
 * @since  1.0.0
 */
class Frontend_Restriction {

	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		// Check restrictions in widgets.
		add_filter( 'elementor/widget/render_content', [ $this, 'restrict_widget' ], 10, 2 );
		// Check restrictions in sections.
		add_filter( 'elementor/frontend/section/should_render', [ $this, 'restrict_section_and_column' ], 10, 2 );
		// Check restrictions in columns.
		add_filter( 'elementor/frontend/column/should_render', [ $this, 'restrict_section_and_column' ], 10, 2 );
		// Check restrictions in container.
		add_filter( 'elementor/frontend/container/should_render', [ $this, 'restrict_section_and_column' ], 10, 2 );
		// Content restriction.
		add_filter( 'elementor/frontend/the_content', [ $this, 'restrict_content_check' ] );

	}

	/**
	 * Filter post content.
	 *
	 * @param string $content html post content.
	 * @return string
	 */
	public function restrict_content_check( $content ) {
			global $post;
			$post_content = isset( $post->post_content ) ? $post->post_content : '';
			return isset( $post->suremembers_content_restricted ) ? $post_content : $content;
	}

	/**
	 * Restrict widget.
	 *
	 * @param string $widget_content Widget content.
	 * @param object $widget Widget settings.
	 * @return string
	 */
	public function restrict_widget( $widget_content, $widget ) {
		$settings = $widget->get_settings();
		if ( empty( $settings['sureMemberRestrictions'] ) || ! is_array( $settings['sureMemberRestrictions'] ) ) {
			return $widget_content;
		}

		$check_user_has_access = Access_Groups::check_if_user_has_access( $settings['sureMemberRestrictions'] );

		$user_condition = isset( $settings['sureMemberShowOnRestriction'] ) && ! empty( $settings['sureMemberShowOnRestriction'] ) ? esc_html( $settings['sureMemberShowOnRestriction'] ) : 'is_in';

		if ( 'is_in' === $user_condition && $check_user_has_access ) {
			return $widget_content;
		}

		if ( 'is_not_in' === $user_condition && ! $check_user_has_access ) {
			return $widget_content;
		}

		return '';
	}

	/**
	 * Restrict sections and columns.
	 *
	 * @param string $template Section or column template.
	 * @param object $settings Section or column settings.
	 * @return string
	 */
	public function restrict_section_and_column( $template, $settings ) {
		$settings = $settings->get_settings_for_display();
		if ( empty( $settings['sureMemberRestrictions'] ) || ! is_array( $settings['sureMemberRestrictions'] ) ) {
			return $template;
		}

		$check_user_has_access = Access_Groups::check_if_user_has_access( $settings['sureMemberRestrictions'] );

		$user_condition = isset( $settings['sureMemberShowOnRestriction'] ) && ! empty( $settings['sureMemberShowOnRestriction'] ) ? esc_html( $settings['sureMemberShowOnRestriction'] ) : 'is_in';

		if ( 'is_in' === $user_condition && $check_user_has_access ) {
			return $template;
		}

		if ( 'is_not_in' === $user_condition && ! $check_user_has_access ) {
			return $template;
		}

		return '';
	}
}
