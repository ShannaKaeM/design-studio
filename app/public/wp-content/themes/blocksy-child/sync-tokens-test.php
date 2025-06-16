<?php
// Simple test to sync tokens without WordPress

// Load studio.json
$studio_json_path = __DIR__ . '/studio.json';
$tokens = json_decode(file_get_contents($studio_json_path), true);

echo "Loaded tokens from studio.json:\n";
echo "Total colors: " . count($tokens['colors']) . "\n";
echo "Colors: " . implode(', ', array_keys($tokens['colors'])) . "\n\n";

// Load theme.json
$theme_json_path = __DIR__ . '/theme.json';
$theme_json = json_decode(file_get_contents($theme_json_path), true);

echo "Current theme.json palette has " . count($theme_json['settings']['color']['palette']) . " colors\n\n";

// Update the color palette
$theme_json['settings']['color']['palette'] = array();
foreach ($tokens['colors'] as $key => $color) {
    $theme_json['settings']['color']['palette'][] = array(
        'slug' => $key,
        'color' => $color['value'],
        'name' => $color['name']
    );
}

// Save updated theme.json
file_put_contents($theme_json_path, json_encode($theme_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "Sync completed! Theme.json now has " . count($theme_json['settings']['color']['palette']) . " colors.\n";
