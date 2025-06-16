# 🏗️ Studio Architecture Documentation

## Overview
Studio is a WordPress theme-integrated system for AI-powered block creation with centralized design token management. The system enables AI to create professionally designed WordPress sites by hydrating blocks with JSON content and design decisions using semantic presets.

## Recent Architectural Improvements (June 16, 2024)

### ✅ **Eliminated studio.json Complexity**
- **Before**: `Admin UI` ↔ `studio.json` ↔ `theme.json` ↔ WordPress Blocks
- **After**: `Admin UI` ↔ `theme.json` ↔ WordPress Blocks  
- **Benefits**: Single source of truth, no sync issues, simpler maintenance
- **Custom Properties**: Extended tokens stored in `theme.json` custom section

## Current Implementation Status (June 16, 2024)

### ✅ Completed Components
- **Design Token System**: Direct theme.json integration (studio.json eliminated)
- **Studio Admin UI**: Tabbed token editor with direct theme.json sync
- **Studio Text Block**: Complete implementation in theme
- **Studio Container Block**: Complete implementation in theme with height presets system and CSS custom properties
- **Studio Button Block**: Complete implementation in theme
- **Studio Grid Block**: Complete implementation in theme
- **Studio Image Block**: Complete implementation in theme
- **Theme Integration**: Studio_Theme_Integration class in functions.php
- **AJAX Handlers**: Direct theme.json token sync, preset management, HTML conversion

### 🔄 In Progress
- **Block Style Builder**: Needs bug fixes

### 📋 Planned
- **Pattern Library**: Fresh patterns using Studio blocks
- **AI Integration**: JSON hydration system

## Architecture Components

### 1. **Design Token System** ✅ **SIMPLIFIED**
- **Flow**: `Admin UI` ↔ `theme.json` → WordPress Blocks
- **Architecture**: Single source of truth - eliminated studio.json complexity
- **Token Sync**: ✅ Direct read/write to theme.json (no intermediate files)
- **Storage**: Design tokens stored in theme.json as WordPress presets + custom section
- **Access**: Blocks access tokens via WordPress preset APIs
- **Admin UI**: Tabbed token editor (Colors, Typography, Spacing, Layout)
- **Custom Properties**: Extended typography and spacing tokens in theme.json custom section

### 2. **Block System**
- **Custom Studio Blocks**: Not GenerateBlocks, but inspired by their architecture
- **Block Types**:
  - ✅ Studio Text Block (Complete with typography presets)
  - ✅ Studio Container Block (Complete with width/padding controls and height presets system)
  - ✅ Studio Button Block (Complete with style presets, icons, hover states)
  - ✅ Studio Grid Block (Complete with responsive columns and gap presets)
  - ✅ Studio Image Block (Complete with aspect ratios, effects, hover effects, caption options)
  - 📋 Studio Headline Block

### 3. Block Architecture

#### Completed Blocks (5/5): ✅

1. **Studio Text Block** ✅
   - Single block for ALL text elements
   - Typography presets control tag + styling
   - Supports: h1-h6, p, span, div

2. **Studio Container Block** ✅
   - Layout wrapper with responsive controls
   - Width: content, wide, full
   - Padding: none, small, medium, large, xlarge
   - Height: auto, 25vh, 50vh, 75vh, 100vh
   - Semantic tags: div, section, article, etc.

3. **Studio Button Block** ✅
   - Styles: primary, secondary, outline, ghost, link
   - Sizes: small, medium, large
   - Icons: 6 options with before/after positioning
   - Link management with popover UI

4. **Studio Grid Block** ✅
   - Columns: 1-12 (responsive)
   - Gap presets: none to xlarge
   - Alignment: items and justify
   - Advanced: auto-flow, auto-rows

5. **Studio Image Block** ✅
   - Aspect ratios: original, 1:1, 16:9, 9:16, 21:9, 4:3, 3:4
   - Effects: grayscale, sepia, blur, brightness, contrast
   - Hover effects: zoom in/out, rotate, blur-focus, color-grayscale
   - Caption options: below, overlay (top/bottom/center)
   - Link support with lightbox option

### 4. **Preset System**
- **Semantic Presets**: pretitle, title, subtitle, description, body
- **Variants**: hero, section, card, large, small
- **Implementation**: Stored in theme.json blockStyles
- **Management**: ✅ Typography Preset Manager in admin UI
- **Integration**: Blocks read presets from theme data

