<?php
/**
 * Types line chart
 *
 * @package ReferrerAnalytics
 * @since 1.0.0
 */
?>
<div class="referrer-analytics-box referrer-analytics-box-referrers-line-chart">
  <h3><?php _e( 'Most Popular Referrers by Date', 'referrer-analytics' ); ?></h3>
  <div class="inside">
    <?php if ( $log ): ?>
      <canvas id="referrer-analytics-referrers-line-chart"></canvas>
      <script>
      <?php
      $parsed = [];
      $names  = [];
      $limit  = 6;

      // Get names
      foreach( $log as $key => $entry ):
        $referrer_name = ! empty( $entry->referrer_name ) ? $entry->referrer_name  : 'N/A';

        if ( empty( $names[ $referrer_name ] ) ) {
          $names[ $referrer_name ] = [
            'name'  => $referrer_name,
            'count' => 1
          ];
        } else {
          $names[ $referrer_name ]['count']++;
        }
      endforeach;

      usort($names, function($a, $b) {
        return $b['count'] <=> $a['count'];
      });

      $cut = count( $names ) - $limit;
      array_splice( $names, count( $names ) - $cut, $cut );


      foreach( $log as $key => $entry ):
        $date_key      = date( 'Y-m-d', strtotime( $entry->date_recorded ) );
        $referrer_name = ! empty( $entry->referrer_name ) ? $entry->referrer_name  : 'N/A';

        if ( empty( $parsed[ $date_key ] ) ) {
          $parsed[ $date_key ] = [];

          foreach( $names as $k => $name ) {
            $parsed[ $date_key ][ $name['name'] ] = 0;
            if ( $name['name'] == $referrer_name ) {
              $parsed[ $date_key ][ $name['name'] ] = 1;
            }
          }
        } else {
          foreach( $names as $k => $name ) {
            if ( $name['name'] == $referrer_name ) {
              $parsed[ $date_key ][ $name['name'] ]++;
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
      var lineChart = document.getElementById('referrer-analytics-referrers-line-chart');
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
                backgroundColor: '<?php echo $predefined_colors[ $count ]; ?>',
                borderColor: '<?php echo $predefined_colors[ $count ]; ?>',
                fill: false
              },
            <?php $count++; endforeach; ?>
          ]
        },
        options: {
          responsive: true,
          tooltips: {
            mode: 'index',
            intersect: false
          },
          hover: {
            mode: 'nearest',
            intersect: true
          },
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
