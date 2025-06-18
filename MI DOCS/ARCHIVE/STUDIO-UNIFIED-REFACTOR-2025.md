# Studio Unified System - Complete Refactor Plan 2025
*Last Updated: June 17, 2025*

## Overview

This document tracks the major refactor from `blocksy-child` to `the-studio` theme, unifying two complementary systems:
1. **Studio Designer** - CSS-driven styling with auto-generated controls ✅ WORKING
2. **Villa Management** - ACF database with JSON sync for Villa management 🔄 IN PROGRESS

**CRITICAL**: We are NOT going back to the blocksy-child theme. This is a complete architectural refactor.

## Current Progress Status
may
### ✅ Phase 1: Foundation Setup - COMPLETE
- [x] Created "The Studio" child theme of Blocksy
- [x] Theme structure with `/studio/` directories created
- [x] ACF Pro installed and configured with Local JSON
- [x] Initial CSS variables file with @control annotations
- [x] Variable scanner detecting 86 variables
- [x] Admin interface with tabbed layout
- [x] **Variable saves are working** 

### ✅ Phase 2: Design System - COMPLETE
- [x] Control generator auto-creates from @control annotations
- [x] Utility generator creates classes from variables
- [x] "Studio Designer" menu implemented:
  - Variables page ✅ (saves working)
  - Selectors page 📋 (placeholder)
  - Utilities page ✅ (with generation)

### ✅ Phase 3: Content Structure - COMPLETE
- [x] ACF field groups created:
  - Villa Properties
  - Villa Owners
  - Villa Committees
  - Villa Proposals
- [x] Custom post types registered
- [x] "Studio Content" menu created
- [x] JSON sync system built (keeping JSON, not YAML)

### 🔄 Phase 4: Villa Admin Interfaces - IN PROGRESS
- [ ] Custom admin dashboard (recreating from blocksy-child)
- [ ] Properties list with inline editing
- [ ] CRM interface for Owners
- [ ] Committee management
- [ ] Voting/Proposal system

### 📋 Phase 5: Advanced Features - PENDING
- [ ] Selector Builder UI
- [ ] Custom HTML elements parser
- [ ] Complete data migration
- [ ] API endpoints

## Architecture Summary

```
The Studio Theme (Design)          Villa Content (Data)
├── CSS Variables → Controls       ├── ACF Database (WordPress)
├── Selector Builder              ├── JSON Files (Existing)
├── Utility Generation            ├── Two-way Sync
└── Custom Elements               └── Custom Admin Interfaces
```

## Key Architecture Decisions

### 1. Data Format: Keeping JSON (Not YAML)
- **Decision**: Keep existing JSON format from blocksy-child
- **Location**: `/blocksy-child/villa-data/`
- **Reason**: User preference, existing data, no migration needed

### 2. Menu Structure
Two top-level menus created:
```
Studio Designer (position 3)
├── Variables (CSS variable editor)
├── Selectors (element targeting)
└── Utilities (generated classes)

Studio Content (position 4)
├── Overview (dashboard)
├── Properties (custom interface)
├── Owners & CRM
├── Committees
├── Proposals & Voting
├── Import/Export
└── JSON Sync
```

### 3. Data Sync Flow
```
blocksy-child/villa-data/*.json
    ↕️ Two-way sync (hourly + on save)
ACF Custom Post Types (Database)
    ↕️ WordPress Admin UI
Custom Admin Interfaces
```

## Current File Structure

```
/wp-content/themes/the-studio/
├── style.css                    ✅ Theme declaration
├── functions.php                ✅ Core setup & menus
├── acf-json/                    ✅ ACF Local JSON
└── studio/
    ├── core/
    │   ├── variable-scanner.php ✅ Scans @control annotations
    │   ├── studio-loader.php    ✅ Main orchestrator
    │   ├── utility-generator.php ✅ Creates utility classes
    │   └── json-sync.php        ✅ Two-way JSON sync
    ├── css/
    │   ├── studio-vars.css      ✅ CSS variables
    │   └── studio-utilities.css ✅ Generated utilities
    ├── admin/
    │   ├── studio-admin.php     ✅ Variables UI
    │   └── studio-utilities.php ✅ Utilities UI
    └── villa/
        ├── villa-loader.php     ✅ Villa system loader
        ├── villa-post-types.php ✅ CPT definitions
        ├── villa-acf-fields.php ✅ ACF groups
        └── admin/
            └── villa-admin-dashboard.php ✅ Dashboard started
```

