<?php
/**
 * Dashboard page.
 *
 * @package DataTracker
 */

namespace DataTracker\Admin;

defined( 'ABSPATH' ) || exit;

use DataTracker\Core\Options;
use DataTracker\Diagnostics\EventLog;
use DataTracker\Diagnostics\Health;
use DataTracker\Platforms\PlatformManager;

/**
 * Renders the tracking health dashboard.
 */
class Dashboard {

	/**
	 * Options.
	 *
	 * @var Options
	 */
	private $options;

	/**
	 * Platforms.
	 *
	 * @var PlatformManager
	 */
	private $platforms;

	/**
	 * Event log.
	 *
	 * @var EventLog
	 */
	private $event_log;

	/**
	 * Constructor.
	 *
	 * @param Options         $options   Options.
	 * @param PlatformManager $platforms Platforms.
	 * @param EventLog        $event_log Event log.
	 */
	public function __construct( Options $options, PlatformManager $platforms, EventLog $event_log ) {
		$this->options   = $options;
		$this->platforms = $platforms;
		$this->event_log = $event_log;
	}

	/**
	 * Render the page.
	 *
	 * @return void
	 */
	public function render() {
		$health        = new Health( $this->options, $this->platforms, $this->event_log );
		$score         = $health->get_score();
		$summary       = $health->get_summary();
		$issue_count   = $health->get_issue_count();
		$problems      = $health->get_problems();
		$next_steps    = $health->get_next_steps();
		$platforms     = $this->platforms->all();
		$event_status  = $this->event_status();
		$recent_events = array_slice( $this->event_log->recent( 7 ), 0, 6 );
		$last_tested   = $this->event_log->last_tested();
		$wc_active     = class_exists( 'WooCommerce' );
		$error_msg     = isset( $_GET['dtw_error'] ) ? sanitize_text_field( wp_unslash( $_GET['dtw_error'] ) ) : '';
		$connected     = $this->platforms->has_any_connected();
		$debug_mode    = (bool) $this->options->get( 'debug_mode' );

		include DTW_PLUGIN_DIR . 'templates/admin/dashboard.php';
	}

	/**
	 * Event status overview.
	 *
	 * @return array
	 */
	private function event_status() {
		$enabled = (array) $this->options->get( 'enabled_events' );
		$map     = array();

		foreach ( Options::$all_events as $key => $label ) {
			$map[] = array(
				'key'     => $key,
				'label'   => $label,
				'enabled' => in_array( $key, $enabled, true ),
			);
		}

		return $map;
	}
}