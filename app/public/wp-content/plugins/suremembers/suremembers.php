<?php
/**
 * Plugin Name: SureMembers
 * Plugin URI: https://surecart.com/
 * Description: A simple yet powerful way to add content restriction to your website.
 * Author: SureCart
 * Author URI: https://surecart.com
 * Version: 1.10.9
 * License: GPL v2
 * Text Domain: suremembers
 *
 * @package suremembers
 */

/**
 * Set constants
 */
define( 'SUREMEMBERS_FILE', __FILE__ );
define( 'SUREMEMBERS_BASE', plugin_basename( SUREMEMBERS_FILE ) );
define( 'SUREMEMBERS_DIR', plugin_dir_path( SUREMEMBERS_FILE ) );
define( 'SUREMEMBERS_URL', plugins_url( '/', SUREMEMBERS_FILE ) );
define( 'SUREMEMBERS_POST_TYPE', 'wsm_access_group' );
define( 'SUREMEMBERS_POST_META', 'suremembers_post_access_group' );
define( 'SUREMEMBERS_USER_META', 'suremembers_user_access_group' );
define( 'SUREMEMBERS_USER_EXPIRATION', 'suremembers_user_expiration' );
define( 'SUREMEMBERS_PLAN_PRIORITY', 'suremembers_plan_priority' );
define( 'SUREMEMBERS_PLAN_EXPIRATION', 'suremembers_plan_expiration' );
define( 'SUREMEMBERS_PLAN_ACTIVE_USERS', 'suremembers_plan_active_users' );
define( 'SUREMEMBERS_REQUIRES_QUERY', 'suremembers_requires_users_fetch_query' );
define( 'SUREMEMBERS_PLAN_RULES', 'suremembers_plan_rules' );
define( 'SUREMEMBERS_PLAN_INCLUDE', 'suremembers_plan_include' );
define( 'SUREMEMBERS_PLAN_EXCLUDE', 'suremembers_plan_exclude' );
define( 'SUREMEMBERS_PLAN_DRIPS', 'suremembers_plan_drips' );
define( 'SUREMEMBERS_ACCESS_GROUPS', 'suremembers_access_groups' );
define( 'SUREMEMBERS_MENU_USER_CONDITION', 'suremembers_menu_user_condition' );
define( 'SUREMEMBERS_ARCHIVE', 'suremembers_archive' );
define( 'SUREMEMBERS_RESTRICTED_URL', 'suremembers_restricted_url' );
define( 'SUREMEMBERS_USER_ROLES', 'suremembers_user_roles' );
define( 'SUREMEMBERS_REDIRECT_RULES', 'suremembers_redirect_rules' );
define( 'SUREMEMBERS_LOGIN_FORM_SETTINGS', 'suremembers_login_form_settings' );
define( 'SUREMEMBERS_LOGIN_RESTRICTIONS_SETTINGS', 'suremembers_login_restrictions_settings' );
define( 'SUREMEMBERS_CUSTOM_CONTENT', 'suremembers_custom_content' );
define( 'SUREMEMBERS_ACCESS_GROUP_DOWNLOADS', 'suremembers_access_group_downloads' );
define( 'SUREMEMBERS_ADMIN_SETTINGS', 'suremembers_admin_settings' );
define( 'SUREMEMBERS_WEBHOOK_ENDPOINTS', 'suremembers_webhook_endpoints' );
define( 'SUREMEMBERS_EMAIL_TEMPLATE_SETTINGS', 'suremembers_email_template_settings' );
define( 'SUREMEMBERS_PUBLIC_KEY', 'pt_EU5YidZvDzcpHqvC9Mk1g2uu' );

define( 'SUREMEMBERS_VER', '1.10.9' );

require_once 'plugin-loader.php';
