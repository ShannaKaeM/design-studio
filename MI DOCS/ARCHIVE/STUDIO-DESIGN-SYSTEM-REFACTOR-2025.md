# The Studio - Design System Complete Refactor 2025

## Overview

"The Studio" is a revolutionary WordPress theme that implements Daniel's variable-driven design system. It abandons traditional WordPress theming patterns in favor of a pure CSS-first approach where variables automatically generate controls.

## Core Philosophy

1. **CSS Variables Drive Everything** - Add a variable, get a control
2. **No Manual Configuration** - System scans and builds itself
3. **Target Anything** - Not limited to blocks or classes
4. **AI-First Design** - Simple patterns that AI can understand
5. **Zero WordPress Dependencies** - Works with any HTML/CSS

## Architecture Clarification: Two Systems

### 1. Studio Design System (This Document)
- **Purpose**: Styling and visual design
- **JSON Usage**: None (pure CSS variables)
- **Location**: Theme files
- **Controls**: Auto-generated from CSS variables

### 2. Studio Content System (Villa Refactor)
- **Purpose**: Content management (properties, owners, etc.)
- **JSON Usage**: ACF Local JSON + Daniel's sync idea
- **Location**: `/wp-content/studio-data/`
- **Database**: ACF custom post types

**These work together but are separate systems!**

## Variable Structure (Tailwind-Based)

```css
:root {
    /* Colors - Semantic */
    --ts-color-primary: oklch(45% .24 277.023);
    --ts-color-secondary: oklch(65% .241 354.308);
    --ts-color-accent: oklch(77% .152 181.912);
    --ts-color-neutral: oklch(14% .005 285.823);
    
    /* Colors - Functional */
    --ts-color-success: oklch(76% .177 163.223);
    --ts-color-warning: oklch(82% .189 84.429);
    --ts-color-error: oklch(71% .194 13.428);
    --ts-color-info: oklch(74% .16 232.661);
    
    /* Base Colors */
    --ts-color-base-100: oklch(100% 0 0);
    --ts-color-base-200: oklch(98% 0 0);
    --ts-color-base-300: oklch(95% 0 0);
    --ts-color-base-content: oklch(21% .006 285.885);
    
    /* Typography - Sizes */
    --ts-text-xs: 0.75rem;
    --ts-text-sm: 0.875rem;
    --ts-text-base: 1rem;
    --ts-text-lg: 1.125rem;
    --ts-text-xl: 1.25rem;
    --ts-text-2xl: 1.5rem;
    --ts-text-3xl: 1.875rem;
    --ts-text-4xl: 2.25rem;
    --ts-text-5xl: 3rem;
    
    /* Typography - Weights */
    --ts-font-thin: 100;
    --ts-font-light: 300;
    --ts-font-normal: 400;
    --ts-font-medium: 500;
    --ts-font-semibold: 600;
    --ts-font-bold: 700;
    --ts-font-extrabold: 800;
    
    /* Spacing */
    --ts-spacing-xs: 0.125rem;
    --ts-spacing-sm: 0.25rem;
    --ts-spacing-md: 0.5rem;
    --ts-spacing-lg: 0.75rem;
    --ts-spacing-xl: 1rem;
    --ts-spacing-2xl: 1.5rem;
    --ts-spacing-3xl: 2rem;
    --ts-spacing-4xl: 3rem;
    
    /* Radius */
    --ts-radius-sm: 0.125rem;
    --ts-radius-md: 0.25rem;
    --ts-radius-lg: 0.5rem;
    --ts-radius-xl: 0.75rem;
    --ts-radius-2xl: 1rem;
    --ts-radius-full: 9999px;
    
    /* Shadows */
    --ts-shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --ts-shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    --ts-shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
    --ts-shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1);
}
```

## Control Annotations System

