<?php

namespace WarextStudios\ReferralSystem\Admin\Controller;

use XF\Admin\Controller\AbstractController;
use XF\Mvc\ParameterBag;

class Referral extends AbstractController
{
    protected function preDispatchController($action, ParameterBag $params)
    {
        $this->assertAdminPermission('user');
        $this->setSectionContext('wrxtReferralSystem');
    }

    public function actionIndex()
    {
        $repo = $this->repository('WarextStudios\ReferralSystem:Referral');
        $db = $this->db();
        $recentCodeChanges = $db->fetchAll(
            'SELECT l.*, tu.username AS target_username, au.username AS actor_username
            FROM xf_wrxt_referral_code_log AS l
            LEFT JOIN xf_user AS tu ON (tu.user_id = l.target_user_id)
            LEFT JOIN xf_user AS au ON (au.user_id = l.actor_user_id)
            ORDER BY l.log_id DESC
            LIMIT 20'
        );
        $failedRewards = $db->fetchAll(
            "SELECT r.*, u.username
            FROM xf_wrxt_referral_reward AS r
            LEFT JOIN xf_user AS u ON (u.user_id = r.user_id)
            WHERE r.status = 'failed'
            ORDER BY r.reward_id DESC
            LIMIT 20"
        );

        return $this->view(
            'WarextStudios\ReferralSystem:AdminDashboard',
            'wrxt_referral_admin_dashboard',
            [
                'stats' => $repo->getGlobalStats(),
                'milestones' => $repo->getAllMilestones(),
                'recentCodeChanges' => $recentCodeChanges,
                'failedRewards' => $failedRewards
            ]
        );
    }

    public function actionInvites()
    {
        $page = max(1, $this->filter('page', 'uint'));
        $status = $this->filter('status', 'str');
        $inviterUsername = trim($this->filter('inviter_username', 'str'));
        $invitedUsername = trim($this->filter('invited_username', 'str'));
        $dateFrom = trim($this->filter('date_from', 'str'));
        $dateTo = trim($this->filter('date_to', 'str'));
        $allowedStatuses = ['pending', 'review', 'valid', 'rejected'];
        $where = [];
        $params = [];

        if (in_array($status, $allowedStatuses, true))
        {
            $where[] = 'r.status = ?';
            $params[] = $status;
        }
        else
        {
            $status = '';
        }

        if ($inviterUsername !== '')
        {
            $where[] = 'iu.username = ?';
            $params[] = $inviterUsername;
        }

        if ($invitedUsername !== '')
        {
            $where[] = 'du.username = ?';
            $params[] = $invitedUsername;
        }

        $fromTimestamp = $this->parseDateFilter($dateFrom, false);
        if ($fromTimestamp > 0)
        {
            $where[] = 'r.created_date >= ?';
            $params[] = $fromTimestamp;
        }

        $toTimestamp = $this->parseDateFilter($dateTo, true);
        if ($toTimestamp > 0)
        {
            $where[] = 'r.created_date <= ?';
            $params[] = $toTimestamp;
        }

        $whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $fromSql = ' FROM xf_wrxt_referral AS r
            LEFT JOIN xf_user AS iu ON (iu.user_id = r.inviter_user_id)
            LEFT JOIN xf_user AS du ON (du.user_id = r.invited_user_id)';
        $db = $this->db();
        $total = (int)$db->fetchOne('SELECT COUNT(*)' . $fromSql . $whereSql, $params);
        $perPage = 50;
        $maxPage = max(1, (int)ceil($total / $perPage));
        $page = min($page, $maxPage);
        $offset = ($page - 1) * $perPage;
        $rows = $db->fetchAll(
            $db->limit(
                'SELECT r.*, iu.username AS inviter_username, du.username AS invited_username'
                . $fromSql . $whereSql . ' ORDER BY r.referral_id DESC',
                $perPage,
                $offset
            ),
            $params
        );

        return $this->view(
            'WarextStudios\ReferralSystem:AdminInvites',
            'wrxt_referral_admin_invites',
            [
                'referrals' => $rows,
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'status' => $status,
                'inviterUsername' => $inviterUsername,
                'invitedUsername' => $invitedUsername,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo
            ]
        );
    }

