<?php
/**
 * Paddle related functionalities
 *
 * @package PaddlePress
 */

namespace PaddlePress\Paddle;

use PaddlePress\Utils;
use \WP_Error as WP_Error;

/**
 * Get products from Paddle
 *
 * @return mixed
 * @since 1.0
 */
function get_products() {
	$endpoint = 'https://vendors.paddle.com/api/2.0/product/get_products';

	$cache_key = 'paddlepress_paddle_products';
	$products  = get_transient( $cache_key );
	if ( $products ) {
		return $products;
	}

	$settings = Utils\get_settings();

	$request_args = [
		'body' => [
			'vendor_id'        => $settings['paddle_vendor_id'],
			'vendor_auth_code' => $settings['paddle_auth_code'],
		],
	];

	$request  = wp_remote_post( $endpoint, $request_args );
	$response = wp_remote_retrieve_body( $request );
	if ( $response ) {
		$products = json_decode( $response, true );
		set_transient( $cache_key, $products, MINUTE_IN_SECONDS * 15 ); // ttl 15 mins

		return $products;
	}
}

/**
 * Get subscription plans
 *
 * @return mixed
 * @since 1.0
 */
function get_subscription_plans() {
	$endpoint = 'https://vendors.paddle.com/api/2.0/subscription/plans';

	$cache_key = 'paddlepress_paddle_subscriptions';
	$products  = get_transient( $cache_key );
	if ( $products ) {
		return $products;
	}

	$settings = Utils\get_settings();

	$request_args = [
		'body' => [
			'vendor_id'        => $settings['paddle_vendor_id'],
			'vendor_auth_code' => $settings['paddle_auth_code'],
		],
	];

	$request  = wp_remote_post( $endpoint, $request_args );
	$response = wp_remote_retrieve_body( $request );
	if ( $response ) {
		$products = json_decode( $response, true );
		set_transient( $cache_key, $products, MINUTE_IN_SECONDS * 15 ); // ttl 15 mins

		return $products;
	}
}

/**
 * Upgrade/Downgrade a paddle subscription
 *
 * @param int $subscription_id Paddle subscription id
 * @param int $plan_id         Paddle plan id
 *
 * @return bool|mixed API response
 * @since 1.0
 */
function change_plan( $subscription_id, $plan_id ) {
	if ( defined( 'PADDLEPRESS_TEST' ) && true === PADDLEPRESS_TEST ) {
		return true;
	}

	$endpoint = 'https://vendors.paddle.com/api/2.0/subscription/users/update';
	$settings = Utils\get_settings();

	$args = [
		'vendor_id'        => $settings['paddle_vendor_id'],
		'vendor_auth_code' => $settings['paddle_auth_code'],
		'subscription_id'  => $subscription_id,
		'quantity'         => 1,
		'plan_id'          => $plan_id,
	];

	$request_args = apply_filters( 'paddlepress_change_plan_args', $args );

	$request  = wp_remote_post( $endpoint, $request_args );
	$response = wp_remote_retrieve_body( $request );

	if ( $response ) {
		return json_decode( $response, true );
	}

	return false;
}


/**
 * Get product id => name pair
 *
 * @return array
 * @since 1.0
 */
function get_paddle_product_names() {
	$all_products = [];
	$paddle_plans = get_subscription_plans();

	if ( $paddle_plans && ! empty( $paddle_plans['response'] ) ) {
		$plans = $paddle_plans['response'];
		foreach ( $plans as $plan ) {
			$all_products[ $plan['id'] ] = $plan['name'];
		}
	}

	$paddle_products = get_products();
	if ( isset( $paddle_products['response'] ) && ( $paddle_products['response']['products'] ) ) {
		$products = $paddle_products['response']['products'];
		foreach ( $products as $product ) {
			$all_products[ $product['id'] ] = $product['name'];
		}
	}

	return $all_products;
}

/**
 * Get payment statues
 * deleted -> cancelled
 *
 * @return mixed|void supported statues
 */
function payment_statuses() {
	$statuses = [
		'active'    => esc_html__( 'Active', 'paddlepress' ),
		'trialing'  => esc_html__( 'Trial', 'paddlepress' ),
		'past_due'  => esc_html__( 'Past Due', 'paddlepress' ),
		'paused'    => esc_html__( 'Paused', 'paddlepress' ),
		'cancelled' => esc_html__( 'Cancelled', 'paddlepress' ), // deleted in API response
	];

	return apply_filters( 'paddlepress_payment_statuses', $statuses );
}

/**
 * Verify paddle request with signature
 *
 * @return bool|WP_Error
 * @link  https://developer.paddle.com/webhook-reference/verifying-webhooks
 * @since 1.0
 */
function verifiy_paddle_signature() {
	// return true for the local development
	if ( defined( 'PADDLEPRESS_TEST' ) && true === PADDLEPRESS_TEST ) {
		return true;
	}

	$signature = base64_decode( $_POST['p_signature'] ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
	$fields    = $_POST;
	unset( $fields['p_signature'] );

	// ksort() and serialize the fields
	ksort( $fields );
	foreach ( $fields as $k => $v ) {
		if ( ! in_array( gettype( $v ), array( 'object', 'array' ), true ) ) {
			$fields[ $k ] = "$v";
		}
	}

	$data       = serialize( $fields ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
	$settings   = Utils\get_settings();
	$public_key = ( isset( $settings['paddle_public_key'] ) ? $settings['paddle_public_key'] : '' );

	// Verify the signature
	$verification = openssl_verify( $data, $signature, $public_key, OPENSSL_ALGO_SHA1 );

	if ( 1 === $verification ) {
		return true;
	}

	return new WP_Error( 'signature_err', esc_html__( 'Problem with request', 'paddlepress' ) );
}
