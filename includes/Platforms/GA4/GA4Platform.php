<?php
/**
 * Google Analytics 4 platform.
 *
 * @package DataTracker
 */

namespace DataTracker\Platforms\GA4;

defined( 'ABSPATH' ) || exit;

use DataTracker\Core\Options;
use DataTracker\Platforms\TrackingPlatform;
use DataTracker\Tracking\Event;

/**
 * Google Analytics 4 (GA4) integration.
 */
class GA4Platform implements TrackingPlatform {

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
		return 'ga4';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label() {
		return __( 'Google Analytics 4', 'data-tracker-woocommerce' );
	}

	/**
	 * The stored measurement id.
	 *
	 * @return string
	 */
	public function get_measurement_id() {
		return (string) $this->options->get( 'ga4_measurement_id' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_connected() {
		return (bool) preg_match( '/^G-[A-Z0-9]{6,}$/i', $this->get_measurement_id() );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_status() {
		$id        = $this->get_measurement_id();
		$connected = $this->is_connected();

		return array(
			'id'        => $this->get_id(),
			'label'     => $this->get_label(),
			'connected' => $connected,
			'value'     => $id,
			'message'   => $connected
				/* translators: %s: measurement id */
				? sprintf( __( 'Connected to %s', 'data-tracker-woocommerce' ), $id )
				: __( 'Not connected', 'data-tracker-woocommerce' ),
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function connect( $data ) {
		$id = isset( $data['ga4_measurement_id'] ) ? sanitize_text_field( wp_unslash( $data['ga4_measurement_id'] ) ) : '';
		$id = strtoupper( trim( $id ) );

		if ( '' !== $id && ! preg_match( '/^G-[A-Z0-9]{6,}$/i', $id ) ) {
			return new \WP_Error( 'dtw_invalid_id', __( 'That Google Analytics ID does not look right. It should look like G-XXXXXXXXXX.', 'data-tracker-woocommerce' ) );
		}

		$this->options->update( 'ga4_measurement_id', $id );
		return $id;
	}

	/**
	 * {@inheritdoc}
	 */
	public function disconnect() {
		$this->options->update( 'ga4_measurement_id', '' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function track_event( Event $event ) {
		$map  = $this->get_event_map();
		$name = isset( $map[ $event->get_name() ] ) ? $map[ $event->get_name() ] : $event->get_name();

		return array(
			'type'    => 'dataLayer',
			'event'   => $name,
			'payload' => $event->get_data(),
		);
	}

	/**
	 * Canonical event to GA4 event name mapping.
	 *
	 * @return array
	 */
	public function get_event_map() {
		return array(
			'product_view'     => 'view_item',
			'add_to_cart'      => 'add_to_cart',
			'remove_from_cart' => 'remove_from_cart',
			'view_cart'        => 'view_cart',
			'begin_checkout'   => 'begin_checkout',
			'add_payment_info' => 'add_payment_info',
			'purchase'         => 'purchase',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_frontend_config() {
		$consent = array(
			'enabled'  => (bool) $this->options->get( 'respect_consent' ),
			'defaults' => (array) $this->options->get( 'consent_defaults', array( 'ad_storage' => 'denied', 'analytics_storage' => 'denied' ) ),
		);

		return array(
			'connected'      => $this->is_connected(),
			'measurement_id' => $this->get_measurement_id(),
			'event_map'      => $this->get_event_map(),
			'consent'        => $consent,
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
				'label'    => __( 'Google Analytics is not connected yet.', 'data-tracker-woocommerce' ),
				'solution' => __( 'Add your GA4 Measurement ID on the Connections page. It looks like G-XXXXXXXXXX.', 'data-tracker-woocommerce' ),
			);
		} elseif ( '' !== $this->get_measurement_id() && ! preg_match( '/^G-[A-Z0-9]{6,}$/i', $this->get_measurement_id() ) ) {
			$checks[] = array(
				'status'   => 'warning',
				'label'    => __( 'The Google Analytics ID does not look valid.', 'data-tracker-woocommerce' ),
				'solution' => __( 'A GA4 Measurement ID looks like G-XXXXXXXXXX.', 'data-tracker-woocommerce' ),
			);
		}

		return $checks;
	}
}