```css
/* Add annotations to auto-generate controls */
:root {
    --ts-color-primary: #5a7b7c; /* @control: color */
    --ts-spacing-base: 1rem; /* @control: range[0.5,2,0.25] */
    --ts-font-body: 'Inter'; /* @control: font */
    --ts-shadow-card: 0 2px 4px rgba(0,0,0,0.1); /* @control: shadow */
    --ts-radius-button: 0.5rem; /* @control: range[0,2,0.125] */
    --ts-layout-sidebar: 280px; /* @control: range[200,400,10] */
}
```

## File Structure

```
/wp-content/themes/the-studio/
├── style.css                    # Theme header only
├── functions.php                # Minimal loader
├── studio/
│   ├── core/
│   │   ├── studio-loader.php    # Main orchestration
│   │   ├── variable-scanner.php # Scans CSS for variables
│   │   ├── control-generator.php # Creates UI from variables
│   │   ├── selector-builder.php # Apply variables to selectors
│   │   └── parser.php           # Custom HTML elements
│   ├── css/
│   │   ├── studio-vars.css      # All design tokens
│   │   ├── studio-base.css      # Element defaults
│   │   ├── studio-utilities.css # Auto-generated utilities
│   │   └── studio-selectors.css # Selector builder output
│   ├── components/              # Optional component library
│   │   ├── buttons.css
│   │   ├── cards.css
│   │   └── forms.css
│   ├── admin/
│   │   ├── studio-admin.php     # Top-level admin page
│   │   ├── studio-admin.css
│   │   └── studio-admin.js
│   └── data/
│       └── selectors.json       # Saved selector rules
```

## Key Components

### 1. Variable Scanner
```php
// Scans CSS files for variables with @control annotations
class StudioVariableScanner {
    public function scan_variables($css_file) {
        // Extract variables and control metadata
        // Return structured data for UI generation
    }
}
```

### 2. Control Generator
```php
// Generates admin controls from scanned variables
class StudioControlGenerator {
    public function generate_control($variable, $metadata) {
        // Create appropriate control based on type
        // color → color picker
        // range[min,max] → slider
        // select[options] → dropdown
    }
}
```

### 3. Selector Builder
```php
// Allows targeting any CSS selector with variable groups
class StudioSelectorBuilder {
    public function create_rule($name, $selector, $variables) {
        // Example: Apply hero styles to .hero h1
        // Generates CSS with selected variables
    }
}
```

### 4. Custom Elements Parser
```php
// Converts semantic HTML to GenerateBlocks
class StudioElementParser {
    // <hero> → GB Container with hero class
    // <card> → GB Container with card styles
    // <button-primary> → GB Button with primary styles
}
```

## Utility Generation

```css
/* Auto-generated from variables */
.text-primary { color: var(--ts-color-primary); }
.bg-primary { background-color: var(--ts-color-primary); }
.p-sm { padding: var(--ts-spacing-sm); }
.rounded-lg { border-radius: var(--ts-radius-lg); }
.shadow-md { box-shadow: var(--ts-shadow-md); }

/* Responsive utilities */
@media (min-width: 768px) {
    .md\:text-lg { font-size: var(--ts-text-lg); }
    .md\:p-lg { padding: var(--ts-spacing-lg); }
}
```

## Element-Based Styling (Daniel's Approach)

```css
/* Base element styles using variables */
button {
    padding: var(--ts-spacing-md) var(--ts-spacing-xl);
    background: var(--ts-color-primary);
    color: var(--ts-color-base-100);
    border-radius: var(--ts-radius-md);
    font-weight: var(--ts-font-medium);
    transition: all 0.2s;
}

button:hover {
    background: var(--ts-color-primary-dark);
    transform: translateY(-1px);
}

/* Component variations via classes */
.btn-secondary {
    background: var(--ts-color-secondary);
}

.btn-large {
    padding: var(--ts-spacing-lg) var(--ts-spacing-2xl);
    font-size: var(--ts-text-lg);
}
```

## Admin Interface Location

