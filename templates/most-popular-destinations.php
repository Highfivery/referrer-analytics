<?php
/**
 * Most popular destinations
 *
 * @package ReferrerAnalytics
 * @since 1.0.0
 */
?>
<div class="referrer-analytics-box referrer-analytics-box-popular-destinations">
  <h3><?php _e( 'Most Popular Referred Destinations (all-time)', 'referrer-analytics' ); ?></h3>
  <div class="inside">
    <?php if ( $destination_totals ): ?>
      <ol class="referreranalytics-list">
        <?php
        $cnt = 0;
        foreach( $destination_totals as $url => $count ):
          $cnt++;
          if ( $cnt > 15 ) { break; }
          ?>
          <li>
            <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo str_replace( site_url(), '', $url ); ?></a>
            <span class="referreranalytics-list-count"><?php echo number_format( $count, 0); ?></span>
          </li>
        <?php endforeach; ?>
      </ol>
    <?php else: ?>
      <?php _e( 'No data to report yet.', 'referrer-analytics' ); ?>
    <?php endif; ?>
  </div>
</div>
