<?php
/**
 * Studio JSON Fields System
 * File-based content management with WordPress integration
 */

class Studio_JSON_Fields {
    
    private $data_directory;
    private $sync_enabled = true;
    private $watched_post_types = [];
    
    public function __construct() {
        $this->data_directory = WP_CONTENT_DIR . '/studio-data';
        $this->init();
    }
    
    /**
     * Initialize the JSON Fields system
     */
    private function init() {
        // Create data directory if it doesn't exist
        if (!file_exists($this->data_directory)) {
            wp_mkdir_p($this->data_directory);
        }
        
        // Hook into WordPress
        add_action('init', [$this, 'register_hooks']);
        add_action('admin_init', [$this, 'sync_json_to_database']);
        
        // Admin notices
        add_action('admin_notices', [$this, 'admin_notices']);
    }
    
    /**
     * Register WordPress hooks
     */
    public function register_hooks() {
        // Sync on post save
        add_action('save_post', [$this, 'sync_post_to_json'], 10, 3);
        
        // Sync on post delete
        add_action('delete_post', [$this, 'delete_json_file']);
        
        // Add meta box for JSON preview
        add_action('add_meta_boxes', [$this, 'add_json_meta_box']);
        
        // REST API endpoint for JSON data
        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }
    
    /**
     * Register post types to watch
     */
    public function watch_post_type($post_type, $options = []) {
        $this->watched_post_types[$post_type] = wp_parse_args($options, [
            'sync_to_json' => true,
            'sync_from_json' => true,
            'json_fields' => [],
            'exclude_fields' => ['post_content', 'post_excerpt'],
            'directory' => $post_type
        ]);
        
        // Create post type directory
        $type_dir = $this->data_directory . '/' . $options['directory'];
        if (!file_exists($type_dir)) {
            wp_mkdir_p($type_dir);
        }
    }
    
    /**
     * Sync post data to JSON file
     */
    public function sync_post_to_json($post_id, $post, $update) {
        // Check if this post type is watched
        if (!isset($this->watched_post_types[$post->post_type])) {
            return;
        }
        
        // Skip auto-saves
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        $config = $this->watched_post_types[$post->post_type];
        if (!$config['sync_to_json']) {
            return;
        }
        
        // Prepare data
        $data = $this->prepare_post_data($post, $config);
        
        // Save to JSON
        $this->save_json_file($post->post_type, $post->post_name, $data);
    }
    
    /**
     * Prepare post data for JSON export
     */
    private function prepare_post_data($post, $config) {
        $data = [
            'id' => $post->ID,
            'title' => $post->post_title,
            'slug' => $post->post_name,
            'status' => $post->post_status,
            'date' => $post->post_date,
            'modified' => $post->post_modified,
            'type' => $post->post_type
        ];
        
        // Add content fields if not excluded
        if (!in_array('post_content', $config['exclude_fields'])) {
            $data['content'] = $post->post_content;
        }
        
        if (!in_array('post_excerpt', $config['exclude_fields'])) {
            $data['excerpt'] = $post->post_excerpt;
        }
        
        // Add custom fields
        $data['fields'] = [];
        
        // Get all meta
        $meta = get_post_meta($post->ID);
        foreach ($meta as $key => $values) {
            // Skip private meta
            if (strpos($key, '_') === 0) continue;
            
            // Add to fields
            $data['fields'][$key] = count($values) === 1 ? $values[0] : $values;
        }
        
        // Add taxonomies
        $taxonomies = get_object_taxonomies($post->post_type);
        $data['taxonomies'] = [];
        
        foreach ($taxonomies as $taxonomy) {
            $terms = wp_get_object_terms($post->ID, $taxonomy, ['fields' => 'slugs']);
            if (!empty($terms)) {
                $data['taxonomies'][$taxonomy] = $terms;
            }
        }
        
        // Add featured image
        if (has_post_thumbnail($post->ID)) {
            $data['featured_image'] = [
                'id' => get_post_thumbnail_id($post->ID),
                'url' => get_the_post_thumbnail_url($post->ID, 'full'),
                'alt' => get_post_meta(get_post_thumbnail_id($post->ID), '_wp_attachment_image_alt', true)
            ];
        }
        
        // Allow filtering
        return apply_filters('studio_json_fields_data', $data, $post, $config);
    }
    
