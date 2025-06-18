<?php
/**
 * Studio Variable Scanner
 * Scan CSS files for variables and their control definitions
 */

function scan_css_variables($file_path) {
    if (!file_exists($file_path)) {
        return [];
    }
    
    $content = file_get_contents($file_path);
    $variables = [];
    
    // Debug: Check if file is being read
    error_log("Studio Scanner: File size: " . strlen($content) . " bytes");
    
    // Match variables with @control comments - updated regex for your format
    preg_match_all(
        '/--st-([\w-]+):\s*([^;\/]+);\s*\/\*\s*@control:\s*(\w+)(?:\[([^\]]+)\])?\s*\*\//',
        $content,
        $matches,
        PREG_SET_ORDER
    );
    
    // Debug: Check matches
    error_log("Studio Scanner: Found " . count($matches) . " matches");
    
    foreach ($matches as $match) {
        $var_name = '--st-' . $match[1];
        $var_value = trim($match[2]);
        $control_type = $match[3];
        $control_params = isset($match[4]) ? $match[4] : '';
        
        error_log("Studio Scanner: Variable found: $var_name = $var_value ($control_type)");
        
        $variables[$var_name] = [
            'name' => $var_name,
            'value' => $var_value,
            'control' => $control_type,
            'params' => $control_params,
            'label' => ucwords(str_replace(['-', '_'], ' ', $match[1])),
            'category' => get_variable_category($var_name)
        ];
    }
    
    return $variables;
}

function get_variable_category($var_name) {
    if (strpos($var_name, 'primary') !== false || strpos($var_name, 'secondary') !== false) {
        return 'Brand Colors';
    }
    if (strpos($var_name, 'text') !== false) {
        return 'Text Colors';
    }
    if (strpos($var_name, 'bg') !== false || strpos($var_name, 'base') !== false || strpos($var_name, 'neutral') !== false) {
        return 'Background Colors';
    }
    if (strpos($var_name, 'success') !== false || strpos($var_name, 'warning') !== false || strpos($var_name, 'error') !== false || strpos($var_name, 'info') !== false) {
        return 'Semantic Colors';
    }
    if (strpos($var_name, 'space') !== false) {
        return 'Spacing';
    }
    if (strpos($var_name, 'font') !== false || strpos($var_name, 'text') !== false) {
        return 'Typography';
    }
    if (strpos($var_name, 'border') !== false || strpos($var_name, 'radius') !== false) {
        return 'Borders';
    }
    if (strpos($var_name, 'shadow') !== false) {
        return 'Shadows';
    }
    
    return 'Other';
}

function scan_all_studio_variables() {
    $vars_file = get_stylesheet_directory() . '/assets/css/studio-vars.css';
    return scan_css_variables($vars_file);
}

function get_studio_variables_by_category() {
    $variables = scan_all_studio_variables();
    $categorized = [];
    
    foreach ($variables as $var) {
        $category = $var['category'];
        if (!isset($categorized[$category])) {
            $categorized[$category] = [];
        }
        $categorized[$category][] = $var;
    }
    
    return $categorized;
}