    public function actionUser()
    {
        $userId = $this->filter('user_id', 'uint');
        $username = trim($this->filter('username', 'str'));
        $user = null;

        if ($userId > 0)
        {
            $user = $this->em()->find('XF:User', $userId);
        }
        elseif ($username !== '')
        {
            $user = $this->finder('XF:User')
                ->where('username', $username)
                ->fetchOne();
        }

        $viewParams = [
            'user' => $user,
            'username' => $username,
            'code' => null,
            'stats' => null,
            'rewards' => [],
            'referrals' => [],
            'codeLogs' => [],
            'reservedCodeCount' => 0
        ];

        if ($user)
        {
            $repo = $this->repository('WarextStudios\ReferralSystem:Referral');
            $viewParams['code'] = $repo->getCodeForUserId((int)$user->user_id, true);
            $viewParams['stats'] = $repo->getStatsForUser((int)$user->user_id);
            $viewParams['rewards'] = $this->finder('WarextStudios\ReferralSystem:Reward')
                ->where('user_id', $user->user_id)
                ->with('Milestone')
                ->order('reward_id', 'DESC')
                ->limit(100)
                ->fetch();
            $viewParams['referrals'] = $this->finder('WarextStudios\ReferralSystem:Referral')
                ->where('inviter_user_id', $user->user_id)
                ->with('Invited')
                ->order('referral_id', 'DESC')
                ->limit(50)
                ->fetch();
            $viewParams['codeLogs'] = $this->db()->fetchAll(
                'SELECT l.*, au.username AS actor_username
                FROM xf_wrxt_referral_code_log AS l
                LEFT JOIN xf_user AS au ON (au.user_id = l.actor_user_id)
                WHERE l.target_user_id = ?
                ORDER BY l.log_id DESC
                LIMIT 100',
                (int)$user->user_id
            );
            $viewParams['reservedCodeCount'] = (int)$this->db()->fetchOne(
                'SELECT COUNT(*) FROM xf_wrxt_referral_code_reservation WHERE user_id = ?',
                (int)$user->user_id
            );
        }

        return $this->view(
            'WarextStudios\ReferralSystem:AdminUser',
            'wrxt_referral_admin_user',
            $viewParams
        );
    }

    public function actionRetryReward()
    {
        $this->assertPostOnly();

        $rewardId = $this->filter('reward_id', 'uint');
        $reward = $this->em()->find('WarextStudios\ReferralSystem:Reward', $rewardId);
        if (!$reward)
        {
            return $this->notFound();
        }

        $userId = (int)$reward->user_id;
        $this->service('WarextStudios\ReferralSystem:RewardManager')->retryReward($rewardId);

        return $this->redirect($this->buildLink('referral-system/user', null, ['user_id' => $userId]));
    }

    public function actionRetryFailedRewards()
    {
        $this->assertPostOnly();

        \XF::app()->jobManager()->enqueueUnique(
            'wrxtReferralReconcileRewards',
            'WarextStudios\ReferralSystem:ReconcileRewards',
            [],
            false
        );

        return $this->redirect($this->buildLink('referral-system'));
    }

    public function actionMilestoneAdd()
    {
        $milestone = $this->em()->create('WarextStudios\ReferralSystem:Milestone');
        $milestone->required_referrals = 1;
        $milestone->icon = 'fa-gift';
        $milestone->reward_type = 'display';
        $milestone->reward_user_group_id = 0;
        $milestone->revoke_on_loss = true;
        $milestone->display_order = 10;
        $milestone->is_active = true;

        return $this->milestoneForm($milestone);
    }

    public function actionMilestoneEdit()
    {
        $milestoneId = $this->filter('milestone_id', 'uint');
        $milestone = $this->em()->find('WarextStudios\ReferralSystem:Milestone', $milestoneId);

        if (!$milestone)
        {
            return $this->notFound();
        }

        return $this->milestoneForm($milestone);
    }

    protected function milestoneForm($milestone)
    {
        $settledRewardCount = 0;

        if ($milestone->milestone_id)
        {
            $settledRewardCount = (int)$this->db()->fetchOne(
                "SELECT COUNT(*) FROM xf_wrxt_referral_reward WHERE milestone_id = ? AND status IN ('granted', 'revoked')",
                $milestone->milestone_id
            );
        }

        return $this->view(
            'WarextStudios\ReferralSystem:MilestoneEdit',
            'wrxt_referral_milestone_edit',
            [
                'milestone' => $milestone,
                'rewardCount' => $settledRewardCount,
                'settledRewardCount' => $settledRewardCount,
                'userGroups' => $this->finder('XF:UserGroup')->order('title', 'ASC')->fetch()
            ]
        );
    }

