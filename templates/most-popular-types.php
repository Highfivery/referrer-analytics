<?php
/**
 * Most popular types
 *
 * @package ReferrerAnalytics
 * @since 1.0.0
 */
?>
<div class="referrer-analytics-box referrer-analytics-box-popular-types">
  <h3><?php _e( 'Most Popular Referrer Types', 'referrer-analytics' ); ?></h3>
  <div class="inside">
    <?php if ( $type_totals ): ?>
      <ol>
        <?php
        $cnt = 0;
        foreach( $type_totals as $type => $count ):
          $cnt++;
          if ( $cnt > 15 ) { break; }
          ?>
          <li><strong><?php echo $type; ?></strong> &mdash; <?php echo $count; ?></li>
        <?php endforeach; ?>
      </ol>
    <?php else: ?>
      <?php _e( 'No data to report yet.', 'referrer-analytics' ); ?>
    <?php endif; ?>
  </div>
</div>
