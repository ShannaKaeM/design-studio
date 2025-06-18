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
                'button-base' => '.s-btn',
                'button-primary' => '.s-btn--primary',
                'button-secondary' => '.s-btn--secondary', 
                'button-outline' => '.s-btn--outline',
                'button-small' => '.s-btn--small',
                'button-large' => '.s-btn--large',
                'button-hover' => '.s-btn:hover',
                'card-base' => '.s-card',
                'card-title' => '.s-card__title',
                'card-content' => '.s-card__content',
                'card-header' => '.s-card__header',
                'card-footer' => '.s-card__footer',
                'hero-section' => '.s-hero',
                'hero-title' => '.s-hero__title',
                'hero-subtitle' => '.s-hero__subtitle',
                'input-base' => '.s-input',
                'input-focus' => '.s-input:focus'
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
        
        // Initialize default component selectors if they don't exist
        $this->init_default_components();
    }
    
    /**
     * Initialize default component selectors
     */
    private function init_default_components() {
        $default_components = [
            'Button Base' => [
                'selector' => '.s-btn',
                'variables' => [
                    'display' => 'inline-flex',
                    'align-items' => 'center',
                    'justify-content' => 'center',
                    'padding' => 'var(--s-space-3) var(--s-space-6)',
                    'border-radius' => 'var(--s-radius-1)',
                    'font-size' => 'var(--s-text-base)',
                    'font-weight' => 'var(--s-font-medium)',
                    'text-decoration' => 'none',
                    'border' => '1px solid transparent',
                    'cursor' => 'pointer',
                    'transition' => 'all 0.2s ease',
                    'gap' => 'var(--s-space-2)'
                ]
            ],
            'Button Primary' => [
                'selector' => '.s-btn--primary',
                'variables' => [
                    'background-color' => 'var(--s-primary)',
                    'color' => 'var(--s-base-lightest)',
                    'border-color' => 'var(--s-primary)'
                ]
            ],
            'Button Primary Hover' => [
                'selector' => '.s-btn--primary:hover',
                'variables' => [
                    'background-color' => 'var(--s-primary-dark)',
                    'border-color' => 'var(--s-primary-dark)',
                    'transform' => 'translateY(-1px)'
                ]
            ],
            'Button Secondary' => [
                'selector' => '.s-btn--secondary',
                'variables' => [
                    'background-color' => 'var(--s-secondary)',
                    'color' => 'var(--s-base-lightest)',
                    'border-color' => 'var(--s-secondary)'
                ]
            ],
            'Button Outline' => [
                'selector' => '.s-btn--outline',
                'variables' => [
                    'background-color' => 'transparent',
                    'color' => 'var(--s-primary)',
                    'border-color' => 'var(--s-primary)'
                ]
            ],
            'Button Outline Hover' => [
                'selector' => '.s-btn--outline:hover',
                'variables' => [
                    'background-color' => 'var(--s-primary)',
                    'color' => 'var(--s-base-lightest)'
                ]
            ],
            'Button Small' => [
                'selector' => '.s-btn--small',
                'variables' => [
                    'padding' => 'var(--s-space-2) var(--s-space-4)',
                    'font-size' => 'var(--s-text-sm)'
                ]
            ],
            'Button Large' => [
                'selector' => '.s-btn--large',
                'variables' => [
                    'padding' => 'var(--s-space-4) var(--s-space-8)',
                    'font-size' => 'var(--s-text-lg)'
                ]
            ],
            'Card Base' => [
                'selector' => '.s-card',
                'variables' => [
                    'background-color' => 'var(--s-base-lightest)',
                    'border-radius' => 'var(--s-radius-2)',
                    'box-shadow' => 'var(--s-shadow-1)',
                    'padding' => 'var(--s-space-6)',
                    'border' => '1px solid var(--s-base-light)'
                ]
            ],
            'Card Hover' => [
                'selector' => '.s-card:hover',
                'variables' => [
                    'box-shadow' => 'var(--s-shadow-2)',
                    'transform' => 'translateY(-2px)',
                    'transition' => 'all 0.3s ease'
                ]
            ],
            'Card Title' => [
                'selector' => '.s-card__title',
                'variables' => [
                    'font-size' => 'var(--s-text-xl)',
                    'font-weight' => 'var(--s-font-semibold)',
                    'color' => 'var(--s-base-darkest)',
                    'margin' => '0'
                ]
            ],
            'Card Content' => [
                'selector' => '.s-card__content',
                'variables' => [
                    'color' => 'var(--s-base-dark)',
                    'line-height' => 'var(--s-leading-relaxed)'
                ]
            ]
        ];
        
        foreach ($default_components as $name => $config) {
            if (!isset($this->selectors[$name])) {
                $this->selectors[$name] = [
                    'name' => $name,
                    'selector' => $config['selector'],
                    'variables' => $config['variables'],
                    'enabled' => true,
                    'created' => current_time('mysql'),
                    'modified' => current_time('mysql'),
                    'type' => 'component'
                ];
            }
        }
        
        // Save if new components were added
        update_option('studio_selectors', $this->selectors);
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
     * Toggle selector enabled state
     */
    public function toggle_selector($name) {
        if (isset($this->selectors[$name])) {
            $this->selectors[$name]['enabled'] = !$this->selectors[$name]['enabled'];
            $this->selectors[$name]['modified'] = time();
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