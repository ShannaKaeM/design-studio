# Studio Implementation: Simple Step-by-Step Guide

## Overview
Build a dead-simple design system using CSS variables, auto-generated utilities, and GenerateBlocks Pro for layout.

**Key Components:**
- CSS Variables as single source of truth
- Auto-generated utility classes from variables
- GenerateBlocks Pro for all layout and blocks
- Visual controls for live editing
- No custom block development needed

## Phase 1: Foundation (Week 1)

### 1.1 Create CSS Variables File
```css
/* /assets/css/studio-vars.css */
:root {
  /* === Colors === */
  --st-primary: #5a7b7c;      /* @control: color */
  --st-secondary: #975d55;    /* @control: color */
  --st-text: #404040;         /* @control: color */
  --st-bg: #ffffff;           /* @control: color */
  
  /* === Spacing === */
  --st-space-xs: 4px;         /* @control: range[2,8] */
  --st-space-sm: 8px;         /* @control: range[4,16] */
  --st-space-md: 16px;        /* @control: range[8,32] */
  --st-space-lg: 24px;        /* @control: range[16,48] */
  --st-space-xl: 32px;        /* @control: range[24,64] */
  
  /* === Typography === */
  --st-text-xs: 12px;         /* @control: range[10,14] */
  --st-text-sm: 14px;         /* @control: range[12,16] */
  --st-text-base: 16px;       /* @control: range[14,20] */
  --st-text-lg: 20px;         /* @control: range[18,28] */
  --st-text-xl: 24px;         /* @control: range[20,32] */
  --st-text-2xl: 32px;        /* @control: range[28,40] */
  
  /* === Borders === */
  --st-radius-sm: 4px;        /* @control: range[0,8] */
  --st-radius: 6px;           /* @control: range[0,12] */
  --st-radius-lg: 8px;        /* @control: range[0,16] */
  --st-border: 1px;           /* @control: range[0,5] */
}
```

### 1.2 Load CSS in Theme
```php
// functions.php
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'studio-vars', 
        get_stylesheet_directory_uri() . '/assets/css/studio-vars.css',
        [],
        '1.0.0'
    );
});

add_action('enqueue_block_editor_assets', function() {
    wp_enqueue_style(
        'studio-vars-editor', 
        get_stylesheet_directory_uri() . '/assets/css/studio-vars.css',
        [],
        '1.0.0'
    );
});
```

### 1.3 Create Utility Generator
```php
// /studio-system/generate-utilities.php
<?php
/**
 * Generate utility classes from CSS variables
 */

function generate_utilities() {
    $css_file = get_stylesheet_directory() . '/assets/css/studio-vars.css';
    $output_file = get_stylesheet_directory() . '/assets/css/studio-utilities.css';
    
    // Read CSS file
    $css_content = file_get_contents($css_file);
    
    // Parse variables
    preg_match_all('/--st-([\w-]+):\s*([^;]+);/', $css_content, $matches);
    
    $utilities = "/* Auto-generated utility classes */\n\n";
    
    foreach ($matches[0] as $index => $match) {
        $var_name = '--st-' . $matches[1][$index];
        $var_full = '--st-' . $matches[1][$index];
        
        // Generate utilities based on variable name patterns
        if (strpos($matches[1][$index], 'color-') === 0 || in_array($matches[1][$index], ['primary', 'secondary', 'text', 'bg'])) {
            // Color utilities
            $class_name = str_replace('color-', '', $matches[1][$index]);
            $utilities .= ".bg-{$class_name} { background-color: var({$var_full}); }\n";
            $utilities .= ".text-{$class_name} { color: var({$var_full}); }\n";
            $utilities .= ".border-{$class_name} { border-color: var({$var_full}); }\n";
        }
        
        if (strpos($matches[1][$index], 'space-') === 0) {
            // Spacing utilities
            $size = str_replace('space-', '', $matches[1][$index]);
            $utilities .= ".p-{$size} { padding: var({$var_full}); }\n";
            $utilities .= ".m-{$size} { margin: var({$var_full}); }\n";
            $utilities .= ".gap-{$size} { gap: var({$var_full}); }\n";
        }
        
        if (strpos($matches[1][$index], 'text-') === 0 && !in_array($matches[1][$index], ['text'])) {
            // Text size utilities
            $size = str_replace('text-', '', $matches[1][$index]);
            $utilities .= ".text-{$size} { font-size: var({$var_full}); }\n";
        }
        
        if (strpos($matches[1][$index], 'radius') === 0) {
            // Border radius utilities
            $size = str_replace('radius-', '', $matches[1][$index]);
            $size = $size === 'radius' ? '' : '-' . $size;
            $utilities .= ".rounded{$size} { border-radius: var({$var_full}); }\n";
        }
    }
    
    // Write utilities file
    file_put_contents($output_file, $utilities);
    
    return true;
}

// Run generator
add_action('init', 'generate_utilities');
```

