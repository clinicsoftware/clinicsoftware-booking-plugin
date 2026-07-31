<?php

namespace Hello2Forms\Architecture;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Form  {
	const DARK_MODE_VALUES = [ 'auto', 'light', 'dark' ];
	const THEMES = [ 'default', 'simple' ];
	const WIDTHS = [ 'centered', 'full' ];
	const VISIBILITY = [ 'public', 'draft', 'closed' ];
}
