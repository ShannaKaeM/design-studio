<?php
/**
 * Plugin Name: Theme.json → GenerateBlocks Integration
 * Plugin URI: https://github.com/yourusername/theme-json-gb-integration
 * Description: Seamlessly integrates WordPress theme.json design tokens with GenerateBlocks styling controls. Replaces empty GenerateBlocks defaults with your design system values.
 * Version: 1.0.0
 * Author: Shanna & Daniel
 * License: GPL v2 or later
 * Requires at least: 6.0
 * Tested up to: 6.8
 * Requires PHP: 8.0
 * Text Domain: theme-json-gb-integration
 */

if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('TJGB_PLUGIN_VERSION', '1.0.0');
define('TJGB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TJGB_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Theme.json → GenerateBlocks Integration Class
 * 
 * Focuses solely on injecting theme.json design tokens into GenerateBlocks styling controls
 */
class Theme_JSON_GenerateBlocks_Integration {
    
    private static $instance = null;
    private $theme_json_data = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        add_action('wp_footer', array($this, 'debug_output'));
    }
    
    public function init() {
        // Only proceed if GenerateBlocks is active
        if (!class_exists('GenerateBlocks')) {
            error_log('Theme.json GB Integration: GenerateBlocks not found');
            return;
        }
        
        error_log('Theme.json GB Integration: Initializing...');
        
        // Load theme.json data
        $this->load_theme_json();
        
        // Setup hooks
        $this->setup_generateblocks_hooks();
        
        // Enqueue admin scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Debug output to check if plugin is working
     */
    public function debug_output() {
        if (current_user_can('manage_options')) {
            echo '<!-- Theme.json GB Integration: Plugin Active -->';
        }
    }
    
    /**
     * Load and parse theme.json from active child theme
     */
    private function load_theme_json() {
        $theme_json_file = get_stylesheet_directory() . '/theme.json';
        
        if (!file_exists($theme_json_file)) {
            error_log('Theme.json GB Integration: theme.json not found at ' . $theme_json_file);
            return;
        }
        
        $theme_json_content = file_get_contents($theme_json_file);
        $this->theme_json_data = json_decode($theme_json_content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Theme.json GB Integration: JSON decode error - ' . json_last_error_msg());
            $this->theme_json_data = null;
            return;
        }
        
        error_log('Theme.json GB Integration: theme.json loaded successfully');
    }
    
    /**
     * Set up all GenerateBlocks integration hooks
     */
    private function setup_generateblocks_hooks() {
        if (!$this->theme_json_data) {
            error_log('Theme.json GB Integration: No theme.json data, skipping hooks');
            return;
        }
        
        // Inject font families into GenerateBlocks typography controls
        add_filter('generateblocks_typography_font_family_list', array($this, 'inject_font_families'));
        
        // Inject design tokens into GenerateBlocks editor data
        add_filter('generateblocks_editor_data', array($this, 'inject_editor_data'));
        
        // Inject design tokens into WordPress block editor settings
        add_filter('block_editor_settings_all', array($this, 'inject_block_editor_settings'));
        
        // Inject defaults into GenerateBlocks block defaults
        add_filter('generateblocks_defaults', array($this, 'inject_block_defaults'));
        
        error_log('Theme.json GB Integration: Hooks set up successfully');
    }
    
    /**
     * Inject theme.json font families into GenerateBlocks font family list
     */
    public function inject_font_families($font_families) {
        if (!isset($this->theme_json_data['settings']['typography']['fontFamilies'])) {
            return $font_families;
        }
        
        $theme_fonts = array();
        foreach ($this->theme_json_data['settings']['typography']['fontFamilies'] as $font) {
            if (isset($font['name']) && isset($font['fontFamily'])) {
                $theme_fonts[] = array(
                    'label' => $font['name'],
                    'value' => $font['fontFamily']
                );
            }
        }
        
        if (!empty($theme_fonts)) {
            $font_families[] = array(
                'label' => 'Theme Fonts',
                'options' => $theme_fonts
            );
        }
        
        return $font_families;
    }
    
    /**
     * Inject theme.json design tokens into GenerateBlocks editor data
     */
    public function inject_editor_data($editor_data) {
        $editor_data['themeJsonTokens'] = array(
            'colors' => $this->get_color_tokens(),
            'fontSizes' => $this->get_font_size_tokens(),
            'spacing' => $this->get_spacing_tokens(),
            'typography' => $this->get_typography_tokens(),
            'borderRadius' => $this->get_border_radius_tokens()
        );
        
        return $editor_data;
    }
    
    /**
     * Inject theme.json tokens into WordPress block editor settings
     */
    public function inject_block_editor_settings($settings) {
        // Add font size presets to UnitControl
        $font_sizes = $this->get_font_size_tokens();
        if (!empty($font_sizes)) {
            $settings['fontSizes'] = array_merge(
                isset($settings['fontSizes']) ? $settings['fontSizes'] : array(),
                $font_sizes
            );
        }
        
        // Add spacing presets to UnitControl
        $spacing = $this->get_spacing_tokens();
        if (!empty($spacing)) {
            $settings['spacingSizes'] = array_merge(
                isset($settings['spacingSizes']) ? $settings['spacingSizes'] : array(),
                $spacing
            );
        }
        
        return $settings;
    }
    
    /**
     * Inject theme.json defaults into GenerateBlocks block defaults
     */
    public function inject_block_defaults($defaults) {
        // Get primary color and default font size
        $colors = $this->get_color_tokens();
        $font_sizes = $this->get_font_size_tokens();
        
        $primary_color = '';
        if (!empty($colors)) {
            foreach ($colors as $color) {
                if (isset($color['slug']) && $color['slug'] === 'primary') {
                    $primary_color = $color['color'];
                    break;
                }
            }
        }
        
        $default_font_size = '';
        if (!empty($font_sizes)) {
            // Use 'large' or first available font size
            foreach ($font_sizes as $size) {
                if (isset($size['slug']) && $size['slug'] === 'large') {
                    $default_font_size = $size['size'];
                    break;
                }
            }
            if (!$default_font_size && !empty($font_sizes[0])) {
                $default_font_size = $font_sizes[0]['size'];
            }
        }
        
        // Apply defaults to GenerateBlocks blocks
        if ($primary_color) {
            $defaults['container']['textColor'] = $primary_color;
            $defaults['button']['textColor'] = $primary_color;
            $defaults['headline']['textColor'] = $primary_color;
        }
        
        if ($default_font_size) {
            $defaults['container']['fontSize'] = $default_font_size;
            $defaults['button']['fontSize'] = $default_font_size;
            $defaults['headline']['fontSize'] = $default_font_size;
        }
        
        return $defaults;
    }
    
    /**
     * Get color tokens from theme.json
     */
    private function get_color_tokens() {
        if (!isset($this->theme_json_data['settings']['color']['palette'])) {
            return array();
        }
        
        return $this->theme_json_data['settings']['color']['palette'];
    }
    
    /**
     * Get font size tokens from theme.json
     */
    private function get_font_size_tokens() {
        if (!isset($this->theme_json_data['settings']['typography']['fontSizes'])) {
            return array();
        }
        
        return $this->theme_json_data['settings']['typography']['fontSizes'];
    }
    
    /**
     * Get spacing tokens from theme.json
     */
    private function get_spacing_tokens() {
        if (!isset($this->theme_json_data['settings']['spacing']['spacingSizes'])) {
            return array();
        }
        
        return $this->theme_json_data['settings']['spacing']['spacingSizes'];
    }
    
    /**
     * Get typography tokens from theme.json
     */
    private function get_typography_tokens() {
        $typography = array();
        
        if (isset($this->theme_json_data['settings']['typography']['fontFamilies'])) {
            $typography['fontFamilies'] = $this->theme_json_data['settings']['typography']['fontFamilies'];
        }
        
        if (isset($this->theme_json_data['settings']['typography']['fontWeights'])) {
            $typography['fontWeights'] = $this->theme_json_data['settings']['typography']['fontWeights'];
        }
        
        if (isset($this->theme_json_data['settings']['typography']['lineHeights'])) {
            $typography['lineHeights'] = $this->theme_json_data['settings']['typography']['lineHeights'];
        }
        
        return $typography;
    }
    
    /**
     * Get border radius tokens from theme.json
     */
    private function get_border_radius_tokens() {
        if (!isset($this->theme_json_data['settings']['border']['radius'])) {
            return array();
        }
        
        return $this->theme_json_data['settings']['border']['radius'];
    }
    
    /**
     * Enqueue admin scripts for GenerateBlocks integration
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on post editor pages
        if (!in_array($hook, array('post.php', 'post-new.php'))) {
            return;
        }
        
        // Only load if GenerateBlocks is active
        if (!class_exists('GenerateBlocks')) {
            return;
        }
        
        wp_enqueue_script(
            'theme-json-gb-integration',
            TJGB_PLUGIN_URL . 'assets/js/integration.js',
            array('wp-hooks', 'wp-element', 'wp-components'),
            TJGB_PLUGIN_VERSION,
            true
        );
        
        // Pass theme.json tokens to JavaScript
        wp_localize_script('theme-json-gb-integration', 'themeJsonGbIntegration', array(
            'tokens' => array(
                'colors' => $this->get_color_tokens(),
                'fontSizes' => $this->get_font_size_tokens(),
                'spacing' => $this->get_spacing_tokens(),
                'typography' => $this->get_typography_tokens(),
                'borderRadius' => $this->get_border_radius_tokens()
            )
        ));
    }
}

// Initialize the plugin
Theme_JSON_GenerateBlocks_Integration::get_instance();
