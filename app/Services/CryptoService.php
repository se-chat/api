<?php

namespace App\Services;

use Elliptic\EC;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use kornrunner\Keccak;

class CryptoService
{
    public static function getPubKey(string $priKeyHex): array
    {
        $ec = new EC('secp256k1');
        $priKey = $ec->keyFromPrivate($priKeyHex);
        $pubKey = $priKey->getPublic();
        return [
            'x' => $pubKey->x->toString('hex'),
            'y' => $pubKey->y->toString('hex')
        ];
    }

    public static function getPubKeyHex(string $priKeyHex)
    {
        $ec = new EC('secp256k1');
        $priKey = $ec->keyFromPrivate($priKeyHex);
        return $priKey->getPublic(true, 'hex');
    }

    public static function generatePriKey()
    {
        $ec = new EC('secp256k1');
        $key = $ec->genKeyPair();
        return $key->getPrivate()->toString(16, 2);
    }

    /**
     * @throws Exception
     */
    public static function verifySignature($message, $signature, $address): bool
    {
        return strtolower($address) == self::fromMessage($message, $signature);
    }

    public static function pubKeyToHex(string $x, string $y)
    {
        $ec = new EC('secp256k1');

        return $ec->keyPair([
            'pub' => [
                'x' => $x,
                'y' => $y
            ],
            'pubEnc' => 'hex'
        ])->getPublic(true, 'hex');
    }

    /**
     * @throws Exception
     */
    public static function pubKeyToAddress($x, $y): string
    {
        $hash = '0x' . Keccak::hash(hex2bin($x . $y), 256);
        $truncatedHash = substr($hash, -40);

        return '0x' . $truncatedHash;
    }

    public static function getSharedSecret($priKeyHex, $pubKeyHex)
    {
        $ec = new EC('secp256k1');
        $priKey = $ec->keyFromPrivate($priKeyHex);
        $pubKey = $ec->keyFromPublic($pubKeyHex, 'hex');
        return $priKey->derive($pubKey->pub)->toString('hex');
    }

    /**
     * @throws Exception
     */
    public static function signature(string $priKeyHex, string $msg): string
    {
        $ec = new EC('secp256k1');
        $priKey = $ec->keyFromPrivate($priKeyHex);
        $hash = Keccak::hash("\x19Ethereum Signed Message:\n" . strlen($msg) . $msg, 256);
        $signature = $priKey->sign($hash, false, ['canonical' => false]);
        $s = $signature->s->toString(16);
        $r = $signature->r->toString(16);
        if (!in_array($signature->recoveryParam, [27, 28])) {
            $v = sprintf("%02x", $signature->recoveryParam + 27);
        } else {
            $v = sprintf("%02x", $signature->recoveryParam);
        }
        return $r . $s . $v;
    }

    /**
     * @throws Exception
     */
    #[ArrayShape(['address' => "string", 'pub_key' => "mixed"])] public static function parseSign($msg, $signed): array
    {
        $msg = "\x19Ethereum Signed Message:\n" . strlen($msg) . $msg;
        $pubKey = self::signToPubkey($msg, $signed);
        $address = self::pubKeyToAddress($pubKey['x'], $pubKey['y']);
        $pubKeyHex = self::pubKeyToHex($pubKey['x'], $pubKey['y']);
        return [
            'address' => $address,
            'pub_key' => $pubKeyHex
        ];
    }

    /**
     * @throws Exception
     */
    public static function fromMessage($msg, $signed): string
    {
        $personal_prefix_msg = "\x19Ethereum Signed Message:\n" . strlen($msg) . $msg;
        return self::fromMessageRaw($personal_prefix_msg, $signed);
    }

