<?php

namespace WarextStudios\ReferralSystem\Service;

use WarextStudios\ReferralSystem\Entity\Referral;
use XF\Entity\User;
use XF\Service\AbstractService;

class ReviewManager extends AbstractService
{
    public function review(User $actor, int $referralId, string $decision): Referral
    {
        if (!$actor->hasPermission('wrxtReferral', 'reviewReferrals'))
        {
            throw new \LogicException((string)\XF::phrase('wrxt_referral_error_no_permission'));
        }

        if (!in_array($decision, ['valid', 'rejected'], true))
        {
            throw new \InvalidArgumentException((string)\XF::phrase('wrxt_referral_error_review_decision'));
        }

        $db = $this->app->db();
        $db->beginTransaction();
        $inviterUserId = 0;

        try
        {
            $lockedId = (int)$db->fetchOne(
                'SELECT referral_id FROM xf_wrxt_referral WHERE referral_id = ? FOR UPDATE',
                $referralId
            );

            if (!$lockedId)
            {
                throw new \InvalidArgumentException((string)\XF::phrase('wrxt_referral_error_referral_not_found'));
            }

            $this->app->em()->clearEntityCache('WarextStudios\ReferralSystem:Referral');
            $referral = $this->app->em()->find(
                'WarextStudios\ReferralSystem:Referral',
                $referralId,
                ['Inviter', 'Invited']
            );

            if (!$referral || $referral->status !== 'review')
            {
                throw new \InvalidArgumentException((string)\XF::phrase('wrxt_referral_error_referral_not_pending_review'));
            }

            if ((int)$actor->user_id === (int)$referral->inviter_user_id)
            {
                throw new \LogicException((string)\XF::phrase('wrxt_referral_error_self_review'));
            }

            $repo = $this->app->repository('WarextStudios\ReferralSystem:Referral');
            $inviterUserId = (int)$referral->inviter_user_id;

            if ($decision === 'valid')
            {
                $inviter = $referral->Inviter;
                $invited = $referral->Invited;

                if (!$inviter || !$repo->isUserUsableAsInviter($inviter))
                {
                    throw new \InvalidArgumentException((string)\XF::phrase('wrxt_referral_error_inviter_invalid'));
                }

                if (!$invited || !$repo->canInvitedUserBecomeValid($invited))
                {
                    throw new \InvalidArgumentException((string)\XF::phrase('wrxt_referral_error_invitee_requirements'));
                }

                if ($referral->risk_reason === 'code_suspended')
                {
                    $currentCode = $repo->getCodeForUserId((int)$referral->inviter_user_id, false);
                    if (
                        !$currentCode
                        || !$currentCode->is_active
                        || !hash_equals((string)$currentCode->code, (string)$referral->referral_code)
                    )
                    {
                        throw new \InvalidArgumentException((string)\XF::phrase('wrxt_referral_error_suspended_review'));
                    }
                }

                $referral->validated_date = \XF::$time;
            }
            else
            {
                $referral->validated_date = 0;
            }

            $referral->status = $decision;
            $referral->reviewed_date = \XF::$time;
            $referral->reviewed_by = $actor->user_id;
            $referral->save();

            $db->commit();
        }
        catch (\Throwable $e)
        {
            $db->rollback();
            throw $e;
        }

        if ($inviterUserId > 0)
        {
            try
            {
                $this->app->service('WarextStudios\ReferralSystem:RewardManager')
                    ->reconcileUser($inviterUserId);
            }
            catch (\Throwable $e)
            {
                \XF::logException($e, false, 'Warext Referral Reward: ');
            }
        }

        return $referral;
    }
}
