<?php

namespace Hello2Forms\Service;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\Migration;
use Hello2Forms\Repository\MigrationRepository;
use function Hello2Forms\Function\isTimestamp;

class MigrationManagerService {

	public const MIGRATION_FOLDER = HELLO2FORMS_PLUGIN_ROOT . '/migration';

	private array $migrations = [];

	public const MIGRATE_UP = 'up';
	public const MIGRATE_DOWN = 'down';

	private function getMigrationsFromFolder(): array {
		if ( ! empty( $this->migrations ) ) {
			return $this->migrations;
		}

		$files      = scandir( self::MIGRATION_FOLDER );
		$migrations = [];
		foreach ( $files as $file ) {
			if (
				false !== strpos( $file, '.php' ) && // Migrations must be php files.
				false !== strpos( $file, 'MigrationV' ) && // Migration files must contain the word "MigrationV".
				is_numeric( $timestamp = str_replace( [
					'MigrationV',
					'.php'
				], '', $file ) ) && // Check if the migration file has a valid number in its name.
				isTimestamp( $timestamp ) // Migration must have a valid timestamp in its name
			) {
				$migrations[] = [
					'file'      => $file,
					'timestamp' => $timestamp,
					'class'     => 'Hello2Forms\\Migration\\' . str_replace( '.php', '', $file )
				];
			}
		}
		$this->migrations = $migrations;

		return $migrations;
	}

	/**
	 *  The timestamp of the migration to execute.
	 *  Migration will be executed in the order of the timestamp.
	 *
	 * @param string $direction The direction of the migration. Either up or down.
	 * @param int|null $timestamp The timestamp will specify from what migration to start or end depending on the direction.
	 *
	 * @return void
	 */
	public function migrate( string $direction = self::MIGRATE_UP, int $timestamp = null ): void {
		$migrations = $this->getMigrationsFromFolder();

		if ( empty( $migrations ) ) {
			return;
		}

		$migrationsRepository = new MigrationRepository();
		$executedMigrations   = $migrationsRepository->findAll();

		foreach ( $migrations as $migration ) {
			// If a timestamp is specified, skip migrations that are not in the direction of the timestamp.
			if (!is_null($timestamp)) {
				if ($direction === self::MIGRATE_UP && $migration['timestamp'] >= $timestamp) {
					continue;
				}

				if ($direction === self::MIGRATE_DOWN && $migration['timestamp'] <= $timestamp) {
					continue;
				}
			}

			$isMigrationAlreadyExecuted = ! empty( array_filter( $executedMigrations,
				function ( $_migration ) use ( $migration ) {
					return $_migration['timestamp'] == $migration['timestamp'];
				} )
			);

			// If the migration has already been executed, skip it.
			if (
				($isMigrationAlreadyExecuted && $direction === self::MIGRATE_UP) ||
				(!$isMigrationAlreadyExecuted && $direction === self::MIGRATE_DOWN)
			) {
				continue;
			}

			$migrationPath = require_once self::MIGRATION_FOLDER . '/' . $migration['file'];

			$migrationObject = new $migration['class']();

			if (!($migrationObject instanceof Migration )) {
				continue;
			}

			if ( $direction === self::MIGRATE_UP ) {
				$migrationObject->up();
				$migrationsRepository->persist( $migration['class'], $migration['timestamp'] );
			} else {
				$migrationObject->down();
				$migrationsRepository->removeBy( 'timestamp', $migration['timestamp'] );
			}
		}
	}

}
