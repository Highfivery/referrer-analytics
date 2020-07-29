<?php
/**
 * Types line chart
 *
 * @package ReferrerAnalytics
 * @since 1.0.0
 */
?>
<div class="referrer-analytics-box referrer-analytics-box-types-line-chart">
  <h3><?php _e( 'Referrer Types by Date', 'referrer-analytics' ); ?></h3>
  <div class="inside">
    <?php if ( $log ): ?>
      <canvas id="referrer-analytics-type-line-chart"></canvas>
      <script>
      <?php
      $parsed = [];
      $types  = [];

      // Get types
      foreach( $log as $key => $entry ):
        if ( ! in_array( $entry->referrer_type, $types ) ) {
          $types[] = $entry->referrer_type;
        }
      endforeach;

      foreach( $log as $key => $entry ):
        $date_key = date( 'Y-m-d', strtotime( $entry->date_recorded ) );


        if ( empty( $parsed[ $date_key ] ) ) {
          $parsed[ $date_key ] = [];

          foreach( $types as $k => $type ) {
            if ( $type == $entry->referrer_type ) {
              $parsed[ $date_key ][ $type ] = 1;
            } else {
              $parsed[ $date_key ][ $type ] = 0;
            }
          }
        } else {
          foreach( $types as $k => $type ) {
            if ( $type == $entry->referrer_type ) {
              $parsed[ $date_key ][ $type ]++;
            }
          }
        }
      endforeach;

      $datasets = [];
      $labels   = [];
      foreach( $parsed as $date => $data ) {
        $labels[] = date( 'M j, Y', strtotime( $date ) );

        foreach( $data as $k => $count ) {
          if ( empty( $datasets[ $k ] ) ) {
            $datasets[$k] = [
              'label' => $k,
              'data'  => [ $count ]
            ];
          } else {
            $datasets[$k]['data'][] = $count;
          }
        }
      }
      ?>
      var lineChart = document.getElementById('referrer-analytics-type-line-chart');
      var lineChartAnalytics= new Chart(lineChart, {
        type: 'line',
        data: {
          labels: <?php echo json_encode( $labels ); ?>,
          fill: false,
          datasets: [
            <?php $count = 0; foreach( $datasets as $key => $data ): ?>
              {
                label: '<?php echo $key; ?>',
                data: <?php echo json_encode( $data['data'] ); ?>,
                borderColor: '<?php echo $predefined_colors[ $count ]; ?>',
                fill: false
              },
            <?php $count++; endforeach; ?>
          ]
        },
        options: {
          scales: {
            xAxes: [{
              display: true,
              scaleLabel: {
                display: true,
                labelString: '<?php _e( 'Date', 'referrer-analytics' ); ?>'
              }
            }],
            yAxes: [{
              display: true,
              scaleLabel: {
                display: true,
                labelString: '<?php _e( 'Count', 'referrer-analytics' ); ?>'
              }
            }]
          }
        }
      });
      </script>
    <?php else: ?>
      <?php _e( 'No data to report yet.', 'referrer-analytics' ); ?>
    <?php endif; ?>
  </div>
</div>
