<?php
/**
 * Meta Pixel platform.
 *
 * @package DataTracker
 */

namespace DataTracker\Platforms\Meta;

defined( 'ABSPATH' ) || exit;

use DataTracker\Core\Options;
use DataTracker\Platforms\TrackingPlatform;
use DataTracker\Tracking\Event;

/**
 * Meta (Facebook) Pixel integration.
 */
class MetaPlatform implements TrackingPlatform {

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
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_id() {
		return 'meta';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label() {
		return __( 'Meta Pixel', 'data-tracker-woocommerce' );
	}

	/**
	 * The stored pixel id.
	 *
	 * @return string
	 */
	public function get_pixel_id() {
		return (string) $this->options->get( 'meta_pixel_id' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_connected() {
		return (bool) preg_match( '/^\d{14,20}$/', $this->get_pixel_id() );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_status() {
		$id        = $this->get_pixel_id();
		$connected = $this->is_connected();

		return array(
			'id'        => $this->get_id(),
			'label'     => $this->get_label(),
			'connected' => $connected,
			'value'     => $id,
			'message'   => $connected
				/* translators: %s: pixel id */
				? sprintf( __( 'Connected to %s', 'data-tracker-woocommerce' ), $id )
				: __( 'Not connected', 'data-tracker-woocommerce' ),
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function connect( $data ) {
		$id = isset( $data['meta_pixel_id'] ) ? sanitize_text_field( wp_unslash( $data['meta_pixel_id'] ) ) : '';
		$id = trim( $id );

		if ( '' !== $id && ! preg_match( '/^\d{14,20}$/', $id ) ) {
			return new \WP_Error(
				'dtw_invalid_id',
				__( 'That Meta Pixel ID does not look right. A Pixel ID is a number from Meta Events Manager, usually 15 digits long (for example 123456789012345).', 'data-tracker-woocommerce' )
			);
		}

		$this->options->update( 'meta_pixel_id', $id );
		return $id;
	}

	/**
	 * {@inheritdoc}
	 */
	public function disconnect() {
		$this->options->update( 'meta_pixel_id', '' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function track_event( Event $event ) {
		$map  = $this->get_event_map();
		$name = isset( $map[ $event->get_name() ] ) ? $map[ $event->get_name() ] : $event->get_name();

		return array(
			'type'    => 'fbq',
			'event'   => $name,
			'payload' => $event->get_data(),
		);
	}

	/**
	 * Canonical event to Meta event name mapping.
	 *
	 * @return array
	 */
	public function get_event_map() {
		return array(
			'product_view'     => 'ViewContent',
			'add_to_cart'      => 'AddToCart',
			'remove_from_cart' => 'RemoveFromCart',
			'view_cart'        => 'ViewContent',
			'begin_checkout'   => 'InitiateCheckout',
			'add_payment_info' => 'AddPaymentInfo',
			'purchase'         => 'Purchase',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_frontend_config() {
		return array(
			'connected'  => $this->is_connected(),
			'pixel_id'   => $this->get_pixel_id(),
			'event_map'  => $this->get_event_map(),
			'consent'    => array(
				'enabled' => (bool) $this->options->get( 'respect_consent' ),
			),
			'head_init'  => ! (bool) $this->options->get( 'respect_consent' ),
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function diagnose() {
		$checks = array();

		if ( ! $this->is_connected() ) {
			$checks[] = array(
				'status'   => 'error',
				'label'    => __( 'Meta Pixel is not connected yet.', 'data-tracker-woocommerce' ),
				'solution' => __( 'Add your Meta Pixel ID on the Connections page. It looks like 123456789.', 'data-tracker-woocommerce' ),
			);
		}

		return $checks;
	}
}