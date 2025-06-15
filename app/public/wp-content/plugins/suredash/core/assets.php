<?php
/**
 * Frontend Assets.
 *
 * @package SureDash
 * @since 1.0.0
 */

namespace SureDashboard\Core;

use SureDashboard\Inc\Traits\Get_Instance;
use SureDashboard\Inc\Utils\Helper;
use SureDashboard\Inc\Utils\Labels;
use SureDashboard\Inc\Utils\PostMeta;

defined( 'ABSPATH' ) || exit;

/**
 * Frontend Assets Compatibility
 *
 * @package SureDash
 */

/**
 * Assets setup
 *
 * @since 1.0.0
 */
class Assets {
	use Get_Instance;

	/**
	 * Enqueue global assets.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_global_assets(): void {

		$localized_data = apply_filters(
			'portal_localized_frontend_data',
			[
				'ajax_url'                      => admin_url( 'admin-ajax.php' ),
				'password_mismatch_message'     => Labels::get_label( 'password_mismatch_message' ),
				'notification_dataset'          => [
					'success' => [
						'icon'    => Helper::get_library_icon( 'CircleCheck', false, 'md' ),
						'message' => Labels::get_label( 'notification_success_message' ),
					],
					'error'   => [
						'icon'    => Helper::get_library_icon( 'CircleX', false, 'md' ),
						'message' => Labels::get_label( 'notification_error_message' ),
					],
					'warning' => [
						'icon'    => Helper::get_library_icon( 'TriangleAlert', false, 'md' ),
						'message' => Labels::get_label( 'notification_warning_message' ),
					],
					'info'    => [
						'icon'    => Helper::get_library_icon( 'Info', false, 'md' ),
						'message' => Labels::get_label( 'notification_info_message' ),
					],
					'neutral' => [
						'icon'    => Helper::get_library_icon( 'Info', false, 'md' ),
						'message' => Labels::get_label( 'notification_neutral_message' ),
					],
				],
				'notification_messages'         => [
					'post_submitted'              => Labels::get_label( 'notify_message_post_submitted' ),
					'please_fill_required_fields' => Labels::get_label( 'notify_message_please_fill_required_fields' ),
					'item_bookmarked'             => Labels::get_label( 'notify_message_item_bookmarked' ),
					'item_un_bookmarked'          => Labels::get_label( 'notify_message_item_un_bookmarked' ),
					'comment_liked'               => Labels::get_label( 'notify_message_comment_liked' ),
					'comment_disliked'            => Labels::get_label( 'notify_message_comment_disliked' ),
					'post_liked'                  => Labels::get_label( 'notify_message_post_liked' ),
					'post_disliked'               => Labels::get_label( 'notify_message_post_disliked' ),
					'profile_updated'             => Labels::get_label( 'notify_message_profile_updated' ),
					'comment_posted'              => Labels::get_label( 'notify_message_comment_posted' ),
					'comment_invalid'             => Labels::get_label( 'notify_message_comment_invalid' ),
					'comment_duplicate'           => Labels::get_label( 'notify_message_comment_duplicate' ),
					'error_occurred'              => Labels::get_label( 'notify_message_error_occurred' ),
				],
				'jodit_messages'                => [
					'bold_tooltip'         => __( 'Bold', 'suredash' ),
					'italic_tooltip'       => __( 'Italic', 'suredash' ),
					'underline_tooltip'    => __( 'Underline', 'suredash' ),
					'image_tooltip'        => __( 'Attach an Image', 'suredash' ),
					'video_tooltip'        => __( 'Attach a Video', 'suredash' ),
					'image_url_text'       => __( 'Image URL', 'suredash' ),
					'image_alt_text'       => __( 'Image Alt Text', 'suredash' ),
					'attach_image_text'    => __( 'Attach Image', 'suredash' ),
					'attach_gif_text'      => __( 'Attach GIF', 'suredash' ),
					'choose_images_text'   => __( 'Drag and drop or browse files', 'suredash' ),
					'upload_text'          => __( 'Upload', 'suredash' ),
					'from_url_text'        => __( 'From URL', 'suredash' ),
					'attach_video_text'    => __( 'Attach Video', 'suredash' ),
					'video_url_text'       => __( 'Video URL', 'suredash' ),
					'videos_placeholder'   => __( 'Insert YouTube or Vimeo link', 'suredash' ),
					'search_user'          => Labels::get_label( 'jodit_search_user' ),
					'search_gif'           => Labels::get_label( 'jodit_search_gif' ),
					'mention_tooltip'      => Labels::get_label( 'jodit_mention_tooltip' ),
					'emoji_tooltip'        => Labels::get_label( 'jodit_emoji_tooltip' ),
					'gif_tooltip'          => Labels::get_label( 'jodit_gif_tooltip' ),
					'no_gif_found'         => Labels::get_label( 'jodit_no_gif_found' ),
					'no_user_found'        => Labels::get_label( 'jodit_no_user_found' ),
					'minimum_3_characters' => Labels::get_label( 'jodit_minimum_3_characters' ),
					'api_error'            => Labels::get_label( 'jodit_api_error' ),
				],
				'jodit_icons'                   => [
					'bold'        => Helper::get_library_icon( 'Bold', false, 'sm' ),
					'italic'      => Helper::get_library_icon( 'Italic', false, 'sm' ),
					'underline'   => Helper::get_library_icon( 'Underline', false, 'sm' ),
					'image'       => Helper::get_library_icon( 'Image', false, 'sm' ),
					'video'       => Helper::get_library_icon( 'Video', false, 'sm' ),
					'emoji'       => Helper::get_library_icon( 'Smile', false, 'sm' ),
					'mention'     => Helper::get_library_icon( 'ImagePlay', false, 'sm' ),
					'upload_file' => Helper::get_library_icon( 'CloudUpload', false, 'md' ),
					'upload'      => Helper::get_library_icon( 'Upload', false, 'sm' ),
					'link'        => Helper::get_library_icon( 'Link2', false, 'sm' ),
					'close'       => Helper::get_library_icon( 'X', false, 'sm' ),
				],
				'close_icon'                    => Helper::get_library_icon( 'X', false, 'sm' ),
				'loading_icon'                  => Helper::get_library_icon( 'LoaderCircle', false, 'md', 'loader-classes' ),
				'liked_text'                    => Labels::get_label( 'liked' ),
				'like_text'                     => Labels::get_label( 'like' ),
				'wp_rest_nonce'                 => is_user_logged_in() ? wp_create_nonce( 'wp_rest' ) : '',
				'social_login_nonce'            => wp_create_nonce( 'social_login_nonce' ),
				'comment_box_placeholder'       => Labels::get_label( 'comment_box_placeholder' ),
				'comment_reply_box_placeholder' => Labels::get_label( 'comment_reply_box_placeholder' ),
				'no_comments_message'           => __( 'No comments yet, be the first to comment!', 'suredash' ),
				'create_post_placeholder'       => Labels::get_label( 'create_post_placeholder' ),
				'giphy_api_key'                 => Helper::get_option( 'giphy_api_key' ),
				'user_logged_in'                => is_user_logged_in(),
			],
		);

		// Get upload directory information.
		$upload_dir     = wp_upload_dir();
		$font_file_url  = $upload_dir['baseurl'] . '/suredashboard/fonts/fonts.css';
		$font_file_path = $upload_dir['basedir'] . '/suredashboard/fonts/fonts.css';

		if ( file_exists( $font_file_path ) ) {
			wp_enqueue_style( 'portal-fonts', esc_url( $font_file_url ), [], SUREDASHBOARD_VER );
		}

		wp_enqueue_script( 'wp-api-fetch' );
		wp_enqueue_script( 'jodit-custom', esc_url( SUREDASHBOARD_JS_ASSETS_FOLDER . 'jodit-custom' . SUREDASHBOARD_JS_SUFFIX ), [], SUREDASHBOARD_VER, true );
		wp_enqueue_script( 'portal-common', esc_url( SUREDASHBOARD_JS_ASSETS_FOLDER . 'common' . SUREDASHBOARD_JS_SUFFIX ), [], SUREDASHBOARD_VER, true );
		wp_enqueue_script( 'portal-global', esc_url( SUREDASHBOARD_JS_ASSETS_FOLDER . 'global' . SUREDASHBOARD_JS_SUFFIX ), [ 'portal-common' ], SUREDASHBOARD_VER, true );
		wp_localize_script( 'portal-global', 'portal_global', $localized_data );
		wp_enqueue_script( 'portal-comments', esc_url( SUREDASHBOARD_JS_ASSETS_FOLDER . 'comments' . SUREDASHBOARD_JS_SUFFIX ), [ 'portal-common', 'jodit-custom', 'portal-global' ], SUREDASHBOARD_VER, true );

		wp_enqueue_style( 'portal-font', esc_url( SUREDASHBOARD_CSS_ASSETS_FOLDER . ( is_rtl() ? 'font-rtl' : 'font' ) . SUREDASHBOARD_CSS_SUFFIX ), [], SUREDASHBOARD_VER );
		wp_enqueue_style( 'portal-utility', esc_url( SUREDASHBOARD_CSS_ASSETS_FOLDER . ( is_rtl() ? 'utility-rtl' : 'utility' ) . SUREDASHBOARD_CSS_SUFFIX ), [], SUREDASHBOARD_VER );
		wp_enqueue_style( 'portal-global', esc_url( SUREDASHBOARD_CSS_ASSETS_FOLDER . ( is_rtl() ? 'global-rtl' : 'global' ) . SUREDASHBOARD_CSS_SUFFIX ), [ 'portal-font' ], SUREDASHBOARD_VER );
		wp_add_inline_style( 'portal-global', self::get_global_css() );

		wp_enqueue_style( 'portal-blocks', esc_url( SUREDASHBOARD_CSS_ASSETS_FOLDER . ( is_rtl() ? 'blocks-rtl' : 'blocks' ) . SUREDASHBOARD_CSS_SUFFIX ), [], SUREDASHBOARD_VER );

		wp_enqueue_script(
			'portal-jodit',
			esc_url( SUREDASHBOARD_JS_ASSETS_FOLDER . 'jodit' . SUREDASHBOARD_JS_SUFFIX ),
			[],
			SUREDASHBOARD_VER,
			true
		);

		wp_enqueue_style(
			'portal-jodit',
			esc_url( SUREDASHBOARD_CSS_ASSETS_FOLDER . ( is_rtl() ? 'jodit-rtl' : 'jodit' ) . SUREDASHBOARD_CSS_SUFFIX ),
			[],
			SUREDASHBOARD_VER
		);

		wp_enqueue_style(
			'portal-jodit-custom',
			esc_url( SUREDASHBOARD_CSS_ASSETS_FOLDER . ( is_rtl() ? 'jodit-custom-rtl' : 'jodit-custom' ) . SUREDASHBOARD_CSS_SUFFIX ),
			[ 'portal-jodit' ],
			SUREDASHBOARD_VER
		);
	}

	/**
	 * Enqueue : Search specific assets.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_search_assets(): void {
		$localize_data = [
			'portal_search_result' => wp_create_nonce( 'portal_search_result' ),
			'ajaxurl'              => admin_url( 'admin-ajax.php' ),
			'least_char'           => Labels::get_label( 'least_search_chars_require' ),
			'end_point_error'      => Labels::get_label( 'end_point_error' ),
			'post_type'            => SUREDASHBOARD_POST_TYPE,
			'recent_items'         => [
				'enabled'         => apply_filters( 'portal_search_recent_items_default_option', true ),
				'cookie_duration' => apply_filters( 'portal_search_recent_items_cookie_duration', 7 * 86400 ), // default to 7 days.
			],
		];

		wp_enqueue_style( 'portal-search', esc_url( SUREDASHBOARD_CSS_ASSETS_FOLDER . ( is_rtl() ? 'search-rtl' : 'search' ) . SUREDASHBOARD_CSS_SUFFIX ), [], SUREDASHBOARD_VER );

		wp_enqueue_script( 'underscore' );
		wp_enqueue_script( 'portal-search', esc_url( SUREDASHBOARD_JS_ASSETS_FOLDER . 'search' . SUREDASHBOARD_JS_SUFFIX ), [ 'jquery' ], SUREDASHBOARD_VER, true );
		wp_localize_script( 'portal-search', 'portal_search', $localize_data );
	}

	/**
	 * Enqueue single item assets.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_single_item_assets(): void {
		$localize_data = [
			'ajax_url'             => admin_url( 'admin-ajax.php' ),
			'track_redirect_nonce' => wp_create_nonce( 'portal_update_redirect_count' ),
			'is_portal_home_view'  => suredash_is_home(),
		];

		wp_enqueue_script(
			'portal-single',
			esc_url( SUREDASHBOARD_JS_ASSETS_FOLDER . 'single' . SUREDASHBOARD_JS_SUFFIX ),
			[ 'portal-common', 'portal-comments' ],
			SUREDASHBOARD_VER,
			true
		);

		wp_localize_script( 'portal-single', 'portal_item', $localize_data );

		wp_enqueue_style(
			'portal-single',
			esc_url( SUREDASHBOARD_CSS_ASSETS_FOLDER . ( is_rtl() ? 'single-rtl' : 'single' ) . SUREDASHBOARD_CSS_SUFFIX ),
			[],
			SUREDASHBOARD_VER
		);

		wp_add_inline_style( 'portal-single', self::get_css( 'single' ) );

		wp_enqueue_style(
			'portal-archive-group',
			esc_url( SUREDASHBOARD_CSS_ASSETS_FOLDER . ( is_rtl() ? 'archive-rtl' : 'archive' ) . SUREDASHBOARD_CSS_SUFFIX ),
			[],
			SUREDASHBOARD_VER
		);

		wp_add_inline_style( 'portal-archive-group', self::get_css( 'archive' ) );

		// Enqueue lightbox assets.
		if ( Helper::get_option( 'enable_lightbox', true ) ) {

			wp_enqueue_style(
				'portal-lightbox',
				esc_url( SUREDASHBOARD_CSS_ASSETS_FOLDER . ( is_rtl() ? 'lightbox-rtl' : 'lightbox' ) . SUREDASHBOARD_CSS_SUFFIX ),
				[],
				SUREDASHBOARD_VER
			);

			$localize_data = [
				'images_selector'  => [
					'single_selectors'  => Helper::get_lightbox_selector( 'single' ),
					'gallery_selectors' => Helper::get_lightbox_selector( 'gallery' ),
				],
				'lightbox_options' => [],
			];

			wp_enqueue_script( 'portal-lightbox', esc_url( SUREDASHBOARD_JS_ASSETS_FOLDER . 'lightbox' . SUREDASHBOARD_JS_SUFFIX ), [ 'portal-common', 'portal-comments' ], SUREDASHBOARD_VER, true );
			wp_localize_script( 'portal-lightbox', 'portal_lightbox', $localize_data );

			wp_add_inline_style( 'portal-lightbox', $this->get_lightbox_css() );
		}
	}

	/**
	 * Get lightbox specific dynamic assets.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_lightbox_css() {
		$css             = '';
		$single_selector = Helper::get_lightbox_selector( 'single' );
		if ( is_string( $single_selector ) && $single_selector ) {
			$css .= $single_selector . ' img { cursor: zoom-in;';
		}

		$gallery_selector = Helper::get_lightbox_selector( 'galley' );
		if ( is_string( $gallery_selector ) && $gallery_selector ) {
			$css .= $gallery_selector . ' img { cursor: zoom-in;';
		}

		return apply_filters( 'suredashboard_lightbox_dynamic_css', $css );
	}

	/**
	 * Enqueue archive item assets.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_archive_group_assets(): void {
		$localized_data = apply_filters(
			'portal_localized_frontend_data',
			[
				'ajax_url'                => admin_url( 'admin-ajax.php' ),
				'category'                => get_queried_object_id(),
				'page'                    => 1,
				'posts_loaded_message'    => '<div class="portal-no-more-posts portal-content sd-box-shadow sd-radius-8">' . Labels::get_label( 'no_more_posts_to_load' ) . '</div>',
				'insufficient_data_error' => Labels::get_label( 'insufficient_data_error' ),
				'infinite_scroll_loading' => false,
				'user_logged_in'          => is_user_logged_in(),
				'sub_queried_page'        => suredash_get_sub_queried_page(),
			]
		);

		wp_enqueue_script(
			'portal-archive-view',
			esc_url( SUREDASHBOARD_JS_ASSETS_FOLDER . 'archive' . SUREDASHBOARD_JS_SUFFIX ),
			[ 'portal-common', 'portal-comments' ],
			SUREDASHBOARD_VER,
			true
		);

		wp_localize_script( 'portal-archive-view', 'portal_archive', $localized_data );

		wp_enqueue_script( 'wp-a11y' );

		wp_enqueue_script(
			'portal-upload-media',
			esc_url( SUREDASHBOARD_JS_ASSETS_FOLDER . 'upload' . SUREDASHBOARD_JS_SUFFIX ),
			[],
			SUREDASHBOARD_VER,
			true
		);

		wp_localize_script( 'portal-upload-media', 'portal_upload', [] );

		wp_enqueue_style(
			'portal-archive-group',
			esc_url( SUREDASHBOARD_CSS_ASSETS_FOLDER . ( is_rtl() ? 'archive-rtl' : 'archive' ) . SUREDASHBOARD_CSS_SUFFIX ),
			[],
			SUREDASHBOARD_VER
		);
		wp_add_inline_style( 'portal-archive-group', self::get_css( 'archive' ) );
	}

	/**
	 * Get global dynamic Assets.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public static function get_global_css() {
		$primary_color = Helper::get_option( 'primary_color' );
		$primary_color = ! empty( $primary_color ) ? $primary_color : '#ffffff';

		$secondary_color       = Helper::get_option( 'secondary_color' );
		$content_bg_color      = Helper::get_option( 'content_bg_color' );
		$heading_color         = Helper::get_option( 'heading_color' );
		$text_color            = Helper::get_option( 'text_color' );
		$link_color            = Helper::get_option( 'link_color' );
		$link_active_color     = Helper::get_option( 'link_active_color' );
		$selection_color       = Helper::get_option( 'selection_color' );
		$content_width         = Helper::get_option( 'container_width', '100%' );
		$narrow_width          = Helper::get_option( 'narrow_container_width' );
		$normal_width          = Helper::get_option( 'normal_container_width' );
		$course_width          = Helper::get_option( 'course_container_width' );
		$border_color          = Helper::get_option( 'border_color' );
		$background_blur_color = Helper::get_option( 'background_blur_color' );
		$backdrop_blur         = Helper::get_option( 'backdrop_blur' );

		$primary_button_color              = Helper::get_option( 'primary_button_color' );
		$primary_button_background_color   = Helper::get_option( 'primary_button_background_color' );
		$secondary_button_color            = Helper::get_option( 'secondary_button_color' );
		$secondary_button_background_color = Helper::get_option( 'secondary_button_background_color' );

		$aside_navigation_width = Helper::get_option( 'aside_navigation_width' );
		$container_padding      = Helper::get_option( 'container_padding' );

		$font_family     = Helper::get_option( 'font_family' );
		$secondary_color = is_string( $secondary_color ) ? $secondary_color : '';

		$css = ':root {
			--portal-primary-color: ' . $primary_color . ';
			--portal-secondary-color: ' . $secondary_color . ';
			--portal-content-bg-color: ' . $content_bg_color . ';
			--portal-secondary-foreground-color: ' . suredash_get_foreground_color( $secondary_color ) . ';
			--portal-text-color: ' . $text_color . ';
			--portal-heading-color: ' . $heading_color . ';
			--portal-link-color: ' . $link_color . ';
			--portal-border-color: ' . $border_color . ';
			--portal-link-active-color: ' . $link_active_color . ';
			--portal-placeholder-color: #9CA3AF;
			--portal-background-blur-color: ' . $background_blur_color . ';
			--portal-backdrop-blur: ' . $backdrop_blur . 'px;

			--portal-primary-button-color: ' . $primary_button_color . ';
			--portal-primary-button-bg-color: ' . $primary_button_background_color . ';
			--portal-primary-button-hover-color: ' . $primary_button_color . ';
			--portal-primary-button-hover-bg-color: ' . $primary_button_background_color . ';

			--portal-secondary-button-color: ' . $secondary_button_color . ';
			--portal-secondary-button-bg-color: ' . $secondary_button_background_color . ';
			--portal-secondary-button-hover-color: ' . $secondary_button_color . ';
			--portal-secondary-button-hover-bg-color: ' . $secondary_button_background_color . ';

			--portal-danger-button-bg-color: #DC2626;

			--portal-navigation-width: ' . $aside_navigation_width . 'px;
			--portal-content-width: ' . $content_width . ';
			--portal-narrow-container-width: ' . $narrow_width . 'px;
			--portal-normal-container-width: ' . $normal_width . 'px;
			--portal-course-container-width: ' . $course_width . 'px;
			--portal-container-spacing: ' . $container_padding . 'px;

			--portal-success-notification-background: #DCFCE7;
			--portal-error-notification-background: #FEE2E2;
			--portal-warning-notification-background: #FEF9C3;
			--portal-info-notification-background: #E0F2FE;
			--portal-neutral-notification-background: #FFFFFF;

			--portal-font-family: ' . $font_family . ';
		}
		';

		// Text selection background color style.
		if ( is_string( $selection_color ) && $selection_color !== 'inherit' ) {
			$selection_text_color = suredash_get_foreground_color( $selection_color );
			$css                 .= '
				.portal-wrapper *::-moz-selection { /* Code for Firefox */
					color: ' . $selection_text_color . ';
					background: ' . $selection_color . ';
				}
				.portal-wrapper *::selection {
					color: ' . $selection_text_color . ';
					background: ' . $selection_color . ';
				}
			';
		}

		return apply_filters( 'suredashboard_global_dynamic_css', $css );
	}

	/**
	 * Get Assets.
	 *
	 * @param string $type Type of css.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public static function get_css( $type = 'single' ) {
		$css = '';
		switch ( $type ) {
			case 'single':
				$css .= self::get_single_item_css();
				break;

			case 'archive':
				$css .= self::get_archive_group_css();
				break;

			default:
				break;
		}

		$css = suredash_trim_css( $css );
		return apply_filters( 'suredashboard_single_item_css', $css );
	}

	/**
	 * Get single docs specific dynamic Assets.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public static function get_single_item_css() {
		$layout        = Helper::get_option( 'global_layout' );
		$content_width = '100%';
		$aside_margin  = '32px';

		if ( suredash_is_home() || ( is_front_page() && Helper::get_option( 'portal_as_homepage' ) ) ) {
			$content_width = '100%';
			$aside_margin  = '0 32px 32px';
		} else {
			if ( suredash_cpt() ) {
				if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
					$post = suredash_get_referer_post();
					if ( ! empty( $post ) && isset( $post['ID'] ) ) {
						$post_id             = $post['ID'];
						$referer_post_layout = sd_get_post_meta( $post_id, 'layout' );
						$layout_details      = Helper::get_layout_details( $referer_post_layout['meta_value'] ?? '' );

						$content_width = $layout_details['content_width'];
						$aside_margin  = $layout_details['aside_spacing'];
					}
				} else {
					$layout_details = Helper::get_layout_details();

					$content_width = $layout_details['content_width'];
					$aside_margin  = $layout_details['aside_spacing'];
				}
			} else {
				$item_id = get_the_ID();
				if ( ! $item_id ) {
					return '';
				}

				$layout         = PostMeta::get_post_meta_value( $item_id, 'layout' );
				$layout_details = Helper::get_layout_details( $layout );

				$content_width = $layout_details['content_width'];
				$aside_margin  = $layout_details['aside_spacing'];
			}
		}

		// Override for the simply_content Quick view.
		if ( suredash_simply_content() ) {
			$content_width = '100%';
			$aside_margin  = '20px';
		}

		$sub_query = suredash_get_sub_queried_page();

		if ( Helper::get_option( 'enable_feeds' ) && $sub_query === 'feeds' ) {
			$layout_details = Helper::get_layout_details();

			$content_width = $layout_details['content_width'];
			$aside_margin  = $layout_details['aside_spacing'];
		}

		$css = '
			:root {
				--portal-content-width: ' . $content_width . ';
				--portal-content-aside-margin: ' . $aside_margin . ';
			}
		';

		return apply_filters( 'suredashboard_single_item_dynamic_css', $css );
	}

	/**
	 * Get archive docs specific dynamic Assets.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public static function get_archive_group_css() {
		$css = '
			:root {
				--portal-content-aside-margin: 0 auto 32px;
			}
		';
		return apply_filters( 'suredashboard_archive_group_dynamic_css', $css );
	}
}
