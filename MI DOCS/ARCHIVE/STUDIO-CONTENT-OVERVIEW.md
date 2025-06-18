# Studio Content System Overview

## Purpose
Studio Content transforms Villa Capriani's real-world property and owner data into a comprehensive content management system that serves as both production CRM functionality and test content for Studio block development.

## Core Components

### 1. Villa Data System
Real-world data from a 116-unit oceanfront condominium complex:
- **Owners**: 154 unique property owners with full profiles
- **Properties**: 116 units with detailed specifications
- **Committees**: Board and committee member information
- **Relationships**: Many-to-many owner-property associations

### 2. Data Structure

#### JSON-Based Storage
```
/villa-data/
├── owners/           # Individual owner profiles
│   └── owner-{id}/
│       └── owner.json
├── properties/       # Property records
│   └── unit-{number}/
│       └── property.json
├── committees/       # Committee data
│   └── {committee-id}/
│       └── committee.json
└── master-index.json # Central registry
```

#### Owner Schema
```json
{
  "owner_id": "owner-shanna-middleton",
  "personal_info": { /* name, email, phone */ },
  "entity_info": { /* individual/LLC details */ },
  "properties": [ /* owned units */ ],
  "wordpress_integration": { /* WP user data */ },
  "crm_data": { /* tags, notes, communications */ }
}
```

#### Property Schema
```json
{
  "unit_details": { /* specs, amenities */ },
  "ownership": { /* current owners, history */ },
  "financial": { /* HOA, insurance */ },
  "maintenance": { /* schedules, requests */ },
  "rental_performance": { /* if applicable */ }
}
```

### 3. Integration Points

#### WordPress Admin
- **Users Section**: Owner management, CRM, email tools
- **Properties Section**: Unit management, ownership tracking
- **Community Section**: Committees, communications, events

#### Studio Blocks
- Dynamic content population
- Real-world test data
- AI training examples

#### Future APIs
- REST endpoints for external access
- GraphQL for complex queries
- Webhook notifications

## Use Cases

### 1. Property Management
- Owner directory with contact info
- Property ownership tracking
- HOA payment status
- Maintenance scheduling

### 2. Communications
- Email campaigns to owners
- Committee announcements
- Event invitations
- Document distribution

### 3. Studio Development
- Test content for new blocks
- Real-world data relationships
- Performance testing with actual data
- AI content generation training

### 4. CRM Functionality
- Owner profiles and history
- Communication preferences
- Relationship tracking
- Activity logging

## Benefits

### For Villa Capriani
- Centralized owner/property data
- Automated communications
- Simplified administration
- Professional web presence

### For Studio Development
- Real-world test content
- Complex data relationships
- Production-ready examples
- Scalability testing

### For AI Integration
- Rich training data
- Natural language queries
- Content generation examples
- Pattern recognition

## Technical Architecture

### Data Flow
1. **Input**: Admin forms, imports, API calls
2. **Storage**: JSON files with validation
3. **Processing**: PHP classes for CRUD operations
4. **Output**: Admin UI, blocks, APIs

### Security
- Role-based access control
- Data validation and sanitization
- Secure file permissions
- Audit logging

### Performance
- Direct file access (no DB queries)
- Lazy loading for large datasets
- Caching for frequently accessed data
- Optimized search indexing

## Current Status

### Completed
- ✅ Owner data consolidation (154 unique owners)
- ✅ Property file structure (116 units)
- ✅ Admin interface (Users, Properties, Community)
- ✅ Basic CRM functionality

### In Progress
- 🔄 Missing owner mapping (29 properties)
- 🔄 Committee data migration
- 🔄 WordPress user integration

### Planned
- 📋 Studio block integration
- 📋 AI content features
- 📋 REST API development
- 📋 Advanced CRM features
