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
 * @var array  $platform_cards
 * @var array  $event_status
 * @var array  $activity
 * @var int    $last_tested
 * @var bool   $wc_active
 * @var bool   $connected
 * @var bool   $debug_mode
 * @var string $error_msg
 */

defined( 'ABSPATH' ) || exit;

$active_tab = 'data-tracker';
include DTW_PLUGIN_DIR . 'templates/admin/partials/header.php';

$activity_labels = array(
	'product_view'     => __( 'Product View', 'data-tracker-woocommerce' ),
	'add_to_cart'      => __( 'Add to Cart', 'data-tracker-woocommerce' ),
	'remove_from_cart' => __( 'Remove from Cart', 'data-tracker-woocommerce' ),
	'view_cart'        => __( 'View Cart', 'data-tracker-woocommerce' ),
	'begin_checkout'   => __( 'Begin Checkout', 'data-tracker-woocommerce' ),
	'add_payment_info' => __( 'Add Payment Information', 'data-tracker-woocommerce' ),
	'purchase'         => __( 'Purchase', 'data-tracker-woocommerce' ),
	'dtw_test_event'   => __( 'Tracking Test', 'data-tracker-woocommerce' ),
);

$platform_labels = array(
	'ga4'        => 'Google Analytics',
	'meta'       => 'Meta Pixel',
	'google_ads' => 'Google Ads',
	'woocommerce' => 'WooCommerce',
);

$has_problems = ! empty( $problems );

if ( ! $connected ) {
	$status_headline = __( "Your tracking isn't set up yet", 'data-tracker-woocommerce' );
	$status_text     = __( 'Connect Google Analytics, Meta Pixel or Google Ads to start tracking WooCommerce events automatically.', 'data-tracker-woocommerce' );
	$status_icon     = 'dashicons-plugins-checked';
} elseif ( $has_problems ) {
	$status_headline = __( 'Tracking needs attention', 'data-tracker-woocommerce' );
	/* translators: %d: number of issues */
	$status_text     = sprintf( _n( '%d issue may affect your conversion data.', '%d issues may affect your conversion data.', $issue_count, 'data-tracker-woocommerce' ), $issue_count );
	$status_icon     = 'dashicons-warning';
} elseif ( ! $last_tested ) {
	$status_headline = __( "Tracking hasn't been verified yet", 'data-tracker-woocommerce' );
	$status_text     = __( 'Run a test to confirm your events are reaching your connected platforms.', 'data-tracker-woocommerce' );
	$status_icon     = 'dashicons-hourglass';
} else {
	$status_headline = __( 'Tracking is healthy', 'data-tracker-woocommerce' );
	$status_text     = __( 'Your WooCommerce tracking is working normally.', 'data-tracker-woocommerce' );
	$status_icon     = 'dashicons-yes-alt';
}

