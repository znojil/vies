<?php
declare(strict_types=1);

namespace Znojil\Vies\Tests\Cases\Http;

use Tester\Assert;
use Znojil\Vies\Http\ZnojilClient;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class ZnojilClientTest extends \Tester\TestCase{

	protected function tearDown(): void{
		parent::tearDown();
		\Mockery::close();
	}

	public function testTranslatesOptions(): void{
		$response = new \Znojil\Http\Message\Response(200);

		$httpClient = \Mockery::mock(\Znojil\Http\Client::class);
		$httpClient->shouldReceive('sendRequest')
			->once()
			->with(
				\Mockery::any(),
				[
					CURLOPT_CONNECTTIMEOUT => 10,
					CURLOPT_TIMEOUT => 5,
					CURLOPT_USERAGENT => 'Agent' // raw CURLOPT_* passes through
				]
			)
			->andReturn($response);

		Assert::same($response, (new ZnojilClient($httpClient))->send(
			'POST',
			'https://example.com/check-vat-number',
			options: [
				CURLOPT_CONNECTTIMEOUT => 10,
				'timeout' => 5,
				CURLOPT_USERAGENT => 'Agent'
			]
		));
	}

	public function testUnknownStringOptionThrows(): void{
		Assert::exception(
			fn() => (new ZnojilClient(\Mockery::mock(\Znojil\Http\Client::class)))->send('POST', 'https://example.com', options: ['tyop' => true]),
			\Znojil\Vies\Exception\InvalidArgumentException::class,
			"Unknown HTTP client option 'tyop'."
		);
	}

}

(new ZnojilClientTest)->run();
