<?php
/**
 * Update Property Files with New Owner IDs
 * Updates property.json files to use new owner-{name} format
 */

// Set up paths
$villa_data_path = dirname(__DIR__);
$properties_dir = $villa_data_path . '/properties';
$owners_dir = $villa_data_path . '/owners';

// Build owner mapping from old IDs to new IDs
$owner_mapping = [];

// Scan all owner directories
$owner_dirs = glob($owners_dir . '/owner-*', GLOB_ONLYDIR);
foreach ($owner_dirs as $owner_dir) {
    $owner_file = $owner_dir . '/owner.json';
    if (file_exists($owner_file)) {
        $owner_data = json_decode(file_get_contents($owner_file), true);
        if ($owner_data && isset($owner_data['owner_id'])) {
            // Map from various formats
            $owner_id = $owner_data['owner_id'];
            $full_name = $owner_data['personal_info']['full_name'];
            
            // Create mappings for different ID formats
            $owner_mapping[$full_name] = $owner_id;
            $owner_mapping[strtolower(str_replace(' ', '-', $full_name))] = $owner_id;
            
            // Also map hash IDs if present
            if (isset($owner_data['hash_ids'])) {
                foreach ($owner_data['hash_ids'] as $hash_id) {
                    $owner_mapping[$hash_id] = $owner_id;
                }
            }
        }
    }
}

echo "Built owner mapping with " . count($owner_mapping) . " entries\n\n";

// Update property files
$property_dirs = glob($properties_dir . '/unit-*', GLOB_ONLYDIR);
$updated = 0;
$missing_owners = [];

foreach ($property_dirs as $property_dir) {
    $property_file = $property_dir . '/property.json';
    if (!file_exists($property_file)) {
        continue;
    }
    
    $property_data = json_decode(file_get_contents($property_file), true);
    if (!$property_data) {
        echo "Error: Could not parse " . basename($property_dir) . "/property.json\n";
        continue;
    }
    
    $modified = false;
    
    // Update current owners
    if (isset($property_data['ownership']['current_owners'])) {
        foreach ($property_data['ownership']['current_owners'] as &$owner) {
            $current_id = $owner['owner_id'];
            
            // Try to find new owner ID
            $new_id = null;
            
            // Check if it's already in new format
            if (strpos($current_id, 'owner-') === 0) {
                // Verify it exists
                if (is_dir($owners_dir . '/' . $current_id)) {
                    continue; // Already correct
                }
            }
            
            // Try mapping
            if (isset($owner_mapping[$current_id])) {
                $new_id = $owner_mapping[$current_id];
            } else {
                // Try to normalize and find
                $normalized = strtolower(str_replace(' ', '-', $current_id));
                if (isset($owner_mapping[$normalized])) {
                    $new_id = $owner_mapping[$normalized];
                } else {
                    // Record missing owner
                    $missing_owners[$current_id] = basename($property_dir);
                    continue;
                }
            }
            
            if ($new_id && $new_id !== $current_id) {
                $owner['owner_id'] = $new_id;
                $modified = true;
                echo "Updated " . basename($property_dir) . ": $current_id -> $new_id\n";
            }
        }
    }
    
    // Update ownership history if present
    if (isset($property_data['ownership']['ownership_history'])) {
        foreach ($property_data['ownership']['ownership_history'] as &$history) {
            if (isset($history['owner_id'])) {
                $current_id = $history['owner_id'];
                if (isset($owner_mapping[$current_id])) {
                    $history['owner_id'] = $owner_mapping[$current_id];
                    $modified = true;
                }
            }
        }
    }
    
    // Save if modified
    if ($modified) {
        // Backup original
        copy($property_file, $property_file . '.backup');
        
        // Save updated
        file_put_contents($property_file, json_encode($property_data, JSON_PRETTY_PRINT));
        $updated++;
    }
}

echo "\n=== Summary ===\n";
echo "Updated: $updated property files\n";
echo "Total properties scanned: " . count($property_dirs) . "\n";

if (!empty($missing_owners)) {
    echo "\n=== Missing Owners ===\n";
    echo "The following owner IDs could not be mapped:\n";
    foreach ($missing_owners as $owner_id => $property) {
        echo "  $owner_id (in $property)\n";
    }
    echo "\nThese may need manual review.\n";
}

// Now update owner files with ownership percentages from properties
echo "\n=== Updating Owner Files with Ownership Percentages ===\n";

foreach ($owner_dirs as $owner_dir) {
    $owner_file = $owner_dir . '/owner.json';
    if (!file_exists($owner_file)) {
        continue;
    }
    
    $owner_data = json_decode(file_get_contents($owner_file), true);
    if (!$owner_data) {
        continue;
    }
    
    $owner_id = $owner_data['owner_id'];
    $updated_properties = false;
    
    // Check each property this owner has
    foreach ($owner_data['properties'] as &$owner_property) {
        $unit = $owner_property['unit_number'];
        $property_file = $properties_dir . '/unit-' . strtolower($unit) . '/property.json';
        
        if (file_exists($property_file)) {
            $property_data = json_decode(file_get_contents($property_file), true);
            
            // Find this owner in the property's owner list
            if (isset($property_data['ownership']['current_owners'])) {
                foreach ($property_data['ownership']['current_owners'] as $prop_owner) {
                    if ($prop_owner['owner_id'] === $owner_id) {
                        // Update ownership percentage
                        if (isset($prop_owner['ownership_percentage'])) {
                            $owner_property['ownership_percentage'] = $prop_owner['ownership_percentage'];
                            $updated_properties = true;
                        }
                        break;
                    }
                }
            }
        }
    }
    
    if ($updated_properties) {
        file_put_contents($owner_file, json_encode($owner_data, JSON_PRETTY_PRINT));
        echo "Updated ownership percentages for: " . basename($owner_dir) . "\n";
    }
}

echo "\nProperty owner update complete!\n";
