<?php

namespace Hello2Forms\Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\Repository;
use Hello2Forms\Casts\FormDataCast;
use Hello2Forms\Casts\SubmissionDataCast;
use Hello2Forms\Internals\CastHelper;

class FormViewRepository extends Repository {
	function store(
		int $form_id,
	): bool {
		$time = gmdate("Y-m-d h:i:s");

		return $this->insert('wp_hello2_forms_form_views', [
			'form_id' => $form_id,
			'created_at' => $time,
			'updated_at' => $time,
		]);
	}

	function analyticsPast30Days( int $id ): array {
		$result = $this->getResults(
			$this->prepare(
				"SELECT
				    dates.date,
				    COALESCE(COUNT(view_data.updated_at), 0) AS views
				FROM (
				    SELECT CURDATE() - INTERVAL (a.a) DAY AS date
				    FROM (
				        SELECT @row := @row + 1 AS a
				        FROM (SELECT 0 AS union1 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4) AS nums1
				        CROSS JOIN (SELECT 0 AS union2 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4) AS nums2
				        CROSS JOIN (SELECT @row := 0) AS vars
				    ) AS a
				    WHERE a.a <= 30
				) AS dates
				LEFT JOIN (
				    SELECT
				        DATE(updated_at) AS date,
				        updated_at
				    FROM
				        wp_hello2_forms_form_views
				    WHERE
				        updated_at >= CURDATE() - INTERVAL 30 DAY
				    AND form_id = %d
				) AS view_data ON dates.date = view_data.date
				GROUP BY
				    dates.date;",
				$id
			)
		);
		$fr = [];
		foreach ($result as $value) {
			$fr[$value['date']] =  $value['views'];
		}
		return $fr;
	}
}
