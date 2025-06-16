# 🏗️ Studio Architecture Documentation

## Overview
Studio is a WordPress theme-integrated system for AI-powered block creation with centralized design token management. The system enables AI to create professionally designed WordPress sites by hydrating blocks with JSON content and design decisions using semantic presets.

## Current Implementation Status (June 16, 2024)

### ✅ Completed Components
- **Design Token System**: Full sync from studio.json → theme.json
- **Studio Admin UI**: Token manager, preset manager, HTML converter
- **Studio Text Block**: Complete implementation in theme
- **Studio Container Block**: Complete implementation in theme
- **Studio Button Block**: Complete implementation in theme
- **Studio Grid Block**: Complete implementation in theme
- **Theme Integration**: Studio_Theme_Integration class in functions.php
- **AJAX Handlers**: Token sync, preset management, HTML conversion

### 🔄 In Progress
- **Block Style Builder**: Needs bug fixes
- **Theme Migration**: Moving remaining components from plugin

### 📋 Planned
- **Studio Image Block**: Media with styling presets
- **Pattern Library**: Fresh patterns using Studio blocks
- **AI Integration**: JSON hydration system

## Architecture Components

### 1. **Design Token System**
- **Flow**: `studio.json` → `theme.json` → WordPress Blocks
- **Token Sync**: ✅ Implemented in theme functions.php
- **Storage**: Design tokens stored in theme.json as WordPress presets
- **Access**: Blocks access tokens via WordPress preset APIs
- **Admin UI**: Visual token editor with live sync

### 2. **Block System**
- **Custom Studio Blocks**: Not GenerateBlocks, but inspired by their architecture
- **Block Types**:
  - ✅ Studio Text Block (Complete with typography presets)
  - ✅ Studio Container Block (Complete with width/padding controls)
  - ✅ Studio Button Block (Complete with style presets, icons, hover states)
  - ✅ Studio Grid Block (Complete with responsive columns and gap presets)
  - 📋 Studio Image Block
  - 📋 Studio Headline Block

### 3. **Preset System**
- **Semantic Presets**: pretitle, title, subtitle, description, body
- **Variants**: hero, section, card, large, small
- **Implementation**: Stored in theme.json blockStyles
- **Management**: ✅ Typography Preset Manager in admin UI
- **Integration**: Blocks read presets from theme data

### 4. **Studio Interface Components**
- **Design Token Manager**: ✅ Color, typography, spacing management
- **Typography Preset Manager**: ✅ Create, edit, preview presets
- **Block Style Builder**: 🔄 Needs completion
- **Pattern Library**: 📋 To be created fresh
- **HTML to Blocks Converter**: ✅ Convert HTML to WordPress blocks

### 5. **Theme Integration**
- **Location**: ✅ Core components moved to theme
- **Files**:
  - `functions.php`: ✅ Studio_Theme_Integration class
  - `theme.json`: ✅ Design token storage
  - `/blocks/`: ✅ Studio block definitions
  - `/assets/`: ✅ Admin CSS/JS files
  - `/patterns/`: 📋 Block patterns (to be created)

## Implementation Details

### Studio_Theme_Integration Class
```php
class Studio_Theme_Integration {
    // Admin menu and pages
    public function add_admin_menu()
    public function render_token_manager()
    public function render_preset_manager()
    public function render_html_converter()
    
    // Asset management
    public function enqueue_admin_assets()
    
    // Block registration
    public function register_studio_blocks()
    
    // Token management
    public function get_design_tokens()
    public function sync_tokens_to_theme_json()
    
    // AJAX handlers
    public function ajax_sync_tokens()
    public function ajax_save_preset()
    public function ajax_convert_html()
}
```

### Data Flow

```
1. Studio Admin UI → AJAX → studio.json (design tokens)
2. Token Sync → theme.json (WordPress presets)
3. Blocks → WordPress APIs → Presets
4. Typography Presets → Theme Data → Block Inspector
5. HTML Input → Converter → Block Markup
```

## WordPress API Integration
- **Block Registration**: Using block.json standard
- **Preset Access**: `wp.data.select('core/block-editor').getSettings()`
- **Style Generation**: Via theme.json blockStyles
- **Dynamic Rendering**: Server-side block rendering when needed
- **Localization**: wp_localize_script for admin data

## AI Integration Points
1. **Block Creation**: AI generates blocks using Studio patterns
2. **Preset Selection**: AI chooses appropriate semantic presets
3. **Content Hydration**: AI provides JSON data for blocks
4. **Design Decisions**: AI creates new presets as needed
5. **HTML Conversion**: AI-powered HTML to blocks transformation

## File Structure (Current Implementation)
```
/blocksy-child/
├── functions.php          # Studio_Theme_Integration class
├── theme.json            # Design tokens, block styles
├── studio.json           # Studio token management
├── /blocks/
│   ├── /studio-text/
│   │   ├── block.json
│   │   ├── index.js
│   │   ├── editor.css
│   │   ├── style.css
│   │   └── /build/
│   ├── /studio-container/
│   │   └── (same structure)
│   ├── /studio-button/
│   │   └── (same structure)
│   ├── /studio-grid/
│   │   └── (same structure)
│   └── /studio-image/
└── /assets/
    ├── /css/
    │   └── studio-admin.css
    └── /js/
        └── studio-admin.js
```

## Security Implementation
- **Nonce Verification**: ✅ All AJAX endpoints verify nonces
- **Capability Checks**: ✅ manage_options for admin, edit_posts for converter
- **Sanitization**: ✅ All user inputs sanitized
- **Escaping**: ✅ Proper output escaping in admin UI

## Performance Considerations
- **Build-time CSS**: Generate styles at build time
- **JSON Hydration**: Future optimization for faster rendering
- **Minimal Runtime**: Reduce client-side processing
- **Cached Presets**: Store computed styles
- **Lazy Loading**: Load blocks and assets only when needed

## Next Steps
1. Complete Studio Image block
2. Fix Block Style Builder bugs
3. Create pattern library with Studio blocks
4. Implement JSON hydration system
5. Complete AI integration layer
