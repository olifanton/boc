<?php declare(strict_types=1);

namespace Olifanton\Boc;

use ajf\TypedArrays\ArrayBuffer;
use ajf\TypedArrays\Uint8Array;
use Brick\Math\BigInteger;
use Olifanton\Boc\Exceptions\BitStringException;
use Olifanton\Utils\Address;
use Olifanton\Utils\Bytes;

class BitString implements \Stringable
{
    private int $length;

    private int $cursor = 0;

    private Uint8Array $array;

    public function __construct(int $length)
    {
        $this->length = $length;
        $this->array = new Uint8Array(array_fill(
            0,
            self::getUint8ArrayLength($length),
            0
        ));
    }

    /**
     * Get free bits length
     */
    public function getFreeBits(): int
    {
        return $this->length - $this->cursor;
    }

    /**
     * Get used bits length
     */
    public function getUsedBits(): int
    {
        return $this->cursor;
    }

    /**
     * Get used bytes length
     */
    public function getUsedBytes(): int
    {
        return (int)ceil($this->cursor / 8);
    }

    /**
     * Get bit value value at `$n` position
     *
     * @throws BitStringException
     */
    public function get(int $n): bool
    {
        $this->checkRange($n);

        return ($this->array[(int)($n / 8) | 0] & (1 << (7 - ($n % 8)))) > 0;
    }

    /**
     * Set bit value to 1 at position `$n`
     *
     * @throws BitStringException
     */
    public function on(int $n): void
    {
        $this->checkRange($n);
        $this->array[(int)($n / 8) | 0] |= 1 << (7 - ($n % 8));
    }

    /**
     * Set bit value to 0 at position `$n`
     *
     * @throws BitStringException
     */
    public function off(int $n): void
    {
        $this->checkRange($n);
        $this->array[(int)($n / 8) | 0] &= ~(1 << (7 - ($n % 8)));
    }

    /**
     * Toggle bit value at position `$n`
     *
     * @throws BitStringException
     */
    public function toggle(int $n): void
    {
        $this->checkRange($n);
        $this->array[(int)($n / 8) | 0] ^= 1 << (7 - ($n % 8));
    }

    /**
     * @throws BitStringException
     */
    public function iterate(): \Generator
    {
        $max = $this->cursor;

        for ($i = 0; $i < $max; $i++) {
            yield $this->get($i);
        }
    }

    /**
     * Write bit and increase cursor
     *
     * @throws BitStringException
     */
    public function writeBit(int | bool $b): void
    {
        if ($this->cursor === $this->length) {
            throw new BitStringException("BitString overflow");
        }

        if ($b && $b > 0) {
            $this->on($this->cursor);
        } else {
            $this->off($this->cursor);
        }

        $this->cursor++;
    }

    /**
     * Write array of bites
     *
     * @param array<int | bool> $ba
     * @throws BitStringException
     */
    public function writeBitArray(array $ba): void
    {
        foreach ($ba as $b) {
            $this->writeBit($b);
        }
    }

    /**
     * Write unsigned integer
     *
     * @throws BitStringException
     */
    public function writeUint(int | BigInteger $number, int $bitLength): void
    {
        if (!$number instanceof BigInteger) {
            $number = BigInteger::of($number);
        }

        if ($bitLength === 0 || strlen($number->toBase(2)) > $bitLength) {
            if ($number->toInt() === 0) {
                return;
            }

            throw new BitStringException("bitLength is too small for number, got number=" . $number . ", bitLength=" . $bitLength);
        }

        $s = $this->toBaseWithPadding($number, 2, $bitLength);

        foreach (str_split($s) as $char) {
            $this->writeBit($char === "1");
        }
    }

    /**
     * Write signed integer
     *
     * @throws BitStringException
     */
    public function writeInt(int | BigInteger $number, int $bitLength): void
    {
        if (!$number instanceof BigInteger) {
            $number = BigInteger::of($number);
        }

        if ($bitLength === 1) {
            if ($number->toInt() === -1) {
                $this->writeBit(true);
                return;
            }

            if ($number->toInt() === 0) {
                $this->writeBit(false);
                return;
            }

            throw new BitStringException("bitLength is too small for number");
        } else {
            if ($number->isNegative()) {
                $this->writeBit(true);
                $b = BigInteger::of(2);
                $nb = $b->power($bitLength - 1);
                $this->writeUint($nb->plus($number), $bitLength - 1);
            } else {
                $this->writeBit(false);
                $this->writeUint($number, $bitLength - 1);
            }
        }
    }

    /**
     * Write unsigned 8-bit integer
     *
     * @throws BitStringException
     */
    public function writeUint8(int $ui8): void
    {
        $this->writeUint($ui8, 8);
    }

    /**
     * Write array of unsigned 8-bit integers
     *
     * @throws BitStringException
     */
    public function writeBytes(Uint8Array $ui8): void
    {
        for ($i = 0; $i < $ui8->length; $i++) {
            $this->writeUint8($ui8[$i]);
        }
    }

    /**
     * Write UTF-8 string
     *
     * @throws BitStringException
     */
    public function writeString(string $value): void
    {
        $this->writeBytes(new Uint8Array(array_values(unpack('C*', $value))));
    }

