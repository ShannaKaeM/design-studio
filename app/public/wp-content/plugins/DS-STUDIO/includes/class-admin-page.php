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
                            <?php
                            $utility_generator = new DS_Studio_Utility_Generator();
                            $utilities_by_category = $utility_generator->get_utilities_by_category();
                            ?>
                            
                            <div class="utility-category">
                                <h5>🎨 Colors</h5>
                                <div class="utility-group">
                                    <h6>Background Colors</h6>
                                    <?php
                                    // Get background color utilities
                                    $bg_utilities = array_filter($utilities_by_category['colors'], function($utility) {
                                        return strpos($utility, 'bg-') === 0;
                                    });
                                    foreach ($bg_utilities as $utility): ?>
                                        <label class="utility-option">
                                            <input type="checkbox" name="utilities[]" value="<?php echo esc_attr($utility); ?>">
                                            <span class="utility-name"><?php echo esc_html($utility); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="utility-group">
                                    <h6>Text Colors</h6>
                                    <?php
                                    // Get text color utilities
                                    $text_utilities = array_filter($utilities_by_category['colors'], function($utility) {
                                        return strpos($utility, 'text-') === 0 && strpos($utility, 'text-xs') !== 0 && strpos($utility, 'text-sm') !== 0 && strpos($utility, 'text-lg') !== 0;
                                    });
                                    foreach ($text_utilities as $utility): ?>
                                        <label class="utility-option">
                                            <input type="checkbox" name="utilities[]" value="<?php echo esc_attr($utility); ?>">
                                            <span class="utility-name"><?php echo esc_html($utility); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="utility-category">
                                <h5>📏 Spacing</h5>
                                <div class="utility-group">
                                    <h6>Padding</h6>
                                    <?php
                                    // Get padding utilities
                                    $padding_utilities = array_filter($utilities_by_category['spacing'], function($utility) {
                                        return strpos($utility, 'p-') === 0 || strpos($utility, 'px-') === 0 || strpos($utility, 'py-') === 0 || strpos($utility, 'pt-') === 0 || strpos($utility, 'pr-') === 0 || strpos($utility, 'pb-') === 0 || strpos($utility, 'pl-') === 0;
                                    });
                                    foreach (array_slice($padding_utilities, 0, 10) as $utility): ?>
                                        <label class="utility-option">
                                            <input type="checkbox" name="utilities[]" value="<?php echo esc_attr($utility); ?>">
                                            <span class="utility-name"><?php echo esc_html($utility); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="utility-group">
                                    <h6>Margin</h6>
                                    <?php
                                    // Get margin utilities
                                    $margin_utilities = array_filter($utilities_by_category['spacing'], function($utility) {
                                        return strpos($utility, 'm-') === 0 || strpos($utility, 'mx-') === 0 || strpos($utility, 'my-') === 0 || strpos($utility, 'mt-') === 0 || strpos($utility, 'mr-') === 0 || strpos($utility, 'mb-') === 0 || strpos($utility, 'ml-') === 0;
                                    });
                                    foreach (array_slice($margin_utilities, 0, 10) as $utility): ?>
                                        <label class="utility-option">
                                            <input type="checkbox" name="utilities[]" value="<?php echo esc_attr($utility); ?>">
                                            <span class="utility-name"><?php echo esc_html($utility); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="utility-category">
                                <h5>📝 Typography</h5>
                                <div class="utility-group">
                                    <h6>Font Sizes</h6>
                                    <?php
                                    // Get font size utilities
                                    $font_size_utilities = array_filter($utilities_by_category['typography'], function($utility) {
                                        return strpos($utility, 'text-') === 0 && (strpos($utility, 'text-xs') === 0 || strpos($utility, 'text-sm') === 0 || strpos($utility, 'text-lg') === 0 || strpos($utility, 'text-xl') === 0 || strpos($utility, 'text-2xl') === 0);
                                    });
                                    foreach ($font_size_utilities as $utility): ?>
                                        <label class="utility-option">
                                            <input type="checkbox" name="utilities[]" value="<?php echo esc_attr($utility); ?>">
                                            <span class="utility-name"><?php echo esc_html($utility); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="utility-group">
                                    <h6>Font Weights</h6>
                                    <?php
                                    // Get font weight utilities
                                    $font_weight_utilities = array_filter($utilities_by_category['typography'], function($utility) {
                                        return strpos($utility, 'font-') === 0;
                                    });
                                    foreach ($font_weight_utilities as $utility): ?>
                                        <label class="utility-option">
                                            <input type="checkbox" name="utilities[]" value="<?php echo esc_attr($utility); ?>">
                                            <span class="utility-name"><?php echo esc_html($utility); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="utility-category">
                                <h5>🏗️ Layout</h5>
                                <div class="utility-group">
                                    <h6>Display & Flexbox</h6>
                                    <?php
                                    // Get layout utilities
                                    foreach (array_slice($utilities_by_category['layout'], 0, 15) as $utility): ?>
                                        <label class="utility-option">
                                            <input type="checkbox" name="utilities[]" value="<?php echo esc_attr($utility); ?>">
                                            <span class="utility-name"><?php echo esc_html($utility); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="utility-category">
                                <h5>🔲 Borders</h5>
                                <div class="utility-group">
                                    <h6>Border Radius & Styles</h6>
                                    <?php
                                    // Get border utilities
                                    foreach (array_slice($utilities_by_category['borders'], 0, 15) as $utility): ?>
                                        <label class="utility-option">
                                            <input type="checkbox" name="utilities[]" value="<?php echo esc_attr($utility); ?>">
                                            <span class="utility-name"><?php echo esc_html($utility); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="utility-category">
                                <h5>✨ Effects</h5>
                                <div class="utility-group">
                                    <h6>Shadows & Opacity</h6>
                                    <?php
                                    // Get effects utilities
                                    foreach (array_slice($utilities_by_category['effects'], 0, 10) as $utility): ?>
                                        <label class="utility-option">
                                            <input type="checkbox" name="utilities[]" value="<?php echo esc_attr($utility); ?>">
                                            <span class="utility-name"><?php echo esc_html($utility); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="utility-category">
                                <h5>📱 Responsive</h5>
                                <div class="utility-group">
                                    <h6>Medium+ Breakpoints (768px+)</h6>
                                    <?php
                                    // Get responsive utilities
                                    foreach (array_slice($utilities_by_category['responsive'], 0, 15) as $utility): ?>
                                        <label class="utility-option">
                                            <input type="checkbox" name="utilities[]" value="<?php echo esc_attr($utility); ?>">
                                            <span class="utility-name"><?php echo esc_html($utility); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="utility-category">
                                <h5>🌊 Fluid</h5>
                                <div class="utility-group">
                                    <h6>Clamp-based Responsive</h6>
                                    <?php
                                    // Get fluid utilities
                                    foreach (array_slice($utilities_by_category['fluid'], 0, 15) as $utility): ?>
                                        <label class="utility-option">
                                            <input type="checkbox" name="utilities[]" value="<?php echo esc_attr($utility); ?>">
                                            <span class="utility-name"><?php echo esc_html($utility); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
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
                
                <!-- Component Builder Section -->
                <div class="postbox">
                    <h2 class="hndle"><span>🎨 Component Builder</span></h2>
                    <div class="inside">
                        <p>Create reusable component patterns by combining utility classes into named components.</p>
                        
                        <div class="component-builder">
                            <!-- Component Creation Form -->
                            <div class="component-form-section">
                                <h3>Create New Component</h3>
                                
                                <div class="component-form">
                                    <div class="form-row">
                                        <label for="component-name">Component Name:</label>
                                        <input type="text" id="component-name" placeholder="e.g., my-custom-button" />
                                        <small>Use lowercase with hyphens (slug format)</small>
                                    </div>
                                    
                                    <div class="form-row">
                                        <label for="component-description">Description:</label>
                                        <input type="text" id="component-description" placeholder="Brief description of this component" />
                                    </div>
                                    
                                    <div class="utility-selector">
                                        <h4>Select Utility Classes:</h4>
                                        <div class="utility-categories">
                                            <?php
                                            $utility_generator = new DS_Studio_Utility_Generator();
                                            $utilities_by_category = $utility_generator->get_utilities_by_category();
                                            
                                            foreach ($utilities_by_category as $category => $utilities): ?>
                                                <div class="utility-category">
                                                    <h5><?php echo $category; ?></h5>
                                                    <?php foreach ($utilities as $utility): ?>
                                                        <label class="utility-option">
                                                            <input type="checkbox" value="<?php echo esc_attr($utility); ?>" />
                                                            <span class="utility-label"><?php echo esc_html($utility); ?></span>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endforeach; ?>
                                            
                                            <div class="utility-category">
                                                <h5>📱 Responsive</h5>
                                                <div class="utility-group">
                                                    <h6>Medium+ Breakpoints (768px+)</h6>
                                                    <?php
                                                    // Get responsive utilities
                                                    foreach (array_slice($utilities_by_category['responsive'], 0, 15) as $utility): ?>
                                                        <label class="utility-option">
                                                            <input type="checkbox" value="<?php echo esc_attr($utility); ?>" />
                                                            <span class="utility-label"><?php echo esc_html($utility); ?></span>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="utility-category">
                                                <h5>🌊 Fluid</h5>
                                                <div class="utility-group">
                                                    <h6>Clamp-based Responsive</h6>
                                                    <?php
                                                    // Get fluid utilities
                                                    foreach (array_slice($utilities_by_category['fluid'], 0, 15) as $utility): ?>
                                                        <label class="utility-option">
                                                            <input type="checkbox" value="<?php echo esc_attr($utility); ?>" />
                                                            <span class="utility-label"><?php echo esc_html($utility); ?></span>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="component-preview">
                                        <h4>Live Preview:</h4>
                                        <div class="preview-container">
                                            <div id="component-preview-element" class="preview-element">
                                                Component Preview
                                            </div>
                                            <div class="preview-code">
                                                <strong>Classes:</strong> <span id="preview-classes">No classes selected</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="button" class="button button-primary" onclick="saveComponent()">
                                            <span class="dashicons dashicons-saved"></span> Save Component
                                        </button>
                                        <button type="button" class="button button-secondary" onclick="clearComponentForm()">
                                            <span class="dashicons dashicons-dismiss"></span> Clear
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Existing Components Management -->
                            <div class="existing-components-section">
                                <h3>Manage Existing Components</h3>
                                
                                <div class="components-list">
                                    <?php
                                    $component_library = new DS_Studio_Component_Library();
                                    $component_library->init(); // Initialize and load components
                                    $components = $component_library->get_components();
                                    
                                    if (empty($components)): ?>
                                        <p class="no-components">No custom components created yet. Create your first component above!</p>
                                    <?php else: ?>
                                        <div class="components-grid">
                                            <?php foreach ($components as $slug => $component): ?>
                                                <div class="component-card" data-slug="<?php echo esc_attr($slug); ?>">
                                                    <div class="component-header">
                                                        <h4><?php echo esc_html($component['name'] ?? $slug); ?></h4>
                                                        <div class="component-actions">
                                                            <button type="button" class="button-link" onclick="editComponent('<?php echo esc_attr($slug); ?>')">
                                                                <span class="dashicons dashicons-edit"></span>
                                                            </button>
                                                            <button type="button" class="button-link delete" onclick="deleteComponent('<?php echo esc_attr($slug); ?>')">
                                                                <span class="dashicons dashicons-trash"></span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if (!empty($component['description'])): ?>
                                                        <p class="component-description"><?php echo esc_html($component['description']); ?></p>
                                                    <?php endif; ?>
                                                    
                                                    <div class="component-classes">
                                                        <strong>Classes:</strong>
                                                        <code><?php echo esc_html($component['classes']); ?></code>
                                                    </div>
                                                    
                                                    <div class="component-usage">
                                                        <strong>Usage:</strong>
                                                        <code>&lt;div class="<?php echo esc_attr($slug); ?>"&gt;...&lt;/div&gt;</code>
                                                    </div>
                                                    
                                                    <div class="component-preview-mini">
                                                        <div class="<?php echo esc_attr($component['classes']); ?>" style="padding: 8px; margin: 4px 0; min-height: 20px; background: #f9f9f9; border: 1px dashed #ccc;">
                                                            Preview
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
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
            gap: 20px;
            margin: 20px 0;
        }
        
        .utility-category {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
        }
        
        .utility-category h5 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 16px;
            font-weight: 600;
        }
        
        .utility-group {
            margin-bottom: 15px;
        }
        
        .utility-group h6 {
            margin: 0 0 8px 0;
            color: #666;
            font-size: 13px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .utility-option {
            display: inline-block;
            margin: 2px 4px 2px 0;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .utility-option:hover {
            background: #f0f0f1;
            border-color: #0073aa;
        }
        
        .utility-option input[type="checkbox"] {
            margin-right: 6px;
            transform: scale(0.9);
        }
        
        .utility-option input[type="checkbox"]:checked + .utility-name {
            color: #0073aa;
            font-weight: 500;
        }
        
        .utility-name {
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 11px;
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
        
        <style>
        .component-builder {
            max-width: 100%;
        }
        
        .component-form-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid #e1e5e9;
        }
        
        .component-form .form-row {
            margin-bottom: 20px;
        }
        
        .component-form label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }
        
        .component-form input[type="text"] {
            width: 100%;
            max-width: 400px;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .component-form small {
            display: block;
            margin-top: 4px;
            color: #666;
            font-size: 12px;
        }
        
        .utility-selector {
            margin: 25px 0;
        }
        
        .utility-categories {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        
        .utility-category {
            background: white;
            border: 1px solid #e1e5e9;
            border-radius: 6px;
            padding: 15px;
        }
        
        .utility-category h5 {
            margin: 0 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #007cba;
            color: #007cba;
            font-size: 14px;
            font-weight: 600;
        }
        
        .utility-subcategory {
            margin-bottom: 15px;
        }
        
        .utility-subcategory h6 {
            margin: 0 0 8px 0;
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            font-weight: 600;
        }
        
        .utility-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 6px;
        }
        
        .utility-option {
            display: flex;
            align-items: center;
            padding: 4px 8px;
            border: 1px solid #e1e5e9;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #fafafa;
        }
        
        .utility-option:hover {
            background: #f0f0f0;
            border-color: #0073aa;
        }
        
        .utility-option input[type="checkbox"] {
            margin: 0 6px 0 0;
        }
        
        .utility-option.selected {
            background: #e1f5fe;
            border-color: #0073aa;
            color: #0073aa;
        }
        
        .utility-label {
            font-size: 11px;
            font-family: monospace;
        }
        
        .component-preview {
            margin: 25px 0;
            padding: 20px;
            background: white;
            border: 1px solid #e1e5e9;
            border-radius: 6px;
        }
        
        .preview-container {
            margin-top: 10px;
        }
        
        .preview-element {
            padding: 15px;
            margin: 10px 0;
            background: #f9f9f9;
            border: 2px dashed #ccc;
            border-radius: 4px;
            min-height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .preview-code {
            margin-top: 10px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
        }
        
        .form-actions {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e1e5e9;
        }
        
        .form-actions .button {
            margin-right: 10px;
        }
        
        .existing-components-section h3 {
            margin-bottom: 20px;
            color: #333;
        }
        
        .components-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .component-card {
            background: white;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            padding: 20px;
            transition: box-shadow 0.2s ease;
        }
        
        .component-card:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .component-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .component-header h4 {
            margin: 0;
            color: #333;
        }
        
        .component-actions {
            display: flex;
            gap: 5px;
        }
        
        .component-actions .button-link {
            padding: 4px;
            border: none;
            background: none;
            cursor: pointer;
            border-radius: 3px;
            transition: background 0.2s ease;
        }
        
        .component-actions .button-link:hover {
            background: #f0f0f0;
        }
        
        .component-actions .button-link.delete:hover {
            background: #ffebee;
            color: #d32f2f;
        }
        
        .component-description {
            margin: 8px 0;
            color: #666;
            font-style: italic;
        }
        
        .component-classes,
        .component-usage {
            margin: 8px 0;
            font-size: 12px;
        }
        
        .component-classes code,
        .component-usage code {
            background: #f5f5f5;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 11px;
        }
        
        .component-preview-mini {
            margin-top: 10px;
        }
        
        .no-components {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 40px 20px;
        }
        </style>
        
        <script>
        // Component Builder JavaScript
        let selectedUtilities = [];
        
        // Initialize component builder
        document.addEventListener('DOMContentLoaded', function() {
            initializeUtilitySelector();
        });
        
        function initializeUtilitySelector() {
            const checkboxes = document.querySelectorAll('.utility-option input[type="checkbox"]');
            
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const label = this.closest('.utility-option');
                    
                    if (this.checked) {
                        label.classList.add('selected');
                        selectedUtilities.push(this.value);
                    } else {
                        label.classList.remove('selected');
                        selectedUtilities = selectedUtilities.filter(u => u !== this.value);
                    }
                    
                    updatePreview();
                });
            });
        }
        
        function updatePreview() {
            const previewElement = document.getElementById('component-preview-element');
            const previewClasses = document.getElementById('preview-classes');
            
            // Update classes
            previewElement.className = 'preview-element ' + selectedUtilities.join(' ');
            
            // Update classes display
            if (selectedUtilities.length > 0) {
                previewClasses.textContent = selectedUtilities.join(' ');
            } else {
                previewClasses.textContent = 'No classes selected';
            }
        }
        
        function saveComponent() {
            const name = document.getElementById('component-name').value.trim();
            const description = document.getElementById('component-description').value.trim();
            
            if (!name) {
                alert('Please enter a component name');
                return;
            }
            
            if (selectedUtilities.length === 0) {
                alert('Please select at least one utility class');
                return;
            }
            
            const componentData = {
                name: name,
                description: description,
                classes: selectedUtilities.join(' ')
            };
            
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'ds_studio_save_component',
                    nonce: '<?php echo wp_create_nonce('ds_studio_utilities_nonce'); ?>',
                    component_slug: name,
                    component_data: JSON.stringify(componentData)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Component saved successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.data);
                }
            })
            .catch(error => {
                alert('Error: ' + error);
            });
        }
        
        function clearComponentForm() {
            document.getElementById('component-name').value = '';
            document.getElementById('component-description').value = '';
            
            // Uncheck all utilities
            const checkboxes = document.querySelectorAll('.utility-option input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
                checkbox.closest('.utility-option').classList.remove('selected');
            });
            
            selectedUtilities = [];
            updatePreview();
        }
        
        function editComponent(slug) {
            // TO DO: Implement component editing
            alert('Edit component: ' + slug + ' (Coming soon!)');
        }
        
        function deleteComponent(slug) {
            if (!confirm('Are you sure you want to delete this component?')) {
                return;
            }
            
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'ds_studio_delete_component',
                    nonce: '<?php echo wp_create_nonce('ds_studio_utilities_nonce'); ?>',
                    component_slug: slug
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.data);
                }
            })
            .catch(error => {
                alert('Error: ' + error);
            });
        }
        </script>
        
        <style>

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
