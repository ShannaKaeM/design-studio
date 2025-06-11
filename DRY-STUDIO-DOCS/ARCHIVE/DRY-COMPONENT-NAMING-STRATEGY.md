# 🏷️ DRY Component Naming Strategy

## 🎯 **Naming Philosophy**

Our naming strategy follows **semantic, hierarchical patterns** that make components instantly recognizable, easy to organize, and scalable for large projects.

---

## 📋 **Core Naming Conventions**

### **1. Template Naming Pattern**
```
{category}-{type}-{variant}
```

**Examples:**
- `hero-section-centered`
- `card-grid-3col`
- `testimonial-carousel-fade`
- `cta-banner-fullwidth`

### **2. Content Naming Pattern**
```
{page/context}-{component}-{purpose}
```

**Examples:**
- `homepage-hero-main`
- `about-team-leadership`
- `services-cta-primary`
- `product-testimonials-featured`

---

## 🗂️ **Category Structure**

### **Layout Components**
- **hero-** (Hero sections, banners, headers)
- **section-** (Generic content sections)
- **container-** (Wrapper components)
- **grid-** (Grid layouts, columns)

### **Content Components**
- **card-** (Card-based layouts)
- **list-** (List components)
- **media-** (Image/video components)
- **text-** (Typography-focused components)

### **Interactive Components**
- **form-** (Forms, inputs, contact)
- **nav-** (Navigation, menus)
- **button-** (Buttons, CTAs)
- **modal-** (Popups, overlays)

### **E-commerce Components**
- **product-** (Product displays)
- **shop-** (Shopping interfaces)
- **cart-** (Cart functionality)
- **checkout-** (Checkout process)

---

## 🎨 **Template Examples**

### **Hero Sections**
```
hero-section-centered     // Centered text hero
hero-section-split        // Split layout hero
hero-section-video        // Video background hero
hero-section-minimal      // Minimal text hero
hero-section-fullscreen   // Full viewport hero
```

### **Card Components**
```
card-grid-2col           // 2-column card grid
card-grid-3col           // 3-column card grid
card-grid-masonry        // Masonry layout cards
card-list-horizontal     // Horizontal card list
card-single-featured     // Single featured card
```

### **Navigation**
```
nav-header-primary       // Main site navigation
nav-footer-links         // Footer navigation
nav-sidebar-menu         // Sidebar navigation
nav-breadcrumb-trail     // Breadcrumb navigation
nav-pagination-numeric   // Numeric pagination
```

---

## 📄 **Content Examples**

### **Homepage Content**
```
homepage-hero-main       // Main homepage hero
homepage-services-intro  // Services introduction
homepage-testimonials-featured  // Featured testimonials
homepage-cta-primary     // Primary call-to-action
```

### **Product Content**
```
product-hero-main        // Product page hero
product-features-list    // Product features
product-testimonials-reviews  // Customer reviews
product-cta-purchase     // Purchase call-to-action
```

### **About Content**
```
about-hero-company       // Company introduction
about-team-leadership    // Leadership team
about-story-timeline     // Company timeline
about-values-mission     // Mission & values
```

---

## 🔧 **File Structure**

### **Templates Directory**
```
templates/
├── hero/
│   ├── hero-section-centered.json
│   ├── hero-section-split.json
│   └── hero-section-video.json
├── cards/
│   ├── card-grid-2col.json
│   ├── card-grid-3col.json
│   └── card-list-horizontal.json
└── navigation/
    ├── nav-header-primary.json
    └── nav-footer-links.json
```

### **Content Directory**
```
content/
├── homepage/
│   ├── homepage-hero-main.json
│   ├── homepage-services-intro.json
│   └── homepage-cta-primary.json
├── about/
│   ├── about-hero-company.json
│   ├── about-team-leadership.json
│   └── about-story-timeline.json
└── products/
    ├── product-hero-main.json
    └── product-features-list.json
```

---

## 🎯 **Semantic Token Naming**

### **Component-Specific Tokens**
```json
{
  "semanticTokens": {
    "hero": {
      "titleFontSize": "var(--wp--preset--font-size--x-large)",
      "titleColor": "var(--wp--preset--color--foreground)",
      "subtitleFontSize": "var(--wp--preset--font-size--medium)",
      "padding": "clamp(2rem, 5vw, 4rem)"
    },
    "card": {
      "titleFontSize": "var(--wp--preset--font-size--large)",
      "bodyFontSize": "var(--wp--preset--font-size--small)",
      "backgroundColor": "var(--wp--preset--color--base)",
      "borderRadius": "var(--wp--preset--spacing--20)"
    }
  }
}
```

---

## 🚀 **Block Registration Naming**

### **Block Names**
```javascript
// WordPress block registration
"dry-studio/hero-section"
"dry-studio/card-grid"
"dry-studio/testimonial-carousel"
"dry-studio/cta-banner"
```

### **Block Attributes**
```json
{
  "attributes": {
    "templateId": {
      "type": "string",
      "default": "hero-section-centered"
    },
    "contentId": {
      "type": "string", 
      "default": "homepage-hero-main"
    }
  }
}
```

---

## 📊 **Usage Examples**

### **Component Implementation**
```php
// Template function usage
dry_component('hero-section-centered', 'homepage-hero-main');

// Shortcode usage
[dry-component template="card-grid-3col" content="services-overview-main"]

// Block editor usage
<DRYComponent 
  templateId="testimonial-carousel-fade"
  contentId="homepage-testimonials-featured"
/>
```

### **Content Structure**
```json
// homepage-hero-main.json
{
  "title": "Welcome to Our Amazing Service",
  "subtitle": "Transform your business with our innovative solutions",
  "buttonText": "Get Started Today",
  "buttonUrl": "/contact",
  "backgroundImage": "/images/hero-bg.jpg"
}
```

---

## 🎯 **Benefits of This Strategy**

### **✅ Clarity & Organization**
- **Instant recognition** of component type and purpose
- **Logical grouping** by category and function
- **Scalable structure** that grows with your project

### **✅ Developer Experience**
- **Predictable naming** reduces cognitive load
- **Easy autocomplete** in IDEs and editors
- **Clear file organization** for large projects

### **✅ Content Management**
- **Intuitive content selection** for non-technical users
- **Clear relationships** between templates and content
- **Easy content reuse** across different contexts

### **✅ Maintenance**
- **Easy refactoring** with consistent patterns
- **Clear dependencies** between components
- **Version control friendly** with descriptive names

---

## 🔮 **Future Considerations**

### **Versioning Strategy**
```
hero-section-centered-v2
card-grid-3col-modern
testimonial-carousel-enhanced
```

### **Client-Specific Variants**
```
hero-section-centered-travel
card-grid-3col-ecommerce
testimonial-carousel-saas
```

### **Responsive Variants**
```
hero-section-centered-mobile
card-grid-responsive-stack
nav-header-mobile-drawer
```

---

**This naming strategy creates a solid foundation for scalable, maintainable DRY components that are easy to understand and use!** 🚀