    public function actionMilestoneSave()
    {
        $this->assertPostOnly();

        $milestoneId = $this->filter('milestone_id', 'uint');
        $required = max(1, $this->filter('required_referrals', 'uint'));
        $title = mb_substr(trim($this->filter('title', 'str')), 0, 100);
        $description = mb_substr(trim($this->filter('description', 'str')), 0, 255);
        $icon = mb_substr(trim($this->filter('icon', 'str')), 0, 100);
        $imageUrl = mb_substr(trim($this->filter('image_url', 'str')), 0, 255);
        $rewardType = $this->filter('reward_type', 'str');
        $rewardUserGroupId = $this->filter('reward_user_group_id', 'uint');
        $revokeOnLoss = (bool)$this->filter('revoke_on_loss', 'bool');
        $displayOrder = $this->filter('display_order', 'uint');
        $isActive = (bool)$this->filter('is_active', 'bool');

        if ($title === '')
        {
            return $this->error(\XF::phrase('wrxt_referral_error_reward_title_required'));
        }

        if ($icon !== '' && !preg_match('/^[a-zA-Z0-9 _-]+$/D', $icon))
        {
            return $this->error(\XF::phrase('wrxt_referral_error_icon_invalid'));
        }

        if (
            $imageUrl !== ''
            && (
                str_starts_with($imageUrl, '//')
                || !preg_match('#^(?:https?://|/[^/]|data/|styles/)#i', $imageUrl)
            )
        )
        {
            return $this->error(\XF::phrase('wrxt_referral_error_image_invalid'));
        }

        if (!in_array($rewardType, ['display', 'user_group'], true))
        {
            return $this->error(\XF::phrase('wrxt_referral_error_reward_type_invalid'));
        }

        if ($rewardType === 'user_group')
        {
            $group = $rewardUserGroupId > 0
                ? $this->em()->find('XF:UserGroup', $rewardUserGroupId)
                : null;

            if (!$group)
            {
                return $this->error(\XF::phrase('wrxt_referral_error_reward_group_required'));
            }
        }
        else
        {
            $rewardUserGroupId = 0;
        }

        $duplicate = $this->finder('WarextStudios\ReferralSystem:Milestone')
            ->where('required_referrals', $required);

        if ($milestoneId)
        {
            $duplicate->where('milestone_id', '<>', $milestoneId);
        }

        if ($duplicate->fetchOne())
        {
            return $this->error(\XF::phrase('wrxt_referral_error_threshold_duplicate'));
        }

        $settledRewardCount = 0;

        if ($milestoneId)
        {
            $milestone = $this->em()->find('WarextStudios\ReferralSystem:Milestone', $milestoneId);
            if (!$milestone)
            {
                return $this->notFound();
            }

            $settledRewardCount = (int)$this->db()->fetchOne(
                "SELECT COUNT(*) FROM xf_wrxt_referral_reward WHERE milestone_id = ? AND status IN ('granted', 'revoked')",
                $milestoneId
            );

            if (
                $settledRewardCount > 0
                && (
                    (int)$milestone->required_referrals !== $required
                    || (string)$milestone->reward_type !== $rewardType
                    || (int)$milestone->reward_user_group_id !== $rewardUserGroupId
                )
            )
            {
                return $this->error(\XF::phrase('wrxt_referral_error_reward_history_locked'));
            }
        }
        else
        {
            $milestone = $this->em()->create('WarextStudios\ReferralSystem:Milestone');
        }

        $milestone->required_referrals = $required;
        $milestone->title = $title;
        $milestone->description = $description;
        $milestone->icon = $icon ?: 'fa-gift';
        $milestone->image_url = $imageUrl;
        $milestone->reward_type = $rewardType;
        $milestone->reward_user_group_id = $rewardUserGroupId;
        $milestone->revoke_on_loss = $revokeOnLoss;
        $milestone->display_order = $displayOrder;
        $milestone->is_active = $isActive;

        try
        {
            $milestone->save();
        }
        catch (\XF\Db\DuplicateKeyException $e)
        {
            return $this->error(\XF::phrase('wrxt_referral_error_threshold_duplicate'));
        }

        if ($milestoneId && $settledRewardCount === 0)
        {
            $this->db()->query(
                "UPDATE xf_wrxt_referral_reward
                SET milestone_required_referrals = ?, reward_type = ?, reward_value = ?, reward_title = ?, status = 'pending', error_code = ''
                WHERE milestone_id = ? AND status IN ('pending', 'failed')",
                [$required, $rewardType, $rewardUserGroupId, $title, $milestoneId]
            );
        }

        \XF::app()->jobManager()->enqueueUnique(
            'wrxtReferralReconcileRewards',
            'WarextStudios\ReferralSystem:ReconcileRewards',
            [],
            false
        );

        return $this->redirect($this->buildLink('referral-system'));
    }

    public function actionMilestoneDelete()
    {
        $this->assertPostOnly();

        $milestoneId = $this->filter('milestone_id', 'uint');
        $milestone = $this->em()->find('WarextStudios\ReferralSystem:Milestone', $milestoneId);

        if (!$milestone)
        {
            return $this->notFound();
        }

        $settledRewardCount = (int)$this->db()->fetchOne(
            "SELECT COUNT(*) FROM xf_wrxt_referral_reward WHERE milestone_id = ? AND status IN ('granted', 'revoked')",
            $milestoneId
        );

        if ($settledRewardCount > 0)
        {
            return $this->error(\XF::phrase('wrxt_referral_error_reward_history_delete'));
        }

        $this->db()->query(
            "DELETE FROM xf_wrxt_referral_reward WHERE milestone_id = ? AND status IN ('pending', 'failed')",
            $milestoneId
        );
        $milestone->delete();

        return $this->redirect($this->buildLink('referral-system'));
    }

    protected function parseDateFilter(string $value, bool $endOfDay): int
    {
        if ($value === '')
        {
            return 0;
        }

        $timestamp = strtotime($value . ($endOfDay ? ' 23:59:59' : ' 00:00:00'));
        return $timestamp === false ? 0 : $timestamp;
    }
}