    /**
     * @throws Exception
     */
    #[ArrayShape(['x' => "string", 'y' => "string"])] public static function signToPubKey($msg, $signed): array
    {

        $hex = '0x' . Keccak::hash($msg, 256);
        $signed = substr($signed, 2);

        $rHex = substr($signed, 0, 64);
        $sHex = substr($signed, 64, 64);
        $vValue = hexdec(substr($signed, 128, 2));

        $messageHex = substr($hex, 2);
        $messageGmp = gmp_init($messageHex, 16);

        $r = $rHex;
        $s = $sHex;
        $v = $vValue;

        $rGmp = gmp_init($r, 16);
        $sGmp = gmp_init($s, 16);

        if ($v !== 27 && $v !== 28) {
            $v += 27;
        }

        $recovery = $v - 27;
        if ($recovery !== 0 && $recovery !== 1) {
            throw new Exception('Invalid signature v value');
        }

        return self::recoverPublicKey($rGmp, $sGmp, $messageGmp, $recovery);
    }

    /**
     * @throws Exception
     */
    public static function fromMessageRaw($msg, $signed): string
    {
        $publicKey = self::signToPubkey($msg, $signed);
        $publicKeyString = $publicKey['x'] . $publicKey['y'];
        $hash = '0x' . Keccak::hash(hex2bin($publicKeyString), 256);
        $truncatedHash = substr($hash, -40);

        return '0x' . $truncatedHash;
    }

    /**
     *  Extraction and a bit gpt unroll of the original project CryptoCurrencyPHP
     * @throws Exception
     */
    #[ArrayShape(['x' => "string", 'y' => "string"])]
    public static function recoverPublicKey($R, $S, $hash, $recoveryFlags): array
    {

        $a = gmp_init('0', 10);
        $b = gmp_init('7', 10);
        $G = [
            'x' => gmp_init('55066263022277343669578718895168534326250603453777594175500187360389116729240'),
            'y' => gmp_init('32670510020758816978083085130507043184471273380659243275938904335757337482424')
        ];
        $n = gmp_init('FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141', 16);
        $p = gmp_init('FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2F', 16);

        $isYEven = ($recoveryFlags & 1) != 0;
        $isSecondKey = ($recoveryFlags & 2) != 0;

        $e = gmp_strval($hash, 16);
        $s = gmp_strval($S, 16);

        $p_over_four = gmp_div(gmp_add($p, 1), 4);


        if (!$isSecondKey) {
            $x = $R;
        } else {
            $x = gmp_add($R, $n);
        }

        $alpha = gmp_mod(gmp_add(gmp_add(gmp_pow($x, 3), gmp_mul($a, $x)), $b), $p);
        $beta = gmp_strval(gmp_powm($alpha, $p_over_four, $p));

        $y = self::isEvenNumber($beta) == $isYEven ? gmp_sub($p, $beta) : gmp_init($beta);

        $Rpt = ['x' => $x, 'y' => $y];

        $rInv = gmp_strval(gmp_invert($R, $n), 16);
        $eGNeg = self::negatePoint(self::mulPoint($e, $G, $a, $b, $p));
        $sR = self::mulPoint($s, $Rpt, $a, $b, $p);
        $sR_plus_eGNeg = self::addPoints($sR, $eGNeg, $a, $p);
        $Q = self::mulPoint($rInv, $sR_plus_eGNeg, $a, $b, $p);

        return [
            'x' => str_pad(gmp_strval($Q['x'], 16), 64, '0', STR_PAD_LEFT),
            'y' => str_pad(gmp_strval($Q['y'], 16), 64, '0', STR_PAD_LEFT)
        ];
    }

    #[ArrayShape(['x' => "mixed", 'y' => "\GMP|resource"])] public static function negatePoint($point): array
    {
        return array('x' => $point['x'], 'y' => gmp_neg($point['y']));
    }

