<?php

/**
 * @package   Hello2Forms
 * @author    Infinite Consultancy LTD <connect@clinicsoftware.com>
 * @copyright 2024 Infinite Consultancy LTD
 * @license   GPL 2.0+
 * @link      https://clinicsoftware.com
 *
 * Plugin Name:     Hello2 Forms
 * Plugin URI:      https://clinicsoftware.com
 * Description:     Builder for Stylish & Smart Forms (Bookings, Marketing, Leads, Appointments) 
 * Version:         1.0.0
 * Author:          Infinite Consultancy LTD
 * Author URI:      https://clinicsoftware.com
 * Text Domain:     hello2-forms
 * License:         GPL 2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path:     /languages
 * Requires PHP:    8.1
 * WordPress-Plugin-Boilerplate-Powered: v3.3.0
 */

// If this file is called directly, abort.
use Hello2Forms\Backend\ActDeact;
use Hello2Forms\Backend\SettingsPage;
use Hello2Forms\Engine\Initialize;
use Micropackage\Requirements\Requirements;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

define( 'HELLO2FORMS_VERSION', '1.0.0' );
define( 'HELLO2FORMS_TEXTDOMAIN', 'hello2-forms' );
define( 'HELLO2FORMS_NAME', 'Hello2 Forms' );
define( 'HELLO2FORMS_PLUGIN_ROOT', plugin_dir_path( __FILE__ ) );
define( 'HELLO2FORMS_PLUGIN_ABSOLUTE', __FILE__ );
define( 'HELLO2FORMS_MIN_PHP_VERSION', '8.1' );
define( 'HELLO2FORMS_MIN_WP_VERSION', '5.3' );

if ( version_compare( PHP_VERSION, HELLO2FORMS_MIN_PHP_VERSION, '<=' ) ) {
	add_action(
		'admin_init',
		static function () {
			deactivate_plugins( plugin_basename( __FILE__ ) );
		}
	);
	add_action(
		'admin_notices',
		static function () {
			echo wp_kses_post(
				sprintf(
					'<div class="notice notice-error"><p>%s</p></div>',
					__( '"Hello2 Forms" requires PHP ', 'hello2-forms' ) . HELLO2FORMS_MIN_PHP_VERSION . __( ' or newer.', 'hello2-forms' )
				)
			);
		}
	);

	// Return early to prevent loading the plugin.
	return;
}

$hello2forms_libraries = require HELLO2FORMS_PLUGIN_ROOT . 'vendor/autoload.php'; //phpcs:ignore

require_once HELLO2FORMS_PLUGIN_ROOT . 'function/functions.php';
require_once HELLO2FORMS_PLUGIN_ROOT . 'function/isTimestamp.php';

$hello2forms_requirements = new Requirements(
	'Hello2 Forms',
	array(
		'php'            => HELLO2FORMS_MIN_PHP_VERSION,
		'wp'             => HELLO2FORMS_MIN_WP_VERSION
	)
);

if ( ! $hello2forms_requirements->satisfied() ) {
	$hello2forms_requirements->print_notice();

	return;
}

if ( ! wp_installing() ) {
	register_activation_hook( __FILE__, array( new ActDeact, 'activate' ) );
	register_deactivation_hook( __FILE__, array( new ActDeact, 'deactivate' ) );
	add_action('plugins_loaded',
		/**
		 * @throws Exception
		 */
		static function () use ( $hello2forms_libraries ) {
			new Initialize( $hello2forms_libraries );
		}
	);
}