$connected_labels = array();
foreach ( $platform_cards as $card ) {
	if ( $card['connected'] ) {
		$connected_labels[] = $card['label'];
	}
}
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

	<div class="dtw-card dtw-status-hero">
		<div class="dtw-status-hero__main">
			<h2 class="dtw-status-headline">
				<span class="dtw-status-headline__icon" aria-hidden="true"><span class="dashicons <?php echo esc_attr( $status_icon ); ?>"></span></span>
				<?php echo esc_html( $status_headline ); ?>
			</h2>
			<p class="dtw-status-text"><?php echo esc_html( $status_text ); ?></p>

			<?php if ( $connected ) : ?>
				<p class="dtw-status-delivery" aria-label="<?php esc_attr_e( 'Connected platforms', 'data-tracker-woocommerce' ); ?>">
					<?php foreach ( $connected_labels as $label ) : ?>
						<span class="dtw-status-delivery__item"><span class="dashicons dashicons-yes-alt" aria-hidden="true"></span><?php echo esc_html( $label ); ?></span>
					<?php endforeach; ?>
				</p>
			<?php endif; ?>

			<div class="dtw-button-row dtw-status-actions">
				<?php if ( ! $connected ) : ?>
					<a class="dtw-btn dtw-btn--primary dtw-btn--large" href="<?php echo esc_url( admin_url( 'admin.php?page=data-tracker-connections' ) ); ?>">
						<span class="dashicons dashicons-plugins-checked" aria-hidden="true"></span>
						<?php esc_html_e( 'Connect Platform', 'data-tracker-woocommerce' ); ?>
					</a>
				<?php elseif ( $has_problems ) : ?>
					<a class="dtw-btn dtw-btn--primary dtw-btn--large" href="#dtw-problems">
						<span class="dashicons dashicons-warning" aria-hidden="true"></span>
						<?php esc_html_e( 'Fix Issues', 'data-tracker-woocommerce' ); ?>
					</a>
					<a class="dtw-btn dtw-btn--ghost dtw-btn--large" href="<?php echo esc_url( admin_url( 'admin.php?page=data-tracker-test' ) ); ?>">
						<span class="dashicons dashicons-clipboard" aria-hidden="true"></span>
						<?php esc_html_e( 'Test Tracking', 'data-tracker-woocommerce' ); ?>
					</a>
				<?php else : ?>
					<a class="dtw-btn dtw-btn--primary dtw-btn--large" href="<?php echo esc_url( admin_url( 'admin.php?page=data-tracker-test' ) ); ?>">
						<span class="dashicons dashicons-clipboard" aria-hidden="true"></span>
						<?php esc_html_e( 'Test Tracking', 'data-tracker-woocommerce' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>

		<div class="dtw-status-hero__side">
			<div class="dtw-status-hero__ring" aria-hidden="true">
				<div class="dtw-health-score" style="--dtw-score: <?php echo (int) $score; ?>">
					<span class="dtw-health-score__value"><?php echo (int) $score; ?>%</span>
				</div>
				<span class="dtw-status-hero__ring-label"><?php esc_html_e( 'Tracking Health', 'data-tracker-woocommerce' ); ?></span>
			</div>
			<p class="dtw-status-verified">
				<span class="dashicons <?php echo $last_tested ? 'dashicons-yes-alt' : 'dashicons-hourglass'; ?>" aria-hidden="true"></span>
				<?php esc_html_e( 'Last verified:', 'data-tracker-woocommerce' ); ?>
				<strong><?php echo esc_html( \DataTracker\Diagnostics\EventLog::relative_time( $last_tested ) ); ?></strong>
			</p>
		</div>
	</div>

	<h2 class="dtw-section-title"><?php esc_html_e( 'Platforms', 'data-tracker-woocommerce' ); ?></h2>
	<div class="dtw-grid dtw-grid-3 dtw-platform-grid">
		<?php foreach ( $platform_cards as $card ) : ?>
			<div class="dtw-card dtw-platform-mini">
				<div class="dtw-platform-mini__head">
					<span class="dtw-platform-mini__icon"><span class="dashicons <?php echo esc_attr( $card['icon'] ); ?>"></span></span>
					<span class="dtw-badge <?php echo $card['connected'] ? 'dtw-badge--ok' : 'dtw-badge--muted'; ?>">
						<?php echo $card['connected'] ? esc_html__( 'Connected', 'data-tracker-woocommerce' ) : esc_html__( 'Not connected', 'data-tracker-woocommerce' ); ?>
					</span>
				</div>
				<h3 class="dtw-platform-mini__title"><?php echo esc_html( $card['label'] ); ?></h3>
				<p class="dtw-platform-mini__value"><?php echo $card['connected'] ? '<code>' . esc_html( $card['value'] ) . '</code>' : esc_html__( '—', 'data-tracker-woocommerce' ); ?></p>
				<a class="dtw-btn dtw-btn--small <?php echo esc_attr( $card['action']['class'] ); ?>" href="<?php echo esc_url( $card['action']['url'] ); ?>">
					<?php echo esc_html( $card['action']['label'] ); ?>
				</a>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="dtw-grid dtw-grid-3">
		<div class="dtw-card dtw-card--activity dtw-span-2">
			<div class="dtw-card__head">
				<h2 class="dtw-card__title"><?php esc_html_e( 'Recent Activity', 'data-tracker-woocommerce' ); ?></h2>
				<span class="dtw-badge dtw-badge--info"><?php esc_html_e( 'Live', 'data-tracker-woocommerce' ); ?></span>
			</div>
			<?php if ( empty( $activity ) ) : ?>
				<p class="dtw-card__desc">
					<?php esc_html_e( 'No tracking activity yet. Activity appears when the tracking script runs on your store — for example when a shopper views a product or checks out. Orders created manually from the WooCommerce admin do not produce tracking events.', 'data-tracker-woocommerce' ); ?>
				</p>
				<a class="dtw-btn dtw-btn--small dtw-btn--primary" href="<?php echo esc_url( admin_url( 'admin.php?page=data-tracker-test' ) ); ?>">
					<?php esc_html_e( 'Run a Tracking Test', 'data-tracker-woocommerce' ); ?>
				</a>
			<?php else : ?>
				<ul class="dtw-activity-list">
					<?php foreach ( $activity as $item ) : ?>
						<?php
						$label    = isset( $activity_labels[ $item['name'] ] ) ? $activity_labels[ $item['name'] ] : ucwords( str_replace( '_', ' ', $item['name'] ) );
						$payload  = $item['payload'];
						$order_id = isset( $payload['transaction_id'] ) ? $payload['transaction_id'] : ( isset( $payload['id'] ) ? $payload['id'] : '' );
						$value    = isset( $payload['value'] ) ? $payload['value'] : '';
						$currency = isset( $payload['currency'] ) ? $payload['currency'] : '';
						$items    = isset( $payload['items'] ) && is_array( $payload['items'] ) ? count( $payload['items'] ) : ( isset( $payload['item_count'] ) ? $payload['item_count'] : '' );
						?>
						<li class="dtw-activity">
							<span class="dtw-activity__check" aria-hidden="true"><span class="dashicons dashicons-yes-alt"></span></span>
							<div class="dtw-activity__main">
								<span class="dtw-activity__name"><?php echo esc_html( $label ); ?></span>
								<span class="dtw-activity__platforms">
									<?php if ( empty( $item['platforms'] ) ) : ?>
										<span class="dtw-activity__platform">—</span>
									<?php else : ?>
										<?php foreach ( $item['platforms'] as $pid ) : ?>
											<?php if ( 'woocommerce' === $pid ) : ?>
												<span class="dtw-activity__platform dtw-activity__platform--source"><?php esc_html_e( 'WooCommerce order', 'data-tracker-woocommerce' ); ?></span>
											<?php elseif ( 'test' === $pid ) : ?>
												<span class="dtw-activity__platform dtw-activity__platform--source"><?php esc_html_e( 'Manual test', 'data-tracker-woocommerce' ); ?></span>
											<?php else : ?>
												<span class="dtw-activity__platform"><?php echo esc_html( isset( $platform_labels[ $pid ] ) ? $platform_labels[ $pid ] : $pid ); ?> ✓</span>
											<?php endif; ?>
										<?php endforeach; ?>
									<?php endif; ?>
								</span>
								<?php if ( 'purchase' === $item['name'] ) : ?>
									<span class="dtw-activity__meta">
										<?php
										printf(
											/* translators: 1: order id, 2: value, 3: currency */
											esc_html__( 'Order #%1$s · %2$s %3$s', 'data-tracker-woocommerce' ),
											esc_html( $order_id ? $order_id : '—' ),
											esc_html( '' !== $value ? round( (float) $value, 2 ) : '—' ),
											esc_html( $currency ? $currency : '' )
										);
										?>
										<?php if ( '' !== $items ) : ?>
											<em><?php echo esc_html( sprintf( /* translators: %d: item count */ _n( '(%d item)', '(%d items)', $items, 'data-tracker-woocommerce' ), $items ) ); ?></em>
										<?php endif; ?>
									</span>
								<?php endif; ?>
							</div>
							<span class="dtw-activity__time"><?php echo esc_html( \DataTracker\Diagnostics\EventLog::relative_time( $item['time'] ) ); ?></span>
							<?php if ( $debug_mode ) : ?>
								<details class="dtw-activity__details">
									<summary><?php esc_html_e( 'Technical', 'data-tracker-woocommerce' ); ?></summary>
									<pre class="dtw-activity__json"><?php echo esc_html( wp_json_encode( $item['payload'], JSON_PRETTY_PRINT ) ); ?></pre>
								</details>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<div class="dtw-card">
			<div class="dtw-card__head">
				<h2 class="dtw-card__title"><?php esc_html_e( 'WooCommerce Tracking', 'data-tracker-woocommerce' ); ?></h2>
				<a class="dtw-link" href="<?php echo esc_url( admin_url( 'admin.php?page=data-tracker-tracking' ) ); ?>"><?php esc_html_e( 'Manage', 'data-tracker-woocommerce' ); ?></a>
			</div>
			<?php
				$state_badges = array(
					'working'    => array( 'dtw-badge--ok', __( 'Working', 'data-tracker-woocommerce' ) ),
					'waiting'    => array( 'dtw-badge--warn', __( 'No activity yet', 'data-tracker-woocommerce' ) ),
					'unverified' => array( 'dtw-badge--muted', __( 'Logging off', 'data-tracker-woocommerce' ) ),
					'off'        => array( 'dtw-badge--muted', __( 'Off', 'data-tracker-woocommerce' ) ),
				);
				$dot_classes  = array(
					'working'    => 'is-ok',
					'waiting'    => 'is-warn',
					'unverified' => 'is-off',
					'off'        => 'is-off',
				);
				$has_waiting  = false;
				?>
			<ul class="dtw-status-list">
				<?php foreach ( $event_status as $event ) : ?>
					<?php $has_waiting = $has_waiting || ( 'waiting' === $event['state'] ); ?>
					<li class="dtw-status <?php echo esc_attr( $dot_classes[ $event['state'] ] ); ?>" title="<?php echo esc_attr( isset( $state_badges[ $event['state'] ][1] ) ? $state_badges[ $event['state'] ][1] : '' ); ?>">
						<span class="dtw-status__dot" aria-hidden="true"></span>
						<span class="dtw-status__label"><?php echo esc_html( $event['label'] ); ?></span>
						<span class="dtw-badge <?php echo esc_attr( $state_badges[ $event['state'] ][0] ); ?>">
							<?php echo esc_html( $state_badges[ $event['state'] ][1] ); ?>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( $has_waiting ) : ?>
				<p class="dtw-card__desc">
					<?php esc_html_e( 'Enabled events show "No activity yet" until the tracker reports them from your store (for example a visit, add to cart, or checkout).', 'data-tracker-woocommerce' ); ?>
				</p>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( ! empty( $other_tracking ) ) : ?>
		<div class="dtw-card dtw-card--muted">
			<div class="dtw-card__head">
				<h2 class="dtw-card__title"><?php esc_html_e( 'Other tracking found on this site', 'data-tracker-woocommerce' ); ?></h2>
				<span class="dtw-badge dtw-badge--warn"><?php esc_html_e( 'Review', 'data-tracker-woocommerce' ); ?></span>
			</div>
			<p class="dtw-card__desc">
				<?php
				/* translators: %s: GTM container ids */
				printf( esc_html__( 'We detected Google Tag Manager (%s). Tag Manager may also load Google or advertising tags, which can double-count page views or hide events from directly-loaded tracking. We cannot confirm what it loads.', 'data-tracker-woocommerce' ), esc_html( implode( ', ', (array) $other_tracking ) ) );
				?>
			</p>
			<details class="dtw-problem__how">
				<summary><?php esc_html_e( 'Show Me How', 'data-tracker-woocommerce' ); ?></summary>
				<p class="dtw-problem__solution">
					<?php esc_html_e( 'If you use Data Tracker for Google Analytics, remove the GA4 tag from Tag Manager so only Data Tracker loads it. Otherwise review what Tag Manager loads and disable GA4 there to avoid duplicates.', 'data-tracker-woocommerce' ); ?>
				</p>
			</details>
		</div>
	<?php endif; ?>

	<?php if ( $has_problems ) : ?>
		<div class="dtw-card" id="dtw-problems">
			<div class="dtw-card__head">
				<h2 class="dtw-card__title"><?php echo esc_html( 1 === $issue_count ? __( '1 thing needs your attention', 'data-tracker-woocommerce' ) : sprintf( /* translators: %d */ __( '%d things need your attention', 'data-tracker-woocommerce' ), $issue_count ) ); ?></h2>
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
				<?php if ( $last_tested ) : ?>
					<?php esc_html_e( 'Your tracking is verified. No action required.', 'data-tracker-woocommerce' ); ?>
				<?php else : ?>
					<?php esc_html_e( 'Everything is configured. Run a tracking test to verify it.', 'data-tracker-woocommerce' ); ?>
				<?php endif; ?>
			</p>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $next_steps ) ) : ?>
		<div class="dtw-card dtw-card--muted">
			<div class="dtw-card__head">
				<h2 class="dtw-card__title"><?php esc_html_e( 'Next step', 'data-tracker-woocommerce' ); ?></h2>
			</div>
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
			<?php if ( empty( $activity ) ) : ?>
				<p><?php esc_html_e( 'No events received yet. Visit your store or run a tracking test to generate events.', 'data-tracker-woocommerce' ); ?></p>
			<?php else : ?>
				<table class="widefat striped dtw-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Time', 'data-tracker-woocommerce' ); ?></th>
							<th><?php esc_html_e( 'Event', 'data-tracker-woocommerce' ); ?></th>
							<th><?php esc_html_e( 'Platforms', 'data-tracker-woocommerce' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $activity as $item ) : ?>
							<tr>
								<td><?php echo esc_html( gmdate( 'M j, H:i:s', $item['time'] ) ); ?></td>
								<td><code><?php echo esc_html( $item['name'] ); ?></code></td>
								<td><?php echo esc_html( implode( ', ', array_map( function ( $p ) use ( $platform_labels ) { return isset( $platform_labels[ $p ] ) ? $platform_labels[ $p ] : $p; }, $item['platforms'] ) ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<?php if ( $debug_mode ) : ?>
				<h3 class="dtw-card__title"><?php esc_html_e( 'Event payloads', 'data-tracker-woocommerce' ); ?></h3>
				<pre class="dtw-test-preview"><?php echo esc_html( wp_json_encode( $activity, JSON_PRETTY_PRINT ) ); ?></pre>
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
				<?php esc_html_e( 'Last verified:', 'data-tracker-woocommerce' ); ?>
				<?php echo esc_html( \DataTracker\Diagnostics\EventLog::relative_time( $last_tested ) ); ?>
			</p>
		</div>
	</details>
</div>
</div>