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
 * Ensure child theme.json takes absolute priority over parent theme
 * Uses WordPress theme.json API to properly override Blocksy settings
 */
function blocksy_child_enforce_theme_json_priority() {
    // Only apply if theme has theme.json
    if (!wp_theme_has_theme_json()) {
        return;
    }
    
    // Hook into theme layer to ensure our settings take priority
    add_filter('wp_theme_json_data_theme', function($theme_json) {
        $new_data = $theme_json->get_data();
        
        // Force our child theme settings to override parent
        // This ensures container widths, typography, colors from our theme.json are authoritative
        $new_data['settings']['useRootPaddingAwareAlignments'] = true;
        $new_data['settings']['appearanceTools'] = true;
        
        // Ensure layout settings take priority
        if (isset($new_data['settings']['layout'])) {
            $new_data['settings']['layout']['contentSize'] = 'var(--wp--custom--layout--content-size)';
            $new_data['settings']['layout']['wideSize'] = 'var(--wp--custom--layout--wide-size)';
        }
        
        return new WP_Theme_JSON_Data($new_data, 'theme');
    }, 20); // Higher priority to run after parent theme
}
add_action('after_setup_theme', 'blocksy_child_enforce_theme_json_priority');

/**
 * Optional: Force user customizations to respect our design system
 * Uncomment if you want to prevent users from overriding your design tokens
 */
/*
function blocksy_child_lock_design_system() {
    add_filter('wp_theme_json_data_user', function($theme_json) {
        $data = $theme_json->get_data();
        
        // Prevent user from changing core design tokens
        // Remove or modify this based on your needs
        unset($data['settings']['color']['palette']);
        unset($data['settings']['typography']['fontFamilies']);
        
        return new WP_Theme_JSON_Data($data, 'custom');
    });
}
add_action('after_setup_theme', 'blocksy_child_lock_design_system');
*/

// Register custom blocks
function blocksy_child_register_blocks() {
    // Future block registrations can go here
}
add_action('init', 'blocksy_child_register_blocks');

// Add your custom functions below this line
