<?php
/**
 * Enhanced Studio Selector Builder
 * Universal selector system for applying variables to any element
 */

class StudioSelectorBuilder {
    
    private $selectors = [];
    private $presets = [];
    
    public function __construct() {
        $this->load_selectors();
        $this->init_presets();
    }
    
    /**
     * Initialize selector presets
     */
    private function init_presets() {
        $this->presets = [
            'Typography' => [
                'all-headings' => 'h1, h2, h3, h4, h5, h6',
                'main-headings' => 'h1, h2, h3',
                'sub-headings' => 'h4, h5, h6',
                'body-text' => 'p, li, td',
                'links' => 'a',
                'link-hover' => 'a:hover',
                'blockquotes' => 'blockquote',
                'code' => 'code, pre'
            ],
            'Components' => [
                'buttons' => 'button, .button, .btn',
                'button-hover' => 'button:hover, .button:hover, .btn:hover',
                'cards' => '.card',
                'card-headers' => '.card-header, .card h3',
                'hero-section' => '.hero, .hero-section',
                'hero-title' => '.hero h1, .hero-section h1',
                'navigation' => 'nav, .navigation',
                'nav-links' => 'nav a, .navigation a'
            ],
            'Layout' => [
                'containers' => '.container, .wrapper',
                'sections' => 'section, .section',
                'columns' => '.col, .column',
                'grid-items' => '.grid-item',
                'flex-items' => '.flex-item'
            ],
            'Forms' => [
                'inputs' => 'input[type="text"], input[type="email"], input[type="password"]',
                'textareas' => 'textarea',
                'selects' => 'select',
                'labels' => 'label',
                'form-groups' => '.form-group, .field-group'
            ],
            'States' => [
                'hover' => ':hover',
                'focus' => ':focus',
                'active' => ':active',
                'disabled' => ':disabled',
                'checked' => ':checked'
            ],
            'Pseudo Elements' => [
                'before' => '::before',
                'after' => '::after',
                'first-line' => '::first-line',
                'first-letter' => '::first-letter',
                'selection' => '::selection'
            ]
        ];
    }
    
    /**
     * Load saved selectors
     */
    private function load_selectors() {
        $this->selectors = get_option('studio_selectors', []);
    }
    
    /**
     * Save selectors
     */
    private function save_selectors() {
        update_option('studio_selectors', $this->selectors);
    }
    
    /**
     * Add a new selector rule
     */
    public function add_selector($name, $selector, $variables) {
        $this->selectors[$name] = [
            'name' => $name,
            'selector' => $selector,
            'variables' => $variables,
            'enabled' => true,
            'created' => time(),
            'modified' => time()
        ];
        
        $this->save_selectors();
        return true;
    }
    
    /**
     * Update existing selector
     */
    public function update_selector($name, $data) {
        if (isset($this->selectors[$name])) {
            $this->selectors[$name] = array_merge(
                $this->selectors[$name],
                $data,
                ['modified' => time()]
            );
            $this->save_selectors();
            return true;
        }
        return false;
    }
    
    /**
     * Remove selector
     */
    public function remove_selector($name) {
        if (isset($this->selectors[$name])) {
            unset($this->selectors[$name]);
            $this->save_selectors();
            return true;
        }
        return false;
    }
    
    /**
     * Generate CSS from all selectors
     */
    public function generate_css() {
        $css = [];
        
        foreach ($this->selectors as $selector_data) {
            if (!$selector_data['enabled']) {
                continue;
            }
            
            $selector = $selector_data['selector'];
            $variables = $selector_data['variables'];
            
            if (empty($variables)) {
                continue;
            }
            
            $css[] = $selector . ' {';
            foreach ($variables as $property => $value) {
                // Handle variable references
                if (strpos($value, '--') === 0) {
                    $value = 'var(' . $value . ')';
                }
                $css[] = '    ' . $property . ': ' . $value . ';';
            }
            $css[] = '}';
            $css[] = '';
        }
        
        return implode("\n", $css);
    }
    
    /**
     * Get all presets organized by category
     */
    public function get_presets() {
        return $this->presets;
    }
    
    /**
     * Get all selectors
     */
    public function get_selectors() {
        return $this->selectors;
    }
    
    /**
     * Create selector from preset
     */
    public function create_from_preset($preset_key, $category, $variables) {
        if (isset($this->presets[$category][$preset_key])) {
            $selector = $this->presets[$category][$preset_key];
            return $this->add_selector($preset_key, $selector, $variables);
        }
        return false;
    }
    
    /**
     * Validate CSS selector
     */
    public function validate_selector($selector) {
        // Basic validation
        if (empty($selector)) {
            return false;
        }
        
        // Try to use it in a test
        $test_css = $selector . ' { color: red; }';
        
        // Check for common invalid patterns
        $invalid_patterns = [
            '/^\d/', // Starts with number
            '/[^\w\s\-\.\#\:\[\]\(\)\+\>\~\*\,\=\^\$\|]/', // Invalid characters
        ];
        
        foreach ($invalid_patterns as $pattern) {
            if (preg_match($pattern, $selector)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Parse selector to identify what it targets
     */
    public function parse_selector($selector) {
        $info = [
            'type' => 'unknown',
            'specificity' => 0,
            'targets' => []
        ];
        
        // Identify type
        if (strpos($selector, '.') === 0) {
            $info['type'] = 'class';
        } elseif (strpos($selector, '#') === 0) {
            $info['type'] = 'id';
        } elseif (preg_match('/^[a-zA-Z]/', $selector)) {
            $info['type'] = 'element';
        } elseif (strpos($selector, '[') !== false) {
            $info['type'] = 'attribute';
        }
        
        // Calculate specificity (simplified)
        $info['specificity'] = 
            substr_count($selector, '#') * 100 +
            substr_count($selector, '.') * 10 +
            preg_match_all('/\b[a-z]+\b/', $selector);
        
        return $info;
    }
    
    /**
     * Export selectors as JSON
     */
    public function export_json() {
        return json_encode($this->selectors, JSON_PRETTY_PRINT);
    }
    
    /**
     * Import selectors from JSON
     */
    public function import_json($json) {
        $data = json_decode($json, true);
        if (is_array($data)) {
            $this->selectors = array_merge($this->selectors, $data);
            $this->save_selectors();
            return true;
        }
        return false;
    }
    
    /**
     * Get selector suggestions based on current page
     */
    public function get_smart_suggestions() {
        $suggestions = [];
        
        // Common WordPress classes
        $suggestions['WordPress'] = [
            'post-content' => '.entry-content',
            'post-title' => '.entry-title',
            'widget-areas' => '.widget-area',
            'sidebar' => '.sidebar',
            'comments' => '.comments-area',
            'navigation' => '.site-navigation'
        ];
        
        // GenerateBlocks specific
        $suggestions['GenerateBlocks'] = [
            'gb-container' => '.gb-container',
            'gb-grid' => '.gb-grid',
            'gb-button' => '.gb-button-wrapper .gb-button',
            'gb-headline' => '.gb-headline'
        ];
        
        return $suggestions;
    }
}