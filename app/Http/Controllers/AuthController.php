<?php

namespace App\Http\Controllers;

use App\Services\ContactService;
use App\Services\GroupMemberService;
use App\Services\GroupService;
use App\Services\MemberService;
use App\Services\MessageService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * @throws Exception
     */
    public function register(Request $request): JsonResponse
    {
        $params = $request->all(['nickname']);
        $params['address'] = $this->authAddress();
        $params['pub_key'] = $this->authPubKey();
        MemberService::create($params);
        return $this->success();
    }

    /**
     * @throws Exception
     */
    public function info(): JsonResponse
    {
        $member = $this->member();
        unset($member['created_at'], $member['updated_at'], $member['id']);
        $member['nickname'] = mb_substr($member['nickname'], 0, 4, 'utf-8');
        return $this->success([
            'auth_info' => $member
        ]);
    }

    /**
     * @throws Exception
     */
    public function destroy(): JsonResponse
    {
        $member = $this->member();
        MessageService::deleteBySenderId($member['id']);
        ContactService::deleteByMemberId($member['id']);
        GroupService::deleteByMemberId($member['id']);
        MemberService::delete($member['id']);

        return $this->success();
    }
}
