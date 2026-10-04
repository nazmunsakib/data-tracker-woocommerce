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