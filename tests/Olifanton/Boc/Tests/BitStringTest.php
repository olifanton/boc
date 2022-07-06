<?php declare(strict_types=1);

namespace Olifanton\Boc\Tests;

use ajf\TypedArrays\Uint8Array;
use Brick\Math\BigInteger;
use Olifanton\Boc\BitString;
use Olifanton\Boc\Exceptions\BitStringException;
use Olifanton\Utils\Units;
use PHPUnit\Framework\TestCase;

class BitStringTest extends TestCase
{
    /**
     * @throws BitStringException
     */
    public function testWriteBitOverflow(): void
    {
        $bs = new BitString(2);

        $bs->writeBit(1); // 0
        $bs->writeBit(1); // 1

        $this->expectException(BitStringException::class);
        $this->expectExceptionMessage("BitString overflow");
        $bs->writeBit(1);
    }

    /**
     * @throws BitStringException
     */
    public function testGetFreeBits(): void
    {
        $bs = new BitString(32);
        $this->assertEquals(32, $bs->getFreeBits());

        $bs->writeBit(1);
        $this->assertEquals(31, $bs->getFreeBits());
    }

    /**
     * @throws BitStringException
     */
    public function testGetUsedBits(): void
    {
        $bs = new BitString(32);
        $this->assertEquals(0, $bs->getUsedBits());

        $bs->writeBit(1);
        $this->assertEquals(1, $bs->getUsedBits());
    }

    /**
     * @throws BitStringException
     */
    public function testGetUsedBytes(): void
    {
        $bs = new BitString(32);
        $this->assertEquals(0, $bs->getUsedBytes());

        $bs->writeBit(1);
        $bs->writeBit(1);
        $this->assertEquals(1, $bs->getUsedBytes());

        $bs->writeBit(1);
        $bs->writeBit(1);
        $bs->writeBit(1);
        $bs->writeBit(1);
        $bs->writeBit(1);
        $bs->writeBit(1);
        $this->assertEquals(1, $bs->getUsedBytes());

        $bs->writeBit(1);
        $this->assertEquals(2, $bs->getUsedBytes());
    }

    /**
     * @throws BitStringException
     */
    public function testGet(): void
    {
        $bs = new BitString(32);
        $this->assertFalse($bs->get(0));

        $bs->writeBit(1);
        $this->assertTrue($bs->get(0));

        $bs->writeBit(0);
        $bs->writeBit(1);
        $this->assertFalse($bs->get(1));
        $this->assertTrue($bs->get(2));

        $this->expectException(BitStringException::class);
        $bs->get(33);
    }

    /**
     * @throws BitStringException
     */
    public function testOn(): void
    {
        $bs = new BitString(31);

        $this->assertFalse($bs->get(16));
        $bs->on(16);
        $this->assertTrue($bs->get(16));

        $this->expectException(BitStringException::class);
        $bs->on(33);
    }

    /**
     * @throws BitStringException
     */
    public function testOff(): void
    {
        $bs = new BitString(31);

        $this->assertFalse($bs->get(17));
        $bs->off(17);
        $this->assertFalse($bs->get(17));

        $bs->on(17);
        $this->assertTrue($bs->get(17));
        $bs->off(17);
        $this->assertFalse($bs->get(17));

        $this->expectException(BitStringException::class);
        $bs->off(33);
    }

    /**
     * @throws BitStringException
     */
    public function testToggle(): void
    {
        $bs = new BitString(32);
        $bs->writeBit(1);

        $this->assertTrue($bs->get(0));

        $bs->toggle(0);
        $this->assertFalse($bs->get(0));

        $bs->toggle(0);
        $this->assertTrue($bs->get(0));

        $this->expectException(BitStringException::class);
        $bs->toggle(32);
    }

    /**
     * @throws BitStringException
     */
    public function testIterate(): void
    {
        $bs = new BitString(8);
        $bs->writeBit(1);
        $bs->writeBit(1);
        $bs->writeBit(1);
        $bs->writeBit(1);
        $bs->writeBit(1);

        $i = 0;

        foreach ($bs->iterate() as $b) {
            $this->assertTrue($b);
            $i++;
        }

        $this->assertEquals(5, $i);
    }

