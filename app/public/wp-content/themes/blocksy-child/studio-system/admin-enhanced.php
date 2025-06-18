<?php
/**
 * Enhanced Studio Admin Interface
 * Implements Daniel's CSS-driven control system
 */

class StudioAdminEnhanced {
    
    private $scanner;
    private $control_generator;
    private $css_sync;
    private $selector_builder;
    private $custom_elements;
    
    public function __construct() {
        // Initialize components
        $this->scanner = new StudioVariableScanner();
        $this->control_generator = new StudioControlGenerator();
        $this->css_sync = new StudioCSSSync();
        $this->selector_builder = new StudioSelectorBuilder();
        $this->custom_elements = new StudioCustomElementsEnhanced();
        
        // Admin hooks
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        
        // AJAX handlers
        add_action('wp_ajax_studio_save_variable', [$this, 'ajax_save_variable']);
        add_action('wp_ajax_studio_save_selector', [$this, 'ajax_save_selector']);
        add_action('wp_ajax_studio_sync_css', [$this, 'ajax_sync_css']);
        add_action('wp_ajax_studio_parse_html', [$this, 'ajax_parse_html']);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_theme_page(
            'Studio System',
            'Studio System',
            'edit_theme_options',
            'studio-system',
            [$this, 'render_admin_page']
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'appearance_page_studio-system') {
            return;
        }
        
        // React and dependencies
        wp_enqueue_script('wp-element');
        wp_enqueue_script('wp-components');
        wp_enqueue_script('wp-api-fetch');
        wp_enqueue_script('wp-i18n');
        
        // Studio admin script
        wp_enqueue_script(
            'studio-admin-enhanced',
            get_stylesheet_directory_uri() . '/studio-system/assets/js/admin-enhanced.js',
            ['wp-element', 'wp-components', 'wp-api-fetch', 'wp-i18n'],
            '1.0.0',
            true
        );
        
        // Localize data
        wp_localize_script('studio-admin-enhanced', 'studioData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('studio_admin'),
            'variables' => $this->get_all_variables(),
            'selectors' => $this->selector_builder->get_selectors(),
            'cssClasses' => $this->css_sync->get_organized_classes(),
            'customElements' => $this->custom_elements->get_element_docs()
        ]);
        
        // Admin styles
        wp_enqueue_style(
            'studio-admin-enhanced',
            get_stylesheet_directory_uri() . '/studio-system/assets/css/admin-enhanced.css',
            ['wp-components'],
            '1.0.0'
        );
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Studio System', 'studio'); ?></h1>
            <div id="studio-admin-app"></div>
        </div>
        <?php
    }
    
    /**
     * Get all variables with controls
     */
    private function get_all_variables() {
        $css_dir = get_stylesheet_directory() . '/assets/css';
        $variables = $this->scanner->scan_directory($css_dir);
        
        // Generate controls for each variable
        $controls = [];
        foreach ($variables as $var_name => $var_data) {
            if (isset($var_data['control'])) {
                $controls[$var_name] = $this->control_generator->generate_control($var_data);
            }
        }
        
        return [
            'variables' => $variables,
            'controls' => $controls,
            'categories' => $this->scanner->get_categorized_variables($variables)
        ];
    }
    
    /**
     * AJAX: Save variable value
     */
    public function ajax_save_variable() {
        check_ajax_referer('studio_admin', 'nonce');
        
        $variable = sanitize_text_field($_POST['variable']);
        $value = sanitize_text_field($_POST['value']);
        
        // Save to database
        $saved_vars = get_option('studio_variable_values', []);
        $saved_vars[$variable] = $value;
        update_option('studio_variable_values', $saved_vars);
        
        // Generate CSS
        $this->generate_custom_css();
        
        wp_send_json_success([
            'message' => 'Variable saved',
            'variable' => $variable,
            'value' => $value
        ]);
    }
    
    /**
     * AJAX: Save selector rule
     */
    public function ajax_save_selector() {
        check_ajax_referer('studio_admin', 'nonce');
        
        $name = sanitize_text_field($_POST['name']);
        $selector = sanitize_text_field($_POST['selector']);
        $variables = json_decode(stripslashes($_POST['variables']), true);
        
        $this->selector_builder->add_selector($name, $selector, $variables);
        
        // Generate CSS
        $this->generate_custom_css();
        
        wp_send_json_success([
            'message' => 'Selector saved',
            'selectors' => $this->selector_builder->get_selectors()
        ]);
    }
    
    /**
     * AJAX: Sync CSS files
     */
    public function ajax_sync_css() {
        check_ajax_referer('studio_admin', 'nonce');
        
        $css_dir = get_stylesheet_directory() . '/assets/css';
        $css_files = glob($css_dir . '/*.css');
        
        $this->css_sync->sync_from_files($css_files);
        
        wp_send_json_success([
            'message' => 'CSS synced successfully',
            'classes' => $this->css_sync->get_organized_classes()
        ]);
    }
    
    /**
     * AJAX: Parse custom HTML to blocks
     */
    public function ajax_parse_html() {
        check_ajax_referer('studio_admin', 'nonce');
        
        $html = stripslashes($_POST['html']);
        $blocks = $this->custom_elements->convert_to_blocks($html);
        
        wp_send_json_success([
            'blocks' => $blocks,
            'preview' => $this->generate_block_preview($blocks)
        ]);
    }
    
    /**
     * Generate custom CSS file
     */
    private function generate_custom_css() {
        $css = [];
        
        // Custom variable values
        $saved_vars = get_option('studio_variable_values', []);
        if (!empty($saved_vars)) {
            $css[] = ':root {';
            foreach ($saved_vars as $var => $value) {
                $css[] = "    $var: $value;";
            }
            $css[] = '}';
            $css[] = '';
        }
        
        // Selector rules
        $css[] = $this->selector_builder->generate_css();
        
        // Save to file
        $css_content = implode("\n", $css);
        $file_path = get_stylesheet_directory() . '/assets/css/studio-custom.css';
        file_put_contents($file_path, $css_content);
    }
    
    /**
     * Generate block preview
     */
    private function generate_block_preview($blocks) {
        // Simple preview for now
        return '<div class="block-preview">' . esc_html($blocks) . '</div>';
    }
}

// Initialize enhanced admin
new StudioAdminEnhanced();