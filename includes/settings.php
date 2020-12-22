<?php
/**
 * Settings page
 *
 * @package Padpress
 */

namespace PaddlePress\Settings;

use const PaddlePress\Constants\LICENSE_KEY_OPTION;
use const PaddlePress\Constants\SETTING_OPTION;
use PaddlePress\Utils;
use PaddlePress\Paddle;
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

	add_action( 'admin_menu', $n( 'admin_menu' ) );
	// save settings
	add_action( 'admin_init', $n( 'save_settings' ) );
	add_action( 'admin_post_paddlepress_clear_transients', $n( 'purge_cache' ) );

}

/**
 * Add admin pages
 */
function admin_menu() {

	add_menu_page(
		esc_html__( 'PaddlePress', 'paddlepress' ),
		esc_html__( 'PaddlePress', 'paddlepress' ),
		'manage_options',
		'paddlepress',
		__NAMESPACE__ . '\\settings_page',
		'dashicons-money-alt'
	);

	/**
	 * Different name submenu item, url point same address with parent.
	 */
	add_submenu_page(
		'paddlepress',
		esc_html__( 'Settings', 'paddlepress' ),
		esc_html__( 'Settings', 'paddlepress' ),
		'manage_options',
		'paddlepress'
	);

	add_submenu_page(
		'paddlepress',
		esc_html__( 'Products', 'paddlepress' ),
		esc_html__( 'Products', 'paddlepress' ),
		'manage_options',
		'paddlepress-products',
		__NAMESPACE__ . '\\products_page'
	);

	add_submenu_page(
		'paddlepress',
		esc_html__( 'Plans', 'paddlepress' ),
		esc_html__( 'Plans', 'paddlepress' ),
		'manage_options',
		'paddlepress-plans',
		__NAMESPACE__ . '\\plans_page'
	);
}

/**
 * Main settings page of the plugin
 */
