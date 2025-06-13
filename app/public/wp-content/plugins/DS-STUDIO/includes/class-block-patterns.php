<?php
/**
 * DS-Studio Block Patterns
 * Pre-configured patterns using design system tokens
 */

class DS_Studio_Block_Patterns {
    
    private $theme_json_data;
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('init', array($this, 'register_pattern_categories'));
        add_action('init', array($this, 'register_patterns'));
    }
    
    public function init() {
        $this->load_theme_json();
    }
    
    /**
     * Load theme.json data for pattern generation
     */
    private function load_theme_json() {
        $theme_json_path = get_stylesheet_directory() . '/theme.json';
        
        if (file_exists($theme_json_path)) {
            $this->theme_json_data = json_decode(file_get_contents($theme_json_path), true);
        } else {
            $this->theme_json_data = array();
        }
    }
    
    /**
     * Register pattern categories
     */
    public function register_pattern_categories() {
        register_block_pattern_category('ds-studio-hero', array(
            'label' => __('DS-Studio Heroes', 'ds-studio')
        ));
        
        register_block_pattern_category('ds-studio-content', array(
            'label' => __('DS-Studio Content', 'ds-studio')
        ));
        
        register_block_pattern_category('ds-studio-cards', array(
            'label' => __('DS-Studio Cards', 'ds-studio')
        ));
        
        register_block_pattern_category('ds-studio-layout', array(
            'label' => __('DS-Studio Layouts', 'ds-studio')
        ));
        
        register_block_pattern_category('ds-studio-cta', array(
            'label' => __('DS-Studio CTAs', 'ds-studio')
        ));
    }
    
    /**
     * Register all patterns
     */
    public function register_patterns() {
        $this->register_hero_patterns();
        $this->register_content_patterns();
        $this->register_card_patterns();
        $this->register_layout_patterns();
        $this->register_cta_patterns();
    }
    
    /**
     * Get design token values for pattern generation
     */
    private function get_token_value($path, $fallback = '') {
        $keys = explode('.', $path);
        $value = $this->theme_json_data;
        
        foreach ($keys as $key) {
            if (isset($value[$key])) {
                $value = $value[$key];
            } else {
                return $fallback;
            }
        }
        
        return $value;
    }
    
    /**
     * Get color slug by name or return first available
     */
    private function get_color_slug($preference = 'primary') {
        $colors = $this->get_token_value('settings.color.palette', array());
        
        if (is_array($colors)) {
            foreach ($colors as $color) {
                if (is_array($color) && isset($color['slug']) && $color['slug'] === $preference) {
                    return $color['slug'];
                }
            }
        }
        
        // Return first available color if preference not found
        return (!empty($colors) && is_array($colors) && isset($colors[0]['slug'])) ? $colors[0]['slug'] : 'primary';
    }
    
    /**
     * Get spacing slug by size
     */
    private function get_spacing_slug($size = 'md') {
        $spacing = $this->get_token_value('settings.spacing.spacingSizes', array());
        
        if (is_array($spacing)) {
            foreach ($spacing as $space) {
                if (is_array($space) && isset($space['slug']) && $space['slug'] === $size) {
                    return $space['slug'];
                }
            }
        }
        
        return (!empty($spacing) && is_array($spacing) && isset($spacing[0]['slug'])) ? $spacing[0]['slug'] : 'md';
    }
    
    /**
     * Get font size slug
     */
    private function get_font_size_slug($size = 'lg') {
        $fonts = $this->get_token_value('settings.typography.fontSizes', array());
        
        if (is_array($fonts)) {
            foreach ($fonts as $font) {
                if (is_array($font) && isset($font['slug']) && $font['slug'] === $size) {
                    return $font['slug'];
                }
            }
        }
        
        return (!empty($fonts) && is_array($fonts) && isset($fonts[0]['slug'])) ? $fonts[0]['slug'] : 'lg';
    }
    
    /**
     * Register hero patterns
     */
    private function register_hero_patterns() {
        $primary_color = $this->get_color_slug('primary');
        $secondary_color = $this->get_color_slug('secondary');
        $large_spacing = $this->get_spacing_slug('lg');
        $xl_spacing = $this->get_spacing_slug('xl');
        $hero_font = $this->get_font_size_slug('3xl');
        $subtitle_font = $this->get_font_size_slug('lg');
        
        // Hero with Background
        register_block_pattern('ds-studio/hero-background', array(
            'title' => __('Hero with Background', 'ds-studio'),
            'description' => __('Full-width hero section with background color and centered content', 'ds-studio'),
            'categories' => array('ds-studio-hero'),
            'content' => '<!-- wp:group {"className":"bg-' . $primary_color . ' text-white fluid-py-' . $xl_spacing . ' text-center","layout":{"type":"constrained"}} -->
<div class="wp-block-group bg-' . $primary_color . ' text-white fluid-py-' . $xl_spacing . ' text-center">
    <!-- wp:heading {"level":1,"className":"fluid-text-' . $hero_font . ' font-bold fluid-mb-' . $large_spacing . '"} -->
    <h1 class="wp-block-heading fluid-text-' . $hero_font . ' font-bold fluid-mb-' . $large_spacing . '">Your Amazing Headline</h1>
    <!-- /wp:heading -->
    
    <!-- wp:paragraph {"className":"fluid-text-' . $subtitle_font . ' opacity-90 fluid-mb-' . $xl_spacing . ' max-w-prose mx-auto"} -->
    <p class="fluid-text-' . $subtitle_font . ' opacity-90 fluid-mb-' . $xl_spacing . ' max-w-prose mx-auto">Compelling subtitle that explains your value proposition and engages your audience with clear, concise messaging.</p>
    <!-- /wp:paragraph -->
    
    <!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
    <div class="wp-block-buttons">
        <!-- wp:button {"className":"bg-white text-' . $primary_color . ' px-' . $xl_spacing . ' py-' . $large_spacing . ' rounded-md font-semibold hover:bg-gray-100"} -->
        <div class="wp-block-button"><a class="wp-block-button__link bg-white text-' . $primary_color . ' px-' . $xl_spacing . ' py-' . $large_spacing . ' rounded-md font-semibold hover:bg-gray-100 wp-element-button">Get Started</a></div>
        <!-- /wp:button -->
    </div>
    <!-- /wp:buttons -->
</div>
<!-- /wp:group -->'
        ));
        
        // Hero Split Layout
        register_block_pattern('ds-studio/hero-split', array(
            'title' => __('Hero Split Layout', 'ds-studio'),
            'description' => __('Two-column hero with content and image', 'ds-studio'),
            'categories' => array('ds-studio-hero'),
            'content' => '<!-- wp:columns {"className":"fluid-py-' . $xl_spacing . ' items-center"} -->
<div class="wp-block-columns fluid-py-' . $xl_spacing . ' items-center">
    <!-- wp:column {"className":"fluid-pr-' . $xl_spacing . '"} -->
    <div class="wp-block-column fluid-pr-' . $xl_spacing . '">
        <!-- wp:heading {"level":1,"className":"fluid-text-' . $hero_font . ' font-bold text-' . $primary_color . ' fluid-mb-' . $large_spacing . '"} -->
        <h1 class="wp-block-heading fluid-text-' . $hero_font . ' font-bold text-' . $primary_color . ' fluid-mb-' . $large_spacing . '">Transform Your Business</h1>
        <!-- /wp:heading -->
        
        <!-- wp:paragraph {"className":"fluid-text-' . $subtitle_font . ' text-gray-600 fluid-mb-' . $xl_spacing . '"} -->
        <p class="fluid-text-' . $subtitle_font . ' text-gray-600 fluid-mb-' . $xl_spacing . '">Discover how our solution can help you achieve your goals with innovative technology and expert support.</p>
        <!-- /wp:paragraph -->
        
        <!-- wp:button {"className":"bg-' . $primary_color . ' text-white px-' . $xl_spacing . ' py-' . $large_spacing . ' rounded-md"} -->
        <div class="wp-block-button"><a class="wp-block-button__link bg-' . $primary_color . ' text-white px-' . $xl_spacing . ' py-' . $large_spacing . ' rounded-md wp-element-button">Learn More</a></div>
        <!-- /wp:button -->
    </div>
    <!-- /wp:column -->
    
    <!-- wp:column -->
    <div class="wp-block-column">
        <!-- wp:image {"className":"rounded-lg shadow-lg"} -->
        <figure class="wp-block-image rounded-lg shadow-lg"><img alt="Hero image" /></figure>
        <!-- /wp:image -->
    </div>
    <!-- /wp:column -->
</div>
<!-- /wp:columns -->'
        ));
    }
    
    /**
     * Register content patterns
     */
    private function register_content_patterns() {
        $primary_color = $this->get_color_slug('primary');
        $large_spacing = $this->get_spacing_slug('lg');
        $xl_spacing = $this->get_spacing_slug('xl');
        $title_font = $this->get_font_size_slug('2xl');
        $body_font = $this->get_font_size_slug('base');
        
        // Feature Grid
        register_block_pattern('ds-studio/feature-grid', array(
            'title' => __('Feature Grid', 'ds-studio'),
            'description' => __('Three-column feature grid with icons and descriptions', 'ds-studio'),
            'categories' => array('ds-studio-content'),
            'content' => '<!-- wp:group {"className":"fluid-py-' . $xl_spacing . '"} -->
<div class="wp-block-group fluid-py-' . $xl_spacing . '">
    <!-- wp:heading {"textAlign":"center","className":"fluid-text-' . $title_font . ' font-bold text-' . $primary_color . ' fluid-mb-' . $xl_spacing . '"} -->
    <h2 class="wp-block-heading has-text-align-center fluid-text-' . $title_font . ' font-bold text-' . $primary_color . ' fluid-mb-' . $xl_spacing . '">Our Features</h2>
    <!-- /wp:heading -->
    
    <!-- wp:columns {"className":"fluid-gap-' . $large_spacing . '"} -->
    <div class="wp-block-columns fluid-gap-' . $large_spacing . '">
        <!-- wp:column {"className":"text-center"} -->
        <div class="wp-block-column text-center">
            <!-- wp:paragraph {"className":"text-' . $primary_color . ' fluid-text-4xl fluid-mb-' . $large_spacing . '"} -->
            <p class="text-' . $primary_color . ' fluid-text-4xl fluid-mb-' . $large_spacing . '">🚀</p>
            <!-- /wp:paragraph -->
            
            <!-- wp:heading {"level":3,"className":"fluid-text-lg font-semibold fluid-mb-sm"} -->
            <h3 class="wp-block-heading fluid-text-lg font-semibold fluid-mb-sm">Fast Performance</h3>
            <!-- /wp:heading -->
            
            <!-- wp:paragraph {"className":"fluid-text-' . $body_font . ' text-gray-600"} -->
            <p class="fluid-text-' . $body_font . ' text-gray-600">Lightning-fast loading times and optimized performance for the best user experience.</p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->
        
        <!-- wp:column {"className":"text-center"} -->
        <div class="wp-block-column text-center">
            <!-- wp:paragraph {"className":"text-' . $primary_color . ' fluid-text-4xl fluid-mb-' . $large_spacing . '"} -->
            <p class="text-' . $primary_color . ' fluid-text-4xl fluid-mb-' . $large_spacing . '">🔒</p>
            <!-- /wp:paragraph -->
            
            <!-- wp:heading {"level":3,"className":"fluid-text-lg font-semibold fluid-mb-sm"} -->
            <h3 class="wp-block-heading fluid-text-lg font-semibold fluid-mb-sm">Secure & Reliable</h3>
            <!-- /wp:heading -->
            
            <!-- wp:paragraph {"className":"fluid-text-' . $body_font . ' text-gray-600"} -->
            <p class="fluid-text-' . $body_font . ' text-gray-600">Enterprise-grade security with 99.9% uptime guarantee and regular backups.</p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->
        
        <!-- wp:column {"className":"text-center"} -->
        <div class="wp-block-column text-center">
            <!-- wp:paragraph {"className":"text-' . $primary_color . ' fluid-text-4xl fluid-mb-' . $large_spacing . '"} -->
            <p class="text-' . $primary_color . ' fluid-text-4xl fluid-mb-' . $large_spacing . '">💡</p>
            <!-- /wp:paragraph -->
            
            <!-- wp:heading {"level":3,"className":"fluid-text-lg font-semibold fluid-mb-sm"} -->
            <h3 class="wp-block-heading fluid-text-lg font-semibold fluid-mb-sm">Smart Solutions</h3>
            <!-- /wp:heading -->
            
            <!-- wp:paragraph {"className":"fluid-text-' . $body_font . ' text-gray-600"} -->
            <p class="fluid-text-' . $body_font . ' text-gray-600">Intelligent automation and AI-powered features to streamline your workflow.</p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->
</div>
<!-- /wp:group -->'
        ));
    }
    
    /**
     * Register card patterns
     */
    private function register_card_patterns() {
        $primary_color = $this->get_color_slug('primary');
        $large_spacing = $this->get_spacing_slug('lg');
        $xl_spacing = $this->get_spacing_slug('xl');
        
        // Product Cards
        register_block_pattern('ds-studio/product-cards', array(
            'title' => __('Product Cards', 'ds-studio'),
            'description' => __('Product showcase cards with pricing', 'ds-studio'),
            'categories' => array('ds-studio-cards'),
            'content' => '<!-- wp:columns {"className":"fluid-gap-' . $large_spacing . ' fluid-py-' . $xl_spacing . '"} -->
<div class="wp-block-columns fluid-gap-' . $large_spacing . ' fluid-py-' . $xl_spacing . '">
    <!-- wp:column -->
    <div class="wp-block-column">
        <!-- wp:group {"className":"bg-white border border-gray-200 rounded-lg shadow-md fluid-p-' . $xl_spacing . ' text-center"} -->
        <div class="wp-block-group bg-white border border-gray-200 rounded-lg shadow-md fluid-p-' . $xl_spacing . ' text-center">
            <!-- wp:heading {"level":3,"className":"fluid-text-xl font-bold text-' . $primary_color . ' fluid-mb-' . $large_spacing . '"} -->
            <h3 class="wp-block-heading fluid-text-xl font-bold text-' . $primary_color . ' fluid-mb-' . $large_spacing . '">Starter Plan</h3>
            <!-- /wp:heading -->
            
            <!-- wp:paragraph {"className":"fluid-text-3xl font-bold text-gray-900 fluid-mb-' . $large_spacing . '"} -->
            <p class="fluid-text-3xl font-bold text-gray-900 fluid-mb-' . $large_spacing . '">$29<span class="fluid-text-base font-normal text-gray-500">/month</span></p>
            <!-- /wp:paragraph -->
            
            <!-- wp:list {"className":"text-left fluid-mb-' . $xl_spacing . ' space-y-sm"} -->
            <ul class="wp-block-list text-left fluid-mb-' . $xl_spacing . ' space-y-sm">
                <li>✓ 5 Projects</li>
                <li>✓ 10GB Storage</li>
                <li>✓ Email Support</li>
                <li>✓ Basic Analytics</li>
            </ul>
            <!-- /wp:list -->
            
            <!-- wp:button {"className":"bg-' . $primary_color . ' text-white w-full py-' . $large_spacing . ' rounded-md"} -->
            <div class="wp-block-button"><a class="wp-block-button__link bg-' . $primary_color . ' text-white w-full py-' . $large_spacing . ' rounded-md wp-element-button">Choose Plan</a></div>
            <!-- /wp:button -->
        </div>
        <!-- /wp:group -->
    </div>
    <!-- /wp:column -->
    
    <!-- wp:column -->
    <div class="wp-block-column">
        <!-- wp:group {"className":"bg-' . $primary_color . ' text-white rounded-lg shadow-lg fluid-p-' . $xl_spacing . ' text-center transform scale-105"} -->
        <div class="wp-block-group bg-' . $primary_color . ' text-white rounded-lg shadow-lg fluid-p-' . $xl_spacing . ' text-center transform scale-105">
            <!-- wp:paragraph {"className":"bg-white text-' . $primary_color . ' px-sm py-xs rounded-full fluid-text-sm font-semibold inline-block fluid-mb-' . $large_spacing . '"} -->
            <p class="bg-white text-' . $primary_color . ' px-sm py-xs rounded-full fluid-text-sm font-semibold inline-block fluid-mb-' . $large_spacing . '">Most Popular</p>
            <!-- /wp:paragraph -->
            
            <!-- wp:heading {"level":3,"className":"fluid-text-xl font-bold fluid-mb-' . $large_spacing . '"} -->
            <h3 class="wp-block-heading fluid-text-xl font-bold fluid-mb-' . $large_spacing . '">Pro Plan</h3>
            <!-- /wp:heading -->
            
            <!-- wp:paragraph {"className":"fluid-text-3xl font-bold fluid-mb-' . $large_spacing . '"} -->
            <p class="fluid-text-3xl font-bold fluid-mb-' . $large_spacing . '">$79<span class="fluid-text-base font-normal opacity-80">/month</span></p>
            <!-- /wp:paragraph -->
            
            <!-- wp:list {"className":"text-left fluid-mb-' . $xl_spacing . ' space-y-sm"} -->
            <ul class="wp-block-list text-left fluid-mb-' . $xl_spacing . ' space-y-sm">
                <li>✓ 25 Projects</li>
                <li>✓ 100GB Storage</li>
                <li>✓ Priority Support</li>
                <li>✓ Advanced Analytics</li>
                <li>✓ Team Collaboration</li>
            </ul>
            <!-- /wp:list -->
            
            <!-- wp:button {"className":"bg-white text-' . $primary_color . ' w-full py-' . $large_spacing . ' rounded-md font-semibold"} -->
            <div class="wp-block-button"><a class="wp-block-button__link bg-white text-' . $primary_color . ' w-full py-' . $large_spacing . ' rounded-md font-semibold wp-element-button">Choose Plan</a></div>
            <!-- /wp:button -->
        </div>
        <!-- /wp:group -->
    </div>
    <!-- /wp:column -->
</div>
<!-- /wp:columns -->'
        ));
    }
    
    /**
     * Register layout patterns
     */
    private function register_layout_patterns() {
        $primary_color = $this->get_color_slug('primary');
        $large_spacing = $this->get_spacing_slug('lg');
        $xl_spacing = $this->get_spacing_slug('xl');
        
        // Content with Sidebar
        register_block_pattern('ds-studio/content-sidebar', array(
            'title' => __('Content with Sidebar', 'ds-studio'),
            'description' => __('Two-column layout with main content and sidebar', 'ds-studio'),
            'categories' => array('ds-studio-layout'),
            'content' => '<!-- wp:columns {"className":"fluid-gap-' . $xl_spacing . ' fluid-py-' . $xl_spacing . '"} -->
<div class="wp-block-columns fluid-gap-' . $xl_spacing . ' fluid-py-' . $xl_spacing . '">
    <!-- wp:column {"width":"66.66%"} -->
    <div class="wp-block-column" style="flex-basis:66.66%">
        <!-- wp:heading {"className":"fluid-text-2xl font-bold text-' . $primary_color . ' fluid-mb-' . $large_spacing . '"} -->
        <h2 class="wp-block-heading fluid-text-2xl font-bold text-' . $primary_color . ' fluid-mb-' . $large_spacing . '">Main Content Area</h2>
        <!-- /wp:heading -->
        
        <!-- wp:paragraph {"className":"fluid-text-base text-gray-700 leading-relaxed fluid-mb-' . $large_spacing . '"} -->
        <p class="fluid-text-base text-gray-700 leading-relaxed fluid-mb-' . $large_spacing . '">This is your main content area where you can add articles, blog posts, or any primary content. The layout is responsive and will stack on mobile devices.</p>
        <!-- /wp:paragraph -->
        
        <!-- wp:paragraph {"className":"fluid-text-base text-gray-700 leading-relaxed"} -->
        <p class="fluid-text-base text-gray-700 leading-relaxed">Add more paragraphs, images, or any other content blocks here. The design system ensures consistent spacing and typography throughout.</p>
        <!-- /wp:paragraph -->
    </div>
    <!-- /wp:column -->
    
    <!-- wp:column {"width":"33.33%"} -->
    <div class="wp-block-column" style="flex-basis:33.33%">
        <!-- wp:group {"className":"bg-gray-50 rounded-lg fluid-p-' . $large_spacing . ' fluid-mb-' . $large_spacing . '"} -->
        <div class="wp-block-group bg-gray-50 rounded-lg fluid-p-' . $large_spacing . ' fluid-mb-' . $large_spacing . '">
            <!-- wp:heading {"level":3,"className":"fluid-text-lg font-semibold text-' . $primary_color . ' fluid-mb-sm"} -->
            <h3 class="wp-block-heading fluid-text-lg font-semibold text-' . $primary_color . ' fluid-mb-sm">Quick Links</h3>
            <!-- /wp:heading -->
            
            <!-- wp:list {"className":"space-y-xs"} -->
            <ul class="wp-block-list space-y-xs">
                <li><a href="#" class="text-' . $primary_color . ' hover:underline">Getting Started</a></li>
                <li><a href="#" class="text-' . $primary_color . ' hover:underline">Documentation</a></li>
                <li><a href="#" class="text-' . $primary_color . ' hover:underline">Support</a></li>
                <li><a href="#" class="text-' . $primary_color . ' hover:underline">Contact Us</a></li>
            </ul>
            <!-- /wp:list -->
        </div>
        <!-- /wp:group -->
        
        <!-- wp:group {"className":"bg-' . $primary_color . ' text-white rounded-lg fluid-p-' . $large_spacing . '"} -->
        <div class="wp-block-group bg-' . $primary_color . ' text-white rounded-lg fluid-p-' . $large_spacing . '">
            <!-- wp:heading {"level":3,"className":"fluid-text-lg font-semibold fluid-mb-sm"} -->
            <h3 class="wp-block-heading fluid-text-lg font-semibold fluid-mb-sm">Newsletter</h3>
            <!-- /wp:heading -->
            
            <!-- wp:paragraph {"className":"fluid-text-sm opacity-90 fluid-mb-' . $large_spacing . '"} -->
            <p class="fluid-text-sm opacity-90 fluid-mb-' . $large_spacing . '">Stay updated with our latest news and updates.</p>
            <!-- /wp:paragraph -->
            
            <!-- wp:button {"className":"bg-white text-' . $primary_color . ' w-full py-sm rounded-md"} -->
            <div class="wp-block-button"><a class="wp-block-button__link bg-white text-' . $primary_color . ' w-full py-sm rounded-md wp-element-button">Subscribe</a></div>
            <!-- /wp:button -->
        </div>
        <!-- /wp:group -->
    </div>
    <!-- /wp:column -->
</div>
<!-- /wp:columns -->'
        ));
    }
    
    /**
     * Register CTA patterns
     */
    private function register_cta_patterns() {
        $primary_color = $this->get_color_slug('primary');
        $secondary_color = $this->get_color_slug('secondary');
        $large_spacing = $this->get_spacing_slug('lg');
        $xl_spacing = $this->get_spacing_slug('xl');
        
        // CTA Banner
        register_block_pattern('ds-studio/cta-banner', array(
            'title' => __('CTA Banner', 'ds-studio'),
            'description' => __('Call-to-action banner with gradient background', 'ds-studio'),
            'categories' => array('ds-studio-cta'),
            'content' => '<!-- wp:group {"className":"bg-gradient-to-r from-' . $primary_color . ' to-' . $secondary_color . ' text-white text-center fluid-py-' . $xl_spacing . ' rounded-lg"} -->
<div class="wp-block-group bg-gradient-to-r from-' . $primary_color . ' to-' . $secondary_color . ' text-white text-center fluid-py-' . $xl_spacing . ' rounded-lg">
    <!-- wp:heading {"level":2,"className":"fluid-text-2xl font-bold fluid-mb-' . $large_spacing . '"} -->
    <h2 class="wp-block-heading fluid-text-2xl font-bold fluid-mb-' . $large_spacing . '">Ready to Get Started?</h2>
    <!-- /wp:heading -->
    
    <!-- wp:paragraph {"className":"fluid-text-lg opacity-90 fluid-mb-' . $xl_spacing . ' max-w-prose mx-auto"} -->
    <p class="fluid-text-lg opacity-90 fluid-mb-' . $xl_spacing . ' max-w-prose mx-auto">Join thousands of satisfied customers who have transformed their business with our solution.</p>
    <!-- /wp:paragraph -->
    
    <!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center","flexWrap":"wrap"},"className":"fluid-gap-' . $large_spacing . '"} -->
    <div class="wp-block-buttons fluid-gap-' . $large_spacing . '">
        <!-- wp:button {"className":"bg-white text-' . $primary_color . ' px-' . $xl_spacing . ' py-' . $large_spacing . ' rounded-md font-semibold hover:bg-gray-100"} -->
        <div class="wp-block-button"><a class="wp-block-button__link bg-white text-' . $primary_color . ' px-' . $xl_spacing . ' py-' . $large_spacing . ' rounded-md font-semibold hover:bg-gray-100 wp-element-button">Start Free Trial</a></div>
        <!-- /wp:button -->
        
        <!-- wp:button {"className":"border-2 border-white text-white px-' . $xl_spacing . ' py-' . $large_spacing . ' rounded-md font-semibold hover:bg-white hover:text-' . $primary_color . '"} -->
        <div class="wp-block-button"><a class="wp-block-button__link border-2 border-white text-white px-' . $xl_spacing . ' py-' . $large_spacing . ' rounded-md font-semibold hover:bg-white hover:text-' . $primary_color . ' wp-element-button">Learn More</a></div>
        <!-- /wp:button -->
    </div>
    <!-- /wp:buttons -->
</div>
<!-- /wp:group -->'
        ));
    }
}

// Initialize block patterns
new DS_Studio_Block_Patterns();
?>
