<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check user permissions
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

// Get committees data
function get_committees_data() {
    $committees_dir = get_stylesheet_directory() . '/villa-data/committees/';
    $committees = [];
    
    if (is_dir($committees_dir)) {
        $committee_folders = scandir($committees_dir);
        foreach ($committee_folders as $folder) {
            if ($folder != '.' && $folder != '..' && is_dir($committees_dir . $folder)) {
                $committee_file = $committees_dir . $folder . '/committee.json';
                if (file_exists($committee_file)) {
                    $committee_data = json_decode(file_get_contents($committee_file), true);
                    if ($committee_data) {
                        $committees[] = $committee_data;
                    }
                }
            }
        }
    }
    
    return $committees;
}

$committees = get_committees_data();
?>

<div class="wrap">
    <h1>Villa Committees Management</h1>
    
    <div class="villa-admin-stats">
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Committees</h3>
                <span class="stat-number"><?php echo count($committees); ?></span>
            </div>
            <div class="stat-card">
                <h3>Active Members</h3>
                <span class="stat-number">
                    <?php 
                    $total_members = 0;
                    foreach ($committees as $committee) {
                        $total_members += count($committee['members'] ?? []);
                    }
                    echo $total_members;
                    ?>
                </span>
            </div>
            <div class="stat-card">
                <h3>Monthly Meetings</h3>
                <span class="stat-number">
                    <?php 
                    $monthly_committees = 0;
                    foreach ($committees as $committee) {
                        if (($committee['committee_info']['meeting_frequency'] ?? '') === 'monthly') {
                            $monthly_committees++;
                        }
                    }
                    echo $monthly_committees;
                    ?>
                </span>
            </div>
            <div class="stat-card">
                <h3>Total Budget</h3>
                <span class="stat-number">
                    $<?php 
                    $total_budget = 0;
                    foreach ($committees as $committee) {
                        $total_budget += $committee['budget']['annual_allocation'] ?? 0;
                    }
                    echo number_format($total_budget, 0);
                    ?>
                </span>
            </div>
        </div>
    </div>

    <div class="villa-admin-controls">
        <input type="text" id="committee-search" placeholder="Search committees..." />
        <select id="meeting-frequency-filter">
            <option value="">All Frequencies</option>
            <option value="monthly">Monthly</option>
            <option value="quarterly">Quarterly</option>
        </select>
        <button type="button" class="button button-primary" onclick="exportCommitteeData()">Export Committee Data</button>
    </div>

    <div class="villa-committees-table">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Committee</th>
                    <th>Description</th>
                    <th>Members</th>
                    <th>Meeting Schedule</th>
                    <th>Budget</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="committees-table-body">
                <?php foreach ($committees as $committee): ?>
                <tr class="committee-row" 
                    data-frequency="<?php echo esc_attr($committee['committee_info']['meeting_frequency'] ?? ''); ?>"
                    data-name="<?php echo esc_attr(strtolower($committee['committee_info']['name'] ?? '')); ?>">
                    
                    <td class="committee-name">
                        <div class="committee-header">
                            <strong style="color: <?php echo esc_attr($committee['branding']['color_scheme']['primary'] ?? '#333'); ?>">
                                <?php echo esc_html($committee['committee_info']['name'] ?? 'Unknown'); ?>
                            </strong>
                            <span class="committee-id"><?php echo esc_html($committee['committee_info']['id'] ?? ''); ?></span>
                        </div>
                    </td>
                    
                    <td class="committee-description">
                        <div class="description-text">
                            <?php echo esc_html(substr($committee['committee_info']['description'] ?? '', 0, 120)); ?>
                            <?php if (strlen($committee['committee_info']['description'] ?? '') > 120): ?>...<?php endif; ?>
                        </div>
                    </td>
                    
                    <td class="committee-members">
                        <div class="member-count">
                            <strong><?php echo count($committee['members'] ?? []); ?></strong> members
                        </div>
                        <?php if (!empty($committee['leadership']['chair']['name'])): ?>
                        <div class="chair-info">
                            Chair: <?php echo esc_html($committee['leadership']['chair']['name']); ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    
                    <td class="meeting-schedule">
                        <div class="frequency">
                            <?php echo esc_html(ucfirst($committee['committee_info']['meeting_frequency'] ?? 'TBD')); ?>
                        </div>
                        <div class="schedule-details">
                            <?php 
                            $meeting_day = $committee['committee_info']['meeting_day'] ?? '';
                            $meeting_time = $committee['committee_info']['meeting_time'] ?? '';
                            if ($meeting_day && $meeting_time) {
                                echo esc_html(ucwords(str_replace('_', ' ', $meeting_day)) . ' at ' . $meeting_time);
                            }
                            ?>
                        </div>
                    </td>
                    
                    <td class="committee-budget">
                        <div class="budget-allocation">
                            $<?php echo number_format($committee['budget']['annual_allocation'] ?? 0, 0); ?>
                        </div>
                        <div class="budget-remaining">
                            $<?php echo number_format($committee['budget']['remaining'] ?? 0, 0); ?> remaining
                        </div>
                    </td>
                    
                    <td class="committee-actions">
                        <button type="button" class="button button-small" onclick="viewCommittee('<?php echo esc_js($committee['committee_info']['id']); ?>')">
                            View Details
                        </button>
                        <button type="button" class="button button-small" onclick="editCommittee('<?php echo esc_js($committee['committee_info']['id']); ?>')">
                            Edit
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.villa-admin-stats {
    margin: 20px 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-align: center;
}

