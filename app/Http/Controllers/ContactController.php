<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactGetInfoRequest;
use App\Models\Group;
use App\Models\Member;
use App\Services\ContactService;
use App\Services\GroupMemberService;
use App\Services\GroupService;
use App\Services\InvitationNoticeService;
use App\Services\MemberService;
use App\Utils\HashId;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * @param ContactGetInfoRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function getInfo(ContactGetInfoRequest $request): JsonResponse
    {
        $member = $this->member();
        $contactHashId = $request->input('id');
        $contactId = HashId::decode('contact', $contactHashId);
        $contact = ContactService::getById($contactId);
        if (!$contact) {
            throw new Exception('联系人不存在');
        }
        unset($contact['member_id'], $contact['created_at'], $contact['updated_at']);
        switch ($contact['business_type']) {
            case Member::class:
                $friend = MemberService::getById($contact['business_id']);
                $contact['name'] = $friend['nickname'];
                $friend['id'] = $contact['business_id'] = HashId::encode('member', $contact['business_id']);
                $contact['business_type'] = 'friend';
                unset($friend['created_at'], $friend['updated_at']);
                $contact['business'] = $friend;
                break;
            case Group::class:
                $group = GroupService::getById($contact['business_id']);
                $contact['name'] = $group['name'];
                $group['is_admin'] = GroupMemberService::isAdmin($contact['business_id'], $member['id']);
                $group['id'] = $contact['business_id'] = HashId::encode('group', $contact['business_id']);
                $contact['business_type'] = 'group';
                $group['is_owner'] = $group['owner_id'] === $member['id'];
                unset($group['created_at'], $group['updated_at'], $group['owner_id']);
                $contact['business'] = $group;
                break;
            default:
                $contact['business_type'] = 'unknown';
        }
        $contact['id'] = $contactHashId;
        return $this->success([
            'contact' => $contact
        ]);
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function getList(): JsonResponse
    {
        $member = $this->member();
        $contacts = ContactService::getListByMemberId($member['id']);
        foreach ($contacts as &$contact) {
            switch ($contact['business_type']) {
                case Member::class:
                    $contact['business_type'] = 'friend';
                    $friend = MemberService::getById($contact['business_id']);
                    $contact['name'] = $friend['nickname'];
                    break;
                case Group::class:
                    $contact['business_type'] = 'group';
                    $group = GroupService::getById($contact['business_id']);
                    $contact['name'] = $group['name'];
                    $contact['business_id'] = HashId::encode('group', $contact['business_id']);
                    break;
                default:
                    break;
            }
            $contact['id'] = HashId::encode('contact', $contact['id']);
            unset($contact['business_id'], $contact['member_id'], $contact['created_at'], $contact['updated_at']);
        }
        return $this->success([
            'contacts' => $contacts
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function addFriend(Request $request): JsonResponse
    {
        $authMember = $this->member();
        $no = $request->input('no');
        $member = MemberService::getByNo($no);
        if (!$member) {
            throw new Exception('用户不存在');
        }

        if ($member['id'] == $authMember['id']) {
            throw new Exception('不能添加自己为好友');
        }
        $data = [
            'title' => '我是' . $authMember['nickname'] . '，请求添加你为好友',
            'member_id' => $member['id'],
            'business_type' => Member::class,
            'business_id' => $authMember['id'],
            'pub_key' => $authMember['pub_key'],
            'expired_at' => now()->addHours(6)
        ];
        if (InvitationNoticeService::has($data['member_id'], $data['business_id'], $data['business_type'])) {
            throw new Exception('已经发送过好友请求');
        }

        InvitationNoticeService::create($data);
        return $this->success();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function addGroup(Request $request): JsonResponse
    {
        $authMember = $this->member();
        $params = $request->all(['member_id', 'group_id', 'content']);
        $memberId = HashId::decode('member', $params['member_id']);
        $member = MemberService::getById($memberId);
        if (!$member) {
            throw new Exception('用户不存在');
        }
        $groupId = HashId::decode('group', $params['group_id']);
        $group = GroupService::getById($groupId);
        if (!$group) {
            throw new Exception('群组不存在');
        }
        if (GroupMemberService::has($groupId, $member['id'])) {
            throw new Exception('已经是群成员');
        }
        $data = [
            'title' => $authMember['nickname'] . '，邀请你加入：' . $group['name'] . '群组',
            'member_id' => $member['id'],
            'business_type' => Group::class,
            'business_id' => $groupId,
            'content' => $params['content'],
            'pub_key' => $authMember['pub_key'],
            'expired_at' => now()->addHours(6)
        ];
        if (InvitationNoticeService::has($data['member_id'], $data['business_id'], $data['business_type'])) {
            throw new Exception('已经发送过邀请!');
        }

        InvitationNoticeService::create($data);
        return $this->success();
    }
}
