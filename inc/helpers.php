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
          $found_match = true;

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
          if( ! empty( $referrer['primary_url'] ) && $entry->referrer_primary_url != $referrer['primary_url'] ) {
            $updates['referrer_primary_url'] = $referrer['primary_url'];
          }

          // If there are any updates, update the database record
          if ( $updates ) {
            $wpdb->update( $table_name, $updates, [ 'referrer_id' => $entry->referrer_id ] );
          }

          break;
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

        $referrer['host'] = $url_referrer_source . ' (UTM Source)';
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
      [ 'host' => 'www.google.at', 'type' => 'organic', 'name' => 'Google (Austria)', 'primary_url' => 'https://www.google.at/' ],
      [ 'host' => 'www.google.ca', 'type' => 'organic', 'name' => 'Google (Canada)', 'primary_url' => 'https://www.google.ca/' ],
      [ 'host' => 'www.google.com', 'type' => 'organic', 'name' => 'Google', 'primary_url' => 'https://www.google.com/' ],
      [ 'host' => 'www.google.cl', 'type' => 'organic', 'name' => 'Google (Chile)', 'primary_url' => 'https://www.google.cl/' ],
      [ 'host' => 'www.google.ru', 'type' => 'organic', 'name' => 'Google (Russia)', 'primary_url' => 'https://www.google.ru/' ],
      [ 'host' => 'www.google.fr', 'type' => 'organic', 'name' => 'Google (France)', 'primary_url' => 'https://www.google.fr/' ],
      [ 'host' => 'www.google.hu', 'type' => 'organic', 'name' => 'Google (Hungary)', 'primary_url' => 'https://www.google.hu/' ],
      [ 'host' => 'www.google.in', 'type' => 'organic', 'name' => 'Google (India)', 'primary_url' => 'https://www.google.in/' ],
      [ 'host' => 'www.google.it', 'type' => 'organic', 'name' => 'Google (Italy)', 'primary_url' => 'https://www.google.it/' ],
      [ 'host' => 'www.google.com.ph', 'type' => 'organic', 'name' => 'Google (Philippines)', 'primary_url' => 'https://www.google.com.ph/' ],
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
      [ 'host' => 'www.google.com.tr', 'type' => 'organic', 'name' => 'Google (Turkey)', 'primary_url' => 'https://www.google.com.tr/' ],
      [ 'host' => 'www.google.com.tw', 'type' => 'organic', 'name' => 'Google (Taiwan)', 'primary_url' => 'https://www.google.com.tw/' ],
      [ 'host' => 'www.google.com.vn', 'type' => 'organic', 'name' => 'Google (Vietnam)', 'primary_url' => 'https://www.google.com.vn/' ],
      [ 'host' => 'www.google.hn', 'type' => 'organic', 'name' => 'Google (Honduras)', 'primary_url' => 'https://www.google.hn/' ],
      [ 'host' => 'www.google.com.sg', 'type' => 'organic', 'name' => 'Google (Singapore)', 'primary_url' => 'https://www.google.com.sg/' ],
      [ 'host' => 'www.google.com.hk', 'type' => 'organic', 'name' => 'Google (Hong Kong)', 'primary_url' => 'https://www.google.com.hk/' ],
      [ 'host' => 'www.google.com.mx', 'type' => 'organic', 'name' => 'Google (Mexico)', 'primary_url' => 'https://www.google.com.mx/' ],
      [ 'host' => 'support.google.com', 'type' => 'organic', 'name' => 'Google Support', 'primary_url' => 'https://support.google.com/' ],
      [ 'host' => 'cse.google.com', 'type' => 'organic', 'name' => 'Google (Creator Search)', 'primary_url' => 'https://cse.google.com/' ],
      [ 'host' => 'keep.google.com', 'type' => 'organic', 'name' => 'Google Keep', 'primary_url' => 'https://keep.google.com/u/0/' ],
      [ 'host' => 'www.google.co.id', 'type' => 'organic', 'name' => 'Google (Indonesia)', 'primary_url' => 'https://www.google.co.id/' ],
      [ 'host' => 'www.google.co.il', 'type' => 'organic', 'name' => 'Google (Israel)', 'primary_url' => 'https://www.google.co.il/' ],
      [ 'host' => 'search.google.com', 'type' => 'organic', 'name' => 'Google', 'primary_url' => 'https://www.google.com/' ],
      [ 'host' => 'www.google.cz', 'type' => 'organic', 'name' => 'Google (Czechia)', 'primary_url' => 'https://www.google.cz/' ],
      [ 'host' => 'www.google.co.jp', 'type' => 'organic', 'name' => 'Google (Japan)', 'primary_url' => 'https://www.google.co.jp/' ],
      [ 'host' => 'www.google.pt', 'type' => 'organic', 'name' => 'Google (Portugal)', 'primary_url' => 'https://www.google.com.co/' ],
      [ 'host' => 'www.google.com.co', 'type' => 'organic', 'name' => 'Google (Colombia)', 'primary_url' => 'https://www.google.pt/' ],
      [ 'host' => 'www.google.ae', 'type' => 'organic', 'name' => 'Google (United Arab Emirates)', 'primary_url' => 'https://www.google.ae/' ],
      [ 'host' => 'www.google.com.ua', 'type' => 'organic', 'name' => 'Google (Ukraine)', 'primary_url' => 'https://www.google.com.ua/' ],
      [ 'host' => 'www.google.co.za', 'type' => 'organic', 'name' => 'Google (South Africa)', 'primary_url' => 'https://www.google.co.za/' ],
      [ 'host' => 'www.google.nl', 'type' => 'organic', 'name' => 'Google (Netherlands)', 'primary_url' => 'https://www.google.nl/' ],
      [ 'host' => 'www.google.fi', 'type' => 'organic', 'name' => 'Google (Finland)', 'primary_url' => 'https://www.google.fi/' ],
      [ 'host' => 'www.google.kz', 'type' => 'organic', 'name' => 'Google (Kazakhstan)', 'primary_url' => 'https://www.google.kz/' ],
      [ 'host' => 'www.google.com.my', 'type' => 'organic', 'name' => 'Google (Malaysia)', 'primary_url' => 'https://www.google.com.my/' ],
      [ 'host' => 'www.google.se', 'type' => 'organic', 'name' => 'Google (Sweden)', 'primary_url' => 'https://www.google.se/' ],
      [ 'host' => 'www.google.es', 'type' => 'organic', 'name' => 'Google (Spain)', 'primary_url' => 'https://www.google.es/' ],
      [ 'host' => 'www.google.no', 'type' => 'organic', 'name' => 'Google (Norway)', 'primary_url' => 'https://www.google.no/' ],

      // Bing
      [ 'host' => 'www.bing.com', 'type' => 'organic', 'name' => 'Bing', 'primary_url' => 'https://www.bing.com/' ],
      [ 'host' => 'cn.bing.com', 'type' => 'organic', 'name' => 'Bing (China)', 'primary_url' => 'https://www.bing.com/?mkt=zh-CN' ],

      // Yahoo
      [ 'host' => 'r.search.yahoo.com', 'type' => 'organic', 'name' => 'Yahoo', 'primary_url' => 'https://www.yahoo.com/' ],
      [ 'host' => 'search.yahoo.com', 'type' => 'organic', 'name' => 'Yahoo', 'primary_url' => 'https://www.yahoo.com/' ],
      [ 'host' => 'fr.search.yahoo.com', 'type' => 'organic', 'name' => 'Yahoo (France)', 'primary_url' => 'https://fr.search.yahoo.com/' ],
      [ 'host' => 'uk.search.yahoo.com', 'type' => 'organic', 'name' => 'Yahoo (United Kingdom)', 'primary_url' => 'https://uk.search.yahoo.com/' ],
      [ 'host' => 'us.search.yahoo.com', 'type' => 'organic', 'name' => 'Yahoo', 'primary_url' => 'https://us.search.yahoo.com/' ],
      [ 'host' => 'in.search.yahoo.com', 'type' => 'organic', 'name' => 'Yahoo (India)', 'primary_url' => 'https://in.search.yahoo.com/' ],
      [ 'host' => 'tw.search.yahoo.com', 'type' => 'organic', 'name' => 'Yahoo (Taiwan)', 'primary_url' => 'https://tw.search.yahoo.com/' ],
      [ 'host' => 'au.search.yahoo.com', 'type' => 'organic', 'name' => 'Yahoo (Australia)', 'primary_url' => 'https://search.yahoo.com/' ],
      [ 'host' => 'finance.yahoo.com', 'type' => 'referral', 'name' => 'Yahoo Finance', 'primary_url' => 'https://finance.yahoo.com/' ],

      // Other search engines
      [ 'host' => 'duckduckgo.com', 'type' => 'organic', 'name' => 'DuckDuckGo', 'primary_url' => 'https://duckduckgo.com/' ],
      [ 'host' => 'baidu.com', 'type' => 'organic', 'name' => 'Baidu', 'primary_url' => 'http://www.baidu.com/' ],
      [ 'host' => 'www.ecosia.org', 'type' => 'organic', 'name' => 'Ecosia', 'primary_url' => 'https://www.ecosia.org/' ],
      [ 'host' => 'www.qwant.com', 'type' => 'organic', 'name' => 'Qwant', 'primary_url' => 'https://www.qwant.com/' ],
      [ 'host' => 'go.mail.ru', 'type' => 'organic', 'name' => 'Поиск Mail.Ru', 'primary_url' => 'https://go.mail.ru/' ],
      [ 'host' => 'search.aol.com', 'type' => 'organic', 'name' => 'AOL', 'primary_url' => 'https://search.aol.com/' ],

      // Social media
      [ 'host' => 't.co', 'type' => 'social', 'name' => 'Twitter', 'primary_url' => 'https://twitter.com/' ],
      [ 'host' => 'twitter.com', 'type' => 'social', 'name' => 'Twitter', 'primary_url' => 'https://twitter.com/' ],
      [ 'host' => 'www.facebook.com', 'type' => 'social', 'name' => 'Facebook', 'primary_url' => 'https://www.facebook.com/' ],
      [ 'host' => 'www.linkedin.com', 'type' => 'social', 'name' => 'LinkedIn', 'primary_url' => 'https://www.linkedin.com/' ],
      [ 'host' => 'www.instagram.com', 'type' => 'social', 'name' => 'Instagram', 'primary_url' => 'https://www.instagram.com/' ],
      [ 'host' => 'www.youtube.com', 'type' => 'social', 'name' => 'YouTube', 'primary_url' => 'https://www.youtube.com/' ],
      [ 'host' => 'www.reddit.com', 'type' => 'social', 'name' => 'reddit', 'primary_url' => 'https://www.reddit.com/' ],
      [ 'host' => 'l.messenger.com', 'type' => 'social', 'name' => 'Facebook (Messenger)', 'primary_url' => 'https://www.messenger.com/' ],

      // WordPress
      [ 'host' => 'wordpress.org', 'type' => 'referral', 'name' => 'WordPress', 'primary_url' => 'https://wordpress.org/' ],
      [ 'host' => 'fr.wordpress.org', 'type' => 'referral', 'name' => 'WordPress (France)', 'primary_url' => 'https://fr.wordpress.org/' ],
      [ 'host' => 'nl.wordpress.org', 'type' => 'referral', 'name' => 'WordPress (Netherlands)', 'primary_url' => 'https://nl.wordpress.org/' ],

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
      [ 'host' => '35.209.238.75', 'type' => 'referral', 'name' => 'Ben Marshall', 'primary_url' => 'https://benmarshall.me' ],
      [ 'host' => 'github.com', 'type' => 'referral', 'name' => 'GitHub', 'primary_url' => 'https://github.com/' ],
      [ 'host' => 'school.nextacademy.com', 'type' => 'referral', 'name' => 'NEXT Academy', 'primary_url' => 'https://school.nextacademy.com/' ],
      [ 'host' => 'www.soliddigital.com', 'type' => 'referral', 'name' => 'Solid Digital', 'primary_url' => 'https://www.soliddigital.com/' ],
      [ 'host' => 'www.benellile.com', 'type' => 'referral', 'name' => 'Benlli', 'primary_url' => 'https://www.benellile.com/' ],
      [ 'host' => 'newsblur.com', 'type' => 'referral', 'name' => 'NewsBlur', 'primary_url' => 'https://newsblur.com/' ],
      [ 'host' => 'knowledge.exlibrisgroup.com', 'type' => 'referral', 'name' => 'Ex Libris Knowledge Center', 'primary_url' => 'https://knowledge.exlibrisgroup.com/' ],
      [ 'host' => 'anti-crisis-seo.com', 'type' => 'bot', 'name' => 'SEO Anti-Crisis Tool', 'primary_url' => 'http://anti-crisis-seo.com' ],
      [ 'host' => 'minepub.net', 'type' => 'referral', 'name' => 'Minepub', 'primary_url' => 'https://minepub.net/' ],
      [ 'host' => 'ricoshae.com.au', 'type' => 'referral', 'name' => 'Ricoshae', 'primary_url' => 'https://ricoshae.com.au/' ],
      [ 'host' => 'bayriverrealty.com', 'type' => 'referral', 'name' => 'Bay River Realty', 'primary_url' => 'http://bayriverrealty.com/' ],
      [ 'host' => 'forums.athemes.com', 'type' => 'referral', 'name' => 'aThemes Forums', 'primary_url' => 'https://forums.athemes.com/' ],
      [ 'host' => 'timnath.org', 'type' => 'referral', 'name' => 'Timnath, CO', 'primary_url' => 'https://timnath.org/' ],
      [ 'host' => 'spr.com', 'type' => 'referral', 'name' => 'SPR', 'primary_url' => 'https://spr.com/' ],
      [ 'host' => 'michaelbox.net', 'type' => 'referral', 'name' => 'Michael Beckwith', 'primary_url' => 'https://michaelbox.net/' ],
      [ 'host' => 'forum.zwiicms.com', 'type' => 'referral', 'name' => 'Support de Zwii', 'primary_url' => 'http://forum.zwiicms.com/' ],
      [ 'host' => 'wohnparc.de', 'type' => 'referral', 'name' => 'wohnparc.de', 'primary_url' => 'https://wohnparc.de/' ],
      [ 'host' => 'scottdeluzio.com', 'type' => 'referral', 'name' => 'Scott DeLuzio', 'primary_url' => 'https://scottdeluzio.com/' ],
      [ 'host' => 'theme.co', 'type' => 'referral', 'name' => 'Theme.co', 'primary_url' => 'https://theme.co/' ],
      [ 'host' => 'luatduonggia.vn', 'type' => 'referral', 'name' => 'Công ty Luật Dương Gia', 'primary_url' => 'https://luatduonggia.vn/' ],
      [ 'host' => 'app.net', 'type' => 'referral', 'name' => 'App.net', 'primary_url' => 'https://app.net/' ],
      [ 'host' => 'www.gynecomastia.org', 'type' => 'referral', 'name' => 'Gynecomastia.org', 'primary_url' => 'https://www.gynecomastia.org/' ],
      [ 'host' => 'aljlaw.com', 'type' => 'referral', 'name' => 'The Estate Planning and Elder Law Group', 'primary_url' => 'http://aljlaw.com/' ],
      [ 'host' => 'rrhelections.com', 'type' => 'referral', 'name' => 'RRH Elections', 'primary_url' => 'https://rrhelections.com/' ],
      [ 'host' => 'bit.ly', 'type' => 'referral', 'name' => 'Bitly', 'primary_url' => 'https://bitly.com/' ],
      [ 'host' => 'feedly.com', 'type' => 'referral', 'name' => 'Feedly', 'primary_url' => 'https://feedly.com/i/welcome' ],
      [ 'host' => 'www.feedly.com', 'type' => 'referral', 'name' => 'Feedly', 'primary_url' => 'https://feedly.com/i/welcome' ],
      [ 'host' => '109.199.107.148', 'type' => 'referral', 'name' => 'SiteGround', 'primary_url' => 'https://www.siteground.com/' ],
      [ 'host' => 'www.sitepoint.com', 'type' => 'referral', 'name' => 'SitePoint', 'primary_url' => 'https://www.sitepoint.com/' ],
      [ 'host' => 'stackoverflow.com', 'type' => 'referral', 'name' => 'Stack Overflow', 'primary_url' => 'https://stackoverflow.com/' ],
      [ 'host' => 'generatepress.com', 'type' => 'referral', 'name' => 'GeneratePress', 'primary_url' => 'https://generatepress.com/' ],
      [ 'host' => 'www.wpmeta.org', 'type' => 'referral', 'name' => 'WPMeta', 'primary_url' => 'http://www.wpmeta.org/' ],
      [ 'host' => 'ask.csdn.net', 'type' => 'referral', 'name' => '编程技术问答-CSDN问答频道', 'primary_url' => 'https://ask.csdn.net/' ],
      [ 'host' => 'www.hipwf.com', 'type' => 'referral', 'name' => 'Heidelberg International Professional Women\'s Forum', 'primary_url' => 'https://www.hipwf.com/' ],
      [ 'host' => 'www.nanoframework.net', 'type' => 'referral', 'name' => 'nanoFramework', 'primary_url' => 'https://www.nanoframework.net/' ],
      [ 'host' => 'data.vtcdns.com', 'type' => 'referral', 'name' => 'data.vtcdns.com', 'primary_url' => 'http://data.vtcdns.com/', 'flag' => true ],
      [ 'host' => 'stash.trusted.visa.com', 'type' => 'bot', 'name' => 'stash.trusted.visa.com' ],
      [ 'host' => 'support.advancedcustomfields.com', 'type' => 'referral', 'name' => 'ACF Support', 'primary_url' => 'https://support.advancedcustomfields.com/' ],
      [ 'host' => 'austinjeeppeople.com', 'type' => 'referral', 'name' => 'Austin JeepPeople', 'primary_url' => 'https://austinjeeppeople.com/' ],
      [ 'host' => 'yandex.ru', 'type' => 'organic', 'name' => 'Yandex', 'primary_url' => 'https://yandex.ru/' ],
      [ 'host' => 'confluence.godaddy.com', 'type' => 'referral', 'name' => 'GoDaddy (Confluence)', 'primary_url' => 'https://godaddy.okta.com/login/login.htm' ],
      [ 'host' => 'pob.ng', 'type' => 'referral', 'name' => 'Princesage Online Branding', 'primary_url' => 'https://pob.ng/' ],
      [ 'host' => 'theabcn.org', 'type' => 'referral', 'name' => 'American Board of Clinical Neuropsychology', 'primary_url' => 'https://theabcn.org/' ],
      [ 'host' => 'lenny.pl', 'type' => 'referral', 'name' => 'Portal Lenny', 'primary_url' => 'http://lenny.pl/' ],
      [ 'host' => 'www.phase2technology.com', 'type' => 'referral', 'name' => 'Phase2', 'primary_url' => 'https://www.phase2technology.com/' ],

      // Edge cases
      [ 'host' => 'PANTHEON_STRIPPED', 'type' => 'direct', 'name' => 'Direct Traffic' ],
      [ 'host' => 'localhost', 'type' => 'intranet', 'name' => 'localhost' ],
      [ 'host' => 'benmarshall.local', 'type' => 'intranet', 'name' => 'Ben Marshall' ],

      // UTM sources
      [ 'host' => 'jobify plugin (UTM Source) ', 'type' => 'referral', 'name' => 'WordPress.org (Jobify plugin)', 'primary_url' => 'https://wordpress.org/plugins/jobify/', 'inferred' => true ],
      [ 'host' => 'wordpress_zero_spam (UTM Source) ', 'type' => 'referral', 'name' => 'WordPress.org (WordPress Zero Spam plugin)', 'primary_url' => 'https://wordpress.org/plugins/zero-spam/', 'inferred' => true ],
    ];
  }
}
