# 🎨 DRY Studio Master Guide

## 🎯 **Current Status: Featured Attractions Component**

### **✅ What We Have Working:**
- **Featured Attractions Component** - Universal loop section for displaying attraction cards
- **Design System Integration** - Theme tokens in `theme.json` + component JSON
- **WordPress Integration** - GenerateBlocks patterns and CSS generation

---

## 📁 **File Structure (Clean & Simple):**

### **Active Component Files:**
```
📂 COMPONENT-STYLES/
  └── featured-attractions-component.json    ← Main component definition
  
📂 WordPress Theme/
  ├── theme.json                            ← Design tokens & theme settings
  ├── components/featured-attractions.css   ← Generated component CSS
  └── patterns/attractions-loop.php         ← WordPress block pattern
```

### **Workflow Scripts:**
```
📂 DRY-STUDIO-DOCS/
  └── simple-sync-test.php                  ← Generates CSS from JSON
```

---

## 🔄 **The DRY Studio Workflow:**

### **1. Edit Design Tokens**
**File:** `theme.json` → `settings.custom.loopSection`
```json
"loopSection": {
  "spacing": { "sectionPadding": "4rem 2rem", "gridGap": "1.5rem" },
  "typography": { "titleFontSize": "1.25rem", "badgeFontSize": "0.75rem" },
  "effects": { "overlayGradient": "linear-gradient(...)", "hoverTransform": "translateY(-4px)" }
}
```

### **2. Edit Component Logic**
**File:** `featured-attractions-component.json`
- Component structure and CSS rules
- References theme tokens: `var(--wp--custom--loop-section--*)`

### **3. Generate CSS**
**Command:** `php simple-sync-test.php`
- Reads component JSON + theme.json
- Generates `featured-attractions.css`
- Auto-enqueued in WordPress

### **4. Update Content**
**File:** WordPress editor
- Paste corrected HTML pattern
- Content displays with generated styles

---

## 🎨 **Component Architecture:**

### **Universal Loop Section Foundation:**
- **Base Classes:** `.loop-section`, `.loop-container`, `.loop-item`
- **Content Areas:** `.loop-item-header`, `.loop-item-body`, `.loop-item-footer`
- **Elements:** `.loop-item-badge`, `.loop-item-title`, `.loop-item-button`

### **Featured Attractions Implementation:**
- **Section:** Popular attractions with "Where To Go" badge
- **Grid:** 4-column responsive layout
- **Cards:** Background images, overlay gradients, badges, titles, location buttons
- **Styling:** Montserrat font, theme colors, hover effects

---

## 🔧 **Current Issues & Solutions:**

### **❌ CSS Not Loading:**
- **Problem:** WordPress not loading generated CSS file
- **Solution:** Check functions.php enqueue, clear cache, or add to theme.json

### **❌ Button Text Missing:**
- **Problem:** Empty anchor tags in WordPress pattern
- **Solution:** Add text content to buttons: `📍 Surf City`

### **❌ Overlay Position:**
- **Problem:** Overlay inside header instead of covering full card
- **Solution:** Move overlay div to be direct child of loop-item

---

## 🚀 **Next Steps:**

1. **Fix CSS Loading** - Ensure featured-attractions.css loads in WordPress
2. **Update Pattern HTML** - Paste corrected HTML with proper structure
3. **Test Component** - Verify all styling and interactions work
4. **Create Variants** - Build additional loop section types (testimonials, team, etc.)

---

## 📝 **Design System Tokens:**

### **Colors:**
- Primary: `#5a7b7c`
- Text: `var(--wp--preset--color--extreme-light)` (white)
- Badges: `rgba(90, 123, 124, 0.1)` with blur effects

### **Typography:**
- Font: Montserrat
- Sizes: Badge (0.75rem), Title (1.25rem), Button (0.875rem)

### **Spacing:**
- Section: 4rem 2rem padding
- Grid: 1.5rem gap
- Items: 1.5rem internal padding

### **Effects:**
- Overlay: Linear gradient to 70% black
- Hover: translateY(-4px) + shadow
- Transitions: 0.3s ease

---

**This is your complete DRY Studio setup for the Featured Attractions component!** 🎨✨
