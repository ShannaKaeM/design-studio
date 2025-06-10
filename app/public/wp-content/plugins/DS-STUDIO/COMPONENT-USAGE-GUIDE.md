# DS-Studio Component Library Usage Guide

## 🎯 **What This Solves**

Instead of writing custom CSS for every component, you can now create **reusable utility class combinations** that work like pre-built components.

## 📦 **Pre-Built Components**

Your DS-Studio now includes these ready-to-use components:

### **Layout Components**
- `card` - Standard card with shadow and padding
- `hero-section` - Large hero section with gradient
- `content-container` - Standard content container
- `flex-center` - Flexbox centered content
- `grid-3-col` - Three-column responsive grid

### **Interactive Components**
- `button-primary` - Main call-to-action button
- `button-secondary` - Secondary action button

### **Typography Components**
- `text-heading` - Large page heading
- `text-subheading` - Section heading
- `text-body` - Standard body text

### **Form Components**
- `form-input` - Styled form input field

### **Feedback Components**
- `alert-success` - Success message alert
- `alert-error` - Error message alert

## 🚀 **Usage Methods**

### **Method 1: Direct Class Usage**
```html
<!-- Traditional way -->
<div class="bg-white rounded-lg shadow-md p-lg border border-gray-200">
  Card content
</div>

<!-- Component way -->
<div class="<?php ds_component_class('card'); ?>">
  Card content
</div>
```

### **Method 2: Helper Functions**
```php
// Simple button
echo ds_button('Click Me', '/contact', 'primary');

// Card with image
echo ds_card(
    'Card Title',
    'Card description content here.',
    'https://example.com/image.jpg'
);

// Alert message
echo ds_alert('Success! Your changes have been saved.', 'success');

// Grid layout
echo ds_grid([
    ds_card('Card 1', 'Content 1'),
    ds_card('Card 2', 'Content 2'),
    ds_card('Card 3', 'Content 3')
], '3-col');
```

### **Method 3: Shortcodes**
```html
[ds_component name="card" class="extra-class"]
  <h3>Card Title</h3>
  <p>Card content goes here.</p>
[/ds_component]
```

### **Method 4: Template Combinations**
```php
// Combine multiple components
$classes = ds_components(['card', 'flex-center'], 'custom-class');
echo '<div class="' . $classes . '">Combined component</div>';

// Wrap content in component
echo ds_component_wrap('hero-section', '<h1>Welcome to Our Site</h1>');
```

## 🎨 **Real-World Examples**

### **Homepage Hero Section**
```php
echo ds_section(
    '<h1 class="' . ds_component('text-heading') . '">Welcome to Our Site</h1>
     <p class="' . ds_component('text-body') . '">Your journey starts here.</p>
     ' . ds_button('Get Started', '/signup', 'primary'),
    'wide',
    ['class' => 'bg-gradient-to-r from-primary to-secondary text-white']
);
```

### **Product Grid**
```php
$products = [
    ds_card('Product 1', 'Description 1', '/images/product1.jpg'),
    ds_card('Product 2', 'Description 2', '/images/product2.jpg'),
    ds_card('Product 3', 'Description 3', '/images/product3.jpg'),
];

echo ds_section(
    '<h2 class="' . ds_component('text-heading') . '">Our Products</h2>' .
    ds_grid($products, '3-col'),
    'prose'
);
```

### **Contact Form**
```html
<form class="<?php ds_component_class('content-container'); ?>">
    <h2 class="<?php ds_component_class('text-heading'); ?>">Contact Us</h2>
    
    <input type="text" 
           class="<?php ds_component_class('form-input'); ?>" 
           placeholder="Your Name">
    
    <input type="email" 
           class="<?php ds_component_class('form-input'); ?>" 
           placeholder="Your Email">
    
    <textarea class="<?php ds_component_class('form-input'); ?>" 
              placeholder="Your Message"></textarea>
    
    <?php echo ds_button('Send Message', '#', 'primary', ['type' => 'submit']); ?>
</form>
```

### **Blog Post Layout**
```php
echo ds_section('
    <article>
        <h1 class="' . ds_component('text-heading') . '">' . get_the_title() . '</h1>
        <div class="' . ds_component('text-body') . '">' . get_the_content() . '</div>
        
        <div class="' . ds_component('flex-center') . '">
            ' . ds_button('Previous Post', get_previous_post_link(), 'secondary') . '
            ' . ds_button('Next Post', get_next_post_link(), 'primary') . '
        </div>
    </article>
', 'prose');
```

## ✨ **Benefits**

### **Before (Traditional CSS)**
```css
/* style.css */
.my-card {
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    padding: 2rem;
    border: 1px solid #e5e7eb;
}

.my-button {
    background: var(--primary-color);
    color: white;
    padding: 1rem 2rem;
    border-radius: 0.375rem;
    font-weight: 500;
}
```

```html
<div class="my-card">
    <h3>Card Title</h3>
    <p>Content</p>
    <button class="my-button">Action</button>
</div>
```

### **After (Component System)**
```php
echo ds_card(
    'Card Title',
    '<p>Content</p>' . ds_button('Action', '#', 'primary')
);
```

## 🔧 **Creating Custom Components**

You can add your own components by modifying the `get_default_components()` method in `class-component-library.php`:

```php
'my-custom-card' => array(
    'name' => 'Custom Card',
    'description' => 'Special card with gradient background',
    'classes' => 'bg-gradient-to-br from-purple-400 to-pink-400 text-white rounded-xl p-xl shadow-lg',
    'category' => 'custom',
    'preview' => '<div class="bg-gradient-to-br from-purple-400 to-pink-400 text-white rounded-xl p-xl shadow-lg">Custom Card</div>'
)
```

Then use it:
```php
echo '<div class="' . ds_component('my-custom-card') . '">Custom content</div>';
```

## 🎯 **Key Advantages**

1. **Consistency** - All components use your design system tokens
2. **Maintainability** - Change the component definition once, updates everywhere
3. **Speed** - No custom CSS writing needed
4. **Flexibility** - Mix and match components and utilities
5. **Performance** - Pure CSS, no JavaScript overhead
6. **Familiarity** - Uses utility classes developers already know

This gives you the best of both worlds: **component-like reusability** with **utility-first flexibility**! 🚀
