<?php
/**
 * Test Tracking template.
 *
 * @package DataTracker
 * @var array  $event_checks
 * @var array  $platform_checks
 * @var bool   $any_connected
 * @var bool   $wc_active
 */

defined( 'ABSPATH' ) || exit;

$active_tab = 'data-tracker-test';
include DTW_PLUGIN_DIR . 'templates/admin/partials/header.php';
?>

	<div class="dtw-page-head">
		<div>
			<h1 class="dtw-page-title"><?php esc_html_e( 'Test Tracking', 'data-tracker-woocommerce' ); ?></h1>
			<p class="dtw-page-intro"><?php esc_html_e( 'Check that your store tracking is set up correctly and send a test event through your real tracking setup.', 'data-tracker-woocommerce' ); ?></p>
		</div>
	</div>

	<?php if ( ! $any_connected ) : ?>
		<div class="dtw-card dtw-empty">
			<div class="dtw-empty__icon"><span class="dashicons dashicons-plugins-checked" aria-hidden="true"></span></div>
			<h2 class="dtw-empty__title"><?php esc_html_e( 'Connect a platform before testing.', 'data-tracker-woocommerce' ); ?></h2>
			<p class="dtw-empty__text">
				<?php esc_html_e( 'Connect Google Analytics, Meta Pixel, or Google Ads first, then come back here to test your tracking.', 'data-tracker-woocommerce' ); ?>
			</p>
			<a class="dtw-btn dtw-btn--primary dtw-btn--large" href="<?php echo esc_url( admin_url( 'admin.php?page=data-tracker-connections' ) ); ?>">
				<span class="dashicons dashicons-plugins-checked" aria-hidden="true"></span>
				<?php esc_html_e( 'Connect Platform', 'data-tracker-woocommerce' ); ?>
			</a>
		</div>
	<?php else : ?>

	<div class="dtw-grid dtw-grid-2">
		<div class="dtw-card">
			<h2 class="dtw-card__title"><?php esc_html_e( 'Store Events', 'data-tracker-woocommerce' ); ?></h2>
			<ul class="dtw-status-list">
				<?php foreach ( $event_checks as $check ) : ?>
					<li class="dtw-status <?php echo $check['ok'] ? 'is-ok' : 'is-off'; ?>">
						<span class="dtw-status__dot" aria-hidden="true"></span>
						<span class="dtw-status__label"><?php echo esc_html( $check['label'] ); ?></span>
						<span class="dtw-badge <?php echo $check['ok'] ? 'dtw-badge--ok' : 'dtw-badge--muted'; ?>">
							<?php echo $check['ok'] ? esc_html__( 'Ready', 'data-tracker-woocommerce' ) : esc_html__( 'Needs attention', 'data-tracker-woocommerce' ); ?>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( ! $wc_active ) : ?>
				<p class="description"><?php esc_html_e( 'WooCommerce is not active. Activate it to track store events.', 'data-tracker-woocommerce' ); ?></p>
			<?php endif; ?>
		</div>

		<div class="dtw-card">
			<h2 class="dtw-card__title"><?php esc_html_e( 'Platforms', 'data-tracker-woocommerce' ); ?></h2>
			<ul class="dtw-status-list">
				<?php foreach ( $platform_checks as $check ) : ?>
					<li class="dtw-status <?php echo $check['ok'] ? 'is-ok' : 'is-off'; ?>">
						<span class="dtw-status__dot" aria-hidden="true"></span>
						<span class="dtw-status__label"><?php echo esc_html( $check['label'] ); ?></span>
						<span class="dtw-badge <?php echo $check['ok'] ? 'dtw-badge--ok' : 'dtw-badge--muted'; ?>">
							<?php echo $check['ok'] ? esc_html__( 'Connected', 'data-tracker-woocommerce' ) : esc_html__( 'Not connected', 'data-tracker-woocommerce' ); ?>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>

	<div class="dtw-card dtw-test-card">
		<div class="dtw-card__head">
			<h2 class="dtw-card__title"><?php esc_html_e( 'Send a test event', 'data-tracker-woocommerce' ); ?></h2>
			<span class="dtw-badge dtw-badge--info"><?php esc_html_e( 'Recommended', 'data-tracker-woocommerce' ); ?></span>
		</div>
		<p><?php esc_html_e( 'This opens your store and sends a test event through Google Analytics and Meta. Then confirm it inside their testing tools.', 'data-tracker-woocommerce' ); ?></p>
		<p>
			<button type="button" id="dtw-send-test" class="dtw-btn dtw-btn--primary dtw-btn--large" <?php disabled( ! $any_connected ); ?>>
				<span class="dashicons dashicons-send" aria-hidden="true"></span>
				<?php esc_html_e( 'Send Test Event', 'data-tracker-woocommerce' ); ?>
			</button>
		</p>
		<p id="dtw-test-status" class="dtw-test-status" role="status"></p>

		<div id="dtw-test-result" class="dtw-test-result" hidden>
			<h3 class="dtw-card__title"><?php esc_html_e( 'How to confirm it worked', 'data-tracker-woocommerce' ); ?></h3>
			<ol class="dtw-test-steps">
				<li><?php esc_html_e( 'Google Analytics: open your property, go to Reports > Realtime (or DebugView) and look for the test event.', 'data-tracker-woocommerce' ); ?></li>
				<li><?php esc_html_e( 'Meta: open Events Manager > your pixel > Test Events, then browse your store.', 'data-tracker-woocommerce' ); ?></li>
				<li><?php esc_html_e( 'Events can take a few minutes to appear in reporting.', 'data-tracker-woocommerce' ); ?></li>
			</ol>
			<details class="dtw-details">
				<summary>
					<span class="dashicons dashicons-editor-code" aria-hidden="true"></span>
					<?php esc_html_e( 'Technical Details', 'data-tracker-woocommerce' ); ?>
				</summary>
				<pre id="dtw-test-preview" class="dtw-test-preview"></pre>
			</details>
		</div>
	</div>

	<?php endif; ?>
</div>
</div>