<?php

declare( strict_types = 1 );

namespace WMDE\Euro\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WMDE\Euro\Euro;

/**
 * @covers \WMDE\Euro\Euro
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EuroTest extends TestCase {

	/**
	 * @dataProvider unsignedIntegerProvider
	 */
	public function testGetCentsReturnsConstructorArgument( int $unsignedInteger ) {
		$amount = Euro::newFromCents( $unsignedInteger );
		$this->assertSame( $unsignedInteger, $amount->getEuroCents() );
	}

	public function unsignedIntegerProvider() {
		return [
			[ 0 ], [ 1 ], [ 2 ], [ 9 ], [ 10 ], [ 11 ],
			[ 99 ], [ 100 ], [ 101 ], [ 999 ], [ 1000 ], [ 1001 ],
		];
	}

	public function testGivenZero_getEuroFloatReturnsZeroFloat() {
		$amount = Euro::newFromCents( 0 );
		$this->assertExactFloat( 0.0, $amount->getEuroFloat() );
		$this->assertNotSame( 0, $amount->getEuroFloat() );
	}

	private function assertExactFloat( float $expected, $actual ) {
		$this->assertInternalType( 'float', $actual );
		$this->assertEquals( $expected, $actual, '', 0 );
	}

	public function testGivenOneEuro_getEuroFloatReturnsOne() {
		$amount = Euro::newFromCents( 100 );
		$this->assertExactFloat( 1.0, $amount->getEuroFloat() );
	}

	public function testGivenOneCent_getEuroFloatReturnsPointZeroOne() {
		$amount = Euro::newFromCents( 1 );
		$this->assertExactFloat( 0.01, $amount->getEuroFloat() );
	}

	public function testGiven33cents_getEuroFloatReturnsPointThreeThree() {
		$amount = Euro::newFromCents( 33 );
		$this->assertExactFloat( 0.33, $amount->getEuroFloat() );
	}

	public function testGivenNegativeAmount_constructorThrowsException() {
		$this->expectException( \InvalidArgumentException::class );
		Euro::newFromCents( -1 );
	}

	public function testGivenZero_getEuroStringReturnsZeroString() {
		$amount = Euro::newFromCents( 0 );
		$this->assertSame( '0.00', $amount->getEuroString() );
	}

	public function testGivenOneEuro_getEuroStringReturnsOnePointZeroZero() {
		$amount = Euro::newFromCents( 100 );
		$this->assertSame( '1.00', $amount->getEuroString() );
	}

	public function testGivenTwoEuros_getEuroStringReturnsTwoPointZeroZero() {
		$amount = Euro::newFromCents( 200 );
		$this->assertSame( '2.00', $amount->getEuroString() );
	}

	public function testGivenOneCent_getEuroStringReturnsZeroPointZeroOne() {
		$amount = Euro::newFromCents( 1 );
		$this->assertSame( '0.01', $amount->getEuroString() );
	}

	public function testGivenTenCents_getEuroStringReturnsZeroPointOneZero() {
		$amount = Euro::newFromCents( 10 );
		$this->assertSame( '0.10', $amount->getEuroString() );
	}

	public function testGiven1234Cents_getEuroStringReturns12euro34() {
		$amount = Euro::newFromCents( 1234 );
		$this->assertSame( '12.34', $amount->getEuroString() );
	}

	public function testGiven9876Cents_stringCastingReturns98euro76() {
		$amount = Euro::newFromCents( 9876 );
		$this->assertSame( '98.76', (string) $amount );
	}

	public function testGivenEuroAmount_jsonEncodeWillEncodeProperly() {
		$amount = Euro::newFromCents( 9876 );
		$this->assertSame( '"98.76"', json_encode( $amount ) );
	}

	public function testOneEuroString_getsTurnedInto100cents() {
		$this->assertSame( 100, Euro::newFromString( '1.00' )->getEuroCents() );
	}

	public function testOneCentString_getsTurnedInto1cents() {
		$this->assertSame( 1, Euro::newFromString( '0.01' )->getEuroCents() );
	}

	public function testTenCentString_getsTurnedInto10cents() {
		$this->assertSame( 10, Euro::newFromString( '0.10' )->getEuroCents() );
	}

	public function testShortTenCentString_getsTurnedInto10cents() {
		$this->assertSame( 10, Euro::newFromString( '0.1' )->getEuroCents() );
	}

	public function testShortOneEuroString_getsTurnedInto100cents() {
		$this->assertSame( 100, Euro::newFromString( '1' )->getEuroCents() );
	}

	public function testOneDecimalOneEuroString_getsTurnedInto100cents() {
		$this->assertSame( 100, Euro::newFromString( '1.0' )->getEuroCents() );
	}

	public function testMultiDecimalOneEuroString_getsTurnedInto100cents() {
		$this->assertSame( 100, Euro::newFromString( '1.00000' )->getEuroCents() );
	}

	public function testHandlingOfLargeEuroString() {
		$this->assertSame( 3133742, Euro::newFromString( '31337.42' )->getEuroCents() );
	}

	public function testEuroStringThatCausedRoundingError_doesNotCauseRoundingError() {
		// Regression test for https://phabricator.wikimedia.org/T183481
		$this->assertSame( 870, Euro::newFromString( '8.70' )->getEuroCents() );
		$this->assertSame( 920, Euro::newFromString( '9.20' )->getEuroCents() );
	}

	public function testEuroStringWithRoundingError_getsRoundedAppropriately() {
		$this->assertSame( 101, Euro::newFromString( '1.0100000001' )->getEuroCents() );
		$this->assertSame( 101, Euro::newFromString( '1.010000009999' )->getEuroCents() );
		$this->assertSame( 101, Euro::newFromString( '1.011' )->getEuroCents() );
		$this->assertSame( 101, Euro::newFromString( '1.014' )->getEuroCents() );
		$this->assertSame( 101, Euro::newFromString( '1.0149' )->getEuroCents() );
		$this->assertSame( 102, Euro::newFromString( '1.015' )->getEuroCents() );
		$this->assertSame( 102, Euro::newFromString( '1.019' )->getEuroCents() );
		$this->assertSame( 102, Euro::newFromString( '1.0199999' )->getEuroCents() );
		$this->assertSame( 870, Euro::newFromString( '8.701' )->getEuroCents() );
		$this->assertSame( 870, Euro::newFromString( '8.70499' )->getEuroCents() );
		$this->assertSame( 871, Euro::newFromString( '8.705' )->getEuroCents() );
		$this->assertSame( 871, Euro::newFromString( '8.705000' )->getEuroCents() );
		$this->assertSame( 871, Euro::newFromString( '8.705001' )->getEuroCents() );
		$this->assertSame( 871, Euro::newFromString( '8.709999' )->getEuroCents() );
	}

	public function testGivenNegativeAmountString_exceptionIsThrown() {
		$this->expectException( \InvalidArgumentException::class );
		Euro::newFromString( '-1.00' );
	}

	public function testGivenStringWithComma_exceptionIsThrown() {
		$this->expectException( \InvalidArgumentException::class );
		Euro::newFromString( '1,00' );
	}

	public function testGivenStringWithMultipleDots_ExceptionIsThrown() {
		$this->expectException( \InvalidArgumentException::class );
		Euro::newFromString( '1.0.0' );
	}

	public function testGivenNonNumber_exceptionIsThrown() {
		$this->expectException( \InvalidArgumentException::class );
		Euro::newFromString( '1.00abc' );
	}

	public function testGivenNegativeFloatAmount_exceptionIsThrown() {
		$this->expectException( \InvalidArgumentException::class );
		Euro::newFromFloat( -1.00 );
	}

	public function testOneEuroFloat_getsTurnedInto100cents() {
		$this->assertSame( 100, Euro::newFromFloat( 1.0 )->getEuroCents() );
	}

	public function testOneCentFloat_getsTurnedInto1cent() {
		$this->assertSame( 1, Euro::newFromFloat( 0.01 )->getEuroCents() );
	}

	public function testTenCentFloat_getsTurnedInto10cents() {
		$this->assertSame( 10, Euro::newFromFloat( 0.1 )->getEuroCents() );
	}

	public function testHandlingOfLargeEuroFloat() {
		$this->assertSame( 3133742, Euro::newFromFloat( 31337.42 )->getEuroCents() );
	}

	public function testFloatWithRoundingError_getsRoundedAppropriately() {
		$this->assertSame( 101, Euro::newFromFloat( 1.0100000001 )->getEuroCents() );
		$this->assertSame( 101, Euro::newFromFloat( 1.010000009999 )->getEuroCents() );
		$this->assertSame( 101, Euro::newFromFloat( 1.011 )->getEuroCents() );
		$this->assertSame( 101, Euro::newFromFloat( 1.014 )->getEuroCents() );
		$this->assertSame( 101, Euro::newFromFloat( 1.0149 )->getEuroCents() );
		$this->assertSame( 102, Euro::newFromFloat( 1.015 )->getEuroCents() );
		$this->assertSame( 102, Euro::newFromFloat( 1.019 )->getEuroCents() );
		$this->assertSame( 102, Euro::newFromFloat( 1.0199999 )->getEuroCents() );
		$this->assertSame( 870, Euro::newFromFloat( 8.70 )->getEuroCents() );
	}

	public function testZeroEuroIntegers_isZeroCents() {
		$this->assertSame( 0, Euro::newFromInt( 0 )->getEuroCents() );
	}

	public function testOneEuroIntegers_is100cents() {
		$this->assertSame( 100, Euro::newFromInt( 1 )->getEuroCents() );
	}

	public function test1337EuroIntegers_is133700cents() {
		$this->assertSame( 133700, Euro::newFromInt( 1337 )->getEuroCents() );
	}

	public function testGivenNegativeIntegerAmount_exceptionIsThrown() {
		$this->expectException( \InvalidArgumentException::class );
		Euro::newFromInt( -1 );
	}

	/**
	 * @dataProvider euroProvider
	 * @param Euro $euro
	 */
	public function testEuroEqualsItself( Euro $euro ) {
		$this->assertTrue( $euro->equals( clone $euro ) );
	}

	public function euroProvider() {
		return [
			[ Euro::newFromCents( 0 ) ],
			[ Euro::newFromCents( 1 ) ],
			[ Euro::newFromCents( 99 ) ],
			[ Euro::newFromCents( 100 ) ],
			[ Euro::newFromCents( 9999 ) ],
		];
	}

	public function testOneCentDoesNotEqualOneEuro() {
		$this->assertFalse( Euro::newFromCents( 1 )->equals( Euro::newFromInt( 1 ) ) );
	}

	public function testOneCentDoesNotEqualTwoCents() {
		$this->assertFalse( Euro::newFromCents( 1 )->equals( Euro::newFromCents( 2 ) ) );
	}

	public function testOneCentDoesNotEqualOneEuroAndOneCent() {
		$this->assertFalse( Euro::newFromCents( 1 )->equals( Euro::newFromCents( 101 ) ) );
	}

	public function test9001centsDoesNotEqual9000cents() {
		$this->assertFalse( Euro::newFromCents( 9001 )->equals( Euro::newFromCents( 9000 ) ) );
	}

}
