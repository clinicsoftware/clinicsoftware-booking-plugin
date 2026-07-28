<?php

namespace Hello2Forms\Casts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\Castable;

class ActionDataCast implements Castable {
	public array $data = [
		'id'      => 'int',
		'user_id' => 'int',
		'name'    => 'string',
		'data'    => 'json',
		'type'    => 'string',
	];
}
