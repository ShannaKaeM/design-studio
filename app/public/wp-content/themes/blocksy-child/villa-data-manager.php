<?php
/**
 * Villa Data Manager
 * Manages consolidated Villa data using the new JSON structure
 * One JSON file per owner, property, and committee
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class VillaDataManager {
    
    private $data_path;
    private $cache = [];
    
    public function __construct() {
        $this->data_path = get_stylesheet_directory() . '/villa-data';
    }
    
    /**
     * Get all owners
     */
    public function get_all_owners() {
        $owners = [];
        $owners_dir = $this->data_path . '/owners';
        
        if (!is_dir($owners_dir)) {
            return $owners;
        }
        
        // Scan for owner directories
        $dirs = glob($owners_dir . '/owner-*/owner.json');
        
        foreach ($dirs as $file) {
            if (file_exists($file)) {
                $owner_data = json_decode(file_get_contents($file), true);
                if ($owner_data) {
                    $owners[] = $owner_data;
                }
            }
        }
        
        return $owners;
    }
    
    /**
     * Get owner by ID
     */
    public function get_owner($owner_id) {
        // Check cache first
        if (isset($this->cache['owners'][$owner_id])) {
            return $this->cache['owners'][$owner_id];
        }
        
        $file = $this->data_path . '/owners/' . $owner_id . '/owner.json';
        
        if (!file_exists($file)) {
            return null;
        }
        
        $owner = json_decode(file_get_contents($file), true);
        
        // Cache the result
        if ($owner) {
            $this->cache['owners'][$owner_id] = $owner;
        }
        
        return $owner;
    }
    
    /**
     * Get property by unit number
     */
    public function get_property($unit_number) {
        // Normalize unit number
        $unit_dir = 'unit-' . strtolower($unit_number);
        
        // Check cache first
        if (isset($this->cache['properties'][$unit_dir])) {
            return $this->cache['properties'][$unit_dir];
        }
        
        $file = $this->data_path . '/properties/' . $unit_dir . '/property.json';
        
        if (!file_exists($file)) {
            return null;
        }
        
        $property = json_decode(file_get_contents($file), true);
        
        // Cache the result
        if ($property) {
            $this->cache['properties'][$unit_dir] = $property;
        }
        
        return $property;
    }
    
    /**
     * Get all properties
     */
    public function get_all_properties() {
        $properties = [];
        $properties_dir = $this->data_path . '/properties';
        
        if (!is_dir($properties_dir)) {
            return $properties;
        }
        
        // Scan for property directories
        $dirs = glob($properties_dir . '/unit-*/property.json');
        
        foreach ($dirs as $file) {
            if (file_exists($file)) {
                $property_data = json_decode(file_get_contents($file), true);
                if ($property_data) {
                    $properties[] = $property_data;
                }
            }
        }
        
        return $properties;
    }
    
    /**
     * Update owner data
     */
    public function update_owner($owner_id, $data) {
        $file = $this->data_path . '/owners/' . $owner_id . '/owner.json';
        
        // Ensure directory exists
        $dir = dirname($file);
        if (!is_dir($dir)) {
            wp_mkdir_p($dir);
        }
        
        // Update metadata
        $data['metadata']['last_modified'] = date('Y-m-d\TH:i:s\Z');
        $data['metadata']['modified_by'] = wp_get_current_user()->user_login ?: 'system';
        
        // Save file
        $result = file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
        
        // Clear cache
        unset($this->cache['owners'][$owner_id]);
        
        return $result !== false;
    }
    
    /**
     * Find owner by name and email
     */
    public function find_owner_by_credentials($first_name, $last_name, $email = null) {
        $owners = $this->get_all_owners();
        
        foreach ($owners as $owner) {
            // Check name match
            $name_match = (
                strcasecmp($owner['personal_info']['first_name'], $first_name) === 0 &&
                strcasecmp($owner['personal_info']['last_name'], $last_name) === 0
            );
            
            if ($name_match) {
                // If email provided, verify it matches
                if ($email && strcasecmp($owner['personal_info']['email'], $email) !== 0) {
                    continue;
                }
                return $owner;
            }
        }
        
        return null;
    }
    
    /**
     * Find owners by property
     */
    public function find_owners_by_property($unit_number) {
        $property = $this->get_property($unit_number);
        
        if (!$property || empty($property['ownership']['current_owners'])) {
            return [];
        }
        
        $owners = [];
        foreach ($property['ownership']['current_owners'] as $owner_ref) {
            $owner = $this->get_owner($owner_ref['owner_id']);
            if ($owner) {
                // Add ownership details to owner data
                $owner['ownership_details'] = $owner_ref;
                $owners[] = $owner;
            }
        }
        
        return $owners;
    }
    
    /**
     * Get owner statistics
     */
    public function get_statistics() {
        $owners = $this->get_all_owners();
        $properties = $this->get_all_properties();
        
        $stats = [
            'total_owners' => count($owners),
            'active_owners' => 0,
            'registered_owners' => 0,
            'owners_with_email' => 0,
            'multi_property_owners' => 0,
            'total_properties' => count($properties),
            'properties_with_owners' => 0,
            'entity_types' => [
                'individual' => 0,
                'llc' => 0,
                'trust' => 0,
                'corporation' => 0
            ]
        ];
        
        // Analyze owners
        foreach ($owners as $owner) {
            if ($owner['status'] === 'active') {
                $stats['active_owners']++;
            }
            
            if (!empty($owner['wordpress_integration']['wp_user_id'])) {
                $stats['registered_owners']++;
            }
            
            if (!empty($owner['personal_info']['email'])) {
                $stats['owners_with_email']++;
            }
            
            if (count($owner['properties']) > 1) {
                $stats['multi_property_owners']++;
            }
            
            // Count entity types
            $entity_type = $owner['entity_info']['entity_type'] ?? 'individual';
            if (isset($stats['entity_types'][$entity_type])) {
                $stats['entity_types'][$entity_type]++;
            }
        }
        
        // Analyze properties
        foreach ($properties as $property) {
            if (!empty($property['ownership']['current_owners'])) {
                $stats['properties_with_owners']++;
            }
        }
        
        return $stats;
    }
    
    /**
     * Search owners
     */
    public function search_owners($query) {
        $owners = $this->get_all_owners();
        $results = [];
        $query = strtolower($query);
        
        foreach ($owners as $owner) {
            // Search in name
            if (stripos($owner['personal_info']['full_name'], $query) !== false) {
                $results[] = $owner;
                continue;
            }
            
            // Search in email
            if (stripos($owner['personal_info']['email'], $query) !== false) {
                $results[] = $owner;
                continue;
            }
            
            // Search in properties
            foreach ($owner['properties'] as $property) {
                if (stripos($property['unit_number'], $query) !== false) {
                    $results[] = $owner;
                    break;
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Register owner with WordPress
     */
    public function register_owner($owner_id, $password) {
        $owner = $this->get_owner($owner_id);
        
        if (!$owner) {
            return ['success' => false, 'message' => 'Owner not found'];
        }
        
        if (!empty($owner['wordpress_integration']['wp_user_id'])) {
            return ['success' => false, 'message' => 'Owner already registered'];
        }
        
        // Generate username
        $username = $this->generate_username(
            $owner['personal_info']['first_name'],
            $owner['personal_info']['last_name']
        );
        
        // Create WordPress user
        $user_id = wp_create_user(
            $username,
            $password,
            $owner['personal_info']['email']
        );
        
        if (is_wp_error($user_id)) {
            return ['success' => false, 'message' => $user_id->get_error_message()];
        }
        
        // Set user role
        $user = new WP_User($user_id);
        $user->set_role('villa_owner');
        
        // Update user profile
        wp_update_user([
            'ID' => $user_id,
            'first_name' => $owner['personal_info']['first_name'],
            'last_name' => $owner['personal_info']['last_name'],
            'display_name' => $owner['personal_info']['full_name']
        ]);
        
        // Update user meta
        $unit_numbers = array_column($owner['properties'], 'unit_number');
        update_user_meta($user_id, 'villa_owner_id', $owner_id);
        update_user_meta($user_id, 'villa_units', $unit_numbers);
        update_user_meta($user_id, 'villa_properties', $owner['properties']);
        
        // Update owner record
        $owner['wordpress_integration'] = [
            'wp_user_id' => $user_id,
            'wp_username' => $username,
            'registration_status' => 'active',
            'registration_date' => date('Y-m-d\TH:i:s\Z'),
            'last_login' => null
        ];
        
        $this->update_owner($owner_id, $owner);
        
        return [
            'success' => true,
            'user_id' => $user_id,
            'username' => $username
        ];
    }
    
    /**
     * Generate unique username
     */
    private function generate_username($first_name, $last_name) {
        $base = strtolower(substr($first_name, 0, 1) . $last_name);
        $base = preg_replace('/[^a-z0-9]/', '', $base);
        
        $username = $base;
        $counter = 1;
        
        while (username_exists($username)) {
            $username = $base . $counter;
            $counter++;
        }
        
        return $username;
    }
    
    /**
     * Get email campaign data
     */
    public function get_email_campaign_data() {
        $owners = $this->get_all_owners();
        
        $data = [
            'total_owners' => count($owners),
            'total_unique_emails' => 0,
            'owners_with_email' => [],
            'owners_without_email' => []
        ];
        
        $unique_emails = [];
        
        foreach ($owners as $owner) {
            if (!empty($owner['personal_info']['email'])) {
                $email = strtolower($owner['personal_info']['email']);
                $unique_emails[$email] = true;
                
                $data['owners_with_email'][] = [
                    'owner_id' => $owner['owner_id'],
                    'name' => $owner['personal_info']['full_name'],
                    'email' => $owner['personal_info']['email'],
                    'units' => array_column($owner['properties'], 'unit_number')
                ];
            } else {
                $data['owners_without_email'][] = [
                    'owner_id' => $owner['owner_id'],
                    'name' => $owner['personal_info']['full_name'],
                    'units' => array_column($owner['properties'], 'unit_number')
                ];
            }
        }
        
        $data['total_unique_emails'] = count($unique_emails);
        
        return $data;
    }
}

// Initialize global instance
global $villa_data_manager;
$villa_data_manager = new VillaDataManager();
