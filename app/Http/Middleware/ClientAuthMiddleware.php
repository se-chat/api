<?php

namespace App\Http\Middleware;

use App\Services\AESService;
use App\Services\CryptoService;
use Carbon\Carbon;
use Closure;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ClientAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @return Response|RedirectResponse|JsonResponse
     * @throws Exception
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse|JsonResponse
    {
        $sign = $request->header('Auth-Sign');
        $contentHash = $request->header('Auth-Content-Hash');
        $salt = $request->header('Auth-Salt');
        $signDate = new Carbon(strval(hex2bin($salt)));
        if ($signDate->addHours(12)->lt(now())) {
            throw new Exception('签名验证失败');
        }
        $msg = $contentHash . ':' . $salt;
        $signInfo = CryptoService::parseSign($msg, $sign);
        $request->headers->set('Auth-Address', substr($signInfo['address'], 2));
        $request->headers->set('Auth-PubKey', $signInfo['pub_key']);
        if ($request->getMethod() == "POST") {
            $sharedKey = CryptoService::getSharedSecret(config('app.system_wallet.pri_key'), $signInfo['pub_key']);
            $postData = json_decode(AESService::de($request->post('data'), $sharedKey), true) ?? [];
            $request->replace($postData);
        }
        return $next($request);
    }
}
