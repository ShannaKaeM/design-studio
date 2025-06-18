<?php
/**
 * Studio Selector Builder
 * Allows targeting any element with CSS variables, not just blocks
 */

class Studio_Selector_Builder {
    
    private $selectors = [];
    private $option_name = 'studio_selector_rules';
    
    public function __construct() {
        $this->load_selectors();
    }
    
    /**
     * Load saved selectors from database
     */
    private function load_selectors() {
        $this->selectors = get_option($this->option_name, []);
    }
    
    /**
     * Save selectors to database
     */
    private function save_selectors() {
        update_option($this->option_name, $this->selectors);
        $this->generate_selector_css();
    }
    
    /**
     * Add a new selector rule
     */
    public function add_selector($selector, $variables, $name = '', $scope = 'global') {
        $id = sanitize_title($name ?: $selector);
        
        $this->selectors[$id] = [
            'selector' => $selector,
            'name' => $name ?: $selector,
            'variables' => $variables,
            'scope' => $scope,
            'active' => true,
            'created' => current_time('mysql')
        ];
        
        $this->save_selectors();
        return $id;
    }
    
    /**
     * Update an existing selector
     */
    public function update_selector($id, $data) {
        if (!isset($this->selectors[$id])) {
            return false;
        }
        
        $this->selectors[$id] = array_merge($this->selectors[$id], $data);
        $this->save_selectors();
        return true;
    }
    
    /**
     * Delete a selector
     */
    public function delete_selector($id) {
        if (!isset($this->selectors[$id])) {
            return false;
        }
        
        unset($this->selectors[$id]);
        $this->save_selectors();
        return true;
    }
    
    /**
     * Get all selectors or a specific one
     */
    public function get_selectors($id = null) {
        if ($id) {
            return $this->selectors[$id] ?? null;
        }
        return $this->selectors;
    }
    
    /**
     * Generate CSS from selector rules
     */
    private function generate_selector_css() {
        $css = "/* Studio Selector Builder - Generated CSS */\n\n";
        
        foreach ($this->selectors as $id => $rule) {
            if (!$rule['active']) continue;
            
            $css .= sprintf("/* %s */\n", $rule['name']);
            
            // Handle scope-specific selectors
            $selector = $rule['selector'];
            if ($rule['scope'] !== 'global') {
                $selector = ".{$rule['scope']} {$selector}";
            }
            
            $css .= "{$selector} {\n";
            
            // Apply variables
            foreach ($rule['variables'] as $var_name => $var_value) {
                // If it's a reference to another variable
                if (strpos($var_value, 'var(') === 0) {
                    $css .= "    {$var_name}: {$var_value};\n";
                } else {
                    $css .= "    {$var_name}: {$var_value};\n";
                }
            }
            
            $css .= "}\n\n";
        }
        
        // Save generated CSS
        $css_file = get_stylesheet_directory() . '/assets/css/studio-selectors.css';
        file_put_contents($css_file, $css);
        
        return $css;
    }
    
    /**
     * Get available CSS variables for selector builder
     */
    public function get_available_variables() {
        if (function_exists('scan_all_studio_variables')) {
            return scan_all_studio_variables();
        }
        return [];
    }
    
    /**
     * Common selector presets
     */
    public function get_selector_presets() {
        return [
            'typography' => [
                'all-headings' => 'h1, h2, h3, h4, h5, h6',
                'all-paragraphs' => 'p',
                'all-links' => 'a',
                'hero-title' => '.hero-section h1',
                'section-title' => '.section-title',
                'card-title' => '.card h3',
            ],
            'components' => [
                'all-buttons' => 'button, .button, .btn',
                'primary-buttons' => '.btn-primary',
                'cards' => '.card',
                'hero-section' => '.hero-section',
                'nav-links' => 'nav a',
            ],
            'blocks' => [
                'gb-containers' => '.gb-container',
                'gb-headlines' => '.gb-headline',
                'gb-buttons' => '.gb-button',
                'block-content' => '.block-content',
            ],
            'scoped' => [
                'header-elements' => 'header',
                'footer-elements' => 'footer',
                'sidebar-elements' => '.sidebar',
                'main-content' => 'main',
            ]
        ];
    }
    
    /**
     * Apply variables to elements matching a selector
     */
    public function apply_variables_to_selector($selector, $variables) {
        $styles = [];
        
        foreach ($variables as $property => $variable) {
            // Map common properties to CSS
            $css_property = $this->map_property_to_css($property);
            
            // If variable is a Studio variable reference
            if (strpos($variable, '--st-') === 0) {
                $styles[] = "{$css_property}: var({$variable})";
            } else {
                $styles[] = "{$css_property}: {$variable}";
            }
        }
        
        return implode('; ', $styles);
    }
    
    /**
     * Map property names to CSS properties
     */
    private function map_property_to_css($property) {
        $mapping = [
            'text' => 'color',
            'bg' => 'background-color',
            'size' => 'font-size',
            'weight' => 'font-weight',
            'spacing' => 'padding',
            'gap' => 'gap',
            'radius' => 'border-radius',
            'shadow' => 'box-shadow',
            'border' => 'border',
        ];
        
        return $mapping[$property] ?? $property;
    }
}

/**
 * Initialize the selector builder
 */
function studio_selector_builder() {
    static $instance = null;
    
    if ($instance === null) {
        $instance = new Studio_Selector_Builder();
    }
    
    return $instance;
}

/**
 * Enqueue selector CSS
 */
add_action('wp_enqueue_scripts', function() {
    $css_file = get_stylesheet_directory_uri() . '/assets/css/studio-selectors.css';
    if (file_exists(get_stylesheet_directory() . '/assets/css/studio-selectors.css')) {
        wp_enqueue_style('studio-selectors', $css_file, ['studio-vars'], filemtime(get_stylesheet_directory() . '/assets/css/studio-selectors.css'));
    }
});

add_action('enqueue_block_editor_assets', function() {
    $css_file = get_stylesheet_directory_uri() . '/assets/css/studio-selectors.css';
    if (file_exists(get_stylesheet_directory() . '/assets/css/studio-selectors.css')) {
        wp_enqueue_style('studio-selectors-editor', $css_file, ['studio-vars-editor'], filemtime(get_stylesheet_directory() . '/assets/css/studio-selectors.css'));
    }
});