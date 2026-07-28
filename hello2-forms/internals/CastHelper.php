<?php

namespace Hello2Forms\Internals;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use CastToType;
use Hello2Forms\Architecture\Castable;

class CastHelper {
	/**
	 *
	 * @throws \Exception
	 */
	public static function cast( string $className, array $data, $dbSave = false ): array {
		$castedData = [];
		$object     = new $className();

		$model = $object->data;
		foreach ( $model as $key => $value ) {

			if ( ! array_key_exists( $key, $data ) ) {
				continue;
			}

			$type = explode( '|', $value )[0];

			$castedData[ $key ] = match ( $type ) {
				'json' => $dbSave ? json_encode( $data[ $key ] ) : json_decode( $data[ $key ], true ),
				'object' => $dbSave ? serialize( $data[ $key ] ) : unserialize( $data[ $key ] ),
				'sanitize' => wp_generate_uuid4(),
				default => CastToType::cast( $data[ $key ], $type, allow_empty: false ),
			};
		}

		return $castedData;
	}

	public static function castArray( string $className, array $array, $dbSave = false ): array {
		$castedArray = [];
		foreach ( $array as $item ) {
			$castedArray[] = self::cast( $className, $item, $dbSave );
		}

		return $castedArray;
	}
}
