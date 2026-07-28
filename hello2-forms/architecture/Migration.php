<?php

namespace Hello2Forms\Architecture;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface Migration {
	public function up(): void;

	public function down(): void;
}
