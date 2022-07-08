<?php declare(strict_types=1);

namespace Olifanton\Boc\Helpers;

use ajf\TypedArrays\Uint8Array;
use Olifanton\Utils\Bytes;

final class BocMagicPrefix
{
    private static ?Uint8Array $reachBocMagicPrefix = null;

    private static ?Uint8Array $leanBocMagicPrefix = null;

    private static ?Uint8Array $leanBocMagicPrefixCRC = null;

    public static function reachBocMagicPrefix(): Uint8Array
    {
        if (!self::$reachBocMagicPrefix) {
            self::$reachBocMagicPrefix = Bytes::hexStringToBytes("b5ee9c72");
        }

        return self::$reachBocMagicPrefix;
    }

    public static function leanBocMagicPrefix(): Uint8Array
    {
        if (!self::$leanBocMagicPrefix) {
            self::$leanBocMagicPrefix = Bytes::hexStringToBytes("68ff65f3");
        }

        return self::$leanBocMagicPrefix;
    }

    public static function leanBocMagicPrefixCRC(): Uint8Array
    {
        if (!self::$leanBocMagicPrefixCRC) {
            self::$leanBocMagicPrefixCRC = Bytes::hexStringToBytes("acc3a728");
        }

        return self::$leanBocMagicPrefixCRC;
    }
}
