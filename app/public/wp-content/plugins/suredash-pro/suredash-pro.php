<?php
/**
 * Plugin Name: SureDash Business
 * Plugin URI: https://suredash.com/
 * Description: Extend the functionality of SureDash with SureDash Pro.
 * Author: SureDash
 * Author URI: https://suredash.com/
 * Version: 1.0.0-rc.3
 * License: GPL v2
 * Requires Plugins: suredash
 * Text Domain: suredash-pro
 * Domain Path: /languages
 *
 * @package SureDashboardPro
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Set constants
 */
define( 'SUREDASH_PRO_VER', '1.0.0-rc.3' );
define( 'SUREDASH_PRO_FILE', __FILE__ );
define( 'SUREDASH_PRO_PRODUCT', 'SureDash Business' );
define( 'SUREDASH_PRO_PUBLIC_TOKEN', 'pt_fWVEaXPGhXWbgritPFczdscV' );
define( 'SUREDASH_PRO_PRODUCT_ID', 'a53c48d0-01c7-4d7b-8997-3c4761aebd65' );
define( 'SUREDASH_FREE_MINIMUM_VER', '1.0.0-rc.3' );

/**
 * Load the plugin
 */
require_once 'loader.php';
