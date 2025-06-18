# S Design System Guide

## How CSS Variables Work in This System

### 1. Variable Definition
Variables are defined in `s-vars.css` with `@control` annotations:
```css
--s-primary: #0066cc; /* @control: color */
--s-space-md: 1rem; /* @control: range[0,4,0.25] */
```

### 2. Control Types
- `color` → Color picker in admin
- `range[min,max,step]` → Slider control
- `select[option1,option2,...]` → Dropdown menu
- `text` → Text input
- `shadow` → Shadow builder
- `font` → Font family picker

### 3. Variable Naming Convention
```
--s-[category]-[property]-[variant]
```
Examples:
- `--s-primary` (color)
- `--s-primary-light` (color variant)
- `--s-space-md` (spacing)
- `--s-text-lg` (typography)
- `--s-radius-xl` (border radius)

### 4. Your Color System
```css
/* Primary Colors */
--s-primary
--s-primary-light
--s-primary-dark

/* Secondary Colors */
--s-secondary
--s-secondary-light
--s-secondary-dark

/* Neutral Colors */
--s-neutral
--s-neutral-light
--s-neutral-dark

/* Base Grays */
--s-base-lightest  (white)
--s-base-lighter
--s-base-light
--s-base          (middle gray)
--s-base-dark
--s-base-darker
--s-base-darkest  (black)
```

## Building Components

### Method 1: Simple Classes
```css
/* Component CSS */
.s-hero {
    background: var(--s-base-lighter);
    padding: var(--s-space-xl);
    border-radius: var(--s-radius-2xl);
}
```

### Method 2: Component Variables
```css
/* Define component-specific variables */
--s-hero-bg: var(--s-base-lighter); /* @control: color */
--s-hero-padding: 4rem; /* @control: range[2,8,0.5] */

/* Use in component */
.s-hero {
    background: var(--s-hero-bg);
    padding: var(--s-hero-padding);
}
```

## Usage in WordPress

### 1. Add Blocks
```
GenerateBlocks Container
└── Additional CSS Classes: s-hero
```

### 2. Variables Update Everywhere
When you change `--s-primary` in Studio admin, it updates:
- All components using that variable
- All utility classes
- All custom selectors

### 3. No Inline Styles Needed
Just add the class name. The CSS variables handle everything.

## Common Issues & Solutions

### Variables Not Working?
1. **Check Units**: `1` should be `1px` or `1rem`
2. **Check Names**: Make sure variable exists
3. **Check Dependencies**: `['s-vars']` not `['studio-vars']`

### Controls Not Appearing?
1. **Check @control syntax**: Must have space after colon
2. **Range needs brackets**: `range[0,10,1]`
3. **Reload admin**: Changes need page refresh

### Styles Not Applying?
1. **Check class names**: `s-hero` not `studio-hero`
2. **Check CSS loading**: View source, look for s-vars.css
3. **Clear cache**: Both browser and WordPress

## Quick Test

1. Create a page
2. Add GenerateBlocks Container
3. Add class: `s-hero`
4. Inside, add Container with class: `s-hero-content`
5. Add Headline (h2) with class: `s-hero-title`
6. Add Headline (p) with class: `s-hero-desc`
7. Save and preview!

## Next Steps

1. **Customize Colors**: Go to Appearance > Studio System
2. **Adjust Spacing**: Use the range sliders
3. **Create New Components**: Follow the pattern
4. **Use Selector Builder**: Apply variables without classes