<?php

namespace Hello2Forms\Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\Repository;
use Hello2Forms\Casts\FormDataCast;
use Hello2Forms\Internals\CastHelper;

class WorkspaceRepository extends Repository {
	function isPro( int $workspace ): bool {
		$result = $this->getResults(
			$this->prepare( 'SELECT id FROM wp_hello2_forms_workspaces WHERE id = %d', $workspace )
		);
		return count( $result ) > 0;
	}

	function findAll(): array|null|object {
		$result = $this->getResults( 'SELECT id FROM wp_hello2_forms_workspaces' );

		foreach ( $result as $key => $val ) {
			$result[ $key ] = $this->findOneById( (int) $val['id'] );
		}

		return $result;
	}

	function findOneById( int $id ): array|null|object {
		$result = $this->getResults(
			$this->prepare( 'SELECT * FROM wp_hello2_forms_workspaces WHERE id = %d', $id )
		);

		if ( count( $result ) === 0 ) {
			return null;
		}

		$result = $result[0];

		$relation = $this->getResults(
			$this->prepare( 'SELECT * FROM wp_hello2_forms_user_workspace WHERE workspace_id = %d', (int) $result['id'] )
		);

		$owners = [];

		foreach ( $relation as $relationVal ) {
			$owners[] = $this->eagerLoadUser( (int) $relationVal['user_id'] );
		}
		return [
			...$result,
			'owners'        => $owners,
			'is_enterprise' => false,
			'is_pro'        => false,
			'pivot'         => $relation
		];
	}

	protected function eagerLoadUser( int $userId ): object|array|null {
		$userRepository = new UserRepository();

		return $userRepository->findById( $userId );
	}

	function store(
		string $name,
		string $icon,
		int $userId
	): bool {
		$time = gmdate("Y-m-d h:i:s");

		$this->insert( 'wp_hello2_forms_workspaces',
			[
				'created_at' => $time,
				'updated_at' => $time,
				'name'       => $name,
				'icon'       => $icon
			]
		);

		$this->insert( 'wp_hello2_forms_user_workspace',
			[
				'workspace_id' => (int) $this->db->insert_id,
				'user_id' => $userId,
				'created_at' => $time,
				'updated_at' => $time,
				'role' => 'admin'
			]
		);

		return false;
	}

	function isUserInWorkspace( int $userId, int $workspaceId ): array|null|object {
		$result = $this->getResults(
			$this->prepare(
				'SELECT * FROM wp_hello2_forms_user_workspace WHERE workspace_id = %d AND user_id = %d',
				$workspaceId,
				$userId
			)
		);
		return $result[0] ?? null;
	}

	function findFormsByWorkspaceId( int $workspaceId ): array|null|object {
		return $this->getResults(
			$this->prepare( 'SELECT * FROM wp_hello2_forms_forms WHERE workspace_id = %d', $workspaceId )
		);
	}

	function delete(int $workspaceId): int|false {
		return $this->delete( 'wp_hello2_forms_workspaces', [ 'id' => $workspaceId ], [ '%d' ] );
	}

	function findCurrentUserForms( int $workspaceId ): array|null|object {
		$finalResults = [];
		$current_user_id = (int) get_current_user_id();
		$result       = $this->getResults(
			$this->prepare(
				'SELECT * FROM wp_hello2_forms_forms WHERE workspace_id = %d AND creator_id = %d ORDER BY updated_at DESC',
				$workspaceId,
				$current_user_id
			)
		);
		$submissionRepository = new FormSubmissionsRepository();
		$formRepository = new FormRepository();
		foreach ( $result as $val ) {

			$links = ( new ActionRepository() )->findLinksForForm( (int) $val['id'] );

			$actions = [];

			foreach ( $links as $link ) {
				$actions[] = ( new ActionRepository() )->findOneBy( 'id', (int) $link['action_id'] );
			}

			$finalResults[] = [
				...CastHelper::cast(FormDataCast::class, $val),
				'actions' => $actions,
				'creator'           => [ ...$this->eagerLoadUser( $current_user_id ), ],
				'share_url'         => get_site_url() . '/form/#/forms/' . $val['slug'],
				'views_count'       => $formRepository->countFormViews((int) $val['id']),
				'submissions'       => [],
				'submissions_count' => $submissionRepository->countSubmissionsForForm((int) $val['id']),
				'last_edited_human' => human_time_diff(strtotime($val['updated_at'])). ' ago',
				'extra'             => [
					"loadedWorkspace" => $this->findOneById( $workspaceId ),
					"workspaceIsPro"  => false,
					"userIsOwner"     => true,
					"cleanings"       => []
				]
			];
		}

		return $finalResults;
	}

	public function findByUserId( int $ID ): ?array {
		$result = $this->getResults(
			$this->prepare( 'SELECT * FROM wp_hello2_forms_user_workspace WHERE user_id = %d', $ID )
		);

		if ( count( $result ) === 0 ) {
			return null;
		}

		$workspaces = [];

		foreach ( $result as $val ) {
			$workspaces[] = $this->findOneById( (int) $val['workspace_id'] );
		}

		return $workspaces;
	}
}
