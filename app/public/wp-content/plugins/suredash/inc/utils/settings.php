<?php
/**
 * Settings.
 *
 * @package SureDash
 * @since 0.0.1
 */

namespace SureDashboard\Inc\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * This class will holds the code related to the managing of settings of the plugin.
 *
 * @class Settings
 */
class Settings {
	/**
	 * Cache the DB options
	 *
	 * @since 0.0.1
	 * @access public
	 * @var array<string, mixed>
	 */
	public static $dashboard_options = [];

	/**
	 * Returns all default portal settings.
	 *
	 * @return array<string, array<string, mixed>>
	 * @since 0.0.1
	 */
	public static function get_settings_dataset() {
		$high_user_id   = '';
		$high_user_name = '';
		if ( current_user_can( SUREDASHBOARD_CAPABILITY ) ) {
			$high_user_id   = get_current_user_id();
			$high_user_name = get_the_author_meta( 'display_name', $high_user_id );
		}

		return apply_filters(
			'suredashboard_settings_dataset',
			[
				// Branding settings.
				'portal_name'                       => [
					'default' => get_bloginfo( 'name' ),
					'type'    => 'string',
				],
				'logo_url'                          => [
					'default' => '',
					'type'    => 'string',
				],
				'hide_branding'                     => [
					'default' => false,
					'type'    => 'boolean',
				],

				// Performance settings.
				'feeds_per_page'                    => [
					'default' => 5,
					'type'    => 'number',
				],
				'user_upload_limit'                 => [
					'default' => 2,
					'type'    => 'number',
				],
				'bypass_wp_interactions'            => [
					'default' => true,
					'type'    => 'boolean',
				],

				// Community settings.
				'hidden_community'                  => [
					'default' => true,
					'type'    => 'boolean',
				],
				'enable_lightbox'                   => [
					'default' => true,
					'type'    => 'boolean',
				],

				// Social settings.
				'google_token_id'                   => [
					'default' => '',
					'type'    => 'string',
				],
				'google_token_secret'               => [
					'default' => '',
					'type'    => 'string',
				],
				'facebook_token_id'                 => [
					'default' => '',
					'type'    => 'string',
				],
				'facebook_token_secret'             => [
					'default' => '',
					'type'    => 'string',
				],
				'recaptcha_site_key_v2'             => [
					'default' => '',
					'type'    => 'string',
				],
				'recaptcha_site_key_v3'             => [
					'default' => '',
					'type'    => 'string',
				],
				'giphy_api_key'                     => [
					'default' => '',
					'type'    => 'string',
				],

				// Layout settings.
				'global_layout'                     => [
					'default' => 'normal',
					'type'    => 'string',
				],
				'global_layout_style'               => [
					'default' => 'boxed',
					'type'    => 'string',
				],
				'narrow_container_width'            => [
					'default' => 600,
					'type'    => 'number',
				],
				'normal_container_width'            => [
					'default' => 800,
					'type'    => 'number',
				],
				'container_padding'                 => [
					'default' => 20,
					'type'    => 'number',
				],
				'aside_navigation_width'            => [
					'default' => 300,
					'type'    => 'number',
				],

				// Miscellaneous settings.
				'portal_as_homepage'                => [
					'default' => false,
					'type'    => 'boolean',
				],
				'home_page'                         => [
					'default' => [
						'label' => __( '– Select –', 'suredash' ),
						'value' => '__placeholder__',
					],
					'type'    => 'array',
				],
				'login_page'                        => [
					'default' => [],
					'type'    => 'array',
				],
				'register_page'                     => [
					'default' => [],
					'type'    => 'array',
				],

				// Color settings.
				'primary_color'                     => [
					'default' => '#ffffff',
					'type'    => 'string',
				],
				'secondary_color'                   => [
					'default' => '#F9FAFB',
					'type'    => 'string',
				],
				'content_bg_color'                  => [
					'default' => '#ffffff',
					'type'    => 'string',
				],
				'heading_color'                     => [
					'default' => '#111827',
					'type'    => 'string',
				],
				'text_color'                        => [
					'default' => '#4B5563',
					'type'    => 'string',
				],
				'link_color'                        => [
					'default' => '#4B5563',
					'type'    => 'string',
				],
				'link_active_color'                 => [
					'default' => '#4338CA',
					'type'    => 'string',
				],
				'border_color'                      => [
					'default' => '#E2E8F0',
					'type'    => 'string',
				],
				'selection_color'                   => [
					'default' => 'inherit',
					'type'    => 'string',
				],
				'primary_button_color'              => [
					'default' => '#ffffff',
					'type'    => 'string',
				],
				'primary_button_background_color'   => [
					'default' => '#4338CA',
					'type'    => 'string',
				],
				'secondary_button_color'            => [
					'default' => '#020617',
					'type'    => 'string',
				],
				'secondary_button_background_color' => [
					'default' => '#ffffff',
					'type'    => 'string',
				],
				'background_blur_color'             => [
					'default' => '#11182780',
					'type'    => 'string',
				],
				'backdrop_blur'                     => [
					'default' => 8,
					'type'    => 'number',
				],

				// Typography settings.
				'font_family'                       => [
					'default' => 'Figtree',
					'type'    => 'string',
				],

				// Label texts settings.
				'home_text'                         => [
					'default' => __( 'Home', 'suredash' ),
					'type'    => 'string',
				],
				'welcome_text'                      => [
					'default' => __( 'Howdy', 'suredash' ),
					'type'    => 'string',
				],
				'your_bookmarks_text'               => [
					'default' => __( 'Your Bookmarks', 'suredash' ),
					'type'    => 'string',
				],
				'profile_information_text'          => [
					'default' => __( 'Profile Information', 'suredash' ),
					'type'    => 'string',
				],
				'pinned_post_text'                  => [
					'default' => __( 'Pinned Post', 'suredash' ),
					'type'    => 'string',
				],
				'no_posts_found'                    => [
					'default' => __( 'No posts found yet. Stay tuned — new content will be shared here soon!', 'suredash' ),
					'type'    => 'string',
				],
				'login_or_join'                     => [
					'default' => __( 'To add to the discussion, join the mastermind.', 'suredash' ),
					'type'    => 'string',
				],
				'back_to_course_text'               => [
					'default' => __( 'Back to Course', 'suredash' ),
					'type'    => 'string',
				],
				'mark_as_complete_text'             => [
					'default' => __( 'Complete and Continue', 'suredash' ),
					'type'    => 'string',
				],
				'start_writing_post_text'           => [
					'default' => __( 'What would you like to share today?', 'suredash' ),
					'type'    => 'string',
				],
				'write_a_post_text'                 => [
					'default' => __( 'Create New Post', 'suredash' ),
					'type'    => 'string',
				],
				'no_discussion_found'               => [
					'default' => __( 'No post found, create a new one!', 'suredash' ),
					'type'    => 'string',
				],
				'restricted_content_heading_text'   => [
					'default' => __( 'Restricted Content', 'suredash' ),
					'type'    => 'string',
				],
				'restricted_content_notice_text'    => [
					'default' => __( 'This content is not available with your current membership.', 'suredash' ),
					'type'    => 'string',
				],

				// Roles & Access settings.
				'profile_links'                     => [
					'default' => [
						[
							'title'     => __( 'Profile', 'suredash' ),
							'icon'      => 'User',
							'slug'      => 'user-profile',
							'link'      => '/{portal_slug}/user-profile/',
							'fixed_url' => true,
						],
						[
							'title'     => __( 'Bookmarks', 'suredash' ),
							'icon'      => 'Bookmark',
							'slug'      => 'bookmarks',
							'link'      => '/{portal_slug}/bookmarks/',
							'fixed_url' => true,
						],
					],
					'type'    => 'array',
				],
				'profile_logout_link'               => [
					'default' => [
						'title'     => __( 'Log out', 'suredash' ),
						'icon'      => 'LogOut',
						'slug'      => 'logout',
						'link'      => '{portal_logout_url}',
						'fixed_url' => true,
					],
					'type'    => 'array',
				],
				'user_capability'                   => [
					'default' => [
						[
							'id'   => 'suredash_user',
							'name' => __( 'SureDash User', 'suredash' ),
						],
					],
					'type'    => 'array',
				],
				'portal_manager'                    => [
					'default' => [
						[
							'id'   => $high_user_id,
							'name' => $high_user_name,
						],
					],
					'type'    => 'array',
				],

				// Email settings.
				'email_from_mail_id'                => [
					'default' => get_bloginfo( 'admin_email' ),
					'type'    => 'email',
				],
				'forgot_password_mail_body'         => [
					'default' => suredash_forgot_password_mail_body(),
					'type'    => 'html',
				],

				'usage_tracking'                    => [
					'default' => get_option( 'suredash_analytics_optin', 'no' ),
					'type'    => 'string',
				],
			]
		);
	}

