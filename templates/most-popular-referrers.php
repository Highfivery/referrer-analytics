<?php
/**
 * Most popular referrers
 *
 * @package ReferrerAnalytics
 * @since 1.0.0
 */
 ?>
 <div class="referrer-analytics-box referrer-analytics-box-popular-referrers">
  <h3><?php _e( 'Most Popular Referrers (all-time)', 'referrer-analytics' ); ?></h3>
  <div class="inside">
    <?php if ( $referrer_totals ): ?>
      <ol>
        <?php
        $cnt = 0;
        foreach( $referrer_totals as $key => $entry ):
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
