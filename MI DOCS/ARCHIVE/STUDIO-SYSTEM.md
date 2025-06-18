# Studio System: Simple CSS Variables → Utilities → Blocks

## Vision
**Dead Simple Design System** - CSS variables define everything, utilities are auto-generated, blocks use utilities. That's it.

## The Simple Truth

### What We're Building:
1. **CSS Variables** = Your design tokens (colors, spacing, fonts)
2. **Utility Classes** = Auto-generated from those variables
3. **Blocks** = Use the utility classes
4. **Controls** = Auto-generated from variable comments

### What We're NOT Building:
- ❌ Complex preset inheritance systems
- ❌ Manual control creation
- ❌ Complicated theme.json modifications
- ❌ Legacy compatibility layers

## How It Works (Super Simple)

### Step 1: Define CSS Variables
```css
/* studio-vars.css */
:root {
  /* Colors */
  --st-primary: #5a7b7c;      /* @control: color */
  --st-secondary: #975d55;    /* @control: color */
  --st-text: #404040;         /* @control: color */
  --st-bg: #ffffff;           /* @control: color */
  
  /* Spacing */
  --st-space-sm: 8px;         /* @control: range[4,16] */
  --st-space-md: 16px;        /* @control: range[8,32] */
  --st-space-lg: 24px;        /* @control: range[16,48] */
  
  /* Typography */
  --st-text-sm: 14px;         /* @control: range[12,16] */
  --st-text-base: 16px;       /* @control: range[14,20] */
  --st-text-lg: 20px;         /* @control: range[18,28] */
  
  /* Borders */
  --st-radius: 6px;           /* @control: range[0,20] */
  --st-border: 1px;           /* @control: range[0,5] */
}
```

### Step 2: Auto-Generate Utilities
```css
/* Generated studio-utilities.css */
.bg-primary { background-color: var(--st-primary); }
.bg-secondary { background-color: var(--st-secondary); }
.text-primary { color: var(--st-primary); }
.text-secondary { color: var(--st-secondary); }

.p-sm { padding: var(--st-space-sm); }
.p-md { padding: var(--st-space-md); }
.p-lg { padding: var(--st-space-lg); }

.text-sm { font-size: var(--st-text-sm); }
.text-base { font-size: var(--st-text-base); }
.text-lg { font-size: var(--st-text-lg); }

.rounded { border-radius: var(--st-radius); }
.border { border-width: var(--st-border); }
```

### Step 3: Use in Blocks
```jsx
// Studio Container Block
function Edit({ attributes, setAttributes }) {
  return (
    <div className="bg-primary p-lg rounded">
      <InnerBlocks />
    </div>
  );
}
```

### Step 4: Auto-Generated Controls
```
Theme Settings Panel:
┌─────────────────────────┐
│ Colors                  │
├─────────────────────────┤
│ Primary:    [#5a7b7c] 🎨│
│ Secondary:  [#975d55] 🎨│
│ Text:       [#404040] 🎨│
│ Background: [#ffffff] 🎨│
└─────────────────────────┘

┌─────────────────────────┐
│ Spacing                 │
├─────────────────────────┤
│ Small:  [====|==] 8px   │
│ Medium: [=======|] 16px │
│ Large:  [========|] 24px│
└─────────────────────────┘
```

## The Magic: Variable Scanner

### How Daniel's Approach Works:

1. **Scan CSS File**
```php
// Find all CSS variables
$variables = scan_css_for_variables('studio-vars.css');
// Returns: ['--st-primary' => '#5a7b7c', ...]
```

2. **Parse Control Comments**
```php
// Find @control comments
if (preg_match('/@control:\s*(\w+)/', $comment, $matches)) {
    $control_type = $matches[1]; // 'color', 'range', etc.
}
```

3. **Generate Controls**
```php
// Create appropriate control based on type
switch ($control_type) {
    case 'color':
        return new ColorPickerControl($variable);
    case 'range':
        return new RangeControl($variable, $min, $max);
}
```

4. **Save Changes**
```javascript
// When user changes a value
updateCSSVariable('--st-primary', '#new-color');
// Updates everywhere instantly!
```

## File Structure (Clean & Simple)

```
blocksy-child/
├── assets/
│   ├── css/
│   │   ├── studio-vars.css      # Your CSS variables
│   │   └── studio-utilities.css # Auto-generated
│   └── js/
│       └── studio-controls.js   # Control logic
├── studio-system/
│   ├── generate-utilities.php   # Utility generator
│   ├── scan-variables.php       # Variable scanner
│   └── controls.json            # Cached control definitions
├── blocks/
│   └── studio-container/        # Uses utility classes
└── functions.php                # Loads everything
```

## Implementation Plan

### Week 1: Foundation
- [x] Create `studio-vars.css` with design tokens
- [ ] Build utility generator (`generate-utilities.php`)
- [ ] Test utilities in existing blocks
- [ ] Load CSS files in theme

### Week 2: Scanner & Controls
- [ ] Build variable scanner (`scan-variables.php`)
- [ ] Parse control comments
- [ ] Generate control definitions
- [ ] Create theme settings page

### Week 3: Block Integration
- [ ] Update Studio Container to use utilities
- [ ] Add utility class selector in block inspector
- [ ] Create utility preset combinations
- [ ] Test with real content

### Week 4: Polish
- [ ] Add responsive utilities
- [ ] Create hover/focus states
- [ ] Build selector tool for advanced styling
- [ ] Documentation and examples

## Benefits of This Approach

### For Developers:
- **Simple**: Just CSS variables and classes
- **Predictable**: Variable → Utility → Usage
- **Maintainable**: Single source of truth
- **Extensible**: Add variables, get utilities

### For Users:
- **Visual Controls**: Change any variable visually
- **Live Preview**: See changes instantly
- **No Code**: Everything through UI
- **Consistent**: Changes apply everywhere

### For AI:
- **Clear Structure**: Semantic variable names
- **Easy Generation**: Just set CSS variables
- **Predictable Output**: Utility classes
- **Simple Updates**: Change variables, not code

## Example: Complete Flow

### 1. You Add a Variable:
```css
--st-button-bg: #5a7b7c; /* @control: color */
```

### 2. System Auto-Generates:

**Utility Class:**
```css
.bg-button { background-color: var(--st-button-bg); }
```

**Theme Control:**
```
Button Background: [#5a7b7c] 🎨
```

**Block Option:**
```jsx
<select>
  <option value="bg-primary">Primary Background</option>
  <option value="bg-button">Button Background</option>
</select>
```

### 3. User Experience:
- Changes color in theme settings
- All buttons update instantly
- No code editing needed

## What Makes This Different

### Traditional WordPress:
- Edit theme.json manually
- Create block styles one by one
- Write custom CSS
- Complex preset systems

### Studio System:
- Add CSS variable → Done
- Utilities generated automatically
- Controls generated automatically
- Dead simple to understand

## Next Steps

1. **Start Fresh**
   - Remove complex configurations
   - Create simple CSS variables file
   - Build basic utility generator

2. **Test & Iterate**
   - Use utilities in one block
   - Get controls working
   - Refine based on usage

3. **Expand Gradually**
   - Add more variables as needed
   - Generate more utility types
   - Build advanced features

## The Bottom Line

**CSS Variables → Utilities → Blocks → Controls**

That's the entire system. No complexity, no magic, just simple tools that work together. Add a variable, get a utility, use in blocks, control visually. Done.
