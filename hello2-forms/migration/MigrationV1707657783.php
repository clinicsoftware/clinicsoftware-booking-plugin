<?php

namespace Hello2Forms\Migration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\DBUtils;
use Hello2Forms\Architecture\Migration;

final class MigrationV1707657783 implements Migration {
	use DBUtils;

	public function up(): void {
		$table = $this->realTable('wp_hello2_forms_forms');

		$this->db->query("ALTER TABLE `{$table}` ADD submission_mode VARCHAR(255) NULL");
		$this->db->query("ALTER TABLE `{$table}` ADD submission_extra_data longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL");
	}

	public function down(): void {
		$table = $this->realTable('wp_hello2_forms_forms');
		$this->db->query("
			ALTER TABLE $table
				DROP COLUMN submission_mode;");

		$this->db->query("
			ALTER TABLE $table
				DROP COLUMN submission_extra_data;
		");
	}
}