### 5. **Studio Interface Components**
- **Design Token Manager**: ✅ Color, typography, spacing management
- **Typography Preset Manager**: ✅ Create, edit, preview presets
- **Block Style Builder**: 🔄 Needs completion
- **Pattern Library**: 📋 To be created fresh
- **HTML to Blocks Converter**: ✅ Convert HTML to WordPress blocks

### 6. **Theme Integration**
- **Location**: ✅ Core components moved to theme
- **Files**:
  - `functions.php`: ✅ Studio_Theme_Integration class
  - `theme.json`: ✅ Design token storage
  - `/blocks/`: ✅ Studio block definitions
  - `/assets/`: ✅ Admin CSS/JS files
  - `/patterns/`: 📋 Block patterns (to be created)

## Token Management

### Token Flow
1. **Admin UI** - Design system source of truth
   - Complete color palette with 17 tokens
   - Typography scales (sizes and weights)
   - Spacing system
   
2. **Token Manager UI** - Visual editing interface
   - Tabbed interface: Colors, Typography, Spacing, Layout  
   - Full CRUD operations with live preview
   - Direct save to theme.json (no sync needed)
   - Professional admin UI with color previews
   
3. **theme.json** - WordPress integration
   - Auto-synced from Admin UI
   - Consumed by blocks and editor
   - Standard WordPress format

### Token Structure
```json
{
  "colors": {
    "primary": { "name": "Primary", "value": "#5a7b7c" },
    "primary-light": { "name": "Primary Light", "value": "#6a8b8c" },
    "primary-dark": { "name": "Primary Dark", "value": "#4a6b6c" },
    // ... additional color tokens
  },
  "typography": {
    "fontSizes": { "small": "14px", "base": "16px", "large": "18px" },
    "fontWeights": { "normal": "400", "medium": "500", "bold": "700" }
  },
  "spacing": {
    "xs": "0.25rem", "sm": "0.5rem", "md": "1rem", "lg": "2rem"
  },
  "container": {
    "widthPresets": {
      "content": { "name": "Content Width", "value": "content" },
      "wide": { "name": "Wide Width", "value": "wide" },
      "full": { "name": "Full Width", "value": "full" },
      "custom": { "name": "Custom", "value": "custom" }
    },
    "heightPresets": {
      "auto": { "name": "Auto", "value": "auto" },
      "quarter": { "name": "25% Viewport", "value": "25vh" },
      "half": { "name": "50% Viewport", "value": "50vh" },
      "threequarter": { "name": "75% Viewport", "value": "75vh" },
      "full": { "name": "Full Viewport", "value": "100vh" }
    },
    "htmlTags": {
      "div": { "name": "Div", "value": "div" },
      "section": { "name": "Section", "value": "section" },
      "article": { "name": "Article", "value": "article" },
      "aside": { "name": "Aside", "value": "aside" },
      "main": { "name": "Main", "value": "main" },
      "header": { "name": "Header", "value": "header" },
      "footer": { "name": "Footer", "value": "footer" }
    }
  }
}
```

## Core Architecture

The Studio Block System is built on WordPress's native block editor (Gutenberg) and uses `theme.json` as the central configuration file for all design tokens, presets, and block configurations.

### Key Components:
- **Theme.json Integration**: Central source of truth for design tokens, layout presets, and block presets
- **Block Preset System**: Complete save/load functionality for block configurations
- **Dynamic Block Inspector**: Runtime-generated controls based on theme.json tokens
- **AJAX Integration**: Secure server-side preset management with nonce verification
- **Container Block**: Core layout block with height presets, width options, and semantic HTML tags

### Data Flow Architecture:
```
WordPress Editor → Block Inspector → AJAX Save → theme.json → Load Preset → Apply Settings
```

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
1. Admin UI → AJAX → theme.json (direct save)
2. Blocks → WordPress APIs → theme.json Presets
3. Typography Presets → Theme Data → Block Inspector
4. HTML Input → Converter → Block Markup
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
│       └── (same structure)
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

## Architecture Benefits (Post studio.json Elimination)

### 🎯 **Simplified Token Management**
- **Single Source of Truth**: theme.json is the only token storage file
- **No Sync Issues**: Direct read/write eliminates sync failures
- **WordPress Native**: Full compliance with WordPress theme.json spec
- **Custom Extensions**: Extended properties in theme.json custom section

