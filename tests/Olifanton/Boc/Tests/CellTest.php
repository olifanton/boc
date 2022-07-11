<?php declare(strict_types=1);

namespace Olifanton\Boc\Tests;

use Olifanton\Boc\Cell;
use Olifanton\Boc\Exceptions\BitStringException;
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

    /**
     * @throws  CellException
     */
    public function testWalletSimpleR1Marshalling(): void
    {
        $hexCode = 'B5EE9C72410101010044000084FF0020DDA4F260810200D71820D70B1FED44D0D31FD3FFD15112BAF2A122F901541044F910F2A2F80001D31F3120D74A96D307D402FB00DED1A4C8CB1FCBFFC9ED5441FDF089';
        $referenceHash = 'a0cfc2c48aee16a271f2cfc0b7382d81756cecb1017d077faaab3bb602f6868c';

        $cell0 = Cell::oneFromBoc($hexCode);

        $this->assertEquals(528, $cell0->getBits()->getUsedBits());
        $this->assertCount(0, $cell0->getRefs());
        $this->assertEquals($referenceHash, Bytes::bytesToHexString($cell0->hash()));

        $serializedHex = strtoupper(Bytes::bytesToHexString($cell0->toBoc(false, true)));
        $this->assertEquals($hexCode, $serializedHex);
    }

    /**
     * @throws CellException|BitStringException
     */
    public function testMarshallingWithRefs(): void
    {
        $referenceHash = "68b8f75d0074aed0b004ec9c50f9f030ac0815d5dc7824cab4769ba4b1112cf1";
        $referenceBoc = "b5ee9c72c1010301000b000004080101c0010102ff020001c038dda6e5";

        $cell = new Cell();
        $cell->bits->writeBit(1);

        $aCell0 = new Cell();
        $aCell0->bits->writeUint8(255);

        $aCell1 = new Cell();
        $aCell1->bits->writeBit(1);

        $aCell0->refs[] = $aCell1;
        $cell->refs[] = $aCell0;

        $this->assertEquals($referenceHash, Bytes::bytesToHexString($cell->hash()));

        $boc = $cell->toBoc();

        $this->assertEquals(
            "b5ee9c72c1010301000b000004080101c0010102ff020001c038dda6e5",
            Bytes::bytesToHexString($boc),
        );

        $cellFromBoc = Cell::fromBoc(strtoupper($referenceBoc))[0];
        $this->assertEquals(
            $referenceHash,
            Bytes::bytesToHexString($cellFromBoc->hash()),
        );
    }

    /**
     * @throws CellException|BitStringException
     */
    public function testWithWriteCell(): void
    {
        $cell0 = new Cell();
        $cell0->bits->writeUint8(1);

        $cell1 = new Cell();
        $cell1->bits->writeUint8(2);

        $cell0->writeCell($cell1);

        $this->assertEquals(
            "b5ee9c72c1010101000400000004010268bb104c",
            Bytes::bytesToHexString($cell0->toBoc()),
        );
    }

    /**
     * @throws CellException|BitStringException
     */
    public function testPrint(): void
    {
        $cell0 = new Cell();
        $cell0->bits->writeUint8(1);

        $cell1 = new Cell();
        $cell1->bits->writeUint8(2);
        $cell1->refs[] = (static function() {
            $c = new Cell();
            $c->bits->writeUint8(3);

            return $c;
        })();

        $cell0->writeCell($cell1);

        $this
            ->assertEquals(
                "x{0102}\n x{03}\n",
                $cell0->print(),
            );
    }

    /**
     * @throws CellException|BitStringException
     */
    public function testComplexRefs(): void
    {
        $cell0 = new Cell();
        $cell0->bits->writeUint8(1);

        $cell1 = new Cell();
        $cell1->bits->writeUint8(2);
        $cell1->refs[] = ((static function () {
            $c = new Cell();
            $c->bits->writeUint8(3);

            return $c;
        })());

        $cell2 = new Cell();
        $cell2->bits->writeUint8(4);
        $cell2->refs[] = ((static function () {
            $c = new Cell();
            $c->bits->writeUint8(5);

            return $c;
        })());

        $cell0->refs[] = $cell2;

        $cell1->refs[] = $cell2;

        $cell0->writeCell($cell1);
        $cell0->refs[] = $cell1;
        $cell0->refs[] = $cell2;

        $this
            ->assertEquals(
                "b5ee9c72c101050100180000090e1115050401020302030103020202020300020301020404000205bad5a376",
                Bytes::bytesToHexString($cell0->toBoc()),
            );
    }
}
