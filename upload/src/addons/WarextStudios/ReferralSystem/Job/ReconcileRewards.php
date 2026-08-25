<?php

namespace WarextStudios\ReferralSystem\Job;

use XF\Job\AbstractRebuildJob;

class ReconcileRewards extends AbstractRebuildJob
{
    protected function getNextIds($start, $batch): array
    {
        $db = $this->app->db();

        return $db->fetchAllColumn(
            $db->limit(
                "SELECT user_id
                FROM (
                    SELECT inviter_user_id AS user_id FROM xf_wrxt_referral WHERE status = 'valid'
                    UNION
                    SELECT user_id FROM xf_wrxt_referral_reward
                ) AS users
                WHERE user_id > ?
                ORDER BY user_id",
                max(1, (int)$batch)
            ),
            (int)$start
        );
    }

    protected function rebuildById($id): void
    {
        $this->app->service('WarextStudios\ReferralSystem:RewardManager')
            ->reconcileUser((int)$id);
    }

    protected function getStatusType(): \XF\Phrase
    {
        return \XF::phrase('users');
    }
}
