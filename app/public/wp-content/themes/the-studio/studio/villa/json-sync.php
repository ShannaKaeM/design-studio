<?php
/**
 * JSON Sync System
 * 
 * Two-way sync between JSON files and ACF database
 * JSON remains the source of truth
 * 
 * @package TheStudio
 */

namespace Studio\Villa;

class JSONSync {
    
    /**
     * JSON data directory (from blocksy-child)
     */
    private $json_dir;
    
    /**
     * Sync status
     */
    private $sync_status = [];
    
    /**
     * Constructor
     */
    public function __construct() {
        // Use existing blocksy-child JSON data
        $this->json_dir = WP_CONTENT_DIR . '/themes/blocksy-child/villa-data';
        
        // Hook into save actions to sync back to JSON
        add_action('acf/save_post', [$this, 'sync_acf_to_json'], 20);
        
        // Add cron for periodic sync
        add_action('studio_sync_json_to_acf', [$this, 'sync_all_json_to_acf']);
        
        // Schedule if not already scheduled
        if (!wp_next_scheduled('studio_sync_json_to_acf')) {
            wp_schedule_event(time(), 'hourly', 'studio_sync_json_to_acf');
        }
    }
    
    /**
     * Sync all JSON files to ACF (JSON → Database)
     */
    public function sync_all_json_to_acf() {
        $this->sync_owners_to_acf();
        $this->sync_properties_to_acf();
        $this->sync_committees_to_acf();
        
        // Save sync status
        update_option('studio_last_json_sync', current_time('timestamp'));
        
        return $this->sync_status;
    }
    
    /**
     * Sync owners from JSON to ACF
     */
    private function sync_owners_to_acf() {
        $owners_dir = $this->json_dir . '/owners';
        $owner_files = glob($owners_dir . '/*/owner.json') + glob($owners_dir . '/*/profile.json');
        
        $this->sync_status['owners_created'] = 0;
        $this->sync_status['owners_updated'] = 0;
        
        foreach ($owner_files as $json_file) {
            $data = json_decode(file_get_contents($json_file), true);
            if (!$data) continue;
            
            // Extract owner ID from directory name (most reliable)
            preg_match('/\/owners\/(owner-[^\/]+)\//', $json_file, $matches);
            $unique_key = $matches[1] ?? '';
            
            // Fallback to data fields
            if (!$unique_key) {
                $unique_key = $data['owner_id'] ?? 'owner-' . sanitize_title($data['name'] ?? '');
            }
            
            // Check if owner exists
            $existing = get_posts([
                'post_type' => 'villa_owner',
                'meta_key' => '_json_key',
                'meta_value' => $unique_key,
                'posts_per_page' => 1
            ]);
            
            // Get the full name from personal_info
            $personal_info = $data['personal_info'] ?? [];
            $full_name = $personal_info['full_name'] ?? '';
            
            // Fallback to first + last name
            if (!$full_name) {
                $first = $personal_info['first_name'] ?? '';
                $last = $personal_info['last_name'] ?? '';
                $full_name = trim($first . ' ' . $last);
            }
            
            // Final fallback
            if (!$full_name) {
                $full_name = 'Unknown Owner';
            }
            
            $post_data = [
                'post_type' => 'villa_owner',
                'post_title' => $full_name,
                'post_status' => 'publish'
            ];
            
            if ($existing) {
                $post_data['ID'] = $existing[0]->ID;
                $post_id = wp_update_post($post_data);
                $this->sync_status['owners_updated']++;
            } else {
                $post_id = wp_insert_post($post_data);
                $this->sync_status['owners_created']++;
            }
            
            if ($post_id && !is_wp_error($post_id)) {
                // Store JSON key for future syncs
                update_post_meta($post_id, '_json_key', $unique_key);
                update_post_meta($post_id, '_json_file', $json_file);
                
                // Update ACF fields
                $this->update_owner_fields($post_id, $data);
            }
        }
    }
    
    /**
     * Sync properties from JSON to ACF
     */
    private function sync_properties_to_acf() {
        $properties_dir = $this->json_dir . '/properties';
        $property_files = glob($properties_dir . '/*/property.json');
        
        $this->sync_status['properties_created'] = 0;
        $this->sync_status['properties_updated'] = 0;
        
        foreach ($property_files as $json_file) {
            $data = json_decode(file_get_contents($json_file), true);
            if (!$data) continue;
            
            $unit_number = $data['unit_details']['unit_number'] ?? '';
            if (!$unit_number) continue;
            
            // Check if property exists
            $existing = get_posts([
                'post_type' => 'villa_property',
                'meta_key' => '_json_key',
                'meta_value' => $unit_number,
                'posts_per_page' => 1
            ]);
            
            $post_data = [
                'post_type' => 'villa_property',
                'post_title' => $data['unit_details']['display_name'] ?? $unit_number,
                'post_status' => 'publish'
            ];
            
            if ($existing) {
                $post_data['ID'] = $existing[0]->ID;
                $post_id = wp_update_post($post_data);
                $this->sync_status['properties_updated']++;
            } else {
                $post_id = wp_insert_post($post_data);
                $this->sync_status['properties_created']++;
            }
            
            if ($post_id && !is_wp_error($post_id)) {
                // Store JSON key for future syncs
                update_post_meta($post_id, '_json_key', $unit_number);
                update_post_meta($post_id, '_json_file', $json_file);
                
                // Update ACF fields
                $this->update_property_fields($post_id, $data);
            }
        }
    }
    
