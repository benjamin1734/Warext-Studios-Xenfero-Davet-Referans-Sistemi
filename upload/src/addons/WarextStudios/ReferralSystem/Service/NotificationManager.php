<?php

namespace WarextStudios\ReferralSystem\Service;

use XF\Repository\UserAlertRepository;
use XF\Service\AbstractService;

class NotificationManager extends AbstractService
{
    public function referralValid(int $userId, string $invitedUsername): void
    {
        $this->send($userId, 'wrxt_referral_valid', [
            'invitedUsername' => $invitedUsername
        ]);
    }

    public function rewardGranted(int $userId, string $rewardTitle): void
    {
        $this->send($userId, 'wrxt_referral_reward_granted', [
            'rewardTitle' => $rewardTitle
        ]);
    }

    public function rewardRevoked(int $userId, string $rewardTitle): void
    {
        $this->send($userId, 'wrxt_referral_reward_revoked', [
            'rewardTitle' => $rewardTitle
        ]);
    }

    protected function send(int $userId, string $action, array $extra): void
    {
        if ($userId <= 0)
        {
            return;
        }

        $user = $this->app->em()->find('XF:User', $userId);
        if (!$user || $user->user_state !== 'valid')
        {
            return;
        }

        $this->app->repository(UserAlertRepository::class)->alert(
            $user,
            0,
            '',
            'user',
            $userId,
            $action,
            $extra
        );
    }
}
