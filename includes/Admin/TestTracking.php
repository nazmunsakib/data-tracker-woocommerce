<?php
/**
 * Test Tracking page.
 *
 * @package DataTracker
 */

namespace DataTracker\Admin;

defined( 'ABSPATH' ) || exit;

use DataTracker\Core\Options;
use DataTracker\Platforms\PlatformManager;

/**
 * Renders the tracking test page.
 */
class TestTracking {

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
		$wc_active    = class_exists( 'WooCommerce' );
		$enabled      = (array) $this->options->get( 'enabled_events' );

		$event_checks = array(
			'product_view' => array(
				'label' => __( 'Product View', 'data-tracker-woocommerce' ),
				'ok'    => $wc_active && in_array( 'product_view', $enabled, true ),
			),
			'add_to_cart' => array(
				'label' => __( 'Add to Cart', 'data-tracker-woocommerce' ),
				'ok'    => $wc_active && in_array( 'add_to_cart', $enabled, true ),
			),
			'begin_checkout' => array(
				'label' => __( 'Checkout', 'data-tracker-woocommerce' ),
				'ok'    => $wc_active && in_array( 'begin_checkout', $enabled, true ),
			),
			'purchase' => array(
				'label' => __( 'Purchase', 'data-tracker-woocommerce' ),
				'ok'    => $wc_active && in_array( 'purchase', $enabled, true ),
			),
		);

		$platform_checks = array(
			'ga4'        => array(
				'label' => 'Google Analytics',
				'ok'    => $this->platforms->get( 'ga4' )->is_connected(),
			),
			'meta'       => array(
				'label' => 'Meta Pixel',
				'ok'    => $this->platforms->get( 'meta' )->is_connected(),
			),
			'google_ads' => array(
				'label' => 'Google Ads',
				'ok'    => $this->platforms->get( 'google_ads' )->is_connected(),
			),
		);

		$any_connected = $this->platforms->has_any_connected();

		include DTW_PLUGIN_DIR . 'templates/admin/test-tracking.php';
	}
}