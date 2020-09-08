<?php
/**
 * Popular list
 *
 * @package ReferrerAnalytics
 */

$parsed = $this->parse_log( $log, $period, $count, $type );
?>
<div class="referrer-analytics-box referrer-analytics-box-popular-list">
	<h3><?php esc_html_e( 'Top ' . $count . ' ' . $type_title . ' (last ' . $period . ' days)', 'referrer-analytics' ); ?></h3>
	<div class="inside">
		<ol class="referreranalytics-list">
			<?php foreach ( $parsed['log'] as $key => $data ): ?>
				<li>
					<span class="referreranalytics-list-label">
						<?php
						if ( ! empty( $data['referrer_primary_url'] ) || ! empty( $data['referrer_url'] ) || ! empty( $data['url_destination'] ) ) :
							if ( ! empty( $data['referrer_primary_url'] ) ) {
								$url = $data['referrer_primary_url'];
							} elseif ( ! empty( $data['referrer_url'] ) ) {
								$url = $data['referrer_url'];
							} elseif ( ! empty( $data['url_destination'] ) ) {
								$url = $data['url_destination'];
							}
							?>
							<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noreferrer noopener">
						<?php endif; ?>
						<?php echo $data['label']; ?>
						<?php if ( ! empty( $data['referrer_primary_url'] ) || ! empty( $data['referrer_url'] ) || ! empty( $data['url_destination'] ) ) : ?>
							</a>
						<?php endif; ?>
					</span>
					<span class="referreranalytics-list-count">
						<?php echo number_format( $data['total'], 0 ); ?>
					</span>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</div>
