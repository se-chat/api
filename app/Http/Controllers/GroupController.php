<?php

namespace App\Http\Controllers;

use App\Http\Requests\GroupCreateRequest;
use App\Http\Requests\GroupQuitRequest;
use App\Models\Group;
use App\Services\ContactService;
use App\Services\GroupMemberService;
use App\Services\GroupService;
use App\Services\MessageService;
use App\Utils\HashId;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    /**
     * @param GroupCreateRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function create(GroupCreateRequest $request): JsonResponse
    {
        $member = $this->member();
        $params = $request->all(['name', 'message_expired_time', 'address', 'pub_key']);
        $params['owner_id'] = $member['id'];
        $params['nickname'] = $member['nickname'];
        DB::beginTransaction();
        try {
            $group = GroupService::create($params);
            if (!ContactService::has($member['id'], $group['id'], Group::class)) {
                ContactService::add($member['id'], $group['id'], Group::class);
            }
            DB::commit();
            $group['id'] = HashId::encode('group', $group['id']);
            return $this->success([
                'group_id' => $group['id']
            ]);
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

    }

    /**
     * @param GroupQuitRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function quit(GroupQuitRequest $request): JsonResponse
    {
        $groupId = HashId::decode('group', $request->input('id'));
        $member = $this->member();
        $group = GroupService::getById($groupId);
        if (!$group) {
            throw new Exception('群组不存在');
        }
        if ($group['owner_id'] === $member['id']) {
            throw new Exception('群主不能退出群组');
        }
        try {
            DB::beginTransaction();
            GroupMemberService::deleteByGroupIdAndMemberId($groupId, $member['id']);
            MessageService::deleteBySenderIdAndReceiverTypeGroup($member['id'], $groupId);
            ContactService::deleteByMemberIdAndGroupId($member['id'], $groupId);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
        return $this->success();
    }
}
