<?php
/**
 * Most popular referrers the past 7 days
 *
 * @package ReferrerAnalytics
 * @since 1.0.0
 */
$todays_date    = current_time( 'timestamp' );
$beginning_date = $todays_date - ( 86400 * 7 );
$entries        = [];

if ( $log ) {
  foreach( $log as $key => $entry ) {
    $time = strtotime( $entry->date_recorded );
    if ( $time >= $beginning_date && $time <= $todays_date ) {
      if ( empty( $entries[ $entry->url_destination ] ) ) {
        $entries[ $entry->url_destination ] = 1;
      } else {
        $entries[ $entry->url_destination ]++;
      }
    }
  }

  if ( $entries ) {
    arsort( $entries );
  }
}
?>
<div class="referrer-analytics-box referrer-analytics-box-popular-7-day-destinations">
  <h3><?php _e( 'Most Popular Referrers (past 7 days)', 'referrer-analytics' ); ?></h3>
  <div class="inside">
    <?php if ( $entries ): ?>
      <ol class="referreranalytics-list">
        <?php
        $cnt = 0;
        foreach( $entries as $url => $count ):
          $cnt++;
          if ( $cnt > 15 ) { break; }
          ?>
          <li>
            <span class="referreranalytics-list-label">
              <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo str_replace( site_url(), '', $url ); ?></a>
            </span>
            <span class="referreranalytics-list-count"><?php echo number_format( $count, 0 ); ?></span>
          </li>
        <?php endforeach; ?>
      </ol>
    <?php else: ?>
      <?php _e( 'No data to report yet.', 'referrer-analytics' ); ?>
    <?php endif; ?>
  </div>
</div>
