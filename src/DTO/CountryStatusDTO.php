<?php
declare(strict_types=1);

namespace Znojil\Vies\DTO;

use Znojil\Vies\Enum;

/**
 * @phpstan-type CountryStatusResponseData array{countryCode: string, availability: string}
 */
final readonly class CountryStatusDTO{

	/**
	 * @param CountryStatusResponseData $data
	 */
	public static function fromResponseData(array $data): self{
		return new self(
			Enum\Country::from($data['countryCode']),
			Enum\Availability::from($data['availability'])
		);
	}

	public function __construct(
		public Enum\Country $country,
		public Enum\Availability $availability
	){}

}
