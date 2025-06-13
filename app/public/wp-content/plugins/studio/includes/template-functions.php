<?php
/**
 * DS-Studio Template Functions
 * 
 * Helper functions for using components and utilities in templates
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get component classes by slug
 * 
 * @param string $component_slug The component slug
 * @return string The utility classes for the component
 */
function ds_component($component_slug) {
    static $component_library = null;
    
    if ($component_library === null) {
        $component_library = new Studio_Component_Library();
    }
    
    return $component_library->get_component_classes($component_slug);
}

/**
 * Echo component classes
 * 
 * @param string $component_slug The component slug
 */
function ds_component_class($component_slug) {
    echo esc_attr(ds_component($component_slug));
}

/**
 * Get multiple component classes combined
 * 
 * @param array $component_slugs Array of component slugs
 * @param string $additional_classes Additional classes to append
 * @return string Combined classes
 */
function ds_components($component_slugs, $additional_classes = '') {
    $classes = array();
    
    foreach ($component_slugs as $slug) {
        $component_classes = ds_component($slug);
        if ($component_classes) {
            $classes[] = $component_classes;
        }
    }
    
    if ($additional_classes) {
        $classes[] = $additional_classes;
    }
    
    return implode(' ', $classes);
}

/**
 * Create a component wrapper div
 * 
 * @param string $component_slug The component slug
 * @param string $content The content inside the component
 * @param array $attributes Additional HTML attributes
 * @return string The complete HTML element
 */
function ds_component_wrap($component_slug, $content, $attributes = array()) {
    $classes = ds_component($component_slug);
    
    $attr_string = '';
    foreach ($attributes as $key => $value) {
        if ($key === 'class') {
            $classes .= ' ' . $value;
        } else {
            $attr_string .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }
    }
    
    return '<div class="' . esc_attr($classes) . '"' . $attr_string . '>' . $content . '</div>';
}

/**
 * Shortcode for components
 * 
 * Usage: [ds_component name="card" class="additional-class"]Content[/ds_component]
 */
function ds_component_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'name' => '',
        'class' => '',
        'tag' => 'div'
    ), $atts);
    
    if (empty($atts['name'])) {
        return $content;
    }
    
    $classes = ds_component($atts['name']);
    if ($atts['class']) {
        $classes .= ' ' . $atts['class'];
    }
    
    $tag = sanitize_key($atts['tag']);
    
    return '<' . $tag . ' class="' . esc_attr($classes) . '">' . do_shortcode($content) . '</' . $tag . '>';
}
add_shortcode('ds_component', 'ds_component_shortcode');

/**
 * Component button helper
 * 
 * @param string $text Button text
 * @param string $url Button URL
 * @param string $type Button type (primary, secondary)
 * @param array $attributes Additional attributes
 * @return string Button HTML
 */
function ds_button($text, $url = '#', $type = 'primary', $attributes = array()) {
    $component_slug = 'button-' . $type;
    $classes = ds_component($component_slug);
    
    $attr_string = '';
    foreach ($attributes as $key => $value) {
        if ($key === 'class') {
            $classes .= ' ' . $value;
        } else {
            $attr_string .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }
    }
    
    return '<a href="' . esc_url($url) . '" class="' . esc_attr($classes) . '"' . $attr_string . '>' . esc_html($text) . '</a>';
}

/**
 * Component card helper
 * 
 * @param string $title Card title
 * @param string $content Card content
 * @param string $image_url Optional image URL
 * @param array $attributes Additional attributes
 * @return string Card HTML
 */
function ds_card($title, $content, $image_url = '', $attributes = array()) {
    $classes = ds_component('card');
    
    if (isset($attributes['class'])) {
        $classes .= ' ' . $attributes['class'];
        unset($attributes['class']);
    }
    
    $attr_string = '';
    foreach ($attributes as $key => $value) {
        $attr_string .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
    }
    
    $html = '<div class="' . esc_attr($classes) . '"' . $attr_string . '>';
    
    if ($image_url) {
        $html .= '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($title) . '" class="w-full aspect-16-9 rounded-md mb-md object-cover">';
    }
    
    if ($title) {
        $html .= '<h3 class="' . esc_attr(ds_component('text-subheading')) . '">' . esc_html($title) . '</h3>';
    }
    
    if ($content) {
        $html .= '<div class="' . esc_attr(ds_component('text-body')) . '">' . wp_kses_post($content) . '</div>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Component alert helper
 * 
 * @param string $message Alert message
 * @param string $type Alert type (success, error, warning, info)
 * @param array $attributes Additional attributes
 * @return string Alert HTML
 */
function ds_alert($message, $type = 'info', $attributes = array()) {
    $component_slug = 'alert-' . $type;
    $classes = ds_component($component_slug);
    
    // Fallback for unknown types
    if (!$classes) {
        $classes = 'bg-blue-100 border border-blue-400 text-blue-700 px-lg py-md rounded-md';
    }
    
    if (isset($attributes['class'])) {
        $classes .= ' ' . $attributes['class'];
        unset($attributes['class']);
    }
    
    $attr_string = '';
    foreach ($attributes as $key => $value) {
        $attr_string .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
    }
    
    return '<div class="' . esc_attr($classes) . '"' . $attr_string . '>' . wp_kses_post($message) . '</div>';
}

/**
 * Component grid helper
 * 
 * @param array $items Array of content items
 * @param string $grid_type Grid type (2-col, 3-col, 4-col)
 * @param array $attributes Additional attributes
 * @return string Grid HTML
 */
function ds_grid($items, $grid_type = '3-col', $attributes = array()) {
    $component_slug = 'grid-' . $grid_type;
    $classes = ds_component($component_slug);
    
    // Fallback for unknown grid types
    if (!$classes) {
        $classes = 'grid grid-cols-1 md:grid-cols-3 gap-lg';
    }
    
    if (isset($attributes['class'])) {
        $classes .= ' ' . $attributes['class'];
        unset($attributes['class']);
    }
    
    $attr_string = '';
    foreach ($attributes as $key => $value) {
        $attr_string .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
    }
    
    $html = '<div class="' . esc_attr($classes) . '"' . $attr_string . '>';
    
    foreach ($items as $item) {
        $html .= '<div class="grid-item">' . $item . '</div>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Component section helper
 * 
 * @param string $content Section content
 * @param string $container_type Container type (prose, wide, full)
 * @param array $attributes Additional attributes
 * @return string Section HTML
 */
function ds_section($content, $container_type = 'prose', $attributes = array()) {
    $component_slug = 'content-container';
    $classes = ds_component($component_slug);
    
    // Override container class based on type
    if ($container_type !== 'prose') {
        $classes = str_replace('container-prose', 'container-' . $container_type, $classes);
    }
    
    if (isset($attributes['class'])) {
        $classes .= ' ' . $attributes['class'];
        unset($attributes['class']);
    }
    
    $attr_string = '';
    foreach ($attributes as $key => $value) {
        $attr_string .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
    }
    
    return '<section class="' . esc_attr($classes) . '"' . $attr_string . '>' . $content . '</section>';
}
