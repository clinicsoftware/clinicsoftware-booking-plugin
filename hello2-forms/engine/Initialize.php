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

namespace Hello2Forms\Engine;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Composer\Autoload\ClassLoader;
use Exception;
use Hello2Forms\Engine;

/**
 * Hello2Forms Initializer
 */
class Initialize {

	/**
	 * List of class to initialize.
	 *
	 * @var array
	 */
	public array $classes = array();

	/**
	 * Instance of this Context.
	 *
	 * @var Context|null
	 */
	protected Context|null $content = null;

	/**
	 * Composer autoload file list.
	 *
	 * @var ClassLoader
	 */
	private ClassLoader $composer;

	/**
	 * The Constructor that load the entry classes
	 *
	 * @param ClassLoader $composer Composer autoload output.
	 *
	 * @throws Exception
	 * @since 1.0.0
	 */
	public function __construct( ClassLoader $composer ) {
		$this->content  = new Engine\Context;
		$this->composer = $composer;

		$this->get_classes( 'Internals' );
		$this->get_classes( 'Integrations' );
		$this->get_classes( 'Controller' );
		$this->get_classes( 'Validators' );

		if ( $this->content->request( 'backend' ) ) {
			$this->get_classes( 'Backend' );
		}

		$this->load_classes();
	}

	/**
	 * Initialize all the classes.
	 *
	 * @return void
	 * @throws Exception
	 * @since 1.0.0
	 */
	private function load_classes(): void {
		$this->classes = \apply_filters( 'hello2forms_classes_to_execute', $this->classes );
		foreach ( $this->classes as $class ) {
			try {
				$this->initialize_plugin_class( $class );
			} catch ( \Throwable $err ) {
				\do_action( 'hello2forms_initialize_failed', $err );

				if ( WP_DEBUG ) {
					throw new Exception( esc_html( $err->getMessage() ) );
				}
			}
		}
	}

	/**
	 * Validate the class and initialize it.
	 *
	 * @param class-string $classtovalidate Class name to validate.
	 *
	 * @return void
	 * @throws \ReflectionException
	 * @since 1.0.0
	 * @SuppressWarnings("MissingImport")
	 */
	private function initialize_plugin_class( string $classtovalidate ): void {
		$reflection = new \ReflectionClass( $classtovalidate );

		if ( $reflection->isAbstract() ) {
			return;
		}

		$temp = new $classtovalidate;

		if ( ! \method_exists( $temp, 'initialize' ) ) {
			return;
		}

		$temp->initialize();
	}

	/**
	 * Based on the folder loads the classes automatically using the Composer autoload to detect the classes of a Namespace.
	 *
	 * @param string $namespaceToFind Class name to find.
	 *
	 * @return void Return the classes.
	 * @since 1.0.0
	 */
	private function get_classes( string $namespaceToFind ): void {
		$prefix          = $this->composer->getPrefixesPsr4();
		$classmap        = $this->composer->getClassMap();
		$namespaceToFind = 'Hello2Forms\\' . $namespaceToFind;

		// In case composer has autoload optimized
		if ( isset( $classmap['Hello2Forms\\Engine\\Initialize'] ) ) {
			$classes = \array_keys( $classmap );

			foreach ( $classes as $class ) {
				if ( 0 !== \strncmp( (string) $class, $namespaceToFind, \strlen( $namespaceToFind ) ) ) {
					continue;
				}

				$this->classes[] = $class;
			}

			return;
		}

		$namespaceToFind .= '\\';

		// In case composer is not optimized
		if ( isset( $prefix[ $namespaceToFind ] ) ) {
			$folder    = $prefix[ $namespaceToFind ][0];
			$php_files = $this->scandir( $folder );
			$this->find_classes( $php_files, $folder, $namespaceToFind );

			if ( ! WP_DEBUG ) {
				\wp_die( \esc_html__( 'Hello2 Forms is on production environment with missing `composer dumpautoload -o` that will improve the performance on autoloading itself.', 'hello2-forms' ) );
			}

			return;
		}

	}

	/**
	 * Get php files inside the folder/subfolder that will be loaded.
	 * This class is used only when Composer is not optimized.
	 *
	 * @param string $folder Path.
	 * @param string $exclude_str Exclude all files whose filename contain this. Defaults to `~`.
	 *
	 * @return array List of files.
	 * @since 1.0.0
	 */
	private function scandir( string $folder, string $exclude_str = '~' ): array {
		// Also exclude these specific scandir findings.
		$blacklist = array( '..', '.', 'index.php' );
		// Scan for files.
		$temp_files = \scandir( $folder );

		$files = array();

		if ( \is_array( $temp_files ) ) {
			foreach ( $temp_files as $temp_file ) {
				// Only include filenames that DO NOT contain the excluded string and ARE NOT on the scandir result blacklist.
				if (
					\is_string( $exclude_str ) && false !== \mb_strpos( $temp_file, $exclude_str )
					|| $temp_file[0] === '.'
					|| \in_array( $temp_file, $blacklist, true )
				) {
					continue;
				}

				$files[] = $temp_file;
			}
		}

		return $files;
	}

	/**
	 * Load namespace classes by files.
	 *
	 * @param array $php_files List of files with the Class.
	 * @param string $folder Path of the folder.
	 * @param string $base Namespace base.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private function find_classes( array $php_files, string $folder, string $base ): void {
		foreach ( $php_files as $php_file ) {
			$class_name = \substr( $php_file, 0, - 4 );
			$path       = $folder . '/' . $php_file;

			if ( \is_file( $path ) ) {
				$this->classes[] = $base . $class_name;

				continue;
			}

			// Verify the Namespace level
			if ( \substr_count( $base . $class_name, '\\' ) < 2 ) {
				continue;
			}

			if ( ! \is_dir( $path ) || \strtolower( $php_file ) === $php_file ) {
				continue;
			}

			$sub_php_files = $this->scandir( $folder . '/' . $php_file );
			$this->find_classes( $sub_php_files, $folder . '/' . $php_file, $base . $php_file . '\\' );
		}
	}

}
