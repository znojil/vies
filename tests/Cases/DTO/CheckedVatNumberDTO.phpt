<?php
declare(strict_types=1);

namespace Znojil\Vies\Tests\Cases\DTO;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class CheckedVatNumberDTOTest extends \Tester\TestCase{

	public function testGetAddressLines(): void{
		foreach([
			["Příkladná 1234/5\nPRAHA 1 - NOVÉ MĚSTO\n110 00  PRAHA 1", ['Příkladná 1234/5', 'PRAHA 1 - NOVÉ MĚSTO', '110 00  PRAHA 1']],
			['Příkladná 1234/5, 110 00 Praha 1', ['Příkladná 1234/5, 110 00 Praha 1']], // single line is kept whole
			["Příkladná 1234/5\r\n110 00 Praha 1", ['Příkladná 1234/5', '110 00 Praha 1']], // CRLF
			["  Příkladná 1234/5  \n\n \n110 00 Praha 1\n", ['Příkladná 1234/5', '110 00 Praha 1']], // blank lines and padding
			['---', []], // VIES placeholder for "no value"
			[null, []]
		] as [$address, $expected]){
			$data = [
				'countryCode' => 'CZ',
				'vatNumber' => '12345674',
				'requestDate' => '2026-09-13T19:26:25.502Z',
				'valid' => true
			];
			if($address !== null){
				$data['address'] = $address;
			}

			\Tester\Assert::same($expected, \Znojil\Vies\DTO\CheckedVatNumberDTO::fromResponseData($data)->getAddressLines());
		}
	}

}

(new CheckedVatNumberDTOTest)->run();
