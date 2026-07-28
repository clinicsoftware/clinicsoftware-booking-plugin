<?php

namespace Hello2Forms\Service;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\ValidatorRule;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;

class DataValidatorService {
	public function validate( array $data, ValidatorRule $validatorRules ): array {
		$validatorRules->setFormData($data);
		$filesystem = new Filesystem();
		$fileLoader = new FileLoader($filesystem, '');
		$translator = new Translator($fileLoader, 'en');
		$translator->setFallback('en');
		$translator->setLoaded([
			'*' => [
				'validation' => [
					'en' => require HELLO2FORMS_PLUGIN_ROOT . '/internals/validator-messages.php',
				],
			],
		]);
		$factory = new Factory($translator,new Container());

		$validator = $factory->make($data, $validatorRules->rules(), $validatorRules->messages());

		if ( $validator->fails() ) {
			return $validator->errors()->toArray();
		}

		return [];
	}
}