    /**
     * Sync committees from JSON to ACF
     */
    private function sync_committees_to_acf() {
        $committees_dir = $this->json_dir . '/committees';
        $committee_files = glob($committees_dir . '/*/committee.json');
        
        $this->sync_status['committees_created'] = 0;
        $this->sync_status['committees_updated'] = 0;
        
        foreach ($committee_files as $json_file) {
            $data = json_decode(file_get_contents($json_file), true);
            if (!$data) continue;
            
            $committee_id = $data['committee_id'] ?? sanitize_title($data['name'] ?? '');
            
            // Check if committee exists
            $existing = get_posts([
                'post_type' => 'villa_committee',
                'meta_key' => '_json_key',
                'meta_value' => $committee_id,
                'posts_per_page' => 1
            ]);
            
            $post_data = [
                'post_type' => 'villa_committee',
                'post_title' => $data['name'] ?? 'Unknown Committee',
                'post_content' => $data['description'] ?? '',
                'post_status' => 'publish'
            ];
            
            if ($existing) {
                $post_data['ID'] = $existing[0]->ID;
                $post_id = wp_update_post($post_data);
                $this->sync_status['committees_updated']++;
            } else {
                $post_id = wp_insert_post($post_data);
                $this->sync_status['committees_created']++;
            }
            
            if ($post_id && !is_wp_error($post_id)) {
                // Store JSON key for future syncs
                update_post_meta($post_id, '_json_key', $committee_id);
                update_post_meta($post_id, '_json_file', $json_file);
                
                // Update ACF fields
                $this->update_committee_fields($post_id, $data);
            }
        }
    }
    
    /**
     * Sync ACF changes back to JSON (Database → JSON)
     */
    public function sync_acf_to_json($post_id) {
        // Check if it's our post type
        $post_type = get_post_type($post_id);
        if (!in_array($post_type, ['villa_property', 'villa_owner', 'villa_committee'])) {
            return;
        }
        
        // Get the JSON file path
        $json_file = get_post_meta($post_id, '_json_file', true);
        if (!$json_file || !file_exists($json_file)) {
            return;
        }
        
        // Read existing JSON
        $json_data = json_decode(file_get_contents($json_file), true);
        if (!$json_data) {
            return;
        }
        
        // Update JSON with ACF data
        switch ($post_type) {
            case 'villa_owner':
                $json_data = $this->update_owner_json($post_id, $json_data);
                break;
            case 'villa_property':
                $json_data = $this->update_property_json($post_id, $json_data);
                break;
            case 'villa_committee':
                $json_data = $this->update_committee_json($post_id, $json_data);
                break;
        }
        
        // Save back to JSON file
        file_put_contents($json_file, json_encode($json_data, JSON_PRETTY_PRINT));
        
        // Log sync
        $this->log_sync($json_file, 'acf_to_json');
    }
    
    /**
     * Update owner fields from JSON
     */
    private function update_owner_fields($post_id, $data) {
        if (!function_exists('update_field')) return;
        
        // Get name from personal_info structure
        $personal_info = $data['personal_info'] ?? [];
        $first_name = $personal_info['first_name'] ?? '';
        $last_name = $personal_info['last_name'] ?? '';
        $full_name = $personal_info['full_name'] ?? trim($first_name . ' ' . $last_name);
        
        update_field('owner_first_name', $first_name, $post_id);
        update_field('owner_last_name', $last_name, $post_id);
        
        // Update post title with full name
        if ($full_name && $full_name !== get_the_title($post_id)) {
            wp_update_post([
                'ID' => $post_id,
                'post_title' => $full_name
            ]);
        }
        
        // Contact info
        $contact = $data['contact_info'] ?? [];
        update_field('owner_email', $contact['email'] ?? '', $post_id);
        update_field('owner_phone', $contact['phone'] ?? '', $post_id);
        update_field('owner_address', $contact['address'] ?? '', $post_id);
        
        // Entity info
        $entity = $data['entity_info'] ?? [];
        update_field('owner_entity_type', $entity['entity_type'] ?? 'individual', $post_id);
        
        // Active/Inactive status (from property status)
        $properties = $data['properties'] ?? $data['properties_owned'] ?? [];
        $has_active = false;
        foreach ($properties as $prop) {
            if ($prop['status'] === 'active') {
                $has_active = true;
                break;
            }
        }
        update_field('owner_status', $has_active ? 'active' : 'inactive', $post_id);
        
        // Registration info
        if (!empty($data['community_info']['member_since'])) {
            update_field('owner_registration_date', $data['community_info']['member_since'], $post_id);
        }
    }
    
