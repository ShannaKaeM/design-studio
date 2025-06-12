<?php
/**
 * Plugin Name: Design Studio
 * Plugin URI: https://github.com/yourusername/ds-studio
 * Description: Unified Block Editor panel for design tokens, block styles, and patterns - Single source of truth design system
 * Version: 1.0.0
 * Author: Shanna & Daniel
 * License: GPL v2 or later
 * Text Domain: ds-studio
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('DS_STUDIO_VERSION', '1.0.0');
define('DS_STUDIO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DS_STUDIO_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Include required files
require_once plugin_dir_path(__FILE__) . 'includes/class-design-token-manager.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-block-style-generator.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-html-to-blocks-converter.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-component-template-system.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-generateblocks-integration.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-component-library.php';
require_once plugin_dir_path(__FILE__) . 'includes/template-functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-block-patterns.php';

/**
 * Main DS Studio Class
 */
class DS_Studio {
    
    /**
     * Initialize the plugin
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
        
        add_action('wp_ajax_ds_studio_save_theme_json', array($this, 'save_theme_json'));
        add_action('wp_ajax_ds_studio_get_theme_json', array($this, 'get_theme_json'));
        
        // Add component management AJAX handlers
        add_action('wp_ajax_ds_studio_save_component', array($this, 'save_component_ajax'));
        add_action('wp_ajax_ds_studio_delete_component', array($this, 'delete_component_ajax'));
    }
    
    /**
     * Initialize plugin functionality
     */
    public function init() {
        // Load text domain for translations
        load_plugin_textdomain('ds-studio', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Enqueue block editor assets
     */
    public function enqueue_block_editor_assets() {
        // Enqueue The Studio - Clean Design System Management Interface
        wp_enqueue_script(
            'the-studio',
            DS_STUDIO_PLUGIN_URL . 'assets/js/studio.js',
            array('wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-i18n'),
            '2.0.0',
            true
        );
        
        // Enqueue Studio styles
        wp_enqueue_style(
            'the-studio-styles',
            DS_STUDIO_PLUGIN_URL . 'assets/css/studio.css',
            array(),
            '2.0.0'
        );
        
        // Localize script with AJAX data
        wp_localize_script('the-studio', 'dsStudio', array(
            'nonce' => wp_create_nonce('ds_studio_nonce'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'pluginUrl' => DS_STUDIO_PLUGIN_URL,
            'version' => '2.0.0'
        ));
        
        // Block styles data for backward compatibility
        wp_localize_script('the-studio', 'dsBlockStyles', array(
            'nonce' => wp_create_nonce('ds_block_styles_nonce'),
            'ajaxUrl' => admin_url('admin-ajax.php')
        ));
    }
    
    /**
     * Get current theme.json data
     */
    private function get_current_theme_json() {
        $theme_json_path = get_stylesheet_directory() . '/theme.json';
        
        if (file_exists($theme_json_path)) {
            $content = file_get_contents($theme_json_path);
            return json_decode($content, true);
        }
        
        return $this->get_default_theme_json();
    }
    
    /**
     * Get default theme.json structure
     */
    private function get_default_theme_json() {
        return array(
            '$schema' => 'https://schemas.wp.org/trunk/theme.json',
            'version' => 3,
            'settings' => array(
                'color' => array(
                    'palette' => array()
                ),
                'spacing' => array(
                    'spacingSizes' => array()
                ),
                'typography' => array(
                    'fontSizes' => array(),
                    'fontFamilies' => array()
                ),
                'layout' => array(
                    'contentSize' => '1200px',
                    'wideSize' => '1400px'
                )
            ),
            'styles' => array(
                'elements' => array()
            )
        );
    }
    
    /**
     * AJAX handler to save theme.json
     */
    public function save_theme_json() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ds_studio_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check user permissions
        if (!current_user_can('edit_theme_options')) {
            wp_die('Insufficient permissions');
        }
        
        // Get current theme.json
        $theme_json_data = $this->get_current_theme_json();
        
        // Handle color saving
        if (isset($_POST['color_name']) && isset($_POST['color_value'])) {
            $color_name = sanitize_text_field($_POST['color_name']);
            $color_value = sanitize_text_field($_POST['color_value']);
            $color_slug = sanitize_title($color_name);
            
            // Initialize color palette if it doesn't exist
            if (!isset($theme_json_data['settings']['color']['palette'])) {
                $theme_json_data['settings']['color']['palette'] = array();
            }
            
            // Check if color already exists and update it, or add new one
            $color_exists = false;
            foreach ($theme_json_data['settings']['color']['palette'] as &$color) {
                if ($color['slug'] === $color_slug) {
                    $color['color'] = $color_value;
                    $color['name'] = $color_name;
                    $color_exists = true;
                    break;
                }
            }
            
            if (!$color_exists) {
                $theme_json_data['settings']['color']['palette'][] = array(
                    'slug' => $color_slug,
                    'color' => $color_value,
                    'name' => $color_name
                );
            }
        }
        
        // Handle typography saving
        if (isset($_POST['typography_type']) && isset($_POST['typography_name']) && isset($_POST['typography_value'])) {
            $typography_type = sanitize_text_field($_POST['typography_type']);
            $typography_name = sanitize_text_field($_POST['typography_name']);
            $typography_value = sanitize_text_field($_POST['typography_value']);
            $typography_slug = sanitize_title($typography_name);
            
            // Handle different typography types
            switch ($typography_type) {
                case 'fontSizes':
                    if (!isset($theme_json_data['settings']['typography']['fontSizes'])) {
                        $theme_json_data['settings']['typography']['fontSizes'] = array();
                    }
                    
                    // Check if font size already exists
                    $exists = false;
                    foreach ($theme_json_data['settings']['typography']['fontSizes'] as &$item) {
                        if ($item['slug'] === $typography_slug) {
                            $item['size'] = $typography_value;
                            $item['name'] = $typography_name;
                            $exists = true;
                            break;
                        }
                    }
                    
                    if (!$exists) {
                        $theme_json_data['settings']['typography']['fontSizes'][] = array(
                            'slug' => $typography_slug,
                            'size' => $typography_value,
                            'name' => $typography_name
                        );
                    }
                    break;
                    
                case 'fontFamilies':
                    if (!isset($theme_json_data['settings']['typography']['fontFamilies'])) {
                        $theme_json_data['settings']['typography']['fontFamilies'] = array();
                    }
                    
                    // Check if font family already exists
                    $exists = false;
                    foreach ($theme_json_data['settings']['typography']['fontFamilies'] as &$item) {
                        if ($item['slug'] === $typography_slug) {
                            $item['fontFamily'] = $typography_value;
                            $item['name'] = $typography_name;
                            $exists = true;
                            break;
                        }
                    }
                    
                    if (!$exists) {
                        $theme_json_data['settings']['typography']['fontFamilies'][] = array(
                            'slug' => $typography_slug,
                            'fontFamily' => $typography_value,
                            'name' => $typography_name
                        );
                    }
                    break;
                    
                case 'fontWeights':
                case 'lineHeights':
                case 'letterSpacing':
                case 'textTransforms':
                    // These go in custom settings
                    $custom_key = str_replace('s', '', $typography_type); // Remove trailing 's'
                    if (!isset($theme_json_data['settings']['custom']['typography'][$custom_key])) {
                        $theme_json_data['settings']['custom']['typography'][$custom_key] = array();
                    }
                    
                    // Check if item already exists
                    $exists = false;
                    foreach ($theme_json_data['settings']['custom']['typography'][$custom_key] as &$item) {
                        if ($item['slug'] === $typography_slug) {
                            $item['value'] = $typography_value;
                            $item['name'] = $typography_name;
                            $exists = true;
                            break;
                        }
                    }
                    
                    if (!$exists) {
                        $theme_json_data['settings']['custom']['typography'][$custom_key][] = array(
                            'slug' => $typography_slug,
                            'value' => $typography_value,
                            'name' => $typography_name
                        );
                    }
                    break;
            }
        }
        
        // Handle borders saving
        if (isset($_POST['borders'])) {
            $borders_data = json_decode(stripslashes($_POST['borders']), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                wp_send_json_error('Invalid borders JSON data');
            }
            
            // Ensure custom.borders structure exists
            if (!isset($theme_json_data['custom'])) {
                $theme_json_data['custom'] = array();
            }
            if (!isset($theme_json_data['custom']['borders'])) {
                $theme_json_data['custom']['borders'] = array();
            }
            
            // Update borders data
            if (isset($borders_data['widths'])) {
                $theme_json_data['custom']['borders']['widths'] = $borders_data['widths'];
            }
            if (isset($borders_data['styles'])) {
                $theme_json_data['custom']['borders']['styles'] = $borders_data['styles'];
            }
            if (isset($borders_data['radii'])) {
                $theme_json_data['custom']['borders']['radii'] = $borders_data['radii'];
            }
        }
        
        // Handle full theme.json data (legacy support)
        if (isset($_POST['themeJson'])) {
            $theme_json_data = json_decode(stripslashes($_POST['themeJson']), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                wp_send_json_error('Invalid JSON data');
            }
        }
        
        $theme_json_path = get_stylesheet_directory() . '/theme.json';
        $json_string = json_encode($theme_json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        if (file_put_contents($theme_json_path, $json_string)) {
            wp_send_json_success('Theme.json saved successfully');
        } else {
            wp_send_json_error('Failed to save theme.json');
        }
    }
    
    /**
     * AJAX handler to get theme.json
     */
    public function get_theme_json() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ds_studio_nonce')) {
            wp_die('Security check failed');
        }
        
        wp_send_json_success($this->get_current_theme_json());
    }
    
    /**
     * AJAX handler to save component
     */
    public function save_component_ajax() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ds_studio_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check user permissions
        if (!current_user_can('edit_theme_options')) {
            wp_die('Insufficient permissions');
        }
        
        // TO DO: Implement logic to save component
        wp_send_json_success('Component saved successfully');
    }
    
    /**
     * AJAX handler to delete component
     */
    public function delete_component_ajax() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ds_studio_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check user permissions
        if (!current_user_can('edit_theme_options')) {
            wp_die('Insufficient permissions');
        }
        
        // TO DO: Implement logic to delete component
        wp_send_json_success('Component deleted successfully');
    }
}

// Initialize the plugin
new DS_Studio();
