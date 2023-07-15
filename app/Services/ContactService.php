<?php

namespace App\Services;


use App\Models\Contact;
use App\Models\Group;
use App\Models\Member;
use Illuminate\Database\Eloquent\Builder;

class ContactService
{
    public static function getById(int $id): array
    {
        $contact = Contact::query()->find($id);
        if ($contact) {
            return $contact->toArray();
        }
        return [];
    }

    public static function add(int $memberId, int $businessId, string $businessType): bool
    {
        $contact = new Contact();
        $contact->member_id = $memberId;
        $contact->business_id = $businessId;
        $contact->business_type = $businessType;
        return $contact->save();
    }

    public static function getListByMemberId(int $memberId): array
    {
        return Contact::query()
            ->where('member_id', $memberId)
            ->get()->toArray();
    }

    public static function deleteByMemberId(int $memberId): bool
    {
        return Contact::query()
            ->where('member_id', $memberId)
            ->orWhere(function (Builder $query) use ($memberId) {
                $query
                    ->where('business_type', Member::class)
                    ->where('business_id', $memberId);
            })
            ->delete();
    }

    public static function isFriend($memberId, $friendId): bool
    {
        return self::has($memberId, $friendId, Member::class);
    }

    public static function has($memberId, $businessId, $businessType): bool
    {
        return Contact::query()
            ->where('member_id', $memberId)
            ->where('business_id', $businessId)
            ->where('business_type', $businessType)
            ->exists();
    }

    public static function deleteByMemberIdAndGroupId(int $memberId, int $groupId): bool
    {
        return Contact::query()
            ->where('member_id', $memberId)
            ->where('business_id', $groupId)
            ->where('business_type', Group::class)
            ->delete();
    }
}
