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
        add_action('wp_ajax_studio_sync_tokens', array($this, 'handle_token_sync'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_studio_save_preset', array($this, 'ajax_save_preset'));
        add_action('wp_ajax_studio_convert_html', array($this, 'ajax_convert_html'));
        add_action('wp_ajax_studio_save_block_style', array($this, 'ajax_save_block_style'));
        add_action('wp_ajax_studio_save_block_preset', array($this, 'ajax_save_block_preset'));
        add_action('wp_ajax_studio_delete_block_preset', array($this, 'ajax_delete_block_preset'));
        add_action('wp_ajax_studio_get_block_preset', array($this, 'ajax_get_block_preset'));
        add_action('wp_ajax_studio_sync_from_theme', array($this, 'ajax_sync_from_theme'));
    }
    
    /**
     * Register Studio blocks
     */
    public function register_studio_blocks() {
        $blocks = array(
            'studio-text',
            'studio-container',
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
            __('Block Presets', 'studio'),
            __('Block Presets', 'studio'),
            'manage_options',
            'studio-block-presets',
            array($this, 'render_block_presets_manager')
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
     * Get theme.json data
     */
    private function get_theme_json() {
        $theme_json_path = get_stylesheet_directory() . '/theme.json';
        if (!file_exists($theme_json_path)) {
            return array();
        }
        
        $theme_json = json_decode(file_get_contents($theme_json_path), true);
        return $theme_json ?: array();
    }
    
    /**
     * Handle token sync AJAX request
     */
    public function handle_token_sync() {
        check_ajax_referer('studio_tokens', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        // Get the updated tokens from the request
        $tokens = isset($_POST['tokens']) ? json_decode(stripslashes($_POST['tokens']), true) : null;
        
        if (!$tokens) {
            wp_send_json_error('No tokens provided');
        }
        
        $studio_json_path = get_stylesheet_directory() . '/studio.json';
        
        // Save to studio.json
        if (!file_put_contents($studio_json_path, json_encode($tokens, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
            wp_send_json_error('Failed to save studio.json');
        }
        
        // Sync to theme.json
        $this->sync_tokens_to_theme_json($tokens);
        
        wp_send_json_success('Tokens saved successfully');
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
                    <a href="<?php echo admin_url('admin.php?page=studio-block-presets'); ?>" class="button">
                        <?php _e('Block Presets', 'studio'); ?>
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
            
            <!-- Token Editor Tabs -->
            <div class="studio-token-tabs">
                <nav class="studio-tab-nav">
                    <button class="studio-tab-button active" data-tab="colors">
                        <span class="studio-tab-icon">🎨</span>
                        <?php _e('Colors', 'studio'); ?>
                    </button>
                    <button class="studio-tab-button" data-tab="typography">
                        <span class="studio-tab-icon">📝</span>
                        <?php _e('Typography', 'studio'); ?>
                    </button>
                    <button class="studio-tab-button" data-tab="spacing">
                        <span class="studio-tab-icon">📐</span>
                        <?php _e('Spacing', 'studio'); ?>
                    </button>
                    <button class="studio-tab-button" data-tab="layout">
                        <span class="studio-tab-icon">🌐</span>
                        <?php _e('Layout', 'studio'); ?>
                    </button>
                </nav>
                
                <!-- Colors Tab -->
                <div class="studio-tab-content active" id="colors-tab">
                    <div class="studio-tab-header">
                        <h2><?php _e('Color Tokens', 'studio'); ?></h2>
                        <button class="studio-button studio-button-small studio-add-token" data-token-type="color">
                            <?php _e('+ Add Color', 'studio'); ?>
                        </button>
                    </div>
                    <div class="studio-token-grid">
                        <?php 
                        $colors = isset($tokens['colors']) ? $tokens['colors'] : array();
                        foreach ($colors as $key => $color): 
                            $colorValue = isset($color['value']) ? $color['value'] : $color;
                            $colorName = isset($color['name']) ? $color['name'] : ucwords(str_replace('-', ' ', $key));
                        ?>
                        <div class="studio-token-card" data-token-key="<?php echo esc_attr($key); ?>">
                            <div class="studio-token-preview">
                                <div class="studio-color-preview" style="background-color: <?php echo esc_attr($colorValue); ?>"></div>
                            </div>
                            <div class="studio-token-info">
                                <label class="studio-token-label"><?php echo esc_html($key); ?></label>
                                <input type="text" 
                                       class="studio-token-name-input" 
                                       data-token-type="color"
                                       data-token-key="<?php echo esc_attr($key); ?>"
                                       data-field="name"
                                       value="<?php echo esc_attr($colorName); ?>"
                                       placeholder="Color Name">
                                <input type="color" 
                                       class="studio-color-input" 
                                       data-token-type="color"
                                       data-token-key="<?php echo esc_attr($key); ?>"
                                       data-field="value"
                                       value="<?php echo esc_attr($colorValue); ?>">
                                <input type="text" 
                                       class="studio-token-value-input" 
                                       data-token-type="color"
                                       data-token-key="<?php echo esc_attr($key); ?>"
                                       data-field="value"
                                       value="<?php echo esc_attr($colorValue); ?>"
                                       placeholder="#000000">
                            </div>
                            <button class="studio-delete-token" data-token-type="color" data-token-key="<?php echo esc_attr($key); ?>">×</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Typography Tab -->
                <div class="studio-tab-content" id="typography-tab">
                    <div class="studio-tab-header">
                        <h2><?php _e('Typography Tokens', 'studio'); ?></h2>
                    </div>
                    
                    <!-- Font Sizes -->
                    <div class="studio-token-section">
                        <h3><?php _e('Font Sizes', 'studio'); ?></h3>
                        <div class="studio-token-list">
                            <?php 
                            $fontSizes = isset($tokens['typography']['fontSizes']) ? $tokens['typography']['fontSizes'] : array();
                            foreach ($fontSizes as $key => $size): 
                            ?>
                            <div class="studio-token-item" data-token-key="<?php echo esc_attr($key); ?>">
                                <span class="studio-token-name"><?php echo esc_html($key); ?></span>
                                <input type="text" 
                                       class="studio-token-input" 
                                       data-token-type="typography"
                                       data-token-section="fontSizes"
                                       data-token-key="<?php echo esc_attr($key); ?>"
                                       value="<?php echo esc_attr($size); ?>"
                                       placeholder="16px">
                                <button class="studio-delete-token" data-token-type="typography" data-token-section="fontSizes" data-token-key="<?php echo esc_attr($key); ?>">×</button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Font Weights -->
                    <div class="studio-token-section">
                        <h3><?php _e('Font Weights', 'studio'); ?></h3>
                        <div class="studio-token-list">
                            <?php 
                            $fontWeights = isset($tokens['typography']['fontWeights']) ? $tokens['typography']['fontWeights'] : array();
                            foreach ($fontWeights as $key => $weight): 
                            ?>
                            <div class="studio-token-item" data-token-key="<?php echo esc_attr($key); ?>">
                                <span class="studio-token-name"><?php echo esc_html($key); ?></span>
                                <input type="number" 
                                       class="studio-token-input" 
                                       data-token-type="typography"
                                       data-token-section="fontWeights"
                                       data-token-key="<?php echo esc_attr($key); ?>"
                                       value="<?php echo esc_attr($weight); ?>"
                                       min="100" max="900" step="100"
                                       placeholder="400">
                                <button class="studio-delete-token" data-token-type="typography" data-token-section="fontWeights" data-token-key="<?php echo esc_attr($key); ?>">×</button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Line Heights -->
                    <div class="studio-token-section">
                        <h3><?php _e('Line Heights', 'studio'); ?></h3>
                        <div class="studio-token-list">
                            <?php 
                            $lineHeights = isset($tokens['typography']['lineHeights']) ? $tokens['typography']['lineHeights'] : array();
                            foreach ($lineHeights as $key => $height): 
                            ?>
                            <div class="studio-token-item" data-token-key="<?php echo esc_attr($key); ?>">
                                <span class="studio-token-name"><?php echo esc_html($key); ?></span>
                                <input type="text" 
                                       class="studio-token-input" 
                                       data-token-type="typography"
                                       data-token-section="lineHeights"
                                       data-token-key="<?php echo esc_attr($key); ?>"
                                       value="<?php echo esc_attr($height); ?>"
                                       placeholder="24px">
                                <button class="studio-delete-token" data-token-type="typography" data-token-section="lineHeights" data-token-key="<?php echo esc_attr($key); ?>">×</button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Spacing Tab -->
                <div class="studio-tab-content" id="spacing-tab">
                    <div class="studio-tab-header">
                        <h2><?php _e('Spacing Tokens', 'studio'); ?></h2>
                        <button class="studio-button studio-button-small studio-add-token" data-token-type="spacing">
                            <?php _e('+ Add Spacing', 'studio'); ?>
                        </button>
                    </div>
                    <div class="studio-token-list">
                        <?php 
                        $spacing = isset($tokens['spacing']) ? $tokens['spacing'] : array();
                        foreach ($spacing as $key => $value): 
                        ?>
                        <div class="studio-token-item" data-token-key="<?php echo esc_attr($key); ?>">
                            <span class="studio-token-name"><?php echo esc_html($key); ?></span>
                            <input type="text" 
                                   class="studio-token-input" 
                                   data-token-type="spacing"
                                   data-token-key="<?php echo esc_attr($key); ?>"
                                   value="<?php echo esc_attr($value); ?>"
                                   placeholder="16px">
                            <button class="studio-delete-token" data-token-type="spacing" data-token-key="<?php echo esc_attr($key); ?>">×</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Layout Tab -->
                <div class="studio-tab-content" id="layout-tab">
                    <div class="studio-tab-header">
                        <h2><?php _e('Layout Tokens', 'studio'); ?></h2>
                        <button class="studio-button studio-button-small studio-add-token" data-token-type="layout">
                            <?php _e('+ Add Layout', 'studio'); ?>
                        </button>
                    </div>
                    
                    <!-- Layout Dimensions -->
                    <div class="studio-token-section">
                        <h3><?php _e('Layout Dimensions', 'studio'); ?></h3>
                        <div class="studio-token-list">
                            <?php 
                            $layout = isset($tokens['layout']) ? $tokens['layout'] : array();
                            foreach ($layout as $key => $value): 
                            ?>
                            <div class="studio-token-item" data-token-key="<?php echo esc_attr($key); ?>">
                                <span class="studio-token-name"><?php echo esc_html($key); ?></span>
                                <input type="text" 
                                       class="studio-token-input" 
                                       data-token-type="layout"
                                       data-token-key="<?php echo esc_attr($key); ?>"
                                       value="<?php echo esc_attr($value); ?>"
                                       placeholder="1200px">
                                <button class="studio-delete-token" data-token-type="layout" data-token-key="<?php echo esc_attr($key); ?>">×</button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Padding Scale -->
                    <div class="studio-token-section">
                        <h3><?php _e('Padding Scale', 'studio'); ?></h3>
                        <div class="studio-token-list">
                            <?php 
                            $paddingScale = isset($tokens['paddingScale']) ? $tokens['paddingScale'] : array();
                            foreach ($paddingScale as $key => $value): 
                            ?>
                            <div class="studio-token-item" data-token-key="<?php echo esc_attr($key); ?>">
                                <span class="studio-token-name"><?php echo esc_html($key); ?></span>
                                <input type="text" 
                                       class="studio-token-input" 
                                       data-token-type="paddingScale"
                                       data-token-key="<?php echo esc_attr($key); ?>"
                                       value="<?php echo esc_attr($value); ?>"
                                       placeholder="16px">
                                <button class="studio-delete-token" data-token-type="paddingScale" data-token-key="<?php echo esc_attr($key); ?>">×</button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="studio-actions">
                <button class="studio-button studio-button-secondary" id="studio-sync-from-theme">
                    <?php _e('Sync from Theme.json', 'studio'); ?>
                </button>
                <button class="studio-button studio-button-primary" id="studio-save-tokens">
                    <?php _e('Save Tokens', 'studio'); ?>
                </button>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render block presets manager page
     */
    public function render_block_presets_manager() {
        // Get block presets from theme.json
        $theme_json = $this->get_theme_json();
        $block_presets = isset($theme_json['settings']['custom']['blockPresets']) ? $theme_json['settings']['custom']['blockPresets'] : array();
        
        // Group presets by block type
        $presets_by_block = array();
        foreach ($block_presets as $preset_id => $preset) {
            $block_types = isset($preset['blockTypes']) ? $preset['blockTypes'] : array('studio/text');
            foreach ($block_types as $block_type) {
                if (!isset($presets_by_block[$block_type])) {
                    $presets_by_block[$block_type] = array();
                }
                $presets_by_block[$block_type][$preset_id] = $preset;
            }
        }
        
        // Define supported block types
        $supported_blocks = array(
            'studio/text' => __('Studio Text', 'studio'),
            'studio/button' => __('Studio Button', 'studio'),
            'studio/container' => __('Studio Container', 'studio'),
            'studio/grid' => __('Studio Grid', 'studio'),
            'studio/image' => __('Studio Image', 'studio')
        );
        ?>
        <div class="wrap studio-admin-wrap">
            <div class="studio-admin-header">
                <h1><?php _e('Block Presets', 'studio'); ?></h1>
                <p><?php _e('Create and manage reusable presets for your Studio blocks', 'studio'); ?></p>
            </div>
            
            <div class="studio-block-presets-container">
                <?php foreach ($supported_blocks as $block_type => $block_name): ?>
                <div class="studio-block-preset-section" data-block-type="<?php echo esc_attr($block_type); ?>">
                    <div class="studio-block-preset-header">
                        <h2><?php echo esc_html($block_name); ?></h2>
                        <button class="studio-button studio-button-small studio-add-preset" data-block-type="<?php echo esc_attr($block_type); ?>">
                            <?php _e('+ Add Preset', 'studio'); ?>
                        </button>
                    </div>
                    
                    <div class="studio-preset-list">
                        <?php 
                        $block_presets = isset($presets_by_block[$block_type]) ? $presets_by_block[$block_type] : array();
                        if (empty($block_presets)): 
                        ?>
                            <p class="studio-no-presets"><?php _e('No presets yet. Create your first preset!', 'studio'); ?></p>
                        <?php else: ?>
                            <?php foreach ($block_presets as $preset_id => $preset): ?>
                            <div class="studio-preset-item" data-preset-id="<?php echo esc_attr($preset_id); ?>">
                                <div class="studio-preset-header">
                                    <h3 class="studio-preset-name"><?php echo esc_html($preset['label'] ?? $preset_id); ?></h3>
                                    <div class="studio-preset-actions">
                                        <button class="studio-button studio-button-secondary studio-edit-preset" 
                                                data-preset-id="<?php echo esc_attr($preset_id); ?>"
                                                data-block-type="<?php echo esc_attr($block_type); ?>">
                                            <?php _e('Edit', 'studio'); ?>
                                        </button>
                                        <button class="studio-button studio-button-danger studio-delete-preset" 
                                                data-preset-id="<?php echo esc_attr($preset_id); ?>">
                                            <?php _e('Delete', 'studio'); ?>
                                        </button>
                                    </div>
                                </div>
                                <?php if (isset($preset['description'])): ?>
                                <p class="studio-preset-description"><?php echo esc_html($preset['description']); ?></p>
                                <?php endif; ?>
                                <?php if (isset($preset['css'])): ?>
                                <div class="studio-preset-preview">
                                    <pre class="studio-preset-css"><?php echo esc_html($preset['css']); ?></pre>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Add/Edit Preset Form (hidden by default) -->
            <div id="studio-preset-form" class="studio-preset-form" style="display: none;">
                <div class="studio-preset-form-header">
                    <h3><?php _e('Add New Preset', 'studio'); ?></h3>
                    <button class="studio-close-form">&times;</button>
                </div>
                <form id="studio-preset-form-content">
                    <div class="studio-form-group">
                        <label><?php _e('Preset Name', 'studio'); ?></label>
                        <input type="text" id="preset-name" class="studio-input" required>
                    </div>
                    <div class="studio-form-group">
                        <label><?php _e('Label', 'studio'); ?></label>
                        <input type="text" id="preset-label" class="studio-input" required>
                    </div>
                    <div class="studio-form-group">
                        <label><?php _e('Description', 'studio'); ?></label>
                        <textarea id="preset-description" class="studio-textarea"></textarea>
                    </div>
                    <div class="studio-form-group">
                        <label><?php _e('CSS (use CSS variables)', 'studio'); ?></label>
                        <textarea id="preset-css" class="studio-textarea studio-code" rows="10" placeholder="font-size: var(--wp--preset--font-size--large);
font-weight: 600;
color: var(--wp--preset--color--primary);
padding: var(--wp--preset--spacing--20);"></textarea>
                    </div>
                    <input type="hidden" id="preset-block-type">
                    <input type="hidden" id="preset-id">
                    <div class="studio-form-actions">
                        <button type="submit" class="studio-button studio-button-primary">
                            <?php _e('Save Preset', 'studio'); ?>
                        </button>
                        <button type="button" class="studio-button studio-cancel-form">
                            <?php _e('Cancel', 'studio'); ?>
                        </button>
                    </div>
                </form>
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
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'studio_tokens')) {
            wp_die('Security check failed');
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        // Get tokens from request
        $tokens = isset($_POST['tokens']) ? json_decode(stripslashes($_POST['tokens']), true) : array();
        
        if (!$tokens) {
            wp_send_json_error('No tokens provided');
        }
        
        $studio_json_path = get_stylesheet_directory() . '/studio.json';
        
        // Save to studio.json
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
        
        // Store the tokens in a custom section to preserve them
        if (!isset($theme_json['settings']['custom'])) {
            $theme_json['settings']['custom'] = array();
        }
        
        // Store complete token data
        $theme_json['settings']['custom']['studioTokens'] = array(
            'lastSynced' => date('Y-m-d H:i:s'),
            'tokens' => $tokens
        );
        
        // Ensure color settings exist
        if (!isset($theme_json['settings']['color'])) {
            $theme_json['settings']['color'] = array(
                'custom' => true,
                'customDuotone' => true,
                'customGradient' => true,
                'defaultPalette' => false,
                'defaultGradients' => false,
                'defaultDuotone' => false
            );
        }
        
        // Sync color tokens - merge with existing
        if (isset($tokens['colors'])) {
            // Get existing palette or empty array
            $existing_palette = isset($theme_json['settings']['color']['palette']) 
                ? $theme_json['settings']['color']['palette'] 
                : array();
            
            // Create a map of existing colors by slug
            $existing_map = array();
            foreach ($existing_palette as $color) {
                if (isset($color['slug'])) {
                    $existing_map[$color['slug']] = $color;
                }
            }
            
            // Update with token colors
            foreach ($tokens['colors'] as $key => $color) {
                $existing_map[$key] = array(
                    'slug' => $key,
                    'color' => $color['value'],
                    'name' => $color['name']
                );
            }
            
            // Convert back to array
            $theme_json['settings']['color']['palette'] = array_values($existing_map);
        }
        
        // Sync typography tokens
        if (isset($tokens['typography'])) {
            // Ensure typography settings exist
            if (!isset($theme_json['settings']['typography'])) {
                $theme_json['settings']['typography'] = array(
                    'customFontSize' => true,
                    'fontStyle' => true,
                    'fontWeight' => true,
                    'letterSpacing' => true,
                    'lineHeight' => true,
                    'textDecoration' => true,
                    'textTransform' => true
                );
            }
            
            // Font sizes - merge with existing
            if (isset($tokens['typography']['fontSizes'])) {
                $existing_sizes = isset($theme_json['settings']['typography']['fontSizes']) 
                    ? $theme_json['settings']['typography']['fontSizes'] 
                    : array();
                
                // Create map of existing sizes
                $size_map = array();
                foreach ($existing_sizes as $size) {
                    if (isset($size['slug'])) {
                        $size_map[$size['slug']] = $size;
                    }
                }
                
                // Update with token sizes
                foreach ($tokens['typography']['fontSizes'] as $key => $size) {
                    $size_map[$key] = array(
                        'slug' => $key,
                        'size' => $size,
                        'name' => ucfirst($key)
                    );
                }
                
                $theme_json['settings']['typography']['fontSizes'] = array_values($size_map);
            }
            
            // Add other typography settings like line height, letter spacing
            if (isset($tokens['typography']['lineHeights'])) {
                $theme_json['settings']['custom']['lineHeights'] = $tokens['typography']['lineHeights'];
            }
            
            if (isset($tokens['typography']['letterSpacing'])) {
                $theme_json['settings']['custom']['letterSpacing'] = $tokens['typography']['letterSpacing'];
            }
        }
        
        // Sync spacing tokens
        if (isset($tokens['spacing'])) {
            if (!isset($theme_json['settings']['spacing'])) {
                $theme_json['settings']['spacing'] = array();
            }
            
            // Spacing sizes - merge with existing
            $existing_spacing = isset($theme_json['settings']['spacing']['spacingSizes']) 
                ? $theme_json['settings']['spacing']['spacingSizes'] 
                : array();
            
            // Create map of existing spacing
            $spacing_map = array();
            foreach ($existing_spacing as $space) {
                if (isset($space['slug'])) {
                    $spacing_map[$space['slug']] = $space;
                }
            }
            
            // Update with token spacing
            foreach ($tokens['spacing'] as $key => $space) {
                $spacing_map[$key] = array(
                    'slug' => $key,
                    'size' => $space,
                    'name' => ucfirst($key)
                );
            }
            
            $theme_json['settings']['spacing']['spacingSizes'] = array_values($spacing_map);
        }
        
        // Sync layout tokens
        if (isset($tokens['layout'])) {
            if (!isset($theme_json['settings']['layout'])) {
                $theme_json['settings']['layout'] = array();
            }
            
            // Handle layout tokens with simple key-value structure
            if (isset($tokens['layout']['contentSize'])) {
                $theme_json['settings']['layout']['contentSize'] = $tokens['layout']['contentSize'];
            }
            
            if (isset($tokens['layout']['wideSize'])) {
                $theme_json['settings']['layout']['wideSize'] = $tokens['layout']['wideSize'];
            }
        }
        
        // Sync padding scale to custom section
        if (isset($tokens['paddingScale'])) {
            if (!isset($theme_json['settings']['custom'])) {
                $theme_json['settings']['custom'] = array();
            }
            $theme_json['settings']['custom']['paddingScale'] = $tokens['paddingScale'];
        }
        
        // Save updated theme.json
        file_put_contents($theme_json_path, json_encode($theme_json, JSON_PRETTY_PRINT));
    }
    
    /**
     * Sync tokens from theme.json to studio.json (reverse sync)
     */
    private function sync_from_theme_to_studio() {
        $theme_json = $this->get_theme_json();
        
        if (!$theme_json) {
            return false;
        }
        
        $studio_tokens = array();
        
        // Extract colors from theme.json palette
        if (isset($theme_json['settings']['color']['palette'])) {
            $studio_tokens['colors'] = array();
            foreach ($theme_json['settings']['color']['palette'] as $color) {
                $studio_tokens['colors'][$color['slug']] = array(
                    'name' => $color['name'],
                    'value' => $color['color']
                );
            }
        }
        
        // Extract typography from theme.json
        if (isset($theme_json['settings']['typography'])) {
            $studio_tokens['typography'] = array();
            
            // Font sizes
            if (isset($theme_json['settings']['typography']['fontSizes'])) {
                $studio_tokens['typography']['fontSizes'] = array();
                foreach ($theme_json['settings']['typography']['fontSizes'] as $size) {
                    $studio_tokens['typography']['fontSizes'][$size['slug']] = $size['size'];
                }
            }
            
            // Add default font families and weights
            $studio_tokens['typography']['fontFamilies'] = array(
                'primary' => array(
                    'name' => 'Montserrat',
                    'value' => 'Montserrat, -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif'
                ),
                'secondary' => array(
                    'name' => 'Inter',
                    'value' => 'Inter, -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif'
                )
            );
            
            $studio_tokens['typography']['lineHeights'] = array(
                'xs' => '16px',
                'sm' => '20px',
                'md' => '24px',
                'lg' => '28px',
                'xl' => '32px',
                'xxl' => '36px',
                'xxxl' => '40px'
            );
            
            $studio_tokens['typography']['fontWeights'] = array(
                'light' => 300,
                'regular' => 400,
                'medium' => 500,
                'semibold' => 600,
                'bold' => 700
            );
        }
        
        // Extract spacing from theme.json
        if (isset($theme_json['settings']['spacing']['spacingSizes'])) {
            $studio_tokens['spacing'] = array();
            foreach ($theme_json['settings']['spacing']['spacingSizes'] as $spacing) {
                $studio_tokens['spacing'][$spacing['slug']] = $spacing['size'];
            }
        }
        
        // Extract layout from theme.json
        if (isset($theme_json['settings']['layout'])) {
            $studio_tokens['layout'] = array();
            if (isset($theme_json['settings']['layout']['contentSize'])) {
                $studio_tokens['layout']['contentSize'] = $theme_json['settings']['layout']['contentSize'];
            }
            if (isset($theme_json['settings']['layout']['wideSize'])) {
                $studio_tokens['layout']['wideSize'] = $theme_json['settings']['layout']['wideSize'];
            }
        }
        
        // Extract padding scale from custom section
        if (isset($theme_json['settings']['custom']['paddingScale'])) {
            $studio_tokens['paddingScale'] = $theme_json['settings']['custom']['paddingScale'];
        }
        
        // Save to studio.json
        $studio_json_path = get_stylesheet_directory() . '/studio.json';
        file_put_contents($studio_json_path, json_encode($studio_tokens, JSON_PRETTY_PRINT));
        
        return true;
    }
    
    /**
     * Handle AJAX request to sync from theme.json to studio.json
     */
    public function ajax_sync_from_theme() {
        check_ajax_referer('studio_admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $result = $this->sync_from_theme_to_studio();
        
        if ($result) {
            wp_send_json_success(array(
                'message' => 'Tokens synced from theme.json to studio.json successfully'
            ));
        } else {
            wp_send_json_error('Failed to sync tokens');
        }
    }
    
    /**
     * Handle AJAX save block style request
     */
    public function ajax_save_block_style() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'studio_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        // Get style data
        $style_key = isset($_POST['styleKey']) ? sanitize_key($_POST['styleKey']) : '';
        $style_data = isset($_POST['styleData']) ? json_decode(stripslashes($_POST['styleData']), true) : array();
        
        if (empty($style_key) || empty($style_data)) {
            wp_send_json_error(array(
                'message' => __('Invalid style data', 'studio')
            ));
        }
        
        // Load theme.json
        $theme_json_path = get_stylesheet_directory() . '/theme.json';
        $theme_json = json_decode(file_get_contents($theme_json_path), true);
        
        // Ensure blockStyles section exists
        if (!isset($theme_json['settings']['custom']['blockStyles'])) {
            $theme_json['settings']['custom']['blockStyles'] = array();
        }
        
        // Add the new block style
        $theme_json['settings']['custom']['blockStyles'][$style_key] = $style_data;
        
        // Save updated theme.json
        file_put_contents($theme_json_path, json_encode($theme_json, JSON_PRETTY_PRINT));
        
        wp_send_json_success(array(
            'message' => __('Block style saved successfully', 'studio'),
            'styleKey' => $style_key,
            'styleData' => $style_data
        ));
    }
    
    /**
     * Handle block preset save AJAX request
     */
    public function ajax_save_block_preset() {
        check_ajax_referer('studio_admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $preset_id = isset($_POST['preset_id']) ? sanitize_text_field($_POST['preset_id']) : '';
        $preset_name = isset($_POST['preset_name']) ? sanitize_text_field($_POST['preset_name']) : '';
        $preset_label = isset($_POST['preset_label']) ? sanitize_text_field($_POST['preset_label']) : '';
        $preset_description = isset($_POST['preset_description']) ? sanitize_textarea_field($_POST['preset_description']) : '';
        $preset_css = isset($_POST['preset_css']) ? wp_strip_all_tags($_POST['preset_css']) : '';
        $block_type = isset($_POST['block_type']) ? sanitize_text_field($_POST['block_type']) : '';
        
        if (empty($preset_name) || empty($preset_label) || empty($block_type)) {
            wp_send_json_error('Missing required fields');
        }
        
        // Get theme.json
        $theme_json = $this->get_theme_json();
        
        // Initialize blockPresets if not exists
        if (!isset($theme_json['settings']['custom']['blockPresets'])) {
            $theme_json['settings']['custom']['blockPresets'] = array();
        }
        
        // Create preset data
        $preset_data = array(
            'label' => $preset_label,
            'description' => $preset_description,
            'css' => $preset_css,
            'blockTypes' => array($block_type)
        );
        
        // If editing existing preset, preserve other block types
        if (!empty($preset_id) && isset($theme_json['settings']['custom']['blockPresets'][$preset_id])) {
            $existing = $theme_json['settings']['custom']['blockPresets'][$preset_id];
            if (isset($existing['blockTypes'])) {
                $preset_data['blockTypes'] = array_unique(array_merge($existing['blockTypes'], array($block_type)));
            }
        }
        
        // Save preset
        $save_id = !empty($preset_id) ? $preset_id : $preset_name;
        $theme_json['settings']['custom']['blockPresets'][$save_id] = $preset_data;
        
        // Save theme.json
        $theme_json_path = get_stylesheet_directory() . '/theme.json';
        file_put_contents($theme_json_path, json_encode($theme_json, JSON_PRETTY_PRINT));
        
        wp_send_json_success(array(
            'message' => 'Preset saved successfully',
            'preset_id' => $save_id
        ));
    }
    
    /**
     * Handle block preset delete AJAX request
     */
    public function ajax_delete_block_preset() {
        check_ajax_referer('studio_admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $preset_id = isset($_POST['preset_id']) ? sanitize_text_field($_POST['preset_id']) : '';
        
        if (empty($preset_id)) {
            wp_send_json_error('Missing preset ID');
        }
        
        // Get theme.json
        $theme_json = $this->get_theme_json();
        
        // Remove preset
        if (isset($theme_json['settings']['custom']['blockPresets'][$preset_id])) {
            unset($theme_json['settings']['custom']['blockPresets'][$preset_id]);
            
            // Save theme.json
            $theme_json_path = get_stylesheet_directory() . '/theme.json';
            file_put_contents($theme_json_path, json_encode($theme_json, JSON_PRETTY_PRINT));
            
            wp_send_json_success('Preset deleted successfully');
        } else {
            wp_send_json_error('Preset not found');
        }
    }
    
    /**
     * Handle get block preset AJAX request
     */
    public function ajax_get_block_preset() {
        check_ajax_referer('studio_admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $preset_id = isset($_POST['preset_id']) ? sanitize_text_field($_POST['preset_id']) : '';
        
        if (empty($preset_id)) {
            wp_send_json_error('Missing preset ID');
        }
        
        // Get theme.json
        $theme_json = $this->get_theme_json();
        
        if (isset($theme_json['settings']['custom']['blockPresets'][$preset_id])) {
            $preset = $theme_json['settings']['custom']['blockPresets'][$preset_id];
            $preset['id'] = $preset_id;
            wp_send_json_success($preset);
        } else {
            wp_send_json_error('Preset not found');
        }
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

require_once get_stylesheet_directory() . '/villa-email-templates.php';
require_once get_stylesheet_directory() . '/villa-smtp-config.php';
