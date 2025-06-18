<?php
/**
 * Blocksy Child Theme functions and definitions
 */

// Add theme support for full-width content
function blocksy_child_theme_setup() {
    // Add support for full and wide align options
    add_theme_support('align-wide');
    
    // Add support for editor styles
    add_theme_support('editor-styles');
    
    // Add support for theme.json
    add_theme_support('wp-block-styles');
    add_theme_support('appearance-tools');
}
add_action('after_setup_theme', 'blocksy_child_theme_setup');

// Force theme.json to be recognized and loaded
add_filter('should_load_separate_core_block_assets', '__return_true');

// Load CSS variables early so theme.json can reference them
add_action('wp_head', function() {
    $vars_file = get_stylesheet_directory() . '/assets/css/s-vars.css';
    if (file_exists($vars_file)) {
        echo '<style id="s-vars-inline">' . file_get_contents($vars_file) . '</style>';
    }
    
    // Override Blocksy's container constraints
    echo '<style id="studio-width-overrides">
    .ct-container,
    .ct-container-full,
    .entry-content.is-layout-constrained,
    .wp-block-group.is-layout-constrained {
        max-width: none !important;
        width: 100% !important;
    }
    
    /* Ensure full-width utilities work */
    .full-width {
        width: 100vw !important;
        margin-left: calc(50% - 50vw) !important;
        margin-right: calc(50% - 50vw) !important;
        max-width: none !important;
    }
    </style>';
}, 1); // Very early priority

// Also load in admin/editor
add_action('admin_head', function() {
    $vars_file = get_stylesheet_directory() . '/assets/css/s-vars.css';
    if (file_exists($vars_file)) {
        echo '<style id="s-vars-admin">' . file_get_contents($vars_file) . '</style>';
    }
}, 1);

// Ensure theme.json styles are loaded with high priority
add_action('wp_enqueue_scripts', function() {
    // Force WordPress to regenerate theme.json styles
    wp_enqueue_style('wp-block-library');
    wp_enqueue_style('global-styles');
}, 5);

/**
 * Studio Design System Integration
 */

// Load S Design System CSS
add_action('wp_enqueue_scripts', function() {
    // S CSS Variables
    wp_enqueue_style(
        's-vars', 
        get_stylesheet_directory_uri() . '/assets/css/s-vars.css',
        [],
        filemtime(get_stylesheet_directory() . '/assets/css/s-vars.css')
    );
    
    // S Utility Classes (auto-generated)
    $utilities_file = get_stylesheet_directory() . '/assets/css/s-utilities.css';
    if (file_exists($utilities_file)) {
        wp_enqueue_style(
            's-utilities', 
            get_stylesheet_directory_uri() . '/assets/css/s-utilities.css',
            ['s-vars'],
            filemtime($utilities_file)
        );
    }
    
    // S Hero Component
    $hero_file = get_stylesheet_directory() . '/assets/css/s-hero.css';
    if (file_exists($hero_file)) {
        wp_enqueue_style(
            's-hero', 
            get_stylesheet_directory_uri() . '/assets/css/s-hero.css',
            ['s-vars'],
            filemtime($hero_file)
        );
    }
    
    // S Custom Variables (user overrides)
    $custom_file = get_stylesheet_directory() . '/assets/css/s-custom.css';
    if (file_exists($custom_file)) {
        wp_enqueue_style(
            's-custom', 
            get_stylesheet_directory_uri() . '/assets/css/s-custom.css',
            ['s-vars'],
            filemtime($custom_file)
        );
    }
});

