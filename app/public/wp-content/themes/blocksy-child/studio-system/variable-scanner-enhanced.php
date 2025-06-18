<?php
/**
 * Studio Variable Scanner with @control Annotations
 * Implements Daniel's CSS-driven control generation system
 */

class StudioVariableScanner {
    
    /**
     * Scan CSS content for variables with @control annotations
     */
    public function scan_css_with_controls($content) {
        $variables = [];
        
        // Match any CSS variable with @control annotation
        $pattern = '/
            (--[\w-]+)                    # Variable name
            \s*:\s*                       # Colon
            ([^;]+?)                      # Variable value
            \s*;                          # Semicolon
            \s*\/\*\s*                    # Comment start
            @control\s*:\s*               # @control annotation
            (\w+)                         # Control type
            (?:\[([^\]]*)\])?             # Optional parameters
            \s*\*\/                       # Comment end
        /x';
        
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $var_name = $match[1];
            $var_value = trim($match[2]);
            $control_type = $match[3];
            $control_params = isset($match[4]) ? $match[4] : '';
            
            $variables[$var_name] = [
                'name' => $var_name,
                'value' => $var_value,
                'control' => $control_type,
                'params' => $this->parse_control_params($control_type, $control_params),
                'label' => $this->generate_label($var_name),
                'category' => $this->detect_category($var_name),
                'group' => $this->detect_group($var_name)
            ];
        }
        
        return $variables;
    }
    
    /**
     * Parse control parameters based on type
     */
    private function parse_control_params($type, $params) {
        if (empty($params)) {
            return [];
        }
        
        switch ($type) {
            case 'range':
                $parts = explode(',', $params);
                return [
                    'min' => floatval(trim($parts[0] ?? 0)),
                    'max' => floatval(trim($parts[1] ?? 100)),
                    'step' => floatval(trim($parts[2] ?? 1))
                ];
                
            case 'select':
                return array_map('trim', explode(',', $params));
                
            case 'spacing':
                return array_map('trim', explode(',', $params));
                
            default:
                return $params;
        }
    }
    
    /**
     * Generate label from variable name
     */
    private function generate_label($var_name) {
        $name = preg_replace('/^--/', '', $var_name);
        $parts = explode('-', $name);
        $parts = array_map('ucfirst', $parts);
        return implode(' ', $parts);
    }
    
    /**
     * Detect category from variable name
     */
    private function detect_category($var_name) {
        $patterns = [
            '/color|primary|secondary|accent|neutral/' => 'Colors',
            '/text-(?:xs|sm|base|lg|xl|2xl|3xl|4xl|5xl|6xl|7xl|8xl|9xl)/' => 'Typography',
            '/font-(?:size|weight|family|line|tracking|leading)/' => 'Typography',
            '/space|spacing|gap|padding|margin/' => 'Spacing',
            '/radius|border/' => 'Borders',
            '/shadow/' => 'Effects',
            '/breakpoint|container|width|height/' => 'Layout',
            '/transition|duration|ease/' => 'Animation',
            '/z-index|depth/' => 'Layering'
        ];
        
        foreach ($patterns as $pattern => $category) {
            if (preg_match($pattern, $var_name)) {
                return $category;
            }
        }
        
        return 'Other';
    }
    
    /**
     * Detect variable group for organization
     */
    private function detect_group($var_name) {
        if (preg_match('/^--([\w]+)-/', $var_name, $matches)) {
            return $matches[1];
        }
        return 'custom';
    }
    
    /**
     * Scan directory for all CSS files
     */
    public function scan_directory($directory) {
        $all_variables = [];
        
        $css_files = glob($directory . '/*.css');
        foreach ($css_files as $file) {
            $content = file_get_contents($file);
            $variables = $this->scan_css_with_controls($content);
            
            foreach ($variables as &$var) {
                $var['source'] = basename($file);
            }
            
            $all_variables = array_merge($all_variables, $variables);
        }
        
        return $all_variables;
    }
    
    /**
     * Get variables by category
     */
    public function get_categorized_variables($variables) {
        $categorized = [];
        
        foreach ($variables as $var) {
            $category = $var['category'];
            if (!isset($categorized[$category])) {
                $categorized[$category] = [];
            }
            $categorized[$category][] = $var;
        }
        
        return $categorized;
    }
}