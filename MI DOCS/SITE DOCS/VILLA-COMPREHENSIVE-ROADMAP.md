# Villa Capriani Comprehensive Site Roadmap

## 📊 **Current Progress Status**

### ✅ **COMPLETED** (Phase 1 - Foundation)
- [x] JSON directory structure in `/villa-data/`
- [x] Sample property, owner, committee JSON files
- [x] Final ownership analysis (54 unique emails ready)
- [x] WordPress Villa Management admin interface
- [x] Property List admin page with filtering
- [x] Owners admin page with contact management
- [x] Email export system for campaigns
- [x] Updated committee names (Architecture, Bylaws, Finance, etc.)
- [x] Old JSON Content Manager removed

### 🔄 **CURRENT FOCUS** (Community Engagement & Committee Registration)
- [ ] **Committee Structure & Descriptions** - Create all 5 committee JSON files with full descriptions
- [ ] **Owner Registration System** - SureMembers integration for first-time community login
- [ ] **Committee Registration Portal** - Allow owners to review and join committees
- [ ] **Admin Posting System** - Owner announcements with email distribution
- [ ] **SureDash Committee Spaces** - Set up committee workspaces and collaboration
- [ ] **Initial Community Invitation** - Email campaign to 54 owners for community launch

### 🔄 **IN PROGRESS** (Phase 1 Completion)
- [ ] **Properties JSON System** - Complete property records for all 116 units
- [ ] **Owner Profile System** - Link owners to properties with full profiles
- [ ] **Committee Admin Page** - Management interface for committees

### 🎯 **NEXT PRIORITIES** (Phase 2 Planning)
- [ ] **Front-End Property Listings** - Public rental/sale marketplace
- [ ] **Studio Blocks Development** - Villa search, listing, detail blocks
- [ ] **Owner Dashboard** - SureDash integration for property management
- [ ] **Committee Registration** - SureMembers integration for signup

---

## **🏠 Properties JSON Architecture Plan**

### **Core Concept: Properties as Single Source of Truth**
Each property serves dual purposes:
1. **Front-End Marketing Tool** - Rental/sale listings with rich media and amenities
2. **Back-End Management Hub** - Complete property history, maintenance, financials

**Key Principle**: Owners come and go, but property records persist with complete history.

### **Property JSON Structure**
**Location**: `/villa-data/properties/unit-{number}/property.json`

#### **Front-End Marketing Fields**
```json
{
  "unit_details": {
    "unit_number": "107A",
    "display_name": "Oceanfront Paradise Villa",
    "description": "Stunning 3-bedroom oceanfront villa...",
    "floor_level": 1,
    "stories": 2,
    "bedrooms": 3,
    "bathrooms": 2,
    "square_footage": 1850,
    "floorplan_type": "Oceanfront Deluxe"
  },
  "amenities_access": {
    "ocean_view": true,
    "balcony_type": "Large Private",
    "pool_access": "Oceanfront Pools",
    "hot_tub_access": true,
    "beach_access": "Direct",
    "parking_spaces": 2,
    "elevator_access": true,
    "storage_unit": true
  },
  "listing_status": {
    "for_rent": true,
    "for_sale": false,
    "rental_rate_nightly": 450,
    "rental_rate_weekly": 2800,
    "sale_price": null,
    "availability_calendar": "link_to_calendar",
    "booking_platforms": {
      "airbnb": "https://airbnb.com/...",
      "vrbo": "https://vrbo.com/...",
      "direct_booking": "https://villacapriani.com/book/107a"
    }
  },
  "media": {
    "featured_image": "/villa-data/properties/unit-107a/images/featured.jpg",
    "gallery_images": [
      "/villa-data/properties/unit-107a/images/living-room.jpg",
      "/villa-data/properties/unit-107a/images/bedroom1.jpg",
      "/villa-data/properties/unit-107a/images/ocean-view.jpg"
    ],
    "floorplan_image": "/villa-data/properties/unit-107a/images/floorplan.pdf",
    "virtual_tour": "https://tour.link"
  }
}
```

