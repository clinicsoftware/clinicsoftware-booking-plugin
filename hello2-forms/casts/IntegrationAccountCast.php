<?php

namespace Hello2Forms\Casts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\Castable;

class IntegrationAccountCast implements Castable {
	public array $data = [
		'id'         => 'int',
		'name'       => 'string',
		'type'       => 'string',
		'data'       => 'json',
		'updated_at' => 'string',
		'created_at' => 'string',
		'user_id'    => 'int',
	];
}

