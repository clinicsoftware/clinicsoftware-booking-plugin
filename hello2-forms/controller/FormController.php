<?php

namespace Hello2Forms\Controller;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hashids\Hashids;
use Hello2Forms\Architecture\AbstractApiController;
use Hello2Forms\Architecture\Integration;
use Hello2Forms\Repository\ActionRepository;
use Hello2Forms\Repository\FormRepository;
use Hello2Forms\Repository\FormViewRepository;
use Hello2Forms\Service\DataValidatorService;
use Hello2Forms\Service\FormCleanerService;
use Hello2Forms\Service\StoreFormSubmissionService;
use Hello2Forms\Validators\StoreFromValidator;

class FormController extends AbstractApiController {

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

		$this->registerRestRoute( '(?P<form_id>\d+)/regenerate-link/uuid', array(

			array(
				'methods'             => 'PUT',
				'callback'            => array( $this, 'changeSlugUuid' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_hello2forms' );
				},
			),
		) );

		$this->registerRestRoute( '(?P<form_id>\d+)/regenerate-link/slug', array(

			array(
				'methods'             => 'PUT',
				'callback'            => array( $this, 'changeSlug' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_hello2forms' );
				},
			),
		) );

		$this->registerRestRoute( '(?P<form_id>\d+)/duplicate', array(

			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'duplicate' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_hello2forms' );
				},
			),
		) );

		$this->registerRestRoute( '(?P<form_id>\d+)', array(

			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'deleteForm' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_hello2forms' );
				},
			),
		) );

		$this->registerRestRoute( '(?P<form_id>\d+)', array(

			array(
				'methods'             => 'PUT',
				'callback'            => array( $this, 'edit' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_hello2forms' );
				},
			),
		) );

		$this->registerRestRoute( '(?P<slug>[\w\s-]+)', array(

			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'findOne' ),
				'permission_callback' => function () {
					return true;
				},
			),
		) );

		$this->registerRestRoute( '(?P<slug>[\w\s-]+)/answer', array(

			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'answer' ),
				'permission_callback' => function () {
					return true;
				},
			),
		) );

		$this->registerRestRoute( '(?P<slug>[\w\s-]+)/password', array(

			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'password' ),
				'permission_callback' => function () {
					return true;
				},
			),
		) );

		$this->registerRestRoute( '(?P<slug>[\w\s-]+)/submissions/(?P<submission_id>\w+)', array(

			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'findSubmission' ),
				'permission_callback' => function () {
					return true;
				},
			),
		) );

		$this->registerRestRoute( '(?P<slug>[\w\s-]+)/records/(?P<record_id>\d+)/delete', array(

			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'deleteRecord' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_hello2forms' );
				},
			),
		) );

		$this->registerRestRoute( '(?P<slug>[\w\s-]+)/submissions', array(

			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'findAllSubmissions' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_hello2forms' );
				},
			),
		) );
	}

	function deleteRecord( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$formRepository = new FormRepository();
		$form           = $formRepository->findOneBy( 'slug', $request->get_param( 'slug' ) );

		$deleteState = $formRepository->deleteRecord( $form['id'], $request->get_param( 'record_id' ) );

		if ( $deleteState ) {
			return rest_ensure_response( [
					'type'    => 'success',
					'message' => esc_html__( 'Record successfully removed.', 'hello2-forms' ),
				]
			);
		}

		return rest_ensure_response( [
				"type"    => "error",
				"message" => esc_html__( 'Record could not be found!', 'hello2-forms' ),
			]
		);
	}

	function findAllSubmissions( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$formRepository = new FormRepository();
		$form           = $formRepository->findOneBy( 'slug', $request->get_param( 'slug' ) );
		$submissions    = [];

		if ( $form['id'] !== null ) {
			$submissions = $formRepository->findAllSubmissions( $form['id'] );
		}

		return rest_ensure_response( [
				'data'  => $submissions,
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
							"label"  => esc_html__( "&laquo; Previous", 'hello2-forms' ),
							"active" => false
						],
						[
							"url"    => "",
							"label"  => "1",
							"active" => true
						],
						[
							"url"    => null,
							"label"  => esc_html__( "Next &raquo;", 'hello2-forms' ),
							"active" => false
						]
					]
				]
			]
		);
	}

	function password( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$formRepository = new FormRepository();
		$form           = $formRepository->findOneBy( 'slug', $request->get_param( 'slug' ) );

		if ( $request->get_param( 'password' ) === $form['password'] ) {
			return rest_ensure_response(
				[
					'type'    => 'success',
					'message' => esc_html__( 'Password is correct!', 'hello2-forms' )
				]
			);
		}

		return rest_ensure_response(
			new \WP_REST_Response(
				[
					"type"    => "error",
					"message" => esc_html__( 'Invalid password!', 'hello2-forms' ),
				],
				400
			)
		);
	}

	function findSubmission( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$formRepository = new FormRepository();
		$hash           = new Hashids();
		$form           = $formRepository->findOneBy( 'slug', $request->get_param( 'slug' ) );

		$submission = $formRepository->findSubmission(
			$hash->decode( $request->get_param( 'submission_id' ) )[0]
			, $form['id'] );

		if ( $submission ) {
			return rest_ensure_response(
				$submission
			);
		}

		return rest_ensure_response(
			new \WP_REST_Response(
				[
					"type"    => "error",
					"message" => esc_html__( 'Submission could not be found!', 'hello2-forms' ),
				],
				400
			)
		);
	}

	function findOne( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$formRepository                    = new FormRepository();
		$form                              = $formRepository->findOneBy( 'slug', $request->get_param( 'slug' ) );
		$is_closed                         = isset( $form['closes_at'] ) && $form['closes_at'] && strtotime( $form['closes_at'] ) < time();
		$max_number_of_submissions_reached = isset( $form['max_submissions_count'] ) && $formRepository->countSubmissions( $form['id'] ) >= (int) $form['max_submissions_count'];

		if ( $form['visibility'] === "draft" ) {
			return rest_ensure_response(
				new \WP_REST_Response(
					[
						"type"    => "error",
						"message" => esc_html__( 'Form is not public!', 'hello2-forms' ),
					], 400
				)
			);
		}

		if ( $form ) {
			( new FormViewRepository() )->store( $form['id'] );

			// Actions

			$actionRepository = new ActionRepository();
			$actions          = $actionRepository->findAllActionsForForm( $form['id'] );

			return rest_ensure_response( [
				...$form,
				'actions'                           => $actions,
				'is_closed'                         => $is_closed,
				'form_pending_submission_key'       => $form['slug'] . '_pending_submission_key',
				'max_number_of_submissions_reached' => $max_number_of_submissions_reached,
				'visibility'                        => $is_closed || $max_number_of_submissions_reached ? 'closed' : $form['visibility'],
				'password'                          => '',
				'has_password'                      => ! is_null( $form['password'] ),
				'is_password_protected'             => ! is_null( $form['password'] ) && wp_get_current_user()->ID !== $form['creator_id'],
				'share_url'                         => get_site_url() . '/wp-admin/plugin/hello2-forms/#/forms/' . $form['slug'],
			] );
		}


		return rest_ensure_response( [
				"type"    => "error",
				"message" => esc_html__( 'Form could not be found!', 'hello2-forms' ),
			]
		);
	}

	function answer( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$formRepository = new FormRepository();
		$form           = $formRepository->findOneBy( 'slug', $request->get_param( 'slug' ) );
		$submissionId   = false;
		$submission     = json_decode( $request->get_body(), true );

		if ( $form['visibility'] !== "public" ) {
			return rest_ensure_response(
				new \WP_REST_Response(
					[
						"type"    => "error",
						"message" => esc_html__( 'Form is not public!', 'hello2-forms' ),
					], 400
				)
			);
		}

		if ( $form['use_captcha'] ) {
			$data   = array(
				'secret'   => defined( 'HELLO2_FORMS_HCAPTCHA_SECRET' ) ? HELLO2_FORMS_HCAPTCHA_SECRET : get_option( 'hello2_forms_hcaptcha_secret', '' ),
				'response' => $submission['h-captcha-response']
			);
			$verifyResponse = wp_remote_post(
				'https://hcaptcha.com/siteverify',
				array(
					'body'    => $data,
					'timeout' => 30,
				)
			);

			if ( is_wp_error( $verifyResponse ) ) {
				return rest_ensure_response(
					new \WP_REST_Response(
						[
							"type"    => "error",
							"message" => esc_html__( 'Captcha verification failed.', 'hello2-forms' ),
						], 400
					)
				);
			}

			$responseData = json_decode( wp_remote_retrieve_body( $verifyResponse ) );

			if ( empty( $responseData->success ) ) {
				return rest_ensure_response(
					new \WP_REST_Response(
						[
							"type"    => "error",
							"message" => esc_html__( 'Captcha is not valid!', 'hello2-forms' ),
						], 400
					)
				);
			}
		}

		if ( isset( $form['closes_at'] ) && strtotime( $form['closes_at'] ) < time() ) {
			return rest_ensure_response(
				new \WP_REST_Response( [
					"type"    => "error",
					"message" => esc_html__( 'Form has closed!', 'hello2-forms' ),
				], 400 )
			);
		}

		if ( isset( $form['max_submissions_count'] ) && $formRepository->countSubmissions( $form['id'] ) >= (int) $form['max_submissions_count'] ) {
			return rest_ensure_response(
				new \WP_REST_Response( [
					"type"    => "error",
					"message" => esc_html__( 'Form has reached maximum submissions count!', 'hello2-forms' ),
				], 400 )
			);
		}
		$job = new StoreFormSubmissionService( $form, $submission );

		if ( $form['editable_submissions'] ) {
			$hashids = new Hashids();
			$job->handle();
			$submissionId = $hashids->encode( $job->getSubmissionId() );
		} else {
			$job->handle();
		}

		if ( $form['send_submission_confirmation'] ) {
			$from    = get_option( 'admin_email' );
			$headers = 'From: ' . $from . "\r\n" .
			           'Reply-To: ' . $from . "\r\n";

			$mail = wp_mail(
				$job->getFormData()['email'],
				$form['notification_subject'],
				$form['notification_body'],
				$headers
			);

			if ( ! $mail ) {
				new \WP_REST_Response( [
					"type"    => "error",
					"message" => esc_html__( 'Form has reached maximum submissions count!', 'hello2-forms' ),
				], 400 );
			}
		}

		// Run all actions

		$actionRepository = new ActionRepository();
		$actions          = $actionRepository->findAllActionsForForm( $form['id'] );

		foreach ( $actions as $action ) {
			$integrationClass = Integration::ACTIONS_MAPPED[ $action['type'] ];
			$integrationClass ? ( new $integrationClass() )->run( $form, $action, $job->getFormData() ) : null;
		}

		// WordPress.org requires all built-in features to be fully functional without license checks.
		return rest_ensure_response( array_merge( [
			'type'          => 'success',
			'message'       => 'Form submission saved.',
			'submission_id' => $submissionId
		], $form['redirect_url'] ? [
			'type'         => 'success',
			'redirect'     => true,
			'redirect_url' => $form['redirect_url']
		] : [
			'redirect' => false
		] ) );

	}

	function changeSlugUuid( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$formRepository = new FormRepository();

		$formRepository->update( [ 'slug' => wp_generate_uuid4() ], [ 'id' => $request->get_param( 'form_id' ) ] );

		return rest_ensure_response( [
				"type"    => "success",
				"message" => esc_html__( 'Regenerated slug successfully.', 'hello2-forms' ),
			]
		);
	}

	function changeSlug( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$formRepository = new FormRepository();
		$form           = $formRepository->findOneBy( 'id', $request->get_param( 'form_id' ) );

		$formRepository->update( [ 'slug' => sanitize_title( $form['title'] ) ], [ 'id' => $request->get_param( 'form_id' ) ] );

		return rest_ensure_response( [
				"type"    => "success",
				"message" => esc_html__( 'Regenerated slug successfully.', 'hello2-forms' ),
			]
		);
	}

	function duplicate( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$formRepository = new FormRepository();

		if ( $formRepository->duplicate( $request->get_param( 'form_id' ) ) ) {
			return rest_ensure_response( [
					"type"    => "success",
					"message" => esc_html__( 'Form duplicated successfully.', 'hello2-forms' ),
				]
			);
		}

		return rest_ensure_response(
			new \WP_REST_Response(
				[
					"type"    => "error",
					"message" => esc_html__( 'Form could not be duplicated!', 'hello2-forms' ),
				], 400
			)
		);
	}

	function deleteForm( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$formRepository = new FormRepository();

		if ( $formRepository->delete( $request->get_param( 'form_id' ) ) ) {
			return rest_ensure_response( [
					"type"    => "success",
					"message" => esc_html__( 'Form deleted successfully.', 'hello2-forms' ),
				]
			);
		}

		return rest_ensure_response( [
				"type"    => "error",
				"message" => esc_html__( 'Form could not be deleted!', 'hello2-forms' ),
			]
		);
	}

	function edit( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$validatorService = new DataValidatorService();

		$rawData         = json_decode( $request->get_body(), true );
		$validatorErrors = $validatorService->validate( $rawData, new StoreFromValidator() );

		if ( ! empty( $validatorErrors ) ) {
			return rest_ensure_response( new \WP_REST_Response( $validatorErrors, 400 ) );
		}

		$formData = new FormCleanerService();
		$data     = $formData->processData( $rawData )
		                     ->simulateCleaning( 0 ) // add workspace
		                     ->getData();

		unset( $data['creator'] );
		unset( $data['views'] );
		unset( $data['views_count'] );
		unset( $data['extra'] );
		unset( $data['last_edited_human'] );
		unset( $data['submissions'] );
		unset( $data['submissions_count'] );
		unset( $data['tags'] );
		unset( $data['seo_meta'] );
		unset( $data['workspace'] );
		unset( $data['properties'] );

		$formData = new FormCleanerService();

		$data = $formData->processData( json_decode( $request->get_body(), true ) )
		                 ->simulateCleaning( 0 ) // add workspace
		                 ->getData();

		$formRepository = new FormRepository();
		$update         = $formRepository->update( $data, [ 'id' => $request->get_param( 'form_id' ) ] );

		if ( $update ) {
			return rest_ensure_response( [
					"type"    => "success",
					"message" => esc_html__( 'Form updated successfully.', 'hello2-forms' ),
					"form"    => $data
				]
			);
		}

		return rest_ensure_response( [
				"type"    => "error",
				"message" => esc_html__( 'Form could not be updated!', 'hello2-forms' ),
			]
		);
	}

	function store( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response|\WP_HTTP_Response {
		$formData         = new FormCleanerService();
		$validatorService = new DataValidatorService();

		$rawData         = json_decode( $request->get_body(), true );
		$validatorErrors = $validatorService->validate( $rawData, new StoreFromValidator() );

		if ( ! empty( $validatorErrors ) ) {
			return rest_ensure_response( new \WP_REST_Response( $validatorErrors, 400 ) );
		}

		$data = $formData->processData( $rawData )
		                 ->simulateCleaning( 0 ) // add workspace
		                 ->getData();

		$formRepository = new FormRepository();
		$store          = $formRepository->store( $data, get_current_user_id() );

		if ( $store ) {
			return rest_ensure_response( [
					"type"    => "success",
					"message" => esc_html__( 'Form created successfully.', 'hello2-forms' ),
					"form"    => $data
				]
			);
		}

		return rest_ensure_response( [
				"type"    => "error",
				"message" => esc_html__( 'Form could not be created!', 'hello2-forms' ),
				"form"    => $data
			]
		);
	}
}
