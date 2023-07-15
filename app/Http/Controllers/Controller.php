<?php

namespace App\Http\Controllers;

use App\Services\AESService;
use App\Services\CryptoService;
use App\Services\MemberService;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function member(): array
    {
        $address = request()->header('Auth-Address');
        return MemberService::getByAddress($address);
    }

    public function authAddress(): string
    {
        return request()->header('Auth-Address') ?? '';
    }

    public function authPubKey(): string
    {
        return request()->header('Auth-PubKey') ?? '';
    }

    /**
     * @throws Exception
     */
    public function success(array|null $data = null, $crypt = true): JsonResponse
    {
        $enData = null;
        if ($crypt && !empty($data)) {
            $pubKey = request()->header('Auth-PubKey');
            $systemPriKey = config('app.system_wallet.pri_key');
            $sharedKey = CryptoService::getSharedSecret($systemPriKey, $pubKey);
            $dataStr = json_encode($data);
            $enData = AESService::en($dataStr, $sharedKey);
        }
        return response()->json([
            'code' => 200,
            'data' => $enData ?? $data
        ]);
    }
}
