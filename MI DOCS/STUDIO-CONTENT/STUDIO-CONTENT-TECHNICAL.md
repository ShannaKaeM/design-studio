# Studio Content Technical Documentation

## Data Architecture

### File Structure
```
/wp-content/themes/blocksy-child/villa-data/
├── owners/                    # Individual owner profiles
│   └── owner-{name}/         # e.g., owner-shanna-middleton/
│       └── owner.json        # Complete owner data
├── properties/               # Property records
│   └── unit-{number}/       # e.g., unit-209a/
│       └── property.json    # Complete property data
├── committees/              # Committee information
│   └── {committee-id}/      # e.g., tech-marketing/
│       └── committee.json   # Committee data
├── master-index.json        # Central registry and statistics
└── migration/               # Migration scripts and backups
```

### Data Relationships

#### Many-to-Many: Owners ↔ Properties
```
Owner (1) ←→ (N) Properties
Property (1) ←→ (N) Owners
```

**Owner Side:**
```json
{
  "owner_id": "owner-shanna-middleton",
  "properties": [
    {
      "unit_number": "209A",
      "role": "primary",
      "ownership_percentage": 100,
      "purchase_date": "2020-01-15",
      "status": "active"
    }
  ]
}
```

**Property Side:**
```json
{
  "ownership": {
    "current_owners": [
      {
        "owner_id": "owner-shanna-middleton",
        "ownership_percentage": 100,
        "start_date": "2020-01-15",
        "contact_preference": "email",
        "rental_management": false
      }
    ]
  }
}
```

### Data Schemas

#### Owner Schema
```json
{
  "owner_id": "string (owner-{normalized-name})",
  "hash_ids": ["array of legacy hash IDs for compatibility"],
  "status": "active|inactive|pending",
  
  "personal_info": {
    "first_name": "string",
    "last_name": "string",
    "full_name": "string",
    "email": "string",
    "phone": "string",
    "emergency_contact": {
      "name": "string",
      "relationship": "string",
      "phone": "string"
    }
  },
  
  "entity_info": {
    "entity_type": "individual|llc|trust|corporation",
    "company_name": "string|null",
    "legal_entity_name": "string",
    "tax_id": "string|null"
  },
  
  "addresses": {
    "primary": {
      "street": "string",
      "city": "string",
      "state": "string",
      "zip": "string",
      "country": "string"
    },
    "billing": {
      "same_as_primary": "boolean",
      "street": "string|null",
      "city": "string|null",
      "state": "string|null",
      "zip": "string|null",
      "country": "string|null"
    }
  },
  
  "properties": [
    {
      "unit_number": "string",
      "role": "primary|secondary",
      "ownership_percentage": "number",
      "purchase_date": "date|null",
      "status": "active|sold|pending"
    }
  ],
  
  "committee_roles": [
    {
      "committee_id": "string",
      "position": "string",
      "start_date": "date",
      "end_date": "date|null",
      "status": "active|inactive"
    }
  ],
  
  "wordpress_integration": {
    "wp_user_id": "number|null",
    "registration_status": "pending|registered|active",
    "registration_date": "datetime|null",
    "last_login": "datetime|null"
  },
  
  "preferences": {
    "communication": {
      "email_opt_in": "boolean",
      "sms_opt_in": "boolean",
      "mail_opt_in": "boolean"
    },
    "privacy": {
      "show_in_directory": "boolean",
      "show_email": "boolean",
      "show_phone": "boolean"
    }
  },
  
  "crm_data": {
    "notes": "string",
    "tags": ["array of strings"],
    "last_contact": "datetime|null",
    "relationship_score": "number|null"
  },
  
  "metadata": {
    "created_date": "datetime",
    "last_modified": "datetime",
    "modified_by": "string",
    "data_source": "string",
    "version": "number"
  }
}
```

