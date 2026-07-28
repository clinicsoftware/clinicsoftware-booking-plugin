<?php

namespace Hello2Forms\Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Architecture\Repository;

class UserRepository extends Repository {
	function findById(int $id): array|null|object {

		$currentUser = get_user_by('ID', $id);

		return [
			"id"                => $id,
			"name"              => $currentUser->display_name,
			"email"             => $currentUser->user_email,
			"email_verified_at" => null,
			"created_at"        => "2024-02-12T18=>21=>57.000000Z",
			"updated_at"        => "2024-02-12T18=>21=>57.000000Z",
			"stripe_id"         => null,
			"pm_type"           => null,
			"pm_last_four"      => null,
			"trial_ends_at"     => null,
			"workspaces_count"  => 1,
			"photo_url"         => get_avatar_url($currentUser->ID),
			"pivot"             => [],
			"subscriptions"     => [],
			'template_editor' => true,
		];
	}
}
