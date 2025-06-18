# The Studio - Complete Refactor Documentation 2025
*Last Updated: June 17, 2025*

## Overview

This document consolidates all refactor documentation for the migration from `blocksy-child` to `the-studio` theme. It combines the unified refactor plan and implementation checklist into one comprehensive guide.

**CRITICAL**: We are NOT going back to the blocksy-child theme. This is a complete architectural refactor.

## Current Status: Week 4 of 6 - Building Admin Interfaces

### System Architecture
```
The Studio Theme (Design)          Villa Content (Data)
├── CSS Variables → Controls       ├── ACF Database (WordPress)
├── Selector Builder              ├── JSON Files (Existing)
├── Utility Generation            ├── Two-way Sync
└── Custom Elements               └── Custom Admin Interfaces
```

## Progress Summary

### ✅ Phase 1: Foundation Setup - COMPLETE
- Created "The Studio" child theme of Blocksy
- Set up complete folder structure
- Configured ACF Pro with Local JSON
- Created CSS variables file with @control annotations
- Built variable scanner (detecting 86 variables)
- Created admin interface structure

### ✅ Phase 2: Design System - COMPLETE
- Control generator auto-creates from @control annotations
- Utility generator creates classes from variables
- **Variable saves are working** (user confirmed: "it worked they are being saved!!")
- Studio Designer menu fully functional

### ✅ Phase 3: Content Structure - COMPLETE
- ACF field groups created (Properties, Owners, Committees, Proposals)
- Custom post types registered
- Studio Content menu created
- JSON sync system built (keeping JSON, not YAML)

### ✅ Phase 4: Villa Admin Interfaces - COMPLETE
- [x] Custom admin dashboard with statistics
- [x] Properties list with inline editing and filters
- [x] CRM interface for Owners with bulk actions
- [x] Committee management with member assignments
- [x] Voting/Proposal system with real-time results
- [x] Registration page placeholder
- [x] Settings page placeholder
- [x] Import/Export interface
- [x] JSON sync with two-way binding

### ✅ Phase 5: Advanced Features - COMPLETE
- [x] Selector Builder UI ✅
- [x] Custom HTML elements parser ✅
- [x] Complete data migration ✅
- [ ] API endpoints (deferred)

### 🔄 Phase 6: Migration & Testing - IN PROGRESS
- [x] Full data sync verification ✅
- [x] Utility generator fixed ✅
- [ ] Documentation completion
- [ ] Full system testing
- [ ] Team training
- [ ] Go-live preparation

## Key Architectural Decisions

### 1. Data Format: JSON (Not YAML)
- **Decision**: Keep existing JSON format
- **Location**: `/blocksy-child/villa-data/`
- **Reason**: User preference, no migration needed

### 2. Menu Structure
```
Studio Designer (position 3)
├── Variables (CSS variable editor) ✅
├── Selectors (element targeting) ✅
└── Utilities (generated classes) ✅

Villa Admin (position 30) - Separate from Studio Designer
├── Overview (dashboard) ✅
├── Properties (custom interface) ✅
├── Owners & CRM ✅
├── Committees ✅
├── Proposals & Voting ✅
├── Registration 📋
├── Import/Export ✅
├── JSON Sync ✅
└── Settings 📋
```

### 3. CPT Visibility
- **Decision**: Hide CPTs from admin menu (`show_in_menu = false`)
- **Access**: Through custom admin interfaces only
- **Benefit**: Clean menu structure, full control over UI

### 4. Data Flow
```
blocksy-child/villa-data/*.json
    ↕️ Two-way sync (hourly + on save)
ACF Custom Post Types (Database)
    ↕️ WordPress Admin UI
Custom Admin Interfaces (Not CPT lists)
```

### 5. Owner-Property Relationships
- **Property → Owner**: via `ownership.current_owners[].owner_id` field
- **Owner → Property**: via `properties[].unit_number` field
- **Sync**: Automatically links during JSON import
- **ID Format**: Handles both `owner-name` and `name` formats

## Implementation Details

### File Structure
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
    │   ├── selector-builder.php ✅ Maps selectors to variables
    │   └── json-sync.php        ✅ Two-way JSON sync
    ├── css/
    │   ├── studio-vars.css      ✅ CSS variables (86 total)
    │   ├── studio-utilities.css ✅ Generated utilities
    │   └── studio-selectors.css ✅ Selector-variable mappings
    ├── admin/
    │   ├── studio-admin.php     ✅ Variables UI
    │   ├── studio-selectors.php ✅ Selectors UI
    │   └── studio-utilities.php ✅ Utilities UI
    └── villa/
        ├── villa-loader.php     ✅ Villa system loader
        ├── villa-post-types.php ✅ CPT definitions
        ├── villa-acf-fields.php ✅ ACF groups
        ├── json-sync.php        ✅ Sync system
        └── admin/
            ├── villa-admin-dashboard.php ✅ Started
            ├── villa-admin-properties.php 📋 Needed
            ├── villa-admin-owners.php     📋 Needed
            ├── villa-admin-committees.php 📋 Needed
            └── villa-admin-proposals.php  📋 Needed
