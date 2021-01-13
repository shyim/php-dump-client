<?php

namespace PhpDumpClient;

class Uuid
{
    public static function randomHex(): string
    {
        $hex = bin2hex(random_bytes(16));
        $timeHi = self::applyVersion(mb_substr($hex, 12, 4), 4);
        $clockSeqHi = self::applyVariant(hexdec(mb_substr($hex, 16, 2)));

        return sprintf(
            '%08s-%04s-%04s-%02s%02s-%012s',
            // time low
            mb_substr($hex, 0, 8),
            // time mid
            mb_substr($hex, 8, 4),
            // time high and version
            str_pad(dechex($timeHi), 4, '0', \STR_PAD_LEFT),
            // clk_seq_hi_res
            str_pad(dechex($clockSeqHi), 2, '0', \STR_PAD_LEFT),
            // clock_seq_low
            mb_substr($hex, 18, 2),
            // node
            mb_substr($hex, 20, 12)
        );
    }

    private static function applyVersion(string $timeHi, int $version): int
    {
        $timeHi = hexdec($timeHi) & 0x0fff;
        $timeHi &= ~(0xf000);
        $timeHi |= $version << 12;

        return $timeHi;
    }

    private static function applyVariant(int $clockSeqHi): int
    {
        // Set the variant to RFC 4122
        $clockSeqHi &= 0x3f;
        $clockSeqHi &= ~(0xc0);
        $clockSeqHi |= 0x80;

        return $clockSeqHi;
    }
}
