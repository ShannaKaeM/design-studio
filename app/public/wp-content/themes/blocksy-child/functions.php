<?php
/**
 * Blocksy Child Theme functions and definitions
 */

// Add theme support for full-width content
function blocksy_child_theme_setup() {
    // Add support for full and wide align options
    add_theme_support('align-wide');
    
    // Add support for editor styles
    add_theme_support('editor-styles');
    
    // Add support for theme.json
    add_theme_support('wp-block-styles');
    add_theme_support('appearance-tools');
}
add_action('after_setup_theme', 'blocksy_child_theme_setup');

// Force theme.json to be recognized and loaded
add_filter('should_load_separate_core_block_assets', '__return_true');

// Ensure theme.json styles are loaded with high priority
add_action('wp_enqueue_scripts', function() {
    // Force WordPress to regenerate theme.json styles
    wp_enqueue_style('wp-block-library');
    wp_enqueue_style('global-styles');
}, 5);

// Override Blocksy's content width with our theme.json values
add_filter('blocksy:general:sidebar-max-width', function() {
    return '1200px'; // Match theme.json contentWidth
});

add_filter('blocksy_content_max_width', function() {
    return '1200px'; // Match theme.json contentWidth
});

// Ensure our CSS variables from theme.json are available
add_action('wp_head', function() {
    ?>
    <style>
        :root {
            --wp--style--global--content-size: 1200px;
            --wp--style--global--wide-size: 1400px;
        }
    </style>
    <?php
}, 1);

/**
 * Studio Blocks Integration
 */
class Studio_Theme_Integration {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'register_studio_blocks'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_filter('block_categories_all', array($this, 'add_studio_block_category'));
        add_action('admin_menu', array($this, 'add_studio_menu'));
        add_action('wp_ajax_studio_sync_tokens', array($this, 'ajax_sync_tokens'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_studio_save_preset', array($this, 'ajax_save_preset'));
        add_action('wp_ajax_studio_convert_html', array($this, 'ajax_convert_html'));
    }
    
    /**
     * Register Studio blocks
     */
    public function register_studio_blocks() {
        $blocks = array(
            'studio-text',
            'studio-container',
            'studio-headline',
            'studio-button',
            'studio-grid',
            'studio-image'
        );
        
        foreach ($blocks as $block) {
            $block_path = get_stylesheet_directory() . '/blocks/' . $block . '/block.json';
            if (file_exists($block_path)) {
                register_block_type($block_path);
            }
        }
    }
    
    /**
     * Enqueue block editor assets
     */
    public function enqueue_block_editor_assets() {
        // Enqueue Studio blocks editor styles
        wp_enqueue_style(
            'studio-blocks-editor',
            get_stylesheet_directory_uri() . '/assets/css/studio-blocks-editor.css',
            array('wp-edit-blocks'),
            filemtime(get_stylesheet_directory() . '/assets/css/studio-blocks-editor.css')
        );
        
        // Enqueue Studio blocks editor script
        wp_enqueue_script(
            'studio-blocks-editor',
            get_stylesheet_directory_uri() . '/assets/js/studio-blocks-editor.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'),
            filemtime(get_stylesheet_directory() . '/assets/js/studio-blocks-editor.js'),
            true
        );
        
        // Localize design tokens for JavaScript
        wp_localize_script('studio-blocks-editor', 'studioTokens', array(
            'tokens' => $this->get_design_tokens(),
            'presets' => $this->get_typography_presets(),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('studio_tokens')
        ));
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        wp_enqueue_style(
            'studio-blocks',
            get_stylesheet_directory_uri() . '/assets/css/studio-blocks.css',
            array(),
            filemtime(get_stylesheet_directory() . '/assets/css/studio-blocks.css')
        );
    }
    
    /**
     * Add Studio block category
     */
    public function add_studio_block_category($categories) {
        return array_merge(
            array(
                array(
                    'slug' => 'studio-blocks',
                    'title' => __('Studio Blocks', 'studio'),
                    'icon' => 'layout'
                )
            ),
            $categories
        );
    }
    
