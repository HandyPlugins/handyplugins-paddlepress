<?php
/**
 * Paddle Billing related functionalities
 *
 * @since   2.0
 * @package PaddlePress
 */

namespace PaddlePress\PaddleBilling;

use PaddlePress\Utils;
use \WP_Error as WP_Error;
use function PaddlePress\Utils\get_decrypted_setting;

/**
 * Get Paddle Billing Prices
 *
 * @return mixed|void|null
 */
function get_prices() {
	$settings  = Utils\get_settings();
	$cache_key = 'paddlepress_billing_prices';

	if ( $settings['is_sandbox'] ) {
		$cache_key .= '_sandbox';
	}

	$prices = get_transient( $cache_key );
	if ( $prices ) {
		return $prices;
	}

	$request  = api_request(
		'prices',
		[
			'method' => 'GET',
			'body'   => [ 'per_page' => 100 ],
		]
	);
	$response = wp_remote_retrieve_body( $request );

	if ( $response ) {
		$prices = json_decode( $response, true );
		set_transient( $cache_key, $prices, MINUTE_IN_SECONDS * 15 ); // ttl 15 mins

		return $prices;
	}
}

/**
 * Make API call to Paddle
 *
 * @param string $path path of the endpoint
 * @param array  $args request parameters
 *
 * @return array|WP_Error The response of the request
 * @since 2.0
 */
function api_request( $path, $args = [] ) {
	$settings = Utils\get_settings();

	$vendor_auth_code = get_decrypted_setting( 'paddle_auth_code' );
	$base_url         = 'https://api.paddle.com/';

	if ( $settings['is_sandbox'] ) {
		$base_url         = 'https://sandbox-api.paddle.com/';
		$vendor_auth_code = get_decrypted_setting( 'sandbox_paddle_auth_code' );
	}

	$endpoint = $base_url . $path;
	$args     = array_merge(
		$args,
		[
			'timeout' => 30,
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $vendor_auth_code,
			],
		]
	);

	return wp_remote_request( $endpoint, $args );
}

/**
 * Get Paddle Billing Product Name
 *
 * @param string $product_id Paddle Billing Product ID
 *
 * @return mixed|string
 */
function get_product_name_by_id( $product_id ) {
	$settings  = Utils\get_settings();
	$cache_key = 'paddlepress_billing_product_name_' . $product_id;
	if ( $settings['is_sandbox'] ) {
		$cache_key .= '_sandbox';
	}

	$product = get_transient( $cache_key );
	if ( $product ) {
		return $product;
	}

	// check product name in get_billing_products() first and then make an individual request if not found
	$products = get_products();
	if ( isset( $products['response'] ) && ( $products['response']['products'] ) ) {
		$products = $products['response']['products'];
		foreach ( $products as $product ) {
			if ( $product['id'] === $product_id ) {
				set_transient( $cache_key, $product['name'], MINUTE_IN_SECONDS * 15 ); // ttl 15 mins

				return $product['name'];
			}
		}
	}

	// make an individual request
	$endpoint = 'products/' . $product_id;
	$request  = api_request( $endpoint, [ 'method' => 'GET' ] );
	$response = wp_remote_retrieve_body( $request );

	if ( $response ) {
		$product = json_decode( $response, true );
		set_transient( $cache_key, $product['data']['name'], MINUTE_IN_SECONDS * 15 ); // ttl 15 mins

		return $product['data']['name'];
	}

	return '';
}

/**
 * Get Paddle Billing Products
 *
 * @return mixed|void|null
 */
function get_products() {
	$settings  = Utils\get_settings();
	$cache_key = 'paddlepress_billing_products';

	if ( $settings['is_sandbox'] ) {
		$cache_key .= '_sandbox';
	}

	$products = get_transient( $cache_key );
	if ( $products ) {
		return $products;
	}

	$request  = api_request( 'products', [ 'method' => 'GET' ] );
	$response = wp_remote_retrieve_body( $request );

	if ( $response ) {
		$products = json_decode( $response, true );
		set_transient( $cache_key, $products, MINUTE_IN_SECONDS * 15 ); // ttl 15 mins

		return $products;
	}
}

