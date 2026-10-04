<?php
/**
 * Platform manager.
 *
 * @package DataTracker
 */

namespace DataTracker\Platforms;

defined( 'ABSPATH' ) || exit;

use DataTracker\Core\Options;

/**
 * Instantiates and exposes the registered tracking platforms.
 */
class PlatformManager {

	/**
	 * Options.
	 *
	 * @var Options
	 */
	private $options;

	/**
	 * Platform instances.
	 *
	 * @var TrackingPlatform[]
	 */
	private $platforms = array();

	/**
	 * Constructor.
	 *
	 * @param Options $options Options.
	 */
	public function __construct( Options $options ) {
		$this->options = $options;
	}

	/**
	 * All registered platforms.
	 *
	 * @return TrackingPlatform[]
	 */
	public function all() {
		if ( empty( $this->platforms ) ) {
			$this->platforms = array(
				'ga4'        => new GA4\GA4Platform( $this->options ),
				'meta'       => new Meta\MetaPlatform( $this->options ),
				'google_ads' => new GoogleAds\GoogleAdsPlatform( $this->options ),
			);
		}
		return $this->platforms;
	}

	/**
	 * Get a single platform by id.
	 *
	 * @param string $id Platform id.
	 * @return TrackingPlatform|null
	 */
	public function get( $id ) {
		$platforms = $this->all();
		return isset( $platforms[ $id ] ) ? $platforms[ $id ] : null;
	}

	/**
	 * Platforms that are currently connected.
	 *
	 * @return TrackingPlatform[]
	 */
	public function connected() {
		$connected = array();
		foreach ( $this->all() as $id => $platform ) {
			if ( $platform->is_connected() ) {
				$connected[ $id ] = $platform;
			}
		}
		return $connected;
	}

	/**
	 * Whether any platform is connected.
	 *
	 * @return bool
	 */
	public function has_any_connected() {
		return count( $this->connected() ) > 0;
	}

	/**
	 * Frontend config for every platform.
	 *
	 * @return array
	 */
	public function get_frontend_config() {
		$config = array();
		foreach ( $this->all() as $platform ) {
			$config[ $platform->get_id() ] = $platform->get_frontend_config();
		}
		return $config;
	}
}