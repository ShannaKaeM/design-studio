<?php
/**
 * The Studio - Functions
 * 
 * @package TheStudio
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Define theme constants
 */
define('STUDIO_VERSION', '1.0.0');
define('STUDIO_DIR', get_stylesheet_directory());
define('STUDIO_URL', get_stylesheet_directory_uri());

/**
 * ACF Local JSON Configuration
 */

// Set ACF Local JSON save location
add_filter('acf/settings/save_json', function($path) {
    return STUDIO_DIR . '/acf-json';
});

// Set ACF Local JSON load location
add_filter('acf/settings/load_json', function($paths) {
    unset($paths[0]);
    $paths[] = STUDIO_DIR . '/acf-json';
    return $paths;
});

/**
 * Load Studio Core
 */
function studio_load_core() {
    // Load core components
    $core_files = [
        'variable-scanner.php',  // Load scanner first
        'studio-loader.php',
        'yaml-sync.php'
    ];
    
    foreach ($core_files as $file) {
        $filepath = STUDIO_DIR . '/studio/core/' . $file;
        if (file_exists($filepath)) {
            require_once $filepath;
        }
    }
    
    // Load Villa system
    if (file_exists(STUDIO_DIR . '/studio/villa/villa-loader.php')) {
        require_once STUDIO_DIR . '/studio/villa/villa-loader.php';
    }
}
add_action('after_setup_theme', 'studio_load_core', 5);

/**
 * Enqueue Studio styles
 */
function studio_enqueue_styles() {
    // Parent theme styles
    wp_enqueue_style('blocksy-parent', get_template_directory_uri() . '/style.css', [], STUDIO_VERSION);
    
    // Studio theme styles
    wp_enqueue_style('studio-theme', STUDIO_URL . '/style.css', ['blocksy-parent'], STUDIO_VERSION);
    
    // S System CSS variables
    if (file_exists(STUDIO_DIR . '/studio/css/s-vars.css')) {
        wp_enqueue_style('s-vars', STUDIO_URL . '/studio/css/s-vars.css', [], STUDIO_VERSION);
    }
    
    // S Hero Component
    if (file_exists(STUDIO_DIR . '/studio/css/s-hero.css')) {
        wp_enqueue_style('s-hero', STUDIO_URL . '/studio/css/s-hero.css', ['s-vars'], STUDIO_VERSION);
    }
    
    // S Custom overrides
    if (file_exists(STUDIO_DIR . '/studio/css/s-custom.css')) {
        wp_enqueue_style('s-custom', STUDIO_URL . '/studio/css/s-custom.css', ['s-vars'], STUDIO_VERSION);
    }
    
    // Legacy Studio CSS variables (keep for now)
    if (file_exists(STUDIO_DIR . '/studio/css/studio-vars.css')) {
        wp_enqueue_style('studio-vars', STUDIO_URL . '/studio/css/studio-vars.css', [], STUDIO_VERSION);
    }
    
    // Studio utilities (auto-generated)
    if (file_exists(STUDIO_DIR . '/studio/css/studio-utilities.css')) {
        wp_enqueue_style('studio-utilities', STUDIO_URL . '/studio/css/studio-utilities.css', ['studio-vars'], STUDIO_VERSION);
    }
    
    // Studio selectors (from selector builder)
    if (file_exists(STUDIO_DIR . '/studio/css/studio-selectors.css')) {
        wp_enqueue_style('studio-selectors', STUDIO_URL . '/studio/css/studio-selectors.css', ['studio-vars'], STUDIO_VERSION);
    }
    
    // Studio hero styles
    if (file_exists(STUDIO_DIR . '/studio/css/hero-styles.css')) {
        wp_enqueue_style('studio-hero-styles', STUDIO_URL . '/studio/css/hero-styles.css', ['studio-vars'], STUDIO_VERSION);
    }
}
add_action('wp_enqueue_scripts', 'studio_enqueue_styles', 20);

/**
 * Admin menu setup
 */
function studio_admin_menu() {
    // Studio Designer menu
    add_menu_page(
        'Studio Designer',
        'Studio Designer',
        'manage_options',
        'studio-designer',
        'studio_admin_designer_dashboard',
        'dashicons-admin-customizer',
        3 // Position after Dashboard
    );
    
    // Designer submenu items
    add_submenu_page(
        'studio-designer',
        'Variables',
        'Variables',
        'manage_options',
        'studio-variables',
        'studio_admin_variables'
    );
    
    add_submenu_page(
        'studio-designer',
        'Selectors',
        'Selectors',
        'manage_options',
        'studio-selectors',
        'studio_admin_selectors'
    );
    
    add_submenu_page(
        'studio-designer',
        'Utilities',
        'Utilities',
        'manage_options',
        'studio-utilities',
        'studio_admin_utilities'
    );
    
    // Villa Admin menu (separate from Studio Designer)
    add_menu_page(
        'Villa Admin',
        'Villa Admin',
        'manage_options',
        'villa-admin',
        'villa_admin_dashboard',
        'dashicons-admin-multisite',
        30 // Position after Users
    );
}
add_action('admin_menu', 'studio_admin_menu', 5);

/**
 * Load admin pages
 */
