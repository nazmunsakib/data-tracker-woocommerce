<?php
/**
 * Setup wizard page.
 *
 * @package DataTracker
 */

namespace DataTracker\Admin;

defined( 'ABSPATH' ) || exit;

use DataTracker\Core\Options;

/**
 * Renders the guided setup wizard.
 */
class Wizard {

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
		include DTW_PLUGIN_DIR . 'templates/admin/wizard.php';
	}
}