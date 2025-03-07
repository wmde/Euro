<?php

declare( strict_types = 1 );

namespace WMDE\Euro\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use WMDE\Euro\Euro;

#[CoversClass( Euro::class )]
class EuroTest extends TestCase {

	#[DataProvider( 'unsignedIntegerProvider' )]
	public function testGetCentsReturnsConstructorArgument( int $unsignedInteger ): void {
		$amount = Euro::newFromCents( $unsignedInteger );
		$this->assertSame( $unsignedInteger, $amount->getEuroCents() );
	}

	/**
	 * @return array{int}[]
	 */
	public static function unsignedIntegerProvider(): array {
		return [
			[ 0 ], [ 1 ], [ 2 ], [ 9 ], [ 10 ], [ 11 ],
			[ 99 ], [ 100 ], [ 101 ], [ 999 ], [ 1000 ], [ 1001 ],
		];
	}

	public function testGivenZero_getEuroFloatReturnsZeroFloat(): void {
		$amount = Euro::newFromCents( 0 );
		$this->assertSame( 0.0, $amount->getEuroFloat() );
		$this->assertNotSame( 0, $amount->getEuroFloat() );
	}

	public function testGivenOneEuro_getEuroFloatReturnsOne(): void {
		$amount = Euro::newFromCents( 100 );
		$this->assertSame( 1.0, $amount->getEuroFloat() );
	}

	public function testGivenOneCent_getEuroFloatReturnsPointZeroOne(): void {
		$amount = Euro::newFromCents( 1 );
		$this->assertSame( 0.01, $amount->getEuroFloat() );
	}

	public function testGiven33cents_getEuroFloatReturnsPointThreeThree(): void {
		$amount = Euro::newFromCents( 33 );
		$this->assertSame( 0.33, $amount->getEuroFloat() );
	}

	#[DataProvider( 'getEurosDataProvider' )]
	public function testGetEurosReturnsCorrectValues( int $cents, int $expectedEuros ): void {
		$amount = Euro::newFromCents( $cents );
		$this->assertEquals( $expectedEuros, $amount->getEuros() );
	}

	/**
	 * @return array{int,int}[]
	 */
	public static function getEurosDataProvider(): array {
		return [
			[ 0, 0 ],
			[ 3, 0 ],
			[ 102, 1 ],
			[ 149, 1 ],
			[ 150, 1 ],
			[ 151, 1 ],
			[ 199, 1 ],
			[ 555, 5 ],
			[ 1033, 10 ],
			[ 9999, 99 ],
		];
	}

	public function testGivenNegativeAmount_constructorThrowsException(): void {
		$this->expectException( \InvalidArgumentException::class );
		Euro::newFromCents( -1 );
	}

	public function testGivenZero_getEuroStringReturnsZeroString(): void {
		$amount = Euro::newFromCents( 0 );
		$this->assertSame( '0.00', $amount->getEuroString() );
	}

	public function testGivenOneEuro_getEuroStringReturnsOnePointZeroZero(): void {
		$amount = Euro::newFromCents( 100 );
		$this->assertSame( '1.00', $amount->getEuroString() );
	}

	public function testGivenTwoEuros_getEuroStringReturnsTwoPointZeroZero(): void {
		$amount = Euro::newFromCents( 200 );
		$this->assertSame( '2.00', $amount->getEuroString() );
	}

	public function testGivenOneCent_getEuroStringReturnsZeroPointZeroOne(): void {
		$amount = Euro::newFromCents( 1 );
		$this->assertSame( '0.01', $amount->getEuroString() );
	}

	public function testGivenTenCents_getEuroStringReturnsZeroPointOneZero(): void {
		$amount = Euro::newFromCents( 10 );
		$this->assertSame( '0.10', $amount->getEuroString() );
	}

	public function testGiven1234Cents_getEuroStringReturns12euro34(): void {
		$amount = Euro::newFromCents( 1234 );
		$this->assertSame( '12.34', $amount->getEuroString() );
	}

