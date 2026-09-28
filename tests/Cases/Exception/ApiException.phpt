<?php
declare(strict_types=1);

namespace Znojil\Vies\Tests\Cases\Exception;

use Tester\Assert;
use Znojil\Vies\Exception\ApiException;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class ApiExceptionTest extends \Tester\TestCase{

	public function testThrowOnErrorWithoutErrorPayload(): void{
		foreach([
			[],
			['valid' => true],
			['errorWrappers' => null],
			['errorWrappers' => 'MS_UNAVAILABLE']
		] as $data){
			Assert::noError(fn() => ApiException::throwOnError($data));
		}
	}

	public function testThrowOnError(): void{
		// message is left out for most error codes
		/** @var ApiException */
		$e = Assert::exception(
			fn() => ApiException::throwOnError(['actionSucceed' => false, 'errorWrappers' => [['error' => 'MS_UNAVAILABLE']]], 200, 'body'),
			ApiException::class,
			'MS_UNAVAILABLE',
			200
		);

		Assert::same(200, $e->statusCode);
		Assert::same('body', $e->responseBody);
		Assert::count(1, $e->errors);
		Assert::same('MS_UNAVAILABLE', $e->errors[0]->code);
		Assert::null($e->errors[0]->message);

		// message present
		Assert::exception(
			fn() => ApiException::throwOnError(['errorWrappers' => [['error' => 'VOW-ERR-1', 'message' => 'An unexpected error occurred.']]]),
			ApiException::class,
			'An unexpected error occurred. (VOW-ERR-1)',
			0
		);

		// VIES placeholders for "no value" count as a missing message
		Assert::exception(
			fn() => ApiException::throwOnError(['errorWrappers' => [['error' => 'TIMEOUT', 'message' => '---']]]),
			ApiException::class,
			'TIMEOUT'
		);

		// multiple errors
		/** @var ApiException */
		$e = Assert::exception(
			fn() => ApiException::throwOnError(['errorWrappers' => [['error' => 'A'], ['error' => 'B', 'message' => 'Second.']]]),
			ApiException::class,
			"A\nSecond. (B)"
		);

		Assert::count(2, $e->errors);
	}

	public function testThrowOnErrorWithUnusableErrorPayload(): void{
		foreach([
			['errorWrappers' => []],
			['errorWrappers' => ['MS_UNAVAILABLE', 42]]
		] as $data){
			/** @var ApiException */
			$e = Assert::exception(
				fn() => ApiException::throwOnError($data),
				ApiException::class,
				'Unknown error.'
			);

			Assert::same([], $e->errors);
		}
	}

}

(new ApiExceptionTest)->run();
