<?php
/**
 * Settings template.
 *
 * @package DataTracker
 * @var array $settings
 */

defined( 'ABSPATH' ) || exit;

$active_tab = 'data-tracker-settings';
include DTW_PLUGIN_DIR . 'templates/admin/partials/header.php';

$save_url = admin_url( 'admin-post.php' );
$consent  = $settings['consent_defaults'];
?>

	<div class="dtw-page-head">
		<div>
			<h1 class="dtw-page-title"><?php esc_html_e( 'Settings', 'data-tracker-woocommerce' ); ?></h1>
			<p class="dtw-page-intro"><?php esc_html_e( 'Tune attribution, privacy and diagnostics for your store.', 'data-tracker-woocommerce' ); ?></p>
		</div>
	</div>

	<div class="dtw-banner">
		<div class="dtw-banner__mark" aria-hidden="true">
			<span class="dashicons dashicons-rocket"></span>
		</div>
		<div class="dtw-banner__content">
			<span class="dtw-banner__eyebrow"><?php esc_html_e( 'Coming soon', 'data-tracker-woocommerce' ); ?></span>
			<h3 class="dtw-banner__title"><?php esc_html_e( 'Go further with Data Tracker Pro', 'data-tracker-woocommerce' ); ?></h3>
			<p class="dtw-banner__text">
				<?php esc_html_e( 'Meta Conversions API, Google Ads Enhanced Conversions, server-side tracking and tracking alerts are on the roadmap.', 'data-tracker-woocommerce' ); ?>
			</p>
		</div>
		<div class="dtw-banner__cta">
			<a class="dtw-btn dtw-btn--light" href="<?php echo esc_url( admin_url( 'admin.php?page=data-tracker-tracking' ) ); ?>">
				<?php esc_html_e( 'See what is tracked', 'data-tracker-woocommerce' ); ?>
			</a>
		</div>
	</div>

	<form method="post" action="<?php echo esc_url( $save_url ); ?>">
		<input type="hidden" name="action" value="dtw_save_settings" />
		<?php wp_nonce_field( 'dtw_settings' ); ?>

		<div class="dtw-stack">
			<div class="dtw-card">
			<div class="dtw-card__head">
				<h2 class="dtw-card__title"><?php esc_html_e( 'Attribution', 'data-tracker-woocommerce' ); ?></h2>
				<span class="dashicons dtw-card__icon dashicons-randomize" aria-hidden="true"></span>
			</div>
			<p class="dtw-card__desc"><?php esc_html_e( 'Attribution shows where your customers come from and stores it with each order.', 'data-tracker-woocommerce' ); ?></p>

			<label class="dtw-checkbox">
				<input type="checkbox" name="dtw_utm_tracking" value="1" <?php checked( $settings['utm_tracking_enabled'] ); ?> />
				<span class="dtw-checkbox__label">
					<strong><?php esc_html_e( 'Track UTM parameters and advertising clicks', 'data-tracker-woocommerce' ); ?></strong>
					<small><?php esc_html_e( 'utm_source, utm_medium, utm_campaign, gclid, fbclid and more', 'data-tracker-woocommerce' ); ?></small>
				</span>
			</label>

			<label class="dtw-checkbox">
				<input type="checkbox" name="dtw_store_attribution" value="1" <?php checked( $settings['store_attribution'] ); ?> />
				<span class="dtw-checkbox__label">
					<strong><?php esc_html_e( 'Store traffic source on orders', 'data-tracker-woocommerce' ); ?></strong>
					<small><?php esc_html_e( 'Save traffic source information on WooCommerce orders', 'data-tracker-woocommerce' ); ?></small>
				</span>
			</label>
		</div>

		<div class="dtw-card">
			<div class="dtw-card__head">
				<h2 class="dtw-card__title"><?php esc_html_e( 'Privacy', 'data-tracker-woocommerce' ); ?></h2>
				<span class="dashicons dtw-card__icon dashicons-privacy" aria-hidden="true"></span>
			</div>

			<label class="dtw-checkbox">
				<input type="checkbox" name="dtw_respect_consent" value="1" id="dtw-respect-consent" <?php checked( $settings['respect_consent'] ); ?> />
				<span class="dtw-checkbox__label">
					<strong><?php esc_html_e( 'Respect cookie consent', 'data-tracker-woocommerce' ); ?></strong>
					<small><?php esc_html_e( 'Works with consent plugins that use the WP Consent API', 'data-tracker-woocommerce' ); ?></small>
				</span>
			</label>

			<div id="dtw-consent-defaults" class="dtw-consent-defaults" <?php echo $settings['respect_consent'] ? '' : 'hidden'; ?>>
				<p class="dtw-card__desc">
					<?php esc_html_e( 'When consent has not been given yet, which storage type should be treated as denied?', 'data-tracker-woocommerce' ); ?>
				</p>
				<div class="dtw-field-grid">
					<div class="dtw-field">
						<label for="dtw_ad_storage"><?php esc_html_e( 'Advertising storage', 'data-tracker-woocommerce' ); ?></label>
						<select class="dtw-input" id="dtw_ad_storage" name="dtw_ad_storage">
							<option value="denied" <?php selected( $consent['ad_storage'], 'denied' ); ?>><?php esc_html_e( 'Denied by default', 'data-tracker-woocommerce' ); ?></option>
							<option value="granted" <?php selected( $consent['ad_storage'], 'granted' ); ?>><?php esc_html_e( 'Granted by default', 'data-tracker-woocommerce' ); ?></option>
						</select>
					</div>
					<div class="dtw-field">
						<label for="dtw_analytics_storage"><?php esc_html_e( 'Analytics storage', 'data-tracker-woocommerce' ); ?></label>
						<select class="dtw-input" id="dtw_analytics_storage" name="dtw_analytics_storage">
							<option value="denied" <?php selected( $consent['analytics_storage'], 'denied' ); ?>><?php esc_html_e( 'Denied by default', 'data-tracker-woocommerce' ); ?></option>
							<option value="granted" <?php selected( $consent['analytics_storage'], 'granted' ); ?>><?php esc_html_e( 'Granted by default', 'data-tracker-woocommerce' ); ?></option>
						</select>
					</div>
				</div>
				<p class="dtw-field__help">
					<?php esc_html_e( 'This plugin does not bypass cookie consent systems. A consent plugin must grant consent before tracking starts.', 'data-tracker-woocommerce' ); ?>
				</p>
				<?php if ( $settings['respect_consent'] && ! $settings['consent_api_available'] ) : ?>
					<p class="dtw-field__help dtw-field__help--warn">
						<span class="dashicons dashicons-warning" aria-hidden="true"></span>
						<?php esc_html_e( 'No consent plugin using the WP Consent API was detected. Tracking will stay off until a consent plugin grants consent.', 'data-tracker-woocommerce' ); ?>
					</p>
				<?php endif; ?>
			</div>
		</div>

		<div class="dtw-card">
			<div class="dtw-card__head">
				<h2 class="dtw-card__title"><?php esc_html_e( 'Diagnostics', 'data-tracker-woocommerce' ); ?></h2>
				<span class="dashicons dtw-card__icon dashicons-heart" aria-hidden="true"></span>
			</div>

			<label class="dtw-checkbox">
				<input type="checkbox" name="dtw_debug_log" value="1" <?php checked( $settings['debug_log'] ); ?> />
				<span class="dtw-checkbox__label">
					<strong><?php esc_html_e( 'Record recent tracking events', 'data-tracker-woocommerce' ); ?></strong>
					<small><?php esc_html_e( 'See them under Technical Details on the Dashboard', 'data-tracker-woocommerce' ); ?></small>
				</span>
			</label>
		</div>

		<div class="dtw-card">
			<div class="dtw-card__head">
				<h2 class="dtw-card__title"><?php esc_html_e( 'Advanced', 'data-tracker-woocommerce' ); ?></h2>
				<span class="dashicons dtw-card__icon dashicons-admin-tools" aria-hidden="true"></span>
			</div>
			<p class="dtw-card__desc">
				<?php esc_html_e( 'Developer tools for support. Keep these off unless you are troubleshooting.', 'data-tracker-woocommerce' ); ?>
			</p>

			<label class="dtw-checkbox">
				<input type="checkbox" name="dtw_debug_mode" value="1" <?php checked( $settings['debug_mode'] ); ?> />
				<span class="dtw-checkbox__label">
					<strong><?php esc_html_e( 'Debug Mode', 'data-tracker-woocommerce' ); ?></strong>
					<small><?php esc_html_e( 'Show event payloads and extra details under Technical Details', 'data-tracker-woocommerce' ); ?></small>
				</span>
			</label>

			<div class="dtw-advanced-actions">
				<button type="button" id="dtw-copy-diagnostics" class="dtw-btn dtw-btn--ghost dtw-btn--small">
					<span class="dashicons dashicons-clipboard" aria-hidden="true"></span>
					<?php esc_html_e( 'Copy Diagnostics', 'data-tracker-woocommerce' ); ?>
				</button>
				<button type="button" id="dtw-clear-log" class="dtw-btn dtw-btn--ghost dtw-btn--small">
					<span class="dashicons dashicons-trash" aria-hidden="true"></span>
					<?php esc_html_e( 'Clear Event Log', 'data-tracker-woocommerce' ); ?>
				</button>
				<span id="dtw-diagnostics-status" class="dtw-diagnostics-status" role="status"></span>
			</div>
		</div>
		</div>

		<p class="dtw-actions">
			<button type="submit" class="dtw-btn dtw-btn--primary dtw-btn--large">
				<span class="dashicons dashicons-saved" aria-hidden="true"></span>
				<?php esc_html_e( 'Save Settings', 'data-tracker-woocommerce' ); ?>
			</button>
		</p>
	</form>
</div>
</div>