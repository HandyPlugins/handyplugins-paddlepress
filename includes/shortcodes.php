<?php
/**
 * Shortcodes
 *
 * @package PaddlePress
 */

namespace PaddlePress\Shortcodes;

/**
 * Default setup routine
 *
 * @return void
 */
function setup() {
	$n = function ( $function ) {
		return __NAMESPACE__ . "\\$function";
	};

	add_shortcode( 'paddlepress', $n( 'paddlepress_shortcode' ) );
}

/**
 * Adds `[paddlepress...]` shortcode
 *
 * @param array $atts Shortcode attributes
 *
 * @return string
 */
function paddlepress_shortcode( $atts ) {
	$settings = \PaddlePress\Utils\get_settings();

	$atts = wp_parse_args(
		$atts,
		[
			'label'      => esc_html__( 'Buy Now!', 'handyplugins-paddlepress' ),
			'product_id' => 0,
		]
	);

	$passthrough = '';
	if ( is_user_logged_in() ) {
		$passthrough = 'data-passthrough="' . absint( get_current_user_id() ) . '"';
	}

	$email = '';
	if ( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		$email        = 'data-email="' . esc_attr( $current_user->user_email ) . '"';
	}

	$data_success     = '';
	$data_success_url = '';

	// first global setting
	if ( ! empty( $settings['redirect_on_success'] ) ) {
		$data_success_url = esc_url( $settings['redirect_on_success'] );
	}

	// shortcode atts can override the global setting
	if ( ! empty( $atts['data-success'] ) ) {
		$data_success_url = esc_url( $atts['data-success'] );
	}

	if ( ! empty( $data_success_url ) ) {
		$data_success = 'data-success="' . esc_url( $data_success_url ) . '"';
	}

	return sprintf(
		'<a href="#!" class="paddle_button paddlepress-button" %s %s %s data-product="%d">%s</a>',
		$passthrough,
		$email,
		$data_success,
		absint( $atts['product_id'] ),
		esc_attr( $atts['label'] )
	);
}
