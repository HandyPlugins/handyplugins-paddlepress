<?php
/**
 * Utils
 *
 * @package PaddlePress
 */

namespace PaddlePress\Utils;

use const PaddlePress\Constants\LICENSE_KEY_OPTION;
use const PaddlePress\Constants\MAPPING_OPTION;
use const PaddlePress\Constants\SETTING_OPTION;

/**
 * Get plugin settings
 *
 * @return mixed|void
 */
function get_settings() {
	$settings = get_option(
		SETTING_OPTION,
		[
			'plugin_version'                    => PADDLEPRESS_VERSION,
			'paddle_vendor_id'                  => '',
			'paddle_auth_code'                  => '',
			'paddle_public_key'                 => '',
			'refund_membership_cancellation'    => '1',
			'self_service_plan_change'          => '1',
			'skip_account_creation_on_mismatch' => true,
			'enable_software_licensing'         => false,
			'ignore_local_host_url'             => true,
			'restriction_message'               => esc_html__( 'You cannot see this content.', 'paddlepress' ),
		]
	);

	return $settings;
}

/**
 * Generate an unique license key
 *
 * @param null $key prefix for unique id
 *
 * @return string
 */
function generate_license_key( $key = null ) {
	if ( null === $key ) {
		$key = wp_rand( 1, PHP_INT_MAX );
	}

	$hash    = wp_hash( sha1( $key . microtime() ) . uniqid( 'license_', true ) );
	$license = substr( $hash, 0, 8 ) . '-' . substr( $hash, 8, 4 ) . '-' . substr( $hash, 12, 4 ) . '-' . substr( $hash, 16, 4 ) . '-' . substr( $hash, 20 );

	return $license;
}


/**
 * Get license key
 *
 * @return mixed|void
 * @since 1.0
 */
function get_license_key() {
	if ( defined( 'PADDLEPRESS_LICENSE_KEY' ) && PADDLEPRESS_LICENSE_KEY ) {
		return PADDLEPRESS_LICENSE_KEY;
	}

	return apply_filters( 'paddlepress_license_key', get_option( LICENSE_KEY_OPTION ) );
}

/**
 * Get paddle product mapping
 *
 * @return mixed|void
 */
function get_paddle_product_mapping() {
	return get_option( MAPPING_OPTION, [] );
}

/**
 * Download directory for PP download items
 *
 * @return mixed|void
 */
function paddlepress_downloads_dir() {
	$upload_dir      = wp_get_upload_dir();
	$basedir         = $upload_dir['basedir'];
	$paddlepress_dir = trailingslashit( $basedir ) . 'paddlepress-downloads/';

	return $paddlepress_dir;
}
