<?php
/**
 * Villa ACF Field Groups
 * 
 * Programmatically register ACF field groups for Villa system
 * 
 * @package TheStudio
 */

namespace Studio\Villa;

class VillaACFFields {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('acf/init', [$this, 'register_field_groups']);
    }
    
    /**
     * Register ACF field groups
     */
    public function register_field_groups() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }
        
        // Property Fields
        acf_add_local_field_group([
            'key' => 'group_villa_property',
            'title' => 'Property Details',
            'fields' => [
                [
                    'key' => 'field_property_address',
                    'label' => 'Address',
                    'name' => 'property_address',
                    'type' => 'text',
                    'required' => 1,
                ],
                [
                    'key' => 'field_property_unit',
                    'label' => 'Unit/Lot Number',
                    'name' => 'property_unit',
                    'type' => 'text',
                    'required' => 1,
                ],
                [
                    'key' => 'field_property_bedrooms',
                    'label' => 'Bedrooms',
                    'name' => 'property_bedrooms',
                    'type' => 'number',
                    'min' => 1,
                    'max' => 10,
                ],
                [
                    'key' => 'field_property_bathrooms',
                    'label' => 'Bathrooms',
                    'name' => 'property_bathrooms',
                    'type' => 'number',
                    'min' => 1,
                    'max' => 10,
                    'step' => 0.5,
                ],
                [
                    'key' => 'field_property_area',
                    'label' => 'Area (sq ft)',
                    'name' => 'property_area',
                    'type' => 'number',
                ],
                [
                    'key' => 'field_property_owner',
                    'label' => 'Owner',
                    'name' => 'property_owner',
                    'type' => 'post_object',
                    'post_type' => ['villa_owner'],
                    'return_format' => 'id',
                    'ui' => 1,
                ],
                [
                    'key' => 'field_property_status',
                    'label' => 'Status',
                    'name' => 'property_status',
                    'type' => 'select',
                    'choices' => [
                        'occupied' => 'Owner Occupied',
                        'rented' => 'Rented',
                        'vacant' => 'Vacant',
                        'sale' => 'For Sale',
                    ],
                    'default_value' => 'occupied',
                ],
                [
                    'key' => 'field_property_gallery',
                    'label' => 'Property Gallery',
                    'name' => 'property_gallery',
                    'type' => 'gallery',
                    'return_format' => 'id',
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'villa_property',
                    ],
                ],
            ],
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
        ]);
        
        // Owner Fields
        acf_add_local_field_group([
            'key' => 'group_villa_owner',
            'title' => 'Owner Details',
            'fields' => [
                [
                    'key' => 'field_owner_first_name',
                    'label' => 'First Name',
                    'name' => 'owner_first_name',
                    'type' => 'text',
                    'required' => 1,
                ],
                [
                    'key' => 'field_owner_last_name',
                    'label' => 'Last Name',
                    'name' => 'owner_last_name',
                    'type' => 'text',
                    'required' => 1,
                ],
                [
                    'key' => 'field_owner_email',
                    'label' => 'Email',
                    'name' => 'owner_email',
                    'type' => 'email',
                    'required' => 1,
                ],
                [
                    'key' => 'field_owner_phone',
                    'label' => 'Phone',
                    'name' => 'owner_phone',
                    'type' => 'text',
                ],
                [
                    'key' => 'field_owner_emergency_contact',
                    'label' => 'Emergency Contact',
                    'name' => 'owner_emergency_contact',
                    'type' => 'textarea',
                    'rows' => 3,
                ],
                [
                    'key' => 'field_owner_properties',
                    'label' => 'Properties',
                    'name' => 'owner_properties',
                    'type' => 'relationship',
                    'post_type' => ['villa_property'],
                    'filters' => ['search'],
                    'return_format' => 'id',
                ],
                [
                    'key' => 'field_owner_committees',
                    'label' => 'Committee Memberships',
                    'name' => 'owner_committees',
                    'type' => 'relationship',
                    'post_type' => ['villa_committee'],
                    'filters' => ['search'],
                    'return_format' => 'id',
                ],
                [
                    'key' => 'field_owner_status',
                    'label' => 'Status',
                    'name' => 'owner_status',
                    'type' => 'select',
                    'choices' => [
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'pending' => 'Pending Approval',
                    ],
                    'default_value' => 'pending',
                ],
                [
                    'key' => 'field_owner_registration_date',
                    'label' => 'Registration Date',
                    'name' => 'owner_registration_date',
                    'type' => 'date_picker',
                    'display_format' => 'F j, Y',
                    'return_format' => 'Y-m-d',
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'villa_owner',
                    ],
                ],
            ],
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
        ]);
        
        // Committee Fields
        acf_add_local_field_group([
            'key' => 'group_villa_committee',
            'title' => 'Committee Details',
            'fields' => [
                [
                    'key' => 'field_committee_description',
                    'label' => 'Description',
                    'name' => 'committee_description',
                    'type' => 'textarea',
                    'rows' => 4,
                ],
                [
                    'key' => 'field_committee_chair',
                    'label' => 'Committee Chair',
                    'name' => 'committee_chair',
                    'type' => 'post_object',
                    'post_type' => ['villa_owner'],
                    'return_format' => 'id',
                    'ui' => 1,
                ],
                [
                    'key' => 'field_committee_members',
                    'label' => 'Committee Members',
                    'name' => 'committee_members',
                    'type' => 'relationship',
                    'post_type' => ['villa_owner'],
                    'filters' => ['search'],
                    'return_format' => 'id',
                ],
                [
                    'key' => 'field_committee_meeting_schedule',
                    'label' => 'Meeting Schedule',
                    'name' => 'committee_meeting_schedule',
                    'type' => 'text',
                    'instructions' => 'e.g., "First Tuesday of each month at 7 PM"',
                ],
                [
                    'key' => 'field_committee_quorum',
                    'label' => 'Quorum Requirement',
                    'name' => 'committee_quorum',
                    'type' => 'number',
                    'instructions' => 'Minimum number of members for decisions',
                    'default_value' => 3,
                    'min' => 1,
                ],
                [
                    'key' => 'field_committee_workspace_url',
                    'label' => 'Workspace URL',
                    'name' => 'committee_workspace_url',
                    'type' => 'url',
                    'instructions' => 'Link to committee workspace or shared documents',
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'villa_committee',
                    ],
                ],
            ],
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
        ]);
        
        // Proposal Fields
        acf_add_local_field_group([
            'key' => 'group_villa_proposal',
            'title' => 'Proposal Details',
            'fields' => [
                [
                    'key' => 'field_proposal_summary',
                    'label' => 'Summary',
                    'name' => 'proposal_summary',
                    'type' => 'textarea',
                    'rows' => 3,
                    'required' => 1,
                ],
                [
                    'key' => 'field_proposal_sponsor',
                    'label' => 'Sponsor',
                    'name' => 'proposal_sponsor',
                    'type' => 'post_object',
                    'post_type' => ['villa_owner'],
                    'return_format' => 'id',
                    'ui' => 1,
                    'required' => 1,
                ],
                [
                    'key' => 'field_proposal_committee',
                    'label' => 'Committee',
                    'name' => 'proposal_committee',
                    'type' => 'post_object',
                    'post_type' => ['villa_committee'],
                    'return_format' => 'id',
                    'ui' => 1,
                ],
                [
                    'key' => 'field_proposal_voting_type',
                    'label' => 'Voting Type',
                    'name' => 'proposal_voting_type',
                    'type' => 'select',
                    'choices' => [
                        'simple_majority' => 'Simple Majority (50% + 1)',
                        'super_majority' => 'Super Majority (2/3)',
                        'unanimous' => 'Unanimous',
                        'quorum_only' => 'Quorum Only',
                    ],
                    'default_value' => 'simple_majority',
                ],
                [
                    'key' => 'field_proposal_voting_start',
                    'label' => 'Voting Start Date',
                    'name' => 'proposal_voting_start',
                    'type' => 'date_time_picker',
                    'display_format' => 'F j, Y g:i a',
                    'return_format' => 'Y-m-d H:i:s',
                ],
                [
                    'key' => 'field_proposal_voting_end',
                    'label' => 'Voting End Date',
                    'name' => 'proposal_voting_end',
                    'type' => 'date_time_picker',
                    'display_format' => 'F j, Y g:i a',
                    'return_format' => 'Y-m-d H:i:s',
                ],
                [
                    'key' => 'field_proposal_results',
                    'label' => 'Voting Results',
                    'name' => 'proposal_results',
                    'type' => 'group',
                    'sub_fields' => [
                        [
                            'key' => 'field_results_yes',
                            'label' => 'Yes Votes',
                            'name' => 'yes_votes',
                            'type' => 'number',
                            'default_value' => 0,
                        ],
                        [
                            'key' => 'field_results_no',
                            'label' => 'No Votes',
                            'name' => 'no_votes',
                            'type' => 'number',
                            'default_value' => 0,
                        ],
                        [
                            'key' => 'field_results_abstain',
                            'label' => 'Abstentions',
                            'name' => 'abstain_votes',
                            'type' => 'number',
                            'default_value' => 0,
                        ],
                    ],
                ],
                [
                    'key' => 'field_proposal_attachments',
                    'label' => 'Attachments',
                    'name' => 'proposal_attachments',
                    'type' => 'repeater',
                    'sub_fields' => [
                        [
                            'key' => 'field_attachment_file',
                            'label' => 'File',
                            'name' => 'file',
                            'type' => 'file',
                            'return_format' => 'array',
                        ],
                        [
                            'key' => 'field_attachment_description',
                            'label' => 'Description',
                            'name' => 'description',
                            'type' => 'text',
                        ],
                    ],
                    'button_label' => 'Add Attachment',
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'villa_proposal',
                    ],
                ],
            ],
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
        ]);
    }
}