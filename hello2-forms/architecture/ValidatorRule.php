<?php

namespace Hello2Forms\Architecture;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class ValidatorRule {
	public array $form;

	public function setFormData( array $data ): void {
		$this->form = $data;
	}

	public abstract function rules(  ): array;

	public abstract function messages(  ): array;
}
