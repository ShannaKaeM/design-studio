<?php
/**
 * Debug WordPress Theme.json API
 * Check how WordPress is processing our theme.json
 */

// Only run in admin with debug parameter
if (is_admin() && isset($_GET['debug_wp_api'])) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-info" style="padding: 20px;"><h2>WordPress Theme.json API Debug</h2>';
        
        // 1. Check if theme.json is detected
        echo '<h3>1. Theme.json Detection</h3>';
        echo '<pre>';
        echo 'wp_theme_has_theme_json(): ' . (wp_theme_has_theme_json() ? 'TRUE' : 'FALSE') . "\n";
        echo 'Child theme directory: ' . get_stylesheet_directory() . "\n";
        echo 'Theme.json file exists: ' . (file_exists(get_stylesheet_directory() . '/theme.json') ? 'TRUE' : 'FALSE') . "\n";
        echo '</pre>';
        
        // 2. Check global settings
        echo '<h3>2. Global Settings (wp_get_global_settings)</h3>';
        $global_settings = wp_get_global_settings();
        echo '<pre>';
        if (isset($global_settings['typography']['fontSizes'])) {
            echo "Font sizes found: " . count($global_settings['typography']['fontSizes']) . "\n";
            foreach ($global_settings['typography']['fontSizes'] as $font_size) {
                echo "- {$font_size['name']} ({$font_size['slug']}): {$font_size['size']}\n";
            }
        } else {
            echo "No font sizes found in global settings\n";
        }
        echo '</pre>';
        
        // 3. Check block editor settings
        echo '<h3>3. Block Editor Settings</h3>';
        $editor_settings = get_block_editor_settings();
        echo '<pre>';
        if (isset($editor_settings['fontSizes'])) {
            echo "Editor font sizes found: " . count($editor_settings['fontSizes']) . "\n";
            foreach ($editor_settings['fontSizes'] as $font_size) {
                echo "- {$font_size['name']} ({$font_size['slug']}): {$font_size['size']}\n";
            }
        } else {
            echo "No font sizes found in editor settings\n";
        }
        echo '</pre>';
        
        // 4. Check theme.json data layers
        echo '<h3>4. Theme.json Data Layers</h3>';
        echo '<pre>';
        
        // Get theme data
        $theme_json_theme = WP_Theme_JSON_Resolver::get_theme_data();
        $theme_data = $theme_json_theme->get_data();
        
        if (isset($theme_data['settings']['typography']['fontSizes'])) {
            echo "Theme layer font sizes: " . count($theme_data['settings']['typography']['fontSizes']) . "\n";
            foreach ($theme_data['settings']['typography']['fontSizes'] as $font_size) {
                echo "- {$font_size['name']} ({$font_size['slug']}): {$font_size['size']}\n";
            }
        } else {
            echo "No font sizes in theme layer\n";
        }
        echo '</pre>';
        
        // 5. Check CSS custom properties
        echo '<h3>5. CSS Custom Properties</h3>';
        echo '<pre>';
        $css_vars = wp_get_global_stylesheet();
        if (strpos($css_vars, '--wp--preset--font-size--huge') !== false) {
            echo "✅ Huge font size CSS variable found in global stylesheet\n";
        } else {
            echo "❌ Huge font size CSS variable NOT found in global stylesheet\n";
        }
        
        if (strpos($css_vars, '6rem') !== false) {
            echo "✅ 6rem value found in global stylesheet\n";
        } else {
            echo "❌ 6rem value NOT found in global stylesheet\n";
        }
        echo '</pre>';
        
        // 6. Check parent theme interference
        echo '<h3>6. Parent Theme Check</h3>';
        echo '<pre>';
        echo 'Active theme: ' . get_stylesheet() . "\n";
        echo 'Parent theme: ' . get_template() . "\n";
        echo 'Is child theme: ' . (get_stylesheet() !== get_template() ? 'TRUE' : 'FALSE') . "\n";
        
        // Check if parent has theme.json
        $parent_theme_json = get_template_directory() . '/theme.json';
        echo 'Parent theme.json exists: ' . (file_exists($parent_theme_json) ? 'TRUE' : 'FALSE') . "\n";
        echo '</pre>';
        
        // 7. Raw theme.json content
        echo '<h3>7. Raw Theme.json Content (First 1000 chars)</h3>';
        echo '<pre>';
        $theme_json_content = file_get_contents(get_stylesheet_directory() . '/theme.json');
        echo htmlspecialchars(substr($theme_json_content, 0, 1000)) . '...';
        echo '</pre>';
        
        echo '</div>';
    });
}
?>
