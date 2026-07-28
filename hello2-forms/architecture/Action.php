<?php

namespace Hello2Forms\Architecture;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface Action {
	public function run(array $form, array $action, array $submittedData): void;
}
