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
        add_action('wp_ajax_ds_manual_sync_to_theme_json', array($this, 'manual_sync_to_theme_json_ajax'));
        add_action('wp_ajax_ds_clear_studio_cache', array($this, 'clear_studio_cache_ajax'));
        add_action('wp_ajax_ds_save_category', array($this, 'save_category_ajax'));
        add_action('wp_ajax_ds_delete_category', array($this, 'delete_category_ajax'));
    }
    
    /**
     * Load design tokens from Studio JSON file
     * Studio JSON file is the single source of truth
     */
    public function load_tokens() {
        $json_file_path = plugin_dir_path(dirname(__FILE__)) . 'studio.json';
        
        if (file_exists($json_file_path)) {
            $json_content = file_get_contents($json_file_path);
            $studio_tokens = json_decode($json_content, true);
            
            if ($studio_tokens) {
                $this->tokens_data = $studio_tokens;
                return $studio_tokens;
            }
        }
        
        // If no Studio JSON file exists, create a minimal one
        return $this->create_minimal_studio_file();
    }
    
    /**
     * Initial hydration from theme.json (one-time only)
     * This runs only when Studio database is empty
     */
    private function initial_hydration_from_theme_json() {
        $theme_json_path = get_stylesheet_directory() . '/theme.json';
        
        if (!file_exists($theme_json_path)) {
            $this->tokens_data = $this->get_default_tokens();
            $this->save_tokens();
            return $this->tokens_data;
        }
        
        $theme_json = json_decode(file_get_contents($theme_json_path), true);
        if (!$theme_json) {
            $this->tokens_data = $this->get_default_tokens();
            $this->save_tokens();
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

        // Save to Studio database
        $this->save_tokens($tokens);
        
        return $tokens;
    }
    
    /**
     * Save design tokens to Studio JSON file (NO AUTO-SYNC)
     */
    public function save_tokens() {
        $json_file_path = plugin_dir_path(dirname(__FILE__)) . 'studio.json';
        
        // Update timestamp
        $this->tokens_data['lastUpdated'] = current_time('Y-m-d H:i:s');
        
        // Save to JSON file ONLY - no auto-sync
        $json_content = json_encode($this->tokens_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $result = file_put_contents($json_file_path, $json_content);
        
        if ($result !== false) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Create minimal studio.json file if it doesn't exist
     */
    private function create_minimal_studio_file() {
        $minimal_tokens = array(
            'version' => '1.0.0',
            'lastUpdated' => current_time('Y-m-d H:i:s'),
            'colors' => array(
                'theme' => array(),
                'semantic' => array()
            )
        );
        
        $this->save_tokens();
        return $minimal_tokens;
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
        
        // Convert colors from tokens (new metadata structure)
        if (isset(($tokens ?: $this->tokens_data)['colors'])) {
            $colors = ($tokens ?: $this->tokens_data)['colors'];
            
            // Sort colors by category and order for better organization
            $sorted_colors = [];
            foreach ($colors as $slug => $color) {
                $category = $color['category'] ?? 'theme';
                $order = $color['order'] ?? 999;
                $sorted_colors[] = [
                    'slug' => $slug,
                    'name' => $color['name'] ?? ucfirst(str_replace('-', ' ', $slug)),
                    'value' => $color['value'] ?? '#000000',
                    'category' => $category,
                    'order' => $order
                ];
            }
            
            // Sort by category priority, then by order within category
            $category_priority = ['theme' => 1, 'brand' => 2, 'semantic' => 3, 'neutral' => 4, 'custom' => 5];
            usort($sorted_colors, function($a, $b) use ($category_priority) {
                $a_priority = $category_priority[$a['category']] ?? 999;
                $b_priority = $category_priority[$b['category']] ?? 999;
                
                if ($a_priority === $b_priority) {
                    return $a['order'] - $b['order'];
                }
                return $a_priority - $b_priority;
            });
            
            // Add to theme.json palette
            foreach ($sorted_colors as $color) {
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
     * Manual sync to theme.json
     */
    public function manual_sync_to_theme_json() {
        // Simple test - write to a test file first
        $test_file = get_stylesheet_directory() . '/ds-studio-test.txt';
        file_put_contents($test_file, 'DS Studio sync was called at ' . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
        
        $theme_json_path = get_stylesheet_directory() . '/theme.json';
        
        // Debug: Log the tokens data
        error_log('DS Studio: Starting manual sync to theme.json');
        error_log('DS Studio: Tokens data: ' . print_r($this->tokens_data, true));
        error_log('DS Studio: Theme JSON path: ' . $theme_json_path);
        
        // Load existing theme.json
        $existing_theme_json = [];
        if (file_exists($theme_json_path)) {
            $existing_content = file_get_contents($theme_json_path);
            $existing_theme_json = json_decode($existing_content, true) ?: [];
            error_log('DS Studio: Loaded existing theme.json with ' . count($existing_theme_json) . ' top-level keys');
        } else {
            error_log('DS Studio: theme.json file does not exist at path: ' . $theme_json_path);
        }
        
        // Get our color tokens
        $our_colors = [];
        if (isset($this->tokens_data['colors'])) {
            $colors = $this->tokens_data['colors'];
            error_log('DS Studio: Found ' . count($colors) . ' colors in tokens');
            
            // Sort colors by category and order for better organization
            $sorted_colors = [];
            foreach ($colors as $slug => $color) {
                $category = $color['category'] ?? 'theme';
                $order = $color['order'] ?? 999;
                $sorted_colors[] = [
                    'slug' => $slug,
                    'name' => $color['name'] ?? ucfirst(str_replace('-', ' ', $slug)),
                    'value' => $color['value'] ?? '#000000',
                    'category' => $category,
                    'order' => $order
                ];
            }
            
            // Sort by category priority, then by order within category
            $category_priority = ['theme' => 1, 'brand' => 2, 'semantic' => 3, 'neutral' => 4, 'custom' => 5];
            usort($sorted_colors, function($a, $b) use ($category_priority) {
                $a_priority = $category_priority[$a['category']] ?? 999;
                $b_priority = $category_priority[$b['category']] ?? 999;
                
                if ($a_priority === $b_priority) {
                    return $a['order'] - $b['order'];
                }
                return $a_priority - $b_priority;
            });
            
            // Convert to theme.json format
            foreach ($sorted_colors as $color) {
                $our_colors[] = [
                    'name' => $color['name'],
                    'slug' => $color['slug'],
                    'color' => $color['value']
                ];
            }
            
            error_log('DS Studio: Converted ' . count($our_colors) . ' colors for theme.json');
        } else {
            error_log('DS Studio: No colors found in tokens data');
        }
        
        // Write our colors to test file
        file_put_contents($test_file, 'Found ' . count($our_colors) . ' colors to sync' . "\n", FILE_APPEND);
        
        // Merge with existing theme.json
        if (!isset($existing_theme_json['settings'])) {
            $existing_theme_json['settings'] = [];
        }
        if (!isset($existing_theme_json['settings']['color'])) {
            $existing_theme_json['settings']['color'] = [];
        }
        
        // Update the color palette and settings
        if (!isset($existing_theme_json['settings']['color'])) {
            $existing_theme_json['settings']['color'] = [];
        }
        
        // Essential color settings to make custom palette work
        $existing_theme_json['settings']['color'] = array_merge(
            $existing_theme_json['settings']['color'],
            [
                'custom' => true,
                'customDuotone' => true,
                'customGradient' => true,
                'defaultDuotone' => false,
                'defaultGradients' => false,
                'defaultPalette' => false,
                'palette' => $our_colors
            ]
        );
        
        // Add custom design tokens for reference
        if (!isset($existing_theme_json['settings']['custom'])) {
            $existing_theme_json['settings']['custom'] = [];
        }
        $existing_theme_json['settings']['custom']['designTokens'] = $this->tokens_data;
        
        // Save to theme.json
        $json_content = json_encode($existing_theme_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $result = file_put_contents($theme_json_path, $json_content);
        
        error_log('DS Studio: File write result: ' . ($result !== false ? 'SUCCESS (' . $result . ' bytes)' : 'FAILED'));
        file_put_contents($test_file, 'File write result: ' . ($result !== false ? 'SUCCESS' : 'FAILED') . "\n", FILE_APPEND);
        
        if ($result !== false) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Automatic sync to theme.json whenever Studio tokens are saved
     */
    private function auto_sync_to_theme_json() {
        $theme_json_path = get_stylesheet_directory() . '/theme.json';
        
        // Load existing theme.json
        $existing_theme_json = [];
        if (file_exists($theme_json_path)) {
            $existing_content = file_get_contents($theme_json_path);
            $existing_theme_json = json_decode($existing_content, true) ?: [];
        } else {
            $existing_theme_json = array(
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
        }
        
        // Get our color tokens
        $our_colors = [];
        if (isset($this->tokens_data['colors'])) {
            $colors = $this->tokens_data['colors'];
            
            // Sort colors by category and order for better organization
            $sorted_colors = [];
            foreach ($colors as $slug => $color) {
                $category = $color['category'] ?? 'theme';
                $order = $color['order'] ?? 999;
                $sorted_colors[] = [
                    'slug' => $slug,
                    'name' => $color['name'] ?? ucfirst(str_replace('-', ' ', $slug)),
                    'value' => $color['value'] ?? '#000000',
                    'category' => $category,
                    'order' => $order
                ];
            }
            
            // Sort by category priority, then by order within category
            $category_priority = ['theme' => 1, 'brand' => 2, 'semantic' => 3, 'neutral' => 4, 'custom' => 5];
            usort($sorted_colors, function($a, $b) use ($category_priority) {
                $a_priority = $category_priority[$a['category']] ?? 999;
                $b_priority = $category_priority[$b['category']] ?? 999;
                
                if ($a_priority === $b_priority) {
                    return $a['order'] - $b['order'];
                }
                return $a_priority - $b_priority;
            });
            
            // Convert to theme.json format
            foreach ($sorted_colors as $color) {
                $our_colors[] = [
                    'name' => $color['name'],
                    'slug' => $color['slug'],
                    'color' => $color['value']
                ];
            }
        }
        
        // Update the color palette and settings
        if (!isset($existing_theme_json['settings']['color'])) {
            $existing_theme_json['settings']['color'] = [];
        }
        
        // Essential color settings to make custom palette work
        $existing_theme_json['settings']['color'] = array_merge(
            $existing_theme_json['settings']['color'],
            [
                'custom' => true,
                'customDuotone' => true,
                'customGradient' => true,
                'defaultDuotone' => false,
                'defaultGradients' => false,
                'defaultPalette' => false,
                'palette' => $our_colors
            ]
        );
        
        // Save to theme.json
        $json_content = json_encode($existing_theme_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($theme_json_path, $json_content);
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
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ds_studio_nonce')) {
            wp_send_json_error('Security check failed');
        }

        if (!current_user_can('edit_theme_options')) {
            wp_send_json_error('Permission denied');
        }

        $tokens_data = json_decode(stripslashes($_POST['tokens'] ?? ''), true);
        if (!$tokens_data) {
            wp_send_json_error('Invalid tokens data');
        }

        $this->tokens_data = $tokens_data;
        $result = $this->save_tokens();
        
        if ($result) {
            // Automatically sync to theme.json after saving Studio tokens
            $this->auto_sync_to_theme_json();
            wp_send_json_success('Tokens saved and synced to theme.json');
        } else {
            wp_send_json_error('Failed to save tokens');
        }
    }
    
    /**
     * AJAX handler to manual sync to theme.json
     */
    public function manual_sync_to_theme_json_ajax() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ds_studio_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check user permissions
        if (!current_user_can('edit_theme_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        if ($this->manual_sync_to_theme_json()) {
            wp_send_json_success('Manual sync to theme.json successful');
        } else {
            wp_send_json_error('Failed to manual sync to theme.json');
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
        $json_file_path = plugin_dir_path(dirname(__FILE__)) . 'studio.json';
        if (file_exists($json_file_path)) {
            unlink($json_file_path);
        }
        $this->tokens_data = null;
        
        wp_send_json_success('Studio cache cleared successfully');
    }
    
    /**
     * AJAX handler to save custom category
     */
    public function save_category_ajax() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ds_studio_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check user permissions
        if (!current_user_can('edit_theme_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $category_data = json_decode(stripslashes($_POST['category'] ?? ''), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error('Invalid JSON data');
        }
        
        // Save category data
        // TO DO: implement saving category data
        
        wp_send_json_success('Category saved successfully');
    }
    
    /**
     * AJAX handler to delete custom category
     */
    public function delete_category_ajax() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ds_studio_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check user permissions
        if (!current_user_can('edit_theme_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $category_id = $_POST['category_id'] ?? '';
        
        // Delete category data
        // TO DO: implement deleting category data
        
        wp_send_json_success('Category deleted successfully');
    }
}

// Initialize the design token manager
new DS_Studio_Design_Token_Manager();
