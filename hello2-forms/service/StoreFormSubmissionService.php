<?php

namespace Hello2Forms\Service;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hashids\Hashids;
use Hello2Forms\Repository\FormSubmissionsRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreFormSubmissionService {
	public ?string $submissionId = null;
	public const FILE_UPLOAD_PATH = '';
	public const TMP_FILE_UPLOAD_PATH = '';

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct(
		public array $form,
		public array $submissionData
	) {
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function handle(): void {
		$formData = $this->getFormData();
		$this->addHiddenPrefills( $formData );

		$this->storeSubmission( $formData );

		$formData["submission_id"] = $this->submissionId;
//        FormSubmitted::dispatch($this->form, $formData);
	}

	public function getSubmissionId(): ?string {
		return $this->submissionId;
	}

	private function storeSubmission( array $formData ): void {
		// Create or update record
		if ( $previousSubmission = $this->submissionToUpdate() ) {
			( new FormSubmissionsRepository() )->update(
				[
					'data'       => json_encode( $formData ),
					'updated_at' => gmdate( 'Y-m-d H:i:s' )
				],
				[ 'id' => $previousSubmission['id'] ]
			);
			$this->submissionId = $previousSubmission['id'];
		} else {
			$id                 = ( new FormSubmissionsRepository() )->store( [
				'form_id'    => $this->form['id'],
				'data'       => json_encode( $formData ),
				'created_at' => gmdate( 'Y-m-d H:i:s' ),
				'updated_at' => gmdate( 'Y-m-d H:i:s' )
			] );
			$this->submissionId = $id;
		}
	}

	/**
	 * Search for Submission record to update and returns it
	 */
	private function submissionToUpdate(): ?array {
		if ( $this->form['editable_submissions'] && isset( $this->submissionData['submission_id'] ) && $this->submissionData['submission_id'] ) {
			$submissionId = $this->submissionData['submission_id'] ? ( new Hashids() )->decode( $this->submissionData['submission_id'] ) : false;
			$submissionId = $submissionId[0] ?? null;
			return ( new FormSubmissionsRepository() )->findOneById( $submissionId );
		}

		return null;
	}

	/**
	 * Retrieve data from a request object, and pre-format it if needed.
	 * - Replace notion forms id with notion field ids
	 * - Clean \ in select id values
	 * - Stores file and replace value with url
	 * - Generate auto increment id & unique id features for rich text field
	 */
	public function getFormData(): array {
		$data      = $this->submissionData;
		$finalData = [];

		$properties = collect( $this->form['properties'] );

		// Do require transformation per type (e.g., file uploads)
		foreach ( $data as $answerKey => $answerValue ) {
			$field = $properties->where( 'id', $answerKey )->first();
			if ( ! $field ) {
				continue;
			}

			if (
				( $field['type'] == 'url' && isset( $field['file_upload'] ) && $field['file_upload'] )
				|| $field['type'] == 'files' ) {
				if ( is_array( $answerValue ) ) {
					$finalData[ $field['id'] ] = [];
					foreach ( $answerValue as $file ) {
						$finalData[ $field['id'] ][] = $this->storeFile( $file );
					}
				} else {
					$finalData[ $field['id'] ] = $this->storeFile( $answerValue );
				}
			} else {
				// WordPress.org requires all built-in features to be fully functional without license checks.
				if ( $field['type'] == 'text' && isset( $field['generates_uuid'] ) && $field['generates_uuid'] ) {
					$finalData[ $field['id'] ] = Str::uuid();
				} else {
					if ( $field['type'] == 'text' && isset( $field['generates_auto_increment_id'] ) && $field['generates_auto_increment_id'] ) {
						$finalData[ $field['id'] ] = (string) ( $this->form['submissions_count'] + 1 );
					} else {
						$finalData[ $field['id'] ] = $answerValue;
					}
				}
			}
			// For Signature
			if (
				$field['type'] == 'signature' ) {
				$finalData[ $field['id'] ] = [$this->storeSignature( $answerValue )];
			}
		}

		return $finalData;
	}

	// This is used when updating a record, and file uploads aren't changed.
	private function isSkipForUpload( $value ): bool {
		$newPath = Str::of( self::FILE_UPLOAD_PATH )->replace( '?', $this->form['id'] );

		return Storage::exists( $newPath . '/' . $value );
	}

	/**
	 * Custom Back-end Value formatting. Use case:
	 * - File uploads (move file from tmp storage to persistent)
	 *
	 * File can have 2 formats:
	 * - file_name-{uuid}.{ext}
	 * - {uuid}
	 */
	private function storeFile( ?string $location ): ?array {
		return [
			'file_url' => $location,
			'file_name' => $location
		];
	}

	/**
	 * Save the image on the server
	 */
	function save_image( $base64_img, $title ): ?array {
		$upload = wp_upload_dir();
		if ( ! empty( $upload['error'] ) ) {
			return null;
		}

		$upload_dir = trailingslashit( $upload['basedir'] ) . 'hello2-forms/signatures';
		if ( ! wp_mkdir_p( $upload_dir ) ) {
			return null;
		}

		$index_file = $upload_dir . '/index.php';
		if ( ! file_exists( $index_file ) ) {
			file_put_contents( $index_file, "<?php\n// Silence is golden.\n" );
		}

		$img             = str_replace( 'data:image/png;base64,', '', $base64_img );
		$img             = str_replace( ' ', '+', $img );
		$decoded         = base64_decode( $img );
		$filename        = $title . '.png';
		$hashed_filename = md5( $filename . microtime() ) . '_' . $filename;

		$upload_file = file_put_contents( $upload_dir . '/' . $hashed_filename, $decoded );

		if ( $upload_file === false ) {
			return null;
		}

		return [
			'file_url'  => trailingslashit( $upload['baseurl'] ) . 'hello2-forms/signatures/' . $hashed_filename,
			'file_name' => $filename,
		];
	}

	private function storeSignature( ?string $file ): ?array {
		return $this->save_image( $file, 'signature' . wp_rand( 0, 100000 ) );
	}

	/**
	 * Adds prefill from hidden fields
	 *
	 * @param array $formData
	 *
	 * @throws \Exception
	 */
	private function addHiddenPrefills( array &$formData ): void {
		// Find hidden properties with prefilling, set values
		collect( $this->form['properties'] )->filter( function ( $property ) {
			return isset( $property['hidden'] )
			       && isset( $property['prefill'] )
			       && $property['hidden'];
		} )->each( function ( array $property ) use ( &$formData ) {
			if ( $property['type'] === 'date' && isset( $property['prefill_today'] ) && $property['prefill_today'] ) {
				$formData[ $property['id'] ] = ( new \DateTime() )->format( ( isset( $property['with_time'] ) && $property['with_time'] ) ? 'Y-m-d H:i' : 'Y-m-d' );
			} else {
				$formData[ $property['id'] ] = $property['prefill'];
			}
		} );
	}
}
