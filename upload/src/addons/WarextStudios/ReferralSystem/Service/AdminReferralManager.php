<?php

namespace WarextStudios\ReferralSystem\Service;

use WarextStudios\ReferralSystem\Entity\Referral;
use XF\Entity\User;
use XF\Service\AbstractService;

class AdminReferralManager extends AbstractService
{
    public function approve(int $referralId, User $actor): Referral
    {
        return $this->changeStatus($referralId, $actor, 'valid');
    }

    public function setPending(int $referralId, User $actor): Referral
    {
        return $this->changeStatus($referralId, $actor, 'pending');
    }

    public function reject(int $referralId, User $actor): Referral
    {
        return $this->changeStatus($referralId, $actor, 'rejected');
    }

    public function delete(int $referralId, User $actor): void
    {
        $db = $this->app->db();
        $db->beginTransaction();
        $inviterUserId = 0;
        $wasValid = false;

        try
        {
            $referral = $this->getLockedReferral($referralId);
            $this->assertActorCanManage($actor, $referral);

            $inviterUserId = (int)$referral->inviter_user_id;
            $wasValid = (string)$referral->status === 'valid';
            $referral->delete();
            $db->commit();
        }
        catch (\Throwable $e)
        {
            $db->rollback();
            throw $e;
        }

        if ($wasValid && $inviterUserId > 0)
        {
            $this->reconcileRewards($inviterUserId);
        }
    }

    protected function changeStatus(int $referralId, User $actor, string $status): Referral
    {
        if (!in_array($status, ['valid', 'pending', 'rejected'], true))
        {
            throw new \InvalidArgumentException('Geçersiz davet durumu.');
        }

        $db = $this->app->db();
        $db->beginTransaction();
        $inviterUserId = 0;
        $oldStatus = '';

        try
        {
            $referral = $this->getLockedReferral($referralId);
            $this->assertActorCanManage($actor, $referral);

            $oldStatus = (string)$referral->status;
            $inviterUserId = (int)$referral->inviter_user_id;

            if ($status === 'valid')
            {
                $this->assertCanForceApprove($referral);
                $referral->status = 'valid';
                $referral->validated_date = \XF::$time;
                $referral->reviewed_date = \XF::$time;
                $referral->reviewed_by = (int)$actor->user_id;
                $referral->risk_reason = 'manual_approved';
            }
            elseif ($status === 'rejected')
            {
                $referral->status = 'rejected';
                $referral->validated_date = 0;
                $referral->reviewed_date = \XF::$time;
                $referral->reviewed_by = (int)$actor->user_id;
                $referral->risk_reason = 'manual_rejected';
            }
            else
            {
                $referral->status = 'pending';
                $referral->validated_date = 0;
                $referral->reviewed_date = 0;
                $referral->reviewed_by = 0;
                $referral->risk_reason = '';
            }

            $referral->save();
            $db->commit();
        }
        catch (\Throwable $e)
        {
            $db->rollback();
            throw $e;
        }

        if ($inviterUserId > 0 && ($oldStatus === 'valid' || $status === 'valid'))
        {
            $this->reconcileRewards($inviterUserId);
        }

        return $referral;
    }

    protected function getLockedReferral(int $referralId): Referral
    {
        if ($referralId <= 0)
        {
            throw new \InvalidArgumentException('Davet kaydı bulunamadı.');
        }

        $lockedId = (int)$this->app->db()->fetchOne(
            'SELECT referral_id FROM xf_wrxt_referral WHERE referral_id = ? FOR UPDATE',
            $referralId
        );

        if (!$lockedId)
        {
            throw new \InvalidArgumentException('Davet kaydı bulunamadı.');
        }

        $this->app->em()->clearEntityCache('WarextStudios\ReferralSystem:Referral');
        $referral = $this->app->em()->find(
            'WarextStudios\ReferralSystem:Referral',
            $referralId,
            ['Inviter', 'Invited']
        );

        if (!$referral)
        {
            throw new \InvalidArgumentException('Davet kaydı bulunamadı.');
        }

        return $referral;
    }

    protected function assertActorCanManage(User $actor, Referral $referral): void
    {
        if ((int)$actor->user_id <= 0)
        {
            throw new \LogicException('Bu işlem için giriş yapmış bir yönetici hesabı gerekli.');
        }

        if ((int)$actor->user_id === (int)$referral->inviter_user_id)
        {
            throw new \LogicException('Kendi davet kaydınızı manuel olarak yönetemezsiniz.');
        }
    }

    protected function assertCanForceApprove(Referral $referral): void
    {
        $repo = $this->app->repository('WarextStudios\ReferralSystem:Referral');
        $inviter = $referral->Inviter;
        $invited = $referral->Invited;

        if (!$inviter || !$repo->isUserUsableAsInviter($inviter))
        {
            throw new \InvalidArgumentException('Davet eden kullanıcı artık geçerli bir davetçi değil.');
        }

        if (!$invited || $invited->user_state !== 'valid' || $invited->is_banned)
        {
            throw new \InvalidArgumentException('Davet edilen hesap geçerli durumda olmadığı için manuel onay verilemez.');
        }
    }

    protected function reconcileRewards(int $inviterUserId): void
    {
        try
        {
            $this->app->service('WarextStudios\ReferralSystem:RewardManager')
                ->reconcileUser($inviterUserId);
        }
        catch (\Throwable $e)
        {
            \XF::logException($e, false, 'Warext Referral Admin Reward: ');
        }
    }
}
