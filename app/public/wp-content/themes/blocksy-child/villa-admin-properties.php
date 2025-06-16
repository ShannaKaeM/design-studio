<?php
/**
 * Villa Property Admin List Page
 * Displays comprehensive property ownership data using the new consolidated data structure
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Check user permissions
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

// Load the VillaDataManager
require_once get_stylesheet_directory() . '/villa-data-manager.php';
$data_manager = new VillaDataManager();

// Get all properties and owners
$properties = $data_manager->getAllProperties();
$owners = $data_manager->getAllOwners();

// Calculate statistics
$stats = [
    'total_units' => count($properties),
    'units_with_owners' => 0,
    'units_missing_data' => 0,
    'units_need_review' => 0,
    'entities' => 0,
    'units_with_emails' => 0
];

// Build property list with owner data
$property_list = [];
foreach ($properties as $property_id => $property) {
    $unit_number = str_replace('unit-', '', $property_id);
    
    // Get owner data
    $primary_owner_data = null;
    $secondary_owner_data = null;
    $email_count = 0;
    $status = 'MISSING_DATA';
    $entity_type = 'Unknown';
    
    if (!empty($property['owners'])) {
        $stats['units_with_owners']++;
        
        // Get primary owner
        if (!empty($property['owners'][0])) {
            $primary_owner_data = $data_manager->getOwner($property['owners'][0]);
            if ($primary_owner_data) {
                $entity_type = !empty($primary_owner_data['entity_type']) ? $primary_owner_data['entity_type'] : 'Individual';
                if ($entity_type === 'Entity') {
                    $stats['entities']++;
                }
                
                // Count emails
                if (!empty($primary_owner_data['email'])) {
                    $email_count++;
                }
            }
        }
        
        // Get secondary owner
        if (!empty($property['owners'][1])) {
            $secondary_owner_data = $data_manager->getOwner($property['owners'][1]);
            if ($secondary_owner_data && !empty($secondary_owner_data['email'])) {
                $email_count++;
            }
        }
        
        $status = ($primary_owner_data || $secondary_owner_data) ? 'COMPLETE' : 'MISSING_OWNER';
    } else {
        $stats['units_missing_data']++;
    }
    
    if ($email_count > 0) {
        $stats['units_with_emails']++;
    }
    
    if ($status === 'MISSING_OWNER') {
        $stats['units_need_review']++;
    }
    
    $property_list[] = [
        'unit' => $unit_number,
        'entity_type' => $entity_type,
        'primary_owner' => $primary_owner_data ? $primary_owner_data['name'] : 'NO_DATA',
        'primary_email' => $primary_owner_data && !empty($primary_owner_data['email']) ? $primary_owner_data['email'] : '',
        'primary_phone' => $primary_owner_data && !empty($primary_owner_data['phone']) ? $primary_owner_data['phone'] : '',
        'secondary_owner' => $secondary_owner_data ? $secondary_owner_data['name'] : '',
        'secondary_email' => $secondary_owner_data && !empty($secondary_owner_data['email']) ? $secondary_owner_data['email'] : '',
        'email_count' => $email_count,
        'status' => $status,
        'data_source' => 'Consolidated JSON'
    ];
}

// Sort by unit number
usort($property_list, function($a, $b) {
    return intval($a['unit']) - intval($b['unit']);
});

?>
<div class="wrap">
    <h1>Villa Capriani - Properties</h1>
    
    <!-- Summary Statistics -->
    <div class="villa-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
            <h3>Total Units</h3>
            <div style="font-size: 2em; color: #0073aa;"><?php echo $stats['total_units']; ?></div>
        </div>
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
            <h3>Units with Owners</h3>
            <div style="font-size: 2em; color: #46b450;"><?php echo $stats['units_with_owners']; ?></div>
        </div>
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
            <h3>Missing Data</h3>
            <div style="font-size: 2em; color: #dc3232;"><?php echo $stats['units_missing_data']; ?></div>
        </div>
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
            <h3>Need Review</h3>
            <div style="font-size: 2em; color: #ffb900;"><?php echo $stats['units_need_review']; ?></div>
        </div>
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
            <h3>Entities</h3>
            <div style="font-size: 2em; color: #826eb4;"><?php echo $stats['entities']; ?></div>
        </div>
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
            <h3>With Emails</h3>
            <div style="font-size: 2em; color: #00a0d2;"><?php echo $stats['units_with_emails']; ?></div>
        </div>
    </div>

    <!-- Filter Controls -->
    <div style="background: #fff; padding: 15px; border: 1px solid #ddd; margin: 20px 0;">
        <h3>Filters</h3>
        <div style="display: flex; gap: 15px; align-items: center;">
            <label>
                <input type="checkbox" id="filter-missing" checked> Show Missing Data
            </label>
            <label>
                <input type="checkbox" id="filter-complete" checked> Show Complete
            </label>
            <label>
                <input type="checkbox" id="filter-entities" checked> Show Entities
            </label>
            <label>
                <input type="checkbox" id="filter-individuals" checked> Show Individuals
            </label>
            <label>
                <input type="checkbox" id="filter-with-emails" checked> Show With Emails
            </label>
            <input type="text" id="search-units" placeholder="Search units..." style="margin-left: 20px; padding: 5px;">
        </div>
    </div>

    <!-- Property List Table -->
    <div style="background: #fff; border: 1px solid #ddd;">
        <table class="wp-list-table widefat fixed striped" id="property-table">
            <thead>
                <tr>
                    <th style="width: 80px;">Unit</th>
                    <th style="width: 100px;">Type</th>
                    <th>Primary Owner</th>
                    <th style="width: 200px;">Primary Email</th>
                    <th style="width: 120px;">Primary Phone</th>
                    <th>Secondary Owner</th>
                    <th style="width: 200px;">Secondary Email</th>
                    <th style="width: 80px;">Email Count</th>
                    <th style="width: 80px;">Status</th>
                    <th style="width: 80px;">Source</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($property_list as $property): ?>
                    <tr class="property-row" 
                        data-status="<?php echo esc_attr($property['status']); ?>"
                        data-type="<?php echo esc_attr($property['entity_type']); ?>"
                        data-unit="<?php echo esc_attr($property['unit']); ?>">
                        
                        <td>
                            <strong><?php echo esc_html($property['unit']); ?></strong>
                        </td>
                        
                        <td>
                            <?php if ($property['entity_type'] === 'Entity'): ?>
                                <span class="entity-type entity">Entity</span>
                            <?php elseif ($property['entity_type'] === 'Individual'): ?>
                                <span class="entity-type individual">Individual</span>
                            <?php else: ?>
                                <span class="entity-type unknown">—</span>
                            <?php endif; ?>
                        </td>
                        
                        <td>
                            <?php if ($property['primary_owner'] === 'NO_DATA'): ?>
                                <span style="color: #dc3232; font-weight: bold;">NO OWNER DATA</span>
                            <?php else: ?>
                                <?php echo esc_html($property['primary_owner']); ?>
                            <?php endif; ?>
                        </td>
                        
                        <td>
                            <?php if ($property['primary_email']): ?>
                                <a href="mailto:<?php echo esc_attr($property['primary_email']); ?>">
                                    <?php echo esc_html($property['primary_email']); ?>
                                </a>
                            <?php endif; ?>
                        </td>
                        
                        <td><?php echo esc_html($property['primary_phone']); ?></td>
                        
                        <td><?php echo esc_html($property['secondary_owner']); ?></td>
                        
                        <td>
                            <?php if ($property['secondary_email']): ?>
                                <a href="mailto:<?php echo esc_attr($property['secondary_email']); ?>">
                                    <?php echo esc_html($property['secondary_email']); ?>
                                </a>
                            <?php endif; ?>
                        </td>
                        
                        <td>
                            <span class="email-count">
                                <?php echo $property['email_count']; ?>
                            </span>
                        </td>
                        
                        <td>
                            <span class="status-badge status-<?php echo strtolower($property['status']); ?>">
                                <?php 
                                switch($property['status']) {
                                    case 'COMPLETE':
                                        echo '✓ Complete';
                                        break;
                                    case 'MISSING_DATA':
                                        echo '⚠ Missing';
                                        break;
                                    case 'MISSING_OWNER':
                                        echo '⚠ Review';
                                        break;
                                    default:
                                        echo $property['status'];
                                }
                                ?>
                            </span>
                        </td>
                        
                        <td style="font-size: 11px;"><?php echo esc_html($property['data_source']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Missing Owner References -->
    <?php
    $missing_refs = [];
    foreach ($properties as $property_id => $property) {
        if (!empty($property['owners'])) {
            foreach ($property['owners'] as $owner_id) {
                if (!$data_manager->getOwner($owner_id)) {
                    $missing_refs[] = [
                        'unit' => str_replace('unit-', '', $property_id),
                        'owner_id' => $owner_id
                    ];
                }
            }
        }
    }
    ?>
    
    <?php if (!empty($missing_refs)): ?>
        <div style="background: #fff3cd; border: 1px solid #ffeaa7; margin: 20px 0; padding: 20px;">
            <h3>⚠️ Missing Owner References</h3>
            <p>The following properties reference owner IDs that don't exist in the system:</p>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; margin-top: 15px;">
                <?php foreach ($missing_refs as $ref): ?>
                    <div style="background: #fff; padding: 10px; border: 1px solid #ddd;">
                        <strong>Unit <?php echo esc_html($ref['unit']); ?>:</strong><br>
                        <code><?php echo esc_html($ref['owner_id']); ?></code>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Email Campaign Summary -->
    <?php
    $unique_emails = [];
    foreach ($owners as $owner_id => $owner) {
        if (!empty($owner['email'])) {
            $unique_emails[$owner['email']] = true;
        }
    }
    ?>
    
    <div style="background: #fff; border: 1px solid #ddd; margin: 20px 0; padding: 20px;">
        <h3>📧 Email Campaign Summary</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div>
                <strong>Total Unique Emails:</strong> 
                <?php echo count($unique_emails); ?>
            </div>
            <div>
                <strong>Units with Emails:</strong> 
                <?php echo $stats['units_with_emails']; ?>
            </div>
            <div>
                <strong>Email Coverage:</strong> 
                <?php echo round(($stats['units_with_emails'] / $stats['total_units']) * 100, 1); ?>%
            </div>
        </div>
    </div>
</div>

<style>
.entity-type.entity { color: #0073aa; font-weight: bold; }
.entity-type.individual { color: #00a32a; }
.entity-type.unknown { color: #666; }

.status-badge {
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: bold;
}

.status-complete { background: #d4edda; color: #155724; }
.status-missing_data { background: #f8d7da; color: #721c24; }
.status-missing_owner { background: #fff3cd; color: #856404; }

.email-count {
    background: #e3f2fd;
    padding: 2px 6px;
    border-radius: 3px;
    font-weight: bold;
    color: #1976d2;
}

.property-row:hover { background: #f8f9fa; }
</style>

<script>
jQuery(document).ready(function($) {
    // Filter functionality
    function filterTable() {
        const showMissing = $('#filter-missing').is(':checked');
        const showComplete = $('#filter-complete').is(':checked');
        const showEntities = $('#filter-entities').is(':checked');
        const showIndividuals = $('#filter-individuals').is(':checked');
        const showWithEmails = $('#filter-with-emails').is(':checked');
        const searchTerm = $('#search-units').val().toLowerCase();
        
        $('.property-row').each(function() {
            const $row = $(this);
            const status = $row.data('status');
            const type = $row.data('type');
            const unit = $row.data('unit').toLowerCase();
            const emailCount = parseInt($row.find('.email-count').text()) || 0;
            let show = true;
            
            // Status filters
            if (status === 'MISSING_DATA' && !showMissing) show = false;
            if (status === 'COMPLETE' && !showComplete) show = false;
            
            // Type filters
            if (type === 'Entity' && !showEntities) show = false;
            if (type === 'Individual' && !showIndividuals) show = false;
            
            // Email filter
            if (showWithEmails && emailCount === 0) show = false;
            
            // Search filter
            if (searchTerm && !unit.includes(searchTerm)) show = false;
            
            $row.toggle(show);
        });
    }
    
    // Bind filter events
    $('#filter-missing, #filter-complete, #filter-entities, #filter-individuals, #filter-with-emails').change(filterTable);
    $('#search-units').on('input', filterTable);
});
</script>
