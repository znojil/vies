<?php
declare(strict_types=1);

namespace Znojil\Vies\Tests\Cases\Internal;

use Tester\Assert;
use Znojil\Vies\Exception\JsonException;
use Znojil\Vies\Internal\Json;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class JsonTest extends \Tester\TestCase{

	public function testDecode(): void{
		foreach([
			['{"a":1}', ['a' => 1]],
			['[1,2]', [1, 2]],
			['null', null]
		] as $v){
			Assert::same($v[1], Json::decode($v[0]));
		}

		// malformed
		Assert::exception(fn() => Json::decode('{'), JsonException::class);
		Assert::exception(fn() => Json::decode(''), JsonException::class);
	}

	public function testEncode(): void{
		foreach([
			['{"url":"https://ec.europa.eu"}', ['url' => 'https://ec.europa.eu']],
			['{"traderName":"Žluťoučký kůň"}', ['traderName' => 'Žluťoučký kůň']]
		] as $v){
			Assert::same($v[0], Json::encode($v[1]));
		}

		// invalid
		Assert::exception(fn() => Json::encode(NAN), JsonException::class);
	}

}

(new JsonTest)->run();
