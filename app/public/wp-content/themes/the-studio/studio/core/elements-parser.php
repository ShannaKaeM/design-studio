<?php
/**
 * Custom Elements Parser
 * 
 * Parse and render custom HTML elements with CSS variable integration
 * 
 * @package TheStudio
 */

namespace Studio\Core;

class ElementsParser {
    
    /**
     * Instance
     */
    private static $instance = null;
    
    /**
     * Registered elements
     */
    private $elements = [];
    
    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize
     */
    private function init() {
        // Register default elements
        $this->register_default_elements();
        
        // Hook into content filters
        add_filter('the_content', [$this, 'parse_content'], 20);
        add_filter('widget_text', [$this, 'parse_content'], 20);
        
        // Add shortcode support
        add_shortcode('studio', [$this, 'studio_shortcode']);
        
        // Enqueue parser styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
    }
    
    /**
     * Register default elements
     */
    private function register_default_elements() {
        // Button element
        $this->register_element('button', [
            'tag' => 'button',
            'classes' => 'studio-button',
            'variables' => [
                '--ts-color-primary',
                '--ts-color-text',
                '--ts-spacing-sm',
                '--ts-spacing-md',
                '--ts-radius-md',
                '--ts-font-medium'
            ],
            'attributes' => [
                'variant' => 'primary', // primary, secondary, outline
                'size' => 'md', // sm, md, lg
                'full' => false
            ]
        ]);
        
        // Card element
        $this->register_element('card', [
            'tag' => 'div',
            'classes' => 'studio-card',
            'variables' => [
                '--ts-spacing-md',
                '--ts-spacing-lg',
                '--ts-radius-lg',
                '--ts-shadow-md',
                '--ts-surface-soft'
            ],
            'slots' => ['header', 'content', 'footer']
        ]);
        
        // Grid element
        $this->register_element('grid', [
            'tag' => 'div',
            'classes' => 'studio-grid',
            'variables' => [
                '--ts-spacing-md',
                '--ts-spacing-lg',
                '--ts-container-width'
            ],
            'attributes' => [
                'cols' => '3', // 1-12
                'gap' => 'md', // sm, md, lg
                'responsive' => true
            ]
        ]);
        
        // Section element
        $this->register_element('section', [
            'tag' => 'section',
            'classes' => 'studio-section',
            'variables' => [
                '--ts-spacing-xl',
                '--ts-spacing-2xl',
                '--ts-container-width'
            ],
            'attributes' => [
                'background' => 'default', // default, soft, accent
                'spacing' => 'lg', // sm, md, lg, xl
                'contained' => true
            ]
        ]);
        
        // Text element
        $this->register_element('text', [
            'tag' => 'p',
            'classes' => 'studio-text',
            'variables' => [
                '--ts-text-base',
                '--ts-line-relaxed',
                '--ts-color-text'
            ],
            'attributes' => [
                'size' => 'base', // sm, base, lg, xl
                'weight' => 'normal', // normal, medium, bold
                'align' => 'left' // left, center, right
            ]
        ]);
    }
    
    /**
     * Register custom element
     */
    public function register_element($name, $config) {
        $this->elements[$name] = wp_parse_args($config, [
            'tag' => 'div',
            'classes' => '',
            'variables' => [],
            'attributes' => [],
            'slots' => [],
            'render_callback' => null
        ]);
    }
    
    /**
     * Parse content for custom elements
     */
    public function parse_content($content) {
        // Parse studio elements syntax: <studio:element>
        $pattern = '/<studio:([a-z]+)([^>]*)>(.*?)<\/studio:\1>/is';
        
        return preg_replace_callback($pattern, [$this, 'render_element'], $content);
    }
    
