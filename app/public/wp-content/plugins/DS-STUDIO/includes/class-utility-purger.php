<?php
/**
 * DS-Studio Utility Purger
 * 
 * Scans site files and only generates utilities that are actually used
 */

if (!defined('ABSPATH')) {
    exit;
}

class DS_Studio_Utility_Purger {
    
    private $used_utilities = array();
    private $scan_paths = array();
    
    public function __construct() {
        add_action('wp_ajax_ds_studio_scan_utilities', array($this, 'scan_utilities_ajax'));
        add_action('wp_ajax_ds_studio_purge_utilities', array($this, 'purge_utilities_ajax'));
    }
    
    /**
     * Initialize scan paths
     */
    private function init_scan_paths() {
        $this->scan_paths = array(
            // Active theme files
            get_template_directory(),
            get_stylesheet_directory(),
            
            // Plugin files (optional - can be configured)
            WP_PLUGIN_DIR,
            
            // WordPress content directory
            WP_CONTENT_DIR . '/themes',
        );
        
        // Filter out non-existent paths
        $this->scan_paths = array_filter($this->scan_paths, 'is_dir');
    }
    
    /**
     * Scan all theme and template files for used utility classes
     */
    public function scan_for_used_utilities() {
        $this->init_scan_paths();
        $this->used_utilities = array();
        
        foreach ($this->scan_paths as $path) {
            $this->scan_directory($path);
        }
        
        // Also scan database content (posts, pages, widgets, etc.)
        $this->scan_database_content();
        
        // Add component utilities that are used
        $this->scan_component_utilities();
        
        return array_unique($this->used_utilities);
    }
    
