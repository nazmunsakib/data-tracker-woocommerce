<?php
/**
 * UTM and advertising click capture.
 *
 * @package DataTracker
 */

namespace DataTracker\Attribution;

defined( 'ABSPATH' ) || exit;

use DataTracker\Core\Options;

/**
 * Reads the attribution cookie set by the frontend tracking script.
 */
class UtmCapture {

	const COOKIE = 'dtw_attribution';

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
	 * Whether UTM capture is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return (bool) $this->options->get( 'utm_tracking_enabled' );
	}

	/**
	 * Read and sanitize attribution data from the cookie.
	 *
	 * @return array
	 */
	public function get_from_cookie() {
		if ( ! $this->is_enabled() || empty( $_COOKIE[ self::COOKIE ] ) ) {
			return array();
		}

		$raw  = wp_unslash( $_COOKIE[ self::COOKIE ] );
		$data = json_decode( $raw, true );

		if ( ! is_array( $data ) ) {
			return array();
		}

		return $this->sanitize( $data );
	}

	/**
	 * Sanitize a full attribution payload.
	 *
	 * @param array $data Raw payload.
	 * @return array
	 */
	public function sanitize( $data ) {
		return array(
			'first' => isset( $data['first'] ) && is_array( $data['first'] ) ? $this->clean_touch( $data['first'] ) : array(),
			'last'  => isset( $data['last'] ) && is_array( $data['last'] ) ? $this->clean_touch( $data['last'] ) : array(),
		);
	}

	/**
	 * Sanitize a single touch object.
	 *
	 * @param array $touch Raw touch.
	 * @return array
	 */
	private function clean_touch( $touch ) {
		return array(
			'source'   => isset( $touch['source'] ) ? sanitize_text_field( $touch['source'] ) : '',
			'medium'   => isset( $touch['medium'] ) ? sanitize_text_field( $touch['medium'] ) : '',
			'campaign' => isset( $touch['campaign'] ) ? sanitize_text_field( $touch['campaign'] ) : '',
			'content'  => isset( $touch['content'] ) ? sanitize_text_field( $touch['content'] ) : '',
			'term'     => isset( $touch['term'] ) ? sanitize_text_field( $touch['term'] ) : '',
			'click_id' => isset( $touch['click_id'] ) ? sanitize_text_field( $touch['click_id'] ) : '',
			'ts'       => isset( $touch['ts'] ) ? absint( $touch['ts'] ) : 0,
		);
	}
}