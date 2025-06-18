<?php
/**
 * S Variable Scanner
 * Scans CSS files for variables with @control annotations
 */

function s_scan_css_variables($file_path) {
    if (!file_exists($file_path)) {
        return [];
    }
    
    $content = file_get_contents($file_path);
    $variables = [];
    
    // Match ANY variable with @control annotation (not just --s- prefix)
    preg_match_all(
        '/(--[\w-]+):\s*([^;\/]+);\s*\/\*\s*@control:\s*(\w+)(?:\[([^\]]+)\])?\s*\*\//',
        $content,
        $matches,
        PREG_SET_ORDER
    );
    
    foreach ($matches as $match) {
        $var_name = $match[1];
        $var_value = trim($match[2]);
        $control_type = $match[3];
        $control_params = isset($match[4]) ? $match[4] : '';
        
        $variables[$var_name] = [
            'name' => $var_name,
            'value' => $var_value,
            'control' => $control_type,
            'params' => $control_params,
            'label' => s_generate_label($var_name),
            'category' => s_get_variable_category($var_name)
        ];
    }
    
    return $variables;
}

function s_generate_label($var_name) {
    // Remove prefix and convert to title case
    $name = preg_replace('/^--s-/', '', $var_name);
    $name = str_replace(['-', '_'], ' ', $name);
    return ucwords($name);
}

function s_get_variable_category($var_name) {
    // Categories based on S system naming
    if (preg_match('/^--s-(primary|secondary|neutral)/', $var_name)) {
        return 'Brand Colors';
    }
    if (preg_match('/^--s-(base|white|black)/', $var_name)) {
        return 'Base Colors';
    }
    if (preg_match('/^--s-(success|warning|danger|info)/', $var_name)) {
        return 'Semantic Colors';
    }
    if (preg_match('/^--s-text-/', $var_name)) {
        return 'Typography Sizes';
    }
    if (preg_match('/^--s-font-/', $var_name)) {
        return 'Typography';
    }
    if (preg_match('/^--s-space-/', $var_name)) {
        return 'Spacing';
    }
    if (preg_match('/^--s-border-/', $var_name)) {
        return 'Borders';
    }
    if (preg_match('/^--s-radius-/', $var_name)) {
        return 'Border Radius';
    }
    if (preg_match('/^--s-shadow-/', $var_name)) {
        return 'Shadows';
    }
    if (preg_match('/^--s-container-/', $var_name)) {
        return 'Layout';
    }
    if (preg_match('/^--s-hero-/', $var_name)) {
        return 'Hero Component';
    }
    
    return 'Other';
}

function s_scan_all_variables() {
    $variables = [];
    
    // Scan s-vars.css
    $vars_file = get_stylesheet_directory() . '/assets/css/s-vars.css';
    if (file_exists($vars_file)) {
        $variables = array_merge($variables, s_scan_css_variables($vars_file));
    }
    
    // Scan s-hero.css
    $hero_file = get_stylesheet_directory() . '/assets/css/s-hero.css';
    if (file_exists($hero_file)) {
        $variables = array_merge($variables, s_scan_css_variables($hero_file));
    }
    
    // Scan any other s-*.css files
    $css_files = glob(get_stylesheet_directory() . '/assets/css/s-*.css');
    foreach ($css_files as $file) {
        if ($file !== $vars_file && $file !== $hero_file) {
            $variables = array_merge($variables, s_scan_css_variables($file));
        }
    }
    
    return $variables;
}

function s_get_variables_by_category() {
    $variables = s_scan_all_variables();
    $categorized = [];
    
    foreach ($variables as $var) {
        $category = $var['category'];
        if (!isset($categorized[$category])) {
            $categorized[$category] = [];
        }
        $categorized[$category][] = $var;
    }
    
    // Sort categories for better display
    $category_order = [
        'Brand Colors',
        'Base Colors',
        'Semantic Colors',
        'Typography',
        'Typography Sizes',
        'Spacing',
        'Borders',
        'Border Radius',
        'Shadows',
        'Layout',
        'Hero Component',
        'Other'
    ];
    
    $sorted = [];
    foreach ($category_order as $cat) {
        if (isset($categorized[$cat])) {
            $sorted[$cat] = $categorized[$cat];
        }
    }
    
    return $sorted;
}