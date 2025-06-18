<?php
/**
 * Villa Admin Dashboard - Main Overview
 * Custom admin interface matching the existing Villa system
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Check user permissions
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

// Get statistics from ACF posts
$property_count = wp_count_posts('villa_property');
$owner_count = wp_count_posts('villa_owner');
$committee_count = wp_count_posts('villa_committee');
$proposal_count = wp_count_posts('villa_proposal');

// Get active proposals
$active_proposals = get_posts([
    'post_type' => 'villa_proposal',
    'posts_per_page' => 5,
    'meta_query' => [
        [
            'key' => 'proposal_voting_end',
            'value' => current_time('Y-m-d H:i:s'),
            'compare' => '>',
            'type' => 'DATETIME'
        ]
    ]
]);

// Get recent owners
$recent_owners = get_posts([
    'post_type' => 'villa_owner',
    'posts_per_page' => 5,
    'orderby' => 'date',
    'order' => 'DESC'
]);

// Calculate percentages
$total_properties = $property_count->publish + $property_count->private;
$total_owners = $owner_count->publish + $owner_count->pending;
$owner_coverage = $total_properties > 0 ? round(($total_owners / $total_properties) * 100) : 0;

?>

<div class="wrap">
    <h1>🏢 Villa Capriani Management Dashboard</h1>
    <p class="description">Welcome to your comprehensive property management system.</p>

    <!-- Quick Stats Overview -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 30px 0;">
        
        <!-- Users Stats -->
        <div style="background: #fff; padding: 25px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; margin-bottom: 15px;">
                <span style="font-size: 2em; margin-right: 15px;">👥</span>
                <div>
                    <h3 style="margin: 0; color: #0073aa;">Users & Owners</h3>
                    <p style="margin: 5px 0 0 0; color: #666; font-size: 14px;">Owner management & CRM</p>
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin: 15px 0;">
                <div>
                    <div style="font-size: 1.8em; font-weight: bold; color: #0073aa;"><?php echo $owner_count->publish; ?></div>
                    <small style="color: #666;">Active Owners</small>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 1.8em; font-weight: bold; color: #f0b849;"><?php echo $owner_count->pending; ?></div>
                    <small style="color: #666;">Pending</small>
                </div>
            </div>
            <a href="<?php echo admin_url('admin.php?page=villa-owners'); ?>" class="button button-primary" style="width: 100%;">
                Manage Users & Owners
            </a>
        </div>

        <!-- Properties Stats -->
        <div style="background: #fff; padding: 25px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; margin-bottom: 15px;">
                <span style="font-size: 2em; margin-right: 15px;">🏠</span>
                <div>
                    <h3 style="margin: 0; color: #46b450;">Properties</h3>
                    <p style="margin: 5px 0 0 0; color: #666; font-size: 14px;">Property & ownership data</p>
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin: 15px 0;">
                <div>
                    <div style="font-size: 1.8em; font-weight: bold; color: #46b450;"><?php echo $total_properties; ?></div>
                    <small style="color: #666;">Total Units</small>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 1.8em; font-weight: bold; color: #f56e28;">
                        <?php echo $owner_coverage; ?>%
                    </div>
                    <small style="color: #666;">Owner Coverage</small>
                </div>
            </div>
            <a href="<?php echo admin_url('admin.php?page=villa-properties'); ?>" class="button button-primary" style="width: 100%;">
                Manage Properties
            </a>
        </div>

        <!-- Community Stats -->
        <div style="background: #fff; padding: 25px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; margin-bottom: 15px;">
                <span style="font-size: 2em; margin-right: 15px;">🏛️</span>
                <div>
                    <h3 style="margin: 0; color: #826eb4;">Community</h3>
                    <p style="margin: 5px 0 0 0; color: #666; font-size: 14px;">Committees & governance</p>
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin: 15px 0;">
                <div>
                    <div style="font-size: 1.8em; font-weight: bold; color: #826eb4;"><?php echo $committee_count->publish; ?></div>
                    <small style="color: #666;">Committees</small>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 1.8em; font-weight: bold; color: #0073aa;"><?php echo count($active_proposals); ?></div>
                    <small style="color: #666;">Active Votes</small>
                </div>
            </div>
            <a href="<?php echo admin_url('admin.php?page=villa-community'); ?>" class="button button-primary" style="width: 100%;">
                Community Management
            </a>
        </div>

        <!-- System Stats -->
        <div style="background: #fff; padding: 25px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; margin-bottom: 15px;">
                <span style="font-size: 2em; margin-right: 15px;">⚙️</span>
                <div>
                    <h3 style="margin: 0; color: #666;">System</h3>
                    <p style="margin: 5px 0 0 0; color: #666; font-size: 14px;">Sync & maintenance</p>
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin: 15px 0;">
                <div>
                    <div style="font-size: 1.8em; font-weight: bold; color: #46b450;">✓</div>
                    <small style="color: #666;">Sync Active</small>
                </div>
                <div style="text-align: right;">
                    <?php 
                    $last_sync = get_option('studio_last_json_sync', 0);
                    ?>
                    <div style="font-size: 0.9em; color: #666;">
                        <?php echo $last_sync ? human_time_diff($last_sync) . ' ago' : 'Never'; ?>
                    </div>
                    <small style="color: #666;">Last Sync</small>
                </div>
            </div>
            <a href="<?php echo admin_url('admin.php?page=studio-yaml-sync'); ?>" class="button button-secondary" style="width: 100%;">
                Sync Settings
            </a>
        </div>
    </div>

    <!-- Recent Activity -->
    <div style="background: #fff; padding: 25px; border: 1px solid #ddd; border-radius: 8px; margin-top: 30px;">
        <h2>Recent Activity</h2>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <!-- Recent Registrations -->
            <div>
                <h3>Recent Owner Registrations</h3>
                <?php if ($recent_owners) : ?>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach ($recent_owners as $owner) : ?>
                            <li style="padding: 10px 0; border-bottom: 1px solid #eee;">
                                <strong><?php echo esc_html($owner->post_title); ?></strong><br>
                                <small style="color: #666;">
                                    <?php echo human_time_diff(strtotime($owner->post_date)) . ' ago'; ?>
                                    <?php if ($owner->post_status === 'pending') : ?>
                                        <span style="color: #f0b849;">• Pending Review</span>
                                    <?php endif; ?>
                                </small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p style="color: #666;">No recent registrations</p>
                <?php endif; ?>
            </div>
            
            <!-- Active Proposals -->
            <div>
                <h3>Active Proposals</h3>
                <?php if ($active_proposals) : ?>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach ($active_proposals as $proposal) : ?>
                            <li style="padding: 10px 0; border-bottom: 1px solid #eee;">
                                <strong><?php echo esc_html($proposal->post_title); ?></strong><br>
                                <small style="color: #666;">
                                    <?php 
                                    $end_date = get_field('proposal_voting_end', $proposal->ID);
                                    if ($end_date) {
                                        echo 'Voting ends ' . human_time_diff(strtotime($end_date));
                                    }
                                    ?>
                                </small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p style="color: #666;">No active proposals</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div style="background: #f9f9f9; padding: 25px; border: 1px solid #ddd; border-radius: 8px; margin-top: 30px;">
        <h2>Quick Actions</h2>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="<?php echo admin_url('post-new.php?post_type=villa_property'); ?>" class="button">Add Property</a>
            <a href="<?php echo admin_url('post-new.php?post_type=villa_owner'); ?>" class="button">Add Owner</a>
            <a href="<?php echo admin_url('post-new.php?post_type=villa_committee'); ?>" class="button">Create Committee</a>
            <a href="<?php echo admin_url('post-new.php?post_type=villa_proposal'); ?>" class="button">Create Proposal</a>
            <a href="<?php echo admin_url('admin.php?page=studio-yaml-sync'); ?>" class="button">Sync JSON Data</a>
            <a href="<?php echo admin_url('admin.php?page=studio-import-export'); ?>" class="button">Import/Export</a>
        </div>
    </div>
</div>