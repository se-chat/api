<?php

namespace App\Http\Controllers;

use App\Http\Requests\MessageGetListRequest;
use App\Http\Requests\MessageSendRequest;
use App\Models\Group;
use App\Models\Member;
use App\Services\ContactService;
use App\Services\GroupMemberService;
use App\Services\GroupService;
use App\Services\MemberService;
use App\Services\MessageService;
use App\Utils\HashId;
use Exception;
use Illuminate\Http\JsonResponse;

class MessageController extends Controller
{
    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function clearAll(): JsonResponse
    {
        $member = $this->member();
        MessageService::deleteBySenderId($member['id']);
        return $this->success();
    }

    /**
     * @param MessageGetListRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function getList(MessageGetListRequest $request): JsonResponse
    {
        $member = $this->member();
        $params = $request->all(['contact_id', 'last_id', 'type']);
        $contactId = HashId::decode('contact', $params['contact_id']);
        $contact = ContactService::getById($contactId);
        if (!$contact) {
            throw new Exception('联系人不存在');
        }
        if ($contact['member_id'] !== $member['id']) {
            throw new Exception('联系人不存在');
        }
        $lastId = 0;
        if ($params['last_id']) {
            $lastId = HashId::decode('message', $params['last_id']);
        }
        $messages = [];
        if ($contact['business_type'] == Member::class) {
            $messages = MessageService::getListByReceiverTypeMember($member['id'], $contact['business_id'], 5, $lastId, $params['type']);
        } else if ($contact['business_type'] == Group::class) {
            $messages = MessageService::getListByReceiverTypeGroup($contact['business_id'], 5, $lastId, $params['type']);
        }
        $members = MemberService::getByIds(array_column($messages, 'sender_id'));
        foreach ($members as &$member) {
            unset($member['created_at'], $member['updated_at'], $member['pub_key'], $member['no']);
        }
        $members = collect($members)->keyBy('id');
        usort($messages, function ($a, $b) {
            return $a['id'] - $b['id'];
        });
        foreach ($messages as &$message) {
            $message['id'] = HashId::encode('message', $message['id']);
            $message['sender'] = $members[$message['sender_id']] ?? null;
            $message['sender']['id'] = HashId::encode('member', $message['sender']['id']);
            unset($message['sender_id'], $message['receiver_id'], $message['receiver_type'], $message['updated_at'], $message['expired_at']);
        }

        return $this->success([
            'messages' => $messages
        ]);
    }

    /**
     * @param MessageSendRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function send(MessageSendRequest $request): JsonResponse
    {
        $member = $this->member();
        $params = $request->all(['type', 'content', 'contact_id']);
        $contactId = HashId::decode('contact', $params['contact_id']);
        $contact = ContactService::getById($contactId);
        if (!$contact) {
            throw new Exception('联系人不存在');
        }
        $params['sender_id'] = $member['id'];
        $params['receiver_id'] = $contact['business_id'];
        $params['receiver_type'] = $contact['business_type'];
        switch ($contact['business_type']) {
            case Group::class:
                if (!GroupMemberService::has($contact['business_id'], $member['id'])) {
                    throw new Exception('你不是该群成员');
                }
                $group = GroupService::getById($contact['business_id']);
                $params['expired_at'] = now()->addHours($group['message_expired_time']);
                break;
            case Member::class:
                if (!ContactService::has($member['id'], $contact['business_id'], Member::class)) {
                    throw new Exception('你不是该用户的好友');
                }
                $params['expired_at'] = now()->addHours(24);
                break;
            default:
                throw new Exception('不支持的联系人类型');
        }
        MessageService::create($params);
        return $this->success();
    }
}
