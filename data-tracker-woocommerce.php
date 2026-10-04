<?php
/**
 * Plugin Name:       Data Tracker for WooCommerce
 * Plugin URI:        https://example.com/data-tracker-for-woocommerce
 * Description:       Easy ecommerce tracking for WooCommerce. Automatically track products, carts, checkouts and purchases with Google Analytics 4, Meta Pixel and Google Ads.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Nazmun Sakib
 * Author URI:        https://nazmunsakib.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       data-tracker-woocommerce
 * Domain Path:       /languages
 * WC requires at least: 7.0
 * WC tested up to:   10.8
 *
 * @package DataTracker
 */

defined( 'ABSPATH' ) || exit;

define( 'DTW_VERSION', '1.0.0' );
define( 'DTW_PLUGIN_FILE', __FILE__ );
define( 'DTW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DTW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'DTW_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'DTW_MIN_PHP', '7.4' );

if ( version_compare( PHP_VERSION, DTW_MIN_PHP, '<' ) ) {
	add_action(
		'admin_notices',
		function () {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html__( 'Data Tracker for WooCommerce requires PHP 7.4 or higher.', 'data-tracker-woocommerce' )
			);
		}
	);
	return;
}

$dtw_vendor_autoload = DTW_PLUGIN_DIR . 'vendor/autoload.php';

if ( is_readable( $dtw_vendor_autoload ) ) {
	require_once $dtw_vendor_autoload;
}

/**
 * Fallback autoloader for environments where Composer has not been run
 * (for example a WordPress.org install built from the source directory).
 */
if ( ! class_exists( '\\DataTracker\\Core\\Autoloader' ) && is_readable( DTW_PLUGIN_DIR . 'includes/Core/Autoloader.php' ) ) {
	require_once DTW_PLUGIN_DIR . 'includes/Core/Autoloader.php';
	\DataTracker\Core\Autoloader::register();
}

/**
 * Declare compatibility with WooCommerce High-Performance Order Storage.
 * The plugin uses storage-agnostic order APIs and supports both order storage types.
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( '\\Automattic\\WooCommerce\\Utilities\\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

register_activation_hook( __FILE__, array( '\\DataTracker\\Core\\Installer', 'activate' ) );
register_deactivation_hook( __FILE__, array( '\\DataTracker\\Core\\Installer', 'deactivate' ) );

/**
 * Return the main plugin instance.
 *
 * @return \DataTracker\Core\Plugin
 */
function dtw() {
	return \DataTracker\Core\Plugin::instance();
}

add_action(
	'plugins_loaded',
	function () {
		dtw()->run();
	}
);