<?php
/**
 * Hello2Forms
 *
 * @package   Hello2Forms
 * @author    Infinite Consultancy LTD <connect@clinicsoftware.com>
 * @copyright 2024 Infinite Consultancy LTD
 * @license   GPL 2.0+
 * @link      https://clinicsoftware.com
 */

namespace Hello2Forms\Internals;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Vite {
	protected $hostname = 'http://localhost'; // or internal ip
	protected $virtualIP = 'http://node.wordpress.internal'; // or internal ip
	protected $port = 3000;
	protected $entry = 'src/app.js';
	protected $out_dir = 'assets';


	public function entry( $entry ) {
		$this->entry = $entry;
		return $this;
	}

	public function hostname( $hostname ) {
		$this->hostname = $hostname;
		return $this;
	}

	public function virtualIP( $virtualIP ) {
		$this->virtualIP = $virtualIP;
		return $this;
	}

	public function port( $port ) {
		$this->port = $port;
		return $this;
	}

	public function outDir( $dir ) {
		$this->out_dir = $dir;
		return $this;
	}


	public static function enqueueApp( string $handle, bool $admin = true ): void {
		$settings = get_option( 'hello2forms_settings' ) ?: [];
		$vapor_url = ( $settings['VITE_VAPOR_ASSET_URL'] ?? '' )
			? $settings['VITE_VAPOR_ASSET_URL']
			: plugin_dir_url( HELLO2FORMS_PLUGIN_ABSOLUTE ) . 'assets/';

		$config = [
			'VITE_VAPOR_ASSET_URL' => $vapor_url,
			'app_name'             => $settings['app_name'] ?? '',
			'locale'               => 'en',
			'locales'              => [ 'en' => 'EN' ],
			'hCaptchaSiteKey'      => $settings['hCaptchaSiteKey'] ?? '',
			'nonce'                => wp_create_nonce( 'wp_rest' ),
			'wordpress_location'   => site_url(),
			'is_logged_in'         => is_user_logged_in() ? '1' : '0',
			'integrations'         => [
				'google' => [
					'calendar' => [
						'client_id'     => $settings['google_calendar_api_client_id'] ?? '',
						'client_secret' => $settings['google_calendar_api_secret'] ?? '',
					],
				],
			],
		];

		$vite = new self();
		$vite->outDir( dirname( HELLO2FORMS_PLUGIN_ABSOLUTE ) . '/assets/client/' );
		$vite->entry( 'src/app.js' );
		$vite->enqueue( $handle, $config, $admin );
	}

	/**
	 * Register and enqueue all Vite assets using WordPress APIs.
	 *
	 * @param string $handle Script/Style handle to use.
	 * @param array  $config Optional data to expose as window.config via wp_localize_script().
	 * @param bool   $admin  Whether to use admin hooks (true) or front-end hooks (false).
	 */
	public function enqueue( string $handle, array $config = [], bool $admin = true ): void {
		$url = $this->isDev()
			? $this->host() . '/' . $this->entry
			: $this->jsUrl();

		if ( ! $url ) {
			return;
		}

		wp_register_script( $handle, $url, [], HELLO2FORMS_VERSION, true );
		wp_enqueue_script( $handle );

		add_filter( 'wp_script_attributes', function ( $attrs ) use ( $handle ) {
			if ( ( $attrs['id'] ?? '' ) !== $handle . '-js' ) {
				return $attrs;
			}
			$attrs['type'] = 'module';
			$attrs['crossorigin'] = 'anonymous';
			return $attrs;
		} );

		if ( ! empty( $config ) ) {
			wp_localize_script( $handle, 'hello2formsConfig', $config );
		}

		foreach ( $this->cssUrls() as $css_url ) {
			$css_handle = $handle . '-css-' . md5( $css_url );
			wp_register_style( $css_handle, $css_url, [], HELLO2FORMS_VERSION );
			wp_enqueue_style( $css_handle );
		}

		$this->enqueuePreloads( $handle, $admin );
		$this->enqueueLegacy( $handle, $admin );
	}

	protected function enqueuePreloads( string $handle, bool $admin ): void {
		$urls = $this->importsUrls( $this->entry );
		if ( empty( $urls ) ) {
			return;
		}

		$head_action = $admin ? 'admin_head' : 'wp_head';
		add_action( $head_action, function () use ( $urls ) {
			foreach ( $urls as $url ) {
				printf( '<link rel="modulepreload" href="%s">' . "\n", esc_url( $url ) );
			}
		}, 1 );
	}

