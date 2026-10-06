<?php
/**
 * Public write-token for the frontend event log.
 *
 * @package DataTracker
 */

namespace DataTracker\Support;

defined( 'ABSPATH' ) || exit;

/**
 * The frontend event log accepts reports from the tracking script. Because
 * pages can be served from cache (where session nonces go stale), the log uses
 * a stable site-wide write token instead. The token only authorizes writing to
 * a bounded, sanitized, rate-limited diagnostic log - it never grants access to
 * other plugin data or actions.
 */
final class LogToken {

	const OPTION = 'dtw_public_token';

	/**
	 * Get (and lazily create) the write token.
	 *
	 * @return string
	 */
	public static function get() {
		$token = get_option( self::OPTION );

		if ( ! is_string( $token ) || '' === $token ) {
			$token = wp_generate_password( 40, false, false );
			update_option( self::OPTION, $token, false );
		}

		return $token;
	}

	/**
	 * Whether a candidate matches the write token.
	 *
	 * @param mixed $candidate Candidate value.
	 * @return bool
	 */
	public static function verify( $candidate ) {
		$token = self::get();

		return is_string( $candidate ) && hash_equals( $token, $candidate );
	}
}