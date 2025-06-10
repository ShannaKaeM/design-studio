<?php
/**
 * Debug script to test DS Studio GenerateBlocks integration
 * 
 * Add this to wp-config.php to enable:
 * define('WP_DEBUG', true);
 * define('WP_DEBUG_LOG', true);
 * define('WP_DEBUG_DISPLAY', false);
 */

// Test our integration class
if (class_exists('DS_Studio_GenerateBlocks_Integration')) {
    echo '<div style="background: #f0f0f0; padding: 20px; margin: 20px; border: 1px solid #ccc;">';
    echo '<h3>DS Studio GenerateBlocks Integration Debug</h3>';
    
    // Test theme.json loading
    $theme_json_file = get_stylesheet_directory() . '/theme.json';
    echo '<p><strong>Theme.json file:</strong> ' . $theme_json_file . '</p>';
    echo '<p><strong>File exists:</strong> ' . (file_exists($theme_json_file) ? 'Yes' : 'No') . '</p>';
    
    if (file_exists($theme_json_file)) {
        $theme_json_content = file_get_contents($theme_json_file);
        $theme_json_data = json_decode($theme_json_content, true);
        
        if (isset($theme_json_data['settings']['typography']['fontSizes'])) {
            echo '<p><strong>Font sizes found:</strong> ' . count($theme_json_data['settings']['typography']['fontSizes']) . '</p>';
            echo '<ul>';
            foreach ($theme_json_data['settings']['typography']['fontSizes'] as $size) {
                echo '<li>' . $size['name'] . ': ' . $size['size'] . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p><strong>No font sizes found in theme.json</strong></p>';
        }
        
        if (isset($theme_json_data['settings']['typography']['fontFamilies'])) {
            echo '<p><strong>Font families found:</strong> ' . count($theme_json_data['settings']['typography']['fontFamilies']) . '</p>';
        }
    }
    
    // Test GenerateBlocks
    echo '<p><strong>GenerateBlocks active:</strong> ' . (class_exists('GenerateBlocks') ? 'Yes' : 'No') . '</p>';
    
    // Test current screen
    if (function_exists('get_current_screen')) {
        $screen = get_current_screen();
        if ($screen) {
            echo '<p><strong>Current screen ID:</strong> ' . $screen->id . '</p>';
            echo '<p><strong>Current screen base:</strong> ' . $screen->base . '</p>';
        }
    }
    
    echo '</div>';
}

// Add this as an admin notice
add_action('admin_notices', function() {
    if (current_user_can('manage_options') && isset($_GET['ds_debug'])) {
        ob_start();
        include __FILE__;
        $debug_output = ob_get_clean();
        echo $debug_output;
    }
});
?>
