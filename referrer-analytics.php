<?php
/**
 * Referrer Analytics WordPress Plugin
 *
 * @package    ReferrerAnalytics
 * @subpackage WordPress
 * @since      1.3.0
 * @author     Ben Marshall
 * @copyright  2020 Ben Marshall
 * @license    GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Referrer Analytics
 * Plugin URI:        https://benmarshall.me/referrer-analytics
 * Description:       Track & store where your users came from for better reporting data in Google Analytics, conversion tracking & more. Make qualified decisions based on facts & figures, not conjecture.
 * Version:           1.3.0
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
      'host'   => false,
      'path'   => false
    ];

    // Get the basic referrer info
    $url = parse_url( $referrer_url );

    $referrer['url']    = $referrer_url;
    $referrer['scheme'] = ! empty( $url['scheme'] ) ? $url['scheme'] : false;
    $referrer['host']   = ! empty( $url['host'] ) ? $url['host'] : false;
    $referrer['path']   = ! empty( $url['path'] ) ? $url['path'] : false;

    // Get the host information
    $hosts = referrer_analytics_get_hosts();

    $found = false;
    foreach( $hosts as $host_key => $host ) {
      if ( $referrer['host'] === $host['host'] ) {
        $referrer = array_merge( $referrer, $host );
        $found = true;
        break;
      }
    }

    // Could not match with a defined host, check if raw host should still be tracked
    if (
      $found ||
      ! $found && 'enabled' === $options['track_all_referrers'] ) {
      return $referrer;
    }

    return false;
  }
}

/**
 * Returns an array of host types.
 */
if ( ! function_exists( 'referrer_analytics_get_hosts' ) ) {
  function referrer_analytics_get_hosts() {
    $options = referrer_analytics_options();
    $hosts   = [];

    // Check if all hosts should be tracked, if so add known
    if ( 'enabled' === $options['track_all_referrers'] ) {
      $known_hosts = referrer_analytics_get_known();
    }

    // Check to make sure at least one host is available
    if ( ! $options['hosts'] && ! $known_hosts ) {
      return false;
    }

    if( ! empty( $known_hosts ) ) {
      // Add known hosts to hosts list
      foreach( $known_hosts as $key => $host ) {
        $hosts[ $host['host'] ] = $host;
      }
    }

    if ( $options['hosts'] ) {
      // Override/add any pre-defined hosts
      foreach( $options['hosts'] as $key => $host ) {
        $hosts[ $host['host'] ] = $host;
      }
    }

    // Clean up any empty records
    foreach( $hosts as $key => $host ) {
      if ( empty( $host['host'] ) ) {
        unset( $hosts[ $key ] );
      }
    }

    if ( ! $hosts ) { return false; }

    return $hosts;
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
    $log = [ 'date' => current_time( 'mysql' ) ];

    $referrer = referrer_analytics_get_referrer();

    if ( ! $referrer ) { return false; }

    $current_url = referrer_analytics_current_url();

    foreach( $referrer as $key => $value ) {
      $log[ $key ] = $value;
    }

    $log['ip']          = referrer_analytics_get_ip();
    $log['userid']      = get_current_user_id();
    $log['destination'] = $current_url['full'];

    $file = fopen( referrer_analytics_get_log_file(), 'a' );
    fwrite( $file, json_encode( $log ) . "\n" );
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
    $file = referrer_analytics_get_log_file();

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
    wp_delete_file( referrer_analytics_get_log_file() );
  }
}

/**
 * Get log file
 */
if ( ! function_exists( 'referrer_analytics_get_log_file' ) ) {
  function referrer_analytics_get_log_file() {
    $wp_upload_dir = wp_upload_dir();
    $wp_upload_dir = $wp_upload_dir['basedir'];

    return $wp_upload_dir . '/referrer-analytics.log';
  }
}

/**
 * Get log
 */
