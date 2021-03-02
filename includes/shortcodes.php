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

	return sprintf(
		'<a href="#!" class="paddle_button paddlepress-button" %s %s data-product="%d">%s</a>',
		$passthrough,
		$email,
		absint( $atts['product_id'] ),
		esc_attr( $atts['label'] )
	);
}
