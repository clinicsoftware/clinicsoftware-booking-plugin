<?php

namespace Hello2Forms\Casts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\Castable;

class SubmissionDataCast implements Castable {
	public array $data = [
		'id'         => 'int',
		'form_id'    => 'int',
		'created_at' => 'string',
		'updated_at' => 'string',
		'data'       => 'json',
	];
}