### 1.4 Test with GenerateBlocks
Instead of creating custom blocks, we'll use GenerateBlocks Pro with our utility classes:

1. **Create a Container Block**
   - Add a GenerateBlocks Container
   - In "Additional CSS Classes" field, add: `bg-primary p-lg rounded`
   - The utilities will automatically apply

2. **Test Different Utilities**
   ```
   Colors: bg-primary, bg-secondary, text-primary, text-secondary
   Spacing: p-xs, p-sm, p-md, p-lg, p-xl, m-xs, m-sm, etc.
   Typography: text-xs, text-sm, text-base, text-lg, text-xl
   Borders: rounded-sm, rounded, rounded-lg
   ```

3. **Responsive Utilities** (if implemented)
   ```
   md:p-lg, lg:text-xl, sm:bg-secondary
   ```

**Advantages of GenerateBlocks + Studio Utilities:**
- No custom block development needed
- Works with any GenerateBlocks element
- Easy to apply multiple utility classes
- No build process required
- Compatible with all WordPress themes

## Phase 2: Scanner & Controls (Week 2)

### 2.1 Build Variable Scanner
```php
// /studio-system/scan-variables.php
<?php
/**
 * Scan CSS files for variables and their control definitions
 */

function scan_css_variables($file_path) {
    $content = file_get_contents($file_path);
    $variables = [];
    
    // Match variables with comments
    preg_match_all(
        '/--st-([\w-]+):\s*([^;]+);\s*\/\*\s*@control:\s*(\w+)(?:\[([^\]]+)\])?\s*\*\//',
        $content,
        $matches,
        PREG_SET_ORDER
    );
    
    foreach ($matches as $match) {
        $var_name = '--st-' . $match[1];
        $var_value = trim($match[2]);
        $control_type = $match[3];
        $control_params = isset($match[4]) ? $match[4] : '';
        
        $variables[$var_name] = [
            'name' => $var_name,
            'value' => $var_value,
            'control' => $control_type,
            'params' => $control_params,
            'label' => ucwords(str_replace(['-', '_'], ' ', $match[1]))
        ];
    }
    
    return $variables;
}
```

### 2.2 Generate Control Definitions
```php
// /studio-system/generate-controls.php
<?php
/**
 * Generate control definitions from scanned variables
 */

function generate_control_config($variable) {
    $control = [
        'type' => $variable['control'],
        'label' => $variable['label'],
        'var' => $variable['name'],
        'default' => $variable['value']
    ];
    
    switch ($variable['control']) {
        case 'color':
            $control['component'] = 'ColorPicker';
            break;
            
        case 'range':
            if ($variable['params']) {
                list($min, $max) = explode(',', $variable['params']);
                $control['min'] = intval($min);
                $control['max'] = intval($max);
            }
            $control['component'] = 'RangeControl';
            break;
            
        case 'select':
            if ($variable['params']) {
                $control['options'] = explode(',', $variable['params']);
            }
            $control['component'] = 'SelectControl';
            break;
    }
    
    return $control;
}
```

### 2.3 Create Theme Settings Page
```php
// Add to functions.php
add_action('admin_menu', function() {
    add_theme_page(
        'Studio Theme Settings',
        'Studio Settings',
        'manage_options',
        'studio-settings',
        'render_studio_settings'
    );
});

function render_studio_settings() {
    $variables = scan_css_variables(get_stylesheet_directory() . '/assets/css/studio-vars.css');
    ?>
    <div class="wrap">
        <h1>Studio Theme Settings</h1>
        <div id="studio-settings-root"></div>
    </div>
    <script>
        window.studioVariables = <?php echo json_encode($variables); ?>;
    </script>
    <?php
}
```

### 2.4 React Component for Controls
```javascript
// /assets/js/studio-settings.js
const { useState, useEffect } = wp.element;
const { ColorPicker, RangeControl, SelectControl, Button } = wp.components;

function StudioSettings() {
    const [variables, setVariables] = useState(window.studioVariables || {});
    const [hasChanges, setHasChanges] = useState(false);
    
    const updateVariable = (varName, value) => {
        setVariables(prev => ({
            ...prev,
            [varName]: { ...prev[varName], value }
        }));
        setHasChanges(true);
        
        // Update CSS variable in real-time
        document.documentElement.style.setProperty(varName, value);
    };
    
    const saveChanges = async () => {
        // Save to CSS file via AJAX
        const response = await fetch(ajaxurl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'save_studio_variables',
                variables: JSON.stringify(variables),
                nonce: window.studioNonce
            })
        });
        
        if (response.ok) {
            setHasChanges(false);
        }
    };
    
    return (
        <div className="studio-settings">
            {Object.entries(variables).map(([varName, config]) => (
                <div key={varName} className="studio-control">
                    {config.control === 'color' && (
                        <ColorPicker
                            label={config.label}
                            color={config.value}
                            onChange={(color) => updateVariable(varName, color)}
                        />
                    )}
                    
                    {config.control === 'range' && (
                        <RangeControl
                            label={config.label}
                            value={parseInt(config.value)}
                            onChange={(value) => updateVariable(varName, value + 'px')}
                            min={config.min || 0}
                            max={config.max || 100}
                        />
                    )}
                </div>
            ))}
            
            {hasChanges && (
                <Button isPrimary onClick={saveChanges}>
                    Save Changes
                </Button>
            )}
        </div>
    );
}

// Mount the app
wp.domReady(() => {
    const root = document.getElementById('studio-settings-root');
    if (root) {
        wp.element.render(<StudioSettings />, root);
    }
});
```