function studio_load_admin_pages() {
    // Load S System admin - DISABLED (using blocksy-child now)
    // if (file_exists(STUDIO_DIR . '/studio/admin/admin-page-s.php')) {
    //     require_once STUDIO_DIR . '/studio/admin/admin-page-s.php';
    // }
    
    // if (file_exists(STUDIO_DIR . '/studio/admin/scan-variables-s.php')) {
    //     require_once STUDIO_DIR . '/studio/admin/scan-variables-s.php';
    // }
    
    // Legacy admin pages
    if (file_exists(STUDIO_DIR . '/studio/admin/studio-admin.php')) {
        require_once STUDIO_DIR . '/studio/admin/studio-admin.php';
    }
    
    if (file_exists(STUDIO_DIR . '/studio/admin/studio-utilities.php')) {
        require_once STUDIO_DIR . '/studio/admin/studio-utilities.php';
    }
    
    if (file_exists(STUDIO_DIR . '/studio/admin/studio-selectors.php')) {
        require_once STUDIO_DIR . '/studio/admin/studio-selectors.php';
    }
}
add_action('admin_init', 'studio_load_admin_pages', 1);

/**
 * Admin page callbacks
 */
function studio_admin_designer_dashboard() {
    ?>
    <div class="wrap">
        <h1>Studio Designer</h1>
        <p>Welcome to Studio Designer - Your CSS-driven design system powered by variables.</p>
        <div class="studio-dashboard-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 30px;">
            <div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px;">
                <h3 style="margin-top: 0;">Variables</h3>
                <p>Define CSS variables with @control annotations to auto-generate UI controls.</p>
                <a href="<?php echo admin_url('admin.php?page=studio-variables'); ?>" class="button">Manage Variables</a>
            </div>
            <div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px;">
                <h3 style="margin-top: 0;">Selectors</h3>
                <p>Target any element with groups of CSS variables.</p>
                <a href="<?php echo admin_url('admin.php?page=studio-selectors'); ?>" class="button">Build Selectors</a>
            </div>
            <div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px;">
                <h3 style="margin-top: 0;">Utilities</h3>
                <p>Auto-generated utility classes from your design tokens.</p>
                <a href="<?php echo admin_url('admin.php?page=studio-utilities'); ?>" class="button">View Utilities</a>
            </div>
        </div>
        
        <div style="background: #f0f0f1; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin-top: 30px;">
            <h2 style="margin-top: 0;">How It Works</h2>
            <ol>
                <li><strong>Define Variables:</strong> Add CSS variables with @control annotations in your CSS</li>
                <li><strong>Auto-Generate Controls:</strong> The system automatically creates UI controls based on annotations</li>
                <li><strong>Build Selectors:</strong> Target any element on your site with variable groups</li>
                <li><strong>Use Utilities:</strong> Apply auto-generated utility classes in your content</li>
            </ol>
        </div>
    </div>
    <?php
}

function villa_admin_dashboard() {
    // This will be handled by the Villa loader
    if (class_exists('\Studio\Villa\VillaLoader')) {
        \Studio\Villa\VillaLoader::get_instance()->render_villa_dashboard();
    } else {
        ?>
        <div class="wrap">
            <h1>Villa Admin</h1>
            <p>Villa Management System loading...</p>
        </div>
        <?php
    }
}

function studio_admin_variables() {
    // Call the function from studio-admin.php
    if (function_exists('studio_admin_variables_page')) {
        studio_admin_variables_page();
    } else {
        ?>
        <div class="wrap">
            <h1>Studio Variables</h1>
            <p>Loading variables system...</p>
        </div>
        <?php
    }
}

function studio_admin_selectors() {
    // Call the function from studio-selectors.php
    if (function_exists('studio_admin_selectors_page')) {
        studio_admin_selectors_page();
    } else {
        ?>
        <div class="wrap">
            <h1>Studio Selectors</h1>
            <p>Loading selectors system...</p>
        </div>
        <?php
    }
}

function studio_admin_utilities() {
    // Call the function from studio-utilities.php
    if (function_exists('studio_admin_utilities_page')) {
        studio_admin_utilities_page();
    } else {
        ?>
        <div class="wrap">
            <h1>Studio Utilities</h1>
            <p>Loading utilities system...</p>
        </div>
        <?php
    }
}

/**
 * Load Studio blocks
 */
function studio_load_blocks() {
    $blocks_dir = STUDIO_DIR . '/studio/blocks';
    if (file_exists($blocks_dir . '/hero-component.php')) {
        require_once $blocks_dir . '/hero-component.php';
    }
    
    // Load hero helper
    if (file_exists(STUDIO_DIR . '/studio/admin/studio-hero-helper.php')) {
        require_once STUDIO_DIR . '/studio/admin/studio-hero-helper.php';
    }
}
add_action('init', 'studio_load_blocks');

/**
 * Theme setup
 */
function studio_theme_setup() {
    // Add theme support features
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption']);
    
    // Create data directories if they don't exist
    $dirs = [
        WP_CONTENT_DIR . '/studio-data',
        WP_CONTENT_DIR . '/studio-data/villas',
        WP_CONTENT_DIR . '/studio-data/owners',
        WP_CONTENT_DIR . '/studio-data/committees',
        WP_CONTENT_DIR . '/studio-data/sync'
    ];
    
    foreach ($dirs as $dir) {
        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
        }
    }
}
add_action('after_setup_theme', 'studio_theme_setup');

/**
 * Initialize Studio
 */
function studio_init() {
    // Future: Initialize components
    do_action('studio_init');
}
add_action('init', 'studio_init');