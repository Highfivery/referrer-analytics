<?php
/**
 * CSS & JS
 *
 * @package ReferrerAnalytics
 * @since 1.0.0
 */

function referrer_analytics_admin_scripts() {
  wp_enqueue_style( 'referrer_analytics_admin', plugin_dir_url( REFERRER_ANALYTICS ) . '/assets/css/admin.css', false, '1.0.0' );

  wp_enqueue_style( 'referrer_analytics_charts', plugin_dir_url( REFERRER_ANALYTICS ) . '/assets/css/Chart.min.css', false, '2.9.3' );
  wp_enqueue_script( 'referrer_analytics_charts', plugin_dir_url( REFERRER_ANALYTICS ) . '/assets/js/Chart.bundle.min.js', [], '2.9.3' );
}
add_action( 'admin_enqueue_scripts', 'referrer_analytics_admin_scripts' );
