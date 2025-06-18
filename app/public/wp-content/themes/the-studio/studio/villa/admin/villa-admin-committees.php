<?php
/**
 * Villa Committees Admin Page
 * Committee management interface with member assignments and schedules
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Check user permissions
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

// Get all committees from ACF
$committees = get_posts([
    'post_type' => 'villa_committee',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
    'post_status' => 'any'
]);

// Get all active owners for member assignment
$active_owners = get_posts([
    'post_type' => 'villa_owner',
    'posts_per_page' => -1,
    'meta_key' => 'owner_status',
    'meta_value' => 'active',
    'orderby' => 'title',
    'order' => 'ASC'
]);

// Build committee data with enhanced information
$committee_list = [];
$total_members = 0;
$active_committees = 0;

foreach ($committees as $committee) {
    $committee_id = $committee->ID;
    
    // Get committee fields
    $description = get_field('committee_description', $committee_id);
    $chair_id = get_field('committee_chair', $committee_id);
    $members = get_field('committee_members', $committee_id) ?: [];
    $meeting_schedule = get_field('committee_meeting_schedule', $committee_id);
    $next_meeting = get_field('committee_next_meeting', $committee_id);
    $status = get_field('committee_status', $committee_id) ?: 'active';
    $email = get_field('committee_email', $committee_id);
    
    // Get chair information
    $chair_name = '';
    $chair_email = '';
    if ($chair_id) {
        $chair = get_post($chair_id);
        if ($chair) {
            $chair_name = $chair->post_title;
            $chair_email = get_field('owner_email', $chair_id);
        }
    }
    
    // Count stats
    $member_count = is_array($members) ? count($members) : 0;
    $total_members += $member_count;
    if ($status === 'active') $active_committees++;
    
    $committee_list[] = [
        'id' => $committee_id,
        'name' => $committee->post_title,
        'description' => $description,
        'chair_id' => $chair_id,
        'chair_name' => $chair_name,
        'chair_email' => $chair_email,
        'members' => $members,
        'member_count' => $member_count,
        'meeting_schedule' => $meeting_schedule,
        'next_meeting' => $next_meeting,
        'status' => $status,
        'email' => $email,
        'edit_link' => get_edit_post_link($committee_id)
    ];
}

// Handle form submissions
if (isset($_POST['action'])) {
    check_admin_referer('villa_committees_nonce');
    
    switch ($_POST['action']) {
        case 'update_committee_status':
            $committee_id = intval($_POST['committee_id']);
            $new_status = sanitize_text_field($_POST['status']);
            update_field('committee_status', $new_status, $committee_id);
            break;
            
        case 'quick_add_member':
            $committee_id = intval($_POST['committee_id']);
            $owner_id = intval($_POST['owner_id']);
            
            // Get current members
            $members = get_field('committee_members', $committee_id) ?: [];
            
            // Add new member if not already in committee
            if (!in_array($owner_id, $members)) {
                $members[] = $owner_id;
                update_field('committee_members', $members, $committee_id);
                
                // Also update the owner's committee status
                update_field('owner_committee_member', true, $owner_id);
            }
            break;
    }
    
    wp_redirect(admin_url('admin.php?page=villa-committees&updated=1'));
    exit;
}

?>
<div class="wrap">
    <h1>Villa Capriani - Committees</h1>
    
    <?php if (isset($_GET['updated'])) : ?>
        <div class="notice notice-success is-dismissible">
            <p>Committee updated successfully!</p>
        </div>
    <?php endif; ?>
    
    <!-- Summary Statistics -->
    <div class="villa-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px 0; color: #333;">Total Committees</h3>
            <div style="font-size: 2em; color: #0073aa; font-weight: bold;"><?php echo count($committees); ?></div>
        </div>
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px 0; color: #333;">Active</h3>
            <div style="font-size: 2em; color: #46b450; font-weight: bold;"><?php echo $active_committees; ?></div>
        </div>
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px 0; color: #333;">Total Members</h3>
            <div style="font-size: 2em; color: #00a0d2; font-weight: bold;"><?php echo $total_members; ?></div>
        </div>
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px 0; color: #333;">Avg Members</h3>
            <div style="font-size: 2em; color: #826eb4; font-weight: bold;"><?php echo count($committees) > 0 ? round($total_members / count($committees), 1) : 0; ?></div>
        </div>
    </div>

    <!-- Committee Cards -->
    <div class="committee-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(500px, 1fr)); gap: 20px; margin-top: 30px;">
        <?php foreach ($committee_list as $committee) : ?>
            <div class="committee-card" style="background: #fff; border: 1px solid #ddd; border-radius: 5px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                    <h3 style="margin: 0; color: #333;"><?php echo esc_html($committee['name']); ?></h3>
                    <form method="post" style="display: inline;">
                        <?php wp_nonce_field('villa_committees_nonce'); ?>
                        <input type="hidden" name="action" value="update_committee_status">
                        <input type="hidden" name="committee_id" value="<?php echo $committee['id']; ?>">
                        <select name="status" onchange="this.form.submit()" style="font-size: 12px;">
                            <option value="active" <?php selected($committee['status'], 'active'); ?>>Active</option>
                            <option value="inactive" <?php selected($committee['status'], 'inactive'); ?>>Inactive</option>
                            <option value="forming" <?php selected($committee['status'], 'forming'); ?>>Forming</option>
                        </select>
                    </form>
                </div>
                
                <?php if ($committee['description']) : ?>
                    <p style="color: #666; margin: 0 0 15px 0;"><?php echo esc_html($committee['description']); ?></p>
                <?php endif; ?>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <strong>Chair:</strong>
                        <?php if ($committee['chair_name']) : ?>
                            <a href="<?php echo get_edit_post_link($committee['chair_id']); ?>">
                                <?php echo esc_html($committee['chair_name']); ?>
                            </a>
                            <?php if ($committee['chair_email']) : ?>
                                <a href="mailto:<?php echo esc_attr($committee['chair_email']); ?>" style="margin-left: 5px;">
                                    <span class="dashicons dashicons-email-alt" style="font-size: 16px;"></span>
                                </a>
                            <?php endif; ?>
                        <?php else : ?>
                            <span style="color: #dc3232;">No Chair Assigned</span>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <strong>Members:</strong> <?php echo $committee['member_count']; ?>
                        <?php if ($committee['member_count'] > 0) : ?>
                            <a href="#" class="toggle-members" data-committee="<?php echo $committee['id']; ?>" style="margin-left: 10px;">View</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <strong>Schedule:</strong>
                        <?php echo $committee['meeting_schedule'] ?: '<span style="color: #999;">Not set</span>'; ?>
                    </div>
                    
                    <div>
                        <strong>Next Meeting:</strong>
                        <?php if ($committee['next_meeting']) : ?>
                            <?php echo date('M j, Y', strtotime($committee['next_meeting'])); ?>
                        <?php else : ?>
                            <span style="color: #999;">TBD</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($committee['email']) : ?>
                    <div style="margin-bottom: 15px;">
                        <strong>Committee Email:</strong>
                        <a href="mailto:<?php echo esc_attr($committee['email']); ?>"><?php echo esc_html($committee['email']); ?></a>
                    </div>
                <?php endif; ?>
                
                <!-- Members List (Hidden by default) -->
                <div class="members-list" id="members-<?php echo $committee['id']; ?>" style="display: none; margin: 15px 0; padding: 15px; background: #f5f5f5; border-radius: 3px;">
                    <h4 style="margin: 0 0 10px 0;">Committee Members:</h4>
                    <?php if (!empty($committee['members'])) : ?>
                        <ul style="margin: 0; padding-left: 20px;">
                            <?php foreach ($committee['members'] as $member_id) : 
                                $member = get_post($member_id);
                                if ($member) :
                                    $member_email = get_field('owner_email', $member_id);
                                ?>
                                    <li>
                                        <?php echo esc_html($member->post_title); ?>
                                        <?php if ($member_email) : ?>
                                            (<a href="mailto:<?php echo esc_attr($member_email); ?>"><?php echo esc_html($member_email); ?></a>)
                                        <?php endif; ?>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    
                    <!-- Quick Add Member -->
                    <form method="post" style="margin-top: 10px;">
                        <?php wp_nonce_field('villa_committees_nonce'); ?>
                        <input type="hidden" name="action" value="quick_add_member">
                        <input type="hidden" name="committee_id" value="<?php echo $committee['id']; ?>">
                        <select name="owner_id" style="margin-right: 10px;">
                            <option value="">Add Member...</option>
                            <?php foreach ($active_owners as $owner) : ?>
                                <?php if (!in_array($owner->ID, $committee['members'])) : ?>
                                    <option value="<?php echo $owner->ID; ?>"><?php echo esc_html($owner->post_title); ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="button button-small">Add</button>
                    </form>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 15px;">
                    <a href="<?php echo $committee['edit_link']; ?>" class="button button-small">Edit Details</a>
                    <?php if ($committee['email']) : ?>
                        <a href="mailto:<?php echo esc_attr($committee['email']); ?>" class="button button-small">Email Committee</a>
                    <?php endif; ?>
                    <?php if ($committee['chair_email']) : ?>
                        <a href="mailto:<?php echo esc_attr($committee['chair_email']); ?>" class="button button-small">Email Chair</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Quick Actions -->
    <div style="margin-top: 30px;">
        <a href="<?php echo admin_url('post-new.php?post_type=villa_committee'); ?>" class="button button-primary">Create New Committee</a>
        <a href="<?php echo admin_url('admin.php?page=villa-json-sync'); ?>" class="button">Sync with JSON</a>
        <a href="<?php echo admin_url('export.php?content=villa_committee'); ?>" class="button">Export Committees</a>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Toggle members list
    $('.toggle-members').on('click', function(e) {
        e.preventDefault();
        var committeeId = $(this).data('committee');
        var $membersList = $('#members-' + committeeId);
        
        if ($membersList.is(':visible')) {
            $membersList.slideUp();
            $(this).text('View');
        } else {
            $membersList.slideDown();
            $(this).text('Hide');
        }
    });
});
</script>