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
 * Description:       Track & store where you users came from for better reporting data in Google Analytics, conversion tracking & more. Make qualified decisions based on facts & figures, not conjecture.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Ben Marshall
 * Author URI:        https://benmarshall.me
 * Text Domain:       referreranalytics
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

define( 'REFERRER_ANALYTICS', __FILE__ );

/**
 * Plugin scripts
 */
require plugin_dir_path( REFERRER_ANALYTICS ) . '/inc/scripts.php';

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
  if ( ! $referrer || $current_url['host'] == $referrer['host']['raw'] ) { return; }

  // Check if referrer data should be stored in cookies
  if ( 'enabled' === $options['store_cookies'] ) {
    setcookie(
      'referrer_analytics_referrer_destination',
      $current_url['full'],
      current_time( 'timestamp' ) + ( $options['cookie_expiration'] * DAY_IN_SECONDS ),
      COOKIEPATH,
      COOKIE_DOMAIN
    );

    foreach( $referrer as $key => $value ) {
      if ( is_array( $value ) ) {
        foreach( $value as $k => $v ) {
          setcookie(
            'referrer_analytics_referrer_' . $k,
            $v,
            current_time( 'timestamp' ) + ( $options['cookie_expiration'] * DAY_IN_SECONDS ),
            COOKIEPATH,
            COOKIE_DOMAIN
          );
        }
      } else {
        setcookie(
          'referrer_analytics_referrer_' . $key,
          $value,
          current_time( 'timestamp' ) + ( $options['cookie_expiration'] * DAY_IN_SECONDS ),
          COOKIEPATH,
          COOKIE_DOMAIN
        );
      }
    }
  }
});

/**
 * Handles redirections for UTM data
 */
