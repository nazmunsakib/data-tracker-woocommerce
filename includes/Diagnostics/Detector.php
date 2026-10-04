<?php
/**
 * Problem and duplicate tracking detector.
 *
 * @package DataTracker
 */

namespace DataTracker\Diagnostics;

defined( 'ABSPATH' ) || exit;

/**
 * Scans the storefront for other tracking setups and known tracking plugins.
 */
class Detector {

	const CACHE_KEY = 'dtw_duplicate_scan';
	const CACHE_TTL = 6 * HOUR_IN_SECONDS;

	/**
	 * Known tracking plugins that may duplicate tracking.
	 *
	 * @return array
	 */
	private static function known_plugins() {
		return array(
			'duracelltomi-google-tag-manager/duracelltomi-google-tag-manager-for-wordpress.php' => 'Google Tag Manager for WordPress (GTM4WP)',
			'pixelyoursite/pixelyoursite.php' => 'PixelYourSite',
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'woocommerce-google-analytics-integration/woocommerce-google-analytics-integration.php' => 'WooCommerce Google Analytics Integration',
			'facebook-for-woocommerce/facebook-for-woocommerce.php' => 'Facebook for WooCommerce',
			'ga-google-analytics/ga-google-analytics.php' => 'GA Google Analytics',
			'woocommerce-google-adwords-conversion-tracking-tag/woocommerce-google-adwords-conversion-tracking-tag.php' => 'Google AdWords Conversion Tracking for WooCommerce',
			'enhanced-e-commerce-for-woocommerce-store/enhanced-ecommerce-google-analytics.php' => 'Enhanced E-commerce Google Analytics for WooCommerce',
			'woocommerce-google-listings-and-ads/woocommerce-google-listings-and-ads.php' => 'Google Listings and Ads',
			'site-kit-by-google/google-site-kit.php' => 'Google Site Kit',
		);
	}

	/**
	 * Scan the homepage HTML for duplicate tracking IDs.
	 *
	 * @return array
	 */
	public function scan() {
		$cached = get_transient( self::CACHE_KEY );

		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}

		$found = array(
			'ga4'        => array(),
			'google_ads' => array(),
			'meta'       => array(),
			'gtm'        => array(),
		);

		$html = $this->fetch_homepage();

		if ( '' !== $html ) {
			if ( preg_match_all( '/[^A-Z0-9](G-[A-Z0-9]{6,})[^A-Z0-9]/', $html, $matches ) ) {
				$found['ga4'] = $this->unique_matches( $matches, 1 );
			}
			if ( preg_match_all( '/[^A-Z0-9](AW-[0-9]{6,})[^A-Z0-9]/', $html, $matches ) ) {
				$found['google_ads'] = $this->unique_matches( $matches, 1 );
			}
			if ( preg_match_all( '/fbq\([\'" ]*init[\'" ]*,[\'" ]*([0-9]{5,20})/i', $html, $matches ) ) {
				$found['meta'] = $this->unique_matches( $matches, 1 );
			}
			if ( preg_match_all( '/GTM-[A-Z0-9]{4,}/', $html, $matches ) ) {
				$found['gtm'] = $this->unique_matches( $matches, 0 );
			}
		}

		$result = array(
			'found' => $found,
			'time'  => time(),
		);

		set_transient( self::CACHE_KEY, $result, self::CACHE_TTL );

		return $result;
	}

	/**
	 * De-duplicate a capture group, guarding against optional/absent groups.
	 *
	 * @param array  $matches preg_match_all output.
	 * @param int    $group   Capture group index (0 = full matches).
	 * @return array
	 */
	private function unique_matches( $matches, $group ) {
		$values = isset( $matches[ $group ] ) ? (array) $matches[ $group ] : array();
		return array_values( array_unique( $values ) );
	}

	/**
	 * Clear the cached scan.
	 *
	 * @return void
	 */
	public function clear_cache() {
		delete_transient( self::CACHE_KEY );
	}

	/**
	 * Active known tracking plugins.
	 *
	 * @return array Plugin names keyed by plugin slug.
	 */
	public function detect_other_plugins() {
		$active = (array) get_option( 'active_plugins', array() );
		$known  = self::known_plugins();
		$found  = array();

		foreach ( $known as $slug => $name ) {
			if ( in_array( $slug, $active, true ) ) {
				$found[ $slug ] = $name;
			}
		}

		return $found;
	}

	/**
	 * Fetch the homepage HTML.
	 *
	 * @return string
	 */
	private function fetch_homepage() {
		$response = wp_remote_get(
			home_url( '/' ),
			array(
				'timeout'     => 5,
				'redirection' => 2,
				'sslverify'   => true,
				'headers'     => array( 'User-Agent' => 'DataTracker-Diagnostics/1.0' ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return '';
		}

		return wp_remote_retrieve_body( $response );
	}
}