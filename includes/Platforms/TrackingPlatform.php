<?php
/**
 * Tracking platform interface.
 *
 * @package DataTracker
 */

namespace DataTracker\Platforms;

defined( 'ABSPATH' ) || exit;

use DataTracker\Tracking\Event;

/**
 * Contract every tracking platform integration implements.
 */
interface TrackingPlatform {

	/**
	 * Platform id used across the plugin.
	 *
	 * @return string
	 */
	public function get_id();

	/**
	 * Human readable platform name.
	 *
	 * @return string
	 */
	public function get_label();

	/**
	 * Whether the platform is connected and usable.
	 *
	 * @return bool
	 */
	public function is_connected();

	/**
	 * Current connection status array.
	 *
	 * @return array
	 */
	public function get_status();

	/**
	 * Validate and store connection data.
	 *
	 * @param array $data Submitted data.
	 * @return string Normalized connection value.
	 */
	public function connect( $data );

	/**
	 * Remove the stored connection data.
	 *
	 * @return void
	 */
	public function disconnect();

	/**
	 * Return a client-side representation of an event for this platform.
	 *
	 * @param Event $event Event to track.
	 * @return array
	 */
	public function track_event( Event $event );

	/**
	 * Platform specific diagnostic checks.
	 *
	 * @return array
	 */
	public function diagnose();

	/**
	 * Frontend configuration exposed to the tracking script.
	 *
	 * @return array
	 */
	public function get_frontend_config();
}