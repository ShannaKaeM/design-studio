<?php
/**
 * Villa Committees Frontend Display Functions
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get committees data for frontend display
 */
function villa_get_committees_data() {
    $committees_dir = get_stylesheet_directory() . '/villa-data/committees/';
    $committees = [];
    
    if (is_dir($committees_dir)) {
        $committee_folders = scandir($committees_dir);
        
        foreach ($committee_folders as $folder) {
            if ($folder !== '.' && $folder !== '..' && is_dir($committees_dir . $folder)) {
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

/**
 * Render a single committee card
 */
function villa_render_committee_card($committee) {
    $committee_info = $committee['committee_info'] ?? [];
    $branding = $committee['branding'] ?? [];
    $budget = $committee['budget'] ?? [];
    $responsibilities = $committee['responsibilities'] ?? [];
    
    // Get committee details
    $name = $committee_info['name'] ?? 'Unknown Committee';
    $description = $committee_info['description'] ?? '';
    $meeting_frequency = $committee_info['meeting_frequency'] ?? '';
    $meeting_day = $committee_info['meeting_day'] ?? '';
    $meeting_time = $committee_info['meeting_time'] ?? '';
    $annual_allocation = $budget['annual_allocation'] ?? 0;
    
    // Get branding
    $banner_image = $branding['banner_image'] ?? '/wp-content/themes/blocksy-child/villa-data/default-banner.jpg';
    $logo_image = $branding['logo_image'] ?? '/wp-content/themes/blocksy-child/villa-data/default-logo.png';
    
    // Format meeting schedule
    $meeting_schedule = '';
    if ($meeting_frequency && $meeting_day && $meeting_time) {
        $meeting_schedule = ucfirst($meeting_frequency) . ' - ' . ucfirst(str_replace('_', ' ', $meeting_day)) . ' at ' . date('g:i A', strtotime($meeting_time));
    }
    
    ob_start();
    ?>
    <article class="villa-committee-card" data-committee-id="<?php echo esc_attr($committee_info['id'] ?? ''); ?>">
        <!-- Committee Banner -->
        <div class="committee-banner">
            <img src="<?php echo esc_url($banner_image); ?>" alt="<?php echo esc_attr($name); ?> Banner" class="committee-banner-image">
            <div class="committee-banner-overlay">
                <div class="committee-logo">
                    <img src="<?php echo esc_url($logo_image); ?>" alt="<?php echo esc_attr($name); ?> Logo" class="committee-logo-image">
                </div>
            </div>
        </div>
        
        <!-- Committee Content -->
        <div class="committee-content">
            <!-- Committee Header -->
            <header class="committee-header">
                <h3 class="committee-title"><?php echo esc_html($name); ?></h3>
                <p class="committee-description"><?php echo esc_html($description); ?></p>
            </header>
            
            <?php if (!empty($responsibilities)): ?>
            <!-- Key Responsibilities -->
            <div class="committee-responsibilities">
                <h4 class="responsibilities-title">Key Responsibilities</h4>
                <ul class="responsibilities-list">
                    <?php foreach ($responsibilities as $responsibility): ?>
                        <li class="responsibility-item"><?php echo esc_html($responsibility); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <!-- Committee Details -->
            <div class="committee-details">
                <?php if ($meeting_schedule): ?>
                <div class="detail-item">
                    <span class="detail-label">Meeting Schedule:</span>
                    <span class="detail-value"><?php echo esc_html($meeting_schedule); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($annual_allocation): ?>
                <div class="detail-item">
                    <span class="detail-label">Annual Budget:</span>
                    <span class="detail-value">$<?php echo esc_html(number_format($annual_allocation)); ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Committee Actions -->
            <div class="committee-actions">
                <button class="btn btn-primary committee-join-btn">Join Committee</button>
                <button class="btn btn-secondary committee-learn-btn">Learn More</button>
            </div>
        </div>
    </article>
    <?php
    return ob_get_clean();
}

/**
 * Render all committees grid
 */
function villa_render_committees_grid() {
    $committees = villa_get_committees_data();
    
    if (empty($committees)) {
        return '<p>No committees found.</p>';
    }
    
    ob_start();
    ?>
    <section class="villa-committees-section">
        <div class="container">
            <!-- Section Header -->
            <header class="section-header">
                <h2 class="section-title">Villa Capriani Committees</h2>
                <p class="section-description">Discover our dedicated committees working to enhance and maintain our beautiful community. Each committee plays a vital role in preserving Villa Capriani's excellence.</p>
            </header>
            
            <!-- Committees Grid -->
            <div class="committees-grid">
                <?php foreach ($committees as $committee): ?>
                    <?php echo villa_render_committee_card($committee); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

/**
 * Shortcode to display committees
 */
function villa_committees_shortcode($atts) {
    $atts = shortcode_atts([
        'committee_id' => '', // Optional: show specific committee
        'layout' => 'grid', // grid or list
    ], $atts);
    
    if (!empty($atts['committee_id'])) {
        // Show specific committee
        $committees = villa_get_committees_data();
        foreach ($committees as $committee) {
            if (($committee['committee_info']['id'] ?? '') === $atts['committee_id']) {
                return villa_render_committee_card($committee);
            }
        }
        return '<p>Committee not found.</p>';
    }
    
    // Show all committees
    return villa_render_committees_grid();
}

// Register shortcode
add_shortcode('villa_committees', 'villa_committees_shortcode');

/**
 * Enqueue committee styles
 */
function villa_committees_enqueue_styles() {
    if (is_page() || is_front_page()) {
        wp_enqueue_style(
            'villa-committees-style',
            get_stylesheet_directory_uri() . '/assets/css/villa-committees.css',
            [],
            '1.0.0'
        );
    }
}
add_action('wp_enqueue_scripts', 'villa_committees_enqueue_styles');

/**
 * Add committee data to the responsibilities from your docs
 */
function villa_update_committee_responsibilities() {
    $committees_dir = get_stylesheet_directory() . '/villa-data/committees/';
    
    // Define responsibilities from your docs
    $committee_responsibilities = [
        'tech-marketing' => [
            'Manage and enhance digital communications, website, and online presence',
            'Maintain and evolve a cohesive brand identity and marketing strategy',
            'Improve digital experiences for owners and guests',
            'Implement strategic communication tools for effective community engagement'
        ],
        'architecture' => [
            'Review, approve, and oversee exterior architectural and landscaping projects',
            'Review, approve, and oversee interior design and improvement projects',
            'Provide guidance and assistance to owners with improvement or repair projects',
            'Ensure compliance with approved design standards, strategic plans, and community vision'
        ],
        'bylaws' => [
            'Manage and regularly review policies and bylaws to ensure clarity and legal compliance',
            'Foster transparency and fairness across all governance processes',
            'Provide conflict resolution and mediation support to owners',
            'Ensure alignment with state laws and evolving community standards'
        ],
        'finance' => [
            'Create, oversee, and manage the annual community budget',
            'Monitor financial health and provide transparent reporting to owners',
            'Plan and manage reserve funds strategically',
            'Evaluate expenditures and vendor agreements for cost efficiency and alignment with strategic goals'
        ],
        'operations' => [
            'Hire and oversee key professionals, including the Property Manager, Accountant, and Lawyer',
            'Ensure Property Manager effectively enforces community standards and strategic plans',
            'Review vendor contracts and service agreements to ensure quality and efficiency',
            'Facilitate communication between management, staff, and owners, promptly resolving issues'
        ]
    ];
    
    // Update each committee file if it exists
    foreach ($committee_responsibilities as $committee_id => $responsibilities) {
        $committee_file = $committees_dir . $committee_id . '/committee.json';
        
        if (file_exists($committee_file)) {
            $committee_data = json_decode(file_get_contents($committee_file), true);
            
            if ($committee_data && empty($committee_data['responsibilities'])) {
                $committee_data['responsibilities'] = $responsibilities;
                file_put_contents($committee_file, json_encode($committee_data, JSON_PRETTY_PRINT));
            }
        }
    }
}

// Uncomment to run once to update committee responsibilities
// add_action('init', 'villa_update_committee_responsibilities');
?>
