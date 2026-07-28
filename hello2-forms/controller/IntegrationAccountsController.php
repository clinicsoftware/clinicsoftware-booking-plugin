<?php

namespace Hello2Forms\Controller;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\AbstractApiController;
use Hello2Forms\Architecture\Integration;
use Hello2Forms\Repository\IntegrationAccountsRepository;

class IntegrationAccountsController extends AbstractApiController {

	// Register our routes.
	public function registerRoutes(): void {

		$this->registerRestRoute( '', array(

			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'store' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_hello2forms' );
				},
			),
		) );

		$this->registerRestRoute( '(?P<type>[\w\.-]+)', array(

			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'list' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_hello2forms' );
				},
			),
		) );

		$this->registerRestRoute( '(?P<id>\d+)', array(

			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'delete' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_hello2forms' );
				},
			),
		) );
	}


	function list( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$integrationAccountsRepository = new IntegrationAccountsRepository();
		$type                          = $request->get_param( 'type' );

		$accounts                      = $integrationAccountsRepository->findByUserIdAndType( get_current_user_id(), $type);

		if ( ! $accounts ) {
			return rest_ensure_response( new \WP_REST_Response( [
				'type' => 'success',
				'data' => [],
			], 201 ));
		}

		return rest_ensure_response( new \WP_REST_Response( [
			'type' => 'success',
			'data' => array_map( function( $account ) {
				return [
					'value' => $account['id'],
					'name' => $account['name'],
				];
			}, $accounts ),
		], 201 ));
	}

	function store( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$integrationAccountsRepository = new IntegrationAccountsRepository();
		$type                          = $request->get_param( 'type' );

		if ( ! in_array( $type, Integration::LIST ) ) {
			return new \WP_REST_Response(
				[
					'error' => 'Invalid type.',
				],
				400 );
		}

		$account =  $request->get_param( 'account' );

		if ( ! is_array( $account ) || count( array_diff_key( $account, array_flip( Integration::STRUCTURES[ $type ] ) ) ) !== 0 ) {
			return new \WP_REST_Response(
				[
					'error' => esc_attr__('Invalid data structure', 'hello2-forms'),
				],
				400 );
		}

		$integrationAccountsRepository->store(
			$request->get_param( 'name' ),
			$type,
			$account,
			get_current_user_id()
		);

		// Return all of our comment response data.
		return rest_ensure_response( new \WP_REST_Response( [
			'type' => 'success',
			'message' => esc_html__('Integration account created successfully', 'hello2-forms')
		], 201 ));
	}

	function delete( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$integrationAccountsRepository = new IntegrationAccountsRepository();
		$id                            = $request->get_param( 'id' );

		$deleted = $integrationAccountsRepository->deleteById( (int) $id, get_current_user_id() );

		if ( ! $deleted ) {
			return new \WP_REST_Response(
				[
					'error' => esc_html__('Account not found or access denied.', 'hello2-forms'),
				],
				404 );
		}

		return rest_ensure_response( new \WP_REST_Response( [
			'type' => 'success',
			'message' => esc_html__('Integration account deleted successfully', 'hello2-forms')
		], 200 ) );
	}

}
