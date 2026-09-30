<?php
declare(strict_types=1);

namespace Znojil\Vies;

final readonly class VatNumber{

	private const NumberPattern = '~^[0-9A-Z+*]{2,12}$~';

	/**
	 * @param ?Enum\Country $country required when the input has no prefix; when given, a prefix in the input must match it (for France only FR counts as a prefix, because a French number may itself start with two letters)
	 * @throws Exception\InvalidArgumentException if the country cannot be determined, the prefix does not match the given country or the number does not match the VIES format
	 */
	public static function parse(string $input, ?Enum\Country $country = null): self{
		$value = preg_replace('~[\s\h.\-]+~u', '', $input)
			?? throw new Exception\InvalidArgumentException('VAT number is not a valid UTF-8 string.');
		$value = strtoupper($value);

		preg_match('~^[A-Z]{2}~', $value, $m);
		$prefix = $m[0] ?? null;

		// a French national number may itself start with two letters, so for France only FR counts as a prefix
		if($prefix !== null && ($country !== Enum\Country::France || $prefix === 'FR')){
			$prefixCountry = ($prefix === 'GR' ? Enum\Country::Greece : Enum\Country::tryFrom($prefix))
				?? throw new Exception\InvalidArgumentException("Unknown country prefix '$prefix' in VAT number '$input'.");

			if($country !== null && $prefixCountry !== $country){
				throw new Exception\InvalidArgumentException("Country prefix '$prefixCountry->value' of VAT number '$input' does not match the expected country '$country->value'.");
			}

			$country = $prefixCountry;
			$value = substr($value, 2);
		}elseif($country === null){
			throw new Exception\InvalidArgumentException("VAT number '$input' has no country prefix, pass the country explicitly.");
		}

		if(preg_match(self::NumberPattern, $value) !== 1){
			throw new Exception\InvalidArgumentException("VAT number '$input' does not match the format accepted by VIES.");
		}

		return new self($country, $value);
	}

	private function __construct(
		public Enum\Country $country,
		public string $number
	){}

	public function __toString(): string{
		return $this->country->value . $this->number;
	}

}
