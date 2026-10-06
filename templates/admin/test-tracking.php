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
		<p><?php esc_html_e( 'This fires identifiable test events for Product View, Add to Cart, Checkout and Purchase through the same tracking code your store uses — directly from this page, no visitors needed. Confirm them in GA4 Realtime/DebugView or Meta Event Testing.', 'data-tracker-woocommerce' ); ?></p>
		<p class="dtw-card__desc">
			<?php esc_html_e( 'In your platforms these test events appear with the prefix dtw_test_ (for example dtw_test_add_to_cart). That is intentional — a test could otherwise be counted as a real conversion. Real visitor events use their normal names, like add_to_cart, begin_checkout and purchase.', 'data-tracker-woocommerce' ); ?>
		</p>
		<p class="dtw-card__desc"><?php esc_html_e( 'No test purchase order is created and no fake revenue or conversion is sent.', 'data-tracker-woocommerce' ); ?></p>

		<?php if ( $consent['enabled'] ) : ?>
			<div class="dtw-alert dtw-alert--warn">
				<span class="dashicons dashicons-warning" aria-hidden="true"></span>
				<span>
					<?php if ( $consent['api_available'] ) : ?>
						<?php esc_html_e( 'Consent mode is on — platforms only receive events after a visitor grants consent. Grant consent (marketing) before testing, otherwise the event will not arrive.', 'data-tracker-woocommerce' ); ?>
					<?php else : ?>
						<?php esc_html_e( 'Consent mode is on, but no WP Consent API consent plugin was detected. Platforms will not receive events until consent is granted, so the test may appear to do nothing in your platform.', 'data-tracker-woocommerce' ); ?>
					<?php endif; ?>
				</span>
			</div>
		<?php endif; ?>
		<p>
			<button type="button" id="dtw-send-test" class="dtw-btn dtw-btn--primary dtw-btn--large" <?php disabled( ! $any_connected ); ?>>
				<span class="dashicons dashicons-send" aria-hidden="true"></span>
				<?php esc_html_e( 'Send Test Event', 'data-tracker-woocommerce' ); ?>
			</button>
		</p>
		<p id="dtw-test-status" class="dtw-test-status" role="status"></p>
		<div id="dtw-test-results" class="dtw-test-results"></div>

		<div id="dtw-test-result" class="dtw-test-result" hidden>
			<h3 class="dtw-card__title"><?php esc_html_e( 'How to confirm it worked', 'data-tracker-woocommerce' ); ?></h3>
			<ol class="dtw-test-steps">
				<li><?php esc_html_e( 'Google Analytics: open Reports > Realtime (or DebugView) on the property that matches your Measurement ID, and look for the test event within a few seconds. Standard Events reports can take up to 24-48 hours to show a new event.', 'data-tracker-woocommerce' ); ?></li>
				<li><?php esc_html_e( 'Meta: open Events Manager > your pixel > Test Events, then browse your store.', 'data-tracker-woocommerce' ); ?></li>
				<li><?php esc_html_e( 'If nothing appears in Realtime, check that consent is granted, that ad / script blockers are disabled for your site, and that the Measurement ID belongs to the property you are viewing.', 'data-tracker-woocommerce' ); ?></li>
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