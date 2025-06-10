<?php
/**
 * DS-Studio Utility Class Generator
 * 
 * Automatically generates utility CSS classes from theme.json design tokens
 * Creates a comprehensive utility system similar to Tailwind CSS
 */

if (!defined('ABSPATH')) {
    exit;
}

class DS_Studio_Utility_Generator {
    
    private $theme_json_data;
    private $utility_classes = [];
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_utility_styles'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_utility_styles'));
    }
    
    public function init() {
        $this->load_theme_json();
        $this->generate_utility_classes();
        $this->write_utility_css();
    }
    
    /**
     * Load theme.json data
     */
    private function load_theme_json() {
        $theme_json_path = get_stylesheet_directory() . '/theme.json';
        
        if (file_exists($theme_json_path)) {
            $theme_json_content = file_get_contents($theme_json_path);
            $this->theme_json_data = json_decode($theme_json_content, true);
        }
    }
    
    /**
     * Generate all utility classes from theme.json tokens
     */
    private function generate_utility_classes() {
        if (!$this->theme_json_data) return;
        
        // Generate spacing utilities
        $this->generate_spacing_utilities();
        
        // Generate color utilities
        $this->generate_color_utilities();
        
        // Generate typography utilities
        $this->generate_typography_utilities();
        
        // Generate border utilities
        $this->generate_border_utilities();
        
        // Generate layout utilities
        $this->generate_layout_utilities();
        
        // Generate shadow utilities
        $this->generate_shadow_utilities();
    }
    
    /**
     * Generate spacing utility classes
     */
    private function generate_spacing_utilities() {
        $spacing = $this->theme_json_data['settings']['spacing']['spacingSizes'] ?? [];
        
        foreach ($spacing as $size) {
            $slug = $size['slug'];
            $value = $size['size'];
            
            // Margin utilities
            $this->utility_classes[] = ".m-{$slug} { margin: {$value} !important; }";
            $this->utility_classes[] = ".mt-{$slug} { margin-top: {$value} !important; }";
            $this->utility_classes[] = ".mr-{$slug} { margin-right: {$value} !important; }";
            $this->utility_classes[] = ".mb-{$slug} { margin-bottom: {$value} !important; }";
            $this->utility_classes[] = ".ml-{$slug} { margin-left: {$value} !important; }";
            $this->utility_classes[] = ".mx-{$slug} { margin-left: {$value} !important; margin-right: {$value} !important; }";
            $this->utility_classes[] = ".my-{$slug} { margin-top: {$value} !important; margin-bottom: {$value} !important; }";
            
            // Padding utilities
            $this->utility_classes[] = ".p-{$slug} { padding: {$value} !important; }";
            $this->utility_classes[] = ".pt-{$slug} { padding-top: {$value} !important; }";
            $this->utility_classes[] = ".pr-{$slug} { padding-right: {$value} !important; }";
            $this->utility_classes[] = ".pb-{$slug} { padding-bottom: {$value} !important; }";
            $this->utility_classes[] = ".pl-{$slug} { padding-left: {$value} !important; }";
            $this->utility_classes[] = ".px-{$slug} { padding-left: {$value} !important; padding-right: {$value} !important; }";
            $this->utility_classes[] = ".py-{$slug} { padding-top: {$value} !important; padding-bottom: {$value} !important; }";
            
            // Gap utilities
            $this->utility_classes[] = ".gap-{$slug} { gap: {$value} !important; }";
            $this->utility_classes[] = ".gap-x-{$slug} { column-gap: {$value} !important; }";
            $this->utility_classes[] = ".gap-y-{$slug} { row-gap: {$value} !important; }";
        }
    }
    
    /**
     * Generate color utility classes
     */
    private function generate_color_utilities() {
        $colors = $this->theme_json_data['settings']['color']['palette'] ?? [];
        
        foreach ($colors as $color) {
            $slug = $color['slug'];
            $value = $color['color'];
            
            // Text color utilities
            $this->utility_classes[] = ".text-{$slug} { color: {$value} !important; }";
            
            // Background color utilities
            $this->utility_classes[] = ".bg-{$slug} { background-color: {$value} !important; }";
            
            // Border color utilities
            $this->utility_classes[] = ".border-{$slug} { border-color: {$value} !important; }";
        }
    }
    
    /**
     * Generate typography utility classes
     */
    private function generate_typography_utilities() {
        // Font sizes
        $font_sizes = $this->theme_json_data['settings']['typography']['fontSizes'] ?? [];
        foreach ($font_sizes as $size) {
            $slug = $size['slug'];
            $value = $size['size'];
            $this->utility_classes[] = ".text-{$slug} { font-size: {$value} !important; }";
        }
        
        // Font families
        $font_families = $this->theme_json_data['settings']['typography']['fontFamilies'] ?? [];
        foreach ($font_families as $family) {
            $slug = $family['slug'];
            $value = $family['fontFamily'];
            $this->utility_classes[] = ".font-{$slug} { font-family: {$value} !important; }";
        }
        
        // Font weights
        $font_weights = $this->theme_json_data['custom']['typography']['fontWeights'] ?? [];
        foreach ($font_weights as $weight) {
            $slug = $weight['slug'];
            $value = $weight['fontWeight'];
            $this->utility_classes[] = ".font-{$slug} { font-weight: {$value} !important; }";
        }
        
        // Line heights
        $line_heights = $this->theme_json_data['custom']['typography']['lineHeights'] ?? [];
        foreach ($line_heights as $height) {
            $slug = $height['slug'];
            $value = $height['lineHeight'];
            $this->utility_classes[] = ".leading-{$slug} { line-height: {$value} !important; }";
        }
        
        // Letter spacing
        $letter_spacings = $this->theme_json_data['custom']['typography']['letterSpacings'] ?? [];
        foreach ($letter_spacings as $spacing) {
            $slug = $spacing['slug'];
            $value = $spacing['letterSpacing'];
            $this->utility_classes[] = ".tracking-{$slug} { letter-spacing: {$value} !important; }";
        }
        
        // Text transforms
        $text_transforms = $this->theme_json_data['custom']['typography']['textTransforms'] ?? [];
        foreach ($text_transforms as $transform) {
            $slug = $transform['slug'];
            $value = $transform['textTransform'];
            $this->utility_classes[] = ".{$slug} { text-transform: {$value} !important; }";
        }
    }
    
    /**
     * Generate border utility classes
     */
    private function generate_border_utilities() {
        // Border widths
        $border_widths = $this->theme_json_data['custom']['borders']['widths'] ?? [];
        foreach ($border_widths as $slug => $value) {
            $this->utility_classes[] = ".border-{$slug} { border-width: {$value} !important; }";
            $this->utility_classes[] = ".border-t-{$slug} { border-top-width: {$value} !important; }";
            $this->utility_classes[] = ".border-r-{$slug} { border-right-width: {$value} !important; }";
            $this->utility_classes[] = ".border-b-{$slug} { border-bottom-width: {$value} !important; }";
            $this->utility_classes[] = ".border-l-{$slug} { border-left-width: {$value} !important; }";
        }
        
        // Border styles
        $border_styles = $this->theme_json_data['custom']['borders']['styles'] ?? [];
        foreach ($border_styles as $slug => $value) {
            $this->utility_classes[] = ".border-{$slug} { border-style: {$value} !important; }";
        }
        
        // Border radius
        $border_radii = $this->theme_json_data['custom']['borders']['radii'] ?? [];
        foreach ($border_radii as $slug => $value) {
            $this->utility_classes[] = ".rounded-{$slug} { border-radius: {$value} !important; }";
            $this->utility_classes[] = ".rounded-t-{$slug} { border-top-left-radius: {$value} !important; border-top-right-radius: {$value} !important; }";
            $this->utility_classes[] = ".rounded-r-{$slug} { border-top-right-radius: {$value} !important; border-bottom-right-radius: {$value} !important; }";
            $this->utility_classes[] = ".rounded-b-{$slug} { border-bottom-left-radius: {$value} !important; border-bottom-right-radius: {$value} !important; }";
            $this->utility_classes[] = ".rounded-l-{$slug} { border-top-left-radius: {$value} !important; border-bottom-left-radius: {$value} !important; }";
        }
    }
    
    /**
     * Generate layout utility classes
     */
    private function generate_layout_utilities() {
        // Container sizes
        $containers = $this->theme_json_data['custom']['layout']['containers'] ?? [];
        foreach ($containers as $slug => $value) {
            $this->utility_classes[] = ".container-{$slug} { max-width: {$value} !important; margin-left: auto !important; margin-right: auto !important; }";
            $this->utility_classes[] = ".w-{$slug} { width: {$value} !important; }";
            $this->utility_classes[] = ".max-w-{$slug} { max-width: {$value} !important; }";
        }
        
        // Aspect ratios
        $aspect_ratios = $this->theme_json_data['custom']['layout']['aspectRatios'] ?? [];
        foreach ($aspect_ratios as $slug => $value) {
            $this->utility_classes[] = ".aspect-{$slug} { aspect-ratio: {$value} !important; }";
        }
        
        // Z-index
        $z_indexes = $this->theme_json_data['custom']['layout']['zIndex'] ?? [];
        foreach ($z_indexes as $slug => $value) {
            $this->utility_classes[] = ".z-{$slug} { z-index: {$value} !important; }";
        }
        
        // Grid templates
        $grid_templates = $this->theme_json_data['custom']['layout']['grid'] ?? [];
        foreach ($grid_templates as $slug => $value) {
            $this->utility_classes[] = ".grid-{$slug} { grid-template-columns: {$value} !important; }";
        }
    }
    
    /**
     * Generate shadow utility classes
     */
    private function generate_shadow_utilities() {
        $shadows = $this->theme_json_data['custom']['shadows'] ?? [];
        foreach ($shadows as $slug => $value) {
            $this->utility_classes[] = ".shadow-{$slug} { box-shadow: {$value} !important; }";
        }
    }
    
    /**
     * Write utility CSS to file
     */
    private function write_utility_css() {
        if (empty($this->utility_classes)) return;
        
        $css_content = "/* DS-Studio Auto-Generated Utility Classes */\n";
        $css_content .= "/* Generated from theme.json design tokens */\n\n";
        
        // Add utility classes
        $css_content .= implode("\n", $this->utility_classes);
        
        // Add common utility classes
        $css_content .= "\n\n/* Common Utilities */\n";
        $css_content .= ".flex { display: flex !important; }\n";
        $css_content .= ".grid { display: grid !important; }\n";
        $css_content .= ".block { display: block !important; }\n";
        $css_content .= ".inline { display: inline !important; }\n";
        $css_content .= ".inline-block { display: inline-block !important; }\n";
        $css_content .= ".hidden { display: none !important; }\n";
        $css_content .= ".relative { position: relative !important; }\n";
        $css_content .= ".absolute { position: absolute !important; }\n";
        $css_content .= ".fixed { position: fixed !important; }\n";
        $css_content .= ".sticky { position: sticky !important; }\n";
        $css_content .= ".text-left { text-align: left !important; }\n";
        $css_content .= ".text-center { text-align: center !important; }\n";
        $css_content .= ".text-right { text-align: right !important; }\n";
        $css_content .= ".justify-start { justify-content: flex-start !important; }\n";
        $css_content .= ".justify-center { justify-content: center !important; }\n";
        $css_content .= ".justify-end { justify-content: flex-end !important; }\n";
        $css_content .= ".justify-between { justify-content: space-between !important; }\n";
        $css_content .= ".items-start { align-items: flex-start !important; }\n";
        $css_content .= ".items-center { align-items: center !important; }\n";
        $css_content .= ".items-end { align-items: flex-end !important; }\n";
        $css_content .= ".w-full { width: 100% !important; }\n";
        $css_content .= ".h-full { height: 100% !important; }\n";
        
        // Write to file
        $upload_dir = wp_upload_dir();
        $css_file_path = $upload_dir['basedir'] . '/ds-studio-utilities.css';
        
        file_put_contents($css_file_path, $css_content);
        
        // Store the file URL for enqueueing
        update_option('ds_studio_utilities_css_url', $upload_dir['baseurl'] . '/ds-studio-utilities.css');
    }
    
    /**
     * Enqueue utility styles
     */
    public function enqueue_utility_styles() {
        // Check if we should use purged CSS
        $use_purged = get_option('ds_studio_use_purged_css', false);
        
        if ($use_purged) {
            $css_url = get_option('ds_studio_purged_css_url');
        } else {
            $css_url = get_option('ds_studio_utilities_css_url');
        }
        
        if ($css_url) {
            wp_enqueue_style(
                'ds-studio-utilities',
                $css_url,
                array(),
                filemtime(str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $css_url))
            );
        }
    }
    
    /**
     * Generate purged utilities (only specified classes)
     */
    public function generate_purged_utilities($used_utilities) {
        $this->load_theme_json();
        
        $css_content = "/* DS-Studio Utilities - Purged Version */\n";
        $css_content .= "/* Generated on " . date('Y-m-d H:i:s') . " */\n";
        $css_content .= "/* Only includes utilities actually used in your site */\n\n";
        
        // Generate only the utilities that are actually used
        foreach ($used_utilities as $utility_class) {
            $css_rule = $this->generate_single_utility_css($utility_class);
            if ($css_rule) {
                $css_content .= $css_rule . "\n";
            }
        }
        
        return $css_content;
    }
    
    /**
     * Generate CSS for a single utility class
     */
    private function generate_single_utility_css($class_name) {
        // Parse the utility class and generate appropriate CSS
        
        // Spacing utilities
        if (preg_match('/^(m|p|gap)-(xs|sm|base|md|lg|xl|2xl|3xl|4xl|5xl|\d+)$/', $class_name, $matches)) {
            $property_map = array('m' => 'margin', 'p' => 'padding', 'gap' => 'gap');
            $property = $property_map[$matches[1]];
            $value = $this->get_spacing_value($matches[2]);
            return ".{$class_name} { {$property}: {$value} !important; }";
        }
        
        // Directional spacing
        if (preg_match('/^(mt|mr|mb|ml|mx|my|pt|pr|pb|pl|px|py)-(xs|sm|base|md|lg|xl|2xl|3xl|4xl|5xl|\d+|auto)$/', $class_name, $matches)) {
            $direction_map = array(
                'mt' => 'margin-top', 'mr' => 'margin-right', 'mb' => 'margin-bottom', 'ml' => 'margin-left',
                'pt' => 'padding-top', 'pr' => 'padding-right', 'pb' => 'padding-bottom', 'pl' => 'padding-left'
            );
            
            if (isset($direction_map[$matches[1]])) {
                $property = $direction_map[$matches[1]];
                $value = $matches[2] === 'auto' ? 'auto' : $this->get_spacing_value($matches[2]);
                return ".{$class_name} { {$property}: {$value} !important; }";
            }
            
            // Handle mx, my, px, py
            if (in_array($matches[1], array('mx', 'my', 'px', 'py'))) {
                $value = $matches[2] === 'auto' ? 'auto' : $this->get_spacing_value($matches[2]);
                $axis = substr($matches[1], 1, 1);
                $type = substr($matches[1], 0, 1) === 'm' ? 'margin' : 'padding';
                
                if ($axis === 'x') {
                    return ".{$class_name} { {$type}-left: {$value} !important; {$type}-right: {$value} !important; }";
                } else {
                    return ".{$class_name} { {$type}-top: {$value} !important; {$type}-bottom: {$value} !important; }";
                }
            }
        }
        
        // Color utilities
        if (preg_match('/^(text|bg|border)-(primary|secondary|accent|neutral|gray|white|black)(-\d+)?$/', $class_name, $matches)) {
            $property_map = array('text' => 'color', 'bg' => 'background-color', 'border' => 'border-color');
            $property = $property_map[$matches[1]];
            $color_value = $this->get_color_value($matches[2] . (isset($matches[3]) ? $matches[3] : ''));
            return ".{$class_name} { {$property}: {$color_value} !important; }";
        }
        
        // Typography utilities
        if (preg_match('/^text-(xs|sm|base|lg|xl|2xl|3xl|4xl|5xl|6xl)$/', $class_name, $matches)) {
            $font_size = $this->get_font_size_value($matches[1]);
            return ".{$class_name} { font-size: {$font_size} !important; }";
        }
        
        if (preg_match('/^font-(heading|body|mono|light|normal|medium|semibold|bold|extrabold)$/', $class_name, $matches)) {
            if (in_array($matches[1], array('light', 'normal', 'medium', 'semibold', 'bold', 'extrabold'))) {
                $weight_map = array(
                    'light' => '300', 'normal' => '400', 'medium' => '500',
                    'semibold' => '600', 'bold' => '700', 'extrabold' => '800'
                );
                return ".{$class_name} { font-weight: {$weight_map[$matches[1]]} !important; }";
            } else {
                $font_family = $this->get_font_family_value($matches[1]);
                return ".{$class_name} { font-family: {$font_family} !important; }";
            }
        }
        
        // Add more utility patterns as needed...
        
        return null;
    }
    
    /**
     * Get spacing value from token
     */
    private function get_spacing_value($token) {
        if (isset($this->theme_json_data['settings']['spacing']['sizes'][$token])) {
            return $this->theme_json_data['settings']['spacing']['sizes'][$token];
        }
        if (isset($this->theme_json_data['custom']['spacing']['sizes'][$token])) {
            return $this->theme_json_data['custom']['spacing']['sizes'][$token];
        }
        return $token; // Fallback to the token itself
    }
    
    /**
     * Get color value from token
     */
    private function get_color_value($token) {
        if (isset($this->theme_json_data['settings']['color']['palette'])) {
            foreach ($this->theme_json_data['settings']['color']['palette'] as $color) {
                if ($color['slug'] === $token) {
                    return $color['color'];
                }
            }
        }
        if (isset($this->theme_json_data['custom']['colors'][$token])) {
            return $this->theme_json_data['custom']['colors'][$token];
        }
        return $token;
    }
    
    /**
     * Get font size value from token
     */
    private function get_font_size_value($token) {
        if (isset($this->theme_json_data['settings']['typography']['fontSizes'])) {
            foreach ($this->theme_json_data['settings']['typography']['fontSizes'] as $size) {
                if ($size['slug'] === $token) {
                    return $size['size'];
                }
            }
        }
        if (isset($this->theme_json_data['custom']['typography']['fontSizes'][$token])) {
            return $this->theme_json_data['custom']['typography']['fontSizes'][$token];
        }
        return $token;
    }
    
    /**
     * Get font family value from token
     */
    private function get_font_family_value($token) {
        if (isset($this->theme_json_data['settings']['typography']['fontFamilies'])) {
            foreach ($this->theme_json_data['settings']['typography']['fontFamilies'] as $family) {
                if ($family['slug'] === $token) {
                    return $family['fontFamily'];
                }
            }
        }
        if (isset($this->theme_json_data['custom']['typography']['fontFamilies'][$token])) {
            return $this->theme_json_data['custom']['typography']['fontFamilies'][$token];
        }
        return $token;
    }
    
    /**
     * Regenerate utilities (called when theme.json is updated)
     */
    public function regenerate_utilities() {
        $this->utility_classes = [];
        $this->load_theme_json();
        $this->generate_utility_classes();
        $this->write_utility_css();
    }
}

// Initialize the utility generator
new DS_Studio_Utility_Generator();