	/**
	 * Returns an option from the default options.
	 *
	 * @param  string $key     The option key.
	 * @param  mixed  $default Option default value if option is not available.
	 * @return mixed   Returns the option value
	 *
	 * @since 0.0.1
	 */
	public static function get_default_option( $key, $default = false ) {
		$default_settings = self::get_default_settings();

		if ( ! is_array( $default_settings ) || ! array_key_exists( $key, $default_settings ) || empty( $default_settings ) ) {
			return $default;
		}

		return $default_settings[ $key ];
	}

	/**
	 * As per the settings dataset, return the default settings.
	 *
	 * @return array<string, mixed>
	 * @since 0.0.1
	 */
	public static function get_default_settings() {
		$settings_dataset = self::get_settings_dataset();

		$default_settings = [];

		foreach ( $settings_dataset as $key => $value ) {
			$default_settings[ $key ] = $value['default'];
		}

		return $default_settings;
	}

	/**
	 * Returns all portal settings.
	 *
	 * @param bool $use_cache Whether to use cached settings.
	 *
	 * @return array<string, mixed>
	 * @since 0.0.1
	 */
	public static function get_suredash_settings( $use_cache = true ) {
		if ( $use_cache && ! empty( self::$dashboard_options ) ) {
			return self::$dashboard_options;
		}

		$db_option = self::get_settings();
		$db_option = self::sync_bsf_analytics_setting( $db_option );

		$defaults = apply_filters( 'suredashboard_dashboard_rest_options', self::get_default_settings() );

		self::$dashboard_options = wp_parse_args( $db_option, $defaults );
		return self::$dashboard_options;
	}

