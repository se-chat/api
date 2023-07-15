<?php

namespace App\Utils;

use Vinkla\Hashids\Facades\Hashids;

class HashId
{
    public static function decode(string $connection, string $hash): string|null
    {
        $tmp = Hashids::connection($connection)->decode($hash);
        if (empty($tmp)) {
            return null;
        }
        return intval($tmp[0]);
    }

    public static function encode(string $connection, int $id): string
    {
        return Hashids::connection($connection)->encode($id);
    }

    public static function getAlphabet(): string
    {
        $base = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        return str_shuffle($base);
    }
}
