<?php
declare(strict_types=1);

namespace Znojil\Vies\Tests\Cases\Request;

use Tester\Assert;
use Znojil\Vies\Exception;
use Znojil\Vies\Tests\Fixtures\TestableBaseRequest;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class BaseRequestTest extends \Tester\TestCase{

	public function testDefaults(): void{
		$request = new TestableBaseRequest;

		Assert::same('GET', $request->getMethod());
		Assert::same(['Accept' => 'application/json'], $request->getHeaders());
		Assert::same([], $request->getHttpClientOptions());
	}

	public function testBuildData(): void{
		Assert::same([
			'string' => 'String',
			'int' => 123,
			'float' => 123.45,
			'bool' => true,
			'false' => false,
			'zero' => 0,
			'empty_string' => ''
		], (new TestableBaseRequest)->getData());
	}

	public function testParseJsonResponseBody(): void{
		Assert::same(['valid' => true], (new TestableBaseRequest)->parseJsonResponseBody('{"valid":true}'));
	}

	public function testParseJsonResponseBodyThrows(): void{
		$request = new TestableBaseRequest;

		/** @var Exception\JsonResponseException */
		$e = Assert::exception(function() use ($request): void{
			$request->parseJsonResponseBody('null');
		}, Exception\JsonResponseException::class, 'Expected JSON object or array in response body.', 0);
		Assert::same('null', $e->responseBody);

		// malformed
		Assert::exception(fn () => $request->parseJsonResponseBody('{'), Exception\JsonException::class);

		// VIES reports business errors with HTTP 200
		$body = '{"actionSucceed":false,"errorWrappers":[{"error":"INVALID_INPUT"}]}';
		/** @var Exception\ApiException */
		$e = Assert::exception(function() use ($request, $body): void{
			$request->parseJsonResponseBody($body);
		}, Exception\ApiException::class, 'INVALID_INPUT', 0);
		Assert::same($body, $e->responseBody);
	}

}

(new BaseRequestTest)->run();
