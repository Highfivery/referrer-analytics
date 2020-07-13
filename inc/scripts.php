<?php
/**
 * CSS & JS
 *
 * @package ReferrerAnalytics
 * @since 1.0.0
 */

/**
 * Add admin scripts
 */
function referrer_analytics_admin_scripts() {
  wp_enqueue_style( 'referrer-analytics-admin', plugin_dir_url( REFERRER_ANALYTICS ) . '/assets/css/admin.css', false, '1.0.0' );

  wp_enqueue_style( 'referrer-analytics-charts', plugin_dir_url( REFERRER_ANALYTICS ) . '/assets/css/Chart.min.css', false, '2.9.3' );
  wp_enqueue_script( 'referrer-analytics-charts', plugin_dir_url( REFERRER_ANALYTICS ) . '/assets/js/Chart.bundle.min.js', [], '2.9.3' );
}
add_action( 'admin_enqueue_scripts', 'referrer_analytics_admin_scripts' );

/**
 * Add frontend scripts
 *
 * @since 1.2.0
 */
function referrer_analytics_scripts() {
  $options = referrer_analytics_options();

  if ( 'enabled' === $options['store_cookies'] ) {
    wp_register_script( 'referrer-analytics-cookie', plugin_dir_url( REFERRER_ANALYTICS ) . '/assets/js/js.cookie.js', [], '2.2.1' );
    wp_enqueue_script( 'referrer-analytics', plugin_dir_url( REFERRER_ANALYTICS ) . '/assets/js/referrer-analytics.js', [ 'referrer-analytics-cookie' ], '1.0.0' );
  }
}
add_action( 'wp_enqueue_scripts', 'referrer_analytics_scripts' );
