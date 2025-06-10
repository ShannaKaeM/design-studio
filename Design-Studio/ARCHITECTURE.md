# Design Studio - Technical Architecture

## 🏗️ System Architecture Overview

The Design Studio system creates a seamless bridge between WordPress theme.json design tokens and GenerateBlocks native styling controls through a multi-layered integration approach.

## 📊 Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    WordPress Frontend                       │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐ │
│  │   Core Blocks   │  │ GenerateBlocks  │  │  Custom Blocks  │ │
│  │   (Heading,     │  │   (Headline,    │  │   (Future       │ │
│  │   Paragraph,    │  │   Container,    │  │   Integration)  │ │
│  │   Button)       │  │   Button, Grid) │  │                 │ │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘ │
├─────────────────────────────────────────────────────────────┤
│                    Block Editor Interface                   │
│  ┌─────────────────────────────────────────────────────────┐ │
│  │              GenerateBlocks Controls                    │ │
│  │  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐       │ │
│  │  │ Typography  │ │   Colors    │ │   Spacing   │       │ │
│  │  │ - Font Size │ │ - Text      │ │ - Margin    │       │ │
│  │  │ - Font Fam. │ │ - Background│ │ - Padding   │       │ │
│  │  │ - Weight    │ │ - Border    │ │ - Gap       │       │ │
│  │  └─────────────┘ └─────────────┘ └─────────────┘       │ │
│  └─────────────────────────────────────────────────────────┘ │
├─────────────────────────────────────────────────────────────┤
│                    DS-Studio Integration Layer              │
│  ┌─────────────────────────────────────────────────────────┐ │
│  │              WordPress Hooks & Filters                 │ │
│  │  ┌─────────────────────────────────────────────────────┐ │ │
│  │  │ generateblocks_editor_data                          │ │ │
│  │  │ block_editor_settings_all                           │ │ │
│  │  │ generateblocks_typography_font_family_list          │ │ │
│  │  │ generateblocks_color_palette (planned)              │ │ │
│  │  │ generateblocks_spacing_presets (planned)            │ │ │
│  │  └─────────────────────────────────────────────────────┘ │ │
│  └─────────────────────────────────────────────────────────┘ │
├─────────────────────────────────────────────────────────────┤
│                    DS-Studio Plugin Core                   │
│  ┌─────────────────────────────────────────────────────────┐ │
│  │              GenerateBlocks Integration                 │ │
│  │  ┌─────────────────────────────────────────────────────┐ │ │
│  │  │ class DS_Studio_GenerateBlocks_Integration          │ │ │
│  │  │ - inject_font_sizes()                               │ │ │
│  │  │ - inject_font_families()                            │ │ │
│  │  │ - inject_unit_presets()                             │ │ │
│  │  │ - inject_colors() (planned)                         │ │ │
│  │  │ - inject_spacing() (planned)                        │ │ │
│  │  └─────────────────────────────────────────────────────┘ │ │
│  └─────────────────────────────────────────────────────────┘ │
│  ┌─────────────────────────────────────────────────────────┐ │
│  │                Style Builder System                     │ │
│  │  ┌─────────────────────────────────────────────────────┐ │ │
│  │  │ - Visual theme.json editor                          │ │ │
│  │  │ - Design token management                           │ │ │
│  │  │ - Utility class generator                           │ │ │
│  │  │ - Live preview system                               │ │ │
│  │  └─────────────────────────────────────────────────────┘ │ │
│  └─────────────────────────────────────────────────────────┘ │
├─────────────────────────────────────────────────────────────┤
│                    Theme.json Foundation                   │
│  ┌─────────────────────────────────────────────────────────┐ │
│  │                  Design Token System                    │ │
│  │  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐       │ │
│  │  │ Typography  │ │   Colors    │ │   Spacing   │       │ │
│  │  │ - Font Sizes│ │ - Palette   │ │ - Scale     │       │ │
│  │  │ - Families  │ │ - Variables │ │ - Presets   │       │ │
│  │  │ - Weights   │ │ - Semantic  │ │ - Utilities │       │ │
│  │  └─────────────┘ └─────────────┘ └─────────────┘       │ │
│  │  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐       │ │
│  │  │   Borders   │ │   Layout    │ │   Custom    │       │ │
│  │  │ - Radius    │ │ - Widths    │ │ - Tokens    │       │ │
│  │  │ - Styles    │ │ - Heights   │ │ - Extensions│       │ │
│  │  │ - Widths    │ │ - Breakpts  │ │ - Variables │       │ │
│  │  └─────────────┘ └─────────────┘ └─────────────┘       │ │
│  └─────────────────────────────────────────────────────────┘ │
│  ┌─────────────────────────────────────────────────────────┐ │
│  │                    Styles Section                       │ │
│  │  ┌─────────────────────────────────────────────────────┐ │ │
│  │  │ Global Defaults + Element Styles + Block Defaults  │ │ │
│  │  │ - Typography base styles                            │ │ │
│  │  │ - Color base styles                                 │ │ │
│  │  │ - Spacing base styles                               │ │ │
│  │  │ - Element styles (links, headings, buttons)        │ │ │
│  │  │ - Core block defaults                               │ │ │
│  │  │ - GenerateBlocks defaults                           │ │ │
│  │  └─────────────────────────────────────────────────────┘ │ │
│  └─────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

