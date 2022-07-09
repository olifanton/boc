<?php declare(strict_types=1);

namespace Olifanton\Boc\Tests;

use Olifanton\Boc\Cell;
use Olifanton\Boc\Exceptions\CellException;
use Olifanton\Utils\Bytes;
use PHPUnit\Framework\TestCase;

class CellTest extends TestCase
{
    /**
     * @throws CellException
     */
    public function testWalletV3R2Marshalling(): void
    {
        // Deserialize v3r2 wallet
        $base64Code = 'te6cckEBAQEAcQAA3v8AIN0gggFMl7ohggEznLqxn3Gw7UTQ0x/THzHXC//jBOCk8mCDCNcYINMf0x/TH/gjE7vyY+1E0NMf0x/T/9FRMrryoVFEuvKiBPkBVBBV+RDyo/gAkyDXSpbTB9QC+wDo0QGkyMsfyx/L/8ntVBC9ba0=';
        $hexCode = 'B5EE9C724101010100710000DEFF0020DD2082014C97BA218201339CBAB19F71B0ED44D0D31FD31F31D70BFFE304E0A4F2608308D71820D31FD31FD31FF82313BBF263ED44D0D31FD31FD3FFD15132BAF2A15144BAF2A204F901541055F910F2A3F8009320D74A96D307D402FB00E8D101A4C8CB1FCB1FCBFFC9ED5410BD6DAD';

        $referenceHash = "84dafa449f98a6987789ba232358072bc0f76dc4524002a5d0918b9a75d2d599";

        $boc0 = Bytes::base64ToBytes($base64Code);
        $cell0 = Cell::oneFromBoc($boc0);

        $this->assertEquals(888, $cell0->getBits()->getUsedBits());
        $this->assertCount(0, $cell0->getRefs());
        $this->assertEquals($referenceHash, Bytes::bytesToHexString($cell0->hash()));

        $cell1 = Cell::oneFromBoc($hexCode);

        $this->assertEquals(888, $cell1->getBits()->getUsedBits());
        $this->assertCount(0, $cell1->getRefs());
        $this->assertEquals($referenceHash, Bytes::bytesToHexString($cell1->hash()));

        $serializedHex = strtoupper(Bytes::bytesToHexString($cell0->toBoc(false, true)));
        $this->assertEquals($hexCode, $serializedHex);
    }
}
