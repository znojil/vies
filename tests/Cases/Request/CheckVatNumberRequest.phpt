<?php
declare(strict_types=1);

namespace Znojil\Vies\Tests\Cases\Request;

use Tester\Assert;
use Znojil\Vies\Enum;
use Znojil\Vies\Request\CheckVatNumberRequest;
use Znojil\Vies\Tests\Fixtures\ResponseFactory;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class CheckVatNumberRequestTest extends \Tester\TestCase{

	public function testConfiguration(): void{
		$request = new CheckVatNumberRequest(
			Enum\Country::CzechRepublic,
			'12345674',
			Enum\Country::Slovakia,
			'1234567890',
			'Example s.r.o.',
			'Příkladná 1234/5',
			'110 00',
			'Praha',
			'a.s.'
		);
		Assert::same('POST', $request->getMethod());
		Assert::same('check-vat-number', $request->getUrn());
		Assert::same(['Accept' => 'application/json', 'Content-Type' => 'application/json'], $request->getHeaders());
		Assert::same([
			'countryCode' => 'CZ',
			'vatNumber' => '12345674',
			'requesterMemberStateCode' => 'SK',
			'requesterNumber' => '1234567890',
			'traderName' => 'Example s.r.o.',
			'traderStreet' => 'Příkladná 1234/5',
			'traderPostalCode' => '110 00',
			'traderCity' => 'Praha',
			'traderCompanyType' => 'a.s.'
		], $request->getData());

		// default properties
		Assert::same([
			'countryCode' => 'CZ',
			'vatNumber' => '12345674'
		], (new CheckVatNumberRequest(Enum\Country::CzechRepublic, '12345674'))->getData());
	}

	public function testCreateResponse(): void{
		$result = (new CheckVatNumberRequest(Enum\Country::CzechRepublic, '12345674'))
			->createResponse(ResponseFactory::create('check-vat-number/valid'));
		Assert::same(Enum\Country::CzechRepublic, $result->country);
		Assert::same('12345674', $result->vatNumber);
		Assert::true($result->valid);
		Assert::same('Example s.r.o.', $result->name);
		Assert::same("Příkladná 1234/5\nPRAHA 1 - NOVÉ MĚSTO\n110 00  PRAHA 1", $result->address);
		Assert::same(['Příkladná 1234/5', 'PRAHA 1 - NOVÉ MĚSTO', '110 00  PRAHA 1'], $result->getAddressLines());
		Assert::null($result->requestIdentifier); // empty string without a requester
		Assert::same('2026-09-13T19:26:25.502+00:00', $result->requestDate->format('Y-m-d\TH:i:s.vP'));
		// trader data not submitted, VIES answers with its '---' placeholder
		Assert::null($result->traderName);
		Assert::null($result->traderStreet);
		Assert::null($result->traderPostalCode);
		Assert::null($result->traderCity);
		Assert::null($result->traderCompanyType);
		Assert::same(Enum\ApproximateMatch::NotProcessed, $result->traderNameMatch);
		Assert::same(Enum\ApproximateMatch::NotProcessed, $result->traderStreetMatch);
		Assert::same(Enum\ApproximateMatch::NotProcessed, $result->traderPostalCodeMatch);
		Assert::same(Enum\ApproximateMatch::NotProcessed, $result->traderCityMatch);
		Assert::same(Enum\ApproximateMatch::NotProcessed, $result->traderCompanyTypeMatch);

		// invalid number
		$result = (new CheckVatNumberRequest(Enum\Country::CzechRepublic, '99999992'))
			->createResponse(ResponseFactory::create('check-vat-number/invalid'));
		Assert::false($result->valid);
		Assert::null($result->name);
		Assert::null($result->address);
		Assert::same([], $result->getAddressLines());
	}

}

(new CheckVatNumberRequestTest)->run();
