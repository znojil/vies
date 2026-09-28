<?php
declare(strict_types=1);

namespace Znojil\Vies\Tests\Fixtures;

use Znojil\Http\Message\Response;

final class ResponseFactory{

	public static function create(string $fixture, int $statusCode = 200): Response{
		if(($body = @file_get_contents($path = __DIR__ . '/data/' . $fixture . '.json')) === false){
			throw new \RuntimeException("Missing fixture '$path'.");
		}

		return new Response($statusCode, body: $body);
	}

}
