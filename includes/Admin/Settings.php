<?php
/**
 * Settings page.
 *
 * @package DataTracker
 */

namespace DataTracker\Admin;

defined( 'ABSPATH' ) || exit;

use DataTracker\Core\Options;

/**
 * Renders the plugin settings.
 */
class Settings {

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
		$settings = array(
			'utm_tracking_enabled'  => (bool) $this->options->get( 'utm_tracking_enabled' ),
			'store_attribution'     => (bool) $this->options->get( 'store_attribution' ),
			'respect_consent'       => (bool) $this->options->get( 'respect_consent' ),
			'consent_defaults'      => (array) $this->options->get( 'consent_defaults', array( 'ad_storage' => 'denied', 'analytics_storage' => 'denied' ) ),
			'debug_log'             => (bool) $this->options->get( 'debug_log' ),
			'debug_mode'            => (bool) $this->options->get( 'debug_mode' ),
			'consent_api_available' => function_exists( 'wp_has_consent' ),
		);

		include DTW_PLUGIN_DIR . 'templates/admin/settings.php';
	}
}