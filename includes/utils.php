<?php
/**
 * Utils
 *
 * @package PaddlePress
 */

namespace PaddlePress\Utils;

use PaddlePress\Encryption;
use const PaddlePress\Constants\LICENSE_KEY_OPTION;
use const PaddlePress\Constants\MAPPING_OPTION;
use const PaddlePress\Constants\SETTING_OPTION;

/**
 * Get plugin settings
 *
 * @return mixed|void
 */
function get_settings() {
	$defaults = [
		'plugin_version'                        => PADDLEPRESS_VERSION,
		'paddle_vendor_id'                      => '',
		'paddle_auth_code'                      => '',
		'paddle_public_key'                     => '',
		'webhook_secret'                        => '',
		'sandbox_webhook_secret'                => '',
		'is_sandbox'                            => false,
		'sandbox_paddle_vendor_id'              => '',
		'sandbox_paddle_auth_code'              => '',
		'sandbox_paddle_public_key'             => '',
		'refund_membership_cancellation'        => '1',
		'self_service_plan_change'              => '1',
		'self_service_plan_change_redirection'  => '',
		'redirect_on_cancellation'              => '',
		'redirect_on_payment_update'            => '',
		'redirect_on_success'                   => '',
		'skip_account_creation_on_mismatch'     => true,
		'customize_new_user_notification_email' => false,
		'enable_software_licensing'             => false,
		'allow_staging_domains'                 => false,
		'ignore_local_host_url'                 => true,
		'restriction_message'                   => esc_html__( 'You cannot see this content.', 'handyplugins-paddlepress' ),
		'enable_logging'                        => false,
		'max_log_count'                         => 20,
		'paddle_event_callback'                 => '',
		'defer_paddle_scripts'                  => false,
		'enable_paddle_billing'                 => false,
		'enable_paddle_classic'                 => false,
		'enable_profitwell'                     => false,
		'profitwell_public_api_token'           => '',
		'paddle_billing_client_token'           => '',
		'sandbox_paddle_billing_client_token'   => '',
		'restrict_wp_admin_for_customers'       => false,
		'self_service_plan_cancel'              => true,
		'self_service_plan_pause'               => true,
		'enable_invoices'                       => true,
	];

	$settings = get_option( SETTING_OPTION, [] );

	$settings = wp_parse_args( $settings, $defaults );

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

/**
 * Mask given string
 *
 * @param string $input_string  String
 * @param int    $unmask_length The length of unmask
 *
 * @return string
 */
function mask_string( $input_string, $unmask_length ) {
	$output_string = substr( $input_string, 0, $unmask_length );

	if ( strlen( $input_string ) > $unmask_length ) {
		$output_string .= str_repeat( '*', strlen( $input_string ) - $unmask_length );
	}

	return $output_string;
}


/**
 * Get sensitive data in decrypted form
 *
 * @param string $field field name
 *
 * @return bool|mixed|string
 */
function get_decrypted_setting( $field ) {
	$settings = \PaddlePress\Utils\get_settings();
	$value    = isset( $settings[ $field ] ) ? $settings[ $field ] : '';

	// decrypt the value
	$encryption      = new Encryption();
	$decrypted_value = $encryption->decrypt( $value );
	if ( false !== $decrypted_value ) {
		return $decrypted_value;
	}

	return $value;
}

/**
 * Whether paddle classic is enabled
 *
 * @return mixed|true
 */
function is_paddle_classic_enabled() {
	$settings = \PaddlePress\Utils\get_settings();

	if ( ! $settings['enable_paddle_billing'] ) {
		return true; // if billing is not enabled, we should use classic
	}

	return $settings['enable_paddle_classic'];
}

/**
 * Whether paddle billing is enabled
 *
 * @return mixed
 */
function is_paddle_billing_enabled() {
	$settings = \PaddlePress\Utils\get_settings();

	return $settings['enable_paddle_billing'];
}


/**
 * Get paddle js url
 *
 * @return string
 */
function get_paddle_js_url() {
	$paddle_js_url = 'https://cdn.paddle.com/paddle/paddle.js';
	if ( is_paddle_billing_enabled() ) {
		$paddle_js_url = 'https://cdn.paddle.com/paddle/v2/paddle.js';
	}

	return (string) apply_filters( 'paddlepress_paddle_js_url', $paddle_js_url );
}

/**
 * Check if the given value is masked
 *
 * @param string $value       The value to check
 * @param int    $mask_length The length of the mask
 *
 * @return bool
 */
function is_masked_value( $value, $mask_length = 3 ) {
	// Get the last characters of the string
	$last_chars = substr( $value, - $mask_length );

	// Check if the last characters are asterisks
	return str_repeat( '*', $mask_length ) === $last_chars;
}
