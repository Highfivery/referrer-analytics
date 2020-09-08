<?php
/**
 * Pie chart
 *
 * @package ReferrerAnalytics
 */

$parsed       = $this->parse_log( $log, $period, $count, $type );
$labels_array = array();
$totals_array = array();
foreach ( $parsed['log'] as $key => $value ) {
	$labels_array[] = $value['label'];
	$totals_array[] = $value['total'];
}
?>
<div class="referrer-analytics-box referrer-analytics-box-type-pie">
	<h3><?php esc_html_e( 'Top ' . $count . ' ' . $type_title . ' (last ' . $period . ' days)', 'referrer-analytics' ); ?></h3>
	<div class="inside">
		<canvas id="referrer-analytics-pie-referrers-<?php echo esc_attr( $period ); ?>-<?php echo esc_attr( $type ); ?>"></canvas>
		<script>
		var referrers = document.getElementById('referrer-analytics-pie-referrers-<?php echo esc_attr( $period ); ?>-<?php echo esc_attr( $type ); ?>');
		var referrerAnalyticsPie = new Chart(referrers, {
			type: 'pie',
			options: {
				legend: {
					position: 'right',
					fullWidth: false
				}
			},
			data: {
				labels: <?php echo wp_json_encode( $labels_array ); ?>,
				datasets: [{
					data: <?php echo wp_json_encode( $totals_array ); ?>,
					backgroundColor: <?php echo wp_json_encode( $colors ); ?>,
					borderColor: '#f1f1f1'
				}]
			}
		});
		</script>
	</div>
</div>
