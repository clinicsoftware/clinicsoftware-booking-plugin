<?php

namespace Hello2Forms\Controller;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Adbar\Dot;
use Hello2Forms\Architecture\AbstractApiController;
use Hello2Forms\Architecture\Integration;
use Hello2Forms\Repository\ActionRepository;
use Hello2Forms\Service\DataValidatorService;
use Hello2Forms\Validators\ActionStripeValidator;
use Hello2Forms\Validators\ActionValidator;

class ActionController extends AbstractApiController {

	// Register our routes.
	public function registerRoutes(): void {
		$this->registerRestRoute( '', array(
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'list' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_hello2forms' );
				},
			),
		) );

		$this->registerRestRoute( '', array(
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'store' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_hello2forms' );
				},
			),
		) );

		$this->registerRestRoute( '(?P<action_id>\d+)', array(
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'delete' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_hello2forms' );
				},
			),
		) );

		$this->registerRestRoute( '(?P<action_id>\d+)', array(
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'single' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_hello2forms' );
				},
			),
		) );
	}

	public function single( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$actionRepository = new ActionRepository();

		$action = $actionRepository->findOneBy('id', $request->get_param('action_id'));

		return rest_ensure_response( new \WP_REST_Response( [
			'data' => $action,
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
		], 200 ) );
	}

	public function list(): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$actionRepository = new ActionRepository();

		$actions = $actionRepository->findAll();

		return rest_ensure_response( new \WP_REST_Response( [
			'data'  => $actions,
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
		], 200 ) );
	}

	public function store( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$actionRepository = new ActionRepository();
		$validatorService = new DataValidatorService();

		// change post actionData to data before validate
		$rawData = ( new Dot( json_decode( $request->get_body(), true ), true ) )->all();
		// Validate request.
		$validatorErrors     = $validatorService->validate( $rawData, new ActionValidator() );

		$validatorClass = Integration::VALIDATORS_MAPPED[$rawData['type']] ?? null;
		$actionDataValidator = $validatorClass === null ? [] : $validatorService->validate( $rawData['actionData'], new $validatorClass() );

		if ( ! empty( $validatorErrors ) || ! empty( $actionDataValidator ) ) {
			return rest_ensure_response(
				new \WP_REST_Response(
					( new Dot(
						array_merge(
							$validatorErrors,
							[
								"actionData" => $actionDataValidator
							]
						), true ) )->flatten(),
					400 )
			);
		}

		$rawData['data'] = $rawData['actionData'] ?? [];
		unset($rawData['actionData'] );

		$action_id = $rawData['id'] ?? null;
		if ( ! empty( $action_id ) && $actionRepository->findOneBy('id', $action_id ) !== null ) {
			$actionRepository->update( $rawData, ['id' => $action_id]);
		} else {
			$actionRepository->store( $rawData, get_current_user_id() );
		}

		return rest_ensure_response( new \WP_REST_Response( [
			'message' => esc_html('Action saved successfully')
		], 201 ) );
	}

	// Create endpoint for getting account products

	public function delete( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$actionRepository = new ActionRepository();

		$actionRepository->delete( $request->get_param( 'action_id' ) );

		return rest_ensure_response( new \WP_REST_Response( [
			'message' => esc_html('Action deleted successfully'),
			'action_id' => $request->get_param( 'action_id' )
		], 200 ) );
	}
}
