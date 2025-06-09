<?php
/**
 * Blocksy Child Theme functions and definitions
 */

// Enqueue child theme styles
function blocksy_child_enqueue_styles() {
    // Enqueue parent theme styles
    wp_enqueue_style('blocksy-parent-style', get_template_directory_uri() . '/style.css');
    
    // Enqueue child theme styles
    wp_enqueue_style('blocksy-child-style', get_stylesheet_directory_uri() . '/style.css', array('blocksy-parent-style'));
    
    // Enqueue design tokens CSS for consistent styling across components
    wp_enqueue_style(
        'blocksy-child-design-tokens',
        get_stylesheet_directory_uri() . '/assets/css/design-tokens.css',
        array('blocksy-parent-style'),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'blocksy_child_enqueue_styles');

// Enqueue design tokens in editor as well
function blocksy_child_enqueue_editor_styles() {
    wp_enqueue_style(
        'blocksy-child-editor-design-tokens',
        get_stylesheet_directory_uri() . '/assets/css/design-tokens.css',
        array(),
        wp_get_theme()->get('Version')
    );
}
add_action('enqueue_block_editor_assets', 'blocksy_child_enqueue_editor_styles');

// Ensure theme.json settings are properly loaded and override parent theme
function blocksy_child_theme_json_settings() {
    return array(
        'version' => 3,
        'settings' => array(
            'useRootPaddingAwareAlignments' => true,
            'appearanceTools' => true,
        )
    );
}
add_filter('wp_theme_json_data_theme', function($theme_json) {
    $new_data = $theme_json->get_data();
    
    // Ensure our child theme settings take priority
    $new_data['settings']['useRootPaddingAwareAlignments'] = true;
    
    return new WP_Theme_JSON_Data($new_data, 'theme');
});

// Register custom blocks
function blocksy_child_register_blocks() {
    // Register Browse Rooms Block
    register_block_type(get_stylesheet_directory() . '/blocks/browse-rooms');
}
add_action('init', 'blocksy_child_register_blocks');

// Enqueue block editor assets
function blocksy_child_enqueue_block_editor_assets() {
    wp_enqueue_script(
        'blocksy-child-blocks',
        get_stylesheet_directory_uri() . '/blocks/browse-rooms/index.js',
        array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-block-editor', 'wp-components'),
        wp_get_theme()->get('Version'),
        true
    );
}
add_action('enqueue_block_editor_assets', 'blocksy_child_enqueue_block_editor_assets');

// Register block patterns
function blocksy_child_register_patterns() {
    // Register pattern category
    register_block_pattern_category(
        'blocksy-child',
        array('label' => __('Blocksy Child Patterns', 'blocksy-child'))
    );
}
add_action('init', 'blocksy_child_register_patterns');

// Add your custom functions below this line
