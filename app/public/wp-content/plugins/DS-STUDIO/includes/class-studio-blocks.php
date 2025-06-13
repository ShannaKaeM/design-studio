<?php
/**
 * Studio Blocks Manager
 * 
 * Handles registration and management of Studio custom blocks
 * 
 * @package DS_Studio
 * @since 2.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class DS_Studio_Blocks {
    
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
        register_block_type(
            DS_STUDIO_PLUGIN_DIR . '/blocks/studio-container/block.json'
        );
        
        // Register Studio Text block
        register_block_type(
            DS_STUDIO_PLUGIN_DIR . '/blocks/studio-text/block.json'
        );
        
        // TODO: Add Studio Button block in Phase 2
        // register_block_type(DS_STUDIO_PLUGIN_DIR . 'blocks/studio-button/block.json');
    }
    
    /**
     * Enqueue editor assets
     */
    public function enqueue_editor_assets() {
        // Main Studio Blocks script
        wp_enqueue_script(
            'studio-blocks-editor',
            DS_STUDIO_PLUGIN_URL . 'assets/js/studio-blocks.js',
            array('wp-blocks', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-element'),
            DS_STUDIO_VERSION,
            true
        );

        // Studio Text Block script
        wp_enqueue_script(
            'studio-text-block',
            DS_STUDIO_PLUGIN_URL . 'blocks/studio-text/index.js',
            array('wp-blocks', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-element'),
            DS_STUDIO_VERSION,
            true
        );

        // Editor styles
        wp_enqueue_style(
            'studio-blocks-editor',
            DS_STUDIO_PLUGIN_URL . 'assets/css/studio-blocks-editor.css',
            array(),
            DS_STUDIO_VERSION
        );

        // Studio Text Block editor styles
        wp_enqueue_style(
            'studio-text-block-editor',
            DS_STUDIO_PLUGIN_URL . 'blocks/studio-text/editor.css',
            array(),
            DS_STUDIO_VERSION
        );

        // Localize Studio tokens for JavaScript
        wp_localize_script('studio-blocks-editor', 'studioTokens', $this->get_studio_tokens());
        wp_localize_script('studio-text-block', 'studioTokens', $this->get_studio_tokens());
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Studio blocks frontend styles
        wp_enqueue_style(
            'studio-blocks-frontend',
            DS_STUDIO_PLUGIN_URL . 'assets/css/studio-blocks.css',
            array(),
            DS_STUDIO_VERSION
        );

        // Studio Text Block frontend styles
        wp_enqueue_style(
            'studio-text-block-frontend',
            DS_STUDIO_PLUGIN_URL . 'blocks/studio-text/style.css',
            array(),
            DS_STUDIO_VERSION
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
                    'title' => __('Studio Blocks', 'ds-studio'),
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
        $token_manager = new DS_Studio_Design_Token_Manager();
        return $token_manager->get_tokens();
    }
}

// Initialize Studio Blocks
new DS_Studio_Blocks();
