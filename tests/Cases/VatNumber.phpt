<?php
declare(strict_types=1);

namespace Znojil\Vies\Tests\Cases;

use Tester\Assert;
use Znojil\Vies\Enum\Country;
use Znojil\Vies\VatNumber;

require __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
final class VatNumberTest extends \Tester\TestCase{

	public function testParse(): void{
		foreach([
			['CZ12345674', null, Country::CzechRepublic, '12345674'],
			['cz 123 456 74', null, Country::CzechRepublic, '12345674'],
			['CZ-1234-5674', null, Country::CzechRepublic, '12345674'],
			["CZ\u{00A0}123\u{00A0}45674", null, Country::CzechRepublic, '12345674'], // non-breaking spaces
			['BE 0123.456.789', null, Country::Belgium, '0123456789'],
			['ATU12345678', null, Country::Austria, 'U12345678'],
			['IE1+34567T', null, Country::Ireland, '1+34567T'],
			['XI123456789', null, Country::NorthernIreland, '123456789'],

			// Greece
			['EL123456789', null, Country::Greece, '123456789'],
			['GR123456789', null, Country::Greece, '123456789'],
			['gr 123456789', Country::Greece, Country::Greece, '123456789'],

			// explicit country
			['12345674', Country::CzechRepublic, Country::CzechRepublic, '12345674'],
			['CZ12345674', Country::CzechRepublic, Country::CzechRepublic, '12345674'],
			['U12345678', Country::Austria, Country::Austria, 'U12345678'],

			// a French number may itself start with two letters
			['FRAT123456789', null, Country::France, 'AT123456789'],
			['FRAB123456789', Country::France, Country::France, 'AB123456789'],
			['AT123456789', Country::France, Country::France, 'AT123456789'],
			['AB123456789', Country::France, Country::France, 'AB123456789'],
			['12123456789', Country::France, Country::France, '12123456789']
		] as [$input, $country, $expectedCountry, $expectedNumber]){
			$vatNumber = VatNumber::parse($input, $country);

			Assert::same($expectedCountry, $vatNumber->country);
			Assert::same($expectedNumber, $vatNumber->number);
		}
	}

	public function testParseThrows(): void{
		foreach([
			['12345674', null, "VAT number '12345674' has no country prefix, pass the country explicitly."],
			['', null, "VAT number '' has no country prefix, pass the country explicitly."],
			['GB123456789', null, "Unknown country prefix 'GB' in VAT number 'GB123456789'."],
			['GB123456789', Country::CzechRepublic, "Unknown country prefix 'GB' in VAT number 'GB123456789'."],
			['SK1234567890', Country::CzechRepublic, "Country prefix 'SK' of VAT number 'SK1234567890' does not match the expected country 'CZ'."],
			['CZ', null, "VAT number 'CZ' does not match the format accepted by VIES."],
			['CZ2', null, "VAT number 'CZ2' does not match the format accepted by VIES."],
			['', Country::CzechRepublic, "VAT number '' does not match the format accepted by VIES."],
			['CZ1234-5674!', null, "VAT number 'CZ1234-5674!' does not match the format accepted by VIES."],
			['CZ1234567890123', null, "VAT number 'CZ1234567890123' does not match the format accepted by VIES."],
			["CZ\xff", null, 'VAT number is not a valid UTF-8 string.']
		] as [$input, $country, $message]){
			Assert::exception(
				fn() => VatNumber::parse($input, $country),
				\Znojil\Vies\Exception\InvalidArgumentException::class,
				$message
			);
		}
	}

	public function testToString(): void{
		Assert::same('CZ12345674', (string) VatNumber::parse('12345674', Country::CzechRepublic));
		Assert::same('EL123456789', (string) VatNumber::parse('GR 123 456 789'));
	}

}

(new VatNumberTest)->run();
