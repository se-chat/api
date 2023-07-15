<?php

namespace App\Services;



use phpseclib3\Crypt\AES;

class AESService
{
    /**
     * @param string $val
     * @param string $key
     * @return string
     */
    public static function en(string $val,string $key): string
    {
        $key = substr(hash('sha256',$key),0,16);
        $cipher = new AES('cbc');
        $cipher->setIV($key);
        $cipher->setKey($key);
        $ciphertext = $cipher->encrypt($val);
        return bin2hex($ciphertext);
    }

    /**
     * @param string $val
     * @param string $key
     * @return bool|int|string
     */
    public static function de(string $val,string $key): bool|int|string
    {
        $key = substr(hash('sha256',$key),0,16);
        $cipher = new AES('cbc');
        $cipher->setIV($key);
        $cipher->setKey($key);
        return $cipher->decrypt(hex2bin($val));
    }
}
