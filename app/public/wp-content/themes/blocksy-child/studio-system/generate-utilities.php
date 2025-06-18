<?php
/**
 * Studio Utility Class Generator
 * Automatically generates utility classes from CSS variables
 */

namespace Studio;

class UtilityGenerator {
    
    private $css_file;
    private $output_file;
    private $variables = [];
    
    public function __construct() {
        $this->css_file = get_stylesheet_directory() . '/assets/css/studio-vars.css';
        $this->output_file = get_stylesheet_directory() . '/assets/css/studio-utilities.css';
    }
    
    /**
     * Generate utilities from CSS variables
     */
    public function generate() {
        // Parse CSS variables
        $this->parse_variables();
        
        // Generate utility classes
        $utilities = $this->build_utilities();
        
        // Write to file
        $this->write_utilities($utilities);
        
        return true;
    }
    
    /**
     * Parse CSS variables from file
     */
    private function parse_variables() {
        if (!file_exists($this->css_file)) {
            return;
        }
        
        $content = file_get_contents($this->css_file);
        
        // Match all CSS variables with their values
        preg_match_all('/--st-([\w-]+):\s*([^;]+);/', $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $this->variables[$match[1]] = trim($match[2]);
        }
    }
    
    /**
     * Build utility classes based on variable names and patterns
     */
    private function build_utilities() {
        $utilities = "/**\n * Studio Auto-Generated Utility Classes\n * Generated: " . date('Y-m-d H:i:s') . "\n */\n\n";
        
        // Color utilities
        $utilities .= $this->generate_color_utilities();
        
        // Spacing utilities
        $utilities .= $this->generate_spacing_utilities();
        
        // Width utilities
        $utilities .= $this->generate_width_utilities();
        
        // Layout utilities
        $utilities .= $this->generate_layout_utilities();
        
        // Typography utilities
        $utilities .= $this->generate_typography_utilities();
        
        // Border utilities
        $utilities .= $this->generate_border_utilities();
        
        // Shadow utilities
        $utilities .= $this->generate_shadow_utilities();
        
        // Responsive utilities
        $utilities .= $this->generate_responsive_utilities();
        
        return $utilities;
    }
    
    /**
     * Generate color utilities
     */
    private function generate_color_utilities() {
        $output = "\n/* ===================================\n   Color Utilities\n   =================================== */\n\n";
        
        foreach ($this->variables as $name => $value) {
            // Check if it's a color variable
            if (preg_match('/^(primary|secondary|success|warning|danger|info|text|bg)/', $name)) {
                $class_name = str_replace(['color-', '-'], ['', '-'], $name);
                
                // Background colors
                $output .= ".bg-{$class_name} { background-color: var(--st-{$name}); }\n";
                
                // Text colors
                $output .= ".text-{$class_name} { color: var(--st-{$name}); }\n";
                
                // Border colors
                $output .= ".border-{$class_name} { border-color: var(--st-{$name}); }\n";
                
                // Hover variants
                $output .= ".hover\\:bg-{$class_name}:hover { background-color: var(--st-{$name}); }\n";
                $output .= ".hover\\:text-{$class_name}:hover { color: var(--st-{$name}); }\n";
                $output .= ".hover\\:border-{$class_name}:hover { border-color: var(--st-{$name}); }\n";
                
                $output .= "\n";
            }
        }
        
        return $output;
    }
    
