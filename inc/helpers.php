<?php
/**
 * Plugin helpers
 *
 * @package ReferrerAnalytics
 * @since 1.3.1
 */

/**
 * Syncs the referrers database with known & defined referrers
 */
if ( ! function_exists( 'referrer_analytics_sync_log' ) ) {
  function referrer_analytics_sync_log() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'referrer_analytics';
    $log        = referrer_analytics_get_log();
    $referrers  = referrer_analytics_get_referrers();

    // No known & defined hosts available
    if ( ! $referrers ) { return false; }

    foreach( $log as $key => $entry ) {
      $found_match = false;

      foreach( $referrers as $k => $referrer ) {
        if( $referrer['host'] == $entry->referrer_host ) {
          // Match found, update accordingly
          $updates = [];

          // Check referrer type
          if( $entry->referrer_type != $referrer['type'] ) {
            $updates['referrer_type'] = $referrer['type'];
          }

          // Check referrer name
          if( $entry->referrer_name != $referrer['name'] ) {
            $updates['referrer_name'] = $referrer['name'];
          }

          // Check referrer primary url
          if( $entry->referrer_primary_url != $referrer['primary_url'] ) {
            $updates['referrer_primary_url'] = $referrer['primary_url'];
          }

          // If there are any updates, update the database record
          if ( $updates ) {
            $wpdb->update( $table_name, $updates, [ 'referrer_id' => $entry->referrer_id ] );
          }

          $found_match = true;

          continue;
        }
      }

      // If no match found, clear the custom fields
      if ( ! $found_match ) {
        $wpdb->update( $table_name, [
          'referrer_type'        => '',
          'referrer_name'        => '',
          'referrer_primary_url' => ''
        ], [ 'referrer_id' => $entry->referrer_id ] );
      }
    }
  }
}

/**
 * Deletes an entry or everything from the referrers database
 */
if ( ! function_exists( 'referrer_analytics_delete_log' ) ) {
  function referrer_analytics_delete_log() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'referrer_analytics';

    $wpdb->query( "TRUNCATE TABLE $table_name" );
  }
}

/**
 * Returns a parsed log
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

      if ( ! empty( $entry->referrer_name ) ) {
        $entry_key = $entry->referrer_name;
      } elseif( ! empty( $enty->referrer_host ) ) {
        $entry_key = $enty->referrer_host;
      } else {
        $entry_key = 'N/A';
      }

      if ( ! $entry_key ) { continue; }

      if ( empty( $entry->referrer_type ) ) {
        $entry->referrer_type = 'N/A';
      }

      // Referrer
      if ( empty( $parsed['referrers'][ $entry_key ] ) ) {
        $parsed['referrers'][ $entry_key ]['count']       = 1;
        $parsed['referrers'][ $entry_key ]['primary_url'] = $entry->referrer_primary_url;
        $parsed['referrers'][ $entry_key ]['flag']        = $entry->is_flagged;
        $parsed['referrers'][ $entry_key ]['name']        = $entry_key;
        $parsed['referrers'][ $entry_key ]['type']        = $entry->referrer_type;
      } else {
        $parsed['referrers'][ $entry_key ]['count']++;
      }

      // Type
      if ( empty( $parsed['types'][ $entry->referrer_type ] ) ) {
        $parsed['types'][ $entry->referrer_type ] = 1;
      } else {
        $parsed['types'][ $entry->referrer_type ]++;
      }

      // Destination
      if ( empty( $parsed['destinations'][ $entry->url_destination ] ) ) {
        $parsed['destinations'][ $entry->url_destination ] = 1;
      } else {
        $parsed['destinations'][ $entry->url_destination ]++;
      }
    }

    return $parsed;
  }
}

/**
 * Returns an array of known referrers
 */
