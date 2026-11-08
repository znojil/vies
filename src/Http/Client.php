<?php
declare(strict_types=1);

namespace Znojil\Vies\Http;

use Psr\Http\Message;

interface Client{

	/**
	 * @param array<string, string|string[]> $headers
	 * @param mixed $data request content: an array is JSON-encoded when the Content-Type header is JSON and form-encoded otherwise (an empty array then means no content), null means no content
	 * @param array<int|string, mixed> $options request options: string keys are transport-agnostic options defined by this library — every implementation must honor them and reject any other string key with an exception; int keys are raw CURLOPT_* constants — non-cURL implementations must reject them with an exception rather than silently ignore them
	 * @return Message\ResponseInterface the response for any status code — the library turns error statuses (4xx, 5xx) into its own exceptions, so they must be returned rather than thrown
	 */
	function send(string $method, string|Message\UriInterface $uri, array $headers = [], mixed $data = null, array $options = []): Message\ResponseInterface;

}
