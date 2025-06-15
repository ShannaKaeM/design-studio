<?php
/**
 * Plugin Loader.
 *
 * @package SureDash
 * @since 1.0.0
 */

namespace SureDashboard;

use SureDashboard\Admin\API;
use SureDashboard\Admin\Editor;
use SureDashboard\Admin\Menu;
use SureDashboard\Admin\Notices;
use SureDashboard\Admin\Setup;
use SureDashboard\Core\Ajax\Backend as AjaxBackend;
use SureDashboard\Core\Blocks\Do_Blocks as InitBlocks;
use SureDashboard\Core\Blocks\Dynamic;
use SureDashboard\Core\Codes as CodesRegistry;
use SureDashboard\Core\CPTs;
use SureDashboard\Core\FontManager;
use SureDashboard\Core\Integrator as IntegrationsRegistry;
use SureDashboard\Core\Notifier\Base as Notifier_Base;
use SureDashboard\Core\Renderer;
use SureDashboard\Core\RewriteRules;
use SureDashboard\Core\Routes;
use SureDashboard\Inc\Compatibility\Comment as Comment_Management;
use SureDashboard\Inc\Compatibility\Plugin;
use SureDashboard\Inc\Compatibility\Starter_Content;
use SureDashboard\Inc\Compatibility\Theme;
use SureDashboard\Inc\Templator\Service as Templates;
use SureDashboard\Inc\Utils\Maintenance;

/**
 * Portals_Loader
 *
 * @since 1.0.0
 */
class Portals_Loader {
	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class Instance.
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Define the constants.
		$this->define_constants();

		// Initialize the plugin bases.
		$this->initialize_base();

		spl_autoload_register( [ $this, 'autoload' ] );

		add_action( 'admin_init', [ $this, 'activation_redirect' ] );

		// Activation hook.
		register_activation_hook( SUREDASHBOARD_FILE, [ $this, 'activation_actions' ] );

		// Deactivation hook.
		register_deactivation_hook( SUREDASHBOARD_FILE, [ $this, 'deactivation_actions' ] );

		add_action( 'init', [ $this, 'load_textdomain' ] );
		add_action( 'plugins_loaded', [ $this, 'load_plugin' ], 99 );

		add_action( 'after_setup_theme', [ $this, 'do_theme_setup' ], 99999 );
		add_filter( 'wp_kses_allowed_html', [ $this, 'allow_iframe_for_custom_role' ], 10, 2 );

