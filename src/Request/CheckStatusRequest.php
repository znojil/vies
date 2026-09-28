<?php
declare(strict_types=1);

namespace Znojil\Vies\Request;

use Znojil\Vies\DTO\StatusInformationDTO;

/**
 * @extends BaseRequest<StatusInformationDTO>
 *
 * @phpstan-import-type StatusInformationResponseData from StatusInformationDTO
 */
final class CheckStatusRequest extends BaseRequest{

	public function getMethod(): string{
		return 'GET';
	}

	public function getUrn(): string{
		return 'check-status';
	}

	public function createResponse(\Psr\Http\Message\ResponseInterface $httpResponse): StatusInformationDTO{
		/** @var StatusInformationResponseData */
		$data = $this->parseJsonResponseBody((string) $httpResponse->getBody());

		return StatusInformationDTO::fromResponseData($data);
	}

}
