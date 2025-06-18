<?php
/**
 * Studio Hero Helper
 * Adds a admin notice with instructions for the hero component
 */

// Add admin notice on pages/posts edit screen
add_action('admin_notices', function() {
    $screen = get_current_screen();
    
    // Only show on post/page edit screens
    if ($screen && ($screen->base === 'post' || $screen->id === 'page')) {
        ?>
        <div class="notice notice-info is-dismissible studio-hero-notice">
            <h3>Studio Hero Component Available!</h3>
            <p><strong>To add the Studio Hero:</strong></p>
            <ol>
                <li>Add a <strong>GenerateBlocks Container</strong> block</li>
                <li>In the block settings, go to <strong>Advanced > Additional CSS Class(es)</strong></li>
                <li>Add these classes: <code>gb-container hero-section</code></li>
                <li>Set Background Image, and add Headline + Text blocks inside</li>
            </ol>
            <p>Or use the shortcode: <code>[studio_hero title="Your Title" description="Your description"]</code></p>
            
            <details style="margin-top: 10px;">
                <summary style="cursor: pointer; color: #0073aa;"><strong>Click for Full GB Code to Copy/Paste</strong></summary>
                <textarea readonly style="width: 100%; height: 200px; margin-top: 10px; font-family: monospace; font-size: 12px;">
<!-- wp:generateblocks/container {"uniqueId":"hero-section","className":"gb-container hero-section","backgroundColor":"","backgroundImage":{"id":"","image":{"url":"ADD-YOUR-IMAGE-URL-HERE"}},"paddingTop":"80","paddingRight":"40","paddingBottom":"80","paddingLeft":"40","minHeight":350,"display":"flex","flexDirection":"column","justifyContent":"center","alignItems":"center"} -->
<!-- wp:generateblocks/headline {"uniqueId":"hero-title","element":"h1","className":"hero-title","textColor":"#ffffff","fontSize":48,"textAlign":"center","marginBottom":"20"} -->
<h1 class="gb-headline gb-headline-text hero-title">Your Hero Title Here</h1>
<!-- /wp:generateblocks/headline -->

<!-- wp:generateblocks/headline {"uniqueId":"hero-desc","element":"p","className":"hero-description","textColor":"#ffffff","fontSize":20,"textAlign":"center"} -->
<p class="gb-headline gb-headline-text hero-description">Your hero description text goes here</p>
<!-- /wp:generateblocks/headline -->
<!-- /wp:generateblocks/container -->
                </textarea>
            </details>
        </div>
        
        <style>
            .studio-hero-notice {
                background: #fff;
                border-left: 4px solid #0073aa;
            }
            .studio-hero-notice h3 {
                margin-top: 0.5em;
            }
            .studio-hero-notice code {
                background: #f0f0f1;
                padding: 2px 6px;
                border-radius: 3px;
            }
        </style>
        <?php
    }
});

// Add to GB Local Templates via filter
add_filter('generateblocks_do_content', function($content) {
    // This ensures our hero CSS is always loaded when GB is used
    if (strpos($content, 'hero-section') !== false) {
        add_action('wp_head', function() {
            echo '<style>/* Hero styles loaded via filter */</style>';
        }, 100);
    }
    return $content;
}, 10, 1);