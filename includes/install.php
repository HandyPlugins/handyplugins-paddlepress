<?php
/**
 * Installation
 *
 * @package PaddlePress
 */

namespace PaddlePress\Install;

use PaddlePress\Encryption;
use const PaddlePress\Constants\DB_VERSION_OPTION;
use const PaddlePress\Constants\SETTING_OPTION;

/**
 * Default setup routine
 *
 * @return void
 */
function setup() {
	add_action( 'init', __NAMESPACE__ . '\\install', 5 );
}

/**
 * Run schema installation routine
 */
function install() {
	if ( ! is_blog_installed() ) {
		return;
	}

	// Check if we are not already running
	if ( 'yes' === get_transient( 'handyplugins_paddlepress_installing' ) ) {
		return;
	}

	// lets set the transient now.
	set_transient( 'handyplugins_paddlepress_installing', 'yes', MINUTE_IN_SECONDS );

	if ( version_compare( get_option( DB_VERSION_OPTION ), PADDLEPRESS_DB_VERSION, '<' ) ) {
		maybe_upgrade_20();
		update_option( DB_VERSION_OPTION, PADDLEPRESS_DB_VERSION );
		do_action( 'paddlepress_db_upgraded' );
	}

	delete_transient( 'handyplugins_paddlepress_installing' );
}


/**
 * Upgrade routine for 2.0
 *
 * @return void
 */
function maybe_upgrade_20() {
	$current_version = get_option( DB_VERSION_OPTION );

	if ( version_compare( $current_version, '2.0', '<' ) ) {
		$settings   = \PaddlePress\Utils\get_settings();
		$encryption = new Encryption();

		if ( ! empty( $settings['paddle_auth_code'] ) && false === $encryption->decrypt( $settings['paddle_auth_code'] ) ) {
			$settings['paddle_auth_code'] = $encryption->encrypt( $settings['paddle_auth_code'] );
		}

		if ( ! empty( $settings['sandbox_paddle_auth_code'] ) && false === $encryption->decrypt( $settings['sandbox_paddle_auth_code'] ) ) {
			$settings['sandbox_paddle_auth_code'] = $encryption->encrypt( $settings['sandbox_paddle_auth_code'] );
		}

		update_option( SETTING_OPTION, $settings, false );
	}
}
