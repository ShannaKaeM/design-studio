<?php
/**
 * Studio YAML Sync System
 * 
 * Two-way sync between YAML files and ACF database
 * Based on Daniel's recommendation for human/AI-friendly content
 * 
 * @package TheStudio
 */

namespace Studio\Core;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class YamlSync {
    
    /**
     * Content directories
     */
    private $content_dir;
    private $sync_dir;
    
    /**
     * Sync status
     */
    private $sync_status = [];
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->content_dir = WP_CONTENT_DIR . '/studio-data';
        $this->sync_dir = $this->content_dir . '/sync';
        
        // Ensure directories exist
        $this->ensure_directories();
        
        // Load Symfony YAML if available
        $this->load_yaml_parser();
    }
    
    /**
     * Load YAML parser
     */
    private function load_yaml_parser() {
        // Check if Symfony YAML is available via Composer
        $vendor_path = STUDIO_DIR . '/vendor/autoload.php';
        if (file_exists($vendor_path)) {
            require_once $vendor_path;
        } else {
            // Use built-in YAML functions if available
            if (!function_exists('yaml_parse')) {
                add_action('admin_notices', function() {
                    ?>
                    <div class="notice notice-warning">
                        <p><?php _e('YAML sync requires the YAML PHP extension or Symfony YAML component.', 'the-studio'); ?></p>
                    </div>
                    <?php
                });
            }
        }
    }
    
    /**
     * Ensure directories exist
     */
    private function ensure_directories() {
        $dirs = [
            $this->content_dir,
            $this->content_dir . '/villas',
            $this->content_dir . '/owners',
            $this->content_dir . '/committees',
            $this->sync_dir
        ];
        
        foreach ($dirs as $dir) {
            if (!file_exists($dir)) {
                wp_mkdir_p($dir);
            }
        }
    }
    
    /**
     * Sync YAML to database
     */
    public function sync_to_database($type, $file_path = null) {
        $files = [];
        
        if ($file_path && file_exists($file_path)) {
            $files[] = $file_path;
        } else {
            // Get all YAML files for type
            $dir = $this->content_dir . '/' . $type;
            if (file_exists($dir)) {
                $files = glob($dir . '/*.yaml');
            }
        }
        
        $synced = 0;
        $errors = [];
        
        foreach ($files as $file) {
            try {
                $data = $this->parse_yaml_file($file);
                
                if ($data) {
                    $result = $this->save_to_database($type, $data);
                    if ($result) {
                        $synced++;
                        $this->log_sync($file, 'to_database', 'success');
                    } else {
                        $errors[] = basename($file) . ': Failed to save';
                    }
                }
            } catch (\Exception $e) {
                $errors[] = basename($file) . ': ' . $e->getMessage();
                $this->log_sync($file, 'to_database', 'error', $e->getMessage());
            }
        }
        
        return [
            'synced' => $synced,
            'total' => count($files),
            'errors' => $errors
        ];
    }
    
    /**
     * Sync database to YAML
     */
    public function sync_to_yaml($type, $post_id = null) {
        $posts = [];
        
        if ($post_id) {
            $post = get_post($post_id);
            if ($post) {
                $posts[] = $post;
            }
        } else {
            // Get all posts of type
            $posts = get_posts([
                'post_type' => 'villa_' . $type,
                'posts_per_page' => -1,
                'post_status' => 'any'
            ]);
        }
        
        $synced = 0;
        $errors = [];
        
        foreach ($posts as $post) {
            try {
                $data = $this->extract_post_data($post);
                $file_path = $this->get_yaml_path($type, $post);
                
                if ($this->write_yaml_file($file_path, $data)) {
                    $synced++;
                    $this->log_sync($file_path, 'to_yaml', 'success');
                } else {
                    $errors[] = $post->post_title . ': Failed to write file';
                }
            } catch (\Exception $e) {
                $errors[] = $post->post_title . ': ' . $e->getMessage();
                $this->log_sync('', 'to_yaml', 'error', $e->getMessage());
            }
        }
        
        return [
            'synced' => $synced,
            'total' => count($posts),
            'errors' => $errors
        ];
    }
    
    /**
     * Parse YAML file
     */
    private function parse_yaml_file($file_path) {
        if (!file_exists($file_path)) {
            throw new \Exception('File not found');
        }
        
        $content = file_get_contents($file_path);
        
        // Try Symfony YAML first
        if (class_exists('Symfony\Component\Yaml\Yaml')) {
            try {
                return Yaml::parse($content);
            } catch (ParseException $e) {
                throw new \Exception('YAML parse error: ' . $e->getMessage());
            }
        }
        
        // Fall back to PHP YAML extension
        if (function_exists('yaml_parse')) {
            $data = yaml_parse($content);
            if ($data === false) {
                throw new \Exception('YAML parse error');
            }
            return $data;
        }
        
        // No YAML parser available
        throw new \Exception('No YAML parser available');
    }
    
    /**
     * Write YAML file
     */
    private function write_yaml_file($file_path, $data) {
        // Add metadata
        $data['_meta'] = [
            'last_sync' => current_time('Y-m-d H:i:s'),
            'sync_version' => STUDIO_VERSION
        ];
        
        // Try Symfony YAML first
        if (class_exists('Symfony\Component\Yaml\Yaml')) {
            $yaml = Yaml::dump($data, 4, 2);
            return file_put_contents($file_path, $yaml) !== false;
        }
        
        // Fall back to PHP YAML extension
        if (function_exists('yaml_emit')) {
            $yaml = yaml_emit($data);
            return file_put_contents($file_path, $yaml) !== false;
        }
        
        // No YAML parser available - save as JSON
        $json_path = str_replace('.yaml', '.json', $file_path);
        return file_put_contents($json_path, json_encode($data, JSON_PRETTY_PRINT)) !== false;
    }
    
    /**
     * Save data to database
     */
    private function save_to_database($type, $data) {
        // Map type to post type
        $post_type = 'villa_' . rtrim($type, 's'); // Remove plural
        
        // Check if post exists
        $existing = null;
        if (!empty($data['id'])) {
            $existing = get_post($data['id']);
        } elseif (!empty($data['slug'])) {
            $existing = get_page_by_path($data['slug'], OBJECT, $post_type);
        }
        
        // Prepare post data
        $post_data = [
            'post_type' => $post_type,
            'post_title' => $data['title'] ?? $data['name'] ?? 'Untitled',
            'post_content' => $data['content'] ?? $data['description'] ?? '',
            'post_status' => $data['status'] ?? 'publish',
            'post_name' => $data['slug'] ?? sanitize_title($data['title'] ?? $data['name'] ?? '')
        ];
        
        if ($existing) {
            $post_data['ID'] = $existing->ID;
            $post_id = wp_update_post($post_data);
        } else {
            $post_id = wp_insert_post($post_data);
        }
        
        if (is_wp_error($post_id)) {
            return false;
        }
        
        // Save ACF fields
        if (function_exists('update_field') && !empty($data['fields'])) {
            foreach ($data['fields'] as $field_key => $field_value) {
                update_field($field_key, $field_value, $post_id);
            }
        }
        
        // Save metadata
        if (!empty($data['meta'])) {
            foreach ($data['meta'] as $meta_key => $meta_value) {
                update_post_meta($post_id, $meta_key, $meta_value);
            }
        }
        
        return $post_id;
    }
    
    /**
     * Extract post data for YAML
     */
    private function extract_post_data($post) {
        $data = [
            'id' => $post->ID,
            'title' => $post->post_title,
            'slug' => $post->post_name,
            'content' => $post->post_content,
            'status' => $post->post_status,
            'created' => $post->post_date,
            'modified' => $post->post_modified
        ];
        
        // Get ACF fields
        if (function_exists('get_fields')) {
            $fields = get_fields($post->ID);
            if ($fields) {
                $data['fields'] = $fields;
            }
        }
        
        // Get relevant metadata
        $meta_keys = apply_filters('studio_yaml_meta_keys', [
            '_thumbnail_id',
            'villa_address',
            'villa_owner',
            'owner_email',
            'owner_phone'
        ], $post);
        
        $meta = [];
        foreach ($meta_keys as $key) {
            $value = get_post_meta($post->ID, $key, true);
            if ($value) {
                $meta[$key] = $value;
            }
        }
        
        if (!empty($meta)) {
            $data['meta'] = $meta;
        }
        
        return $data;
    }
    
    /**
     * Get YAML file path for post
     */
    private function get_yaml_path($type, $post) {
        $dir = $this->content_dir . '/' . $type;
        $filename = $post->post_name . '.yaml';
        
        return $dir . '/' . $filename;
    }
    
    /**
     * Log sync operation
     */
    private function log_sync($file_path, $direction, $status, $message = '') {
        $log = [
            'file' => basename($file_path),
            'direction' => $direction,
            'status' => $status,
            'message' => $message,
            'timestamp' => current_time('Y-m-d H:i:s')
        ];
        
        $this->sync_status[] = $log;
        
        // Save to log file
        $log_file = $this->sync_dir . '/sync-log.json';
        $logs = [];
        
        if (file_exists($log_file)) {
            $logs = json_decode(file_get_contents($log_file), true) ?: [];
        }
        
        $logs[] = $log;
        
        // Keep only last 100 entries
        if (count($logs) > 100) {
            $logs = array_slice($logs, -100);
        }
        
        file_put_contents($log_file, json_encode($logs, JSON_PRETTY_PRINT));
    }
    
    /**
     * Get sync status
     */
    public function get_sync_status() {
        return $this->sync_status;
    }
    
    /**
     * Check if content needs sync
     */
    public function needs_sync($type, $file_or_post) {
        // Implementation for checking if sync is needed
        // Compare timestamps, checksums, etc.
        return true; // Placeholder
    }
}