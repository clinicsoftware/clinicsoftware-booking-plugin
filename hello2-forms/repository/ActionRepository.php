<?php

namespace Hello2Forms\Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\Integration;
use Hello2Forms\Architecture\Repository;
use Hello2Forms\Casts\ActionDataCast;
use Hello2Forms\Internals\CastHelper;

class ActionRepository extends Repository {

	private const SEARCHABLE_COLUMNS = [ 'id', 'user_id', 'type' ];

	function findOneBy( string $column, mixed $value ): ?array {
		if ( ! in_array( $column, self::SEARCHABLE_COLUMNS, true ) ) {
			return null;
		}
		$placeholder = $column === 'type' ? '%s' : '%d';
		$result = $this->getResults(
			$this->prepare( "SELECT * FROM wp_hello2_forms_actions WHERE {$column} = {$placeholder}", $value )
		);
		if ( empty( $result ) ) {
			return null;
		}
		return CastHelper::cast(ActionDataCast::class,$result[0]) ?? null;
	}

	function findAll( ): ?array {
		$result = $this->getResults( 'SELECT * FROM wp_hello2_forms_actions' );
		return CastHelper::castArray(ActionDataCast::class,$result) ?? null;
	}

	function store(
		array $data,
		int $creator_id
	): object|bool|array|null {

		return $this->insert( 'wp_hello2_forms_actions',
			CastHelper::cast( ActionDataCast::class, [
				...$data,
				'created_at' => gmdate( "Y-m-d h:i:s" ),
				'updated_at' => gmdate( "Y-m-d h:i:s" ),
				'user_id' => $creator_id
			], true ),
		);
	}

	function link(int $action_id, int $form_id, int $key): object|bool|array|null {
		return $this->insert( 'wp_hello2_forms_form_actions', [
				'form_id'    => $form_id,
				'action_id'  => $action_id,
				'order'      => $key,
				'updated_at' => gmdate( "Y-m-d h:i:s" ),
				'created_at' => gmdate( "Y-m-d h:i:s" ),
			]
		);
	}

	function findLink(int $action_id, int $form_id): object|bool|array|null {
		return $this->getResults(
			$this->prepare(
				'SELECT * FROM wp_hello2_forms_form_actions WHERE form_id = %d AND action_id = %d',
				$form_id,
				$action_id
			)
		);
	}

	function findLinksForForm(int $form_id): object|bool|array|null {
		return $this->getResults(
			$this->prepare(
				'SELECT * FROM wp_hello2_forms_form_actions WHERE form_id = %d ORDER BY `order` ASC',
				$form_id
			)
		);
	}

	function findAllActionsForForm( int $form_id ): ?array {
		$result = $this->getResults(
			$this->prepare(
				'SELECT * FROM wp_hello2_forms_form_actions WHERE form_id = %d ORDER BY `order` ASC',
				$form_id
			)
		);

		if ( empty( $result ) ) {
			return null;
		}

		$actions = array_map( function ( $link ) {
			return (int) $link['action_id'];
		}, $result );

		if ( empty( $actions ) ) {
			return null;
		}

		$placeholders = implode( ',', array_fill( 0, count( $actions ), '%d' ) );
		$result = $this->getResults(
			$this->prepare(
				"SELECT * FROM wp_hello2_forms_actions WHERE id IN ({$placeholders})",
				...$actions
			)
		);

		$result = CastHelper::castArray(ActionDataCast::class,$result) ?? null;

		if ($result === null) {
			return null;
		}

		foreach ($result as $key => $value) {
			if (isset( $value['data']['account'])) {
				$account_id = (int) $value['data']['account'];
				$account = $this->getRow(
					$this->prepare(
						'SELECT * FROM wp_hello2_forms_integrations_accounts WHERE id = %d',
						$account_id
					)
				);
				if ( $account ) {
					$account['data'] = json_decode( $account['data'], true );
					$result[$key]['data']['account'] = $account;
				}
			}
		}

		return $result;
	}

	function update(
		array $data,
		array $where
	): bool {
		$update_data = [
			'data' => $data['data'],
			'updated_at' => gmdate( "Y-m-d h:i:s" ),
		];
		if ( isset( $data['name'] ) ) {
			$update_data['name'] = $data['name'];
		}
		if ( isset( $data['type'] ) ) {
			$update_data['type'] = $data['type'];
		}

		return ! is_null( $this->update( 'wp_hello2_forms_actions',
			CastHelper::cast( ActionDataCast::class, $update_data, true ),
			$where
		) );
	}
	function deleteLink(int $linkId): int|false {
		return $this->delete( 'wp_hello2_forms_form_actions', [ 'id' => $linkId ], [ '%d' ] );
	}
	function delete(int $actionId): int|false {
		return $this->delete( 'wp_hello2_forms_actions', [ 'id' => $actionId ], [ '%d' ] );
	}
}
