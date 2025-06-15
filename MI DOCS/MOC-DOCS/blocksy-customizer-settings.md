# Blocksy Theme Customizer Settings

**Complete reference for all Blocksy theme customizer options and settings**

*Generated: June 12, 2025*

---

## 📋 **Table of Contents**

1. [General Options](#general-options)
2. [Post Types](#post-types)
3. [WooCommerce](#woocommerce)
4. [Extensions](#extensions)
5. [Color System](#color-system)
6. [Typography System](#typography-system)
7. [Layout System](#layout-system)
8. [Integration Notes](#integration-notes)

---

## 🔧 **General Options**

### **General**
- **Site Layout & Structure**
- **Global Settings**
- **Content Area Configuration**

### **Header**
- **Header Builder**
- **Logo & Branding**
- **Navigation Menus**
- **Header Layouts**
- **Mobile Header**
- **Sticky Header Options**

### **Footer**
- **Footer Builder**
- **Footer Widgets**
- **Footer Layout**
- **Copyright Area**
- **Social Links**

### **Sidebar**
- **Sidebar Position**
- **Widget Areas**
- **Sidebar Styling**
- **Mobile Sidebar Behavior**

### **Colors**
- **Global Color Palette** (Primary system)
- **Color Picker Interface**
- **Predefined Color Schemes**
- **Custom Color Management**

### **Typography**
- **Base Font Settings**
- **Heading Typography (H1-H6)**
- **Font Family Management**
- **Font Weight & Style**
- **Line Height & Spacing**
- **Google Fonts Integration**

### **Performance**
- **Asset Loading**
- **Font Loading Optimization**
- **CSS/JS Minification**
- **Caching Settings**

---

## 📝 **Post Types**

### **Blog Posts**
- **Archive Layout**
- **Post Grid/List Views**
- **Featured Images**
- **Excerpt Settings**
- **Read More Button**
- **Post Meta Display**

### **Single Post**
- **Post Layout**
- **Featured Image Display**
- **Author Box**
- **Related Posts**
- **Post Navigation**
- **Share Buttons**
- **Comments Section**

### **Categories**
- **Category Archive Layout**
- **Category Description**
- **Category Header**
- **Filtering Options**

### **Pages**
- **Page Layout**
- **Page Header**
- **Content Width**
- **Page Elements**

### **Author Page**
- **Author Bio Layout**
- **Author Posts Display**
- **Social Links**

### **Search Page**
- **Search Results Layout**
- **Search Form Styling**
- **No Results Page**

---

## 🛒 **WooCommerce** *(if active)*

### **General**
- **Shop Layout**
- **Product Display**
- **Cart & Checkout**
- **Account Pages**

### **Product Archives**
- **Shop Page Layout**
- **Product Grid**
- **Filtering & Sorting**
- **Pagination**

### **Single Product**
- **Product Page Layout**
- **Image Gallery**
- **Product Information**
- **Related Products**
- **Product Tabs**

---

## 🧩 **Extensions**

Blocksy supports various extensions that add additional customizer options:

- **Header Footer Builder**
- **White Label**
- **Cookies Consent**
- **Local Google Fonts**
- **Post Types Extra**
- **Social Sharing**
- **And more...**

---

## 🎨 **Color System Details**

### **Global Color Palette Structure**
```php
'colorPalette' => [
    'color1' => ['color' => '#2872fa'], // Primary
    'color2' => ['color' => '#1559ed'], // Secondary  
    'color3' => ['color' => '#3A4F66'], // Accent
    'color4' => ['color' => '#192a3d'], // Dark
    'color5' => ['color' => '#ffffff'], // Light
    // Additional colors...
]
```

### **Color Application**
- **Text Colors**
- **Background Colors**
- **Border Colors**
- **Link Colors**
- **Button Colors**
- **Accent Colors**

### **Color Inheritance**
- **CSS Custom Properties Generated**
- **Live Preview Support**
- **Theme.json Integration**

---

## ✍️ **Typography System Details**

### **Base Typography**
```php
'rootTypography' => [
    'family' => 'System Default',
    'variation' => 'n4',
    'size' => '16px',
    'line-height' => '1.65',
    'letter-spacing' => '0em',
    'text-transform' => 'none',
]
```

### **Heading Typography**
- **H1**: 40px, Bold (n7), 1.5 line-height
- **H2**: 35px, Bold (n7), 1.5 line-height
- **H3**: 30px, Bold (n7), 1.5 line-height
- **H4**: 25px, Bold (n7), 1.5 line-height
- **H5**: 20px, Bold (n7), 1.5 line-height
- **H6**: 16px, Bold (n7), 1.5 line-height

### **Typography Features**
- **Google Fonts Support**
- **Font Display Options**
- **Responsive Typography**
- **Font Loading Optimization**

---

## 📐 **Layout System Details**

### **Site Width & Spacing**
```php
'maxSiteWidth' => 1290, // Max site width (700-1900px)
'contentAreaSpacing' => [
    'desktop' => '60px',
    'tablet' => '60px', 
    'mobile' => '50px'
],
'contentEdgeSpacing' => [
    'desktop' => 5,
    'tablet' => 5,
    'mobile' => 6
]
```

### **Layout Options**
- **Container Widths**
- **Content Spacing**
- **Responsive Breakpoints**
- **Grid Systems**

---

## 🔗 **Integration Notes**

### **WordPress Integration**
- **Full Site Editing Support**
- **Block Editor Integration**
- **Theme.json Compatibility**
- **Customizer API Usage**

### **Plugin Compatibility**
- **WooCommerce Deep Integration**
- **Popular Page Builders**
- **SEO Plugins**
- **Performance Plugins**

### **Developer Features**
- **Hook System**
- **Filter System**
- **Custom Post Type Support**
- **Child Theme Support**

---

## 🎯 **Key Customizer Controls**

### **Control Types Used**
- `ct-color-palettes-picker` - Color palette management
- `ct-typography` - Typography controls
- `ct-slider` - Numeric sliders
- `ct-options` - Option groups
- `ct-panel` - Panel containers
- `ct-group-title` - Section headers

### **Transport Methods**
- `postMessage` - Live preview (most settings)
- `refresh` - Page refresh (some settings)

### **Responsive Controls**
Most spacing, typography, and layout controls support:
- **Desktop** settings
- **Tablet** settings  
- **Mobile** settings

---

## 📱 **Mobile Responsiveness**

### **Responsive Features**
- **Mobile-first approach**
- **Touch-friendly interfaces**
- **Responsive typography**
- **Mobile navigation**
- **Responsive spacing**

### **Breakpoints**
- **Mobile**: < 768px
- **Tablet**: 768px - 1024px
- **Desktop**: > 1024px

---

## 🚀 **Performance Considerations**

### **Optimization Features**
- **Selective asset loading**
- **Font display optimization**
- **CSS custom properties**
- **Minimal DOM manipulation**
- **Efficient customizer controls**

### **Best Practices**
- Use `postMessage` transport when possible
- Leverage CSS custom properties
- Minimize layout recalculations
- Optimize font loading

---

## 🔧 **Developer Notes**

### **Customizer Structure**
```php
$options = [
    'section_name' => [
        'type' => 'ct-panel',
        'label' => 'Section Label',
        'inner-options' => [
            // Individual controls
        ]
    ]
];
```

### **Adding Custom Options**
- Use Blocksy's option framework
- Follow naming conventions
- Implement proper sanitization
- Add live preview support

### **Hooks & Filters**
- `blocksy_extensions_customizer_options`
- `blocksy_customizer_options`
- Various control-specific filters

---

## 📚 **Resources**

- **Official Documentation**: [Blocksy Docs](https://creativethemes.com/blocksy/docs/)
- **Customizer API**: WordPress Customizer API
- **GitHub Repository**: Blocksy Theme Repository
- **Support Forums**: Blocksy Support

---

*This documentation covers the core Blocksy customizer structure. Individual installations may have additional options based on active plugins and extensions.*
