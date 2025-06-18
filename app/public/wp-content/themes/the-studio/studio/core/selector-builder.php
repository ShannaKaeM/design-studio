<?php
/**
 * Selector Builder
 * 
 * Build CSS selectors and assign variable groups
 * 
 * @package TheStudio
 */

namespace Studio\Core;

class SelectorBuilder {
    
    /**
     * Instance
     */
    private static $instance = null;
    
    /**
     * Selectors data
     */
    private $selectors = [];
    
    /**
     * Variable groups
     */
    private $variable_groups = [];
    
    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize
     */
    private function init() {
        // Load selectors from database
        $this->load_selectors();
        
        // AJAX handlers
        add_action('wp_ajax_studio_save_selector', [$this, 'ajax_save_selector']);
        add_action('wp_ajax_studio_delete_selector', [$this, 'ajax_delete_selector']);
        add_action('wp_ajax_studio_get_selectors', [$this, 'ajax_get_selectors']);
        add_action('wp_ajax_studio_generate_selector_css', [$this, 'ajax_generate_css']);
        
        // Generate CSS file when selectors change
        add_action('studio_selectors_updated', [$this, 'generate_css_file']);
    }
    
    /**
     * Load selectors from database
     */
    private function load_selectors() {
        $this->selectors = get_option('studio_selectors', []);
        $this->organize_variable_groups();
    }
    
    /**
     * Organize variables into logical groups
     */
    private function organize_variable_groups() {
        // Get scanner from StudioLoader
        $loader = StudioLoader::get_instance();
        $scanner = $loader->get_scanner();
        $all_vars = $scanner->get_variables_by_category(); // Returns all variables when no category specified
        
        // Group variables by prefix
        $groups = [];
        foreach ($all_vars as $var_data) {
            $var_name = $var_data['name'];
            // Extract prefix (e.g., --ts-color, --ts-spacing)
            if (preg_match('/^(--ts-[a-z]+)/', $var_name, $matches)) {
                $prefix = $matches[1];
                if (!isset($groups[$prefix])) {
                    $groups[$prefix] = [
                        'name' => $this->format_group_name($prefix),
                        'variables' => []
                    ];
                }
                $groups[$prefix]['variables'][$var_name] = $var_data;
            }
        }
        
        $this->variable_groups = $groups;
    }
    
    /**
     * Format group name from prefix
     */
    private function format_group_name($prefix) {
        $name = str_replace(['--ts-', '-'], ['', ' '], $prefix);
        return ucfirst($name);
    }
    
    /**
     * Save selector
     */
    public function save_selector($selector_data) {
        $id = $selector_data['id'] ?? uniqid('sel_');
        
        $this->selectors[$id] = [
            'id' => $id,
            'name' => sanitize_text_field($selector_data['name'] ?? ''),
            'selector' => sanitize_text_field($selector_data['selector'] ?? ''),
            'variables' => array_map('sanitize_text_field', $selector_data['variables'] ?? []),
            'description' => sanitize_textarea_field($selector_data['description'] ?? ''),
            'active' => !empty($selector_data['active']),
            'created' => $selector_data['created'] ?? current_time('mysql'),
            'modified' => current_time('mysql')
        ];
        
        update_option('studio_selectors', $this->selectors);
        do_action('studio_selectors_updated');
        
        return $id;
    }
    
    /**
     * Delete selector
     */
    public function delete_selector($id) {
        if (isset($this->selectors[$id])) {
            unset($this->selectors[$id]);
            update_option('studio_selectors', $this->selectors);
            do_action('studio_selectors_updated');
            return true;
        }
        return false;
    }
    
    /**
     * Get all selectors
     */
    public function get_selectors() {
        return $this->selectors;
    }
    
    /**
     * Get variable groups
     */
    public function get_variable_groups() {
        return $this->variable_groups;
    }
    
    /**
     * Generate CSS for selectors
     */
    public function generate_css() {
        $css = "/**\n * Studio Selectors\n * Generated: " . current_time('mysql') . "\n */\n\n";
        
        $loader = StudioLoader::get_instance();
        $scanner = $loader->get_scanner();
        $all_vars = $scanner->get_variables_by_category();
        
        foreach ($this->selectors as $selector_data) {
            if (!$selector_data['active']) continue;
            
            $selector = $selector_data['selector'];
            $variables = $selector_data['variables'];
            
            if (empty($variables)) continue;
            
            $css .= "/* {$selector_data['name']} */\n";
            if (!empty($selector_data['description'])) {
                $css .= "/* {$selector_data['description']} */\n";
            }
            
            $css .= "{$selector} {\n";
            
            foreach ($variables as $var_name) {
                // Find the variable in our array
                $found = false;
                foreach ($all_vars as $var_data) {
                    if ($var_data['name'] === $var_name) {
                        $css .= "    {$var_name}: var({$var_name});\n";
                        $found = true;
                        break;
                    }
                }
            }
            
            $css .= "}\n\n";
        }
        
        return $css;
    }
    
    /**
     * Generate CSS file
     */
    public function generate_css_file() {
        $css = $this->generate_css();
        $file = STUDIO_DIR . '/studio/css/studio-selectors.css';
        
        // Ensure directory exists
        $dir = dirname($file);
        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
        }
        
        file_put_contents($file, $css);
    }
    
    /**
     * AJAX: Save selector
     */
    public function ajax_save_selector() {
        check_ajax_referer('studio_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $selector_data = $_POST['selector'] ?? [];
        $id = $this->save_selector($selector_data);
        
        wp_send_json_success([
            'id' => $id,
            'message' => 'Selector saved successfully'
        ]);
    }
    
    /**
     * AJAX: Delete selector
     */
    public function ajax_delete_selector() {
        check_ajax_referer('studio_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $id = $_POST['id'] ?? '';
        $success = $this->delete_selector($id);
        
        if ($success) {
            wp_send_json_success(['message' => 'Selector deleted']);
        } else {
            wp_send_json_error(['message' => 'Selector not found']);
        }
    }
    
    /**
     * AJAX: Get selectors
     */
    public function ajax_get_selectors() {
        check_ajax_referer('studio_nonce', 'nonce');
        
        wp_send_json_success([
            'selectors' => $this->get_selectors(),
            'groups' => $this->get_variable_groups()
        ]);
    }
    
    /**
     * AJAX: Generate CSS
     */
    public function ajax_generate_css() {
        check_ajax_referer('studio_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $this->generate_css_file();
        
        wp_send_json_success([
            'message' => 'CSS generated successfully',
            'css' => $this->generate_css()
        ]);
    }
}

// Initialize after StudioLoader
add_action('init', function() {
    // Only initialize if StudioLoader exists
    if (class_exists('\Studio\Core\StudioLoader')) {
        SelectorBuilder::get_instance();
    }
}, 30); // Higher priority to run after StudioLoader