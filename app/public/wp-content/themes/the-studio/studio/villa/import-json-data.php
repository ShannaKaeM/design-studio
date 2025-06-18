<?php
/**
 * Import JSON Villa Data to ACF Posts
 * 
 * One-time import script to migrate JSON data to ACF
 * 
 * @package TheStudio
 */

namespace Studio\Villa;

class ImportJSONData {
    
    private $source_dir;
    private $imported = [];
    private $errors = [];
    
    public function __construct() {
        // Point to blocksy-child villa data
        $this->source_dir = WP_CONTENT_DIR . '/themes/blocksy-child/villa-data';
    }
    
    /**
     * Run import
     */
    public function import_all() {
        // Import in order to maintain relationships
        $this->import_owners();
        $this->import_properties();
        $this->import_committees();
        
        return [
            'imported' => $this->imported,
            'errors' => $this->errors
        ];
    }
    
    /**
     * Import owners
     */
    private function import_owners() {
        $owners_dir = $this->source_dir . '/owners';
        
        if (!is_dir($owners_dir)) {
            $this->errors[] = 'Owners directory not found';
            return;
        }
        
        $owner_dirs = glob($owners_dir . '/owner-*', GLOB_ONLYDIR);
        
        foreach ($owner_dirs as $owner_dir) {
            // Try both owner.json and profile.json
            $json_file = file_exists($owner_dir . '/owner.json') 
                ? $owner_dir . '/owner.json' 
                : $owner_dir . '/profile.json';
                
            if (!file_exists($json_file)) {
                continue;
            }
            
            $data = json_decode(file_get_contents($json_file), true);
            
            if (!$data) {
                $this->errors[] = "Invalid JSON in: " . $json_file;
                continue;
            }
            
            // Create owner post
            $post_data = [
                'post_type' => 'villa_owner',
                'post_title' => $data['name'] ?? 'Unknown Owner',
                'post_status' => 'publish',
                'post_name' => sanitize_title($data['name'] ?? '')
            ];
            
            $post_id = wp_insert_post($post_data);
            
            if (!is_wp_error($post_id)) {
                // Update ACF fields
                if (function_exists('update_field')) {
                    // Parse name into first/last
                    $name_parts = explode(' ', $data['name'] ?? '', 2);
                    update_field('owner_first_name', $name_parts[0] ?? '', $post_id);
                    update_field('owner_last_name', $name_parts[1] ?? '', $post_id);
                    
                    update_field('owner_email', $data['email'] ?? '', $post_id);
                    update_field('owner_phone', $data['phone'] ?? '', $post_id);
                    update_field('owner_status', 'active', $post_id);
                    
                    if (!empty($data['registration_date'])) {
                        update_field('owner_registration_date', $data['registration_date'], $post_id);
                    }
                }
                
                // Store mapping for relationships
                $this->imported['owners'][$data['owner_id'] ?? $data['name']] = $post_id;
            } else {
                $this->errors[] = "Failed to create owner: " . $data['name'];
            }
        }
    }
    
    /**
     * Import properties
     */
    private function import_properties() {
        $properties_dir = $this->source_dir . '/properties';
        
        if (!is_dir($properties_dir)) {
            $this->errors[] = 'Properties directory not found';
            return;
        }
        
        $property_files = glob($properties_dir . '/*/property.json');
        
        foreach ($property_files as $json_file) {
            $data = json_decode(file_get_contents($json_file), true);
            
            if (!$data) {
                $this->errors[] = "Invalid JSON in: " . $json_file;
                continue;
            }
            
            // Get unit details
            $unit = $data['unit_details'] ?? [];
            
            // Create property post
            $post_data = [
                'post_type' => 'villa_property',
                'post_title' => $unit['display_name'] ?? $unit['unit_number'] ?? 'Unknown Property',
                'post_content' => $data['description'] ?? '',
                'post_status' => 'publish',
                'post_name' => sanitize_title($unit['unit_number'] ?? '')
            ];
            
            $post_id = wp_insert_post($post_data);
            
            if (!is_wp_error($post_id)) {
                // Update ACF fields
                if (function_exists('update_field')) {
                    update_field('property_unit', $unit['unit_number'] ?? '', $post_id);
                    update_field('property_address', $unit['address'] ?? '', $post_id);
                    update_field('property_bedrooms', $unit['bedrooms'] ?? 0, $post_id);
                    update_field('property_bathrooms', $unit['bathrooms'] ?? 0, $post_id);
                    update_field('property_area', $unit['square_footage'] ?? 0, $post_id);
                    
                    // Link to owner
                    if (!empty($data['ownership']['current_owners'][0]['owner_id'])) {
                        $owner_key = $data['ownership']['current_owners'][0]['owner_id'];
                        if (isset($this->imported['owners'][$owner_key])) {
                            update_field('property_owner', $this->imported['owners'][$owner_key], $post_id);
                        }
                    }
                    
                    // Set status based on listing
                    $status = 'occupied';
                    if (!empty($data['listing_status']['for_rent'])) {
                        $status = 'rented';
                    }
                    update_field('property_status', $status, $post_id);
                }
                
                $this->imported['properties'][] = $post_id;
            } else {
                $this->errors[] = "Failed to create property: " . ($unit['unit_number'] ?? 'Unknown');
            }
        }
    }
    
