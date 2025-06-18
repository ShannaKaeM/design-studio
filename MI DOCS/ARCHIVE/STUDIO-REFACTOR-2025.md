# Studio System & Villa Platform Comprehensive Refactor 2025

## Vision Statement

Transform Villa Capriani from a traditional HOA into an owner-first, democratically governed community platform that serves as both a property management system and a model for modern community governance. This refactor unifies the Studio Design System with a robust content management architecture using ACF Pro and modern database design.

## Core Principles

### 1. Owner-First Governance
- **No Hierarchy**: Committee "organizers" not "chairs"
- **Distributed Authority**: Each committee manages specific domain
- **Democratic Decisions**: All committees vote on major decisions
- **Transparency**: All activities visible to owners
- **No Executive Committee**: Committees collectively handle governance

### 2. Data Architecture Philosophy
- **Properties as Truth**: Villa units are the central organizing principle
- **Database-Driven**: Move from JSON files to ACF/database for performance
- **AI-Friendly**: Maintain file-based editing capabilities via ACF Local JSON
- **Version Controlled**: All configurations in git-trackable files

### 3. Studio Design System Integration
- **CSS Variables**: Continue using the dead-simple token system
- **Utility Classes**: Auto-generated from design tokens
- **Selector Builder**: Apply styles to any element, not just blocks
- **Component Library**: Reusable patterns for portal and frontend

## Technical Architecture

### Database Design (ACF Custom Post Types)

#### 1. **Properties (villa_property)**
Central entity that never changes, regardless of ownership
```yaml
Fields:
  - unit_number: text
  - unit_name: text
  - bedrooms: number
  - bathrooms: number
  - square_footage: number
  - floor_level: number
  - building: select
  - view_type: select
  - amenities: checkbox
  - floorplan_type: select
  - images: gallery
  - floorplan: image
  - rental_links: repeater
    - platform: select (Airbnb, VRBO, Direct)
    - url: url
  - for_sale: true/false
  - sale_link: url
Relationships:
  - current_owners: bidirectional to villa_owner
  - ownership_history: custom table
```

#### 2. **Owners (villa_owner)**
Owner profiles linked to WordPress users
```yaml
Fields:
  - personal_info: group
    - first_name: text
    - last_name: text
    - email: email
    - phone_primary: text
    - phone_type: select (mobile/home)
    - sms_alerts: true/false
    - emergency_contact: text
  - address: group
    - billing_address: textarea
    - mailing_address: textarea
  - entity_info: group
    - ownership_type: select (Individual, LLC, Trust)
    - entity_name: text
    - tax_id: text
  - preferences: group
    - communication: checkboxes
    - committee_interests: relationship
  - status: select (pending, active, inactive)
Relationships:
  - owned_properties: bidirectional to villa_property
  - committee_memberships: bidirectional to villa_committee
  - wordpress_user: user
```

#### 3. **Committees (villa_committee)**
Self-organizing volunteer groups
```yaml
Fields:
  - name: text
  - description: textarea
  - purpose: wysiwyg
  - meeting_schedule: text
  - google_meet_link: url
  - google_drive_folder: url
  - shared_calendar: url
  - status: select (active, forming, inactive)
  - organizer_guideline: textarea
Relationships:
  - members: bidirectional to villa_owner
  - organizers: relationship to villa_owner (multiple)
  - roadmap_items: bidirectional to villa_roadmap
```

#### 4. **Roadmap Items (villa_roadmap)**
Collaborative project management
```yaml
Fields:
  - title: text
  - description: wysiwyg
  - rationale: textarea
  - stage: select (ideas, considering, approved, in_progress, delayed, completed)
  - priority: select (low, medium, high, critical)
  - estimated_cost: number
  - estimated_timeline: text
  - attachments: repeater
    - file: file
    - description: text
  - discussion_enabled: true/false
  - voting_enabled: true/false
  - vote_count: number (calculated)
Relationships:
  - committee: bidirectional to villa_committee
  - submitted_by: relationship to villa_owner
  - assigned_to: relationship to villa_owner (multiple)
```

#### 5. **Surveys (villa_survey)**
Owner feedback collection
```yaml
Fields:
  - title: text
  - description: wysiwyg
  - survey_type: select (poll, feedback, formal_vote)
  - questions: flexible_content
    - text_question: text, required
    - multiple_choice: choices, single/multiple
    - scale_rating: min, max, labels
    - open_text: character_limit
  - settings: group
    - anonymous: true/false
    - one_per_owner: true/false
    - show_results: select (after_vote, after_close, never)
    - open_date: date_time
    - close_date: date_time
Relationships:
  - committee: relationship to villa_committee
  - responses: custom table
```

