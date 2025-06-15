# 🧩 Studio Core Blocks Structure

## Block Development Reference
**GenerateBlocks Documentation:** [https://learn.generatepress.com/](https://learn.generatepress.com/)

---

## 📋 Block Development Checklist

### **For Each Block:**
- [ ] Create `block.json` with attributes
- [ ] Create `index.js` with React components
- [ ] Create `style.css` for frontend styles
- [ ] Create `editor.css` for editor styles
- [ ] Add to main plugin registration
- [ ] Test with Studio design tokens
- [ ] Verify theme.json integration

---

## 🎯 Priority Block Types

### **1. Studio Text Block** ✅ COMPLETE
**Location:** `/blocks/studio-text/`
**Purpose:** Typography with semantic control
**Status:** Working with Title/Subtitle/Body presets

**Features:**
- ✅ Typography preset selection
- ✅ HTML tag control (h1, h2, p, etc.)
- ✅ Studio design token integration
- ✅ WordPress inspector controls

---

### **2. Studio Headline Block** 📋 NEXT
**Location:** `/blocks/studio-headline/` (to create)
**Purpose:** Semantic headings (H1-H6) with hierarchy control
**GB Reference:** [Headline Block Docs](https://learn.generatepress.com/)

**Planned Features:**
- [ ] Heading level selection (H1-H6)
- [ ] Typography presets (Title, Subtitle, etc.)
- [ ] Semantic hierarchy enforcement
- [ ] Studio design token integration
- [ ] Accessibility features (proper heading structure)

**Attributes:**
```json
{
  "content": {
    "type": "string",
    "source": "html",
    "selector": "h1,h2,h3,h4,h5,h6"
  },
  "level": {
    "type": "number",
    "default": 2
  },
  "typographyPreset": {
    "type": "string",
    "default": "title"
  }
}
```

---

### **3. Studio Button Block** 📋 PLANNED
**Location:** `/blocks/studio-button/` (to create)
**Purpose:** Call-to-action elements with style presets
**GB Reference:** [Button Block Docs](https://learn.generatepress.com/)

**Planned Features:**
- [ ] Button style presets (Primary, Secondary, Outline)
- [ ] Hover state controls
- [ ] Icon integration
- [ ] Link management
- [ ] Size variations
- [ ] Studio design token integration

**Attributes:**
```json
{
  "text": {
    "type": "string",
    "source": "html",
    "selector": "a,button"
  },
  "url": {
    "type": "string",
    "source": "attribute",
    "selector": "a",
    "attribute": "href"
  },
  "buttonStyle": {
    "type": "string",
    "default": "primary"
  },
  "size": {
    "type": "string",
    "default": "medium"
  }
}
```

---

### **4. Studio Container Block** 📋 PLANNED
**Location:** `/blocks/studio-container/` (to create)
**Purpose:** Layout wrapper with spacing and background controls
**GB Reference:** [Container Block Docs](https://learn.generatepress.com/)

**Planned Features:**
- [ ] Width control (Full, Wide, Content)
- [ ] Padding presets (Small, Medium, Large)
- [ ] Background color/image
- [ ] Border and shadow controls
- [ ] Inner blocks support
- [ ] Studio design token integration

**Attributes:**
```json
{
  "width": {
    "type": "string",
    "default": "content"
  },
  "paddingPreset": {
    "type": "string",
    "default": "medium"
  },
  "backgroundColor": {
    "type": "string"
  },
  "backgroundImage": {
    "type": "object"
  }
}
```

---

### **5. Studio Grid Block** 📋 PLANNED
**Location:** `/blocks/studio-grid/` (to create)
**Purpose:** Responsive grid layouts with flexible columns
**GB Reference:** [Grid Block Docs](https://learn.generatepress.com/)

**Planned Features:**
- [ ] Column count control (1-6 columns)
- [ ] Gap presets (Small, Medium, Large)
- [ ] Responsive breakpoints
- [ ] Alignment controls
- [ ] Inner blocks support
- [ ] Studio design token integration

**Attributes:**
```json
{
  "columns": {
    "type": "number",
    "default": 2
  },
  "gap": {
    "type": "string",
    "default": "medium"
  },
  "alignItems": {
    "type": "string",
    "default": "stretch"
  }
}
```

---

### **6. Studio Image Block** 📋 PLANNED
**Location:** `/blocks/studio-image/` (to create)
**Purpose:** Enhanced image display with overlays and captions
**GB Reference:** [Image Block Docs](https://learn.generatepress.com/)

**Planned Features:**
- [ ] Aspect ratio presets (16:9, 4:3, 1:1, etc.)
- [ ] Overlay controls
- [ ] Caption styling
- [ ] Lazy loading
- [ ] Responsive image handling
- [ ] Studio design token integration

**Attributes:**
```json
{
  "id": {
    "type": "number"
  },
  "url": {
    "type": "string",
    "source": "attribute",
    "selector": "img",
    "attribute": "src"
  },
  "alt": {
    "type": "string",
    "source": "attribute",
    "selector": "img",
    "attribute": "alt"
  },
  "aspectRatio": {
    "type": "string",
    "default": "auto"
  },
  "caption": {
    "type": "string",
    "source": "html",
    "selector": "figcaption"
  }
}
```

---

## 🚀 Advanced Blocks (Phase 2)

### **7. Studio Navigation Block**
**Purpose:** Menu systems with dropdown support
**Features:** Multi-level menus, mobile responsiveness, Studio styling

### **8. Studio Accordion Block**
**Purpose:** Collapsible content sections
**Features:** Multiple panels, animation controls, accessibility

### **9. Studio Shape Block**
**Purpose:** Decorative SVG elements
**Features:** Shape library, color controls, size variations

### **10. Studio Site Header Block**
**Purpose:** Complete header templates
**Features:** Logo, navigation, search, responsive layout

### **11. Studio Query Block**
**Purpose:** Dynamic content loops
**Features:** Post queries, custom post types, pagination

---

## 🔧 Development Standards

### **Block Structure Template:**
```
/blocks/studio-[name]/
├── block.json          # Block configuration
├── index.js           # Main block registration
├── edit.js            # Editor component
├── save.js            # Frontend save component
├── style.css          # Frontend styles
├── editor.css         # Editor-only styles
└── README.md          # Block documentation
```

### **Required Files:**
1. **block.json** - WordPress block configuration
2. **index.js** - Block registration and components
3. **style.css** - Frontend styling
4. **editor.css** - Editor-specific styling

### **Studio Integration Requirements:**
- [ ] Use WordPress preset APIs for design tokens
- [ ] Support Studio typography presets
- [ ] Include block style variations
- [ ] Follow semantic HTML practices
- [ ] Ensure accessibility compliance

### **GenerateBlocks Pattern Adoption:**
- [ ] Study GB block.json structure
- [ ] Copy their attribute patterns
- [ ] Learn their CSS methodology
- [ ] Adopt their responsive approach
- [ ] Use their performance optimizations

---

## 📚 Development Resources

### **WordPress Block Development:**
- [Block Editor Handbook](https://developer.wordpress.org/block-editor/)
- [Block API Reference](https://developer.wordpress.org/block-editor/reference-guides/block-api/)
- [Theme.json Reference](https://developer.wordpress.org/themes/advanced-topics/theme-json/)

### **GenerateBlocks Study:**
- [Main Documentation](https://learn.generatepress.com/)
- [Block Architecture Examples](https://learn.generatepress.com/)
- [CSS Methodology](https://learn.generatepress.com/)

### **Studio Integration:**
- Studio design tokens via theme.json
- Typography presets via blockStyles
- WordPress preset APIs
- Block style management system

---

## 🎯 Next Steps

### **Immediate Actions:**
1. **Create Studio Headline Block** - Next priority block
2. **Study GenerateBlocks Patterns** - Learn from their architecture
3. **Set up Block Development Environment** - Ensure proper tooling
4. **Test Studio Token Integration** - Verify theme.json flow

### **Development Process:**
1. **Plan Block Features** - Define attributes and functionality
2. **Create Block Structure** - Set up files and folders
3. **Implement Core Features** - Basic functionality first
4. **Add Studio Integration** - Design token support
5. **Test and Refine** - Ensure quality and compatibility

---

*Last Updated: June 14, 2025*
*Status: Planning Phase - Ready for Development*