#### Property Schema
```json
{
  "unit_details": {
    "unit_number": "string",
    "display_name": "string",
    "description": "string",
    "floor_level": "number",
    "stories": "number",
    "bedrooms": "number",
    "bathrooms": "number",
    "floorplan_type": "string",
    "square_footage": "number",
    "balcony_type": "string",
    "view_type": "string",
    "amenities_access": ["array of strings"]
  },
  
  "listing_status": {
    "for_rent": "boolean",
    "for_sale": "boolean",
    "rental_platforms": {
      "airbnb": "url|null",
      "vrbo": "url|null",
      "direct_booking": "url|null"
    },
    "sale_platforms": {
      "mls": "string|null",
      "fsbo": "boolean",
      "realtor_contact": "string|null"
    }
  },
  
  "media": {
    "featured_image": "path",
    "gallery_images": ["array of paths"],
    "floorplan_image": "path"
  },
  
  "ownership": {
    "current_owners": [
      {
        "owner_id": "string",
        "ownership_percentage": "number",
        "start_date": "date|null",
        "contact_preference": "email|phone|mail",
        "rental_management": "boolean"
      }
    ],
    "ownership_history": ["array of past owners"]
  },
  
  "maintenance": {
    "last_inspection": "date",
    "next_inspection": "date",
    "repair_requests": ["array"],
    "maintenance_schedule": ["array"]
  },
  
  "financial": {
    "hoa_dues": {
      "monthly_amount": "number",
      "current_status": "current|late|delinquent",
      "last_payment": "date",
      "next_due": "date"
    },
    "assessments": ["array"],
    "violations": ["array"],
    "insurance": {
      "policy_number": "string",
      "provider": "string",
      "expiration": "date",
      "coverage_amount": "number"
    }
  },
  
  "rental_performance": {
    "occupancy_rate_2023": "number",
    "average_nightly_rate": "number",
    "total_revenue_2023": "number",
    "guest_rating": "number|null",
    "total_bookings_2023": "number"
  }
}
```

## PHP Implementation

### Data Access Layer
```php
class VillaDataManager {
    private $data_path;
    
    public function __construct() {
        $this->data_path = get_stylesheet_directory() . '/villa-data';
    }
    
    public function getOwner($owner_id) {
        $file = $this->data_path . '/owners/' . $owner_id . '/owner.json';
        return json_decode(file_get_contents($file), true);
    }
    
    public function getProperty($unit_number) {
        $file = $this->data_path . '/properties/unit-' . strtolower($unit_number) . '/property.json';
        return json_decode(file_get_contents($file), true);
    }
    
    public function updateOwner($owner_id, $data) {
        $file = $this->data_path . '/owners/' . $owner_id . '/owner.json';
        $data['metadata']['last_modified'] = date('Y-m-d H:i:s');
        return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }
}
```

### AJAX Endpoints
```php
// Get owner data
add_action('wp_ajax_get_owner_data', 'ajax_get_owner_data');
function ajax_get_owner_data() {
    $owner_id = sanitize_text_field($_POST['owner_id']);
    $manager = new VillaDataManager();
    $owner = $manager->getOwner($owner_id);
    wp_send_json_success($owner);
}

// Update owner
add_action('wp_ajax_update_owner', 'ajax_update_owner');
function ajax_update_owner() {
    $owner_id = sanitize_text_field($_POST['owner_id']);
    $data = json_decode(stripslashes($_POST['data']), true);
    $manager = new VillaDataManager();
    $result = $manager->updateOwner($owner_id, $data);
    wp_send_json_success($result);
}
```

## JavaScript Integration

### Data Loading
```javascript
class VillaDataClient {
    constructor() {
        this.ajaxUrl = villa_ajax.ajax_url;
        this.nonce = villa_ajax.nonce;
    }
    
    async getOwner(ownerId) {
        const response = await fetch(this.ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'get_owner_data',
                owner_id: ownerId,
                _ajax_nonce: this.nonce
            })
        });
        
        const data = await response.json();
        return data.success ? data.data : null;
    }
    
    async getProperty(unitNumber) {
        // Similar implementation
    }
}
```

