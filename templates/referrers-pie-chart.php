<?php
/**
 * Referrer pie chart
 *
 * @package ReferrerAnalytics
 * @since 1.0.0
 */
?>
<div class="referrer-analytics-box referrer-analytics-box-referrers-pie">
  <h3><?php _e( 'Top ' . $chart_limit . ' Referrers', 'referrer-analytics' ); ?></h3>
  <div class="inside">
    <?php if ( $referrer_totals ): ?>
      <canvas id="referrer-analytics-pie-referrers"></canvas>
      <script>
      <?php
      $labels = [];
      $data   = [];
      $count  = 0;
      foreach( $referrer_totals as $key => $value ):
        $count++;
        if ( $count > $chart_limit ): break; endif;

        $labels[] = $value['name'];
        $data[]   = $value['count'];
        $colors[] = $predefined_colors[ $count ];
      endforeach;
      ?>
      var referrers = document.getElementById('referrer-analytics-pie-referrers');
      var referrerAnalyticsPie = new Chart(referrers, {
        type: 'pie',
        data: {
          labels: <?php echo json_encode( $labels ); ?>,
          datasets: [{
            data: <?php echo json_encode( $data ); ?>,
            backgroundColor: <?php echo json_encode( $colors ); ?>,
            borderWidth: 0,
            borderColor: '#f1f1f1'
          }],
        },
        options: {
          legend: {
            position: 'right',
            fullWidth: false
          }
        }
      });
      </script>
    <?php else: ?>
      <?php _e( 'No data to report yet.', 'referrer-analytics' ); ?>
    <?php endif; ?>
  </div>
</div>
