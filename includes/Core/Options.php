<?php
/**
 * Options storage.
 *
 * @package DataTracker
 */

namespace DataTracker\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Wrapper around the single plugin settings option.
 */
class Options {

	const OPTION_NAME = 'data_tracker_settings';

	/**
	 * All known events that can be tracked.
	 *
	 * @var array
	 */
	public static $all_events = array(
		'product_view'     => 'Product View',
		'add_to_cart'      => 'Add to Cart',
		'remove_from_cart' => 'Remove from Cart',
		'view_cart'        => 'View Cart',
		'begin_checkout'   => 'Begin Checkout',
		'add_payment_info' => 'Add Payment Information',
		'purchase'         => 'Purchase',
	);

	/**
	 * Default settings.
	 *
	 * @var array
	 */
	private $defaults = array(
		'ga4_measurement_id'        => '',
		'meta_pixel_id'             => '',
		'google_ads_conversion_id'  => '',
		'google_ads_conversion_label' => '',
		'enabled_events'            => array(
			'product_view',
			'add_to_cart',
			'remove_from_cart',
			'view_cart',
			'begin_checkout',
			'add_payment_info',
			'purchase',
		),
		'respect_consent'           => 0,
		'consent_defaults'          => array(
			'ad_storage'         => 'denied',
			'analytics_storage'  => 'denied',
		),
		'utm_tracking_enabled'      => 1,
		'store_attribution'         => 1,
		'debug_log'                 => 1,
		'debug_mode'                => 0,
		'show_welcome'              => 1,
		'dismissed_notices'         => array(),
	);

	/**
	 * Current settings.
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->settings = get_option( self::OPTION_NAME, array() );
	}

	/**
	 * Get a single setting.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Optional fallback.
	 * @return mixed
	 */
	public function get( $key, $default = null ) {
		if ( array_key_exists( $key, $this->settings ) ) {
			return $this->settings[ $key ];
		}
		if ( null !== $default ) {
			return $default;
		}
		return array_key_exists( $key, $this->defaults ) ? $this->defaults[ $key ] : null;
	}

	/**
	 * All settings merged with defaults.
	 *
	 * @return array
	 */
	public function all() {
		return wp_parse_args( $this->settings, $this->defaults );
	}

	/**
	 * Update a single setting.
	 *
	 * @param string $key   Setting key.
	 * @param mixed  $value Value.
	 * @return bool
	 */
	public function update( $key, $value ) {
		$this->settings[ $key ] = $value;
		return update_option( self::OPTION_NAME, $this->settings, false );
	}

	/**
	 * Update multiple settings at once.
	 *
	 * @param array $values Key/value pairs.
	 * @return bool
	 */
	public function bulk_update( $values ) {
		$this->settings = wp_parse_args( $values, $this->settings );
		return update_option( self::OPTION_NAME, $this->settings, false );
	}
}