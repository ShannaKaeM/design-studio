<?php
/**
 * DS-Studio Admin Page
 * 
 * Provides an admin interface to view and manage generated utility classes
 */

if (!defined('ABSPATH')) {
    exit;
}

class DS_Studio_Admin_Page {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_theme_page(
            'DS-Studio Utilities',
            'DS-Studio Utilities',
            'edit_theme_options',
            'ds-studio-utilities',
            array($this, 'admin_page_content')
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'appearance_page_ds-studio-utilities') {
            return;
        }
        
        wp_enqueue_style('ds-studio-admin', DS_STUDIO_PLUGIN_URL . 'assets/css/admin.css', array(), DS_STUDIO_VERSION);
    }
    
    /**
     * Admin page content
     */
    public function admin_page_content() {
        $css_url = get_option('ds_studio_utilities_css_url');
        $css_path = str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $css_url);
        
        ?>
        <div class="wrap">
            <h1>DS-Studio Utility Classes</h1>
            
            <div class="ds-studio-admin-content">
                <div class="ds-studio-card">
                    <h2>Auto-Generated Utility Classes</h2>
                    <p>These utility classes are automatically generated from your theme.json design tokens and are available throughout your site.</p>
                    
                    <?php if ($css_url && file_exists($css_path)): ?>
                        <div class="ds-studio-status success">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <strong>Utility classes are active!</strong>
                            <p>CSS file: <code><?php echo esc_html($css_url); ?></code></p>
                            <p>Last updated: <?php echo date('F j, Y g:i a', filemtime($css_path)); ?></p>
                        </div>
                        
                        <h3>Available Utility Categories</h3>
                        <div class="ds-studio-utility-categories">
                            <div class="utility-category">
                                <h4>🎨 Colors</h4>
                                <p>Text, background, and border color utilities</p>
                                <code>.text-primary, .bg-secondary, .border-accent</code>
                            </div>
                            
                            <div class="utility-category">
                                <h4>📏 Spacing</h4>
                                <p>Margin, padding, and gap utilities</p>
                                <code>.m-lg, .p-sm, .gap-md, .mx-auto</code>
                            </div>
                            
                            <div class="utility-category">
                                <h4>✍️ Typography</h4>
                                <p>Font size, family, weight, and spacing utilities</p>
                                <code>.text-xl, .font-heading, .font-bold, .leading-relaxed</code>
                            </div>
                            
                            <div class="utility-category">
                                <h4>🔲 Borders</h4>
                                <p>Border width, style, and radius utilities</p>
                                <code>.border-thin, .border-solid, .rounded-lg</code>
                            </div>
                            
                            <div class="utility-category">
                                <h4>📐 Layout</h4>
                                <p>Container, aspect ratio, z-index, and grid utilities</p>
                                <code>.container-prose, .aspect-16-9, .z-modal, .grid-3-col</code>
                            </div>
                            
                            <div class="utility-category">
                                <h4>🌟 Shadows</h4>
                                <p>Box shadow utilities</p>
                                <code>.shadow-sm, .shadow-lg, .shadow-glow</code>
                            </div>
                            
                            <div class="utility-category">
                                <h4>🔧 Common</h4>
                                <p>Display, position, and flexbox utilities</p>
                                <code>.flex, .grid, .relative, .text-center, .justify-between</code>
                            </div>
                        </div>
                        
                        <div class="ds-studio-actions">
                            <a href="<?php echo esc_url($css_url); ?>" target="_blank" class="button button-secondary">
                                <span class="dashicons dashicons-visibility"></span>
                                View Generated CSS
                            </a>
                            
                            <button type="button" class="button button-primary" onclick="regenerateUtilities()">
                                <span class="dashicons dashicons-update"></span>
                                Regenerate Utilities
                            </button>
                        </div>
                        
                    <?php else: ?>
                        <div class="ds-studio-status error">
                            <span class="dashicons dashicons-warning"></span>
                            <strong>Utility classes not found!</strong>
                            <p>Click the button below to generate utility classes from your theme.json tokens.</p>
                        </div>
                        
                        <div class="ds-studio-actions">
                            <button type="button" class="button button-primary" onclick="regenerateUtilities()">
                                <span class="dashicons dashicons-plus-alt"></span>
                                Generate Utility Classes
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="ds-studio-card">
                    <h2>Usage Examples</h2>
                    <p>Here are some examples of how to use the generated utility classes in your templates:</p>
                    
                    <div class="code-example">
                        <h4>Basic Layout</h4>
                        <pre><code>&lt;div class="container-prose mx-auto p-lg"&gt;
    &lt;h1 class="text-3xl font-heading font-bold mb-md"&gt;Page Title&lt;/h1&gt;
    &lt;p class="text-base leading-relaxed text-gray-700"&gt;Content text...&lt;/p&gt;
