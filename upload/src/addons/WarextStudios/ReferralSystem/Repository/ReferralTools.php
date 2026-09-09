<?php

namespace WarextStudios\ReferralSystem\Repository;

class ReferralTools extends XFCP_ReferralTools
{
    public function reconcileReferralById(int $referralId): void
    {
        if ($referralId > 0)
        {
            $referral = $this->em->find(
                'WarextStudios\ReferralSystem:Referral',
                $referralId,
                ['Inviter', 'Invited']
            );

            if (
                $referral
                && (string)$referral->status === 'valid'
                && (string)$referral->risk_reason === 'manual_approved'
                && $referral->Inviter
                && $referral->Invited
                && $this->isUserUsableAsInviter($referral->Inviter)
                && $referral->Invited->user_state === 'valid'
                && !$referral->Invited->is_banned
            )
            {
                return;
            }
        }

        parent::reconcileReferralById($referralId);
    }
}
