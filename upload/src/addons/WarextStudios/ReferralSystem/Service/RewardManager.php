<?php

namespace WarextStudios\ReferralSystem\Service;

use WarextStudios\ReferralSystem\Entity\Milestone;
use WarextStudios\ReferralSystem\Entity\Reward;
use XF\Service\AbstractService;

class RewardManager extends AbstractService
{
    public function reconcileUser(int $userId): void
    {
        if ($userId <= 0)
        {
            return;
        }

        $user = $this->app->em()->find('XF:User', $userId);
        if (!$user)
        {
            return;
        }

        $validCount = (int)$this->app->db()->fetchOne(
            "SELECT COUNT(*) FROM xf_wrxt_referral WHERE inviter_user_id = ? AND status = 'valid'",
            $userId
        );

        $milestones = $this->app->finder('WarextStudios\ReferralSystem:Milestone')
            ->where('is_active', 1)
            ->order('required_referrals', 'ASC')
            ->fetch();

        foreach ($milestones as $milestone)
        {
            $this->reconcileMilestone($userId, $validCount, $milestone);
        }
    }

    public function retryReward(int $rewardId): bool
    {
        if ($rewardId <= 0)
        {
            return false;
        }

        $reward = $this->app->finder('WarextStudios\ReferralSystem:Reward')
            ->where('reward_id', $rewardId)
            ->with('Milestone')
            ->fetchOne();

        if (!$reward || $reward->status !== 'failed' || !$reward->Milestone || !$reward->Milestone->is_active)
        {
            return false;
        }

        $validCount = (int)$this->app->db()->fetchOne(
            "SELECT COUNT(*) FROM xf_wrxt_referral WHERE inviter_user_id = ? AND status = 'valid'",
            (int)$reward->user_id
        );

        $this->reconcileMilestone((int)$reward->user_id, $validCount, $reward->Milestone);
        $this->app->em()->clearEntityCache('WarextStudios\ReferralSystem:Reward');
        $updated = $this->app->em()->find('WarextStudios\ReferralSystem:Reward', $rewardId);

        return $updated && $updated->status !== 'failed';
    }

    protected function reconcileMilestone(int $userId, int $validCount, Milestone $milestone): void
    {
        $eligible = $validCount >= (int)$milestone->required_referrals;
        $db = $this->app->db();
        $db->beginTransaction();
        $operation = '';

        try
        {
            $lockedUserId = (int)$db->fetchOne(
                'SELECT user_id FROM xf_user WHERE user_id = ? FOR UPDATE',
                $userId
            );

            if (!$lockedUserId)
            {
                $db->commit();
                return;
            }

            $this->app->em()->clearEntityCache('WarextStudios\ReferralSystem:Reward');
            $reward = $this->app->finder('WarextStudios\ReferralSystem:Reward')
                ->where('user_id', $userId)
                ->where('milestone_id', $milestone->milestone_id)
                ->fetchOne();

            if ($eligible && !$reward)
            {
                $reward = $this->createReward($userId, $milestone);
            }

            if (!$reward)
            {
                $db->commit();
                return;
            }

            if ($eligible)
            {
                if ($reward->status !== 'granted')
                {
                    $operation = 'grant';
                    $this->grantReward($reward);
                }
            }
            elseif (
                $milestone->revoke_on_loss
                && (
                    $reward->status === 'granted'
                    || ($reward->status === 'failed' && $reward->error_code === 'revoke_failed')
                )
            )
            {
                $operation = 'revoke';
                $this->revokeReward($reward);
            }

            $db->commit();
        }
        catch (\Throwable $e)
        {
            $db->rollback();

            if ($operation !== '')
            {
                try
                {
                    $this->recordFailure(
                        $userId,
                        $milestone,
                        $operation === 'revoke' ? 'revoke_failed' : 'delivery_failed'
                    );
                }
                catch (\Throwable $failureException)
                {
                    \XF::logException($failureException, false, 'Warext Referral Reward Failure: ');
                }
            }

            \XF::logException($e, false, 'Warext Referral Reward: ');
        }
    }

    protected function createReward(int $userId, Milestone $milestone): Reward
    {
        $reward = $this->app->em()->create('WarextStudios\ReferralSystem:Reward');
        $reward->user_id = $userId;
        $reward->milestone_id = $milestone->milestone_id;
        $reward->milestone_required_referrals = $milestone->required_referrals;
        $reward->reward_type = $milestone->reward_type;
        $reward->reward_value = $milestone->reward_user_group_id;
        $reward->reward_title = $milestone->title;
        $reward->status = 'pending';
        $reward->created_date = \XF::$time;

        try
        {
            $reward->save();
            return $reward;
        }
        catch (\XF\Db\DuplicateKeyException $e)
        {
            $this->app->em()->clearEntityCache('WarextStudios\ReferralSystem:Reward');
            $existing = $this->app->finder('WarextStudios\ReferralSystem:Reward')
                ->where('user_id', $userId)
                ->where('milestone_id', $milestone->milestone_id)
                ->fetchOne();

            if (!$existing)
            {
                throw $e;
            }

            return $existing;
        }
    }

    protected function grantReward(Reward $reward): void
    {
        $reward->last_attempt_date = \XF::$time;
        $reward->attempts = (int)$reward->attempts + 1;
        $reward->error_code = '';

        if ($reward->reward_type === 'user_group')
        {
            $groupId = (int)$reward->reward_value;
            $group = $groupId > 0 ? $this->app->em()->find('XF:UserGroup', $groupId) : null;
            if (!$group)
            {
                $reward->status = 'failed';
                $reward->error_code = 'user_group_missing';
                $reward->save();
                return;
            }

            $service = $this->app->service('XF:User\UserGroupChange');
            $service->addUserGroupChange(
                (int)$reward->user_id,
                $this->getGroupChangeKey((int)$reward->milestone_id),
                [$groupId]
            );
        }
        elseif ($reward->reward_type !== 'display')
        {
            $reward->status = 'failed';
            $reward->error_code = 'unsupported_reward_type';
            $reward->save();
            return;
        }

        $reward->status = 'granted';
        $reward->granted_date = \XF::$time;
        $reward->revoked_date = 0;
        $reward->save();
    }

    protected function revokeReward(Reward $reward): void
    {
        $reward->last_attempt_date = \XF::$time;
        $reward->attempts = (int)$reward->attempts + 1;
        $reward->error_code = '';

        if ($reward->reward_type === 'user_group')
        {
            $service = $this->app->service('XF:User\UserGroupChange');
            $service->removeUserGroupChange(
                (int)$reward->user_id,
                $this->getGroupChangeKey((int)$reward->milestone_id)
            );
        }

        $reward->status = 'revoked';
        $reward->revoked_date = \XF::$time;
        $reward->save();
    }

    protected function recordFailure(int $userId, Milestone $milestone, string $errorCode): void
    {
        $this->app->em()->clearEntityCache('WarextStudios\ReferralSystem:Reward');
        $reward = $this->app->finder('WarextStudios\ReferralSystem:Reward')
            ->where('user_id', $userId)
            ->where('milestone_id', $milestone->milestone_id)
            ->fetchOne();

        if (!$reward)
        {
            $reward = $this->createReward($userId, $milestone);
        }

        $reward->status = 'failed';
        $reward->last_attempt_date = \XF::$time;
        $reward->attempts = (int)$reward->attempts + 1;
        $reward->error_code = $errorCode;
        $reward->save();
    }

    protected function getGroupChangeKey(int $milestoneId): string
    {
        return 'wrxtReferralMilestone_' . $milestoneId;
    }
}
