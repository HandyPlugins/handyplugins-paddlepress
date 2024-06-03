<?php
/**
 * Plugin Name:       HandyPlugins PaddlePress - Paddle Integration for WordPress
 * Plugin URI:        https://handyplugins.co/paddlepress-pro/
 * Description:       HandyPlugins PaddlePress is a standalone payments plugin that connects Paddle with WordPress.
 * Version:           2.3.1
 * Requires at least: 5.0
 * Requires PHP:      7.2.5
 * Author:            HandyPlugins
 * Author URI:        https://handyplugins.co/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       handyplugins-paddlepress
 * Domain Path:       /languages
 *
 * @package           PaddlePress
 */

// Useful global constants.
define( 'PADDLEPRESS_VERSION', '2.3.1' );
define( 'PADDLEPRESS_DB_VERSION', '2.0.1' );
define( 'PADDLEPRESS_PLUGIN_FILE', __FILE__ );
define( 'PADDLEPRESS_URL', plugin_dir_url( __FILE__ ) );
define( 'PADDLEPRESS_PATH', plugin_dir_path( __FILE__ ) );
define( 'PADDLEPRESS_INC', PADDLEPRESS_PATH . 'includes/' );

// deactivate pro
if ( defined( 'PADDLEPRESS_PRO_PLUGIN_FILE' ) ) {
	deactivate_plugins( plugin_basename( PADDLEPRESS_PRO_PLUGIN_FILE ) );
	return;
}

// Require Composer autoloader if it exists.
if ( file_exists( PADDLEPRESS_PATH . '/vendor/autoload.php' ) ) {
	require_once PADDLEPRESS_PATH . 'vendor/autoload.php';
}

// Include files.
require_once PADDLEPRESS_INC . 'constants.php';
require_once PADDLEPRESS_INC . 'core.php';
require_once PADDLEPRESS_INC . 'paddle.php';
require_once PADDLEPRESS_INC . 'paddle-billing.php';
require_once PADDLEPRESS_INC . 'settings.php';
require_once PADDLEPRESS_INC . 'shortcodes.php';
require_once PADDLEPRESS_INC . 'utils.php';
require_once PADDLEPRESS_INC . 'install.php';
require_once PADDLEPRESS_INC . 'class-encryption.php';

PaddlePress\Core\setup();
PaddlePress\Install\setup();
PaddlePress\Settings\setup();
PaddlePress\Shortcodes\setup();

