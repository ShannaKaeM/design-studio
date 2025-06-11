<?php
/**
 * DS-Studio Component Template System
 * 
 * DRY system for reusable component templates with content injection
 * Separates structure/styling from site-specific content
 */

class DS_Studio_Component_Template_System {
    
    private $templates_dir;
    private $component_templates = [];
    
    public function __construct() {
        $this->templates_dir = plugin_dir_path(__FILE__) . '../templates/components/';
        $this->init();
    }
    
    public function init() {
        add_action('wp_ajax_ds_studio_generate_component', [$this, 'generate_component']);
        add_action('wp_ajax_ds_studio_save_template', [$this, 'save_template']);
        add_action('wp_ajax_ds_studio_get_templates', [$this, 'get_templates']);
        
        // Ensure templates directory exists
        if (!file_exists($this->templates_dir)) {
            wp_mkdir_p($this->templates_dir);
        }
        
        $this->load_default_templates();
    }
    
    /**
     * Load default component templates
     */
    private function load_default_templates() {
        $this->component_templates = [
            'destinations-section' => [
                'name' => 'Destinations Section',
                'description' => 'Travel destinations grid with cards',
                'category' => 'travel',
                'structure' => [
                    'type' => 'section',
                    'class' => 'section-outer',
                    'children' => [
                        [
                            'type' => 'div',
                            'class' => 'section',
                            'children' => [
                                [
                                    'type' => 'header',
                                    'class' => 'section-header',
                                    'children' => [
                                        [
                                            'type' => 'p',
                                            'class' => 'section-subtitle',
                                            'content' => '{{subtitle}}'
                                        ],
                                        [
                                            'type' => 'h2',
                                            'class' => 'section-title',
                                            'content' => '{{title}}'
                                        ]
                                    ]
                                ],
                                [
                                    'type' => 'div',
                                    'class' => 'section-body',
                                    'children' => [
                                        [
                                            'type' => 'repeat',
                                            'data' => '{{cards}}',
                                            'template' => [
                                                'type' => 'article',
                                                'class' => 'card',
                                                'children' => [
                                                    [
                                                        'type' => 'div',
                                                        'class' => 'card-header',
                                                        'children' => [
                                                            [
                                                                'type' => 'img',
                                                                'attributes' => [
                                                                    'src' => '{{image}}',
                                                                    'alt' => '{{alt}}'
                                                                ]
                                                            ]
                                                        ]
                                                    ],
                                                    [
                                                        'type' => 'div',
                                                        'class' => 'card-body',
                                                        'children' => [
                                                            [
                                                                'type' => 'h3',
                                                                'class' => 'card-title',
                                                                'content' => '{{cardTitle}}'
                                                            ]
                                                        ]
                                                    ],
                                                    [
                                                        'type' => 'div',
                                                        'class' => 'card-footer',
                                                        'children' => [
                                                            [
                                                                'type' => 'button',
                                                                'class' => 'button',
                                                                'content' => '{{buttonText}}'
                                                            ]
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'type' => 'div',
                                    'class' => 'section-footer',
                                    'children' => [
                                        [
                                            'type' => 'button',
                                            'class' => 'button button-primary',
                                            'content' => '{{ctaText}}'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'blocks_mapping' => [
                    'section-outer' => 'core/group',
                    'section' => 'core/group', 
                    'section-header' => 'core/group',
                    'section-title' => 'core/heading',
                    'section-subtitle' => 'core/paragraph',
                    'section-body' => 'core/group',
                    'card' => 'core/group',
                    'card-header' => 'core/group',
                    'card-body' => 'core/group',
                    'card-footer' => 'core/group',
                    'card-title' => 'core/heading',
                    'button' => 'core/button',
                    'img' => 'core/image'
                ],
                'default_content' => [
                    'subtitle' => 'WHERE TO GO',
                    'title' => 'Popular Destinations',
                    'cards' => [
                        [
                            'image' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&h=300&fit=crop',
                            'alt' => 'Norway landscape',
                            'cardTitle' => 'Tristique Magna',
                            'buttonText' => '📍 Norway'
                        ],
                        [
                            'image' => 'https://images.unsplash.com/photo-1586348943529-beaae6c28db9?w=400&h=300&fit=crop',
                            'alt' => 'Indonesia tropical beach',
                            'cardTitle' => 'Egestas Quis',
                            'buttonText' => '📍 Indonesia'
                        ]
                    ],
                    'ctaText' => 'View All Destinations'
                ]
            ],
            
            'hero-section' => [
                'name' => 'Hero Section',
                'description' => 'Full-width hero with title, subtitle, and CTA',
                'category' => 'layout',
                'structure' => [
                    'type' => 'section',
                    'class' => 'hero-outer',
                    'children' => [
                        [
                            'type' => 'div',
                            'class' => 'hero',
                            'children' => [
                                [
                                    'type' => 'h1',
                                    'class' => 'hero-title',
                                    'content' => '{{title}}'
                                ],
                                [
                                    'type' => 'p',
                                    'class' => 'hero-subtitle',
                                    'content' => '{{subtitle}}'
                                ],
                                [
                                    'type' => 'button',
                                    'class' => 'button button-primary',
                                    'content' => '{{ctaText}}'
                                ]
                            ]
                        ]
                    ]
                ],
                'blocks_mapping' => [
                    'hero-outer' => 'core/group',
                    'hero' => 'core/group',
                    'hero-title' => 'core/heading',
                    'hero-subtitle' => 'core/paragraph',
                    'button' => 'core/button'
                ],
                'default_content' => [
                    'title' => 'Explore the World',
                    'subtitle' => 'Discover amazing destinations and create unforgettable memories.',
                    'ctaText' => 'Start Your Journey'
                ]
            ]
        ];
    }
    
    /**
     * Generate component from template and content data
     */
    public function generate_component() {
        if (!wp_verify_nonce($_POST['nonce'], 'ds_studio_nonce')) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        $template_id = sanitize_text_field($_POST['template_id']);
        $content_data = json_decode(stripslashes($_POST['content_data']), true);
        
        if (!isset($this->component_templates[$template_id])) {
            wp_send_json_error('Template not found');
            return;
        }
        
        $template = $this->component_templates[$template_id];
        
        // Generate HTML from template and content
        $html = $this->render_template($template['structure'], $content_data);
        
        // Convert to WordPress blocks
        $blocks = $this->convert_to_blocks($template, $html, $content_data);
        
        wp_send_json_success([
            'html' => $html,
            'blocks' => $blocks,
            'template' => $template,
            'content' => $content_data
        ]);
    }
    
    /**
     * Render template structure with content data
     */
    private function render_template($structure, $content_data) {
        $html = '';
        
        if ($structure['type'] === 'repeat') {
            // Handle repeating elements (like cards)
            $data_array = $this->get_nested_value($content_data, $structure['data']);
            if (is_array($data_array)) {
                foreach ($data_array as $item) {
                    $html .= $this->render_template($structure['template'], array_merge($content_data, $item));
                }
            }
            return $html;
        }
        
        // Start tag
        $html .= '<' . $structure['type'];
        
        // Add class if specified
        if (isset($structure['class'])) {
            $html .= ' class="' . $structure['class'] . '"';
        }
        
        // Add attributes if specified
        if (isset($structure['attributes'])) {
            foreach ($structure['attributes'] as $attr => $value) {
                $processed_value = $this->process_template_variables($value, $content_data);
                $html .= ' ' . $attr . '="' . esc_attr($processed_value) . '"';
            }
        }
        
        $html .= '>';
        
        // Add content if specified
        if (isset($structure['content'])) {
            $processed_content = $this->process_template_variables($structure['content'], $content_data);
            $html .= $processed_content;
        }
        
        // Add children if specified
        if (isset($structure['children'])) {
            foreach ($structure['children'] as $child) {
                $html .= $this->render_template($child, $content_data);
            }
        }
        
        // Close tag (skip for self-closing tags)
        if (!in_array($structure['type'], ['img', 'br', 'hr', 'input'])) {
            $html .= '</' . $structure['type'] . '>';
        }
        
        return $html;
    }
    
    /**
     * Process template variables like {{title}}
     */
    private function process_template_variables($text, $content_data) {
        return preg_replace_callback('/\{\{([^}]+)\}\}/', function($matches) use ($content_data) {
            $key = trim($matches[1]);
            return $this->get_nested_value($content_data, $key) ?? $matches[0];
        }, $text);
    }
    
    /**
     * Get nested value from array using dot notation
     */
    private function get_nested_value($array, $key) {
        if (strpos($key, '{{') === 0) {
            $key = trim($key, '{}');
        }
        
        if (isset($array[$key])) {
            return $array[$key];
        }
        
        return null;
    }
    
    /**
     * Convert template to WordPress blocks
     */
    private function convert_to_blocks($template, $html, $content_data) {
        // This would use the existing HTML to Blocks converter
        // but with template-aware mapping
        
        $blocks = [];
        
        // For now, return a simplified block structure
        // This would be enhanced to use the full converter
        $blocks[] = [
            'blockName' => 'core/group',
            'attrs' => [
                'className' => 'generated-component ' . str_replace('_', '-', array_keys($this->component_templates)[0])
            ],
            'innerBlocks' => [],
            'innerHTML' => $html,
            'innerContent' => [$html]
        ];
        
        return $blocks;
    }
    
    /**
     * Get all available templates
     */
    public function get_templates() {
        if (!wp_verify_nonce($_POST['nonce'], 'ds_studio_nonce')) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        wp_send_json_success($this->component_templates);
    }
    
    /**
     * Save custom template
     */
    public function save_template() {
        if (!wp_verify_nonce($_POST['nonce'], 'ds_studio_nonce')) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        if (!current_user_can('edit_theme_options')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        $template_id = sanitize_text_field($_POST['template_id']);
        $template_data = json_decode(stripslashes($_POST['template_data']), true);
        
        // Save to file system or database
        $file_path = $this->templates_dir . $template_id . '.json';
        file_put_contents($file_path, json_encode($template_data, JSON_PRETTY_PRINT));
        
        wp_send_json_success('Template saved successfully');
    }
}

// Initialize the component template system
new DS_Studio_Component_Template_System();
