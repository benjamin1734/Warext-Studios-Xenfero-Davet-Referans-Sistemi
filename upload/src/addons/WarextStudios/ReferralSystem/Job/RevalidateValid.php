<?php

namespace WarextStudios\ReferralSystem\Job;

use XF\Job\AbstractRebuildJob;

class RevalidateValid extends AbstractRebuildJob
{
    protected function getNextIds($start, $batch): array
    {
        $db = $this->app->db();

        return $db->fetchAllColumn(
            $db->limit(
                "SELECT referral_id
                FROM xf_wrxt_referral
                WHERE referral_id > ? AND status = 'valid'
                ORDER BY referral_id",
                max(1, (int)$batch)
            ),
            (int)$start
        );
    }

    protected function rebuildById($id): void
    {
        $referralId = (int)$id;
        $row = $this->app->db()->fetchRow(
            "SELECT r.risk_reason,
                    inviter.user_state AS inviter_state,
                    inviter.is_banned AS inviter_banned,
                    invited.user_state AS invited_state,
                    invited.is_banned AS invited_banned
            FROM xf_wrxt_referral AS r
            LEFT JOIN xf_user AS inviter ON (inviter.user_id = r.inviter_user_id)
            LEFT JOIN xf_user AS invited ON (invited.user_id = r.invited_user_id)
            WHERE r.referral_id = ? AND r.status = 'valid'",
            $referralId
        );

        if (
            $row
            && (string)$row['risk_reason'] === 'manual_approved'
            && (string)$row['inviter_state'] === 'valid'
            && !(bool)$row['inviter_banned']
            && (string)$row['invited_state'] === 'valid'
            && !(bool)$row['invited_banned']
        )
        {
            return;
        }

        $this->app->repository('WarextStudios\ReferralSystem:Referral')
            ->reconcileReferralById($referralId);
    }

    protected function getStatusType(): \XF\Phrase
    {
        return \XF::phrase('users');
    }
}
