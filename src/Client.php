<?php
declare(strict_types=1);

namespace Znojil\Vies;

final class Client{

	private const ApiUrl = 'https://ec.europa.eu/taxation_customs/vies/rest-api';

	private readonly Http\Client $httpClient;

	public function __construct(
		?Http\Client $httpClient = null
	){
		$this->httpClient = $httpClient ?? new Http\ZnojilClient;
	}

	/**
	 * @template TResponse
	 * @param Http\Request<TResponse> $request
	 * @return TResponse
	 * @throws Exception\ApiException if the API reported an error with HTTP 2xx
	 * @throws Exception\ClientException on 4xx, with ApiException as previous when the body carries an error payload
	 * @throws Exception\ServerException on 5xx, with ApiException as previous when the body carries an error payload
	 * @throws Exception\ResponseException on any other non-2xx response
	 * @throws Exception\JsonException if a successful response body is not valid JSON
	 * @throws Exception\JsonResponseException if a successful response body is not a JSON object or array
	 */
	public function send(Http\Request $request): mixed{
		$uri = new \Znojil\Http\Message\Uri(self::ApiUrl . '/' . ltrim($request->getUrn(), '/'));

		$response = $this->httpClient->send($request->getMethod(), $uri, $request->getHeaders(), $request->getData(), $request->getHttpClientOptions());

		$statusCode = $response->getStatusCode();
		$body = (string) $response->getBody();

		if($statusCode < 200 || $statusCode >= 300){
			$apiException = null;
			try{
				$data = Internal\Json::decode($body);
				if(is_array($data)){
					Exception\ApiException::throwOnError($data, $statusCode, $body);
				}
			}catch(Exception\ApiException $e){
				$apiException = $e;
			}catch(Exception\JsonException){
				// non-JSON error body (proxy, outage) — keep the raw body message
			}

			$message = $apiException?->getMessage() ?? "Request failed. Result:\n" . $body;

			throw match(true){
				$statusCode >= 500 => new Exception\ServerException($message, $statusCode, $body, $apiException),
				$statusCode >= 400 => new Exception\ClientException($message, $statusCode, $body, $apiException),
				default => new Exception\ResponseException($message, $statusCode, $body, $apiException)
			};
		}

		return $request->createResponse($response);
	}

}
