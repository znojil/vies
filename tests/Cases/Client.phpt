<?php
declare(strict_types=1);

namespace Znojil\Vies\Tests\Cases;

use Tester\Assert;
use Znojil\Http\Message\Response;
use Znojil\Vies;

require __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
final class ClientTest extends \Tester\TestCase{

	protected function tearDown(): void{
		parent::tearDown();
		\Mockery::close();
	}

	public function testSend(): void{
		$httpClient = \Mockery::mock(Vies\Http\Client::class);
		$httpClient->shouldReceive('send')
			->once()
			->with(
				'POST',
				\Mockery::on(fn(\Psr\Http\Message\UriInterface $uri): bool => (string) $uri === 'https://ec.europa.eu/taxation_customs/vies/rest-api/check-vat-number'),
				['Accept' => 'application/json', 'Content-Type' => 'application/json'],
				['countryCode' => 'CZ', 'vatNumber' => '12345674'],
				[]
			)
			->andReturn(Vies\Tests\Fixtures\ResponseFactory::create('check-vat-number/valid'));

		$result = (new Vies\Client($httpClient))
			->send(new Vies\Request\CheckVatNumberRequest(Vies\Enum\Country::CzechRepublic, '12345674'));

		Assert::true($result->valid);
		Assert::same('Example s.r.o.', $result->name);
	}

	public function testSendThrowsApiException(): void{
		// VIES reports business errors with HTTP 200, detected while parsing the body
		/** @var Vies\Exception\ApiException */
		$e = Assert::exception(
			fn() => $this->getClient(new Response(body: '{"actionSucceed":false,"errorWrappers":[{"error": "MS_UNAVAILABLE"}]}'))
				->send(new Vies\Request\CheckVatNumberRequest(Vies\Enum\Country::CzechRepublic, '12345674')),
			Vies\Exception\ApiException::class,
			'MS_UNAVAILABLE',
			0
		);

		Assert::count(1, $e->errors);
		Assert::same('MS_UNAVAILABLE', $e->errors[0]->code);
	}

	public function testSendThrows(): void{
		// error payload with a 5xx status
		/** @var Vies\Exception\ServerException */
		$e = Assert::exception(
			fn() => $this->getClient(new Response(500, body: '{"actionSucceed":false,"errorWrappers":[{"error":"VOW-ERR-1","message":"An unexpected error occurred. Please retry later or contact the support team."}]}'))
				->send(new Vies\Request\CheckStatusRequest),
			Vies\Exception\ServerException::class,
			'An unexpected error occurred. Please retry later or contact the support team. (VOW-ERR-1)',
			500
		);
		/** @var Vies\Exception\ApiException */
		$previous = $e->getPrevious();
		Assert::type(Vies\Exception\ApiException::class, $previous);
		Assert::same(500, $previous->statusCode);
		Assert::same('VOW-ERR-1', $previous->errors[0]->code);

		// response code mapping
		foreach([
			[199, Vies\Exception\ResponseException::class],
			[302, Vies\Exception\ResponseException::class],
			[400, Vies\Exception\ClientException::class],
			[499, Vies\Exception\ClientException::class],
			[500, Vies\Exception\ServerException::class],
			[503, Vies\Exception\ServerException::class]
		] as [$statusCode, $exception]){
			Assert::exception(
				fn() => $this->getClient(new Response($statusCode))
					->send(new Vies\Request\CheckStatusRequest),
				$exception,
				code: $statusCode
			);
		}

		// response body is not valid JSON
		/** @var Vies\Exception\ServerException */
		$e = Assert::exception(
			fn() => $this->getClient(new Response(502, body: '<html>Bad Gateway</html>'))
				->send(new Vies\Request\CheckStatusRequest),
			Vies\Exception\ServerException::class,
			"Request failed. Result:\n<html>Bad Gateway</html>"
		);
		Assert::null($e->getPrevious());
		Assert::same('<html>Bad Gateway</html>', $e->responseBody);

		// malformed (mistyped) error payload must not mask the HTTP error
		/** @var Vies\Exception\ServerException */
		$e = Assert::exception(
			fn() => $this->getClient(new Response(500, body: '{"actionSucceed":false,"errorWrappers":[{"error":42}]}'))
				->send(new Vies\Request\CheckStatusRequest),
			Vies\Exception\ServerException::class,
			"Request failed. Result:\n{\"actionSucceed\":false,\"errorWrappers\":[{\"error\":42}]}",
			500
		);
		Assert::null($e->getPrevious());
	}

	public function testSendThrowsUnexpectedResponseException(): void{
		// the body does not match the expected shape, the original error is kept as previous
		foreach([
			[
				new Vies\Request\CheckStatusRequest,
				'{"vow":{"available":true},"countries":[{"countryCode":"AL","availability":"Available"}]}', // unknown country
				\ValueError::class
			],
			[
				new Vies\Request\CheckStatusRequest,
				'{"vow":{"available":"yes"},"countries":[]}', // mistyped field
				\TypeError::class
			],
			[
				new Vies\Request\CheckVatNumberRequest(Vies\Enum\Country::CzechRepublic, '12345674'),
				'{"countryCode":"CZ","vatNumber":"12345674","requestDate":"not a date","valid":true}', // invalid date
				\Exception::class
			]
		] as [$request, $body, $previous]){
			/** @var Vies\Exception\UnexpectedResponseException */
			$e = Assert::exception(
				fn() => $this->getClient(new Response(body: $body))->send($request),
				Vies\Exception\UnexpectedResponseException::class
			);
			Assert::type($previous, $e->getPrevious());
			Assert::same($body, $e->responseBody);
		}
	}

	private function getClient(Response $response): Vies\Client{
		$httpClient = \Mockery::mock(Vies\Http\Client::class);
		$httpClient->shouldReceive('send')
			->once()
			->andReturn($response);

		return new Vies\Client($httpClient);
	}

}

(new ClientTest)->run();
