<?php
declare(strict_types=1);

namespace Znojil\Vies\Http;

/**
 * @template-covariant T the type of response object returned by createResponse()
 */
interface Request{

	function getMethod(): string;

	function getUrn(): string;

	/**
	 * @return array<string, string|string[]>
	 */
	function getHeaders(): array;

	/**
	 * @return array<mixed>
	 */
	function getData(): array;

	/**
	 * Options for the HTTP client for this request.
	 * String keys are transport-agnostic options defined by this library,
	 * int keys are raw CURLOPT_* constants (rejected by non-cURL clients).
	 * @return array<int|string, mixed>
	 */
	function getHttpClientOptions(): array;

	/**
	 * @return T
	 */
	function createResponse(\Psr\Http\Message\ResponseInterface $httpResponse): mixed;

}
