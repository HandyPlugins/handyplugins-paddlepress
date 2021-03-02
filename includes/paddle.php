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
 * Make API call to Paddle
 *
 * @param string $endpoint path of the endpoint
 * @param array  $args     request parameters
 *
 * @return array|WP_Error The response of the request
 * @since 1.1
 */
function api_request( $endpoint, $args ) {
	$settings         = Utils\get_settings();
	$vendor_id        = $settings['paddle_vendor_id'];
	$vendor_auth_code = $settings['paddle_auth_code'];

	$base_url = 'https://vendors.paddle.com/api/2.0/';

	if ( $settings['is_sandbox'] ) {
		$base_url         = 'https://sandbox-vendors.paddle.com/api/2.0/';
		$vendor_id        = $settings['sandbox_paddle_vendor_id'];
		$vendor_auth_code = $settings['sandbox_paddle_auth_code'];
	}

	$existing_body = isset( $args['body'] ) ? (array) $args['body'] : [];

	// add auth parameters
	$default_body = [
		'vendor_id'        => $vendor_id,
		'vendor_auth_code' => $vendor_auth_code,
	];

	$args['body'] = array_merge( $existing_body, $default_body );

	$url = $base_url . $endpoint;

	return wp_remote_post( $url, $args );
}

/**
 * Get products from Paddle
 *
 * @return mixed
 * @since 1.0
 */
function get_products() {
	$settings  = Utils\get_settings();
	$cache_key = 'paddlepress_paddle_products';

	if ( $settings['is_sandbox'] ) {
		$cache_key .= '_sandbox';
	}

	$products = get_transient( $cache_key );
	if ( $products ) {
		return $products;
	}

	$request  = api_request( 'product/get_products', [] );
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
	$settings  = Utils\get_settings();
	$cache_key = 'paddlepress_paddle_subscriptions';

	if ( $settings['is_sandbox'] ) {
		$cache_key .= '_sandbox';
	}

	$products = get_transient( $cache_key );
	if ( $products ) {
		return $products;
	}

	$request  = api_request( 'subscription/plans', [] );
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
	// dont make live request on dev mode
	if ( defined( 'PADDLEPRESS_TEST' ) && true === PADDLEPRESS_TEST ) {
		return true;
	}

	$args = [
		'body' => [
			'subscription_id' => $subscription_id,
			'quantity'        => 1,
			'plan_id'         => $plan_id,
		],
	];

	$args['headers'] = [
		'Content-Type' => 'application/x-www-form-urlencoded',
	];

	$request_args = apply_filters( 'paddlepress_change_plan_args', $args );

	$request  = api_request( 'subscription/users/update', $request_args );
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
		'active'    => esc_html__( 'Active', 'handyplugins-paddlepress' ),
		'trialing'  => esc_html__( 'Trial', 'handyplugins-paddlepress' ),
		'past_due'  => esc_html__( 'Past Due', 'handyplugins-paddlepress' ),
		'paused'    => esc_html__( 'Paused', 'handyplugins-paddlepress' ),
		'cancelled' => esc_html__( 'Cancelled', 'handyplugins-paddlepress' ), // deleted in API response
	];

	return apply_filters( 'paddlepress_payment_statuses', $statuses );
}

/**
 * Prep a purchase url for given paddle product
 *
 * @param int $product_id Paddle product or plan id
 *
 * @return string buy url
 * @since 1.1
 */
function purchase_url( $product_id ) {
	$settings = Utils\get_settings();

	$url = sprintf( 'https://buy.paddle.com/product/%d', absint( $product_id ) );
	if ( $settings['is_sandbox'] ) {
		$url = sprintf( 'https://sandbox-buy.paddle.com/product/%d', absint( $product_id ) );
	}

	return $url;
}
