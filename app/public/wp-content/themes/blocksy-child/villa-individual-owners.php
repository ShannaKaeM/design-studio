<?php
/**
 * Villa Individual Owner Management System
 * Handles individual owner accounts where each owner can have their own account
 * and multiple owners can be linked to the same property
 */

class Villa_Individual_Owners {
    
    private $owners_file;
    private $properties_file;
    
    public function __construct() {
        $this->owners_file = get_stylesheet_directory() . '/villa-data/individual-owners.json';
        $this->properties_file = get_stylesheet_directory() . '/villa-data/migration/final-ownership-analysis.json';
        
        // Initialize individual owners data if not exists
        if (!file_exists($this->owners_file)) {
            $this->initialize_individual_owners();
        }
    }
    
    /**
     * Initialize individual owners from property data
     */
    private function initialize_individual_owners() {
        $properties = $this->get_properties_data();
        if (!$properties) return;
        
        $individual_owners = [];
        $owner_id = 1;
        
        foreach ($properties['property_list'] as $property) {
            // Skip properties with no data
            if ($property['primary_owner'] === 'NO_DATA') continue;
            
            // Process primary owner
            if (!empty($property['primary_owner'])) {
                $owner_key = $this->generate_owner_key($property['primary_owner'], $property['primary_email']);
                
                if (!isset($individual_owners[$owner_key])) {
                    $individual_owners[$owner_key] = [
                        'owner_id' => $owner_id++,
                        'full_name' => $property['primary_owner'],
                        'email' => $property['primary_email'],
                        'phone' => $property['primary_phone'],
                        'properties' => [],
                        'is_primary' => true,
                        'wp_user_id' => null,
                        'registration_status' => 'pending'
                    ];
                }
                
                // Add property to owner's list
                $individual_owners[$owner_key]['properties'][] = [
                    'unit' => $property['unit'],
                    'role' => 'primary',
                    'entity_type' => $property['entity_type'],
                    'company' => $property['company']
                ];
            }
            
            // Process secondary owner
            if (!empty($property['secondary_owner'])) {
                $owner_key = $this->generate_owner_key($property['secondary_owner'], $property['secondary_email']);
                
                if (!isset($individual_owners[$owner_key])) {
                    $individual_owners[$owner_key] = [
                        'owner_id' => $owner_id++,
                        'full_name' => $property['secondary_owner'],
                        'email' => $property['secondary_email'],
                        'phone' => $property['secondary_phone'],
                        'properties' => [],
                        'is_primary' => false,
                        'wp_user_id' => null,
                        'registration_status' => 'pending'
                    ];
                }
                
                // Add property to owner's list
                $individual_owners[$owner_key]['properties'][] = [
                    'unit' => $property['unit'],
                    'role' => 'secondary',
                    'entity_type' => $property['entity_type'],
                    'company' => $property['company']
                ];
            }
        }
        
        // Save individual owners data
        $this->save_individual_owners($individual_owners);
    }
    
    /**
     * Generate unique key for owner
     */
    private function generate_owner_key($name, $email = '') {
        $key = strtolower(trim($name));
        if (!empty($email)) {
            $key .= '_' . strtolower(trim($email));
        }
        return md5($key);
    }
    
    /**
     * Get properties data
     */
    private function get_properties_data() {
        if (!file_exists($this->properties_file)) {
            return null;
        }
        return json_decode(file_get_contents($this->properties_file), true);
    }
    
    /**
     * Get individual owners data
     */
    public function get_individual_owners() {
        if (!file_exists($this->owners_file)) {
            $this->initialize_individual_owners();
        }
        return json_decode(file_get_contents($this->owners_file), true);
    }
    
    /**
     * Save individual owners data
     */
    private function save_individual_owners($data) {
        file_put_contents($this->owners_file, json_encode($data, JSON_PRETTY_PRINT));
    }
    