&lt;/div&gt;</code></pre>
                    </div>
                    
                    <div class="code-example">
                        <h4>Card Component</h4>
                        <pre><code>&lt;div class="bg-white rounded-lg shadow-md p-lg border border-gray-200"&gt;
    &lt;img class="w-full aspect-16-9 rounded-md mb-md" src="image.jpg" alt="Card image"&gt;
    &lt;h3 class="text-xl font-semibold mb-sm"&gt;Card Title&lt;/h3&gt;
    &lt;p class="text-gray-600 mb-md"&gt;Card description...&lt;/p&gt;
    &lt;button class="bg-primary text-white px-md py-sm rounded-md"&gt;Learn More&lt;/button&gt;
&lt;/div&gt;</code></pre>
                    </div>
                    
                    <div class="code-example">
                        <h4>Flexbox Layout</h4>
                        <pre><code>&lt;div class="flex justify-between items-center gap-md p-md bg-gray-50 rounded-lg"&gt;
    &lt;div class="flex items-center gap-sm"&gt;
        &lt;span class="text-lg font-medium"&gt;Navigation Item&lt;/span&gt;
    &lt;/div&gt;
    &lt;button class="bg-accent text-white px-sm py-xs rounded-sm"&gt;Action&lt;/button&gt;
&lt;/div&gt;</code></pre>
                    </div>
                </div>
                
                <!-- Production Optimization Section -->
                <div class="postbox">
                    <h2 class="hndle"><span>🚀 Production Optimization</span></h2>
                    <div class="inside">
                        <p>Optimize your site's performance by only including the utility classes you actually use.</p>
                        
                        <?php
                        $purger = new DS_Studio_Utility_Purger();
                        $stats = $purger->get_purge_stats();
                        ?>
                        
                        <div class="utility-optimization">
                            <div class="optimization-stats">
                                <div class="stat-card">
                                    <h4>Current Mode</h4>
                                    <p class="stat-value <?php echo $stats['using_purged'] ? 'purged' : 'full'; ?>">
                                        <?php echo $stats['using_purged'] ? 'Purged CSS' : 'Full CSS'; ?>
                                    </p>
                                </div>
                                
                                <?php if ($stats['full_css_exists']): ?>
                                <div class="stat-card">
                                    <h4>Full CSS Size</h4>
                                    <p class="stat-value"><?php echo $this->format_file_size($stats['full_css_size']); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($stats['purged_css_exists']): ?>
                                <div class="stat-card">
                                    <h4>Purged CSS Size</h4>
                                    <p class="stat-value"><?php echo $this->format_file_size($stats['purged_css_size']); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (isset($stats['size_reduction'])): ?>
                                <div class="stat-card">
                                    <h4>Size Reduction</h4>
                                    <p class="stat-value reduction"><?php echo $stats['size_reduction']; ?>%</p>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="optimization-actions">
                                <button type="button" class="button button-secondary" onclick="scanUtilities()">
                                    <span class="dashicons dashicons-search"></span> Scan Used Utilities
                                </button>
                                
                                <button type="button" class="button button-primary" onclick="purgeUtilities()">
                                    <span class="dashicons dashicons-performance"></span> Generate Purged CSS
                                </button>
                                
                                <?php if ($stats['using_purged']): ?>
                                <button type="button" class="button button-secondary" onclick="useFullCSS()">
                                    <span class="dashicons dashicons-undo"></span> Switch to Full CSS
                                </button>
                                <?php endif; ?>
                            </div>
                            
                            <div id="scan-results" class="scan-results" style="display: none;">
                                <h4>Scan Results</h4>
                                <div id="scan-output"></div>
                            </div>
                            
                            <div class="optimization-info">
                                <h4>How It Works</h4>
                                <ul>
                                    <li><strong>Scan:</strong> Analyzes your theme files, posts, pages, and widgets to find which utility classes are actually used</li>
                                    <li><strong>Purge:</strong> Generates a new CSS file containing only the utilities you're using</li>
                                    <li><strong>Optimize:</strong> Can reduce CSS file size by 60-90% depending on usage</li>
                                    <li><strong>Safe:</strong> You can always switch back to full CSS if needed</li>
                                </ul>
                                
                                <div class="notice notice-info inline">
                                    <p><strong>Recommendation:</strong> Use full CSS during development, then purge for production to get the best performance.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .utility-optimization {
            max-width: 100%;
        }
        
        .optimization-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            border: 1px solid #e1e5e9;
        }
        
        .stat-card h4 {
            margin: 0 0 8px 0;
            font-size: 12px;
            text-transform: uppercase;
            color: #666;
            font-weight: 600;
        }
        
        .stat-value {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        
        .stat-value.purged {
            color: #00a32a;
        }
        
        .stat-value.full {
            color: #0073aa;
        }
        
        .stat-value.reduction {
            color: #00a32a;
        }
        
        .optimization-actions {
            margin-bottom: 20px;
        }
        
        .optimization-actions .button {
            margin-right: 10px;
            margin-bottom: 5px;
        }
        
        .scan-results {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
            border: 1px solid #e1e5e9;
        }
        
        .scan-results h4 {
            margin-top: 0;
        }
        
        .optimization-info {
            margin-top: 20px;
        }
        
        .optimization-info ul {
            margin: 10px 0;
        }
        
        .optimization-info li {
            margin-bottom: 5px;
        }
        
        .dashicons.spin {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        </style>
        
        <script>
        function scanUtilities() {
            const button = event.target;
            const originalText = button.innerHTML;
            const resultsDiv = document.getElementById('scan-results');
            const outputDiv = document.getElementById('scan-output');
            
            button.innerHTML = '<span class="dashicons dashicons-update spin"></span> Scanning...';
            button.disabled = true;
            
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'ds_studio_scan_utilities',
                    nonce: '<?php echo wp_create_nonce('ds_studio_utilities_nonce'); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultsDiv.style.display = 'block';
                    outputDiv.innerHTML = `
                        <p><strong>${data.data.message}</strong></p>
                        <details>
                            <summary>View Used Utilities (${data.data.count} total)</summary>
                            <div style="max-height: 200px; overflow-y: auto; background: white; padding: 10px; margin-top: 10px; border: 1px solid #ddd;">
                                ${data.data.used_utilities.map(utility => `<span style="display: inline-block; background: #e1f5fe; padding: 2px 6px; margin: 2px; border-radius: 3px; font-size: 11px;">${utility}</span>`).join('')}
                            </div>
                        </details>
                    `;
                } else {
                    alert('Error: ' + data.data);
                }
                button.innerHTML = originalText;
                button.disabled = false;
            })
            .catch(error => {
                alert('Error: ' + error);
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }
        
        function purgeUtilities() {
            const button = event.target;
            const originalText = button.innerHTML;
            
            button.innerHTML = '<span class="dashicons dashicons-update spin"></span> Purging...';
            button.disabled = true;
            
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'ds_studio_purge_utilities',
                    nonce: '<?php echo wp_create_nonce('ds_studio_utilities_nonce'); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Success! Generated purged CSS with ${data.data.utilities_count} utilities. File size: ${data.data.file_size}`);
                    location.reload();
                } else {
                    alert('Error: ' + data.data);
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                alert('Error: ' + error);
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }
        
        function useFullCSS() {
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'ds_studio_use_full_css',
                    nonce: '<?php echo wp_create_nonce('ds_studio_utilities_nonce'); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.data);
                }
            });
        }
        
        function regenerateUtilities() {
            const button = event.target;
            const originalText = button.innerHTML;
            
            button.innerHTML = '<span class="dashicons dashicons-update spin"></span> Generating...';
            button.disabled = true;
            
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'ds_studio_regenerate_utilities',
                    nonce: '<?php echo wp_create_nonce('ds_studio_utilities_nonce'); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.data);
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }
        </script>
        
        <style>
        .ds-studio-admin-content {
            display: grid;
            gap: 20px;
            max-width: 1200px;
        }
        
        .ds-studio-card {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
        }
        
        .ds-studio-status {
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        
        .ds-studio-status.success {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        
        .ds-studio-status.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .ds-studio-utility-categories {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        
        .utility-category {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
            border-left: 4px solid #007cba;
        }
        
        .utility-category h4 {
            margin: 0 0 8px 0;
            color: #1d2327;
        }
        
        .utility-category p {
            margin: 0 0 8px 0;
            color: #646970;
            font-size: 14px;
        }
        
        .utility-category code {
            background: #fff;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            color: #d63384;
        }
        
        .ds-studio-actions {
            margin-top: 20px;
        }
        
        .ds-studio-actions .button {
            margin-right: 10px;
        }
        
        .code-example {
            margin: 15px 0;
            padding: 15px;
            background: #f6f7f7;
            border-radius: 4px;
        }
        
        .code-example h4 {
            margin-top: 0;
        }
        
        .code-example pre {
            margin: 0;
            background: #1d2327;
            color: #f0f0f1;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }
        
        .spin {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        </style>
        <?php
    }
    
    /**
     * Format file size for display
     */
    private function format_file_size($bytes) {
        if ($bytes >= 1024 * 1024) {
            return round($bytes / (1024 * 1024), 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}

// Initialize admin page
new DS_Studio_Admin_Page();
