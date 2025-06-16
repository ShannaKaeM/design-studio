<?php
/**
 * Villa Data Consolidation Script
 * Consolidates owner data from individual-owners.json into individual owner files
 */

// Set up paths
$villa_data_path = dirname(__DIR__);
$individual_owners_file = $villa_data_path . '/individual-owners.json';
$owners_dir = $villa_data_path . '/owners';
$properties_dir = $villa_data_path . '/properties';

// Load individual owners data
if (!file_exists($individual_owners_file)) {
    die("Error: individual-owners.json not found\n");
}

$individual_owners = json_decode(file_get_contents($individual_owners_file), true);
if (!$individual_owners) {
    die("Error: Could not parse individual-owners.json\n");
}

echo "Found " . count($individual_owners) . " entries in individual-owners.json\n\n";

// Create owner ID mapping
$owner_mapping = [];
$consolidated_owners = [];

// First pass: Group by actual person (consolidate duplicates)
foreach ($individual_owners as $hash_id => $owner_data) {
    $full_name = $owner_data['full_name'];
    $normalized_name = strtolower(str_replace(' ', '-', $full_name));
    
    if (!isset($consolidated_owners[$normalized_name])) {
        $consolidated_owners[$normalized_name] = [
            'hash_ids' => [],
            'owner_data' => $owner_data,
            'all_properties' => []
        ];
    }
    
    // Add hash ID for mapping
    $consolidated_owners[$normalized_name]['hash_ids'][] = $hash_id;
    
    // Merge properties
    if (isset($owner_data['properties'])) {
        foreach ($owner_data['properties'] as $property) {
            $consolidated_owners[$normalized_name]['all_properties'][] = $property;
        }
    }
    
    // Keep email/phone if not empty
    if (!empty($owner_data['email']) && empty($consolidated_owners[$normalized_name]['owner_data']['email'])) {
        $consolidated_owners[$normalized_name]['owner_data']['email'] = $owner_data['email'];
    }
    if (!empty($owner_data['phone']) && empty($consolidated_owners[$normalized_name]['owner_data']['phone'])) {
        $consolidated_owners[$normalized_name]['owner_data']['phone'] = $owner_data['phone'];
    }
}

echo "Consolidated to " . count($consolidated_owners) . " unique owners\n\n";

// Second pass: Create/update owner files
$created = 0;
$updated = 0;

foreach ($consolidated_owners as $normalized_name => $owner_info) {
    $owner_id = 'owner-' . $normalized_name;
    $owner_dir = $owners_dir . '/' . $owner_id;
    $owner_file = $owner_dir . '/owner.json';
    
    // Create directory if needed
    if (!is_dir($owner_dir)) {
        mkdir($owner_dir, 0755, true);
    }
    
    // Check if profile.json exists
    $profile_file = $owner_dir . '/profile.json';
    $existing_data = null;
    
    if (file_exists($profile_file)) {
        $existing_data = json_decode(file_get_contents($profile_file), true);
    }
    
    // Build consolidated owner data
    $owner_data = $owner_info['owner_data'];
    
    $consolidated_owner = [
        'owner_id' => $owner_id,
        'hash_ids' => $owner_info['hash_ids'], // Keep for backward compatibility
        'status' => 'active',
        
        'personal_info' => [
            'first_name' => $existing_data['personal_info']['first_name'] ?? explode(' ', $owner_data['full_name'])[0],
            'last_name' => $existing_data['personal_info']['last_name'] ?? trim(substr($owner_data['full_name'], strlen(explode(' ', $owner_data['full_name'])[0]))),
            'full_name' => $owner_data['full_name'],
            'email' => $existing_data['personal_info']['email'] ?? $owner_data['email'] ?? '',
            'phone' => $existing_data['personal_info']['phone'] ?? $owner_data['phone'] ?? '',
            'emergency_contact' => $existing_data['personal_info']['emergency_contact'] ?? [
                'name' => null,
                'relationship' => null,
                'phone' => null
            ]
        ],
        
        'entity_info' => $existing_data['entity_info'] ?? [
            'entity_type' => 'individual',
            'company_name' => null,
            'legal_entity_name' => $owner_data['full_name'],
            'tax_id' => null
        ],
        
        'addresses' => $existing_data['addresses'] ?? [
            'primary' => [
                'street' => null,
                'city' => null,
                'state' => null,
                'zip' => null,
                'country' => 'USA'
            ],
            'billing' => [
                'same_as_primary' => true,
                'street' => null,
                'city' => null,
                'state' => null,
                'zip' => null,
                'country' => null
            ]
        ],
        
        'properties' => [],
        
        'committee_roles' => $existing_data['committee_roles'] ?? [],
        
        'wordpress_integration' => [
            'wp_user_id' => $owner_data['wp_user_id'] ?? null,
            'registration_status' => $owner_data['registration_status'] ?? 'pending',
            'registration_date' => null,
            'last_login' => null
        ],
        
        'preferences' => $existing_data['preferences'] ?? [
            'communication' => [
                'email_opt_in' => true,
                'sms_opt_in' => false,
                'mail_opt_in' => true
            ],
            'privacy' => [
                'show_in_directory' => true,
                'show_email' => false,
                'show_phone' => false
            ]
        ],
        
        'crm_data' => [
            'notes' => '',
            'tags' => [],
            'last_contact' => null,
            'relationship_score' => null
        ],
        
        'metadata' => [
            'created_date' => date('Y-m-d H:i:s'),
            'last_modified' => date('Y-m-d H:i:s'),
            'modified_by' => 'migration_script',
            'data_source' => 'migration',
            'version' => 1
        ]
    ];
    
    // Process properties - deduplicate and consolidate
    $property_map = [];
    foreach ($owner_info['all_properties'] as $property) {
        $unit = $property['unit'];
        if (!isset($property_map[$unit])) {
            $property_map[$unit] = [
                'unit_number' => $unit,
                'role' => $property['role'],
                'ownership_percentage' => null, // To be determined from property files
                'purchase_date' => null,
                'status' => 'active'
            ];
        }
        // If we have multiple entries for same unit, prefer primary role
        if ($property['role'] === 'primary') {
            $property_map[$unit]['role'] = 'primary';
        }
    }
    
    $consolidated_owner['properties'] = array_values($property_map);
    
    // Save the consolidated owner file
    file_put_contents($owner_file, json_encode($consolidated_owner, JSON_PRETTY_PRINT));
    
    // Remove old profile.json if it exists
    if (file_exists($profile_file)) {
        rename($profile_file, $profile_file . '.backup');
        $updated++;
    } else {
        $created++;
    }
    
    echo "Processed: $owner_id (" . count($consolidated_owner['properties']) . " properties)\n";
}

echo "\n=== Summary ===\n";
echo "Created: $created new owner files\n";
echo "Updated: $updated existing owners\n";
echo "Total unique owners: " . count($consolidated_owners) . "\n";

// Create backup of individual-owners.json
$backup_file = $individual_owners_file . '.backup-' . date('Y-m-d-His');
copy($individual_owners_file, $backup_file);
echo "\nBacked up individual-owners.json to: " . basename($backup_file) . "\n";

echo "\nMigration complete! Next steps:\n";
echo "1. Review the consolidated owner files\n";
echo "2. Update property files to use new owner IDs\n";
echo "3. Update PHP code to use new structure\n";
echo "4. Delete individual-owners.json when ready\n";
