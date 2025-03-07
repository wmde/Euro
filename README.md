# Euro

[Value Object](https://en.wikipedia.org/wiki/Value_object) that represents a positive amount of Euro.

## Installation

To add this package as a local, per-project dependency to your project, add a
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

### 1.1.1 (dev)

* Large numbers now cause an InvalidArgumentException rather than a TypeError

### 1.1.0 (2018-03-21)

* Bumped minimum PHP version from 7.0 to 7.1

### 1.0.2 (2018-03-20)

* Internal changes to avoid dealing with floats when constructing an Euro object from string

### 1.0.1 (2018-03-17)

* Fixed rounding issue occurring on some platforms

### 1.0.0 (2016-07-31)

* Initial release
