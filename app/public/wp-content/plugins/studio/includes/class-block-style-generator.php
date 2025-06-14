<?php
/**
 * Block Style Generator
 * Create custom block styles with utility classes in the block editor
 */

class Studio_Block_Style_Generator {
    
    private $styles = [];
    
    public function __construct() {
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
        add_action('wp_ajax_studio_save_block_style', array($this, 'save_block_style'));
        add_action('wp_ajax_studio_delete_block_style', array($this, 'delete_block_style'));
        add_action('wp_ajax_studio_get_block_styles', array($this, 'get_block_styles'));
        add_action('wp_ajax_studio_update_block_style', array($this, 'update_block_style'));
        add_action('wp_ajax_studio_save_typography_preset', array($this, 'save_typography_preset'));
        add_action('wp_ajax_studio_get_typography_presets', array($this, 'get_typography_presets_ajax'));
        add_action('init', array($this, 'load_saved_styles'));
    }
    
    /**
     * Enqueue block editor assets - DISABLED (integrated into unified editor)
     * 
     * This method is disabled because all block style functionality has been
     * integrated into the unified Design Studio editor (unified-editor.js).
     * The class is kept for its AJAX handlers and backend functionality.
     */
    public function enqueue_block_editor_assets() {
        // This functionality is now handled by unified-editor.js
        return;
    }
    
