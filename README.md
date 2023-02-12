---
# ⚠️⚠️⚠️ This package is outdated and will not be updated! Use [`olifanton/interop`](https://github.com/olifanton/interop) instead.
---

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

- [BitString](https://github.com/olifanton/boc#olifantonbocbitstring)
- [Cell](https://github.com/olifanton/boc#olifantonboccell)
- [Slice](https://github.com/olifanton/boc#olifantonbocslice)

---

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

- `$length` &mdash; length of Uint8Array. Default value for TVM Cell: _1023_ ([Documentation](https://ton.org/docs/learn/overviews/Cells))

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
Writes coins in nanotoncoins. 1 TON === 1000000000 (10^9) nanotoncoins.


###### writeAddress(): void
```php
/**
 * @param \Olifanton\Utils\Address|null $address TON Address
 */
public function writeAddress(?Address $address): void
```
Writes TON address. See Address implementation in [olifanton/utils](http://github.com/olifanton/utils) package.


###### writeBitString(): void
```php
/**
 * @param \Olifanton\Boc\BitString $anotherBitString BitString instance
 */
public function writeBitString(BitString $anotherBitString): void
```
Writes another BitString to this BitString.


###### clone(): BitString
Clones this BitString and returns new BitString instance.


###### toHex(): string
Returns hex string representation of BitString.


###### getImmutableArray(): Uint8Array
Returns immutable copy of internal Uint8Array.


###### getLength(): int
Returns size of BitString in bits.

---

#### `Olifanton\Boc\Cell`

`Cell` is a class that implements the concept of [TVM Cells](https://ton.org/docs/learn/overviews/Cells) in PHP. To create new and process received messages from the blockchain, you will work with instances of the Cell class. 


##### _Cell_ constructor
Without parameters.


##### _Cell_ methods


###### fromBoc(): Array\<Cell\>
```php
/**
 * @param string|Uint8Array $serializedBoc Serialized BoC
 * @return Cell[]
 */
public static function fromBoc(string|Uint8Array $serializedBoc): array
```
Creates array of Cell's from byte array or hex string.


###### oneFromBoc(): Cell
```php
/**
 * @param string|Uint8Array $serializedBoc Serialized BoC
 * @param bool $isBase64 Base64-serialized flag, default false
 */
public static function oneFromBoc(string|Uint8Array $serializedBoc, bool $isBase64 = false): Cell
```
Fetch one root Cell from byte array or hex string.


###### writeCell(): void
```php
/**
 * @param Cell $anotherCell Another cell
 * @return Cell This Cell
 */
public function writeCell(Cell $anotherCell): self
```
Writes another Cell to this cell and returns this cell. Mutable method.


###### getMaxDepth(): int
Returns max depth of child cells.


###### getBits(): BitString
Returns internal BitString instance for writing and reading.


###### getRefs(): ArrayObject\<Cell\>
Returns Array-like object of children cells.


###### hash(): Uint8Array
Returns SHA-256 hash of this Cell.


###### print(): string
Recursively prints cell's content like Fift.


###### toBoc(): Uint8Array
```php
/**
 * @param bool $has_idx Default _true_
 * @param bool $hash_crc32 Default _true_
 * @param bool $has_cache_bits Default _false_
 * @param int $flags Default _0_
 */
public function toBoc(bool $has_idx = true,
                      bool $hash_crc32 = true,
                      bool $has_cache_bits = false,
                      int  $flags = 0): Uint8Array
```
Creates BoC Byte array.


---

#### `Olifanton\Boc\Slice`

`Slice` is the type of cell slices. A cell can be transformed into a slice, and then the data bits and references to other cells from the cell can be obtained by loading them from the slice.

`load%` (loadBit, loadUint, ...) methods move the Slice internal cursor. If you try to read a value that exceeds the length of the free bits, `SliceException` exception will be thrown.

##### _Slice_ constructor

```php
/**
 * @param \ajf\TypedArrays\Uint8Array $array
 * @param int $length
 * @param \Olifanton\Boc\Slice[] $refs
 */
public function __construct(Uint8Array $array, int $length, array $refs)
```

Parameters:

- `$array` &mdash; Uint8Array from BitString representation of Cell 
- `$length` &mdash; BitString length
- `$refs` &mdash; Children Cells slices

##### _Slice_ methods

###### getFreeBits(): int
Returns the unread bits according to the internal cursor.

###### get(): bool
```php
/**
 * @param int $n
 */
public function get(int $n): bool
```
Returns a bit value at position `$n`.

###### loadBit(): bool
Reads a bit and moves the cursor.

###### loadBits(): Uint8Array
```php
/**
 * @param int $bitLength
 */
public function loadBits(int $bitLength): Uint8Array
```
Reads bit array.

###### loadUint(): BigInteger
```php
/**
 * @param int $bitLength
 */
public function loadUint(int $bitLength): BigInteger
```
Reads unsigned integer.

###### loadInt(): BigInteger
```php
/**
 * @param int $bitLength
 */
public function loadInt(int $bitLength): BigInteger
```
Reads signed integer.

###### loadVarUint(): BigInteger
```php
/**
 * @param int $bitLength
 */
public function loadVarUint(int $bitLength): BigInteger
```


###### loadCoins(): BigInteger
Reads TON amount in nanotoncoins.

###### loadAddress(): ?Address
Reads [Address](https://github.com/olifanton/utils#olifantonutilsaddress).

###### loadRef(): Slice
Reads Slice of children Cell.

---

## Tests

```bash
composer run test
```

---

# License

MIT