function settings_page() {
	$settings    = Utils\get_settings();
	$license_key = Utils\get_license_key();

	?>
	<div class="wrap">
		<?php settings_errors(); ?>
		<h1><?php esc_html_e( 'PaddlePress', 'paddlepress' ); ?></h1>
		<form method="post" action="">
			<?php
			wp_nonce_field( 'paddlepress_settings', 'paddlepress_settings' );
			?>
			<div id="pp-setting-tabs">
				<div class="nav-tab-wrapper">
					<ul>
						<li><a href="#pp-settings-paddle" class="pp-setting-tab nav-tab nav-tab-active"><?php esc_html_e( 'Paddle Details', 'paddlepress' ); ?></a></li>
						<li><a href="#pp-settings-preferences" class="pp-setting-tab nav-tab"><?php esc_html_e( 'Preferences', 'paddlepress' ); ?></a></li>
						<li><a href="#pp-settings-upgrade" class="pp-setting-tab nav-tab"><?php esc_html_e( 'Upgrade', 'paddlepress' ); ?></a></li>
					</ul>
				</div>
				<div id="pp-settings-paddle">
					<table class="form-table">
						<tr>
							<th scope="row"><label for="paddle_vendor_id"><?php esc_html_e( 'Vendor ID', 'paddlepress' ); ?></label></th>
							<td>
								<input type="text" id="paddle_vendor_id" name="paddle_vendor_id" value="<?php echo esc_attr( $settings ? $settings['paddle_vendor_id'] : '' ); ?>">
								<p class="description"><?php esc_html_e( 'Enter your Paddle Vendor ID. It can be found in Developer Tools > Authentication on Paddle dashboard', 'paddlepress' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="paddle_auth_code"><?php esc_html_e( 'Auth Code', 'paddlepress' ); ?></label></th>
							<td>
								<input type="text" size="60" id="paddle_auth_code" name="paddle_auth_code" value="<?php echo esc_attr( $settings ? $settings['paddle_auth_code'] : '' ); ?>">
								<p class="description"><?php esc_html_e( 'Enter your Paddle Auth code. You can create a new one from Developer Tools > Authentication on Paddle dashboard.', 'paddlepress' ); ?></p>
							</td>
						</tr>

						<tr>
							<th scope="row"></th>
							<td>
								<?php
								$url = wp_nonce_url( admin_url( 'admin-post.php?action=paddlepress_clear_transients' ), 'paddlepress_clear_transients' );
								printf(
									'<a href="%s" class="button">%s</a>',
									esc_url_raw( $url ),
									esc_html__( 'Clear Cache', 'paddlepress' )
								)
								?>
								<p class="description"><?php esc_html_e( 'API responses are cached for 15 minutes, if you want to see the products or subscriptions immediately after adding them to paddle, you can purge the cache.', 'paddlepress' ); ?></p>
							</td>
						</tr>

					</table>
				</div>
				<div id="pp-settings-preferences">
					<div class="notice inline notice-error"><p><?php esc_html_e( 'Only available in pro version' ); ?></p></div>
					<fieldset disabled>
						<table class="form-table">
							<tr>
								<th scope="row"><label for="enable_software_licensing"><?php esc_html_e( 'License Server', 'paddlepress' ); ?></label></th>
								<td>
									<label>
										<input type="checkbox" <?php checked( $settings['enable_software_licensing'], 1 ); ?> id="enable_software_licensing" name="enable_software_licensing" value="1">
										<?php esc_html_e( 'Enable license server which allows creating license key for purchase.', 'paddlepress' ); ?>
									</label>
									<p class="description"><?php esc_html_e( 'If you are planning to sell WordPress plugins or themes, activate this option.', 'paddlepress' ); ?></p>
								</td>
							</tr>

							<tr id="ignore_local_host_url_row" style="<?php echo( ! isset( $settings['enable_software_licensing'] ) || ! $settings['enable_software_licensing'] ? 'display:none;' : '' ); ?>">
								<th scope="row"><label for="ignore_local_host_url"><?php esc_html_e( 'Ignore Local Host URLs', 'paddlepress' ); ?></label></th>
								<td>
									<label>
										<input type="checkbox" <?php checked( $settings['ignore_local_host_url'], 1 ); ?> id="ignore_local_host_url" name="ignore_local_host_url" value="1">
										<?php esc_html_e( 'Allow local development domains to be activated without registering the URL.', 'paddlepress' ); ?>
									</label>
									<p class="description"><?php esc_html_e( 'Supported domains: localhost, *.test, *.local', 'paddlepress' ); ?></p>
								</td>
							</tr>

							<tr>
								<th scope="row"><label for="refund_membership_cancellation"><?php esc_html_e( 'Automatic Cancellation', 'paddlepress' ); ?></label></th>
								<td>
									<label>
										<input type="checkbox" <?php checked( $settings['refund_membership_cancellation'], 1 ); ?> id="refund_membership_cancellation" name="refund_membership_cancellation" value="1">
										<?php esc_html_e( 'Cancel membership when full refunded.', 'paddlepress' ); ?>
									</label>
									<p class="description"><?php esc_html_e( 'It will immediately terminate the subscription once the full refund proceeds. One-off purchases are not included.', 'paddlepress' ); ?></p>
								</td>
							</tr>

							<tr>
								<th scope="row"><label for="self_service_plan_change"><?php esc_html_e( 'Upgrade/Downgrade', 'paddlepress' ); ?></label></th>
								<td>
									<label>
										<input type="checkbox" <?php checked( ( isset( $settings['self_service_plan_change'] ) ? $settings['self_service_plan_change'] : 0 ), 1 ); ?> id="self_service_plan_change" name="self_service_plan_change" value="1">
										<?php esc_html_e( 'Enable Upgrade & Downgrade Subscriptions', 'paddlepress' ); ?>
									</label>
									<p class="description"><?php esc_html_e( 'Allow users to upgrade/downgrade their subscriptions on my accounts page.', 'paddlepress' ); ?></p>
								</td>
							</tr>

							<tr>
								<th scope="row"><label for="skip_account_creation_on_mismatch"><?php esc_html_e( 'Skip Account Creation', 'paddlepress' ); ?></label></th>
								<td>
									<label>
										<input type="checkbox" <?php checked( ( isset( $settings['skip_account_creation_on_mismatch'] ) && $settings['skip_account_creation_on_mismatch'] ), 1 ); ?> id="skip_account_creation_on_mismatch" name="skip_account_creation_on_mismatch" value="1">
										<?php esc_html_e( 'Do not create an account when a related product or plan is not mapped to a membership plan.', 'paddlepress' ); ?>
									</label>
									<p class="description"><?php esc_html_e( 'If the purchased product has not mapped into a membership plan, it will skip the account creation or update.', 'paddlepress' ); ?></p>
								</td>
							</tr>

							<tr>
								<th scope="row"><label for="restriction_message"><?php esc_html_e( 'Restriction Message', 'paddlepress' ); ?></label></th>
								<td>
									<input type="text" size="120" id="restriction_message" name="restriction_message" value="<?php echo esc_attr( $settings && isset( $settings['restriction_message'] ) ? $settings['restriction_message'] : '' ); ?>">
									<br />
									<span class="description"><?php esc_html_e( 'Message displayed when a user does not have permission to view content.', 'paddlepress' ); ?></span>
								</td>
							</tr>
						</table>
					</fieldset>

				</div>
				<div id="pp-settings-upgrade">
					<div class="meta-box-sortables ui-sortable">
						<div class="postbox">
							<div class="inside">
								<div id="premium-promote">
									<h2><?php esc_html_e( 'PaddlePress PRO benefits', 'paddlepress' ); ?></h2>
									<hr>
									<ul class="premium-benefits">
										<li>
											<span class="dashicons dashicons-yes"></span>
											<?php
											printf(
												'<strong>%s:</strong> %s',
												esc_html__( 'Customer Dashboard', 'paddlepress' ),
												esc_html__( 'Let your members easily view and manage their account details.', 'paddlepress' )
											);
											?>
										</li>
										<li>
											<span class="dashicons dashicons-yes"></span>
											<?php
											printf(
												'<strong>%s:</strong> %s',
												esc_html__( 'Membership Levels', 'paddlepress' ),
												esc_html__( 'Create an unlimited number of membership packages and map with your Paddle products or plans.', 'paddlepress' )
											);
											?>
										</li>
										<li>
											<span class="dashicons dashicons-yes"></span>
											<?php
											printf(
												'<strong>%s:</strong> %s',
												esc_html__( 'Restrict Contents', 'paddlepress' ),
												esc_html__( 'Restrict your contents to particular membership levels easily.', 'paddlepress' )
											);
											?>
										</li>
										<li>
											<span class="dashicons dashicons-yes"></span>
											<?php
											printf(
												'<strong>%s:</strong> %s',
												esc_html__( 'Downloads', 'paddlepress' ),
												esc_html__( 'Downloadable items are available under the customer’s account page. You can limit access to files based on the plans that customers have.', 'paddlepress' )
											);
											?>
										</li>
										<li>
											<span class="dashicons dashicons-yes"></span>
											<?php
											printf(
												'<strong>%s:</strong> %s',
												esc_html__( 'Website License Management', 'paddlepress' ),
												esc_html__( 'If you decide to sell domain based licensing keys. You can let your users register their domains.', 'paddlepress' )
											);
											?>
										</li>
										<li>
											<span class="dashicons dashicons-yes"></span>
											<?php
											printf(
												'<strong>%s:</strong> %s',
												esc_html__( 'Subscription Upgrades and Downgrades', 'paddlepress' ),
												esc_html__( 'Customers can move between subscription levels and only pay the difference.', 'paddlepress' )
											);
											?>
										</li>
										<li>
											<span class="dashicons dashicons-yes"></span>
											<?php
											printf(
												'<strong>%s:</strong> %s',
												esc_html__( 'Emails', 'paddlepress' ),
												esc_html__( 'Send welcome emails to new members, email payment receipts, and remind members before their account expires automatically.', 'paddlepress' )
											);
											?>
										</li>
										<li>
											<span class="dashicons dashicons-yes"></span>
											<?php
											printf(
												'<strong>%s:</strong> %s',
												esc_html__( 'Premium Support', 'paddlepress' ),
												esc_html__( 'We are providing top-notch premium support to premium users.', 'paddlepress' )
											);
											?>
										</li>
									</ul>

									<a class="pro-upgrade-btn" href="https://handyplugins.co/paddlepress-pro/" target="_blank" rel="noopener">
										<span><?php esc_html_e( 'Buy PaddlePress Pro', 'paddlepress' ); ?></span>
									</a>

								</div>
							</div>
							<!-- .inside -->
						</div>
						<!-- .postbox -->
					</div>
				</div>

			</div>

			<?php
			submit_button( esc_html__( 'Save Changes', 'paddlepress' ), 'submit primary' );
			?>
		</form>
	</div>
	<?php
}

/**
 * Products settings
 */
function products_page() {
	$products          = [];
	$products_response = Paddle\get_products();
	if ( isset( $products_response['response'] ) && ( $products_response['response']['products'] ) ) {
		$products = $products_response['response']['products'];
	}

	?>
	<div class="wrap paddlepress-settings">
		<h1><?php esc_html_e( 'Paddle Products', 'paddlepress' ); ?></h1>
		<?php if ( $products ) : ?>
			<table class="wp-list-table widefat fixed striped posts">
				<thead>
				<tr>
					<th scope="col" id="icon" class="manage-column column-author"><?php esc_html_e( 'Icon', 'paddlepress' ); ?></th>
					<th scope="col" id="id" class="manage-column column-author"><?php esc_html_e( 'ID', 'paddlepress' ); ?></th>
					<th scope="col" id="product-name" class="manage-column column-product-name"><?php esc_html_e( 'Product Name', 'paddlepress' ); ?></th>
					<th scope="col" id="shortcode" class="manage-column column-shortcode"><?php esc_html_e( 'ShortCode', 'paddlepress' ); ?></th>
					<th scope="col" id="price" class="manage-column column-price"><?php esc_html_e( 'Price', 'paddlepress' ); ?></th>
				</tr>
				</thead>

				<tbody id="the-list">
				<?php foreach ( $products as $product ) : ?>
					<tr id="product-1" class="iedit author-self level-0 post-1 type-post status-publish format-standard hentry category-Dummy">
						<td class="icon column-icon" data-colname="icon">
							<?php printf( '<img src="%s" width="50" alt="Product icon"/>', esc_url( $product['icon'] ) ); ?>
						</td>
						<td class="id column-id vertical-align-middle" data-colname="id">
							<?php echo absint( $product['id'] ); ?>
						</td>

						<td class="product-name column-title column-primary page-title vertical-align-middle" data-colname="product-name">
							<strong>
								<a href="<?php echo 'https://buy.paddle.com/product/' . absint( $product['id'] ); ?>"><?php echo esc_attr( $product['name'] ); ?></a>
							</strong>
						</td>

						<td class="shortcode column-shortcode vertical-align-middle" data-colname="Shortcode">
							<code><?php printf( '[paddlepress product_id="%d" label="%s"]', absint( $product['id'] ), esc_html__( 'Buy Now!', 'paddlepress' ) ); ?></code>
						</td>

						<td class="price column-price vertical-align-middle" data-colname="Price">
							<?php if ( empty( $product['base_price'] ) ) : ?>
								<?php esc_html_e( 'Base price is missing', 'paddlepress' ); ?>
							<?php else : ?>
								<?php echo esc_attr( $product['base_price'] ); ?> <strong><?php echo esc_attr( $product['currency'] ); ?></strong>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<div class="notice inline"><p><?php esc_html_e( 'No products found! Please, make sure products have been added to your Paddle account and API credentials are correct.', 'paddlepress' ); ?></p></div>
		<?php endif; ?>
	</div>

	<?php
}

/**
 * Display paddle subscription plans
 */
function plans_page() {

	$plans = Paddle\get_subscription_plans();

	?>
	<div class="wrap paddlepress-settings">
		<h1><?php esc_html_e( 'Paddle Subscription Plans', 'paddlepress' ); ?></h1>
		<?php if ( isset( $plans['response'] ) && $plans['response'] ) : ?>
			<table class="wp-list-table widefat fixed striped posts">
				<thead>
				<tr>
					<th scope="col" id="id" class="manage-column column-author"><?php esc_html_e( 'ID', 'paddlepress' ); ?></th>
					<th scope="col" id="product-name" class="manage-column column-product-name"><?php esc_html_e( 'Product Name', 'paddlepress' ); ?></th>
					<th scope="col" id="shortcode" class="manage-column column-shortcode"><?php esc_html_e( 'ShortCode', 'paddlepress' ); ?></th>
					<th scope="col" id="billing-type" class="column-billing-type"><?php esc_html_e( 'Billing Type', 'paddlepress' ); ?></th>
					<th scope="col" id="initial-price" class="manage-column column-price"><?php esc_html_e( 'Initial Price', 'paddlepress' ); ?></th>
					<th scope="col" id="recurring-price" class="manage-column column-price"><?php esc_html_e( 'Recurring Price', 'paddlepress' ); ?></th>
					<th scope="col" id="trial" class="manage-column column-price"><?php esc_html_e( 'Trial Days', 'paddlepress' ); ?></th>
				</tr>
				</thead>

				<tbody id="the-list">
				<?php foreach ( $plans['response'] as $plan ) : ?>
					<tr id="product-1" class="iedit author-self level-0 post-1 type-post status-publish format-standard hentry category-Dummy">
						<td class="id column-id vertical-align-middle" data-colname="id">
							<?php echo absint( $plan['id'] ); ?>
						</td>

						<td class="product-name column-title column-primary page-title vertical-align-middle" data-colname="product-name">
							<strong>
								<a href="<?php echo 'https://buy.paddle.com/product/' . absint( $plan['id'] ); ?>"><?php echo esc_attr( $plan['name'] ); ?></a>
							</strong>
						</td>

						<td class="shortcode column-shortcode vertical-align-middle" data-colname="Shortcode">
							<code><?php printf( '[paddlepress product_id="%d" label="%s"]', absint( $plan['id'] ), esc_html__( 'Buy Now!', 'paddlepress' ) ); ?></code>
						</td>

						<td class="billing-type vertical-align-middle" data-colname="BillingType">
							<?php echo esc_attr( $plan['billing_type'] ); ?>
						</td>

						<td class="initial-price column-shortcode vertical-align-middle" data-colname="InitialPrice">
							<?php foreach ( $plan['initial_price'] as $currency => $amount ) : ?>
								<?php echo esc_attr( $amount ) . ' - ' . esc_attr( $currency ); ?><br />
							<?php endforeach; ?>
						</td>

						<td class="reccuring-price column-price vertical-align-middle" data-colname="RecurringPrice">
							<?php foreach ( $plan['recurring_price'] as $currency => $amount ) : ?>
								<?php echo esc_attr( $amount ) . ' - ' . esc_attr( $currency ); ?><br />
							<?php endforeach; ?>
						</td>

						<td class="trials column-price vertical-align-middle" data-colname="Trials">
							<?php echo esc_attr( $plan['trial_days'] ); ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<div class="notice inline"><p><?php esc_html_e( 'No subscription plans found! Please, make sure subscription plans have been added to your Paddle account and API credentials are correct.', 'paddlepress' ); ?></p></div>
		<?php endif; ?>
	</div>

	<?php
}

/**
 * Save settings
 */
function save_settings() {
	$settings = [];

	$nonce = filter_input( INPUT_POST, 'paddlepress_settings', FILTER_SANITIZE_STRING );

	if ( wp_verify_nonce( $nonce, 'paddlepress_settings' ) ) {
		$settings['plugin_version']                    = PADDLEPRESS_VERSION;
		$settings['paddle_vendor_id']                  = sanitize_text_field( filter_input( INPUT_POST, 'paddle_vendor_id' ) );
		$settings['paddle_auth_code']                  = sanitize_text_field( filter_input( INPUT_POST, 'paddle_auth_code' ) );
		$settings['paddle_public_key']                 = sanitize_textarea_field( filter_input( INPUT_POST, 'paddle_public_key' ) );
		$settings['enable_software_licensing']         = (bool) filter_input( INPUT_POST, 'enable_software_licensing' );
		$settings['ignore_local_host_url']             = (bool) filter_input( INPUT_POST, 'ignore_local_host_url' );
		$settings['refund_membership_cancellation']    = (bool) filter_input( INPUT_POST, 'refund_membership_cancellation' );
		$settings['skip_account_creation_on_mismatch'] = (bool) filter_input( INPUT_POST, 'skip_account_creation_on_mismatch' );
		$settings['self_service_plan_change']          = (bool) filter_input( INPUT_POST, 'self_service_plan_change' );
		$settings['restriction_message']               = sanitize_text_field( filter_input( INPUT_POST, 'restriction_message' ) );

		update_option( SETTING_OPTION, $settings );
		add_settings_error( 'paddlepress', 'paddlepress', esc_html__( 'Settings saved.', 'paddlepress' ), 'success' );

		$license_key = sanitize_text_field( filter_input( INPUT_POST, 'license_key' ) );
		update_option( LICENSE_KEY_OPTION, $license_key, false );

		return;
	}

}

/**
 * Purges transients cache for the API responses
 */
function purge_cache() {
	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'paddlepress_clear_transients' ) ) {
		wp_nonce_ays( '' );
	}

	delete_transient( 'paddlepress_paddle_products' );
	delete_transient( 'paddlepress_paddle_subscriptions' );

	wp_safe_redirect( esc_url_raw( add_query_arg( 'cache-cleared', true, wp_get_referer() ) ) );
	exit;
}
