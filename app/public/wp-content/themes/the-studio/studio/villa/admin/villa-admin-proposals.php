<?php
/**
 * Villa Proposals & Voting Admin Page
 * Manage proposals, voting sessions, and results
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Check user permissions
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

// Get all proposals from ACF
$proposals = get_posts([
    'post_type' => 'villa_proposal',
    'posts_per_page' => -1,
    'orderby' => 'date',
    'order' => 'DESC',
    'post_status' => 'any'
]);

// Calculate statistics
$stats = [
    'total_proposals' => count($proposals),
    'draft' => 0,
    'voting' => 0,
    'passed' => 0,
    'failed' => 0,
    'withdrawn' => 0
];

// Get active owners count for quorum calculations
$active_owners = get_posts([
    'post_type' => 'villa_owner',
    'posts_per_page' => -1,
    'meta_key' => 'owner_status',
    'meta_value' => 'active'
]);
$total_voters = count($active_owners);
$default_quorum = ceil($total_voters * 0.51); // 51% default quorum

// Build proposal list with voting data
$proposal_list = [];
foreach ($proposals as $proposal) {
    $proposal_id = $proposal->ID;
    
    // Get proposal fields
    $status = get_field('proposal_status', $proposal_id) ?: 'draft';
    $type = get_field('proposal_type', $proposal_id);
    $submitted_by = get_field('proposal_submitted_by', $proposal_id);
    $committee = get_field('proposal_committee', $proposal_id);
    $description = get_field('proposal_description', $proposal_id);
    $vote_start = get_field('proposal_vote_start', $proposal_id);
    $vote_end = get_field('proposal_vote_end', $proposal_id);
    $quorum_required = get_field('proposal_quorum_required', $proposal_id) ?: $default_quorum;
    $approval_percentage = get_field('proposal_approval_percentage', $proposal_id) ?: 51;
    
    // Get voting results
    $votes_yes = get_field('proposal_votes_yes', $proposal_id) ?: 0;
    $votes_no = get_field('proposal_votes_no', $proposal_id) ?: 0;
    $votes_abstain = get_field('proposal_votes_abstain', $proposal_id) ?: 0;
    $total_votes = $votes_yes + $votes_no + $votes_abstain;
    
    // Update stats
    $stats[$status]++;
    
    // Get submitter info
    $submitter_name = '';
    if ($submitted_by) {
        $submitter = get_post($submitted_by);
        if ($submitter) {
            $submitter_name = $submitter->post_title;
        }
    }
    
    // Get committee info
    $committee_name = '';
    if ($committee) {
        $committee_post = get_post($committee);
        if ($committee_post) {
            $committee_name = $committee_post->post_title;
        }
    }
    
    // Calculate voting status
    $voting_active = false;
    $voting_ended = false;
    if ($vote_start && $vote_end) {
        $now = current_time('timestamp');
        $start_time = strtotime($vote_start);
        $end_time = strtotime($vote_end);
        
        if ($now >= $start_time && $now <= $end_time) {
            $voting_active = true;
        } elseif ($now > $end_time) {
            $voting_ended = true;
        }
    }
    
    // Calculate approval percentage
    $approval_percent = 0;
    if ($total_votes > 0) {
        $approval_percent = round(($votes_yes / ($votes_yes + $votes_no)) * 100, 1);
    }
    
    $proposal_list[] = [
        'id' => $proposal_id,
        'title' => $proposal->post_title,
        'status' => $status,
        'type' => $type,
        'submitter_name' => $submitter_name,
        'committee_name' => $committee_name,
        'description' => $description,
        'vote_start' => $vote_start,
        'vote_end' => $vote_end,
        'quorum_required' => $quorum_required,
        'approval_percentage' => $approval_percentage,
        'votes_yes' => $votes_yes,
        'votes_no' => $votes_no,
        'votes_abstain' => $votes_abstain,
        'total_votes' => $total_votes,
        'approval_percent' => $approval_percent,
        'voting_active' => $voting_active,
        'voting_ended' => $voting_ended,
        'edit_link' => get_edit_post_link($proposal_id),
        'created_date' => $proposal->post_date
    ];
}

// Handle form submissions
if (isset($_POST['action'])) {
    check_admin_referer('villa_proposals_nonce');
    
    switch ($_POST['action']) {
        case 'update_proposal_status':
            $proposal_id = intval($_POST['proposal_id']);
            $new_status = sanitize_text_field($_POST['status']);
            update_field('proposal_status', $new_status, $proposal_id);
            
            // If starting voting, set dates if not set
            if ($new_status === 'voting' && !get_field('proposal_vote_start', $proposal_id)) {
                update_field('proposal_vote_start', current_time('Y-m-d'), $proposal_id);
                update_field('proposal_vote_end', date('Y-m-d', strtotime('+7 days')), $proposal_id);
            }
            break;
            
        case 'close_voting':
            $proposal_id = intval($_POST['proposal_id']);
            
            // Get current votes
            $votes_yes = get_field('proposal_votes_yes', $proposal_id) ?: 0;
            $votes_no = get_field('proposal_votes_no', $proposal_id) ?: 0;
            $total_votes = $votes_yes + $votes_no + get_field('proposal_votes_abstain', $proposal_id) ?: 0;
            $quorum_required = get_field('proposal_quorum_required', $proposal_id) ?: $default_quorum;
            $approval_percentage = get_field('proposal_approval_percentage', $proposal_id) ?: 51;
            
            // Determine result
            if ($total_votes < $quorum_required) {
                update_field('proposal_status', 'failed', $proposal_id);
                update_field('proposal_result_notes', 'Failed to meet quorum', $proposal_id);
            } else {
                $approval_percent = ($votes_yes / ($votes_yes + $votes_no)) * 100;
                if ($approval_percent >= $approval_percentage) {
                    update_field('proposal_status', 'passed', $proposal_id);
                } else {
                    update_field('proposal_status', 'failed', $proposal_id);
                }
            }
            break;
    }
    
    wp_redirect(admin_url('admin.php?page=villa-proposals&updated=1'));
    exit;
}

?>
<div class="wrap">
    <h1>Villa Capriani - Proposals & Voting</h1>
    
    <?php if (isset($_GET['updated'])) : ?>
        <div class="notice notice-success is-dismissible">
            <p>Proposal updated successfully!</p>
        </div>
    <?php endif; ?>
    
    <!-- Summary Statistics -->
    <div class="villa-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px; margin: 20px 0;">
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px 0; color: #333;">Total</h3>
            <div style="font-size: 2em; color: #0073aa; font-weight: bold;"><?php echo $stats['total_proposals']; ?></div>
        </div>
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px 0; color: #333;">Draft</h3>
            <div style="font-size: 2em; color: #666; font-weight: bold;"><?php echo $stats['draft']; ?></div>
        </div>
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px 0; color: #333;">Voting</h3>
            <div style="font-size: 2em; color: #f56e28; font-weight: bold;"><?php echo $stats['voting']; ?></div>
        </div>
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px 0; color: #333;">Passed</h3>
            <div style="font-size: 2em; color: #46b450; font-weight: bold;"><?php echo $stats['passed']; ?></div>
        </div>
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px 0; color: #333;">Failed</h3>
            <div style="font-size: 2em; color: #dc3232; font-weight: bold;"><?php echo $stats['failed']; ?></div>
        </div>
        <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px 0; color: #333;">Active Voters</h3>
            <div style="font-size: 2em; color: #00a0d2; font-weight: bold;"><?php echo $total_voters; ?></div>
        </div>
    </div>

    <!-- Filter Controls -->
    <div style="background: #fff; padding: 15px; border: 1px solid #ddd; margin: 20px 0; border-radius: 5px;">
        <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            <select id="filter-status" style="padding: 5px 10px;">
                <option value="">All Status</option>
                <option value="draft">Draft</option>
                <option value="voting">Voting</option>
                <option value="passed">Passed</option>
                <option value="failed">Failed</option>
                <option value="withdrawn">Withdrawn</option>
            </select>
            <select id="filter-type" style="padding: 5px 10px;">
                <option value="">All Types</option>
                <option value="bylaw">Bylaw Amendment</option>
                <option value="budget">Budget</option>
                <option value="capital">Capital Improvement</option>
                <option value="policy">Policy</option>
                <option value="other">Other</option>
            </select>
            <input type="text" id="search-proposals" placeholder="Search proposals..." style="padding: 5px 10px; border: 1px solid #ddd; border-radius: 3px; width: 300px;">
        </div>
    </div>

    <!-- Proposals Table -->
    <div style="background: #fff; padding: 0; border: 1px solid #ddd; border-radius: 5px; overflow: hidden;">
        <table class="wp-list-table widefat fixed striped" id="proposals-table">
            <thead>
                <tr>
                    <th>Proposal</th>
                    <th style="width: 100px;">Type</th>
                    <th style="width: 150px;">Submitted By</th>
                    <th style="width: 100px;">Status</th>
                    <th style="width: 150px;">Voting Period</th>
                    <th style="width: 200px;">Results</th>
                    <th style="width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($proposal_list as $proposal) : ?>
                    <tr data-status="<?php echo esc_attr($proposal['status']); ?>" 
                        data-type="<?php echo esc_attr($proposal['type']); ?>">
                        <td>
                            <strong><?php echo esc_html($proposal['title']); ?></strong>
                            <?php if ($proposal['description']) : ?>
                                <br><span style="color: #666; font-size: 0.9em;"><?php echo esc_html(wp_trim_words($proposal['description'], 20)); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $type_labels = [
                                'bylaw' => 'Bylaw',
                                'budget' => 'Budget',
                                'capital' => 'Capital',
                                'policy' => 'Policy',
                                'other' => 'Other'
                            ];
                            echo $type_labels[$proposal['type']] ?? $proposal['type'];
                            ?>
                        </td>
                        <td>
                            <?php echo esc_html($proposal['submitter_name']); ?>
                            <?php if ($proposal['committee_name']) : ?>
                                <br><small style="color: #666;"><?php echo esc_html($proposal['committee_name']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="post" style="display: inline;">
                                <?php wp_nonce_field('villa_proposals_nonce'); ?>
                                <input type="hidden" name="action" value="update_proposal_status">
                                <input type="hidden" name="proposal_id" value="<?php echo $proposal['id']; ?>">
                                <select name="status" onchange="this.form.submit()" style="font-size: 12px;">
                                    <option value="draft" <?php selected($proposal['status'], 'draft'); ?>>Draft</option>
                                    <option value="voting" <?php selected($proposal['status'], 'voting'); ?>>Voting</option>
                                    <option value="passed" <?php selected($proposal['status'], 'passed'); ?>>Passed</option>
                                    <option value="failed" <?php selected($proposal['status'], 'failed'); ?>>Failed</option>
                                    <option value="withdrawn" <?php selected($proposal['status'], 'withdrawn'); ?>>Withdrawn</option>
                                </select>
                            </form>
                            <?php if ($proposal['voting_active']) : ?>
                                <br><span style="color: #46b450; font-size: 0.9em;">Active</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($proposal['vote_start'] && $proposal['vote_end']) : ?>
                                <?php echo date('M j', strtotime($proposal['vote_start'])); ?> - 
                                <?php echo date('M j, Y', strtotime($proposal['vote_end'])); ?>
                                <?php if ($proposal['voting_active']) : ?>
                                    <br><small style="color: #f56e28;">
                                        <?php 
                                        $days_left = ceil((strtotime($proposal['vote_end']) - current_time('timestamp')) / 86400);
                                        echo $days_left . ' days left';
                                        ?>
                                    </small>
                                <?php endif; ?>
                            <?php else : ?>
                                <span style="color: #999;">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($proposal['total_votes'] > 0) : ?>
                                <div style="margin-bottom: 5px;">
                                    <span style="color: #46b450;">Yes: <?php echo $proposal['votes_yes']; ?></span> | 
                                    <span style="color: #dc3232;">No: <?php echo $proposal['votes_no']; ?></span> | 
                                    <span style="color: #666;">Abstain: <?php echo $proposal['votes_abstain']; ?></span>
                                </div>
                                <div style="background: #e0e0e0; height: 20px; border-radius: 3px; overflow: hidden;">
                                    <?php 
                                    $yes_width = ($proposal['votes_yes'] / $proposal['total_votes']) * 100;
                                    $no_width = ($proposal['votes_no'] / $proposal['total_votes']) * 100;
                                    ?>
                                    <div style="float: left; background: #46b450; height: 100%; width: <?php echo $yes_width; ?>%;"></div>
                                    <div style="float: left; background: #dc3232; height: 100%; width: <?php echo $no_width; ?>%;"></div>
                                </div>
                                <small>
                                    Total: <?php echo $proposal['total_votes']; ?>/<?php echo $total_voters; ?> 
                                    (<?php echo round(($proposal['total_votes'] / $total_voters) * 100); ?>%)
                                    | Approval: <?php echo $proposal['approval_percent']; ?>%
                                </small>
                            <?php else : ?>
                                <span style="color: #999;">No votes yet</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo $proposal['edit_link']; ?>" class="button button-small">Edit</a>
                            <?php if ($proposal['voting_ended'] && $proposal['status'] === 'voting') : ?>
                                <form method="post" style="display: inline;">
                                    <?php wp_nonce_field('villa_proposals_nonce'); ?>
                                    <input type="hidden" name="action" value="close_voting">
                                    <input type="hidden" name="proposal_id" value="<?php echo $proposal['id']; ?>">
                                    <button type="submit" class="button button-small">Close</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Quick Actions -->
    <div style="margin-top: 30px;">
        <a href="<?php echo admin_url('post-new.php?post_type=villa_proposal'); ?>" class="button button-primary">Create New Proposal</a>
        <a href="<?php echo admin_url('admin.php?page=villa-voting-report'); ?>" class="button">Voting Reports</a>
        <a href="<?php echo admin_url('admin.php?page=villa-json-sync'); ?>" class="button">Sync with JSON</a>
        <a href="<?php echo admin_url('export.php?content=villa_proposal'); ?>" class="button">Export Proposals</a>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Filter functionality
    function filterTable() {
        var statusFilter = $('#filter-status').val();
        var typeFilter = $('#filter-type').val();
        var searchTerm = $('#search-proposals').val().toLowerCase();
        
        $('#proposals-table tbody tr').each(function() {
            var $row = $(this);
            var status = $row.data('status');
            var type = $row.data('type');
            var rowText = $row.text().toLowerCase();
            
            var showRow = true;
            
            if (statusFilter && status !== statusFilter) showRow = false;
            if (typeFilter && type !== typeFilter) showRow = false;
            if (searchTerm && !rowText.includes(searchTerm)) showRow = false;
            
            $row.toggle(showRow);
        });
    }
    
    $('#filter-status, #filter-type').on('change', filterTable);
    $('#search-proposals').on('keyup', filterTable);
});
</script>