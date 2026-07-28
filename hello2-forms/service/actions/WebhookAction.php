<?php

namespace Hello2Forms\Service\Actions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\Action;

class WebhookAction implements Action {

	public function run( array $form, array $action, array $submittedData ): void {
		$this->runWebhook( $action['data']['url'], $form );
	}

	public function runWebhook( string $url, array $data ): void {
		$response = wp_remote_post(
			esc_url_raw( $url ),
			array(
				'method'  => 'POST',
				'body'    => $data,
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			echo esc_html( $response->get_error_message() );
		}
	}
}
