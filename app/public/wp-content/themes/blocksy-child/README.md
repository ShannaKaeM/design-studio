# Blocksy Child Theme with Studio Integration

## 🎯 **Overview**

This child theme extends the Blocksy parent theme with a comprehensive `theme.json` configuration that integrates with The Studio plugin for design token management. The theme.json completely overrides WordPress core defaults and Blocksy's built-in color system to provide a unified design token approach.

## 📁 **File Structure**

```
blocksy-child/
├── 📄 style.css                 # Child theme styles
├── 📄 functions.php             # Theme functions
├── 🎨 theme.json               # COMPLETE design system override
└── 📄 README.md                # This documentation
```

## 🎨 **theme.json Architecture**

### **Complete Override Strategy**
The `theme.json` file completely replaces:
- ✅ **WordPress Core Colors** - Disabled via `"defaultPalette": false`
- ✅ **WordPress Core Gradients** - Disabled via `"defaultGradients": false`  
- ✅ **WordPress Core Duotones** - Disabled via `"defaultDuotone": false`
- ✅ **Blocksy Theme Colors** - Replaced with Studio-managed colors

### **Design Token Integration**
- **Studio Plugin** manages colors in `studio.json`
- **Manual Sync** from Studio → `theme.json` via "Save to theme.json" buttons
- **WordPress API** automatically applies `theme.json` to frontend and editor

## 🎨 **Color System**

### **Current Color Palette (20 colors):**

#### **Theme Colors (Blocksy Integration):**
```json
{
  "slug": "color1", "color": "#2872fa", "name": "Color 1"
},
{
  "slug": "color2", "color": "#1559ed", "name": "Color 2"  
},
{
  "slug": "color3", "color": "#3A4F66", "name": "Color 3"
},
{
  "slug": "color4", "color": "#192a3d", "name": "Color 4"
},
{
  "slug": "color5", "color": "#ffffff", "name": "Color 5"
}
```

#### **Semantic Colors (WordPress Core Replacements):**
```json
{
  "slug": "primary-light", "color": "#d6dcd6", "name": "Primary Light"
},
{
  "slug": "primary", "color": "#5a7b7c", "name": "Primary"
},
{
  "slug": "primary-dark", "color": "#3a5a59", "name": "Primary Dark"
},
{
  "slug": "secondary-light", "color": "#2c2c2c", "name": "Secondary Light"
},
{
  "slug": "secondary", "color": "#975d55", "name": "Secondary"
},
{
  "slug": "secondary-dark", "color": "#853d2d", "name": "Secondary Dark"
},
{
  "slug": "neutral-light", "color": "#d8d6cf", "name": "Neutral Light"
},
{
  "slug": "neutral", "color": "#b3b09f", "name": "Neutral"
},
{
  "slug": "neutral-dark", "color": "#8e897b", "name": "Neutral Dark"
},
{
  "slug": "base-lightest", "color": "#f0f0f0", "name": "Base Lightest"
},
{
  "slug": "base-light", "color": "#cacaca", "name": "Base Light"
},
{
  "slug": "base", "color": "#777777", "name": "Base"
},
{
  "slug": "base-dark", "color": "#606060", "name": "Base Dark"
},
{
  "slug": "base-darkest", "color": "#323232", "name": "Base Darkest"
},
{
  "slug": "extreme-light", "color": "#ffffff", "name": "Extreme Light"
},
{
  "slug": "extreme-dark", "color": "#000000", "name": "Extreme Dark"
}
```

## 🔧 **WordPress API Integration**

### **CSS Custom Properties Generated:**
WordPress automatically generates CSS custom properties from theme.json:

```css
:root {
  --wp--preset--color--color1: #2872fa;
  --wp--preset--color--color2: #1559ed;
  --wp--preset--color--primary: #5a7b7c;
  --wp--preset--color--primary-light: #d6dcd6;
  /* ... all colors become CSS variables */
}
```

### **Block Editor Integration:**
- Colors appear in **Block Editor color picker**
- **Gutenberg blocks** can use colors via dropdown
- **Custom blocks** can reference colors via CSS variables

### **Frontend Application:**
```css
/* Colors are available as CSS variables */
.my-element {
  background-color: var(--wp--preset--color--primary);
  color: var(--wp--preset--color--extreme-light);
}

/* Or via WordPress utility classes */
.has-primary-background-color {
  background-color: var(--wp--preset--color--primary);
}
```

## 🎯 **Blocksy Theme Compatibility**

