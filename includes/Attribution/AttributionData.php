<?php
/**
 * Attribution display helpers.
 *
 * @package DataTracker
 */

namespace DataTracker\Attribution;

defined( 'ABSPATH' ) || exit;

/**
 * Turns raw attribution touch data into friendly labels for store owners.
 */
class AttributionData {

	/**
	 * Friendly source labels.
	 *
	 * @return array
	 */
	private static function sources() {
		return array(
			'google'     => 'Google',
			'facebook'   => 'Facebook',
			'instagram'  => 'Instagram',
			'twitter'    => 'Twitter / X',
			'x'          => 'Twitter / X',
			'pinterest'  => 'Pinterest',
			'linkedin'   => 'LinkedIn',
			'tiktok'     => 'TikTok',
			'youtube'    => 'YouTube',
			'snapchat'   => 'Snapchat',
			'bing'       => 'Bing',
			'yahoo'      => 'Yahoo',
			'gmail'      => 'Gmail',
			'outlook'    => 'Outlook',
			'direct'     => 'Direct',
			'newsletter' => 'Newsletter',
			'email'      => 'Email',
		);
	}

	/**
	 * Friendly medium labels.
	 *
	 * @return array
	 */
	private static function mediums() {
		return array(
			'cpc'     => 'CPC',
			'cpm'     => 'CPM',
			'organic' => 'Organic',
			'social'  => 'Social',
			'email'   => 'Email',
			'paid'    => 'Paid',
			'paid-social' => 'Paid Social',
			'referral' => 'Referral',
			'sms'     => 'SMS',
		);
	}

	/**
	 * Build a friendly label for a touch object.
	 *
	 * @param array $touch Touch data.
	 * @return string
	 */
	public static function label_for_touch( $touch ) {
		if ( ! is_array( $touch ) ) {
			return __( 'Direct', 'data-tracker-woocommerce' );
		}

		$source = isset( $touch['source'] ) ? strtolower( $touch['source'] ) : '';
		$medium = isset( $touch['medium'] ) ? strtolower( $touch['medium'] ) : '';

		$sources = self::sources();
		$mediums = self::mediums();

		$source_label = isset( $sources[ $source ] ) ? $sources[ $source ] : ucwords( $source );
		$medium_label = isset( $mediums[ $medium ] ) ? $mediums[ $medium ] : ucwords( $medium );

		if ( '' === $source && '' === $medium ) {
			return __( 'Direct', 'data-tracker-woocommerce' );
		}
		if ( '' === $medium ) {
			return $source_label;
		}
		return $source_label . ' / ' . $medium_label;
	}
}