    /**
     * Save block style via AJAX
     */
    public function save_block_style() {
        check_ajax_referer('studio_nonce', 'nonce');
        
        if (!current_user_can('edit_theme_options')) {
            wp_die(__('Insufficient permissions', 'studio'));
        }
        
        $style_name = sanitize_text_field($_POST['style_name']);
        $utility_classes = sanitize_text_field($_POST['utility_classes']);
        $custom_css = wp_kses_post($_POST['custom_css']); // Allow CSS in custom_css field
        $description = sanitize_text_field($_POST['description']);
        $style_type = sanitize_text_field($_POST['style_type']) ?: 'utility'; // 'utility', 'css', or 'combined'
        
        if (empty($style_name)) {
            wp_send_json_error(__('Style name is required', 'studio'));
        }
        
        // Validate based on style type
        if ($style_type === 'utility' && empty($utility_classes)) {
            wp_send_json_error(__('Utility classes are required for utility-based styles', 'studio'));
        }
        
        if ($style_type === 'css' && empty($custom_css)) {
            wp_send_json_error(__('Custom CSS is required for CSS-based styles', 'studio'));
        }
        
        if ($style_type === 'combined' && empty($utility_classes) && empty($custom_css)) {
            wp_send_json_error(__('Either utility classes or custom CSS is required', 'studio'));
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
            'message' => __('Block style saved successfully', 'studio'),
            'style' => $styles[$style_name]
        ]);
    }
    
    /**
     * Delete block style via AJAX
     */
    public function delete_block_style() {
        check_ajax_referer('studio_nonce', 'nonce');
        
        if (!current_user_can('edit_theme_options')) {
            wp_die(__('Insufficient permissions', 'studio'));
        }
        
        $style_name = sanitize_text_field($_POST['style_name']);
        
        $styles = $this->get_saved_styles();
        if (isset($styles[$style_name])) {
            unset($styles[$style_name]);
            $this->save_styles_to_theme_json($styles);
        }
        
        wp_send_json_success([
            'message' => __('Block style deleted successfully', 'studio')
        ]);
    }
    
    /**
     * Get block styles via AJAX
     */
    public function get_block_styles() {
        check_ajax_referer('studio_nonce', 'nonce');
        
        $block_type = sanitize_text_field($_POST['block_type'] ?? '');
        $all_styles = $this->get_saved_styles();
        
        // Debug: Log what we're getting
        error_log('Studio Debug - All styles: ' . print_r($all_styles, true));
        error_log('Studio Debug - Block type requested: ' . $block_type);
        
        // If no block type specified, return all styles (for Studio UI)
        if (empty($block_type)) {
            // Convert to array format for Studio UI
            $styles_array = [];
            foreach ($all_styles as $key => $style) {
                $style['id'] = $key; // Add the key as ID
                $styles_array[] = $style;
            }
            error_log('Studio Debug - Returning styles array: ' . print_r($styles_array, true));
            wp_send_json_success($styles_array);
            return;
        }
        
        // Filter styles by block type (for block inspector)
        $filtered_styles = [];
        foreach ($all_styles as $key => $style) {
            if (isset($style['blockType']) && $style['blockType'] === $block_type) {
                $style['id'] = $key; // Add the key as ID
                $filtered_styles[] = $style;
            }
        }
        
        error_log('Studio Debug - Returning filtered styles: ' . print_r($filtered_styles, true));
        wp_send_json_success($filtered_styles);
    }
    
    /**
     * Update block style via AJAX
     */
    public function update_block_style() {
        check_ajax_referer('studio_nonce', 'nonce');
        
        if (!current_user_can('edit_theme_options')) {
            wp_die(__('Insufficient permissions', 'studio'));
        }
        
        $id = sanitize_text_field($_POST['id']);
        $name = sanitize_text_field($_POST['name']);
        $label = sanitize_text_field($_POST['label']);
        $block_type = sanitize_text_field($_POST['blockType']);
        $css = wp_kses_post($_POST['css']);
        $description = sanitize_text_field($_POST['description']);
        
        if (empty($id) || empty($name) || empty($label)) {
            wp_send_json_error(__('Required fields are missing', 'studio'));
        }
        
        $styles = $this->get_saved_styles();
        if (isset($styles[$id])) {
            $styles[$id] = [
                'name' => $name,
                'label' => $label,
                'blockType' => $block_type,
                'classes' => "is-style-{$name}",
                'customCSS' => $css,
                'description' => $description,
                'type' => 'css',
                'created' => $styles[$id]['created'] ?? date('Y-m-d H:i:s')
            ];
            
            $this->save_styles_to_theme_json($styles);
            
            wp_send_json_success([
                'message' => __('Block style updated successfully', 'studio'),
                'style' => $styles[$id]
            ]);
        } else {
            wp_send_json_error(__('Block style not found', 'studio'));
        }
    }
    
    /**
     * Save typography preset via AJAX
     */
    public function save_typography_preset() {
        check_ajax_referer('studio_nonce', 'nonce');
        
        if (!current_user_can('edit_theme_options')) {
            wp_die(__('Insufficient permissions', 'studio'));
        }
        
        $preset_name = sanitize_text_field($_POST['preset_name']);
        $preset_data = $_POST['preset_data'];
        
        if (empty($preset_name)) {
            wp_send_json_error(__('Preset name is required', 'studio'));
        }
        
        $typography_presets = $this->get_typography_presets_from_theme_json();
        $typography_presets[$preset_name] = $preset_data;
        
        $this->save_typography_presets_to_theme_json($typography_presets);
        
        wp_send_json_success([
            'message' => __('Typography preset saved successfully', 'studio'),
            'preset' => $typography_presets[$preset_name]
        ]);
    }
    
    /**
     * Get typography presets via AJAX
     */
    public function get_typography_presets_ajax() {
        check_ajax_referer('studio_nonce', 'nonce');
        
        $typography_presets = $this->get_typography_presets_from_theme_json();
        
        wp_send_json_success([
            'presets' => $typography_presets
        ]);
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
        
        // Create CSS for block styles
        $css = "/* Studio Block Styles */\n";
        foreach ($styles as $style_name => $style_data) {
            // Add custom CSS if available
            if (isset($style_data['customCSS']) && !empty($style_data['customCSS'])) {
                // Get the block type without namespace for core blocks
                $block_type = str_replace('/', '-', $style_data['blockType']);
                $block_type = str_replace('core-', '', $block_type);
                
                // For studio blocks, use the full block type
                if (strpos($style_data['blockType'], 'studio/') === 0) {
                    $block_type = str_replace('/', '-', $style_data['blockType']);
                }
                
                // Build the proper CSS selector for block styles
                $selector = ".wp-block-{$block_type}.{$style_data['classes']}";
                
                // For Studio Text block, also apply to the content element
                if ($style_data['blockType'] === 'studio/text') {
                    $selector .= " .studio-text-content";
                }
                
                $css .= "{$selector} {\n";
                $css .= $style_data['customCSS'];
                $css .= "}\n\n";
            }
        }
        
        // Add inline styles to WordPress
        wp_add_inline_style('wp-block-library', $css);
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
    
    /**
     * Get typography presets from theme.json
     */
    private function get_typography_presets_from_theme_json() {
        $theme_json = $this->load_theme_json();
        return $theme_json['settings']['custom']['typographyPresets'] ?? [];
    }
    
    /**
     * Save typography presets to theme.json
     */
    private function save_typography_presets_to_theme_json($presets) {
        $theme_json = $this->load_theme_json();
        
        // Ensure the structure exists
        if (!isset($theme_json['settings'])) {
            $theme_json['settings'] = [];
        }
        if (!isset($theme_json['settings']['custom'])) {
            $theme_json['settings']['custom'] = [];
        }
        
        // Save typography presets
        $theme_json['settings']['custom']['typographyPresets'] = $presets;
        
        return $this->save_theme_json($theme_json);
    }
    
    /**
     * Get saved typography presets
     */
    private function get_saved_typography_presets() {
        return $this->get_typography_presets_from_theme_json();
    }
}

// Initialize the class
new Studio_Block_Style_Generator();
