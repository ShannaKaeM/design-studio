<?php
/**
 * Villa Property Admin List Page
 * Displays comprehensive property ownership data with cross-reference analysis
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
    <h1>Villa Capriani - Property Ownership Analysis</h1>
    
    <?php if ($analysis_data): ?>
        
        <!-- Summary Statistics -->
        <div class="villa-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
            <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                <h3>Total Units</h3>
                <div style="font-size: 2em; color: #0073aa;"><?php echo $analysis_data['summary_stats']['total_units']; ?></div>
            </div>
            <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                <h3>Units with Owners</h3>
                <div style="font-size: 2em; color: #46b450;"><?php echo $analysis_data['summary_stats']['units_with_owners']; ?></div>
            </div>
            <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                <h3>Missing Data</h3>
                <div style="font-size: 2em; color: #dc3232;"><?php echo $analysis_data['summary_stats']['units_missing_data']; ?></div>
            </div>
            <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                <h3>Need Review</h3>
                <div style="font-size: 2em; color: #ffb900;"><?php echo $analysis_data['summary_stats']['units_need_review']; ?></div>
            </div>
            <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                <h3>Entities</h3>
                <div style="font-size: 2em; color: #826eb4;"><?php echo $analysis_data['summary_stats']['entities']; ?></div>
            </div>
            <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                <h3>With Emails</h3>
                <div style="font-size: 2em; color: #00a0d2;"><?php echo $analysis_data['summary_stats']['units_with_emails']; ?></div>
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
                        <th>Company/Entity</th>
                        <th>Primary Owner</th>
                        <th>Primary Email</th>
                        <th>Primary Phone</th>
                        <th>Secondary Owner</th>
                        <th>Secondary Email</th>
                        <th>Secondary Phone</th>
                        <th style="width: 80px;">Email Count</th>
                        <th style="width: 80px;">Status</th>
                        <th style="width: 80px;">Source</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($analysis_data['property_list'] as $property): ?>
                        <tr class="property-row" 
                            data-status="<?php echo esc_attr($property['status']); ?>"
                            data-type="<?php echo esc_attr($property['entity_type']); ?>"
                            data-unit="<?php echo esc_attr($property['unit']); ?>">
                            
                            <td><strong><?php echo esc_html($property['unit']); ?></strong></td>
                            
                            <td>
                                <span class="entity-type <?php echo strtolower($property['entity_type']); ?>">
                                    <?php echo esc_html($property['entity_type']); ?>
                                </span>
                            </td>
                            
                            <td>
                                <?php if ($property['company']): ?>
                                    <strong><?php echo esc_html($property['company']); ?></strong>
                                <?php else: ?>
                                    <span style="color: #666;">—</span>
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
                            
                            <td><?php echo esc_html($property['secondary_phone']); ?></td>
                            
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
                                            echo '❌ Missing';
                                            break;
                                        case 'MISSING_OWNER':
                                            echo '⚠ No Owner';
                                            break;
                                        default:
                                            echo esc_html($property['status']);
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

        <!-- Multi-Unit Owners Section -->
        <?php if (!empty($analysis_data['multi_unit_owners'])): ?>
            <div style="background: #fff; border: 1px solid #ddd; margin: 20px 0; padding: 20px;">
                <h3>🏢 Multi-Unit Owners</h3>
                <table class="wp-list-table widefat">
                    <thead>
                        <tr>
                            <th>Owner/Entity</th>
                            <th>Type</th>
                            <th>Units Owned</th>
                            <th>Unit Numbers</th>
                            <th>Email Addresses</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($analysis_data['multi_unit_owners'] as $owner): ?>
                            <tr>
                                <td><strong><?php echo esc_html($owner['name']); ?></strong></td>
                                <td>
                                    <span class="entity-type <?php echo strtolower($owner['type']); ?>">
                                        <?php echo esc_html($owner['type']); ?>
                                    </span>
                                </td>
                                <td><strong><?php echo $owner['unit_count']; ?></strong></td>
                                <td><?php echo esc_html(implode(', ', $owner['units'])); ?></td>
                                <td>
                                    <?php foreach ($owner['emails'] as $email): ?>
                                        <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a><br>
                                    <?php endforeach; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Missing Data Units Section -->
        <?php if (!empty($analysis_data['missing_data_units'])): ?>
            <div style="background: #fff; border: 1px solid #ddd; margin: 20px 0; padding: 20px;">
                <h3 style="color: #dc3232;">⚠️ Units Missing Ownership Data</h3>
                <p>The following <?php echo count($analysis_data['missing_data_units']); ?> units have no ownership information:</p>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 10px; margin-top: 15px;">
                    <?php foreach ($analysis_data['missing_data_units'] as $unit): ?>
                        <div style="background: #ffebee; border: 1px solid #f44336; padding: 10px; text-align: center; border-radius: 4px;">
                            <strong><?php echo esc_html($unit['unit']); ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Email Campaign Summary -->
        <div style="background: #fff; border: 1px solid #ddd; margin: 20px 0; padding: 20px;">
            <h3>📧 Email Campaign Summary</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div>
                    <strong>Total Unique Emails:</strong> 
                    <?php echo $analysis_data['email_campaign_data']['total_unique_emails']; ?>
                </div>
                <div>
                    <strong>Units with Emails:</strong> 
                    <?php echo $analysis_data['summary_stats']['units_with_emails']; ?>
                </div>
                <div>
                    <strong>Email Coverage:</strong> 
                    <?php echo round(($analysis_data['summary_stats']['units_with_emails'] / 116) * 100, 1); ?>%
                </div>
            </div>
            <p style="margin-top: 15px;">
                <a href="<?php echo get_stylesheet_directory_uri(); ?>/villa-data/migration/email-campaign-final.json" 
                   class="button button-primary" target="_blank">
                    Download Email List (JSON)
                </a>
            </p>
        </div>

    <?php else: ?>
        <div class="notice notice-error">
            <p>Analysis data file not found. Please run the ownership analysis script first.</p>
        </div>
    <?php endif; ?>
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
            
            // Email filter - show only if has emails when filter is checked
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
