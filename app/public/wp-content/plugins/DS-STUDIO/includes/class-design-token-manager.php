<?php
/**
 * Design Token Manager
 * Manages the single source of truth for all design tokens
 */

if (!defined('ABSPATH')) {
    exit;
}

class DS_Studio_Design_Token_Manager {
    
    private $tokens_file_path;
    private $tokens_data;
    
    public function __construct() {
        $this->tokens_file_path = DS_STUDIO_PLUGIN_PATH . 'design-tokens.json';
        $this->load_tokens();
        
        // Add AJAX handlers
        add_action('wp_ajax_ds_studio_get_design_tokens', array($this, 'get_design_tokens_ajax'));
        add_action('wp_ajax_ds_studio_save_design_tokens', array($this, 'save_design_tokens_ajax'));
        add_action('wp_ajax_ds_studio_sync_to_theme_json', array($this, 'sync_to_theme_json_ajax'));
    }
    
    /**
     * Load design tokens from JSON file
     */
    private function load_tokens() {
        if (file_exists($this->tokens_file_path)) {
            $json_content = file_get_contents($this->tokens_file_path);
            $this->tokens_data = json_decode($json_content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('DS Studio: Invalid JSON in design-tokens.json');
                $this->tokens_data = $this->get_default_tokens();
            }
        } else {
            $this->tokens_data = $this->get_default_tokens();
            $this->save_tokens();
        }
    }
    
    /**
     * Get default token structure
     */
    private function get_default_tokens() {
        return array(
            'version' => '1.0.0',
            'lastUpdated' => current_time('Y-m-d'),
            'colors' => array(
                'primary' => array(
                    '500' => '#3b82f6'
                ),
                'neutral' => array(
                    '500' => '#6b7280'
                )
            ),
            'typography' => array(
                'fontFamilies' => array(
                    'heading' => 'system-ui, sans-serif',
                    'body' => 'system-ui, sans-serif'
                ),
                'fontSizes' => array(
                    'base' => '1rem'
                )
            ),
            'spacing' => array(
                'scale' => array(
                    '4' => '1rem'
                )
            )
        );
    }
    
    /**
     * Save tokens to JSON file
     */
    private function save_tokens() {
        $this->tokens_data['lastUpdated'] = current_time('Y-m-d');
        $json_content = json_encode($this->tokens_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        return file_put_contents($this->tokens_file_path, $json_content);
    }
    
    /**
     * Get all design tokens
     */
    public function get_tokens() {
        return $this->tokens_data;
    }
    
    /**
     * Update design tokens
     */
    public function update_tokens($new_tokens) {
        $this->tokens_data = $new_tokens;
        return $this->save_tokens();
    }
    
    /**
     * Convert design tokens to WordPress theme.json format
     */
    public function convert_to_theme_json() {
        $theme_json = array(
            'version' => 2,
            'settings' => array(
                'color' => array(
                    'palette' => array()
                ),
                'typography' => array(
                    'fontFamilies' => array(),
                    'fontSizes' => array()
                ),
                'spacing' => array(
                    'spacingScale' => array()
                ),
                'custom' => array(
                    'designTokens' => $this->tokens_data
                )
            )
        );
        
        // Convert colors
        if (isset($this->tokens_data['colors'])) {
            foreach ($this->tokens_data['colors'] as $color_name => $color_values) {
                if (is_array($color_values)) {
                    foreach ($color_values as $shade => $hex) {
                        $theme_json['settings']['color']['palette'][] = array(
                            'name' => ucfirst($color_name) . ' ' . $shade,
                            'slug' => $color_name . '-' . $shade,
                            'color' => $hex
                        );
                    }
                } else {
                    $theme_json['settings']['color']['palette'][] = array(
                        'name' => ucfirst($color_name),
                        'slug' => $color_name,
                        'color' => $color_values
                    );
                }
            }
        }
        
        // Convert typography
        if (isset($this->tokens_data['typography']['fontFamilies'])) {
            foreach ($this->tokens_data['typography']['fontFamilies'] as $family_name => $family_value) {
                $theme_json['settings']['typography']['fontFamilies'][] = array(
                    'name' => ucfirst($family_name),
                    'slug' => $family_name,
                    'fontFamily' => $family_value
                );
            }
        }
        
        if (isset($this->tokens_data['typography']['fontSizes'])) {
            foreach ($this->tokens_data['typography']['fontSizes'] as $size_name => $size_value) {
                $theme_json['settings']['typography']['fontSizes'][] = array(
                    'name' => ucfirst($size_name),
                    'slug' => $size_name,
                    'size' => $size_value
                );
            }
        }
        
        return $theme_json;
    }
    
    /**
     * Sync design tokens to theme.json (one-way)
     */
    public function sync_to_theme_json() {
        $theme_json_path = get_stylesheet_directory() . '/theme.json';
        $theme_json_data = $this->convert_to_theme_json();
        
        // Preserve existing theme.json structure if it exists
        if (file_exists($theme_json_path)) {
            $existing_theme_json = json_decode(file_get_contents($theme_json_path), true);
            if ($existing_theme_json && is_array($existing_theme_json)) {
                // Merge with existing, but DS Studio tokens take precedence
                $theme_json_data = array_merge_recursive($existing_theme_json, $theme_json_data);
            }
        }
        
        $json_content = json_encode($theme_json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        return file_put_contents($theme_json_path, $json_content);
    }
    
    /**
     * AJAX handler to get design tokens
     */
    public function get_design_tokens_ajax() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ds_studio_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        wp_send_json_success($this->get_tokens());
    }
    
    /**
     * AJAX handler to save design tokens
     */
    public function save_design_tokens_ajax() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ds_studio_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check user permissions
        if (!current_user_can('edit_theme_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $tokens_data = json_decode(stripslashes($_POST['tokens'] ?? ''), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error('Invalid JSON data');
        }
        
        if ($this->update_tokens($tokens_data)) {
            // Auto-sync to theme.json after saving
            $this->sync_to_theme_json();
            wp_send_json_success('Design tokens saved and synced successfully');
        } else {
            wp_send_json_error('Failed to save design tokens');
        }
    }
    
    /**
     * AJAX handler to sync tokens to theme.json
     */
    public function sync_to_theme_json_ajax() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ds_studio_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check user permissions
        if (!current_user_can('edit_theme_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        if ($this->sync_to_theme_json()) {
            wp_send_json_success('Design tokens synced to theme.json successfully');
        } else {
            wp_send_json_error('Failed to sync to theme.json');
        }
    }
}

// Initialize the design token manager
new DS_Studio_Design_Token_Manager();
