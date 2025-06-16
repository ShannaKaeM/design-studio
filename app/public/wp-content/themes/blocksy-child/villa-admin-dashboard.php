<?php
/**
 * Villa Admin Dashboard - Main Overview
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Check user permissions
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

// Load the analysis data for statistics
$analysis_file = get_stylesheet_directory() . '/villa-data/migration/final-ownership-analysis.json';
$analysis_data = null;

if (file_exists($analysis_file)) {
    $analysis_data = json_decode(file_get_contents($analysis_file), true);
}

// Get WordPress users with villa_owner role
$villa_users = get_users(array('role' => 'villa_owner'));

// Calculate statistics
$total_properties = $analysis_data ? count($analysis_data['property_list']) : 0;
$registered_users = count($villa_users);
$email_addresses = $analysis_data ? ($analysis_data['email_campaign_data']['total_unique_emails'] ?? 0) : 0;

// Load committee data
$committees_file = get_stylesheet_directory() . '/villa-data/committees.json';
$committees_count = 0;
if (file_exists($committees_file)) {
    $committees_data = json_decode(file_get_contents($committees_file), true);
    $committees_count = count($committees_data);
}
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
                    <div style="font-size: 1.8em; font-weight: bold; color: #0073aa;"><?php echo $registered_users; ?></div>
                    <small style="color: #666;">Registered Users</small>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 1.8em; font-weight: bold; color: #46b450;"><?php echo $email_addresses; ?></div>
                    <small style="color: #666;">Email Addresses</small>
                </div>
            </div>
            <a href="<?php echo admin_url('admin.php?page=villa-users'); ?>" class="button button-primary" style="width: 100%;">
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
                        <?php echo $total_properties > 0 ? round(($registered_users / $total_properties) * 100) : 0; ?>%
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
                    <p style="margin: 5px 0 0 0; color: #666; font-size: 14px;">Committees & communications</p>
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin: 15px 0;">
                <div>
                    <div style="font-size: 1.8em; font-weight: bold; color: #826eb4;"><?php echo $committees_count; ?></div>
                    <small style="color: #666;">Active Committees</small>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 1.8em; font-weight: bold; color: #dc3232;">0</div>
                    <small style="color: #666;">Pending Events</small>
                </div>
            </div>
            <a href="<?php echo admin_url('admin.php?page=villa-community'); ?>" class="button button-primary" style="width: 100%;">
                Manage Community
            </a>
        </div>

    </div>

    <!-- Recent Activity & Quick Actions -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin: 30px 0;">
        
        <!-- Recent Activity -->
        <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 25px;">
            <h3>📈 Recent Activity</h3>
            
            <?php if (!empty($villa_users)): ?>
                <div style="margin: 20px 0;">
                    <h4 style="color: #666; font-size: 14px; text-transform: uppercase; margin-bottom: 15px;">Latest User Registrations</h4>
                    <?php 
                    $recent_users = array_slice(array_reverse($villa_users), 0, 5);
                    foreach ($recent_users as $user): 
                        $unit = get_user_meta($user->ID, 'villa_unit_number', true);
                    ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f0f0f1;">
                            <div>
                                <strong><?php echo esc_html($user->display_name); ?></strong>
                                <?php if ($unit): ?>
                                    <span style="background: #f0f0f1; padding: 2px 6px; border-radius: 3px; font-size: 12px; margin-left: 10px;">
                                        Unit <?php echo esc_html($unit); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <small style="color: #666;">
                                <?php echo human_time_diff(strtotime($user->user_registered), current_time('timestamp')); ?> ago
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: #666; font-style: italic;">No recent activity. Users will appear here as they register.</p>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 25px;">
            <h3>⚡ Quick Actions</h3>
            
            <div style="margin: 20px 0;">
                <a href="<?php echo admin_url('admin.php?page=villa-users&tab=registration'); ?>" 
                   class="button button-secondary" style="width: 100%; margin-bottom: 10px; text-align: center;">
                    📋 View Registration Form
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=villa-users&tab=email'); ?>" 
                   class="button button-secondary" style="width: 100%; margin-bottom: 10px; text-align: center;">
                    📧 Export Email Lists
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=villa-smtp-settings'); ?>" 
                   class="button button-secondary" style="width: 100%; margin-bottom: 10px; text-align: center;">
                    ⚙️ Configure SMTP
                </a>
                
                <a href="<?php echo home_url('/committees/'); ?>" 
                   class="button button-secondary" style="width: 100%; margin-bottom: 10px; text-align: center;" target="_blank">
                    👁️ Preview Committees
                </a>
            </div>

            <!-- System Status -->
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #f0f0f1;">
                <h4 style="color: #666; font-size: 14px; text-transform: uppercase; margin-bottom: 15px;">System Status</h4>
                
                <div style="margin: 10px 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span>Owner Data</span>
                        <span style="color: #46b450; font-weight: bold;">✓ Loaded</span>
                    </div>
                </div>
                
                <div style="margin: 10px 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span>Email System</span>
                        <span style="color: #46b450; font-weight: bold;">✓ Active</span>
                    </div>
                </div>
                
                <div style="margin: 10px 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span>Committees</span>
                        <span style="color: #46b450; font-weight: bold;">✓ Configured</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Information -->
    <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin: 30px 0;">
        <h3 style="margin-top: 0;">ℹ️ System Information</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div>
                <strong>Data Source:</strong><br>
                <small style="color: #666;">JSON-based ownership records</small>
            </div>
            <div>
                <strong>User Management:</strong><br>
                <small style="color: #666;">WordPress integrated CRM</small>
            </div>
            <div>
                <strong>Email System:</strong><br>
                <small style="color: #666;">SMTP with professional templates</small>
            </div>
            <div>
                <strong>Last Updated:</strong><br>
                <small style="color: #666;"><?php echo date('M j, Y g:i A'); ?></small>
            </div>
        </div>
    </div>
</div>

<style>
.wrap h1 { color: #0073aa; }
.button { transition: all 0.2s ease; }
.button:hover { transform: translateY(-1px); }
</style>
