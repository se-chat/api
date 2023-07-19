<?php

namespace App\Services;

use App\Models\Member;
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
        $member->no = self::generateNo();
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

    public static function generateNo(): string
    {
        $length = rand(2, 5);
        // 前缀
        $prefix = rand(10, 99);
        // 混淆码
        $confusion = rand(1, 9);
        // 根据 length 长度 生成随机数
        $random = rand(pow(10, $length - 1), pow(10, $length) - 1);
        $no = $prefix . $confusion . $random;
        $member = Member::query()->where('no', $no)->exists();
        if ($member) {
            return self::generateNo();
        }
        return $no;
    }
}
