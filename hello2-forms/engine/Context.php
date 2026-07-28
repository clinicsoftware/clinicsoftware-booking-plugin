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

use Inpsyde\WpContext;

/**
 * Hello2 Forms Is Methods
 */
class Context {

	/**
	 * WpContext Class
	 *
	 * @var ?object
	 */
	public ?object $context = null;

	/**
	 * What type of request is this?
	 *
	 * @since 1.0.0
	 * @param  string $type admin, ajax, cron, cli, amp or frontend.
	 * @return bool
	 * @SuppressWarnings("StaticAccess")
	 */
	public function request( string $type ): bool {
		$this->context = WpContext::determine();

		switch ( $type ) {
			case 'backend':
				return $this->context->isBackoffice();

			case 'ajax':
				return $this->context->isAjax();

			case 'installing_wp':
				return $this->context->isInstalling();

			case 'rest':
				return $this->context->isRest();

			case 'cron':
				return $this->context->isCron();

			case 'frontend':
				return $this->context->isFrontoffice();

			case 'cli':
				return $this->context->isWpCli();

			case 'amp':
				return $this->is_amp();

			default:
				\_doing_it_wrong( __METHOD__, \esc_html( \sprintf( 'Unknown request type: %s', $type ) ), '1.0.0' );

				return false;
		}
	}

	/**
	 * Is AMP
	 *
	 * @return bool
	 */
	public function is_amp(): bool {
		return \function_exists( 'is_amp_endpoint' ) && \is_amp_endpoint();
	}
}
