<?php
/**
 * Internationalization.
 *
 * @package DataTracker
 */

namespace DataTracker\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Loads the plugin text domain.
 */
class I18n {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Load translations.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'data-tracker-woocommerce', false, dirname( DTW_PLUGIN_BASENAME ) . '/languages' );
	}
}