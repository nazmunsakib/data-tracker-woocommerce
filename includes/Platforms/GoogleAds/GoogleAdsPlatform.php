<?php
/**
 * Google Ads platform.
 *
 * @package DataTracker
 */

namespace DataTracker\Platforms\GoogleAds;

defined( 'ABSPATH' ) || exit;

use DataTracker\Core\Options;
use DataTracker\Platforms\TrackingPlatform;
use DataTracker\Tracking\Event;

/**
 * Google Ads conversion tracking integration.
 */
class GoogleAdsPlatform implements TrackingPlatform {

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
		return 'google_ads';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label() {
		return __( 'Google Ads', 'data-tracker-woocommerce' );
	}

	/**
	 * The stored conversion id.
	 *
	 * @return string
	 */
	public function get_conversion_id() {
		return (string) $this->options->get( 'google_ads_conversion_id' );
	}

	/**
	 * The stored conversion label.
	 *
	 * @return string
	 */
	public function get_conversion_label() {
		return (string) $this->options->get( 'google_ads_conversion_label' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_connected() {
		return (bool) preg_match( '/^AW-[0-9]{6,}$/i', $this->get_conversion_id() )
			&& '' !== $this->get_conversion_label();
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_status() {
		$connected = $this->is_connected();

		return array(
			'id'        => $this->get_id(),
			'label'     => $this->get_label(),
			'connected' => $connected,
			'value'     => $this->get_conversion_id(),
			'message'   => $connected
				/* translators: %s: conversion id */
				? sprintf( __( 'Connected to %s', 'data-tracker-woocommerce' ), $this->get_conversion_id() )
				: __( 'Not connected', 'data-tracker-woocommerce' ),
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function connect( $data ) {
		$id = isset( $data['google_ads_conversion_id'] ) ? sanitize_text_field( wp_unslash( $data['google_ads_conversion_id'] ) ) : '';
		$id = strtoupper( trim( $id ) );

		$label = isset( $data['google_ads_conversion_label'] ) ? sanitize_text_field( wp_unslash( $data['google_ads_conversion_label'] ) ) : '';
		$label = trim( $label );

		if ( '' !== $id && ! preg_match( '/^AW-[0-9]{6,}$/i', $id ) ) {
			return new \WP_Error( 'dtw_invalid_id', __( 'That Google Ads conversion ID does not look right. It should look like AW-123456789.', 'data-tracker-woocommerce' ) );
		}

		$this->options->update( 'google_ads_conversion_id', $id );
		$this->options->update( 'google_ads_conversion_label', $label );
		return $id;
	}

	/**
	 * {@inheritdoc}
	 */
	public function disconnect() {
		$this->options->update( 'google_ads_conversion_id', '' );
		$this->options->update( 'google_ads_conversion_label', '' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function track_event( Event $event ) {
		$payload = $event->get_data();

		return array(
			'type'    => 'gtag',
			'event'   => 'conversion',
			'payload' => array(
				'send_to'        => $this->get_conversion_id() . '/' . $this->get_conversion_label(),
				'transaction_id' => isset( $payload['transaction_id'] ) ? $payload['transaction_id'] : '',
				'value'          => isset( $payload['value'] ) ? $payload['value'] : 0,
				'currency'       => isset( $payload['currency'] ) ? $payload['currency'] : '',
			),
		);
	}

	/**
	 * Canonical event to Google Ads mapping. Only purchases are conversions.
	 *
	 * @return array
	 */
	public function get_event_map() {
		return array(
			'purchase' => 'conversion',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_frontend_config() {
		return array(
			'connected'        => $this->is_connected(),
			'conversion_id'    => $this->get_conversion_id(),
			'conversion_label' => $this->get_conversion_label(),
			'event_map'        => $this->get_event_map(),
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function diagnose() {
		$checks = array();

		if ( '' === $this->get_conversion_id() ) {
			$checks[] = array(
				'status'   => 'error',
				'label'    => __( 'Google Ads is not connected yet.', 'data-tracker-woocommerce' ),
				'solution' => __( 'Add your Google Ads conversion ID and label on the Connections page.', 'data-tracker-woocommerce' ),
			);
		} elseif ( ! $this->is_connected() ) {
			$checks[] = array(
				'status'   => 'warning',
				'label'    => __( 'The Google Ads conversion setup is incomplete.', 'data-tracker-woocommerce' ),
				'solution' => __( 'Add both the conversion ID and the conversion label to track purchases in Google Ads.', 'data-tracker-woocommerce' ),
			);
		}

		return $checks;
	}
}