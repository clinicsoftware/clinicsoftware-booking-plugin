<?php

namespace Hello2Forms\Function;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function isTimestamp($timestamp): bool {
	if(ctype_digit($timestamp) && strtotime(gmdate('Y-m-d H:i:s',$timestamp)) === (int)$timestamp) {
		return true;
	} else {
		return false;
	}
}
