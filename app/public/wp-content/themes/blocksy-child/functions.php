<?php
/**
 * Blocksy Child Theme functions and definitions
 */

// Enqueue parent theme stylesheet
function blocksy_child_enqueue_styles() {
    wp_enqueue_style(
        'blocksy-style', 
        get_template_directory_uri() . '/style.css',
        array(), 
        wp_get_theme('blocksy')->get('Version')
    );
    
    wp_enqueue_style(
        'blocksy-child-style',
        get_stylesheet_uri(),
        array('blocksy-style'),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'blocksy_child_enqueue_styles');

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
