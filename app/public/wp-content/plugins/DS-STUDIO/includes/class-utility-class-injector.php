<?php
/**
 * Utility Class Injector
 * 
 * Injects DS-Studio utility classes into GenerateBlocks class editor
 * Provides autocomplete and suggestions for design system classes
 *
 * @package DS_Studio
 */

if (!defined('ABSPATH')) {
    exit;
}

class DS_Studio_Utility_Class_Injector {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // Only run if GenerateBlocks is active
        if (!class_exists('GenerateBlocks')) {
            return;
        }
        
        // Hook into GenerateBlocks editor
        add_action('admin_enqueue_scripts', array($this, 'enqueue_class_injector_script'));
        add_filter('generateblocks_editor_data', array($this, 'inject_utility_class_data'));
        
        // Add REST API endpoint for class suggestions
        add_action('rest_api_init', array($this, 'register_class_suggestion_endpoint'));
    }
    
    /**
     * Enqueue utility class injector script
     */
    public function enqueue_class_injector_script() {
        if (!is_admin()) {
            return;
        }
        
        wp_enqueue_script(
            'ds-studio-utility-injector',
            DS_STUDIO_PLUGIN_URL . 'assets/js/utility-class-injector.js',
            array('wp-element', 'wp-components', 'wp-hooks', 'wp-data'),
            DS_STUDIO_VERSION,
            true
        );
        
        // Pass utility class data to JavaScript
        wp_localize_script(
            'ds-studio-utility-injector',
            'dsStudioUtilities',
            array(
                'classes' => $this->get_all_utility_classes(),
                'categories' => $this->get_utility_categories(),
                'apiUrl' => rest_url('ds-studio/v1/class-suggestions'),
                'nonce' => wp_create_nonce('wp_rest')
            )
        );
    }
    
    /**
     * Inject utility class data into GenerateBlocks editor
     */
    public function inject_utility_class_data($data) {
        $data['dsStudioUtilities'] = array(
            'classes' => $this->get_all_utility_classes(),
            'categories' => $this->get_utility_categories(),
            'suggestions' => $this->get_class_suggestions()
        );
        
        return $data;
    }
    
    /**
     * Register REST API endpoint for dynamic class suggestions
     */
    public function register_class_suggestion_endpoint() {
        register_rest_route('ds-studio/v1', '/class-suggestions', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_class_suggestions_api'),
            'permission_callback' => function() {
                return current_user_can('edit_posts');
            },
            'args' => array(
                'query' => array(
                    'required' => false,
                    'type' => 'string',
                    'description' => 'Search query for class suggestions'
                ),
                'category' => array(
                    'required' => false,
                    'type' => 'string',
                    'description' => 'Filter by utility category'
                )
            )
        ));
    }
    
    /**
     * API callback for class suggestions
     */
    public function get_class_suggestions_api($request) {
        $query = $request->get_param('query');
        $category = $request->get_param('category');
        
        $all_classes = $this->get_all_utility_classes();
        $suggestions = array();
        
        foreach ($all_classes as $class_data) {
            // Filter by category if specified
            if ($category && $class_data['category'] !== $category) {
                continue;
            }
            
            // Filter by query if specified
            if ($query && stripos($class_data['class'], $query) === false && 
                stripos($class_data['description'], $query) === false) {
                continue;
            }
            
            $suggestions[] = $class_data;
        }
        
        return rest_ensure_response($suggestions);
    }
    
    /**
     * Get all utility classes with metadata
     */
    private function get_all_utility_classes() {
        $theme_json = $this->get_theme_json_data();
        $classes = array();
        
        // Color utilities
        if (isset($theme_json['settings']['color']['palette'])) {
            foreach ($theme_json['settings']['color']['palette'] as $color) {
                $slug = $color['slug'];
                $name = $color['name'];
                
                $classes[] = array(
                    'class' => "text-{$slug}",
                    'description' => "Text color: {$name}",
                    'category' => 'colors',
                    'value' => $color['color'],
                    'type' => 'text-color'
                );
                
                $classes[] = array(
                    'class' => "bg-{$slug}",
                    'description' => "Background color: {$name}",
                    'category' => 'colors',
                    'value' => $color['color'],
                    'type' => 'background-color'
                );
                
                $classes[] = array(
                    'class' => "border-{$slug}",
                    'description' => "Border color: {$name}",
                    'category' => 'colors',
                    'value' => $color['color'],
                    'type' => 'border-color'
                );
            }
        }
        
        // Font size utilities
        if (isset($theme_json['settings']['typography']['fontSizes'])) {
            foreach ($theme_json['settings']['typography']['fontSizes'] as $size) {
                $slug = $size['slug'];
                $name = $size['name'];
                
                $classes[] = array(
                    'class' => "text-{$slug}",
                    'description' => "Font size: {$name} ({$size['size']})",
                    'category' => 'typography',
                    'value' => $size['size'],
                    'type' => 'font-size'
                );
            }
        }
        
        // Font family utilities
        if (isset($theme_json['settings']['typography']['fontFamilies'])) {
            foreach ($theme_json['settings']['typography']['fontFamilies'] as $font) {
                $slug = $font['slug'];
                $name = $font['name'];
                
                $classes[] = array(
                    'class' => "font-{$slug}",
                    'description' => "Font family: {$name}",
                    'category' => 'typography',
                    'value' => $font['fontFamily'],
                    'type' => 'font-family'
                );
            }
        }
        
        // Spacing utilities
        if (isset($theme_json['settings']['spacing']['spacingSizes'])) {
            foreach ($theme_json['settings']['spacing']['spacingSizes'] as $spacing) {
                $slug = $spacing['slug'];
                $name = $spacing['name'];
                $size = $spacing['size'];
                
                // Padding utilities
                $classes[] = array(
                    'class' => "p-{$slug}",
                    'description' => "Padding: {$name} ({$size})",
                    'category' => 'spacing',
                    'value' => $size,
                    'type' => 'padding'
                );
                
                $classes[] = array(
                    'class' => "pt-{$slug}",
                    'description' => "Padding top: {$name} ({$size})",
                    'category' => 'spacing',
                    'value' => $size,
                    'type' => 'padding-top'
                );
                
                $classes[] = array(
                    'class' => "pr-{$slug}",
                    'description' => "Padding right: {$name} ({$size})",
                    'category' => 'spacing',
                    'value' => $size,
                    'type' => 'padding-right'
                );
                
                $classes[] = array(
                    'class' => "pb-{$slug}",
                    'description' => "Padding bottom: {$name} ({$size})",
                    'category' => 'spacing',
                    'value' => $size,
                    'type' => 'padding-bottom'
                );
                
                $classes[] = array(
                    'class' => "pl-{$slug}",
                    'description' => "Padding left: {$name} ({$size})",
                    'category' => 'spacing',
                    'value' => $size,
                    'type' => 'padding-left'
                );
                
                $classes[] = array(
                    'class' => "px-{$slug}",
                    'description' => "Padding horizontal: {$name} ({$size})",
                    'category' => 'spacing',
                    'value' => $size,
                    'type' => 'padding-horizontal'
                );
                
                $classes[] = array(
                    'class' => "py-{$slug}",
                    'description' => "Padding vertical: {$name} ({$size})",
                    'category' => 'spacing',
                    'value' => $size,
                    'type' => 'padding-vertical'
                );
                
                // Margin utilities
                $classes[] = array(
                    'class' => "m-{$slug}",
                    'description' => "Margin: {$name} ({$size})",
                    'category' => 'spacing',
                    'value' => $size,
                    'type' => 'margin'
                );
                
                $classes[] = array(
                    'class' => "mt-{$slug}",
                    'description' => "Margin top: {$name} ({$size})",
                    'category' => 'spacing',
                    'value' => $size,
                    'type' => 'margin-top'
                );
                
                $classes[] = array(
                    'class' => "mr-{$slug}",
                    'description' => "Margin right: {$name} ({$size})",
                    'category' => 'spacing',
                    'value' => $size,
                    'type' => 'margin-right'
                );
                
                $classes[] = array(
                    'class' => "mb-{$slug}",
                    'description' => "Margin bottom: {$name} ({$size})",
                    'category' => 'spacing',
                    'value' => $size,
                    'type' => 'margin-bottom'
                );
                
                $classes[] = array(
                    'class' => "ml-{$slug}",
                    'description' => "Margin left: {$name} ({$size})",
                    'category' => 'spacing',
                    'value' => $size,
                    'type' => 'margin-left'
                );
                
                $classes[] = array(
                    'class' => "mx-{$slug}",
                    'description' => "Margin horizontal: {$name} ({$size})",
                    'category' => 'spacing',
                    'value' => $size,
                    'type' => 'margin-horizontal'
                );
                
                $classes[] = array(
                    'class' => "my-{$slug}",
                    'description' => "Margin vertical: {$name} ({$size})",
                    'category' => 'spacing',
                    'value' => $size,
                    'type' => 'margin-vertical'
                );
                
                // Gap utilities
                $classes[] = array(
                    'class' => "gap-{$slug}",
                    'description' => "Gap: {$name} ({$size})",
                    'category' => 'spacing',
                    'value' => $size,
                    'type' => 'gap'
                );
            }
        }
        
        // Border radius utilities
        if (isset($theme_json['settings']['border']['radius'])) {
            foreach ($theme_json['settings']['border']['radius'] as $radius) {
                $slug = $radius['slug'];
                $name = $radius['name'];
                $size = $radius['size'];
                
                $classes[] = array(
                    'class' => "rounded-{$slug}",
                    'description' => "Border radius: {$name} ({$size})",
                    'category' => 'borders',
                    'value' => $size,
                    'type' => 'border-radius'
                );
                
                $classes[] = array(
                    'class' => "rounded-t-{$slug}",
                    'description' => "Border radius top: {$name} ({$size})",
                    'category' => 'borders',
                    'value' => $size,
                    'type' => 'border-radius-top'
                );
                
                $classes[] = array(
                    'class' => "rounded-r-{$slug}",
                    'description' => "Border radius right: {$name} ({$size})",
                    'category' => 'borders',
                    'value' => $size,
                    'type' => 'border-radius-right'
                );
                
                $classes[] = array(
                    'class' => "rounded-b-{$slug}",
                    'description' => "Border radius bottom: {$name} ({$size})",
                    'category' => 'borders',
                    'value' => $size,
                    'type' => 'border-radius-bottom'
                );
                
                $classes[] = array(
                    'class' => "rounded-l-{$slug}",
                    'description' => "Border radius left: {$name} ({$size})",
                    'category' => 'borders',
                    'value' => $size,
                    'type' => 'border-radius-left'
                );
            }
        }
        
        // Border width utilities
        if (isset($theme_json['settings']['border']['widths'])) {
            foreach ($theme_json['settings']['border']['widths'] as $width) {
                $slug = $width['slug'];
                $name = $width['name'];
                $size = $width['size'];
                
                $classes[] = array(
                    'class' => "border-{$slug}",
                    'description' => "Border width: {$name} ({$size})",
                    'category' => 'borders',
                    'value' => $size,
                    'type' => 'border-width'
                );
                
                $classes[] = array(
                    'class' => "border-t-{$slug}",
                    'description' => "Border top width: {$name} ({$size})",
                    'category' => 'borders',
                    'value' => $size,
                    'type' => 'border-top-width'
                );
                
                $classes[] = array(
                    'class' => "border-r-{$slug}",
                    'description' => "Border right width: {$name} ({$size})",
                    'category' => 'borders',
                    'value' => $size,
                    'type' => 'border-right-width'
                );
                
                $classes[] = array(
                    'class' => "border-b-{$slug}",
                    'description' => "Border bottom width: {$name} ({$size})",
                    'category' => 'borders',
                    'value' => $size,
                    'type' => 'border-bottom-width'
                );
                
                $classes[] = array(
                    'class' => "border-l-{$slug}",
                    'description' => "Border left width: {$name} ({$size})",
                    'category' => 'borders',
                    'value' => $size,
                    'type' => 'border-left-width'
                );
            }
        }
        
        // Border style utilities
        if (isset($theme_json['settings']['border']['styles'])) {
            foreach ($theme_json['settings']['border']['styles'] as $style) {
                $slug = $style['slug'];
                $name = $style['name'];
                
                $classes[] = array(
                    'class' => "border-{$slug}",
                    'description' => "Border style: {$name}",
                    'category' => 'borders',
                    'value' => $slug,
                    'type' => 'border-style'
                );
            }
        }
        
        // Shadow utilities
        if (isset($theme_json['settings']['custom']['shadows'])) {
            foreach ($theme_json['settings']['custom']['shadows'] as $slug => $shadow) {
                $classes[] = array(
                    'class' => "shadow-{$slug}",
                    'description' => "Box shadow: {$slug}",
                    'category' => 'effects',
                    'value' => $shadow,
                    'type' => 'box-shadow'
                );
            }
        }
        
        // Container utilities
        if (isset($theme_json['settings']['custom']['layout']['containers'])) {
            foreach ($theme_json['settings']['custom']['layout']['containers'] as $slug => $width) {
                $classes[] = array(
                    'class' => "max-w-{$slug}",
                    'description' => "Max width: {$slug} ({$width})",
                    'category' => 'layout',
                    'value' => $width,
                    'type' => 'max-width'
                );
            }
        }
        
        // Grid utilities
        if (isset($theme_json['settings']['custom']['layout']['grid'])) {
            foreach ($theme_json['settings']['custom']['layout']['grid'] as $slug => $value) {
                $classes[] = array(
                    'class' => "grid-{$slug}",
                    'description' => "Grid template columns: {$slug}",
                    'category' => 'layout',
                    'value' => $value,
                    'type' => 'grid-template-columns'
                );
            }
        }
        
        // Aspect ratio utilities
        if (isset($theme_json['settings']['custom']['layout']['aspectRatios'])) {
            foreach ($theme_json['settings']['custom']['layout']['aspectRatios'] as $slug => $ratio) {
                $classes[] = array(
                    'class' => "aspect-{$slug}",
                    'description' => "Aspect ratio: {$slug} ({$ratio})",
                    'category' => 'layout',
                    'value' => $ratio,
                    'type' => 'aspect-ratio'
                );
            }
        }
        
        // Z-index utilities
        if (isset($theme_json['settings']['custom']['layout']['zIndex'])) {
            foreach ($theme_json['settings']['custom']['layout']['zIndex'] as $slug => $value) {
                $classes[] = array(
                    'class' => "z-{$slug}",
                    'description' => "Z-index: {$slug} ({$value})",
                    'category' => 'layout',
                    'value' => $value,
                    'type' => 'z-index'
                );
            }
        }
        
        // Animation duration utilities
        if (isset($theme_json['settings']['custom']['animations']['durations'])) {
            foreach ($theme_json['settings']['custom']['animations']['durations'] as $slug => $duration) {
                $classes[] = array(
                    'class' => "duration-{$slug}",
                    'description' => "Animation duration: {$slug} ({$duration})",
                    'category' => 'effects',
                    'value' => $duration,
                    'type' => 'animation-duration'
                );
            }
        }
        
        // Animation easing utilities
        if (isset($theme_json['settings']['custom']['animations']['easings'])) {
            foreach ($theme_json['settings']['custom']['animations']['easings'] as $slug => $easing) {
                $classes[] = array(
                    'class' => "ease-{$slug}",
                    'description' => "Animation timing: {$slug}",
                    'category' => 'effects',
                    'value' => $easing,
                    'type' => 'animation-timing-function'
                );
            }
        }
        
        // Common layout utilities
        $layout_utilities = array(
            // Display
            array('class' => 'block', 'description' => 'Display: block', 'category' => 'layout', 'type' => 'display'),
            array('class' => 'inline-block', 'description' => 'Display: inline-block', 'category' => 'layout', 'type' => 'display'),
            array('class' => 'inline', 'description' => 'Display: inline', 'category' => 'layout', 'type' => 'display'),
            array('class' => 'flex', 'description' => 'Display: flex', 'category' => 'layout', 'type' => 'display'),
            array('class' => 'inline-flex', 'description' => 'Display: inline-flex', 'category' => 'layout', 'type' => 'display'),
            array('class' => 'grid', 'description' => 'Display: grid', 'category' => 'layout', 'type' => 'display'),
            array('class' => 'inline-grid', 'description' => 'Display: inline-grid', 'category' => 'layout', 'type' => 'display'),
            array('class' => 'hidden', 'description' => 'Display: none', 'category' => 'layout', 'type' => 'display'),
            
            // Flexbox
            array('class' => 'flex-row', 'description' => 'Flex direction: row', 'category' => 'layout', 'type' => 'flex-direction'),
            array('class' => 'flex-col', 'description' => 'Flex direction: column', 'category' => 'layout', 'type' => 'flex-direction'),
            array('class' => 'flex-wrap', 'description' => 'Flex wrap: wrap', 'category' => 'layout', 'type' => 'flex-wrap'),
            array('class' => 'flex-nowrap', 'description' => 'Flex wrap: nowrap', 'category' => 'layout', 'type' => 'flex-wrap'),
            array('class' => 'justify-start', 'description' => 'Justify content: flex-start', 'category' => 'layout', 'type' => 'justify-content'),
            array('class' => 'justify-center', 'description' => 'Justify content: center', 'category' => 'layout', 'type' => 'justify-content'),
            array('class' => 'justify-end', 'description' => 'Justify content: flex-end', 'category' => 'layout', 'type' => 'justify-content'),
            array('class' => 'justify-between', 'description' => 'Justify content: space-between', 'category' => 'layout', 'type' => 'justify-content'),
            array('class' => 'justify-around', 'description' => 'Justify content: space-around', 'category' => 'layout', 'type' => 'justify-content'),
            array('class' => 'items-start', 'description' => 'Align items: flex-start', 'category' => 'layout', 'type' => 'align-items'),
            array('class' => 'items-center', 'description' => 'Align items: center', 'category' => 'layout', 'type' => 'align-items'),
            array('class' => 'items-end', 'description' => 'Align items: flex-end', 'category' => 'layout', 'type' => 'align-items'),
            array('class' => 'items-stretch', 'description' => 'Align items: stretch', 'category' => 'layout', 'type' => 'align-items'),
            
            // Text alignment
            array('class' => 'text-left', 'description' => 'Text align: left', 'category' => 'typography', 'type' => 'text-align'),
            array('class' => 'text-center', 'description' => 'Text align: center', 'category' => 'typography', 'type' => 'text-align'),
            array('class' => 'text-right', 'description' => 'Text align: right', 'category' => 'typography', 'type' => 'text-align'),
            array('class' => 'text-justify', 'description' => 'Text align: justify', 'category' => 'typography', 'type' => 'text-align'),
            
            // Font weights
            array('class' => 'font-thin', 'description' => 'Font weight: 100', 'category' => 'typography', 'type' => 'font-weight'),
            array('class' => 'font-light', 'description' => 'Font weight: 300', 'category' => 'typography', 'type' => 'font-weight'),
            array('class' => 'font-normal', 'description' => 'Font weight: 400', 'category' => 'typography', 'type' => 'font-weight'),
            array('class' => 'font-medium', 'description' => 'Font weight: 500', 'category' => 'typography', 'type' => 'font-weight'),
            array('class' => 'font-semibold', 'description' => 'Font weight: 600', 'category' => 'typography', 'type' => 'font-weight'),
            array('class' => 'font-bold', 'description' => 'Font weight: 700', 'category' => 'typography', 'type' => 'font-weight'),
            array('class' => 'font-extrabold', 'description' => 'Font weight: 800', 'category' => 'typography', 'type' => 'font-weight'),
            array('class' => 'font-black', 'description' => 'Font weight: 900', 'category' => 'typography', 'type' => 'font-weight'),
        );
        
        $classes = array_merge($classes, $layout_utilities);
        
        return $classes;
    }
    
    /**
     * Get utility class categories
     */
    private function get_utility_categories() {
        return array(
            'colors' => array(
                'label' => 'Colors',
                'description' => 'Text, background, and border colors from theme.json'
            ),
            'typography' => array(
                'label' => 'Typography',
                'description' => 'Font sizes, families, weights, and text alignment from theme.json'
            ),
            'spacing' => array(
                'label' => 'Spacing',
                'description' => 'Padding, margin, and gap utilities from theme.json'
            ),
            'borders' => array(
                'label' => 'Borders',
                'description' => 'Border radius, width, and style utilities from theme.json'
            ),
            'layout' => array(
                'label' => 'Layout',
                'description' => 'Flexbox, grid, display, and positioning utilities'
            ),
            'effects' => array(
                'label' => 'Effects',
                'description' => 'Shadows, animations, and visual effects from theme.json'
            )
        );
    }
    
    /**
     * Get smart class suggestions based on context
     */
    private function get_class_suggestions() {
        return array(
            'button' => array('bg-primary', 'text-white', 'px-lg', 'py-md', 'rounded-base'),
            'card' => array('bg-white', 'p-lg', 'rounded-lg', 'border-neutral-200'),
            'container' => array('max-w-screen-lg', 'mx-auto', 'px-md'),
            'hero' => array('text-center', 'py-10xl', 'bg-primary', 'text-white'),
            'grid' => array('grid', 'grid-cols-1', 'md:grid-cols-2', 'lg:grid-cols-3', 'gap-lg')
        );
    }
    
    /**
     * Get theme.json data
     */
    private function get_theme_json_data() {
        static $theme_json_data = null;
        
        if ($theme_json_data === null) {
            $theme_json_file = get_stylesheet_directory() . '/theme.json';
            
            if (file_exists($theme_json_file)) {
                $theme_json_content = file_get_contents($theme_json_file);
                $theme_json_data = json_decode($theme_json_content, true);
            } else {
                $theme_json_data = array();
            }
        }
        
        return $theme_json_data;
    }
}

// Initialize the utility class injector
new DS_Studio_Utility_Class_Injector();
