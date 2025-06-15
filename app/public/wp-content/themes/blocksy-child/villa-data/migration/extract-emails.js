const fs = require('fs');

// Load the original owners data
const ownersData = JSON.parse(fs.readFileSync('./original-owners-data.json', 'utf8'));

// Extract all emails
const emails = [];
const ownersWithEmails = [];

ownersData.villa_community.members.forEach(owner => {
  if (owner.personal.email) {
    emails.push(owner.personal.email);
    ownersWithEmails.push({
      id: owner.id,
      name: `${owner.personal.firstName} ${owner.personal.lastName}`,
      email: owner.personal.email,
      unit: owner.villa?.unit || null,
      status: owner.membership?.status || 'active'
    });
  }
});

// Create email list for campaign
const emailData = {
  campaign_info: {
    total_emails: emails.length,
    extraction_date: new Date().toISOString().split('T')[0],
    purpose: "Committee signup invitation campaign",
    next_steps: [
      "Setup SureDash email campaign",
      "Create branded landing page",
      "Send committee signup invitations"
    ]
  },
  email_list: emails,
  owners_with_emails: ownersWithEmails,
  email_groups: {
    all_owners: emails,
    owners_with_units: ownersWithEmails.filter(o => o.unit).map(o => o.email),
    owners_without_units: ownersWithEmails.filter(o => !o.unit).map(o => o.email)
  }
};

// Save email data
fs.writeFileSync('./email-campaign-data.json', JSON.stringify(emailData, null, 2));

console.log(`Extracted ${emails.length} email addresses`);
console.log(`Owners with units: ${emailData.email_groups.owners_with_units.length}`);
console.log(`Owners without units: ${emailData.email_groups.owners_without_units.length}`);
console.log('Email data saved to email-campaign-data.json');
