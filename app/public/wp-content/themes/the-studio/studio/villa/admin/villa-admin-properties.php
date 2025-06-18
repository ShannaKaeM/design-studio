<?php
/**
 * Villa Property Admin List Page
 * Custom interface for property management with inline editing
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Check user permissions
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

// Get all properties from ACF
$properties = get_posts([
    'post_type' => 'villa_property',
    'posts_per_page' => -1,
    'orderby' => 'meta_value',
    'meta_key' => 'property_unit',
    'order' => 'ASC',
    'post_status' => 'any'
]);

// Calculate statistics
$stats = [
    'total_units' => count($properties),
    'units_with_owners' => 0,
    'units_for_rent' => 0,
    'units_occupied' => 0,
    'units_vacant' => 0,
    'units_with_emails' => 0
];

// Build property list with owner data
$property_list = [];
foreach ($properties as $property) {
    $unit_number = get_field('property_unit', $property->ID);
    $owner_id = get_field('property_owner', $property->ID);
    $status = get_field('property_status', $property->ID);
    
    $owner_data = null;
    $owner_email = '';
    $owner_phone = '';
    
    if ($owner_id) {
        $stats['units_with_owners']++;
        $owner_data = get_post($owner_id);
        if ($owner_data) {
            $owner_email = get_field('owner_email', $owner_id);
            $owner_phone = get_field('owner_phone', $owner_id);
            if ($owner_email) {
                $stats['units_with_emails']++;
            }
        }
    }
    
    // Update stats
    switch ($status) {
        case 'rented':
            $stats['units_for_rent']++;
            break;
        case 'occupied':
            $stats['units_occupied']++;
            break;
        case 'vacant':
            $stats['units_vacant']++;
            break;
    }
    
    $property_list[] = [
        'id' => $property->ID,
        'unit' => $unit_number,
        'address' => get_field('property_address', $property->ID),
        'bedrooms' => get_field('property_bedrooms', $property->ID),
        'bathrooms' => get_field('property_bathrooms', $property->ID),
        'area' => get_field('property_area', $property->ID),
        'status' => $status,
        'owner_name' => $owner_data ? $owner_data->post_title : 'NO OWNER',
        'owner_id' => $owner_id,
        'owner_email' => $owner_email,
        'owner_phone' => $owner_phone,
        'edit_link' => get_edit_post_link($property->ID)
    ];
}

// Handle AJAX actions
if (isset($_POST['action']) && $_POST['action'] === 'update_property_status') {
    check_admin_referer('villa_properties_nonce');
    
    $property_id = intval($_POST['property_id']);
    $new_status = sanitize_text_field($_POST['status']);
    
    update_field('property_status', $new_status, $property_id);
    
    wp_redirect(admin_url('admin.php?page=villa-properties&updated=1'));
    exit;
}

?>
<div class="wrap">
    <h1>Villa Capriani - Properties</h1>
    
    <?php if (isset($_GET['updated'])) : ?>
        <div class="notice notice-success is-dismissible">
            <p>Property updated successfully!</p>
        </div>
    <?php endif; ?>
    
    <!-- Summary Statistics -->
    <div class="villa-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px 0; color: #333;">Total Units</h3>
            <div style="font-size: 2em; color: #0073aa; font-weight: bold;"><?php echo $stats['total_units']; ?></div>
        </div>
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px 0; color: #333;">With Owners</h3>
            <div style="font-size: 2em; color: #46b450; font-weight: bold;"><?php echo $stats['units_with_owners']; ?></div>
        </div>
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px 0; color: #333;">Occupied</h3>
            <div style="font-size: 2em; color: #00a0d2; font-weight: bold;"><?php echo $stats['units_occupied']; ?></div>
        </div>
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px 0; color: #333;">For Rent</h3>
            <div style="font-size: 2em; color: #f56e28; font-weight: bold;"><?php echo $stats['units_for_rent']; ?></div>
        </div>
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px 0; color: #333;">Vacant</h3>
            <div style="font-size: 2em; color: #dc3232; font-weight: bold;"><?php echo $stats['units_vacant']; ?></div>
        </div>
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px 0; color: #333;">With Emails</h3>
            <div style="font-size: 2em; color: #826eb4; font-weight: bold;"><?php echo $stats['units_with_emails']; ?></div>
        </div>
    </div>

    <!-- Filter Controls -->
    <div style="background: #fff; padding: 15px; border: 1px solid #ddd; margin: 20px 0; border-radius: 5px;">
        <h3 style="margin: 0 0 15px 0;">Filters</h3>
        <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            <label>
                <input type="checkbox" id="filter-no-owner" checked> Show Units Without Owners
            </label>
            <label>
                <input type="checkbox" id="filter-occupied" checked> Show Occupied
            </label>
            <label>
                <input type="checkbox" id="filter-rented" checked> Show For Rent
            </label>
            <label>
                <input type="checkbox" id="filter-vacant" checked> Show Vacant
            </label>
            <input type="text" id="search-box" placeholder="Search unit, owner, email..." style="padding: 5px 10px; border: 1px solid #ddd; border-radius: 3px;">
        </div>
    </div>

    <!-- Properties Table -->
    <div style="background: #fff; padding: 0; border: 1px solid #ddd; border-radius: 5px; overflow: hidden;">
        <table class="wp-list-table widefat fixed striped" id="properties-table">
            <thead>
                <tr>
                    <th style="width: 80px;">Unit</th>
                    <th>Address</th>
                    <th style="width: 60px;">Beds</th>
                    <th style="width: 60px;">Baths</th>
                    <th style="width: 80px;">Sq Ft</th>
                    <th>Owner</th>
                    <th>Contact</th>
                    <th style="width: 100px;">Status</th>
                    <th style="width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($property_list as $property) : ?>
                    <tr data-unit="<?php echo esc_attr($property['unit']); ?>" 
                        data-status="<?php echo esc_attr($property['status']); ?>"
                        data-owner="<?php echo $property['owner_id'] ? 'has-owner' : 'no-owner'; ?>">
                        <td><strong><?php echo esc_html($property['unit']); ?></strong></td>
                        <td><?php echo esc_html($property['address']); ?></td>
                        <td style="text-align: center;"><?php echo esc_html($property['bedrooms']); ?></td>
                        <td style="text-align: center;"><?php echo esc_html($property['bathrooms']); ?></td>
                        <td style="text-align: center;"><?php echo esc_html($property['area']); ?></td>
                        <td>
                            <?php if ($property['owner_id']) : ?>
                                <a href="<?php echo get_edit_post_link($property['owner_id']); ?>">
                                    <?php echo esc_html($property['owner_name']); ?>
                                </a>
                            <?php else : ?>
                                <span style="color: #dc3232;">NO OWNER</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($property['owner_email']) : ?>
                                <div><a href="mailto:<?php echo esc_attr($property['owner_email']); ?>"><?php echo esc_html($property['owner_email']); ?></a></div>
                            <?php endif; ?>
                            <?php if ($property['owner_phone']) : ?>
                                <div><?php echo esc_html($property['owner_phone']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="post" style="display: inline;">
                                <?php wp_nonce_field('villa_properties_nonce'); ?>
                                <input type="hidden" name="action" value="update_property_status">
                                <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                                <select name="status" onchange="this.form.submit()" style="font-size: 12px;">
                                    <option value="occupied" <?php selected($property['status'], 'occupied'); ?>>Occupied</option>
                                    <option value="rented" <?php selected($property['status'], 'rented'); ?>>For Rent</option>
                                    <option value="vacant" <?php selected($property['status'], 'vacant'); ?>>Vacant</option>
                                    <option value="sale" <?php selected($property['status'], 'sale'); ?>>For Sale</option>
                                </select>
                            </form>
                        </td>
                        <td>
                            <a href="<?php echo $property['edit_link']; ?>" class="button button-small">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Quick Actions -->
    <div style="margin-top: 30px;">
        <a href="<?php echo admin_url('post-new.php?post_type=villa_property'); ?>" class="button button-primary">Add New Property</a>
        <a href="<?php echo admin_url('admin.php?page=villa-json-sync'); ?>" class="button">Sync with JSON</a>
        <a href="<?php echo admin_url('export.php?content=villa_property'); ?>" class="button">Export Properties</a>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Filter functionality
    function filterTable() {
        var showNoOwner = $('#filter-no-owner').is(':checked');
        var showOccupied = $('#filter-occupied').is(':checked');
        var showRented = $('#filter-rented').is(':checked');
        var showVacant = $('#filter-vacant').is(':checked');
        var searchTerm = $('#search-box').val().toLowerCase();
        
        $('#properties-table tbody tr').each(function() {
            var $row = $(this);
            var status = $row.data('status');
            var hasOwner = $row.data('owner') === 'has-owner';
            var rowText = $row.text().toLowerCase();
            
            var showRow = true;
            
            // Status filters
            if (status === 'occupied' && !showOccupied) showRow = false;
            if (status === 'rented' && !showRented) showRow = false;
            if (status === 'vacant' && !showVacant) showRow = false;
            if (!hasOwner && !showNoOwner) showRow = false;
            
            // Search filter
            if (searchTerm && !rowText.includes(searchTerm)) showRow = false;
            
            $row.toggle(showRow);
        });
    }
    
    // Attach filter events
    $('#filter-no-owner, #filter-occupied, #filter-rented, #filter-vacant').on('change', filterTable);
    $('#search-box').on('keyup', filterTable);
    
    // Initial filter
    filterTable();
});
</script>