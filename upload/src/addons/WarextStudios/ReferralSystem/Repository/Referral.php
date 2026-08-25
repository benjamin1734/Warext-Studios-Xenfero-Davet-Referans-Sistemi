<?php

namespace WarextStudios\ReferralSystem\Repository;

use WarextStudios\ReferralSystem\Entity\ReferralCode;
use XF\Entity\User;
use XF\Mvc\Entity\Repository;

class Referral extends Repository
{
    public function normalizeCode(string $code): string
    {
        return strtoupper(trim($code));
    }

    public function isCodeValid(string $code): bool
    {
        return (bool)preg_match('/^[A-Z0-9]{6,24}$/D', $this->normalizeCode($code));
    }

    public function generateCode(int $length = 10): string
    {
        $length = max(6, min(24, $length));
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $max = strlen($alphabet) - 1;
        $code = '';

        for ($i = 0; $i < $length; $i++)
        {
            $code .= $alphabet[random_int(0, $max)];
        }

        return $code;
    }

    public function reserveCode(string $code, int $userId): bool
    {
        $code = $this->normalizeCode($code);
        if ($userId <= 0 || !$this->isCodeValid($code))
        {
            return false;
        }

        try
        {
            $this->db()->insert('xf_wrxt_referral_code_reservation', [
                'code' => $code,
                'user_id' => $userId,
                'reserved_date' => \XF::$time
            ]);
            return true;
        }
        catch (\XF\Db\DuplicateKeyException $e)
        {
            $ownerUserId = (int)$this->db()->fetchOne(
                'SELECT user_id FROM xf_wrxt_referral_code_reservation WHERE code = ?',
                $code
            );

            return $ownerUserId === $userId;
        }
    }

    public function isCodeReservedForUser(string $code, int $userId): bool
    {
        $code = $this->normalizeCode($code);
        if ($userId <= 0 || !$this->isCodeValid($code))
        {
            return false;
        }

        return (int)$this->db()->fetchOne(
            'SELECT user_id FROM xf_wrxt_referral_code_reservation WHERE code = ?',
            $code
        ) === $userId;
    }

    public function isUserUsableAsInviter(User $user): bool
    {
        return $user->user_state === 'valid' && !$user->is_banned;
    }

    public function canInvitedUserBecomeValid(User $user): bool
    {
        if ($user->user_state !== 'valid' || $user->is_banned)
        {
            return false;
        }

        $minDays = max(0, (int)(\XF::options()->wrxtReferralMinDays ?? 3));
        $minMessages = max(0, (int)(\XF::options()->wrxtReferralMinMessages ?? 3));
        $minRegisterDate = \XF::$time - ($minDays * 86400);

        return (int)$user->register_date <= $minRegisterDate
            && (int)$user->message_count >= $minMessages;
    }

    public function getCodeForUserId(int $userId, bool $create = true, bool $captureOwnerIp = false): ?ReferralCode
    {
        if ($userId <= 0)
        {
            return null;
        }

        $existing = $this->em->find('WarextStudios\ReferralSystem:ReferralCode', $userId);
        if ($existing)
        {
            $this->reserveCode((string)$existing->code, $userId);

            if (
                $existing->owner_ip_hash === ''
                && (int)\XF::visitor()->user_id === $userId
            )
            {
                $ownerIpHash = $this->getCurrentIpHash();
                if ($ownerIpHash !== '')
                {
                    $existing->owner_ip_hash = $ownerIpHash;
                    $existing->save();
                }
            }

            return $existing;
        }

        if (!$create)
        {
            return null;
        }

        $ownerIpHash = $captureOwnerIp ? $this->getCurrentIpHash() : '';

        for ($attempt = 0; $attempt < 16; $attempt++)
        {
            $codeValue = $this->generateCode(10);
            if (!$this->reserveCode($codeValue, $userId))
            {
                continue;
            }

            $code = $this->em->create('WarextStudios\ReferralSystem:ReferralCode');
            $code->user_id = $userId;
            $code->code = $codeValue;
            $code->owner_ip_hash = $ownerIpHash;
            $code->is_active = true;
            $code->created_date = \XF::$time;

            try
            {
                $code->save();
                return $code;
            }
            catch (\XF\Db\DuplicateKeyException $e)
            {
                $this->em->clearEntityCache('WarextStudios\ReferralSystem:ReferralCode');
                $existing = $this->em->find('WarextStudios\ReferralSystem:ReferralCode', $userId);
                if ($existing)
                {
                    $this->reserveCode((string)$existing->code, $userId);
                    return $existing;
                }

                if ($attempt === 15)
                {
                    throw $e;
                }
            }
        }

        return null;
    }

