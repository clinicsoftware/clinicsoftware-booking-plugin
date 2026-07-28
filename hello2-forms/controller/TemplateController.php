<?php

namespace Hello2Forms\Controller;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\AbstractApiController;
use Hello2Forms\Repository\TemplateRepository;
use Hello2Forms\Service\DataValidatorService;
use Hello2Forms\Validators\FormTemplateValidator;

class TemplateController extends AbstractApiController {

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

		$this->registerRestRoute( '', array(

			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'create' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_hello2forms' );
				},
			),
		) );

		$this->registerRestRoute( '(?P<template_slug>\w+)', array(

			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'findSingle' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_hello2forms' );
				},
			),
		) );
	}

	function findSingle( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$templateRepository = new TemplateRepository();
		$data               = $templateRepository->findOneBySlug( $request->get_param( 'template_slug' ));

		// Return all of our comment response data.
		return rest_ensure_response( $data );
	}

	function all(): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$templateRepository = new TemplateRepository();
		$data               = $templateRepository->findAll();

		// Return all of our comment response data.
		return rest_ensure_response( $data );
	}

	function create( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$validatorService = new DataValidatorService();

		$rawData = json_decode( $request->get_body(), true ) ;
		$validatorErrors =  $validatorService->validate( $rawData, new FormTemplateValidator() ) ;

		if ( !empty($validatorErrors) ) {
			return rest_ensure_response( new \WP_REST_Response($validatorErrors, 400) );
		}

		$templateRepository = new TemplateRepository();

		$mapped['structure'] = $rawData['form'];

		unset($mapped['form']);

		$mapped['types']             = $mapped['types'] ?? [];
		$mapped['industries']        = $mapped['industries'] ?? [];
		$mapped['related_templates'] = $mapped['related_templates'] ?? [];
		$mapped['questions']         = $mapped['questions'] ?? [];

		$data = $templateRepository->store( $mapped );

		// Return all of our comment response data.
		return rest_ensure_response( $data );
	}
}
