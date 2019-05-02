<?php

declare( strict_types = 1 );

namespace WMDE\Euro;

use InvalidArgumentException;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
final class Euro implements \JsonSerializable {

	private const DECIMAL_COUNT = 2;
	private const CENTS_PER_EURO = 100;

	private $cents;

	/**
	 * @param int $cents
	 * @throws InvalidArgumentException
	 */
	private function __construct( int $cents ) {
		if ( $cents < 0 ) {
			throw new InvalidArgumentException( 'Amount needs to be positive' );
		}

		$this->cents = $cents;
	}

	/**
	 * @return string
	 */
	public function __toString(): string {
		return $this->getEuroString();
	}

	/**
	 * @return string
	 */
	public function jsonSerialize(): string {
		return $this->getEuroString();
	}

	/**
	 * @param int $cents
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public static function newFromCents( int $cents ): self {
		return new self( $cents );
	}

	/**
	 * Constructs a Euro object from a string representation such as "13.37".
	 *
	 * This method takes into account the errors that can arise from floating
	 * point number usage. Amounts with too many decimals are rounded to the
	 * nearest whole euro cent amount.
	 *
	 * @param string $euroAmount
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public static function newFromString( string $euroAmount ): self {
		if ( !is_numeric( $euroAmount ) ) {
			throw new InvalidArgumentException( 'Not a number' );
		}

		$parts = explode( '.', $euroAmount, 2 );

		$euros = (int)$parts[0];
		$cents = self::centsFromString( $parts[1] ?? '0' );

		return new self( $euros * self::CENTS_PER_EURO + $cents );
	}

	private static function centsFromString( string $cents ): int {
		if ( strlen( $cents ) > self::DECIMAL_COUNT ) {
			return self::roundCentsToInt( $cents );
		}

		// Turn .1 into .10, so it ends up as 10 cents
		return (int)str_pad( $cents, self::DECIMAL_COUNT, '0' );
	}

	private static function roundCentsToInt( string $cents ): int {
		$centsInt = (int)substr( $cents, 0, self::DECIMAL_COUNT );

		if ( (int)$cents[self::DECIMAL_COUNT] >= 5 ) {
			$centsInt++;
		}

		return $centsInt;
	}

	/**
	 * This method takes into account the errors that can arise from floating
	 * point number usage. Amounts with too many decimals are rounded to the
	 * nearest whole euro cent amount.
	 *
	 * @param float $euroAmount
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public static function newFromFloat( float $euroAmount ): self {
		return new self( intval(
			round(
				round( $euroAmount, self::DECIMAL_COUNT ) * self::CENTS_PER_EURO,
				0
			)
		) );
	}

	/**
	 * @param int $euroAmount
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public static function newFromInt( int $euroAmount ): self {
		return new self( $euroAmount * self::CENTS_PER_EURO );
	}

	public function getEuroCents(): int {
		return $this->cents;
	}

	public function getEuroFloat(): float {
		return $this->cents / self::CENTS_PER_EURO;
	}

	/**
	 * Returns the euro amount as string with two decimals always present in format "42.00".
	 */
	public function getEuroString(): string {
		return number_format( $this->getEuroFloat(), self::DECIMAL_COUNT, '.', '' );
	}

	public function equals( Euro $euro ): bool {
		return $this->cents === $euro->cents;
	}

}