    /**
     * @throws Exception
     */
    public static function mulPoint($k, array $pG, $a, $b, $p, $base = null): array
    {
        if ($base === 16 || $base === null || is_resource($base)) {
            $k = gmp_init($k, 16);
        } elseif ($base === 10) {
            $k = gmp_init($k, 10);
        }

        $kBin = gmp_strval($k, 2);

        $lastPoint = $pG;

        for ($i = 1, $length = strlen($kBin); $i < $length; $i++) {
            $lastPoint = self::doublePoint($lastPoint, $a, $p);

            if ($kBin[$i] === '1') {
                $lastPoint = self::addPoints($lastPoint, $pG, $a, $p);
            }
        }

        if (!self::validatePoint(gmp_strval($lastPoint['x'], 16), gmp_strval($lastPoint['y'], 16), $a, $b, $p)) {
            throw new Exception('The resulting point is not on the curve.');
        }

        return $lastPoint;
    }

    /**
     * @throws Exception
     */
    public static function doublePoint(array $pt, $a, $p): array
    {
        $twoY = gmp_mod(gmp_mul(gmp_init(2, 10), $pt['y']), $p);

        $gcd = gmp_strval(gmp_gcd($twoY, $p));
        if ($gcd !== '1') {
            throw new Exception('This library doesn\'t yet support point at infinity. See https://github.com/BitcoinPHP/BitcoinECDSA.php/issues/9');
        }

        $threeXSquare = gmp_mul(gmp_init(3, 10), gmp_pow($pt['x'], 2));
        $addThreeXSquareA = gmp_add($threeXSquare, $a);

        $invertModTwoY = gmp_invert($twoY, $p);

        $slope = gmp_mod(gmp_mul($invertModTwoY, $addThreeXSquareA), $p);

        $subSubPow = gmp_sub(gmp_sub(gmp_pow($slope, 2), $pt['x']), $pt['x']);
        $nPt['x'] = gmp_mod($subSubPow, $p);

        $subXMul = gmp_mul($slope, gmp_sub($pt['x'], $nPt['x']));
        $subXMulSubY = gmp_sub($subXMul, $pt['y']);
        $nPt['y'] = gmp_mod($subXMulSubY, $p);

        return $nPt;

    }

    public static function validatePoint($x, $y, $a, $b, $p): bool
    {
        $x = gmp_init($x, 16);
        $y2Expected = gmp_mod(gmp_add(gmp_add(gmp_powm($x, gmp_init(3, 10), $p), gmp_mul($a, $x)), $b), $p);
        $y2Actual = gmp_mod(gmp_pow(gmp_init($y, 16), 2), $p);

        return gmp_cmp($y2Expected, $y2Actual) === 0;
    }

    /**
     * @throws Exception
     */
    public static function addPoints(array $pt1, array $pt2, $a, $p): array
    {
        if (gmp_cmp($pt1['x'], $pt2['x']) === 0 && gmp_cmp($pt1['y'], $pt2['y']) === 0) {
            return self::doublePoint($pt1, $a, $p);
        }

        $deltaX = gmp_sub($pt1['x'], $pt2['x']);
        $deltaY = gmp_sub($pt1['y'], $pt2['y']);
        $gcd = gmp_strval(gmp_gcd($deltaX, $p));

        if ($gcd !== '1') {
            throw new Exception('This library doesn\'t yet support point at infinity. See https://github.com/BitcoinPHP/BitcoinECDSA.php/issues/9');
        }

        $slope = gmp_mod(gmp_mul($deltaY, gmp_invert($deltaX, $p)), $p);

        $nPt = [];
        $nPt['x'] = gmp_mod(gmp_sub(gmp_sub(gmp_pow($slope, 2), $pt1['x']), $pt2['x']), $p);
        $nPt['y'] = gmp_mod(gmp_sub(gmp_mul($slope, gmp_sub($pt1['x'], $nPt['x'])), $pt1['y']), $p);

        return $nPt;
    }

    public static function isEvenNumber($number): bool
    {
        $lastDigit = $number[strlen($number) - 1];
        return ($lastDigit % 2) === 0;
    }
}
