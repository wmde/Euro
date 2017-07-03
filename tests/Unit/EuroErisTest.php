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
		$this->forAll( Generator\pos() )
			->then( function( int $unsignedInteger ) {
				$amount = Euro::newFromCents( $unsignedInteger );
				$this->assertSame( $unsignedInteger, $amount->getEuroCents() );
			} );
	}

	public function testEquality() {
		$this->forAll( Generator\pos() )
			->then( function( int $unsignedInteger ) {
				$amount = Euro::newFromCents( $unsignedInteger );
				$this->assertTrue( $amount->equals( Euro::newFromCents( $unsignedInteger ) ) );
			} );
	}

}
