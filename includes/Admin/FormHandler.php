<?php
/**
 * Admin form submission handler.
 *
 * @package DataTracker
 */

namespace DataTracker\Admin;

defined( 'ABSPATH' ) || exit;

use DataTracker\Core\Options;
use DataTracker\Platforms\PlatformManager;

/**
 * Processes admin-post form submissions with nonce, capability and sanitization.
 */
class FormHandler {

	/**
	 * Options.
	 *
	 * @var Options
	 */
	private $options;

	/**
	 * Platforms.
	 *
	 * @var PlatformManager
	 */
	private $platforms;

	/**
	 * Constructor.
	 *
	 * @param Options         $options   Options.
	 * @param PlatformManager $platforms Platforms.
	 */
	public function __construct( Options $options, PlatformManager $platforms ) {
		$this->options   = $options;
		$this->platforms = $platforms;

		add_action( 'admin_post_dtw_save_connections', array( $this, 'save_connections' ) );
		add_action( 'admin_post_dtw_save_tracking', array( $this, 'save_tracking' ) );
		add_action( 'admin_post_dtw_save_settings', array( $this, 'save_settings' ) );
		add_action( 'admin_post_dtw_wizard_complete', array( $this, 'wizard_complete' ) );
		add_action( 'admin_post_dtw_dismiss_welcome', array( $this, 'dismiss_welcome' ) );
	}

	/**
	 * Save (or disconnect) platform connections.
	 *
	 * @return void
	 */
	public function save_connections() {
		$this->verify( 'dtw_connections', 'data-tracker-connections' );

		$from_wizard = isset( $_POST['dtw_from_wizard'] ) ? 1 : 0;
		$redirect_to = $from_wizard ? 'data-tracker-wizard' : 'data-tracker-connections';

		$disconnect = isset( $_POST['dtw_disconnect'] ) ? sanitize_key( wp_unslash( $_POST['dtw_disconnect'] ) ) : '';

		if ( '' !== $disconnect && $this->platforms->get( $disconnect ) ) {
			$this->platforms->get( $disconnect )->disconnect();
			$this->redirect( $redirect_to, 'disconnected' );
		}

		$result = $this->platforms->get( 'ga4' )->connect( $_POST );
		if ( is_wp_error( $result ) ) {
			$this->redirect_with_error( $redirect_to, $result );
		}

		$result = $this->platforms->get( 'meta' )->connect( $_POST );
		if ( is_wp_error( $result ) ) {
			$this->redirect_with_error( $redirect_to, $result );
		}

		$result = $this->platforms->get( 'google_ads' )->connect( $_POST );
		if ( is_wp_error( $result ) ) {
			$this->redirect_with_error( $redirect_to, $result );
		}

		if ( $from_wizard ) {
			$this->redirect( $redirect_to, 'connected', array( 'dtw_step' => '3' ) );
		}

		if ( ! $this->submitted_ids() ) {
			if ( $this->platforms->has_any_connected() ) {
				$this->redirect( $redirect_to, 'saved' );
			}
			$this->redirect( $redirect_to, 'nochange' );
		}

		$this->redirect( $redirect_to, 'connected' );
	}

	/**
	 * Whether any tracking ID field was submitted with a value.
	 *
	 * @return bool
	 */
	private function submitted_ids() {
		$fields = array(
			'ga4_measurement_id',
			'meta_pixel_id',
			'google_ads_conversion_id',
			'google_ads_conversion_label',
		);

		foreach ( $fields as $field ) {
			if ( isset( $_POST[ $field ] ) && '' !== trim( (string) wp_unslash( $_POST[ $field ] ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Save which events should be tracked.
	 *
	 * @return void
	 */
	public function save_tracking() {
		$this->verify( 'dtw_tracking', 'data-tracker-tracking' );

		$allowed = array_keys( Options::$all_events );
		$posted  = isset( $_POST['dtw_events'] ) && is_array( $_POST['dtw_events'] ) ? array_map( 'sanitize_key', wp_unslash( $_POST['dtw_events'] ) ) : array();
		$events  = array_values( array_intersect( $allowed, $posted ) );

		$this->options->update( 'enabled_events', $events );

		$this->redirect( 'data-tracker-tracking', 'saved' );
	}

	/**
	 * Save general settings.
	 *
	 * @return void
	 */
	public function save_settings() {
		$this->verify( 'dtw_settings', 'data-tracker-settings' );

		$respect_consent = isset( $_POST['dtw_respect_consent'] ) ? 1 : 0;

		$consent_defaults = array(
			'ad_storage'        => isset( $_POST['dtw_ad_storage'] ) && 'granted' === sanitize_key( wp_unslash( $_POST['dtw_ad_storage'] ) ) ? 'granted' : 'denied',
			'analytics_storage' => isset( $_POST['dtw_analytics_storage'] ) && 'granted' === sanitize_key( wp_unslash( $_POST['dtw_analytics_storage'] ) ) ? 'granted' : 'denied',
		);

		$this->options->bulk_update(
			array(
				'utm_tracking_enabled' => isset( $_POST['dtw_utm_tracking'] ) ? 1 : 0,
				'store_attribution'    => isset( $_POST['dtw_store_attribution'] ) ? 1 : 0,
				'respect_consent'      => $respect_consent,
				'consent_defaults'     => $consent_defaults,
				'debug_log'            => isset( $_POST['dtw_debug_log'] ) ? 1 : 0,
				'debug_mode'           => isset( $_POST['dtw_debug_mode'] ) ? 1 : 0,
			)
		);

		$this->redirect( 'data-tracker-settings', 'saved' );
	}

	/**
	 * Finish the setup wizard.
	 *
	 * @return void
	 */
	public function wizard_complete() {
		$this->verify( 'dtw_wizard', 'data-tracker' );

		$this->options->update( 'show_welcome', 0 );

		$this->redirect( 'data-tracker', 'welcome-done' );
	}

	/**
	 * Dismiss the welcome notice.
	 *
	 * @return void
	 */
	public function dismiss_welcome() {
		check_admin_referer( 'dtw_dismiss_welcome' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'data-tracker-woocommerce' ) );
		}

		$this->options->update( 'show_welcome', 0 );

		wp_safe_redirect( admin_url() );
		exit;
	}

	/**
	 * Verify nonce and capability, otherwise die.
	 *
	 * @param string $nonce Nonce action.
	 * @return void
	 */
	private function verify( $nonce, $page ) {
		check_admin_referer( $nonce );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'data-tracker-woocommerce' ) );
		}
	}

	/**
	 * Redirect back to a Data Tracker page with a success message.
	 *
	 * @param string $page    Full page slug, e.g. "data-tracker-connections".
	 * @param string $message Message key.
	 * @param array  $extra   Extra query args.
	 * @return void
	 */
	private function redirect( $page, $message, $extra = array() ) {
		$args = array_merge(
			array( 'page' => $page, 'dtw_message' => $message ),
			$extra
		);

		wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Redirect back with a WP_Error message.
	 *
	 * @param string   $page  Full page slug, e.g. "data-tracker-connections".
	 * @param WP_Error $error Error object.
	 * @return void
	 */
	private function redirect_with_error( $page, $error ) {
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'      => $page,
					'dtw_error' => rawurlencode( $error->get_error_message() ),
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}
}