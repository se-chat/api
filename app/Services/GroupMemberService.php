<?php

namespace App\Services;

use App\Models\GroupMember;

class GroupMemberService
{
    public static function isAdmin(int $groupId, int $memberId): bool
    {
        $groupMember = GroupMember::query()
            ->where('group_id', $groupId)
            ->where('member_id', $memberId)
            ->first();
        if ($groupMember) {
            return $groupMember->role === 'admin';
        }
        return false;
    }

    public static function deleteByGroupIdAndMemberId(int $groupId, int $memberId): bool
    {
        return GroupMember::query()
            ->where('group_id', $groupId)
            ->where('member_id', $memberId)
            ->delete();
    }

    public static function getByMemberId(int $memberId): array
    {
        $groupMembers = GroupMember::query()->where('member_id', $memberId)->get();
        if ($groupMembers->count()) {
            return $groupMembers->toArray();
        }
        return [];
    }

    public static function has(int $groupId, int $memberId): bool
    {
        return GroupMember::query()
            ->where('group_id', $groupId)
            ->where('member_id', $memberId)->exists();
    }

    public static function getByGroupIdAndMemberId(int $groupId, int $memberId): array
    {
        $groupMember = GroupMember::query()
            ->where('group_id', $groupId)
            ->where('member_id', $memberId)->first();
        if ($groupMember) {
            return $groupMember->toArray();
        }
        return [];
    }

    public static function create(int $groupId, int $memberId, string $role, string $nickname): array
    {
        $groupMember = new GroupMember();
        $groupMember->nickname = $nickname;
        $groupMember->group_id = $groupId;
        $groupMember->member_id = $memberId;
        $groupMember->role = $role;
        $groupMember->save();
        return $groupMember->toArray();
    }

    public static function delete(int $groupId, int $memberId): bool
    {
        $groupMember = self::getByGroupIdAndMemberId($groupId, $memberId);
        if ($groupMember) {
            return $groupMember->delete();
        }
        return false;
    }

    public static function setRole(int $groupId, int $memberId, string $role): bool
    {
        $groupMember = self::getByGroupIdAndMemberId($groupId, $memberId);
        if ($groupMember) {
            $groupMember->role = $role;
            return $groupMember->save();
        }
        return false;
    }

    public static function getByGroupId(int $groupId): array
    {
        $groupMembers = GroupMember::query()->where('group_id', $groupId)->get();
        $members = MemberService::getByIds($groupMembers->pluck('member_id')->toArray());
        foreach ($members as &$member) {
            $groupMember = $groupMembers->where('member_id', $member->id)->first();
            $member['nickname'] = $groupMember->nickname;
            $member['role'] = $groupMember->role;
            $member['created_at'] = $groupMember->created_at;
        }
        return collect($members)->sortBy('created_at')->values()->toArray();
    }
}
