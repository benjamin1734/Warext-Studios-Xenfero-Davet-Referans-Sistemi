<?php

namespace WarextStudios\ReferralSystem\Cron;

class Cleanup
{
    public static function run(): void
    {
        \XF::app()->jobManager()->enqueueUnique(
            'wrxtReferralCleanupHashes',
            'WarextStudios\ReferralSystem:CleanupHashes',
            [],
            false
        );
        \XF::app()->jobManager()->enqueueUnique(
            'wrxtReferralCleanupOwnerHashes',
            'WarextStudios\ReferralSystem:CleanupOwnerHashes',
            [],
            false
        );
    }
}
