<?php
/**
 * Referrer Analytics WordPress Plugin
 *
 * @package    ReferrerAnalytics
 * @subpackage WordPress
 * @since      1.4.0
 * @author     Ben Marshall
 * @copyright  2020 Ben Marshall
 * @license    GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Referrer Analytics
 * Plugin URI:        https://benmarshall.me/referrer-analytics
 * Description:       Track & store where your users came from for better reporting data in Google Analytics, conversion tracking & more. Make qualified decisions based on facts & figures, not conjecture.
 * Version:           1.4.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Ben Marshall
 * Author URI:        https://benmarshall.me
 * Text Domain:       referreranalytics
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

/**
 * Security Note: Blocks direct access to the plugin PHP files.
 */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Define plugin constants
define( 'REFERRER_ANALYTICS', __FILE__ );
define( 'REFERRER_ANALYTICS_DB_VERSION', '1.0' );

/**
 * Install plugin tables
 */
function referrer_analytics_install() {
  global $wpdb;

  $charset_collate      = $wpdb->get_charset_collate();
  $installed_db_version = get_option( 'referrer_analytics_db_version' );

  if ( $installed_db_version != REFERRER_ANALYTICS_DB_VERSION ) {
    $table_name = $wpdb->prefix . 'referrer_analytics';

    $sql = "CREATE TABLE $table_name (
      referrer_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      date_recorded DATETIME NOT NULL,
      referrer_url VARCHAR(255) NOT NULL,
      referrer_primary_url VARCHAR(255) NOT NULL,
      referrer_host VARCHAR(255) NOT NULL,
      referrer_type VARCHAR(255) NOT NULL,
      referrer_name VARCHAR(255) NOT NULL,
      visitor_ip VARCHAR(255) NOT NULL,
      user_id BIGINT NOT NULL,
      url_destination VARCHAR(255) NOT NULL,
      is_flagged BOOLEAN NOT NULL DEFAULT FALSE,
      PRIMARY KEY (`referrer_id`)) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    update_option( 'referrer_analytics_db_version', REFERRER_ANALYTICS_DB_VERSION );
  }
}
register_activation_hook( __FILE__, 'referrer_analytics_install' );

/**
 * Check to ensure the database tables have been installed
 */
function referrer_analytics_db_check() {
  if ( get_site_option( 'referrer_analytics_db_version' ) != REFERRER_ANALYTICS_DB_VERSION ) {
    referrer_analytics_install();
  }
}
add_action( 'plugins_loaded', 'referrer_analytics_db_check' );

/**
 * Plugin scripts
 */
require plugin_dir_path( REFERRER_ANALYTICS ) . '/inc/scripts.php';

/**
 * Plugin helpers
 */
require plugin_dir_path( REFERRER_ANALYTICS ) . '/inc/helpers.php';

/**
 * Admin interface
 */
require plugin_dir_path( REFERRER_ANALYTICS ) . '/inc/admin.php';

/**
 * Handles setting cookies
 */
add_action( 'init', function() {
  // Only fire on the frontend & not after a redirect
  if ( is_admin() || ! empty( $_REQUEST['redirect'] ) && 1 == $_REQUEST['redirect'] ) { return; }

  $options     = referrer_analytics_options();
  $referrer    = referrer_analytics_get_referrer();
  $current_url = referrer_analytics_current_url();

  // Check if it came from the same host, if so ignore
  if ( ! $referrer || $current_url['host'] == $referrer['host'] ) { return; }

  // Check if referrer data should be stored in cookies
  if ( 'enabled' === $options['store_cookies'] ) {
    setcookie(
      'referrer-analytics-referrer_destination',
      $current_url['full'],
      current_time( 'timestamp' ) + ( $options['cookie_expiration'] * DAY_IN_SECONDS ),
      COOKIEPATH,
      COOKIE_DOMAIN
    );

    foreach( $referrer as $key => $value ) {
      setcookie(
        'referrer-analytics-referrer_' . $key,
        $value,
        current_time( 'timestamp' ) + ( $options['cookie_expiration'] * DAY_IN_SECONDS ),
        COOKIEPATH,
        COOKIE_DOMAIN
      );
    }
  }
});

/**
 * Handles redirections for UTM data
 */
