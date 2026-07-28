<?php

namespace Hello2Forms\Service;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use HTMLPurifier;
use HTMLPurifier_Config;

class FormCleanerService {
	/**
	 * All the performed cleanings
	 *
	 * Note: pro-feature cleaning has been removed to comply with WordPress.org
	 * guidelines requiring all built-in features to remain fully functional.
	 */
	private array $cleanings = [];

	private array $data;

	/**
	 * Returns form data after request ingestion
	 * @return array
	 */
	public function getData(): array {
		return $this->data;
	}

	/**
	 * Returns true if at least one cleaning was done
	 * @return bool
	 */
	public function hasCleaned(): bool {
		return count( $this->cleanings ) > 0;
	}

	/**
	 * Returns the messages for each cleaning step performed
	 */
	public function getPerformedCleanings(): array {
		return [];
	}

	public function processData( array $data ): self {
		$this->data = $this->commonCleaning( $data );

		return $this;
	}

	public function processForm( array $form ): self {
		$this->data = $this->commonCleaning( $form );

		return $this;
	}

	/**
	 * @param int $workspace
	 *
	 * @return FormCleanerService
	 */
	public function simulateCleaning( int $workspace ): self {
		return $this;
	}

	/**
	 *
	 * @param int $workspace
	 *
	 * @return FormCleanerService
	 */
	public function performCleaning( int $workspace ): self {
		return $this;
	}

	/**
	 * Clean all forms:
	 * - Escape html of custom text block
	 */
	private function commonCleaning( array $data ): array {
		foreach ( $data['properties'] as &$property ) {
			if ( $property['type'] == 'nf-text' && isset( $property['content'] ) ) {
				$config = HTMLPurifier_Config::createDefault();
				$purifier = new HTMLPurifier($config);
				$property['content'] = $purifier->purify($property['content']);
			}
		}

		return $data;
	}

}
