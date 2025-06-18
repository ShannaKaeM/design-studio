<?php
/**
 * Villa Custom Post Types
 * 
 * Register custom post types for Villa management
 * 
 * @package TheStudio
 */

namespace Studio\Villa;

class VillaPostTypes {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', [$this, 'register_post_types']);
        add_action('init', [$this, 'register_taxonomies']);
    }
    
    /**
     * Register custom post types
     */
    public function register_post_types() {
        
        // Villa Properties
        register_post_type('villa_property', [
            'labels' => [
                'name' => 'Properties',
                'singular_name' => 'Property',
                'add_new' => 'Add Property',
                'add_new_item' => 'Add New Property',
                'edit_item' => 'Edit Property',
                'new_item' => 'New Property',
                'view_item' => 'View Property',
                'search_items' => 'Search Properties',
                'not_found' => 'No properties found',
                'not_found_in_trash' => 'No properties in trash',
                'all_items' => 'All Properties',
                'menu_name' => 'Properties'
            ],
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => false, // Hide from menu, we'll use custom admin pages
            'query_var' => true,
            'rewrite' => ['slug' => 'property'],
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-admin-home'
        ]);
        
        // Villa Owners
        register_post_type('villa_owner', [
            'labels' => [
                'name' => 'Owners',
                'singular_name' => 'Owner',
                'add_new' => 'Add Owner',
                'add_new_item' => 'Add New Owner',
                'edit_item' => 'Edit Owner',
                'new_item' => 'New Owner',
                'view_item' => 'View Owner',
                'search_items' => 'Search Owners',
                'not_found' => 'No owners found',
                'not_found_in_trash' => 'No owners in trash',
                'all_items' => 'All Owners',
                'menu_name' => 'Owners'
            ],
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => false, // Hide from menu, we'll use custom admin pages
            'query_var' => false,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'supports' => ['title', 'custom-fields'],
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-admin-users'
        ]);
        
        // Committees
        register_post_type('villa_committee', [
            'labels' => [
                'name' => 'Committees',
                'singular_name' => 'Committee',
                'add_new' => 'Add Committee',
                'add_new_item' => 'Add New Committee',
                'edit_item' => 'Edit Committee',
                'new_item' => 'New Committee',
                'view_item' => 'View Committee',
                'search_items' => 'Search Committees',
                'not_found' => 'No committees found',
                'not_found_in_trash' => 'No committees in trash',
                'all_items' => 'All Committees',
                'menu_name' => 'Committees'
            ],
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => false, // Hide from menu, we'll use custom admin pages
            'query_var' => true,
            'rewrite' => ['slug' => 'committee'],
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'supports' => ['title', 'editor', 'custom-fields'],
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-groups'
        ]);
        
        // Voting Proposals
        register_post_type('villa_proposal', [
            'labels' => [
                'name' => 'Proposals',
                'singular_name' => 'Proposal',
                'add_new' => 'Add Proposal',
                'add_new_item' => 'Add New Proposal',
                'edit_item' => 'Edit Proposal',
                'new_item' => 'New Proposal',
                'view_item' => 'View Proposal',
                'search_items' => 'Search Proposals',
                'not_found' => 'No proposals found',
                'not_found_in_trash' => 'No proposals in trash',
                'all_items' => 'All Proposals',
                'menu_name' => 'Proposals'
            ],
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => false, // Hide from menu, we'll use custom admin pages
            'query_var' => true,
            'rewrite' => ['slug' => 'proposal'],
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'supports' => ['title', 'editor', 'custom-fields', 'comments'],
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-clipboard'
        ]);
    }
    
    /**
     * Register taxonomies
     */
    public function register_taxonomies() {
        
        // Property Types
        register_taxonomy('property_type', 'villa_property', [
            'labels' => [
                'name' => 'Property Types',
                'singular_name' => 'Property Type',
                'search_items' => 'Search Property Types',
                'all_items' => 'All Property Types',
                'edit_item' => 'Edit Property Type',
                'update_item' => 'Update Property Type',
                'add_new_item' => 'Add New Property Type',
                'new_item_name' => 'New Property Type Name',
                'menu_name' => 'Property Types'
            ],
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'property-type'],
            'show_in_rest' => true
        ]);
        
        // Committee Types
        register_taxonomy('committee_type', 'villa_committee', [
            'labels' => [
                'name' => 'Committee Types',
                'singular_name' => 'Committee Type',
                'search_items' => 'Search Committee Types',
                'all_items' => 'All Committee Types',
                'edit_item' => 'Edit Committee Type',
                'update_item' => 'Update Committee Type',
                'add_new_item' => 'Add New Committee Type',
                'new_item_name' => 'New Committee Type Name',
                'menu_name' => 'Committee Types'
            ],
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'committee-type'],
            'show_in_rest' => true
        ]);
        
        // Proposal Status
        register_taxonomy('proposal_status', 'villa_proposal', [
            'labels' => [
                'name' => 'Proposal Status',
                'singular_name' => 'Status',
                'search_items' => 'Search Statuses',
                'all_items' => 'All Statuses',
                'edit_item' => 'Edit Status',
                'update_item' => 'Update Status',
                'add_new_item' => 'Add New Status',
                'new_item_name' => 'New Status Name',
                'menu_name' => 'Status'
            ],
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'proposal-status'],
            'show_in_rest' => true
        ]);
    }
}