    /**
     * Save data to JSON file
     */
    private function save_json_file($post_type, $slug, $data) {
        $config = $this->watched_post_types[$post_type];
        $dir = $this->data_directory . '/' . $config['directory'];
        $file_path = $dir . '/' . $slug . '/fields.json';
        
        // Create post directory
        $post_dir = dirname($file_path);
        if (!file_exists($post_dir)) {
            wp_mkdir_p($post_dir);
        }
        
        // Save JSON
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        file_put_contents($file_path, $json);
        
        // Create index file
        $this->update_index($post_type);
    }
    
    /**
     * Delete JSON file when post is deleted
     */
    public function delete_json_file($post_id) {
        $post = get_post($post_id);
        
        if (!$post || !isset($this->watched_post_types[$post->post_type])) {
            return;
        }
        
        $config = $this->watched_post_types[$post->post_type];
        $dir = $this->data_directory . '/' . $config['directory'] . '/' . $post->post_name;
        
        if (file_exists($dir)) {
            $this->delete_directory($dir);
            $this->update_index($post->post_type);
        }
    }
    
    /**
     * Sync JSON files to database
     */
    public function sync_json_to_database() {
        if (!$this->sync_enabled) {
            return;
        }
        
        foreach ($this->watched_post_types as $post_type => $config) {
            if (!$config['sync_from_json']) {
                continue;
            }
            
            $this->sync_post_type_from_json($post_type);
        }
    }
    
    /**
     * Sync a specific post type from JSON
     */
    private function sync_post_type_from_json($post_type) {
        $config = $this->watched_post_types[$post_type];
        $dir = $this->data_directory . '/' . $config['directory'];
        
        if (!file_exists($dir)) {
            return;
        }
        
        // Get all JSON files
        $items = glob($dir . '/*/fields.json');
        
        foreach ($items as $file) {
            $data = json_decode(file_get_contents($file), true);
            
            if (!$data) {
                continue;
            }
            
            // Check if post exists
            $existing = get_page_by_path($data['slug'], OBJECT, $post_type);
            
            if ($existing) {
                // Update existing post
                $this->update_post_from_json($existing->ID, $data);
            } else {
                // Create new post
                $this->create_post_from_json($data, $post_type);
            }
        }
    }
    
    /**
     * Create post from JSON data
     */
    private function create_post_from_json($data, $post_type) {
        $post_data = [
            'post_title' => $data['title'],
            'post_name' => $data['slug'],
            'post_type' => $post_type,
            'post_status' => $data['status'] ?? 'publish',
            'post_date' => $data['date'] ?? current_time('mysql'),
        ];
        
        if (isset($data['content'])) {
            $post_data['post_content'] = $data['content'];
        }
        
        if (isset($data['excerpt'])) {
            $post_data['post_excerpt'] = $data['excerpt'];
        }
        
        // Create post
        $post_id = wp_insert_post($post_data);
        
        if (!is_wp_error($post_id)) {
            // Add meta fields
            if (isset($data['fields'])) {
                foreach ($data['fields'] as $key => $value) {
                    update_post_meta($post_id, $key, $value);
                }
            }
            
            // Add JSON reference
            update_post_meta($post_id, '_studio_json_fields', $data);
            
            // Add taxonomies
            if (isset($data['taxonomies'])) {
                foreach ($data['taxonomies'] as $taxonomy => $terms) {
                    wp_set_object_terms($post_id, $terms, $taxonomy);
                }
            }
        }
        
        return $post_id;
    }
    
