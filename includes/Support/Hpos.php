<?php
/**
 * HPOS detection helper.
 *
 * @package DataTracker
 */

namespace DataTracker\Support;

defined( 'ABSPATH' ) || exit;

/**
 * Safe wrapper around WooCommerce High-Performance Order Storage detection.
 */
final class Hpos {

	/**
	 * Whether HPOS is enabled, with guards for older WooCommerce versions.
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		$util = '\Automattic\WooCommerce\Utilities\OrderUtil';

		if ( ! class_exists( $util ) ) {
			return false;
		}

		if ( ! method_exists( $util, 'custom_orders_table_usage_is_enabled' ) ) {
			return false;
		}

		return $util::custom_orders_table_usage_is_enabled();
	}
}