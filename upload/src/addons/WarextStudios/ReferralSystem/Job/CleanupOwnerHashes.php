<?php

namespace WarextStudios\ReferralSystem\Job;

use XF\Job\AbstractRebuildJob;

class CleanupOwnerHashes extends AbstractRebuildJob
{
    protected function getNextIds($start, $batch): array
    {
        $days = max(30, min(3650, (int)(\XF::options()->wrxtReferralIpHashRetentionDays ?? 90)));
        $cutoff = \XF::$time - ($days * 86400);
        $db = $this->app->db();

        return $db->fetchAllColumn(
            $db->limit(
                "SELECT user_id
                FROM xf_wrxt_referral_code
                WHERE user_id > ? AND owner_ip_hash <> '' AND owner_ip_hash_date > 0 AND owner_ip_hash_date < ?
                ORDER BY user_id",
                max(1, (int)$batch)
            ),
            [(int)$start, $cutoff]
        );
    }

    protected function rebuildById($id): void
    {
        $this->app->db()->query(
            "UPDATE xf_wrxt_referral_code SET owner_ip_hash = '', owner_ip_hash_date = 0 WHERE user_id = ?",
            (int)$id
        );
    }

    protected function getStatusType(): \XF\Phrase
    {
        return \XF::phrase('users');
    }
}
