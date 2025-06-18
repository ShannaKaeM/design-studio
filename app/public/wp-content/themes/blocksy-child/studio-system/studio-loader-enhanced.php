<?php
/**
 * Enhanced Studio System Loader
 * Loads all components of Daniel's CSS-driven design system
 */

class StudioLoaderEnhanced {
    
    private static $instance = null;
    
    public static function init() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_dependencies();
        $this->init_components();
        $this->setup_hooks();
    }
    
    /**
     * Load all required files
     */
    private function load_dependencies() {
        $base_path = get_stylesheet_directory() . '/studio-system/';
        
        // Core components
        require_once $base_path . 'variable-scanner-enhanced.php';
        require_once $base_path . 'control-generator.php';
        require_once $base_path . 'css-sync.php';
        require_once $base_path . 'selector-builder-enhanced.php';
        require_once $base_path . 'custom-elements-enhanced.php';
        
        // Admin interface
        require_once $base_path . 'admin-page-s.php';
        
        // Keep existing components that still work
        if (file_exists($base_path . 'generate-utilities.php')) {
            require_once $base_path . 'generate-utilities.php';
        }
        if (file_exists($base_path . 'json-fields.php')) {
            require_once $base_path . 'json-fields.php';
        }
    }
    
    /**
     * Initialize all components
     */
    private function init_components() {
        // Components initialize themselves via their constructors
        // This ensures proper loading order
    }
    
    /**
     * Setup WordPress hooks
     */
    private function setup_hooks() {
        // Frontend CSS loading
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_styles']);
        
        // Block editor CSS
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_styles']);
        
        // Custom element parsing
        add_filter('the_content', [$this, 'parse_custom_elements'], 5);
        
        // REST API endpoints
        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }
    
    /**
     * Enqueue frontend styles
     */
    public function enqueue_frontend_styles() {
        // S system CSS is already loaded by functions.php
        // This method is kept for compatibility but doesn't load anything
        // to avoid duplicate enqueues
    }
    
    /**
     * Enqueue editor styles
     */
    public function enqueue_editor_styles() {
        // S system CSS is already loaded by functions.php
        // This method is kept for compatibility
    }
    
    /**
     * Parse custom elements in content
     */
    public function parse_custom_elements($content) {
        if (is_admin() || wp_is_json_request()) {
            return $content;
        }
        
        $parser = new StudioCustomElementsEnhanced();
        return $parser->convert_to_blocks($content);
    }
    
    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route('studio/v1', '/variables', [
            'methods' => 'GET',
            'callback' => [$this, 'get_variables'],
            'permission_callback' => '__return_true'
        ]);
        
        register_rest_route('studio/v1', '/variables', [
            'methods' => 'POST',
            'callback' => [$this, 'update_variable'],
            'permission_callback' => [$this, 'check_permission']
        ]);
        
        register_rest_route('studio/v1', '/selectors', [
            'methods' => 'GET',
            'callback' => [$this, 'get_selectors'],
            'permission_callback' => '__return_true'
        ]);
        
        register_rest_route('studio/v1', '/parse-html', [
            'methods' => 'POST',
            'callback' => [$this, 'parse_html'],
            'permission_callback' => [$this, 'check_permission']
        ]);
    }
    
    /**
     * REST: Get all variables
     */
    public function get_variables() {
        $scanner = new StudioVariableScanner();
        $css_dir = get_stylesheet_directory() . '/assets/css';
        $variables = $scanner->scan_directory($css_dir);
        
        return rest_ensure_response($variables);
    }
    
    /**
     * REST: Update variable value
     */
    public function update_variable($request) {
        $variable = $request->get_param('variable');
        $value = $request->get_param('value');
        
        $saved_vars = get_option('studio_variable_values', []);
        $saved_vars[$variable] = $value;
        update_option('studio_variable_values', $saved_vars);
        
        return rest_ensure_response([
            'success' => true,
            'variable' => $variable,
            'value' => $value
        ]);
    }
    
    /**
     * REST: Get all selectors
     */
    public function get_selectors() {
        $builder = new StudioSelectorBuilder();
        return rest_ensure_response($builder->get_selectors());
    }
    
    /**
     * REST: Parse HTML to blocks
     */
    public function parse_html($request) {
        $html = $request->get_param('html');
        $parser = new StudioCustomElementsEnhanced();
        $blocks = $parser->convert_to_blocks($html);
        
        return rest_ensure_response([
            'blocks' => $blocks
        ]);
    }
    
    /**
     * Check REST API permissions
     */
    public function check_permission() {
        return current_user_can('edit_theme_options');
    }
}

// Initialize the enhanced Studio system
add_action('after_setup_theme', function() {
    StudioLoaderEnhanced::init();
});