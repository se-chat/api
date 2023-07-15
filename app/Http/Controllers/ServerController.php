<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;

class ServerController extends Controller
{
    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function getPubKey(): JsonResponse
    {
        $pubKey = config('app.system_wallet.pub_key');
        if (!$pubKey) {
            throw new Exception('服务器公钥不存在');
        }
        return $this->success([
            'pub_key' => $pubKey,
        ], false);
    }
}
