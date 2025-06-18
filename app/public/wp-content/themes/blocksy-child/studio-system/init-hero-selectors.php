<?php
/**
 * Initialize Hero Component Selectors
 * This file should be included in functions.php or run via WordPress admin
 */

// Ensure we're in WordPress
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Initialize hero component selectors
 */
function studio_init_hero_selectors() {
    // Check if selector builder is available
    if (!function_exists('studio_selector_builder')) {
        error_log('Studio Selector Builder not available');
        return false;
    }
    
    $selector_builder = studio_selector_builder();
    
    // Define hero component selectors with Studio variables
    $hero_selectors = [
        // Hero Wrapper
        'studio-hero-wrapper' => [
            'selector' => '.studio-hero-wrapper',
            'name' => 'Studio Hero Wrapper',
            'variables' => [
                'padding' => 'var(--st-space-3xl)',
                'padding-top' => 'var(--st-space-3xl)',
                'padding-bottom' => 'var(--st-space-3xl)',
                'background-color' => 'var(--st-primary-light)',
                'min-height' => '60vh',
                'display' => 'flex',
                'align-items' => 'center',
                'justify-content' => 'center',
                'position' => 'relative',
                'overflow' => 'hidden'
            ]
        ],
        
        // Hero Inner Container
        'studio-hero-inner' => [
            'selector' => '.studio-hero-inner',
            'name' => 'Studio Hero Inner Container',
            'variables' => [
                'max-width' => '1200px',
                'width' => '100%',
                'margin' => '0 auto',
                'padding' => 'var(--st-space-xl)',
                'text-align' => 'center',
                'position' => 'relative',
                'z-index' => 'var(--st-z-10)'
            ]
        ],
        
        // Hero Title
        'studio-hero-title' => [
            'selector' => '.studio-hero-title',
            'name' => 'Studio Hero Title',
            'variables' => [
                'font-family' => 'var(--st-font-heading)',
                'font-size' => 'var(--st-text-5xl)',
                'font-weight' => 'var(--st-font-bold)',
                'line-height' => 'var(--st-leading-tight)',
                'color' => 'var(--st-base-darkest)',
                'margin' => '0',
                'margin-bottom' => 'var(--st-space-lg)',
                'text-shadow' => '0 2px 4px rgba(0, 0, 0, 0.1)'
            ]
        ],
        
        // Hero Description
        'studio-hero-description' => [
            'selector' => '.studio-hero-description',
            'name' => 'Studio Hero Description',
            'variables' => [
                'font-family' => 'var(--st-font-body)',
                'font-size' => 'var(--st-text-xl)',
                'font-weight' => 'var(--st-font-normal)',
                'line-height' => 'var(--st-leading-relaxed)',
                'color' => 'var(--st-base-dark)',
                'margin' => '0',
                'margin-bottom' => 'var(--st-space-xl)',
                'max-width' => '800px',
                'margin-left' => 'auto',
                'margin-right' => 'auto'
            ]
        ],
        
        // Hero with overlay
        'studio-hero-overlay' => [
            'selector' => '.studio-hero-wrapper.has-overlay::before',
            'name' => 'Studio Hero Overlay',
            'variables' => [
                'content' => '""',
                'position' => 'absolute',
                'top' => '0',
                'left' => '0',
                'right' => '0',
                'bottom' => '0',
                'background-color' => 'rgba(0, 0, 0, var(--st-opacity-50))',
                'z-index' => 'var(--st-z-0)'
            ]
        ],
        
        // Hero with background image
        'studio-hero-bg-image' => [
            'selector' => '.studio-hero-wrapper.has-bg-image',
            'name' => 'Studio Hero with Background Image',
            'variables' => [
                'background-size' => 'cover',
                'background-position' => 'center',
                'background-repeat' => 'no-repeat'
            ]
        ],
        
        // Light text variant for title
        'studio-hero-title-light' => [
            'selector' => '.studio-hero-wrapper.light-text .studio-hero-title',
            'name' => 'Studio Hero Title - Light Text',
            'variables' => [
                'color' => 'var(--st-base-lightest)'
            ]
        ],
        
        // Light text variant for description
        'studio-hero-description-light' => [
            'selector' => '.studio-hero-wrapper.light-text .studio-hero-description',
            'name' => 'Studio Hero Description - Light Text',
            'variables' => [
                'color' => 'var(--st-base-lighter)'
            ]
        ],
        
        // Hero button
        'studio-hero-button' => [
            'selector' => '.studio-hero-inner .studio-hero-button',
            'name' => 'Studio Hero Button',
            'variables' => [
                'display' => 'inline-block',
                'padding' => 'var(--st-space-md) var(--st-space-xl)',
                'background-color' => 'var(--st-primary)',
                'color' => 'var(--st-base-lightest)',
                'font-family' => 'var(--st-font-body)',
                'font-size' => 'var(--st-text-base)',
                'font-weight' => 'var(--st-font-semibold)',
                'text-decoration' => 'none',
                'border-radius' => 'var(--st-radius-md)',
                'transition' => 'all var(--st-transition) var(--st-ease-out)',
                'box-shadow' => 'var(--st-shadow-md)'
            ]
        ],
        
        // Hero button hover
        'studio-hero-button-hover' => [
            'selector' => '.studio-hero-inner .studio-hero-button:hover',
            'name' => 'Studio Hero Button - Hover',
            'variables' => [
                'background-color' => 'var(--st-primary-dark)',
                'transform' => 'translateY(-2px)',
                'box-shadow' => 'var(--st-shadow-lg)'
            ]
        ]
    ];
    
    // Mobile responsive selectors
    $hero_mobile_selectors = [
        'studio-hero-wrapper-mobile' => [
            'selector' => '@media (max-width: 768px) { .studio-hero-wrapper',
            'name' => 'Studio Hero Wrapper - Mobile',
            'variables' => [
                'padding-top' => 'var(--st-space-2xl)',
                'padding-bottom' => 'var(--st-space-2xl)',
                'min-height' => '50vh'
            ]
        ],
        
        'studio-hero-title-mobile' => [
            'selector' => '@media (max-width: 768px) { .studio-hero-title',
            'name' => 'Studio Hero Title - Mobile',
            'variables' => [
                'font-size' => 'var(--st-text-3xl)',
                'margin-bottom' => 'var(--st-space-md)'
            ]
        ],
        
        'studio-hero-description-mobile' => [
            'selector' => '@media (max-width: 768px) { .studio-hero-description',
            'name' => 'Studio Hero Description - Mobile',
            'variables' => [
                'font-size' => 'var(--st-text-lg)',
                'margin-bottom' => 'var(--st-space-lg)'
            ]
        ]
    ];
    
    // Add all selectors
    $added = [];
    foreach (array_merge($hero_selectors, $hero_mobile_selectors) as $id => $config) {
        $result = $selector_builder->add_selector(
            $config['selector'],
            $config['variables'],
            $config['name'],
            'global'
        );
        
        if ($result) {
            $added[] = $config['name'];
        }
    }
    
    return [
        'success' => count($added) > 0,
        'added' => $added,
        'message' => count($added) . ' hero selectors have been created'
    ];
}

