<?php
/**
 * Villa Users Admin Page
 * Comprehensive owner/user management combining CRM and directory functionality
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Check user permissions
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

// Load the new data manager
require_once get_stylesheet_directory() . '/villa-data-manager.php';

global $villa_data_manager;

// Get data from the new consolidated structure
$owners = $villa_data_manager->get_all_owners();
$stats = $villa_data_manager->get_statistics();
$email_data = $villa_data_manager->get_email_campaign_data();

// Get WordPress users with villa_owner role
$villa_users = get_users(array(
    'role' => 'villa_owner',
    'orderby' => 'user_registered',
    'order' => 'DESC'
));

// Calculate statistics
$total_owners = $stats['total_owners'];
$registered_owners = $stats['registered_owners'];
$email_coverage = $stats['owners_with_email'];
?>

<div class="wrap villa-admin-container">
    <div class="villa-admin-header">
        <h1>👥 Users & Owner Management</h1>
    </div>
    
    <!-- Tab Navigation -->
    <div class="villa-admin-nav nav-tab-wrapper">
        <a href="#dashboard" class="nav-tab nav-tab-active" onclick="showTab('dashboard', event)">📊 Dashboard</a>
        <a href="#directory" class="nav-tab" onclick="showTab('directory', event)">📋 Owner Directory</a>
        <a href="#registration" class="nav-tab" onclick="showTab('registration', event)">✅ Registration</a>
        <a href="#email" class="nav-tab" onclick="showTab('email', event)">📧 Email Tools</a>
        <a href="#settings" class="nav-tab" onclick="showTab('settings', event)">⚙️ Settings</a>
    </div>

    <!-- Dashboard Tab -->
    <div id="dashboard-tab" class="tab-content villa-admin-content">
        <h2>📊 User Management Dashboard</h2>
        
        <!-- CRM Statistics -->
        <div class="stat-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0;">
            <div class="stat-card" style="background: #fff; padding: 25px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 10px 0; color: #0073aa;">Total Owners</h3>
                <div style="font-size: 2.5em; color: #0073aa; font-weight: bold;"><?php echo $total_owners; ?></div>
                <small style="color: #666;">Unique owner records</small>
            </div>
            
            <div class="stat-card" style="background: #fff; padding: 25px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 10px 0; color: #46b450;">Registered Users</h3>
                <div style="font-size: 2.5em; color: #46b450; font-weight: bold;"><?php echo $registered_owners; ?></div>
                <small style="color: #666;">WordPress accounts created</small>
            </div>
            
            <div class="stat-card" style="background: #fff; padding: 25px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 10px 0; color: #f56e28;">Registration Rate</h3>
                <div style="font-size: 2.5em; color: #f56e28; font-weight: bold;">
                    <?php echo $total_owners > 0 ? round(($registered_owners / $total_owners) * 100, 1) : 0; ?>%
                </div>
                <small style="color: #666;">Owners with accounts</small>
            </div>
            
            <div class="stat-card" style="background: #fff; padding: 25px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 10px 0; color: #826eb4;">Email Coverage</h3>
                <div style="font-size: 2.5em; color: #826eb4; font-weight: bold;"><?php echo $email_coverage; ?></div>
                <small style="color: #666;">Owners with email addresses</small>
            </div>
        </div>

        <!-- Recent Activity -->
        <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3>📈 Recent Registration Activity</h3>
            <?php if (!empty($villa_users)): ?>
                <table class="wp-list-table widefat">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Owner</th>
                            <th>Units</th>
                            <th>Email</th>
                            <th>Registered</th>
                            <th>Last Login</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Show last 10 registered users
                        $recent_users = array_slice($villa_users, 0, 10);
                        foreach ($recent_users as $user): 
                            $owner_id = get_user_meta($user->ID, 'villa_owner_id', true);
                            $units = get_user_meta($user->ID, 'villa_units', true);
                            $last_login = get_user_meta($user->ID, 'last_login', true);
                        ?>
                            <tr>
                                <td><strong><?php echo esc_html($user->user_login); ?></strong></td>
                                <td><?php echo esc_html($user->display_name); ?></td>
                                <td><?php echo is_array($units) ? esc_html(implode(', ', $units)) : esc_html($units); ?></td>
                                <td><?php echo esc_html($user->user_email); ?></td>
                                <td><?php echo date('M j, Y', strtotime($user->user_registered)); ?></td>
                                <td><?php echo $last_login ? date('M j, Y', strtotime($last_login)) : 'Never'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #666; font-style: italic;">No registered users yet. Users will appear here as they register.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Owner Directory Tab -->
    <div id="directory-tab" class="tab-content villa-admin-content" style="display: none;">
        <div class="owner-stats">
            <div class="stat-card">
                <h3>Total Owners</h3>
                <div class="stat-number"><?php echo $stats['total_owners']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Active Owners</h3>
                <div class="stat-number"><?php echo $stats['active_owners']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Multi-Property Owners</h3>
                <div class="stat-number"><?php echo $stats['multi_property_owners']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Entity Types</h3>
                <div class="stat-breakdown">
                    <?php foreach ($stats['entity_types'] as $type => $count): ?>
                        <?php if ($count > 0): ?>
                            <div><?php echo ucfirst($type); ?>: <?php echo $count; ?></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="owner-directory-section">
            <h3>Owner Directory</h3>
            <p class="description">Click column headers to sort. Each owner has their own JSON file with complete data.</p>
            
            <table id="owners-table" class="wp-list-table widefat fixed striped sortable-table">
                <thead>
                    <tr>
                        <th class="sortable" data-sort="name">Owner Name <span class="sort-indicator"></span></th>
                        <th class="sortable" data-sort="units">Properties <span class="sort-indicator"></span></th>
                        <th class="sortable" data-sort="entity">Entity Type <span class="sort-indicator"></span></th>
                        <th class="sortable" data-sort="email">Email <span class="sort-indicator"></span></th>
                        <th class="sortable" data-sort="status">Status <span class="sort-indicator"></span></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($owners as $owner): ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($owner['personal_info']['full_name']); ?></strong>
                                <?php if (count($owner['properties']) > 1): ?>
                                    <span class="multi-property-badge"><?php echo count($owner['properties']); ?> properties</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $units = array_column($owner['properties'], 'unit_number');
                                echo esc_html(implode(', ', $units));
                                ?>
                            </td>
                            <td><?php echo esc_html(ucfirst($owner['entity_info']['entity_type'] ?? 'individual')); ?></td>
                            <td>
                                <?php if (!empty($owner['personal_info']['email'])): ?>
                                    <a href="mailto:<?php echo esc_attr($owner['personal_info']['email']); ?>">
                                        <?php echo esc_html($owner['personal_info']['email']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="no-data">No email</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($owner['wordpress_integration']['wp_user_id'])): ?>
                                    <span class="status-badge active">Registered</span>
                                <?php else: ?>
                                    <span class="status-badge pending">Not Registered</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="button button-small" onclick="viewOwnerDetails('<?php echo esc_js($owner['owner_id']); ?>')">View</button>
                                <?php if (empty($owner['wordpress_integration']['wp_user_id']) && !empty($owner['personal_info']['email'])): ?>
                                    <button class="button button-small" onclick="inviteOwner('<?php echo esc_js($owner['personal_info']['email']); ?>')">Invite</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Registration Tab -->
    <div id="registration-tab" class="tab-content villa-admin-content" style="display: none;">
        <h2>✅ Registration Management</h2>
        
        <div class="registration-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0;">
            <div class="stat-card">
                <h4>Pending Registration</h4>
                <div class="stat-number"><?php echo $total_owners - $registered_owners; ?></div>
            </div>
            <div class="stat-card">
                <h4>Completed</h4>
                <div class="stat-number"><?php echo $registered_owners; ?></div>
            </div>
            <div class="stat-card">
                <h4>Missing Email</h4>
                <div class="stat-number"><?php echo count($email_data['owners_without_email']); ?></div>
            </div>
        </div>

        <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3>Owners Pending Registration</h3>
            <table class="wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th>Owner</th>
                        <th>Properties</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $pending_count = 0;
                    foreach ($owners as $owner): 
                        if (!empty($owner['wordpress_integration']['wp_user_id'])) continue;
                        $pending_count++;
                        if ($pending_count > 20) break; // Show first 20
                    ?>
                        <tr>
                            <td><strong><?php echo esc_html($owner['personal_info']['full_name']); ?></strong></td>
                            <td>
                                <?php 
                                $units = array_column($owner['properties'], 'unit_number');
                                echo esc_html(implode(', ', $units));
                                ?>
                            </td>
                            <td>
                                <?php if (!empty($owner['personal_info']['email'])): ?>
                                    <?php echo esc_html($owner['personal_info']['email']); ?>
                                <?php else: ?>
                                    <span class="no-data">Missing</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($owner['personal_info']['phone'])): ?>
                                    <?php echo esc_html($owner['personal_info']['phone']); ?>
                                <?php else: ?>
                                    <span class="no-data">Missing</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($owner['personal_info']['email'])): ?>
                                    <button class="button button-small button-primary" onclick="sendInvitation('<?php echo esc_js($owner['owner_id']); ?>')">Send Invitation</button>
                                <?php else: ?>
                                    <button class="button button-small" disabled>No Email</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($pending_count > 20): ?>
                <p style="margin-top: 10px; color: #666;">Showing first 20 of <?php echo $total_owners - $registered_owners; ?> pending registrations.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Email Tab -->
    <div id="email-tab" class="tab-content villa-admin-content" style="display: none;">
        <h2>📧 Email Tools</h2>
        
        <div class="email-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0;">
            <div class="stat-card">
                <h4>Total Recipients</h4>
                <div class="stat-number"><?php echo $email_data['total_unique_emails']; ?></div>
            </div>
            <div class="stat-card">
                <h4>Missing Emails</h4>
                <div class="stat-number"><?php echo count($email_data['owners_without_email']); ?></div>
            </div>
        </div>

        <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3>Email Campaign Tools</h3>
            <p>Send targeted emails to Villa owners based on various criteria.</p>
            
            <div style="margin: 20px 0;">
                <button class="button button-primary">Compose Email Campaign</button>
                <button class="button">Export Email List</button>
                <button class="button">View Email History</button>
            </div>
        </div>

        <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3>Owners Without Email</h3>
            <p>These owners need email addresses added to enable communication:</p>
            <table class="wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th>Owner</th>
                        <th>Properties</th>
                        <th>Phone</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $shown = 0;
                    foreach ($email_data['owners_without_email'] as $owner): 
                        if ($shown++ >= 10) break;
                    ?>
                        <tr>
                            <td><strong><?php echo esc_html($owner['name']); ?></strong></td>
                            <td><?php echo esc_html(implode(', ', $owner['units'])); ?></td>
                            <td>
                                <button class="button button-small" onclick="editOwner('<?php echo esc_js($owner['owner_id']); ?>')">Add Email</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (count($email_data['owners_without_email']) > 10): ?>
                <p style="margin-top: 10px; color: #666;">Showing first 10 of <?php echo count($email_data['owners_without_email']); ?> owners without email.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Settings Tab -->
    <div id="settings-tab" class="tab-content villa-admin-content" style="display: none;">
        <h2>⚙️ Settings</h2>
        
        <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3>Email Configuration</h3>
            <p>Configure SMTP settings for sending emails to owners.</p>
            <a href="<?php echo admin_url('admin.php?page=villa-smtp-settings'); ?>" class="button">Configure SMTP</a>
        </div>

        <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3>Data Management</h3>
            <p>Tools for managing Villa owner data.</p>
            <button class="button">Export All Data</button>
            <button class="button">Import Data</button>
            <button class="button">Sync with Properties</button>
        </div>

        <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3>System Information</h3>
            <table class="form-table">
                <tr>
                    <th>Data Path</th>
                    <td><code><?php echo get_stylesheet_directory() . '/villa-data'; ?></code></td>
                </tr>
                <tr>
                    <th>Total JSON Files</th>
                    <td><?php echo $stats['total_owners'] + $stats['total_properties']; ?> files</td>
                </tr>
                <tr>
                    <th>Last Update</th>
                    <td><?php echo date('F j, Y g:i a'); ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<style>
.villa-admin-container {
    max-width: 1200px;
    margin: 20px auto;
    background: #f9f9f9;
    border-radius: 8px;
    overflow: hidden;
}

.villa-admin-header {
    background: linear-gradient(135deg, #5a7b7c 0%, #4a6b6c 100%);
    color: white;
    padding: 30px;
    text-align: center;
}

.villa-admin-header h1 {
    margin: 0;
    font-size: 2.2em;
    font-weight: 300;
}

.villa-admin-header p {
    margin: 10px 0 0 0;
    opacity: 0.9;
    font-size: 1.1em;
}

.villa-admin-nav {
    background: #fff;
    border-bottom: 1px solid #ddd;
    padding: 0;
}

.villa-admin-nav .nav-tab {
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    color: #666;
    padding: 15px 25px;
    font-size: 16px;
    transition: all 0.3s ease;
}

.villa-admin-nav .nav-tab:hover {
    background: #f8f9fa;
    color: #5a7b7c;
}

.villa-admin-nav .nav-tab-active {
    color: #5a7b7c;
    border-bottom-color: #5a7b7c;
    background: #f8f9fa;
}

.villa-admin-content {
    padding: 30px;
    background: #fff;
}

.stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.unit-badge {
    background: #5a7b7c;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}

.entity-type {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}

.entity-type.entity {
    background: #e3f2fd;
    color: #1976d2;
}

.entity-type.individual {
    background: #f3e5f5;
    color: #7b1fa2;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}

.status-badge.registered {
    background: #e8f5e8;
    color: #2e7d32;
}

.status-badge.not-registered {
    background: #fff3e0;
    color: #f57c00;
}

/* Sortable Table Styles */
.sortable-table th.sortable {
    cursor: pointer;
    user-select: none;
    position: relative;
    padding-right: 30px;
    transition: background-color 0.2s ease;
}