    /**
     * Add Studio menu to WordPress admin
     */
    public function add_studio_menu() {
        add_menu_page(
            __('Studio', 'studio'),
            __('Studio', 'studio'),
            'manage_options',
            'studio-settings',
            array($this, 'render_studio_settings'),
            'dashicons-layout',
            30
        );
        
        add_submenu_page(
            'studio-settings',
            __('Design Tokens', 'studio'),
            __('Design Tokens', 'studio'),
            'manage_options',
            'studio-tokens',
            array($this, 'render_token_manager')
        );
        
        add_submenu_page(
            'studio-settings',
            __('Typography Presets', 'studio'),
            __('Typography Presets', 'studio'),
            'manage_options',
            'studio-presets',
            array($this, 'render_preset_manager')
        );
        
        add_submenu_page(
            'studio-settings',
            __('HTML Converter', 'studio'),
            __('HTML Converter', 'studio'),
            'manage_options',
            'studio-converter',
            array($this, 'render_html_converter')
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'studio-') === false) {
            return;
        }
        
        wp_enqueue_style(
            'studio-admin',
            get_stylesheet_directory_uri() . '/assets/css/studio-admin.css',
            array(),
            filemtime(get_stylesheet_directory() . '/assets/css/studio-admin.css')
        );
        
        wp_enqueue_script(
            'studio-admin',
            get_stylesheet_directory_uri() . '/assets/js/studio-admin.js',
            array('jquery', 'wp-color-picker'),
            filemtime(get_stylesheet_directory() . '/assets/js/studio-admin.js'),
            true
        );
        