## Next Immediate Steps

### 1. Sync Existing Data (First Priority)
```bash
1. Go to Studio Content > JSON Sync
2. Click "Sync Now" button
3. Verify data appears in WordPress
```

### 2. Complete Villa Admin Pages
Create these files in `/studio/villa/admin/`:
- `villa-admin-properties.php` - Property grid with filters
- `villa-admin-owners.php` - CRM with email/phone
- `villa-admin-committees.php` - Committee management
- `villa-admin-proposals.php` - Voting interface

### 3. Test Two-Way Sync
- Edit in WordPress → Verify JSON updates
- Edit JSON file → Verify database updates

### 4. Build Selector Builder
- UI for targeting any element
- Variable group assignment
- CSS generation

## Implementation Checklist

### ✅ Week 1: Foundation (COMPLETE)
- [x] Create theme folder structure
- [x] Add ACF Local JSON configuration
- [x] Create initial CSS variables file
- [x] Build variable scanner prototype
- [x] Create basic admin interface

### ✅ Week 2: Design System (COMPLETE)
- [x] Implement control generator
- [x] Build utility generator
- [x] Create admin menu structure
- [x] Test variable editing

### ✅ Week 3: Content Structure (COMPLETE)
- [x] Create ACF field groups
- [x] Register custom post types
- [x] Build sync system
- [x] Create menu structure

### 🔄 Week 4: Admin Interfaces (CURRENT)
- [ ] Port custom admin pages from blocksy-child
- [ ] Implement inline editing
- [ ] Create CRM features
- [ ] Add voting system

### 📋 Week 5: Migration & Testing
- [ ] Complete data migration
- [ ] Test all workflows
- [ ] Update documentation
- [ ] Train team

### 📋 Week 6: Advanced Features
- [ ] Selector builder
- [ ] Custom HTML elements
- [ ] API endpoints
- [ ] Performance optimization

## Critical Notes

### DO NOT:
- ❌ Go back to blocksy-child theme
- ❌ Convert JSON to YAML
- ❌ Use default CPT interfaces
- ❌ Lose any existing functionality

### DO:
- ✅ Keep all Villa features
- ✅ Use custom admin interfaces
- ✅ Maintain JSON format
- ✅ Preserve existing data structure
- ✅ Continue forward with refactor

## Technical Details

### CSS Variable System
```css
/* Variables with @control auto-generate UI */
:root {
    --ts-color-primary: #5a7b7c; /* @control: color */
    --ts-spacing-md: 1rem; /* @control: range[0,4,0.25] */
    --ts-font-sans: system-ui; /* @control: font */
}
```

### JSON Sync System
```php
// Automatic two-way sync
JSON Files → ACF Database (hourly cron)
ACF Database → JSON Files (on save)

// Manual sync available in admin
Studio Content > JSON Sync > "Sync Now"
```

### ACF Configuration
- Local JSON enabled in theme
- Field groups for all Villa entities
- Bidirectional relationships
- Programmatic registration

## Success Metrics

### Completed ✅
- CSS variables with working saves
- Utility class generation
- ACF structure with field groups
- JSON sync system
- Basic admin structure

### In Progress 🔄
- Custom admin interfaces
- Data migration
- Feature parity

### Pending 📋
- Selector builder
- Custom elements
- Full documentation
- Team training

## Quick Commands

```bash
# Verify theme is active
wp theme list

# Trigger manual sync
wp cron event run studio_sync_json_to_acf

# Check sync status
wp option get studio_last_json_sync

# View sync logs
wp option get studio_sync_logs
```

## Support & Questions

This refactor represents a major architectural improvement:
- **From**: Scattered files in child theme
- **To**: Organized, scalable system in dedicated theme

All existing Villa functionality will be preserved and enhanced. The new architecture provides better organization, easier maintenance, and AI-friendly data structures while keeping the familiar WordPress admin experience.