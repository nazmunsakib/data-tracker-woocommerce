<?php
/**
 * Activation and deactivation routines.
 *
 * @package DataTracker
 */

namespace DataTracker\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Handles plugin activation and deactivation.
 */
class Installer {

	/**
	 * Run on activation.
	 *
	 * @return void
	 */
	public static function activate() {
		$settings = get_option( Options::OPTION_NAME, array() );

		if ( empty( $settings ) ) {
			add_option( Options::OPTION_NAME, array( 'show_welcome' => 1 ) );
		} else {
			$settings['show_welcome'] = 1;
			update_option( Options::OPTION_NAME, $settings, false );
		}
	}

	/**
	 * Run on deactivation.
	 *
	 * @return void
	 */
	public static function deactivate() {
	}
}