## Phase 3: Block Integration (Week 3)

### 3.1 Add Utility Class Selector
```javascript
// Block inspector control for utility classes
const { InspectorControls } = wp.blockEditor;
const { PanelBody, SelectControl, TextControl } = wp.components;

function UtilityClassControl({ attributes, setAttributes }) {
    const colorUtilities = [
        { label: 'Primary Background', value: 'bg-primary' },
        { label: 'Secondary Background', value: 'bg-secondary' },
        { label: 'White Background', value: 'bg-white' },
    ];
    
    const spacingUtilities = [
        { label: 'Small Padding', value: 'p-sm' },
        { label: 'Medium Padding', value: 'p-md' },
        { label: 'Large Padding', value: 'p-lg' },
    ];
    
    return (
        <InspectorControls>
            <PanelBody title="Utility Classes">
                <SelectControl
                    label="Background"
                    value={attributes.bgUtility}
                    options={colorUtilities}
                    onChange={(bgUtility) => setAttributes({ bgUtility })}
                />
                
                <SelectControl
                    label="Spacing"
                    value={attributes.spacingUtility}
                    options={spacingUtilities}
                    onChange={(spacingUtility) => setAttributes({ spacingUtility })}
                />
                
                <TextControl
                    label="Custom Classes"
                    value={attributes.customClasses}
                    onChange={(customClasses) => setAttributes({ customClasses })}
                />
            </PanelBody>
        </InspectorControls>
    );
}
```

### 3.2 Create Utility Presets
```javascript
// Preset combinations for common patterns
const utilityPresets = {
    'card': ['bg-white', 'p-lg', 'rounded', 'shadow'],
    'hero': ['bg-primary', 'text-white', 'p-xl', 'text-center'],
    'section': ['p-xl', 'bg-bg'],
    'button': ['bg-primary', 'text-white', 'p-sm', 'rounded', 'hover:bg-secondary']
};
```

## Phase 4: Polish & Advanced Features (Week 4)

### 4.1 Responsive Utilities
```php
// Add to utility generator
$breakpoints = ['sm' => '640px', 'md' => '768px', 'lg' => '1024px'];

foreach ($breakpoints as $prefix => $breakpoint) {
    $utilities .= "\n@media (min-width: {$breakpoint}) {\n";
    // Generate responsive versions
    $utilities .= "  .{$prefix}\\:p-{$size} { padding: var({$var_full}); }\n";
    $utilities .= "}\n";
}
```

### 4.2 Hover States
```css
/* Add interactive states */
.hover\:bg-primary:hover { background-color: var(--st-primary); }
.hover\:text-primary:hover { color: var(--st-primary); }
.hover\:scale-105:hover { transform: scale(1.05); }
```

### 4.3 Documentation
Create clear documentation showing:
- How to add new variables
- How to use utilities in blocks
- How to create custom presets
- How to extend the system

## Migration Strategy

### From Existing System:
1. Keep existing blocks working
2. Gradually add utility classes
3. Replace inline styles with utilities
4. Remove old preset system once stable

### For New Blocks:
1. Use utilities from the start
2. No inline styles
3. Leverage preset combinations
4. Keep it simple

## Success Metrics

- [ ] CSS variables load correctly
- [ ] Utilities generate automatically
- [ ] Controls appear in theme settings
- [ ] Changes update in real-time
- [ ] Blocks use utility classes
- [ ] System is simple to understand

## Troubleshooting

### Common Issues:
1. **Utilities not generating**: Check file permissions
2. **Controls not showing**: Verify variable comments
3. **Changes not saving**: Check AJAX nonce
4. **Styles not applying**: Ensure CSS is loaded

### Debug Mode:
```php
// Add to wp-config.php
define('STUDIO_DEBUG', true);

// In your code
if (defined('STUDIO_DEBUG') && STUDIO_DEBUG) {
    error_log('Variables found: ' . print_r($variables, true));
}
```

## Next Steps

1. **Immediate**: Create studio-vars.css and test
2. **This Week**: Build utility generator
3. **Next Week**: Add scanner and controls
4. **Following Week**: Update all blocks
5. **Ongoing**: Refine and document

Remember: Keep it simple. CSS variables → Utilities → Blocks → Controls. That's it!
