<?php
/**
 * DS-Studio Component Library
 * 
 * Manages reusable utility class combinations as components
 */

if (!defined('ABSPATH')) {
    exit;
}

class DS_Studio_Component_Library {
    
    private $components = [];
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_component_styles'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_component_styles'));
        add_action('wp_ajax_ds_studio_save_component', array($this, 'save_component_ajax'));
        add_action('wp_ajax_ds_studio_get_components', array($this, 'get_components_ajax'));
    }
    
    public function init() {
        $this->load_components();
        $this->generate_component_css();
    }
    
    /**
     * Load saved components from database
     */
    private function load_components() {
        $this->components = get_option('ds_studio_components', $this->get_default_components());
    }
    
    /**
     * Get default component patterns
     */
    private function get_default_components() {
        return array(
            'card' => array(
                'name' => 'Card',
                'description' => 'Standard card component with shadow and padding',
                'classes' => 'bg-white rounded-lg shadow-md p-lg border border-gray-200',
                'category' => 'layout',
                'preview' => '<div class="bg-white rounded-lg shadow-md p-lg border border-gray-200">Card Content</div>'
            ),
            'button-primary' => array(
                'name' => 'Primary Button',
                'description' => 'Main call-to-action button',
                'classes' => 'bg-primary text-white px-lg py-md rounded-md font-medium hover:bg-primary-dark transition-colors',
                'category' => 'interactive',
                'preview' => '<button class="bg-primary text-white px-lg py-md rounded-md font-medium">Primary Button</button>'
            ),
            'button-secondary' => array(
                'name' => 'Secondary Button',
                'description' => 'Secondary action button',
                'classes' => 'bg-transparent border border-primary text-primary px-lg py-md rounded-md font-medium hover:bg-primary hover:text-white transition-colors',
                'category' => 'interactive',
                'preview' => '<button class="bg-transparent border border-primary text-primary px-lg py-md rounded-md font-medium">Secondary Button</button>'
            ),
            'hero-section' => array(
                'name' => 'Hero Section',
                'description' => 'Large hero section with centered content',
                'classes' => 'bg-gradient-to-r from-primary to-secondary text-white py-5xl px-lg text-center',
                'category' => 'layout',
                'preview' => '<div class="bg-gradient-to-r from-primary to-secondary text-white py-5xl px-lg text-center">Hero Content</div>'
            ),
            'content-container' => array(
                'name' => 'Content Container',
                'description' => 'Standard content container with max width',
                'classes' => 'container-prose mx-auto px-lg py-xl',
                'category' => 'layout',
                'preview' => '<div class="container-prose mx-auto px-lg py-xl">Content Container</div>'
            ),
            'flex-center' => array(
                'name' => 'Flex Center',
                'description' => 'Flexbox container with centered content',
                'classes' => 'flex items-center justify-center gap-md',
                'category' => 'layout',
                'preview' => '<div class="flex items-center justify-center gap-md">Centered Content</div>'
            ),
            'grid-3-col' => array(
                'name' => 'Three Column Grid',
                'description' => 'Responsive three-column grid layout',
                'classes' => 'grid grid-cols-1 md:grid-cols-3 gap-lg',
                'category' => 'layout',
                'preview' => '<div class="grid grid-cols-1 md:grid-cols-3 gap-lg">Grid Layout</div>'
            ),
            'text-heading' => array(
                'name' => 'Page Heading',
                'description' => 'Large page heading with spacing',
                'classes' => 'text-3xl font-heading font-bold text-gray-900 mb-lg',
                'category' => 'typography',
                'preview' => '<h1 class="text-3xl font-heading font-bold text-gray-900 mb-lg">Page Heading</h1>'
            ),
            'text-subheading' => array(
                'name' => 'Section Subheading',
                'description' => 'Medium section heading',
                'classes' => 'text-xl font-semibold text-gray-800 mb-md',
                'category' => 'typography',
                'preview' => '<h2 class="text-xl font-semibold text-gray-800 mb-md">Section Heading</h2>'
            ),
            'text-body' => array(
                'name' => 'Body Text',
                'description' => 'Standard body text with line height',
                'classes' => 'text-base leading-relaxed text-gray-700 mb-md',
                'category' => 'typography',
                'preview' => '<p class="text-base leading-relaxed text-gray-700 mb-md">Body text content</p>'
            ),
            'form-input' => array(
                'name' => 'Form Input',
                'description' => 'Styled form input field',
                'classes' => 'w-full px-md py-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary',
                'category' => 'forms',
                'preview' => '<input class="w-full px-md py-sm border border-gray-300 rounded-md" placeholder="Form Input">'
            ),
            'alert-success' => array(
                'name' => 'Success Alert',
                'description' => 'Success message alert box',
                'classes' => 'bg-green-100 border border-green-400 text-green-700 px-lg py-md rounded-md',
                'category' => 'feedback',
                'preview' => '<div class="bg-green-100 border border-green-400 text-green-700 px-lg py-md rounded-md">Success message</div>'
            ),
            'alert-error' => array(
                'name' => 'Error Alert',
                'description' => 'Error message alert box',
                'classes' => 'bg-red-100 border border-red-400 text-red-700 px-lg py-md rounded-md',
                'category' => 'feedback',
                'preview' => '<div class="bg-red-100 border border-red-400 text-red-700 px-lg py-md rounded-md">Error message</div>'
            )
        );
    }
    
    /**
     * Generate CSS for component shortcuts
     */
    private function generate_component_css() {
        $css_content = "/* DS-Studio Component Library */\n";
        $css_content .= "/* Reusable utility class combinations */\n\n";
        
        foreach ($this->components as $slug => $component) {
            $css_content .= ".{$slug} {\n";
            
            // Parse utility classes and convert to CSS properties
            $classes = explode(' ', $component['classes']);
            foreach ($classes as $class) {
                $css_content .= "  /* Apply: {$class} */\n";
            }
            
            $css_content .= "}\n\n";
        }
        
        // Alternative: Generate CSS that applies the utility classes
        $css_content .= "/* Component Class Shortcuts */\n";
        foreach ($this->components as $slug => $component) {
            $css_content .= ".component-{$slug} { @apply {$component['classes']}; }\n";
        }
        
        // Write to file
        $upload_dir = wp_upload_dir();
        $css_file_path = $upload_dir['basedir'] . '/ds-studio-components.css';
        
        file_put_contents($css_file_path, $css_content);
        
        // Store the file URL for enqueueing
        update_option('ds_studio_components_css_url', $upload_dir['baseurl'] . '/ds-studio-components.css');
    }
    
    /**
     * Enqueue component styles
     */
    public function enqueue_component_styles() {
        $css_url = get_option('ds_studio_components_css_url');
        
        if ($css_url) {
            wp_enqueue_style(
                'ds-studio-components',
                $css_url,
                array('ds-studio-utilities'),
                filemtime(str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $css_url))
            );
        }
    }
    
    /**
     * Save component via AJAX
     */
    public function save_component_ajax() {
        if (!wp_verify_nonce($_POST['nonce'], 'ds_studio_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('edit_theme_options')) {
            wp_die('Insufficient permissions');
        }
        
        $slug = sanitize_key($_POST['slug']);
        $name = sanitize_text_field($_POST['name']);
        $description = sanitize_text_field($_POST['description']);
        $classes = sanitize_text_field($_POST['classes']);
        $category = sanitize_text_field($_POST['category']);
        
        $this->components[$slug] = array(
            'name' => $name,
            'description' => $description,
            'classes' => $classes,
            'category' => $category,
            'preview' => '<div class="' . esc_attr($classes) . '">' . esc_html($name) . '</div>'
        );
        
        update_option('ds_studio_components', $this->components);
        $this->generate_component_css();
        
        wp_send_json_success('Component saved successfully');
    }
    
    /**
     * Get components via AJAX
     */
    public function get_components_ajax() {
        if (!wp_verify_nonce($_POST['nonce'], 'ds_studio_nonce')) {
            wp_die('Security check failed');
        }
        
        wp_send_json_success($this->components);
    }
    
    /**
     * Get components by category
     */
    public function get_components_by_category($category = null) {
        if ($category) {
            return array_filter($this->components, function($component) use ($category) {
                return $component['category'] === $category;
            });
        }
        
        return $this->components;
    }
    
    /**
     * Get component classes by slug
     */
    public function get_component_classes($slug) {
        return isset($this->components[$slug]) ? $this->components[$slug]['classes'] : '';
    }
}

// Initialize component library
new DS_Studio_Component_Library();