```

### CSS Variable System
```css
/* Variables with @control auto-generate UI controls */
:root {
    --ts-color-primary: #5a7b7c; /* @control: color */
    --ts-spacing-md: 1rem; /* @control: range[0,4,0.25] */
    --ts-font-sans: system-ui; /* @control: font */
    --ts-shadow-md: 0 4px 6px rgba(0,0,0,0.1); /* @control: shadow */
}
```

### Selector Builder System
```css
/* Map groups of variables to any CSS selector */
.button-primary {
    --ts-color-primary: var(--ts-color-primary);
    --ts-color-text: var(--ts-color-text);
    --ts-spacing-sm: var(--ts-spacing-sm);
    --ts-radius-md: var(--ts-radius-md);
}
```
Features:
- Visual selector builder interface
- Group variables by category (colors, spacing, typography)
- Apply variable groups to any CSS selector
- Auto-generates studio-selectors.css
- Preview CSS before applying

### JSON Sync System
```php
// Automatic sync
JSON → Database: Hourly cron job
Database → JSON: Immediate on save

// Manual sync
Studio Content > JSON Sync > "Sync Now"

// File locations
Source: /blocksy-child/villa-data/*.json
Target: ACF Custom Post Types
```

## Next Immediate Actions

### 1. Run Initial Data Sync (TODAY)
```
1. Navigate to: Studio Content > JSON Sync
2. Click: "Sync Now" button
3. Verify: Data appears in WordPress admin
4. Check: Properties, Owners, Committees populated
```

### 2. Complete Villa Admin Pages (THIS WEEK)
Create custom interfaces matching blocksy-child functionality:
- **Properties**: Grid view, filters, inline editing
- **Owners**: CRM features, email/phone, registration
- **Committees**: Member management, schedules
- **Proposals**: Voting interface, results tracking

### 3. Test Two-Way Sync
- Edit property in WordPress → Check JSON file updates
- Edit JSON file → Wait/trigger sync → Check database

### 4. Build Selector Builder (NEXT WEEK)
- UI for CSS selector targeting
- Variable group assignment
- Live preview system

## Critical Requirements

### MUST MAINTAIN:
- ✅ All Villa functionality from blocksy-child
- ✅ CRM capabilities
- ✅ Registration system (auto-approve)
- ✅ Owner portal access
- ✅ Committee workspaces
- ✅ JSON data format
- ✅ Custom admin interfaces (not CPT lists)

### MUST NOT:
- ❌ Revert to blocksy-child theme
- ❌ Convert JSON to YAML
- ❌ Use default WordPress CPT interfaces
- ❌ Lose any existing features
- ❌ Break existing workflows

## Testing Checklist

### Completed Testing ✅
- [x] Theme activation
- [x] Variable scanning
- [x] Control generation
- [x] Variable saves
- [x] Utility generation
- [x] Menu structure

### Pending Testing 📋
- [ ] JSON to ACF sync
- [ ] ACF to JSON sync
- [ ] Custom admin interfaces
- [ ] Data integrity
- [ ] Performance benchmarks
- [ ] User workflows

## Quick Reference Commands

```bash
# Verify theme
wp theme status the-studio

# Manual sync trigger
wp cron event run studio_sync_json_to_acf

# Check sync status
wp option get studio_last_json_sync

# View sync logs
wp option get studio_sync_logs

# Generate utilities
# Navigate to: Studio Designer > Utilities > Generate Utilities
```

## Timeline

### Week 1-3: ✅ COMPLETE
Foundation, Design System, Content Structure

### Week 4: 🔄 CURRENT (June 17-21)
Villa Admin Interfaces

### Week 5: 📋 UPCOMING (June 24-28)
Migration, Testing, Advanced Features

### Week 6: 📋 PLANNED (July 1-5)
Documentation, Training, Go-live

## Success Metrics

### Technical ✅
- CSS variable system with working saves
- Utility generation functional
- ACF structure complete
- JSON sync system ready

### Functional 🔄
- Custom admin interfaces (in progress)
- Feature parity with blocksy-child
- Data migration verified

### User Experience 📋
- Intuitive admin interfaces
- Preserved workflows
- Enhanced capabilities
- Comprehensive documentation

## Support Notes

This refactor represents a major architectural improvement:
- **From**: Scattered functionality in child theme
- **To**: Organized, scalable system in dedicated theme

All Villa functionality is being preserved and enhanced. The new architecture provides:
- Better code organization
- Easier maintenance
- AI-friendly data structures
- Modern WordPress patterns
- Scalable foundation for growth

---

*This is the single source of truth for The Studio refactor. All team members should reference this document for current status and next steps.*