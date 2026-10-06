<?php
/**
 * WooCommerce event listeners.
 *
 * @package DataTracker
 */

namespace DataTracker\WooCommerce;

defined( 'ABSPATH' ) || exit;

use DataTracker\Attribution\UtmCapture;
use DataTracker\Core\Options;
use DataTracker\Diagnostics\EventLog;

/**
 * Listens to WooCommerce lifecycle hooks and attaches attribution data to orders.
 */
class EventListener {

	/**
	 * Options.
	 *
	 * @var Options
	 */
	private $options;

	/**
	 * UTM capture.
	 *
	 * @var UtmCapture
	 */
	private $utm;

	/**
	 * Constructor.
	 *
	 * @param Options    $options Options.
	 * @param UtmCapture $utm     UTM capture.
	 */
	public function __construct( Options $options, UtmCapture $utm ) {
		$this->options = $options;
		$this->utm     = $utm;

		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'attach_attribution_to_order' ), 10, 2 );
		add_action( 'woocommerce_new_order', array( $this, 'attach_attribution_to_order' ), 10, 1 );
		add_action( 'woocommerce_order_status_changed', array( $this, 'log_trackable_purchase' ), 10, 4 );
	}

	/**
	 * Record a confirmed purchase in the diagnostic event log.
	 *
	 * This is a local, diagnostic-only record written from the authoritative
	 * WooCommerce order status transition. It does NOT send anything to any
	 * platform (no server-side conversion APIs in v1.0). It only ensures a
	 * confirmed purchase is visible as evidence in Recent Activity even when
	 * the browser-to-REST event log is blocked.
	 *
	 * @param int          $order_id Order id.
	 * @param string       $from     Previous status.
	 * @param string       $to       New status.
	 * @param \WC_Order|false $order Order object.
	 * @return void
	 */
	public function log_trackable_purchase( $order_id, $from, $to, $order ) {
		if ( ! $this->options->get( 'debug_log' ) ) {
			return;
		}

		$enabled = (array) $this->options->get( 'enabled_events' );
		if ( ! in_array( 'purchase', $enabled, true ) ) {
			return;
		}

		if ( in_array( $to, array( 'failed', 'cancelled', 'refunded', 'pending', 'checkout-draft' ), true ) ) {
			return;
		}

		if ( ! $order instanceof \WC_Order ) {
			$order = wc_get_order( $order_id );
		}

		if ( ! $order ) {
			return;
		}

		if ( $order->get_meta( '_dtw_purchase_logged', true ) ) {
			return;
		}

		$order->update_meta_data( '_dtw_purchase_logged', '1' );
		$order->save();

		( new EventLog() )->log(
			array(
				'time'      => time(),
				'name'      => 'purchase',
				'platform'  => 'woocommerce',
				'page_type' => 'order-received',
				'payload'   => array(
					'transaction_id' => (string) $order->get_id(),
					'value'          => (string) round( (float) $order->get_total(), 2 ),
					'currency'       => $order->get_currency(),
					'item_count'     => count( $order->get_items() ),
				),
				'url'       => '',
			)
		);
	}

	/**
	 * Attach available attribution data to a new order.
	 *
	 * @param int   $order_id Order id.
	 * @param array $data     Optional checkout posted data.
	 * @return void
	 */
	public function attach_attribution_to_order( $order_id, $data = array() ) {
		if ( ! $this->options->get( 'store_attribution' ) ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		if ( $order->get_meta( '_dtw_attribution', true ) ) {
			return;
		}

		$attribution = $this->utm->get_from_cookie();
		if ( empty( $attribution['first'] ) && empty( $attribution['last'] ) ) {
			return;
		}

		$last = isset( $attribution['last'] ) ? $attribution['last'] : array();

		$order->update_meta_data( '_dtw_attribution', wp_json_encode( $attribution ) );
		$order->update_meta_data( '_dtw_source', isset( $last['source'] ) ? $last['source'] : '' );
		$order->update_meta_data( '_dtw_medium', isset( $last['medium'] ) ? $last['medium'] : '' );
		$order->update_meta_data( '_dtw_campaign', isset( $last['campaign'] ) ? $last['campaign'] : '' );
		$order->update_meta_data( '_dtw_first_touch', isset( $attribution['first'] ) ? wp_json_encode( $attribution['first'] ) : '' );
		$order->update_meta_data( '_dtw_last_touch', isset( $attribution['last'] ) ? wp_json_encode( $attribution['last'] ) : '' );
		$order->save();
	}
}