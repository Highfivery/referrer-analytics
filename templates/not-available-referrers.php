<?php
/**
 * Most popular referrers
 *
 * @package ReferrerAnalytics
 * @since 1.0.0
 */

$unknown = [];
if ( $log ) {
  foreach( $log as $key => $item ) {
    if ( empty( $item->referrer_name ) ) {
      if ( empty( $unknown[ $item->referrer_host ] ) ) {
        $unknown[ $item->referrer_host ] = 1;
      } else {
        $unknown[ $item->referrer_host ]++;
      }
    }
  }

  if ( $unknown ) {
    arsort( $unknown );
  }
}
?>
 <div class="referrer-analytics-box referrer-analytics-box-unknown-referrers">
  <h3><?php _e( 'Unknown Referrers', 'referrer-analytics' ); ?></h3>
  <div class="inside">
    <?php if ( $unknown ): ?>
      <ol>
        <?php
        $cnt = 0;
        foreach( $unknown as $host => $count ):
        $cnt++;
        if ( $cnt > 50 ) { break; }
        ?>
          <li>
            <?php echo $host . ' &mdash; ' . $count; ?>
          </li>
        <?php endforeach; ?>
        </ol>
    <?php else: ?>
      <?php _e( 'No data to report yet.', 'referrer-analytics' ); ?>
    <?php endif; ?>
  </div>
</div>