### **Color Mapping:**
Blocksy expects colors named `color1`, `color2`, etc. Our theme.json provides:
- `color1` → Primary blue (#2872fa)
- `color2` → Secondary blue (#1559ed)  
- `color3` → Accent dark blue (#3A4F66)
- `color4` → Dark navy (#192a3d)
- `color5` → White (#ffffff)

### **Blocksy Integration Section:**
```json
"custom": {
  "blocksyIntegration": {
    "version": "1.0.0",
    "maxSiteWidth": "var(--theme-block-max-width, 1290px)",
    "contentAreaSpacing": "var(--theme-content-spacing, 60px)",
    "colorPalette": {
      "color1": "#2872fa",
      "color2": "#1559ed", 
      "color3": "#3A4F66",
      "color4": "#192a3d",
      "color5": "#ffffff"
    }
  }
}
```

## 📐 **Complete Design System**

### **Typography System:**
```json
"typography": {
  "fontFamilies": [
    {
      "fontFamily": "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif",
      "slug": "system-font",
      "name": "System Font"
    },
    {
      "fontFamily": "Georgia, serif", 
      "slug": "serif",
      "name": "Serif"
    },
    {
      "fontFamily": "'Courier New', Courier, monospace",
      "slug": "monospace", 
      "name": "Monospace"
    }
  ],
  "fontSizes": [
    { "size": "13px", "slug": "small", "name": "Small" },
    { "size": "16px", "slug": "base", "name": "Base" },
    { "size": "20px", "slug": "medium", "name": "Medium" },
    { "size": "30px", "slug": "large", "name": "Large" },
    { "size": "42px", "slug": "x-large", "name": "Extra Large" },
    { "size": "80px", "slug": "xx-large", "name": "Extra Extra Large" }
  ]
}
```

### **Spacing System:**
```json
"spacing": {
  "spacingSizes": [
    { "size": "0.5rem", "slug": "30", "name": "1" },
    { "size": "1rem", "slug": "40", "name": "2" },
    { "size": "1.5rem", "slug": "50", "name": "3" },
    { "size": "2rem", "slug": "60", "name": "4" },
    { "size": "2.5rem", "slug": "70", "name": "5" },
    { "size": "3rem", "slug": "80", "name": "6" },
    { "size": "4rem", "slug": "90", "name": "7" },
    { "size": "5rem", "slug": "100", "name": "8" }
  ]
}
```

### **Layout System:**
```json
"layout": {
  "contentSize": "var(--theme-block-max-width, 620px)",
  "wideSize": "var(--theme-block-wide-max-width, 1280px)",
  "fullSize": "none"
}
```

## 🔄 **Studio Integration Workflow**

### **Data Flow:**
```
Studio Plugin (studio.json)
     ↓ (manual sync)
Child Theme (theme.json)
     ↓ (WordPress API)
Frontend CSS Variables
     ↓ (applied to)
Blocksy Theme + All Blocks
```

### **Sync Process:**
1. **Edit colors** in Studio plugin interface
2. **Save changes** to `studio.json` automatically
3. **Click "Save to theme.json"** to sync to WordPress
4. **WordPress API** automatically generates CSS variables
5. **Blocksy theme** uses updated colors immediately

## 🎨 **Element Styling Examples**

### **Global Styles:**
```json
"styles": {
  "color": {
    "background": "var(--wp--preset--color--white)",
    "text": "var(--wp--preset--color--black)"
  },
  "elements": {
    "link": {
      "color": { "text": "var(--wp--preset--color--color1)" },
      ":hover": { "color": { "text": "var(--wp--preset--color--color2)" }}
    },
    "button": {
      "color": {
        "background": "var(--wp--preset--color--color1)",
        "text": "var(--wp--preset--color--white)"
      },
      ":hover": {
        "color": { "background": "var(--wp--preset--color--color2)" }
      }
    }
  }
}
```

## 🚀 **Recreation Instructions**

To recreate this exact setup:

### **1. Create Child Theme:**
```php
// style.css
/*
Theme Name: Blocksy Child
Template: blocksy
Version: 1.0.0
*/
```

### **2. Install Studio Plugin:**
- Place DS-STUDIO plugin in `/wp-content/plugins/`
- Activate plugin
- Colors will be managed via Studio interface

### **3. Create theme.json:**
- Copy the complete `theme.json` structure
- Ensure all override flags are set to `false`
- Include complete color palette (20 colors)
- Include typography, spacing, and layout systems
- Add Blocksy integration section

### **4. Key Configuration:**
```json
{
  "version": 2,
  "settings": {
    "color": {
      "defaultPalette": false,    // CRITICAL: Disables WP core
      "defaultGradients": false,  // CRITICAL: Disables WP gradients  
      "defaultDuotone": false     // CRITICAL: Disables WP duotones
    }
  }
}
```

### **5. Verification:**
- Block editor should show only your 20 custom colors
- No WordPress core colors should appear
- Blocksy theme should use color1-5 correctly
- Studio plugin should sync colors to theme.json

## 🎯 **Expected Behavior**

### **✅ What Works:**
- **Complete color override** - Only custom colors appear
- **Studio integration** - Colors sync from Studio to theme.json
- **Blocksy compatibility** - Theme uses color1-5 system
- **WordPress API** - All colors available as CSS variables
- **Block editor** - Custom colors in color picker
- **Frontend styling** - Colors applied via CSS variables

### **❌ What's Disabled:**
- WordPress core color palette
- WordPress default gradients
- WordPress default duotones
- Blocksy's built-in color system (replaced)

This setup provides a **complete design token system** that works seamlessly with WordPress, Blocksy, and The Studio plugin while maintaining full control over the color palette.
