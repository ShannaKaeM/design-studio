# Studio Theme Refactor Plan - Daniel's Approach
## Based on CSS Variable-Driven Design System

### Executive Summary
This refactor plan transforms the Studio theme from a WordPress-centric block preset system to a revolutionary CSS variable-driven design system with auto-generated controls, universal selector targeting, and AI-friendly architecture.

## Key Innovations from Daniel's System

### 1. CSS Variables → Auto-Generated Controls
Instead of manually creating controls, CSS variables with annotations automatically generate UI controls.

```css
/* Current Studio Approach */
// Define in PHP/JSON, create controls manually

/* Daniel's Approach */
--st-primary: #5a7b7c; /* @control: color */
--st-spacing-hero: 4rem; /* @control: range[0,10,0.5] */
--st-font-hero: 3rem; /* @control: range[1,5,0.1] */
```

### 2. CSS Sync Feature (NEW)
Daniel's morning addition - CSS Sync for classes:
- Saves and bundles ALL CSS properties
- Works even without @control annotations
- Enables complete CSS management through the system

### 3. Universal Selector Builder
- Target ANY element, not just blocks
- Apply variable groups to selectors
- Create scoped styles for components
- More flexible than block presets

### 4. Custom HTML Elements
```html
<!-- Semantic HTML that converts to blocks -->
<hero type="centered" background="gradient">
  <hero-title>Welcome</hero-title>
  <hero-content>Content here</hero-content>
</hero>
```

## Current Studio System Analysis

### What You Have:
1. **Variable Scanner** - Reads CSS files for variables
2. **Admin Interface** - Tabbed system for variables, utilities, selectors
3. **Utility Generator** - Creates utility classes from variables
4. **Selector Builder** - Basic implementation exists
5. **YAML Sync** - For AI integration
6. **Block Presets** - WordPress-focused approach

### What Needs Enhancement:
1. **@control Annotation Parser** - Auto-generate controls from CSS comments
2. **Dynamic Control Generation** - Create controls without PHP config
3. **CSS Sync Implementation** - Bundle all CSS, not just variables
4. **Enhanced Selector Builder** - Full flexibility for any selector
5. **Custom Element Parser** - Convert semantic HTML to blocks

## Refactor Implementation Plan

### Phase 1: Enhanced Variable Scanning (Week 1)

#### 1.1 Upgrade Variable Scanner
```php
// Enhanced scanner that reads @control annotations
class StudioVariableScanner {
    public function scan_with_controls($css_content) {
        // Pattern to match variables with @control comments
        $pattern = '/
            (--[\w-]+):\s*    # Variable name
            ([^;]+);          # Variable value
            \s*\/\*\s*        # Comment start
            @control:\s*      # Control annotation
            (\w+)             # Control type
            (?:\[(.*?)\])?    # Optional parameters
            \s*\*\/           # Comment end
        /x';
        
        preg_match_all($pattern, $css_content, $matches, PREG_SET_ORDER);
        
        $variables = [];
        foreach ($matches as $match) {
            $variables[] = [
                'name' => $match[1],
                'value' => trim($match[2]),
                'control' => $match[3],
                'params' => $match[4] ?? ''
            ];
        }
        
        return $variables;
    }
}
```

#### 1.2 Control Type Mapping
```php
// Map control types to WordPress components
$control_types = [
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
```

### Phase 2: CSS Sync Implementation (Week 1-2)

#### 2.1 CSS Class Scanner
```php
class StudioCSSSync {
    private $scanned_classes = [];
    
    public function scan_css_classes($css_content) {
        // Extract all class definitions
        preg_match_all('/\.([\w-]+)\s*{([^}]+)}/', $css_content, $matches);
        
        foreach ($matches[0] as $index => $match) {
            $class_name = $matches[1][$index];
            $properties = $matches[2][$index];
            
            $this->scanned_classes[$class_name] = $this->parse_properties($properties);
        }
        
        return $this->scanned_classes;
    }
    
    public function save_and_bundle() {
        // Save all classes to database
        // Generate optimized CSS bundle
        // Include classes without @control annotations
    }
}
```

#### 2.2 Integration with Variable System
- Scan both variables AND classes
- Allow editing of any CSS property
- Bundle optimized CSS output

### Phase 3: Enhanced Selector Builder (Week 2)

#### 3.1 Universal Selector Interface
```javascript
// React component for selector builder
const SelectorBuilder = () => {
    const [selector, setSelector] = useState('');
    const [variables, setVariables] = useState({});
    
    const selectorPresets = [
        { label: 'All Paragraphs', value: 'p' },
        { label: 'Hero Section H1', value: '.hero-section h1' },
        { label: 'Button Hover', value: '.btn:hover' },
        { label: 'First Child', value: ':first-child' },
        { label: 'Custom', value: 'custom' }
    ];
    
    return (
        <div className="selector-builder">
            <SelectControl
                label="Target Selector"
                value={selector}
                options={selectorPresets}
                onChange={setSelector}
            />
            
            <VariablePicker
                onSelect={(variable) => addVariableToSelector(variable)}
            />
            
            <CSSPreview selector={selector} variables={variables} />
        </div>
    );
};
```

#### 3.2 Selector Categories
```php
// Organize selectors by purpose
$selector_categories = [
    'typography' => [
        'headings' => 'h1, h2, h3, h4, h5, h6',
        'body-text' => 'p, li, td',
        'links' => 'a'
    ],
    'components' => [
        'buttons' => '.btn, button',
        'cards' => '.card',
        'hero' => '.hero-section'
    ],
    'states' => [
        'hover' => ':hover',
        'focus' => ':focus',
        'active' => ':active'
    ]
];
```