if ( ! function_exists( 'referrer_analytics_get_log' ) ) {
  function referrer_analytics_get_log() {
    $log = [];

    $file = referrer_analytics_get_log_file();

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
if ( ! function_exists( 'referrer_analytics_parse_log' ) ) {
  function referrer_analytics_parse_log( $log ) {
    if ( ! $log ) { return false; }

    $parsed = [
      'referrers'    => [],
      'types'        => [],
      'destinations' => []
    ];

    foreach( $log as $key => $entry ) {
      $referrer_key = false;

      if ( ! empty( $entry['name'] ) ) {
        $referrer_key = 'name';
      } elseif( ! empty( $entry['host'] ) ) {
        $referrer_key = 'host';
      }

      if ( ! $referrer_key ) { continue; }

      if ( empty( $entry['type'] ) ) {
        $entry['type'] = 'N/A';
      }

      // Referrer
      if ( empty( $parsed['referrers'][ $entry[ $referrer_key ] ] ) ) {
        $parsed['referrers'][ $entry[ $referrer_key ] ]['count'] = 1;
        $parsed['referrers'][ $entry[ $referrer_key ] ]['url']   = ! empty( $entry['url'] ) ? $entry['url'] : false;
        $parsed['referrers'][ $entry[ $referrer_key ] ]['flag']  = ! empty( $entry['flag'] ) ? $entry['flag'] : false;
        $parsed['referrers'][ $entry[ $referrer_key ] ]['name']  = $entry[ $referrer_key ];
        $parsed['referrers'][ $entry[ $referrer_key ] ]['type']  = $entry['type'];
      } else {
        $parsed['referrers'][ $entry[ $referrer_key ] ]['count']++;
      }

      // Type
      if ( empty( $parsed['types'][ $entry['type'] ] ) ) {
        $parsed['types'][ $entry['type'] ] = 1;
      } else {
        $parsed['types'][ $entry['type'] ]++;
      }

      // Destination
      if ( empty( $parsed['destinations'][ $entry['destination'] ] ) ) {
        $parsed['destinations'][ $entry['destination'] ] = 1;
      } else {
        $parsed['destinations'][ $entry['destination'] ]++;
      }
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
 * Sync log with known referrers
 */
if ( ! function_exists( 'referrer_analytics_sync_log' ) ) {
  function referrer_analytics_sync_log() {
    $log   = referrer_analytics_get_log();
    $hosts = referrer_analytics_get_hosts();
    $file  = referrer_analytics_get_log_file();

    if ( ! $hosts ) { return false; }

    $new_log = '';
    foreach( $log as $key => $entry ) {
      $new_entry = $entry;

      foreach( $hosts as $k => $host ) {
        if ( $entry['host'] == $host['host'] ) {
          $new_entry['host'] = $host['host'];
          $new_entry['type'] = $host['type'];
          $new_entry['name'] = $host['name'];
          $new_entry['flag'] = ! empty( $host['flag'] ) ? $host['flag'] : false;
          $new_entry['url']  = ! empty( $host['url'] ) ? $host['url'] : false;
        }
      }

      $new_log .= json_encode( $new_entry ) . "\n";
    }

    $file = fopen( $file, 'w' );
    fwrite( $file, $new_log );
    fclose( $file );
  }
}

/**
 * Get known hosts
 */
if ( ! function_exists( 'referrer_analytics_get_known' ) ) {
  function referrer_analytics_get_known() {
    return [
      // Google
      [ 'host' => 'www.google.com', 'type' => 'organic', 'name' => 'Google', 'url' => 'https://www.google.com/' ],
      [ 'host' => 'www.google.ru', 'type' => 'organic', 'name' => 'Google (Russia)', 'url' => 'https://www.google.ru/' ],
      [ 'host' => 'www.google.fr', 'type' => 'organic', 'name' => 'Google (France)', 'url' => 'https://www.google.fr/' ],
      [ 'host' => 'www.google.in', 'type' => 'organic', 'name' => 'Google (India)', 'url' => 'https://www.google.in/' ],
      [ 'host' => 'www.google.co.in', 'type' => 'organic', 'name' => 'Google (India)', 'url' => 'https://www.google.in/' ],
      [ 'host' => 'www.google.co.uk', 'type' => 'organic', 'name' => 'Google (United Kingdom)', 'url' => 'https://www.google.co.uk/' ],
      [ 'host' => 'www.google.ch', 'type' => 'organic', 'name' => 'Google (Switzerland)', 'url' => 'https://www.google.ch/' ],
      [ 'host' => 'www.google.co.kr', 'type' => 'organic', 'name' => 'Google (South Korea)', 'url' => 'https://www.google.co.kr/' ],
      [ 'host' => 'www.google.co.th', 'type' => 'organic', 'name' => 'Google (Thailand)', 'url' => 'https://www.google.co.th/' ],
      [ 'host' => 'www.google.com.eg', 'type' => 'organic', 'name' => 'Google (Egypt)', 'url' => 'https://www.google.com.eg/' ],
      [ 'host' => 'www.google.com.ar', 'type' => 'organic', 'name' => 'Google (Argentina)', 'url' => 'https://www.google.com.ar/' ],
      [ 'host' => 'www.google.com.br', 'type' => 'organic', 'name' => 'Google (Brazil)', 'url' => 'https://www.google.com.br/' ],
      [ 'host' => 'www.google.ro', 'type' => 'organic', 'name' => 'Google (Romania)', 'url' => 'https://www.google.ro/' ],
      [ 'host' => 'www.google.com.au', 'type' => 'organic', 'name' => 'Google (Australia)', 'url' => 'https://www.google.com.au/' ],
      [ 'host' => 'www.google.dk', 'type' => 'organic', 'name' => 'Google (Denmark)', 'url' => 'https://www.google.dk/' ],
      [ 'host' => 'www.google.de', 'type' => 'organic', 'name' => 'Google (Germany)', 'url' => 'https://www.google.de/' ],

      // Bing
      [ 'host' => 'www.bing.com', 'type' => 'organic', 'name' => 'Bing', 'url' => 'https://www.bing.com/' ],
      [ 'host' => 'cn.bing.com', 'type' => 'organic', 'name' => 'Bing (China)', 'url' => 'https://www.bing.com/?mkt=zh-CN' ],

      // Yahoo
      [ 'host' => 'r.search.yahoo.com', 'type' => 'organic', 'name' => 'Yahoo', 'url' => 'https://www.yahoo.com/' ],
      [ 'host' => 'search.yahoo.com', 'type' => 'organic', 'name' => 'Yahoo', 'url' => 'https://www.yahoo.com/' ],
      [ 'host' => 'fr.search.yahoo.com', 'type' => 'organic', 'name' => 'Yahoo (France)', 'url' => 'https://fr.search.yahoo.com/' ],
      [ 'host' => 'uk.search.yahoo.com', 'type' => 'organic', 'name' => 'Yahoo (United Kingdom)', 'url' => 'https://uk.search.yahoo.com/' ],

      // Other search engines
      [ 'host' => 'duckduckgo.com', 'type' => 'organic', 'name' => 'DuckDuckGo', 'url' => 'https://duckduckgo.com/' ],
      [ 'host' => 'baidu.com', 'type' => 'organic', 'name' => 'Baidu', 'url' => 'http://www.baidu.com/' ],
      [ 'host' => 'www.ecosia.org', 'type' => 'organic', 'name' => 'Ecosia', 'url' => 'https://www.ecosia.org/' ],
      [ 'host' => 'www.qwant.com', 'type' => 'organic', 'name' => 'Qwant', 'url' => 'https://www.qwant.com/' ],

      // Others
      [ 'host' => 'site.ru', 'type' => 'bot', 'name' => 'site.ru', 'flag' => true ],
      [ 'host' => 'css-tricks.com', 'type' => 'backlink', 'name' => 'CSS-Tricks', 'url' => 'https://css-tricks.com/' ],
      [ 'host' => 'lurkmore.to', 'type' => 'backlink', 'name' => 'Lurkmore', 'url' => 'https://lurkmore.to/' ],
      [ 'host' => 'drupalsun.com', 'type' => 'backlink', 'name' => 'Drupal Sun', 'url' => 'https://drupalsun.com/' ],
      [ 'host' => 'cdpn.io', 'type' => 'backlink', 'name' => 'CodePen', 'url' => 'https://codepen.io/' ],
      [ 'host' => 'amzn.to', 'type' => 'backlink', 'name' => 'Amazon', 'url' => 'https://www.amazon.com/' ],
      [ 'host' => 'jobsnearme.online', 'type' => 'backlink', 'name' => 'Jobs Near Me', 'url' => 'https://jobsnearme.online/' ],
      [ 'host' => 'www.entermedia.com', 'type' => 'backlink', 'name' => 'Entermedia, LLC.', 'url' => 'https://www.entermedia.com/' ],
      [ 'host' => 'forum.bubble.io', 'type' => 'backlink', 'name' => 'Bubble Forum', 'url' => 'https://forum.bubble.io/' ],
      [ 'host' => 'www.benmarshall.me', 'type' => 'backlink', 'name' => 'Ben Marshall', 'url' => 'https://benmarshall.me' ],
      [ 'host' => 'github.com', 'type' => 'backlink', 'name' => 'GitHub', 'url' => 'https://github.com/' ],
      [ 'host' => 'wordpress.org', 'type' => 'backlink', 'name' => 'WordPress', 'url' => 'https://wordpress.org/' ],
      [ 'host' => 'school.nextacademy.com', 'type' => 'backlink', 'name' => 'NEXT Academy', 'url' => 'https://school.nextacademy.com/' ],
      [ 'host' => 'www.soliddigital.com', 'type' => 'backlink', 'name' => 'Solid Digital', 'url' => 'https://www.soliddigital.com/' ],
      [ 'host' => 'www.benellile.com', 'type' => 'backlink', 'name' => 'Benlli', 'url' => 'https://www.benellile.com/' ],
    ];
  }
}