    public function findActiveCode(string $value): ?ReferralCode
    {
        $value = $this->normalizeCode($value);
        if (!$this->isCodeValid($value))
        {
            return null;
        }

        $code = $this->finder('WarextStudios\ReferralSystem:ReferralCode')
            ->where('code', $value)
            ->where('is_active', 1)
            ->with('User')
            ->fetchOne();

        if (!$code || !$code->User || !$this->isUserUsableAsInviter($code->User))
        {
            return null;
        }

        return $code;
    }

    public function attachReferral(User $invitedUser, string $explicitCode = ''): bool
    {
        if ((int)$invitedUser->user_id <= 0)
        {
            return false;
        }

        $existing = $this->finder('WarextStudios\ReferralSystem:Referral')
            ->where('invited_user_id', $invitedUser->user_id)
            ->fetchOne();

        if ($existing)
        {
            return false;
        }

        $codeValue = $this->normalizeCode($explicitCode);
        if ($codeValue === '')
        {
            $codeValue = $this->normalizeCode((string)\XF::app()->request()->getCookie('wrxt_referral'));
        }

        $code = $this->findActiveCode($codeValue);
        if (!$code || (int)$code->user_id === (int)$invitedUser->user_id)
        {
            return false;
        }

        $ipHash = $this->getCurrentIpHash();
        $status = 'pending';
        $riskReason = '';

        if ($ipHash !== '' && $code->owner_ip_hash !== '' && hash_equals((string)$code->owner_ip_hash, $ipHash))
        {
            $status = 'review';
            $riskReason = 'same_owner_network';
        }
        elseif ($ipHash !== '')
        {
            $sameNetwork = $this->finder('WarextStudios\ReferralSystem:Referral')
                ->where('inviter_user_id', $code->user_id)
                ->where('ip_hash', $ipHash)
                ->where('created_date', '>=', \XF::$time - 2592000)
                ->fetchOne();

            if ($sameNetwork)
            {
                $status = 'review';
                $riskReason = 'reused_network';
            }
        }

        $referral = $this->em->create('WarextStudios\ReferralSystem:Referral');
        $referral->inviter_user_id = $code->user_id;
        $referral->invited_user_id = $invitedUser->user_id;
        $referral->referral_code = $code->code;
        $referral->status = $status;
        $referral->created_date = \XF::$time;
        $referral->ip_hash = $ipHash;
        $referral->risk_reason = $riskReason;

        try
        {
            $referral->save();
            return true;
        }
        catch (\XF\Db\DuplicateKeyException $e)
        {
            $existing = $this->finder('WarextStudios\ReferralSystem:Referral')
                ->where('invited_user_id', $invitedUser->user_id)
                ->fetchOne();

            if ($existing)
            {
                return false;
            }

            throw $e;
        }
    }