#### 6. **Formal Votes (villa_formal_vote)**
Official HOA decisions requiring quorum
```yaml
Fields:
  - title: text
  - proposal: wysiwyg
  - supporting_docs: repeater
  - vote_type: select (owner_vote, committee_consensus)
  - quorum_required: number (default: 33%)
  - pass_threshold: number (default: 50%)
  - voting_period: group
    - open_date: date_time
    - close_date: date_time
  - results: group
    - total_eligible: number
    - total_voted: number
    - votes_for: number
    - votes_against: number
    - abstentions: number
    - quorum_met: true/false
    - passed: true/false
For Committee Consensus:
  - committee_votes: repeater
    - committee: relationship
    - vote: select (for, against, abstain)
    - date_cast: date_time
```

### Custom Database Tables

```sql
-- Ownership history tracking
CREATE TABLE villa_ownership_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    property_id INT,
    owner_id INT,
    start_date DATE,
    end_date DATE,
    ownership_percentage DECIMAL(5,2),
    notes TEXT
);

-- Voting records for audit trail
CREATE TABLE villa_vote_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vote_type ENUM('roadmap', 'survey', 'formal'),
    vote_id INT,
    owner_id INT,
    vote_value TEXT,
    vote_weight DECIMAL(5,2),
    voted_at TIMESTAMP,
    ip_address VARCHAR(45)
);

-- Activity logging
CREATE TABLE villa_activity_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(255),
    object_type VARCHAR(50),
    object_id INT,
    details JSON,
    created_at TIMESTAMP
);

-- Registration tracking (for post-registration review)
CREATE TABLE villa_registration_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    owner_id INT,
    unit_numbers TEXT,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    email VARCHAR(255),
    phone VARCHAR(20),
    billing_address TEXT,
    submitted_at TIMESTAMP,
    auto_approved BOOLEAN DEFAULT TRUE,
    data_matched BOOLEAN,
    match_details JSON,
    review_status ENUM('pending_review', 'verified', 'flagged'),
    admin_notes TEXT,
    reviewed_by INT,
    reviewed_at TIMESTAMP
);
```

## Implementation Phases

### Phase 1: Foundation (Week 1-2)
1. **Install ACF Pro**
   - Configure Local JSON sync
   - Create acf-json directory in theme
   
2. **Create Custom Post Types**
   - Register all CPTs via ACF
   - Setup basic field groups
   - Configure relationships
   
3. **Data Migration**
   - Import existing JSON data to ACF
   - Validate all relationships
   - Create ownership history records
   
4. **Enhanced Registration System**
   - Build registration form with unit validation
   - Auto-approve if data matches database
   - Immediate access with review disclaimer
   - Admin interface for post-registration review
   - Auto-create WordPress users (no email verification)
   - Welcome email with "subject to review" notice

### Phase 2: Committee Collaboration (Week 3-4)
1. **Committee Workspaces**
   - Member management interface
   - Google Workspace integration (limited admin accounts)
   - Shared resource management
   
2. **Unified Roadmap System**
   - Master view with filters
   - Committee-specific views
   - Permission-based visibility
   - Voting mechanism
   
3. **Owner Portal Foundation**
   - Dashboard with owned units
   - Committee memberships
   - Active votes/surveys
   - Announcement center

#### Registration Flow Details
1. **Owner submits registration form**
   - Unit number(s), name, email, phone, billing address
   
2. **System validates against database**
   - Check if unit exists
   - Fuzzy match owner name
   - Compare provided data
   
3. **Automatic approval if match found**
   - Create WordPress user immediately
   - Grant owner portal access
   - Log registration for review
   - Send welcome email with disclaimer:
     ```
     Welcome! Your account is active and you can access all owner features.
     Note: All registrations are subject to verification. If any discrepancies 
     are found, we will contact you to resolve them.
     ```
   
4. **Admin review process**
   - Dashboard shows all registrations
   - Flag any mismatches for follow-up
   - Contact owners only if issues found
   - Bulk verify matching registrations

### Phase 3: Democratic Governance (Week 5-6)
1. **Voting Systems**
   - Informal polls (instant results)
   - Formal votes (quorum tracking)
   - Committee consensus voting
   - One vote per owner enforcement
   
