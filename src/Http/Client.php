<?php
declare(strict_types=1);

namespace Znojil\Vies\Http;

use Psr\Http\Message;

interface Client{

	/**
	 * @param array<string, string|string[]> $headers
	 * @param mixed $data request content: an array is encoded according to the Content-Type header (JSON or form), an empty array or null means no content
	 * @param array<int|string, mixed> $options request options: string keys are transport-agnostic options defined by this library — every implementation must honor them and reject any other string key with an exception; int keys are raw CURLOPT_* constants — non-cURL implementations must reject them with an exception rather than silently ignore them
	 */
	function send(string $method, string|Message\UriInterface $uri, array $headers = [], mixed $data = null, array $options = []): Message\ResponseInterface;

}