    /**
     * Scan a directory for utility classes
     */
    private function scan_directory($directory) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($this->should_scan_file($file)) {
                $this->scan_file($file->getPathname());
            }
        }
    }
    
    /**
     * Check if file should be scanned
     */
    private function should_scan_file($file) {
        $extension = strtolower($file->getExtension());
        $allowed_extensions = array('php', 'html', 'htm', 'twig', 'blade');
        
        // Skip certain directories
        $skip_dirs = array('node_modules', '.git', 'vendor', 'cache', 'logs');
        $path = $file->getPathname();
        
        foreach ($skip_dirs as $skip_dir) {
            if (strpos($path, '/' . $skip_dir . '/') !== false) {
                return false;
            }
        }
        
        return in_array($extension, $allowed_extensions);
    }
    
    /**
     * Scan individual file for utility classes
     */
    private function scan_file($file_path) {
        $content = file_get_contents($file_path);
        if ($content === false) {
            return;
        }
        
        // Extract all class attributes
        preg_match_all('/class=["\']([^"\']*)["\']/', $content, $matches);
        
        foreach ($matches[1] as $class_string) {
            $classes = preg_split('/\s+/', trim($class_string));
            foreach ($classes as $class) {
                if ($this->is_utility_class($class)) {
                    $this->used_utilities[] = $class;
                }
            }
        }
        
        // Also scan for PHP function calls that generate utilities
        $this->scan_php_utility_functions($content);
    }
    
    /**
     * Scan for PHP utility function calls
     */
    private function scan_php_utility_functions($content) {
        // Scan for ds_component() calls
        preg_match_all('/ds_component\(["\']([^"\']*)["\']/', $content, $matches);
        foreach ($matches[1] as $component_slug) {
            $component_classes = $this->get_component_classes($component_slug);
            if ($component_classes) {
                $classes = explode(' ', $component_classes);
                $this->used_utilities = array_merge($this->used_utilities, $classes);
            }
        }
        
        // Scan for ds_components() calls
        preg_match_all('/ds_components\(\s*\[([^\]]*)\]/', $content, $matches);
        foreach ($matches[1] as $components_array) {
            preg_match_all('/["\']([^"\']*)["\']/', $components_array, $component_matches);
            foreach ($component_matches[1] as $component_slug) {
                $component_classes = $this->get_component_classes($component_slug);
                if ($component_classes) {
                    $classes = explode(' ', $component_classes);
                    $this->used_utilities = array_merge($this->used_utilities, $classes);
                }
            }
        }
        
        // Scan for shortcode usage
        preg_match_all('/\[ds_component[^\]]*name=["\']([^"\']*)["\'][^\]]*\]/', $content, $matches);
        foreach ($matches[1] as $component_slug) {
            $component_classes = $this->get_component_classes($component_slug);
            if ($component_classes) {
                $classes = explode(' ', $component_classes);
                $this->used_utilities = array_merge($this->used_utilities, $classes);
            }
        }
    }
    
    /**
     * Scan database content for utility classes
     */
    private function scan_database_content() {
        global $wpdb;
        
        // Scan post content
        $posts = $wpdb->get_results("
            SELECT post_content 
            FROM {$wpdb->posts} 
            WHERE post_status = 'publish' 
            AND post_type IN ('post', 'page')
        ");
        
        foreach ($posts as $post) {
            $this->extract_classes_from_content($post->post_content);
        }
        
        // Scan widget content
        $widgets = $wpdb->get_results("
            SELECT option_value 
            FROM {$wpdb->options} 
            WHERE option_name LIKE 'widget_%'
        ");
        
        foreach ($widgets as $widget) {
            $this->extract_classes_from_content($widget->option_value);
        }
        
        // Scan customizer settings
        $customizer = get_option('theme_mods_' . get_option('stylesheet'));
        if ($customizer) {
            $this->extract_classes_from_content(serialize($customizer));
        }
    }
    
    /**
     * Extract utility classes from content
     */
    private function extract_classes_from_content($content) {
        preg_match_all('/class=["\']([^"\']*)["\']/', $content, $matches);
        
        foreach ($matches[1] as $class_string) {
            $classes = preg_split('/\s+/', trim($class_string));
            foreach ($classes as $class) {
                if ($this->is_utility_class($class)) {
                    $this->used_utilities[] = $class;
                }
            }
        }
    }
    
    /**
     * Scan component utilities that are actually used
     */
    private function scan_component_utilities() {
        $components = get_option('ds_studio_components', array());
        
        foreach ($components as $slug => $component) {
            // Check if this component is actually used
            if ($this->is_component_used($slug)) {
                $classes = explode(' ', $component['classes']);
                $this->used_utilities = array_merge($this->used_utilities, $classes);
            }
        }
    }
    
    /**
     * Check if a component is actually used in the site
     */
    private function is_component_used($component_slug) {
        // This would need to scan for component usage
        // For now, we'll include all components, but this could be optimized
        return true;
    }
    
    /**
     * Check if a class is a utility class (matches our naming patterns)
     */
    private function is_utility_class($class) {
        $utility_patterns = array(
            // Spacing utilities
            '/^(m|p|gap)-(xs|sm|base|md|lg|xl|2xl|3xl|4xl|5xl|\d+)$/',
            '/^(mt|mr|mb|ml|mx|my|pt|pr|pb|pl|px|py)-(xs|sm|base|md|lg|xl|2xl|3xl|4xl|5xl|\d+|auto)$/',
            
            // Color utilities
            '/^(text|bg|border)-(primary|secondary|accent|neutral|gray|white|black)(-\d+)?$/',
            
            // Typography utilities
            '/^text-(xs|sm|base|lg|xl|2xl|3xl|4xl|5xl|6xl)$/',
            '/^font-(heading|body|mono|light|normal|medium|semibold|bold|extrabold)$/',
            '/^(leading|tracking)-(tight|normal|relaxed|loose)$/',
            
            // Border utilities
            '/^border-(thin|base|thick|\d+)$/',
            '/^border-(solid|dashed|dotted|none)$/',
            '/^rounded-(none|sm|base|md|lg|xl|2xl|full)$/',
            
            // Layout utilities
            '/^container-(prose|narrow|wide|full)$/',
            '/^aspect-(square|video|cinema|\d+-\d+)$/',
            '/^z-(dropdown|sticky|fixed|modal|popover|tooltip|toast|overlay|max|\d+)$/',
            '/^grid-(\d+-col)$/',
            
            // Shadow utilities
            '/^shadow-(xs|sm|base|md|lg|xl|2xl|inner|glow|glow-lg)$/',
            
            // Common utilities
            '/^(flex|grid|block|inline|hidden|relative|absolute|fixed|sticky)$/',
            '/^(items|justify|content|self)-(start|end|center|between|around|evenly|stretch|baseline)$/',
            '/^(w|h)-(auto|full|\d+)$/',
        );
        
        foreach ($utility_patterns as $pattern) {
            if (preg_match($pattern, $class)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get component classes by slug
     */
    private function get_component_classes($slug) {
        $components = get_option('ds_studio_components', array());
        return isset($components[$slug]) ? $components[$slug]['classes'] : '';
    }
    
    /**
     * Generate purged CSS with only used utilities
     */
    public function generate_purged_css() {
        $used_utilities = $this->scan_for_used_utilities();
        
        // Get the utility generator
        $utility_generator = new DS_Studio_Utility_Generator();
        
        // Generate CSS only for used utilities
        $css_content = $utility_generator->generate_purged_utilities($used_utilities);
        
        // Write purged CSS file
        $upload_dir = wp_upload_dir();
        $css_file_path = $upload_dir['basedir'] . '/ds-studio-utilities-purged.css';
        
        file_put_contents($css_file_path, $css_content);
        
        // Update option to use purged CSS
        update_option('ds_studio_use_purged_css', true);
        update_option('ds_studio_purged_css_url', $upload_dir['baseurl'] . '/ds-studio-utilities-purged.css');
        
        return array(
            'used_utilities' => $used_utilities,
            'css_file_size' => filesize($css_file_path),
            'utilities_count' => count($used_utilities)
        );
    }
    
    /**
     * AJAX handler for scanning utilities
     */
    public function scan_utilities_ajax() {
        if (!wp_verify_nonce($_POST['nonce'], 'ds_studio_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('edit_theme_options')) {
            wp_die('Insufficient permissions');
        }
        
        $used_utilities = $this->scan_for_used_utilities();
        
        wp_send_json_success(array(
            'used_utilities' => $used_utilities,
            'count' => count($used_utilities),
            'message' => 'Found ' . count($used_utilities) . ' utilities in use'
        ));
    }
    
    /**
     * AJAX handler for purging utilities
     */
    public function purge_utilities_ajax() {
        if (!wp_verify_nonce($_POST['nonce'], 'ds_studio_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('edit_theme_options')) {
            wp_die('Insufficient permissions');
        }
        
        $result = $this->generate_purged_css();
        
        wp_send_json_success(array(
            'message' => 'Purged CSS generated successfully',
            'utilities_count' => $result['utilities_count'],
            'file_size' => $this->format_file_size($result['css_file_size']),
            'used_utilities' => $result['used_utilities']
        ));
    }
    
    /**
     * Format file size for display
     */
    private function format_file_size($bytes) {
        if ($bytes >= 1024 * 1024) {
            return round($bytes / (1024 * 1024), 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
    
    /**
     * Switch back to full CSS
     */
    public function use_full_css() {
        update_option('ds_studio_use_purged_css', false);
        delete_option('ds_studio_purged_css_url');
    }
    
    /**
     * Get purging statistics
     */
    public function get_purge_stats() {
        $full_css_path = wp_upload_dir()['basedir'] . '/ds-studio-utilities.css';
        $purged_css_path = wp_upload_dir()['basedir'] . '/ds-studio-utilities-purged.css';
        
        $stats = array(
            'full_css_exists' => file_exists($full_css_path),
            'purged_css_exists' => file_exists($purged_css_path),
            'using_purged' => get_option('ds_studio_use_purged_css', false),
            'full_css_size' => file_exists($full_css_path) ? filesize($full_css_path) : 0,
            'purged_css_size' => file_exists($purged_css_path) ? filesize($purged_css_path) : 0,
        );
        
        if ($stats['full_css_size'] > 0 && $stats['purged_css_size'] > 0) {
            $stats['size_reduction'] = round((1 - $stats['purged_css_size'] / $stats['full_css_size']) * 100, 1);
        }
        
        return $stats;
    }
}

// Initialize purger
new DS_Studio_Utility_Purger();
