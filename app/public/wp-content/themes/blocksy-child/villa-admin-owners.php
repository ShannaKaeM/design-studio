<?php
/**
 * Villa Owners Admin Page
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Check user permissions
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

// Load the analysis data
$analysis_file = get_stylesheet_directory() . '/villa-data/migration/final-ownership-analysis.json';
$analysis_data = null;

if (file_exists($analysis_file)) {
    $analysis_data = json_decode(file_get_contents($analysis_file), true);
}
?>

<div class="wrap">
    <h1>Villa Owners Management</h1>
    
    <?php if ($analysis_data): ?>
        
        <!-- Summary Stats -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0;">
            <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; text-align: center;">
                <h3 style="margin: 0; color: #0073aa;"><?php echo $analysis_data['summary_stats']['entities']; ?></h3>
                <p style="margin: 5px 0 0 0;">Entity Owners</p>
            </div>
            <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; text-align: center;">
                <h3 style="margin: 0; color: #00a32a;"><?php echo $analysis_data['summary_stats']['individuals']; ?></h3>
                <p style="margin: 5px 0 0 0;">Individual Owners</p>
            </div>
            <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; text-align: center;">
                <h3 style="margin: 0; color: #1976d2;"><?php echo $analysis_data['email_campaign_data']['total_unique_emails']; ?></h3>
                <p style="margin: 5px 0 0 0;">Email Addresses</p>
            </div>
            <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; text-align: center;">
                <h3 style="margin: 0; color: #f57c00;"><?php echo count($analysis_data['multi_unit_owners']); ?></h3>
                <p style="margin: 5px 0 0 0;">Multi-Unit Owners</p>
            </div>
        </div>

        <!-- Filter Controls -->
        <div style="background: #fff; padding: 15px; border: 1px solid #ddd; margin: 20px 0;">
            <h3>Filters</h3>
            <div style="display: flex; gap: 15px; align-items: center;">
                <label>
                    <input type="checkbox" id="filter-entities" checked> Show Entities
                </label>
                <label>
                    <input type="checkbox" id="filter-individuals" checked> Show Individuals
                </label>
                <label>
                    <input type="checkbox" id="filter-with-emails" checked> Show With Emails
                </label>
                <label>
                    <input type="checkbox" id="filter-multi-unit" checked> Show Multi-Unit
                </label>
                <input type="text" id="search-owners" placeholder="Search owners..." style="margin-left: 20px; padding: 5px;">
            </div>
        </div>

        <!-- Owners Table -->
        <div style="background: #fff; border: 1px solid #ddd; margin: 20px 0;">
            <table class="wp-list-table widefat">
                <thead>
                    <tr>
                        <th>Owner/Entity</th>
                        <th>Type</th>
                        <th>Company</th>
                        <th>Units Owned</th>
                        <th>Primary Email</th>
                        <th>Primary Phone</th>
                        <th>Secondary Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Create unique owners list
                    $owners = [];
                    foreach ($analysis_data['property_list'] as $property) {
                        if ($property['primary_owner'] !== 'NO_DATA') {
                            $key = $property['company'] ?: $property['primary_owner'];
                            
                            if (!isset($owners[$key])) {
                                $owners[$key] = [
                                    'name' => $property['primary_owner'],
                                    'company' => $property['company'],
                                    'entity_type' => $property['entity_type'],
                                    'primary_email' => $property['primary_email'],
                                    'primary_phone' => $property['primary_phone'],
                                    'secondary_owner' => $property['secondary_owner'],
                                    'secondary_email' => $property['secondary_email'],
                                    'secondary_phone' => $property['secondary_phone'],
                                    'units' => [],
                                    'emails' => []
                                ];
                            }
                            
                            $owners[$key]['units'][] = $property['unit'];
                            
                            // Collect all emails
                            foreach ($property['all_emails'] as $email) {
                                if ($email && !in_array($email, $owners[$key]['emails'])) {
                                    $owners[$key]['emails'][] = $email;
                                }
                            }
                        }
                    }
                    
                    // Sort by name
                    uasort($owners, function($a, $b) {
                        return strcasecmp($a['name'], $b['name']);
                    });
                    
                    foreach ($owners as $owner): ?>
                        <tr class="owner-row" 
                            data-type="<?php echo esc_attr($owner['entity_type']); ?>"
                            data-has-email="<?php echo !empty($owner['emails']) ? 'yes' : 'no'; ?>"
                            data-multi-unit="<?php echo count($owner['units']) > 1 ? 'yes' : 'no'; ?>"
                            data-name="<?php echo esc_attr(strtolower($owner['name'])); ?>">
                            
                            <td>
                                <strong><?php echo esc_html($owner['name']); ?></strong>
                            </td>
                            
                            <td>
                                <span class="entity-type <?php echo strtolower($owner['entity_type']); ?>">
                                    <?php echo esc_html($owner['entity_type']); ?>
                                </span>
                            </td>
                            
                            <td>
                                <?php if ($owner['company']): ?>
                                    <strong><?php echo esc_html($owner['company']); ?></strong>
                                <?php else: ?>
                                    <span style="color: #666;">—</span>
                                <?php endif; ?>
                            </td>
                            
                            <td>
                                <span class="unit-count"><?php echo count($owner['units']); ?></span>
                                <small style="display: block; color: #666;">
                                    <?php echo esc_html(implode(', ', $owner['units'])); ?>
                                </small>
                            </td>
                            
                            <td>
                                <?php if ($owner['primary_email']): ?>
                                    <a href="mailto:<?php echo esc_attr($owner['primary_email']); ?>">
                                        <?php echo esc_html($owner['primary_email']); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                            
                            <td><?php echo esc_html($owner['primary_phone']); ?></td>
                            
                            <td>
                                <?php if ($owner['secondary_owner']): ?>
                                    <strong><?php echo esc_html($owner['secondary_owner']); ?></strong><br>
                                    <?php if ($owner['secondary_email']): ?>
                                        <a href="mailto:<?php echo esc_attr($owner['secondary_email']); ?>">
                                            <?php echo esc_html($owner['secondary_email']); ?>
                                        </a><br>
                                    <?php endif; ?>
                                    <?php echo esc_html($owner['secondary_phone']); ?>
                                <?php else: ?>
                                    <span style="color: #666;">—</span>
                                <?php endif; ?>
                            </td>
                            
                            <td>
                                <button class="button button-small" onclick="viewOwnerDetails('<?php echo esc_js($owner['name']); ?>')">
                                    View Details
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Email Export Section -->
        <div style="background: #fff; border: 1px solid #ddd; margin: 20px 0; padding: 20px;">
            <h3>📧 Email Export</h3>
            <p>Export owner email addresses for campaigns and communications.</p>
            <div style="display: flex; gap: 10px;">
                <a href="<?php echo get_stylesheet_directory_uri(); ?>/villa-data/migration/villa-owners-email-list.csv" 
                   class="button button-primary" target="_blank">
                    Download Detailed CSV
                </a>
                <a href="<?php echo get_stylesheet_directory_uri(); ?>/villa-data/migration/villa-owners-emails-only.txt" 
                   class="button button-secondary" target="_blank">
                    Download Email List Only
                </a>
            </div>
        </div>

    <?php else: ?>
        <div class="notice notice-error">
            <p>Owner data not found. Please run the ownership analysis script first.</p>
        </div>
    <?php endif; ?>
</div>

<style>
.entity-type.entity { color: #0073aa; font-weight: bold; }
.entity-type.individual { color: #00a32a; }

.unit-count {
    background: #e3f2fd;
    padding: 2px 6px;
    border-radius: 3px;
    font-weight: bold;
    color: #1976d2;
}

.owner-row:hover { background: #f8f9fa; }
</style>

<script>
jQuery(document).ready(function($) {
    // Filter functionality
    function filterTable() {
        const showEntities = $('#filter-entities').is(':checked');
        const showIndividuals = $('#filter-individuals').is(':checked');
        const showWithEmails = $('#filter-with-emails').is(':checked');
        const showMultiUnit = $('#filter-multi-unit').is(':checked');
        const searchTerm = $('#search-owners').val().toLowerCase();
        
        $('.owner-row').each(function() {
            const $row = $(this);
            const type = $row.data('type');
            const hasEmail = $row.data('has-email') === 'yes';
            const isMultiUnit = $row.data('multi-unit') === 'yes';
            const name = $row.data('name');
            let show = true;
            
            // Type filters
            if (type === 'Entity' && !showEntities) show = false;
            if (type === 'Individual' && !showIndividuals) show = false;
            
            // Email filter
            if (showWithEmails && !hasEmail) show = false;
            
            // Multi-unit filter
            if (showMultiUnit && !isMultiUnit) show = false;
            
            // Search filter
            if (searchTerm && !name.includes(searchTerm)) show = false;
            
            $row.toggle(show);
        });
    }
    
    // Bind filter events
    $('#filter-entities, #filter-individuals, #filter-with-emails, #filter-multi-unit').change(filterTable);
    $('#search-owners').on('input', filterTable);
});

function viewOwnerDetails(ownerName) {
    alert('Owner details for: ' + ownerName + '\n\nFull owner profile management coming soon!');
}
</script>