add_action( 'template_redirect', function() {
  // Only process on frontend pages & not after a redirect
  if ( is_admin() || 1 == $_REQUEST['redirect'] ) { return; }

  global $wp;
  $options     = referrer_analytics_options();
  $current_url = referrer_analytics_current_url();
  $referrer    = referrer_analytics_get_referrer();

  if ( 'enabled' === $options['logging'] && $current_url['host'] != $referrer['host']['raw'] ) {
    referrer_analytics_log();
  }

  // Check if user should be redirected with UTM data
  if (
    'enabled' === $options['redirect_with_utm'] &&
    ! empty( $referrer['host']['host'] ) &&
    $current_url['host'] != $referrer['host']['raw']
  ) {
    // Add the UTM parameters
    if ( ! empty( $current_url['query'] ) ) {
      // Check if utm_source is already provided, if so, ignore the referrer.
      if (
        empty( $current_url['query']['utm_source'] ) &&
        'ignore' != $options['utm_source'] &&
        ! empty( $referrer['host'][ $options['utm_source'] ] )
      ) {
        $current_url['query']['utm_source'] = $referrer['host'][ $options['utm_source'] ];
      }

      // Check if utm_medium is already provided, if so, ignore the referrer.
      if (
        empty( $current_url['query']['utm_medium'] ) &&
        'ignore' != $options['utm_medium'] &&
        ! empty( $referrer['host'][ $options['utm_medium'] ] )
      ) {
        $current_url['query']['utm_medium'] = $referrer['host'][ $options['utm_medium'] ];
      }

      // Check if utm_campaign is already provided, if so, ignore the referrer.
      if (
        empty( $current_url['query']['utm_campaign'] ) &&
        'ignore' != $options['utm_campaign'] &&
        ! empty( $referrer['host'][ $options['utm_campaign'] ] )
      ) {
        $current_url['query']['utm_campaign'] = $referrer['host'][ $options['utm_campaign'] ];
      }
    } else {

      $current_url['query'] = [];
      if ( 'ignore' != 'utm_source' && ! empty( $referrer['host'][ $options['utm_source'] ] ) ) {
        $current_url['query']['utm_source'] = $referrer['host'][ $options['utm_source'] ];
      }

      if ( 'ignore' != 'utm_medium' && ! empty( $referrer['host'][ $options['utm_medium'] ] ) ) {
        $current_url['query']['utm_medium'] = $referrer['host'][ $options['utm_medium'] ];
      }

      if ( 'ignore' != 'utm_campaign' && ! empty( $referrer['host'][ $options['utm_campaign'] ] ) ) {
        $current_url['query']['utm_campaign'] = $referrer['host'][ $options['utm_campaign'] ];
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

/**
 * Parses a referrer URL.
 */
if ( ! function_exists( 'referrer_analytics_get_referrer' ) ) {
  function referrer_analytics_get_referrer() {
    $options      = referrer_analytics_options();
    $referrer_url = ! empty( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : false;

    // No referrer found.
    if ( ! $referrer_url ) { return false; }

    $referrer = [
      'url'    => false,
      'scheme' => false,
      'host'   => [
        'raw' => false
      ],
      'path'   => false
    ];

    // Get the basic referrer info
    $url = parse_url( $referrer_url );

    $referrer['url']         = $referrer_url;
    $referrer['scheme']      = ! empty( $url['scheme'] ) ? $url['scheme'] : false;
    $referrer['host']['raw'] = ! empty( $url['host'] ) ? $url['host'] : false;
    $referrer['path']        = ! empty( $url['path'] ) ? $url['path'] : false;

    // Get the host information
    $hosts = referrer_analytics_referrers();

    // If no defined referrer hosts, check if raw host should still be tracked
    if ( ! $hosts && 'enabled' === $options['track_all_referrers'] ) {
      $referrer['host']['host'] = $referrer['host']['raw'];
    } else {
      $found = false;
      foreach( $hosts as $key => $host ) {
        if ( false !== strpos( $referrer['host']['raw'], $host['host'] ) ) {
          $referrer['host']['host'] = $host['host'];
          $referrer['host'] = array_merge( $referrer['host'], $host );
          $found = true;
          break;
        }
      }

      // Could not match with a defined host, check if raw host should still be tracked
      if ( ! $found && 'enabled' === $options['track_all_referrers'] ) {
        $referrer['host']['host'] = $referrer['host']['raw'];
      } elseif ( ! $found ) {
        return false;
      }
    }

    return $referrer;
  }
}

/**
 * Returns an array of host types.
 */
if ( ! function_exists( 'referrer_analytics_referrers' ) ) {
  function referrer_analytics_referrers() {
    $options = referrer_analytics_options();

    // Check if all hosts should be tracked, if so add known
    if ( 'enabled' === $options['track_all_referrers'] ) {
      $options['hosts'] = referrer_analytics_get_known();
    }

    // Check to make sure at least one host is available
    if ( ! $options['hosts'] ) {
      return false;
    }

    // Clean up any empty records
    $has_hosts = false;
    foreach( $options['hosts'] as $key => $value ) {
      if ( ! empty( $value['host'] ) ) {
        $has_hosts = true;
      } else {
        unset( $options['hosts'][ $key ] );
      }
    }

    if ( ! $has_hosts ) { return false; }

    return $options['hosts'];
  }
}

/**
 * Get plugin options
 */
if ( ! function_exists( 'referrer_analytics_options' ) ) {
  function referrer_analytics_options() {
    $options = get_option( 'referrer_analytics_options' );

    if ( empty( $options['utm_source'] ) ) { $options['utm_source'] = 'host'; }
    if ( empty( $options['utm_medium'] ) ) { $options['utm_medium'] = 'type'; }
    if ( empty( $options['utm_campaign'] ) ) { $options['utm_campaign'] = 'name'; }

    if ( empty( $options['cookie_expiration'] ) ) { $options['cookie_expiration'] = 30; }
    if ( empty( $options['track_all_referrers'] ) ) { $options['track_all_referrers'] = 'disabled'; }
    if ( empty( $options['redirect_with_utm'] ) ) { $options['redirect_with_utm'] = 'disabled'; }
    if ( empty( $options['store_cookies'] ) ) { $options['store_cookies'] = 'disabled'; }
    if ( empty( $options['logging'] ) ) { $options['logging'] = 'disabled'; }
    if ( empty( $options['hosts'] ) ) { $options['hosts'] = []; }

    return $options;
  }
}

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
 * Logs data
 */
if ( ! function_exists( 'referrer_analytics_log' ) ) {
  function referrer_analytics_log() {
    $wp_upload_dir = wp_upload_dir();
    $wp_upload_dir = $wp_upload_dir['basedir'];

    $log = [ 'date' => current_time( 'mysql' ) ];

    $referrer = referrer_analytics_get_referrer();
    if ( ! $referrer ) { return false; }

    $current_url = referrer_analytics_current_url();

    foreach( $referrer as $key => $value ) {
      if ( is_array( $value ) ) {
        foreach( $value as $k => $v ) {
          $log[ $k ] = $v;
        }
      } else {
        $log[ $key ] = $value;
      }
    }

    $log['ip']          = referrer_analytics_get_ip();
    $log['userid']      = get_current_user_id();
    $log['destination'] = $current_url['full'];

    $file = fopen( $wp_upload_dir . '/referrer-analytics.log', 'a' );
    fwrite( $file, "\n" . json_encode( $log ) );
    fclose( $file );
  }
}

/**
 * Get user's IP
 */
if ( ! function_exists( 'referrer_analytics_get_ip' ) ) {
  function referrer_analytics_get_ip() {
    if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
      return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
      return $_SERVER['REMOTE_ADDR'];
    }

    return false;
  }
}

/**
 * Get log file size
 */
if ( ! function_exists( 'referrer_analytics_log_size' ) ) {
  function referrer_analytics_log_size() {
    $wp_upload_dir = wp_upload_dir();
    $wp_upload_dir = $wp_upload_dir['basedir'];

    $file = $wp_upload_dir . '/referrer-analytics.log';

    if ( file_exists( $file ) ) {
      return referrer_analytics_bytes( filesize( $file ) );
    }

    return;
  }
}

/**
 * Delete log
 */
if ( ! function_exists( 'referrer_analytics_delete_log' ) ) {
  function referrer_analytics_delete_log() {
    $wp_upload_dir = wp_upload_dir();
    $wp_upload_dir = $wp_upload_dir['basedir'];

    $file = $wp_upload_dir . '/referrer-analytics.log';
    wp_delete_file( $file );
  }
}

/**
 * Get log
 */
if ( ! function_exists( 'referrer_analytics_get_log' ) ) {
  function referrer_analytics_get_log() {
    $log = [];

    $wp_upload_dir = wp_upload_dir();
    $wp_upload_dir = $wp_upload_dir['basedir'];

    $file = $wp_upload_dir . '/referrer-analytics.log';

    if ( file_exists( $file ) ) {
      $contents = file_get_contents( $file );
      $entries  = explode( "\n", $contents );

      foreach( $entries as $key => $entry ) {
        if ( ! $entry ) { continue; }

        $log[] = json_decode( $entry, true );
      }
    }

    return $log;
  }
}

/**
 * Parsed log
 */
if ( ! function_exists( 'referrer_analytics_parsed_log' ) ) {
  function referrer_analytics_parsed_log( $log ) {
    if ( ! $log ) { return false; }

    $parsed = [
      'charts' => [
        'referrers' => [
          'labels' => [],
          'data'   => [],
          'colors' => []
        ],
        'type' => [
          'labels' => [],
          'data'   => [],
          'colors' => []
        ]
      ]
    ];

    foreach( $log as $key => $entry ) {
      $referrer_key = false;

      if ( ! empty( $entry['name'] ) ) {
        $referrer_key = 'name';
      } elseif( ! empty( $entry['host'] ) ) {
        $referrer_key = 'host';
      } elseif( ! empty( $entry['raw'] ) ) {
        $referrer_key = 'raw';
      }

      if ( empty( $entry['type'] ) ) {
        $entry['type'] = 'N/A';
      }

      if ( ! $referrer_key ) { continue; }

      // Set labels
      if ( ! in_array( $entry[ $referrer_key ], $parsed['charts']['referrers']['labels'] ) ) {
        $parsed['charts']['referrers']['labels'][] = $entry[ $referrer_key ];
      }

      if ( ! in_array( $entry['type'], $parsed['charts']['type']['labels'] ) ) {
        $parsed['charts']['type']['labels'][] = $entry['type'];
      }

      // Set data
      if ( ! empty( $parsed['charts']['referrers']['data'][ $entry[ $referrer_key ] ] ) ) {
        $parsed['charts']['referrers']['data'][ $entry[ $referrer_key ] ]++;
      } else {
        $parsed['charts']['referrers']['data'][ $entry[ $referrer_key ] ] = 1;
      }

      if ( ! empty( $parsed['charts']['type']['data'][ $entry['type'] ] ) ) {
        $parsed['charts']['type']['data'][ $entry['type'] ]++;
      } else {
        $parsed['charts']['type']['data'][ $entry['type'] ] = 1;
      }
    }

    // Set colors for graphs
    for ( $i = 1; $i <= count( $parsed['charts']['referrers']['labels'] ); $i++ ) {
      $parsed['charts']['referrers']['colors'][] = sprintf("#%06x",rand(0,16777215));
    }

    for ( $i = 1; $i <= count( $parsed['charts']['type']['labels'] ); $i++ ) {
      $parsed['charts']['type']['colors'][] = sprintf("#%06x",rand(0,16777215));
    }

    return $parsed;
  }
}

/**
 * Bytes to readable
 */
if ( ! function_exists( 'referrer_analytics_bytes' ) ) {
  function referrer_analytics_bytes( $bytes, $unit = false ) {
    if( ( ! $unit && $bytes >= 1<<30) || $unit == 'GB' ) {
      return number_format( $bytes / ( 1<<30 ), 2 ) . 'GB';
    }

    if( (!$unit && $bytes >= 1<<20) || $unit == 'MB') {
      return number_format( $bytes / (1<<20 ), 2 ) . 'MB';
    }

    if( (!$unit && $bytes >= 1<<10) || $unit == 'KB') {
      return number_format( $bytes / (1<<10 ), 2 ) . 'KB';
    }

    return number_format( $bytes ).' bytes';
  }
}

/**
 * Get known hosts
 */
if ( ! function_exists( 'referrer_analytics_get_known' ) ) {
  function referrer_analytics_get_known() {
    return [
      // Search engines
      'google.com'         => [ 'host' => 'google.com', 'type' => 'organic', 'name' => 'Google' ],
      'www.google.com'     => [ 'host' => 'google.com', 'type' => 'organic', 'name' => 'Google' ],
      'google.dk'          => [ 'host' => 'google.dk', 'type' => 'organic', 'name' => 'Google (DK)' ],
      'www.google.dk'      => [ 'host' => 'google.dk', 'type' => 'organic', 'name' => 'Google (DK)' ],
      'google.es'          => [ 'host' => 'google.es', 'type' => 'organic', 'name' => 'Google (ES)' ],
      'www.google.es'      => [ 'host' => 'google.es', 'type' => 'organic', 'name' => 'Google (ES)' ],
      'bing.com'           => [ 'host' => 'bing.com', 'type' => 'organic', 'name' => 'Bing' ],
      'www.bing.com'       => [ 'host' => 'bing.com', 'type' => 'organic', 'name' => 'Bing' ],
      'cn.bing.com'        => [ 'host' => 'bing.com', 'type' => 'organic', 'name' => 'Bing (CN)' ],
      'yahoo.com'          => [ 'host' => 'yahoo.com', 'type' => 'organic', 'name' => 'Yahoo' ],
      'www.yahoo.com'      => [ 'host' => 'yahoo.com', 'type' => 'organic', 'name' => 'Yahoo' ],
      'search.yahoo.com'   => [ 'host' => 'yahoo.com', 'type' => 'organic', 'name' => 'Yahoo' ],
      'duckduckgo.com'     => [ 'host' => 'duckduckgo.com', 'type' => 'organic', 'name' => 'DuckDuckGo' ],
      'www.duckduckgo.com' => [ 'host' => 'duckduckgo.com', 'type' => 'organic', 'name' => 'DuckDuckGo' ],
      'qwant.com'          => [ 'host' => 'qwant.com', 'type' => 'organic', 'name' => 'Qwant' ],
      'www.qwant.com'      => [ 'host' => 'qwant.com', 'type' => 'organic', 'name' => 'Qwant' ],
      'ecosia.com'         => [ 'host' => 'ecosia.com', 'type' => 'organic', 'name' => 'Ecosia' ],
      'www.ecosia.com'     => [ 'host' => 'ecosia.com', 'type' => 'organic', 'name' => 'Ecosia' ],

      // Websites
      'csstricks.com' => [ 'host' => 'csstricks.com', 'type' => 'backlink', 'name' => 'CSS-Tricks' ],

      // Social media
      'facebook.com'   => [ 'host' => 'facebook.com', 'type' => 'social', 'name' => 'Facebook' ],
      'l.facebook.com' => [ 'host' => 'facebook.com', 'type' => 'social', 'name' => 'Facebook' ],
    ];
  }
}
