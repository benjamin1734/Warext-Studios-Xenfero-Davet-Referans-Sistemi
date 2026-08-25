<?php

namespace WarextStudios\ReferralSystem\Cron;

class GenerateCodes
{
    public static function run(): void
    {
        \XF::app()->jobManager()->enqueueUnique(
            'wrxtReferralGenerateCodes',
            'WarextStudios\ReferralSystem:GenerateCodes',
            [],
            false
        );
    }
}
