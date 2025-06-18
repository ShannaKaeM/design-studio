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
        // Villa System submenu
        add_submenu_page(
            'the-studio',
            'Villa System',
            'Villa System',
            'manage_options',
            'studio-villa',
            [$this, 'render_villa_dashboard']
        );
        
        // Import/Export submenu
        add_submenu_page(
            'the-studio',
            'Import/Export',
            'Import/Export',
            'manage_options',
            'studio-import-export',
            [$this, 'render_import_export']
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
        // Get stats
        $property_count = wp_count_posts('villa_property');
        $owner_count = wp_count_posts('villa_owner');
        $committee_count = wp_count_posts('villa_committee');
        $proposal_count = wp_count_posts('villa_proposal');
        
        ?>
        <div class="wrap">
            <h1>Studio Content - Villa Management</h1>
            
            <div class="studio-villa-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 30px 0;">
                
                <div class="studio-stat-box" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; text-align: center;">
                    <h3 style="margin: 0 0 10px 0; color: #23282d;"><?php echo $property_count->publish; ?></h3>
                    <p style="margin: 0; color: #666;">Properties</p>
                    <a href="<?php echo admin_url('edit.php?post_type=villa_property'); ?>" class="button button-small" style="margin-top: 10px;">Manage</a>
                </div>
                
                <div class="studio-stat-box" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; text-align: center;">
                    <h3 style="margin: 0 0 10px 0; color: #23282d;"><?php echo $owner_count->publish + $owner_count->pending; ?></h3>
                    <p style="margin: 0; color: #666;">Owners</p>
                    <a href="<?php echo admin_url('edit.php?post_type=villa_owner'); ?>" class="button button-small" style="margin-top: 10px;">Manage</a>
                </div>
                
                <div class="studio-stat-box" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; text-align: center;">
                    <h3 style="margin: 0 0 10px 0; color: #23282d;"><?php echo $committee_count->publish; ?></h3>
                    <p style="margin: 0; color: #666;">Committees</p>
                    <a href="<?php echo admin_url('edit.php?post_type=villa_committee'); ?>" class="button button-small" style="margin-top: 10px;">Manage</a>
                </div>
                
                <div class="studio-stat-box" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; text-align: center;">
                    <h3 style="margin: 0 0 10px 0; color: #23282d;"><?php echo $proposal_count->publish; ?></h3>
                    <p style="margin: 0; color: #666;">Active Proposals</p>
                    <a href="<?php echo admin_url('edit.php?post_type=villa_proposal'); ?>" class="button button-small" style="margin-top: 10px;">Manage</a>
                </div>
                
            </div>
            
            <div class="studio-villa-features" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 30px; margin-top: 30px;">
                <h2>System Features</h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-top: 20px;">
                    
                    <div>
                        <h3>Owner Management</h3>
                        <ul>
                            <li>Automatic registration approval</li>
                            <li>Owner profiles with contact info</li>
                            <li>Property ownership tracking</li>
                            <li>Committee membership management</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3>Property Management</h3>
                        <ul>
                            <li>Property details and specifications</li>
                            <li>Owner assignment</li>
                            <li>Status tracking</li>
                            <li>Photo galleries</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3>Committee System</h3>
                        <ul>
                            <li>Committee creation and management</li>
                            <li>Member assignment</li>
                            <li>Meeting schedules</li>
                            <li>Workspace integration</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3>Voting System</h3>
                        <ul>
                            <li>Proposal creation and tracking</li>
                            <li>Multiple voting types</li>
                            <li>Automated vote counting</li>
                            <li>Result reporting</li>
                        </ul>
                    </div>
                    
                </div>
            </div>
            
            <div class="studio-villa-actions" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 30px; margin-top: 30px;">
                <h2>Quick Actions</h2>
                
                <p>
                    <a href="<?php echo admin_url('post-new.php?post_type=villa_property'); ?>" class="button button-primary">Add New Property</a>
                    <a href="<?php echo admin_url('post-new.php?post_type=villa_owner'); ?>" class="button">Add New Owner</a>
                    <a href="<?php echo admin_url('post-new.php?post_type=villa_committee'); ?>" class="button">Create Committee</a>
                    <a href="<?php echo admin_url('post-new.php?post_type=villa_proposal'); ?>" class="button">Create Proposal</a>
                    <a href="<?php echo admin_url('admin.php?page=studio-import-export'); ?>" class="button">Import/Export Data</a>
                </p>
            </div>
            
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
            
            <div class="notice notice-info">
                <p>YAML sync functionality coming soon. This will enable two-way sync between YAML files and the database.</p>
            </div>
            
            <div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 30px; margin-top: 30px;">
                <h2>Export Data</h2>
                <p>Export your Villa data to YAML files for version control and backup.</p>
                <button class="button button-primary" disabled>Export to YAML</button>
            </div>
            
            <div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 30px; margin-top: 30px;">
                <h2>Import Data</h2>
                <p>Import Villa data from YAML files.</p>
                <button class="button button-primary" disabled>Import from YAML</button>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render YAML sync page
     */
    public function render_yaml_sync() {
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
                <a href="<?php echo admin_url('admin.php?page=studio-content'); ?>" class="button button-primary">Content Dashboard</a>
            </div>
        </div>
        <?php
    }
}

// Initialize
add_action('init', function() {
    VillaLoader::get_instance();
}, 5);