<?php

/**
 * Hello2Forms
 *
 * @package   Hello2Forms
 * @author    Infinite Consultancy LTD <connect@clinicsoftware.com>
 * @copyright 2024 Infinite Consultancy LTD
 * @license   GPL 2.0+
 * @link      https://clinicsoftware.com
 */

namespace Hello2Forms\Backend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Engine\Base;
use Hello2Forms\Repository\WorkspaceRepository;
use Hello2Forms\Service\MigrationManagerService;

/**
 * Activate and deactive method of the plugin and relates.
 */
class ActDeact extends Base {

	/**
	 * Initialize the class.
	 *
	 * @return void
	 */
	public function initialize() : void {

		// Activate plugin when new blog is added
		\add_action( 'wpmu_new_blog', array( $this, 'single_activate' ) );

		\add_action( 'admin_init', array( $this, 'single_activate' ) );
	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @param int $blog_id ID of the new blog.
	 *
	 * @return void
	 * @throws \Exception
	 * @since 1.0.0
	 */
	public function activate_new_site( int $blog_id ): void {
		if ( 1 !== \did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		\switch_to_blog( $blog_id );
		self::single_activate();
		\restore_current_blog();
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @param bool|null $network_wide True if active in a multiste, false if classic site.
	 *
	 * @return void
	 * @throws \Exception
	 * @since 1.0.0
	 */
	public static function activate( ?bool $network_wide ): void {
		if ( \function_exists( 'is_multisite' ) && \is_multisite() ) {
			if ( $network_wide ) {
				// Get all blog ids
				/** @var array<\WP_Site> $blogs */
				$blogs = \get_sites();

				foreach ( $blogs as $blog ) {
					\switch_to_blog( (int) $blog->blog_id );
					self::single_activate();
					\restore_current_blog();
				}

				return;
			}
		}

		self::single_activate();
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param bool $network_wide True if WPMU superadmin uses
	 * "Network Deactivate" action, false if
	 * WPMU is disabled or plugin is
	 * deactivated on an individual blog.
	 * @since 1.0.0
	 * @return void
	 */
	public static function deactivate( bool $network_wide ): void {
		if ( \function_exists( 'is_multisite' ) && \is_multisite() ) {
			if ( $network_wide ) {
				// Get all blog ids
				/** @var array<\WP_Site> $blogs */
				$blogs = \get_sites();

				foreach ( $blogs as $blog ) {
					\switch_to_blog( (int) $blog->blog_id );
					self::single_deactivate();
					\restore_current_blog();
				}

				return;
			}
		}

		self::single_deactivate();
	}

	/**
	 * Add admin capabilities
	 *
	 * @return void
	 */
	public static function add_capabilities(): void {
		// Add the capabilites to all the roles
		$caps  = array(
			'manage_hello2forms',
		);
		$roles = array(
			\get_role( 'administrator' ),
			\get_role( 'editor' ),
			\get_role( 'author' ),
			\get_role( 'contributor' ),
			\get_role( 'subscriber' ),
		);

		foreach ( $roles as $role ) {
			foreach ( $caps as $cap ) {
				if ( \is_null( $role ) ) {
					continue;
				}

				$role->add_cap( $cap );
			}
		}
	}

	/**
	 * Remove capabilities to specific roles
	 *
	 * @return void
	 */
	public static function remove_capabilities(): void {
		// Remove capabilities to specific roles
		$bad_caps = array(
			'manage_hello2forms',
		);
		$roles    = array(
			\get_role( 'administrator' ),
			\get_role( 'editor' ),
			\get_role( 'author' ),
			\get_role( 'contributor' ),
			\get_role( 'subscriber' ),
		);

		foreach ( $roles as $role ) {
			foreach ( $bad_caps as $cap ) {
				if ( \is_null( $role ) ) {
					continue;
				}

				$role->remove_cap( $cap );
			}
		}
	}

	/**
	 * Upgrade procedure
	 *
	 * @return void
	 */
	public static function upgrade_procedure(): void {
		if ( !\is_admin() ) {
			return;
		}

		$version = \strval( \get_option( 'hello-forms-version' ) );

		if ( !\version_compare( HELLO2FORMS_VERSION, $version, '>' ) ) {
			return;
		}

		\update_option( 'hello-forms-version', HELLO2FORMS_VERSION );
		\delete_option( 'hello2-forms_fake-meta' );
	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @return void
	 * @throws \Exception
	 * @since 1.0.0
	 */
	public static function single_activate(): void {
		self::add_capabilities();

		// insert migration database table:

		self::create_migrations_table();

		$migrationService = new MigrationManagerService();
		$migrationService->migrate();

		$args = array(
			'post_type' => 'page',
			'post_status' => 'publish',
			'posts_per_page' => 1, // Only retrieve one page
			'title' => 'form',
		);

		$check_page_exist = new \WP_Query($args);

		// Check if the page already exists
		if($check_page_exist->is_page) {
			wp_insert_post(
				array(
					'comment_status' => 'close',
					'ping_status'    => 'close',
					'post_author'    => 1,
					'post_title'     => ucwords('form'),
					'post_name'      => strtolower(str_replace(' ', '-', trim('form'))),
					'post_status'    => 'publish',
					'post_content'   => 'Form',
					'post_type'      => 'page',
				)
			);
		}

		// get list of all users:
		$users = get_users();
		// for each user insert workspace
		foreach ($users as $user) {
			$workspace = new WorkspaceRepository();
			if ($workspace->findByUserId($user->ID) === null) {
				$workspace->store($user->display_name.'\'s Personal Workspace', '📥', $user->ID);
			}
		}

		// Clear the permalinks
		\flush_rewrite_rules();
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private static function single_deactivate(): void {
		self::remove_capabilities();
		// Clear the permalinks
		\flush_rewrite_rules();
	}

	/**
	 * @throws \Exception
	 */
	private static function create_migrations_table(): void {
		// Create the migrations table
		global $wpdb;
		require_once ABSPATH.'/wp-admin/install-helper.php';
		$tableName = $wpdb->prefix . 'hello2_forms_migrations';

		$sql = "
			CREATE TABLE $tableName (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				class varchar(60) NOT NULL UNIQUE,
				timestamp int(11) NOT NULL UNIQUE,
				run_date int(11) NOT NULL,
				PRIMARY KEY  (id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
		";
		if (!maybe_create_table( $tableName, $sql )) {
			throw new \Exception( 'Could not create the migrations table' );
		}
	}
}
