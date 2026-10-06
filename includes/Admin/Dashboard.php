<?php
/**
 * Dashboard page.
 *
 * @package DataTracker
 */

namespace DataTracker\Admin;

defined( 'ABSPATH' ) || exit;

use DataTracker\Core\Options;
use DataTracker\Diagnostics\EventLog;
use DataTracker\Diagnostics\Health;
use DataTracker\Platforms\PlatformManager;

/**
 * Renders the tracking health dashboard.
 */
class Dashboard {

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
	 * Event log.
	 *
	 * @var EventLog
	 */
	private $event_log;

	/**
	 * Constructor.
	 *
	 * @param Options         $options   Options.
	 * @param PlatformManager $platforms Platforms.
	 * @param EventLog        $event_log Event log.
	 */
	public function __construct( Options $options, PlatformManager $platforms, EventLog $event_log ) {
		$this->options   = $options;
		$this->platforms = $platforms;
		$this->event_log = $event_log;
	}

	/**
	 * Render the page.
	 *
	 * @return void
	 */
	public function render() {
		$health        = new Health( $this->options, $this->platforms, $this->event_log );
		$score         = $health->get_score();
		$summary       = $health->get_summary();
		$issue_count   = $health->get_issue_count();
		$problems      = $health->get_problems();
		$next_steps    = $health->get_next_steps();
		$platforms     = $this->platforms->all();
		$event_status  = $this->event_status();
		$activity      = $this->recent_activity();
		$last_tested   = $this->event_log->last_tested();
		$wc_active     = class_exists( 'WooCommerce' );
		$error_msg     = isset( $_GET['dtw_error'] ) ? sanitize_text_field( wp_unslash( $_GET['dtw_error'] ) ) : '';
		$connected     = $this->platforms->has_any_connected();
		$debug_mode    = (bool) $this->options->get( 'debug_mode' );
		$platform_cards = $this->platform_cards();

		include DTW_PLUGIN_DIR . 'templates/admin/dashboard.php';
	}

	/**
	 * Per-platform dashboard cards.
	 *
	 * @return array
	 */
	private function platform_cards() {
		$icons = array(
			'ga4'        => 'dashicons-chart-area',
			'meta'       => 'dashicons-share',
			'google_ads' => 'dashicons-megaphone',
		);

		$cards = array();

		foreach ( $this->platforms->all() as $id => $platform ) {
			$status = $platform->get_status();

			$action = $status['connected']
				? array(
					'label' => __( 'Test Tracking', 'data-tracker-woocommerce' ),
					'url'   => admin_url( 'admin.php?page=data-tracker-test' ),
					'class' => 'dtw-btn--ghost',
				)
				: array(
					'label' => __( 'Connect', 'data-tracker-woocommerce' ),
					'url'   => admin_url( 'admin.php?page=data-tracker-connections' ),
					'class' => 'dtw-btn--primary',
				);

			$cards[] = array(
				'id'        => $id,
				'label'     => $platform->get_label(),
				'icon'      => $icons[ $id ],
				'connected' => $status['connected'],
				'value'     => $status['value'],
				'action'    => $action,
			);
		}

		return $cards;
	}

	/**
	 * Grouped recent activity with per-platform delivery status.
	 *
	 * @return array
	 */
	private function recent_activity() {
		$entries = $this->event_log->recent( 7 );
		$grouped = array();

		foreach ( $entries as $entry ) {
			$name = isset( $entry['name'] ) ? (string) $entry['name'] : '';
			$time = isset( $entry['time'] ) ? (int) $entry['time'] : 0;

			if ( '' === $name || ! $time ) {
				continue;
			}

			$key = $name . '|' . gmdate( 'YmdHi', $time );

			if ( ! isset( $grouped[ $key ] ) ) {
				$grouped[ $key ] = array(
					'name'      => $name,
					'time'      => $time,
					'platforms' => array(),
					'payload'   => isset( $entry['payload'] ) && is_array( $entry['payload'] ) ? $entry['payload'] : array(),
				);
			}

			if ( ! empty( $entry['platform'] ) && ! in_array( $entry['platform'], $grouped[ $key ]['platforms'], true ) ) {
				$grouped[ $key ]['platforms'][] = $entry['platform'];
			}
		}

		return array_slice( array_values( $grouped ), 0, 6 );
	}

	/**
	 * Event status overview.
	 *
	 * @return array
	 */
	private function event_status() {
		$enabled = (array) $this->options->get( 'enabled_events' );
		$map     = array();

		foreach ( Options::$all_events as $key => $label ) {
			$map[] = array(
				'key'     => $key,
				'label'   => $label,
				'enabled' => in_array( $key, $enabled, true ),
			);
		}

		return $map;
	}
}