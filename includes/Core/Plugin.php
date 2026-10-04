<?php
/**
 * Plugin bootstrap.
 *
 * @package DataTracker
 */

namespace DataTracker\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin container that wires the modules together.
 */
class Plugin {

	const VERSION = DTW_VERSION;

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Options.
	 *
	 * @var Options
	 */
	private $options;

	/**
	 * Platform manager.
	 *
	 * @var \DataTracker\Platforms\PlatformManager
	 */
	private $platforms;

	/**
	 * Event log.
	 *
	 * @var \DataTracker\Diagnostics\EventLog
	 */
	private $event_log;

	/**
	 * UTM capture.
	 *
	 * @var \DataTracker\Attribution\UtmCapture
	 */
	private $utm;

	/**
	 * Page context.
	 *
	 * @var \DataTracker\WooCommerce\PageContext
	 */
	private $page_context;

	/**
	 * Get the singleton instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Boot the plugin modules.
	 *
	 * @return void
	 */
	public function run() {
		$this->options      = new Options();
		$this->platforms    = new \DataTracker\Platforms\PlatformManager( $this->options );
		$this->event_log    = new \DataTracker\Diagnostics\EventLog();
		$this->utm          = new \DataTracker\Attribution\UtmCapture( $this->options );
		$this->page_context = new \DataTracker\WooCommerce\PageContext();

		new I18n();
		new \DataTracker\WooCommerce\EventListener( $this->options, $this->utm );
		new \DataTracker\WooCommerce\OrderAttribution( $this->options, $this->utm );
		new \DataTracker\Tracking\Tracker( $this->options, $this->platforms, $this->utm, $this->event_log, $this->page_context );
		new \DataTracker\Diagnostics\Health( $this->options, $this->platforms, $this->event_log );
		new \DataTracker\REST\Server( $this->options, $this->event_log, $this->platforms );
		new \DataTracker\Admin\Admin( $this->options, $this->platforms, $this->event_log );
	}

	/**
	 * Options accessor.
	 *
	 * @return Options
	 */
	public function options() {
		return $this->options;
	}

	/**
	 * Platform manager accessor.
	 *
	 * @return \DataTracker\Platforms\PlatformManager
	 */
	public function platforms() {
		return $this->platforms;
	}

	/**
	 * Event log accessor.
	 *
	 * @return \DataTracker\Diagnostics\EventLog
	 */
	public function event_log() {
		return $this->event_log;
	}

	/**
	 * UTM capture accessor.
	 *
	 * @return \DataTracker\Attribution\UtmCapture
	 */
	public function utm() {
		return $this->utm;
	}

	/**
	 * Page context accessor.
	 *
	 * @return \DataTracker\WooCommerce\PageContext
	 */
	public function page_context() {
		return $this->page_context;
	}
}