## 🔧 Core Components

### 1. Theme.json Foundation Layer
**Purpose**: Centralized design token definition and CSS custom property generation

**Key Features**:
- Design token definitions (typography, colors, spacing, borders)
- CSS custom property generation (`--wp--preset--*`)
- Base styles for consistent defaults
- WordPress native integration

**Files**:
- `/app/public/wp-content/themes/blocksy-child/theme.json`

### 2. DS-Studio Plugin Core
**Purpose**: Main plugin orchestrating design system integration

**Key Components**:
```php
class DS_Studio {
    // Main plugin class
    // Handles initialization, admin interface, style building
}

class DS_Studio_Style_Builder {
    // Visual theme.json editor
    // Utility class generation
    // Design token management
}

class DS_Studio_GenerateBlocks_Integration {
    // GenerateBlocks-specific integration
    // Hook implementations
    // Design token injection
}
```

### 3. GenerateBlocks Integration Layer
**Purpose**: Inject design tokens into GenerateBlocks native controls

**Integration Points**:
```php
// Current implementations:
add_filter('generateblocks_editor_data', [$this, 'inject_font_sizes']);
add_filter('block_editor_settings_all', [$this, 'inject_unit_presets']);
add_filter('generateblocks_typography_font_family_list', [$this, 'inject_font_families']);

// Planned implementations:
add_filter('generateblocks_color_palette', [$this, 'inject_colors']);
add_filter('generateblocks_spacing_presets', [$this, 'inject_spacing']);
add_filter('generateblocks_border_presets', [$this, 'inject_borders']);
```

## 📊 Data Flow Architecture

### 1. Design Token Processing Flow
```
Theme.json File
    ↓ (File Reading)
DS-Studio Parser
    ↓ (Token Extraction)
WordPress Hooks
    ↓ (Filter Application)
GenerateBlocks Controls
    ↓ (User Interface)
Block Attributes
    ↓ (CSS Generation)
Frontend Output
```

### 2. Integration Hook Flow
```php
// Font Size Integration Flow:
1. theme.json defines font sizes
2. DS_Studio_GenerateBlocks_Integration reads theme.json
3. inject_font_sizes() processes tokens
4. generateblocks_editor_data filter applies data
5. GenerateBlocks receives font size presets
6. Block editor displays populated dropdown
7. User selects preset
8. CSS custom property applied to block
```

### 3. Default Styling Flow
```php
// Default Application Flow:
1. theme.json styles section defines defaults
2. WordPress processes theme.json on load
3. CSS custom properties generated
4. Block editor loads with defaults applied
5. GenerateBlocks inherits theme.json defaults
6. User sees pre-styled blocks
7. Design system consistency maintained
```