    /**
     * @throws BitStringException
     */
    public function testWriteBitArray(): void
    {
        $bs = new BitString(5);
        $stub = [1, 0, true, false, 1];

        $bs->writeBitArray($stub);

        $this->assertTrue($bs->get(0));
        $this->assertFalse($bs->get(1));
        $this->assertTrue($bs->get(2));
        $this->assertFalse($bs->get(3));
        $this->assertTrue($bs->get(4));
    }

    /**
     * @throws BitStringException
     */
    public function testWriteBitArrayOverflow(): void
    {
        $bs = new BitString(2);
        $stub = [1, 1, 1];

        $this->expectException(BitStringException::class);
        $bs->writeBitArray($stub);
    }

    /**
     * @throws BitStringException
     */
    public function testWriteUint(): void
    {
        $bs = new BitString(32);
        $bs->writeUint(1234567890, 32);
        $this->assertEquals("499602D2", $bs->toHex());

        $bs = new BitString(32);
        $bs->writeUint(255, 8);
        $this->assertEquals("FF", $bs->toHex());

        // Big integer
        $bs = new BitString(64);
        $bs->writeUint(BigInteger::of("18446744073709551615"), 64);
        $this->assertEquals("FFFFFFFFFFFFFFFF", $bs->toHex());
    }

    public function testWriteUintBitLengthOverflow(): void
    {
        $bs = new BitString(4);
        $this->expectException(BitStringException::class);
        $this->expectExceptionMessage("bitLength is too small for number, got number=600, bitLength=8");
        $bs->writeUint(600, 8);
    }

    public function testWriteUintBitStringOverflow(): void
    {
        $bs = new BitString(4);
        $this->expectException(BitStringException::class);
        $this->expectExceptionMessage("BitString overflow");
        $bs->writeUint(255, 8);
    }

    /**
     * @throws BitStringException
     */
    public function testWriteIntAsBit(): void
    {
        $bs = new BitString(32);
        $bs->writeInt(-1, 1);
        $this->assertTrue($bs->get(0));

        $bs = new BitString(32);
        $bs->writeInt(0, 1);
        $this->assertFalse($bs->get(0));
    }

    public function testWriteIntBitLengthOverflow(): void
    {
        $bs = new BitString(32);
        $this->expectException(BitStringException::class);
        $this->expectExceptionMessage("bitLength is too small for number");
        $bs->writeInt(2, 1);
    }

    /**
     * @throws BitStringException
     */
    public function testWriteIntBitLengthOverflow2(): void
    {
        $bs = new BitString(32);
        $this->expectException(BitStringException::class);
        $this->expectExceptionMessage("bitLength is too small for number");
        $bs->writeInt(128, 8);
    }

    /**
     * @throws BitStringException
     */
    public function testWriteIntNeg(): void
    {
        $bs = new BitString(32);
        $bs->writeInt(-10, 8);
        $this->assertEquals("F6", $bs->toHex());
    }

    /**
     * @throws BitStringException
     */
    public function testWriteInt(): void
    {
        $bs = new BitString(32);
        $bs->writeInt(128, 16);
        $this->assertEquals("0080", $bs->toHex());
    }

    /**
     * @throws BitStringException
     */
    public function testWriteUint8(): void
    {
        $bs = new BitString(32);
        $bs->writeUint8(255);
        $this->assertEquals("FF", $bs->toHex());
    }

    /**
     * @throws BitStringException
     */
    public function testWriteBytes(): void
    {
        $stub = new Uint8Array([0, 1, 2, 3]);
        $bs = new BitString(32);
        $bs->writeBytes($stub);

        $this->assertEquals('00010203', $bs->toHex());
    }

    /**
     * @throws BitStringException
     */
    public function testWriteString(): void
    {
        $bs = new BitString(32);
        $bs->writeString("🔥");

        $this->assertEquals("F09F94A5", $bs->toHex());
    }

    /**
     * @throws BitStringException
     */
    public function testWriteCoins(): void
    {
        $bs = new BitString(32);
        $bs->writeCoins(0);
        $this->assertEquals("0", $bs->toHex());

        $bs = new BitString(257);
        $bs->writeCoins(Units::toNano(123));
        $this->assertEquals("51CA35F0E00", $bs->toHex());
    }
}
