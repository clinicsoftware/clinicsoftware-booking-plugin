<?php

namespace Hello2Forms\Casts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\Castable;

class FormDataCast implements Castable {
	public array $data = [
		'id'                               => 'int',
		'title'                            => 'string',
		'slug'                             => 'string',
		'description'                      => 'string',
		'visibility'                       => 'string',
		'password'                         => 'string',
		'workspace_id'                     => 'int',
		'properties'                       => 'json',
		'notifies'                         => 'bool',
		'webhook_url'                      => 'string',
		'notification_settings'            => 'json',
		'notification_emails'              => 'string',
		'theme'                            => 'string',
		'width'                            => 'string',
		'dark_mode'                        => 'string',
		'color'                            => 'string',
		'hide_title'                       => 'bool',
		'no_branding'                      => 'bool',
		'uppercase_labels'                 => 'bool',
		'transparent_background'           => 'bool',
		'closes_at'                        => 'string',
		'closed_text'                      => 'string',
		'submit_button_text'               => 'string',
		're_fillable'                      => 'bool',
		're_fill_button_text'              => 'string',
		'submitted_text'                   => 'string',
		'notification_sender'              => 'string',
		'notification_subject'             => 'string',
		'notification_body'                => 'string',
		'notifications_include_submission' => 'bool',
		'use_captcha'                      => 'bool',
		'max_submissions_count'            => 'int',
		'max_submissions_reached_text'     => 'string',
		'editable_submissions_button_text' => 'string',
		'confetti_on_submission'           => 'bool',
		'can_be_indexed'                   => 'bool',
		'seo_meta'                         => 'json',
		'editable_submissions'             => 'bool',
		'redirect_url'                     => 'string',
		'send_submission_confirmation'     => 'bool',
		'database_fields_update'           => 'json',
		// 'custom_code'
		'cover_picture'                    => 'string',
		'logo_picture'                     => 'string',
		'updated_at'                       => 'string',
		'created_at'                       => 'string',
		'creator_id'                       => 'int',
		'submission_mode'                  => 'string',
		'submission_extra_data'            => 'json',
		'tags'                             => 'json',
	];
}
