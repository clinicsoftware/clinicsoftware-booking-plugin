<?php

namespace Hello2Forms\Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\DBUtils;
use Hello2Forms\Architecture\Repository;


class TemplateRepository extends Repository {
	private function preProcess(?array $result) {
		if ($result === null) {
			return null;
		}

		if (!isset($result['questions'])) {
			$_result=  [];
			foreach ($result as $value) {
				$_result[] = $this->preProcess($value);
			}

			return $_result;
		}

		$result['questions'] = json_decode($result['questions'], true);
		$result['types'] = json_decode($result['types'], true);
		$result['industries'] = json_decode($result['industries'], true);
		$result['related_templates'] = json_decode($result['related_templates'], true);
		$result['structure'] = json_decode($result['structure'], true);

		return $result;

	}

	function findOneBySlug(string $slug) : ?array {
		$result = $this->getResults(
			$this->prepare( 'SELECT * FROM wp_hello2_forms_templates WHERE slug = %s', $slug )
		);
		return $this->preProcess($result[0]) ?? null;
	}

	function findAll(): array {
		return $this->preProcess($this->getResults( 'SELECT * FROM wp_hello2_forms_templates ORDER BY created_at DESC' ));
	}

	function store(
		array $data,
	): bool {
		$time = gmdate("Y-m-d h:i:s");
		if ($data['created_at'] === null) {
			$data['created_at'] = $time;
		}

		if ($data['updated_at'] === null) {
			$data['updated_at'] = $time;
		}

		if ($data['questions'] !== null) {
			$data['questions'] = json_encode($data['questions']);
		}

		if ($data['structure'] !== null) {
			$data['structure'] = json_encode($data['structure']);
		}

		if ($data['types'] !== null) {
			$data['types'] = json_encode($data['types']);
		}

		if ($data['industries'] !== null) {
			$data['industries'] = json_encode($data['industries']);
		}

		if ($data['related_templates'] !== null) {
			$data['related_templates'] = json_encode($data['related_templates']);
		}

		return ! is_null( $this->insert( 'wp_hello2_forms_templates', $data ) );
	}
}
