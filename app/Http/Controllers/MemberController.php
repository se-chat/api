<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberSearchRequest;
use App\Services\ContactService;
use App\Services\GroupMemberService;
use App\Services\MemberService;
use App\Utils\HashId;
use Exception;
use Illuminate\Http\JsonResponse;

class MemberController extends Controller
{
    /**
     * @param MemberSearchRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function search(MemberSearchRequest $request): JsonResponse
    {
        $authMember = $this->member();
        $q = $request->input('q');
        $members = MemberService::search($q);
        $members = collect($members)->filter(function ($member) use ($authMember) {
            return $member['id'] != $authMember['id'];
        })->map(function ($member) use ($authMember, $request) {
            $member['group_status'] = false;
            $member['group_role'] = '';
            $groupHashId = $request->input('group_id');
            if ($groupHashId) {
                $groupId = HashId::decode('group', $groupHashId);
                $groupMember = GroupMemberService::getByGroupIdAndMemberId($groupId, $member['id']);
                if ($groupMember) {
                    $member['group_status'] = true;
                    $member['group_role'] = $groupMember['role'];
                }
            }
            $member['friend_status'] = ContactService::isFriend($authMember['id'], $member['id']);
            unset($member['created_at'], $member['updated_at'], $member['id']);
            return $member;
        })->toArray();
        return $this->success([
            'members' => $members
        ]);
    }
}
