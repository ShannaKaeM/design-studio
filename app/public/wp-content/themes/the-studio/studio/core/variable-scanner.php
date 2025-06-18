<?php
/**
 * Studio Variable Scanner
 * 
 * Scans CSS files for variables with @control annotations
 * and extracts metadata for control generation
 * 
 * @package TheStudio
 */

namespace Studio\Core;

class VariableScanner {
    
    /**
     * Scanned variables storage
     */
    private $variables = [];
    
    /**
     * Control type patterns
     */
    private $control_patterns = [
        'color'     => '/^color$/i',
        'range'     => '/^range\[([\d.]+),([\d.]+)(?:,([\d.]+))?\]$/i',
        'select'    => '/^select\[([^\]]+)\]$/i',
        'font'      => '/^font$/i',
        'shadow'    => '/^shadow$/i',
        'text'      => '/^text$/i',
    ];
    
    /**
     * Scan CSS file for variables
     */
    public function scan_file($file_path) {
        if (!file_exists($file_path)) {
            return false;
        }
        
        $content = file_get_contents($file_path);
        $this->parse_css_content($content);
        
        return $this->variables;
    }
    
    /**
     * Parse CSS content for variables
     */
    private function parse_css_content($content) {
        // Match CSS custom properties with optional @control comment
        // Pattern: --variable-name: value; /* @control: type */
        $pattern = '/(--([\w-]+)):\s*([^;]+);\s*(?:\/\*\s*@control:\s*([^\*]+)\s*\*\/)?/m';
        
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $variable_name = $match[1];
            $css_property = $match[2];
            $value = trim($match[3]);
            $control_type = isset($match[4]) ? trim($match[4]) : null;
            
            // Extract variable info
            $variable = [
                'name' => $variable_name,
                'property' => $css_property,
                'value' => $value,
                'label' => $this->generate_label($css_property),
                'control' => null,
                'metadata' => []
            ];
            
            // Parse control annotation if present
            if ($control_type) {
                $variable['control'] = $this->parse_control_annotation($control_type);
            }
            
            // Categorize variable
            $variable['category'] = $this->categorize_variable($css_property);
            
            $this->variables[] = $variable;
        }
        
        return $this->variables;
    }
    
    /**
     * Parse control annotation
     */
    private function parse_control_annotation($annotation) {
        $control = [
            'type' => 'text',
            'params' => []
        ];
        
        // Check each control pattern
        foreach ($this->control_patterns as $type => $pattern) {
            if (preg_match($pattern, $annotation, $matches)) {
                $control['type'] = $type;
                
                switch ($type) {
                    case 'range':
                        $control['params'] = [
                            'min' => floatval($matches[1]),
                            'max' => floatval($matches[2]),
                            'step' => isset($matches[3]) ? floatval($matches[3]) : 0.1
                        ];
                        break;
                        
                    case 'select':
                        $options = explode(',', $matches[1]);
                        $control['params'] = [
                            'options' => array_map('trim', $options)
                        ];
                        break;
                }
                
                break;
            }
        }
        
        return $control;
    }
    
    /**
     * Generate human-readable label from variable name
     */
    private function generate_label($property_name) {
        // Remove prefix
        $label = preg_replace('/^--ts-/', '', $property_name);
        
        // Replace hyphens with spaces
        $label = str_replace('-', ' ', $label);
        
        // Capitalize words
        $label = ucwords($label);
        
        return $label;
    }
    
    /**
     * Categorize variable based on name
     */
    private function categorize_variable($property_name) {
        $categories = [
            'color' => '/color|primary|secondary|neutral|success|warning|error|info/',
            'typography' => '/text|font|leading/',
            'spacing' => '/spacing|space/',
            'borders' => '/border|radius/',
            'shadows' => '/shadow/',
            'layout' => '/container|width|height/',
            'components' => '/button|card|hero/'
        ];
        
        foreach ($categories as $category => $pattern) {
            if (preg_match($pattern, $property_name)) {
                return $category;
            }
        }
        
        return 'other';
    }
    
    /**
     * Get variables by category
     */
    public function get_variables_by_category($category = null) {
        if (!$category) {
            return $this->variables;
        }
        
        return array_filter($this->variables, function($var) use ($category) {
            return $var['category'] === $category;
        });
    }
    
    /**
     * Get variable by name
     */
    public function get_variable($name) {
        foreach ($this->variables as $variable) {
            if ($variable['name'] === $name) {
                return $variable;
            }
        }
        
        return null;
    }
    
    /**
     * Save scanned variables to database
     */
    public function save_to_database() {
        update_option('studio_scanned_variables', $this->variables);
        update_option('studio_variables_last_scan', time());
        
        return true;
    }
    
    /**
     * Load variables from database
     */
    public function load_from_database() {
        // Always scan fresh from CSS file to ensure we have current values
        $css_file = STUDIO_DIR . '/studio/css/studio-vars.css';
        
        if (file_exists($css_file)) {
            $this->scan_file($css_file);
        } else {
            // Fallback to database if file doesn't exist
            $this->variables = get_option('studio_scanned_variables', []);
        }
        
        return $this->variables;
    }
    
    /**
     * Check if rescan is needed
     */
    public function needs_rescan($file_path) {
        // Always return false since we're scanning fresh in load_from_database
        return false;
    }
}