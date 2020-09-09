<?php
/**
 * Types line chart
 *
 * @package ReferrerAnalytics
 */

$parsed = $this->parse_log( $log, $period, $count, $type );
?>
<div class="referrer-analytics-box referrer-analytics-box-line-chart">
	<h3><?php esc_html_e( 'Top ' . $count . ' ' . $type_title . ' (last ' . $period . ' days)', 'referrer-analytics' ); ?></h3>
	<div class="inside">
		<canvas id="referrer-analytics-referrers-line-chart-<?php echo esc_attr( $period ); ?>-<?php echo esc_attr( $type ); ?>"></canvas>
		<script>
		var lineChart = document.getElementById('referrer-analytics-referrers-line-chart-<?php echo esc_attr( $period ); ?>-<?php echo esc_attr( $type ); ?>');
		var lineChartAnalytics= new Chart(lineChart, {
			type: 'line',
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
							labelString: 'Date'
						}
					}],
					yAxes: [{
						display: true,
						scaleLabel: {
							display: true,
							labelString: 'Count'
						}
					}]
				}
			},
			data: {
				fill: false,
				labels: <?php echo wp_json_encode( $parsed['labels'] ); ?>,
				datasets: [
					<?php foreach ( $parsed['log'] as $key => $data ) : ?>
						{
							label: '<?php echo esc_attr( $data['label'] ); ?>',
							data: <?php echo wp_json_encode( array_values( $data['data'] ) ); ?>,
							backgroundColor: '<?php echo esc_attr( $colors[ $key ] ); ?>',
							borderColor: '<?php echo esc_attr( $colors[ $key ] ); ?>',
							fill: false
						},
					<?php endforeach; ?>
				]
			}
		});
		</script>
  </div>
</div>
