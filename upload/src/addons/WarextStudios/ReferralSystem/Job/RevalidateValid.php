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
        $this->app->repository('WarextStudios\ReferralSystem:Referral')
            ->reconcileReferralById((int)$id);
    }

    protected function getStatusType(): \XF\Phrase
    {
        return \XF::phrase('users');
    }
}
