# Studio Components - Usage Guide

## How Studio Selectors Create Reusable Components

Your Studio system creates reusable components through **CSS classes** that use your design system variables. Here's how it works:

## 🎯 **The System Explained**

### **1. CSS Variables (Design Tokens)**
```css
/* s-vars.css - Your single source of truth */
--s-primary: #0066cc;
--s-space-md: 1rem;
--s-radius-1: 0.25rem;
```

### **2. Component Classes** 
```css
/* s-selectors.css - Components using variables */
.s-btn {
  padding: var(--s-space-3) var(--s-space-6);
  background: var(--s-primary);
  border-radius: var(--s-radius-1);
}
```

### **3. Utility Classes**
```css
/* s-utilities.css - Auto-generated from variables */
.text-primary { color: var(--s-primary); }
.p-md { padding: var(--s-space-md); }
.rounded { border-radius: var(--s-radius-1); }
```

## 📝 **How to Use Components**

### **In WordPress Block Editor:**

1. **Add any block** (paragraph, heading, group, etc.)
2. **Add CSS classes** in the Advanced panel
3. **Mix component + utility classes**

```html
<!-- Example: Button using Group block -->
<div class="wp-block-group s-btn s-btn--primary">
  Get Started
</div>

<!-- Example: Card using Group block -->
<div class="wp-block-group s-card">
  <h3 class="s-card__title">My Title</h3>
  <p class="s-card__content">My content here</p>
</div>
```

### **Available Components:**

#### **Buttons**
```html
<!-- Basic button -->
<button class="s-btn s-btn--primary">Click Me</button>

<!-- Button variants -->
<button class="s-btn s-btn--secondary">Secondary</button>
<button class="s-btn s-btn--outline">Outline</button>

<!-- Button sizes -->
<button class="s-btn s-btn--primary s-btn--small">Small</button>
<button class="s-btn s-btn--primary s-btn--large">Large</button>
```

#### **Cards**
```html
<div class="s-card">
  <div class="s-card__header">
    <h3 class="s-card__title">Card Title</h3>
  </div>
  <div class="s-card__content">
    <p>Card content goes here</p>
  </div>
  <div class="s-card__footer">
    <button class="s-btn s-btn--primary s-btn--small">Action</button>
  </div>
</div>
```

#### **Hero Sections**
```html
<section class="s-hero">
  <h1 class="s-hero__title">Main Heading</h1>
  <p class="s-hero__subtitle">Subtitle text</p>
</section>
```

#### **Forms**
```html
<input type="text" class="s-input" placeholder="Your input">
<input type="email" class="s-input" placeholder="Email">
<textarea class="s-input" placeholder="Message"></textarea>
```

### **Combine with Utilities:**

```html
<!-- Card with utility spacing -->
<div class="s-card mb-xl">
  <h3 class="s-card__title text-primary">Special Title</h3>
  <p class="s-card__content text-base-dark">Content with custom color</p>
</div>

<!-- Button with utility spacing -->
<button class="s-btn s-btn--primary mt-lg">Spaced Button</button>

<!-- Flex layout with gap -->
<div class="flex gap-md">
  <button class="s-btn s-btn--primary">Button 1</button>
  <button class="s-btn s-btn--outline">Button 2</button>
</div>
```

## 🔧 **Creating Custom Components**

### **Method 1: Add to s-selectors.css**
```css
/* Add your own component */
.my-custom-component {
  background: var(--s-base-lighter);
  padding: var(--s-space-lg);
  border-radius: var(--s-radius-2);
  border: 1px solid var(--s-base-light);
}

.my-custom-component__title {
  color: var(--s-primary);
  font-size: var(--s-text-xl);
  margin-bottom: var(--s-space-md);
}
```

### **Method 2: Use Studio Admin Interface**
1. Go to **WordPress Admin → Studio → Selectors**
2. Add new selector rule
3. Apply variables to any CSS selector
4. Save and use immediately

## 🎨 **Styling Tips**

### **Component Variants**
Use BEM methodology for variants:
```css
.s-btn { /* base */ }
.s-btn--primary { /* variant */ }
.s-btn--large { /* size modifier */ }
```

### **Responsive Design**
```html
<!-- Responsive grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-lg">
  <div class="s-card">Card 1</div>
  <div class="s-card">Card 2</div>
  <div class="s-card">Card 3</div>
</div>
```

### **Color Customization**
```html
<!-- Override component colors with utilities -->
<button class="s-btn bg-success text-base-lightest">Success Button</button>
<div class="s-card border-primary">Primary Border Card</div>
```

## 🚀 **Benefits**

1. **Consistent Design** - All components use the same variables
2. **Easy Updates** - Change `--s-primary` and all components update
3. **Mix & Match** - Combine components with utilities
4. **WordPress Compatible** - Works with any block or theme
5. **No JavaScript** - Pure CSS solution

## 📋 **Quick Reference**

### **Available Utility Prefixes:**
- `text-` - Colors (text-primary, text-base-dark)
- `bg-` - Backgrounds (bg-primary, bg-base-lighter) 
- `p-`, `m-` - Spacing (p-md, m-lg)
- `text-` - Typography (text-xl, font-bold)
- `flex`, `grid` - Layout
- `rounded` - Border radius
- `shadow-` - Box shadows

### **Component Naming:**
- `.s-btn` - Buttons
- `.s-card` - Cards  
- `.s-hero` - Hero sections
- `.s-input` - Form inputs

**Change variables in `s-vars.css` → All components update automatically!**