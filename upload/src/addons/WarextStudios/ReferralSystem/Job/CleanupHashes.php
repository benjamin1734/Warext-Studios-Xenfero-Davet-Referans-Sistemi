<?php

namespace WarextStudios\ReferralSystem\Job;

use XF\Job\AbstractRebuildJob;

class CleanupHashes extends AbstractRebuildJob
{
    protected function getNextIds($start, $batch): array
    {
        $days = max(30, min(3650, (int)(\XF::options()->wrxtReferralIpHashRetentionDays ?? 90)));
        $cutoff = \XF::$time - ($days * 86400);
        $db = $this->app->db();

        return $db->fetchAllColumn(
            $db->limit(
                "SELECT referral_id
                FROM xf_wrxt_referral
                WHERE referral_id > ? AND ip_hash <> '' AND created_date < ?
                ORDER BY referral_id",
                max(1, (int)$batch)
            ),
            [(int)$start, $cutoff]
        );
    }

    protected function rebuildById($id): void
    {
        $this->app->db()->query(
            "UPDATE xf_wrxt_referral SET ip_hash = '' WHERE referral_id = ?",
            (int)$id
        );
    }

    protected function getStatusType(): \XF\Phrase
    {
        return \XF::phrase('users');
    }
}
