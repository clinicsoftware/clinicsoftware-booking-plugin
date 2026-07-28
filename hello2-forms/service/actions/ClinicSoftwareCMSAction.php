<?php

namespace Hello2Forms\Service\Actions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\Action;
use Hello2Forms\Lib\Salon_api;

class ClinicSoftwareCMSAction implements Action {

	public function run( array $form, array $action, array $submittedData ): void {
		$account = $action['data']['account']['data'];
		$submittedDataParsed = [];

		foreach($form['properties'] as $value){
			foreach ($submittedData as $key => $data){
				if ($key === $value['id']) {
					$submittedDataParsed[$value['crm_map'] ?? strtolower($value['name'])] = $data;
				}
			}
		}

		$api = new Salon_api(
			$account['key'],
			$account['secret'],
			$account['alias'],
			$account['server']
		);

		$api->addLead([
			...$submittedDataParsed,
			'marketing_list_name' => $action['data']['marketingList'],
			'rawForm' => $submittedData,
		]);
	}
}
