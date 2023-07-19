<?php

namespace App\Services;

use App\Models\Group;

class GroupService
{
    public static function getById(int $id): array
    {
        $group = Group::query()->find($id);
        if ($group) {
            return $group->toArray();
        }
        return [];
    }

    public static function create(array $data): array
    {
        $group = new Group();
        $group->name = $data['name'];
        $group->avatar = $data['avatar'] ?? '';
        $group->message_expired_time = $data['message_expired_time'] ?? 30;
        $group->owner_id = $data['owner_id'];
        $group->save();
        GroupMemberService::create($group->id, $data['owner_id'], 'owner', $data['nickname']);
        return $group->toArray();
    }

    public static function setOwner(int $groupId, int $memberId): bool
    {
        $group = self::getById($groupId);
        if ($group) {
            $group->owner_id = $memberId;
            return $group->save();
        }
        return false;
    }

    public static function delete(int $id): bool
    {
        $group = Group::query()->find($id);
        if ($group) {
            return $group->delete();
        }
        return false;
    }

    public static function deleteByMemberId(int $memberId): bool
    {
        $groups = GroupMemberService::getByMemberId($memberId);
        foreach ($groups as $group) {
            if ($group['owner_id'] == $memberId) {
                $members = GroupMemberService::getByGroupId($group['id']);
                if ($firstMember = $members[0] ?? null) {
                    self::setOwner($group['id'], $firstMember['member_id']);
                } else {
                    self::delete($group['id']);
                }
            }
            GroupMemberService::deleteByGroupIdAndMemberId($group['id'], $memberId);
        }
        return true;
    }
}
