<?php

namespace Hello2Forms\Controller;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\AbstractApiController;
use Hello2Forms\Repository\TemplateRepository;
use Hello2Forms\Repository\UserRepository;
use Hello2Forms\Repository\WorkspaceRepository;

class UserController extends AbstractApiController {

	// Register our routes.
	public function registerRoutes(): void {
		$this->registerRestRoute( 'current', array(

			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'current' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_hello2forms' );
				},
			),
		) );

	}

	function current( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$workspaceRepository = new UserRepository();
		// Return all of our comment response data.
		return rest_ensure_response( $workspaceRepository->findById(get_current_user_id()) );
	}
}
