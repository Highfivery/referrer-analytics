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

      // Get the referrer key
      $referrer_key = false;
      if ( ! empty( $entry->referrer_name ) ) {
        $entry_key = $entry->referrer_name;
      } elseif( ! empty( $enty->referrer_host ) ) {
        $entry_key = $enty->referrer_host;
      } else {
        $entry_key = 'N/A';
      }
      if ( ! $entry_key ) { continue; }

      if ( empty( $entries[ $entry_key ] ) ) {
        $entries[ $entry_key ]['count']       = 1;
        $entries[ $entry_key ]['primary_url'] = $entry->referrer_primary_url;
        $entries[ $entry_key ]['flag']        = $entry->is_flagged;
        $entries[ $entry_key ]['name']        = $entry_key;
        $entries[ $entry_key ]['type']        = $entry->referrer_type;
      } else {
        $entries[ $entry_key ]['count']++;
      }
    }
  }

  usort($entries, function($a, $b) {
    return $b['count'] <=> $a['count'];
  });
}
?>
<div class="referrer-analytics-box referrer-analytics-box-7-day-referrers">
  <h3><?php _e( 'Most Popular Referrers (past 7 days)', 'referrer-analytics' ); ?></h3>
  <div class="inside">
    <?php if ( $entries ): ?>
      <ol>
        <?php
        $cnt = 0;
        foreach( $entries as $key => $entry ):
          $cnt++;
          if ( $cnt > 15 ) { break; }
          ?>
          <li>
            <?php if ( ! empty( $entry['primary_url'] ) ): ?><a href="<?php echo esc_url( $entry['primary_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php endif; ?>
              <strong><?php echo $entry['name']; ?></strong>
            <?php if ( ! empty( $entry['primary_url'] ) ): ?></a><?php endif; ?> &mdash; <?php echo $entry['count']; ?>
            <?php if ( ! empty( $entry['type'] ) ): ?>
              (<?php echo $entry['type']; ?>)
            <?php endif; ?>
            <?php if ( ! empty( $entry['flag'] ) && $entry['flag'] ): ?>
              <span style="color: #ca4a1f;"><?php _e( 'potentially malicious, consider blocking', 'referrer-analytics' ); ?></span>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ol>
    <?php else: ?>
      <?php _e( 'No data to report yet.', 'referrer-analytics' ); ?>
    <?php endif; ?>
  </div>
</div>