    /**
     * Write coins in nanotons
     *
     * @param int|BigInteger $amount in nanotons
     * @throws BitStringException
     */
    public function writeCoins(int | BigInteger $amount): void
    {
        if (!$amount instanceof BigInteger) {
            $amount = BigInteger::of($amount);
        }

        if ($amount->toInt() === 0) {
            $this->writeUint(0, 4);
        } else {
            $l = (int)ceil((strlen($amount->toBase(16))) / 2);
            $this->writeUint($l, 4);
            $this->writeUint($amount, $l * 8);
        }
    }

    /**
     * Write Address
     *
     * @throws BitStringException
     */
    public function writeAddress(?Address $address): void
    {
        if (!$address) {
            $this->writeUint(0, 2);
        } else {
            $this->writeUint(2, 2);
            $this->writeUint(0, 1);
            $this->writeInt($address->getWorkchain(), 8);
            $this->writeBytes($address->getHashPart());
        }
    }

    /**
     * Write another BitString to this BitString
     *
     * @throws BitStringException
     */
    public function writeBitString(BitString $anotherBitString): void
    {
        foreach ($anotherBitString->iterate() as $x) {
            $this->writeBit($x);
        }
    }

    /**
     * Clone this BitString and return new BitString instance
     */
    public function clone(): BitString
    {
        $result = new BitString(0);

        $result->array = Bytes::arraySlice($this->array, 0, self::getUint8ArrayLength($this->length));
        $result->length = $this->length;
        $result->cursor = $this->cursor;

        return $result;
    }

    /**
     * @throws BitStringException
     */
    public function getTopUppedArray(): Uint8Array
    {
        $ret = $this->clone();
        $tu = (int)ceil($ret->cursor / 8) * 8 - $ret->cursor;

        if ($tu > 0) {
            $tu--;

            if (!$ret->getFreeBits()) {
                $ret = self::incLength($ret, $ret->length + 1);
            }

            $ret->writeBit(true);

            while ($tu > 0) {
                $tu--;

                if (!$ret->getFreeBits()) {
                    $ret = self::incLength($ret, $ret->length + 1);
                }

                $ret->writeBit(false);
            }
        }

        return Bytes::arraySlice($ret->array, 0, (int)ceil($ret->cursor / 8));
    }

    /**
     * @throws BitStringException
     */
    public function setTopUppedArray(Uint8Array $array, bool $fulfilledBytes = true): void
    {
        $this->length = $array->length * 8;
        $this->array = $array;
        $this->cursor = $this->length;

        if ($fulfilledBytes || !$this->length) {
            return;
        }

        $foundEndBit = false;

        for ($c = 0; $c < 7; $c++) {
            $this->cursor--;

            if ($this->get($this->cursor)) {
                $foundEndBit = true;
                $this->off($this->cursor);
                break;
            }
        }

        if (!$foundEndBit) {
            throw new BitStringException("Incorrect TopUppedArray");
        }
    }

    /**
     * Return hex string representation of this BitString
     *
     * @throws BitStringException
     */
    public function toHex(bool $fiftStyle = true): string
    {
        if ($this->cursor % 4 === 0) {
            $s = Bytes::bytesToHexString(
                Bytes::arraySlice(
                    $this->array,
                    0,
                    (int)ceil($this->cursor / 8)
                )
            );

            if ($this->cursor % 8 === 0) {
                return $fiftStyle ? strtoupper($s) : $s;
            }

            $s = substr($s, 0, strlen($s) - 1);

            return $fiftStyle ? strtoupper($s) : $s;
        }

        $temp = $this->clone();

        if (!$temp->getFreeBits()) {
            $temp = self::incLength($temp, $this->length + 1);
        }

        $temp->writeBit(1);

        while ($temp->cursor & 4 !== 0) {
            if (!$temp->getFreeBits()) {
                $temp = self::incLength($temp, $this->length + 1);
            }

            $temp->writeBit(0);
        }

        $hex = $temp->toHex($fiftStyle);

        return $hex . '_';
    }

    /**
     * @throws BitStringException
     */
    public function __toString(): string
    {
        return $this->toHex();
    }

    /**
     * @throws BitStringException
     */
    private function checkRange(int $n): void
    {
        if ($n >= $this->length) {
            throw new BitStringException("BitString overflow");
        }
    }

    private function toBaseWithPadding(BigInteger $number, int $base, int $padding): string
    {
        $str = $number->toBase($base);
        $needPad = $padding - strlen($str);

        if ($needPad > 0) {
            return str_pad($str, $padding, "0", STR_PAD_LEFT);
        }

        return $str;
    }

    private static function getUint8ArrayLength(int $bitStringLength): int
    {
        return (int)ceil($bitStringLength / 8);
    }

    private static function incLength(BitString $bitString, int $newLength): BitString
    {
        if ($newLength < $bitString->length) {
            throw new \OutOfRangeException();
        }

        $bitString = $bitString->clone();
        $bitString->length = $newLength;
        $tmpArr = $bitString->array;
        $bitString->array = new Uint8Array(self::getUint8ArrayLength($newLength));
        $bitString->array->set($tmpArr);

        return $bitString;
    }
}