.sortable-table th.sortable:hover {
    background-color: #f5f5f5;
}

.sortable-table th.sortable .sort-indicator {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 12px;
    opacity: 0.5;
    transition: opacity 0.2s ease;
}

.sortable-table th.sortable:hover .sort-indicator {
    opacity: 1;
}

.sortable-table th.sortable.asc .sort-indicator::after {
    content: " ↑";
    color: #5a7b7c;
    font-weight: bold;
}

.sortable-table th.sortable.desc .sort-indicator::after {
    content: " ↓";
    color: #5a7b7c;
    font-weight: bold;
}

.sortable-table th.sortable.asc,
.sortable-table th.sortable.desc {
    background-color: #f0f8ff;
    color: #5a7b7c;
}

.owner-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    margin-left: 5px;
}

.owner-badge.primary {
    background: #e3f2fd;
    color: #1976d2;
}

.owner-badge.secondary {
    background: #f3e5f5;
    color: #7b1fa2;
}

.multi-property-badge {
    display: inline-block;
    background: #fff3cd;
    color: #856404;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    margin-left: 5px;
}

.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.active {
    background: #d4edda;
    color: #155724;
}

.status-badge.pending {
    background: #fff3cd;
    color: #856404;
}

.status-badge.error {
    background: #f8d7da;
    color: #721c24;
}