if ( ! function_exists( 'referrer_analytics_get_referrers' ) ) {
  function referrer_analytics_get_referrers() {
    $options = referrer_analytics_options();
    $hosts   = [];

    // Check if all hosts should be tracked, if so add known
    if ( 'enabled' === $options['track_all_referrers'] ) {
      $known_referrers = referrer_analytics_referrers();
    }

    // Check to make sure at least one host is available
    if ( ! $options['hosts'] && ! $known_referrers ) {
      return false;
    }

    if( ! empty( $known_referrers ) ) {
      // Add known hosts to hosts list
      foreach( $known_referrers as $key => $host ) {
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
 * Returns a visitor's referrer
 */
if ( ! function_exists( 'referrer_analytics_get_referrer' ) ) {
  function referrer_analytics_get_referrer() {
    $options      = referrer_analytics_options();
    $referrer_url = ! empty( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : false;

    $referrer = [
      'url'         => false,
      'primary_url' => false,
      'scheme'      => false,
      'host'        => false,
      'path'        => false,
      'type'        => false,
      'name'        => false,
      'is_flagged'  => false
    ];

    if ( $referrer_url ) {
      // Referrer found, parse
      $url = parse_url( $referrer_url );

      $referrer['url']    = $referrer_url;
      $referrer['scheme'] = ! empty( $url['scheme'] ) ? $url['scheme'] : false;
      $referrer['host']   = ! empty( $url['host'] ) ? $url['host'] : false;
      $referrer['path']   = ! empty( $url['path'] ) ? $url['path'] : false;
    } elseif( 'enabled' == $options['url_referrer_fallback'] ) {
      // Unable to get referrer, fallback to URL referrer
      $current_url = referrer_analytics_current_url();

      if ( ! empty( $current_url['query'] ) && ! empty( $current_url['query'][ $options['referrer_fallback_param'] ] ) ) {
        // URL referrer parameter found
        $url_referrer_source = $current_url['query']['utm_source'];

        $referrer['host'] = $url_referrer_source;
      } else {
        // No referrer found
        return false;
      }
    } else {
      // No referrer found
      return false;
    }

    // Get the host information
    $hosts = referrer_analytics_get_referrers();

    $found = false;
    foreach( $hosts as $host_key => $host ) {
      if ( $referrer['host'] === $host['host'] ) {
        $referrer = array_merge( $referrer, $host );
        $found = true;
        break;
      }
    }

    // Couldn't find a match with a host, check if host should still be tracked
    if (
      $found ||
      ! $found && 'enabled' === $options['track_all_referrers'] ) {
      return $referrer;
    }

    return false;
  }
}

/**
 * Logs a referrer entry to the database
 */
if ( ! function_exists( 'referrer_analytics_log' ) ) {
  function referrer_analytics_log() {
    global $wpdb;

    // Check for a referrer, if none ignore
    $referrer = referrer_analytics_get_referrer();
    if ( ! $referrer ) { return false; }

    // Get the current URL (destination URL)
    $current_url = referrer_analytics_current_url();

    // Insert referrer entry into the database
    $table_name = $wpdb->prefix . 'referrer_analytics';

    $wpdb->insert( $table_name, [
      'date_recorded'        => current_time( 'mysql' ),
      'referrer_url'         => $referrer['url'],
      'referrer_primary_url' => $referrer['primary_url'],
      'referrer_host'        => $referrer['host'],
      'referrer_type'        => $referrer['type'],
      'referrer_name'        => $referrer['name'],
      'visitor_ip'           => referrer_analytics_get_ip(),
      'user_id'              => get_current_user_id(),
      'url_destination'      => $current_url['full'],
      'is_flagged'           => $referrer['is_flagged']
    ], [
      '%s',
      '%s',
      '%s',
      '%s',
      '%s',
      '%s',
      '%s',
      '%s',
      '%s'
    ]);
  }
}

/**
 * Returns the referrer entries from the database
 */
if ( ! function_exists( 'referrer_analytics_get_log' ) ) {
  function referrer_analytics_get_log( $args = [] ) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'referrer_analytics';

    $query = "SELECT * FROM $table_name";
    if ( ! empty( $args['limit'] ) ) {
      $query .= " LIMIT " .$args['limit'];
    }

    if ( ! empty( $args['offset'] ) ) {
      $query .= ", " . $args['offset'];
    }

    $results = $wpdb->get_results( $query );

    return $results;
  }
}

/**
 * Returns the plugin settings
 */
if ( ! function_exists( 'referrer_analytics_options' ) ) {
  function referrer_analytics_options() {
    $options = get_option( 'referrer_analytics_options' );

    // Required fields
    if ( empty( $options['utm_source'] ) ) { $options['utm_source'] = 'host'; }
    if ( empty( $options['utm_medium'] ) ) { $options['utm_medium'] = 'type'; }
    if ( empty( $options['utm_campaign'] ) ) { $options['utm_campaign'] = 'name'; }
    if ( empty( $options['cookie_expiration'] ) ) { $options['cookie_expiration'] = 30; }
    if ( empty( $options['referrer_fallback_param'] ) ) { $options['referrer_fallback_param'] = 'utm_source'; }
    if ( empty( $options['hosts'] ) ) { $options['hosts'] = []; }

    // Optional fields
    if ( empty( $options['store_cookies'] ) ) { $options['store_cookies'] = 'disabled'; }
    if ( empty( $options['logging'] ) ) { $options['logging'] = 'disabled'; }
    if ( empty( $options['redirect_with_utm'] ) ) { $options['redirect_with_utm'] = 'enabled'; }
    if ( empty( $options['track_all_referrers'] ) ) { $options['track_all_referrers'] = 'enabled'; }
    if ( empty( $options['url_referrer_fallback'] ) ) { $options['url_referrer_fallback'] = 'enabled'; }
    if ( empty( $options['disable_noreferrer'] ) ) { $options['disable_noreferrer'] = 'disabled'; }

    return $options;
  }
}

/**
 * Returns a list of plugin defined referrers
 */
if ( ! function_exists( 'referrer_analytics_referrers' ) ) {
  function referrer_analytics_referrers() {
    return [
      // Google
      [ 'host' => 'www.google.com', 'type' => 'organic', 'name' => 'Google', 'primary_url' => 'https://www.google.com/' ],
      [ 'host' => 'www.google.cl', 'type' => 'organic', 'name' => 'Google (Chile)', 'primary_url' => 'https://www.google.cl/' ],
      [ 'host' => 'www.google.ru', 'type' => 'organic', 'name' => 'Google (Russia)', 'primary_url' => 'https://www.google.ru/' ],
      [ 'host' => 'www.google.fr', 'type' => 'organic', 'name' => 'Google (France)', 'primary_url' => 'https://www.google.fr/' ],
      [ 'host' => 'www.google.in', 'type' => 'organic', 'name' => 'Google (India)', 'primary_url' => 'https://www.google.in/' ],
      [ 'host' => 'www.google.co.in', 'type' => 'organic', 'name' => 'Google (India)', 'primary_url' => 'https://www.google.in/' ],
      [ 'host' => 'www.google.co.uk', 'type' => 'organic', 'name' => 'Google (United Kingdom)', 'primary_url' => 'https://www.google.co.uk/' ],
      [ 'host' => 'www.google.ch', 'type' => 'organic', 'name' => 'Google (Switzerland)', 'primary_url' => 'https://www.google.ch/' ],
      [ 'host' => 'www.google.co.kr', 'type' => 'organic', 'name' => 'Google (South Korea)', 'primary_url' => 'https://www.google.co.kr/' ],
      [ 'host' => 'www.google.co.th', 'type' => 'organic', 'name' => 'Google (Thailand)', 'primary_url' => 'https://www.google.co.th/' ],
      [ 'host' => 'www.google.com.eg', 'type' => 'organic', 'name' => 'Google (Egypt)', 'primary_url' => 'https://www.google.com.eg/' ],
      [ 'host' => 'www.google.com.ar', 'type' => 'organic', 'name' => 'Google (Argentina)', 'primary_url' => 'https://www.google.com.ar/' ],
      [ 'host' => 'www.google.com.br', 'type' => 'organic', 'name' => 'Google (Brazil)', 'primary_url' => 'https://www.google.com.br/' ],
      [ 'host' => 'www.google.ro', 'type' => 'organic', 'name' => 'Google (Romania)', 'primary_url' => 'https://www.google.ro/' ],
      [ 'host' => 'www.google.com.au', 'type' => 'organic', 'name' => 'Google (Australia)', 'primary_url' => 'https://www.google.com.au/' ],
      [ 'host' => 'www.google.dk', 'type' => 'organic', 'name' => 'Google (Denmark)', 'primary_url' => 'https://www.google.dk/' ],
      [ 'host' => 'www.google.de', 'type' => 'organic', 'name' => 'Google (Germany)', 'primary_url' => 'https://www.google.de/' ],
      [ 'host' => 'www.google.pl', 'type' => 'organic', 'name' => 'Google (Poland)', 'primary_url' => 'https://www.google.pl/' ],

      // Bing
      [ 'host' => 'www.bing.com', 'type' => 'organic', 'name' => 'Bing', 'primary_url' => 'https://www.bing.com/' ],
      [ 'host' => 'cn.bing.com', 'type' => 'organic', 'name' => 'Bing (China)', 'primary_url' => 'https://www.bing.com/?mkt=zh-CN' ],

      // Yahoo
      [ 'host' => 'r.search.yahoo.com', 'type' => 'organic', 'name' => 'Yahoo', 'primary_url' => 'https://www.yahoo.com/' ],
      [ 'host' => 'search.yahoo.com', 'type' => 'organic', 'name' => 'Yahoo', 'primary_url' => 'https://www.yahoo.com/' ],
      [ 'host' => 'fr.search.yahoo.com', 'type' => 'organic', 'name' => 'Yahoo (France)', 'primary_url' => 'https://fr.search.yahoo.com/' ],
      [ 'host' => 'uk.search.yahoo.com', 'type' => 'organic', 'name' => 'Yahoo (United Kingdom)', 'primary_url' => 'https://uk.search.yahoo.com/' ],

      // Other search engines
      [ 'host' => 'duckduckgo.com', 'type' => 'organic', 'name' => 'DuckDuckGo', 'primary_url' => 'https://duckduckgo.com/' ],
      [ 'host' => 'baidu.com', 'type' => 'organic', 'name' => 'Baidu', 'primary_url' => 'http://www.baidu.com/' ],
      [ 'host' => 'www.ecosia.org', 'type' => 'organic', 'name' => 'Ecosia', 'primary_url' => 'https://www.ecosia.org/' ],
      [ 'host' => 'www.qwant.com', 'type' => 'organic', 'name' => 'Qwant', 'primary_url' => 'https://www.qwant.com/' ],

      // Social media
      [ 'host' => 't.co', 'type' => 'social', 'name' => 'Twitter', 'primary_url' => 'https://twitter.com/' ],
      [ 'host' => 'www.facebook.com', 'type' => 'social', 'name' => 'Facebook', 'primary_url' => 'https://www.facebook.com/' ],
      [ 'host' => 'www.linkedin.com', 'type' => 'social', 'name' => 'LinkedIn', 'primary_url' => 'https://www.linkedin.com/' ],
      [ 'host' => 'www.instagram.com', 'type' => 'social', 'name' => 'Instagram', 'primary_url' => 'https://www.instagram.com/' ],
      [ 'host' => 'www.youtube.com', 'type' => 'social', 'name' => 'YouTube', 'primary_url' => 'https://www.youtube.com/' ],

      // Others
      [ 'host' => 'site.ru', 'type' => 'bot', 'name' => 'site.ru', 'flag' => true ],
      [ 'host' => 'css-tricks.com', 'type' => 'referral', 'name' => 'CSS-Tricks', 'primary_url' => 'https://css-tricks.com/' ],
      [ 'host' => 'lurkmore.to', 'type' => 'referral', 'name' => 'Lurkmore', 'primary_url' => 'https://lurkmore.to/' ],
      [ 'host' => 'drupalsun.com', 'type' => 'referral', 'name' => 'Drupal Sun', 'primary_url' => 'https://drupalsun.com/' ],
      [ 'host' => 'cdpn.io', 'type' => 'referral', 'name' => 'CodePen', 'primary_url' => 'https://codepen.io/' ],
      [ 'host' => 'amzn.to', 'type' => 'referral', 'name' => 'Amazon', 'primary_url' => 'https://www.amazon.com/' ],
      [ 'host' => 'jobsnearme.online', 'type' => 'referral', 'name' => 'Jobs Near Me', 'primary_url' => 'https://jobsnearme.online/' ],
      [ 'host' => 'www.entermedia.com', 'type' => 'referral', 'name' => 'Entermedia, LLC.', 'primary_url' => 'https://www.entermedia.com/' ],
      [ 'host' => 'entermedianow.com', 'type' => 'redirect', 'name' => 'Entermedia, LLC.', 'primary_url' => 'https://www.entermedia.com/' ],
      [ 'host' => 'entermedia.com', 'type' => 'referral', 'name' => 'Entermedia, LLC.', 'primary_url' => 'https://www.entermedia.com/' ],
      [ 'host' => 'forum.bubble.io', 'type' => 'referral', 'name' => 'Bubble Forum', 'primary_url' => 'https://forum.bubble.io/' ],
      [ 'host' => 'www.benmarshall.me', 'type' => 'referral', 'name' => 'Ben Marshall', 'primary_url' => 'https://benmarshall.me' ],
      [ 'host' => 'benmarshall.me', 'type' => 'referral', 'name' => 'Ben Marshall', 'primary_url' => 'https://benmarshall.me' ],
      [ 'host' => 'github.com', 'type' => 'referral', 'name' => 'GitHub', 'primary_url' => 'https://github.com/' ],
      [ 'host' => 'wordpress.org', 'type' => 'referral', 'name' => 'WordPress', 'primary_url' => 'https://wordpress.org/' ],
      [ 'host' => 'school.nextacademy.com', 'type' => 'referral', 'name' => 'NEXT Academy', 'primary_url' => 'https://school.nextacademy.com/' ],
      [ 'host' => 'www.soliddigital.com', 'type' => 'referral', 'name' => 'Solid Digital', 'primary_url' => 'https://www.soliddigital.com/' ],
      [ 'host' => 'www.benellile.com', 'type' => 'referral', 'name' => 'Benlli', 'primary_url' => 'https://www.benellile.com/' ],
      [ 'host' => 'newsblur.com', 'type' => 'referral', 'name' => 'NewsBlur', 'primary_url' => 'https://newsblur.com/' ],
      [ 'host' => 'knowledge.exlibrisgroup.com', 'type' => 'referral', 'name' => 'Ex Libris Knowledge Center', 'primary_url' => 'https://knowledge.exlibrisgroup.com/' ],
      [ 'host' => 'anti-crisis-seo.com', 'type' => 'bot', 'name' => 'SEO Anti-Crisis Tool', 'primary_url' => 'http://anti-crisis-seo.com' ],
      [ 'host' => 'minepub.net', 'type' => 'referral', 'name' => 'Minepub', 'primary_url' => 'https://minepub.net/' ],

      // Edge cases
      [ 'host' => 'PANTHEON_STRIPPED', 'type' => 'direct', 'name' => 'Direct Traffic' ],
    ];
  }
}