    /**
     * Generate spacing utilities
     */
    private function generate_spacing_utilities() {
        $output = "\n/* ===================================\n   Spacing Utilities\n   =================================== */\n\n";
        
        $sides = [
            '' => '',
            't' => '-top',
            'r' => '-right',
            'b' => '-bottom',
            'l' => '-left',
            'x' => ['left', 'right'],
            'y' => ['top', 'bottom']
        ];
        
        foreach ($this->variables as $name => $value) {
            if (strpos($name, 'space-') === 0) {
                $size = str_replace('space-', '', $name);
                
                // Padding utilities
                foreach ($sides as $side => $css_side) {
                    if ($side === 'x') {
                        $output .= ".p{$side}-{$size} { padding-left: var(--st-{$name}); padding-right: var(--st-{$name}); }\n";
                    } elseif ($side === 'y') {
                        $output .= ".p{$side}-{$size} { padding-top: var(--st-{$name}); padding-bottom: var(--st-{$name}); }\n";
                    } else {
                        $output .= ".p{$side}-{$size} { padding{$css_side}: var(--st-{$name}); }\n";
                    }
                }
                
                // Margin utilities
                foreach ($sides as $side => $css_side) {
                    if ($side === 'x') {
                        $output .= ".m{$side}-{$size} { margin-left: var(--st-{$name}); margin-right: var(--st-{$name}); }\n";
                    } elseif ($side === 'y') {
                        $output .= ".m{$side}-{$size} { margin-top: var(--st-{$name}); margin-bottom: var(--st-{$name}); }\n";
                    } else {
                        $output .= ".m{$side}-{$size} { margin{$css_side}: var(--st-{$name}); }\n";
                    }
                }
                
                // Gap utilities (for flexbox/grid)
                $output .= ".gap-{$size} { gap: var(--st-{$name}); }\n";
                $output .= ".gap-x-{$size} { column-gap: var(--st-{$name}); }\n";
                $output .= ".gap-y-{$size} { row-gap: var(--st-{$name}); }\n";
                
                $output .= "\n";
            }
        }
        
        // Special margin auto utilities
        $output .= ".mx-auto { margin-left: auto; margin-right: auto; }\n";
        $output .= ".my-auto { margin-top: auto; margin-bottom: auto; }\n";
        $output .= ".m-auto { margin: auto; }\n";
        
        return $output;
    }
    
    /**
     * Generate width utilities
     */
    private function generate_width_utilities() {
        $output = "\n/* ===================================\n   Width Utilities\n   =================================== */\n\n";
        
        $output .= ".w-full { width: 100%; }\n";
        $output .= ".w-screen { width: 100vw; }\n";
        $output .= ".w-auto { width: auto; }\n";
        $output .= ".w-fit { width: fit-content; }\n";
        $output .= ".w-min { width: min-content; }\n";
        $output .= ".w-max { width: max-content; }\n";
        
        $output .= "\n/* Max Width Utilities */\n";
        $output .= ".max-w-none { max-width: none; }\n";
        $output .= ".max-w-full { max-width: 100%; }\n";
        $output .= ".max-w-screen { max-width: 100vw; }\n";
        $output .= ".max-w-xs { max-width: 20rem; }\n";
        $output .= ".max-w-sm { max-width: 24rem; }\n";
        $output .= ".max-w-md { max-width: 28rem; }\n";
        $output .= ".max-w-lg { max-width: 32rem; }\n";
        $output .= ".max-w-xl { max-width: 36rem; }\n";
        $output .= ".max-w-2xl { max-width: 42rem; }\n";
        $output .= ".max-w-3xl { max-width: 48rem; }\n";
        $output .= ".max-w-4xl { max-width: 56rem; }\n";
        $output .= ".max-w-5xl { max-width: 64rem; }\n";
        $output .= ".max-w-6xl { max-width: 72rem; }\n";
        $output .= ".max-w-7xl { max-width: 80rem; }\n";
        
        $output .= "\n/* Full Width Breakout Utilities */\n";
        $output .= ".full-width { width: 100vw; margin-left: calc(50% - 50vw); margin-right: calc(50% - 50vw); }\n";
        $output .= ".breakout { width: calc(100vw - 2rem); margin-left: calc(50% - 50vw + 1rem); margin-right: calc(50% - 50vw + 1rem); }\n";
        
        return $output;
    }
    
