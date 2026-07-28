<?php

namespace Hello2Forms\Architecture;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Engine\Base;

abstract class AbstractApiController extends Base {
	protected string $resourceName;
	private string $namespace = 'hello2forms';
	protected string $version = '/v1/';

	public function __construct() {
		$this->resourceName = substr($this::class, strrpos($this::class, '\\')+1);

		add_action('rest_api_init', [$this, 'registerRoutes']);
	}

	abstract public function registerRoutes(): void;

	protected function registerRestRoute(string $route, array $args = [], bool $override = false) : bool {
		return register_rest_route( $this->namespace, $this->version . $this->resourceName . '/' .$route , $args, $override);
	}
}
