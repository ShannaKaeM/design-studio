<?php
/**
 * Villa System Loader
 * 
 * Initializes the Villa management system
 * 
 * @package TheStudio
 */

namespace Studio\Villa;

class VillaLoader {
    
    /**
     * Instance
     */
    private static $instance = null;
    
    /**
     * Components
     */
    private $post_types;
    private $acf_fields;
    
    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize
     */
    private function init() {
        // Load components
        $this->load_components();
        
        // Add admin menu
        add_action('admin_menu', [$this, 'add_admin_menu'], 20);
        
        // Add dashboard widget
        add_action('wp_dashboard_setup', [$this, 'add_dashboard_widget']);
    }
    
    /**
     * Load components
     */
    private function load_components() {
        // Load post types
        require_once STUDIO_DIR . '/studio/villa/villa-post-types.php';
        $this->post_types = new VillaPostTypes();
        
        // Load ACF fields
        require_once STUDIO_DIR . '/studio/villa/villa-acf-fields.php';
        $this->acf_fields = new VillaACFFields();
        
        // Load JSON sync
        if (file_exists(STUDIO_DIR . '/studio/villa/json-sync.php')) {
            require_once STUDIO_DIR . '/studio/villa/json-sync.php';
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Remove the default first submenu item that WordPress creates
        remove_submenu_page('villa-admin', 'villa-admin');
        
        // Villa Overview submenu
        add_submenu_page(
            'villa-admin',
            'Overview',
            'Overview',
            'manage_options',
            'villa-admin',
            [$this, 'render_villa_dashboard']
        );
        
        // Custom admin pages (not CPT pages)
        add_submenu_page(
            'villa-admin',
            'Properties',
            'Properties',
            'manage_options',
            'villa-properties',
            [$this, 'render_properties_page']
        );
        
        add_submenu_page(
            'villa-admin',
            'Owners & CRM',
            'Owners & CRM',
            'manage_options',
            'villa-owners',
            [$this, 'render_owners_page']
        );
        
        add_submenu_page(
            'villa-admin',
            'Committees',
            'Committees',
            'manage_options',
            'villa-committees',
            [$this, 'render_committees_page']
        );
        
        add_submenu_page(
            'villa-admin',
            'Proposals & Voting',
            'Proposals & Voting',
            'manage_options',
            'villa-proposals',
            [$this, 'render_proposals_page']
        );
        
        // Registration submenu
        add_submenu_page(
            'villa-admin',
            'Registration',
            'Registration',
            'manage_options',
            'villa-registration',
            [$this, 'render_registration_page']
        );
        
        // Import/Export submenu
        add_submenu_page(
            'villa-admin',
            'Import/Export',
            'Import/Export',
            'manage_options',
            'villa-import-export',
            [$this, 'render_import_export']
        );
        
        // JSON Sync submenu
        add_submenu_page(
            'villa-admin',
            'JSON Sync',
            'JSON Sync',
            'manage_options',
            'villa-json-sync',
            [$this, 'render_json_sync']
        );
        
        // Settings submenu
        add_submenu_page(
            'villa-admin',
            'Settings',
            'Settings',
            'manage_options',
            'villa-settings',
            [$this, 'render_settings_page']
        );
    }
    
    /**
     * Add dashboard widget
     */
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'studio_villa_overview',
            'Villa Overview',
            [$this, 'render_dashboard_widget']
        );
    }
    
    /**
     * Render Villa dashboard
     */
    public function render_villa_dashboard() {
        // Use the custom admin dashboard
        if (file_exists(STUDIO_DIR . '/studio/villa/admin/villa-admin-dashboard.php')) {
            require_once STUDIO_DIR . '/studio/villa/admin/villa-admin-dashboard.php';
            return;
        }
        
        // Fallback
        echo '<div class="wrap"><h1>Villa Dashboard</h1><p>Loading...</p></div>';
    }
    
    /**
     * Render properties page
     */
    public function render_properties_page() {
        if (file_exists(STUDIO_DIR . '/studio/villa/admin/villa-admin-properties.php')) {
            require_once STUDIO_DIR . '/studio/villa/admin/villa-admin-properties.php';
            return;
        }
        
        // Fallback to CPT list
        ?>
        <div class="wrap">
            <h1>Properties</h1>
            <p>Custom properties interface coming soon.</p>
            <p><a href="<?php echo admin_url('edit.php?post_type=villa_property'); ?>" class="button">View Properties (CPT)</a></p>
        </div>
        <?php
    }
    
    /**
     * Render owners page
     */
    public function render_owners_page() {
        if (file_exists(STUDIO_DIR . '/studio/villa/admin/villa-admin-owners.php')) {
            require_once STUDIO_DIR . '/studio/villa/admin/villa-admin-owners.php';
            return;
        }
        
        // Fallback to CPT list
        ?>
        <div class="wrap">
            <h1>Owners & CRM</h1>
            <p>Custom CRM interface coming soon.</p>
            <p><a href="<?php echo admin_url('edit.php?post_type=villa_owner'); ?>" class="button">View Owners (CPT)</a></p>
        </div>
        <?php
    }
    
    /**
     * Render committees page
     */
    public function render_committees_page() {
        if (file_exists(STUDIO_DIR . '/studio/villa/admin/villa-admin-committees.php')) {
            require_once STUDIO_DIR . '/studio/villa/admin/villa-admin-committees.php';
            return;
        }
        
        // Fallback to CPT list
        ?>
        <div class="wrap">
            <h1>Committees</h1>
            <p>Custom committees interface coming soon.</p>
            <p><a href="<?php echo admin_url('edit.php?post_type=villa_committee'); ?>" class="button">View Committees (CPT)</a></p>
        </div>
        <?php
    }
    
    /**
     * Render proposals page
     */
    public function render_proposals_page() {
        if (file_exists(STUDIO_DIR . '/studio/villa/admin/villa-admin-proposals.php')) {
            require_once STUDIO_DIR . '/studio/villa/admin/villa-admin-proposals.php';
            return;
        }
        
        // Fallback to CPT list
        ?>
        <div class="wrap">
            <h1>Proposals & Voting</h1>
            <p>Custom voting interface coming soon.</p>
            <p><a href="<?php echo admin_url('edit.php?post_type=villa_proposal'); ?>" class="button">View Proposals (CPT)</a></p>
        </div>
        <?php
    }
    
    /**
     * Render import/export page
     */
    public function render_import_export() {
        ?>
        <div class="wrap">
            <h1>Import/Export Villa Data</h1>
            
            <div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 30px; margin-top: 30px;">
                <h2>Export Data</h2>
                <p>Export your Villa data to JSON/YAML files for version control and backup.</p>
                <button class="button button-primary" onclick="alert('Export functionality coming soon')">Export to JSON</button>
            </div>
            
            <div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 30px; margin-top: 30px;">
                <h2>Import Data</h2>
                <p>Import Villa data from JSON/YAML files.</p>
                <button class="button button-primary" onclick="alert('Import functionality coming soon')">Import from JSON</button>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render JSON sync page
     */
    public function render_json_sync() {
        // Handle sync action
        if (isset($_POST['sync_now']) && wp_verify_nonce($_POST['_wpnonce'], 'json_sync')) {
            $sync = new \Studio\Villa\JSONSync();
            $results = $sync->sync_all_json_to_acf();
        }
        
        // Get sync status
        $last_sync = get_option('studio_last_json_sync', 0);
        $sync_logs = get_option('studio_sync_logs', []);
        
        ?>
        <div class="wrap">
            <h1>JSON/Database Sync</h1>
            
            <div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 30px; margin-top: 30px;">
                <h2>Sync Configuration</h2>
                <p>Two-way sync between JSON files and ACF database.</p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">JSON Location</th>
                        <td><code>/blocksy-child/villa-data/</code></td>
                    </tr>
                    <tr>
                        <th scope="row">Sync Mode</th>
                        <td>
                            <strong>Two-way sync enabled</strong><br>
                            <small>• JSON changes sync to database hourly<br>
                            • Database changes sync to JSON immediately</small>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Last Sync</th>
                        <td>
                            <?php if ($last_sync) : ?>
                                <?php echo human_time_diff($last_sync) . ' ago'; ?>
                                <small>(<?php echo date('Y-m-d H:i:s', $last_sync); ?>)</small>
                            <?php else : ?>
                                Never
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                
                <form method="post">
                    <?php wp_nonce_field('json_sync'); ?>
                    <p class="submit">
                        <button type="submit" name="sync_now" class="button button-primary">Sync Now</button>
                        <span class="description">Manually sync all JSON files to database</span>
                    </p>
                </form>
                
                <?php if (isset($results)) : ?>
                    <div class="notice notice-success">
                        <p><strong>Sync completed!</strong></p>
                        <ul>
                            <?php foreach ($results as $key => $value) : ?>
                                <?php if ($value > 0) : ?>
                                    <li><?php echo str_replace('_', ' ', ucfirst($key)) . ': ' . $value; ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
            
            <div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 30px; margin-top: 30px;">
                <h2>Recent Sync Activity</h2>
                <?php if (!empty($sync_logs)) : ?>
                    <table class="widefat">
                        <thead>
                            <tr>
                                <th>File</th>
                                <th>Direction</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($sync_logs, 0, 20) as $log) : ?>
                                <tr>
                                    <td><code><?php echo esc_html($log['file']); ?></code></td>
                                    <td>
                                        <?php if ($log['direction'] === 'json_to_acf') : ?>
                                            JSON → Database
                                        <?php else : ?>
                                            Database → JSON
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html($log['timestamp']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p>No sync activity yet.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render registration page
     */
    public function render_registration_page() {
        ?>
        <div class="wrap">
            <h1>Owner Registration</h1>
            <p>Manage owner registration requests and approvals.</p>
            <div class="notice notice-info">
                <p>Registration system with automatic approval coming soon!</p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>Villa Settings</h1>
            <p>Configure Villa system settings.</p>
            <div class="notice notice-info">
                <p>Settings page coming soon!</p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render dashboard widget
     */
    public function render_dashboard_widget() {
        $property_count = wp_count_posts('villa_property');
        $owner_count = wp_count_posts('villa_owner');
        
        // Get pending owners
        $pending_owners = get_posts([
            'post_type' => 'villa_owner',
            'post_status' => 'pending',
            'posts_per_page' => 5,
            'fields' => 'ids'
        ]);
        
        ?>
        <div class="studio-villa-widget">
            <div style="display: flex; justify-content: space-around; margin-bottom: 15px;">
                <div style="text-align: center;">
                    <strong style="font-size: 24px; display: block;"><?php echo $property_count->publish; ?></strong>
                    <span>Properties</span>
                </div>
                <div style="text-align: center;">
                    <strong style="font-size: 24px; display: block;"><?php echo $owner_count->publish; ?></strong>
                    <span>Active Owners</span>
                </div>
                <?php if ($owner_count->pending > 0) : ?>
                <div style="text-align: center;">
                    <strong style="font-size: 24px; display: block; color: #f0b849;"><?php echo $owner_count->pending; ?></strong>
                    <span>Pending</span>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($pending_owners)) : ?>
                <div style="border-top: 1px solid #eee; padding-top: 15px;">
                    <strong>Recent Registrations:</strong>
                    <ul style="margin: 10px 0 0 0;">
                        <?php foreach ($pending_owners as $owner_id) : ?>
                            <li>
                                <a href="<?php echo get_edit_post_link($owner_id); ?>">
                                    <?php echo get_the_title($owner_id); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div style="margin-top: 15px; text-align: center;">
                <a href="<?php echo admin_url('admin.php?page=villa-admin'); ?>" class="button button-primary">Villa Dashboard</a>
            </div>
        </div>
        <?php
    }
}

// Initialize
add_action('init', function() {
    VillaLoader::get_instance();
}, 5);