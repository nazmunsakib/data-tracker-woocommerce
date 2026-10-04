<?php
/**
 * Event log.
 *
 * @package DataTracker
 */

namespace DataTracker\Diagnostics;

defined( 'ABSPATH' ) || exit;

/**
 * Stores a small ring buffer of the most recent tracking events reported from
 * the storefront so admins can verify tracking is working.
 */
class EventLog {

	const KEY          = 'dtw_event_log';
	const LIMIT        = 50;
	const TESTED_KEY   = 'dtw_last_tested';

	/**
	 * Add an entry to the log.
	 *
	 * @param array $entry Log entry.
	 * @return bool
	 */
	public function log( $entry ) {
		$entries = $this->get();
		array_unshift( $entries, $entry );
		$entries = array_slice( $entries, 0, self::LIMIT );
		return set_transient( self::KEY, $entries, WEEK_IN_SECONDS );
	}

	/**
	 * Record that a tracking test was run.
	 *
	 * @return void
	 */
	public function mark_tested() {
		set_transient( self::TESTED_KEY, time(), WEEK_IN_SECONDS );
	}

	/**
	 * Timestamp of the last tracking test, or 0 if never tested.
	 *
	 * @return int
	 */
	public function last_tested() {
		return (int) get_transient( self::TESTED_KEY );
	}

	/**
	 * Human readable "X ago" for a timestamp.
	 *
	 * @param int $timestamp Timestamp.
	 * @return string
	 */
	public static function relative_time( $timestamp ) {
		if ( ! $timestamp ) {
			return __( 'Not tested yet', 'data-tracker-woocommerce' );
		}
		return sprintf(
			/* translators: %s: human readable time difference, e.g. "5 minutes" */
			__( '%s ago', 'data-tracker-woocommerce' ),
			human_time_diff( (int) $timestamp, time() )
		);
	}

	/**
	 * All stored entries, newest first.
	 *
	 * @return array
	 */
	public function get() {
		$entries = get_transient( self::KEY );
		return is_array( $entries ) ? $entries : array();
	}

	/**
	 * Entries newer than a given number of days.
	 *
	 * @param int $days Number of days.
	 * @return array
	 */
	public function recent( $days = 7 ) {
		$cutoff = time() - $days * DAY_IN_SECONDS;

		return array_values(
			array_filter(
				$this->get(),
				function ( $entry ) use ( $cutoff ) {
					return isset( $entry['time'] ) && (int) $entry['time'] >= $cutoff;
				}
			)
		);
	}

	/**
	 * Whether a specific event name was logged within the given window.
	 *
	 * @param string $name Event name.
	 * @param int    $days Number of days.
	 * @return bool
	 */
	public function has_event( $name, $days = 30 ) {
		$cutoff = time() - $days * DAY_IN_SECONDS;

		foreach ( $this->get() as $entry ) {
			if ( isset( $entry['name'] ) && $entry['name'] === $name && (int) $entry['time'] >= $cutoff ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Whether any entries exist within the given window.
	 *
	 * @param int $days Number of days.
	 * @return bool
	 */
	public function has_activity( $days = 7 ) {
		return count( $this->recent( $days ) ) > 0;
	}

	/**
	 * Empty the log.
	 *
	 * @return void
	 */
	public function clear() {
		delete_transient( self::KEY );
	}
}