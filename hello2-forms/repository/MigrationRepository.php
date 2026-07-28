<?php

namespace Hello2Forms\Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\Repository;

final class MigrationRepository extends Repository {

	private const REMOVABLE_COLUMNS = [ 'class', 'timestamp' ];

	public function findAll(): array {
		return $this->getResults( 'SELECT * FROM wp_hello2_forms_migrations' );
	}

	public function persist( string $class, int $timestamp ): void {
		$this->insert( 'wp_hello2_forms_migrations', [
			'class'     => $class,
			'timestamp' => $timestamp,
			'run_date'  => time()
		] );
	}

	public function removeBy( string $by, int $value ): void {
		if ( ! in_array( $by, self::REMOVABLE_COLUMNS, true ) ) {
			return;
		}
		$this->delete( 'wp_hello2_forms_migrations', [ $by => $value ], [ '%s', '%d' ] );
	}
}
