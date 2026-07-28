<?php

namespace Hello2Forms\Validators;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\ValidatorRule;

class FormTemplateValidator extends ValidatorRule
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules() : array
    {
        return [
            'form' => 'required|array',
            'publicly_listed' => 'boolean',
            'name' => 'required|string|max:60',
            'slug' => 'required|string|alpha_dash',
            'short_description' => 'required|string|max:1000',
            'description' => 'required|string',
            'image_url' => 'required|string',
            'types' => 'nullable|array',
            'industries' => 'nullable|array',
            'related_templates' => 'nullable|array',
            'questions' => 'array',
        ];
    }

	public function messages(): array {
		return [];
	}
}