    /**
     * Update post from JSON data
     */
    private function update_post_from_json($post_id, $data) {
        // Check if JSON is newer than post
        $post = get_post($post_id);
        $post_modified = strtotime($post->post_modified);
        $json_modified = strtotime($data['modified'] ?? $data['date']);
        
        if ($json_modified <= $post_modified) {
            return; // Post is newer
        }
        
        // Update post
        $post_data = [
            'ID' => $post_id,
            'post_title' => $data['title'],
            'post_modified' => $data['modified']
        ];
        
        if (isset($data['content'])) {
            $post_data['post_content'] = $data['content'];
        }
        
        wp_update_post($post_data);
        
        // Update meta
        if (isset($data['fields'])) {
            foreach ($data['fields'] as $key => $value) {
                update_post_meta($post_id, $key, $value);
            }
        }
        
        // Update JSON reference
        update_post_meta($post_id, '_studio_json_fields', $data);
    }
    
    /**
     * Update index file for post type
     */
    private function update_index($post_type) {
        $config = $this->watched_post_types[$post_type];
        $dir = $this->data_directory . '/' . $config['directory'];
        $index_file = $dir . '/index.json';
        
        $items = [];
        $files = glob($dir . '/*/fields.json');
        
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data) {
                $items[] = [
                    'id' => $data['id'],
                    'title' => $data['title'],
                    'slug' => $data['slug'],
                    'modified' => $data['modified'] ?? $data['date']
                ];
            }
        }
        
        file_put_contents($index_file, json_encode($items, JSON_PRETTY_PRINT));
    }
    
    /**
     * Add meta box for JSON preview
     */
    public function add_json_meta_box() {
        foreach ($this->watched_post_types as $post_type => $config) {
            add_meta_box(
                'studio_json_fields',
                'JSON Fields Data',
                [$this, 'render_json_meta_box'],
                $post_type,
                'side',
                'default'
            );
        }
    }
    
    /**
     * Render JSON meta box
     */
    public function render_json_meta_box($post) {
        $json_data = get_post_meta($post->ID, '_studio_json_fields', true);
        
        if ($json_data) {
            echo '<div style="max-height: 300px; overflow-y: auto;">';
            echo '<pre style="font-size: 11px; margin: 0;">';
            echo esc_html(json_encode($json_data, JSON_PRETTY_PRINT));
            echo '</pre>';
            echo '</div>';
        } else {
            echo '<p>No JSON data synced yet.</p>';
        }
        
        $config = $this->watched_post_types[$post->post_type];
        $file_path = $this->data_directory . '/' . $config['directory'] . '/' . $post->post_name . '/fields.json';
        
        if (file_exists($file_path)) {
            echo '<p><small>File: ' . esc_html(str_replace(WP_CONTENT_DIR, '', $file_path)) . '</small></p>';
            echo '<p><small>Modified: ' . date('Y-m-d H:i:s', filemtime($file_path)) . '</small></p>';
        }
    }
    
    /**
     * Register REST routes
     */
    public function register_rest_routes() {
        register_rest_route('studio/v1', '/json-fields/(?P<post_type>[a-zA-Z0-9_-]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_json_fields'],
            'permission_callback' => '__return_true'
        ]);
    }
    
    /**
     * REST API callback
     */
    public function get_json_fields($request) {
        $post_type = $request['post_type'];
        
        if (!isset($this->watched_post_types[$post_type])) {
            return new WP_Error('invalid_post_type', 'Post type not configured for JSON fields');
        }
        
        $config = $this->watched_post_types[$post_type];
        $index_file = $this->data_directory . '/' . $config['directory'] . '/index.json';
        
        if (file_exists($index_file)) {
            $data = json_decode(file_get_contents($index_file), true);
            return rest_ensure_response($data);
        }
        
        return rest_ensure_response([]);
    }
    
    /**
     * Admin notices
     */
    public function admin_notices() {
        if (isset($_GET['studio_json_sync'])) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>JSON fields synchronized successfully!</p>';
            echo '</div>';
        }
    }
    
    /**
     * Delete directory recursively
     */
    private function delete_directory($dir) {
        if (!file_exists($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->delete_directory($path) : unlink($path);
        }
        
        rmdir($dir);
    }
}

// Initialize JSON Fields system
function studio_json_fields() {
    static $instance = null;
    
    if ($instance === null) {
        $instance = new Studio_JSON_Fields();
    }
    
    return $instance;
}

// Helper function to register a post type for JSON sync
function studio_watch_post_type($post_type, $options = []) {
    studio_json_fields()->watch_post_type($post_type, $options);
}