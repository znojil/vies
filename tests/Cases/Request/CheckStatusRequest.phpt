<?php
declare(strict_types=1);

namespace Znojil\Vies\Tests\Cases\Request;

use Tester\Assert;
use Znojil\Vies\Enum\Availability;
use Znojil\Vies\Request\CheckStatusRequest;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class CheckStatusRequestTest extends \Tester\TestCase{

	public function testConfiguration(): void{
		$request = new CheckStatusRequest;

		Assert::same('GET', $request->getMethod());
		Assert::same('check-status', $request->getUrn());
		Assert::same(['Accept' => 'application/json'], $request->getHeaders());
	}

	public function testCreateResponse(): void{
		$result = (new CheckStatusRequest)->createResponse(\Znojil\Vies\Tests\Fixtures\ResponseFactory::create('check-status/status'));

		Assert::true($result->vow->available);
		Assert::count(3, $result->countries);
		/** @var array<string, Availability> */
		$availability = [];
		foreach($result->countries as $country){
			$availability[$country->country->value] = $country->availability;
		}
		Assert::same(Availability::Available, $availability['CZ']);
		Assert::same(Availability::Unavailable, $availability['SK']);
		Assert::same(Availability::MonitoringDisabled, $availability['DE']);
	}

}

(new CheckStatusRequestTest)->run();
