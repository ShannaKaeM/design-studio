# 🧱 Block & Block Pattern Naming Strategy

## 🎯 **Hierarchical Block Structure**

Our naming follows a **semantic hierarchy** that mirrors how content is actually structured on web pages.

---

## 📋 **Core Block Categories**

### **1. Section-Level Blocks (Page Structure)**
```
section-hero
section-features  
section-testimonials
section-cta
section-content
section-footer
```

### **2. Component-Level Blocks (Within Sections)**
```
section-header
section-content
section-body
section-footer
```

### **3. Element-Level Blocks (Within Components)**
```
hero-title
hero-subtitle
hero-button
feature-icon
feature-title
feature-description
```

---

## 🏗️ **Section-Based Block Patterns**

### **Hero Patterns**
```
hero-centered-text       // Centered text hero
hero-split-content       // Split layout with image
hero-video-background    // Video background hero
hero-minimal-text        // Minimal text-only hero
hero-product-showcase    // Product-focused hero
```

### **Features Patterns**
```
features-3-column        // 3-column feature grid
features-2-column        // 2-column feature layout
features-list-vertical   // Vertical feature list
features-icon-cards      // Icon-based feature cards
features-alternating     // Alternating left/right layout
```

### **Content Patterns**
```
content-text-image       // Text with side image
content-image-gallery    // Image gallery section
content-quote-highlight  // Featured quote section
content-stats-numbers    // Statistics/numbers section
content-timeline         // Timeline/process section
```

### **Testimonials Patterns**
```
testimonials-carousel    // Sliding testimonials
testimonials-grid-3      // 3-column testimonial grid
testimonials-single      // Single featured testimonial
testimonials-list        // Simple testimonial list
testimonials-video       // Video testimonials
```

### **CTA Patterns**
```
cta-banner-full          // Full-width banner CTA
cta-centered-box         // Centered box CTA
cta-split-content        // Split CTA with content
cta-newsletter-signup    // Newsletter signup CTA
cta-contact-form         // Contact form CTA
```

---

## 🧩 **Component Hierarchy Structure**

### **Section Components**
```
section-wrapper          // Main section container
├── section-header       // Section title/subtitle area
├── section-content      // Main content area
│   ├── section-body     // Primary content
│   └── section-aside    // Secondary content
└── section-footer       // Section bottom/CTA area
```

### **Hero Components**
```
hero-section
├── hero-header
│   ├── hero-title
│   ├── hero-subtitle
│   └── hero-badge
├── hero-content
│   ├── hero-description
│   ├── hero-features
│   └── hero-media
└── hero-footer
    ├── hero-buttons
    └── hero-social
```

### **Feature Components**
```
features-section
├── features-header
│   ├── features-title
│   └── features-subtitle
├── features-content
│   ├── feature-item
│   │   ├── feature-icon
│   │   ├── feature-title
│   │   └── feature-description
│   └── feature-grid
└── features-footer
    └── features-cta
```

---

## 🏷️ **Block Registration Names**

### **WordPress Block Names**
```javascript
// Section-level blocks
"dry-studio/hero-section"
"dry-studio/features-section"
"dry-studio/testimonials-section"
"dry-studio/cta-section"
"dry-studio/content-section"

// Component-level blocks
"dry-studio/section-header"
"dry-studio/section-content"
"dry-studio/section-footer"

// Element-level blocks
"dry-studio/hero-title"
"dry-studio/feature-item"
"dry-studio/testimonial-card"
```

---

## 📁 **File Organization**

### **Block Pattern Files**
```
patterns/
├── hero/
│   ├── hero-centered-text.php
│   ├── hero-split-content.php
│   └── hero-video-background.php
├── features/
│   ├── features-3-column.php
│   ├── features-icon-cards.php
│   └── features-alternating.php
├── testimonials/
│   ├── testimonials-carousel.php
│   ├── testimonials-grid-3.php
│   └── testimonials-single.php
└── cta/
    ├── cta-banner-full.php
    ├── cta-centered-box.php
    └── cta-newsletter-signup.php
```

