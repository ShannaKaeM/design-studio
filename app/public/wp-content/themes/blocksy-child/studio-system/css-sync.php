<?php
/**
 * Studio CSS Sync System
 * Saves and bundles all CSS classes, even without @control annotations
 */

class StudioCSSSync {
    
    private $css_classes = [];
    private $css_variables = [];
    private $custom_css = '';
    
    /**
     * Scan CSS for all classes and their properties
     */
    public function scan_css_classes($content) {
        // Extract all class definitions with their properties
        preg_match_all(
            '/\.([a-zA-Z0-9_-]+)(?:\s*,\s*\.([a-zA-Z0-9_-]+))*\s*\{([^}]+)\}/s',
            $content,
            $matches,
            PREG_SET_ORDER
        );
        
        foreach ($matches as $match) {
            $classes = [$match[1]];
            if (!empty($match[2])) {
                $classes[] = $match[2];
            }
            
            $properties = $this->parse_css_properties($match[3]);
            
            foreach ($classes as $class) {
                $this->css_classes[$class] = $properties;
            }
        }
        
        // Also scan for element selectors
        $this->scan_element_selectors($content);
        
        // Scan for complex selectors
        $this->scan_complex_selectors($content);
        
        return $this->css_classes;
    }
    
    /**
     * Parse CSS properties from a rule block
     */
    private function parse_css_properties($properties_string) {
        $properties = [];
        
        // Match property: value pairs
        preg_match_all(
            '/([a-zA-Z-]+)\s*:\s*([^;]+);/s',
            $properties_string,
            $matches,
            PREG_SET_ORDER
        );
        
        foreach ($matches as $match) {
            $property = trim($match[1]);
            $value = trim($match[2]);
            
            // Check if value uses CSS variables
            $uses_variables = [];
            if (preg_match_all('/var\((--[a-zA-Z0-9_-]+)\)/', $value, $var_matches)) {
                $uses_variables = $var_matches[1];
            }
            
            $properties[$property] = [
                'value' => $value,
                'uses_variables' => $uses_variables,
                'editable' => true
            ];
        }
        
        return $properties;
    }
    
    /**
     * Scan for element selectors (p, h1, etc.)
     */
    private function scan_element_selectors($content) {
        preg_match_all(
            '/^([a-zA-Z]+[1-6]?)\s*\{([^}]+)\}/m',
            $content,
            $matches,
            PREG_SET_ORDER
        );
        
        foreach ($matches as $match) {
            $element = $match[1];
            $properties = $this->parse_css_properties($match[2]);
            $this->css_classes['element:' . $element] = $properties;
        }
    }
    
    /**
     * Scan for complex selectors
     */
    private function scan_complex_selectors($content) {
        // Match selectors like .class > element, .class:hover, etc.
        preg_match_all(
            '/([\.#]?[a-zA-Z0-9_-]+(?:\s*[>+~]\s*[a-zA-Z0-9_-]+)*(?::[a-zA-Z-]+)?)\s*\{([^}]+)\}/s',
            $content,
            $matches,
            PREG_SET_ORDER
        );
        
        foreach ($matches as $match) {
            $selector = trim($match[1]);
            if (!isset($this->css_classes[$selector])) {
                $properties = $this->parse_css_properties($match[2]);
                $this->css_classes['selector:' . $selector] = $properties;
            }
        }
    }
    
    /**
     * Save CSS data to database
     */
    public function save_to_database() {
        // Save classes
        update_option('studio_css_classes', $this->css_classes);
        
        // Save variables separately for quick access
        update_option('studio_css_variables', $this->css_variables);
        
        // Save custom CSS
        update_option('studio_custom_css', $this->custom_css);
        
        // Update timestamp
        update_option('studio_css_last_sync', time());
    }
    
    /**
     * Load CSS data from database
     */
    public function load_from_database() {
        $this->css_classes = get_option('studio_css_classes', []);
        $this->css_variables = get_option('studio_css_variables', []);
        $this->custom_css = get_option('studio_custom_css', '');
    }
    
    /**
     * Bundle all CSS into optimized output
     */
    public function bundle_css() {
        $output = [];
        
        // Add CSS variables first
        if (!empty($this->css_variables)) {
            $output[] = ':root {';
            foreach ($this->css_variables as $var_name => $var_data) {
                $output[] = sprintf('    %s: %s;', $var_name, $var_data['value']);
            }
            $output[] = '}';
            $output[] = '';
        }
        
        // Add classes and selectors
        foreach ($this->css_classes as $selector => $properties) {
            // Handle special prefixes
            if (strpos($selector, 'element:') === 0) {
                $selector = str_replace('element:', '', $selector);
            } elseif (strpos($selector, 'selector:') === 0) {
                $selector = str_replace('selector:', '', $selector);
            } else {
                $selector = '.' . $selector;
            }
            
            $output[] = $selector . ' {';
            foreach ($properties as $property => $data) {
                $output[] = sprintf('    %s: %s;', $property, $data['value']);
            }
            $output[] = '}';
            $output[] = '';
        }
        
        // Add custom CSS
        if (!empty($this->custom_css)) {
            $output[] = '/* Custom CSS */';
            $output[] = $this->custom_css;
        }
        
        return implode("\n", $output);
    }
    
    /**
     * Update a specific CSS property
     */
    public function update_property($selector, $property, $value) {
        if (isset($this->css_classes[$selector][$property])) {
            $this->css_classes[$selector][$property]['value'] = $value;
            $this->save_to_database();
            return true;
        }
        return false;
    }
    
    /**
     * Add new CSS rule
     */
    public function add_rule($selector, $properties) {
        $this->css_classes[$selector] = $this->parse_css_properties($properties);
        $this->save_to_database();
    }
    
    /**
     * Remove CSS rule
     */
    public function remove_rule($selector) {
        if (isset($this->css_classes[$selector])) {
            unset($this->css_classes[$selector]);
            $this->save_to_database();
            return true;
        }
        return false;
    }
    
    /**
     * Get all CSS classes organized by type
     */
    public function get_organized_classes() {
        $organized = [
            'classes' => [],
            'elements' => [],
            'complex' => []
        ];
        
        foreach ($this->css_classes as $selector => $properties) {
            if (strpos($selector, 'element:') === 0) {
                $organized['elements'][str_replace('element:', '', $selector)] = $properties;
            } elseif (strpos($selector, 'selector:') === 0) {
                $organized['complex'][str_replace('selector:', '', $selector)] = $properties;
            } else {
                $organized['classes'][$selector] = $properties;
            }
        }
        
        return $organized;
    }
    
    /**
     * Export CSS to file
     */
    public function export_to_file($filename) {
        $css_content = $this->bundle_css();
        $file_path = get_stylesheet_directory() . '/assets/css/' . $filename;
        
        return file_put_contents($file_path, $css_content);
    }
    
    /**
     * Sync from multiple CSS files
     */
    public function sync_from_files($files) {
        foreach ($files as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $this->scan_css_classes($content);
                
                // Also scan for variables
                $scanner = new StudioVariableScanner();
                $variables = $scanner->scan_css_with_controls($content);
                $this->css_variables = array_merge($this->css_variables, $variables);
            }
        }
        
        $this->save_to_database();
    }
}