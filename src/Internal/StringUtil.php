<?php
declare(strict_types=1);

namespace Znojil\Vies\Internal;

final class StringUtil{

	/**
	 * VIES uses '---' for "no value"
	 */
	public static function normalizeNullableProperty(?string $value): ?string{
		if($value !== null){
			$value = trim($value);

			return ($value === '' || $value === '---') ? null : $value;
		}

		return null;
	}

}
