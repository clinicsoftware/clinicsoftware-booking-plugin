<?php

namespace Hello2Forms\Controller;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\AbstractApiController;
use Hello2Forms\Architecture\Integration;
use Hello2Forms\Lib\Salon_api;
use Hello2Forms\Repository\FormViewRepository;
use Hello2Forms\Repository\IntegrationAccountsRepository;
use Hello2Forms\Repository\WorkspaceRepository;
use OpenAI;

class IntegrationsController extends AbstractApiController {

	// Register our routes.
	public function registerRoutes(): void {
		$this->registerRestRoute( 'chat-gpt', array(
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'chatGptReply' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_hello2forms' );
				},
			),
		) );

		$this->registerRestRoute( 'stripe/products', array(
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'stripeProducts' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_hello2forms' );
				},
			),
		) );

		$this->registerRestRoute( 'crm/marketing-list/(?P<account_id>\d+)', array(
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'crmMarketingList' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_hello2forms' );
				},
			),
		) );

		$this->registerRestRoute( 'crm/custom-fields/(?P<account_id>\d+)', array(
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'crmLeadCustomFields' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_hello2forms' );
				},
			),
		) );
	}

	function crmMarketingList( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$accountRepository = new IntegrationAccountsRepository();

		$account = $accountRepository->findByIdAndType( $request->get_param('account_id'), Integration::TYPE_CLINIC_SOFTWARE_CMS );
		$account = $account[0]['data'];

		$api = new Salon_api(
			$account['key'],
			$account['secret'],
			$account['alias'],
			$account['server']
		);

		$api->getMapping();
		$getMapping = $api->getLastResult();

		return rest_ensure_response( [
			'data' => array_map( function ( $item ) {
				return [
					'value'   => $item['id'],
					'name' => $item['name'],
				];
			}, $getMapping['data']['marketing_lists'] )
		] );
	}


	function crmLeadCustomFields( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$accountRepository = new IntegrationAccountsRepository();

		$account = $accountRepository->findByIdAndType( $request->get_param('account_id'), Integration::TYPE_CLINIC_SOFTWARE_CMS );
		$account = $account[0]['data'];

		$api = new Salon_api(
			$account['key'],
			$account['secret'],
			$account['alias'],
			$account['server']
		);

		$api->getLeadCustomFields();
		$result = $api->getLastResult();

		return rest_ensure_response( [
			'data' => $result['data']
		] );
	}

	function chatGptReply( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$client = OpenAI::client(get_option( 'hello2forms_settings' )['chat_gpt_key'] ?? '');

		$result = $client->chat()->create([
			'model' => 'gpt-3.5-turbo',
			'messages' => [
				['role' => 'system', 'content' => 'You are a helpful assistant.'],
				['role' => 'user', 'content' => $request->get_param('message')]
			],
			'temperature' => 0.6
		]);

		return rest_ensure_response( ['reply' => $result->choices[0]->message->content] );
	}

	function stripeProducts( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$stripeApiKey= get_option( 'hello2forms_settings' )['stripe_api_key'] ?? '';

		$stripe = new \Stripe\StripeClient($stripeApiKey);

		return rest_ensure_response( $stripe->products->all() );
	}
}
