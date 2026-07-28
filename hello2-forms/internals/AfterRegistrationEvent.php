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

namespace Hello2Forms\Internals;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use DecodeLabs\Tagged as Html;
use Hello2Forms\Engine\Base;
use Hello2Forms\Repository\WorkspaceRepository;

/**
 * Shortcodes of this plugin
 */
class AfterRegistrationEvent extends Base {

	/**
	 * Initialize the class.
	 *
	 * @return void
	 */
	public function initialize() : void {
		parent::initialize();

		\add_action( 'user_register', array( $this, 'afterRegister' ) );
	}

	/**
	 * Shortcode example
	 *
	 * @param int $user_id
	 * @param array $userdata
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public static function afterRegister( int $user_id, array $userdata ) : void {
		$workspace = new WorkspaceRepository();

		$workspace->store($userdata['display_name'].'\'s '. esc_html__('Personal Workspace', 'hello2-forms'), '📥', $user_id);
	}

}
