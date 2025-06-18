<?php
/**
 * Studio Utility Generator
 * 
 * Generates utility classes from CSS variables
 * Based on Daniel's innovative variable-driven system
 * 
 * @package TheStudio
 */

namespace Studio\Core;

class UtilityGenerator {
    
    /**
     * Variable scanner instance
     */
    private $scanner;
    
    /**
     * Generated utilities
     */
    private $utilities = [];
    
    /**
     * Utility patterns
     */
    private $patterns = [
        // Colors
        'color' => [
            'text' => 'color: var(%s);',
            'bg' => 'background-color: var(%s);',
            'border' => 'border-color: var(%s);'
        ],
        
        // Typography
        'typography' => [
            'text' => 'font-size: var(%s);',
            'font' => 'font-family: var(%s);',
            'font-weight' => 'font-weight: var(%s);',
            'leading' => 'line-height: var(%s);'
        ],
        
        // Spacing
        'spacing' => [
            'p' => 'padding: var(%s);',
            'pt' => 'padding-top: var(%s);',
            'pr' => 'padding-right: var(%s);',
            'pb' => 'padding-bottom: var(%s);',
            'pl' => 'padding-left: var(%s);',
            'px' => 'padding-left: var(%s); padding-right: var(%s);',
            'py' => 'padding-top: var(%s); padding-bottom: var(%s);',
            'm' => 'margin: var(%s);',
            'mt' => 'margin-top: var(%s);',
            'mr' => 'margin-right: var(%s);',
            'mb' => 'margin-bottom: var(%s);',
            'ml' => 'margin-left: var(%s);',
            'mx' => 'margin-left: var(%s); margin-right: var(%s);',
            'my' => 'margin-top: var(%s); margin-bottom: var(%s);',
            'gap' => 'gap: var(%s);'
        ],
        
        // Borders & Radius
        'borders' => [
            'border' => 'border-width: var(%s);',
            'border-t' => 'border-top-width: var(%s);',
            'border-r' => 'border-right-width: var(%s);',
            'border-b' => 'border-bottom-width: var(%s);',
            'border-l' => 'border-left-width: var(%s);',
            'rounded' => 'border-radius: var(%s);',
            'rounded-t' => 'border-top-left-radius: var(%s); border-top-right-radius: var(%s);',
            'rounded-r' => 'border-top-right-radius: var(%s); border-bottom-right-radius: var(%s);',
            'rounded-b' => 'border-bottom-left-radius: var(%s); border-bottom-right-radius: var(%s);',
            'rounded-l' => 'border-top-left-radius: var(%s); border-bottom-left-radius: var(%s);'
        ],
        
        // Shadows
        'shadows' => [
            'shadow' => 'box-shadow: var(%s);'
        ],
        
        // Layout
        'layout' => [
            'container' => 'max-width: var(%s);',
            'w' => 'width: var(%s);',
            'max-w' => 'max-width: var(%s);',
            'min-w' => 'min-width: var(%s);',
            'h' => 'height: var(%s);',
            'max-h' => 'max-height: var(%s);',
            'min-h' => 'min-height: var(%s);'
        ]
    ];
    
    /**
     * Constructor
     */
    public function __construct(VariableScanner $scanner) {
        $this->scanner = $scanner;
    }
    
    /**
     * Generate utilities from variables
     */
    public function generate() {
        $variables = $this->scanner->get_variables_by_category();
        
        foreach ($variables as $variable) {
            if (!$variable['control']) {
                continue; // Skip variables without controls
            }
            
            $this->generate_utilities_for_variable($variable);
        }
        
        return $this->utilities;
    }
    
    /**
     * Generate utilities for a single variable
     */
    private function generate_utilities_for_variable($variable) {
        $category = $variable['category'];
        $name = $variable['name'];
        $property = $variable['property'];
        
        // Get patterns for this category
        if (!isset($this->patterns[$category])) {
            return;
        }
        
        $patterns = $this->patterns[$category];
        
        // Generate utility classes based on property name
        foreach ($patterns as $prefix => $css_template) {
            $utility_name = $this->generate_utility_name($prefix, $property);
            
            if ($utility_name) {
                // Count the number of %s placeholders
                $placeholder_count = substr_count($css_template, '%s');
                
                // Generate the appropriate number of arguments
                if ($placeholder_count > 1) {
                    $args = array_fill(0, $placeholder_count, $name);
                    $css = vsprintf($css_template, $args);
                } else {
                    $css = sprintf($css_template, $name);
                }
                
                $this->utilities[$utility_name] = [
                    'class' => '.' . $utility_name,
                    'css' => $css,
                    'variable' => $name,
                    'category' => $category
                ];
            }
        }
    }
    
