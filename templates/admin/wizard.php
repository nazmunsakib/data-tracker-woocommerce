<?php
/**
 * Setup wizard template.
 *
 * @package DataTracker
 * @var int $connected
 */

defined( 'ABSPATH' ) || exit;

$wizard_url  = admin_url( 'admin-post.php' );
$dashboard   = admin_url( 'admin.php?page=data-tracker' );
$start_step  = isset( $_GET['dtw_step'] ) ? absint( $_GET['dtw_step'] ) : 1;
$start_step  = min( max( $start_step, 1 ), 3 );
$error_msg   = isset( $_GET['dtw_error'] ) ? sanitize_text_field( wp_unslash( $_GET['dtw_error'] ) ) : '';
?>

<div class="wrap dtw-wizard">
	<?php if ( '' !== $error_msg ) : ?>
		<div class="notice notice-error is-dismissible"><p><?php echo esc_html( $error_msg ); ?></p></div>
	<?php endif; ?>
	<div class="dtw-wizard__card" data-start="<?php echo (int) $start_step; ?>">
		<div class="dtw-wizard__step is-active" data-step="1">
			<h1><?php esc_html_e( 'Welcome to Data Tracker for WooCommerce', 'data-tracker-woocommerce' ); ?></h1>
			<p class="dtw-page-intro">
				<?php esc_html_e( "Let's set up your ecommerce tracking in a few simple steps. No technical knowledge needed.", 'data-tracker-woocommerce' ); ?>
			</p>
			<p>
				<?php esc_html_e( 'Connect your tracking platforms and the plugin will automatically track products, carts, checkouts and purchases.', 'data-tracker-woocommerce' ); ?>
			</p>
			<p>
				<button type="button" class="dtw-btn dtw-btn--primary dtw-btn--large dtw-wizard-next"><?php esc_html_e( 'Get Started', 'data-tracker-woocommerce' ); ?></button>
				<a class="dtw-btn dtw-btn--ghost dtw-wizard-skip" href="<?php echo esc_url( $dashboard ); ?>"><?php esc_html_e( 'Set up later', 'data-tracker-woocommerce' ); ?></a>
			</p>
		</div>

		<div class="dtw-wizard__step" data-step="2" hidden>
			<h1><?php esc_html_e( 'Connect your platforms', 'data-tracker-woocommerce' ); ?></h1>
			<p class="dtw-page-intro">
				<?php esc_html_e( 'Add the tracking IDs from Google Analytics, Meta and Google Ads. You can skip any platform and connect it later.', 'data-tracker-woocommerce' ); ?>
			</p>

			<form method="post" action="<?php echo esc_url( $wizard_url ); ?>" id="dtw-wizard-form">
				<input type="hidden" name="action" value="dtw_save_connections" />
				<input type="hidden" name="dtw_from_wizard" value="1" />
				<?php wp_nonce_field( 'dtw_connections' ); ?>

				<p>
					<label for="dtw_wizard_ga4"><strong><?php esc_html_e( 'Google Analytics 4 Measurement ID', 'data-tracker-woocommerce' ); ?></strong></label>
					<input class="regular-text" type="text" id="dtw_wizard_ga4" name="ga4_measurement_id" value="" placeholder="G-XXXXXXXXXX" autocomplete="off" />
					<span class="description"><?php esc_html_e( 'Optional. Skip if you do not use Google Analytics.', 'data-tracker-woocommerce' ); ?></span>
				</p>

				<p>
					<label for="dtw_wizard_meta"><strong><?php esc_html_e( 'Meta Pixel ID', 'data-tracker-woocommerce' ); ?></strong></label>
					<input class="regular-text" type="text" id="dtw_wizard_meta" name="meta_pixel_id" value="" placeholder="123456789" autocomplete="off" />
					<span class="description"><?php esc_html_e( 'Optional. Skip if you do not use Meta.', 'data-tracker-woocommerce' ); ?></span>
				</p>

				<p>
					<label for="dtw_wizard_ads"><strong><?php esc_html_e( 'Google Ads Conversion ID', 'data-tracker-woocommerce' ); ?></strong></label>
					<input class="regular-text" type="text" id="dtw_wizard_ads" name="google_ads_conversion_id" value="" placeholder="AW-123456789" autocomplete="off" />
				</p>

				<p>
					<label for="dtw_wizard_ads_label"><strong><?php esc_html_e( 'Google Ads Conversion Label', 'data-tracker-woocommerce' ); ?></strong></label>
					<input class="regular-text" type="text" id="dtw_wizard_ads_label" name="google_ads_conversion_label" value="" placeholder="XXXXX_XXXXX" autocomplete="off" />
					<span class="description"><?php esc_html_e( 'Optional. Skip if you do not use Google Ads.', 'data-tracker-woocommerce' ); ?></span>
				</p>

				<p>
					<button type="submit" class="dtw-btn dtw-btn--primary dtw-btn--large"><?php esc_html_e( 'Continue', 'data-tracker-woocommerce' ); ?></button>
				</p>
			</form>
		</div>

		<div class="dtw-wizard__step" data-step="3" hidden>
			<h1><?php esc_html_e( "You're all set!", 'data-tracker-woocommerce' ); ?></h1>
			<p class="dtw-page-intro">
				<?php esc_html_e( 'Your tracking is configured. Test it now to confirm everything works.', 'data-tracker-woocommerce' ); ?>
			</p>
			<form method="post" action="<?php echo esc_url( $wizard_url ); ?>" class="dtw-button-row">
				<input type="hidden" name="action" value="dtw_wizard_complete" />
				<?php wp_nonce_field( 'dtw_wizard' ); ?>
				<button type="submit" class="dtw-btn dtw-btn--primary dtw-btn--large"><?php esc_html_e( 'Finish Setup', 'data-tracker-woocommerce' ); ?></button>
				<a class="dtw-btn dtw-btn--ghost" href="<?php echo esc_url( admin_url( 'admin.php?page=data-tracker-test' ) ); ?>"><?php esc_html_e( 'Test Tracking', 'data-tracker-woocommerce' ); ?></a>
			</form>
		</div>
	</div>
</div>