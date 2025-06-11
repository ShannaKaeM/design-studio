<?php
/**
 * DS-Studio Admin Page - Brand New Clean Version
 * 
 * Super simple admin interface for theme.json utility generation
 */

if (!defined('ABSPATH')) {
    exit;
}

class DS_Studio_Admin_Page {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_ds_studio_regenerate_utilities', array($this, 'ajax_regenerate_utilities'));
    }
    
    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_theme_page(
            'DS-Studio',
            'DS-Studio',
            'edit_theme_options',
            'ds-studio',
            array($this, 'render_admin_page')
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'appearance_page_ds-studio') {
            return;
        }
        
        // Inline styles for clean admin UI
        wp_add_inline_style('wp-admin', '
            .ds-studio-container {
                max-width: 900px;
                margin: 20px 0;
            }
            .ds-status-card {
                background: white;
                border: 1px solid #c3c4c7;
                border-radius: 8px;
                padding: 24px;
                margin-bottom: 20px;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
            .ds-status-card h2 {
                margin: 0 0 16px 0;
                font-size: 18px;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .ds-token-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 16px;
                margin: 16px 0;
            }
            .ds-token-item {
                background: #f6f7f7;
                padding: 16px;
                border-radius: 6px;
                text-align: center;
            }
            .ds-token-count {
                font-size: 24px;
                font-weight: 600;
                color: #1d2327;
                display: block;
            }
            .ds-token-label {
                font-size: 13px;
                color: #646970;
                margin-top: 4px;
            }
            .ds-action-buttons {
                display: flex;
                gap: 12px;
                margin-top: 20px;
            }
            .ds-success { color: #00a32a; }
            .ds-error { color: #d63638; }
            .ds-warning { color: #dba617; }
        ');
    }
    
    /**
     * Render the brand new clean admin page
     */
    public function render_admin_page() {
        // Check for theme.json
        $theme_json_path = get_stylesheet_directory() . '/theme.json';
        $theme_json_exists = file_exists($theme_json_path);
        $theme_data = null;
        
        if ($theme_json_exists) {
            $theme_data = json_decode(file_get_contents($theme_json_path), true);
        }
        
        // Get utility stats
        $utility_stats = $this->get_utility_stats();
        ?>
        <div class="wrap">
            <h1>🎨 DS-Studio</h1>
            <p>Transform your theme.json design tokens into utility classes.</p>
            
            <div class="ds-studio-container">
                
                <!-- Theme.json Status Card -->
                <div class="ds-status-card">
                    <h2>📄 Theme.json Status</h2>
                    
                    <?php if ($theme_json_exists && $theme_data): ?>
                        <p class="ds-success"><strong>✅ Connected</strong> - Reading design tokens from your child theme</p>
                        
                        <div class="ds-token-grid">
                            <?php
                            $tokens = array(
                                'Colors' => isset($theme_data['settings']['color']['palette']) ? count($theme_data['settings']['color']['palette']) : 0,
                                'Font Sizes' => isset($theme_data['settings']['typography']['fontSizes']) ? count($theme_data['settings']['typography']['fontSizes']) : 0,
                                'Spacing' => isset($theme_data['settings']['spacing']['spacingSizes']) ? count($theme_data['settings']['spacing']['spacingSizes']) : 0,
                                'Containers' => isset($theme_data['settings']['custom']['layout']['containers']) ? count($theme_data['settings']['custom']['layout']['containers']) : 0
                            );
                            
                            foreach ($tokens as $label => $count):
                            ?>
                                <div class="ds-token-item">
                                    <span class="ds-token-count"><?php echo $count; ?></span>
                                    <div class="ds-token-label"><?php echo $label; ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                    <?php else: ?>
                        <p class="ds-error"><strong>❌ No theme.json found</strong></p>
                        <p>Create a <code>theme.json</code> file in your child theme to get started.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Utility Generation Card -->
                <div class="ds-status-card">
                    <h2>⚡ Utility Generation</h2>
                    
                    <?php if ($utility_stats['total'] > 0): ?>
                        <p class="ds-success"><strong>✅ Generated <?php echo $utility_stats['total']; ?> utility classes</strong></p>
                        
                        <div class="ds-token-grid">
                            <?php foreach ($utility_stats['categories'] as $category => $count): ?>
                                <div class="ds-token-item">
                                    <span class="ds-token-count"><?php echo $count; ?></span>
                                    <div class="ds-token-label"><?php echo ucfirst($category); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="ds-action-buttons">
                            <button type="button" class="button button-primary" onclick="regenerateUtilities()">
                                🔄 Regenerate Utilities
                            </button>
                            <?php
                            // Get the correct CSS file URL from utility generator
                            $generator = new DS_Studio_Utility_Generator();
                            $css_url = $generator->get_css_file_url();
                            ?>
                            <a href="<?php echo esc_url($css_url); ?>" class="button" target="_blank">
                                👁️ View CSS File
                            </a>
                        </div>
                        
                    <?php else: ?>
                        <p class="ds-warning"><strong>⚠️ No utilities generated yet</strong></p>
                        <p>Click the button below to generate utility classes from your theme.json tokens.</p>
                        
                        <div class="ds-action-buttons">
                            <button type="button" class="button button-primary" onclick="regenerateUtilities()">
                                ⚡ Generate Utilities
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                
            </div>
        </div>
        
        <script>
        function regenerateUtilities() {
            const button = event.target;
            const originalText = button.textContent;
            button.disabled = true;
            button.textContent = '🔄 Generating...';
            
            fetch(ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'ds_studio_regenerate_utilities',
                    nonce: '<?php echo wp_create_nonce('ds_studio_nonce'); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.data || 'Unknown error'));
                    button.disabled = false;
                    button.textContent = originalText;
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
                button.disabled = false;
                button.textContent = originalText;
            });
        }
        </script>
        <?php
    }
    
    /**
     * Get utility generation statistics
     */
    private function get_utility_stats() {
        $stats = array(
            'total' => 0,
            'categories' => array()
        );
        
        // Check if utility generator exists
        if (class_exists('DS_Studio_Utility_Generator')) {
            $generator = new DS_Studio_Utility_Generator();
            $utilities_by_category = $generator->get_utilities_by_category();
            
            foreach ($utilities_by_category as $category => $utilities) {
                $count = count($utilities);
                $stats['categories'][$category] = $count;
                $stats['total'] += $count;
            }
        }
        
        return $stats;
    }
    
    /**
     * AJAX handler for regenerating utilities
     */
    public function ajax_regenerate_utilities() {
        // Security check
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ds_studio_nonce')) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        if (!current_user_can('edit_theme_options')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        // Regenerate utilities
        if (class_exists('DS_Studio_Utility_Generator')) {
            $generator = new DS_Studio_Utility_Generator();
            $result = $generator->regenerate_utilities();
            
            if ($result) {
                wp_send_json_success('Utilities regenerated successfully');
            } else {
                wp_send_json_error('Failed to regenerate utilities');
            }
        } else {
            wp_send_json_error('Utility generator not available');
        }
    }
}

// Initialize the brand new admin page
new DS_Studio_Admin_Page();
