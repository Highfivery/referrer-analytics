<?php
/**
 * Types pie chart
 *
 * @package ReferrerAnalytics
 * @since 1.0.0
 */
?>
<div class="referrer-analytics-box referrer-analytics-box-type-pie">
  <h3><?php _e( 'Top ' . $chart_limit . ' Referrer Types', 'referrer-analytics' ); ?></h3>
  <div class="inside">
    <?php if ( $type_totals ): ?>
      <canvas id="referrer-analytics-pie-types"></canvas>
      <script>
      <?php
      $labels = [];
      $data   = [];
      $count  = 0;
      foreach( $type_totals as $key => $value ):
        $count++;
        if ( $count > $chart_limit ): break; endif;

        $labels[] = $key;
        $data[]   = $value;
        $colors[] = $predefined_colors[ $count ];
      endforeach;
      ?>
      var types = document.getElementById('referrer-analytics-pie-types');
      var referrerAnalyticsPie = new Chart(types, {
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
