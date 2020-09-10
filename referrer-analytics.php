<?php
/**
 * Referrer Analytics WordPress Plugin
 *
 * @package    ReferrerAnalytics
 * @subpackage WordPress
 * @since      1.0.0
 * @author     Ben Marshall
 * @copyright  2020 Ben Marshall
 * @license    GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Referrer Analytics
 * Plugin URI:        https://benmarshall.me/referrer-analytics
 * Description:       Track & store where your users came from for better reporting data in Google Analytics, conversion tracking & more. Make qualified decisions based on facts & figures, not conjecture.
 * Version:           2.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Ben Marshall
 * Author URI:        https://benmarshall.me
 * Text Domain:       referreranalytics
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Security Note: Blocks direct access to the plugin PHP files.
defined( 'ABSPATH' ) || die();

// Define plugin constants.
define( 'REFERRER_ANALYTICS', __FILE__ );
define( 'REFERRER_ANALYTICS_DB_VERSION', '1.0' );
define( 'REFERRER_ANALYTICS_VERSION', '2.0.1' );
define( 'REFERRER_ANALYTICS_REDIRECT_PARAM', 'referrer-analytics' );

/**
 * Include the predefined referrers helper function.
 */
require plugin_dir_path( REFERRER_ANALYTICS ) . 'inc/predefined-hosts.php';

/**
 * Include the Referrer Analytics class.
 */
require plugin_dir_path( REFERRER_ANALYTICS ) . 'classes/class-referrer-analytics.php';

// Initialize the plugin.
$referrer_analytics = new Referrer_Analytics();