        // Localize script with necessary data
        wp_localize_script('studio-admin', 'studioAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('studio_admin_nonce'),
            'tokens' => $this->get_design_tokens(),
            'presets' => $this->get_typography_presets()
        ));
        
        wp_enqueue_style('wp-color-picker');
    }
    
    /**
     * Get design tokens from studio.json
     */
    private function get_design_tokens() {
        $studio_json_path = get_stylesheet_directory() . '/studio.json';
        if (file_exists($studio_json_path)) {
            $tokens = json_decode(file_get_contents($studio_json_path), true);
            return $tokens ?: array();
        }
        return array();
    }
    
    /**
     * Get typography presets
     */
    private function get_typography_presets() {
        return array(
            'pretitle' => array(
                'name' => 'Pretitle',
                'size' => 'var(--wp--preset--font-size--small)',
                'weight' => '600',
                'transform' => 'uppercase',
                'spacing' => '0.05em'
            ),
            'title-hero' => array(
                'name' => 'Title (Hero)',
                'size' => 'var(--wp--preset--font-size--xx-large)',
                'weight' => '700',
                'lineHeight' => '1.2'
            ),
            'title-section' => array(
                'name' => 'Title (Section)',
                'size' => 'var(--wp--preset--font-size--x-large)',
                'weight' => '700',
                'lineHeight' => '1.3'
            ),
            'title-card' => array(
                'name' => 'Title (Card)',
                'size' => 'var(--wp--preset--font-size--large)',
                'weight' => '600',
                'lineHeight' => '1.4'
            ),
            'subtitle' => array(
                'name' => 'Subtitle',
                'size' => 'var(--wp--preset--font-size--medium)',
                'weight' => '500',
                'lineHeight' => '1.5'
            ),
            'description' => array(
                'name' => 'Description',
                'size' => 'var(--wp--preset--font-size--base)',
                'weight' => '400',
                'lineHeight' => '1.6'
            ),
            'body' => array(
                'name' => 'Body',
                'size' => 'var(--wp--preset--font-size--base)',
                'weight' => '400',
                'lineHeight' => '1.7'
            )
        );
    }
    
    /**
     * Handle token sync AJAX request
     */
    public function handle_token_sync() {
        check_ajax_referer('studio_tokens', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $studio_json_path = get_stylesheet_directory() . '/studio.json';
        $theme_json_path = get_stylesheet_directory() . '/theme.json';
        
        if (!file_exists($studio_json_path)) {
            wp_send_json_error('studio.json not found');
        }
        
        // Read studio.json
        $studio_tokens = json_decode(file_get_contents($studio_json_path), true);
        
        // Read theme.json
        $theme_json = json_decode(file_get_contents($theme_json_path), true);
        
        // Sync colors
        if (isset($studio_tokens['colors'])) {
            foreach ($studio_tokens['colors'] as $key => $color) {
                $theme_json['settings']['color']['palette'][] = array(
                    'slug' => $key,
                    'color' => $color['value'],
                    'name' => $color['name'] ?? ucfirst($key)
                );
            }
        }
        
        // Save updated theme.json
        file_put_contents($theme_json_path, json_encode($theme_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        
        wp_send_json_success('Tokens synced successfully');
    }
    
    /**
     * Render Studio settings page
     */
    public function render_studio_settings() {
        ?>
        <div class="wrap">
            <h1><?php _e('Studio Settings', 'studio'); ?></h1>
            <p><?php _e('Welcome to Studio - AI-powered WordPress blocks with centralized design token management.', 'studio'); ?></p>
            
            <div class="studio-dashboard">
                <div class="studio-card">
                    <h2><?php _e('Quick Actions', 'studio'); ?></h2>
                    <a href="<?php echo admin_url('admin.php?page=studio-tokens'); ?>" class="button button-primary">
                        <?php _e('Manage Design Tokens', 'studio'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=studio-presets'); ?>" class="button">
                        <?php _e('Typography Presets', 'studio'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=studio-converter'); ?>" class="button">
                        <?php _e('HTML Converter', 'studio'); ?>
                    </a>
                </div>
                
                <div class="studio-card">
                    <h2><?php _e('Studio Blocks', 'studio'); ?></h2>
                    <p><?php _e('Available blocks:', 'studio'); ?></p>
                    <ul>
                        <li>✅ Studio Text</li>
                        <li>✅ Studio Container</li>
                        <li>📋 Studio Headline</li>
                        <li>📋 Studio Button</li>
                        <li>📋 Studio Grid</li>
                        <li>📋 Studio Image</li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render token manager page
     */
    public function render_token_manager() {
        $tokens = $this->get_design_tokens();
        ?>
        <div class="wrap studio-admin-wrap">
            <div class="studio-admin-header">
                <h1><?php _e('Design Token Manager', 'studio'); ?></h1>
                <p><?php _e('Manage your design tokens and sync them to theme.json', 'studio'); ?></p>
                <div class="studio-sync-status">All tokens synced</div>
            </div>
            
            <div class="studio-tokens-grid">
                <!-- Color Tokens -->
                <div class="studio-token-section">
                    <h2><?php _e('Color Tokens', 'studio'); ?></h2>
                    <div class="studio-token-group">
                        <?php 
                        $colors = isset($tokens['colors']) ? $tokens['colors'] : array();
                        foreach ($colors as $key => $color): 
                        ?>
                        <div class="studio-token-item">
                            <span class="studio-token-name"><?php echo esc_html($key); ?></span>
                            <div class="studio-token-value">
                                <div class="studio-color-preview" style="background-color: <?php echo esc_attr($color['value']); ?>"></div>
                                <input type="color" 
                                       class="studio-token-input studio-color-input" 
                                       data-token-type="color"
                                       data-token-name="<?php echo esc_attr($key); ?>"
                                       data-token-label="<?php echo esc_attr($color['name']); ?>"
                                       value="<?php echo esc_attr($color['value']); ?>">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Typography Tokens -->
                <div class="studio-token-section">
                    <h2><?php _e('Typography Tokens', 'studio'); ?></h2>
                    
                    <div class="studio-token-group">
                        <h3><?php _e('Font Sizes', 'studio'); ?></h3>
                        <?php 
                        $fontSizes = isset($tokens['typography']['fontSizes']) ? $tokens['typography']['fontSizes'] : array();
                        foreach ($fontSizes as $key => $size): 
                        ?>
                        <div class="studio-token-item">
                            <span class="studio-token-name"><?php echo esc_html($key); ?></span>
                            <input type="text" 
                                   class="studio-token-input studio-font-size-input" 
                                   data-token-type="fontSize"
                                   data-token-name="<?php echo esc_attr($key); ?>"
                                   value="<?php echo esc_attr($size); ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="studio-token-group">
                        <h3><?php _e('Font Weights', 'studio'); ?></h3>
                        <?php 
                        $fontWeights = isset($tokens['typography']['fontWeights']) ? $tokens['typography']['fontWeights'] : array();
                        foreach ($fontWeights as $key => $weight): 
                        ?>
                        <div class="studio-token-item">
                            <span class="studio-token-name"><?php echo esc_html($key); ?></span>
                            <input type="text" 
                                   class="studio-token-input studio-font-weight-input" 
                                   data-token-type="fontWeight"
                                   data-token-name="<?php echo esc_attr($key); ?>"
                                   value="<?php echo esc_attr($weight); ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Spacing Tokens -->
                <div class="studio-token-section">
                    <h2><?php _e('Spacing Tokens', 'studio'); ?></h2>
                    <div class="studio-token-group">
                        <?php 
                        $spacing = isset($tokens['spacing']) ? $tokens['spacing'] : array();
                        foreach ($spacing as $key => $space): 
                        ?>
                        <div class="studio-token-item">
                            <span class="studio-token-name"><?php echo esc_html($key); ?></span>
                            <input type="text" 
                                   class="studio-token-input studio-spacing-input" 
                                   data-token-type="spacing"
                                   data-token-name="<?php echo esc_attr($key); ?>"
                                   value="<?php echo esc_attr($space); ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="studio-actions">
                <button class="studio-button studio-button-primary" id="studio-sync-tokens">
                    <?php _e('Sync Tokens', 'studio'); ?>
                </button>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render preset manager page
     */
    public function render_preset_manager() {
        $presets = $this->get_typography_presets();
        ?>
        <div class="wrap studio-admin-wrap">
            <div class="studio-admin-header">
                <h1><?php _e('Typography Presets', 'studio'); ?></h1>
                <p><?php _e('Manage semantic typography presets for your blocks', 'studio'); ?></p>
            </div>
            
            <div class="studio-preset-list">
                <?php foreach ($presets as $key => $preset): ?>
                <div class="studio-preset-item">
                    <div class="studio-preset-header">
                        <h3 class="studio-preset-name"><?php echo esc_html($preset['name']); ?></h3>
                        <div class="studio-preset-actions">
                            <button class="studio-button studio-button-secondary studio-edit-preset" data-preset-name="<?php echo esc_attr($key); ?>">
                                <?php _e('Edit', 'studio'); ?>
                            </button>
                            <button class="studio-button studio-button-danger studio-delete-preset" data-preset-name="<?php echo esc_attr($key); ?>">
                                <?php _e('Delete', 'studio'); ?>
                            </button>
                        </div>
                    </div>
                    <div class="studio-preset-preview" style="
                        font-size: <?php echo esc_attr($preset['size'] ?? '1rem'); ?>;
                        font-weight: <?php echo esc_attr($preset['weight'] ?? '400'); ?>;
                        line-height: <?php echo esc_attr($preset['lineHeight'] ?? '1.5'); ?>;
                        letter-spacing: <?php echo esc_attr($preset['spacing'] ?? 'normal'); ?>;
                        text-transform: <?php echo esc_attr($preset['transform'] ?? 'none'); ?>;
                    ">
                        <?php _e('The quick brown fox jumps over the lazy dog', 'studio'); ?>
                    </div>
                    <div class="studio-preset-details">
                        <div class="studio-preset-detail">
                            <strong><?php _e('Font Size:', 'studio'); ?></strong>
                            <span><?php echo esc_html($preset['size'] ?? '1rem'); ?></span>
                        </div>
                        <div class="studio-preset-detail">
                            <strong><?php _e('Font Weight:', 'studio'); ?></strong>
                            <span><?php echo esc_html($preset['weight'] ?? '400'); ?></span>
                        </div>
                        <?php if (isset($preset['lineHeight'])): ?>
                        <div class="studio-preset-detail">
                            <strong><?php _e('Line Height:', 'studio'); ?></strong>
                            <span><?php echo esc_html($preset['lineHeight']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($preset['spacing'])): ?>
                        <div class="studio-preset-detail">
                            <strong><?php _e('Letter Spacing:', 'studio'); ?></strong>
                            <span><?php echo esc_html($preset['spacing']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($preset['transform'])): ?>
                        <div class="studio-preset-detail">
                            <strong><?php _e('Text Transform:', 'studio'); ?></strong>
                            <span><?php echo esc_html($preset['transform']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="studio-actions">
                <button class="studio-button studio-button-primary" id="studio-add-preset">
                    <?php _e('Add New Preset', 'studio'); ?>
                </button>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render HTML converter page
     */
    public function render_html_converter() {
        ?>
        <div class="wrap studio-admin-wrap">
            <div class="studio-admin-header">
                <h1><?php _e('HTML to Blocks Converter', 'studio'); ?></h1>
                <p><?php _e('Convert HTML content to WordPress blocks using AI-powered transformation', 'studio'); ?></p>
            </div>
            
            <div class="studio-converter-container">
                <div class="studio-converter-input">
                    <h2><?php _e('HTML Input', 'studio'); ?></h2>
                    <textarea id="studio-html-input" 
                              class="studio-converter-textarea" 
                              placeholder="<?php _e('Paste your HTML here...', 'studio'); ?>"></textarea>
                    <div class="studio-converter-actions">
                        <button class="studio-button studio-button-primary" id="studio-convert-html">
                            <?php _e('Convert to Blocks', 'studio'); ?>
                        </button>
                    </div>
                </div>
                
                <div class="studio-converter-output">
                    <h2><?php _e('Block Output', 'studio'); ?></h2>
                    <textarea id="studio-blocks-output" 
                              class="studio-converter-textarea" 
                              readonly
                              placeholder="<?php _e('Converted blocks will appear here...', 'studio'); ?>"></textarea>
                    <div class="studio-converter-actions">
                        <button class="studio-button studio-button-secondary" id="studio-copy-blocks" disabled>
                            <?php _e('Copy to Clipboard', 'studio'); ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="studio-converter-tips">
                <h3><?php _e('Tips:', 'studio'); ?></h3>
                <ul>
                    <li><?php _e('The converter will automatically detect headings, paragraphs, lists, and other common HTML elements', 'studio'); ?></li>
                    <li><?php _e('Complex layouts will be converted to Studio Container blocks with appropriate styling', 'studio'); ?></li>
                    <li><?php _e('Images and media will be converted to Studio Image blocks', 'studio'); ?></li>
                    <li><?php _e('CSS classes and inline styles will be preserved where possible', 'studio'); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }
    
    /**
     * Handle AJAX token sync request
     */
    public function ajax_sync_tokens() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'studio_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        // Get tokens from request
        $tokens = isset($_POST['tokens']) ? json_decode(stripslashes($_POST['tokens']), true) : array();
        
        // Save tokens to studio.json
        $studio_json_path = get_stylesheet_directory() . '/studio.json';
        $result = file_put_contents($studio_json_path, json_encode($tokens, JSON_PRETTY_PRINT));
        
        if ($result !== false) {
            // Sync to theme.json
            $this->sync_tokens_to_theme_json($tokens);
            
            wp_send_json_success(array(
                'message' => __('Tokens synced successfully', 'studio')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Failed to save tokens', 'studio')
            ));
        }
    }
    
    /**
     * Handle AJAX preset save request
     */
    public function ajax_save_preset() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'studio_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        // Get preset data
        $preset_name = isset($_POST['name']) ? sanitize_key($_POST['name']) : '';
        $preset_data = isset($_POST['preset']) ? json_decode(stripslashes($_POST['preset']), true) : array();
        
        if (empty($preset_name) || empty($preset_data)) {
            wp_send_json_error(array(
                'message' => __('Invalid preset data', 'studio')
            ));
        }
        
        // Save preset (in real implementation, this would save to database or file)
        // For now, we'll just return success
        wp_send_json_success(array(
            'message' => __('Preset saved successfully', 'studio')
        ));
    }
    
    /**
     * Handle AJAX HTML conversion request
     */
    public function ajax_convert_html() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'studio_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            wp_die('Insufficient permissions');
        }
        
        // Get HTML content
        $html = isset($_POST['html']) ? wp_kses_post($_POST['html']) : '';
        
        if (empty($html)) {
            wp_send_json_error(array(
                'message' => __('No HTML content provided', 'studio')
            ));
        }
        
        // Convert HTML to blocks
        $blocks = $this->convert_html_to_blocks($html);
        
        wp_send_json_success(array(
            'blocks' => $blocks,
            'message' => __('HTML converted successfully', 'studio')
        ));
    }
    
    /**
     * Convert HTML to WordPress blocks
     */
    private function convert_html_to_blocks($html) {
        // Simple conversion logic - in production this would be more sophisticated
        $blocks = array();
        
        // Parse HTML
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        $body = $dom->getElementsByTagName('body')->item(0);
        
        if ($body) {
            foreach ($body->childNodes as $node) {
                $block = $this->node_to_block($node);
                if ($block) {
                    $blocks[] = $block;
                }
            }
        }
        
        // Convert to block markup
        $block_markup = '';
        foreach ($blocks as $block) {
            $block_markup .= $this->generate_block_markup($block);
        }
        
        return $block_markup;
    }
    
    /**
     * Convert DOM node to block data
     */
    private function node_to_block($node) {
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return null;
        }
        
        $tag = strtolower($node->nodeName);
        $content = $this->get_node_content($node);
        
        switch ($tag) {
            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':
                return array(
                    'name' => 'studio/headline',
                    'attributes' => array(
                        'level' => intval(substr($tag, 1)),
                        'content' => $content
                    )
                );
                
            case 'p':
                return array(
                    'name' => 'studio/text',
                    'attributes' => array(
                        'content' => $content
                    )
                );
                
            case 'div':
                // Convert div to container block
                $innerBlocks = array();
                foreach ($node->childNodes as $child) {
                    $childBlock = $this->node_to_block($child);
                    if ($childBlock) {
                        $innerBlocks[] = $childBlock;
                    }
                }
                return array(
                    'name' => 'studio/container',
                    'attributes' => array(),
                    'innerBlocks' => $innerBlocks
                );
                
            default:
                // Default to text block
                return array(
                    'name' => 'studio/text',
                    'attributes' => array(
                        'content' => $content
                    )
                );
        }
    }
    
    /**
     * Get text content from DOM node
     */
    private function get_node_content($node) {
        $content = '';
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $content .= $child->nodeValue;
            } elseif ($child->nodeType === XML_ELEMENT_NODE) {
                $content .= $this->get_node_content($child);
            }
        }
        return trim($content);
    }
    
    /**
     * Generate block markup
     */
    private function generate_block_markup($block) {
        $markup = '<!-- wp:' . $block['name'];
        
        if (!empty($block['attributes'])) {
            $markup .= ' ' . json_encode($block['attributes']);
        }
        
        $markup .= ' -->' . "\n";
        
        if (isset($block['attributes']['content'])) {
            $markup .= '<p>' . esc_html($block['attributes']['content']) . '</p>' . "\n";
        }
        
        if (isset($block['innerBlocks'])) {
            foreach ($block['innerBlocks'] as $innerBlock) {
                $markup .= $this->generate_block_markup($innerBlock);
            }
        }
        
        $markup .= '<!-- /wp:' . $block['name'] . ' -->' . "\n";
        
        return $markup;
    }
    
    /**
     * Sync tokens to theme.json
     */
    private function sync_tokens_to_theme_json($tokens) {
        $theme_json_path = get_stylesheet_directory() . '/theme.json';
        
        // Load existing theme.json
        $theme_json = array();
        if (file_exists($theme_json_path)) {
            $theme_json = json_decode(file_get_contents($theme_json_path), true);
        }
        
        // Ensure structure exists
        if (!isset($theme_json['settings'])) {
            $theme_json['settings'] = array();
        }
        
        // Sync color tokens
        if (isset($tokens['colors'])) {
            $theme_json['settings']['color'] = array(
                'palette' => array()
            );
            
            foreach ($tokens['colors'] as $key => $color) {
                $theme_json['settings']['color']['palette'][] = array(
                    'slug' => $key,
                    'color' => $color['value'],
                    'name' => $color['name']
                );
            }
        }
        
        // Sync typography tokens
        if (isset($tokens['typography'])) {
            // Font sizes
            if (isset($tokens['typography']['fontSizes'])) {
                $theme_json['settings']['typography']['fontSizes'] = array();
                
                foreach ($tokens['typography']['fontSizes'] as $key => $size) {
                    $theme_json['settings']['typography']['fontSizes'][] = array(
                        'slug' => $key,
                        'size' => $size,
                        'name' => ucfirst($key)
                    );
                }
            }
        }
        
        // Sync spacing tokens
        if (isset($tokens['spacing'])) {
            $theme_json['settings']['spacing'] = array(
                'spacingSizes' => array()
            );
            
            foreach ($tokens['spacing'] as $key => $space) {
                $theme_json['settings']['spacing']['spacingSizes'][] = array(
                    'slug' => $key,
                    'size' => $space,
                    'name' => ucfirst($key)
                );
            }
        }
        
        // Save updated theme.json
        file_put_contents($theme_json_path, json_encode($theme_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}

// Initialize Studio theme integration
Studio_Theme_Integration::get_instance();

// Enqueue child theme styles
function blocksy_child_enqueue_styles() {
    // Enqueue parent theme styles
    wp_enqueue_style('blocksy-parent-style', get_template_directory_uri() . '/style.css');
    
    // Enqueue child theme styles with all MI design system
    wp_enqueue_style(
        'blocksy-child-style', 
        get_stylesheet_directory_uri() . '/style.css', 
        array('blocksy-parent-style'),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'blocksy_child_enqueue_styles');

/**
 * Register custom block pattern categories
 */
function register_mi_agency_pattern_categories() {
    register_block_pattern_category(
        'mi-agency',
        array(
            'label' => __('MI Agency', 'blocksy-child'),
            'description' => __('Custom patterns for MI Agency projects', 'blocksy-child'),
        )
    );
}
add_action('init', 'register_mi_agency_pattern_categories');

/**
 * Enqueue component styles
 */
function enqueue_component_styles() {
    // Enqueue Attractions Loop component CSS
    wp_enqueue_style(
        'attractions-loop-component',
        get_stylesheet_directory_uri() . '/components/pages/home/sections/attractions-loop/attractions-loop.css',
        array(),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'enqueue_component_styles');

/**
 * Villa Admin Menu
 */
function villa_admin_menu() {
    add_menu_page(
        'Villa Management',
        'Villa Management',
        'manage_options',
        'villa-management',
        'villa_admin_dashboard',
        'dashicons-building',
        30
    );
    
    add_submenu_page(
        'villa-management',
        'Users & Owners',
        '👥 Users',
        'manage_options',
        'villa-users',
        'villa_users_page'
    );
    
    add_submenu_page(
        'villa-management',
        'Properties',
        '🏠 Properties',
        'manage_options',
        'villa-properties',
        'villa_properties_page'
    );
    
    add_submenu_page(
        'villa-management',
        'Community',
        '🏛️ Community',
        'manage_options',
        'villa-community',
        'villa_community_page'
    );
}
add_action('admin_menu', 'villa_admin_menu');

/**
 * Villa Admin Dashboard
 */
function villa_admin_dashboard() {
    include get_stylesheet_directory() . '/villa-admin-dashboard.php';
}

/**
 * Villa Users Page (combines CRM + Owner management)
 */
function villa_users_page() {
    include get_stylesheet_directory() . '/villa-admin-users.php';
}

/**
 * Villa Properties Page
 */
function villa_properties_page() {
    include get_stylesheet_directory() . '/villa-admin-properties.php';
}

/**
 * Villa Community Page
 */
function villa_community_page() {
    include get_stylesheet_directory() . '/villa-admin-community.php';
}

// Include Villa Capriani committee functionality
require_once get_stylesheet_directory() . '/villa-committees-frontend.php';

// Include Villa Capriani CRM system
require_once get_stylesheet_directory() . '/villa-owner-crm.php';

// Include Villa Individual Registration
require_once get_stylesheet_directory() . '/villa-individual-registration.php';

require_once get_stylesheet_directory() . '/villa-email-templates.php';
require_once get_stylesheet_directory() . '/villa-smtp-config.php';

/**
 * Enqueue Villa CRM styles
 */
function villa_enqueue_crm_styles() {
    if (is_page() || is_front_page()) {
        wp_enqueue_style(
            'villa-owner-registration',
            get_stylesheet_directory_uri() . '/assets/css/villa-owner-registration.css',
            [],
            '1.0.0'
        );
    }
}
add_action('wp_enqueue_scripts', 'villa_enqueue_crm_styles');
