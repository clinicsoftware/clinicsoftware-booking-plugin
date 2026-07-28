<?php

namespace Hello2Forms\Validators;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\ValidatorRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ActionValidator extends ValidatorRule
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules() : array
    {
	    return [
	        'name' => 'required',
	        'type' => 'required',
	    ];
    }

	public function messages(): array {
		return [];
	}
}
