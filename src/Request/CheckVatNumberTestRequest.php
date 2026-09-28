<?php
declare(strict_types=1);

namespace Znojil\Vies\Request;

use Znojil\Vies\DTO\CheckedVatNumberDTO;
use Znojil\Vies\Enum\Country;
use Znojil\Vies\Enum\TestVatNumber;

/**
 * @extends BaseRequest<CheckedVatNumberDTO>
 *
 * @phpstan-import-type CheckedVatNumberResponseData from CheckedVatNumberDTO
 */
final class CheckVatNumberTestRequest extends BaseRequest{

	public function __construct(
		private readonly Country $country,
		private readonly TestVatNumber $vatNumber
	){}

	public function getMethod(): string{
		return 'POST';
	}

	public function getUrn(): string{
		return 'check-vat-test-service';
	}

	public function getHeaders(): array{
		return parent::getHeaders() + ['Content-Type' => 'application/json'];
	}

	public function getData(): array{
		return $this->buildData([
			'countryCode' => $this->country->value,
			'vatNumber' => $this->vatNumber->value
		]);
	}

	public function createResponse(\Psr\Http\Message\ResponseInterface $httpResponse): CheckedVatNumberDTO{
		/** @var CheckedVatNumberResponseData */
		$data = $this->parseJsonResponseBody((string) $httpResponse->getBody());

		return CheckedVatNumberDTO::fromResponseData($data);
	}

}
