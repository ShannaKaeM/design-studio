const fs = require('fs');

// Read the authoritative current owners data
const csvData = fs.readFileSync('./current-owners-complete.csv', 'utf8');

// Parse CSV data
function parseCSV(csvText) {
  const lines = csvText.split('\n');
  const headers = lines[0].split(',');
  const data = [];
  
  for (let i = 1; i < lines.length; i++) {
    if (lines[i].trim()) {
      const values = [];
      let current = '';
      let inQuotes = false;
      
      for (let j = 0; j < lines[i].length; j++) {
        const char = lines[i][j];
        if (char === '"') {
          inQuotes = !inQuotes;
        } else if (char === ',' && !inQuotes) {
          values.push(current.trim());
          current = '';
        } else {
          current += char;
        }
      }
      values.push(current.trim()); // Add the last value
      
      const row = {};
      headers.forEach((header, index) => {
        row[header.trim()] = values[index] ? values[index].replace(/"/g, '') : '';
      });
      data.push(row);
    }
  }
  return data;
}

// Generate all 116 units for reference
function generateAllUnits() {
  const units = [];
  
  // Floor 1: 101A-118B
  for (let i = 1; i <= 18; i++) {
    const unitNum = i.toString().padStart(2, '0');
    units.push(`1${unitNum}A`);
    units.push(`1${unitNum}B`);
  }
  
  // Floor 2: 201A-220B  
  for (let i = 1; i <= 20; i++) {
    const unitNum = i.toString().padStart(2, '0');
    units.push(`2${unitNum}A`);
    units.push(`2${unitNum}B`);
  }
  
  // Floor 3: 301A-320B
  for (let i = 1; i <= 20; i++) {
    const unitNum = i.toString().padStart(2, '0');
    units.push(`3${unitNum}A`);
    units.push(`3${unitNum}B`);
  }
  
  // Floor 4: 404A-416B (limited units)
  const floor4Units = ['404A', '404B', '407A', '407B', '408A', '408B', '411A', '411B', '412A', '412B', '416A', '416B'];
  units.push(...floor4Units);
  
  return units.slice(0, 116); // Ensure exactly 116 units
}

const csvOwners = parseCSV(csvData);
const allUnits = generateAllUnits();

console.log('=== FINAL OWNERSHIP ANALYSIS ===');
console.log(`CSV Records: ${csvOwners.length}`);
console.log(`Expected Units: ${allUnits.length}`);

// Create comprehensive property list
const propertyList = allUnits.map(unit => {
  const ownerData = csvOwners.find(row => row.Property === unit);
  
  if (ownerData) {
    // Determine entity type and primary owner
    let entityType = 'Individual';
    let primaryOwner = '';
    let primaryEmail = '';
    let primaryPhone = '';
    
    if (ownerData.Company && ownerData.Company.trim()) {
      entityType = 'Entity';
      primaryOwner = ownerData.Company;
      primaryEmail = ownerData['Company Email'] || '';
      primaryPhone = ownerData['Company Phone'] || '';
    } else {
      primaryOwner = ownerData['Owner 1'] || '';
      primaryEmail = ownerData['Owner 1 Email'] || '';
      primaryPhone = ownerData['Owner 1 Phone'] || '';
    }
    
    // Secondary owner info
    const secondaryOwner = ownerData['Owner 2'] || '';
    const secondaryEmail = ownerData['Owner 2 Email'] || '';
    const secondaryPhone = ownerData['Owner 2 Phone'] || '';
    
    // Collect all emails for campaign
    const emails = [primaryEmail, secondaryEmail, ownerData['Company Email'] || ''].filter(e => e.trim());
    
    return {
      unit: unit,
      entity_type: entityType,
      company: ownerData.Company || '',
      primary_owner: primaryOwner,
      primary_email: primaryEmail,
      primary_phone: primaryPhone,
      secondary_owner: secondaryOwner,
      secondary_email: secondaryEmail,
      secondary_phone: secondaryPhone,
      all_emails: emails,
      email_count: emails.length,
      status: primaryOwner ? 'COMPLETE' : 'MISSING_OWNER',
      data_source: 'AUTHORITATIVE_CSV'
    };
  } else {
    return {
      unit: unit,
      entity_type: 'Unknown',
      company: '',
      primary_owner: 'NO_DATA',
      primary_email: '',
      primary_phone: '',
      secondary_owner: '',
      secondary_email: '',
      secondary_phone: '',
      all_emails: [],
      email_count: 0,
      status: 'MISSING_DATA',
      data_source: 'NONE'
    };
  }
});

// Sort by unit number
propertyList.sort((a, b) => {
  const aNum = parseInt(a.unit.substring(0, 3));
  const bNum = parseInt(b.unit.substring(0, 3));
  if (aNum !== bNum) return aNum - bNum;
  return a.unit.localeCompare(b.unit);
});

// Generate statistics
const stats = {
  total_units: 116,
  units_with_data: propertyList.filter(p => p.status === 'COMPLETE').length,
  units_missing_data: propertyList.filter(p => p.status === 'MISSING_DATA').length,
  entities: propertyList.filter(p => p.entity_type === 'Entity').length,
  individuals: propertyList.filter(p => p.entity_type === 'Individual').length,
  units_with_emails: propertyList.filter(p => p.email_count > 0).length,
  total_emails: propertyList.reduce((sum, p) => sum + p.email_count, 0)
};

// Extract all unique emails for campaign
const allEmails = [];
const emailOwnerMap = {};

propertyList.forEach(property => {
  property.all_emails.forEach(email => {
    if (email && !allEmails.includes(email)) {
      allEmails.push(email);
      emailOwnerMap[email] = {
        unit: property.unit,
        owner: property.primary_owner,
        company: property.company,
        type: property.entity_type
      };
    }
  });
});

// Multi-unit owners analysis
const ownerUnits = {};
propertyList.forEach(property => {
  if (property.primary_owner && property.primary_owner !== 'NO_DATA') {
    const key = property.company || property.primary_owner;
    if (!ownerUnits[key]) {
      ownerUnits[key] = {
        name: key,
        type: property.entity_type,
        units: [],
        emails: new Set()
      };
    }
    ownerUnits[key].units.push(property.unit);
    property.all_emails.forEach(email => {
      if (email) ownerUnits[key].emails.add(email);
    });
  }
});

const multiUnitOwners = Object.values(ownerUnits)
  .filter(owner => owner.units.length > 1)
  .map(owner => ({
    ...owner,
    emails: Array.from(owner.emails),
    unit_count: owner.units.length
  }))
  .sort((a, b) => b.unit_count - a.unit_count);

// Create final analysis
const finalAnalysis = {
  generated_date: new Date().toISOString(),
  data_source: 'AUTHORITATIVE_CSV',
  summary_stats: stats,
  property_list: propertyList,
  email_campaign_data: {
    total_unique_emails: allEmails.length,
    email_list: allEmails,
    email_owner_mapping: emailOwnerMap
  },
  multi_unit_owners: multiUnitOwners,
  missing_data_units: propertyList.filter(p => p.status === 'MISSING_DATA')
};

// Save analysis
fs.writeFileSync('./final-ownership-analysis.json', JSON.stringify(finalAnalysis, null, 2));

// Save email campaign data
fs.writeFileSync('./email-campaign-final.json', JSON.stringify({
  generated_date: new Date().toISOString(),
  total_emails: allEmails.length,
  emails: allEmails,
  owner_details: emailOwnerMap
}, null, 2));

console.log('\n=== FINAL STATISTICS ===');
console.log(`Total Units: ${stats.total_units}`);
console.log(`Units with Complete Data: ${stats.units_with_data}`);
console.log(`Units Missing Data: ${stats.units_missing_data}`);
console.log(`Entities: ${stats.entities}`);
console.log(`Individuals: ${stats.individuals}`);
console.log(`Units with Emails: ${stats.units_with_emails}`);
console.log(`Total Email Addresses: ${stats.total_emails}`);
console.log(`Unique Email Addresses: ${allEmails.length}`);

console.log('\n=== MULTI-UNIT OWNERS ===');
multiUnitOwners.forEach(owner => {
  console.log(`${owner.name}: ${owner.unit_count} units (${owner.units.join(', ')})`);
});

console.log('\n=== MISSING DATA UNITS ===');
finalAnalysis.missing_data_units.forEach(unit => {
  console.log(`${unit.unit}: No ownership data`);
});

console.log('\nFinal analysis saved to final-ownership-analysis.json');
console.log('Email campaign data saved to email-campaign-final.json');
