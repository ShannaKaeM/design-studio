<?php
/**
 * Plugin Loader.
 *
 * @package SureDashboardPro
 * @since 1.0.0
 */

namespace SureDashboardPro;

use SureDashboardPro\Admin\Licensing;
use SureDashboardPro\Admin\Menu;
use SureDashboardPro\Admin\Notices;
use SureDashboardPro\Core\CPT\Posts;
use SureDashboardPro\Core\Integrator as IntegrationsRegistry;
use SureDashboardPro\Core\Renderer;
use SureDashboardPro\Core\Routes;
use SureDashboardPro\Core\Updater;
use SureDashboardPro\Inc\Utils\PostMeta;
use SureDashboardPro\Inc\Utils\Settings;
use SureDashboardPro\Inc\Utils\TermMeta;

/**
 * SureDash_Pro_Loader
 *
 * @since 1.0.0
 */
class SureDash_Pro_Loader {
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

		// Initialize the plugin.
		$this->init();

		spl_autoload_register( [ $this, 'autoload' ] );

		// Activation hook.
		register_activation_hook( SUREDASH_PRO_FILE, [ $this, 'activation_actions' ] );

		// Deactivation hook.
		register_deactivation_hook( SUREDASH_PRO_FILE, [ $this, 'deactivation_actions' ] );

		add_action( 'init', [ $this, 'load_textdomain' ] );
		add_action( 'plugins_loaded', [ $this, 'load_pre_requisites' ], 80 );
		add_action( 'plugins_loaded', [ $this, 'load_plugin' ], 100 );
		add_filter( 'bsf_core_stats', [ $this, 'add_suredash_pro_analytics_data' ] );
	}

	/**
	 * Callback function to add SureDash Pro specific analytics data.
	 *
	 * @param array $stats_data existing stats_data.
	 * @since 0.0.5
	 * @return array
	 */
	public function add_suredash_pro_analytics_data( $stats_data ) {

		$stats_data['plugin_data']['suredash']['pro_version'] = SUREDASH_PRO_VER;

		return $stats_data;
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

		$file = SUREDASHBOARD_PRO_DIR . $filename . '.php';

		// if the file readable, include it.
		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}

	/**
	 * Load Plugin Text Domain.
	 * This will load the translation textdomain depending on the file priorities.
	 *      1. Global Languages /wp-content/languages/suredash-pro/ folder
	 *      2. Local directory /wp-content/plugins/suredash-pro/languages/ folder
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function load_textdomain(): void {
		// Default languages directory.
		$lang_dir = SUREDASHBOARD_PRO_DIR . 'languages/';

		/**
		 * Filters the languages directory path to use for plugin.
		 *
		 * @param string $lang_dir The languages directory path.
		 */
		$lang_dir = apply_filters( 'suredash_pro_languages_directory', $lang_dir );

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
		$locale = apply_filters( 'plugin_locale', $get_locale, 'suredash-pro' );//phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- wordpress hook
		$mofile = sprintf( '%1$s-%2$s.mo', 'suredash-pro', $locale );

		// Setup paths to current locale file.
		$mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;
		$mofile_local  = $lang_dir . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/suredash/ folder.
			load_textdomain( 'suredash-pro', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/suredash/languages/ folder.
			load_textdomain( 'suredash-pro', $mofile_local );
		} else {
			// Load the default language files.
			load_plugin_textdomain( 'suredash-pro', false, $lang_dir );
		}
	}

	/**
	 * Define the constants which will be used throughout the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function define_constants(): void {
		define( 'SUREDASHBOARD_PRO_BASE', plugin_basename( SUREDASH_PRO_FILE ) );
		define( 'SUREDASHBOARD_PRO_DIR', plugin_dir_path( SUREDASH_PRO_FILE ) );
		define( 'SUREDASHBOARD_PRO_URL', plugins_url( '/', SUREDASH_PRO_FILE ) );

		! defined( 'SUREDASHBOARD_DEVELOPMENT_MODE' ) && define( 'SUREDASHBOARD_DEVELOPMENT_MODE', false );

		define( 'SUREDASHBOARD_PRO_CSS_ASSETS_FOLDER', SUREDASHBOARD_DEVELOPMENT_MODE ? SUREDASHBOARD_PRO_URL . 'assets/css/unminified/' : SUREDASHBOARD_PRO_URL . 'assets/css/minified/' );
		define( 'SUREDASHBOARD_PRO_JS_ASSETS_FOLDER', SUREDASHBOARD_DEVELOPMENT_MODE ? SUREDASHBOARD_PRO_URL . 'assets/js/unminified/' : SUREDASHBOARD_PRO_URL . 'assets/js/minified/' );

		// Include required functions.
		require_once 'inc/functions/functions.php';

		// Include required shortcode supportive functions.
		require_once 'inc/functions/shortcode-supports.php';
	}

	/**
	 * Initialize the plugin.
	 *
	 * @since 1.0.0
	 */
	public function init(): void {
		require_once SUREDASHBOARD_PRO_DIR . 'inc/lib/astra-notices/class-astra-notices.php';
	}

	/**
	 * Plugin Activation actions.
	 *
	 * @since 1.0.0
	 */
	public function activation_actions(): void {
	}

	/**
	 * Plugin Deactivation actions.
	 *
	 * @since 1.0.0
	 */
	public function deactivation_actions(): void {
	}

	/**
	 * Load pre-requisites.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function load_pre_requisites(): void {
		/* Load Router for API. */
		Routes::get_instance();

		/* Initialize pro settings */
		Settings::get_instance();

		/* Initialize pro post meta */
		PostMeta::get_instance();

		/* Initialize pro term meta */
		TermMeta::get_instance();
	}

	/**
	 * Enqueue required classes after plugins loaded.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function load_plugin(): void {
		/* Initialize pro updater */
		Updater::get_instance();

		if ( ! did_action( 'suredash_init' ) ) {
			add_action( 'admin_notices', [ Notices::get_instance(), 'fails_to_load' ] );
			return;
		}

		/* Initialize pro integrations */
		IntegrationsRegistry::get_instance();

		/* Initialize pro posts */
		Posts::get_instance();

		if ( is_admin() ) {
			/* Admin Notices */
			Notices::get_instance();

			/* Licensing */
			Licensing::get_instance();

			/* Menu init */
			Menu::get_instance();
		} else {
			/* Frontend Renderer. */
			Renderer::get_instance();
		}

		/**
		 * SureDash Pro Init.
		 *
		 * Fires when SureDash Pro is instantiated.
		 *
		 * @since 1.0.0
		 */
		do_action( 'suredash_pro_init' );
	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
SureDash_Pro_Loader::get_instance();
