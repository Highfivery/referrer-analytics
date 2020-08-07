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
      <ol class="referreranalytics-list">
        <?php
        $cnt = 0;
        foreach( $type_totals as $type => $count ):
          $cnt++;
          if ( $cnt > 15 ) { break; }
          ?>
          <li>
            <span class="referreranalytics-list-label">
              <?php
              switch( $type ):
                case 'organic':
                  echo __( 'Organic', 'referreranalytics' );
                break;
                case 'referral':
                  echo __( 'Referral', 'referreranalytics' );
                break;
                case 'bot':
                  echo __( 'Bot', 'referreranalytics' );
                break;
                case 'social':
                  echo __( 'Social Network', 'referreranalytics' );
                break;
                case 'email':
                  echo __( 'Email', 'referreranalytics' );
                break;
                case 'intranet':
                  echo __( 'Intranet', 'referreranalytics' );
                break;
                default:
                  echo $type;
              endswitch;
              ?>
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
