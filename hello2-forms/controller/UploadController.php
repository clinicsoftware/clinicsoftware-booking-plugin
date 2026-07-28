<?php

namespace Hello2Forms\Controller;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\AbstractApiController;
use Hello2Forms\Repository\FormRepository;
use Hello2Forms\Service\DataValidatorService;
use Hello2Forms\Service\FormCleanerService;
use Hello2Forms\Validators\StoreFromValidator;

class UploadController extends AbstractApiController {

	// Register our routes.
	public function registerRoutes(): void {
		$this->registerRestRoute( 'upload', array(

			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'upload' ),
				'permission_callback' => function () {
					return true;
				},
			),
		) );

	}

	function upload( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$request->get_file_params();
		$param = $request->get_file_params()['file'];

		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		$upload = \wp_handle_upload( $param, array( 'test_form' => false ) );

		if ( empty( $upload['error'] ) ) {
			return rest_ensure_response( $upload );
		}

		return rest_ensure_response(
			new \WP_REST_Response([
				"type"    => "error",
				"message" => $upload['error'],
			], 400)
		);

	}
}
