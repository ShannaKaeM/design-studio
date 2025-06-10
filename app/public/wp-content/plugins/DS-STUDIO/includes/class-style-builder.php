<?php
/**
 * DS-Studio Style Builder
 * Create and manage custom block styles from the block editor
 */

class DS_Studio_Style_Builder {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_editor_assets'));
        add_action('wp_ajax_ds_studio_save_custom_style', array($this, 'save_custom_style'));
        add_action('wp_ajax_ds_studio_get_custom_styles', array($this, 'get_custom_styles'));
        add_action('wp_ajax_ds_studio_delete_custom_style', array($this, 'delete_custom_style'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_styles'));
    }
    
    public function init() {
        $this->register_dynamic_block_styles();
    }
    
    /**
     * Enqueue editor assets for style builder
     */
    public function enqueue_editor_assets() {
        error_log('DS-Studio: Enqueuing style builder assets');
        
        wp_enqueue_script(
            'ds-studio-style-builder',
            DS_STUDIO_PLUGIN_URL . 'assets/js/style-builder.js',
            array('wp-blocks', 'wp-element', 'wp-components', 'wp-data', 'wp-compose', 'jquery'),
            '1.0.2',
            true
        );
        
        wp_localize_script('ds-studio-style-builder', 'dsStudioStyleBuilder', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ds_studio_style_builder'),
            'customStyles' => $this->get_saved_custom_styles()
        ));
        
        wp_enqueue_style(
            'ds-studio-style-builder',
            DS_STUDIO_PLUGIN_URL . 'assets/css/style-builder.css',
            array(),
            DS_STUDIO_VERSION
        );
    }
    