### Phase 4: Custom HTML Element Parser (Week 2-3)

#### 4.1 Element Definitions
```php
class StudioCustomElements {
    private $elements = [
        'hero' => [
            'block' => 'generateblocks/container',
            'children' => ['hero-title', 'hero-content', 'hero-cta'],
            'attributes' => ['type', 'background', 'height']
        ],
        'accordion-root' => [
            'block' => 'generateblocks/accordion',
            'children' => ['accordion-item'],
            'attributes' => ['allow-multiple', 'default-open']
        ],
        'query-root' => [
            'block' => 'generateblocks/query-loop',
            'attributes' => ['post-type', 'posts-per-page', 'order-by']
        ]
    ];
    
    public function parse_to_blocks($html) {
        // Convert custom elements to WordPress blocks
    }
}
```

### Phase 5: Integration & Migration (Week 3)

#### 5.1 Backwards Compatibility
```php
// Maintain support for existing presets while adding new features
class StudioCompatibility {
    public function migrate_presets_to_selectors() {
        $old_presets = [
            'is-style-title' => 'h1, h2',
            'is-style-subtitle' => 'h3, h4',
            'is-style-body' => 'p'
        ];
        
        foreach ($old_presets as $class => $selector) {
            $this->create_selector_from_preset($class, $selector);
        }
    }
}
```

#### 5.2 Progressive Enhancement
1. Keep existing functionality working
2. Add new features alongside
3. Provide migration tools
4. Document upgrade path

## New File Structure

```
studio-system/
├── core/
│   ├── variable-scanner-enhanced.php    # With @control parsing
│   ├── css-sync.php                    # New CSS sync feature
│   ├── control-generator.php           # Auto-generate controls
│   ├── selector-builder-pro.php        # Enhanced selector system
│   └── custom-elements-parser.php      # HTML to blocks
├── admin/
│   ├── studio-variables-pro.php        # Enhanced variables UI
│   ├── studio-selectors-pro.php        # Universal selector UI
│   ├── studio-css-sync.php             # CSS sync interface
│   └── studio-elements.php             # Custom elements UI
├── assets/
│   ├── css/
│   │   ├── studio-vars.css             # With @control annotations
│   │   ├── studio-classes.css          # Synced classes
│   │   └── studio-output.css           # Generated styles
│   └── js/
│       ├── selector-builder.js         # React components
│       └── css-sync.js                 # Sync functionality
└── templates/
    └── custom-elements/                # Element templates
```

## Implementation Timeline

### Week 1: Foundation
- [ ] Enhanced variable scanner with @control support
- [ ] Basic CSS sync implementation
- [ ] Control generation from annotations

### Week 2: Builder Systems
- [ ] Universal selector builder
- [ ] CSS class management
- [ ] Custom element parser basics

### Week 3: Integration
- [ ] Full CSS sync with bundling
- [ ] Custom element library
- [ ] Migration tools
- [ ] Documentation

### Week 4: Polish & Launch
- [ ] Testing & debugging
- [ ] Performance optimization
- [ ] AI training data preparation
- [ ] Final documentation

## Quick Start Guide

### 1. Adding Variables with Controls
```css
/* studio-vars.css */
:root {
    /* Color with color picker */
    --st-primary: #5a7b7c; /* @control: color */
    
    /* Spacing with range slider */
    --st-gap: 2rem; /* @control: range[0,10,0.25] */
    
    /* Font size with select */
    --st-text-size: 1rem; /* @control: select[0.875rem,1rem,1.125rem,1.25rem] */
    
    /* Shadow with shadow control */
    --st-shadow: 0 2px 4px rgba(0,0,0,0.1); /* @control: shadow */
}
```

### 2. Using CSS Sync
```css
/* Any class will be synced and editable */
.my-custom-button {
    padding: var(--st-padding);
    background: var(--st-primary);
    border-radius: var(--st-radius);
    /* No @control needed - CSS Sync handles it */
}
```

### 3. Building with Selectors
```php
// In admin, create selector rules
$selector_rule = [
    'selector' => '.hero-section h1',
    'variables' => [
        'font-size' => 'var(--st-text-hero)',
        'color' => 'var(--st-primary)',
        'margin-bottom' => 'var(--st-spacing-lg)'
    ]
];
```

### 4. Using Custom Elements
```html
<!-- Write this -->
<hero background="gradient" height="full">
    <hero-title>Welcome to Studio</hero-title>
    <hero-content>Build amazing sites</hero-content>
</hero>

<!-- Converts to GenerateBlocks automatically -->
```

## Benefits of This Refactor

1. **Zero-Config Controls**: Just add CSS variables with annotations
2. **Universal Styling**: Target any element, not just blocks
3. **CSS-First**: Everything starts with CSS, not PHP
4. **AI-Friendly**: Clear patterns for AI understanding
5. **Developer Experience**: Write CSS, get UI controls automatically
6. **Flexibility**: Use classes, selectors, or custom elements
7. **Performance**: Optimized CSS output with bundling

## Next Steps

1. Review this plan and identify priorities
2. Start with Phase 1 (Enhanced Variable Scanning)
3. Test @control annotations with simple examples
4. Implement CSS Sync for existing classes
5. Build enhanced selector interface

This refactor will transform Studio into a truly revolutionary design system that matches Daniel's vision while maintaining your WordPress standards approach.