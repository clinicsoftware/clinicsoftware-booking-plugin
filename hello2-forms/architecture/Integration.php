<?php

namespace Hello2Forms\Architecture;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Service\Actions\ClinicSoftwareCMSAction;
use Hello2Forms\Service\Actions\WebhookAction;
use Hello2Forms\Validators\ActionStripeValidator;

class Integration {

	public const LIST = [
		self::TYPE_STRIPE,
		self::TYPE_WEBHOOK,
		self::TYPE_CLINIC_SOFTWARE_CMS
	];

	public const STRUCTURES = [
		self::TYPE_STRIPE => [
			'pk'
		],
		self::TYPE_WEBHOOK => [
			'url'
		],
		self::TYPE_CLINIC_SOFTWARE_CMS => [
			'key',
			'secret',
			'alias',
			'server'
		]
	];

	public const VALIDATORS_MAPPED = [
		self::TYPE_STRIPE => ActionStripeValidator::class,
		self::TYPE_WEBHOOK => null,
		self::TYPE_CLINIC_SOFTWARE_CMS => null
	];

	public const ACTIONS_MAPPED = [
		self::TYPE_STRIPE => null,
		self::TYPE_WEBHOOK => WebhookAction::class,
		self::TYPE_CLINIC_SOFTWARE_CMS => ClinicSoftwareCMSAction::class
	];

	public const TYPE_STRIPE = 'payment.stripe';
	public const TYPE_WEBHOOK = 'webhook';
	public const TYPE_CLINIC_SOFTWARE_CMS = 'webhook.clinic_software.cms';
}
