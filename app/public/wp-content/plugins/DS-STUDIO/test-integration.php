<?php
/**
 * DS-Studio GenerateBlocks Integration Test
 * 
 * Test page to verify theme.json design tokens are properly injected into GenerateBlocks
 * Access via: /wp-admin/admin.php?page=ds-studio-test-integration
 */

if (!defined('ABSPATH')) {
    exit;
}

// Add admin menu item
add_action('admin_menu', function() {
    add_submenu_page(
        'ds-studio',
        'Integration Test',
        'Integration Test',
        'manage_options',
        'ds-studio-test-integration',
        'ds_studio_test_integration_page'
    );
});

function ds_studio_test_integration_page() {
    ?>
    <div class="wrap">
        <h1>🧪 DS-Studio GenerateBlocks Integration Test</h1>
        
        <div class="notice notice-info">
            <p><strong>Testing Integration Status:</strong> This page verifies that theme.json design tokens are properly injected into GenerateBlocks styling controls.</p>
        </div>
        
        <?php
        // Test 1: Check if GenerateBlocks is active
        echo '<div class="card" style="margin: 20px 0; padding: 20px;">';
        echo '<h2>🔌 Plugin Status</h2>';
        
        if (class_exists('GenerateBlocks')) {
            echo '<p style="color: green;">✅ <strong>GenerateBlocks:</strong> Active and detected</p>';
        } else {
            echo '<p style="color: red;">❌ <strong>GenerateBlocks:</strong> Not found or inactive</p>';
        }
        
        if (class_exists('DS_Studio_GenerateBlocks_Integration')) {
            echo '<p style="color: green;">✅ <strong>DS-Studio Integration:</strong> Loaded successfully</p>';
        } else {
            echo '<p style="color: red;">❌ <strong>DS-Studio Integration:</strong> Not loaded</p>';
        }
        
        if (class_exists('DS_Studio_Utility_Class_Injector')) {
            echo '<p style="color: green;">✅ <strong>Utility Class Injector:</strong> Loaded successfully</p>';
        } else {
            echo '<p style="color: red;">❌ <strong>Utility Class Injector:</strong> Not loaded</p>';
        }
        echo '</div>';
        
        // Test 2: Theme.json data verification
        echo '<div class="card" style="margin: 20px 0; padding: 20px;">';
        echo '<h2>🎨 Theme.json Design Tokens</h2>';
        
        $theme_json_file = get_stylesheet_directory() . '/theme.json';
        if (file_exists($theme_json_file)) {
            echo '<p style="color: green;">✅ <strong>Theme.json file:</strong> Found at ' . $theme_json_file . '</p>';
            
            $theme_json_content = file_get_contents($theme_json_file);
            $theme_json_data = json_decode($theme_json_content, true);
            
            if ($theme_json_data) {
                echo '<p style="color: green;">✅ <strong>Theme.json parsing:</strong> Valid JSON structure</p>';
                
                // Check design tokens
                $colors = isset($theme_json_data['settings']['color']['palette']) ? count($theme_json_data['settings']['color']['palette']) : 0;
                $font_sizes = isset($theme_json_data['settings']['typography']['fontSizes']) ? count($theme_json_data['settings']['typography']['fontSizes']) : 0;
                $spacing = isset($theme_json_data['settings']['spacing']['spacingSizes']) ? count($theme_json_data['settings']['spacing']['spacingSizes']) : 0;
                
                echo "<p><strong>Design Tokens Found:</strong></p>";
                echo "<ul>";
                echo "<li>🎨 <strong>Colors:</strong> {$colors} presets</li>";
                echo "<li>📝 <strong>Font Sizes:</strong> {$font_sizes} presets</li>";
                echo "<li>📏 <strong>Spacing:</strong> {$spacing} presets</li>";
                echo "</ul>";
                
                if ($colors > 0 && $font_sizes > 0 && $spacing > 0) {
                    echo '<p style="color: green;">✅ <strong>Design System:</strong> Complete token structure detected</p>';
                } else {
                    echo '<p style="color: orange;">⚠️ <strong>Design System:</strong> Some tokens missing</p>';
                }
            } else {
                echo '<p style="color: red;">❌ <strong>Theme.json parsing:</strong> Invalid JSON structure</p>';
            }
        } else {
            echo '<p style="color: red;">❌ <strong>Theme.json file:</strong> Not found</p>';
        }
        echo '</div>';
        
        // Test 3: GenerateBlocks integration hooks
        if (class_exists('GenerateBlocks')) {
            echo '<div class="card" style="margin: 20px 0; padding: 20px;">';
            echo '<h2>🔗 Integration Hooks Status</h2>';
            
            // Test font family filter
            $font_families = apply_filters('generateblocks_typography_font_family_list', array());
            $theme_fonts_found = false;
            foreach ($font_families as $group) {
                if (isset($group['label']) && $group['label'] === 'Theme Fonts') {
                    $theme_fonts_found = true;
                    break;
                }
            }
            
            if ($theme_fonts_found) {
                echo '<p style="color: green;">✅ <strong>Font Family Injection:</strong> Theme fonts successfully added to GenerateBlocks</p>';
            } else {
                echo '<p style="color: orange;">⚠️ <strong>Font Family Injection:</strong> Theme fonts not detected in GenerateBlocks list</p>';
            }
            
            // Test defaults injection
            $defaults = apply_filters('generateblocks_defaults', array());
            if (!empty($defaults)) {
                echo '<p style="color: green;">✅ <strong>Defaults Injection:</strong> GenerateBlocks defaults filter is active</p>';
            } else {
                echo '<p style="color: orange;">⚠️ <strong>Defaults Injection:</strong> No defaults detected</p>';
            }
            
            // Test editor data injection
            $editor_data = apply_filters('generateblocks_editor_data', array());
            if (isset($editor_data['dsStudioTokens'])) {
                echo '<p style="color: green;">✅ <strong>Editor Data Injection:</strong> Design tokens successfully added to editor data</p>';
                $tokens = $editor_data['dsStudioTokens'];
                echo "<p><strong>Injected Tokens:</strong></p>";
                echo "<ul>";
                if (isset($tokens['colors'])) echo "<li>🎨 Colors: " . count($tokens['colors']) . " tokens</li>";
                if (isset($tokens['fontSizes'])) echo "<li>📝 Font Sizes: " . count($tokens['fontSizes']) . " tokens</li>";
                if (isset($tokens['spacing'])) echo "<li>📏 Spacing: " . count($tokens['spacing']) . " tokens</li>";
                if (isset($tokens['typography'])) echo "<li>🔤 Typography: " . count($tokens['typography']) . " tokens</li>";
                echo "</ul>";
            } else {
                echo '<p style="color: orange;">⚠️ <strong>Editor Data Injection:</strong> Design tokens not found in editor data</p>';
            }
            
            echo '</div>';
        }
        
        // Test 4: WordPress editor settings
        echo '<div class="card" style="margin: 20px 0; padding: 20px;">';
        echo '<h2>⚙️ WordPress Editor Settings</h2>';
        
        $editor_settings = apply_filters('block_editor_settings_all', array(), null);
        
        if (isset($editor_settings['fontSizes'])) {
            echo '<p style="color: green;">✅ <strong>Font Size Settings:</strong> ' . count($editor_settings['fontSizes']) . ' font sizes available in editor</p>';
        } else {
            echo '<p style="color: orange;">⚠️ <strong>Font Size Settings:</strong> No font sizes found in editor settings</p>';
        }
        
        if (isset($editor_settings['spacingSizes'])) {
            echo '<p style="color: green;">✅ <strong>Spacing Settings:</strong> ' . count($editor_settings['spacingSizes']) . ' spacing sizes available in editor</p>';
        } else {
            echo '<p style="color: orange;">⚠️ <strong>Spacing Settings:</strong> No spacing sizes found in editor settings</p>';
        }
        
        echo '</div>';
        
        // Test 5: Utility class generation
        if (class_exists('DS_Studio_Utility_Class_Injector')) {
            echo '<div class="card" style="margin: 20px 0; padding: 20px;">';
            echo '<h2>🛠️ Utility Class System</h2>';
            
            // Test REST API endpoint
            $api_url = rest_url('ds-studio/v1/class-suggestions');
            echo "<p><strong>REST API Endpoint:</strong> <code>{$api_url}</code></p>";
            
            // Test utility class generation (simulate)
            $injector = new DS_Studio_Utility_Class_Injector();
            $test_data = apply_filters('generateblocks_editor_data', array());
            
            if (isset($test_data['dsStudioUtilities'])) {
                $utilities = $test_data['dsStudioUtilities'];
                echo '<p style="color: green;">✅ <strong>Utility Classes:</strong> Successfully generated and injected</p>';
                echo "<p><strong>Generated Utilities:</strong></p>";
                echo "<ul>";
                if (isset($utilities['classes'])) echo "<li>📦 Total Classes: " . count($utilities['classes']) . "</li>";
                if (isset($utilities['categories'])) echo "<li>📂 Categories: " . count($utilities['categories']) . "</li>";
                echo "</ul>";
            } else {
                echo '<p style="color: orange;">⚠️ <strong>Utility Classes:</strong> Not found in editor data</p>';
            }
            
            echo '</div>';
        }
        
        // Test 6: JavaScript integration
        echo '<div class="card" style="margin: 20px 0; padding: 20px;">';
        echo '<h2>🚀 JavaScript Integration</h2>';
        
        echo '<p><strong>Integration Scripts:</strong></p>';
        echo '<ul>';
        echo '<li>📄 <code>gb-integration.js</code> - Main GenerateBlocks enhancement script</li>';
        echo '<li>🛠️ <code>utility-class-injector.js</code> - Utility class autocomplete and picker</li>';
        echo '</ul>';
        
        echo '<p><strong>Expected Features:</strong></p>';
        echo '<ul>';
        echo '<li>🎨 Color picker with theme.json color presets</li>';
        echo '<li>📝 Font size controls with theme.json size presets</li>';
        echo '<li>📏 Spacing controls with theme.json spacing presets</li>';
        echo '<li>🛠️ Class field autocomplete with utility class suggestions</li>';
        echo '<li>🎯 Smart suggestions based on block type</li>';
        echo '<li>🔍 Design token inspector panel</li>';
        echo '</ul>';
        
        echo '<div style="background: #f0f0f0; padding: 15px; border-radius: 4px; margin-top: 15px;">';
        echo '<p><strong>🧪 Manual Testing Instructions:</strong></p>';
        echo '<ol>';
        echo '<li>Go to any page/post editor</li>';
        echo '<li>Add a GenerateBlocks Container, Button, or Headline block</li>';
        echo '<li>Open the block settings panel</li>';
        echo '<li>Check typography controls for font size presets</li>';
        echo '<li>Check color controls for theme color presets</li>';
        echo '<li>Check spacing controls for theme spacing presets</li>';
        echo '<li>Try typing in the "Additional CSS Classes" field for autocomplete</li>';
        echo '<li>Look for the "🎨 Browse Classes" button next to class fields</li>';
        echo '</ol>';
        echo '</div>';
        
        echo '</div>';
        
        // Summary
        echo '<div class="card" style="margin: 20px 0; padding: 20px; background: #e7f3ff; border-left: 4px solid #0073aa;">';
        echo '<h2>📊 Integration Summary</h2>';
        
        $total_tests = 0;
        $passed_tests = 0;
        
        // Count tests
        if (class_exists('GenerateBlocks')) $passed_tests++;
        $total_tests++;
        
        if (class_exists('DS_Studio_GenerateBlocks_Integration')) $passed_tests++;
        $total_tests++;
        
        if (file_exists($theme_json_file)) $passed_tests++;
        $total_tests++;
        
        $score = $total_tests > 0 ? round(($passed_tests / $total_tests) * 100) : 0;
        
        echo "<p><strong>Integration Score: {$score}% ({$passed_tests}/{$total_tests} tests passed)</strong></p>";
        
        if ($score >= 80) {
            echo '<p style="color: green; font-size: 16px;">🎉 <strong>Excellent!</strong> Your GenerateBlocks integration is working well. The theme.json design tokens should be available in GenerateBlocks styling controls.</p>';
        } elseif ($score >= 60) {
            echo '<p style="color: orange; font-size: 16px;">⚠️ <strong>Good progress!</strong> Most components are working, but some issues need attention.</p>';
        } else {
            echo '<p style="color: red; font-size: 16px;">❌ <strong>Issues detected.</strong> Several components need troubleshooting before the integration will work properly.</p>';
        }
        
        echo '</div>';
        ?>
        
        <style>
        .card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .card h2 {
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        code {
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        </style>
    </div>
    <?php
}
