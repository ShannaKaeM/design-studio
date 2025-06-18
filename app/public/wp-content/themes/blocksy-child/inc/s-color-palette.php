<?php
/**
 * S Design System - WordPress Color Palette Integration
 * Adds S system colors to block editor and Blocksy
 */

// Add S colors to WordPress block editor palette
add_action('after_setup_theme', function() {
    // Get current S color values
    $s_colors = [
        [
            'name'  => 'S Primary',
            'slug'  => 's-primary',
            'color' => '#519191', // Your teal color
        ],
        [
            'name'  => 'S Primary Light',
            'slug'  => 's-primary-light',
            'color' => '#8dc0c1',
        ],
        [
            'name'  => 'S Primary Dark',
            'slug'  => 's-primary-dark',
            'color' => '#14706d',
        ],
        [
            'name'  => 'S Secondary',
            'slug'  => 's-secondary',
            'color' => '#6c757d',
        ],
        [
            'name'  => 'S Neutral',
            'slug'  => 's-neutral',
            'color' => '#8b8680',
        ],
        [
            'name'  => 'S Neutral Light',
            'slug'  => 's-neutral-light',
            'color' => '#a8a39d',
        ],
        [
            'name'  => 'S Neutral Dark',
            'slug'  => 's-neutral-dark',
            'color' => '#6e6963',
        ],
        [
            'name'  => 'S Success',
            'slug'  => 's-success',
            'color' => '#28a745',
        ],
        [
            'name'  => 'S Warning',
            'slug'  => 's-warning',
            'color' => '#ffc107',
        ],
        [
            'name'  => 'S Danger',
            'slug'  => 's-danger',
            'color' => '#dc3545',
        ],
        [
            'name'  => 'S Info',
            'slug'  => 's-info',
            'color' => '#17a2b8',
        ],
    ];
    
    // Add color palette support
    add_theme_support('editor-color-palette', $s_colors);
    
    // Disable custom colors if you want to limit to your palette
    // add_theme_support('disable-custom-colors');
    
    // Add gradient presets using S colors
    add_theme_support('editor-gradient-presets', [
        [
            'name'     => 'S Primary to Light',
            'slug'     => 's-primary-gradient',
            'gradient' => 'linear-gradient(135deg, #519191 0%, #8dc0c1 100%)',
        ],
        [
            'name'     => 'S Primary to Dark',
            'slug'     => 's-primary-dark-gradient',
            'gradient' => 'linear-gradient(135deg, #519191 0%, #14706d 100%)',
        ],
        [
            'name'     => 'S Neutral Gradient',
            'slug'     => 's-neutral-gradient',
            'gradient' => 'linear-gradient(135deg, #8b8680 0%, #a8a39d 100%)',
        ],
    ]);
});

// Generate CSS for color classes
add_action('wp_head', function() {
    ?>
    <style>
        /* WordPress color palette classes */
        .has-s-primary-color { color: var(--s-primary) !important; }
        .has-s-primary-background-color { background-color: var(--s-primary) !important; }
        
        .has-s-primary-light-color { color: var(--s-primary-light) !important; }
        .has-s-primary-light-background-color { background-color: var(--s-primary-light) !important; }
        
        .has-s-primary-dark-color { color: var(--s-primary-dark) !important; }
        .has-s-primary-dark-background-color { background-color: var(--s-primary-dark) !important; }
        
        .has-s-secondary-color { color: var(--s-secondary) !important; }
        .has-s-secondary-background-color { background-color: var(--s-secondary) !important; }
        
        .has-s-neutral-color { color: var(--s-neutral) !important; }
        .has-s-neutral-background-color { background-color: var(--s-neutral) !important; }
        
        .has-s-neutral-light-color { color: var(--s-neutral-light) !important; }
        .has-s-neutral-light-background-color { background-color: var(--s-neutral-light) !important; }
        
        .has-s-neutral-dark-color { color: var(--s-neutral-dark) !important; }
        .has-s-neutral-dark-background-color { background-color: var(--s-neutral-dark) !important; }
        
        .has-s-success-color { color: var(--s-success) !important; }
        .has-s-success-background-color { background-color: var(--s-success) !important; }
        
        .has-s-warning-color { color: var(--s-warning) !important; }
        .has-s-warning-background-color { background-color: var(--s-warning) !important; }
        
        .has-s-danger-color { color: var(--s-danger) !important; }
        .has-s-danger-background-color { background-color: var(--s-danger) !important; }
        
        .has-s-info-color { color: var(--s-info) !important; }
        .has-s-info-background-color { background-color: var(--s-info) !important; }
        
        /* Gradient classes */
        .has-s-primary-gradient-gradient-background {
            background: linear-gradient(135deg, var(--s-primary) 0%, var(--s-primary-light) 100%) !important;
        }
        
        .has-s-primary-dark-gradient-gradient-background {
            background: linear-gradient(135deg, var(--s-primary) 0%, var(--s-primary-dark) 100%) !important;
        }
        
        .has-s-neutral-gradient-gradient-background {
            background: linear-gradient(135deg, var(--s-neutral) 0%, var(--s-neutral-light) 100%) !important;
        }
    </style>
    <?php
});

