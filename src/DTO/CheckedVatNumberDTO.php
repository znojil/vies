<?php
declare(strict_types=1);

namespace Znojil\Vies\DTO;

use Znojil\Vies\Enum;
use Znojil\Vies\Internal\StringUtil;

/**
 * @phpstan-type CheckedVatNumberResponseData array{countryCode: string, vatNumber: string, requestDate: string, valid: bool, requestIdentifier?: string, name?: string, address?: string, traderName?: string, traderStreet?: string, traderPostalCode?: string, traderCity?: string, traderCompanyType?: string, traderNameMatch?: string, traderStreetMatch?: string, traderPostalCodeMatch?: string, traderCityMatch?: string, traderCompanyTypeMatch?: string}
 */
final readonly class CheckedVatNumberDTO{

	/**
	 * @param CheckedVatNumberResponseData $data
	 */
	public static function fromResponseData(array $data): self{
		return new self(
			Enum\Country::from($data['countryCode']),
			$data['vatNumber'],
			$data['valid'],
			StringUtil::normalizeNullableProperty($data['name'] ?? null),
			StringUtil::normalizeNullableProperty($data['address'] ?? null),
			StringUtil::normalizeNullableProperty($data['traderName'] ?? null),
			StringUtil::normalizeNullableProperty($data['traderStreet'] ?? null),
			StringUtil::normalizeNullableProperty($data['traderPostalCode'] ?? null),
			StringUtil::normalizeNullableProperty($data['traderCity'] ?? null),
			StringUtil::normalizeNullableProperty($data['traderCompanyType'] ?? null),
			isset($data['traderNameMatch']) ? Enum\ApproximateMatch::from($data['traderNameMatch']) : Enum\ApproximateMatch::NotProcessed,
			isset($data['traderStreetMatch']) ? Enum\ApproximateMatch::from($data['traderStreetMatch']) : Enum\ApproximateMatch::NotProcessed,
			isset($data['traderPostalCodeMatch']) ? Enum\ApproximateMatch::from($data['traderPostalCodeMatch']) : Enum\ApproximateMatch::NotProcessed,
			isset($data['traderCityMatch']) ? Enum\ApproximateMatch::from($data['traderCityMatch']) : Enum\ApproximateMatch::NotProcessed,
			isset($data['traderCompanyTypeMatch']) ? Enum\ApproximateMatch::from($data['traderCompanyTypeMatch']) : Enum\ApproximateMatch::NotProcessed,
			StringUtil::normalizeNullableProperty($data['requestIdentifier'] ?? null),
			new \DateTimeImmutable($data['requestDate'])
		);
	}

	public function __construct(
		public Enum\Country $country,
		public string $vatNumber,
		public bool $valid,
		public ?string $name,
		public ?string $address,
		public ?string $traderName,
		public ?string $traderStreet,
		public ?string $traderPostalCode,
		public ?string $traderCity,
		public ?string $traderCompanyType,
		public Enum\ApproximateMatch $traderNameMatch,
		public Enum\ApproximateMatch $traderStreetMatch,
		public Enum\ApproximateMatch $traderPostalCodeMatch,
		public Enum\ApproximateMatch $traderCityMatch,
		public Enum\ApproximateMatch $traderCompanyTypeMatch,
		public ?string $requestIdentifier,
		public \DateTimeImmutable $requestDate
	){}

	/**
	 * @return list<string>
	 */
	public function getAddressLines(): array{
		return $this->address !== null
			? array_values(array_filter(
				array_map(trim(...), preg_split('~\R~u', $this->address) ?: []),
				static fn(string $line): bool => $line !== ''
			))
			: [];
	}

}