    public function reconcileReferralById(int $referralId): void
    {
        if ($referralId <= 0)
        {
            return;
        }

        $db = $this->db();
        $db->beginTransaction();
        $rewardUserId = 0;
        $rewardReconcile = false;

        try
        {
            $lockedId = (int)$db->fetchOne(
                'SELECT referral_id FROM xf_wrxt_referral WHERE referral_id = ? FOR UPDATE',
                $referralId
            );

            if (!$lockedId)
            {
                $db->commit();
                return;
            }

            $this->em->clearEntityCache('WarextStudios\ReferralSystem:Referral');
            $referral = $this->em->find(
                'WarextStudios\ReferralSystem:Referral',
                $referralId,
                ['Inviter', 'Invited']
            );

            if (!$referral || !in_array($referral->status, ['pending', 'valid', 'review'], true))
            {
                $db->commit();
                return;
            }

            $oldStatus = (string)$referral->status;
            $oldRiskReason = (string)$referral->risk_reason;
            $rewardUserId = (int)$referral->inviter_user_id;
            $inviter = $referral->Inviter;
            $invited = $referral->Invited;
            $changed = false;

            if (!$inviter)
            {
                $referral->status = 'rejected';
                $referral->validated_date = 0;
                $referral->risk_reason = 'inviter_missing';
                $referral->reviewed_date = \XF::$time;
                $referral->reviewed_by = 0;
                $changed = $oldStatus !== 'rejected' || $oldRiskReason !== 'inviter_missing';
            }
            elseif (!$invited)
            {
                $referral->status = 'rejected';
                $referral->validated_date = 0;
                $referral->risk_reason = 'user_missing';
                $referral->reviewed_date = \XF::$time;
                $referral->reviewed_by = 0;
                $changed = $oldStatus !== 'rejected' || $oldRiskReason !== 'user_missing';
            }
            elseif ($referral->status !== 'review')
            {
                if (!$this->isUserUsableAsInviter($inviter))
                {
                    $referral->status = 'pending';
                    $referral->validated_date = 0;
                    $referral->risk_reason = 'inviter_unavailable';
                    $referral->reviewed_date = 0;
                    $referral->reviewed_by = 0;
                    $changed = $oldStatus !== 'pending' || $oldRiskReason !== 'inviter_unavailable';
                }
                elseif (!$this->canInvitedUserBecomeValid($invited))
                {
                    $reason = ($invited->user_state !== 'valid' || $invited->is_banned)
                        ? 'invitee_unavailable'
                        : 'requirements_not_met';
                    $referral->status = 'pending';
                    $referral->validated_date = 0;
                    $referral->risk_reason = $reason;
                    $referral->reviewed_date = 0;
                    $referral->reviewed_by = 0;
                    $changed = $oldStatus !== 'pending' || $oldRiskReason !== $reason;
                }
                elseif ($referral->status === 'pending')
                {
                    $referral->status = 'valid';
                    $referral->validated_date = \XF::$time;
                    $referral->risk_reason = '';
                    $referral->reviewed_date = 0;
                    $referral->reviewed_by = 0;
                    $changed = true;
                }
            }

            if ($changed)
            {
                $referral->save();
            }

            $newStatus = (string)$referral->status;
            $rewardReconcile = $oldStatus !== $newStatus
                && ($oldStatus === 'valid' || $newStatus === 'valid');

            $db->commit();
        }
        catch (\Throwable $e)
        {
            $db->rollback();
            throw $e;
        }

        if ($rewardReconcile && $rewardUserId > 0)
        {
            \XF::app()->service('WarextStudios\ReferralSystem:RewardManager')
                ->reconcileUser($rewardUserId);
        }
    }

    public function getStatsForUser(int $userId): array
    {
        $rows = $this->db()->fetchPairs(
            'SELECT status, COUNT(*) FROM xf_wrxt_referral WHERE inviter_user_id = ? GROUP BY status',
            $userId
        );

        $stats = [
            'total' => 0,
            'valid' => 0,
            'pending' => 0,
            'review' => 0,
            'rejected' => 0
        ];

        foreach ($rows as $status => $count)
        {
            $count = (int)$count;
            $stats['total'] += $count;
            if (array_key_exists($status, $stats))
            {
                $stats[$status] = $count;
            }
        }

        return $stats;
    }

    public function getRecentReferrals(int $userId, int $limit = 20)
    {
        return $this->finder('WarextStudios\ReferralSystem:Referral')
            ->where('inviter_user_id', $userId)
            ->with('Invited')
            ->order('created_date', 'DESC')
            ->limit(max(1, min(100, $limit)))
            ->fetch();
    }

    public function getRewardsForUser(int $userId, int $limit = 50)
    {
        return $this->finder('WarextStudios\ReferralSystem:Reward')
            ->where('user_id', $userId)
            ->with('Milestone')
            ->order('created_date', 'DESC')
            ->limit(max(1, min(100, $limit)))
            ->fetch();
    }

    public function getRewardMapForUser(int $userId): array
    {
        $rewards = $this->finder('WarextStudios\ReferralSystem:Reward')
            ->where('user_id', $userId)
            ->fetch();
        $map = [];

        foreach ($rewards as $reward)
        {
            $map[(int)$reward->milestone_id] = $reward;
        }

        return $map;
    }

