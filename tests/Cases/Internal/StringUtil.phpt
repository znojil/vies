<?php
declare(strict_types=1);

namespace Znojil\Vies\Tests\Cases\Internal;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class StringUtilTest extends \Tester\TestCase{

	public function testNormalizeNullableProperty(): void{
		foreach([
			['Example s.r.o.', 'Example s.r.o.'],
			[" Example s.r.o.\n", 'Example s.r.o.'],
			["Příkladná 1234/5\nPRAHA 1", "Příkladná 1234/5\nPRAHA 1"], // inner line breaks are kept
			['---', null], // VIES placeholder for "no value"
			[' --- ', null],
			['', null],
			['	 ', null],
			[null, null]
		] as $v){
			\Tester\Assert::same($v[1], \Znojil\Vies\Internal\StringUtil::normalizeNullableProperty($v[0]));
		}
	}

}

(new StringUtilTest)->run();
