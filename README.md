# DS-Studio - WordPress Design System Plugin

A WordPress plugin for real-time theme.json design token management with block editor integration.

## 🚀 Features

- **Live Color Management** - Edit theme.json colors directly from the block editor sidebar
- **Real-time Preview** - See changes instantly with CSS variable injection
- **Interactive UI** - Click-to-edit color swatches with inline editing
- **Theme.json Integration** - Direct read/write to actual theme.json file
- **WordPress Native** - Built with WordPress block editor APIs and components

## 📁 Project Structure

```
├── app/public/wp-content/
│   ├── plugins/DS-STUDIO/          # Main plugin directory
│   │   ├── ds-studio.php           # Plugin bootstrap file
│   │   ├── assets/js/editor.js     # React frontend
│   │   └── assets/css/editor.css   # Plugin styles
│   └── themes/blocksy-child/       # Child theme with DS-Studio integration
│       ├── theme.json              # Design token definitions
│       └── style.css               # Theme styles
```

## 🛠 Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/ShannaKaeM/design-studio.git
   ```

2. **Activate the plugin**
   - Go to WordPress Admin → Plugins → Installed Plugins
   - Activate "DS-Studio"

3. **Use the plugin**
   - Edit any page/post in the block editor
   - Look for "Design System Studio" in the sidebar
   - Edit colors and save to theme.json

## 🎯 Current Status

✅ **Completed:**
- Colors module with full theme.json integration
- Live preview with CSS variables
- Interactive editing interface
- AJAX save/load functionality

🚧 **Roadmap:**
- Typography module (font weights, line heights, letter spacing)
- Spacing module (padding, margin, gap scales)
- Borders module (widths, styles, radii)
- Layout module (containers, breakpoints, grids)

## 🤝 Collaboration

This project is a collaboration between Shanna and Daniel for creating an innovative WordPress design system management tool.

## 📄 License

MIT License - Feel free to use and modify for your projects.
