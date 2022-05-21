<?php
/**
 * Core plugin functionality.
 *
 * @package PaddlePress
 */

namespace PaddlePress\Core;

use PaddlePress\Utils;
use \WP_Error as WP_Error;

/**
 * Default setup routine
 *
 * @return void
 */
function setup() {
	$n = function ( $function ) {
		return __NAMESPACE__ . "\\$function";
	};

	add_action( 'init', $n( 'i18n' ) );
	add_action( 'init', $n( 'init' ) );
	add_action( 'wp_enqueue_scripts', $n( 'scripts' ) );
	add_action( 'admin_enqueue_scripts', $n( 'admin_styles' ) );
	add_action( 'admin_enqueue_scripts', $n( 'admin_scripts' ) );
	do_action( 'paddlepress_loaded' );
}

/**
 * Registers the default textdomain.
 *
 * @return void
 */
function i18n() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'handyplugins-paddlepress' );
	load_textdomain( 'handyplugins-paddlepress', WP_LANG_DIR . '/paddlepress/paddlepress-' . $locale . '.mo' );
	load_plugin_textdomain( 'handyplugins-paddlepress', false, plugin_basename( PADDLEPRESS_PATH ) . '/languages/' );
}

/**
 * Initializes the plugin and fires an action other plugins can hook into.
 *
 * @return void
 */
function init() {
	do_action( 'paddlepress_init' );
}

/**
 * The list of knows contexts for enqueuing scripts/styles.
 *
 * @return array
 */
function get_enqueue_contexts() {
	return [ 'admin', 'frontend', 'shared' ];
}

/**
 * Generate an URL to a script, taking into account whether SCRIPT_DEBUG is enabled.
 *
 * @param string $script  Script file name (no .js extension)
 * @param string $context Context for the script ('admin', 'frontend', or 'shared')
 *
 * @return string|WP_Error URL
 */
function script_url( $script, $context ) {

	if ( ! in_array( $context, get_enqueue_contexts(), true ) ) {
		return new WP_Error( 'invalid_enqueue_context', 'Invalid $context specified in PaddlePressscript loader.' );
	}

	return PADDLEPRESS_URL . "dist/js/${script}.js";

}

/**
 * Generate an URL to a stylesheet, taking into account whether SCRIPT_DEBUG is enabled.
 *
 * @param string $stylesheet Stylesheet file name (no .css extension)
 * @param string $context    Context for the script ('admin', 'frontend', or 'shared')
 *
 * @return string URL
 */
function style_url( $stylesheet, $context ) {

	if ( ! in_array( $context, get_enqueue_contexts(), true ) ) {
		return new WP_Error( 'invalid_enqueue_context', 'Invalid $context specified in PaddlePressstylesheet loader.' );
	}

	return PADDLEPRESS_URL . "dist/css/${stylesheet}.css";

}

/**
 * Enqueue scripts for front-end.
 *
 * @return void
 */
function scripts() {
	$settings = Utils\get_settings();

	if ( empty( $settings['paddle_vendor_id'] ) && empty( $settings['sandbox_paddle_vendor_id'] ) ) {
		return;
	}

	$event_callback = str_replace( 'eventCallback:', '', $settings['paddle_event_callback'] );
	$event_callback = wp_unslash( $event_callback );

	$paddle_script_data  = '{' . PHP_EOL;
	$paddle_script_data .= 'vendor: ' . esc_attr( $settings['paddle_vendor_id'] ) . ( $event_callback ? ',' : '' ) . PHP_EOL;
	if ( $event_callback ) {
		$paddle_script_data .= 'eventCallback: ' . $event_callback . PHP_EOL;
	}

	$paddle_script_data .= '}';

	$paddle_script = 'Paddle.Setup(' . $paddle_script_data . ');';

	if ( $settings['is_sandbox'] ) {
		$paddle_script = "Paddle.Environment.set('sandbox');" . PHP_EOL;

		$paddle_sandbox_script_data  = '{' . PHP_EOL;
		$paddle_sandbox_script_data .= 'vendor: ' . esc_attr( $settings['sandbox_paddle_vendor_id'] ) . ( $event_callback ? ',' : '' ) . PHP_EOL;
		if ( $event_callback ) {
			$paddle_sandbox_script_data .= 'eventCallback: ' . $event_callback . PHP_EOL;
		}

		$paddle_sandbox_script_data .= '}';

		$paddle_script .= 'Paddle.Setup(' . $paddle_sandbox_script_data . ');';
	}

	$paddle_script = apply_filters( 'paddlepress_paddle_script', $paddle_script );

	wp_enqueue_script( 'paddlepress-paddle', 'https://cdn.paddle.com/paddle/paddle.js', [], null, true ); // phpcs:ignore
	wp_add_inline_script( 'paddlepress-paddle', $paddle_script );
}

/**
 * Enqueue scripts for admin.
 *
 * @return void
 */
function admin_scripts() {
	wp_enqueue_script(
		'paddlepress_admin',
		script_url( 'admin', 'admin' ),
		[ 'jquery', 'jquery-ui-tabs' ],
		PADDLEPRESS_VERSION,
		true
	);

}

/**
 * Enqueue styles for admin.
 *
 * @return void
 */
function admin_styles() {
	wp_enqueue_style(
		'paddlepress_admin',
		style_url( 'admin-style', 'admin' ),
		[],
		PADDLEPRESS_VERSION
	);

}
