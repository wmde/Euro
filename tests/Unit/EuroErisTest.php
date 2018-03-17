<?php

declare( strict_types = 1 );

namespace WMDE\Euro\Tests\Unit;

use Eris\Generator;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;
use WMDE\Euro\Euro;

/**
 * @covers \WMDE\Euro\Euro
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EuroErisTest extends TestCase {
	use TestTrait;

	public function testGivenNegativeAmount_constructorThrowsException() {
		$this->forAll( Generator\neg() )
			->then( function( int $negativeInteger ) {
				$this->expectException( \InvalidArgumentException::class );
				Euro::newFromCents( $negativeInteger );
			} );
	}

	public function testGetCentsReturnsConstructorArgument() {
		$this->limitTo( 10000 )
			->forAll( Generator\choose( 0, 100 * 100 ) )
			->then( function( int $unsignedInteger ) {
				$amount = Euro::newFromCents( $unsignedInteger );
				$this->assertSame( $unsignedInteger, $amount->getEuroCents() );
			} );
	}

	public function testEquality() {
		$this->limitTo( 10000 )
			->forAll( Generator\choose( 0, 100 * 100 ) )
			->then( function( int $unsignedInteger ) {
				$amount = Euro::newFromCents( $unsignedInteger );
				$this->assertTrue( $amount->equals( Euro::newFromCents( $unsignedInteger ) ) );
			} );
	}

	public function testNewFromString() {
		$this->limitTo( 10000 )
			->forAll( Generator\choose( 0, 100 ), Generator\choose( 0, 99 ) )
			->then( function( int $firstInt, int $secondInt ) {
				$euroString =
					(string)$firstInt
					. '.'
					. str_pad( (string)$secondInt, 2, '0', STR_PAD_LEFT );

				$this->assertSame(
					$firstInt * 100 + $secondInt,
					Euro::newFromString( $euroString )->getEuroCents()
				);
			} );
	}

}
