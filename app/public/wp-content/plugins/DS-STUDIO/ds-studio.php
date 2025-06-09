<?php
/**
 * Plugin Name: Design System Studio
 * Plugin URI: https://github.com/yourusername/ds-studio
 * Description: Modern UI replacement for WordPress Customizer - Visual theme.json management with live preview
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
        // Enqueue the main JavaScript file
        wp_enqueue_script(
            'ds-studio-editor',
            DS_STUDIO_PLUGIN_URL . 'assets/js/editor.js',
            array('wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data'),
            DS_STUDIO_VERSION,
            true
        );
        
        // Enqueue editor styles
        wp_enqueue_style(
            'ds-studio-editor',
            DS_STUDIO_PLUGIN_URL . 'assets/css/editor.css',
            array(),
            DS_STUDIO_VERSION
        );
        
        // Localize script with data
        wp_localize_script('ds-studio-editor', 'dsStudio', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ds_studio_nonce'),
            'themeJsonPath' => get_stylesheet_directory() . '/theme.json',
            'currentThemeJson' => $this->get_current_theme_json()
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
        
        $theme_json_data = json_decode(stripslashes($_POST['themeJson']), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error('Invalid JSON data');
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
}

// Initialize the plugin
new DS_Studio();
