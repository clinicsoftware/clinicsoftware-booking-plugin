<?php

namespace Hello2Forms\Migration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\DBUtils;
use Hello2Forms\Architecture\Migration;

final class MigrationV1707157782 implements Migration {
	use DBUtils;

	public function up(): void {
		$this->maybeCreateTable('wp_hello2_forms_form_statistics',"
			CREATE TABLE `wp_hello2_forms_form_statistics` (
			  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  `form_id` bigint(20) unsigned NOT NULL,
			  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT('{}') CHECK (json_valid(`data`)),
			  `date` date NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
		");

		$this->maybeCreateTable('wp_hello2_forms_workspaces',"
			CREATE TABLE `wp_hello2_forms_workspaces` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`created_at` timestamp NULL DEFAULT NULL,
				`updated_at` timestamp NULL DEFAULT NULL,
				`name` varchar(255) NOT NULL,
				`icon` text DEFAULT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
		");

		$this->maybeCreateTable('wp_hello2_forms_user_workspace',"
			CREATE TABLE `wp_hello2_forms_user_workspace` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`workspace_id` bigint(20) unsigned NOT NULL,
				`user_id` bigint(20) unsigned NOT NULL,
				`created_at` timestamp NULL DEFAULT NULL,
				`updated_at` timestamp NULL DEFAULT NULL,
				`role` varchar(255) NOT NULL DEFAULT('admin'),
				PRIMARY KEY (`id`),
				UNIQUE KEY `user_workspace_workspace_id_user_id_unique` (`workspace_id`,`user_id`),
				KEY `user_workspace_user_id_foreign` (`user_id`),
				CONSTRAINT `user_workspace_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `wp_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				CONSTRAINT `user_workspace_workspace_id_foreign` FOREIGN KEY (`workspace_id`) REFERENCES `wp_hello2_forms_workspaces` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
		");

		$this->maybeCreateTable('wp_hello2_forms_form_submissions',"
			CREATE TABLE `wp_hello2_forms_form_submissions` (
			  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  `form_id` bigint(20) unsigned NOT NULL,
			  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT('{}') CHECK (json_valid(`data`)),
			  `created_at` timestamp NULL DEFAULT NULL,
			  `updated_at` timestamp NULL DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  KEY `form_submissions_form_id_index` (`form_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
		");

		$this->maybeCreateTable('wp_hello2_forms_form_views',"
			CREATE TABLE `wp_hello2_forms_form_views` (
			  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  `form_id` bigint(20) unsigned NOT NULL,
			  `created_at` timestamp NULL DEFAULT NULL,
			  `updated_at` timestamp NULL DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
		");

		$this->maybeCreateTable('wp_hello2_forms_form_zapier_webhooks',"
			CREATE TABLE `wp_hello2_forms_form_zapier_webhooks` (
			  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  `form_id` bigint(20) unsigned NOT NULL,
			  `hook_url` varchar(255) NOT NULL,
			  `deleted_at` timestamp NULL DEFAULT NULL,
			  `created_at` timestamp NULL DEFAULT NULL,
			  `updated_at` timestamp NULL DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
		");

		$this->maybeCreateTable('wp_hello2_forms_forms',"
			CREATE TABLE `wp_hello2_forms_forms` (
			  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  `workspace_id` bigint(20) unsigned NOT NULL,
			  `title` varchar(255) NOT NULL,
			  `slug` varchar(255) NOT NULL,
			  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`properties`)),
			  `created_at` timestamp NULL DEFAULT NULL,
			  `updated_at` timestamp NULL DEFAULT NULL,
			  `notifies` tinyint(1) NOT NULL DEFAULT 0,
			  `description` text DEFAULT NULL,
			  `submit_button_text` text NOT NULL DEFAULT('Submit'),
			  `re_fillable` tinyint(1) NOT NULL DEFAULT 0,
			  `re_fill_button_text` text NOT NULL DEFAULT('Fill Again'),
			  `color` varchar(255) NOT NULL DEFAULT('#3B82F6'),
			  `uppercase_labels` tinyint(1) NOT NULL DEFAULT 1,
			  `no_branding` tinyint(1) NOT NULL DEFAULT 0,
			  `hide_title` tinyint(1) NOT NULL DEFAULT 0,
			  `submitted_text` text NOT NULL DEFAULT('Amazing, we saved your answers. Thank you for your time and have a great day!'),
			  `dark_mode` varchar(255) NOT NULL DEFAULT('auto'),
			  `webhook_url` varchar(255) DEFAULT NULL,
			  `send_submission_confirmation` tinyint(1) NOT NULL DEFAULT 0,
			  `logo_picture` varchar(255) DEFAULT NULL,
			  `cover_picture` varchar(255) DEFAULT NULL,
			  `redirect_url` varchar(255) DEFAULT NULL,
			  `custom_code` text DEFAULT NULL,
			  `notification_emails` text DEFAULT NULL,
			  `theme` varchar(255) NOT NULL DEFAULT('default'),
			  `database_fields_update` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`database_fields_update`)),
			  `width` varchar(255) NOT NULL DEFAULT('centered'),
			  `transparent_background` tinyint(1) NOT NULL DEFAULT 0,
			  `closes_at` timestamp NULL DEFAULT NULL,
			  `closed_text` text DEFAULT NULL,
			  `notification_subject` varchar(255) NOT NULL DEFAULT('We saved your answers'),
			  `notification_body` text NOT NULL DEFAULT('<p>Hello there ? <br>This is a confirmation that your submission was successfully saved.</p>'),
			  `notifications_include_submission` tinyint(1) NOT NULL DEFAULT 1,
			  `use_captcha` tinyint(1) NOT NULL DEFAULT 0,
			  `can_be_indexed` tinyint(1) NOT NULL DEFAULT 1,
			  `password` varchar(255) DEFAULT NULL,
			  `notification_sender` varchar(255) NOT NULL DEFAULT('Hello2Forms'),
			  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT('{}') CHECK (json_valid(`tags`)),
			  `deleted_at` timestamp NULL DEFAULT NULL,
			  `creator_id` bigint(20) unsigned NOT NULL,
			  `removed_properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT('{}') CHECK (json_valid(`removed_properties`)),
			  `max_submissions_count` int(11) DEFAULT NULL,
			  `max_submissions_reached_text` text DEFAULT('This form has now reached the maximum number of allowed submissions and is now closed.'),
			  `slack_webhook_url` varchar(255) DEFAULT NULL,
			  `visibility` varchar(255) NOT NULL DEFAULT('public'),
			  `editable_submissions` tinyint(1) NOT NULL DEFAULT 0,
			  `discord_webhook_url` varchar(255) DEFAULT NULL,
			  `editable_submissions_button_text` text NOT NULL DEFAULT('Edit submission'),
			  `confetti_on_submission` tinyint(1) NOT NULL DEFAULT 0,
			  `seo_meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT('{}') CHECK (json_valid(`seo_meta`)),
			  `notification_settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT('{}') CHECK (json_valid(`notification_settings`)),
			  PRIMARY KEY (`id`),
			  KEY `forms_creator_id_foreign` (`creator_id`),
			  KEY `forms_workspace_id_index` (`workspace_id`),
			  KEY `forms_slug_index` (`slug`),
			  CONSTRAINT `forms_creator_id_foreign` FOREIGN KEY (`creator_id`) REFERENCES `wp_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
		");

		$this->maybeCreateTable('wp_hello2_forms_templates',"
			CREATE TABLE `wp_hello2_forms_templates` (
			  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  `created_at` timestamp NULL DEFAULT NULL,
			  `updated_at` timestamp NULL DEFAULT NULL,
			  `name` varchar(255) NOT NULL,
			  `slug` varchar(255) NOT NULL,
			  `description` text NOT NULL,
			  `image_url` varchar(500) DEFAULT NULL,
			  `structure` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT('{}') CHECK (json_valid(`structure`)),
			  `questions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT('{}') CHECK (json_valid(`questions`)),
			  `publicly_listed` tinyint(1) NOT NULL DEFAULT 0,
			  `industries` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT('{}') CHECK (json_valid(`industries`)),
			  `types` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT('{}') CHECK (json_valid(`types`)),
			  `short_description` varchar(255) DEFAULT NULL,
			  `related_templates` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT('{}') CHECK (json_valid(`related_templates`)),
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
		");
	}

	public function down(): void {
		$this->db->query("DROP TABLE IF EXISTS `wp_hello2_forms_form_statistics`");
		$this->db->query("DROP TABLE IF EXISTS `wp_hello2_forms_form_submissions`");
		$this->db->query("DROP TABLE IF EXISTS `wp_hello2_forms_form_views`");
		$this->db->query("DROP TABLE IF EXISTS `wp_hello2_forms_form_zapier_webhooks`");
		$this->db->query("DROP TABLE IF EXISTS `wp_hello2_forms_forms`");
		$this->db->query("DROP TABLE IF EXISTS `wp_hello2_forms_templates`");
	}
}
