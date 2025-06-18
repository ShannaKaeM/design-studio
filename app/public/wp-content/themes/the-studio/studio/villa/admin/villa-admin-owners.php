<?php
/**
 * Villa Owners Admin List Page
 * CRM interface for owner management with registration and communication features
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Check user permissions
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

// Get all owners from ACF
$owners = get_posts([
    'post_type' => 'villa_owner',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
    'post_status' => 'any'
]);

// Calculate statistics
$stats = [
    'total_owners' => count($owners),
    'active_owners' => 0,
    'pending_registration' => 0,
    'committee_members' => 0,
    'with_emails' => 0,
    'with_phones' => 0
];

// Build owner list with enhanced data
$owner_list = [];
foreach ($owners as $owner) {
    $owner_id = $owner->ID;
    
    // Get all owner fields
    $email = get_field('owner_email', $owner_id);
    $phone = get_field('owner_phone', $owner_id);
    $status = get_field('owner_status', $owner_id) ?: 'active';
    $registration_date = get_field('owner_registration_date', $owner_id);
    $committee_member = get_field('owner_committee_member', $owner_id);
    $notes = get_field('owner_notes', $owner_id);
    
    // Get linked properties
    $properties = get_posts([
        'post_type' => 'villa_property',
        'meta_key' => 'property_owner',
        'meta_value' => $owner_id,
        'posts_per_page' => -1
    ]);
    
    $property_units = [];
    foreach ($properties as $property) {
        $property_units[] = get_field('property_unit', $property->ID);
    }
    
    // Update stats
    if ($status === 'active') $stats['active_owners']++;
    if ($status === 'pending') $stats['pending_registration']++;
    if ($committee_member) $stats['committee_members']++;
    if ($email) $stats['with_emails']++;
    if ($phone) $stats['with_phones']++;
    
    $owner_list[] = [
        'id' => $owner_id,
        'name' => $owner->post_title,
        'email' => $email,
        'phone' => $phone,
        'status' => $status,
        'registration_date' => $registration_date,
        'committee_member' => $committee_member,
        'notes' => $notes,
        'property_units' => $property_units,
        'properties_count' => count($property_units),
        'edit_link' => get_edit_post_link($owner_id)
    ];
}

// Handle AJAX actions
if (isset($_POST['action'])) {
    check_admin_referer('villa_owners_nonce');
    
    switch ($_POST['action']) {
        case 'update_owner_status':
            $owner_id = intval($_POST['owner_id']);
            $new_status = sanitize_text_field($_POST['status']);
            update_field('owner_status', $new_status, $owner_id);
            
            // If approving, set registration date
            if ($new_status === 'active' && !get_field('owner_registration_date', $owner_id)) {
                update_field('owner_registration_date', current_time('Y-m-d'), $owner_id);
            }
            break;
            
        case 'send_bulk_email':
            // Future implementation
            break;
    }
    
    wp_redirect(admin_url('admin.php?page=villa-owners&updated=1'));
    exit;
}

?>
<div class="wrap">
    <h1>Villa Capriani - Owners & CRM</h1>
    
    <?php if (isset($_GET['updated'])) : ?>
        <div class="notice notice-success is-dismissible">
            <p>Owner information updated successfully!</p>
        </div>
    <?php endif; ?>
    
    <!-- Summary Statistics -->
    <div class="villa-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px 0; color: #333;">Total Owners</h3>
            <div style="font-size: 2em; color: #0073aa; font-weight: bold;"><?php echo $stats['total_owners']; ?></div>
        </div>
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px 0; color: #333;">Active</h3>
            <div style="font-size: 2em; color: #46b450; font-weight: bold;"><?php echo $stats['active_owners']; ?></div>
        </div>
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px 0; color: #333;">Pending</h3>
            <div style="font-size: 2em; color: #f56e28; font-weight: bold;"><?php echo $stats['pending_registration']; ?></div>
        </div>
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px 0; color: #333;">Committee</h3>
            <div style="font-size: 2em; color: #00a0d2; font-weight: bold;"><?php echo $stats['committee_members']; ?></div>
        </div>
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px 0; color: #333;">With Email</h3>
            <div style="font-size: 2em; color: #826eb4; font-weight: bold;"><?php echo $stats['with_emails']; ?></div>
        </div>
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px 0; color: #333;">With Phone</h3>
            <div style="font-size: 2em; color: #3db0d8; font-weight: bold;"><?php echo $stats['with_phones']; ?></div>
        </div>
    </div>

    <!-- CRM Actions Bar -->
    <div style="background: #fff; padding: 15px; border: 1px solid #ddd; margin: 20px 0; border-radius: 5px;">
        <h3 style="margin: 0 0 15px 0;">CRM Actions</h3>
        <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            <select id="bulk-action-selector" style="padding: 5px 10px;">
                <option value="">Bulk Actions</option>
                <option value="email-selected">Email Selected</option>
                <option value="export-selected">Export Selected</option>
                <option value="add-to-committee">Add to Committee</option>
            </select>
            <button class="button" onclick="executeBulkAction()">Apply</button>
            <div style="margin-left: auto; display: flex; gap: 10px; align-items: center;">
                <input type="text" id="search-owners" placeholder="Search name, email, phone, unit..." style="padding: 5px 10px; border: 1px solid #ddd; border-radius: 3px; width: 300px;">
                <select id="filter-status" style="padding: 5px 10px;">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="pending">Pending</option>
                    <option value="inactive">Inactive</option>
                </select>
                <label>
                    <input type="checkbox" id="filter-committee"> Committee Only
                </label>
            </div>
        </div>
    </div>

    <!-- Owners Table -->
    <div style="background: #fff; padding: 0; border: 1px solid #ddd; border-radius: 5px; overflow: hidden;">
        <table class="wp-list-table widefat fixed striped" id="owners-table">
            <thead>
                <tr>
                    <th style="width: 30px;"><input type="checkbox" id="select-all-owners"></th>
                    <th>Name</th>
                    <th>Properties</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th style="width: 100px;">Status</th>
                    <th style="width: 120px;">Registration</th>
                    <th style="width: 80px;">Committee</th>
                    <th style="width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($owner_list as $owner) : ?>
                    <tr data-owner-id="<?php echo $owner['id']; ?>" 
                        data-status="<?php echo esc_attr($owner['status']); ?>"
                        data-committee="<?php echo $owner['committee_member'] ? 'yes' : 'no'; ?>">
                        <td><input type="checkbox" class="owner-checkbox" value="<?php echo $owner['id']; ?>"></td>
                        <td>
                            <strong><?php echo esc_html($owner['name']); ?></strong>
                            <?php if ($owner['notes']) : ?>
                                <span class="dashicons dashicons-admin-comments" title="<?php echo esc_attr($owner['notes']); ?>" style="color: #666; cursor: help;"></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($owner['property_units'])) : ?>
                                <?php echo implode(', ', array_map('esc_html', $owner['property_units'])); ?>
                                <span style="color: #666;">(<?php echo $owner['properties_count']; ?>)</span>
                            <?php else : ?>
                                <span style="color: #dc3232;">No Properties</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($owner['email']) : ?>
                                <a href="mailto:<?php echo esc_attr($owner['email']); ?>"><?php echo esc_html($owner['email']); ?></a>
                            <?php else : ?>
                                <span style="color: #999;">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($owner['phone']) : ?>
                                <?php echo esc_html($owner['phone']); ?>
                            <?php else : ?>
                                <span style="color: #999;">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="post" style="display: inline;">
                                <?php wp_nonce_field('villa_owners_nonce'); ?>
                                <input type="hidden" name="action" value="update_owner_status">
                                <input type="hidden" name="owner_id" value="<?php echo $owner['id']; ?>">
                                <select name="status" onchange="this.form.submit()" style="font-size: 12px;">
                                    <option value="active" <?php selected($owner['status'], 'active'); ?>>Active</option>
                                    <option value="pending" <?php selected($owner['status'], 'pending'); ?>>Pending</option>
                                    <option value="inactive" <?php selected($owner['status'], 'inactive'); ?>>Inactive</option>
                                </select>
                            </form>
                        </td>
                        <td>
                            <?php if ($owner['registration_date']) : ?>
                                <?php echo date('M j, Y', strtotime($owner['registration_date'])); ?>
                            <?php else : ?>
                                <span style="color: #999;">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <?php if ($owner['committee_member']) : ?>
                                <span class="dashicons dashicons-yes" style="color: #46b450;"></span>
                            <?php else : ?>
                                <span class="dashicons dashicons-no-alt" style="color: #ccc;"></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo $owner['edit_link']; ?>" class="button button-small">Edit</a>
                            <?php if ($owner['email']) : ?>
                                <a href="mailto:<?php echo esc_attr($owner['email']); ?>" class="button button-small">Email</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Quick Actions -->
    <div style="margin-top: 30px;">
        <a href="<?php echo admin_url('post-new.php?post_type=villa_owner'); ?>" class="button button-primary">Add New Owner</a>
        <a href="<?php echo admin_url('admin.php?page=villa-registration'); ?>" class="button">Manage Registrations</a>
        <a href="<?php echo admin_url('admin.php?page=villa-json-sync'); ?>" class="button">Sync with JSON</a>
        <a href="<?php echo admin_url('export.php?content=villa_owner'); ?>" class="button">Export Owners</a>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Select all functionality
    $('#select-all-owners').on('change', function() {
        $('.owner-checkbox').prop('checked', $(this).is(':checked'));
    });
    
    // Filter and search functionality
    function filterTable() {
        var searchTerm = $('#search-owners').val().toLowerCase();
        var statusFilter = $('#filter-status').val();
        var committeeOnly = $('#filter-committee').is(':checked');
        
        $('#owners-table tbody tr').each(function() {
            var $row = $(this);
            var rowText = $row.text().toLowerCase();
            var status = $row.data('status');
            var isCommittee = $row.data('committee') === 'yes';
            
            var showRow = true;
            
            // Search filter
            if (searchTerm && !rowText.includes(searchTerm)) showRow = false;
            
            // Status filter
            if (statusFilter && status !== statusFilter) showRow = false;
            
            // Committee filter
            if (committeeOnly && !isCommittee) showRow = false;
            
            $row.toggle(showRow);
        });
    }
    
    $('#search-owners').on('keyup', filterTable);
    $('#filter-status, #filter-committee').on('change', filterTable);
    
    // Bulk actions
    window.executeBulkAction = function() {
        var action = $('#bulk-action-selector').val();
        var selectedIds = [];
        
        $('.owner-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });
        
        if (!action || selectedIds.length === 0) {
            alert('Please select an action and at least one owner.');
            return;
        }
        
        // Handle bulk actions
        switch (action) {
            case 'email-selected':
                // Collect emails and open mail client
                var emails = [];
                selectedIds.forEach(function(id) {
                    var $row = $('tr[data-owner-id="' + id + '"]');
                    var email = $row.find('a[href^="mailto:"]').attr('href');
                    if (email) {
                        emails.push(email.replace('mailto:', ''));
                    }
                });
                if (emails.length > 0) {
                    window.location.href = 'mailto:' + emails.join(',');
                }
                break;
                
            case 'export-selected':
                alert('Export functionality will be implemented soon.');
                break;
                
            case 'add-to-committee':
                if (confirm('Add selected owners to committee?')) {
                    // Implementation would go here
                    alert('Committee assignment will be implemented soon.');
                }
                break;
        }
    };
});
</script>