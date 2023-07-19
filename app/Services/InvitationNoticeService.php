<?php

namespace App\Services;

use App\Models\Group;
use App\Models\InvitationNotice;
use App\Models\Member;

class InvitationNoticeService
{
    public static function reject(int $id): bool
    {
        return InvitationNotice::query()->where('id', $id)->delete();
    }

    public static function accept(int $id): bool
    {
        $invitationNotice = self::getById($id);
        if ($invitationNotice) {
            switch ($invitationNotice['business_type']) {
                case Group::class:
                    if (!ContactService::has($invitationNotice['member_id'], $invitationNotice['business_id'], Group::class)) {
                        ContactService::add($invitationNotice['member_id'], $invitationNotice['business_id'], Group::class);
                        $member = MemberService::getById($invitationNotice['member_id']);
                        GroupMemberService::create($invitationNotice['business_id'], $invitationNotice['member_id'], 'member', $member['nickname']);
                    }
                    break;
                case Member::class:
                    if (!ContactService::has($invitationNotice['member_id'], $invitationNotice['business_id'], Member::class)) {
                        ContactService::add($invitationNotice['member_id'], $invitationNotice['business_id'], Member::class);
                    }
                    if (!ContactService::has($invitationNotice['business_id'], $invitationNotice['member_id'], Member::class)) {
                        ContactService::add($invitationNotice['business_id'], $invitationNotice['member_id'], Member::class);
                    }

                    break;
                default:
                    break;
            }
        }
        InvitationNotice::query()->where('id', $id)->delete();
        return true;

    }

    public static function getById(int $id): array
    {
        $notice = InvitationNotice::query()->find($id);
        if ($notice) {
            return $notice->toArray();
        }
        return [];
    }

    public static function has(int $memberId, int $businessId, string $businessType): bool
    {
        return InvitationNotice::query()
            ->where('member_id', $memberId)
            ->where('business_id', $businessId)
            ->where('business_type', $businessType)
            ->where('expired_at', '>', now())
            ->exists();
    }

    public static function create(array $data): array
    {
        $invitationNotice = new InvitationNotice();
        $invitationNotice->title = $data['title'];
        $invitationNotice->member_id = $data['member_id'];
        $invitationNotice->business_id = $data['business_id'];
        $invitationNotice->business_type = $data['business_type'];
        $invitationNotice->expired_at = $data['expired_at'];
        $invitationNotice->save();
        return $invitationNotice->toArray();
    }

    public static function getListByMemberId(int $memberId): array
    {
        return InvitationNotice::query()
            ->where('member_id', $memberId)
            ->where('expired_at', '>', now())
            ->orderBy('id', 'desc')
            ->get()->toArray();
    }

    public static function deleteExpired(): bool
    {
        return InvitationNotice::query()
            ->where('expired_at', '<', now())
            ->delete();
    }
}