    /**
     * Render custom element
     */
    private function render_element($matches) {
        $element_name = $matches[1];
        $attributes_string = $matches[2];
        $content = $matches[3];
        
        if (!isset($this->elements[$element_name])) {
            return $matches[0]; // Return unchanged if element not registered
        }
        
        $element = $this->elements[$element_name];
        $attributes = $this->parse_attributes($attributes_string);
        
        // Merge with default attributes
        $attributes = wp_parse_args($attributes, $element['attributes']);
        
        // Build classes
        $classes = [$element['classes']];
        
        // Add variant classes
        if (isset($attributes['variant'])) {
            $classes[] = $element['classes'] . '--' . $attributes['variant'];
        }
        
        if (isset($attributes['size'])) {
            $classes[] = $element['classes'] . '--' . $attributes['size'];
        }
        
        if (!empty($attributes['class'])) {
            $classes[] = $attributes['class'];
        }
        
        // Build inline styles for variables
        $styles = [];
        foreach ($element['variables'] as $var) {
            $styles[] = "{$var}: var({$var})";
        }
        
        // Custom render callback
        if (is_callable($element['render_callback'])) {
            return call_user_func($element['render_callback'], $attributes, $content, $element);
        }
        
        // Default rendering
        $tag = $element['tag'];
        $class_string = implode(' ', $classes);
        $style_string = implode('; ', $styles);
        
        $html = "<{$tag} class=\"{$class_string}\" style=\"{$style_string}\"";
        
        // Add data attributes
        foreach ($attributes as $key => $value) {
            if (strpos($key, 'data-') === 0) {
                $html .= " {$key}=\"" . esc_attr($value) . "\"";
            }
        }
        
        $html .= ">";
        
        // Parse slots if any
        if (!empty($element['slots'])) {
            $html .= $this->parse_slots($content, $element['slots']);
        } else {
            $html .= $content;
        }
        
        $html .= "</{$tag}>";
        
        return $html;
    }
    
    /**
     * Parse attributes from string
     */
    private function parse_attributes($attr_string) {
        $attributes = [];
        
        if (preg_match_all('/(\w+)=["\']([^"\']+)["\']/', $attr_string, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $attributes[$match[1]] = $match[2];
            }
        }
        
        return $attributes;
    }
    
    /**
     * Parse slots in content
     */
    private function parse_slots($content, $slots) {
        $parsed = '';
        
        foreach ($slots as $slot) {
            $pattern = '/<slot:' . $slot . '>(.*?)<\/slot:' . $slot . '>/is';
            if (preg_match($pattern, $content, $match)) {
                $parsed .= '<div class="studio-slot studio-slot--' . $slot . '">' . $match[1] . '</div>';
            }
        }
        
        // Add any remaining content not in slots
        $remaining = preg_replace('/<slot:[^>]+>.*?<\/slot:[^>]+>/is', '', $content);
        if (trim($remaining)) {
            $parsed .= '<div class="studio-slot studio-slot--default">' . $remaining . '</div>';
        }
        
        return $parsed;
    }
    
    /**
     * Studio shortcode handler
     */
    public function studio_shortcode($atts, $content = null) {
        $atts = shortcode_atts([
            'element' => 'div',
            'class' => '',
            'variant' => '',
            'size' => ''
        ], $atts);
        
        $element_name = $atts['element'];
        unset($atts['element']);
        
        if (!isset($this->elements[$element_name])) {
            return '';
        }
        
        // Build attributes string
        $attr_string = '';
        foreach ($atts as $key => $value) {
            if (!empty($value)) {
                $attr_string .= ' ' . $key . '="' . esc_attr($value) . '"';
            }
        }
        
        // Render using the element parser
        $studio_element = "<studio:{$element_name}{$attr_string}>{$content}</studio:{$element_name}>";
        
        return $this->parse_content($studio_element);
    }
    
    /**
     * Enqueue parser styles
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'studio-elements',
            STUDIO_URL . '/studio/css/studio-elements.css',
            ['studio-vars'],
            STUDIO_VERSION
        );
    }
    
    /**
     * Get registered elements
     */
    public function get_elements() {
        return $this->elements;
    }
}

// Initialize
add_action('init', function() {
    ElementsParser::get_instance();
}, 25);