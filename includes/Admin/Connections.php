<?php
/**
 * Connections page.
 *
 * @package DataTracker
 */

namespace DataTracker\Admin;

defined( 'ABSPATH' ) || exit;

use DataTracker\Core\Options;
use DataTracker\Diagnostics\EventLog;
use DataTracker\Platforms\PlatformManager;

/**
 * Renders the platform connection cards.
 */
class Connections {

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
	 * Constructor.
	 *
	 * @param Options         $options   Options.
	 * @param PlatformManager $platforms Platforms.
	 */
	public function __construct( Options $options, PlatformManager $platforms ) {
		$this->options   = $options;
		$this->platforms = $platforms;
	}

	/**
	 * Render the page.
	 *
	 * @return void
	 */
	public function render() {
		$statuses = array();
		foreach ( $this->platforms->all() as $platform ) {
			$statuses[] = $platform->get_status();
		}

		$fields = array(
			'ga4' => array(
				'key'         => 'ga4_measurement_id',
				'label'       => __( 'Measurement ID', 'data-tracker-woocommerce' ),
				'help'        => __( 'Found in Google Analytics under Admin > Data Streams. It starts with G- (this is not a Google Tag Manager container ID).', 'data-tracker-woocommerce' ),
				'value'       => $this->options->get( 'ga4_measurement_id' ),
				'placeholder' => 'G-XXXXXXXXXX',
			),
			'meta' => array(
				'key'         => 'meta_pixel_id',
				'label'       => __( 'Pixel ID', 'data-tracker-woocommerce' ),
				'help'        => __( 'Found in Meta Events Manager. It is a number, usually 15 digits long.', 'data-tracker-woocommerce' ),
				'value'       => $this->options->get( 'meta_pixel_id' ),
				'placeholder' => '123456789012345',
			),
		);

		$ads = array(
			'id'    => array(
				'key'       => 'google_ads_conversion_id',
				'label'     => __( 'Conversion ID', 'data-tracker-woocommerce' ),
				'help'      => __( 'Found in Google Ads under Tools > Conversions. It looks like AW-123456789.', 'data-tracker-woocommerce' ),
				'value'     => $this->options->get( 'google_ads_conversion_id' ),
				'placeholder' => 'AW-123456789',
			),
			'label' => array(
				'key'       => 'google_ads_conversion_label',
				'label'     => __( 'Conversion Label', 'data-tracker-woocommerce' ),
				'help'      => __( 'The label that goes with your conversion action.', 'data-tracker-woocommerce' ),
				'value'     => $this->options->get( 'google_ads_conversion_label' ),
				'placeholder' => 'XXXXX_XXXXX',
			),
		);

		$error_msg = isset( $_GET['dtw_error'] ) ? sanitize_text_field( wp_unslash( $_GET['dtw_error'] ) ) : '';

		$event_log   = new EventLog();
		$last_tested = $event_log->last_tested();

		include DTW_PLUGIN_DIR . 'templates/admin/connections.php';
	}
}