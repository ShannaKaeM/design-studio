<?php
/**
 * Plugin Loader.
 *
 * @package suremembers
 * @since 1.0.0
 */

namespace SureMembers;

use WP_REST_Response;
use WP_Post;
use SureMembers\Admin\Admin_Menu;
use SureMembers\Admin\Settings_Screen;
use SureMembers\Admin\Gutenberg_Admin_Bar;
use SureMembers\Admin\Restrictions;
use SureMembers\Admin\Rules_Engine;
use SureMembers\Admin\Menu_Restriction;
use SureMembers\Admin\User_Access;
use SureMembers\Admin\Login_Redirect;
use SureMembers\Inc\Admin_Bar;
use SureMembers\Inc\Template_Redirect;
use SureMembers\Inc\Content_Restriction;
use SureMembers\Inc\Block_Restriction;
use SureMembers\Inc\Menu_Items;
use SureMembers\Inc\Dashboard_Access;
use SureMembers\Inc\Activator;
use SureMembers\Inc\Login_Page;
use SureMembers\Inc\Downloads;
use SureMembers\Inc\Access_Groups;
use SureMembers\Inc\Settings;
use SureMembers\Inc\Restricted;
use SureMembers\Inc\Updates;
use SureMembers\Inc\Login_Restriction;
use SureMembers\Integrations\Surecart_Integration;
use SureMembers\Modules\Learndash\Learndash;
use SureMembers\Integrations\Buddyboss\Buddyboss;
use SureMembers\Modules\Tutorlms\Tutorlms;
use SureMembers\Integrations\Woocommerce;
use SureMembers\Modules\Elementor\Elementor_Loader;

// Compatibility Classes.
use SureMembers\Compatibility\Jetpack_Compatibility;

/**
 * Plugin_Loader
 *
 * @since 0.0.1
 */
class Plugin_Loader {

	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class Instance.
	 * @since 0.0.1
	 */
	private static $instance = null;

	/**
	 * Initiator
	 *
	 * @since 0.0.1
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Autoload classes.
	 *
	 * @param string $class class name.
	 * @return void
	 */
	public function autoload( $class ) {
		if ( 0 !== strpos( $class, __NAMESPACE__ ) ) {
			return;
		}

		$filename = preg_replace(
			[ '/^' . __NAMESPACE__ . '\\\/', '/([a-z])([A-Z])/', '/_/', '/\\\/' ],
			[ '', '$1-$2', '-', DIRECTORY_SEPARATOR ],
			$class
		);

		if ( is_string( $filename ) ) {

			$filename = strtolower( $filename );

			$file = SUREMEMBERS_DIR . $filename . '.php';

			// if the file is readable, include it.
			if ( is_readable( $file ) ) {
				require_once $file;
			}
		}

	}

	/**
	 * Constructor
	 *
	 * @since 0.0.1
	 */
	public function __construct() {
		// Remove this after the translation error is fixed.
		add_filter( 'doing_it_wrong_trigger_error', [ $this, 'suppress_translation_error' ], 10, 4 );

		if ( ! class_exists( 'SureCart\Licensing\Client' ) ) {
			require_once SUREMEMBERS_DIR . '/licensing-sdk/src/Client.php';
			add_action( 'init', [ $this, 'surecart_licensing' ], 1 );
		}
		spl_autoload_register( [ $this, 'autoload' ] );
		add_action( 'init', [ $this, 'load_classes' ] );
		add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
		add_filter( 'wp_untrash_post_status', [ $this, 'change_untrash_post_status' ], 10, 2 );
		add_filter( 'rest_prepare_page', [ $this, 'restrict_rest_api_access' ], 10, 2 );
		add_filter( 'rest_prepare_post', [ $this, 'restrict_rest_api_access' ], 10, 2 );
		Rules_Engine::get_instance();
		Login_Redirect::get_Instance();

		/**
		 * The code that runs during plugin activation
		 */
		register_activation_hook(
			SUREMEMBERS_FILE,
			function () {
				Activator::activate();
			}
		);
	}


	/**
	 * Restrict access to the REST API based on content restrictions
	 *
	 * This function modifies the REST API response for a post based on the user's access level.
	 * If the user has administrative privileges, they get full access to the post content.
	 * Otherwise, access is determined based on membership restrictions.
	 *
	 * @since 1.10.6
	 *
	 * @param WP_REST_Response $response The REST API response object.
	 * @param WP_Post          $post The WordPress post object.
	 * @return WP_REST_Response Modified REST API response.
	 */
	public function restrict_rest_api_access( WP_REST_Response $response, WP_Post $post ) {
		// Get the existing data.
		$data = $response->get_data();

		// Fix cannot access data on mixed type.
		if ( ! is_array( $data ) ) {
			return $response;
		}

		if ( current_user_can( 'manage_options' ) ) {
			$data['content']['rendered'] = apply_filters( 'the_content', $post->post_content );
			$response->set_data( $data );
			return $response;
		}

		$option = [
			'include'           => SUREMEMBERS_PLAN_INCLUDE,
			'exclusion'         => SUREMEMBERS_PLAN_EXCLUDE,
			'priority'          => SUREMEMBERS_PLAN_PRIORITY,
			'current_post_id'   => absint( $post->ID ),
			'current_post_type' => $post->post_type,
			'current_page_type' => 'is_singular',
		];

		// Get access groups that apply to the current post.
		$access_groups = Restricted::by_access_groups( SUREMEMBERS_POST_TYPE, $option );
		if ( empty( $access_groups ) || empty( $access_groups[ SUREMEMBERS_POST_TYPE ] ) ) {
			return $response;
		}

		// Check if the current user has access to this post based on access groups.
		$check_user_has_access = Access_Groups::check_if_user_has_access( array_keys( $access_groups[ SUREMEMBERS_POST_TYPE ] ) );

		// Ensure administrators always have access via the API.
		if ( ! $check_user_has_access ) {
			if ( apply_filters( 'suremembers_show_restricted_post_in_loop', true ) ) {
				$loop_content                = Settings::get_custom_content_data( 'loop_content' );
				$restricted_content          = ! empty( $loop_content['value'] ) ? sanitize_text_field( $loop_content['value'] ) : $loop_content['default'];
				$data['content']['rendered'] = $restricted_content;
				$data['excerpt']['rendered'] = '';
			} else {
				$data['content']['rendered'] = '';
				$data['excerpt']['rendered'] = '';
			}

			$response->set_data( $data );
			return $response;
		}

		// If the user has access, return the full post content and excerpt.
		$data['content']['rendered'] = apply_filters( 'the_content', $post->post_content );
		$data['excerpt']['rendered'] = apply_filters( 'the_excerpt', $post->post_excerpt );

		$response->set_data( $data );
		return $response;
	}

