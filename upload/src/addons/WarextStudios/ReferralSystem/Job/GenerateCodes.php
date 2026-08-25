<?php

namespace WarextStudios\ReferralSystem\Job;

use XF\Job\AbstractRebuildJob;

class GenerateCodes extends AbstractRebuildJob
{
    protected function getNextIds($start, $batch): array
    {
        $db = $this->app->db();

        return $db->fetchAllColumn(
            $db->limit(
                'SELECT u.user_id
                FROM xf_user u
                LEFT JOIN xf_wrxt_referral_code c ON c.user_id = u.user_id
                WHERE u.user_id > ? AND c.user_id IS NULL
                ORDER BY u.user_id',
                max(1, (int)$batch)
            ),
            (int)$start
        );
    }

    protected function rebuildById($id): void
    {
        $this->app->repository('WarextStudios\ReferralSystem:Referral')
            ->getCodeForUserId((int)$id, true);
    }

    protected function getStatusType(): \XF\Phrase
    {
        return \XF::phrase('users');
    }
}
