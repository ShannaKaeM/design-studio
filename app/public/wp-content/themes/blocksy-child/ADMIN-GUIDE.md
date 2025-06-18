# Studio Components - Admin Management Guide

## 🎯 **How Components Work in the Selector System**

Your Studio system creates components through the **Selector Builder** - components are just CSS selectors with design system variables applied to them.

## 📍 **Accessing Component Management**

### **WordPress Admin Path:**
```
WordPress Admin → Appearance → S System → Selectors Tab
```

## 🔧 **Component Management Interface**

When you visit the Selectors tab, you'll see:

### **1. Add New Selector Form**
- **Rule Name**: Name your component (e.g., "Button Primary")
- **CSS Selector**: The selector (e.g., `.s-btn--primary`)  
- **Preset Dropdown**: Quick component presets including:
  - `button-base` → `.s-btn`
  - `button-primary` → `.s-btn--primary`
  - `card-base` → `.s-card`
  - `hero-section` → `.s-hero`
- **Properties**: Add CSS properties with variable values

### **2. Existing Selectors List**
Shows all your components with:
- **Name** and **Selector**
- **Properties** applied
- **Toggle** (enable/disable)
- **Edit Button** (to modify)
- **Delete Button**

## 🚀 **Auto-Initialized Components**

When you first load the Selectors page, these components are automatically created:

### **Button Components:**
- **Button Base** (`.s-btn`) - Core button styling
- **Button Primary** (`.s-btn--primary`) - Primary color variant
- **Button Secondary** (`.s-btn--secondary`) - Secondary color variant  
- **Button Outline** (`.s-btn--outline`) - Outline style variant
- **Button Small/Large** (`.s-btn--small`, `.s-btn--large`) - Size variants
- **Button Hover States** - Hover effects

### **Card Components:**
- **Card Base** (`.s-card`) - Core card styling
- **Card Title** (`.s-card__title`) - Card header styling
- **Card Content** (`.s-card__content`) - Card body styling

## ✏️ **Editing Components**

### **To Edit a Component:**
1. Go to **WordPress Admin → Appearance → S System → Selectors**
2. Find your component in the list
3. Click **Edit** button
4. Modify properties:
   - Change colors: `var(--s-primary)` → `var(--s-secondary)`
   - Adjust spacing: `var(--s-space-4)` → `var(--s-space-6)`
   - Update any CSS property
5. Click **Save**
6. CSS is automatically regenerated!

### **Adding New Components:**
1. **Rule Name**: "My Custom Button"
2. **CSS Selector**: `.my-btn`
3. **Add Properties**:
   ```
   background-color: var(--s-primary)
   padding: var(--s-space-3)
   border-radius: var(--s-radius-1)
   ```
4. **Save** - Now use `.my-btn` in WordPress!

## 🎨 **Using Design Variables**

Your components should use variables from `s-vars.css`:

### **Colors:**
```css
color: var(--s-primary)
background-color: var(--s-base-lightest)
border-color: var(--s-neutral)
```

### **Spacing:**
```css
padding: var(--s-space-md)
margin: var(--s-space-lg)
gap: var(--s-space-sm)
```

### **Typography:**
```css
font-size: var(--s-text-lg)
font-weight: var(--s-font-semibold)
line-height: var(--s-leading-relaxed)
```

### **Other Properties:**
```css
border-radius: var(--s-radius-1)
box-shadow: var(--s-shadow-md)
```

## 💡 **Component Strategy**

### **Base + Modifiers Pattern:**
```css
/* Base component */
.s-btn { /* core styles */ }

/* Modifiers */
.s-btn--primary { /* color variant */ }
.s-btn--large { /* size variant */ }

/* Usage */
<button class="s-btn s-btn--primary s-btn--large">
```

### **BEM Methodology:**
```css
.s-card { /* Block */ }
.s-card__title { /* Element */ }
.s-card--featured { /* Modifier */ }
```

## 🔄 **CSS Generation Process**

1. **Edit in Admin** → Properties saved to database
2. **Auto-Generation** → CSS written to `s-selectors.css`
3. **WordPress Loads** → CSS applied to frontend
4. **Components Work** → Use classes in blocks/HTML

## 🎯 **Benefits of This System**

### **For You:**
- **Visual Interface** - No manual CSS editing
- **Variable Integration** - Components use design tokens
- **Live Updates** - Changes apply immediately
- **Organized** - All components in one place

### **For Your Workflow:**
- **Consistent Design** - All components use same variables
- **Easy Maintenance** - Change variable once, update everywhere
- **Scalable** - Add unlimited components
- **WordPress Native** - Works with any block or theme

## 🚀 **Quick Start**

1. **Visit**: WordPress Admin → Appearance → S System → Selectors
2. **See**: Auto-created button and card components
3. **Edit**: Click edit on "Button Primary"
4. **Change**: `background-color` to `var(--s-secondary)`
5. **Save**: See all primary buttons update!
6. **Use**: Add `s-btn s-btn--primary` class to any WordPress block

Your components are now manageable through the WordPress admin interface!