    /**
     * Update property fields from JSON
     */
    private function update_property_fields($post_id, $data) {
        if (!function_exists('update_field')) return;
        
        $unit = $data['unit_details'] ?? [];
        
        update_field('property_unit', $unit['unit_number'] ?? '', $post_id);
        update_field('property_address', $unit['address'] ?? '', $post_id);
        update_field('property_bedrooms', $unit['bedrooms'] ?? 0, $post_id);
        update_field('property_bathrooms', $unit['bathrooms'] ?? 0, $post_id);
        update_field('property_area', $unit['square_footage'] ?? 0, $post_id);
        
        // Determine status
        $status = 'occupied';
        if (!empty($data['listing_status']['for_rent'])) {
            $status = 'rented';
        }
        update_field('property_status', $status, $post_id);
        
        // Link to owner
        $current_owners = $data['ownership']['current_owners'] ?? [];
        if (!empty($current_owners)) {
            $ownership = $current_owners[0]; // Get first owner
            if (!empty($ownership['owner_id'])) {
                $owner_id_raw = $ownership['owner_id'];
                
                // The property files use format without "owner-" prefix
                // But the owner files/directories use "owner-" prefix
                // So we need to check both formats
                
                // First try with "owner-" prefix (matches directory structure)
                $owner_id_with_prefix = 'owner-' . $owner_id_raw;
                
                $owner_posts = get_posts([
                    'post_type' => 'villa_owner',
                    'meta_query' => [
                        'relation' => 'OR',
                        [
                            'key' => '_json_key',
                            'value' => $owner_id_with_prefix,
                            'compare' => '='
                        ],
                        [
                            'key' => '_json_key',
                            'value' => $owner_id_raw,
                            'compare' => '='
                        ]
                    ],
                    'posts_per_page' => 1
                ]);
                
                if (!empty($owner_posts)) {
                    update_field('property_owner', $owner_posts[0]->ID, $post_id);
                }
            }
        }
    }
    
    /**
     * Update committee fields from JSON
     */
    private function update_committee_fields($post_id, $data) {
        if (!function_exists('update_field')) return;
        
        update_field('committee_description', $data['purpose'] ?? '', $post_id);
        update_field('committee_meeting_schedule', $data['meeting_schedule'] ?? '', $post_id);
        update_field('committee_quorum', $data['quorum'] ?? 3, $post_id);
    }
    
    /**
     * Update owner JSON from ACF
     */
    private function update_owner_json($post_id, $json_data) {
        if (!function_exists('get_field')) return $json_data;
        
        // Update JSON with ACF values
        $first_name = get_field('owner_first_name', $post_id);
        $last_name = get_field('owner_last_name', $post_id);
        
        $json_data['name'] = trim($first_name . ' ' . $last_name);
        $json_data['email'] = get_field('owner_email', $post_id) ?: $json_data['email'] ?? '';
        $json_data['phone'] = get_field('owner_phone', $post_id) ?: $json_data['phone'] ?? '';
        $json_data['status'] = get_field('owner_status', $post_id) ?: 'active';
        
        return $json_data;
    }
    
    /**
     * Update property JSON from ACF
     */
    private function update_property_json($post_id, $json_data) {
        if (!function_exists('get_field')) return $json_data;
        
        // Update unit details
        $json_data['unit_details']['unit_number'] = get_field('property_unit', $post_id);
        $json_data['unit_details']['bedrooms'] = get_field('property_bedrooms', $post_id);
        $json_data['unit_details']['bathrooms'] = get_field('property_bathrooms', $post_id);
        $json_data['unit_details']['square_footage'] = get_field('property_area', $post_id);
        
        // Update listing status based on property status
        $status = get_field('property_status', $post_id);
        $json_data['listing_status']['for_rent'] = ($status === 'rented');
        
        return $json_data;
    }
    
    /**
     * Update committee JSON from ACF
     */
    private function update_committee_json($post_id, $json_data) {
        if (!function_exists('get_field')) return $json_data;
        
        $json_data['purpose'] = get_field('committee_description', $post_id) ?: $json_data['purpose'] ?? '';
        $json_data['meeting_schedule'] = get_field('committee_meeting_schedule', $post_id) ?: $json_data['meeting_schedule'] ?? '';
        $json_data['quorum'] = get_field('committee_quorum', $post_id) ?: 3;
        
        return $json_data;
    }
    
    /**
     * Log sync operation
     */
    private function log_sync($file, $direction) {
        $log = [
            'file' => basename(dirname($file)) . '/' . basename($file),
            'direction' => $direction,
            'timestamp' => current_time('mysql')
        ];
        
        $logs = get_option('studio_sync_logs', []);
        array_unshift($logs, $log);
        $logs = array_slice($logs, 0, 100); // Keep last 100
        
        update_option('studio_sync_logs', $logs);
    }
    
    /**
     * Get sync status
     */
    public function get_sync_status() {
        return [
            'last_sync' => get_option('studio_last_json_sync', 0),
            'logs' => get_option('studio_sync_logs', []),
            'status' => $this->sync_status
        ];
    }
}

// Initialize
add_action('init', function() {
    new JSONSync();
});