    /**
     * Save custom style via AJAX
     */
    public function save_custom_style() {
        check_ajax_referer('ds_studio_style_builder', 'nonce');
        
        if (!current_user_can('edit_theme_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $style_name = sanitize_text_field($_POST['style_name']);
        $style_label = sanitize_text_field($_POST['style_label']);
        $block_type = sanitize_text_field($_POST['block_type']);
        $style_attributes = json_decode(stripslashes($_POST['style_attributes']), true);
        
        if (empty($style_name) || empty($style_label) || empty($block_type)) {
            wp_send_json_error('Missing required fields');
        }
        
        $custom_styles = get_option('ds_studio_custom_styles', array());
        
        $custom_styles[$block_type][$style_name] = array(
            'label' => $style_label,
            'attributes' => $style_attributes,
            'created' => current_time('mysql')
        );
        
        update_option('ds_studio_custom_styles', $custom_styles);
        
        // Register the new style immediately
        $this->register_single_block_style($block_type, $style_name, $style_label);
        
        wp_send_json_success(array(
            'message' => 'Style saved successfully',
            'style' => array(
                'name' => $style_name,
                'label' => $style_label,
                'block_type' => $block_type
            )
        ));
    }
    
    /**
     * Get custom styles via AJAX
     */
    public function get_custom_styles() {
        check_ajax_referer('ds_studio_style_builder', 'nonce');
        
        $custom_styles = $this->get_saved_custom_styles();
        wp_send_json_success($custom_styles);
    }
    
    /**
     * Delete custom style via AJAX
     */
    public function delete_custom_style() {
        check_ajax_referer('ds_studio_style_builder', 'nonce');
        
        if (!current_user_can('edit_theme_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $style_name = sanitize_text_field($_POST['style_name']);
        $block_type = sanitize_text_field($_POST['block_type']);
        
        $custom_styles = get_option('ds_studio_custom_styles', array());
        
        if (isset($custom_styles[$block_type][$style_name])) {
            unset($custom_styles[$block_type][$style_name]);
            update_option('ds_studio_custom_styles', $custom_styles);
            
            wp_send_json_success('Style deleted successfully');
        } else {
            wp_send_json_error('Style not found');
        }
    }
    
    /**
     * Get saved custom styles
     */
    private function get_saved_custom_styles() {
        return get_option('ds_studio_custom_styles', array());
    }
    
    /**
     * Register dynamic block styles from saved data
     */
    public function register_dynamic_block_styles() {
        $custom_styles = $this->get_saved_custom_styles();
        
        foreach ($custom_styles as $block_type => $styles) {
            foreach ($styles as $style_name => $style_data) {
                $this->register_single_block_style($block_type, $style_name, $style_data['label']);
            }
        }
    }
    
    /**
     * Register a single block style
     */
    private function register_single_block_style($block_type, $style_name, $style_label) {
        register_block_style($block_type, array(
            'name' => $style_name,
            'label' => $style_label,
            'style_handle' => 'ds-studio-custom-styles'
        ));
    }
    
    /**
     * Generate CSS for custom styles
     */
    public function generate_custom_styles_css() {
        $custom_styles = $this->get_saved_custom_styles();
        $css = '';
        
        foreach ($custom_styles as $block_type => $styles) {
            foreach ($styles as $style_name => $style_data) {
                $attributes = $style_data['attributes'];
                $block_class = str_replace('core/', 'wp-block-', $block_type);
                $style_class = ".{$block_class}.is-style-{$style_name}";
                
                $css .= $this->attributes_to_css($style_class, $attributes);
            }
        }
        
        return $css;
    }
    
    /**
     * Convert block attributes to CSS
     */
    private function attributes_to_css($selector, $attributes) {
        $css = "{$selector} {\n";
        
        // Typography
        if (isset($attributes['fontSize'])) {
            $css .= "    font-size: var(--wp--preset--font-size--{$attributes['fontSize']}) !important;\n";
        }
        
        if (isset($attributes['fontFamily'])) {
            $css .= "    font-family: var(--wp--preset--font-family--{$attributes['fontFamily']}) !important;\n";
        }
        
        // Colors
        if (isset($attributes['textColor'])) {
            $css .= "    color: var(--wp--preset--color--{$attributes['textColor']}) !important;\n";
        }
        
        if (isset($attributes['backgroundColor'])) {
            $css .= "    background-color: var(--wp--preset--color--{$attributes['backgroundColor']}) !important;\n";
        }
        
        // Custom colors (hex values)
        if (isset($attributes['style']['color']['text'])) {
            $css .= "    color: {$attributes['style']['color']['text']} !important;\n";
        }
        
        if (isset($attributes['style']['color']['background'])) {
            $css .= "    background-color: {$attributes['style']['color']['background']} !important;\n";
        }
        
        // Typography styles
        if (isset($attributes['style']['typography']['fontSize'])) {
            $css .= "    font-size: {$attributes['style']['typography']['fontSize']} !important;\n";
        }
        
        if (isset($attributes['style']['typography']['fontWeight'])) {
            $css .= "    font-weight: {$attributes['style']['typography']['fontWeight']} !important;\n";
        }
        
        if (isset($attributes['style']['typography']['lineHeight'])) {
            $css .= "    line-height: {$attributes['style']['typography']['lineHeight']} !important;\n";
        }
        
        if (isset($attributes['style']['typography']['letterSpacing'])) {
            $css .= "    letter-spacing: {$attributes['style']['typography']['letterSpacing']} !important;\n";
        }
        
        if (isset($attributes['style']['typography']['textTransform'])) {
            $css .= "    text-transform: {$attributes['style']['typography']['textTransform']} !important;\n";
        }
        
        // Spacing
        if (isset($attributes['style']['spacing']['margin'])) {
            $margin = $attributes['style']['spacing']['margin'];
            if (isset($margin['top'])) $css .= "    margin-top: {$margin['top']} !important;\n";
            if (isset($margin['right'])) $css .= "    margin-right: {$margin['right']} !important;\n";
            if (isset($margin['bottom'])) $css .= "    margin-bottom: {$margin['bottom']} !important;\n";
            if (isset($margin['left'])) $css .= "    margin-left: {$margin['left']} !important;\n";
        }
        
        if (isset($attributes['style']['spacing']['padding'])) {
            $padding = $attributes['style']['spacing']['padding'];
            if (isset($padding['top'])) $css .= "    padding-top: {$padding['top']} !important;\n";
            if (isset($padding['right'])) $css .= "    padding-right: {$padding['right']} !important;\n";
            if (isset($padding['bottom'])) $css .= "    padding-bottom: {$padding['bottom']} !important;\n";
            if (isset($padding['left'])) $css .= "    padding-left: {$padding['left']} !important;\n";
        }
        
        // Border
        if (isset($attributes['style']['border'])) {
            $border = $attributes['style']['border'];
            if (isset($border['width'])) $css .= "    border-width: {$border['width']} !important;\n";
            if (isset($border['style'])) $css .= "    border-style: {$border['style']} !important;\n";
            if (isset($border['color'])) $css .= "    border-color: {$border['color']} !important;\n";
            if (isset($border['radius'])) $css .= "    border-radius: {$border['radius']} !important;\n";
        }
        
        // Text alignment
        if (isset($attributes['textAlign'])) {
            $css .= "    text-align: {$attributes['textAlign']} !important;\n";
        }
        
        $css .= "}\n\n";
        
        return $css;
    }
    
    /**
     * Enqueue frontend styles
     */
    public function enqueue_frontend_styles() {
        $css = $this->generate_custom_styles_css();
        
        if (!empty($css)) {
            wp_register_style(
                'ds-studio-custom-styles',
                false,
                array(),
                DS_STUDIO_VERSION
            );
            
            wp_enqueue_style('ds-studio-custom-styles');
            wp_add_inline_style('ds-studio-custom-styles', $css);
        }
    }
}

// Initialize style builder
new DS_Studio_Style_Builder();
?>