#### **Back-End Management Fields**
```json
{
  "ownership": {
    "current_owners": [
      {
        "owner_id": "owner-guillaume-akouka",
        "ownership_percentage": 100,
        "primary_contact": true,
        "start_date": "2023-01-15"
      }
    ],
    "ownership_history": [
      {
        "owner_id": "previous-owner-id",
        "ownership_percentage": 100,
        "start_date": "2020-03-01",
        "end_date": "2023-01-14",
        "transfer_reason": "Sale"
      }
    ]
  },
  "maintenance_records": {
    "repair_requests": [
      {
        "request_id": "REQ-2024-001",
        "date_submitted": "2024-01-15",
        "description": "AC unit not cooling properly",
        "priority": "High",
        "status": "Completed",
        "assigned_vendor": "HVAC Solutions",
        "completion_date": "2024-01-18",
        "cost": 450.00
      }
    ],
    "maintenance_schedule": {
      "hvac_service": "2024-06-01",
      "deep_cleaning": "2024-03-15",
      "inspection": "2024-12-01"
    }
  },
  "financial_records": {
    "hoa_dues_status": "Current",
    "monthly_hoa_amount": 485.00,
    "assessments": [
      {
        "assessment_id": "ASSESS-2024-ROOF",
        "description": "Roof replacement project",
        "amount": 2500.00,
        "due_date": "2024-08-01",
        "status": "Paid"
      }
    ],
    "violation_tickets": []
  },
  "rental_performance": {
    "occupancy_rate_ytd": 78,
    "revenue_ytd": 45600.00,
    "average_nightly_rate": 425.00,
    "guest_rating": 4.8,
    "total_bookings_ytd": 42
  }
}
```

### **Owner Profile JSON Structure**
**Location**: `/villa-data/owners/owner-{id}/profile.json`

```json
{
  "personal_info": {
    "first_name": "Guillaume",
    "last_name": "Akouka",
    "email": "guillaume@example.com",
    "phone": "+1-555-123-4567",
    "entity_type": "Individual"
  },
  "properties_owned": [
    {
      "unit_number": "107A",
      "ownership_percentage": 100,
      "primary_residence": false,
      "rental_management": "Self-Managed"
    }
  ],
  "portal_preferences": {
    "dashboard_layout": "Property-Focused",
    "notification_preferences": {
      "maintenance_alerts": true,
      "financial_updates": true,
      "committee_announcements": true
    },
    "committee_memberships": ["tech-marketing", "finance"]
  },
  "contact_preferences": {
    "preferred_contact_method": "Email",
    "emergency_contact": {
      "name": "Emergency Contact Name",
      "phone": "+1-555-987-6543"
    }
  }
}
```

### **Front-End Filtering System**
**Filter Categories for Property Search:**

1. **Property Type**
   - Bedrooms: 1, 2, 3, 4+
   - Bathrooms: 1, 2, 3+
   - Floor Level: Ground, 2nd, 3rd, Penthouse

2. **Amenities**
   - Ocean View, Pool View, Garden View
   - Balcony Type: Private, Shared, Large
   - Beach Access: Direct, Short Walk
   - Parking: 1 Space, 2+ Spaces

3. **Availability**
   - For Rent, For Sale
   - Available Dates
   - Price Range

4. **Features**
   - Pet Friendly
   - Elevator Access
   - Storage Unit
   - Recently Renovated

### **Implementation Plan**

#### **Step 1: Create Property JSON Generator**
- Script to convert existing ownership data to full property JSON files
- Generate all 116 property files with current ownership data
- Include placeholder data for missing fields

#### **Step 2: Owner Profile Migration**
- Convert current owner data to full profile JSON files
- Link owners to their properties via owner_id references
- Handle multi-property owners and multi-owner properties

#### **Step 3: WordPress Admin Integration**
- Update Property List admin page to show full property data
- Add property editing interface for maintenance, financials
- Create owner profile management interface

#### **Step 4: Front-End Studio Blocks**
- Villa Search Block - Filter and search properties
- Villa Listing Block - Display property cards
- Villa Detail Block - Full property information page
- Owner Contact Block - Contact property owners

#### **Step 5: SureDash Integration**
- Owner dashboard reading from property JSON
- Maintenance request workflow
- Financial reporting and HOA management
- Committee access based on owner profiles

---

