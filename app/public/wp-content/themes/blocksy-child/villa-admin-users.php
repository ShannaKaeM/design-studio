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

// Load data files
require_once get_stylesheet_directory() . '/villa-owner-crm.php';
require_once get_stylesheet_directory() . '/villa-individual-owners.php';

// Initialize the objects if they don't exist
if (!isset($GLOBALS['villa_owner_crm'])) {
    $GLOBALS['villa_owner_crm'] = new Villa_Owner_CRM();
}
if (!isset($GLOBALS['villa_individual_owners'])) {
    $GLOBALS['villa_individual_owners'] = new Villa_Individual_Owners();
}

global $villa_owner_crm;
global $villa_individual_owners;

$owners_data = $villa_owner_crm->get_owners_data();
$individual_owners = $villa_individual_owners->get_individual_owners();
$stats = $villa_individual_owners->get_statistics();

// Get WordPress users with villa_owner role
$villa_users = get_users(array(
    'role' => 'villa_owner',
    'meta_key' => 'villa_unit_number',
    'orderby' => 'meta_value_num'
));

// Get registration statistics
$total_owners = 0;
$registered_owners = count($villa_users);
$email_coverage = 0;

if ($owners_data) {
    $total_owners = count($owners_data['property_list']);
    $email_coverage = $owners_data['email_campaign_data']['total_unique_emails'] ?? 0;
}
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
                <h3 style="margin: 0 0 10px 0; color: #0073aa;">Total Property Owners</h3>
                <div style="font-size: 2.5em; color: #0073aa; font-weight: bold;"><?php echo $total_owners; ?></div>
                <small style="color: #666;">From ownership records</small>
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
                <h3 style="margin: 0 0 10px 0; color: #826eb4;">Email Addresses</h3>
                <div style="font-size: 2.5em; color: #826eb4; font-weight: bold;"><?php echo $email_coverage; ?></div>
                <small style="color: #666;">Available for communication</small>
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
                            <th>Unit</th>
                            <th>Email</th>
                            <th>Registered</th>
                            <th>Last Login</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Show last 10 registered users
                        $recent_users = array_slice(array_reverse($villa_users), 0, 10);
                        foreach ($recent_users as $user): 
                            $unit = get_user_meta($user->ID, 'villa_unit_number', true);
                            $last_login = get_user_meta($user->ID, 'last_login', true);
                        ?>
                            <tr>
                                <td><strong><?php echo esc_html($user->display_name); ?></strong></td>
                                <td><span class="unit-badge"><?php echo esc_html($unit); ?></span></td>
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
                <h3>Total Individual Owners</h3>
                <div class="stat-number"><?php echo $stats['total_individual_owners']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Registered Owners</h3>
                <div class="stat-number"><?php echo $stats['registered_owners']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Pending Registration</h3>
                <div class="stat-number"><?php echo $stats['pending_owners']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Multi-Property Owners</h3>
                <div class="stat-number"><?php echo $stats['multi_property_owners']; ?></div>
            </div>
        </div>

        <div class="owner-directory-section">
            <h3>Individual Owners Directory</h3>
            <p class="description">Click column headers to sort. Each owner has their own account and can own multiple properties.</p>
            
            <table class="wp-list-table widefat fixed striped" id="owners-table">
                <thead>
                    <tr>
                        <th class="sortable" data-sort="text">Owner Name <span class="sort-indicator">↕️</span></th>
                        <th class="sortable" data-sort="text">Properties <span class="sort-indicator">↕️</span></th>
                        <th class="sortable" data-sort="text">Role <span class="sort-indicator">↕️</span></th>
                        <th class="sortable" data-sort="text">Email <span class="sort-indicator">↕️</span></th>
                        <th class="sortable" data-sort="text">Phone <span class="sort-indicator">↕️</span></th>
                        <th class="sortable" data-sort="text">Account Status <span class="sort-indicator">↕️</span></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($individual_owners as $owner_key => $owner): ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($owner['full_name']); ?></strong>
                                <?php if ($owner['is_primary']): ?>
                                    <span class="owner-badge primary">Primary</span>
                                <?php else: ?>
                                    <span class="owner-badge secondary">Secondary</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $units = array_column($owner['properties'], 'unit');
                                echo esc_html(implode(', ', $units));
                                if (count($units) > 1): ?>
                                    <span class="multi-property-badge"><?php echo count($units); ?> units</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $roles = array_unique(array_column($owner['properties'], 'role'));
                                echo esc_html(implode(', ', $roles));
                                ?>
                            </td>
                            <td>
                                <?php if (!empty($owner['email'])): ?>
                                    <a href="mailto:<?php echo esc_attr($owner['email']); ?>">
                                        <?php echo esc_html($owner['email']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="no-data">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo !empty($owner['phone']) ? esc_html($owner['phone']) : '<span class="no-data">—</span>'; ?>
                            </td>
                            <td>
                                <?php if ($owner['wp_user_id']): ?>
                                    <?php $user = get_user_by('id', $owner['wp_user_id']); ?>
                                    <?php if ($user): ?>
                                        <span class="status-badge active">Active</span>
                                        <br><small>User: <?php echo esc_html($user->user_login); ?></small>
                                    <?php else: ?>
                                        <span class="status-badge error">User Missing</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="status-badge pending">Not Registered</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($owner['wp_user_id']): ?>
                                    <a href="<?php echo get_edit_user_link($owner['wp_user_id']); ?>" class="button button-small">
                                        Edit User
                                    </a>
                                <?php else: ?>
                                    <button class="button button-small send-invite" data-owner="<?php echo esc_attr($owner_key); ?>">
                                        Send Invite
                                    </button>
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
        
        <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3>🔗 Registration Form</h3>
            <p>Use this shortcode to embed the owner registration form on any page:</p>
            <code style="background: #f1f1f1; padding: 10px; display: block; margin: 10px 0;">[villa_owner_registration]</code>
            
            <h3 style="margin-top: 30px;">🏠 Owner Portal</h3>
            <p>Use this shortcode to embed the owner portal (for logged-in owners):</p>
            <code style="background: #f1f1f1; padding: 10px; display: block; margin: 10px 0;">[villa_owner_portal]</code>
        </div>

        <!-- Registration Statistics -->
        <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3>📊 Registration Progress</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 6px;">
                    <div style="font-size: 2em; color: #0073aa; font-weight: bold;"><?php echo $registered_owners; ?></div>
                    <div>Registered</div>
                </div>
                <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 6px;">
                    <div style="font-size: 2em; color: #dc3232; font-weight: bold;"><?php echo $total_owners - $registered_owners; ?></div>
                    <div>Pending</div>
                </div>
                <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 6px;">
                    <div style="font-size: 2em; color: #46b450; font-weight: bold;">
                        <?php echo $total_owners > 0 ? round(($registered_owners / $total_owners) * 100) : 0; ?>%
                    </div>
                    <div>Complete</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Tools Tab -->
    <div id="email-tab" class="tab-content villa-admin-content" style="display: none;">
        <h2>📧 Email Tools</h2>
        
        <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3>📋 Email Export</h3>
            <p>Export owner email addresses for campaigns and communications.</p>
            <div style="display: flex; gap: 10px; margin: 15px 0;">
                <a href="<?php echo get_stylesheet_directory_uri(); ?>/villa-data/migration/villa-owners-email-list.csv" 
                   class="button button-primary" target="_blank">
                    📥 Download Detailed CSV
                </a>
                <a href="<?php echo get_stylesheet_directory_uri(); ?>/villa-data/migration/villa-owners-emails-only.txt" 
                   class="button button-secondary" target="_blank">
                    📝 Download Email List Only
                </a>
            </div>
        </div>

        <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3>✉️ Email Templates</h3>
            <p>Manage email templates for owner communications.</p>
            <button class="button" onclick="alert('Email template management coming soon!')">
                Manage Templates
            </button>
        </div>
    </div>

    <!-- Settings Tab -->
    <div id="settings-tab" class="tab-content villa-admin-content" style="display: none;">
        <h2>⚙️ Settings</h2>
        
        <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3>📧 SMTP Configuration</h3>
            <p>Configure email settings for owner communications.</p>
            <a href="<?php echo admin_url('admin.php?page=villa-smtp-settings'); ?>" class="button button-primary">
                Configure SMTP Settings
            </a>
        </div>

        <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3>👥 User Roles</h3>
            <p>Manage user roles and permissions.</p>
            <button class="button" onclick="alert('User role management coming soon!')">
                Manage Roles
            </button>
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
