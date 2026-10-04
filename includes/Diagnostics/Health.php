<?php
/**
 * Tracking health scoring.
 *
 * @package DataTracker
 */

namespace DataTracker\Diagnostics;

defined( 'ABSPATH' ) || exit;

use DataTracker\Core\Options;
use DataTracker\Platforms\PlatformManager;

/**
 * Computes a plain-language health score and the checks behind it.
 */
class Health {

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
	 * Detector.
	 *
	 * @var Detector
	 */
	private $detector;

	/**
	 * Cached checks.
	 *
	 * @var array|null
	 */
	private $checks;

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
		$this->detector  = new Detector();
	}

	/**
	 * All health checks.
	 *
	 * @return array
	 */
	public function get_checks() {
		if ( null !== $this->checks ) {
			return $this->checks;
		}

		$checks = array();
		$wc_active = class_exists( 'WooCommerce' );

		$checks[] = array(
			'id'       => 'woocommerce',
			'status'   => $wc_active ? 'ok' : 'error',
			'weight'   => 10,
			'label'    => $wc_active ? __( 'WooCommerce is active', 'data-tracker-woocommerce' ) : __( 'WooCommerce is not active', 'data-tracker-woocommerce' ),
			'message'  => $wc_active
				? __( 'Your store events can be tracked.', 'data-tracker-woocommerce' )
				: __( 'Data Tracker needs WooCommerce to be installed and active.', 'data-tracker-woocommerce' ),
			'solution' => __( 'Install and activate WooCommerce.', 'data-tracker-woocommerce' ),
			'action'   => admin_url( 'plugins.php' ),
		);

		$any_connected = $this->platforms->has_any_connected();
		$checks[] = array(
			'id'       => 'platforms',
			'status'   => $any_connected ? 'ok' : 'error',
			'weight'   => 30,
			'label'    => $any_connected ? __( 'Tracking platforms connected', 'data-tracker-woocommerce' ) : __( 'No tracking platforms connected yet', 'data-tracker-woocommerce' ),
			'message'  => $any_connected
				? __( 'At least one tracking platform is connected.', 'data-tracker-woocommerce' )
				: __( 'Connect Google Analytics, Meta Pixel or Google Ads to start tracking.', 'data-tracker-woocommerce' ),
			'solution' => __( 'Connect a tracking platform on the Connections page.', 'data-tracker-woocommerce' ),
			'action'   => admin_url( 'admin.php?page=data-tracker-connections' ),
		);

		foreach ( $this->platforms->all() as $id => $platform ) {
			$connected = $platform->is_connected();
			$checks[] = array(
				'id'       => 'platform_' . $id,
				'status'   => $connected ? 'ok' : 'warning',
				'weight'   => 20,
				'label'    => $connected
					/* translators: %s: platform label */
					? sprintf( __( '%s is connected', 'data-tracker-woocommerce' ), $platform->get_label() )
					/* translators: %s: platform label */
					: sprintf( __( '%s is not connected', 'data-tracker-woocommerce' ), $platform->get_label() ),
				'message'  => $connected
					/* translators: %s: platform label */
					? sprintf( __( '%s is ready to track your store.', 'data-tracker-woocommerce' ), $platform->get_label() )
					/* translators: %s: platform label */
					: sprintf( __( 'Connect %s to see its tracking here.', 'data-tracker-woocommerce' ), $platform->get_label() ),
				'solution' => /* translators: %s: platform label */
					sprintf( __( 'Add your %s tracking ID on the Connections page.', 'data-tracker-woocommerce' ), $platform->get_label() ),
				'action'   => admin_url( 'admin.php?page=data-tracker-connections' ),
			);
		}

		$enabled = (array) $this->options->get( 'enabled_events' );
		$purchase_enabled = in_array( 'purchase', $enabled, true );
		$checks[] = array(
			'id'       => 'purchase_tracking',
			'status'   => ( $wc_active && $purchase_enabled ) ? 'ok' : 'warning',
			'weight'   => 10,
			'label'    => ( $wc_active && $purchase_enabled ) ? __( 'Purchase tracking is on', 'data-tracker-woocommerce' ) : __( 'Purchase tracking needs attention', 'data-tracker-woocommerce' ),
			'message'  => __( 'WooCommerce purchase tracking is set up automatically.', 'data-tracker-woocommerce' ),
			'solution' => __( 'Make sure WooCommerce is active and purchase tracking is enabled on the Tracking page.', 'data-tracker-woocommerce' ),
			'action'   => admin_url( 'admin.php?page=data-tracker-tracking' ),
		);

		$scan = $this->detector->scan();
		$duplicate_maps = array(
			'ga4'        => array( 'id' => 'ga4_measurement_id', 'label' => 'Google Analytics' ),
			'meta'       => array( 'id' => 'meta_pixel_id', 'label' => 'Meta Pixel' ),
			'google_ads' => array( 'id' => 'google_ads_conversion_id', 'label' => 'Google Ads' ),
		);

		foreach ( $duplicate_maps as $key => $map ) {
			$configured = (string) $this->options->get( $map['id'] );
			$found      = isset( $scan['found'][ $key ] ) ? (array) $scan['found'][ $key ] : array();
			$extra      = array();

			foreach ( $found as $candidate ) {
				$candidate = strtoupper( $candidate );
				if ( '' !== $configured && strpos( strtoupper( $configured ), $candidate ) !== false ) {
					continue;
				}
				$extra[] = $candidate;
			}

			if ( ! empty( $extra ) ) {
				$checks[] = array(
					'id'       => 'duplicate_' . $key,
					'status'   => 'warning',
					'weight'   => 5,
					'label'    => /* translators: %s: platform label */
						sprintf( __( 'Another %s setup was found', 'data-tracker-woocommerce' ), $map['label'] ),
					'message'  => /* translators: 1: platform label, 2: detected id */
						sprintf( __( 'We found another %1$s tracking setup on your website (%2$s). This may cause duplicate data.', 'data-tracker-woocommerce' ), $map['label'], implode( ', ', $extra ) ),
					'solution' => __( 'Remove the other tracking code for this platform to avoid counting the same events twice.', 'data-tracker-woocommerce' ),
				);
			}
		}

		$other_plugins = $this->detector->detect_other_plugins();
		if ( ! empty( $other_plugins ) ) {
			$checks[] = array(
				'id'       => 'other_plugins',
				'status'   => 'info',
				'weight'   => 0,
				'label'    => __( 'Another tracking plugin is active', 'data-tracker-woocommerce' ),
				'message'  => /* translators: %s: plugin names */
					sprintf( __( 'We found another tracking plugin active: %s. This may cause duplicate tracking.', 'data-tracker-woocommerce' ), implode( ', ', array_values( $other_plugins ) ) ),
				'solution' => __( 'If you see double-counted events, consider deactivating the other tracking plugin for the same platform.', 'data-tracker-woocommerce' ),
			);
		}

		if ( $wc_active ) {
			$activity = $this->event_log->has_activity();
			$checks[] = array(
				'id'       => 'event_activity',
				'status'   => $activity ? 'ok' : 'info',
				'weight'   => 10,
				'label'    => $activity ? __( 'Recent tracking events received', 'data-tracker-woocommerce' ) : __( 'No recent tracking events yet', 'data-tracker-woocommerce' ),
				'message'  => $activity
					? __( 'Your store is sending tracking events.', 'data-tracker-woocommerce' )
					: __( 'Run a tracking test to confirm events are being sent.', 'data-tracker-woocommerce' ),
				'solution' => __( 'Use Test Tracking to send a test event through your store.', 'data-tracker-woocommerce' ),
				'action'   => admin_url( 'admin.php?page=data-tracker-test' ),
			);

			$purchase_verified = $this->event_log->has_event( 'purchase' );
			$checks[] = array(
				'id'       => 'purchase_verified',
				'status'   => $purchase_verified ? 'ok' : 'info',
				'weight'   => 5,
				'label'    => $purchase_verified ? __( 'Purchase tracking verified', 'data-tracker-woocommerce' ) : __( 'Purchase tracking not verified yet', 'data-tracker-woocommerce' ),
				'message'  => $purchase_verified
					? __( 'A purchase event was received recently, so purchase tracking is confirmed.', 'data-tracker-woocommerce' )
					: __( 'We have not confirmed a purchase event yet.', 'data-tracker-woocommerce' ),
				'solution' => __( 'Complete a small test order or run a tracking test to verify purchase tracking.', 'data-tracker-woocommerce' ),
				'action'   => admin_url( 'admin.php?page=data-tracker-test' ),
			);

			$last_tested = $this->event_log->last_tested();
			$checks[] = array(
				'id'       => 'last_tested',
				'status'   => $last_tested ? 'ok' : 'info',
				'weight'   => 5,
				'label'    => $last_tested ? __( 'Tracking test run', 'data-tracker-woocommerce' ) : __( 'Tracking not tested yet', 'data-tracker-woocommerce' ),
				'message'  => $last_tested
					/* translators: %s: relative time, e.g. "5 minutes ago" */
					? sprintf( __( 'Your tracking was last tested %s.', 'data-tracker-woocommerce' ), EventLog::relative_time( $last_tested ) )
					: __( 'Run a tracking test to confirm events arrive at your platforms.', 'data-tracker-woocommerce' ),
				'solution' => __( 'Use Test Tracking to send a test event through your store.', 'data-tracker-woocommerce' ),
				'action'   => admin_url( 'admin.php?page=data-tracker-test' ),
			);

			$has_attribution = $this->recent_attribution_exists();
			$checks[] = array(
				'id'       => 'attribution',
				'status'   => $has_attribution ? 'ok' : 'info',
				'weight'   => 5,
				'label'    => $has_attribution ? __( 'Attribution data is being captured', 'data-tracker-woocommerce' ) : __( 'Attribution data will be captured', 'data-tracker-woocommerce' ),
				'message'  => $has_attribution
					? __( 'Recent orders are storing traffic source information.', 'data-tracker-woocommerce' )
					: __( 'Traffic source information is stored with your next order.', 'data-tracker-woocommerce' ),
				'solution' => __( 'No action needed. Attribution is captured automatically.', 'data-tracker-woocommerce' ),
			);
		}

		$this->checks = $checks;
		return $this->checks;
	}

	/**
	 * Whether a recent order has attribution data.
	 *
	 * @return bool
	 */
	private function recent_attribution_exists() {
		global $wpdb;

		$cutoff = gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) );
		$count  = 0;

		if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
			$orders_table = $wpdb->prefix . 'wc_orders';
			$meta_table   = $wpdb->prefix . 'wc_orders_meta';

			$count = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$meta_table} WHERE meta_key = '_dtw_attribution' AND meta_value <> '' AND order_id IN (
						SELECT id FROM {$orders_table} WHERE type = 'shop_order' AND date_created_gmt >= %s
					)",
					$cutoff
				)
			);
		} else {
			$count = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_dtw_attribution' AND meta_value <> '' AND post_id IN (
						SELECT ID FROM {$wpdb->posts} WHERE post_type = 'shop_order' AND post_date >= %s
					)",
					$cutoff
				)
			);
		}

		return $count > 0;
	}

	/**
	 * Health score from 0 to 100.
	 *
	 * @return int
	 */
	public function get_score() {
		$total  = 0;
		$earned = 0;

		foreach ( $this->get_checks() as $check ) {
			$weight = (int) $check['weight'];
			$total += $weight;

			if ( 'ok' === $check['status'] ) {
				$earned += $weight;
			} elseif ( 'warning' === $check['status'] ) {
				$earned += (int) floor( $weight / 2 );
			}
		}

		if ( $total <= 0 ) {
			return 0;
		}

		return (int) round( ( $earned / $total ) * 100 );
	}

	/**
	 * Plain language health summary.
	 *
	 * @return string
	 */
	public function get_summary() {
		$score = $this->get_score();

		if ( $score >= 85 ) {
			return __( 'No critical problems detected.', 'data-tracker-woocommerce' );
		}
		if ( $score >= 60 ) {
			return __( 'Some tracking needs attention.', 'data-tracker-woocommerce' );
		}
		if ( $score >= 40 ) {
			return __( 'Several tracking issues need attention.', 'data-tracker-woocommerce' );
		}
		return __( 'Tracking needs immediate attention.', 'data-tracker-woocommerce' );
	}

	/**
	 * Checks with a warning or error status.
	 *
	 * @return array
	 */
	public function get_problems() {
		return array_values(
			array_filter(
				$this->get_checks(),
				function ( $check ) {
					return in_array( $check['status'], array( 'warning', 'error' ), true );
				}
			)
		);
	}

	/**
	 * Informational checks that suggest useful next steps.
	 *
	 * @return array
	 */
	public function get_next_steps() {
		return array_values(
			array_filter(
				$this->get_checks(),
				function ( $check ) {
					return 'info' === $check['status'];
				}
			)
		);
	}

	/**
	 * Number of checks that need attention (warning or error).
	 *
	 * @return int
	 */
	public function get_issue_count() {
		return count( $this->get_problems() );
	}
}