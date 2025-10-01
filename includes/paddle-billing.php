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
 * Get Paddle Billing Prices (pagination-safe).
 *
 * Uses a 15-minute transient cache. Paginates over the Prices API and
 * merges all `data` items into the first response structure so that
 * meta shape is preserved.
 *
 * @return array|null Full response array (with merged `data`) or null on failure.
 */
function get_prices() {
	$settings  = Utils\get_settings();
	$cache_key = 'paddlepress_billing_prices';

	if ( ! empty( $settings['is_sandbox'] ) ) {
		$cache_key .= '_sandbox';
	}

	// Re-use cache if available. get_transient() returns false when not set.
	$cached = get_transient( $cache_key );
	if ( false !== $cached ) {
		return $cached;
	}

	$all_data       = array();
	$first_response = null;
	$after          = null;
	$has_more       = true;

	/**
	 * Filter the per-page value used for the Paddle Billing request.
	 *
	 * @since 1.0.0
	 *
	 * @param int $per_page Default 100.
	 */
	$per_page = (int) apply_filters( 'paddlepress_billing_prices_per_page', 100 );

	while ( $has_more ) {
		$body = array(
			'per_page' => $per_page,
		);

		if ( null !== $after && '' !== $after ) {
			$body['after'] = $after;
		}

		$request = api_request(
			'prices',
			array(
				'method' => 'GET',
				'body'   => $body,
			)
		);

		// If the request errored out...
		if ( is_wp_error( $request ) ) {
			// ...and it's the first page, preserve the original bail behavior.
			if ( null === $first_response ) {
				return null;
			}

			// Otherwise, break out and return what we have so far.
			break;
		}

		$response = wp_remote_retrieve_body( $request );

		// If first page fails, keep original behavior (bail).
		if ( ! $response ) {
			return null;
		}

		$json = json_decode( $response, true );

		// Guard against JSON errors.
		if ( null === $json && JSON_ERROR_NONE !== json_last_error() ) {
			// On first page, bail to preserve behavior.
			if ( null === $first_response ) {
				return null;
			}

			// Subsequent pages: stop paginating and return what we have.
			break;
		}

		// Capture the very first response to preserve structure/meta.
		if ( null === $first_response ) {
			$first_response = is_array( $json ) ? $json : array();
		}

		// Accumulate data arrays.
		if ( isset( $json['data'] ) && is_array( $json['data'] ) ) {
			$all_data = array_merge( $all_data, $json['data'] );
		}

		// Pagination handling.
		$has_more = ! empty( $json['meta']['pagination']['has_more'] );

		if ( $has_more && ! empty( $json['data'] ) && is_array( $json['data'] ) ) {
			$last_item = end( $json['data'] );
			$after     = ( isset( $last_item['id'] ) && '' !== $last_item['id'] ) ? $last_item['id'] : null;

			// Safety: stop if we can't determine next cursor.
			if ( null === $after ) {
				$has_more = false;
			}
		} else {
			$has_more = false;
		}
	}

	// Merge results back into the first response structure.
	if ( ! is_array( $first_response ) ) {
		$first_response = array();
	}

	$first_response['data'] = $all_data;

	// Keep existing meta shape; just mark has_more=false and set per_page to what we used.
	if ( ! isset( $first_response['meta'] ) || ! is_array( $first_response['meta'] ) ) {
		$first_response['meta'] = array();
	}
	if ( ! isset( $first_response['meta']['pagination'] ) || ! is_array( $first_response['meta']['pagination'] ) ) {
		$first_response['meta']['pagination'] = array();
	}

	$first_response['meta']['pagination']['has_more'] = false;
	$first_response['meta']['pagination']['per_page'] = $per_page;

	// Cache for 15 minutes.
	set_transient( $cache_key, $first_response, MINUTE_IN_SECONDS * 15 );

	return $first_response;
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

