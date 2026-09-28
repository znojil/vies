<?php
declare(strict_types=1);

namespace Znojil\Vies\Tests\Cases\Request;

use Tester\Assert;
use Znojil\Vies\Enum;
use Znojil\Vies\Request\CheckVatNumberTestRequest;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class CheckVatNumberTestRequestTest extends \Tester\TestCase{

	public function testConfiguration(): void{
		$request = new CheckVatNumberTestRequest(Enum\Country::CzechRepublic, Enum\TestVatNumber::MsUnavailable);

		Assert::same('POST', $request->getMethod());
		Assert::same('check-vat-test-service', $request->getUrn());
		Assert::same(['Accept' => 'application/json', 'Content-Type' => 'application/json'], $request->getHeaders());
		Assert::same(['countryCode' => 'CZ', 'vatNumber' => '301'], $request->getData());
	}

	public function testCreateResponse(): void{
		// the test service leaves out all trader data
		$result = (new CheckVatNumberTestRequest(Enum\Country::CzechRepublic, Enum\TestVatNumber::Valid))
			->createResponse(\Znojil\Vies\Tests\Fixtures\ResponseFactory::create('check-vat-test-service/valid'));

		Assert::true($result->valid);
		Assert::same('100', $result->vatNumber);
		Assert::same('John Doe', $result->name);
		Assert::same('123 Main St, Anytown, UK', $result->address);
		Assert::null($result->traderName);
		Assert::null($result->traderCompanyType);
		Assert::same(Enum\ApproximateMatch::NotProcessed, $result->traderNameMatch);
		Assert::same(Enum\ApproximateMatch::NotProcessed, $result->traderCompanyTypeMatch);
	}

}

(new CheckVatNumberTestRequestTest)->run();
