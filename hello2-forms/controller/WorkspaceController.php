<?php

namespace Hello2Forms\Controller;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\AbstractApiController;
use Hello2Forms\Repository\FormRepository;
use Hello2Forms\Repository\FormViewRepository;
use Hello2Forms\Repository\TemplateRepository;
use Hello2Forms\Repository\WorkspaceRepository;
use Hello2Forms\Service\FormCleanerService;

class WorkspaceController extends AbstractApiController {

	// Register our routes.
	public function registerRoutes(): void {
		$this->registerRestRoute( '', array(

			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'all' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_hello2forms' );
				},
			),
		) );

		$this->registerRestRoute( '(?P<workspace_id>\d+)', array(

			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'delete' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_hello2forms' );
				},
			),
		) );

		$this->registerRestRoute( 'store', array(

			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'store' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_hello2forms' );
				},
			),
		) );

		$this->registerRestRoute( '(?P<workspace_id>\d+)/forms', array(

			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'listForms' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_hello2forms' );
				},
			),
		) );

		$this->registerRestRoute( '(?P<workspace_id>\d+)/form-stats/(?P<form_id>\d+)', array(

			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'formStats' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_hello2forms' );
				},
			),
		) );
	}

	function formStats( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$formViewRepository = new FormViewRepository();
		$views           = $formViewRepository->analyticsPast30Days( $request->get_param( 'form_id' ) );
		return rest_ensure_response( [
			'views' => array_merge($views),
			'submissions' => []
		] );
	}

	function listForms( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$workspaceRepository = new WorkspaceRepository();
		$data                = $workspaceRepository->findCurrentUserForms( $request->get_param( 'workspace_id' ) );


		return rest_ensure_response( [
			'data'  => $data,
			'links' => [
				"first" => "",
				"last"  => "",
				"prev"  => null,
				"next"  => null
			],
			'meta'  => [
				"current_page" => 1,
				"from"         => 1,
				"last_page"    => 1,
				"links"        => [
					[
						"url"    => null,
						"label"  => esc_html__("&laquo; Previous",'hello2-forms' ),
						"active" => false
					],
					[
						"url"    => "",
						"label"  => "1",
						"active" => true
					],
					[
						"url"    => null,
						"label"  => esc_html__("Next &raquo;",'hello2-forms' ),
						"active" => false
					]
				]
			]
		] );
	}

	function delete( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$workspaceRepository = new WorkspaceRepository();
		$workspace = $workspaceRepository->findOneById( $request->get_param( 'workspace_id' ) );

		if ($workspaceRepository->isUserInWorkspace(get_current_user_id(), $workspace['id'])['role'] !== 'admin') {
			return rest_ensure_response(
				new \WP_REST_Response( [
					'message' => esc_html__('You are not allowed to delete this workspace', 'hello2-forms')
				], 403)
			);
		}

		$workspaceRepository->delete(  $workspace['id'] );

		// Return all of our comment response data.
		return rest_ensure_response( [
			'workspace_id' => $request->get_param( 'workspace_id' ),
		]);
	}

	function all( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$workspaceRepository = new WorkspaceRepository();

		// Return all of our comment response data.
		return rest_ensure_response( $workspaceRepository->findAll() );
	}

	function store( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$workspaceRepository = new WorkspaceRepository();
		$data                = $workspaceRepository->store(
			$request->get_param( 'name' ),
			$request->get_param( 'emoji' ),
			get_current_user_id()
		);

		// Return all of our comment response data.
		return rest_ensure_response( $data );
	}

}
