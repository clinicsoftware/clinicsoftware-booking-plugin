<?php

namespace Hello2Forms\Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\Repository;
use Hello2Forms\Casts\FormDataCast;
use Hello2Forms\Casts\SubmissionDataCast;
use Hello2Forms\Internals\CastHelper;

class FormRepository extends Repository {

	private const SEARCHABLE_COLUMNS = [ 'id', 'slug', 'creator_id', 'workspace_id' ];

	function findOneBy( string $column, mixed $value ): ?array {
		if ( ! in_array( $column, self::SEARCHABLE_COLUMNS, true ) ) {
			return null;
		}
		$placeholder = $column === 'slug' ? '%s' : '%d';
		$result = $this->getResults(
			$this->prepare( "SELECT * FROM wp_hello2_forms_forms WHERE {$column} = {$placeholder}", $value )
		);

		if ( empty( $result ) ) {
			return null;
		}

		$result[0]['actions'] = ( new ActionRepository() )->findLinksForForm( (int) $result[0]['id'] );

		return CastHelper::cast( FormDataCast::class, $result[0] ) ?? null;
	}

	function findSubmission( int $submissionId, int $formId ): ?array {
		$result = $this->getResults(
			$this->prepare(
				'SELECT * FROM wp_hello2_forms_form_submissions WHERE form_id = %d AND id = %d',
				$formId,
				$submissionId
			)
		);

		if ( empty( $result ) ) {
			return null;
		}

		return CastHelper::cast( SubmissionDataCast::class, $result[0] ) ?? null;
	}

	function findAllSubmissions( int $formId ): ?array {
		$result = $this->getResults(
			$this->prepare(
				'SELECT * FROM wp_hello2_forms_form_submissions WHERE form_id = %d',
				$formId
			)
		);

		return CastHelper::castArray( SubmissionDataCast::class, $result ) ?? null;
	}

	function countAllSubmissions(): int {
		$result = $this->getResults( 'SELECT COUNT(id) FROM wp_hello2_forms_form_submissions' );

		return (int) ( $result[0]['COUNT(*)'] ?? 0 );
	}

	function countSubmissions( int $formId ): int {
		$result = $this->getResults(
			$this->prepare(
				'SELECT COUNT(*) FROM wp_hello2_forms_form_submissions WHERE form_id = %d',
				$formId
			)
		);

		return (int) ( $result[0]['COUNT(*)'] ?? 0 );
	}

	function delete( int $id ): int|false {
		return $this->delete( 'wp_hello2_forms_forms', [ 'id' => $id ], [ '%d' ] );
	}

	function duplicate( int $id ): array|object|null|bool|int {
		$result = $this->getResults(
			$this->prepare( 'SELECT * FROM wp_hello2_forms_forms WHERE id = %d', $id )
		);

		if ( count( $result ) === 0 ) {
			return null;
		}
		$result = $result[0];

		return $this->insert( 'wp_hello2_forms_forms', [ ...$result, 'id' => null ] );
	}

	function store(
		array $data,
		int $creator_id
	): bool {
		$firstInsert      = $this->insert( 'wp_hello2_forms_forms',
			CastHelper::cast( FormDataCast::class, [
				...$data,
				'created_at' => gmdate( "Y-m-d h:i:s" ),
				'updated_at' => gmdate( "Y-m-d h:i:s" ),
				'slug'       => sanitize_title( $data['title'], esc_html__( 'Could not safely sanitize title!', 'hello2-forms' ) ),
				'creator_id' => $creator_id
			], true ),
		);
		$actionRepository = new ActionRepository();

		foreach ( $data['actions'] as $action ) {
			if ( $actionRepository->findLink( (int) $action['id'], (int) $data['id'] ) === null ) {
				$actionRepository->link( (int) $action['id'], (int) $data['id'], (int) $action['order'] );
			}
		}

		return (bool) $firstInsert;
	}

	function countFormViews( int $form_id ): int {
		$result = $this->getResults(
			$this->prepare(
				'SELECT COUNT(id) FROM wp_hello2_forms_form_views WHERE form_id = %d',
				$form_id
			)
		);

		return (int) ( $result[0]['COUNT(id)'] ?? 0 );
	}

	function update(
		array $data,
		array $where
	): bool {
		$actionRepository = new ActionRepository();

		foreach ( $data['actions'] as $key => $action ) {
			if ( count( $actionRepository->findLink( (int) $action['id'], (int) $data['id'] ) ) === 0 ) {
				$actionRepository->link( (int) $action['id'], (int) $data['id'], (int) $key );
			}
		}

		$links = $actionRepository->findLinksForForm( (int) $data['id'] );

		if ( count( $links ) > 0 ) {
			foreach ( $links as $link ) {
				$found = false;
				foreach ( $data['actions'] as $action ) {
					if ( $action['id'] == $link['action_id'] ) {
						$found = true;
						break;
					}
				}
				if ( ! $found ) {
					$actionRepository->deleteLink( (int) $link['id'] );
				}
			}
		}

		return ! is_null( $this->update( 'wp_hello2_forms_forms',
			CastHelper::cast( FormDataCast::class, [
				...$data,
				'created_at' => gmdate( "Y-m-d h:i:s" ),
				'updated_at' => gmdate( "Y-m-d h:i:s" ),
			], true ),
			$where
		) );
	}

	public function deleteRecord( int $id, int $recordId ): int|false {
		return $this->delete( 'wp_hello2_forms_form_submissions', [ 'id' => $recordId ], [ '%d' ] );
	}
}