// Add the same styles to the editor
add_action('enqueue_block_editor_assets', function() {
    $inline_css = '
        /* WordPress color palette classes in editor */
        .has-s-primary-color { color: var(--s-primary) !important; }
        .has-s-primary-background-color { background-color: var(--s-primary) !important; }
        
        .has-s-primary-light-color { color: var(--s-primary-light) !important; }
        .has-s-primary-light-background-color { background-color: var(--s-primary-light) !important; }
        
        .has-s-primary-dark-color { color: var(--s-primary-dark) !important; }
        .has-s-primary-dark-background-color { background-color: var(--s-primary-dark) !important; }
        
        .has-s-secondary-color { color: var(--s-secondary) !important; }
        .has-s-secondary-background-color { background-color: var(--s-secondary) !important; }
        
        .has-s-neutral-color { color: var(--s-neutral) !important; }
        .has-s-neutral-background-color { background-color: var(--s-neutral) !important; }
        
        .has-s-neutral-light-color { color: var(--s-neutral-light) !important; }
        .has-s-neutral-light-background-color { background-color: var(--s-neutral-light) !important; }
        
        .has-s-neutral-dark-color { color: var(--s-neutral-dark) !important; }
        .has-s-neutral-dark-background-color { background-color: var(--s-neutral-dark) !important; }
        
        /* Success, Warning, Danger, Info colors */
        .has-s-success-color { color: var(--s-success) !important; }
        .has-s-success-background-color { background-color: var(--s-success) !important; }
        
        .has-s-warning-color { color: var(--s-warning) !important; }
        .has-s-warning-background-color { background-color: var(--s-warning) !important; }
        
        .has-s-danger-color { color: var(--s-danger) !important; }
        .has-s-danger-background-color { background-color: var(--s-danger) !important; }
        
        .has-s-info-color { color: var(--s-info) !important; }
        .has-s-info-background-color { background-color: var(--s-info) !important; }
    ';
    
    wp_add_inline_style('wp-edit-blocks', $inline_css);
});

// For Blocksy integration - add colors to customizer
add_filter('blocksy:colors:palette:defaults', function($colors) {
    // Add S system colors to Blocksy's palette
    $s_palette = [
        [
            'id' => 's-primary',
            'color' => 'var(--s-primary)',
        ],
        [
            'id' => 's-primary-light', 
            'color' => 'var(--s-primary-light)',
        ],
        [
            'id' => 's-primary-dark',
            'color' => 'var(--s-primary-dark)',
        ],
        [
            'id' => 's-secondary',
            'color' => 'var(--s-secondary)',
        ],
        [
            'id' => 's-neutral',
            'color' => 'var(--s-neutral)',
        ],
    ];
    
    return array_merge($colors, $s_palette);
});

// Add S colors to GenerateBlocks
add_filter('generateblocks_editor_color_palette', function($colors) {
    $s_colors = [
        [ 'name' => 'S Primary', 'slug' => 's-primary', 'color' => 'var(--s-primary)' ],
        [ 'name' => 'S Primary Light', 'slug' => 's-primary-light', 'color' => 'var(--s-primary-light)' ],
        [ 'name' => 'S Primary Dark', 'slug' => 's-primary-dark', 'color' => 'var(--s-primary-dark)' ],
        [ 'name' => 'S Secondary', 'slug' => 's-secondary', 'color' => 'var(--s-secondary)' ],
        [ 'name' => 'S Neutral', 'slug' => 's-neutral', 'color' => 'var(--s-neutral)' ],
        [ 'name' => 'S Neutral Light', 'slug' => 's-neutral-light', 'color' => 'var(--s-neutral-light)' ],
        [ 'name' => 'S Neutral Dark', 'slug' => 's-neutral-dark', 'color' => 'var(--s-neutral-dark)' ],
        [ 'name' => 'S Success', 'slug' => 's-success', 'color' => 'var(--s-success)' ],
        [ 'name' => 'S Warning', 'slug' => 's-warning', 'color' => 'var(--s-warning)' ],
        [ 'name' => 'S Danger', 'slug' => 's-danger', 'color' => 'var(--s-danger)' ],
        [ 'name' => 'S Info', 'slug' => 's-info', 'color' => 'var(--s-info)' ],
    ];
    
    return array_merge($colors, $s_colors);
});