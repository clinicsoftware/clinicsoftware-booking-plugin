<?php

namespace Hello2Forms\Migration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\DBUtils;
use Hello2Forms\Architecture\Migration;

final class MigrationV1707657785 implements Migration {
	use DBUtils;

	public function up(): void {
		$table   = $this->realTable( 'wp_hello2_forms_forms' );
		$columns = $this->db->get_col(
			"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$table}'"
		);

		if ( ! in_array( 'submission_mode', $columns, true ) ) {
			$this->db->query( "ALTER TABLE `{$table}` ADD COLUMN submission_mode VARCHAR(255) NULL" );
		}

		if ( ! in_array( 'submission_extra_data', $columns, true ) ) {
			$this->db->query( "ALTER TABLE `{$table}` ADD COLUMN submission_extra_data longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL" );
		}
	}

	public function down(): void {
		$table = $this->realTable( 'wp_hello2_forms_forms' );

		$this->db->query( "ALTER TABLE `{$table}` DROP COLUMN IF EXISTS submission_extra_data" );
		$this->db->query( "ALTER TABLE `{$table}` DROP COLUMN IF EXISTS submission_mode" );
	}
}
