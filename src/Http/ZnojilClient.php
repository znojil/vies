<?php
declare(strict_types=1);

namespace Znojil\Vies\Http;

use Psr\Http\Message;
use Znojil\Http;

final class ZnojilClient implements Client{

	private readonly Http\Client $client;

	public function __construct(
		?Http\Client $client = null
	){
		$this->client = $client ?? new Http\Client;
	}

	public function send(string $method, string|Message\UriInterface $uri, array $headers = [], mixed $data = null, array $options = []): Message\ResponseInterface{
		return $this->client->sendRequest(
			(new Http\RequestFactory)->createRequest($method, $uri, $headers, $data),
			$this->translateOptions($options)
		);
	}

	/**
	 * @param array<int|string, mixed> $options
	 * @return array<int, mixed>
	 */
	private function translateOptions(array $options): array{
		$translated = [];
		foreach($options as $k => $v){
			if(is_string($k)){
				// no Option cases defined yet; translate via Option enum once the first case exists
				throw new \Znojil\Vies\Exception\InvalidArgumentException("Unknown HTTP client option '$k'.");
			}

			$translated[$k] = $v;
		}

		return $translated;
	}

}
