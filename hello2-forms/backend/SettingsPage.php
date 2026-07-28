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

namespace Hello2Forms\Backend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Hello2Forms\Engine\Base;
use Hello2Forms\Internals\Vite;

/**
 * Create the settings page in the backend
 */
class SettingsPage extends Base {

	private string $app_page_hook = '';

	/**
	 * Initialize the class.
	 *
	 * @return void
	 */
	public function initialize(): void {
		// Add the options page and menu item.
		\add_action( 'admin_menu', array( $this, 'addPluginAdminMenu' ) );
		add_action( 'admin_menu', array( $this, 'addSettingsPage' ) );
		add_action( 'admin_init', array( $this, 'registerSettings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAppAssets' ) );
	}

	public function addSettingsPage(): void {
		add_submenu_page(
			'hello2-forms',
			'Hello2Forms Settings',
			'Settings',
			'manage_hello2forms',
			'hello2forms-settings',
			array( $this, 'renderSettingsPage' )
		);
	}

	// Render settings page
	public function renderSettingsPage(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Hello2Forms Settings', 'hello2-forms' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'hello2forms-settings-group' ); ?>
				<?php do_settings_sections( 'hello2forms-settings' ); ?>
				<?php submit_button( esc_html__( 'Save Settings', 'hello2-forms' ) ); ?>
			</form>
		</div>
		<?php
	}

	// Register and define the settings
	public function registerSettings(): void {
		register_setting( 'hello2forms-settings-group', 'hello2forms_settings', array( $this, 'validate_settings' ) );
		add_settings_section( 'hello2forms-settings-section', 'Settings', array(
			$this,
			'settingsSectionCallback'
		), 'hello2forms-settings' );

		// Add fields
		$fields = array(
			esc_html__( 'Assets URL', 'hello2-forms' )                    => 'VITE_VAPOR_ASSET_URL',
			esc_html__( 'App Name', 'hello2-forms' )                      => 'app_name',
			esc_html__( 'hCaptcha Site Key', 'hello2-forms' )             => 'hCaptchaSiteKey',
			esc_html__( 'hCaptcha Secret', 'hello2-forms' )               => 'hCaptchaSecret',
			esc_html__( 'Google Calendar Api Secret', 'hello2-forms' )    => 'google_calendar_api_secret',
			esc_html__( 'Google Calendar Api Client ID', 'hello2-forms' ) => 'google_calendar_api_client_id',
			esc_html__( 'Chat GPT Key', 'hello2-forms' )                  => 'chat_gpt_key',
			esc_html__( 'Stripe API Key', 'hello2-forms' )                => 'stripe_api_key',
		);

		foreach ( $fields as $name => $field ) {
			add_settings_field( $field, $name, array(
				$this,
				'fieldCallback'
			), 'hello2forms-settings', 'hello2forms-settings-section', array( 'field' => $field ) );
		}
	}

	// Validate settings
	public function validate_settings( $input ): array {
		$validated_input = array();

		// Validate VITE_VAPOR_ASSET_URL
		if ( isset( $input['VITE_VAPOR_ASSET_URL'] ) ) {
			$validated_input['VITE_VAPOR_ASSET_URL'] = esc_url_raw( $input['VITE_VAPOR_ASSET_URL'] );
		}

		// Validate app_ame
		if ( isset( $input['app_name'] ) ) {
			$validated_input['app_name'] = sanitize_text_field( $input['app_name'] );
		}

		// Validate hCaptchaSiteKey
		if ( isset( $input['hCaptchaSiteKey'] ) ) {
			$validated_input['hCaptchaSiteKey'] = sanitize_text_field( $input['hCaptchaSiteKey'] );
		}

		// Validate hCaptchaSiteKey
		if ( isset( $input['hCaptchaSecret'] ) ) {
			$validated_input['hCaptchaSecret'] = sanitize_text_field( $input['hCaptchaSecret'] );
		}

		if ( isset( $input['google_calendar_api_secret'] ) ) {
			$validated_input['google_calendar_api_secret'] = sanitize_text_field( $input['google_calendar_api_secret'] );
		}

		if ( isset( $input['google_calendar_api_client_id'] ) ) {
			$validated_input['google_calendar_api_client_id'] = sanitize_text_field( $input['google_calendar_api_client_id'] );
		}

		if ( isset( $input['chat_gpt_key'] ) ) {
			$validated_input['chat_gpt_key'] = sanitize_text_field( $input['chat_gpt_key'] );
		}

		if ( isset( $input['stripe_api_key'] ) ) {
			$validated_input['stripe_api_key'] = sanitize_text_field( $input['stripe_api_key'] );
		}

		return $validated_input;
	}

	// Render individual fields
	public function fieldCallback( $args ): void {
		$options = get_option( 'hello2forms_settings' );
		$field   = $args['field'];
		$value   = isset( $options[ $field ] ) ? $options[ $field ] : '';
		echo '<input type="text" id="' . esc_attr( $field ) . '" name="hello2forms_settings[' . esc_attr( $field ) . ']" value="' . esc_attr( $value ) . '" />';
	}

	// Render settings section
	public function settingsSectionCallback(): void {
		echo '<p>' . esc_html__( 'Configure Hello2Forms settings below:', 'hello2-forms' ) . '</p>';
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function addPluginAdminMenu(): void {
		$this->app_page_hook = \add_menu_page(
			\__( 'Hello2 Forms', 'hello2-forms' ),
			HELLO2FORMS_NAME,
			'manage_options',
			'hello2-forms',
			array( $this, 'renderAppPage' ),
			plugin_dir_url( dirname( __FILE__ ) ) . 'assets/img/logo-20.jpg',
			90
		);
	}

	public function enqueueAppAssets( string $hook ): void {
		if ( $hook !== $this->app_page_hook ) {
			return;
		}

		Vite::enqueueApp( 'hello2forms-app', true );
	}

	public function renderAppPage(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		include HELLO2FORMS_PLUGIN_ROOT . 'backend/views/admin.php';
	}
}
