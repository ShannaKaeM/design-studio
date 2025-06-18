<?php
/**
 * Blocksy Child Theme - S Design System
 * Clean implementation with Daniel's CSS-driven approach
 */

// Basic theme setup
add_action('after_setup_theme', function() {
    add_theme_support('align-wide');
    add_theme_support('editor-styles');
});

// Enqueue parent theme styles
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('blocksy-parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('blocksy-child-style', get_stylesheet_directory_uri() . '/style.css', ['blocksy-parent-style']);
});

// Load S Design System CSS
add_action('wp_enqueue_scripts', function() {
    // S CSS Variables
    wp_enqueue_style(
        's-vars', 
        get_stylesheet_directory_uri() . '/assets/css/s-vars.css',
        [],
        file_exists(get_stylesheet_directory() . '/assets/css/s-vars.css') ? filemtime(get_stylesheet_directory() . '/assets/css/s-vars.css') : '1.0'
    );
    
    // S Utilities (auto-generated)
    $utilities_file = get_stylesheet_directory() . '/assets/css/s-utilities.css';
    if (file_exists($utilities_file)) {
        wp_enqueue_style(
            's-utilities', 
            get_stylesheet_directory_uri() . '/assets/css/s-utilities.css',
            ['s-vars'],
            filemtime($utilities_file)
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
    
    // S Selectors (generated from selector builder)
    $selectors_file = get_stylesheet_directory() . '/assets/css/s-selectors.css';
    if (file_exists($selectors_file)) {
        wp_enqueue_style(
            's-selectors', 
            get_stylesheet_directory_uri() . '/assets/css/s-selectors.css',
            ['s-vars'],
            filemtime($selectors_file)
        );
    }
}, 100); // Higher priority to load after other styles

// Load S Design System CSS in block editor
add_action('enqueue_block_editor_assets', function() {
    // S CSS Variables in editor
    wp_enqueue_style(
        's-vars-editor', 
        get_stylesheet_directory_uri() . '/assets/css/s-vars.css',
        [],
        file_exists(get_stylesheet_directory() . '/assets/css/s-vars.css') ? filemtime(get_stylesheet_directory() . '/assets/css/s-vars.css') : '1.0'
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
    
    // S Selectors in editor
    $selectors_file = get_stylesheet_directory() . '/assets/css/s-selectors.css';
    if (file_exists($selectors_file)) {
        wp_enqueue_style(
            's-selectors-editor', 
            get_stylesheet_directory_uri() . '/assets/css/s-selectors.css',
            ['s-vars-editor'],
            filemtime($selectors_file)
        );
    }
});

// Load enhanced Studio system with Daniel's approach
if (file_exists(get_stylesheet_directory() . '/studio-system/studio-loader-enhanced.php')) {
    require_once get_stylesheet_directory() . '/studio-system/studio-loader-enhanced.php';
}

// Load S Design System color palette integration
if (file_exists(get_stylesheet_directory() . '/inc/s-color-palette.php')) {
    require_once get_stylesheet_directory() . '/inc/s-color-palette.php';
}