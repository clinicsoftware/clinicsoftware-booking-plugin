<?php

/**
 * Hello2Forms
 *
 * @package   Hello2Forms
 * @author    Infinite Consultancy LTD <connect@clinicsoftware.com>
 * @copyright 2024 Infinite Consultancy LTD
 * @license   GPL 2.0+
 * @link      https://clinicsoftware.com
 */

namespace Hello2Forms\Engine;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base skeleton of the plugin
 */
class Base {

	/**
	 * @var array The settings of the plugin.
	 */
	public array $settings = [];

	/**
	 * Initialize the class and get the plugin settings
	 *
	 * @return void
	 */
	public function initialize(): void {
		$settings = \hello2forms_get_settings();

		$this->settings = is_bool( $settings ) ? [] : $settings;
	}

}
