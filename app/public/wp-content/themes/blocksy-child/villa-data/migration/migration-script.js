const fs = require('fs');
const path = require('path');

// Load the original owners data
const ownersData = JSON.parse(fs.readFileSync('./original-owners-data.json', 'utf8'));

// Villa structure: 4 floors, units 01-29 per floor, A and B sides
// Floor 1: 101A-129A, 101B-129B (58 units)
// Floor 2: 201A-229A, 201B-229B (58 units) 
// Floor 3: 301A-329A, 301B-329B (58 units)
// Floor 4: 401A-429A, 401B-429B (58 units)
// Total: 232 possible units, but only 116 exist

function generateAllUnits() {
  const units = [];
  
  // Generate based on actual units found in data
  const existingUnits = new Set();
  ownersData.villa_community.members.forEach(owner => {
    if (owner.villa && owner.villa.unit) {
      existingUnits.add(owner.villa.unit);
    }
  });
  
  // Add existing units
  existingUnits.forEach(unit => units.push(unit));
  
  // Generate additional units to reach 116 total
  for (let floor = 1; floor <= 4; floor++) {
    for (let unitNum = 1; unitNum <= 29; unitNum++) {
      ['A', 'B'].forEach(side => {
        const unit = `${floor}${unitNum.toString().padStart(2, '0')}${side}`;
        if (!existingUnits.has(unit) && units.length < 116) {
          units.push(unit);
        }
      });
    }
  }
  
  return units.slice(0, 116).sort();
}

function getFloorplanType(bedrooms, bathrooms) {
  if (bedrooms === 1 && bathrooms === 1) return '1.1';
  if (bedrooms === 2 && bathrooms === 1) return '2.1';
  if (bedrooms === 2 && bathrooms === 2) return '2.2';
  if (bedrooms === 3 && bathrooms === 2) return '3.2';
  if (bedrooms === 3 && bathrooms === 3) return '3.3';
  return '2.2'; // default
}

function generatePropertyData(unitNumber, owner = null) {
  const floor = parseInt(unitNumber.charAt(0));
  const side = unitNumber.slice(-1);
  
  // Determine unit specs based on floor and side
  let bedrooms, bathrooms, sqft, viewType;
  
  if (floor === 4) {
    bedrooms = 3; bathrooms = 2; sqft = 1450; viewType = 'ocean';
  } else if (floor === 3) {
    bedrooms = 2; bathrooms = 2; sqft = 1200; viewType = 'ocean';
  } else if (floor === 2) {
    bedrooms = 2; bathrooms = 1; sqft = 1100; viewType = 'partial_ocean';
  } else {
    bedrooms = 1; bathrooms = 1; sqft = 950; viewType = 'garden';
  }
  
  const balconyType = side === 'A' ? 'oceanfront' : 'garden';
  
  return {
    unit_details: {
      unit_number: unitNumber,
      display_name: `Villa Capriani - Unit ${unitNumber}`,
      description: `Beautiful ${bedrooms}-bedroom, ${bathrooms}-bathroom unit with ${viewType} views. Located on floor ${floor} with ${balconyType} balcony access.`,
      floor_level: floor,
      stories: 1,
      bedrooms: bedrooms,
      bathrooms: bathrooms,
      floorplan_type: getFloorplanType(bedrooms, bathrooms),
      square_footage: sqft,
      balcony_type: balconyType,
      view_type: viewType,
      amenities_access: ["pool", "hot_tub", "beach", "cabana_bar", "restaurant", "fitness"]
    },
    listing_status: {
      for_rent: false,
      for_sale: false,
      rental_platforms: {
        airbnb: null,
        vrbo: null,
        direct_booking: null
      },
      sale_platforms: {
        mls: null,
        fsbo: null,
        realtor_contact: null
      }
    },
    media: {
      featured_image: `/villa-data/properties/unit-${unitNumber.toLowerCase()}/images/featured.jpg`,
      gallery_images: [
        `/villa-data/properties/unit-${unitNumber.toLowerCase()}/images/living-room.jpg`,
        `/villa-data/properties/unit-${unitNumber.toLowerCase()}/images/master-bedroom.jpg`,
        `/villa-data/properties/unit-${unitNumber.toLowerCase()}/images/kitchen.jpg`,
        `/villa-data/properties/unit-${unitNumber.toLowerCase()}/images/balcony.jpg`,
        `/villa-data/properties/unit-${unitNumber.toLowerCase()}/images/bathroom.jpg`
      ],
      floorplan_image: `/villa-data/properties/unit-${unitNumber.toLowerCase()}/images/floorplan.jpg`
    },
    ownership: {
      current_owners: owner ? [{
        owner_id: owner.id,
        ownership_percentage: 100,
        start_date: null,
        contact_preference: "email",
        rental_management: false
      }] : [],
      ownership_history: []
    },
    maintenance: {
      last_inspection: "2024-01-15",
      next_inspection: "2024-07-15",
      repair_requests: [],
      maintenance_schedule: [
        {
          type: "HVAC Filter Change",
          frequency: "quarterly",
          last_completed: "2024-01-15",
          next_due: "2024-04-15"
        },
        {
          type: "Deep Cleaning",
          frequency: "bi-annual",
          last_completed: "2023-12-01",
          next_due: "2024-06-01"
        }
      ]
    },
    financial: {
      hoa_dues: {
        monthly_amount: 450.00 + (floor * 25), // Higher floors pay more
        current_status: "current",
        last_payment: "2024-02-01",
        next_due: "2024-03-01"
      },
      assessments: [],
      violations: [],
      insurance: {
        policy_number: `INS-${unitNumber}-2024`,
        provider: "Coastal Insurance Co",
        expiration: "2024-12-31",
        coverage_amount: 350000 + (sqft * 100)
      }
    },
    rental_performance: {
      occupancy_rate_2023: 0,
      average_nightly_rate: 0,
      total_revenue_2023: 0,
      guest_rating: null,
      total_bookings_2023: 0
    },
    metadata: {
      created_date: "2024-01-01",
      last_updated: "2024-02-15",
      data_version: "1.0",
      sync_status: "current"
    }
  };
}

