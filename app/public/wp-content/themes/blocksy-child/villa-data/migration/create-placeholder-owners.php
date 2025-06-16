<?php
/**
 * Create placeholder owners for properties with missing owner references
 * This will close the loop on data integrity
 */

require_once dirname(__DIR__, 2) . '/villa-data-manager.php';

$data_manager = new VillaDataManager();
$properties = $data_manager->getAllProperties();

$missing_refs = [];
$placeholder_owners_created = [];

// Find all missing owner references
foreach ($properties as $property_id => $property) {
    if (!empty($property['owners'])) {
        foreach ($property['owners'] as $owner_id) {
            if (!$data_manager->getOwner($owner_id)) {
                $unit_number = str_replace('unit-', '', $property_id);
                $missing_refs[] = [
                    'unit' => $unit_number,
                    'owner_id' => $owner_id,
                    'property_id' => $property_id
                ];
            }
        }
    }
}

echo "Found " . count($missing_refs) . " missing owner references\n\n";

// Create placeholder owners
foreach ($missing_refs as $ref) {
    $owner_id = $ref['owner_id'];
    
    // Skip if we already created this placeholder
    if (in_array($owner_id, $placeholder_owners_created)) {
        echo "Placeholder for {$owner_id} already created\n";
        continue;
    }
    
    // Create placeholder owner data
    $placeholder_data = [
        'id' => $owner_id,
        'name' => 'PLACEHOLDER - ' . str_replace(['owner-', '-'], [' ', ' '], $owner_id),
        'email' => '',
        'phone' => '',
        'address' => '',
        'city' => '',
        'state' => '',
        'zip' => '',
        'properties' => [],
        'entity_type' => 'Unknown',
        'is_placeholder' => true,
        'placeholder_reason' => 'Created to resolve missing owner reference from unit ' . $ref['unit'],
        'created_date' => date('Y-m-d H:i:s'),
        'metadata' => [
            'last_modified' => date('Y-m-d H:i:s'),
            'modified_by' => 'placeholder-creation-script'
        ]
    ];
    
    // Find all properties that reference this owner
    foreach ($properties as $prop_id => $prop) {
        if (!empty($prop['owners']) && in_array($owner_id, $prop['owners'])) {
            $placeholder_data['properties'][] = $prop_id;
        }
    }
    
    // Create the owner directory
    $owner_dir = dirname(__DIR__) . '/owners/' . $owner_id;
    if (!file_exists($owner_dir)) {
        mkdir($owner_dir, 0755, true);
    }
    
    // Save the placeholder owner
    $owner_file = $owner_dir . '/owner.json';
    file_put_contents($owner_file, json_encode($placeholder_data, JSON_PRETTY_PRINT));
    
    $placeholder_owners_created[] = $owner_id;
    echo "Created placeholder owner: {$owner_id} for unit {$ref['unit']}\n";
}

echo "\n✅ Created " . count($placeholder_owners_created) . " placeholder owners\n";

// Verify all references are now resolved
echo "\nVerifying data integrity...\n";
$unresolved = 0;
foreach ($properties as $property_id => $property) {
    if (!empty($property['owners'])) {
        foreach ($property['owners'] as $owner_id) {
            if (!$data_manager->getOwner($owner_id)) {
                echo "❌ Still missing: {$owner_id} in {$property_id}\n";
                $unresolved++;
            }
        }
    }
}

if ($unresolved === 0) {
    echo "✅ All owner references are now resolved!\n";
} else {
    echo "⚠️  {$unresolved} references still unresolved\n";
}
