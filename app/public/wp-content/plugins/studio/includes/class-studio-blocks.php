<?php
/**
 * Studio Blocks Manager
 * 
 * Handles registration and management of Studio custom blocks
 * 
 * @package Studio
 * @since 2.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Studio_Blocks {
    
    /**
     * Initialize the Studio Blocks system
     */
    public function __construct() {
        add_action('init', array($this, 'register_studio_blocks'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_editor_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_filter('block_categories_all', array($this, 'add_studio_block_category'));
    }
    
    /**
     * Register all Studio blocks
     */
    public function register_studio_blocks() {
        // Register Studio Container block
        register_block_type(STUDIO_PLUGIN_DIR . 'blocks/studio-container/block.json');
        
        // Register Studio Text block
        register_block_type(STUDIO_PLUGIN_DIR . 'blocks/studio-text/block.json');
        
        // TODO: Add Studio Button block in Phase 2
        // register_block_type(STUDIO_PLUGIN_DIR . 'blocks/studio-button/block.json');
    }
    
    /**
     * Enqueue editor assets
     */
    public function enqueue_editor_assets() {
        // Main Studio Blocks script
        wp_enqueue_script(
            'studio-blocks-editor',
            STUDIO_PLUGIN_URL . 'assets/js/studio-blocks.js',
            array('wp-blocks', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-element'),
            STUDIO_VERSION,
            true
        );

        // Studio Text Block script
        wp_enqueue_script(
            'studio-text-block',
            STUDIO_PLUGIN_URL . 'blocks/studio-text/index.js',
            array('wp-blocks', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-element'),
            STUDIO_VERSION,
            true
        );

        // Editor styles
        wp_enqueue_style(
            'studio-blocks-editor',
            STUDIO_PLUGIN_URL . 'assets/css/studio-blocks-editor.css',
            array(),
            STUDIO_VERSION
        );

        // Studio Text Block editor styles
        wp_enqueue_style(
            'studio-text-block-editor',
            STUDIO_PLUGIN_URL . 'blocks/studio-text/editor.css',
            array(),
            STUDIO_VERSION
        );

        // Studio Container Block editor styles
        wp_enqueue_style(
            'studio-container-block-editor',
            STUDIO_PLUGIN_URL . 'blocks/studio-container/editor.css',
            array(),
            STUDIO_VERSION
        );

        // Localize Studio tokens for JavaScript
        $studio_tokens = array(
            'colors' => array(),
            'typography' => array(
                'presets' => array(
                    'hero-title' => array('label' => 'Hero Title'),
                    'section-title' => array('label' => 'Section Title'),
                    'card-title' => array('label' => 'Card Title'),
                    'body-text' => array('label' => 'Body Text'),
                    'caption' => array('label' => 'Caption'),
                    'small-text' => array('label' => 'Small Text')
                )
            ),
            'spacing' => array()
        );
        
        // Try to get actual tokens, but provide fallback
        try {
            $token_manager = new Studio_Design_Token_Manager();
            $actual_tokens = $token_manager->get_tokens();
            if (!empty($actual_tokens)) {
                $studio_tokens = array_merge($studio_tokens, $actual_tokens);
            }
        } catch (Exception $e) {
            error_log('Studio: Token loading failed, using fallback tokens');
        }
        
        wp_localize_script('studio-text-block', 'studioTokens', $studio_tokens);
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Studio blocks frontend styles
        wp_enqueue_style(
            'studio-blocks-frontend',
            STUDIO_PLUGIN_URL . 'assets/css/studio-blocks.css',
            array(),
            STUDIO_VERSION
        );

        // Studio Text Block frontend styles
        wp_enqueue_style(
            'studio-text-block-frontend',
            STUDIO_PLUGIN_URL . 'blocks/studio-text/style.css',
            array(),
            STUDIO_VERSION
        );
    }
    
    /**
     * Add Studio block category
     */
    public function add_studio_block_category($categories) {
        return array_merge(
            array(
                array(
                    'slug'  => 'studio-blocks',
                    'title' => __('Studio Blocks', 'studio'),
                    'icon'  => 'art'
                )
            ),
            $categories
        );
    }
    
    /**
     * Get Studio design tokens for blocks
     */
    private function get_studio_tokens() {
        $token_manager = new Studio_Design_Token_Manager();
        return $token_manager->get_tokens();
    }
}

// Initialize Studio Blocks
new Studio_Blocks();
