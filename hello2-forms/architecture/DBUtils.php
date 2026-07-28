<?php

namespace Hello2Forms\Architecture;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait DBUtils {
	protected \wpdb $db;
	private string $TABLE_PREFIX = 'hello2_forms_';

	public function __construct() {
		global $wpdb;
		$this->db = $wpdb;
	}

	public function maybeCreateTable( string $tableName, string $sql ): void {
		$table = $this->realTable( $tableName );
		$sql = $this->realTable( $sql );

		$exists = $this->db->get_var( $this->db->prepare( "SHOW TABLES LIKE %s", $table ) );

		if ( ! $exists ) {
			$this->db->query( $sql );
		}
	}

	/**
	 * Returns the real table name with the user's WordPress instance prefix.
	 * This way we get autosuggestions from the PHPStorm.
	 * @param string $table
	 *
	 * @return string
	 */
	public function realTable( string $table ): string {
		if ($this->db->prefix === 'wp_') return $table;

		$replaceWordPressPrefixes = str_replace( 'wp_', $this->db->prefix, $table );

		return str_replace( ['wp_hello2_forms_'], $this->db->prefix . $this->TABLE_PREFIX, $replaceWordPressPrefixes );
	}

	public function prepare( string $sql, mixed ...$args ): string|false {
		if ( empty( $args ) ) {
			return $sql;
		}
		return $this->db->prepare( $sql, ...$args );
	}

	public function getResults( $sql, $mode = ARRAY_A ): array|object|null|bool {
		$prepared = is_string( $sql ) ? $this->prepare( $sql ) : $sql;
		return $this->db->get_results( $this->realTable( $prepared ), $mode );
	}

	public function getRow( $sql, $mode = ARRAY_A ): array|object|null|bool {
		$prepared = is_string( $sql ) ? $this->prepare( $sql ) : $sql;
		return $this->db->get_row( $this->realTable( $prepared ), $mode );
	}

	public function getVar( $sql ): mixed {
		$prepared = is_string( $sql ) ? $this->prepare( $sql ) : $sql;
		return $this->db->get_var( $this->realTable( $prepared ) );
	}

	public function insert( string $table, array $data, ?array $format = null ): array|object|null|bool {
		return $this->db->insert( $this->realTable( $table ), $data, $format );
	}

	public function delete( string $table, array $where, ?array $format = null ): int|false {
		return $this->db->delete( $this->realTable( $table ), $where, $format );
	}

	public function update( string $table, array $data, array $where, ?array $format = null, ?array $where_format = null ): int|false {
		return $this->db->update( $this->realTable( $table ), $data, $where, $format, $where_format );
	}
}
