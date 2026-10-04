<?php
/**
 * Tracking template.
 *
 * @package DataTracker
 * @var array  $events
 * @var array  $enabled
 * @var bool   $wc_active
 */

defined( 'ABSPATH' ) || exit;

$active_tab = 'data-tracker-tracking';
include DTW_PLUGIN_DIR . 'templates/admin/partials/header.php';

$save_url = admin_url( 'admin-post.php' );
?>

	<div class="dtw-page-head">
		<div>
			<h1 class="dtw-page-title"><?php esc_html_e( 'Tracking', 'data-tracker-woocommerce' ); ?></h1>
			<p class="dtw-page-intro"><?php esc_html_e( 'Choose what you want to track. The events below are sent to your connected platforms automatically.', 'data-tracker-woocommerce' ); ?></p>
		</div>
	</div>

	<?php if ( ! $wc_active ) : ?>
		<div class="dtw-alert dtw-alert--warn">
			<span class="dashicons dashicons-warning" aria-hidden="true"></span>
			<span><?php esc_html_e( 'WooCommerce is not active, so store events cannot be tracked yet.', 'data-tracker-woocommerce' ); ?></span>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( $save_url ); ?>">
		<input type="hidden" name="action" value="dtw_save_tracking" />
		<?php wp_nonce_field( 'dtw_tracking' ); ?>

		<div class="dtw-stack">
			<div class="dtw-card">
			<div class="dtw-card__head">
				<h2 class="dtw-card__title"><?php esc_html_e( 'Events', 'data-tracker-woocommerce' ); ?></h2>
				<span class="dtw-badge dtw-badge--info"><?php echo count( $enabled ); ?>/<?php echo count( $events ); ?> <?php esc_html_e( 'enabled', 'data-tracker-woocommerce' ); ?></span>
			</div>
			<p class="dtw-card__desc"><?php esc_html_e( 'Leave all events on for complete tracking. Each event includes product and purchase information automatically.', 'data-tracker-woocommerce' ); ?></p>

			<?php foreach ( $events as $key => $label ) : ?>
				<label class="dtw-checkbox">
					<input type="checkbox" name="dtw_events[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $enabled, true ) ); ?> />
					<span class="dtw-checkbox__label"><?php echo esc_html( $label ); ?></span>
				</label>
			<?php endforeach; ?>
		</div>

		<div class="dtw-card dtw-card--muted">
			<div class="dtw-card__head">
				<h2 class="dtw-card__title"><?php esc_html_e( 'Advanced server tracking', 'data-tracker-woocommerce' ); ?></h2>
				<span class="dtw-badge dtw-badge--info"><?php esc_html_e( 'Coming soon', 'data-tracker-woocommerce' ); ?></span>
			</div>
			<p>
				<?php esc_html_e( 'Server-side conversion APIs (Meta Conversions API, Google Ads Enhanced Conversions) are planned for a future version.', 'data-tracker-woocommerce' ); ?>
			</p>
		</div>
		</div>

		<p class="dtw-actions">
			<button type="submit" class="dtw-btn dtw-btn--primary dtw-btn--large">
				<span class="dashicons dashicons-saved" aria-hidden="true"></span>
				<?php esc_html_e( 'Save Tracking', 'data-tracker-woocommerce' ); ?>
			</button>
		</p>
	</form>
</div>
</div>