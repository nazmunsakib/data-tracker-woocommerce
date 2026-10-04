<?php
/**
 * Uninstall handler.
 *
 * Deletes all plugin data when the plugin is deleted from the admin.
 *
 * @package DataTracker
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'data_tracker_settings' );
delete_option( 'dtw_version' );

delete_transient( 'dtw_event_log' );
delete_transient( 'dtw_duplicate_scan' );

global $wpdb;

$meta_keys = array( '_dtw_attribution', '_dtw_source', '_dtw_medium', '_dtw_campaign', '_dtw_first_touch', '_dtw_last_touch' );

$placeholders = implode( ', ', array_fill( 0, count( $meta_keys ), '%s' ) );

$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->postmeta} WHERE meta_key IN ( {$placeholders} )",
		$meta_keys
	)
);

$orders_meta_table = $wpdb->prefix . 'wc_orders_meta';

if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $orders_meta_table ) ) === $orders_meta_table ) {
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$orders_meta_table} WHERE meta_key IN ( {$placeholders} )",
			$meta_keys
		)
	);
}