<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Member;
use App\Models\Message;

class MessageService
{
    public static function getListByReceiverTypeMember(int $memberId, int $receiverId, int $limit, int $lastId, string $type): array
    {
        $query = Message::query()
            ->where('receiver_type', Member::class)
            ->whereIn('sender_id', [$memberId, $receiverId])
            ->whereIn('receiver_id', [$memberId, $receiverId]);
        if ($type == 'after') {
            $query = $query->where('id', '>', $lastId)->latest('id');
        } else {
            $query = $query->where('id', '<', $lastId)->orderBy('id');
        }
        return $query->offset(0)
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public static function getListByReceiverTypeGroup(int $groupId, int $limit, int $lastId, string $type): array
    {
        $query = Message::query()
            ->where('receiver_type', Group::class)
            ->where('receiver_id', $groupId);
        if ($type == 'after') {
            $query = $query->where('id', '>', $lastId)->latest('id');

        } else {
            $query = $query->where('id', '<', $lastId)->orderBy('id');
        }
        return $query->offset(0)
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public static function deleteBySenderId(int $senderId): bool
    {
        return Message::query()
            ->where('sender_id', $senderId)
            ->delete();
    }


    public static function deleteExpiredMessage(): bool
    {
        return Message::query()
            ->where('expired_at', '<', now())
            ->delete();
    }

    public static function create(array $params): array
    {
        $message = new Message();

        $message->sender_id = $params['sender_id'];
        $message->type = $params['type'];
        $message->receiver_id = $params['receiver_id'];
        $message->receiver_type = $params['receiver_type'];
        $message->content = $params['content'];
        $message->expired_at = $params['expired_at'];
        $message->save();
        return $message->toArray();
    }

    public static function deleteBySenderIdAndReceiverTypeGroup(int $senderId, int $groupId): bool
    {
        return Message::query()
            ->where('sender_id', $senderId)
            ->where('receiver_type', Group::class)
            ->where('receiver_id', $groupId)
            ->delete();
    }
}
