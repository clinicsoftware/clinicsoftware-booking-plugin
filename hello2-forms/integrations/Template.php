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

namespace Hello2Forms\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Engine\Base;
use Hello2Forms\Internals\Vite;

/**
 * Load custom template files
 */
class Template extends Base {

	/**
	 * Initialize the class.
	 *
	 * @return void
	 */
	public function initialize() : void {
		parent::initialize();

		// Override the template hierarchy for load /templates/content-demo.php
		\add_filter( 'template_include', array( self::class, 'load_content_demo' ) );
		\add_action( 'wp_enqueue_scripts', array( self::class, 'enqueuePublicAppAssets' ) );
	}

	public static function enqueuePublicAppAssets(): void {
		global $wp_query;
		if ( ! isset( $wp_query->query['name'] ) || $wp_query->query['name'] !== 'form' ) {
			return;
		}

		Vite::enqueueApp( 'hello2forms-public-app', false );
	}

	/**
	 * Example for override the template system on the frontend
	 *
	 * @param string $template The original templace HTML.
	 * @since 1.0.0
	 * @return string
	 */
	public static function load_content_demo( string $template ): string {
		global $wp_query;
		$theme_files = 'hello2forms/form.php';
		$exists_in_theme = locate_template($theme_files, false);

		if (isset($wp_query->query['name']) && $wp_query->query['name'] == 'form') {
			if ( $exists_in_theme != '' ) {
				$template = $exists_in_theme;
			} else {
				$template = plugin_dir_path( __DIR__ ) . 'templates/form.php';
			}
		}

		return $template;
	}

}
