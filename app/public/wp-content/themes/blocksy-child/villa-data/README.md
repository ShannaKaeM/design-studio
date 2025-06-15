# Villa Capriani JSON Data Structure

This directory contains all JSON-based data for the Villa Capriani community website, replacing traditional WordPress CPTs and database complexity.

## Directory Structure

```
villa-data/
├── properties/          # Individual property data
│   ├── unit-107a/
│   │   ├── property.json
│   │   ├── images/
│   │   └── documents/
│   └── unit-107b/
├── owners/             # Owner profile data
│   ├── owner-guillaume-akouka/
│   │   ├── profile.json
│   │   ├── preferences.json
│   │   └── billing.json
├── committees/         # Committee information
│   ├── tech-marketing/
│   ├── built-environment/
│   ├── compliance-legal/
│   ├── budget-finance/
│   └── operations-oversight/
├── community/          # Community-wide data
│   ├── announcements/
│   ├── surveys/
│   ├── events/
│   └── amenities/
├── roadmap/           # Project management
│   ├── projects/
│   └── ideas/
└── schema/            # JSON schema definitions
    ├── property-schema.json
    ├── owner-schema.json
    └── committee-schema.json
```

## Key Concepts

### Properties as Single Source of Truth
- Each property maintains complete historical records
- Properties persist through ownership changes
- All unit-related data (maintenance, financial, rental) tied to property
- Multiple owners per property supported
- Multiple properties per owner supported

### Owner Profiles
- Separate from WordPress users (initially)
- Can own multiple properties with different percentages
- Committee memberships and community engagement tracking
- Financial and communication preferences
- Portal access and dashboard customization

### Data Relationships
- **Many-to-Many**: Owners ↔ Properties
- **One-to-Many**: Properties ↔ Maintenance Records
- **One-to-Many**: Properties ↔ Financial Records
- **Many-to-Many**: Owners ↔ Committees

## Integration Points

### Front-End (Public Site)
- Property listings read from `properties/*/property.json`
- Search/filter system uses property JSON data
- Owner contact info pulled from property ownership records

### Back-End (Owner Portal)
- Owner dashboards read from owner profile + associated properties
- Committee access based on owner committee memberships
- Financial data aggregated from owned properties

### SureDash Integration
- JSON files serve as data source for workflows
- Repair requests, billing, and project management
- Committee collaboration and document management

## Data Management

### File Naming Conventions
- Properties: `unit-{number}` (e.g., `unit-107a`, `unit-205b`)
- Owners: `owner-{first-last}` (e.g., `owner-guillaume-akouka`)
- Committees: `{committee-slug}` (e.g., `tech-marketing`)

### Schema Validation
- JSON schemas in `/schema/` directory
- Validate data integrity before saves
- Ensure consistent data structure across all files

### Version Control
- All JSON files are version controlled
- Easy to track changes and rollback if needed
- Collaborative editing with proper merge handling

## Benefits

✅ **Zero Database Complexity** - No SQL queries or database management
✅ **AI-Friendly Structure** - Easy for AI to read, understand, and modify
✅ **Version Control Friendly** - Git-based change tracking
✅ **Highly Portable** - Easy to backup, migrate, or replicate
✅ **Developer Friendly** - Simple file-based editing and debugging
✅ **User Friendly** - WordPress admin interface for non-technical users
✅ **SureDash Compatible** - Direct integration with workflow systems

## Next Steps

1. **Complete Schema Definitions** - Finish all JSON schemas
2. **Data Migration** - Convert existing CSV/database data to JSON
3. **WordPress Admin Interface** - Build user-friendly editing interface
4. **Studio Blocks Integration** - Create blocks that read JSON data
5. **SureDash Integration** - Connect workflows to JSON data sources
6. **Testing & Validation** - Ensure data integrity and performance
