<?php
declare(strict_types=1);

namespace Znojil\Vies\DTO;

/**
 * @phpstan-import-type CountryStatusResponseData from CountryStatusDTO
 * @phpstan-import-type VowStatusResponseData from VowStatusDTO
 * @phpstan-type StatusInformationResponseData array{vow: VowStatusResponseData, countries: list<CountryStatusResponseData>}
 */
final readonly class StatusInformationDTO{

	/**
	 * @param StatusInformationResponseData $data
	 */
	public static function fromResponseData(array $data): self{
		return new self(
			VowStatusDTO::fromResponseData($data['vow']),
			array_map(CountryStatusDTO::fromResponseData(...), $data['countries'])
		);
	}

	/**
	 * @param list<CountryStatusDTO> $countries
	 */
	public function __construct(
		public VowStatusDTO $vow,
		public array $countries
	){}

}
