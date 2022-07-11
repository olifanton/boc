<?php declare(strict_types=1);

namespace Olifanton\Boc\Tests\Helpers;

use Olifanton\Boc\Helpers\BocMagicPrefix;
use Olifanton\Utils\Bytes;
use PHPUnit\Framework\TestCase;

class BocMagicPrefixTest extends TestCase
{
    public function testConstant(): void
    {
        $this->assertTrue(Bytes::compareBytes(Bytes::hexStringToBytes("b5ee9c72"), BocMagicPrefix::reachBocMagicPrefix()));
        $this->assertTrue(Bytes::compareBytes(Bytes::hexStringToBytes("68ff65f3"), BocMagicPrefix::leanBocMagicPrefix()));
        $this->assertTrue(Bytes::compareBytes(Bytes::hexStringToBytes("acc3a728"), BocMagicPrefix::leanBocMagicPrefixCRC()));
    }
}
