<?php
/**
 * Studio Hero Component
 * 
 * GenerateBlocks Hero Component with Studio Variables
 */

// Add custom CSS for the hero component
add_action('wp_head', function() {
    ?>
    <style>
        /* Studio Hero Component Styles */
        .gb-container.hero-section {
            /* Use Studio variables */
            max-width: var(--ts-container-width, 1200px);
            margin-left: 10% !important;
            margin-right: 10% !important;
            margin-top: var(--ts-spacing-10, 10%) !important;
            margin-bottom: var(--ts-spacing-10, 10%) !important;
            width: 80% !important; /* 100% - 20% margins */
            border-radius: var(--ts-radius-xl, 2rem);
            position: relative;
            overflow: hidden;
            
            /* Ensure centered */
            margin-left: auto !important;
            margin-right: auto !important;
            
            /* Padding using Studio variables */
            padding: var(--ts-spacing-2xl, 4rem) var(--ts-spacing-xl, 2rem) !important;
        }
        
        /* Dark overlay - works with GB's built-in overlay */
        .gb-container.hero-section .gb-background-overlay {
            background-color: var(--ts-overlay-dark, rgba(0, 0, 0, 0.5)) !important;
        }
        
        /* Fallback overlay if GB overlay not used */
        .gb-container.hero-section:not(.gb-has-background-overlay)::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--ts-overlay-dark, rgba(0, 0, 0, 0.5));
            z-index: 1;
            pointer-events: none;
        }
        
        /* Ensure content is above overlay */
        .gb-container.hero-section > * {
            position: relative;
            z-index: 2;
        }
        
        /* Hero title styling */
        .gb-container.hero-section .hero-title {
            font-size: var(--ts-text-hero, 3rem) !important;
            color: var(--ts-color-white, #ffffff) !important;
            line-height: var(--ts-line-tight, 1.2);
            margin-bottom: var(--ts-spacing-md, 1rem) !important;
        }
        
        /* Hero description styling */
        .gb-container.hero-section .hero-description {
            font-size: var(--ts-text-lg, 1.25rem) !important;
            color: var(--ts-color-white, #ffffff) !important;
            line-height: var(--ts-line-relaxed, 1.75);
            opacity: 0.95;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .gb-container.hero-section {
                margin-left: 5% !important;
                margin-right: 5% !important;
                width: 90% !important;
                padding: var(--ts-spacing-xl, 2rem) var(--ts-spacing-lg, 1.5rem) !important;
                min-height: 300px !important;
            }
            
            .gb-container.hero-section .hero-title {
                font-size: var(--ts-text-2xl, 2rem) !important;
            }
            
            .gb-container.hero-section .hero-description {
                font-size: var(--ts-text-base, 1rem) !important;
            }
        }
    </style>
    <?php
});

/**
 * Register block pattern
 */
add_action('init', function() {
    if (!function_exists('register_block_pattern')) {
        return;
    }
    
    register_block_pattern(
        'studio/hero-section',
        [
            'title' => __('Studio Hero Section', 'the-studio'),
            'description' => __('Full width hero with centered content and overlay', 'the-studio'),
            'categories' => ['header', 'hero'],
            'keywords' => ['hero', 'banner', 'header', 'studio'],
            'content' => '<!-- wp:generateblocks/container {"uniqueId":"hero-wrapper","className":"hero-wrapper","width":100,"widthUnit":"%","innerContainer":"full","paddingTop":"0","paddingRight":"0","paddingBottom":"0","paddingLeft":"0"} -->
<!-- wp:generateblocks/container {"uniqueId":"hero-inner","className":"gb-container hero-section","width":100,"widthUnit":"%","minHeight":350,"minHeightUnit":"px","display":"flex","flexDirection":"column","justifyContent":"center","alignItems":"center","position":"relative","backgroundImage":{"url":"https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=1600&h=600&fit=crop"},"backgroundSize":"cover","backgroundPosition":"center center"} -->
<!-- wp:generateblocks/headline {"uniqueId":"hero-title","className":"hero-title","element":"h1","content":"Welcome to Villa Capriani","textAlign":"center","textColor":"#ffffff"} /-->

<!-- wp:generateblocks/text {"uniqueId":"hero-description","className":"hero-description","content":"<p>Experience luxury coastal living in our exclusive beachfront community</p>","textAlign":"center","textColor":"#ffffff"} /-->
<!-- /wp:generateblocks/container -->
<!-- /wp:generateblocks/container -->'
        ]
    );
});

/**
 * Shortcode version
 */
add_shortcode('studio_hero', function($atts) {
    $atts = shortcode_atts([
        'title' => 'Welcome to Villa Capriani',
        'description' => 'Experience luxury coastal living in our exclusive beachfront community',
        'image' => 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=1600&h=600&fit=crop',
        'min_height' => '350px',
        'overlay' => 'dark'
    ], $atts);
    
    ob_start();
    ?>
    <div class="wp-block-generateblocks-container hero-wrapper">
        <div class="gb-container hero-section" style="background-image: url('<?php echo esc_url($atts['image']); ?>'); min-height: <?php echo esc_attr($atts['min_height']); ?>;">
            <h1 class="hero-title"><?php echo esc_html($atts['title']); ?></h1>
            <div class="hero-description">
                <p><?php echo esc_html($atts['description']); ?></p>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
});

/**
 * Add to Studio variables if they don't exist
 */
add_action('init', function() {
    // This ensures these variables are available
    $default_vars = [
        '--ts-spacing-10' => '10%',
        '--ts-radius-xl' => '2rem',
        '--ts-overlay-dark' => 'rgba(0, 0, 0, 0.5)',
        '--ts-text-hero' => 'clamp(2rem, 5vw, 3.5rem)',
        '--ts-spacing-2xl' => '4rem',
        '--ts-line-tight' => '1.2'
    ];
    
    // You could add these to your studio-vars.css if they're missing
});