.no-data {
    color: #999;
    font-style: italic;
}

@media (max-width: 768px) {
    .villa-admin-container {
        margin: 10px;
        border-radius: 0;
    }
    
    .villa-admin-header {
        padding: 20px;
    }
    
    .villa-admin-content {
        padding: 15px;
    }
    
    .stat-grid {
        grid-template-columns: 1fr;
    }
    
    .villa-admin-nav .nav-tab {
        padding: 12px 15px;
        font-size: 14px;
    }
}
</style>

<script>
function showTab(tabName, event) {
    // Hide all tab content
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.style.display = 'none';
    });
    
    // Remove active class from all nav tabs
    document.querySelectorAll('.nav-tab').forEach(tab => {
        tab.classList.remove('nav-tab-active');
    });
    
    // Show selected tab
    document.getElementById(tabName + '-tab').style.display = 'block';
    
    // Add active class to clicked nav tab
    event.target.classList.add('nav-tab-active');
}

jQuery(document).ready(function($) {
    // Sorting functionality for directory
    function sortTable(e) {
        const $header = $(e.currentTarget);
        const sortColumn = $header.data('sort');
        const currentOrder = $header.hasClass('asc') ? 'desc' : 'asc';
        const columnIndex = $header.index();
        const $tbody = $('#owners-table tbody');
        const rows = $tbody.find('tr').toArray();
        
        // Sort rows
        rows.sort(function(a, b) {
            const aValue = $(a).find('td').eq(columnIndex).text().trim().toLowerCase();
            const bValue = $(b).find('td').eq(columnIndex).text().trim().toLowerCase();
            
            // Handle numeric sorting for units
            if (sortColumn === 'units') {
                const aNum = parseInt(aValue) || 0;
                const bNum = parseInt(bValue) || 0;
                return currentOrder === 'asc' ? aNum - bNum : bNum - aNum;
            }
            
            // Handle text sorting
            if (currentOrder === 'asc') {
                return aValue.localeCompare(bValue);
            } else {
                return bValue.localeCompare(aValue);
            }
        });
        
        // Update table
        $tbody.empty().append(rows);
        
        // Update sort indicators
        $('.sortable').removeClass('asc desc');
        $header.addClass(currentOrder);
    }
    
    // Bind sort events
    $('.sortable').on('click', sortTable);
});

function viewOwnerDetails(ownerName) {
    alert('Owner details for: ' + ownerName + '\n\nFull owner profile management coming soon!');
}

function inviteOwner(email) {
    if (email) {
        alert('Sending registration invitation to: ' + email + '\n\nEmail invitation system coming soon!');
    } else {
        alert('No email address available for this owner.');
    }
}
</script>
