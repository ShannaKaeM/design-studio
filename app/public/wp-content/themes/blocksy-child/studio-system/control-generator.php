<?php
/**
 * Studio Control Generator
 * Generates WordPress controls from CSS variable annotations
 */

class StudioControlGenerator {
    
    /**
     * Generate control configuration from variable data
     */
    public function generate_control($variable) {
        $control_type = $variable['control'];
        $params = $variable['params'];
        
        $base_config = [
            'id' => $variable['name'],
            'label' => $variable['label'],
            'type' => $this->map_control_type($control_type),
            'default' => $variable['value'],
            'css_var' => $variable['name']
        ];
        
        switch ($control_type) {
            case 'color':
                return array_merge($base_config, [
                    'type' => 'color',
                    'alpha' => true
                ]);
                
            case 'range':
                return array_merge($base_config, [
                    'type' => 'range',
                    'min' => $params['min'] ?? 0,
                    'max' => $params['max'] ?? 100,
                    'step' => $params['step'] ?? 1,
                    'unit' => $this->detect_unit($variable['value'])
                ]);
                
            case 'select':
                return array_merge($base_config, [
                    'type' => 'select',
                    'choices' => $this->format_select_choices($params)
                ]);
                
            case 'text':
                return array_merge($base_config, [
                    'type' => 'text'
                ]);
                
            case 'number':
                return array_merge($base_config, [
                    'type' => 'number',
                    'unit' => $this->detect_unit($variable['value'])
                ]);
                
            case 'font':
                return array_merge($base_config, [
                    'type' => 'font-family',
                    'choices' => $this->get_font_choices()
                ]);
                
            case 'shadow':
                return array_merge($base_config, [
                    'type' => 'box-shadow'
                ]);
                
            case 'spacing':
                return array_merge($base_config, [
                    'type' => 'spacing',
                    'sides' => $params ?: ['all']
                ]);
                
            case 'toggle':
                return array_merge($base_config, [
                    'type' => 'toggle'
                ]);
                
            default:
                return array_merge($base_config, [
                    'type' => 'text'
                ]);
        }
    }
    
    /**
     * Map control types to WordPress/React components
     */
    private function map_control_type($type) {
        $mappings = [
            'color' => 'ColorPicker',
            'range' => 'RangeControl',
            'select' => 'SelectControl',
            'text' => 'TextControl',
            'number' => 'NumberControl',
            'font' => 'FontFamilyPicker',
            'shadow' => 'BoxShadowControl',
            'spacing' => 'SpacingControl',
            'toggle' => 'ToggleControl'
        ];
        
        return $mappings[$type] ?? 'TextControl';
    }
    
    /**
     * Detect unit from value
     */
    private function detect_unit($value) {
        if (preg_match('/(px|rem|em|%|vw|vh)$/', $value, $matches)) {
            return $matches[1];
        }
        return '';
    }
    
    /**
     * Format select choices
     */
    private function format_select_choices($options) {
        $choices = [];
        foreach ($options as $option) {
            $choices[] = [
                'label' => ucfirst($option),
                'value' => $option
            ];
        }
        return $choices;
    }
    
    /**
     * Get font family choices
     */
    private function get_font_choices() {
        return [
            ['label' => 'System UI', 'value' => 'system-ui, sans-serif'],
            ['label' => 'Transitional', 'value' => 'Charter, Bitstream Charter, Sitka Text, Cambria, serif'],
            ['label' => 'Old Style', 'value' => 'Iowan Old Style, Palatino Linotype, URW Palladio L, P052, serif'],
            ['label' => 'Humanist', 'value' => 'Seravek, Gill Sans Nova, Ubuntu, Calibri, DejaVu Sans, source-sans-pro, sans-serif'],
            ['label' => 'Geometric Humanist', 'value' => 'Avenir, Montserrat, Corbel, URW Gothic, source-sans-pro, sans-serif'],
            ['label' => 'Classical Humanist', 'value' => 'Optima, Candara, Noto Sans, source-sans-pro, sans-serif'],
            ['label' => 'Neo Grotesque', 'value' => 'Inter, Roboto, Helvetica Neue, Arial Nova, Nimbus Sans, Arial, sans-serif'],
            ['label' => 'Monospace', 'value' => 'ui-monospace, SF Mono, Monaco, Droid Sans Mono, Source Code Pro, monospace'],
            ['label' => 'Industrial', 'value' => 'Bahnschrift, DIN Alternate, Franklin Gothic Medium, Nimbus Sans Narrow, sans-serif'],
            ['label' => 'Rounded Sans', 'value' => 'ui-rounded, Hiragino Maru Gothic ProN, Quicksand, Comfortaa, Manjari, Arial Rounded MT, sans-serif'],
            ['label' => 'Slab Serif', 'value' => 'Rockwell, Rockwell Nova, Roboto Slab, DejaVu Serif, Sitka Small, serif'],
            ['label' => 'Antique', 'value' => 'Superclarendon, Bookman Old Style, URW Bookman, URW Bookman L, Georgia Pro, Georgia, serif'],
            ['label' => 'Didone', 'value' => 'Didot, Bodoni MT, Noto Serif Display, URW Palladio L, P052, Sylfaen, serif'],
            ['label' => 'Handwritten', 'value' => 'Segoe Print, Bradley Hand, Chilanka, TSCu_Comic, casual, cursive']
        ];
    }
    
    /**
     * Generate controls for all variables
     */
    public function generate_all_controls($variables) {
        $controls = [];
        
        foreach ($variables as $var_name => $variable) {
            $controls[$var_name] = $this->generate_control($variable);
        }
        
        return $controls;
    }
    
    /**
     * Generate React component code for controls
     */
    public function generate_react_controls($controls, $category = '') {
        $output = [];
        
        foreach ($controls as $control) {
            $component = $this->generate_react_component($control);
            $output[] = $component;
        }
        
        return implode("\n\n", $output);
    }
    
    /**
     * Generate individual React component
     */
    private function generate_react_component($control) {
        $type = $control['type'];
        
        switch ($type) {
            case 'ColorPicker':
                return sprintf(
                    '<ColorPicker
                        label="%s"
                        value={values["%s"]}
                        onChange={(value) => onChange("%s", value)}
                        enableAlpha={%s}
                    />',
                    $control['label'],
                    $control['id'],
                    $control['id'],
                    $control['alpha'] ? 'true' : 'false'
                );
                
            case 'RangeControl':
                return sprintf(
                    '<RangeControl
                        label="%s"
                        value={values["%s"]}
                        onChange={(value) => onChange("%s", value)}
                        min={%s}
                        max={%s}
                        step={%s}
                    />',
                    $control['label'],
                    $control['id'],
                    $control['id'],
                    $control['min'],
                    $control['max'],
                    $control['step']
                );
                
            case 'SelectControl':
                return sprintf(
                    '<SelectControl
                        label="%s"
                        value={values["%s"]}
                        onChange={(value) => onChange("%s", value)}
                        options={%s}
                    />',
                    $control['label'],
                    $control['id'],
                    $control['id'],
                    json_encode($control['choices'])
                );
                
            default:
                return sprintf(
                    '<TextControl
                        label="%s"
                        value={values["%s"]}
                        onChange={(value) => onChange("%s", value)}
                    />',
                    $control['label'],
                    $control['id'],
                    $control['id']
                );
        }
    }
}