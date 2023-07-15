<?php

namespace App\Services;

use App\Models\Member;
use App\Utils\HashId;

class MemberService
{
    public static function getByNo(string $no): array
    {
        $member = Member::query()->where('no', $no)->first();
        if ($member) {
            return $member->toArray();
        }
        return [];
    }

    public static function search(string $keyword): array
    {
        return Member::query()
            ->where('no', 'LIKE', '%' . $keyword . '%')
            ->orWhere('address', 'LIKE', '%' . $keyword . '%')
            ->limit(10)
            ->get()->toArray();
    }

    public static function getById(int $id): array
    {
        $member = Member::query()->find($id);
        if ($member) {
            return $member->toArray();
        }
        return [];
    }

    public static function getByAddress(string $address): array
    {
        $member = Member::query()->where('address', $address)->first();
        if ($member) {
            return $member->toArray();
        }
        return [];
    }

    public static function create(array $data): array
    {
        if ($oldMember = self::getByAddress($data['address'])) {
            return $oldMember;
        }
        $member = new Member();
        $member->nickname = $data['nickname'] ?? '新用户';
        $member->avatar = $data['avatar'] ?? '';
        $member->address = $data['address'];
        $member->pub_key = $data['pub_key'];
        $member->save();
        $member->no = HashId::encode('member', $member->id);
        $member->save();
        return $member->toArray();
    }

    public static function delete(int $id): bool
    {
        $member = Member::query()->find($id);
        if ($member) {
            return $member->delete();
        }
        return false;
    }

    public static function getByIds(array $ids): array
    {
        $members = Member::query()->whereIn('id', $ids)->get();
        return $members->toArray();
    }
    // 传入 id
    // 将 id 转为 十六进制字符
    // 字符串转为二进制
    // 在将二进制
}