// Load S Design System CSS in block editor
add_action('enqueue_block_editor_assets', function() {
    // S CSS Variables in editor
    wp_enqueue_style(
        's-vars-editor', 
        get_stylesheet_directory_uri() . '/assets/css/s-vars.css',
        [],
        filemtime(get_stylesheet_directory() . '/assets/css/s-vars.css')
    );
    
    // S Utilities in editor
    $utilities_file = get_stylesheet_directory() . '/assets/css/s-utilities.css';
    if (file_exists($utilities_file)) {
        wp_enqueue_style(
            's-utilities-editor', 
            get_stylesheet_directory_uri() . '/assets/css/s-utilities.css',
            ['s-vars-editor'],
            filemtime($utilities_file)
        );
    }
    
    // S Hero in editor
    $hero_file = get_stylesheet_directory() . '/assets/css/s-hero.css';
    if (file_exists($hero_file)) {
        wp_enqueue_style(
            's-hero-editor', 
            get_stylesheet_directory_uri() . '/assets/css/s-hero.css',
            ['s-vars-editor'],
            filemtime($hero_file)
        );
    }
    
    // S Custom in editor
    $custom_file = get_stylesheet_directory() . '/assets/css/s-custom.css';
    if (file_exists($custom_file)) {
        wp_enqueue_style(
            's-custom-editor', 
            get_stylesheet_directory_uri() . '/assets/css/s-custom.css',
            ['s-vars-editor'],
            filemtime($custom_file)
        );
    }
});

// Load enhanced Studio system with Daniel's approach
if (file_exists(get_stylesheet_directory() . '/studio-system/studio-loader-enhanced.php')) {
    require_once get_stylesheet_directory() . '/studio-system/studio-loader-enhanced.php';
}

// Add admin notice when utilities are generated
add_action('admin_notices', function() {
    if (isset($_GET['studio_utilities_generated'])) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Studio utility classes have been generated successfully!', 'studio'); ?></p>
        </div>
        <?php
    }
});

// Add regenerate utilities button to admin bar
add_action('admin_bar_menu', function($wp_admin_bar) {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $wp_admin_bar->add_node([
        'id' => 'studio-regenerate-utilities',
        'title' => '🎨 Regenerate Studio Utilities',
        'href' => add_query_arg([
            'action' => 'studio_regenerate_utilities',
            'nonce' => wp_create_nonce('studio_regenerate_utilities')
        ], admin_url('admin-post.php')),
        'meta' => [
            'title' => 'Regenerate Studio utility classes from CSS variables'
        ]
    ]);
}, 100);

// Handle utility regeneration
add_action('admin_post_studio_regenerate_utilities', function() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    if (!wp_verify_nonce($_GET['nonce'], 'studio_regenerate_utilities')) {
        wp_die('Invalid nonce');
    }
    
    // Regenerate utilities
    if (function_exists('Studio\studio_generate_utilities')) {
        Studio\studio_generate_utilities();
    }
    
    // Redirect back with success message
    wp_redirect(add_query_arg('studio_utilities_generated', '1', wp_get_referer()));
    exit;
});

/**
 * Villa Admin Menu
 */
