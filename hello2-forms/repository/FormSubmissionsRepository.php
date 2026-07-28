<?php

namespace Hello2Forms\Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\Repository;
use Hello2Forms\Casts\SubmissionDataCast;
use Hello2Forms\Internals\CastHelper;

class FormSubmissionsRepository extends Repository {
	function store( array $data ): int {
		$this->insert( 'wp_hello2_forms_form_submissions', $data );

		return (int) $this->db->insert_id;
	}

	function update( array $data, array $where ): int {
		$this->update( 'wp_hello2_forms_form_submissions', $data, $where );

		return (int) $this->db->insert_id;
	}

	function findOneById( int $id ): object|bool|array|null {
		$result = $this->getResults(
			$this->prepare( 'SELECT * FROM wp_hello2_forms_form_submissions WHERE id = %d', $id )
		);
		if ( empty( $result ) ) {
			return null;
		}
		return CastHelper::cast(SubmissionDataCast::class,$result[0]) ?? null;
	}

	public function countSubmissionsForForm( int $id ): int {
		$result = $this->getResults(
			$this->prepare( 'SELECT COUNT(id) FROM wp_hello2_forms_form_submissions WHERE form_id = %d', $id )
		);

		return (int) ( $result[0]['COUNT(id)'] ?? 0 );
	}
}