### 🚀 **Developer Experience**
- **Cleaner Codebase**: Removed 100+ lines of sync logic
- **Easier Debugging**: No intermediate file to track
- **Direct Editing**: Admin UI reads/writes theme.json directly
- **Better Performance**: Eliminated file sync operations

### 💼 **Maintenance Benefits**
- **Reduced Complexity**: One file to manage instead of two
- **Future-Proof**: Better alignment with WordPress standards
- **Error Reduction**: Fewer failure points in token workflow
- **Clear Data Flow**: Straightforward Admin UI → theme.json → Blocks

## Next Steps
1. Fix Block Style Builder
2. Create pattern library
3. Document AI integration points
4. Enhance admin UI features

### Block Preset System Implementation

#### Overview
The Block Preset System is a crucial component of the Studio Block System, enabling users to save and load custom block configurations. This system is built on top of the WordPress block editor and utilizes the `theme.json` file for storing preset data.

#### Technical Implementation
The Block Preset System consists of the following key components:

* **Save Functionality**: A "Save Current as Preset" button in the Block Presets panel allows users to save their current block configuration as a preset. This triggers an AJAX request to the `wp_ajax_studio_save_preset` endpoint, which verifies the nonce and saves the preset data to the `theme.json` file.
* **Load Functionality**: The "Load Preset" dropdown in the Block Presets panel allows users to load a saved preset. This triggers an AJAX request to the `wp_ajax_studio_load_preset` endpoint, which retrieves the preset data from the `theme.json` file and applies it to the block.
* **Data Structure**: Preset data is stored in the `theme.json` file in the following format:
```json
{
  "settings": {
    "custom": {
      "blockPresets": {
        "container": {
          "preset_id_timestamp": {
            "name": "User Defined Name",
            "description": "Auto-generated description",
            "attributes": {
              "widthPreset": "full",
              "paddingPreset": "medium", 
              "heightPreset": "50vh",
              "tagName": "section",
              "minHeight": ""
            },
            "created": "2025-06-16 23:06:19",
            "id": "preset_id_timestamp"
          }
        }
      }
    }
  }
}
```
* **JavaScript Integration**: The Block Preset System utilizes React components to manage the preset data and interact with the WordPress block editor. The `useMemo` hook is used to memoize the preset data and prevent unnecessary re-renders.

#### Container Block Features

The Studio Container block serves as the foundational layout block with the following capabilities:

#### Layout Controls
- **Width Presets**: content (1200px), wide (1400px), full (100vw), custom
- **Height Presets**: auto, 25vh, 50vh, 75vh, 100vh, custom
- **Padding Scale**: xs (8px), sm (16px), md (24px), lg (32px), xl (48px), xxl (64px)
- **HTML Tag Options**: div, section, article, aside, main, header, footer, nav

#### Block Preset System (✅ COMPLETED)
**Save Functionality:**
- "Save Current as Preset" button in Block Presets panel
- Modal dialog for preset naming with current settings preview
- AJAX endpoint (`wp_ajax_studio_save_preset`) with nonce verification
- Automatic unique ID generation with timestamps
- Direct storage to `theme.json` under `settings.custom.blockPresets.container`

**Load Functionality:**
- "Load Preset" dropdown in Block Presets panel
- Dynamic preset list generated from theme.json data
- Instant application of saved preset attributes
- Theme data localized to JavaScript via `window.studioThemeData`

**Data Structure:**
```json
{
  "settings": {
    "custom": {
      "blockPresets": {
        "container": {
          "preset_id_timestamp": {
            "name": "User Defined Name",
            "description": "Auto-generated description",
            "attributes": {
              "widthPreset": "full",
              "paddingPreset": "medium", 
              "heightPreset": "50vh",
              "tagName": "section",
              "minHeight": ""
            },
            "created": "2025-06-16 23:06:19",
            "id": "preset_id_timestamp"
          }
        }
      }
    }
  }
}
```

#### Technical Implementation
- **AJAX Handler**: `handle_studio_save_preset()` function with comprehensive error handling
- **JavaScript Integration**: React components with `useMemo` for preset management
- **Data Validation**: Server-side sanitization and capability checks (`manage_options`)
- **File Management**: Direct theme.json read/write with proper JSON encoding
