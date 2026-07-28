<?php

namespace Hello2Forms\Validators;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class StoreFromValidator extends UserFormValidator
{
	public function rules(): array
	{
		return array_merge(parent::rules(), [// Info about database
			'workspace_id' => 'required',
		]);
	}

	public function messages(): array
	{
		return [];
	}
}
