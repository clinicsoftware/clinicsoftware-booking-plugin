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

/**
 * Get the settings of the plugin in a filterable way
 *
 * @return array|bool
 * @since 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function hello2forms_get_settings(): array|bool {
	return apply_filters( 'hello2forms_get_settings', get_option( 'hello2forms_settings' ) );
}
