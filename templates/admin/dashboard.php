<?php
/**
 * Dashboard template.
 *
 * @package DataTracker
 * @var int    $score
 * @var string $summary
 * @var int    $issue_count
 * @var array  $problems
 * @var array  $next_steps
 * @var array  $platforms
 * @var array  $event_status
 * @var array  $recent_events
 * @var int    $last_tested
 * @var bool   $wc_active
 * @var bool   $connected
 * @var bool   $debug_mode
 * @var string $error_msg
 */

defined( 'ABSPATH' ) || exit;

$active_tab = 'data-tracker';
include DTW_PLUGIN_DIR . 'templates/admin/partials/header.php';
?>

	<?php if ( '' !== $error_msg ) : ?>
		<div class="notice notice-error is-dismissible"><p><?php echo esc_html( $error_msg ); ?></p></div>
	<?php endif; ?>

	<?php if ( ! $wc_active ) : ?>
		<div class="dtw-alert dtw-alert--warn">
			<span class="dashicons dashicons-warning" aria-hidden="true"></span>
			<span><?php esc_html_e( 'WooCommerce is not active. Activate WooCommerce to start tracking store events.', 'data-tracker-woocommerce' ); ?></span>
		</div>
	<?php endif; ?>

	<?php if ( ! $connected ) : ?>
		<div class="dtw-card dtw-empty">
			<div class="dtw-empty__icon"><span class="dashicons dashicons-plugins-checked" aria-hidden="true"></span></div>
			<h2 class="dtw-empty__title"><?php esc_html_e( "Your tracking isn't connected yet.", 'data-tracker-woocommerce' ); ?></h2>
			<p class="dtw-empty__text">
				<?php esc_html_e( 'Connect Google Analytics, Meta Pixel, or Google Ads to get started. Store events are tracked automatically after that.', 'data-tracker-woocommerce' ); ?>
			</p>
			<a class="dtw-btn dtw-btn--primary dtw-btn--large" href="<?php echo esc_url( admin_url( 'admin.php?page=data-tracker-connections' ) ); ?>">
				<span class="dashicons dashicons-plugins-checked" aria-hidden="true"></span>
				<?php esc_html_e( 'Connect Platform', 'data-tracker-woocommerce' ); ?>
			</a>
		</div>
	<?php endif; ?>

	<div class="dtw-hero dtw-grid-3">
		<div class="dtw-card dtw-health-card">
			<div class="dtw-health-score" style="--dtw-score: <?php echo (int) $score; ?>">
				<div class="dtw-health-score__value"><?php echo (int) $score; ?>%</div>
			</div>
			<h2 class="dtw-card__title"><?php esc_html_e( 'Tracking Health', 'data-tracker-woocommerce' ); ?></h2>
			<p class="dtw-health-summary"><?php echo esc_html( $summary ); ?></p>

			<?php if ( $issue_count > 0 ) : ?>
				<p class="dtw-health-issues">
					<span class="dashicons dashicons-warning" aria-hidden="true"></span>
					<?php
					/* translators: %d: number of issues */
					echo esc_html( sprintf( _n( '%d issue needs attention', '%d issues need attention', $issue_count, 'data-tracker-woocommerce' ), $issue_count ) );
					?>
				</p>
			<?php endif; ?>

			<p class="dtw-health-tested">
				<span class="dashicons <?php echo $last_tested ? 'dashicons-yes-alt' : 'dashicons-hourglass'; ?>" aria-hidden="true"></span>
				<?php esc_html_e( 'Last tracking test:', 'data-tracker-woocommerce' ); ?>
				<strong><?php echo esc_html( \DataTracker\Diagnostics\EventLog::relative_time( $last_tested ) ); ?></strong>
			</p>

			<a class="dtw-btn dtw-btn--primary" href="<?php echo esc_url( admin_url( 'admin.php?page=data-tracker-test' ) ); ?>">
				<span class="dashicons dashicons-clipboard" aria-hidden="true"></span>
				<?php esc_html_e( 'Test Tracking', 'data-tracker-woocommerce' ); ?>
			</a>
		</div>

		<div class="dtw-card">
			<div class="dtw-card__head">
				<h2 class="dtw-card__title"><?php esc_html_e( 'Connected Platforms', 'data-tracker-woocommerce' ); ?></h2>
				<a class="dtw-link" href="<?php echo esc_url( admin_url( 'admin.php?page=data-tracker-connections' ) ); ?>"><?php esc_html_e( 'Manage', 'data-tracker-woocommerce' ); ?></a>
			</div>
			<ul class="dtw-status-list">
				<?php foreach ( $platforms as $platform ) : ?>
					<?php $status = $platform->get_status(); ?>
					<li class="dtw-status <?php echo $status['connected'] ? 'is-ok' : 'is-off'; ?>">
						<span class="dtw-status__dot" aria-hidden="true"></span>
						<span class="dtw-status__label"><?php echo esc_html( $status['label'] ); ?></span>
						<span class="dtw-badge <?php echo $status['connected'] ? 'dtw-badge--ok' : 'dtw-badge--muted'; ?>">
							<?php echo $status['connected'] ? esc_html__( 'Connected', 'data-tracker-woocommerce' ) : esc_html__( 'Not connected', 'data-tracker-woocommerce' ); ?>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( $connected && ! $last_tested ) : ?>
				<p class="dtw-card__desc">
					<?php esc_html_e( 'Platforms are connected but tracking has not been tested yet.', 'data-tracker-woocommerce' ); ?>
				</p>
			<?php elseif ( $connected && $last_tested ) : ?>
				<p class="dtw-card__desc">
					<?php
					/* translators: %s: relative time, e.g. "5 minutes ago" */
					echo esc_html( sprintf( __( 'Tracking tested %s.', 'data-tracker-woocommerce' ), \DataTracker\Diagnostics\EventLog::relative_time( $last_tested ) ) );
					?>
				</p>
			<?php endif; ?>
		</div>

		<div class="dtw-card">
			<div class="dtw-card__head">
				<h2 class="dtw-card__title"><?php esc_html_e( 'WooCommerce Tracking', 'data-tracker-woocommerce' ); ?></h2>
				<a class="dtw-link" href="<?php echo esc_url( admin_url( 'admin.php?page=data-tracker-tracking' ) ); ?>"><?php esc_html_e( 'Manage', 'data-tracker-woocommerce' ); ?></a>
			</div>
			<ul class="dtw-status-list">
				<?php foreach ( $event_status as $event ) : ?>
					<li class="dtw-status <?php echo $event['enabled'] ? 'is-ok' : 'is-off'; ?>">
						<span class="dtw-status__dot" aria-hidden="true"></span>
						<span class="dtw-status__label"><?php echo esc_html( $event['label'] ); ?></span>
						<span class="dtw-badge <?php echo $event['enabled'] ? 'dtw-badge--ok' : 'dtw-badge--muted'; ?>">
							<?php echo $event['enabled'] ? esc_html__( 'Working', 'data-tracker-woocommerce' ) : esc_html__( 'Off', 'data-tracker-woocommerce' ); ?>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>

	<div class="dtw-card dtw-card--activity">
		<div class="dtw-card__head">
			<h2 class="dtw-card__title"><?php esc_html_e( 'Recent Activity', 'data-tracker-woocommerce' ); ?></h2>
			<span class="dtw-badge dtw-badge--info"><?php esc_html_e( 'Live', 'data-tracker-woocommerce' ); ?></span>
		</div>
		<?php if ( empty( $recent_events ) ) : ?>
			<p class="dtw-card__desc">
				<?php esc_html_e( 'No activity yet. Visit your store or run a tracking test to generate events.', 'data-tracker-woocommerce' ); ?>
			</p>
		<?php else : ?>
			<ul class="dtw-activity-list">
				<?php foreach ( $recent_events as $entry ) : ?>
					<li class="dtw-activity">
						<span class="dtw-activity__check" aria-hidden="true"><span class="dashicons dashicons-yes-alt"></span></span>
						<span class="dtw-activity__name"><?php echo esc_html( $entry['name'] ); ?></span>
						<span class="dtw-activity__time">
							<?php echo esc_html( \DataTracker\Diagnostics\EventLog::relative_time( (int) $entry['time'] ) ); ?>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $problems ) ) : ?>
		<div class="dtw-card">
			<div class="dtw-card__head">
				<h2 class="dtw-card__title"><?php esc_html_e( 'Things to check', 'data-tracker-woocommerce' ); ?></h2>
			</div>
			<?php foreach ( $problems as $problem ) : ?>
				<div class="dtw-problem">
					<div class="dtw-problem__top">
						<span class="dtw-problem__icon <?php echo 'error' === $problem['status'] ? 'is-error' : ''; ?>" aria-hidden="true">
							<?php echo 'error' === $problem['status'] ? '!' : '?'; ?>
						</span>
						<strong class="dtw-problem__title"><?php echo esc_html( $problem['label'] ); ?></strong>
						<span class="dtw-badge <?php echo 'error' === $problem['status'] ? 'dtw-badge--danger' : 'dtw-badge--warn'; ?>">
							<?php echo 'error' === $problem['status'] ? esc_html__( 'Needs attention', 'data-tracker-woocommerce' ) : esc_html__( 'Check recommended', 'data-tracker-woocommerce' ); ?>
						</span>
					</div>
					<p class="dtw-problem__message"><?php echo esc_html( $problem['message'] ); ?></p>
					<div class="dtw-problem__actions">
						<details class="dtw-problem__how">
							<summary><?php esc_html_e( 'Show Me How', 'data-tracker-woocommerce' ); ?></summary>
							<p class="dtw-problem__solution"><?php echo esc_html( $problem['solution'] ); ?></p>
						</details>
						<?php if ( ! empty( $problem['action'] ) ) : ?>
							<a class="dtw-btn dtw-btn--small dtw-btn--primary" href="<?php echo esc_url( $problem['action'] ); ?>"><?php esc_html_e( 'Fix Issue', 'data-tracker-woocommerce' ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<div class="dtw-card dtw-card--success">
			<p class="dtw-all-good">
				<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
				<?php esc_html_e( 'No critical problems detected. Your tracking looks good.', 'data-tracker-woocommerce' ); ?>
			</p>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $next_steps ) ) : ?>
		<div class="dtw-card dtw-card--muted">
			<h2 class="dtw-card__title"><?php esc_html_e( 'Next steps', 'data-tracker-woocommerce' ); ?></h2>
			<ul class="dtw-next-steps">
				<?php foreach ( $next_steps as $step ) : ?>
					<li class="dtw-next-step">
						<span class="dashicons dashicons-arrow-right-alt" aria-hidden="true"></span>
						<span class="dtw-next-step__label"><?php echo esc_html( $step['label'] ); ?></span>
						<?php if ( ! empty( $step['action'] ) ) : ?>
							<a class="dtw-link" href="<?php echo esc_url( $step['action'] ); ?>"><?php esc_html_e( 'Do it', 'data-tracker-woocommerce' ); ?></a>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<details class="dtw-details">
		<summary>
			<span class="dashicons dashicons-editor-code" aria-hidden="true"></span>
			<?php esc_html_e( 'Technical Details', 'data-tracker-woocommerce' ); ?>
		</summary>
		<div class="dtw-card dtw-card--technical">
			<h3 class="dtw-card__title"><?php esc_html_e( 'Recent tracking events', 'data-tracker-woocommerce' ); ?></h3>
			<?php if ( empty( $recent_events ) ) : ?>
				<p><?php esc_html_e( 'No events received yet. Visit your store or run a tracking test to generate events.', 'data-tracker-woocommerce' ); ?></p>
			<?php else : ?>
				<table class="widefat striped dtw-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Time', 'data-tracker-woocommerce' ); ?></th>
							<th><?php esc_html_e( 'Event', 'data-tracker-woocommerce' ); ?></th>
							<th><?php esc_html_e( 'Platform', 'data-tracker-woocommerce' ); ?></th>
							<th><?php esc_html_e( 'Page', 'data-tracker-woocommerce' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $recent_events as $entry ) : ?>
							<tr>
								<td><?php echo esc_html( gmdate( 'M j, H:i:s', (int) $entry['time'] ) ); ?></td>
								<td><code><?php echo esc_html( $entry['name'] ); ?></code></td>
								<td><?php echo esc_html( $entry['platform'] ); ?></td>
								<td><?php echo esc_html( $entry['page_type'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<?php if ( $debug_mode ) : ?>
				<h3 class="dtw-card__title"><?php esc_html_e( 'Event payloads', 'data-tracker-woocommerce' ); ?></h3>
				<pre class="dtw-test-preview"><?php echo esc_html( wp_json_encode( $recent_events, JSON_PRETTY_PRINT ) ); ?></pre>
			<?php endif; ?>

			<h3 class="dtw-card__title"><?php esc_html_e( 'Tracking IDs', 'data-tracker-woocommerce' ); ?></h3>
			<ul class="dtw-id-list">
				<?php foreach ( $platforms as $platform ) : ?>
					<?php $status = $platform->get_status(); ?>
					<li>
						<span><?php echo esc_html( $status['label'] ); ?>:</span>
						<code><?php echo $status['connected'] ? esc_html( $status['value'] ) : esc_html__( '—', 'data-tracker-woocommerce' ); ?></code>
					</li>
				<?php endforeach; ?>
			</ul>

			<p class="description">
				<?php esc_html_e( 'Plugin version:', 'data-tracker-woocommerce' ); ?>
				<?php echo esc_html( DTW_VERSION ); ?>
				&middot;
				<?php esc_html_e( 'WooCommerce:', 'data-tracker-woocommerce' ); ?>
				<?php echo $wc_active && defined( 'WC_VERSION' ) ? esc_html( WC_VERSION ) : esc_html__( 'inactive', 'data-tracker-woocommerce' ); ?>
				&middot;
				<?php esc_html_e( 'Last tested:', 'data-tracker-woocommerce' ); ?>
				<?php echo esc_html( \DataTracker\Diagnostics\EventLog::relative_time( $last_tested ) ); ?>
			</p>
		</div>
	</details>
</div>
</div>