/**
 * Remove hero selectors (for cleanup)
 */
function studio_remove_hero_selectors() {
    if (!function_exists('studio_selector_builder')) {
        return false;
    }
    
    $selector_builder = studio_selector_builder();
    $selectors = $selector_builder->get_selectors();
    
    $removed = [];
    foreach ($selectors as $id => $selector) {
        if (strpos($selector['name'], 'Studio Hero') !== false) {
            if ($selector_builder->delete_selector($id)) {
                $removed[] = $selector['name'];
            }
        }
    }
    
    return [
        'success' => count($removed) > 0,
        'removed' => $removed,
        'message' => count($removed) . ' hero selectors have been removed'
    ];
}

/**
 * Add admin notice for initialization
 */
add_action('admin_notices', function() {
    if (isset($_GET['studio_hero_init'])) {
        $type = $_GET['studio_hero_init'] === 'success' ? 'success' : 'error';
        $message = $_GET['studio_hero_message'] ?? 'Operation completed';
        
        printf(
            '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
            esc_attr($type),
            esc_html($message)
        );
    }
});

/**
 * Handle admin action
 */
add_action('admin_init', function() {
    if (isset($_GET['studio_action']) && $_GET['studio_action'] === 'init_hero_selectors') {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $result = studio_init_hero_selectors();
        
        $redirect_args = [
            'studio_hero_init' => $result['success'] ? 'success' : 'error',
            'studio_hero_message' => $result['message']
        ];
        
        wp_redirect(add_query_arg($redirect_args, admin_url('themes.php?page=studio-system')));
        exit;
    }
});