	/**
	 * Sync BSF Analytics Setting.
	 *
	 * @param array<string, mixed> $options options.
	 * @since 0.0.5
	 * @return array<string, mixed>
	 */
	public static function sync_bsf_analytics_setting( $options ) {

		$usage_tracking            = get_option( 'suredash_analytics_optin', 'no' );
		$options['usage_tracking'] = $usage_tracking === 'yes' ? '1' : '';

		return $options;
	}

	/**
	 * Returns all portal settings.
	 * Note: Fallback function as get_portal_settings() is deprecated.
	 *
	 * @return array<string, mixed>
	 * @since 0.0.1
	 */
	public static function get_portal_settings() {
		return self::get_suredash_settings();
	}

	/**
	 * Update portal all settings.
	 *
	 * @param array<string, mixed> $settings The settings to update.
	 * @return void
	 * @since 0.0.1
	 */
	public static function update_suredash_settings( $settings ): void {

		$settings = self::encrypt_keys( $settings );
		update_option( SUREDASHBOARD_SETTINGS, $settings );

		// Run Font Manager if the font has changed.
		do_action( 'suredash_process_fonts', $settings['font_family'] ?? '' ); // Pass the new font family.

		// Flush the rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Encrypt the keys of the settings array.
	 *
	 * @param array<string, mixed> $settings The settings to encrypt.
	 * @return array<string, mixed>
	 * @since 0.0.1
	 */
	public static function encrypt_keys( $settings ) {

		$keys_to_encrypt = [
			'google_token_id',
			'google_token_secret',
			'facebook_token_id',
			'facebook_token_secret',
			'recaptcha_site_key_v2',
			'recaptcha_site_key_v3',
			'giphy_api_key',
		];

		foreach ( $keys_to_encrypt as $key ) {
			if ( array_key_exists( $key, $settings ) ) {
				$settings[ $key ] = base64_encode( $settings[ $key ] );
			}
		}

		return $settings;
	}

	/**
	 * Decrypt the keys of the settings array.
	 *
	 * @param array<string, mixed> $settings The settings to decrypt.
	 * @return array<string, mixed>
	 * @since 0.0.1
	 */
	public static function decrypt_keys( $settings ) {
		$keys_to_decrypt = [
			'google_token_id',
			'google_token_secret',
			'facebook_token_id',
			'facebook_token_secret',
			'recaptcha_site_key_v2',
			'recaptcha_site_key_v3',
			'giphy_api_key',
		];

		foreach ( $keys_to_decrypt as $key ) {
			if ( array_key_exists( $key, $settings ) && ! empty( $settings[ $key ] ) ) {
				$decoded = base64_decode( $settings[ $key ], true );
				// Decode only if valid base64.
				if ( $decoded !== false ) {
					$settings[ $key ] = $decoded;
				}
			}
		}

		return $settings;
	}

	/**
	 * Decrypt the keys of the settings array.
	 *
	 * @return array<string, mixed>
	 * @since 0.0.1
	 */
	public static function get_settings() {
		// Adjust this option key to match your plugin's saved settings.
		$settings = get_option( SUREDASHBOARD_SETTINGS, [] );

		// Decrypt sensitive keys.
		return self::decrypt_keys( $settings );
	}

	/**
	 * Get the type of the setting.
	 *
	 * @param string $key The setting key.
	 * @return string
	 * @since 0.0.1
	 */
	public static function get_setting_type( $key ) {
		$settings_dataset = self::get_settings_dataset();

		if ( ! is_array( $settings_dataset ) || ! array_key_exists( $key, $settings_dataset ) ) {
			return 'string';
		}

		return $settings_dataset[ $key ]['type'];
	}
}
