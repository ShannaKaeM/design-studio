<?php
/**
 * Create Studio Selectors for Hero Components
 * Maps Studio CSS variables to hero component classes
 */

require_once 'selector-builder.php';

// Initialize the selector builder
$selector_builder = studio_selector_builder();

// Hero Wrapper Selector
$hero_wrapper_vars = [
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
];

$selector_builder->add_selector(
    '.studio-hero-wrapper',
    $hero_wrapper_vars,
    'Studio Hero Wrapper',
    'global'
);

// Hero Inner Container Selector
$hero_inner_vars = [
    'max-width' => '1200px',
    'width' => '100%',
    'margin' => '0 auto',
    'padding' => 'var(--st-space-xl)',
    'text-align' => 'center',
    'position' => 'relative',
    'z-index' => 'var(--st-z-10)'
];

$selector_builder->add_selector(
    '.studio-hero-inner',
    $hero_inner_vars,
    'Studio Hero Inner Container',
    'global'
);

// Hero Title Selector
$hero_title_vars = [
    'font-family' => 'var(--st-font-heading)',
    'font-size' => 'var(--st-text-5xl)',
    'font-weight' => 'var(--st-font-bold)',
    'line-height' => 'var(--st-leading-tight)',
    'color' => 'var(--st-base-darkest)',
    'margin' => '0',
    'margin-bottom' => 'var(--st-space-lg)',
    'text-shadow' => '0 2px 4px rgba(0, 0, 0, 0.1)'
];

$selector_builder->add_selector(
    '.studio-hero-title',
    $hero_title_vars,
    'Studio Hero Title',
    'global'
);

// Hero Description Selector
$hero_description_vars = [
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
];

$selector_builder->add_selector(
    '.studio-hero-description',
    $hero_description_vars,
    'Studio Hero Description',
    'global'
);

// Additional responsive selectors for mobile
$hero_wrapper_mobile_vars = [
    'padding-top' => 'var(--st-space-2xl)',
    'padding-bottom' => 'var(--st-space-2xl)',
    'min-height' => '50vh'
];

$selector_builder->add_selector(
    '@media (max-width: 768px) { .studio-hero-wrapper',
    $hero_wrapper_mobile_vars,
    'Studio Hero Wrapper - Mobile',
    'global'
);

$hero_title_mobile_vars = [
    'font-size' => 'var(--st-text-3xl)',
    'margin-bottom' => 'var(--st-space-md)'
];

$selector_builder->add_selector(
    '@media (max-width: 768px) { .studio-hero-title',
    $hero_title_mobile_vars,
    'Studio Hero Title - Mobile',
    'global'
);

$hero_description_mobile_vars = [
    'font-size' => 'var(--st-text-lg)',
    'margin-bottom' => 'var(--st-space-lg)'
];

$selector_builder->add_selector(
    '@media (max-width: 768px) { .studio-hero-description',
    $hero_description_mobile_vars,
    'Studio Hero Description - Mobile',
    'global'
);

// Hero variants with overlay
$hero_overlay_vars = [
    'position' => 'absolute',
    'top' => '0',
    'left' => '0',
    'right' => '0',
    'bottom' => '0',
    'background-color' => 'rgba(0, 0, 0, var(--st-opacity-50))',
    'z-index' => 'var(--st-z-0)'
];

$selector_builder->add_selector(
    '.studio-hero-wrapper.has-overlay::before',
    $hero_overlay_vars,
    'Studio Hero Overlay',
    'global'
);

// Hero with background image support
$hero_bg_image_vars = [
    'background-size' => 'cover',
    'background-position' => 'center',
    'background-repeat' => 'no-repeat'
];

$selector_builder->add_selector(
    '.studio-hero-wrapper.has-bg-image',
    $hero_bg_image_vars,
    'Studio Hero with Background Image',
    'global'
);

// Light text variant for dark backgrounds
$hero_light_text_vars = [
    'color' => 'var(--st-base-lightest)'
];

$selector_builder->add_selector(
    '.studio-hero-wrapper.light-text .studio-hero-title',
    $hero_light_text_vars,
    'Studio Hero Title - Light Text',
    'global'
);

$hero_light_description_vars = [
    'color' => 'var(--st-base-lighter)'
];

$selector_builder->add_selector(
    '.studio-hero-wrapper.light-text .studio-hero-description',
    $hero_light_description_vars,
    'Studio Hero Description - Light Text',
    'global'
);

// Hero button styles if included
$hero_button_vars = [
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
];

$selector_builder->add_selector(
    '.studio-hero-inner .studio-hero-button',
    $hero_button_vars,
    'Studio Hero Button',
    'global'
);

$hero_button_hover_vars = [
    'background-color' => 'var(--st-primary-dark)',
    'transform' => 'translateY(-2px)',
    'box-shadow' => 'var(--st-shadow-lg)'
];

$selector_builder->add_selector(
    '.studio-hero-inner .studio-hero-button:hover',
    $hero_button_hover_vars,
    'Studio Hero Button - Hover',
    'global'
);

echo "Hero component selectors have been created successfully!\n";
echo "The following selectors were added:\n";
echo "- .studio-hero-wrapper\n";
echo "- .studio-hero-inner\n";
echo "- .studio-hero-title\n";
echo "- .studio-hero-description\n";
echo "- .studio-hero-wrapper.has-overlay\n";
echo "- .studio-hero-wrapper.has-bg-image\n";
echo "- .studio-hero-wrapper.light-text (variants)\n";
echo "- .studio-hero-button\n";
echo "- Mobile responsive variants\n";
echo "\nThe CSS has been generated in /assets/css/studio-selectors.css\n";