	public function testGiven9876Cents_stringCastingReturns98euro76(): void {
		$amount = Euro::newFromCents( 9876 );
		$this->assertSame( '98.76', (string)$amount );
	}

	public function testGivenEuroAmount_jsonEncodeWillEncodeProperly(): void {
		$amount = Euro::newFromCents( 9876 );
		$this->assertSame( '"98.76"', json_encode( $amount ) );
	}

	public function testOneEuroString_getsTurnedInto100cents(): void {
		$this->assertSame( 100, Euro::newFromString( '1.00' )->getEuroCents() );
	}

	public function testOneCentString_getsTurnedInto1cents(): void {
		$this->assertSame( 1, Euro::newFromString( '0.01' )->getEuroCents() );
	}

	public function testTenCentString_getsTurnedInto10cents(): void {
		$this->assertSame( 10, Euro::newFromString( '0.10' )->getEuroCents() );
	}

	public function testShortTenCentString_getsTurnedInto10cents(): void {
		$this->assertSame( 10, Euro::newFromString( '0.1' )->getEuroCents() );
	}

	public function testShortOneEuroString_getsTurnedInto100cents(): void {
		$this->assertSame( 100, Euro::newFromString( '1' )->getEuroCents() );
	}

	public function testOneDecimalOneEuroString_getsTurnedInto100cents(): void {
		$this->assertSame( 100, Euro::newFromString( '1.0' )->getEuroCents() );
	}

	public function testMultiDecimalOneEuroString_getsTurnedInto100cents(): void {
		$this->assertSame( 100, Euro::newFromString( '1.00000' )->getEuroCents() );
	}

	public function testHandlingOfLargeEuroString(): void {
		$this->assertSame( 3133742, Euro::newFromString( '31337.42' )->getEuroCents() );
	}

	public function testEuroStringThatCausedRoundingError_doesNotCauseRoundingError(): void {
		// Regression test for https://phabricator.wikimedia.org/T183481
		$this->assertSame( 870, Euro::newFromString( '8.70' )->getEuroCents() );
		$this->assertSame( 920, Euro::newFromString( '9.20' )->getEuroCents() );
	}

