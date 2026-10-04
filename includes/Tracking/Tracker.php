<?php
/**
 * Frontend tracking loader.
 *
 * @package DataTracker
 */

namespace DataTracker\Tracking;

defined( 'ABSPATH' ) || exit;

use DataTracker\Attribution\UtmCapture;
use DataTracker\Core\Options;
use DataTracker\Diagnostics\EventLog;
use DataTracker\Platforms\PlatformManager;
use DataTracker\WooCommerce\PageContext;

/**
 * Enqueues the frontend tracking script, prints the platform snippets in the
 * head, and localizes the data the script needs.
 */
class Tracker {

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
	 * UTM capture.
	 *
	 * @var UtmCapture
	 */
	private $utm;

	/**
	 * Event log.
	 *
	 * @var EventLog
	 */
	private $event_log;

	/**
	 * Page context.
	 *
	 * @var PageContext
	 */
	private $page_context;

	/**
	 * Constructor.
	 *
	 * @param Options         $options      Options.
	 * @param PlatformManager $platforms    Platforms.
	 * @param UtmCapture      $utm          UTM capture.
	 * @param EventLog        $event_log    Event log.
	 * @param PageContext     $page_context Page context.
	 */
	public function __construct( Options $options, PlatformManager $platforms, UtmCapture $utm, EventLog $event_log, PageContext $page_context ) {
		$this->options      = $options;
		$this->platforms    = $platforms;
		$this->utm          = $utm;
		$this->event_log    = $event_log;
		$this->page_context = $page_context;

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_head', array( $this, 'print_head_scripts' ), 1 );
	}

	/**
	 * Whether the frontend tracker should load.
	 *
	 * @return bool
	 */
	public function should_load() {
		if ( $this->platforms->has_any_connected() ) {
			return true;
		}
		if ( $this->utm->is_enabled() ) {
			return true;
		}
		return false;
	}

	/**
	 * Enqueue the tracker script with localized data.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		if ( ! $this->should_load() ) {
			return;
		}

		wp_enqueue_script( 'dtw-tracker', DTW_PLUGIN_URL . 'assets/js/tracker.js', array(), DTW_VERSION, true );
		wp_localize_script( 'dtw-tracker', 'DTW', $this->localize_data() );
	}

	/**
	 * Data exposed to the tracking script.
	 *
	 * @return array
	 */
	private function localize_data() {
		return array(
			'context'     => $this->page_context->get(),
			'platforms'   => $this->platforms->get_frontend_config(),
			'attribution' => array(
				'enabled'     => $this->utm->is_enabled(),
				'cookie_name' => UtmCapture::COOKIE,
			),
			'debug'       => array(
				'log_enabled' => (bool) $this->options->get( 'debug_log' ),
				'nonce'       => wp_create_nonce( 'dtw_frontend' ),
				'url'         => esc_url_raw( rest_url( 'data-tracker/v1/events' ) ),
			),
			'settings'    => array(
				'enabled_events' => (array) $this->options->get( 'enabled_events' ),
			),
			'is_test'     => isset( $_GET['dtw_test'] ) ? absint( $_GET['dtw_test'] ) : 0,
		);
	}

	/**
	 * Print the platform snippets in the head.
	 *
	 * @return void
	 */
	public function print_head_scripts() {
		if ( is_admin() || ! $this->should_load() ) {
			return;
		}

		$output = '';
		$ga4    = $this->platforms->get( 'ga4' );
		$ads    = $this->platforms->get( 'google_ads' );
		$meta   = $this->platforms->get( 'meta' );

		$gtag_configs = array();

		if ( $ga4 && $ga4->is_connected() ) {
			$gtag_configs[] = array(
				'id'      => $ga4->get_measurement_id(),
				'consent' => (bool) $this->options->get( 'respect_consent' ),
			);
		}

		if ( $ads && $ads->is_connected() ) {
			$gtag_configs[] = array(
				'id'      => $ads->get_conversion_id(),
				'consent' => false,
			);
		}

		if ( ! empty( $gtag_configs ) ) {
			$first = $gtag_configs[0]['id'];
			$output .= '<script async src="https://www.googletagmanager.com/gtag/js?id=' . esc_attr( $first ) . '"></script>' . "\n";
			$output .= '<script>' . "\n";
			$output .= 'window.dataLayer = window.dataLayer || [];' . "\n";
			$output .= 'function gtag(){dataLayer.push(arguments);}' . "\n";
			$output .= 'gtag("js", new Date());' . "\n";

			foreach ( $gtag_configs as $config ) {
				if ( $config['consent'] ) {
					$defaults = (array) $this->options->get( 'consent_defaults', array( 'ad_storage' => 'denied', 'analytics_storage' => 'denied' ) );
					$output .= 'gtag("consent", "default", ' . wp_json_encode( $defaults ) . ');' . "\n";
				}
				$output .= 'gtag("config", "' . esc_attr( $config['id'] ) . '");' . "\n";
			}

			$output .= '</script>' . "\n";
		}

		if ( $meta && $meta->is_connected() ) {
			if ( $this->options->get( 'respect_consent' ) ) {
				$output .= '<script>' . "\n";
				$output .= 'window.fbq = window.fbq || function(){ window.fbq.queue = window.fbq.queue || []; window.fbq.queue.push( arguments ); };' . "\n";
				$output .= 'window._fbq = window._fbq || window.fbq.queue;' . "\n";
				$output .= '</script>' . "\n";
			} else {
				$output .= '<script>' . "\n";
				$output .= '!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version="2.0";n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,"script","https://connect.facebook.net/en_US/fbevents.js");' . "\n";
				$output .= 'fbq("init", "' . esc_attr( $meta->get_pixel_id() ) . '");' . "\n";
				$output .= 'fbq("track", "PageView");' . "\n";
				$output .= '</script>' . "\n";
			}
		}

		if ( '' !== $output ) {
			echo '<!-- Data Tracker for WooCommerce -->' . "\n" . $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}