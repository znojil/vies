<?php
declare(strict_types=1);

namespace Znojil\Vies\Request;

use Znojil\Vies\DTO\CheckedVatNumberDTO;
use Znojil\Vies\Enum\Country;

/**
 * @extends BaseRequest<CheckedVatNumberDTO>
 *
 * @phpstan-import-type CheckedVatNumberResponseData from CheckedVatNumberDTO
 */
final class CheckVatNumberRequest extends BaseRequest{

	public function __construct(
		private readonly Country $country,
		private readonly string $vatNumber,
		private readonly ?Country $requesterCountry = null,
		private readonly ?string $requesterVatNumber = null,
		private readonly ?string $traderName = null,
		private readonly ?string $traderStreet = null,
		private readonly ?string $traderPostalCode = null,
		private readonly ?string $traderCity = null,
		private readonly ?string $traderCompanyType = null
	){}

	public function getMethod(): string{
		return 'POST';
	}

	public function getUrn(): string{
		return 'check-vat-number';
	}

	public function getHeaders(): array{
		return parent::getHeaders() + ['Content-Type' => 'application/json'];
	}

	public function getData(): array{
		return $this->buildData([
			'countryCode' => $this->country->value,
			'vatNumber' => $this->vatNumber,
			'requesterMemberStateCode' => $this->requesterCountry?->value,
			'requesterNumber' => $this->requesterVatNumber,
			'traderName' => $this->traderName,
			'traderStreet' => $this->traderStreet,
			'traderPostalCode' => $this->traderPostalCode,
			'traderCity' => $this->traderCity,
			'traderCompanyType' => $this->traderCompanyType
		]);
	}

	public function createResponse(\Psr\Http\Message\ResponseInterface $httpResponse): CheckedVatNumberDTO{
		/** @var CheckedVatNumberResponseData */
		$data = $this->parseJsonResponseBody((string) $httpResponse->getBody());

		return CheckedVatNumberDTO::fromResponseData($data);
	}

}
