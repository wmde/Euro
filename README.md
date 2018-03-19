# Euro

[![Build Status](https://secure.travis-ci.org/wmde/Euro.png?branch=master)](http://travis-ci.org/wmde/Euro)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/wmde/Euro/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/wmde/Euro/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/wmde/Euro/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/wmde/Euro/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/wmde/euro/version.png)](https://packagist.org/packages/wmde/euro)
[![Download count](https://poser.pugx.org/wmde/euro/d/total.png)](https://packagist.org/packages/wmde/euro)

[Value Object](https://en.wikipedia.org/wiki/Value_object) that represents a positive amount of Euro.

## Installation

To add this package as a local, per-project dependency to your project, simply add a
dependency on `wmde/euro` to your project's `composer.json` file.
Here is a minimal example of a `composer.json` file that just defines a dependency on
Euro 1.x:

```json
{
    "require": {
        "wmde/euro": "^1.0.1"
    }
}
```

## Usage

### Construction

Constructing from Euro cents (int):

```php
$productPrice = Euro::newFromCents(4200);
```

Constructing from a Euro amount (float):

```php
$productPrice = Euro::newFromFloat(42.00);
```

Constructing from a Euro amount (string):

```php
$productPrice = Euro::newFromString('42.00');
```

Constructing from a Euro amount (int):

```php
$productPrice = Euro::newFromInt(42);
```

### Access

```php
echo $productPrice->getEuroCents();
// 4200 (int) for all above examples
```

```php
echo $productPrice->getEuroFloat();
// 42.0 (float) for all above examples
```

```php
echo $productPrice->getEuroString();
// "42.00" (string) for all above examples
```

### Comparison

```php
Euro::newFromCents(4200)->equals(Euro::newFromInt(42));
// true
```

```php
Euro::newFromCents(4201)->equals(Euro::newFromInt(42));
// false
```

## Running the tests

For tests only

    composer test

For style checks only

	composer cs

For a full CI run

	composer ci

## Release notes

### 1.0.1 (2018-03-17)

* Fixed rounding issue occuring on some platforms

### 1.0.0 (2016-07-31)

* Initial release
