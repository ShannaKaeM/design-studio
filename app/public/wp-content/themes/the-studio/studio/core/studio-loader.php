<?php
/**
 * Studio Loader
 * 
 * Main orchestration file for The Studio system
 * 
 * @package TheStudio
 */

namespace Studio\Core;

class StudioLoader {
    
    /**
     * Instance
     */
    private static $instance = null;
    
    /**
     * Components
     */
    private $scanner;
    private $generator;
    
    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize
     */
    private function init() {
        // Load components
        $this->load_components();
        
        // Hook into WordPress
        add_action('admin_init', [$this, 'admin_init']);
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);
        add_action('wp_ajax_studio_scan_variables', [$this, 'ajax_scan_variables']);
        add_action('wp_ajax_studio_save_variable', [$this, 'ajax_save_variable']);
        add_action('wp_ajax_studio_generate_utilities', [$this, 'ajax_generate_utilities']);
        
        // Add admin notices
        add_action('admin_notices', [$this, 'admin_notices']);
    }
    
    /**
     * Load components
     */
    private function load_components() {
        // Load scanner
        $this->scanner = new VariableScanner();
        
        // Load from database if available
        $this->scanner->load_from_database();
        
        // Load utility generator
        require_once STUDIO_DIR . '/studio/core/utility-generator.php';
        $this->generator = new UtilityGenerator($this->scanner);
        
        // Load selector builder
        require_once STUDIO_DIR . '/studio/core/selector-builder.php';
        
        // Load elements parser
        require_once STUDIO_DIR . '/studio/core/elements-parser.php';
    }
    
    /**
     * Admin init
     */
    public function admin_init() {
        // Scanner now handles fresh loading automatically
    }
    
    /**
     * Admin scripts
     */
    public function admin_scripts($hook) {
        // Only load on our admin pages
        if (!strpos($hook, 'the-studio')) {
            return;
        }
        
        // Enqueue color picker - MUST be done before our scripts
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        // Admin CSS
        wp_enqueue_style(
            'studio-admin',
            STUDIO_URL . '/studio/admin/studio-admin.css',
            ['wp-color-picker'],
            STUDIO_VERSION
        );
        
        // Admin JS - Use simple version for now
        wp_enqueue_script(
            'studio-admin',
            STUDIO_URL . '/studio/admin/studio-admin-simple.js',
            ['jquery', 'wp-color-picker', 'jquery-ui-core', 'jquery-ui-widget'],
            STUDIO_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('studio-admin', 'studio_admin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('studio_admin_nonce'),
            'variables' => $this->scanner->get_variables_by_category()
        ]);
    }
    
    /**
     * Scan variables
     */
    public function scan_variables() {
        $css_file = STUDIO_DIR . '/studio/css/studio-vars.css';
        
        if (file_exists($css_file)) {
            $variables = $this->scanner->scan_file($css_file);
            
            if ($variables) {
                $this->scanner->save_to_database();
                
                // Generate utilities after scanning
                $this->generate_utilities();
                do_action('studio_variables_scanned', $variables);
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * AJAX: Scan variables
     */
    public function ajax_scan_variables() {
        check_ajax_referer('studio_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $result = $this->scan_variables();
        
        if ($result) {
            wp_send_json_success([
                'message' => 'Variables scanned successfully',
                'variables' => $this->scanner->get_variables_by_category()
            ]);
        } else {
            wp_send_json_error('Failed to scan variables');
        }
    }
    
    /**
     * AJAX: Save variable value
     */
    public function ajax_save_variable() {
        check_ajax_referer('studio_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $variable_name = sanitize_text_field($_POST['variable']);
        $value = sanitize_text_field($_POST['value']);
        
        // Update CSS file
        $css_file = STUDIO_DIR . '/studio/css/studio-vars.css';
        $content = file_get_contents($css_file);
        
        // Get the original value to preserve units if needed
        $original_pattern = '/(' . preg_quote($variable_name) . '):\s*([^;]+);/';
        preg_match($original_pattern, $content, $matches);
        
        if (isset($matches[2])) {
            $original_value = trim($matches[2]);
            
            // Check if the original had units and the new value doesn't
            if (preg_match('/^([\d.]+)(rem|px|em|%|vh|vw)$/', $original_value, $unit_match)) {
                // If new value is just a number, add the original unit back
                if (is_numeric($value)) {
                    $value = $value . $unit_match[2];
                }
            }
        }
        
        // Replace variable value
        $pattern = '/(' . preg_quote($variable_name) . '):\s*([^;]+);/';
        $replacement = '$1: ' . $value . ';';
        
        $new_content = preg_replace($pattern, $replacement, $content);
        
        if ($new_content !== $content) {
            // Write the file
            $result = file_put_contents($css_file, $new_content);
            
            if ($result !== false) {
                // Clear any caches
                wp_cache_flush();
                
                // Rescan to update database
                $this->scan_variables();
                
                // Regenerate utilities
                $this->generate_utilities();
                
                wp_send_json_success([
                    'message' => 'Variable updated successfully',
                    'variable' => $variable_name,
                    'value' => $value
                ]);
            } else {
                wp_send_json_error('Failed to write CSS file. Check file permissions.');
            }
        } else {
            wp_send_json_error('No changes detected');
        }
    }
    
    /**
     * Get scanner instance
     */
    public function get_scanner() {
        return $this->scanner;
    }
    
    /**
     * AJAX: Generate utilities
     */
    public function ajax_generate_utilities() {
        check_ajax_referer('studio_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $result = $this->generate_utilities();
        
        if ($result) {
            wp_send_json_success([
                'message' => 'Utilities generated successfully',
                'count' => count($this->generator->get_utilities())
            ]);
        } else {
            wp_send_json_error('Failed to generate utilities');
        }
    }
    
    /**
     * Generate utilities
     */
    public function generate_utilities() {
        if (!$this->generator) {
            return false;
        }
        
        $this->generator->generate();
        
        // Write to CSS file
        $utilities_file = STUDIO_DIR . '/studio/css/studio-utilities.css';
        $result = $this->generator->write_css($utilities_file);
        
        if ($result) {
            // Enqueue the utilities CSS
            add_action('wp_enqueue_scripts', function() {
                wp_enqueue_style(
                    'studio-utilities',
                    STUDIO_URL . '/studio/css/studio-utilities.css',
                    [],
                    STUDIO_VERSION
                );
            });
            
            // Also enqueue in admin for preview
            add_action('admin_enqueue_scripts', function() {
                wp_enqueue_style(
                    'studio-utilities',
                    STUDIO_URL . '/studio/css/studio-utilities.css',
                    [],
                    STUDIO_VERSION
                );
            });
        }
        
        return $result;
    }
    
    /**
     * Admin notices
     */
    public function admin_notices() {
        $screen = get_current_screen();
        
        if ($screen && strpos($screen->id, 'the-studio') !== false) {
            $variables = $this->scanner->get_variables_by_category();
            
            if (empty($variables)) {
                ?>
                <div class="notice notice-warning">
                    <p><?php _e('No variables found. Click "Scan Variables" to detect CSS variables.', 'the-studio'); ?></p>
                </div>
                <?php
            }
        }
    }
}

// Initialize
add_action('init', function() {
    StudioLoader::get_instance();
});