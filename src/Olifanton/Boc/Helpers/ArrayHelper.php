<?php declare(strict_types=1);

namespace Olifanton\Boc\Helpers;

use ajf\TypedArrays\Uint8Array;
use Olifanton\Utils\Bytes;

final class ArrayHelper
{
    public static function sliceUint8Array(Uint8Array $arr, int $start, ?int $end = null): Uint8Array
    {
        if ($end === null) {
            $end = $arr->length;
        }

        return Bytes::arraySlice($arr, $start, $end);
    }
}
