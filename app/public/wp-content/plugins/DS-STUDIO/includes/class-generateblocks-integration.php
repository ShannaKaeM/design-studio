<?php
/**
 * GenerateBlocks Integration
 * 
 * Injects DS-Studio design tokens into GenerateBlocks styling controls
 * Replaces GB's empty defaults with comprehensive theme.json design system
 *
 * @package DS_Studio
 */

if (!defined('ABSPATH')) {
    exit;
}

class DS_Studio_GenerateBlocks_Integration {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // Only run if GenerateBlocks is active
        if (!class_exists('GenerateBlocks')) {
            return;
        }
        
        // Hook into GenerateBlocks filters
        add_filter('generateblocks_typography_font_family_list', array($this, 'inject_theme_font_families'));
        add_filter('generateblocks_defaults', array($this, 'inject_theme_defaults'));
        add_filter('generateblocks_editor_data', array($this, 'inject_editor_data'));
        
        // Hook into WordPress editor settings
        add_filter('block_editor_settings_all', array($this, 'inject_editor_settings'));
        
        // Enqueue our enhanced integration script
        add_action('admin_enqueue_scripts', array($this, 'enqueue_integration_script'));
        
        // Add AJAX handler for utility class picker
        add_action('wp_ajax_ds_studio_get_utilities_by_category', array($this, 'ajax_get_utilities_by_category'));
    }
    
    /**
     * Inject theme.json defaults into GenerateBlocks block defaults
     */
    public function inject_theme_defaults($defaults) {
        $theme_json = $this->get_theme_json_data();
        
        if (empty($theme_json)) {
            return $defaults;
        }
        
        // Get design tokens
        $colors = $this->get_theme_colors();
        $font_sizes = $this->get_theme_font_sizes();
        $spacing = $this->get_theme_spacing();
        $typography = $this->get_theme_typography();
        
        // Inject defaults into Container block
        if (isset($defaults['container'])) {
            $defaults['container'] = array_merge($defaults['container'], array(
                'backgroundColor' => $colors['primary'] ?? '',
                'textColor' => $colors['base'] ?? '',
                'fontSize' => $font_sizes[2]['size'] ?? '', // Medium size
                'fontFamily' => $typography['fontFamily'] ?? '',
                'fontWeight' => $typography['fontWeight'] ?? '',
            ));
        }
        
        // Inject defaults into Button block
        if (isset($defaults['button'])) {
            $defaults['button'] = array_merge($defaults['button'], array(
                'backgroundColor' => $colors['primary'] ?? '',
                'textColor' => $colors['white'] ?? '',
                'fontSize' => $font_sizes[2]['size'] ?? '', // Medium size
                'fontFamily' => $typography['fontFamily'] ?? '',
                'fontWeight' => '600', // Semi-bold for buttons
            ));
        }
        
        // Inject defaults into Headline block
        if (isset($defaults['headline'])) {
            $defaults['headline'] = array_merge($defaults['headline'], array(
                'textColor' => $colors['base'] ?? '',
                'fontSize' => $font_sizes[4]['size'] ?? '', // Large size
                'fontFamily' => $typography['fontFamily'] ?? '',
                'fontWeight' => '700', // Bold for headlines
            ));
        }
        
        return $defaults;
    }
    
    /**
     * Inject comprehensive design tokens into GenerateBlocks editor data
     */
    public function inject_editor_data($data) {
        $theme_json = $this->get_theme_json_data();
        
        if (empty($theme_json)) {
            return $data;
        }
        
        // Add all design tokens to editor data
        $data['dsStudioTokens'] = array(
            'colors' => $this->get_theme_colors(),
            'fontSizes' => $this->get_theme_font_sizes(),
            'spacing' => $this->get_theme_spacing(),
            'typography' => $this->get_theme_typography(),
            'borderRadius' => $this->get_border_radius_tokens(),
            'shadows' => $this->get_shadow_tokens(),
        );
        
        return $data;
    }
    
    /**
     * Inject design tokens into WordPress block editor settings
     */
    public function inject_editor_settings($settings) {
        $theme_json = $this->get_theme_json_data();
        
        if (empty($theme_json)) {
            return $settings;
        }
        
        // Add font size presets for UnitControl
        $font_sizes = $this->get_theme_font_sizes();
        if (!empty($font_sizes)) {
            $settings['fontSizes'] = $font_sizes;
        }
        
        // Add spacing presets for UnitControl
        $spacing = $this->get_theme_spacing();
        if (!empty($spacing)) {
            $settings['spacingSizes'] = $spacing;
        }
        
        return $settings;
    }
    
    /**
     * Enqueue comprehensive integration script
     */
    public function enqueue_integration_script() {
        if (!is_admin()) {
            return;
        }
        
        wp_enqueue_script(
            'ds-studio-gb-integration',
            DS_STUDIO_PLUGIN_URL . 'assets/js/gb-integration.js',
            array('wp-element', 'wp-components', 'wp-hooks', 'wp-data'),
            DS_STUDIO_VERSION,
            true
        );
        
        // Pass all design tokens to JavaScript
        wp_localize_script(
            'ds-studio-gb-integration',
            'dsStudioTokens',
            array(
                'colors' => $this->get_theme_colors(),
                'fontSizes' => $this->get_theme_font_sizes(),
                'spacing' => $this->get_theme_spacing(),
                'typography' => $this->get_theme_typography(),
                'borderRadius' => $this->get_border_radius_tokens(),
                'shadows' => $this->get_shadow_tokens(),
                'nonce' => wp_create_nonce('ds_studio_utilities_nonce'),
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'debug' => true
            )
        );
    }
    
    /**
     * Inject theme.json font families into GenerateBlocks
     */
    public function inject_theme_font_families($font_families) {
        $theme_json_fonts = $this->get_theme_font_families();
        
        if (!empty($theme_json_fonts)) {
            $font_families[] = array(
                'label' => __('Theme Fonts', 'ds-studio'),
                'options' => $theme_json_fonts
            );
        }
        
        return $font_families;
    }
    
    /**
     * Get colors from theme.json
     */
    private function get_theme_colors() {
        $theme_json = $this->get_theme_json_data();
        $colors = array();
        
        if (isset($theme_json['settings']['color']['palette'])) {
            foreach ($theme_json['settings']['color']['palette'] as $color) {
                $colors[$color['slug']] = $color['color'];
            }
        }
        
        return $colors;
    }
    
    /**
     * Get font sizes from theme.json
     */
    private function get_theme_font_sizes() {
        $theme_json = $this->get_theme_json_data();
        
        if (isset($theme_json['settings']['typography']['fontSizes'])) {
            return $theme_json['settings']['typography']['fontSizes'];
        }
        
        return array();
    }
    
    /**
     * Get spacing presets from theme.json
     */
    private function get_theme_spacing() {
        $theme_json = $this->get_theme_json_data();
        
        if (isset($theme_json['settings']['spacing']['spacingSizes'])) {
            return $theme_json['settings']['spacing']['spacingSizes'];
        }
        
        return array();
    }
    
    /**
     * Get typography settings from theme.json
     */
    private function get_theme_typography() {
        $theme_json = $this->get_theme_json_data();
        $typography = array();
        
        if (isset($theme_json['settings']['typography'])) {
            $typo = $theme_json['settings']['typography'];
            
            // Get default font family
            if (isset($typo['fontFamilies'][0])) {
                $typography['fontFamily'] = $typo['fontFamilies'][0]['fontFamily'];
            }
            
            // Get font weights
            if (isset($typo['fontWeight'])) {
                $typography['fontWeight'] = $typo['fontWeight'];
            }
            
            // Get line height
            if (isset($typo['lineHeight'])) {
                $typography['lineHeight'] = $typo['lineHeight'];
            }
        }
        
        return $typography;
    }
    
    /**
     * Get font families from theme.json
     */
    private function get_theme_font_families() {
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
     * Get border radius tokens from theme.json custom section
     */
    private function get_border_radius_tokens() {
        $theme_json = $this->get_theme_json_data();
        
        if (isset($theme_json['settings']['custom']['borderRadius'])) {
            return $theme_json['settings']['custom']['borderRadius'];
        }
        
        return array();
    }
    
    /**
     * Get shadow tokens from theme.json custom section
     */
    private function get_shadow_tokens() {
        $theme_json = $this->get_theme_json_data();
        
        if (isset($theme_json['settings']['custom']['shadows'])) {
            return $theme_json['settings']['custom']['shadows'];
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
            
            if (file_exists($theme_json_file)) {
                $theme_json_content = file_get_contents($theme_json_file);
                $theme_json_data = json_decode($theme_json_content, true);
            } else {
                $theme_json_data = array();
            }
        }
        
        return $theme_json_data;
    }
    
    /**
     * AJAX handler for utility class picker
     */
    public function ajax_get_utilities_by_category() {
        // Check nonce for security
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ds_studio_utilities_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check user capabilities
        if (!current_user_can('edit_theme_options')) {
            wp_die('Insufficient permissions');
        }
        
        // Get utility generator instance
        if (!class_exists('DS_Studio_Utility_Generator')) {
            wp_send_json_error('Utility generator not available');
            return;
        }
        
        $utility_generator = new DS_Studio_Utility_Generator();
        $utilities_by_category = $utility_generator->get_utilities_by_category();
        
        // Send utilities data as JSON
        wp_send_json_success($utilities_by_category);
    }
}

// Initialize the integration
new DS_Studio_GenerateBlocks_Integration();
