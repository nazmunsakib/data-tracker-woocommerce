<?php
/**
 * Order attribution meta box.
 *
 * @package DataTracker
 */

namespace DataTracker\WooCommerce;

defined( 'ABSPATH' ) || exit;

use DataTracker\Attribution\AttributionData;
use DataTracker\Core\Options;

/**
 * Adds a Data Tracker attribution section to the WooCommerce order screen.
 */
class OrderAttribution {

	/**
	 * Options.
	 *
	 * @var Options
	 */
	private $options;

	/**
	 * Constructor.
	 *
	 * @param Options $options Options.
	 */
	public function __construct( Options $options ) {
		$this->options = $options;

		add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
	}

	/**
	 * Register the meta box on classic and HPOS order screens.
	 *
	 * @return void
	 */
	public function register_meta_box() {
		if ( ! $this->options->get( 'store_attribution' ) ) {
			return;
		}

		$screens = array( 'shop_order', 'woocommerce_page_wc-orders' );

		foreach ( $screens as $screen ) {
			add_meta_box(
				'dtw-order-attribution',
				'Data Tracker',
				array( $this, 'render_meta_box' ),
				$screen,
				'side',
				'default'
			);
		}
	}

	/**
	 * Render the meta box.
	 *
	 * @param \WP_Post|\WC_Order $post_or_order Post or order object.
	 * @return void
	 */
	public function render_meta_box( $post_or_order ) {
		$order = ( $post_or_order instanceof \WC_Order ) ? $post_or_order : wc_get_order( $post_or_order->ID );

		if ( ! $order ) {
			return;
		}

		$attribution = $order->get_meta( '_dtw_attribution', true );
		$attribution = $attribution ? json_decode( $attribution, true ) : array();

		$data = array(
			'source'   => $order->get_meta( '_dtw_source', true ),
			'medium'   => $order->get_meta( '_dtw_medium', true ),
			'campaign' => $order->get_meta( '_dtw_campaign', true ),
		);

		$first_touch = $order->get_meta( '_dtw_first_touch', true );
		$last_touch  = $order->get_meta( '_dtw_last_touch', true );

		$display = array(
			'data'        => $data,
			'first_touch' => $first_touch ? json_decode( $first_touch, true ) : array(),
			'last_touch'  => $last_touch ? json_decode( $last_touch, true ) : array(),
			'has_data'    => ! empty( $data['source'] ) || ! empty( $data['medium'] ) || ! empty( $data['campaign'] ) || ! empty( $attribution ),
			'first_label' => $first_touch ? AttributionData::label_for_touch( json_decode( $first_touch, true ) ) : '',
			'last_label'  => $last_touch ? AttributionData::label_for_touch( json_decode( $last_touch, true ) ) : '',
		);

		include DTW_PLUGIN_DIR . 'templates/order/attribution-box.php';
	}
}