<?php
/**
 * Design Token Manager
 * Manages the single source of truth for all design tokens
 */

if (!defined('ABSPATH')) {
    exit;
}

class DS_Studio_Design_Token_Manager {
    
    private $tokens_data;
    
    public function __construct() {
        $this->load_tokens();
        
        // Add AJAX handlers
        add_action('wp_ajax_ds_get_design_tokens', array($this, 'get_design_tokens_ajax'));
        add_action('wp_ajax_ds_save_design_tokens', array($this, 'save_design_tokens_ajax'));
        add_action('wp_ajax_ds_sync_to_theme_json', array($this, 'sync_to_theme_json_ajax'));
        add_action('wp_ajax_ds_clear_studio_cache', array($this, 'clear_studio_cache_ajax'));
    }
    
    /**
     * Load design tokens from Studio database (WordPress options)
     * Studio is the single source of truth
     */
    public function load_tokens() {
        // Load from Studio's own database (WordPress options)
        $studio_tokens = get_option('ds_studio_tokens', null);
        
        if ($studio_tokens) {
            $this->tokens_data = $studio_tokens;
            return $studio_tokens;
        }
        
        // If no Studio tokens exist, do initial hydration from theme.json (one-time only)
        return $this->initial_hydration_from_theme_json();
    }
    
    /**
     * Initial hydration from theme.json (one-time only)
     * This runs only when Studio database is empty
     */
    private function initial_hydration_from_theme_json() {
        $theme_json_path = get_stylesheet_directory() . '/theme.json';
        
        if (!file_exists($theme_json_path)) {
            $this->tokens_data = $this->get_default_tokens();
            $this->save_tokens($this->tokens_data);
            return $this->tokens_data;
        }
        
        $theme_json = json_decode(file_get_contents($theme_json_path), true);
        if (!$theme_json) {
            $this->tokens_data = $this->get_default_tokens();
            $this->save_tokens($this->tokens_data);
            return $this->tokens_data;
        }
        
        // Extract and structure tokens for initial hydration
        $tokens = array(
            'version' => '1.0.0',
            'lastUpdated' => current_time('Y-m-d'),
            'colors' => array(),
            'typography' => array(
                'fontFamilies' => array(),
                'fontSizes' => array()
            ),
            'spacing' => array(
                'scale' => array()
            )
        );

        // Extract colors from palette with deduplication and source attribution
        $unique_colors = array();
        if (isset($theme_json['settings']['color']['palette'])) {
            foreach ($theme_json['settings']['color']['palette'] as $color) {
                $slug = $color['slug'];
                
                // Skip if we already have this color (deduplication)
                if (isset($unique_colors[$slug])) {
                    continue;
                }
                
                // Determine color source
                $source = 'wp-core'; // default
                if (strpos($slug, 'blocksy-') === 0 || strpos(strtolower($color['name']), 'blocksy') !== false) {
                    $source = 'blocksy-theme';
                }
                
                // Structure color data properly for JavaScript
                $unique_colors[$slug] = array(
                    'name' => $color['name'],
                    'slug' => $slug,
                    'value' => $color['color'],
                    'source' => $source
                );
            }
        }
        
        // Convert to indexed array for JavaScript
        $tokens['colors'] = array_values($unique_colors);

        // Extract typography
        if (isset($theme_json['settings']['typography']['fontFamilies'])) {
            foreach ($theme_json['settings']['typography']['fontFamilies'] as $font) {
                $tokens['typography']['fontFamilies'][$font['slug']] = $font['fontFamily'];
            }
        }

        if (isset($theme_json['settings']['typography']['fontSizes'])) {
            foreach ($theme_json['settings']['typography']['fontSizes'] as $size) {
                $tokens['typography']['fontSizes'][$size['slug']] = $size['size'];
            }
        }

        // Extract spacing
        if (isset($theme_json['settings']['spacing']['spacingSizes'])) {
            foreach ($theme_json['settings']['spacing']['spacingSizes'] as $spacing) {
                $tokens['spacing']['scale'][$spacing['slug']] = $spacing['size'];
            }
        }

        // Save to Studio database and auto-sync
        $this->save_tokens($tokens);
        
        return $tokens;
    }
    
    /**
     * Save design tokens to Studio database and auto-sync everywhere
     * Studio is the single source of truth
     */
    public function save_tokens($tokens) {
        // Save to Studio's database (WordPress options)
        update_option('ds_studio_tokens', $tokens);
        $this->tokens_data = $tokens;
        
        // Auto-sync to theme.json (one-way only)
        $this->auto_sync_to_theme_json($tokens);
        
        // Removed Blocksy customizer sync - Studio is the only source now
        
        return true;
    }
    
