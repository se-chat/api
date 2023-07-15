<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Member;
use App\Models\Message;

class MessageService
{
    public static function getListByReceiverTypeMember(int $memberId, int $receiverId, int $lastId = 0): array
    {
        return Message::query()
            ->where('id', '>', $lastId)
            ->where(function ($query) use ($memberId, $receiverId) {
                $query->where('receiver_type', Member::class)
                    ->orWhere(function ($query) use ($memberId, $receiverId) {
                        $query->where('sender_id', $receiverId)
                            ->where('receiver_id', $memberId);
                    })->orWhere(function ($query) use ($memberId, $receiverId) {
                        $query->where('sender_id', $memberId)
                            ->where('receiver_id', $receiverId);
                    });
            })
            ->orderBy('id')
            ->offset(0)
            ->limit(5)
            ->get()
            ->toArray();
    }

    public static function getListByReceiverTypeGroup(int $groupId, int $lastId = 0): array
    {
        return Message::query()
            ->where('receiver_type', Group::class)
            ->where('receiver_id', $groupId)
            ->where('id', '>', $lastId)
            ->orderBy('id')
            ->limit(5)
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
