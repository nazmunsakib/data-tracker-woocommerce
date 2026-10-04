<?php
/**
 * Connections template.
 *
 * @package DataTracker
 * @var array  $statuses
 * @var array  $fields
 * @var array  $ads
 * @var string $error_msg
 * @var int    $last_tested
 */

defined( 'ABSPATH' ) || exit;

$active_tab = 'data-tracker-connections';
include DTW_PLUGIN_DIR . 'templates/admin/partials/header.php';

$save_url = admin_url( 'admin-post.php' );

$icons = array(
	'ga4'        => 'dashicons-chart-area',
	'meta'       => 'dashicons-share',
	'google_ads' => 'dashicons-megaphone',
);
?>

	<div class="dtw-page-head">
		<div>
			<h1 class="dtw-page-title"><?php esc_html_e( 'Connections', 'data-tracker-woocommerce' ); ?></h1>
			<p class="dtw-page-intro"><?php esc_html_e( 'Connect the platforms you want to track with. Your store events are sent automatically — no technical setup needed.', 'data-tracker-woocommerce' ); ?></p>
		</div>
	</div>

	<?php if ( '' !== $error_msg ) : ?>
		<div class="dtw-alert dtw-alert--error">
			<span class="dashicons dashicons-dismiss" aria-hidden="true"></span>
			<span><?php echo esc_html( $error_msg ); ?></span>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( $save_url ); ?>">
		<input type="hidden" name="action" value="dtw_save_connections" />
		<?php wp_nonce_field( 'dtw_connections' ); ?>

		<div class="dtw-stack">
		<?php foreach ( $statuses as $status ) : $id = $status['id']; ?>
			<div class="dtw-card dtw-platform">
				<div class="dtw-platform__head">
					<div class="dtw-platform__title-wrap">
						<span class="dtw-platform__icon"><span class="dashicons <?php echo esc_attr( $icons[ $id ] ); ?>"></span></span>
						<h2 class="dtw-platform__title"><?php echo esc_html( $status['label'] ); ?></h2>
					</div>
					<span class="dtw-badge <?php echo $status['connected'] ? 'dtw-badge--ok' : 'dtw-badge--muted'; ?>">
						<?php echo $status['connected'] ? esc_html__( 'Connected', 'data-tracker-woocommerce' ) : esc_html__( 'Not connected', 'data-tracker-woocommerce' ); ?>
					</span>
				</div>

				<?php if ( 'google_ads' === $id ) : ?>
					<div class="dtw-field-grid">
						<?php foreach ( $ads as $ads_field ) : ?>
							<div class="dtw-field">
								<label for="<?php echo esc_attr( $ads_field['key'] ); ?>"><?php echo esc_html( $ads_field['label'] ); ?></label>
								<input class="dtw-input" type="text" id="<?php echo esc_attr( $ads_field['key'] ); ?>" name="<?php echo esc_attr( $ads_field['key'] ); ?>" value="<?php echo esc_attr( $ads_field['value'] ); ?>" placeholder="<?php echo esc_attr( $ads_field['placeholder'] ); ?>" autocomplete="off" />
								<p class="dtw-field__help"><?php echo esc_html( $ads_field['help'] ); ?></p>
							</div>
						<?php endforeach; ?>
					</div>
				<?php else : $field = $fields[ $id ]; ?>
					<div class="dtw-field dtw-field--single">
						<label for="<?php echo esc_attr( $field['key'] ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
						<input class="dtw-input" type="text" id="<?php echo esc_attr( $field['key'] ); ?>" name="<?php echo esc_attr( $field['key'] ); ?>" value="<?php echo esc_attr( $field['value'] ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" autocomplete="off" />
						<p class="dtw-field__help"><?php echo esc_html( $field['help'] ); ?></p>
					</div>
				<?php endif; ?>

				<?php if ( $status['connected'] ) : ?>
					<div class="dtw-platform__tested">
						<span class="dashicons <?php echo $last_tested ? 'dashicons-yes-alt' : 'dashicons-hourglass'; ?>" aria-hidden="true"></span>
						<?php if ( $last_tested ) : ?>
							<?php
							/* translators: %s: relative time, e.g. "5 minutes ago" */
							printf( esc_html__( 'Tracking tested %s', 'data-tracker-woocommerce' ), esc_html( \DataTracker\Diagnostics\EventLog::relative_time( $last_tested ) ) );
							?>
						<?php else : ?>
							<?php esc_html_e( 'Connected but not tested yet', 'data-tracker-woocommerce' ); ?>
						<?php endif; ?>
					</div>
					<div class="dtw-platform__actions">
						<button type="submit" name="dtw_disconnect" value="<?php echo esc_attr( $id ); ?>" class="dtw-btn dtw-btn--danger">
							<span class="dashicons dashicons-unplug" aria-hidden="true"></span>
							<?php esc_html_e( 'Disconnect', 'data-tracker-woocommerce' ); ?>
						</button>
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
		</div>

		<p class="dtw-actions">
			<button type="submit" class="dtw-btn dtw-btn--primary dtw-btn--large">
				<span class="dashicons dashicons-saved" aria-hidden="true"></span>
				<?php esc_html_e( 'Save Connections', 'data-tracker-woocommerce' ); ?>
			</button>
		</p>
	</form>
</div>
</div>