    /**
     * Automatically sync Studio tokens to theme.json (one-way)
     */
    private function auto_sync_to_theme_json($tokens) {
        $theme_json_path = get_stylesheet_directory() . '/theme.json';
        
        // Load existing theme.json structure
        $theme_json_data = array();
        if (file_exists($theme_json_path)) {
            $existing_theme_json = json_decode(file_get_contents($theme_json_path), true);
            if ($existing_theme_json && is_array($existing_theme_json)) {
                $theme_json_data = $existing_theme_json;
            }
        }
        
        // Ensure basic structure exists
        if (!isset($theme_json_data['settings'])) {
            $theme_json_data['settings'] = array();
        }
        if (!isset($theme_json_data['settings']['color'])) {
            $theme_json_data['settings']['color'] = array();
        }
        
        // Force override the palette with Studio colors (no merging)
        $theme_json_data['settings']['color']['palette'] = $this->build_theme_json_palette($tokens);
        
        // Ensure we disable defaults to make our colors the only ones
        $theme_json_data['settings']['color']['defaultPalette'] = false;
        $theme_json_data['settings']['color']['defaultGradients'] = false;
        $theme_json_data['settings']['color']['defaultDuotone'] = false;
        
        $json_content = json_encode($theme_json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($theme_json_path, $json_content);
        
        error_log('DS Studio: Synced ' . count($this->build_theme_json_palette($tokens)) . ' colors to theme.json');
    }
    
    /**
     * Build theme.json color palette from Studio tokens
     */
    private function build_theme_json_palette($tokens) {
        $palette = array();
        
        if (isset($tokens['colors']) && is_array($tokens['colors'])) {
            foreach ($tokens['colors'] as $color) {
                $palette[] = array(
                    'name' => $color['name'],
                    'slug' => $color['slug'],
                    'color' => $color['value']
                );
            }
        }
        
        return $palette;
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
     * Get all design tokens
     */
    public function get_tokens() {
        return $this->tokens_data;
    }
    
    /**
     * Convert design tokens to WordPress theme.json format
     */
    public function convert_to_theme_json($tokens = null) {
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
                    'designTokens' => $tokens ?: $this->tokens_data
                )
            )
        );
        
        // Convert colors from tokens
        if (isset(($tokens ?: $this->tokens_data)['colors'])) {
            foreach (($tokens ?: $this->tokens_data)['colors'] as $color) {
                $theme_json['settings']['color']['palette'][] = array(
                    'name' => $color['name'],
                    'slug' => $color['slug'],
                    'color' => $color['value']
                );
            }
        }
        
        // Convert typography
        if (isset(($tokens ?: $this->tokens_data)['typography']['fontFamilies'])) {
            foreach (($tokens ?: $this->tokens_data)['typography']['fontFamilies'] as $family_name => $family_value) {
                $theme_json['settings']['typography']['fontFamilies'][] = array(
                    'name' => ucfirst($family_name),
                    'slug' => $family_name,
                    'fontFamily' => $family_value
                );
            }
        }
        
        if (isset(($tokens ?: $this->tokens_data)['typography']['fontSizes'])) {
            foreach (($tokens ?: $this->tokens_data)['typography']['fontSizes'] as $size_name => $size_value) {
                $theme_json['settings']['typography']['fontSizes'][] = array(
                    'name' => ucfirst($size_name),
                    'slug' => $size_name,
                    'size' => $size_value
                );
            }
        }
        
        // Convert spacing
        if (isset(($tokens ?: $this->tokens_data)['spacing']['scale'])) {
            foreach (($tokens ?: $this->tokens_data)['spacing']['scale'] as $spacing_name => $spacing_value) {
                $theme_json['settings']['spacing']['spacingScale'][] = array(
                    'name' => ucfirst($spacing_name),
                    'slug' => $spacing_name,
                    'size' => $spacing_value
                );
            }
        }
        
        return $theme_json;
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
        
        if ($this->save_tokens($tokens_data)) {
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
        
        if ($this->auto_sync_to_theme_json($this->get_tokens())) {
            wp_send_json_success('Design tokens synced to theme.json successfully');
        } else {
            wp_send_json_error('Failed to sync to theme.json');
        }
    }
    
    /**
     * AJAX handler to clear Studio cache (for testing/debugging)
     */
    public function clear_studio_cache_ajax() {
        if (!check_ajax_referer('ds_studio_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }
        
        if (!current_user_can('edit_theme_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        // Clear Studio's database
        delete_option('ds_studio_tokens');
        $this->tokens_data = null;
        
        wp_send_json_success('Studio cache cleared successfully');
    }
}

// Initialize the design token manager
new DS_Studio_Design_Token_Manager();