    /**
     * Generate typography utilities
     */
    private function generate_typography_utilities() {
        $output = "\n/* ===================================\n   Typography Utilities\n   =================================== */\n\n";
        
        // Font size utilities
        foreach ($this->variables as $name => $value) {
            if (strpos($name, 'text-') === 0 && !in_array($name, ['text', 'text-light', 'text-muted'])) {
                $size = str_replace('text-', '', $name);
                $output .= ".text-{$size} { font-size: var(--st-{$name}); }\n";
            }
        }
        
        $output .= "\n";
        
        // Font weight utilities
        foreach ($this->variables as $name => $value) {
            if (strpos($name, 'font-') === 0 && preg_match('/(normal|medium|semibold|bold)/', $name)) {
                $weight = str_replace('font-', '', $name);
                $output .= ".font-{$weight} { font-weight: var(--st-{$name}); }\n";
            }
        }
        
        $output .= "\n";
        
        // Line height utilities
        foreach ($this->variables as $name => $value) {
            if (strpos($name, 'leading-') === 0) {
                $height = str_replace('leading-', '', $name);
                $output .= ".leading-{$height} { line-height: var(--st-{$name}); }\n";
            }
        }
        
        $output .= "\n";
        
        // Font family utilities
        $output .= ".font-heading { font-family: var(--st-font-heading); }\n";
        $output .= ".font-body { font-family: var(--st-font-body); }\n";
        $output .= ".font-mono { font-family: var(--st-font-mono); }\n";
        
        $output .= "\n";
        
        // Text alignment
        $output .= ".text-left { text-align: left; }\n";
        $output .= ".text-center { text-align: center; }\n";
        $output .= ".text-right { text-align: right; }\n";
        $output .= ".text-justify { text-align: justify; }\n";
        
        return $output;
    }
    
    /**
     * Generate border utilities
     */
    private function generate_border_utilities() {
        $output = "\n/* ===================================\n   Border Utilities\n   =================================== */\n\n";
        
        // Border width utilities
        $output .= ".border { border-width: var(--st-border-width); }\n";
        $output .= ".border-2 { border-width: var(--st-border-width-2); }\n";
        $output .= ".border-4 { border-width: var(--st-border-width-4); }\n";
        $output .= ".border-0 { border-width: 0; }\n";
        
        $output .= "\n";
        
        // Border radius utilities
        foreach ($this->variables as $name => $value) {
            if (strpos($name, 'radius') === 0) {
                if ($name === 'radius') {
                    $output .= ".rounded { border-radius: var(--st-{$name}); }\n";
                } elseif ($name === 'radius-full') {
                    $output .= ".rounded-full { border-radius: var(--st-{$name}); }\n";
                } else {
                    $size = str_replace('radius-', '', $name);
                    $output .= ".rounded-{$size} { border-radius: var(--st-{$name}); }\n";
                }
            }
        }
        
        $output .= ".rounded-none { border-radius: 0; }\n";
        
        return $output;
    }
    
    /**
     * Generate shadow utilities
     */
    private function generate_shadow_utilities() {
        $output = "\n/* ===================================\n   Shadow Utilities\n   =================================== */\n\n";
        
        foreach ($this->variables as $name => $value) {
            if (strpos($name, 'shadow') === 0) {
                if ($name === 'shadow') {
                    $output .= ".shadow { box-shadow: var(--st-{$name}); }\n";
                } else {
                    $size = str_replace('shadow-', '', $name);
                    $output .= ".shadow-{$size} { box-shadow: var(--st-{$name}); }\n";
                }
            }
        }
        
        $output .= ".shadow-none { box-shadow: none; }\n";
        
        return $output;
    }
    
