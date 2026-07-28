<?php

namespace Hello2Forms\Migration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\DBUtils;
use Hello2Forms\Architecture\Migration;

final class MigrationV1707657784 implements Migration {
	use DBUtils;

	public function up(): void {
		$this->maybeCreateTable( 'wp_hello2_forms_actions', "
			CREATE TABLE IF NOT EXISTS wp_hello2_forms_actions (
				id INT AUTO_INCREMENT PRIMARY KEY,
			    user_id BIGINT UNSIGNED NOT NULL,
				name VARCHAR(255) NOT NULL,
			    `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT('{}') CHECK (json_valid(`data`)),
				type VARCHAR(255) NOT NULL,
				created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			    CONSTRAINT `action_user_key` FOREIGN KEY (`user_id`) REFERENCES `wp_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
			);
		" );

		$this->maybeCreateTable( 'wp_hello2_forms_form_actions', "
			CREATE TABLE IF NOT EXISTS wp_hello2_forms_form_actions (
				id INT AUTO_INCREMENT PRIMARY KEY,
				form_id BIGINT UNSIGNED NOT NULL,
				action_id INT NOT NULL,
				`order` INT NOT NULL,
				created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			    CONSTRAINT `form_key` FOREIGN KEY (`form_id`) REFERENCES `wp_hello2_forms_forms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
			    CONSTRAINT `form_action_key` FOREIGN KEY (`action_id`) REFERENCES `wp_hello2_forms_actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
			);
		" );

		$this->maybeCreateTable( 'wp_hello2_forms_integrations_accounts', "
			CREATE TABLE IF NOT EXISTS wp_hello2_forms_integrations_accounts (
				id INT AUTO_INCREMENT PRIMARY KEY,
				user_id BIGINT UNSIGNED NOT NULL,
				name VARCHAR(255) NOT NULL,
				type VARCHAR(255) NOT NULL,
			    `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT('{}') CHECK (json_valid(`data`)),
				created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
			);
		" );
	}

	public function down(): void {
		$this->db->query("DROP TABLE IF EXISTS `wp_hello2_forms_form_actions`");
		$this->db->query("DROP TABLE IF EXISTS `wp_hello2_forms_actions`");
		$this->db->query("DROP TABLE IF EXISTS `wp_hello2_forms_integrations_accounts`");
	}
}