## 🔌 Integration Points

### WordPress Core Integration
```php
// Theme.json API
wp_get_global_settings()
wp_get_global_styles()
WP_Theme_JSON::get_from_editor_settings()

// Block Editor Integration
block_editor_settings_all
enqueue_block_editor_assets
wp_enqueue_scripts
```

### GenerateBlocks Integration
```php
// Typography
generateblocks_typography_font_family_list
generateblocks_editor_data

// Colors (planned)
generateblocks_color_palette
generateblocks_color_settings

// Spacing (planned)
generateblocks_spacing_presets
generateblocks_dimension_settings

// Defaults (planned)
generateblocks_default_attributes
generateblocks_block_defaults
```

## 🗂️ File Structure

```
/app/public/wp-content/
├── plugins/
│   └── DS-STUDIO/
│       ├── ds-studio.php                 # Main plugin file
│       ├── includes/
│       │   ├── class-ds-studio.php       # Core plugin class
│       │   ├── class-style-builder.php   # Style builder system
│       │   └── class-generateblocks-integration.php # GB integration
│       ├── assets/
│       │   ├── css/                      # Plugin stylesheets
│       │   ├── js/                       # JavaScript files
│       │   └── images/                   # Plugin assets
│       ├── admin/                        # Admin interface files
│       └── debug-integration.php         # Debug utilities
├── themes/
│   └── blocksy-child/
│       ├── theme.json                    # Design token definitions
│       ├── style.css                     # Theme styles
│       └── functions.php                 # Theme functions
└── Design-Studio/                        # Project documentation
    ├── README.md                         # Project overview
    ├── OBJECTIVES.md                     # Technical objectives
    ├── ARCHITECTURE.md                   # This file
    └── CHANGELOG.md                      # Development history
```

## 🔄 Development Workflow

### 1. Design Token Updates
```
1. Modify theme.json design tokens
2. DS-Studio automatically detects changes
3. Utility classes regenerated
4. GenerateBlocks controls updated
5. Frontend CSS updated
6. Design system synchronized
```

### 2. Integration Development
```
1. Identify GenerateBlocks hook/filter
2. Implement in DS_Studio_GenerateBlocks_Integration
3. Test with theme.json tokens
4. Validate in block editor
5. Test frontend output
6. Document integration point
```

### 3. Testing Protocol
```
1. Unit tests for token processing
2. Integration tests for WordPress hooks
3. UI tests for block editor controls
4. Performance tests for large sites
5. Compatibility tests with themes/plugins
6. User acceptance testing
```

## ⚡ Performance Considerations

### 1. Optimization Strategies
- **Lazy Loading**: Load design tokens only when block editor is active
- **Caching**: Cache processed theme.json data
- **Minimal CSS**: Generate only necessary utility classes
- **Conditional Loading**: Load integration only when GenerateBlocks is active

### 2. Memory Management
```php
// Efficient token processing
private static $cached_tokens = null;

public function get_design_tokens() {
    if (self::$cached_tokens === null) {
        self::$cached_tokens = $this->process_theme_json();
    }
    return self::$cached_tokens;
}
```

### 3. CSS Optimization
- Use CSS custom properties for dynamic values
- Minimize CSS output size
- Leverage browser caching
- Optimize critical CSS loading

## 🔒 Security Considerations

### 1. Input Validation
- Validate all theme.json data
- Sanitize user inputs in admin interface
- Escape output in templates
- Validate CSS values before output

### 2. Permission Checks
```php
// Admin capability checks
if (!current_user_can('manage_options')) {
    wp_die(__('Insufficient permissions'));
}

// Nonce verification
if (!wp_verify_nonce($_POST['nonce'], 'ds_studio_action')) {
    wp_die(__('Security check failed'));
}
```

### 3. File Security
- Validate theme.json file integrity
- Prevent arbitrary file access
- Secure admin interface endpoints
- Implement proper error handling

---

**Architecture Status**: Current implementation covers foundation and basic GenerateBlocks integration. Next phase focuses on complete design token coverage and advanced features.
