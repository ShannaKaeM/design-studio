<?php
/**
 * GenerateBlocks Integration
 * 
 * Injects DS-Studio design tokens into GenerateBlocks styling controls
 *
 * @package DS_Studio
 */

if (!defined('ABSPATH')) {
    exit;
}

class DS_Studio_GenerateBlocks_Integration {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        // Debug: Log that class is being instantiated
        error_log('DS_Studio_GenerateBlocks_Integration: Class instantiated');
    }
    
    public function init() {
        // Debug: Log init
        error_log('DS_Studio_GenerateBlocks_Integration: Init called');
        
        // Only run if GenerateBlocks is active
        if (!class_exists('GenerateBlocks')) {
            error_log('DS_Studio_GenerateBlocks_Integration: GenerateBlocks not found');
            return;
        }
        
        error_log('DS_Studio_GenerateBlocks_Integration: GenerateBlocks found, adding hooks');
        
        // Hook into GB's font family filter
        add_filter('generateblocks_typography_font_family_list', array($this, 'inject_theme_font_families'));
        
        // Enqueue our custom script for font size presets
        add_action('admin_enqueue_scripts', array($this, 'enqueue_font_size_presets_script'));
        
        // Hook into WordPress font sizes for UnitControl
        add_filter('block_editor_settings_all', array($this, 'inject_editor_font_sizes'));
    }
    
    /**
     * Enqueue script to add font size presets to GB interface
     */
    public function enqueue_font_size_presets_script() {
        // Debug: Check if we're in the right context
        error_log('DS_Studio_GenerateBlocks_Integration: enqueue_font_size_presets_script called');
        
        // For now, let's enqueue on all admin pages to test
        if (is_admin()) {
            error_log('DS_Studio_GenerateBlocks_Integration: Enqueueing script on admin page');
            
            wp_enqueue_script(
                'ds-studio-gb-font-presets',
                DS_STUDIO_PLUGIN_URL . 'assets/js/gb-font-presets.js',
                array('wp-element', 'wp-components', 'wp-hooks'),
                time(), // Use timestamp for cache busting during development
                true
            );
            
            // Pass font sizes to JavaScript
            $font_sizes = $this->get_theme_json_font_sizes();
            error_log('DS_Studio_GenerateBlocks_Integration: Font sizes for JS: ' . print_r($font_sizes, true));
            
            wp_localize_script(
                'ds-studio-gb-font-presets',
                'dsStudioFontSizes',
                array(
                    'fontSizes' => $font_sizes,
                    'debug' => 'Script loaded successfully!'
                )
            );
            
            // Add admin notice for debugging
            add_action('admin_notices', function() use ($font_sizes) {
                if (current_user_can('manage_options')) {
                    echo '<div class="notice notice-success"><p><strong>DS Studio:</strong> Script enqueued! Font sizes: ' . count($font_sizes) . '</p></div>';
                }
            });
        } else {
            error_log('DS_Studio_GenerateBlocks_Integration: Not on admin page');
        }
    }
    
    /**
     * Inject theme.json font families into GenerateBlocks
     */
    public function inject_theme_font_families($font_families) {
        error_log('DS_Studio_GenerateBlocks_Integration: inject_theme_font_families called');
        
        $theme_json_fonts = $this->get_theme_json_font_families();
        
        error_log('DS_Studio_GenerateBlocks_Integration: Theme fonts found: ' . print_r($theme_json_fonts, true));
        
        if (!empty($theme_json_fonts)) {
            // Add our theme fonts as a new group
            $font_families[] = array(
                'label' => __('Theme Fonts', 'ds-studio'),
                'options' => $theme_json_fonts
            );
        }
        
        error_log('DS_Studio_GenerateBlocks_Integration: Final font families: ' . print_r($font_families, true));
        
        return $font_families;
    }
    
    /**
     * Inject font sizes into block editor settings for UnitControl
     */
    public function inject_editor_font_sizes($settings) {
        error_log('DS_Studio_GenerateBlocks_Integration: inject_editor_font_sizes called');
        
        $theme_json_font_sizes = $this->get_theme_json_font_sizes();
        
        if (!empty($theme_json_font_sizes)) {
            // Format for WordPress editor
            $formatted_sizes = array();
            foreach ($theme_json_font_sizes as $size) {
                $formatted_sizes[] = array(
                    'name' => $size['name'],
                    'slug' => $size['slug'],
                    'size' => $size['size']
                );
            }
            
            $settings['fontSizes'] = $formatted_sizes;
            error_log('DS_Studio_GenerateBlocks_Integration: Added font sizes to editor settings: ' . print_r($formatted_sizes, true));
        }
        
        return $settings;
    }
    
    /**
     * Get font families from theme.json
     */
    private function get_theme_json_font_families() {
        $theme_json = $this->get_theme_json_data();
        
        if (isset($theme_json['settings']['typography']['fontFamilies'])) {
            $families = array();
            foreach ($theme_json['settings']['typography']['fontFamilies'] as $family) {
                $families[] = array(
                    'value' => $family['fontFamily'],
                    'label' => $family['name']
                );
            }
            return $families;
        }
        
        return array();
    }
    
    /**
     * Get font sizes from theme.json
     */
    private function get_theme_json_font_sizes() {
        $theme_json = $this->get_theme_json_data();
        
        if (isset($theme_json['settings']['typography']['fontSizes'])) {
            return $theme_json['settings']['typography']['fontSizes'];
        }
        
        return array();
    }
    
    /**
     * Get theme.json data
     */
    private function get_theme_json_data() {
        static $theme_json_data = null;
        
        if ($theme_json_data === null) {
            $theme_json_file = get_stylesheet_directory() . '/theme.json';
            
            error_log('DS_Studio_GenerateBlocks_Integration: Looking for theme.json at: ' . $theme_json_file);
            
            if (file_exists($theme_json_file)) {
                $theme_json_content = file_get_contents($theme_json_file);
                $theme_json_data = json_decode($theme_json_content, true);
                error_log('DS_Studio_GenerateBlocks_Integration: Theme.json loaded successfully');
            } else {
                $theme_json_data = array();
                error_log('DS_Studio_GenerateBlocks_Integration: Theme.json file not found');
            }
        }
        
        return $theme_json_data;
    }
}

// Initialize the integration
new DS_Studio_GenerateBlocks_Integration();
