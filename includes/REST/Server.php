<?php
/**
 * REST API endpoints.
 *
 * @package DataTracker
 */

namespace DataTracker\REST;

defined( 'ABSPATH' ) || exit;

use DataTracker\Core\Options;
use DataTracker\Diagnostics\Detector;
use DataTracker\Diagnostics\EventLog;
use DataTracker\Platforms\PlatformManager;
use DataTracker\Support\Hpos;
use DataTracker\Support\LogToken;
use DataTracker\Tracking\Event;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Registers the plugin REST routes.
 */
class Server {

	const NAMESPACE = 'data-tracker/v1';

	/**
	 * Options.
	 *
	 * @var Options
	 */
	private $options;

	/**
	 * Event log.
	 *
	 * @var EventLog
	 */
	private $event_log;

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
	 * @param EventLog        $event_log Event log.
	 * @param PlatformManager $platforms Platforms.
	 */
	public function __construct( Options $options, EventLog $event_log, PlatformManager $platforms ) {
		$this->options   = $options;
		$this->event_log = $event_log;
		$this->platforms = $platforms;

		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			self::NAMESPACE,
			'/events',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'receive_events' ),
				'permission_callback' => array( $this, 'permission_frontend' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/test-event',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'test_event' ),
				'permission_callback' => array( $this, 'permission_admin' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/diagnostics-refresh',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'diagnostics_refresh' ),
				'permission_callback' => array( $this, 'permission_admin' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/event-log-clear',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'event_log_clear' ),
				'permission_callback' => array( $this, 'permission_admin' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/diagnostics',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'diagnostics' ),
				'permission_callback' => array( $this, 'permission_admin' ),
			)
		);
	}

	/**
	 * Frontend permission: valid nonce, rate limited.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool|WP_Error
	 */
	public function permission_frontend( WP_REST_Request $request ) {
		$nonce = $request->get_param( 'nonce' );
		$token = $request->get_param( 'token' );

		$is_allowed = ( is_string( $nonce ) && wp_verify_nonce( $nonce, 'dtw_frontend' ) )
			|| LogToken::verify( $token );

		if ( ! $is_allowed ) {
			return new WP_Error( 'dtw_forbidden', __( 'Invalid request.', 'data-tracker-woocommerce' ), array( 'status' => 403 ) );
		}

		$ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
		$key   = 'dtw_evt_' . md5( $ip );
		$count = (int) get_transient( $key );

		if ( $count > 200 ) {
			return new WP_Error( 'dtw_rate', __( 'Too many requests.', 'data-tracker-woocommerce' ), array( 'status' => 429 ) );
		}

		set_transient( $key, $count + 1, HOUR_IN_SECONDS );

		return true;
	}

	/**
	 * Admin permission.
	 *
	 * @return bool
	 */
	public function permission_admin() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Receive events reported by the storefront script.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function receive_events( WP_REST_Request $request ) {
		if ( ! $this->options->get( 'debug_log' ) ) {
			return rest_ensure_response( array( 'logged' => false ) );
		}

		$events = $request->get_param( 'events' );

		if ( ! is_array( $events ) ) {
			return new WP_Error( 'dtw_invalid', __( 'Invalid event payload.', 'data-tracker-woocommerce' ), array( 'status' => 400 ) );
		}

		$count    = 0;
		$saw_test = false;

		foreach ( array_slice( $events, 0, 20 ) as $event ) {
			if ( ! is_array( $event ) || empty( $event['name'] ) ) {
				continue;
			}

			$name = sanitize_text_field( $event['name'] );

			if ( 'dtw_test_event' === $name ) {
				$saw_test = true;
			}

			$this->event_log->log(
				array(
					'time'      => time(),
					'name'      => $name,
					'platform'  => isset( $event['platform'] ) ? sanitize_key( $event['platform'] ) : '',
					'page_type' => isset( $event['page_type'] ) ? sanitize_key( $event['page_type'] ) : '',
					'payload'   => $this->sanitize_payload( isset( $event['payload'] ) ? $event['payload'] : array() ),
					'url'       => isset( $event['url'] ) ? esc_url_raw( $event['url'] ) : '',
				)
			);
			$count++;
		}

		if ( $saw_test ) {
			$this->event_log->mark_tested();
		}

		return rest_ensure_response( array( 'logged' => $count ) );
	}

	/**
	 * Build a preview of the test event sent to each connected platform.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function test_event( WP_REST_Request $request ) {
		$preview = array();
		$test_id = wp_generate_uuid4();

		$this->event_log->mark_tested();

		foreach ( $this->platforms->all() as $platform ) {
			if ( ! $platform->is_connected() ) {
				continue;
			}
			$event                 = new Event( 'test', array( 'test_id' => $test_id ) );
			$preview[ $platform->get_id() ] = $platform->track_event( $event );
		}

		return rest_ensure_response(
			array(
				'ok'      => true,
				'test_id' => $test_id,
				'preview' => $preview,
				'consent' => array(
					'enabled'       => (bool) $this->options->get( 'respect_consent' ),
					'api_available' => function_exists( 'wp_has_consent' ),
				),
				'message' => __( 'Test event sent. Open Google Analytics 4 DebugView or the Meta Event Testing tool to confirm it arrives.', 'data-tracker-woocommerce' ),
			)
		);
	}

	/**
	 * Build a plain-text diagnostics summary for support. No secrets included.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function diagnostics( WP_REST_Request $request ) {
		$lines = array(
			'Data Tracker for WooCommerce',
			'Version: ' . DTW_VERSION,
			'',
		);

		$lines[] = 'WordPress: ' . get_bloginfo( 'version' );
		$lines[] = 'WooCommerce: ' . ( defined( 'WC_VERSION' ) ? WC_VERSION : 'Not active' );
		$lines[] = 'PHP: ' . PHP_VERSION;
		$lines[] = 'HPOS: ' . ( Hpos::is_enabled() ? 'Enabled' : 'Disabled' );

		$theme = wp_get_theme();
		$lines[] = 'Theme: ' . $theme->get( 'Name' ) . ' ' . $theme->get( 'Version' );

		$lines[] = '';
		$lines[] = 'Google Analytics: ' . ( $this->platforms->get( 'ga4' )->is_connected() ? 'Connected' : 'Not Connected' );
		$lines[] = 'Meta Pixel: ' . ( $this->platforms->get( 'meta' )->is_connected() ? 'Connected' : 'Not Connected' );
		$lines[] = 'Google Ads: ' . ( $this->platforms->get( 'google_ads' )->is_connected() ? 'Connected' : 'Not Connected' );
		$lines[] = '';

		$enabled_events = (array) $this->options->get( 'enabled_events' );
		$purchase       = in_array( 'purchase', $enabled_events, true ) && class_exists( 'WooCommerce' );
		$verified       = $this->event_log->has_event( 'purchase' );

		$lines[] = 'Purchase Tracking: ' . ( $purchase ? ( $verified ? 'Verified' : 'Enabled - Not Verified Yet' ) : 'Disabled' );
		$lines[] = 'Last Tested: ' . EventLog::relative_time( $this->event_log->last_tested() );
		$lines[] = 'Consent API: ' . ( function_exists( 'wp_has_consent' ) ? 'Available' : 'Not Available' );
		$lines[] = 'Debug Mode: ' . ( $this->options->get( 'debug_mode' ) ? 'Enabled' : 'Disabled' );

		$other_plugins = ( new Detector() )->detect_other_plugins();
		$lines[] = '';
		if ( empty( $other_plugins ) ) {
			$lines[] = 'Relevant Plugins: None detected';
		} else {
			$lines[] = 'Relevant Plugins:';
			foreach ( $other_plugins as $name ) {
				$lines[] = ' - ' . $name;
			}
		}

		$gtm = ( new Detector() )->get_gtm_containers();
		$lines[] = 'Google Tag Manager: ' . ( $gtm ? implode( ', ', $gtm ) : 'Not detected' );

		return rest_ensure_response(
			array(
				'ok'   => true,
				'text' => implode( "\n", $lines ),
			)
		);
	}

	/**
	 * Re-run duplicate detection.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function diagnostics_refresh( WP_REST_Request $request ) {
		$detector = new Detector();
		$detector->clear_cache();
		$scan = $detector->scan();

		return rest_ensure_response(
			array(
				'ok'    => true,
				'scan'  => $scan,
				'other' => $detector->detect_other_plugins(),
			)
		);
	}

	/**
	 * Clear the event log.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function event_log_clear( WP_REST_Request $request ) {
		$this->event_log->clear();

		return rest_ensure_response( array( 'ok' => true ) );
	}

	/**
	 * Recursively sanitize an event payload.
	 *
	 * @param array $payload Payload.
	 * @return array
	 */
	private function sanitize_payload( $payload ) {
		if ( ! is_array( $payload ) ) {
			return array();
		}

		$clean = array();

		foreach ( $payload as $key => $value ) {
			$key = sanitize_key( (string) $key );

			if ( is_array( $value ) ) {
				$clean[ $key ] = $this->sanitize_payload( $value );
			} else {
				$clean[ $key ] = sanitize_text_field( (string) $value );
			}
		}

		return $clean;
	}
}