.stat-card h3 {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #666;
    text-transform: uppercase;
}

.stat-number {
    font-size: 32px;
    font-weight: bold;
    color: #2271b1;
}

.villa-admin-controls {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    align-items: center;
}

.villa-admin-controls input,
.villa-admin-controls select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.committee-header {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.committee-id {
    font-size: 12px;
    color: #666;
    font-style: italic;
}

.description-text {
    font-size: 13px;
    line-height: 1.4;
}

.member-count {
    font-weight: bold;
    margin-bottom: 4px;
}

.chair-info {
    font-size: 12px;
    color: #666;
}

.frequency {
    font-weight: bold;
    text-transform: capitalize;
}

.schedule-details {
    font-size: 12px;
    color: #666;
}

.budget-allocation {
    font-weight: bold;
    color: #d63638;
}

.budget-remaining {
    font-size: 12px;
    color: #666;
}

.committee-actions {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
</style>

<script>
// Search functionality
document.getElementById('committee-search').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    filterCommittees();
});

// Frequency filter
document.getElementById('meeting-frequency-filter').addEventListener('change', function() {
    filterCommittees();
});

function filterCommittees() {
    const searchTerm = document.getElementById('committee-search').value.toLowerCase();
    const frequencyFilter = document.getElementById('meeting-frequency-filter').value;
    const rows = document.querySelectorAll('.committee-row');
    
    rows.forEach(row => {
        const name = row.dataset.name;
        const frequency = row.dataset.frequency;
        
        const matchesSearch = name.includes(searchTerm);
        const matchesFrequency = !frequencyFilter || frequency === frequencyFilter;
        
        row.style.display = (matchesSearch && matchesFrequency) ? '' : 'none';
    });
}

function viewCommittee(committeeId) {
    alert('View committee details for: ' + committeeId);
    // TODO: Implement committee detail view
}

function editCommittee(committeeId) {
    alert('Edit committee: ' + committeeId);
    // TODO: Implement committee editing
}

function exportCommitteeData() {
    // Create CSV export
    const committees = <?php echo json_encode($committees); ?>;
    let csv = 'Committee,Description,Members,Meeting Frequency,Budget\n';
    
    committees.forEach(committee => {
        const name = committee.committee_info.name || '';
        const description = (committee.committee_info.description || '').replace(/"/g, '""');
        const memberCount = (committee.members || []).length;
        const frequency = committee.committee_info.meeting_frequency || '';
        const budget = committee.budget.annual_allocation || 0;
        
        csv += `"${name}","${description}",${memberCount},"${frequency}",${budget}\n`;
    });
    
    // Download CSV
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.style.display = 'none';
    a.href = url;
    a.download = 'villa-committees-' + new Date().toISOString().split('T')[0] + '.csv';
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
}
</script>