```php
// In functions.php or studio-loader.php
add_action('admin_menu', function() {
    // Top-level menu item (not under Appearance)
    add_menu_page(
        'The Studio',
        'The Studio',
        'manage_options',
        'the-studio',
        'studio_admin_page',
        'dashicons-admin-customizer',
        3 // Position after Dashboard
    );
    
    // Submenu items
    add_submenu_page('the-studio', 'Variables', 'Variables', 'manage_options', 'studio-variables');
    add_submenu_page('the-studio', 'Selectors', 'Selectors', 'manage_options', 'studio-selectors');
    add_submenu_page('the-studio', 'Components', 'Components', 'manage_options', 'studio-components');
});
```

## GenerateBlocks Integration

```css
/* Studio utilities work with GB */
/* Apply via Additional CSS Classes field */

/* GB Container with Studio utilities */
<div class="gb-container bg-primary p-xl rounded-lg shadow-md">
    <!-- Content -->
</div>

/* Or use Selector Builder to target GB elements */
.gb-container.hero-section {
    background: var(--ts-color-accent);
    padding: var(--ts-spacing-4xl);
}
```

## Custom HTML Elements (Future)

```html
<!-- Write this -->
<studio-hero background="primary" spacing="large">
    <heading level="1">Welcome to The Studio</heading>
    <text>Build beautiful WordPress sites with ease</text>
    <button-primary href="/get-started">Get Started</button-primary>
</studio-hero>

<!-- Converts to GenerateBlocks -->
<div class="gb-container studio-hero bg-primary p-xl">
    <h1 class="gb-headline">Welcome to The Studio</h1>
    <p class="gb-text">Build beautiful WordPress sites with ease</p>
    <a class="gb-button btn-primary" href="/get-started">Get Started</a>
</div>
```

## Migration Path

### Phase 1: Foundation (Week 1)
1. Create fresh Blocksy child theme "The Studio"
2. Implement variable scanner with @control detection
3. Build basic admin interface (top-level menu)
4. Generate utilities from variables
5. Test with simple components

### Phase 2: Selector Builder (Week 2)
1. Create selector builder interface
2. Save/load selector rules
3. Generate selector CSS file
4. Add import/export functionality
5. Test with complex selectors

### Phase 3: Advanced Features (Week 3)
1. Custom HTML elements parser
2. Component library with variations
3. Responsive control options
4. Live preview system
5. AI training documentation

### Phase 4: Integration (Week 4)
1. Connect with Villa Content System
2. Create unified admin experience
3. Add helper functions for common tasks
4. Performance optimization
5. Documentation and examples

## AI-Friendly Patterns

```css
/* Simple variable changes */
--ts-color-primary: #new-color;

/* Component creation */
.my-feature-card {
    background: var(--ts-color-base-200);
    padding: var(--ts-spacing-xl);
    border-radius: var(--ts-radius-lg);
    box-shadow: var(--ts-shadow-md);
}

/* Utility combinations */
class="bg-primary text-white p-xl rounded-lg shadow-md hover:shadow-xl"
```

## Key Differences from Current System

| Current Studio | The Studio (New) |
|----------------|------------------|
| theme.json integration | Pure CSS variables |
| Manual control creation | Auto-generated controls |
| Block-focused presets | Element/selector based |
| WordPress patterns | Framework agnostic |
| Appearance menu | Top-level admin menu |
| Complex configuration | Self-configuring |

## Success Metrics

1. **Developer Experience**
   - Zero configuration setup
   - Intuitive variable naming
   - Fast iteration cycles

2. **AI Compatibility**
   - Simple, predictable patterns
   - Standard naming conventions
   - Easy to generate/modify

3. **Performance**
   - Minimal overhead
   - Efficient CSS output
   - Fast admin interface

4. **Flexibility**
   - Works with any HTML
   - Compatible with GB
   - Theme independent

## Next Steps

1. Confirm this aligns with your vision
2. Create "The Studio" child theme folder
3. Start with variable scanner implementation
4. Build minimal admin interface
5. Test with real components

This is a complete ground-up rebuild that embraces Daniel's philosophy while maintaining your goals for AI-friendliness and GenerateBlocks compatibility.