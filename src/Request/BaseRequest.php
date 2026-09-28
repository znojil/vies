<?php
declare(strict_types=1);

namespace Znojil\Vies\Request;

use Znojil\Vies\Exception;
use Znojil\Vies\Http\Request;

/**
 * @template T
 * @implements Request<T>
 * @link https://ec.europa.eu/assets/taxud/vow-information/swagger_publicVAT.yaml
 */
abstract class BaseRequest implements Request{

	public function getHeaders(): array{
		return ['Accept' => 'application/json'];
	}

	public function getData(): array{
		return [];
	}

	public function getHttpClientOptions(): array{
		return [];
	}

	/**
	 * @param array<string, mixed> $mapping
	 * @return array<string, mixed>
	 */
	protected function buildData(array $mapping): array{
		$data = [];
		foreach($mapping as $k => $v){
			if($v !== null){
				$data[$k] = $v;
			}
		}

		return $data;
	}

	/**
	 * @throws Exception\JsonException if malformed JSON
	 * @throws Exception\JsonResponseException if unexpected response body shape
	 * @throws Exception\ApiException
	 * @return array<int|string, mixed>
	 */
	protected function parseJsonResponseBody(string $responseBody): array{
		$data = \Znojil\Vies\Internal\Json::decode($responseBody);
		if(!is_array($data)){
			throw new Exception\JsonResponseException(
				'Expected JSON object or array in response body.',
				0,
				responseBody: $responseBody
			);
		}

		// VIES reports business errors with HTTP 200 too
		Exception\ApiException::throwOnError($data, responseBody: $responseBody);

		return $data;
	}

}