## **🚀 SureDash + SureMembers Integration Strategy**

### **Community Engagement Goals**
Based on your "Owner First Governance" model, the integration should support:
1. **Transparent Committee Participation** - Easy committee discovery and registration
2. **Collaborative Decision Making** - Committee workspaces for projects and discussions
3. **Community Communication** - Admin announcements with email distribution
4. **Owner Onboarding** - Seamless first-time registration and community introduction

### **Recommended SureDash Space Structure**

#### **1. Main Community Hub** (Posts/Discussion)
- **Purpose**: Central community announcements and general discussions
- **Content**: Welcome messages, community updates, general Q&A
- **Access**: All registered owners
- **Features**: Admin posting with email notification capability

#### **2. Committee Spaces** (Posts/Discussion for each)
- **Tech & Marketing Committee Space**
- **Architecture Committee Space** 
- **Bylaws Committee Space**
- **Finance Committee Space**
- **Operations Oversight Committee Space**

**Each Committee Space Includes:**
- Committee description and responsibilities
- Current projects and initiatives
- Meeting schedules and minutes
- Member roster and contact info
- Document sharing and resources

#### **3. Owner Resources Hub** (Collection)
- **Purpose**: Centralized resource library
- **Content**: 
  - Community bylaws and policies
  - Property management contacts
  - Maintenance request forms
  - Financial reporting and HOA information
  - Committee meeting minutes archive

#### **4. Committee Registration Portal** (Form)
- **Purpose**: Allow owners to join committees
- **Fields**:
  - Owner information (name, email, unit number)
  - Committee preferences (multiple selections allowed)
  - Availability and skills
  - Contact preferences
- **Integration**: Auto-add to selected committee spaces

### **SureMembers Integration Plan**

#### **Owner Registration Flow**
1. **Initial Invitation Email** → Sent to 54 owner emails
2. **Landing Page** → Community overview with committee descriptions
3. **SureMembers Registration** → Account creation with property linking
4. **Committee Selection** → Choose committees to join during onboarding
5. **Dashboard Access** → Personalized view of joined committees and resources

#### **User Roles & Permissions**
- **Community Admin** - Full access, posting, member management
- **Committee Chairs** - Moderate their committee spaces, post updates
- **Committee Members** - Participate in discussions, access resources
- **General Owners** - Access community hub, view committee info, register for committees

### **Technical Implementation**

#### **WordPress Integration Points**
- **Owner Data Sync** - SureMembers profiles linked to villa-data owner JSON
- **Committee Membership** - Update owner profiles with committee selections
- **Property Linking** - Connect SureMembers accounts to owned properties
- **Email Lists** - Export committee member lists for targeted communications

#### **SureDash Posting System**
- **Admin Announcements** - Community-wide posts with email notifications
- **Committee Updates** - Committee-specific posts and project updates
- **Event Scheduling** - Committee meetings and community events
- **Document Sharing** - Meeting minutes, financial reports, policy updates

### **Launch Sequence**

#### **Phase 1: Setup (Week 1)**
- [ ] Create all 5 committee spaces in SureDash
- [ ] Set up main community hub and resources collection
- [ ] Configure SureMembers registration flow
- [ ] Create committee registration form

#### **Phase 2: Content Population (Week 2)**
- [ ] Add committee descriptions and responsibilities
- [ ] Upload initial resources (bylaws, contacts, forms)
- [ ] Create welcome post and community guidelines
- [ ] Test registration and committee assignment flow

#### **Phase 3: Owner Invitation (Week 3)**
- [ ] Send initial community invitation emails to 54 owners
- [ ] Create onboarding sequence for new registrations
- [ ] Monitor registration and provide support
- [ ] Begin committee formation and initial meetings

#### **Phase 4: Active Community (Week 4+)**
- [ ] Regular admin announcements and updates
- [ ] Committee project launches and discussions
- [ ] Monthly community reports and engagement metrics
- [ ] Continuous improvement based on owner feedback

### **Success Metrics**
- **Registration Rate**: Target 70% of invited owners (38+ registrations)
- **Committee Participation**: Average 2.5 committees per active owner
- **Engagement**: 80% of owners active in community within 30 days
- **Communication**: 90% email open rate for community announcements

---
