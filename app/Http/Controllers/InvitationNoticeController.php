<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvitationNoticeAcceptRequest;
use App\Http\Requests\InvitationNoticeRejectRequest;
use App\Models\Group;
use App\Models\Member;
use App\Services\InvitationNoticeService;
use App\Utils\HashId;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvitationNoticeController extends Controller
{
    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function getList(): JsonResponse
    {
        $member = $this->member();
        $notices = InvitationNoticeService::getListByMemberId($member['id']);
        foreach ($notices as &$notice) {
            $notice['id'] = HashId::encode('invitation-notice', $notice['id']);
            switch ($notice['business_type']) {
                case Group::class:
                    $notice['business_type'] = 'group';
                    $notice['business_id'] = HashId::encode('group', $notice['business_id']);
                    break;
                case Member::class:
                    $notice['business_id'] = HashId::encode('member', $notice['business_id']);
                    $notice['business_type'] = 'friend';
                    break;
                default:
                    $notice['business_type'] = 'unknown';
            }
            unset($notice['expired_at'], $notice['member_id'], $notice['created_at'], $notice['updated_at']);
        }
        return $this->success([
            'notices' => $notices
        ]);
    }


    /**
     * @throws ValidationException
     * @throws Exception
     */
    public function accept(InvitationNoticeAcceptRequest $request): JsonResponse
    {
        $member = $this->member();
        $invitationNoticeHashId = $request->input('id');
        $invitationNoticeId = HashId::decode('invitation-notice', $invitationNoticeHashId);
        $invitationNotice = InvitationNoticeService::getById($invitationNoticeId);
        if (!$invitationNotice) {
            throw new Exception('邀请不存在');
        }
        if ($invitationNotice['member_id'] !== $member['id']) {
            throw new Exception('邀请不存在');
        }
        DB::beginTransaction();
        try {
            InvitationNoticeService::accept($invitationNoticeId);
            DB::commit();
            return $this->success();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * @param InvitationNoticeRejectRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function reject(InvitationNoticeRejectRequest $request): JsonResponse
    {
        $member = $this->member();
        $invitationNoticeHashId = $request->input('id');
        $invitationNoticeId = HashId::decode('invitation-notice', $invitationNoticeHashId);
        $invitationNotice = InvitationNoticeService::getById($invitationNoticeId);
        if (!$invitationNotice) {
            throw new Exception('邀请不存在');
        }
        if ($invitationNotice['member_id'] !== $member['id']) {
            throw new Exception('邀请不存在');
        }
        InvitationNoticeService::reject($invitationNoticeId);
        return $this->success();
    }
}
