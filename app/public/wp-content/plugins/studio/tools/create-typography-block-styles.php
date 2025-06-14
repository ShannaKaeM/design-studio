<?php
/**
 * Create Typography Block Styles for Studio Text Block
 * Run this script to populate all typography presets as block styles
 */

// Path to theme.json
$theme_json_path = dirname(dirname(dirname(dirname(__DIR__)))) . '/wp-content/themes/blocksy-child/theme.json';

if (!file_exists($theme_json_path)) {
    die("Error: theme.json not found at: $theme_json_path\n");
}

// Typography presets to create as block styles
$typography_styles = [
    // Hero Typography
    [
        'name' => 'hero-pretitle',
        'label' => 'Hero Pretitle',
        'css' => '
            font-size: 14px;
            font-weight: 600;
            line-height: 1.2;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        '
    ],
    [
        'name' => 'hero-title',
        'label' => 'Hero Title',
        'css' => '
            font-size: clamp(48px, 5vw, 72px);
            font-weight: 700;
            line-height: 1.1;
            letter-spacing: -0.02em;
        '
    ],
    [
        'name' => 'hero-subtitle',
        'label' => 'Hero Subtitle',
        'css' => '
            font-size: 24px;
            font-weight: 400;
            line-height: 1.5;
        '
    ],
    [
        'name' => 'hero-description',
        'label' => 'Hero Description',
        'css' => '
            font-size: 18px;
            font-weight: 400;
            line-height: 1.6;
        '
    ],
    
    // Section Typography
    [
        'name' => 'section-pretitle',
        'label' => 'Section Pretitle',
        'css' => '
            font-size: 12px;
            font-weight: 600;
            line-height: 1.2;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        '
    ],
    [
        'name' => 'section-title',
        'label' => 'Section Title',
        'css' => '
            font-size: clamp(36px, 4vw, 48px);
            font-weight: 600;
            line-height: 1.2;
            letter-spacing: -0.01em;
        '
    ],
    [
        'name' => 'section-subtitle',
        'label' => 'Section Subtitle',
        'css' => '
            font-size: 20px;
            font-weight: 400;
            line-height: 1.4;
        '
    ],
    [
        'name' => 'section-description',
        'label' => 'Section Description',
        'css' => '
            font-size: 16px;
            font-weight: 400;
            line-height: 1.6;
        '
    ],
    
    // Card Typography
    [
        'name' => 'card-pretitle',
        'label' => 'Card Pretitle',
        'css' => '
            font-size: 11px;
            font-weight: 500;
            line-height: 1.2;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        '
    ],
    [
        'name' => 'card-title',
        'label' => 'Card Title',
        'css' => '
            font-size: clamp(20px, 2.5vw, 24px);
            font-weight: 600;
            line-height: 1.3;
        '
    ],
    [
        'name' => 'card-subtitle',
        'label' => 'Card Subtitle',
        'css' => '
            font-size: 16px;
            font-weight: 400;
            line-height: 1.4;
        '
    ],
    [
        'name' => 'card-description',
        'label' => 'Card Description',
        'css' => '
            font-size: 14px;
            font-weight: 400;
            line-height: 1.5;
        '
    ],
    
    // Content Typography
    [
        'name' => 'body-large',
        'label' => 'Body Large',
        'css' => '
            font-size: 20px;
            font-weight: 400;
            line-height: 1.7;
        '
    ],
    [
        'name' => 'body-text',
        'label' => 'Body Text',
        'css' => '
            font-size: 16px;
            font-weight: 400;
            line-height: 1.6;
        '
    ],
    [
        'name' => 'body-small',
        'label' => 'Body Small',
        'css' => '
            font-size: 14px;
            font-weight: 400;
            line-height: 1.5;
        '
    ],
    [
        'name' => 'caption',
        'label' => 'Caption',
        'css' => '
            font-size: 12px;
            font-weight: 400;
            line-height: 1.4;
        '
    ],
    
    // UI Typography
    [
        'name' => 'button-large',
        'label' => 'Button Large',
        'css' => '
            font-size: 16px;
            font-weight: 600;
            line-height: 1;
            letter-spacing: 0.02em;
        '
    ],
    [
        'name' => 'button-medium',
        'label' => 'Button Medium',
        'css' => '
            font-size: 14px;
            font-weight: 600;
            line-height: 1;
            letter-spacing: 0.02em;
        '
    ],
    [
        'name' => 'button-small',
        'label' => 'Button Small',
        'css' => '
            font-size: 12px;
            font-weight: 600;
            line-height: 1;
            letter-spacing: 0.02em;
        '
    ],
    [
        'name' => 'label',
        'label' => 'Label',
        'css' => '
            font-size: 12px;
            font-weight: 500;
            line-height: 1.2;
            letter-spacing: 0.01em;
        '
    ],
    [
        'name' => 'input',
        'label' => 'Input',
        'css' => '
            font-size: 16px;
            font-weight: 400;
            line-height: 1.4;
        '
    ],
    
    // Special Typography
    [
        'name' => 'quote',
        'label' => 'Quote',
        'css' => '
            font-size: 24px;
            font-weight: 400;
            line-height: 1.6;
            font-style: italic;
        '
    ],
    [
        'name' => 'quote-citation',
        'label' => 'Quote Citation',
        'css' => '
            font-size: 14px;
            font-weight: 500;
            line-height: 1.4;
        '
    ],
    [
        'name' => 'code',
        'label' => 'Code',
        'css' => '
            font-size: 14px;
            font-family: "JetBrains Mono", Monaco, Menlo, monospace;
            font-weight: 400;
            line-height: 1.5;
        '
    ],
    [
        'name' => 'overline',
        'label' => 'Overline',
        'css' => '
            font-size: 10px;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: 0.15em;
            text-transform: uppercase;
        '
    ]
];

// Get existing theme.json
$theme_json = json_decode(file_get_contents($theme_json_path), true);
$existing_styles = $theme_json['settings']['custom']['blockStyles'] ?? [];

// Add typography styles for studio/text block
foreach ($typography_styles as $style) {
    $style_key = 'studio-text-' . $style['name'];
    
    $existing_styles[$style_key] = [
        'name' => $style['name'],
        'label' => $style['label'],
        'blockType' => 'studio/text',
        'classes' => 'is-style-' . $style['name'],
        'customCSS' => $style['css'],
        'description' => 'Typography preset: ' . $style['label'],
        'type' => 'css',
        'created' => date('Y-m-d H:i:s')
    ];
    
    echo "Created block style: {$style['label']}\n";
}

// Ensure proper structure
if (!isset($theme_json['settings'])) {
    $theme_json['settings'] = [];
}
if (!isset($theme_json['settings']['custom'])) {
    $theme_json['settings']['custom'] = [];
}

$theme_json['settings']['custom']['blockStyles'] = $existing_styles;

// Write the updated theme.json
$json_content = json_encode($theme_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
file_put_contents($theme_json_path, $json_content);

echo "\nSuccessfully created " . count($typography_styles) . " typography block styles for Studio Text block!\n";
echo "Block styles have been saved to theme.json\n";
echo "Path: $theme_json_path\n";
