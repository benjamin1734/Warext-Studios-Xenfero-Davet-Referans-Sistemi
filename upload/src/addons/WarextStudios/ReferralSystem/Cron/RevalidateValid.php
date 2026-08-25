<?php

namespace WarextStudios\ReferralSystem\Cron;

class RevalidateValid
{
    public static function run(): void
    {
        \XF::app()->jobManager()->enqueueUnique(
            'wrxtReferralRevalidateValid',
            'WarextStudios\ReferralSystem:RevalidateValid',
            [],
            false
        );
    }
}