    /**
     * Generate layout utilities
     */
    private function generate_layout_utilities() {
        $output = "\n/* ===================================\n   Layout Utilities\n   =================================== */\n\n";
        
        // Display utilities
        $output .= ".block { display: block; }\n";
        $output .= ".inline-block { display: inline-block; }\n";
        $output .= ".inline { display: inline; }\n";
        $output .= ".flex { display: flex; }\n";
        $output .= ".inline-flex { display: inline-flex; }\n";
        $output .= ".grid { display: grid; }\n";
        $output .= ".hidden { display: none; }\n";
        
        $output .= "\n";
        
        // Flexbox utilities
        $output .= ".flex-row { flex-direction: row; }\n";
        $output .= ".flex-col { flex-direction: column; }\n";
        $output .= ".flex-wrap { flex-wrap: wrap; }\n";
        $output .= ".flex-nowrap { flex-wrap: nowrap; }\n";
        $output .= ".items-start { align-items: flex-start; }\n";
        $output .= ".items-center { align-items: center; }\n";
        $output .= ".items-end { align-items: flex-end; }\n";
        $output .= ".justify-start { justify-content: flex-start; }\n";
        $output .= ".justify-center { justify-content: center; }\n";
        $output .= ".justify-end { justify-content: flex-end; }\n";
        $output .= ".justify-between { justify-content: space-between; }\n";
        $output .= ".justify-around { justify-content: space-around; }\n";
        
        $output .= "\n";
        
        // Height utilities
        $output .= ".h-full { height: 100%; }\n";
        $output .= ".h-auto { height: auto; }\n";
        
        // Overflow utilities
        $output .= ".overflow-hidden { overflow: hidden; }\n";
        $output .= ".overflow-auto { overflow: auto; }\n";
        $output .= ".overflow-visible { overflow: visible; }\n";
        
        // Position utilities
        $output .= ".relative { position: relative; }\n";
        $output .= ".absolute { position: absolute; }\n";
        $output .= ".fixed { position: fixed; }\n";
        $output .= ".sticky { position: sticky; }\n";
        
        // Z-index utilities
        foreach ($this->variables as $name => $value) {
            if (strpos($name, 'z-') === 0) {
                $level = str_replace('z-', '', $name);
                $output .= ".z-{$level} { z-index: var(--st-{$name}); }\n";
            }
        }
        
        // Opacity utilities
        foreach ($this->variables as $name => $value) {
            if (strpos($name, 'opacity-') === 0) {
                $level = str_replace('opacity-', '', $name);
                $output .= ".opacity-{$level} { opacity: var(--st-{$name}); }\n";
            }
        }
        
        return $output;
    }
    
    /**
     * Generate responsive utilities
     */
    private function generate_responsive_utilities() {
        $output = "\n/* ===================================\n   Responsive Utilities\n   =================================== */\n\n";
        
        $breakpoints = [
            'sm' => '640px',
            'md' => '768px',
            'lg' => '1024px',
            'xl' => '1280px',
            '2xl' => '1536px'
        ];
        
        foreach ($breakpoints as $prefix => $breakpoint) {
            $output .= "@media (min-width: {$breakpoint}) {\n";
            
            // Display utilities
            $output .= "  .{$prefix}\\:block { display: block; }\n";
            $output .= "  .{$prefix}\\:inline-block { display: inline-block; }\n";
            $output .= "  .{$prefix}\\:flex { display: flex; }\n";
            $output .= "  .{$prefix}\\:grid { display: grid; }\n";
            $output .= "  .{$prefix}\\:hidden { display: none; }\n";
            
            // Flexbox utilities
            $output .= "  .{$prefix}\\:flex-row { flex-direction: row; }\n";
            $output .= "  .{$prefix}\\:flex-col { flex-direction: column; }\n";
            
            // Text alignment
            $output .= "  .{$prefix}\\:text-left { text-align: left; }\n";
            $output .= "  .{$prefix}\\:text-center { text-align: center; }\n";
            $output .= "  .{$prefix}\\:text-right { text-align: right; }\n";
            
            $output .= "}\n\n";
        }
        
        return $output;
    }
    
    /**
     * Write utilities to file
     */
    private function write_utilities($utilities) {
        // Ensure directory exists
        $dir = dirname($this->output_file);
        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
        }
        
        // Write file
        file_put_contents($this->output_file, $utilities);
    }
}

// Initialize and run generator
function studio_generate_utilities() {
    $generator = new UtilityGenerator();
    return $generator->generate();
}