add_action( 'template_redirect', function() {
  // Only process on frontend pages & not after a redirect
  if ( is_admin() || (  ! empty( $_REQUEST['redirect'] ) && 1 == $_REQUEST['redirect'] ) ) { return; }

  global $wp;
  $options     = referrer_analytics_options();
  $current_url = referrer_analytics_current_url();
  $referrer    = referrer_analytics_get_referrer();

  if ( 'enabled' === $options['logging'] && $current_url['host'] != $referrer['host'] ) {
    referrer_analytics_log();
  }

  // Check if user should be redirected with UTM data
  if (
    'enabled' === $options['redirect_with_utm'] &&
    ! empty( $referrer['host'] ) &&
    $current_url['host'] != $referrer['host']
  ) {
    // Add the UTM parameters
    if ( ! empty( $current_url['query'] ) ) {
      // Check if utm_source is already provided, if so, ignore the referrer.
      if (
        empty( $current_url['query']['utm_source'] ) &&
        'ignore' != $options['utm_source'] &&
        ! empty( $referrer[ $options['utm_source'] ] )
      ) {
        $current_url['query']['utm_source'] = $referrer[ $options['utm_source'] ];
      }

      // Check if utm_medium is already provided, if so, ignore the referrer.
      if (
        empty( $current_url['query']['utm_medium'] ) &&
        'ignore' != $options['utm_medium'] &&
        ! empty( $referrer[ $options['utm_medium'] ] )
      ) {
        $current_url['query']['utm_medium'] = $referrer[ $options['utm_medium'] ];
      }

      // Check if utm_campaign is already provided, if so, ignore the referrer.
      if (
        empty( $current_url['query']['utm_campaign'] ) &&
        'ignore' != $options['utm_campaign'] &&
        ! empty( $referrer[ $options['utm_campaign'] ] )
      ) {
        $current_url['query']['utm_campaign'] = $referrer[ $options['utm_campaign'] ];
      }
    } else {
      $current_url['query'] = [];
      if ( 'ignore' != 'utm_source' && ! empty( $referrer[ $options['utm_source'] ] ) ) {
        $current_url['query']['utm_source'] = $referrer[ $options['utm_source'] ];
      }

      if ( 'ignore' != 'utm_medium' && ! empty( $referrer[ $options['utm_medium'] ] ) ) {
        $current_url['query']['utm_medium'] = $referrer[ $options['utm_medium'] ];
      }

      if ( 'ignore' != 'utm_campaign' && ! empty( $referrer[ $options['utm_campaign'] ] ) ) {
        $current_url['query']['utm_campaign'] = $referrer[ $options['utm_campaign'] ];
      }
    }

    // Only redirect is URL params have been added
    if ( ! $current_url['query'] ) { return; }

    // Add to avoid redirect loops & doubling logging for 1 visits
    $current_url['query']['redirect'] = 1;

    // Redirect the user with the new UTM values
    $redirect_url = $current_url['scheme'] . '://' . $current_url['host'] . $current_url['path'] . '?' . http_build_query( $current_url['query'] );

    wp_redirect( $redirect_url );
    exit;
  }
});

add_filter( 'wp_targeted_link_rel', function( $rel_values ) {
  $options = referrer_analytics_options();

  if ( 'enabled' == $options['disable_noreferrer'] ) {
    return str_replace( 'noreferrer', '', $rel_values );
  }

  return $rel_values;
});

/**
 * Returns the current URL
 */
if ( ! function_exists( 'referrer_analytics_current_url' ) ) {
  function referrer_analytics_current_url() {
    $url = [];

    $url['full']     = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $url = array_merge( $url, parse_url( $url['full'] ) );

    // Parse the URL query string
    if ( ! empty( $url['query'] ) ) {
      parse_str( $url['query'], $url['query'] );
    }

    return $url;
  }
}

/**
 * Get user's IP
 */
if ( ! function_exists( 'referrer_analytics_get_ip' ) ) {
  function referrer_analytics_get_ip() {
    $ip = false;

    if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
      $ip = $_SERVER['REMOTE_ADDR'];
    }

    // Strange instance of some servers returning a duplicate comma seperated IP
    // address like Pantheon.
    if ( strpos( $ip, ',' ) ) {
      $ip_ary = explode( ',', $ip );
      $ip     = $ip_ary[0];
    }

    return trim( $ip );
  }
}
