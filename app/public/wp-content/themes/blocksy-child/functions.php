<?php
/**
 * Blocksy Child Theme functions and definitions
 */

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

/**
 * Enqueue component styles
 */
function enqueue_component_styles() {
    // Enqueue Attractions Loop component CSS
    wp_enqueue_style(
        'attractions-loop-component',
        get_stylesheet_directory_uri() . '/components/pages/home/sections/attractions-loop/attractions-loop.css',
        array(),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'enqueue_component_styles');

/**
 * Villa Admin Menu
 */
function villa_admin_menu() {
    add_menu_page(
        'Villa Management',
        'Villa Management',
        'manage_options',
        'villa-management',
        'villa_admin_dashboard',
        'dashicons-building',
        30
    );
    
    add_submenu_page(
        'villa-management',
        'Property List',
        'Property List',
        'manage_options',
        'villa-property-list',
        'villa_property_list_page'
    );
    
    add_submenu_page(
        'villa-management',
        'Owners',
        'Owners',
        'manage_options',
        'villa-owners',
        'villa_owners_page'
    );
    
    add_submenu_page(
        'villa-management',
        'Committees',
        'Committees',
        'manage_options',
        'villa-committees',
        'villa_committees_page'
    );
}
add_action('admin_menu', 'villa_admin_menu');

/**
 * Villa Admin Dashboard
 */
function villa_admin_dashboard() {
    echo '<div class="wrap">';
    echo '<h1>Villa Management Dashboard</h1>';
    echo '<p>Welcome to the Villa Capriani management system.</p>';
    echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;">';
    
    // Quick stats cards
    echo '<div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">';
    echo '<h3>Property Overview</h3>';
    echo '<p><a href="admin.php?page=villa-property-list" class="button button-primary">View Property List</a></p>';
    echo '</div>';
    
    echo '<div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">';
    echo '<h3>Owner Management</h3>';
    echo '<p><a href="admin.php?page=villa-owners" class="button button-primary">Manage Owners</a></p>';
    echo '</div>';
    
    echo '<div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">';
    echo '<h3>Committees</h3>';
    echo '<p><a href="admin.php?page=villa-committees" class="button button-primary">Manage Committees</a></p>';
    echo '</div>';
    
    echo '</div>';
    echo '</div>';
}

/**
 * Villa Property List Page
 */
function villa_property_list_page() {
    include get_stylesheet_directory() . '/villa-admin-property-list.php';
}

/**
 * Villa Owners Page
 */
function villa_owners_page() {
    include get_stylesheet_directory() . '/villa-admin-owners.php';
}

/**
 * Villa Committees Page
 */
function villa_committees_page() {
    include get_stylesheet_directory() . '/villa-admin-committees.php';
}