function generateOwnerProfile(owner) {
  const ownerId = `owner-${owner.id}`;
  
  return {
    personal_info: {
      owner_id: ownerId,
      first_name: owner.personal.firstName,
      last_name: owner.personal.lastName,
      email: owner.personal.email,
      phone: owner.contact?.home || owner.contact?.mobile || null,
      emergency_contact: {
        name: null,
        relationship: null,
        phone: null
      }
    },
    entity_info: {
      entity_type: "individual",
      company_name: null,
      legal_entity_name: `${owner.personal.firstName} ${owner.personal.lastName}`,
      tax_id: null
    },
    addresses: {
      primary: {
        street: owner.address?.street || null,
        city: owner.address?.city || null,
        state: owner.address?.state || null,
        zip: owner.address?.zip || null,
        country: "USA"
      },
      billing: {
        same_as_primary: true,
        street: null,
        city: null,
        state: null,
        zip: null,
        country: null
      }
    },
    properties_owned: owner.villa?.unit ? [{
      unit_number: owner.villa.unit,
      ownership_percentage: 100,
      acquisition_date: null,
      acquisition_price: null,
      primary_use: "personal",
      management_preference: "self_managed"
    }] : [],
    portal_access: {
      wordpress_user_id: null,
      login_email: owner.personal.email,
      account_status: owner.membership?.status || "active",
      last_login: null,
      dashboard_preferences: {
        default_view: "property_overview",
        notifications_enabled: true,
        email_frequency: "weekly"
      }
    },
    committee_memberships: [],
    communication_preferences: {
      announcement_delivery: "email",
      survey_notifications: true,
      maintenance_alerts: "email",
      financial_notifications: "email",
      emergency_contact_method: "email"
    },
    financial: {
      payment_method: {
        type: "manual",
        bank_account_last4: null,
        auto_pay_enabled: false,
        backup_payment: null
      },
      payment_history: [],
      outstanding_balances: [],
      credit_status: "good"
    },
    engagement: {
      survey_responses: [],
      roadmap_interactions: [],
      community_participation_score: 50
    },
    rental_management: {
      manages_own_rental: false,
      property_manager: null,
      rental_platforms: [],
      guest_communication_preference: null,
      cleaning_service: null,
      maintenance_contact: "hoa"
    },
    metadata: {
      profile_created: "2024-01-01",
      last_updated: "2024-02-15",
      data_version: "1.0",
      sync_status: "current",
      migrated_from: "villa_owners_data_2024"
    }
  };
}

// Main execution
console.log('Starting Villa Data Migration...');

const allUnits = generateAllUnits();
console.log(`Generated ${allUnits.length} units`);

// Create owners map
const ownersMap = new Map();
ownersData.villa_community.members.forEach(owner => {
  ownersMap.set(owner.id, owner);
  if (owner.villa?.unit) {
    ownersMap.set(owner.villa.unit, owner);
  }
});

console.log(`Found ${ownersData.villa_community.members.length} owners`);

// Generate all property files
allUnits.forEach(unitNumber => {
  const owner = ownersMap.get(unitNumber);
  const propertyData = generatePropertyData(unitNumber, owner);
  
  const unitDir = `../properties/unit-${unitNumber.toLowerCase()}`;
  if (!fs.existsSync(unitDir)) {
    fs.mkdirSync(unitDir, { recursive: true });
  }
  
  fs.writeFileSync(
    path.join(unitDir, 'property.json'),
    JSON.stringify(propertyData, null, 2)
  );
});

// Generate all owner profiles
ownersData.villa_community.members.forEach(owner => {
  const ownerProfile = generateOwnerProfile(owner);
  
  const ownerDir = `../owners/owner-${owner.id}`;
  if (!fs.existsSync(ownerDir)) {
    fs.mkdirSync(ownerDir, { recursive: true });
  }
  
  fs.writeFileSync(
    path.join(ownerDir, 'profile.json'),
    JSON.stringify(ownerProfile, null, 2)
  );
});

console.log('Migration complete!');
console.log(`Created ${allUnits.length} property files`);
console.log(`Created ${ownersData.villa_community.members.length} owner profiles`);
