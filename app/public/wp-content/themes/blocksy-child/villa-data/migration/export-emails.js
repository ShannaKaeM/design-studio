const fs = require('fs');

// Read the final analysis data
const analysisData = JSON.parse(fs.readFileSync('./final-ownership-analysis.json', 'utf8'));

// Extract unique emails with owner details
const emailList = [];
const processedEmails = new Set();

analysisData.property_list.forEach(property => {
  property.all_emails.forEach(email => {
    if (email && !processedEmails.has(email)) {
      processedEmails.add(email);
      
      // Determine primary contact info
      let ownerName = property.primary_owner;
      let ownerType = property.entity_type;
      let company = property.company;
      
      // If this is the secondary email, use secondary owner info
      if (email === property.secondary_email) {
        ownerName = property.secondary_owner || property.primary_owner;
      }
      
      emailList.push({
        email: email,
        owner_name: ownerName,
        company: company,
        entity_type: ownerType,
        unit: property.unit,
        primary_contact: email === property.primary_email ? 'Yes' : 'No'
      });
    }
  });
});

// Sort by owner name
emailList.sort((a, b) => a.owner_name.localeCompare(b.owner_name));

// Create CSV content
const csvHeaders = 'Email,Owner Name,Company,Entity Type,Unit,Primary Contact\n';
const csvRows = emailList.map(item => 
  `"${item.email}","${item.owner_name}","${item.company}","${item.entity_type}","${item.unit}","${item.primary_contact}"`
).join('\n');

const csvContent = csvHeaders + csvRows;

// Save CSV file
fs.writeFileSync('./villa-owners-email-list.csv', csvContent);

// Create simple email-only list
const simpleEmailList = emailList.map(item => item.email).join('\n');
fs.writeFileSync('./villa-owners-emails-only.txt', simpleEmailList);

console.log('=== EMAIL EXPORT COMPLETE ===');
console.log(`Total unique emails: ${emailList.length}`);
console.log(`Primary contacts: ${emailList.filter(item => item.primary_contact === 'Yes').length}`);
console.log(`Secondary contacts: ${emailList.filter(item => item.primary_contact === 'No').length}`);
console.log(`Entities: ${emailList.filter(item => item.entity_type === 'Entity').length}`);
console.log(`Individuals: ${emailList.filter(item => item.entity_type === 'Individual').length}`);

console.log('\nFiles created:');
console.log('- villa-owners-email-list.csv (detailed)');
console.log('- villa-owners-emails-only.txt (simple list)');

// Show sample of emails
console.log('\nSample emails:');
emailList.slice(0, 5).forEach(item => {
  console.log(`${item.email} - ${item.owner_name} (${item.unit})`);
});

if (emailList.length > 5) {
  console.log(`... and ${emailList.length - 5} more`);
}