    public function getReviewQueue(int $limit = 100)
    {
        return $this->finder('WarextStudios\ReferralSystem:Referral')
            ->where('status', 'review')
            ->with(['Inviter', 'Invited'])
            ->order('created_date', 'ASC')
            ->limit(max(1, min(250, $limit)))
            ->fetch();
    }

    public function getActiveMilestones()
    {
        return $this->finder('WarextStudios\ReferralSystem:Milestone')
            ->where('is_active', 1)
            ->with('RewardUserGroup')
            ->order('required_referrals', 'ASC')
            ->order('display_order', 'ASC')
            ->fetch();
    }

    public function getAllMilestones()
    {
        return $this->finder('WarextStudios\ReferralSystem:Milestone')
            ->with('RewardUserGroup')
            ->order('required_referrals', 'ASC')
            ->order('display_order', 'ASC')
            ->fetch();
    }

    public function getProgressData(int $validReferrals): array
    {
        $milestones = $this->getActiveMilestones();
        $next = null;
        $highest = 0;

        foreach ($milestones as $milestone)
        {
            $required = (int)$milestone->required_referrals;
            $highest = max($highest, $required);
            if ($required > $validReferrals && !$next)
            {
                $next = $milestone;
            }
        }

        $points = [];
        if ($highest > 0)
        {
            foreach ($milestones as $milestone)
            {
                $points[] = [
                    'milestone' => $milestone,
                    'position' => min(100, max(0, ((int)$milestone->required_referrals / $highest) * 100))
                ];
            }
        }

        $target = $next ? (int)$next->required_referrals : max($highest, $validReferrals);
        $percent = $highest > 0
            ? min(100, (int)floor(($validReferrals / $highest) * 100))
            : 0;

        return [
            'milestones' => $milestones,
            'points' => $points,
            'next' => $next,
            'highest' => $highest,
            'target' => $target,
            'remaining' => $next ? max(0, $target - $validReferrals) : 0,
            'percent' => $percent
        ];
    }

    public function getGlobalStats(): array
    {
        $db = $this->db();
        $statusRows = $db->fetchPairs('SELECT status, COUNT(*) FROM xf_wrxt_referral GROUP BY status');
        $rewardRows = $db->fetchPairs('SELECT status, COUNT(*) FROM xf_wrxt_referral_reward GROUP BY status');

        return [
            'codes' => (int)$db->fetchOne('SELECT COUNT(*) FROM xf_wrxt_referral_code'),
            'active_codes' => (int)$db->fetchOne('SELECT COUNT(*) FROM xf_wrxt_referral_code WHERE is_active = 1'),
            'reserved_codes' => (int)$db->fetchOne('SELECT COUNT(*) FROM xf_wrxt_referral_code_reservation'),
            'referrals' => (int)$db->fetchOne('SELECT COUNT(*) FROM xf_wrxt_referral'),
            'valid' => (int)($statusRows['valid'] ?? 0),
            'pending' => (int)($statusRows['pending'] ?? 0),
            'review' => (int)($statusRows['review'] ?? 0),
            'rejected' => (int)($statusRows['rejected'] ?? 0),
            'milestones' => (int)$db->fetchOne('SELECT COUNT(*) FROM xf_wrxt_referral_milestone'),
            'rewards' => (int)$db->fetchOne('SELECT COUNT(*) FROM xf_wrxt_referral_reward'),
            'rewards_granted' => (int)($rewardRows['granted'] ?? 0),
            'rewards_revoked' => (int)($rewardRows['revoked'] ?? 0),
            'rewards_failed' => (int)($rewardRows['failed'] ?? 0),
            'code_changes' => (int)$db->fetchOne('SELECT COUNT(*) FROM xf_wrxt_referral_code_log')
        ];
    }

    public function getCurrentIpHash(): string
    {
        try
        {
            $ip = (string)\XF::app()->request()->getIp();
        }
        catch (\Throwable $e)
        {
            return '';
        }

        if ($ip === '')
        {
            return '';
        }

        return hash_hmac('sha256', $ip, (string)\XF::app()->config('globalSalt'));
    }
}