2. **Survey Builder**
   - Complex question types
   - Conditional logic
   - Result visualization
   - Export capabilities
   
3. **Audit & Compliance**
   - Complete activity logging
   - Vote record keeping
   - Legal compliance reports
   - Historical data access

### Phase 4: Advanced Features (Week 7-8)
1. **Communication System**
   - SMS alerts (via Twilio or similar)
   - Email notifications
   - In-portal messaging
   - Committee discussions
   
2. **Analytics Dashboard**
   - Participation metrics
   - Financial overview
   - Community health indicators
   - Committee activity reports
   
3. **API Development**
   - REST endpoints for external access
   - Webhook support
   - Mobile app readiness

## Integration with Studio Design System

### CSS Architecture
```css
/* Base tokens remain in studio-vars.css */
:root {
  /* Portal-specific tokens */
  --st-portal-sidebar-width: 280px;
  --st-portal-header-height: 64px;
  --st-committee-color-base: #3b82f6;
  --st-owner-color-base: #10b981;
  --st-vote-positive: #22c55e;
  --st-vote-negative: #ef4444;
}

/* Component classes auto-generated */
.portal-card { }
.committee-workspace { }
.roadmap-kanban { }
.vote-widget { }
```

### ACF Local JSON Structure
```
/blocksy-child/acf-json/
├── group_properties.json
├── group_owners.json
├── group_committees.json
├── group_roadmaps.json
├── group_surveys.json
├── group_formal_votes.json
└── group_settings.json
```

### Frontend/Backend Unification
- Shared component library
- Consistent utility classes
- Single design token source
- Responsive portal layouts

## Security & Permissions

### Role Hierarchy (Flat Structure)
1. **Administrator**: System management only
2. **Committee Organizer**: Facilitate, not control
3. **Committee Member**: Full collaboration rights
4. **Property Owner**: Vote, view, participate
5. **Guest**: Public information only

### Permission Matrix
| Action | Owner | Committee Member | Organizer | Admin |
|--------|-------|------------------|-----------|--------|
| View all roadmaps | ✓ | ✓ | ✓ | ✓ |
| Vote on roadmaps | ✓ | ✓ | ✓ | ✓ |
| Create roadmap items | - | ✓ | ✓ | ✓ |
| Edit committee items | - | ✓ | ✓ | ✓ |
| Manage members | - | - | ✓ | ✓ |
| Create formal votes | - | ✓ | ✓ | ✓ |
| Approve registrations | - | - | - | ✓ |

## Google Workspace Integration

### Limited Account Strategy
- 1-2 Google Workspace admin accounts only
- Committees share resources via links
- No individual Google accounts required

### Integration Points
1. **Google Drive**
   - Shared folders per committee
   - Document templates
   - Meeting recordings storage
   
2. **Google Calendar**
   - Committee meeting schedules
   - Community events
   - Voting deadlines
   
3. **Google Meet**
   - Embedded meeting links
   - No plugin required
   - Recording capabilities

## Success Metrics

### Technical Goals
- Page load under 2 seconds
- 99.9% uptime
- Mobile-responsive portal
- Accessibility compliance

### Community Goals
- 50%+ owner registration in 6 months
- 30%+ participation in votes
- Active committee participation
- Transparent governance

### Platform Goals
- Fully replace traditional HOA model
- Demonstrate owner-first governance
- Create replicable system
- Open-source components

## Development Notes

### AI-Friendly Patterns
```yaml
# ACF Local JSON can be edited by AI
# Example: Create a new survey
{
  "key": "group_survey_example",
  "title": "Owner Satisfaction Survey",
  "fields": [
    {
      "key": "field_survey_title",
      "label": "Survey Title",
      "name": "title",
      "type": "text"
    }
  ]
}
```

### Migration Commands
```bash
# Import owners from JSON
wp villa import-owners --source=/villa-data/owners/

# Export for backup
wp villa export --format=json --output=/backups/

# Sync ACF fields
wp acf sync
```

### Testing Strategy
1. Unit tests for data integrity
2. Integration tests for workflows
3. User acceptance testing with committee volunteers
4. Performance testing with full data load

## Conclusion

This refactor creates a modern, democratic community platform that:
- Eliminates traditional HOA hierarchy
- Empowers owner participation
- Provides transparent governance
- Uses enterprise-grade architecture
- Maintains simplicity through Studio Design System
- Enables AI-assisted content management

The Villa Capriani platform will serve as a model for community-driven governance, demonstrating that technology can enable true democratic participation in community management.