class Villa_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_villa_export_owner_emails', array($this, 'ajax_export_owner_emails'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Villa Management',
            'Villa Management',
            'manage_options',
            'villa-management',
            array($this, 'render_property_list'),
            'dashicons-building',
            30
        );
        
        add_submenu_page(
            'villa-management',
            'Property List',
            'Property List',
            'manage_options',
            'villa-management',
            array($this, 'render_property_list')
        );
        
        add_submenu_page(
            'villa-management',
            'Owners',
            'Owners',
            'manage_options',
            'villa-owners',
            array($this, 'render_owners_page')
        );
        
        add_submenu_page(
            'villa-management',
            'Committees',
            'Committees',
            'manage_options',
            'villa-committees',
            array($this, 'render_committees_page')
        );
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'villa-') === false) {
            return;
        }
        
        wp_enqueue_style(
            'villa-admin-crm',
            get_stylesheet_directory_uri() . '/assets/css/villa-admin-crm.css',
            array(),
            filemtime(get_stylesheet_directory() . '/assets/css/villa-admin-crm.css')
        );
        
        if ($hook === 'villa-management_page_villa-committees') {
            wp_enqueue_style(
                'villa-committees',
                get_stylesheet_directory_uri() . '/assets/css/villa-committees.css',
                array(),
                filemtime(get_stylesheet_directory() . '/assets/css/villa-committees.css')
            );
        }
    }
    
    public function render_property_list() {
        $json_dir = get_stylesheet_directory() . '/villa-data/properties/';
        $properties = array();
        
        if (is_dir($json_dir)) {
            $files = glob($json_dir . '*/property.json');
            foreach ($files as $file) {
                $content = file_get_contents($file);
                if ($content) {
                    $property = json_decode($content, true);
                    if ($property) {
                        $properties[] = $property;
                    }
                }
            }
        }
        
        // Sort properties by unit number
        usort($properties, function($a, $b) {
            return strcmp($a['unit']['unitNumber'], $b['unit']['unitNumber']);
        });
        
        include get_stylesheet_directory() . '/templates/admin/property-list.php';
    }
    
    public function render_owners_page() {
        $json_dir = get_stylesheet_directory() . '/villa-data/owners/';
        $owners = array();
        
        if (is_dir($json_dir)) {
            $files = glob($json_dir . '*/profile.json');
            foreach ($files as $file) {
                $content = file_get_contents($file);
                if ($content) {
                    $owner = json_decode($content, true);
                    if ($owner) {
                        $owners[] = $owner;
                    }
                }
            }
        }
        
        // Sort owners by last name
        usort($owners, function($a, $b) {
            return strcmp($a['personalInfo']['lastName'], $b['personalInfo']['lastName']);
        });
        
        include get_stylesheet_directory() . '/templates/admin/owners-list.php';
    }
    
    public function render_committees_page() {
        $json_dir = get_stylesheet_directory() . '/villa-data/committees/';
        $committees = array();
        
        if (is_dir($json_dir)) {
            $files = glob($json_dir . '*/committee.json');
            foreach ($files as $file) {
                $content = file_get_contents($file);
                if ($content) {
                    $committee = json_decode($content, true);
                    if ($committee) {
                        $committees[] = $committee;
                    }
                }
            }
        }
        
        include get_stylesheet_directory() . '/templates/admin/committees-list.php';
    }
    
    public function ajax_export_owner_emails() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        check_ajax_referer('villa_admin_nonce', 'nonce');
        
        $json_dir = get_stylesheet_directory() . '/villa-data/owners/';
        $emails = array();
        
        if (is_dir($json_dir)) {
            $files = glob($json_dir . '*/profile.json');
            foreach ($files as $file) {
                $content = file_get_contents($file);
                if ($content) {
                    $owner = json_decode($content, true);
                    if ($owner && isset($owner['contactInfo']['email'])) {
                        $emails[] = array(
                            'email' => $owner['contactInfo']['email'],
                            'firstName' => $owner['personalInfo']['firstName'],
                            'lastName' => $owner['personalInfo']['lastName'],
                            'fullName' => $owner['personalInfo']['firstName'] . ' ' . $owner['personalInfo']['lastName']
                        );
                    }
                }
            }
        }
        
        wp_send_json_success($emails);
    }
}

// Initialize Villa Admin
new Villa_Admin();

// Enqueue child theme styles
function blocksy_child_enqueue_styles() {
    // Enqueue parent theme styles
    wp_enqueue_style('blocksy-parent-style', get_template_directory_uri() . '/style.css');
    
    // Enqueue child theme styles with all MI design system
    wp_enqueue_style(
        'blocksy-child-style', 
        get_stylesheet_directory_uri() . '/style.css', 
        array('blocksy-parent-style'),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'blocksy_child_enqueue_styles');

/**
 * Register custom block pattern categories
 */
function register_mi_agency_pattern_categories() {
    register_block_pattern_category(
        'mi-agency',
        array(
            'label' => __('MI Agency', 'blocksy-child'),
            'description' => __('Custom patterns for MI Agency projects', 'blocksy-child'),
        )
    );
}
add_action('init', 'register_mi_agency_pattern_categories');
