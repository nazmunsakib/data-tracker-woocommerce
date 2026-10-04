<?php
/**
 * Tracking page.
 *
 * @package DataTracker
 */

namespace DataTracker\Admin;

defined( 'ABSPATH' ) || exit;

use DataTracker\Core\Options;

/**
 * Renders the "what to track" page.
 */
class Tracking {

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
	 * Render the page.
	 *
	 * @return void
	 */
	public function render() {
		$enabled = (array) $this->options->get( 'enabled_events' );
		$events  = Options::$all_events;

		$wc_active = class_exists( 'WooCommerce' );

		include DTW_PLUGIN_DIR . 'templates/admin/tracking.php';
	}
}