	public function testEuroStringWithRoundingError_getsRoundedAppropriately(): void {
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

	public function testGivenNegativeAmountString_exceptionIsThrown(): void {
		$this->expectException( \InvalidArgumentException::class );
		Euro::newFromString( '-1.00' );
	}

	public function testGivenStringWithComma_exceptionIsThrown(): void {
		$this->expectException( \InvalidArgumentException::class );
		Euro::newFromString( '1,00' );
	}

	public function testGivenStringWithMultipleDots_ExceptionIsThrown(): void {
		$this->expectException( \InvalidArgumentException::class );
		Euro::newFromString( '1.0.0' );
	}

	public function testGivenNonNumber_exceptionIsThrown(): void {
		$this->expectException( \InvalidArgumentException::class );
		Euro::newFromString( '1.00abc' );
	}

	public function testGivenNegativeFloatAmount_exceptionIsThrown(): void {
		$this->expectException( \InvalidArgumentException::class );
		Euro::newFromFloat( -1.00 );
	}

	public function testOneEuroFloat_getsTurnedInto100cents(): void {
		$this->assertSame( 100, Euro::newFromFloat( 1.0 )->getEuroCents() );
	}

	public function testOneCentFloat_getsTurnedInto1cent(): void {
		$this->assertSame( 1, Euro::newFromFloat( 0.01 )->getEuroCents() );
	}

	public function testTenCentFloat_getsTurnedInto10cents(): void {
		$this->assertSame( 10, Euro::newFromFloat( 0.1 )->getEuroCents() );
	}

	public function testHandlingOfLargeEuroFloat(): void {
		$this->assertSame( 3133742, Euro::newFromFloat( 31337.42 )->getEuroCents() );
	}

	public function testFloatWithRoundingError_getsRoundedAppropriately(): void {
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

	public function testZeroEuroIntegers_isZeroCents(): void {
		$this->assertSame( 0, Euro::newFromInt( 0 )->getEuroCents() );
	}

	public function testOneEuroIntegers_is100Cents(): void {
		$this->assertSame( 100, Euro::newFromInt( 1 )->getEuroCents() );
	}

	// phpcs:ignore MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
	public function test1337EuroIntegers_is133700Cents(): void {
		$this->assertSame( 133700, Euro::newFromInt( 1337 )->getEuroCents() );
	}

	public function testGivenNegativeIntegerAmount_exceptionIsThrown(): void {
		$this->expectException( \InvalidArgumentException::class );
		Euro::newFromInt( -1 );
	}

	/**
	 * @param Euro $euro
	 */
	#[DataProvider( 'euroProvider' )]
	public function testEuroEqualsItself( Euro $euro ): void {
		$this->assertTrue( $euro->equals( clone $euro ) );
	}

	/**
	 * @return array{Euro}[]
	 */
	public static function euroProvider(): array {
		return [
			[ Euro::newFromCents( 0 ) ],
			[ Euro::newFromCents( 1 ) ],
			[ Euro::newFromCents( 99 ) ],
			[ Euro::newFromCents( 100 ) ],
			[ Euro::newFromCents( 9999 ) ],
		];
	}

	public function testOneCentDoesNotEqualOneEuro(): void {
		$this->assertFalse( Euro::newFromCents( 1 )->equals( Euro::newFromInt( 1 ) ) );
	}

	public function testOneCentDoesNotEqualTwoCents(): void {
		$this->assertFalse( Euro::newFromCents( 1 )->equals( Euro::newFromCents( 2 ) ) );
	}

	public function testOneCentDoesNotEqualOneEuroAndOneCent(): void {
		$this->assertFalse( Euro::newFromCents( 1 )->equals( Euro::newFromCents( 101 ) ) );
	}

	public function test9001centsDoesNotEqual9000cents(): void {
		$this->assertFalse( Euro::newFromCents( 9001 )->equals( Euro::newFromCents( 9000 ) ) );
	}

	#[DataProvider( 'tooLongStringProvider' )]
	public function testNewFromStringThrowsExceptionWhenStringIsTooLong( string $string ): void {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Number is too big' );

		Euro::newFromString( $string );
	}

	/**
	 * @return iterable<array{string}>
	 */
	public static function tooLongStringProvider(): iterable {
		yield [ '1111111111111111111111111111111' ];
		yield [ (string)PHP_INT_MAX ];
		// This large number will be interpreted as a Euro value, which will then be too large to be stored as a Cent value
		yield [ substr( (string)PHP_INT_MAX, 0, -2 ) ];
	}

	public function testNewFromStringHandlesLongStringsWithoutExcpection(): void {
		// The test ensures that Euro does not throw an exception when getting a large, but not too large number.
		$this->expectNotToPerformAssertions();
		Euro::newFromString( substr( (string)PHP_INT_MAX, 0, -3 ) );
	}

	#[DataProvider( 'tooHighNumberProvider' )]
	public function testNewFromIntThrowsExceptionWhenIntegerIsTooHigh( int $int ): void {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Number is too big' );
		Euro::newFromInt( $int );
	}

	/**
	 * @return iterable<array{int}>
	 */
	public static function tooHighNumberProvider(): iterable {
		yield [ PHP_INT_MAX ];
		yield [ (int)floor( PHP_INT_MAX / 10 ) ];
		yield [ (int)floor( PHP_INT_MAX / 100 ) ];
	}

	#[DataProvider( 'tooHighNumberProvider' )]
	public function testNewFromFloatThrowsExceptionWhenFloatIsTooHigh( int $int ): void {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Number is too big' );
		Euro::newFromFloat( (float)$int );
	}

	public function testNewFromIntHandlesBigIntegers(): void {
		// Edge case test for the highest allowed value (Euro::CENTS_PER_EURO +1 )
		// 100 (Euro::CENTS_PER_EURO) does not work due to rounding
		$number = (int)floor( PHP_INT_MAX / 101 );

		$this->assertSame(
			$number * 100,
			Euro::newFromInt( $number )->getEuroCents()
		);
	}

}
