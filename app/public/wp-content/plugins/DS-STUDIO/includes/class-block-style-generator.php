<?php
/**
 * Block Style Generator
 * Create custom block styles with utility classes in the block editor
 */

class DS_Studio_Block_Style_Generator {
    
    private $styles = [];
    
    public function __construct() {
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
        add_action('wp_ajax_save_block_style', array($this, 'save_block_style'));
        add_action('wp_ajax_delete_block_style', array($this, 'delete_block_style'));
        add_action('wp_ajax_get_block_styles', array($this, 'get_block_styles'));
        add_action('wp_ajax_get_utility_classes', array($this, 'get_utility_classes'));
        add_action('wp_ajax_update_block_style', array($this, 'update_block_style'));
        add_action('wp_ajax_regenerate_utility_classes', array($this, 'regenerate_utility_classes'));
        add_action('init', array($this, 'load_saved_styles'));
    }
    
    /**
     * Enqueue block editor assets
     */
    public function enqueue_block_editor_assets() {
        wp_enqueue_script(
            'ds-studio-block-styles',
            plugin_dir_url(dirname(__FILE__)) . 'assets/js/block-styles-editor.js',
            ['wp-plugins', 'wp-edit-post', 'wp-components', 'wp-data', 'wp-element', 'wp-i18n'],
            '1.0.0',
            true
        );
        
        wp_localize_script('ds-studio-block-styles', 'dsBlockStyles', [
            'nonce' => wp_create_nonce('ds_block_styles_nonce'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'savedStyles' => $this->get_saved_styles()
        ]);
    }
    
    /**
     * Save block style via AJAX
     */
    public function save_block_style() {
        check_ajax_referer('ds_block_styles_nonce', 'nonce');
        
        if (!current_user_can('edit_theme_options')) {
            wp_die(__('Insufficient permissions', 'ds-studio'));
        }
        
        $style_name = sanitize_text_field($_POST['style_name']);
        $utility_classes = sanitize_text_field($_POST['utility_classes']);
        $custom_css = wp_kses_post($_POST['custom_css']); // Allow CSS in custom_css field
        $description = sanitize_text_field($_POST['description']);
        $style_type = sanitize_text_field($_POST['style_type']) ?: 'utility'; // 'utility', 'css', or 'combined'
        
        if (empty($style_name)) {
            wp_send_json_error(__('Style name is required', 'ds-studio'));
        }
        
        // Validate based on style type
        if ($style_type === 'utility' && empty($utility_classes)) {
            wp_send_json_error(__('Utility classes are required for utility-based styles', 'ds-studio'));
        }
        
        if ($style_type === 'css' && empty($custom_css)) {
            wp_send_json_error(__('Custom CSS is required for CSS-based styles', 'ds-studio'));
        }
        
        if ($style_type === 'combined' && empty($utility_classes) && empty($custom_css)) {
            wp_send_json_error(__('Either utility classes or custom CSS is required', 'ds-studio'));
        }
        
        $styles = $this->get_saved_styles();
        $styles[$style_name] = [
            'classes' => $utility_classes,
            'customCSS' => $custom_css,
            'description' => $description,
            'type' => $style_type,
            'created' => current_time('mysql')
        ];
        
        $this->save_styles_to_theme_json($styles);
        
        wp_send_json_success([
            'message' => __('Block style saved successfully', 'ds-studio'),
            'style' => $styles[$style_name]
        ]);
    }
    
    /**
     * Delete block style via AJAX
     */
    public function delete_block_style() {
        check_ajax_referer('ds_block_styles_nonce', 'nonce');
        
        if (!current_user_can('edit_theme_options')) {
            wp_die(__('Insufficient permissions', 'ds-studio'));
        }
        
        $style_name = sanitize_text_field($_POST['style_name']);
        
        $styles = $this->get_saved_styles();
        if (isset($styles[$style_name])) {
            unset($styles[$style_name]);
            $this->save_styles_to_theme_json($styles);
        }
        
        wp_send_json_success([
            'message' => __('Block style deleted successfully', 'ds-studio')
        ]);
    }
    
    /**
     * Get block styles via AJAX
     */
    public function get_block_styles() {
        check_ajax_referer('ds_block_styles_nonce', 'nonce');
        
        $styles = $this->get_saved_styles();
        
        wp_send_json_success([
            'styles' => $styles
        ]);
    }
    
    /**
     * Get all available utility classes for autocomplete
     */
    public function get_utility_classes() {
        check_ajax_referer('ds_block_styles_nonce', 'nonce');
        
        // Get utility generator instance
        $utility_generator = new DS_Studio_Utility_Generator();
        $available_utilities = $utility_generator->get_available_utilities();
        
        // Remove duplicates and sort
        $available_utilities = array_unique($available_utilities);
        sort($available_utilities);
        
        wp_send_json_success([
            'classes' => $available_utilities
        ]);
    }
    
    /**
     * Update block style via AJAX
     */
    public function update_block_style() {
        check_ajax_referer('ds_block_styles_nonce', 'nonce');
        
        if (!current_user_can('edit_theme_options')) {
            wp_die(__('Insufficient permissions', 'ds-studio'));
        }
        
        $style_name = sanitize_text_field($_POST['style_name']);
        $utility_classes = sanitize_text_field($_POST['utility_classes']);
        $custom_css = wp_kses_post($_POST['custom_css']);
        $description = sanitize_text_field($_POST['description']);
        $style_type = sanitize_text_field($_POST['style_type']) ?: 'utility';
        
        if (empty($style_name)) {
            wp_send_json_error(__('Style name is required', 'ds-studio'));
        }
        
        $styles = $this->get_saved_styles();
        if (isset($styles[$style_name])) {
            $styles[$style_name] = [
                'classes' => $utility_classes,
                'customCSS' => $custom_css,
                'description' => $description,
                'type' => $style_type,
                'created' => $styles[$style_name]['created']
            ];
            
            $this->save_styles_to_theme_json($styles);
            
            wp_send_json_success([
                'message' => __('Block style updated successfully', 'ds-studio'),
                'style' => $styles[$style_name]
            ]);
        } else {
            wp_send_json_error(__('Block style not found', 'ds-studio'));
        }
    }
    
    /**
     * Regenerate utility classes via AJAX
     */
    public function regenerate_utility_classes() {
        check_ajax_referer('ds_block_styles_nonce', 'nonce');
        
        if (!current_user_can('edit_theme_options')) {
            wp_die(__('Insufficient permissions', 'ds-studio'));
        }
        
        // Get utility generator instance and regenerate
        $utility_generator = new DS_Studio_Utility_Generator();
        $success = $utility_generator->regenerate_utilities();
        
        if ($success) {
            // Get fresh utility classes for autocomplete
            $available_utilities = $utility_generator->get_available_utilities();
            
            wp_send_json_success([
                'message' => __('Utility classes regenerated successfully', 'ds-studio'),
                'classes' => $available_utilities
            ]);
        } else {
            wp_send_json_error(__('Failed to regenerate utility classes', 'ds-studio'));
        }
    }
    
    /**
     * Load saved styles and register them
     */
    public function load_saved_styles() {
        $styles = $this->get_saved_styles();
        
        if (!empty($styles)) {
            add_action('wp_enqueue_scripts', array($this, 'enqueue_block_styles'));
            add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_styles'));
        }
    }
    
    /**
     * Enqueue block styles CSS
     */
    public function enqueue_block_styles() {
        $styles = $this->get_saved_styles();
        
        if (empty($styles)) {
            return;
        }
        
        // First, make sure utility CSS is loaded
        $upload_dir = wp_upload_dir();
        $css_file_path = $upload_dir['basedir'] . '/ds-studio/utilities.css';
        $css_file_url = $upload_dir['baseurl'] . '/ds-studio/utilities.css';
        
        if (file_exists($css_file_path)) {
            wp_enqueue_style(
                'ds-studio-utilities',
                $css_file_url,
                [],
                filemtime($css_file_path)
            );
        }
        
        // Now create CSS that maps block style names to utility classes
        $css = "/* DS-Studio Block Styles */\n";
        foreach ($styles as $style_name => $style_data) {
            $utility_classes = trim($style_data['classes']);
            if (!empty($utility_classes)) {
                // Apply all the utility classes to the block style name
                $css .= ".{$style_name} {\n";
                $css .= "  /* Block style applies: {$utility_classes} */\n";
                
                // For now, let's just add a comment and rely on the utility classes being applied directly
                // The real solution is to make sure when someone uses class="card", it also gets the utility classes
                
                $css .= "}\n\n";
            }
            
            // Add custom CSS if available
            if (isset($style_data['customCSS']) && !empty($style_data['customCSS'])) {
                $css .= ".{$style_name} {\n";
                $css .= $style_data['customCSS'];
                $css .= "}\n\n";
            }
        }
        
        wp_add_inline_style('ds-studio-utilities', $css);
    }
    
    /**
     * Get theme.json file path
     */
    private function get_theme_json_path() {
        return get_stylesheet_directory() . '/theme.json';
    }
    
    /**
     * Load theme.json data
     */
    private function load_theme_json() {
        $theme_json_path = $this->get_theme_json_path();
        
        if (!file_exists($theme_json_path)) {
            return [];
        }
        
        $json_content = file_get_contents($theme_json_path);
        return json_decode($json_content, true) ?: [];
    }
    
    /**
     * Save theme.json data
     */
    private function save_theme_json($data) {
        $theme_json_path = $this->get_theme_json_path();
        $json_content = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        return file_put_contents($theme_json_path, $json_content);
    }
    
    /**
     * Get saved block styles from theme.json
     */
    private function get_saved_styles() {
        $theme_json = $this->load_theme_json();
        return $theme_json['settings']['custom']['blockStyles'] ?? [];
    }
    
    /**
     * Save block styles to theme.json
     */
    private function save_styles_to_theme_json($styles) {
        $theme_json = $this->load_theme_json();
        
        // Ensure the structure exists
        if (!isset($theme_json['settings'])) {
            $theme_json['settings'] = [];
        }
        if (!isset($theme_json['settings']['custom'])) {
            $theme_json['settings']['custom'] = [];
        }
        
        // Save block styles
        $theme_json['settings']['custom']['blockStyles'] = $styles;
        
        return $this->save_theme_json($theme_json);
    }
}

// Initialize the class
new DS_Studio_Block_Style_Generator();
