<?php
/**
 * Tracking event value object.
 *
 * @package DataTracker
 */

namespace DataTracker\Tracking;

defined( 'ABSPATH' ) || exit;

/**
 * A single tracking event with its payload.
 */
class Event {

	/**
	 * Canonical event name.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Event payload.
	 *
	 * @var array
	 */
	private $data;

	/**
	 * Constructor.
	 *
	 * @param string $name Event name.
	 * @param array  $data Payload.
	 */
	public function __construct( $name, $data = array() ) {
		$this->name = $name;
		$this->data = $data;
	}

	/**
	 * Event name.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Event payload.
	 *
	 * @return array
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Array representation.
	 *
	 * @return array
	 */
	public function to_array() {
		return array(
			'name' => $this->name,
			'data' => $this->data,
		);
	}
}