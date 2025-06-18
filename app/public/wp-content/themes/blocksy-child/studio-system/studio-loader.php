<?php
/**
 * Studio System Loader
 * Main integration file that loads all Studio components
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Studio System Main Class
 */
class Studio_System {
    
    private static $instance = null;
    private $components = [];
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->define_constants();
        $this->load_components();
        $this->init_hooks();
    }
    
    /**
     * Define plugin constants
     */
    private function define_constants() {
        define('STUDIO_VERSION', '1.0.0');
        define('STUDIO_PATH', get_stylesheet_directory() . '/studio-system/');
        define('STUDIO_URL', get_stylesheet_directory_uri() . '/studio-system/');
    }
    
    /**
     * Load all components
     */
    private function load_components() {
        // Core components
        $components = [
            'scan-variables.php',
            'generate-controls.php',
            'generate-utilities.php',
            'selector-builder.php',
            'selector-builder-ui.php',
            'custom-elements-parser.php',
            'json-fields.php',
            'admin-page.php'
        ];
        
        foreach ($components as $component) {
            $file = STUDIO_PATH . $component;
            if (file_exists($file)) {
                require_once $file;
                $this->components[] = $component;
            } else {
                error_log("Studio System: Component not found - {$component}");
            }
        }
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Enqueue styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        
        // Auto-generate utilities on theme switch
        add_action('after_switch_theme', [$this, 'regenerate_utilities']);
        
        // Add body classes
        add_filter('body_class', [$this, 'add_body_classes']);
        
        // Initialize components
        add_action('init', [$this, 'init_components'], 1);
    }
    
    /**
     * Initialize individual components
     */
    public function init_components() {
        // Initialize JSON Fields for example post types
        if (function_exists('studio_watch_post_type')) {
            // Example: Watch custom post types
            if (post_type_exists('property')) {
                studio_watch_post_type('property', [
                    'sync_to_json' => true,
                    'sync_from_json' => true,
                    'directory' => 'properties'
                ]);
            }
        }
        
        // Trigger utility generation if needed
        $this->maybe_generate_utilities();
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Studio CSS variables
        wp_enqueue_style(
            'studio-vars',
            get_stylesheet_directory_uri() . '/assets/css/studio-vars.css',
            [],
            filemtime(get_stylesheet_directory() . '/assets/css/studio-vars.css')
        );
        
        // Studio utilities
        $utilities_file = get_stylesheet_directory() . '/assets/css/studio-utilities.css';
        if (file_exists($utilities_file)) {
            wp_enqueue_style(
                'studio-utilities',
                get_stylesheet_directory_uri() . '/assets/css/studio-utilities.css',
                ['studio-vars'],
                filemtime($utilities_file)
            );
        }
        
        // Studio selectors
        $selectors_file = get_stylesheet_directory() . '/assets/css/studio-selectors.css';
        if (file_exists($selectors_file)) {
            wp_enqueue_style(
                'studio-selectors',
                get_stylesheet_directory_uri() . '/assets/css/studio-selectors.css',
                ['studio-vars'],
                filemtime($selectors_file)
            );
        }
        
        // Custom overrides
        $custom_file = get_stylesheet_directory() . '/assets/css/studio-custom.css';
        if (file_exists($custom_file)) {
            wp_enqueue_style(
                'studio-custom',
                get_stylesheet_directory_uri() . '/assets/css/studio-custom.css',
                ['studio-vars', 'studio-utilities'],
                filemtime($custom_file)
            );
        }
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only on Studio admin pages
        if (strpos($hook, 'studio-system') === false) {
            return;
        }
        
        // Admin styles
        wp_enqueue_style(
            'studio-admin',
            STUDIO_URL . 'assets/css/studio-admin.css',
            ['wp-color-picker'],
            STUDIO_VERSION
        );
        
        // Admin scripts
        wp_enqueue_script(
            'studio-admin',
            STUDIO_URL . 'assets/js/studio-admin.js',
            ['jquery', 'wp-color-picker', 'wp-element', 'wp-components'],
            STUDIO_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('studio-admin', 'studioAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('studio_admin'),
            'variables' => function_exists('scan_all_studio_variables') ? scan_all_studio_variables() : [],
            'version' => STUDIO_VERSION
        ]);
    }
    
    /**
     * Maybe generate utilities
     */
    private function maybe_generate_utilities() {
        $utilities_file = get_stylesheet_directory() . '/assets/css/studio-utilities.css';
        
        // Generate if file doesn't exist or is older than vars file
        if (!file_exists($utilities_file)) {
            $this->regenerate_utilities();
        } else {
            $vars_file = get_stylesheet_directory() . '/assets/css/studio-vars.css';
            if (file_exists($vars_file) && filemtime($vars_file) > filemtime($utilities_file)) {
                $this->regenerate_utilities();
            }
        }
    }
    
    /**
     * Regenerate utilities
     */
    public function regenerate_utilities() {
        if (function_exists('generate_utilities')) {
            generate_utilities();
        }
    }
    
    /**
     * Add body classes
     */
    public function add_body_classes($classes) {
        $classes[] = 'studio-system';
        $classes[] = 'studio-' . str_replace('.', '-', STUDIO_VERSION);
        
        // Add theme mode class
        $theme_mode = get_option('studio_theme_mode', 'light');
        $classes[] = 'studio-' . $theme_mode;
        
        return $classes;
    }
    
    /**
     * Get system status
     */
    public function get_status() {
        $status = [
            'version' => STUDIO_VERSION,
            'components' => $this->components,
            'files' => [
                'vars' => file_exists(get_stylesheet_directory() . '/assets/css/studio-vars.css'),
                'utilities' => file_exists(get_stylesheet_directory() . '/assets/css/studio-utilities.css'),
                'selectors' => file_exists(get_stylesheet_directory() . '/assets/css/studio-selectors.css'),
                'custom' => file_exists(get_stylesheet_directory() . '/assets/css/studio-custom.css')
            ],
            'data' => [
                'variables' => function_exists('scan_all_studio_variables') ? count(scan_all_studio_variables()) : 0,
                'selectors' => function_exists('studio_selector_builder') ? count(studio_selector_builder()->get_selectors()) : 0
            ]
        ];
        
        return $status;
    }
}

/**
 * Initialize Studio System
 */
function studio_system() {
    return Studio_System::get_instance();
}

// Initialize on plugins_loaded to ensure theme is ready
add_action('after_setup_theme', function() {
    studio_system();
}, 5);

/**
 * Activation hook
 */
function studio_system_activate() {
    // Create necessary directories
    $dirs = [
        get_stylesheet_directory() . '/assets/css',
        get_stylesheet_directory() . '/assets/js',
        WP_CONTENT_DIR . '/studio-data'
    ];
    
    foreach ($dirs as $dir) {
        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
        }
    }
    
    // Generate initial utilities
    if (function_exists('generate_utilities')) {
        generate_utilities();
    }
}

/**
 * Deactivation hook
 */
function studio_system_deactivate() {
    // Clean up transients
    delete_transient('studio_system_status');
}

// Register activation/deactivation hooks
register_activation_hook(__FILE__, 'studio_system_activate');
register_deactivation_hook(__FILE__, 'studio_system_deactivate');