	/**
	 * Load Plugin Text Domain.
	 * This will load the translation textdomain depending on the file priorities.
	 *      1. Global Languages /wp-content/languages/suremembers/ folder
	 *      2. Local directory /wp-content/plugins/suremembers/languages/ folder
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function load_textdomain() {
		// Default languages directory.
		$lang_dir = SUREMEMBERS_DIR . 'languages/';

		/**
		 * Filters the languages directory path to use for plugin.
		 *
		 * @param string $lang_dir The languages directory path.
		 */
		$lang_dir = apply_filters( 'suremembers_languages_directory', $lang_dir );

		// Traditional WordPress plugin locale filter.
		global $wp_version;

		$get_locale = get_locale();

		if ( $wp_version >= 4.7 ) {
			$get_locale = get_user_locale();
		}

		/**
		 * Language Locale for plugin
		 *
		 * @var string $get_locale The locale to use.
		 * Uses get_user_locale()` in WordPress 4.7 or greater,
		 * otherwise uses `get_locale()`.
		 */
		$locale = apply_filters( 'plugin_locale', $get_locale, 'suremembers' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'suremembers', $locale );

		// Setup paths to current locale file.
		$mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;
		$mofile_local  = $lang_dir . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/suremembers/ folder.
			load_textdomain( 'suremembers', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/suremembers/languages/ folder.
			load_textdomain( 'suremembers', $mofile_local );
		} else {
			// Load the default language files.
			load_plugin_textdomain( 'suremembers', false, $lang_dir );
		}
	}

	/**
	 * Provides untrash posts publish status, only for SUREMEMBERS_POST_TYPE
	 *
	 * @param string $status current status to be applied for untrashed post 'draft' generally.
	 * @param int    $id post id to be untrashed.
	 * @return string
	 * @since 1.0.0
	 */
	public function change_untrash_post_status( $status, $id ) {
		$post_id = intval( $id );
		if ( ! empty( $post_id ) && SUREMEMBERS_POST_TYPE === get_post_type( $post_id ) ) {
			$status = 'publish';
		}
		return $status;
	}

	/**
	 * Loads plugin classes as per requirement.
	 *
	 * @return void
	 * @since  0.0.1
	 */
	public function load_classes() {

		if ( is_admin() ) {
			Admin_Menu::get_instance();
			Restrictions::get_instance();
			Menu_Restriction::get_instance();
			Gutenberg_Admin_Bar::get_instance();
			User_Access::get_instance();
			Settings_Screen::get_instance();
		} else {
			Template_Redirect::get_instance();
			Content_Restriction::get_instance();
			Block_Restriction::get_instance();
			Menu_Items::get_instance();
			Login_page::get_instance();
		}
		Login_Restriction::get_Instance();
		Admin_Bar::get_instance();
		Dashboard_Access::get_instance();
		if ( defined( 'SURECART_APP_URL' ) ) {
			( new Surecart_Integration() )->bootstrap();
		}
		if ( class_exists( 'SFWD_LMS' ) ) {
			Learndash::get_instance();
		}

		if ( is_plugin_active( 'buddyboss-platform/bp-loader.php' ) ) {
			Buddyboss::get_instance();
		}

		if ( function_exists( 'WC' ) ) {
			Woocommerce::get_instance();
		}

		if ( function_exists( 'tutor_lms' ) ) {
			Tutorlms::get_instance();
		}
		// JetPack Compatibility Class.
		if ( class_exists( 'Jetpack' ) ) {
			Jetpack_Compatibility::get_instance();
		}
		Elementor_Loader::get_instance();
		Downloads::get_instance();
		Updates::get_instance();
	}

	/**
	 * Adds SureCart licensing
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function surecart_licensing() {
		// initialize client with your plugin name.
		$client = new \SureCart\Licensing\Client( 'SureMembers', SUREMEMBERS_PUBLIC_KEY, SUREMEMBERS_FILE );

		// set your textdomain.
		$client->set_textdomain( 'suremembers' );
	}

	/**
	 * Suppress translation error.
	 *
	 * @param bool   $status       Status.
	 * @param string $function_name Function name.
	 * @param string $message      Message.
	 * @param string $version      Version.
	 *
	 * @return bool
	 */
	public function suppress_translation_error( $status, $function_name, $message, $version ) {
		if ( '_load_textdomain_just_in_time' === $function_name && false !== strpos( $message, 'suremembers' ) ) {
			return false;
		}
		return $status;
	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
Plugin_Loader::get_instance();