    /**
     * Find owner by name and unit
     */
    public function find_owner($first_name, $last_name, $unit_number) {
        $owners = $this->get_individual_owners();
        $search_name = trim($first_name . ' ' . $last_name);
        
        foreach ($owners as $owner_key => $owner) {
            // Check if name matches
            if (strcasecmp($owner['full_name'], $search_name) === 0) {
                // Check if owner has access to this unit
                foreach ($owner['properties'] as $property) {
                    if ($property['unit'] === $unit_number) {
                        return [
                            'success' => true,
                            'owner_key' => $owner_key,
                            'owner' => $owner,
                            'property' => $property
                        ];
                    }
                }
            }
        }
        
        return ['success' => false, 'message' => 'Owner not found for this unit.'];
    }
    
    /**
     * Register individual owner
     */
    public function register_owner($owner_key, $email, $password, $billing_address) {
        $owners = $this->get_individual_owners();
        
        if (!isset($owners[$owner_key])) {
            return ['success' => false, 'message' => 'Owner not found.'];
        }
        
        $owner = $owners[$owner_key];
        
        // Check if already registered
        if ($owner['wp_user_id']) {
            return ['success' => false, 'message' => 'This owner already has an account.'];
        }
        
        // Parse name
        $name_parts = explode(' ', $owner['full_name']);
        $first_name = $name_parts[0];
        $last_name = count($name_parts) > 1 ? implode(' ', array_slice($name_parts, 1)) : '';
        
        // Generate username
        $username = $this->generate_username($first_name, $last_name);
        
        // Create WordPress user
        $user_id = wp_create_user($username, $password, $email);
        
        if (is_wp_error($user_id)) {
            return ['success' => false, 'message' => $user_id->get_error_message()];
        }
        
        // Set user role
        $user = new WP_User($user_id);
        $user->set_role('villa_owner');
        
        // Update user profile
        wp_update_user([
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => $owner['full_name']
        ]);
        
        // Update user meta with all properties
        $unit_numbers = array_column($owner['properties'], 'unit');
        update_user_meta($user_id, 'villa_units', $unit_numbers);
        update_user_meta($user_id, 'villa_properties', $owner['properties']);
        update_user_meta($user_id, 'villa_billing_address', $billing_address);
        update_user_meta($user_id, 'villa_owner_key', $owner_key);
        
        // Update owner record
        $owners[$owner_key]['wp_user_id'] = $user_id;
        $owners[$owner_key]['wp_username'] = $username;
        $owners[$owner_key]['email'] = $email;
        $owners[$owner_key]['registration_date'] = current_time('c');
        $owners[$owner_key]['registration_status'] = 'active';
        $owners[$owner_key]['billing_address'] = $billing_address;
        
        $this->save_individual_owners($owners);
        
        // Send welcome email
        $this->send_welcome_email($user_id, $owner);
        
        return ['success' => true, 'user_id' => $user_id, 'username' => $username];
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
     * Send welcome email
     */
    private function send_welcome_email($user_id, $owner_data) {
        $user = get_user_by('id', $user_id);
        if (!$user) return;
        
        // Get email template function
        if (function_exists('villa_get_welcome_email_template')) {
            $units = implode(', ', array_column($owner_data['properties'], 'unit'));
            $email_content = villa_get_welcome_email_template($user->user_login, $units);
            
            $subject = 'Welcome to Villa Capriani Owner Portal';
            $headers = ['Content-Type: text/html; charset=UTF-8'];
            
            wp_mail($user->user_email, $subject, $email_content, $headers);
        }
    }
    
    /**
     * Get statistics
     */
    public function get_statistics() {
        $owners = $this->get_individual_owners();
        
        $stats = [
            'total_individual_owners' => count($owners),
            'registered_owners' => 0,
            'pending_owners' => 0,
            'multi_property_owners' => 0,
            'primary_owners' => 0,
            'secondary_owners' => 0
        ];
        
        foreach ($owners as $owner) {
            if ($owner['wp_user_id']) {
                $stats['registered_owners']++;
            } else {
                $stats['pending_owners']++;
            }
            
            if (count($owner['properties']) > 1) {
                $stats['multi_property_owners']++;
            }
            
            if ($owner['is_primary']) {
                $stats['primary_owners']++;
            } else {
                $stats['secondary_owners']++;
            }
        }
        
        return $stats;
    }
}

// Initialize the class
global $villa_individual_owners;
$villa_individual_owners = new Villa_Individual_Owners();
