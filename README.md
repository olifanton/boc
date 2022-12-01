BOC (Bag of Cells) PHP serialization library
---

![Code Coverage Badge](./.github/badges/coverage.svg)
![Tests](https://github.com/olifanton/boc/actions/workflows/tests.yml/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/olifanton/boc/v/stable)](https://packagist.org/packages/olifanton/boc)
[![Total Downloads](https://poser.pugx.org/olifanton/boc/downloads)](https://packagist.org/packages/olifanton/boc)


PHP port of [`tonweb-boc`](https://github.com/toncenter/tonweb/tree/master/src/boc) JS library

## Installation

```bash
composer require olifanton/boc
```

## Documentation

### Getting started

Install [`olifanton/boc`](https://packagist.org/packages/olifanton/boc) package via Composer and include autoload script:

```php
<?php declare(strict_types=1);

require __DIR__ . "/vendor/autoload.php";

use Olifanton\Boc\BitString;
use Olifanton\Boc\Cell;

// Now you can use BoC classes

```

### Library classes

#### `Olifanton\Boc\BitString`

`BitString` is a class that allows you to manipulate binary data. `BitString` is at the heart of the PHP representation of TVM Cells. `BitString` is memory optimized for storing binary data.
Internally, BitString uses implementation of `Uint8Array` provided by [`ajf/typed-arrays`](https://packagist.org/packages/ajf/typed-arrays) package and is used as the base type for transferring binary data between parts of the Olifanton libraries.

The BitString instance is created with a strictly fixed length. `write%` (writeBit, writeUint, ...) methods move the internal cursor. If you try to write a value that exceeds the length of the free bits, `BitStringException` exception will be thrown.

##### _BitString_ constructor

```php
/**
 * @param int $length
 */
public function __construct(int $length)
```

Parameters:

- `$length` &mdash; length Uint8Array. Default value for TVM Cell: _1023_ ([Documentation](https://ton.org/docs/learn/overviews/Cells))

##### _BitString_ methods

###### getFreeBits(): int
Returns unused bits length of BitString.


###### getUsedBits(): int
Returns used bits length of BitString.


###### getUsedBytes(): int
Returns used bytes length of BitString.


###### get(): bool

```php
/**
 * @param int $n Position
 */
public function get(int $n): bool
```
Returns a bit value at `$n` position.


###### on(): void
```php
/**
 * @param int $n Position
 */
public function on(int $n): void
```
Sets a bit value to 1 at position `$n`.


###### off(): void
```php
/**
 * @param int $n Position
 */
public function off(int $n): void
```
Sets a bit value to 0 at position `$n`.


###### toggle(): void
```php
/**
 * @param int $n Position
 */
public function toggle(int $n): void
```
Toggle (inverse) bit value at position `$n`.


###### iterate(): \Generator
Returns Generator of used bits.

Example:
```php
<?php declare(strict_types=1);

use Olifanton\Boc\BitString;

$bs = new BitString(4);
$bs->writeBit(1);
$bs->writeBit(0);
$bs->writeBit(1);
$bs->writeBit(1);

foreach ($bs->iterate() as $b) {
    echo (int)$b;
}
// Prints "1011"
```


###### writeBit(): void
```php
/**
 * @param int|bool $b
 */
public function writeBit(int | bool $b): void
```
Writes bit and increase BitString internal cursor.


###### writeBitArray(): void
```php
/**
 * @param array<int | bool> $ba Array of bits
 */
public function writeBitArray(array $ba): void
```
Writes array of bits.

Example:
```php
<?php declare(strict_types=1);

use Olifanton\Boc\BitString;

$bs = new BitString(4);
$bs->writeBitArray([1, false, 0, true]);

foreach ($bs->iterate() as $b) {
    echo (int)$b;
}
// Prints "1001"
```


###### writeUint(): void
```php
/**
 * @param int|\Brick\Math\BigInteger $number Unsigned integer
 * @param int $bitLength Integer size (8, 16, 32, ...)
 */
public function writeUint(int | BigInteger $number, int $bitLength): void
```
Writes $bitLength-bit unsigned integer.


###### writeInt(): void
```php
/**
 * @param int|\Brick\Math\BigInteger $number Signed integer
 * @param int $bitLength Integer size (8, 16, 32, ...)
 */
public function writeInt(int | BigInteger $number, int $bitLength): void
```
Writes $bitLength-bit signed integer.


###### writeUint8(): void
Alias of `writeUint()` method with predefined $bitLength parameter value.


###### writeBytes(): void
```php
/**
 * @param \ajf\TypedArrays\Uint8Array $ui8 Byte array
 */
public function writeBytes(Uint8Array $ui8): void
```
Write array of unsigned 8-bit integers.


###### writeString(): void
```php
/**
 * @param string $value
 */
public function writeString(string $value): void
```
Writes UTF-8 string.


###### writeCoins(): void
```php
/**
 * @param int|\Brick\Math\BigInteger $amount
 */
public function writeCoins(int | BigInteger $amount): void;
```
Writes coins in nanotoncoins. 1 TON === 1000000000 (10^9) nanotoncoins


###### writeAddress(): void
```php
/**
 * @param \Olifanton\Utils\Address|null $address TON Address
 */
public function writeAddress(?Address $address): void
```
Writes TON address. See Address implementation in [olifanton/utils](http://github.com/olifanton/utils) package


###### writeBitString(): void
```php
/**
 * @param \Olifanton\Boc\BitString $anotherBitString BitString instance
 */
public function writeBitString(BitString $anotherBitString): void
```
Writes another BitString to this BitString.


###### clone(): BitString
Clones this BitString and return new BitString instance.


###### toHex(): string
Returns hex string representation of BitString.


###### getImmutableArray(): Uint8Array
Returns immutable copy of internal Uint8Array.


###### getLength(): int
Returns size of BitString in bits

#### `Olifanton\Boc\Cell`

`@TODO`

#### `Olifanton\Boc\Slice`

`@TODO`

## Tests

```bash
composer run test
```

# License

MIT
