<?php
/**
 * Shortcodes
 *
 * @package PaddlePress
 */

namespace PaddlePress\Shortcodes;

use const PaddlePress\Constants\BILLING_ATTRIBUTES;
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
	add_shortcode( 'paddlepress_billing', $n( 'paddlepress_billing_shortcode' ) );
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

	$class = 'paddle_button paddlepress-button paddlepress-classic-button';

	if ( ! empty( $atts['class'] ) ) {
		$class .= ' ' . esc_attr( $atts['class'] );
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
			'<a href="#!" class="%s" %s %s %s %s data-product="%d">%s</a>',
			$class,
			$passthrough,
			$email,
			$data_success,
			$additional_attributes,
			absint( $atts['product_id'] ),
			esc_attr( $atts['label'] )
		)
	);
}

/**
 * Adds `[paddlepress_billing...]` shortcode
 *
 * @param array $atts Shortcode attributes
 *
 * @return mixed|null
 * @since 2.0
 */
function paddlepress_billing_shortcode( $atts ) {
	$settings           = \PaddlePress\Utils\get_settings();
	$allowed_attributes = BILLING_ATTRIBUTES;
	$data_items_str     = '';

	$atts = wp_parse_args(
		$atts,
		[
			'label'    => esc_html__( 'Buy Now!', 'paddlepress' ),
			'price_id' => '',
		]
	);

	if ( ! empty( $atts['price_id'] ) ) {
		$data_items     = wp_json_encode(
			[
				[
					'priceId'  => $atts['price_id'],
					'quantity' => 1,
				],
			]
		);
		$data_items_str = "data-items='" . $data_items . "'";
	}

	$email = '';
	if ( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		$email        = 'data-customer-email="' . esc_attr( $current_user->user_email ) . '"';
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
		$data_success = 'data-success-url="' . esc_url( $data_success_url ) . '"';
	}

	$additional_attributes = '';

	foreach ( $allowed_attributes as $attribute ) {
		if ( isset( $atts[ $attribute ] ) ) {
			$additional_attributes .= sprintf( ' %s="%s" ', $attribute, esc_attr( $atts[ $attribute ] ) );
		}
	}

	$class = 'paddle_button paddlepress-button paddlepress-billing-button';

	if ( ! empty( $atts['class'] ) ) {
		$class .= ' ' . esc_attr( $atts['class'] );
	}

	/**
	 * Filters shortcode output
	 *
	 * @hook  paddlepress_button_shortcode
	 * @since 1.6
	 */
	return apply_filters(
		'paddlepress_button_shortcode',
		sprintf(
			'<a href="#!" class="%s" %s %s %s %s >%s</a>',
			$class,
			$email,
			$data_success,
			$data_items_str,
			$additional_attributes,
			esc_attr( $atts['label'] )
		)
	);
}
