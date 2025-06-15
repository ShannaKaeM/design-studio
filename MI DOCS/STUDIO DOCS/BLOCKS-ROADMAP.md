# 🧩 **Studio Blocks Roadmap**


#### **🔧 Enhanced Theme.json Sync:**
- **✅ ENHANCED:** Studio colors populate WordPress color palettes exclusively
- **✅ IMPROVED:** Studio typography scales populate WordPress font controls
- **✅ ADDED:** Studio spacing tokens power WordPress spacing controls
- **✅ CONFIGURED:** `defaultPalette: false` - Only Studio colors show
- **✅ Core Wp variables used if not defined with studio.


- ✅ **Semantic tokens** - Add for easier preset creation - IE Title Color Title Fontweight TItle font etc..
- ✅ **Preset + Variant system** - Need to define - like hero, section, card, large, small etc..


#### **Token Structure (Simplified & Implemented):**
```json
{
  "settings": {
    "custom": {
      "designTokens": {
        "colors": "Essential theme colors only (16 total)",
        "typography": "Font families, key sizes, weights",
        "layout": "Content widths, basic spacing",
        "gradients": "4 base gradients"
      },
      "semanticTokens": {
        "colors": {
          "pretitle-color": "var(--wp--preset--color--base-darkest)",
          "title-color": "var(--wp--preset--color--base-darkest)",
          "subtitle-color": "var(--wp--preset--color--neutral)",
          "description-color": "var(--wp--preset--color--neutral)",
          "body-color": "var(--wp--preset--color--neutral)",
          "link-color": "var(--wp--preset--color--primary)",
          "site-background": "var(--wp--preset--color--base-lightest)",
          "card-background": "var(--wp--preset--color--base-lighter)",
          "header-background": "var(--wp--preset--color--base-lighter)",
          "footer-background": "var(--wp--preset--color--base-lighter)"
          "section-background": "var(--wp--preset--color--base-lighter)",
          "content-container-background": "var(--wp--preset--color--base-lighter)",
        },
        "typography": {
          "pretitle-text-size": "var(--wp--preset--font-size--md)",
          "title-text-size": "var(--wp--preset--font-size--xxl)",
          "subtitle-text-size": "var(--wp--preset--font-size--xxxl)",
          "body-text-size": "var(--wp--preset--font-size--md)",
          "description-text-size": "var(--wp--preset--font-size--md)",
          "link-text-size": "var(--wp--preset--font-size--md)",
        },
        "typography": {
          "pretitle-fontweight": "var(--wp--preset--font-size--md)",
          "title-fontweight": "var(--wp--preset--font-size--xxl)",
          "subtitle-fontweight": "var(--wp--preset--font-size--xxxl)",
          "body-fontweight": "var(--wp--preset--font-size--md)",
          "description-fontweight": "var(--wp--preset--font-size--md)",
          "link-fontweight": "var(--wp--preset--font-size--md)",
        }
      }
    }
  }
}
```etc...


#### **Typography Presets examples:**
- **Pretitle** - pretitle-text-size + pretitle-fontweight + pretitle-color  + pretitle-text-transform + variants (hero, section, card)
- **Title** - title-text-size + title-fontweight + title-color + title-text-transform + variants (hero, section, card)
- **Subtitle** - subtitle-text-size + subtitle-fontweight + subtitle-color + subtitle-text-transform + variants (hero, section, card)
- **Description** - description-text-size + description-fontweight + description-color + description-text-transform + variants (hero, section, card)
- **Body** - body-text-size + body-fontweight + body-color + body-text-transform + variants (large, small)


---

## 🎯 **PHASE 2C: Studio Preset Management Layer**

### **Advanced Studio Features (On Top of WordPress Foundation):**

#### **Studio UI Global Preset Editor:**
```javascript
const StudioPresetManager = {
    2-locations: 1. 'Studio UI → Block-Styles-Builder maybe rename to block presets builder? ', 2. in the wp inspector with options to choose presets, or edit them using the core wp block controls and future custom block editor controls.
    features: [
        'Edit existing presets (Hero Title, Section Title, etc.)',
        'Create new preset variants (Hero Title Style 1, 2, 3)',
        'Live preview of changes across site',
        'Save/update buttons for preset management'
        'Delete presets'
    ],
    sync: 'Auto-updates theme.json to Studio Editor (or maybe they go right to theme.json ?? not sure)'
};



### **🎨 vision goals etc: Visual Builder (ADVANCED)**
- [ ] **In-Studio AI Interface:** Generate block patterns/components in Studio UI
- [ ] **Performance Preview:** Real-time optimized block preview
- [ ] **Component Templates:** Pre-built AI patterns with WordPress contro



### **🚀 Long-term Vision:**
- **Best of both worlds:** WordPress familiarity + Studio power
- **AI-ready architecture:** Studio tokens/theme.json block presets - drive AI generation
- **Performance optimized:** Build-time CSS generation
- **Scalable pattern:** Easy to apply to all future blocks
- **User-friendly:** No learning curve, familiar WordPress UI

**Ready to build the NEXT GENERATION of WordPress blocks - combining familiar WordPress UX with powerful Studio design tokens!** 🎨⚡🚀
