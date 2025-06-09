# DS-Studio Installation Guide

## 🚀 **Quick Start (Ready to Test!)**

### **Option 1: Immediate Testing (No Build Required)**
The plugin is ready to test with the simplified JavaScript version:

1. **Copy the DS-STUDIO folder** to your WordPress plugins directory:
   ```
   /wp-content/plugins/ds-studio/
   ```

2. **Activate the plugin** in WordPress Admin → Plugins

3. **Open the Block Editor** and look for the DS-Studio sidebar panel

4. **Start managing your design system!**

---

## 🛠 **Option 2: Full Development Setup**

### **Prerequisites:**
- Node.js 16+ 
- npm or yarn
- WordPress 6.0+

### **Installation Steps:**

1. **Install Dependencies:**
   ```bash
   cd DS-STUDIO
   npm install
   ```

2. **Build for Production:**
   ```bash
   npm run build
   ```

3. **Or Start Development Mode:**
   ```bash
   npm run start
   ```

4. **Activate Plugin:**
   - Copy to `/wp-content/plugins/ds-studio/`
   - Activate in WordPress Admin

---

## 🎯 **Features Available Now:**

### **✅ Working Features:**
- **Color Management** - Add, edit, remove colors with live preview
- **Spacing System** - Create spacing scales with visual previews
- **Typography Controls** - Font size management
- **Layout Settings** - Content and wide size controls
- **Export/Import** - Save and share design systems
- **Live Preview** - See changes instantly in block editor
- **theme.json Generation** - Auto-saves to your theme's theme.json

### **🎨 How to Use:**

1. **Open Block Editor** (edit any page/post)
2. **Look for DS-Studio icon** in the sidebar
3. **Click to open the Design System panel**
4. **Start adding colors, spacing, etc.**
5. **See live preview** as you make changes
6. **Save to theme.json** when ready

---

## 🔧 **Integration with GB Styles:**

The plugin generates CSS variables that work seamlessly with Daniel's GB Styles:

```css
/* Auto-generated variables available for use: */
--wp--preset--color--primary
--wp--preset--spacing--lg
--wp--preset--font-size--large
```

These can be used in:
- Component CSS files
- GenerateBlocks custom CSS
- Theme stylesheets

---

## 🐛 **Troubleshooting:**

### **Plugin Not Showing:**
- Check WordPress version (6.0+ required)
- Verify plugin is activated
- Check browser console for JavaScript errors

### **Saving Issues:**
- Verify file permissions on theme.json
- Check user has `edit_theme_options` capability

### **Live Preview Not Working:**
- Clear browser cache
- Check for conflicting plugins
- Verify theme.json structure

---

## 📚 **Next Steps:**

1. **Test the current version**
2. **Provide feedback on workflow**
3. **Request additional features**
4. **Integration with your existing components**

---

**🎉 Ready to revolutionize your design system workflow!**
