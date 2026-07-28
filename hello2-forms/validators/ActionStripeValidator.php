<?php

namespace Hello2Forms\Validators;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\ValidatorRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ActionStripeValidator extends ValidatorRule
{

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, mixed>
	 */
	public function rules() : array
	{
		return [
			'account' => 'required|int',
			'lineItems' => 'array',
			'disableAdvancedFraudDetection' => 'boolean',
			'billingAddressCollection' => Rule::in(['required', 'auto']),
			'shippingAddressCollection' => Rule::in(['required', 'auto']),
			'submitType' => Rule::in(['pay', 'book', 'donate', 'auto']),
			'successUrl' => 'url',
			'cancelUrl' => 'url',
		];
	}

	public function messages(): array {
		return [];
	}
}