### Block Integration
```javascript
registerBlockType('studio/owner-profile', {
    edit: function(props) {
        const [ownerData, setOwnerData] = useState(null);
        const client = new VillaDataClient();
        
        useEffect(() => {
            if (props.attributes.ownerId) {
                client.getOwner(props.attributes.ownerId)
                    .then(data => setOwnerData(data));
            }
        }, [props.attributes.ownerId]);
        
        return (
            <div className="owner-profile">
                {ownerData && (
                    <>
                        <h2>{ownerData.personal_info.full_name}</h2>
                        <p>{ownerData.personal_info.email}</p>
                        {/* More fields */}
                    </>
                )}
            </div>
        );
    }
});
```

## Data Integrity Rules

### Validation Rules
1. **Owner IDs** must follow format: `owner-{normalized-name}`
2. **Ownership percentages** must total 100% per property
3. **Email addresses** must be valid format
4. **Phone numbers** should be normalized to (XXX) XXX-XXXX
5. **Dates** must be in YYYY-MM-DD format

### Bidirectional Sync
When updating owner ↔ property relationships:
1. Update owner's properties array
2. Update property's current_owners array
3. Validate ownership percentages = 100%
4. Update metadata timestamps
5. Create audit log entry

### Data Migration
```bash
# Backup before migration
cp -r villa-data villa-data-backup-$(date +%Y%m%d)

# Run migration scripts
php migration/consolidate-owners.php
php migration/update-property-owners.php
php migration/migrate-committees.php

# Verify data integrity
php migration/verify-data.php
```

## Security Considerations

### Access Control
```php
// Check user capabilities
if (!current_user_can('manage_villa_data')) {
    wp_die('Unauthorized access');
}

// Sanitize all inputs
$owner_id = sanitize_text_field($_POST['owner_id']);
$email = sanitize_email($_POST['email']);
$phone = preg_replace('/[^0-9-()]/', '', $_POST['phone']);
```

### Data Protection
1. **No sensitive data** in JSON files (SSN, bank info)
2. **File permissions** set to 644 for JSON files
3. **Directory permissions** set to 755
4. **HTTPS required** for admin pages
5. **Nonce verification** on all AJAX calls

## Performance Optimization

### Caching Strategy
```php
class VillaDataCache {
    private $cache_key_prefix = 'villa_data_';
    private $cache_duration = 3600; // 1 hour
    
    public function get($key) {
        return get_transient($this->cache_key_prefix . $key);
    }
    
    public function set($key, $data) {
        set_transient($this->cache_key_prefix . $key, $data, $this->cache_duration);
    }
    
    public function clear($key = null) {
        if ($key) {
            delete_transient($this->cache_key_prefix . $key);
        } else {
            // Clear all villa data cache
            global $wpdb;
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_villa_data_%'");
        }
    }
}
```

### Search Indexing
```php
// Build search index
function build_owner_search_index() {
    $index = [];
    $owners_dir = get_stylesheet_directory() . '/villa-data/owners';
    
    foreach (glob($owners_dir . '/owner-*/owner.json') as $file) {
        $owner = json_decode(file_get_contents($file), true);
        $index[] = [
            'id' => $owner['owner_id'],
            'name' => $owner['personal_info']['full_name'],
            'email' => $owner['personal_info']['email'],
            'properties' => array_column($owner['properties'], 'unit_number')
        ];
    }
    
    update_option('villa_owner_search_index', $index);
}
```

## Troubleshooting

### Common Issues

1. **Missing Owner References**
   - Check owner ID format
   - Verify owner file exists
   - Run mapping script

2. **Ownership Percentage Mismatch**
   - Validate all owners for property
   - Check for rounding errors
   - Update both sides of relationship

3. **Performance Issues**
   - Enable caching
   - Build search indexes
   - Optimize file I/O

4. **Data Corruption**
   - Restore from backup
   - Validate JSON syntax
   - Check file permissions
