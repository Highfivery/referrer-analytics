<?php
/**
 * Unknowns
 *
 * @package ReferrerAnalytics
 */

$unknowns = array();
foreach ( $log as $key => $entry ) {
	if ( empty( $entry['referrer_name'] ) ) {
		if ( empty( $unknowns[ $entry['referrer_host'] ] ) ) {
			$unknowns[ $entry['referrer_host'] ] = 1;
		} else {
			$unknowns[ $entry['referrer_host'] ]++;
		}
	}
}

arsort( $unknowns );

$unknowns = array_slice( $unknowns, 0, 100 );
?>
<div class="referrer-analytics-box referrer-analytics-box-unknown-referrers">
	<h3><?php esc_html_e( 'Top 100 Undefined Referrers', 'referrer-analytics' ); ?></h3>
	<div class="inside">
		<ol class="referreranalytics-list">
			<?php foreach ( $unknowns as $host => $count ): ?>
				<li>
					<span class="referreranalytics-list-label"><?php echo $host; ?></span>
					<span class="referreranalytics-list-count"><?php echo number_format( $count, 0 ); ?></span>
			</li>
			<?php endforeach; ?>
		</ol>
	</div>
</div>
