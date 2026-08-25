<?php

namespace WarextStudios\ReferralSystem\Cron;

class ReconcileRewards
{
    public static function run(): void
    {
        \XF::app()->jobManager()->enqueueUnique(
            'wrxtReferralReconcileRewards',
            'WarextStudios\ReferralSystem:ReconcileRewards',
            [],
            false
        );
    }
}