		// Remove this after the translation error is fixed.
		add_filter( 'doing_it_wrong_trigger_error', [ $this, 'suppress_translation_error' ], 10, 4 );
	}

	/**
	 * Allow iframe for custom role.
	 *
	 * @param array  $allowed_tags Allowed tags.
	 * @param string $context      Context.
	 *
	 * @return array
	 */
	public function allow_iframe_for_custom_role( $allowed_tags, $context ) {

		if ( $context !== 'post' ) {
			return $allowed_tags;
		}

		$user = wp_get_current_user();
		if ( in_array( 'suredash_user', (array) $user->roles, true ) ) {
			$allowed_tags['iframe'] = [
				'src'             => true,
				'height'          => true,
				'width'           => true,
				'frameborder'     => true,
				'allowfullscreen' => true,
				'allow'           => true,
				'loading'         => true,
			];
			$allowed_tags           = apply_filters( 'suredash_allowed_html', $allowed_tags );
		}

		return $allowed_tags;
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
		if ( $function_name === '_load_textdomain_just_in_time' && strpos( $message, 'suredash' ) !== false ) {
			return false;
		}
		return $status;
	}

	/**
	 * Load Plugin Text Domain.
	 * This will load the translation textdomain depending on the file priorities.
	 *      1. Global Languages /wp-content/languages/suredash/ folder
	 *      2. Local directory /wp-content/plugins/suredash/languages/ folder
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function load_textdomain(): void {
		// Default languages directory.
		$lang_dir = SUREDASHBOARD_DIR . 'languages/';

		/**
		 * Filters the languages directory path to use for plugin.
		 *
		 * @param string $lang_dir The languages directory path.
		 */
		$lang_dir = apply_filters( 'suredash_languages_directory', $lang_dir );

		// Traditional WordPress plugin locale filter.
		global $wp_version;

		$get_locale = get_locale();

		if ( $wp_version >= 4.7 ) {
			$get_locale = get_user_locale();
		}

		/**
		 * Language Locale for plugin
		 *
		 * Uses get_user_locale()` in WordPress 4.7 or greater,
		 * otherwise uses `get_locale()`.
		 */
		$locale = apply_filters( 'plugin_locale', $get_locale, 'suredash' );//phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- wordpress hook
		$mofile = sprintf( '%1$s-%2$s.mo', 'suredash', $locale );

		// Setup paths to current locale file.
		$mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;
		$mofile_local  = $lang_dir . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/suredash/ folder.
			load_textdomain( 'suredash', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/suredash/languages/ folder.
			load_textdomain( 'suredash', $mofile_local );
		} else {
			// Load the default language files.
			load_plugin_textdomain( 'suredash', false, $lang_dir );
		}
	}

	/**
	 * Initiator
	 *
	 * @since 1.0.0
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Autoload classes.
	 *
	 * @param string $class class name.
	 */
	public function autoload( $class ): void {
		if ( strpos( $class, __NAMESPACE__ ) !== 0 ) {
			return;
		}

		$class_to_load = $class;

		$filename = strtolower(
			preg_replace(
				[ '/^' . __NAMESPACE__ . '\\\/', '/([a-z])([A-Z])/', '/_/', '/\\\/' ],
				[ '', '$1-$2', '-', DIRECTORY_SEPARATOR ],
				$class_to_load
			)
		);

		$file = SUREDASHBOARD_DIR . $filename . '.php';

		// if the file readable, include it.
		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}

	/**
	 * Activation Reset
	 *
	 * @return void
	 * @since 0.0.1
	 */
	public function activation_redirect(): void {
		// Avoid redirection in case of WP_CLI calls.
		if ( defined( 'WP_CLI' ) && \WP_CLI ) {
			return;
		}

		// Avoid redirection in case of ajax calls.
		if ( wp_doing_ajax() ) {
			return;
		}

		$do_redirect = apply_filters( 'suredash_enable_redirect_activation', get_option( '__suredash_do_redirect' ) );

		if ( $do_redirect ) {

			update_option( '__suredash_do_redirect', false );

			if ( ! is_multisite() ) {
				wp_safe_redirect(
					add_query_arg(
						[
							'page'                   => 'portal',
							'sd-activation-redirect' => true,
						],
						admin_url( 'admin.php' )
					)
				);
				exit;
			}
		}
	}

	/**
	 * Define the constants which will be used throughout the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function define_constants(): void {
		define( 'SUREDASHBOARD_UPGRADE_LINK', 'https://suredash.com' );
		define( 'SUREDASHBOARD_TABLET_BREAKPOINT', '1024' );
		define( 'SUREDASHBOARD_MOBILE_BREAKPOINT', '768' );

		define( 'SUREDASHBOARD_BASE', plugin_basename( SUREDASHBOARD_FILE ) );
		define( 'SUREDASHBOARD_DIR', plugin_dir_path( SUREDASHBOARD_FILE ) );
		define( 'SUREDASHBOARD_URL', plugins_url( '/', SUREDASHBOARD_FILE ) );
		define( 'SUREDASHBOARD_INTERACTIVE_BLOCKS_DIR', SUREDASHBOARD_DIR . 'core/blocks/interactivity/build/' );

		define( 'SUREDASHBOARD_SETTINGS', 'portal_settings' );
		define( 'SUREDASHBOARD_CAPABILITY', 'manage_options' );

		! defined( 'SUREDASHBOARD_DEVELOPMENT_MODE' ) && define( 'SUREDASHBOARD_DEVELOPMENT_MODE', false );

		$css_suffix = SUREDASHBOARD_DEVELOPMENT_MODE ? '.css' : '.min.css';
		$js_suffix  = SUREDASHBOARD_DEVELOPMENT_MODE ? '.js' : '.min.js';

		define( 'SUREDASHBOARD_CSS_SUFFIX', $css_suffix );
		define( 'SUREDASHBOARD_JS_SUFFIX', $js_suffix );

		define( 'SUREDASHBOARD_CSS_ASSETS_FOLDER', SUREDASHBOARD_DEVELOPMENT_MODE ? SUREDASHBOARD_URL . 'assets/css/unminified/' : SUREDASHBOARD_URL . 'assets/css/minified/' );
		define( 'SUREDASHBOARD_JS_ASSETS_FOLDER', SUREDASHBOARD_DEVELOPMENT_MODE ? SUREDASHBOARD_URL . 'assets/js/unminified/' : SUREDASHBOARD_URL . 'assets/js/minified/' );

		// Include required functions.
		require_once 'inc/functions/functions.php';
		require_once 'inc/functions/markup.php';
		require_once 'inc/functions/operations.php';

		// Include required email functions.
		require_once 'inc/functions/emails.php';
	}

	/**
	 * Initialize the plugin bases.
	 *
	 * @since 1.0.0
	 */
	public function initialize_base(): void {
		define( 'SUREDASHBOARD_SLUG', 'portal' );
		define( 'SUREDASHBOARD_POST_TYPE', 'portal' );
		define( 'SUREDASHBOARD_TAXONOMY', 'portal_group' );
		define( 'SUREDASHBOARD_FEED_POST_TYPE', 'community-post' );
		define( 'SUREDASHBOARD_FEED_TAXONOMY', 'community-forum' );
		define( 'SUREDASHBOARD_SUB_CONTENT_POST_TYPE', 'community-content' );

		/* Load the Notices Library class. */
		require_once 'inc/lib/astra-notices/class-astra-notices.php';
	}

	/**
	 * Plugin Activation actions.
	 *
	 * @since 1.0.0
	 */
	public function activation_actions(): void {
		Starter_Content::get_instance()->create_pages();

		/**
		 * Reset rewrite rules to avoid go to permalinks page
		 * through deleting the database options to force WP to do it
		 * because of on activation not work well flush_rewrite_rules()
		 */
		delete_option( 'rewrite_rules' );
		$this->create_custom_user_role();
		update_option( '__suredash_do_redirect', true );
	}

	/**
	 * Create a custom user role for SureDash.
	 *
	 * @since 1.0.0
	 */
	public function create_custom_user_role(): void {
		$subscriber = get_role( 'subscriber' );
		$caps       = [];

		if ( $subscriber ) {
			foreach ( $subscriber->capabilities as $cap => $grant ) {
				$caps[ $cap ] = $grant;
			}
		}

		$caps = apply_filters( 'suredash_user_role_caps', $caps );
		add_role( 'suredash_user', 'SureDash User', $caps ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase -- wordpress hook
	}

	/**
	 * Plugin Deactivation actions.
	 *
	 * @since 1.0.0
	 */
	public function deactivation_actions(): void {
		remove_role( 'suredash_user' );
	}

	/**
	 * Register theme menus.
	 *
	 * @since 1.0.0
	 */
	public function do_theme_setup(): void {
		/* FSE Templates Support */
		Templates::get_instance();

		register_nav_menus(
			[
				'portal_menu' => esc_html__( 'Portal Menu', 'suredash' ),
			]
		);

		/* Load themes compatibility. */
		Theme::get_instance();
	}

	/**
	 * Enqueue required classes after plugins loaded.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function load_plugin(): void {
		/* RewriteRules init */
		RewriteRules::get_instance();

		/* Maintenance init */
		Maintenance::get_instance();

		/* Initialize the Font Manager */
		FontManager::get_instance();

		/* Load Router for API. */
		Routes::get_instance();

		/* API init */
		API::get_instance();

		/* CPTs init */
		CPTs::get_instance();

		/* Integrations init */
		IntegrationsRegistry::get_instance();

		/* Notifier init */
		Notifier_Base::get_instance();

		if ( is_admin() ) {
			/* Admin Notices init */
			Notices::get_instance();

			/* Admin Setup init */
			Setup::get_instance();

			/* Editor init */
			Editor::get_instance();

			/* Admin Menu init */
			Menu::get_instance();

			/* Load Ajax for Backend. */
			AjaxBackend::get_instance();
		} else {
			/* Frontend Renderer. */
			Renderer::get_instance();

			/* Load Shortcodes. */
			CodesRegistry::get_instance();
		}

		/* InitBlocks init */
		InitBlocks::get_instance();

		/* Blocks Dynamic init */
		Dynamic::get_instance();

		/* Load plugins compatibility. */
		Plugin::get_instance();

		/* Comment Management init */
		Comment_Management::get_instance();

		/**
		 * SureDash Init.
		 *
		 * Fires when SureDash is instantiated.
		 *
		 * @since 1.0.0
		 */
		do_action( 'suredash_init' );
	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
Portals_Loader::get_instance();
