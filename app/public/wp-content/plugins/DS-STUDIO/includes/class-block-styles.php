<?php
/**
 * DS-Studio Block Styles
 * Register block style variations using design system tokens
 */

class DS_Studio_Block_Styles {
    
    private $theme_json_data;
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_styles'));
    }
    
    public function init() {
        $this->load_theme_json();
        $this->register_block_styles();
    }
    
    /**
     * Load theme.json data for style generation
     */
    private function load_theme_json() {
        $theme_json_path = get_stylesheet_directory() . '/theme.json';
        
        if (file_exists($theme_json_path)) {
            $this->theme_json_data = json_decode(file_get_contents($theme_json_path), true);
        } else {
            $this->theme_json_data = array();
        }
    }
    
    /**
     * Register all block style variations
     */
    public function register_block_styles() {
        $this->register_heading_styles();
        $this->register_paragraph_styles();
        $this->register_button_styles();
        $this->register_group_styles();
        $this->register_column_styles();
        $this->register_image_styles();
    }
    
    /**
     * Register heading block styles
     */
    private function register_heading_styles() {
        // Hero Title Style
        register_block_style('core/heading', array(
            'name' => 'hero-title',
            'label' => __('Hero Title', 'ds-studio'),
            'style_handle' => 'ds-studio-block-styles'
        ));
        
        // Section Title Style
        register_block_style('core/heading', array(
            'name' => 'section-title',
            'label' => __('Section Title', 'ds-studio'),
            'style_handle' => 'ds-studio-block-styles'
        ));
        
        // Card Title Style
        register_block_style('core/heading', array(
            'name' => 'card-title',
            'label' => __('Card Title', 'ds-studio'),
            'style_handle' => 'ds-studio-block-styles'
        ));
        
        // Accent Title Style
        register_block_style('core/heading', array(
            'name' => 'accent-title',
            'label' => __('Accent Title', 'ds-studio'),
            'style_handle' => 'ds-studio-block-styles'
        ));
        
        // Subtitle Style
        register_block_style('core/heading', array(
            'name' => 'subtitle',
            'label' => __('Subtitle', 'ds-studio'),
            'style_handle' => 'ds-studio-block-styles'
        ));
    }
    
    /**
     * Register paragraph block styles
     */
    private function register_paragraph_styles() {
        // Lead Text Style
        register_block_style('core/paragraph', array(
            'name' => 'lead-text',
            'label' => __('Lead Text', 'ds-studio'),
            'style_handle' => 'ds-studio-block-styles'
        ));
        
        // Small Text Style
        register_block_style('core/paragraph', array(
            'name' => 'small-text',
            'label' => __('Small Text', 'ds-studio'),
            'style_handle' => 'ds-studio-block-styles'
        ));
        
        // Highlight Text Style
        register_block_style('core/paragraph', array(
            'name' => 'highlight-text',
            'label' => __('Highlight Text', 'ds-studio'),
            'style_handle' => 'ds-studio-block-styles'
        ));
        
        // Caption Style
        register_block_style('core/paragraph', array(
            'name' => 'caption',
            'label' => __('Caption', 'ds-studio'),
            'style_handle' => 'ds-studio-block-styles'
        ));
    }
    
    /**
     * Register button block styles
     */
    private function register_button_styles() {
        // Primary Button (already default, but for consistency)
        register_block_style('core/button', array(
            'name' => 'primary',
            'label' => __('Primary', 'ds-studio'),
            'style_handle' => 'ds-studio-block-styles'
        ));
        
        // Secondary Button
        register_block_style('core/button', array(
            'name' => 'secondary',
            'label' => __('Secondary', 'ds-studio'),
            'style_handle' => 'ds-studio-block-styles'
        ));
        
        // Outline Button
        register_block_style('core/button', array(
            'name' => 'outline',
            'label' => __('Outline', 'ds-studio'),
            'style_handle' => 'ds-studio-block-styles'
        ));
        
        // Ghost Button
        register_block_style('core/button', array(
            'name' => 'ghost',
            'label' => __('Ghost', 'ds-studio'),
            'style_handle' => 'ds-studio-block-styles'
        ));
        
        // Large Button
        register_block_style('core/button', array(
            'name' => 'large',
            'label' => __('Large', 'ds-studio'),
            'style_handle' => 'ds-studio-block-styles'
        ));
        
        // Small Button
        register_block_style('core/button', array(
            'name' => 'small',
            'label' => __('Small', 'ds-studio'),
            'style_handle' => 'ds-studio-block-styles'
        ));
    }
    
    /**
     * Register group block styles (containers)
     */
    private function register_group_styles() {
        // Card Container
        register_block_style('core/group', array(
            'name' => 'card',
            'label' => __('Card', 'ds-studio'),
            'style_handle' => 'ds-studio-block-styles'
        ));
        
        // Hero Section
        register_block_style('core/group', array(
            'name' => 'hero-section',
            'label' => __('Hero Section', 'ds-studio'),
            'style_handle' => 'ds-studio-block-styles'
        ));
        
        // Feature Box
        register_block_style('core/group', array(
            'name' => 'feature-box',
            'label' => __('Feature Box', 'ds-studio'),
            'style_handle' => 'ds-studio-block-styles'
        ));
        
        // Callout Box
        register_block_style('core/group', array(
            'name' => 'callout',
            'label' => __('Callout', 'ds-studio'),
            'style_handle' => 'ds-studio-block-styles'
        ));
        
        // Bordered Section
        register_block_style('core/group', array(
            'name' => 'bordered',
            'label' => __('Bordered', 'ds-studio'),
            'style_handle' => 'ds-studio-block-styles'
        ));
    }
    
    /**
     * Register column block styles
     */
    private function register_column_styles() {
        // Card Column
        register_block_style('core/column', array(
            'name' => 'card-column',
            'label' => __('Card Column', 'ds-studio'),
            'style_handle' => 'ds-studio-block-styles'
        ));
        
        // Feature Column
        register_block_style('core/column', array(
            'name' => 'feature-column',
            'label' => __('Feature Column', 'ds-studio'),
            'style_handle' => 'ds-studio-block-styles'
        ));
    }
    
    /**
     * Register image block styles
     */
    private function register_image_styles() {
        // Rounded Image
        register_block_style('core/image', array(
            'name' => 'rounded',
            'label' => __('Rounded', 'ds-studio'),
            'style_handle' => 'ds-studio-block-styles'
        ));
        
        // Circle Image
        register_block_style('core/image', array(
            'name' => 'circle',
            'label' => __('Circle', 'ds-studio'),
            'style_handle' => 'ds-studio-block-styles'
        ));
        
        // Shadow Image
        register_block_style('core/image', array(
            'name' => 'shadow',
            'label' => __('Shadow', 'ds-studio'),
            'style_handle' => 'ds-studio-block-styles'
        ));
    }
    
    /**
     * Generate CSS for block styles using design tokens
     */
    private function generate_block_styles_css() {
        $css = '';
        
        // Get design tokens
        $primary_color = $this->get_token_value('settings.color.palette.0.color', '#007cba');
        $secondary_color = $this->get_token_value('settings.color.palette.1.color', '#6c757d');
        $accent_color = $this->get_token_value('settings.color.palette.2.color', '#28a745');
        
        // Spacing tokens
        $spacing_sm = $this->get_token_value('settings.spacing.spacingSizes.0.size', '0.5rem');
        $spacing_md = $this->get_token_value('settings.spacing.spacingSizes.1.size', '1rem');
        $spacing_lg = $this->get_token_value('settings.spacing.spacingSizes.2.size', '1.5rem');
        $spacing_xl = $this->get_token_value('settings.spacing.spacingSizes.3.size', '2rem');
        
        // Typography tokens
        $font_sm = $this->get_token_value('settings.typography.fontSizes.0.size', '0.875rem');
        $font_base = $this->get_token_value('settings.typography.fontSizes.1.size', '1rem');
        $font_lg = $this->get_token_value('settings.typography.fontSizes.2.size', '1.125rem');
        $font_xl = $this->get_token_value('settings.typography.fontSizes.3.size', '1.25rem');
        $font_2xl = $this->get_token_value('settings.typography.fontSizes.4.size', '1.5rem');
        $font_3xl = $this->get_token_value('settings.typography.fontSizes.5.size', '1.875rem');
        $font_4xl = $this->get_token_value('settings.typography.fontSizes.6.size', '2.25rem');
        
        // Border radius tokens
        $radius_sm = $this->get_token_value('custom.borders.radius.sm', '0.25rem');
        $radius_md = $this->get_token_value('custom.borders.radius.md', '0.375rem');
        $radius_lg = $this->get_token_value('custom.borders.radius.lg', '0.5rem');
        
        // Shadow tokens
        $shadow_sm = $this->get_token_value('custom.shadows.sm', '0 1px 2px 0 rgba(0, 0, 0, 0.05)');
        $shadow_md = $this->get_token_value('custom.shadows.md', '0 4px 6px -1px rgba(0, 0, 0, 0.1)');
        $shadow_lg = $this->get_token_value('custom.shadows.lg', '0 10px 15px -3px rgba(0, 0, 0, 0.1)');
        
        $css .= "
        /* === HEADING STYLES === */
        .wp-block-heading.is-style-hero-title {
            font-size: {$font_4xl} !important;
            color: {$primary_color} !important;
            font-weight: 700 !important;
            line-height: 1.2 !important;
            margin-bottom: {$spacing_lg} !important;
        }
        
        .wp-block-heading.is-style-section-title {
            font-size: {$font_2xl} !important;
            color: {$primary_color} !important;
            font-weight: 600 !important;
            margin-bottom: {$spacing_md} !important;
            border-bottom: 2px solid {$primary_color};
            padding-bottom: {$spacing_sm};
        }
        
        .wp-block-heading.is-style-card-title {
            font-size: {$font_xl} !important;
            color: {$primary_color} !important;
            font-weight: 600 !important;
            margin-bottom: {$spacing_sm} !important;
        }
        
        .wp-block-heading.is-style-accent-title {
            font-size: {$font_lg} !important;
            color: {$accent_color} !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            margin-bottom: {$spacing_sm} !important;
        }
        
        .wp-block-heading.is-style-subtitle {
            font-size: {$font_lg} !important;
            color: {$secondary_color} !important;
            font-weight: 400 !important;
            margin-bottom: {$spacing_md} !important;
        }
        
        /* === PARAGRAPH STYLES === */
        .wp-block-paragraph.is-style-lead-text {
            font-size: {$font_lg} !important;
            line-height: 1.6 !important;
            color: {$primary_color} !important;
            margin-bottom: {$spacing_lg} !important;
        }
        
        .wp-block-paragraph.is-style-small-text {
            font-size: {$font_sm} !important;
            color: {$secondary_color} !important;
        }
        
        .wp-block-paragraph.is-style-highlight-text {
            background-color: rgba(255, 235, 59, 0.3) !important;
            padding: {$spacing_sm} !important;
            border-radius: {$radius_sm} !important;
            border-left: 4px solid {$accent_color} !important;
        }
        
        .wp-block-paragraph.is-style-caption {
            font-size: {$font_sm} !important;
            color: {$secondary_color} !important;
            font-style: italic !important;
            text-align: center !important;
        }
        
        /* === BUTTON STYLES === */
        .wp-block-button.is-style-primary .wp-block-button__link {
            background-color: {$primary_color} !important;
            color: white !important;
            border: none !important;
            border-radius: {$radius_md} !important;
            padding: {$spacing_sm} {$spacing_lg} !important;
            font-weight: 600 !important;
            transition: all 0.2s ease !important;
        }
        
        .wp-block-button.is-style-primary .wp-block-button__link:hover {
            background-color: color-mix(in srgb, {$primary_color} 80%, black) !important;
            transform: translateY(-1px) !important;
        }
        
        .wp-block-button.is-style-secondary .wp-block-button__link {
            background-color: {$secondary_color} !important;
            color: white !important;
            border: none !important;
            border-radius: {$radius_md} !important;
            padding: {$spacing_sm} {$spacing_lg} !important;
            font-weight: 600 !important;
        }
        
        .wp-block-button.is-style-outline .wp-block-button__link {
            background-color: transparent !important;
            color: {$primary_color} !important;
            border: 2px solid {$primary_color} !important;
            border-radius: {$radius_md} !important;
            padding: {$spacing_sm} {$spacing_lg} !important;
            font-weight: 600 !important;
        }
        
        .wp-block-button.is-style-outline .wp-block-button__link:hover {
            background-color: {$primary_color} !important;
            color: white !important;
        }
        
        .wp-block-button.is-style-ghost .wp-block-button__link {
            background-color: transparent !important;
            color: {$primary_color} !important;
            border: none !important;
            padding: {$spacing_sm} {$spacing_lg} !important;
            font-weight: 600 !important;
            text-decoration: underline !important;
        }
        
        .wp-block-button.is-style-large .wp-block-button__link {
            padding: {$spacing_md} {$spacing_xl} !important;
            font-size: {$font_lg} !important;
        }
        
        .wp-block-button.is-style-small .wp-block-button__link {
            padding: {$spacing_sm} {$spacing_md} !important;
            font-size: {$font_sm} !important;
        }
        
        /* === GROUP STYLES === */
        .wp-block-group.is-style-card {
            background-color: white !important;
            border-radius: {$radius_lg} !important;
            box-shadow: {$shadow_md} !important;
            padding: {$spacing_lg} !important;
            border: 1px solid #e5e7eb !important;
        }
        
        .wp-block-group.is-style-hero-section {
            background: linear-gradient(135deg, {$primary_color}, {$accent_color}) !important;
            color: white !important;
            padding: {$spacing_xl} !important;
            border-radius: {$radius_lg} !important;
            text-align: center !important;
        }
        
        .wp-block-group.is-style-feature-box {
            background-color: #f8f9fa !important;
            border-radius: {$radius_md} !important;
            padding: {$spacing_lg} !important;
            border-left: 4px solid {$primary_color} !important;
        }
        
        .wp-block-group.is-style-callout {
            background-color: rgba(59, 130, 246, 0.1) !important;
            border: 1px solid {$primary_color} !important;
            border-radius: {$radius_md} !important;
            padding: {$spacing_lg} !important;
        }
        
        .wp-block-group.is-style-bordered {
            border: 2px solid {$primary_color} !important;
            border-radius: {$radius_md} !important;
            padding: {$spacing_lg} !important;
        }
        
        /* === COLUMN STYLES === */
        .wp-block-column.is-style-card-column {
            background-color: white !important;
            border-radius: {$radius_lg} !important;
            box-shadow: {$shadow_sm} !important;
            padding: {$spacing_lg} !important;
            margin: {$spacing_sm} !important;
        }
        
        .wp-block-column.is-style-feature-column {
            text-align: center !important;
            padding: {$spacing_lg} !important;
        }
        
        /* === IMAGE STYLES === */
        .wp-block-image.is-style-rounded img {
            border-radius: {$radius_lg} !important;
        }
        
        .wp-block-image.is-style-circle img {
            border-radius: 50% !important;
        }
        
        .wp-block-image.is-style-shadow img {
            box-shadow: {$shadow_lg} !important;
            border-radius: {$radius_md} !important;
        }
        
        /* === RESPONSIVE STYLES === */
        @media (max-width: 768px) {
            .wp-block-heading.is-style-hero-title {
                font-size: {$font_3xl} !important;
            }
            
            .wp-block-group.is-style-hero-section {
                padding: {$spacing_lg} !important;
            }
        }
        ";
        
        return $css;
    }
    
    /**
     * Get design token values for style generation
     */
    private function get_token_value($path, $fallback = '') {
        $keys = explode('.', $path);
        $value = $this->theme_json_data;
        
        foreach ($keys as $key) {
            if (isset($value[$key])) {
                $value = $value[$key];
            } else {
                return $fallback;
            }
        }
        
        return $value;
    }
    
    /**
     * Enqueue block styles for editor
     */
    public function enqueue_block_styles() {
        $css = $this->generate_block_styles_css();
        
        wp_register_style(
            'ds-studio-block-styles',
            false,
            array(),
            DS_STUDIO_VERSION
        );
        
        wp_enqueue_style('ds-studio-block-styles');
        wp_add_inline_style('ds-studio-block-styles', $css);
    }
    
    /**
     * Enqueue block styles for frontend
     */
    public function enqueue_frontend_styles() {
        $css = $this->generate_block_styles_css();
        
        wp_register_style(
            'ds-studio-block-styles-frontend',
            false,
            array(),
            DS_STUDIO_VERSION
        );
        
        wp_enqueue_style('ds-studio-block-styles-frontend');
        wp_add_inline_style('ds-studio-block-styles-frontend', $css);
    }
}

// Initialize block styles
new DS_Studio_Block_Styles();
?>
