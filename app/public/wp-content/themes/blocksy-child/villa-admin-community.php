<?php
/**
 * Villa Community Admin Page
 * Manage committees, communications, and community features
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Check user permissions
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

// Load committee data
$committees_file = get_stylesheet_directory() . '/villa-data/committees.json';
$committees_data = [];

if (file_exists($committees_file)) {
    $committees_data = json_decode(file_get_contents($committees_file), true);
}
?>

<div class="wrap">
    <h1>🏛️ Community Management</h1>
    
    <!-- Tab Navigation -->
    <nav class="nav-tab-wrapper">
        <a href="#committees" class="nav-tab nav-tab-active" onclick="showTab('committees')">🏛️ Committees</a>
        <a href="#communications" class="nav-tab" onclick="showTab('communications')">📢 Communications</a>
        <a href="#events" class="nav-tab" onclick="showTab('events')">📅 Events</a>
        <a href="#documents" class="nav-tab" onclick="showTab('documents')">📄 Documents</a>
    </nav>

    <!-- Committees Tab -->
    <div id="committees-tab" class="tab-content">
        <h2>🏛️ Committee Management</h2>
        
        <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>Committee Directory</h3>
                <button class="button button-primary" onclick="addNewCommittee()">
                    ➕ Add New Committee
                </button>
            </div>
            
            <?php if (!empty($committees_data)): ?>
                <table class="wp-list-table widefat">
                    <thead>
                        <tr>
                            <th>Committee Name</th>
                            <th>Members</th>
                            <th>Responsibilities</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($committees_data as $committee): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($committee['name']); ?></strong>
                                    <?php if (!empty($committee['description'])): ?>
                                        <br><small style="color: #666;"><?php echo esc_html($committee['description']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($committee['members'])): ?>
                                        <span class="member-count"><?php echo count($committee['members']); ?> members</span>
                                        <br><small style="color: #666;">
                                            <?php echo implode(', ', array_slice($committee['members'], 0, 2)); ?>
                                            <?php if (count($committee['members']) > 2): ?>
                                                ... +<?php echo count($committee['members']) - 2; ?> more
                                            <?php endif; ?>
                                        </small>
                                    <?php else: ?>
                                        <span style="color: #dc3232;">No members</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($committee['responsibilities'])): ?>
                                        <span class="responsibility-count"><?php echo count($committee['responsibilities']); ?> items</span>
                                    <?php else: ?>
                                        <span style="color: #666;">Not defined</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo !empty($committee['members']) ? 'active' : 'inactive'; ?>">
                                        <?php echo !empty($committee['members']) ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="button button-small" onclick="editCommittee('<?php echo esc_js($committee['name']); ?>')">
                                        Edit
                                    </button>
                                    <button class="button button-small" onclick="viewCommittee('<?php echo esc_js($committee['name']); ?>')">
                                        View
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="notice notice-info">
                    <p>No committees found. <a href="#" onclick="addNewCommittee()">Add your first committee</a>.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Committee Display Settings -->
        <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3>🎨 Frontend Display</h3>
            <p>Use this shortcode to display committees on your website:</p>
            <code style="background: #f1f1f1; padding: 10px; display: block; margin: 10px 0;">[villa_committees]</code>
            
            <div style="margin-top: 15px;">
                <a href="<?php echo home_url('/committees/'); ?>" class="button" target="_blank">
                    👁️ Preview Committee Page
                </a>
            </div>
        </div>
    </div>

    <!-- Communications Tab -->
    <div id="communications-tab" class="tab-content" style="display: none;">
        <h2>📢 Community Communications</h2>
        
        <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3>📧 Email Announcements</h3>
            <p>Send announcements to all owners or specific groups.</p>
            <button class="button button-primary" onclick="alert('Email announcement system coming soon!')">
                📤 Send Announcement
            </button>
        </div>

        <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3>📋 Notice Board</h3>
            <p>Manage community notices and announcements.</p>
            <button class="button" onclick="alert('Notice board management coming soon!')">
                Manage Notices
            </button>
        </div>

        <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3>📱 Communication Preferences</h3>
            <p>Configure how owners receive community communications.</p>
            <button class="button" onclick="alert('Communication preferences coming soon!')">
                Configure Preferences
            </button>
        </div>
    </div>

    <!-- Events Tab -->
    <div id="events-tab" class="tab-content" style="display: none;">
        <h2>📅 Community Events</h2>
        
        <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3>🎉 Upcoming Events</h3>
            <p>Manage community events and meetings.</p>
            <button class="button button-primary" onclick="alert('Event management system coming soon!')">
                ➕ Add New Event
            </button>
        </div>

        <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3>📊 Meeting Minutes</h3>
            <p>Store and manage meeting minutes and records.</p>
            <button class="button" onclick="alert('Meeting minutes system coming soon!')">
                Manage Minutes
            </button>
        </div>
    </div>

    <!-- Documents Tab -->
    <div id="documents-tab" class="tab-content" style="display: none;">
        <h2>📄 Community Documents</h2>
        
        <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3>📋 Bylaws & Policies</h3>
            <p>Manage community bylaws, policies, and governing documents.</p>
            <button class="button button-primary" onclick="alert('Document management system coming soon!')">
                📁 Manage Documents
            </button>
        </div>

        <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3>📊 Financial Reports</h3>
            <p>Store and share financial reports and budgets.</p>
            <button class="button" onclick="alert('Financial reports system coming soon!')">
                💰 Manage Reports
            </button>
        </div>

        <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3>🔒 Owner Resources</h3>
            <p>Private documents and resources for registered owners.</p>
            <button class="button" onclick="alert('Owner resources system coming soon!')">
                🗂️ Manage Resources
            </button>
        </div>
    </div>
</div>

<style>
.nav-tab-wrapper { margin-bottom: 20px; }
.tab-content { margin-top: 20px; }

.member-count {
    background: #e3f2fd;
    padding: 2px 6px;
    border-radius: 3px;
    font-weight: bold;
    color: #1976d2;
}

.responsibility-count {
    background: #f3e5f5;
    padding: 2px 6px;
    border-radius: 3px;
    font-weight: bold;
    color: #7b1fa2;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}

.status-badge.active {
    background: #d4edda;
    color: #155724;
}

.status-badge.inactive {
    background: #f8d7da;
    color: #721c24;
}
</style>

<script>
function showTab(tabName) {
    // Hide all tabs
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

function addNewCommittee() {
    alert('Add new committee functionality coming soon!\n\nThis will allow you to create new committees with members and responsibilities.');
}

function editCommittee(committeeName) {
    alert('Edit committee: ' + committeeName + '\n\nCommittee editing functionality coming soon!');
}

function viewCommittee(committeeName) {
    alert('View committee details: ' + committeeName + '\n\nDetailed committee view coming soon!');
}
</script>
