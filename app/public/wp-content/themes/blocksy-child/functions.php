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