	protected function enqueueLegacy( string $handle, bool $admin ): void {
		if ( $this->isDev() ) {
			return;
		}

		$legacy_url = $this->assetUrl( str_replace( '.js', '-legacy.js', $this->entry ) );
		$polyfill_url = $this->assetUrl( 'vite/legacy-polyfills' );
		if ( ! $polyfill_url ) {
			$polyfill_url = $this->assetUrl( '../vite/legacy-polyfills' );
		}
		if ( ! $legacy_url || ! $polyfill_url ) {
			return;
		}

		$detect_handle = $handle . '-legacy-detect';
		wp_register_script( $detect_handle, false, [], HELLO2FORMS_VERSION, true );
		wp_enqueue_script( $detect_handle );

		wp_add_inline_script(
			$detect_handle,
			'!function(){var e=document,t=e.createElement("script");if(!("noModule"in t)&&"onbeforeload"in t){var n=!1;e.addEventListener("beforeload",(function(e){if(e.target===t)n=!0;else if(!e.target.hasAttribute("nomodule")||!n)return;e.preventDefault()}),!0),t.type="module",t.src=".",e.head.appendChild(t),t.remove()}}();',
			'before'
		);

		$polyfill_handle = $handle . '-legacy-polyfill';
		wp_register_script( $polyfill_handle, $polyfill_url, [ $detect_handle ], HELLO2FORMS_VERSION, true );
		wp_enqueue_script( $polyfill_handle );

		$entry_handle = $handle . '-legacy-entry';
		wp_register_script( $entry_handle, false, [ $polyfill_handle ], HELLO2FORMS_VERSION, true );
		wp_enqueue_script( $entry_handle );

		wp_add_inline_script(
			$entry_handle,
			'System.import(document.getElementById("vite-legacy-entry").getAttribute("data-src"))',
			'after'
		);

		add_filter( 'wp_script_attributes', function ( $attrs ) use ( $polyfill_handle, $entry_handle ) {
			$id = $attrs['id'] ?? '';
			if ( $id === $polyfill_handle . '-js' || $id === $entry_handle . '-js' ) {
				$attrs['nomodule'] = true;
			}
			return $attrs;
		} );

		$entry_data_handle = $handle . '-legacy-entry-data';
		wp_register_script( $entry_data_handle, false, [], HELLO2FORMS_VERSION, false );
		wp_enqueue_script( $entry_data_handle );
		wp_add_inline_script(
			$entry_data_handle,
			'var viteLegacyEntry = ' . wp_json_encode( $legacy_url ) . ';',
			'after'
		);

		$footer_action = $admin ? 'admin_print_footer_scripts' : 'wp_print_footer_scripts';
		add_action( $footer_action, function () use ( $legacy_url ) {
			wp_print_inline_script_tag(
				'',
				[
					'type'     => 'text/template',
					'id'       => 'vite-legacy-entry',
					'data-src' => $legacy_url,
				]
			);
		}, 1 );
	}

	public function jsUrl() {
		return $this->assetUrl( $this->entry );
	}

	public function cssUrls() {
		return $this->assetsUrls( $this->entry, 'css' );
	}

	public function assetUrl( $entry ) {
		$manifest = $this->manifest();

		if ( ! isset( $manifest[ $entry ] ) ) {
			return '';
		}

		return plugin_dir_url( $this->out_dir ) . 'client/' . ( $manifest[ $entry ]['file'] );
	}

	public function assetsUrls( $entry, $path = 'assets' ) {
		$urls = [];
		$entries = [];
		$manifest = $this->manifest();
		if ( ! empty( $manifest ) ) {
			foreach ( $manifest as $entry ) {
				if ( isset( $entry[ $path ] ) ) {
					$entries[] = $entry;
				}
			}
			foreach ( $entries as $entry ) {
				$url = plugin_dir_url( $this->out_dir )
				       . 'client/' . $entry[ $path ][0];
				if ( ! in_array( $url, $urls ) ) {
					$urls[] = $url;
				}
			}
		}
		return $urls;
	}

	public function importsUrls( $entry ) {
		$urls = [];
		$manifest = $this->manifest();
		if ( ! empty( $manifest[ $entry ]['imports'] ) ) {
			foreach ( $manifest[ $entry ]['imports'] as $imports ) {
				$urls[] = plugin_dir_url( __FILE__ )
				          . $this->out_dir
				          . '/' . $manifest[ $imports ]['file'];
			}
		}

		return $urls;
	}

	protected function isDev() {
		return WP_DEBUG && $this->entryExists();
	}

	protected function host() {
		return $this->hostname . ':' . $this->port;
	}

	protected function virtualHost() {
		return $this->virtualIP . ':' . $this->port;
	}

	protected function manifest() {
		$path = $this->out_dir . '/manifest.json';
		return file_exists( $path )
			? json_decode( file_get_contents( $path ), true )
			: [];
	}

	// This method is very useful for the local server
	// if we try to access it, and by any means, didn't started Vite yet
	// it will fallback to load the production files from manifest
	// so you still navigate your site as you intended
	protected function entryExists() {
		static $exists = null;
		if ( $exists !== null ) {
			return $exists;
		}
		$link = $this->host() . '/' . $this->entry;

		$server_name = isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '';
		if ( false !== strpos( $server_name, '.lndo.site' ) ||
		    false !== strpos( $server_name, 'localhost' ) ||
		    false !== strpos( $server_name, 'ngrok' ) ||
		    false !== strpos( $server_name, '0.0.0.0' ) ||
		    false !== strpos( $server_name, '127.0.0.1' ) ) {
			$link = $this->virtualHost() . '/' . $this->entry;
		}

		$response = wp_remote_head( $link, array( 'timeout' => 5 ) );
		return $exists = ! is_wp_error( $response );
	}
}
