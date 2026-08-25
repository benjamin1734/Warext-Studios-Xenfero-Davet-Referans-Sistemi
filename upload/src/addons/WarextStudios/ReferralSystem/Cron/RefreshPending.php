<?php

namespace WarextStudios\ReferralSystem\Cron;

class RefreshPending
{
    public static function run(): void
    {
        \XF::app()->jobManager()->enqueueUnique(
            'wrxtReferralRefreshPending',
            'WarextStudios\ReferralSystem:RefreshPending',
            [],
            false
        );
    }
}