    /**
     * Import committees
     */
    private function import_committees() {
        $committees_dir = $this->source_dir . '/committees';
        
        if (!is_dir($committees_dir)) {
            $this->errors[] = 'Committees directory not found';
            return;
        }
        
        $committee_files = glob($committees_dir . '/*/committee.json');
        
        foreach ($committee_files as $json_file) {
            $data = json_decode(file_get_contents($json_file), true);
            
            if (!$data) {
                $this->errors[] = "Invalid JSON in: " . $json_file;
                continue;
            }
            
            // Create committee post
            $post_data = [
                'post_type' => 'villa_committee',
                'post_title' => $data['name'] ?? 'Unknown Committee',
                'post_content' => $data['description'] ?? '',
                'post_status' => 'publish',
                'post_name' => sanitize_title($data['committee_id'] ?? $data['name'] ?? '')
            ];
            
            $post_id = wp_insert_post($post_data);
            
            if (!is_wp_error($post_id)) {
                // Update ACF fields
                if (function_exists('update_field')) {
                    update_field('committee_description', $data['purpose'] ?? '', $post_id);
                    
                    // Find chair by name
                    if (!empty($data['members'])) {
                        foreach ($data['members'] as $member) {
                            if ($member['role'] === 'Chair' && !empty($member['name'])) {
                                // Find owner post by name
                                $owner_post = get_page_by_title($member['name'], OBJECT, 'villa_owner');
                                if ($owner_post) {
                                    update_field('committee_chair', $owner_post->ID, $post_id);
                                }
                            }
                        }
                    }
                    
                    update_field('committee_quorum', $data['quorum'] ?? 3, $post_id);
                }
                
                $this->imported['committees'][] = $post_id;
            } else {
                $this->errors[] = "Failed to create committee: " . ($data['name'] ?? 'Unknown');
            }
        }
    }
}

// Add admin page for import
add_action('admin_menu', function() {
    add_submenu_page(
        'studio-content',
        'Import JSON Data',
        'Import JSON Data',
        'manage_options',
        'studio-import-json',
        'studio_import_json_page'
    );
});

function studio_import_json_page() {
    $results = null;
    
    if (isset($_POST['import_json']) && wp_verify_nonce($_POST['_wpnonce'], 'import_json')) {
        $importer = new ImportJSONData();
        $results = $importer->import_all();
    }
    
    ?>
    <div class="wrap">
        <h1>Import JSON Data to ACF</h1>
        
        <?php if ($results) : ?>
            <div class="notice notice-success">
                <p>Import completed!</p>
                <ul>
                    <li>Owners imported: <?php echo count($results['imported']['owners'] ?? []); ?></li>
                    <li>Properties imported: <?php echo count($results['imported']['properties'] ?? []); ?></li>
                    <li>Committees imported: <?php echo count($results['imported']['committees'] ?? []); ?></li>
                </ul>
                <?php if (!empty($results['errors'])) : ?>
                    <p><strong>Errors:</strong></p>
                    <ul>
                        <?php foreach ($results['errors'] as $error) : ?>
                            <li><?php echo esc_html($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin-top: 20px;">
            <h2>Import Villa Data from JSON</h2>
            <p>This will import all Villa data from the blocksy-child theme JSON files into the new ACF-based system.</p>
            
            <p><strong>Source:</strong> <code>/blocksy-child/villa-data/</code></p>
            <p><strong>Destination:</strong> ACF Custom Post Types</p>
            
            <form method="post">
                <?php wp_nonce_field('import_json'); ?>
                <p class="submit">
                    <input type="submit" name="import_json" class="button button-primary" value="Import JSON Data" />
                </p>
            </form>
        </div>
    </div>
    <?php
}