    /**
     * Generate utility class name
     */
    private function generate_utility_name($prefix, $property) {
        // Extract meaningful part of property name
        $suffix = str_replace('ts-', '', $property);
        
        // Special handling for different property types
        if (strpos($property, 'color-') !== false) {
            // Color utilities: text-primary, bg-secondary, etc.
            $color_name = str_replace('color-', '', $suffix);
            return $prefix . '-' . $color_name;
        }
        
        if (strpos($property, 'text-') !== false && $prefix === 'text') {
            // Font size utilities: text-sm, text-lg, etc.
            $size_name = str_replace('text-', '', $suffix);
            return $prefix . '-' . $size_name;
        }
        
        if (strpos($property, 'font-') !== false && $prefix === 'font') {
            // Font family utilities: font-sans, font-serif, etc.
            $font_name = str_replace('font-', '', $suffix);
            return $prefix . '-' . $font_name;
        }
        
        if (strpos($property, 'font-') !== false && $prefix === 'font-weight') {
            // Font weight utilities: font-bold, font-normal, etc.
            $weight_name = str_replace('font-', '', $suffix);
            return 'font-' . $weight_name;
        }
        
        if (strpos($property, 'leading-') !== false) {
            // Line height utilities: leading-tight, leading-loose, etc.
            $leading_name = str_replace('leading-', '', $suffix);
            return 'leading-' . $leading_name;
        }
        
        if (strpos($property, 'spacing-') !== false) {
            // Spacing utilities: p-sm, m-lg, etc.
            $size_name = str_replace('spacing-', '', $suffix);
            return $prefix . '-' . $size_name;
        }
        
        if (strpos($property, 'border-') !== false && strpos($prefix, 'border') !== false) {
            // Border utilities: border-thin, border-thick, etc.
            $border_name = str_replace('border-', '', $suffix);
            return $prefix . '-' . $border_name;
        }
        
        if (strpos($property, 'radius-') !== false) {
            // Radius utilities: rounded-sm, rounded-lg, etc.
            $radius_name = str_replace('radius-', '', $suffix);
            return $prefix . '-' . $radius_name;
        }
        
        if (strpos($property, 'shadow-') !== false) {
            // Shadow utilities: shadow-sm, shadow-lg, etc.
            $shadow_name = str_replace('shadow-', '', $suffix);
            return $prefix . '-' . $shadow_name;
        }
        
        if (strpos($property, 'container-') !== false) {
            // Container utilities: container-sm, container-lg, etc.
            $container_name = str_replace('container-', '', $suffix);
            return 'container-' . $container_name;
        }
        
        return null;
    }
    
    /**
     * Write utilities to CSS file
     */
    public function write_css($file_path) {
        $css = "/**\n";
        $css .= " * Studio Utilities - Auto-generated\n";
        $css .= " * Generated from CSS variables with @control annotations\n";
        $css .= " * Generated at: " . date('Y-m-d H:i:s') . "\n";
        $css .= " */\n\n";
        
        // Group utilities by category
        $by_category = [];
        foreach ($this->utilities as $name => $utility) {
            $category = $utility['category'];
            if (!isset($by_category[$category])) {
                $by_category[$category] = [];
            }
            $by_category[$category][$name] = $utility;
        }
        
        // Write utilities by category
        foreach ($by_category as $category => $utilities) {
            $css .= "/* ==========================================================================\n";
            $css .= "   " . ucfirst($category) . " Utilities\n";
            $css .= "   ========================================================================== */\n\n";
            
            foreach ($utilities as $name => $utility) {
                $css .= $utility['class'] . " {\n";
                $css .= "    " . $utility['css'] . "\n";
                $css .= "}\n\n";
            }
        }
        
        // Add responsive utilities
        $css .= $this->generate_responsive_utilities();
        
        return file_put_contents($file_path, $css);
    }
    
    /**
     * Generate responsive utilities
     */
    private function generate_responsive_utilities() {
        $css = "/* ==========================================================================\n";
        $css .= "   Responsive Utilities\n";
        $css .= "   ========================================================================== */\n\n";
        
        $breakpoints = [
            'sm' => '640px',
            'md' => '768px',
            'lg' => '1024px',
            'xl' => '1280px',
            '2xl' => '1536px'
        ];
        
        foreach ($breakpoints as $prefix => $min_width) {
            $css .= "@media (min-width: {$min_width}) {\n";
            
            // Generate responsive versions of key utilities
            foreach ($this->utilities as $name => $utility) {
                // Only generate responsive versions for layout and spacing utilities
                if (in_array($utility['category'], ['spacing', 'layout'])) {
                    $responsive_class = ".{$prefix}\\:{$name}";
                    $css .= "    {$responsive_class} {\n";
                    $css .= "        " . $utility['css'] . "\n";
                    $css .= "    }\n";
                }
            }
            
            $css .= "}\n\n";
        }
        
        return $css;
    }
    
    /**
     * Get generated utilities
     */
    public function get_utilities() {
        return $this->utilities;
    }
}