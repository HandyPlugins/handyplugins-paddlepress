<?php
/**
 * Shortcodes
 *
 * @package PaddlePress
 */

namespace PaddlePress\Shortcodes;

use const PaddlePress\Constants\CHECKOUT_ATTRIBUTES;

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
	$settings           = \PaddlePress\Utils\get_settings();
	$allowed_attributes = CHECKOUT_ATTRIBUTES;

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

	$additional_attributes = '';

	foreach ( $allowed_attributes as $attribute ) {
		if ( isset( $atts[ $attribute ] ) ) {
			$additional_attributes .= sprintf( ' %s="%s" ', $attribute, esc_attr( $atts[ $attribute ] ) );
		}
	}

	/**
	 * Filters shortcode output
	 *
	 * @hook  paddlepress_button_shortcode
	 * @since 1.5
	 */
	return apply_filters(
		'paddlepress_button_shortcode',
		sprintf(
			'<a href="#!" class="paddle_button paddlepress-button" %s %s %s %s data-product="%d">%s</a>',
			$passthrough,
			$email,
			$data_success,
			$additional_attributes,
			absint( $atts['product_id'] ),
			esc_attr( $atts['label'] )
		)
	);
}
