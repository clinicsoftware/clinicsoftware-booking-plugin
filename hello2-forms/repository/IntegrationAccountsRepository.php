<?php

namespace Hello2Forms\Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\Repository;
use Hello2Forms\Casts\FormDataCast;
use Hello2Forms\Casts\IntegrationAccountCast;
use Hello2Forms\Internals\CastHelper;

class IntegrationAccountsRepository extends Repository {

	private const SEARCHABLE_COLUMNS = [ 'id', 'user_id', 'type' ];

	function store(
		string $name,
		string $type,
		array $data,
		int $userId
	): bool {
		$time = gmdate("Y-m-d h:i:s");

		$this->insert( 'wp_hello2_forms_integrations_accounts',
			[
				'created_at' => $time,
				'updated_at' => $time,
				'name'       => $name,
				'type'       => $type,
				'data'       => json_encode( $data ),
				'user_id'    => $userId
			]
		);

		return false;
	}

	function findBy( string $column, mixed $value ): ?array {
		if ( ! in_array( $column, self::SEARCHABLE_COLUMNS, true ) ) {
			return null;
		}
		$placeholder = $column === 'type' ? '%s' : '%d';
		$result = $this->getResults(
			$this->prepare( "SELECT * FROM wp_hello2_forms_integrations_accounts WHERE {$column} = {$placeholder}", $value )
		);
		return CastHelper::castArray(IntegrationAccountCast::class,$result) ?? null;
	}

	function findByUserIdAndType( int $id, string $type ): ?array {
		$result = $this->getResults(
			$this->prepare(
				'SELECT * FROM wp_hello2_forms_integrations_accounts WHERE user_id = %d AND type = %s',
				$id,
				$type
			)
		);
		return CastHelper::castArray(IntegrationAccountCast::class,$result) ?? null;
	}

	function findByIdAndType( int $id, string $type ): ?array {
		$result = $this->getResults(
			$this->prepare(
				'SELECT * FROM wp_hello2_forms_integrations_accounts WHERE id = %d AND type = %s',
				$id,
				$type
			)
		);
		return CastHelper::castArray(IntegrationAccountCast::class,$result) ?? null;
	}

	function deleteById( int $id, int $userId ): bool {
		$result = $this->delete(
			'wp_hello2_forms_integrations_accounts',
			[
				'id'      => $id,
				'user_id' => $userId,
			],
			[ '%d', '%d' ]
		);

		return $result !== false && $result > 0;
	}
}