### **Template Files (DRY System)**
```
templates/
├── sections/
│   ├── hero-section-centered.json
│   ├── features-section-3col.json
│   └── testimonials-section-carousel.json
├── components/
│   ├── section-header-standard.json
│   ├── section-content-split.json
│   └── section-footer-cta.json
└── elements/
    ├── hero-title-large.json
    ├── feature-item-icon.json
    └── testimonial-card-quote.json
```

---

## 🎨 **Pattern Registration**

### **Block Pattern Categories**
```php
// WordPress pattern categories
register_block_pattern_category('dry-studio-hero', [
    'label' => 'Hero Sections'
]);

register_block_pattern_category('dry-studio-features', [
    'label' => 'Features'
]);

register_block_pattern_category('dry-studio-testimonials', [
    'label' => 'Testimonials'
]);

register_block_pattern_category('dry-studio-cta', [
    'label' => 'Call to Action'
]);
```

### **Pattern Registration Example**
```php
register_block_pattern('dry-studio/hero-centered-text', [
    'title'       => 'Hero: Centered Text',
    'description' => 'Centered hero section with title, subtitle, and button',
    'categories'  => ['dry-studio-hero'],
    'content'     => '<!-- wp:dry-studio/hero-section {"templateId":"hero-section-centered","contentId":"homepage-hero-main"} /-->'
]);
```

---

## 🔧 **Props Integration**

### **Section-Level Props**
```json
// hero-section-centered.json (template)
{
  "structure": {
    "section": {
      "class": "hero-section hero-centered",
      "components": [
        {
          "type": "section-header",
          "template": "hero-header-standard"
        },
        {
          "type": "section-content", 
          "template": "hero-content-centered"
        },
        {
          "type": "section-footer",
          "template": "hero-footer-buttons"
        }
      ]
    }
  }
}

// homepage-hero-main.json (content)
{
  "header": {
    "title": "Welcome to Our Service",
    "subtitle": "Transform your business today"
  },
  "content": {
    "description": "Our innovative solution helps...",
    "image": "/images/hero-image.jpg"
  },
  "footer": {
    "primaryButton": {
      "text": "Get Started",
      "url": "/signup"
    },
    "secondaryButton": {
      "text": "Learn More", 
      "url": "/about"
    }
  }
}
```

---

## 🎯 **Usage Examples**

### **Block Editor Interface**
```
Pattern Inserter:
├── Hero Sections
│   ├── Hero: Centered Text
│   ├── Hero: Split Content
│   └── Hero: Video Background
├── Features
│   ├── Features: 3 Column
│   ├── Features: Icon Cards
│   └── Features: Alternating
└── Testimonials
    ├── Testimonials: Carousel
    ├── Testimonials: Grid
    └── Testimonials: Single Featured
```

### **Block Inspector Controls**
```
Selected Block: Hero Section
├── Template: [Dropdown]
│   ├── hero-section-centered
│   ├── hero-section-split
│   └── hero-section-video
└── Content: [Dropdown]
    ├── homepage-hero-main
    ├── about-hero-company
    └── services-hero-intro
```

---

## 🚀 **Benefits of This Structure**

### **✅ Semantic Clarity**
- **Intuitive hierarchy** matches mental model of page structure
- **Clear relationships** between sections, components, and elements
- **Predictable naming** reduces cognitive load

### **✅ WordPress Native**
- **Standard pattern categories** for familiar user experience
- **Block editor integration** with proper hierarchy
- **Theme compatibility** with existing WordPress patterns

### **✅ Scalable Architecture**
- **Modular components** can be mixed and matched
- **Consistent structure** across all patterns
- **Easy expansion** for new pattern types

### **✅ Developer Efficiency**
- **Clear file organization** for easy maintenance
- **Reusable components** reduce duplication
- **Props system integration** for dynamic content

---

**This hierarchical naming strategy creates a solid foundation for scalable, maintainable block patterns that feel native to WordPress!** 🚀
