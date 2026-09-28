<?php
declare(strict_types=1);

namespace Znojil\Vies\Internal;

use Znojil\Vies\Exception\JsonException;

final class Json{

	/**
	 * @throws JsonException
	 */
	public static function decode(string $json): mixed{
		try{
			return json_decode($json, true, flags: JSON_THROW_ON_ERROR);
		}catch(\JsonException $e){
			throw new JsonException($e->getMessage(), $e->getCode(), $e);
		}
	}

	/**
	 * @throws JsonException
	 */
	public static function encode(mixed $value): string{
		try{
			return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		}catch(\JsonException $e){
			throw new JsonException($e->getMessage(), $e->getCode(), $e);
		}
	}

}
