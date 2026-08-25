<?php

namespace WarextStudios\ReferralSystem\Pub\Controller;

use XF\Pub\Controller\AbstractController;

class Referral extends AbstractController
{
    public function actionIndex()
    {
        if (!(bool)(\XF::options()->wrxtReferralEnabled ?? true))
        {
            return $this->error(\XF::phrase('wrxt_referral_error_disabled'));
        }

        $this->assertRegistrationRequired();

        $visitor = \XF::visitor();
        $repo = $this->repository('WarextStudios\ReferralSystem:Referral');

        if (!$repo->isUserUsableAsInviter($visitor))
        {
            return $this->error(\XF::phrase('wrxt_referral_error_inviter_unavailable'));
        }

        $code = $repo->getCodeForUserId((int)$visitor->user_id, true);
        if (!$code)
        {
            return $this->error(\XF::phrase('wrxt_referral_error_code_create_failed'));
        }

        try
        {
            $this->service('WarextStudios\ReferralSystem:RewardManager')
                ->reconcileUser((int)$visitor->user_id);
        }
        catch (\Throwable $e)
        {
            \XF::logException($e, false, 'Warext Referral Reward: ');
        }

        $stats = $repo->getStatsForUser((int)$visitor->user_id);
        $progress = $repo->getProgressData((int)$stats['valid']);
        $referrals = $repo->getRecentReferrals((int)$visitor->user_id, 20);
        $rewards = $repo->getRewardsForUser((int)$visitor->user_id, 50);
        $rewardMap = $repo->getRewardMapForUser((int)$visitor->user_id);
        $inviteLink = $this->buildLink('canonical:davetler/go', null, ['code' => $code->code]);
        $whatsappShareUrl = 'https://wa.me/?text=' . rawurlencode('Davet bağlantım: ' . $inviteLink);
        $telegramShareUrl = 'https://t.me/share/url?url=' . rawurlencode($inviteLink)
            . '&text=' . rawurlencode('Davet bağlantımı kullanarak kayıt olabilirsin.');

        return $this->view(
            'WarextStudios\ReferralSystem:Dashboard',
            'wrxt_referral_dashboard',
            [
                'code' => $code,
                'stats' => $stats,
                'progress' => $progress,
                'referrals' => $referrals,
                'rewards' => $rewards,
                'rewardMap' => $rewardMap,
                'inviteLink' => $inviteLink,
                'whatsappShareUrl' => $whatsappShareUrl,
                'telegramShareUrl' => $telegramShareUrl,
                'canManageCodes' => $visitor->hasPermission('wrxtReferral', 'manageCodes'),
                'canReviewReferrals' => $visitor->hasPermission('wrxtReferral', 'reviewReferrals')
            ]
        );
    }

    public function actionHistory()
    {
        if (!(bool)(\XF::options()->wrxtReferralEnabled ?? true))
        {
            return $this->error(\XF::phrase('wrxt_referral_error_disabled'));
        }

        $this->assertRegistrationRequired();

        if (!(bool)(\XF::options()->wrxtReferralShowList ?? true))
        {
            return $this->notFound();
        }

        $visitor = \XF::visitor();
        $page = max(1, $this->filter('page', 'uint'));
        $status = $this->filter('status', 'str');
        $allowedStatuses = ['pending', 'review', 'valid', 'rejected'];

        if (!in_array($status, $allowedStatuses, true))
        {
            $status = '';
        }

        $finder = $this->finder('WarextStudios\ReferralSystem:Referral')
            ->where('inviter_user_id', $visitor->user_id)
            ->with('Invited');

        if ($status !== '')
        {
            $finder->where('status', $status);
        }

        $perPage = 30;
        $total = $finder->total();
        $this->assertValidPage($page, $perPage, $total, 'davetler/history');
        $referrals = $finder
            ->order('referral_id', 'DESC')
            ->limitByPage($page, $perPage)
            ->fetch();

        return $this->view(
            'WarextStudios\ReferralSystem:History',
            'wrxt_referral_history',
            [
                'referrals' => $referrals,
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'status' => $status
            ]
        );
    }

    public function actionGo()
    {
        if (!(bool)(\XF::options()->wrxtReferralEnabled ?? true))
        {
            return $this->error(\XF::phrase('wrxt_referral_error_disabled'));
        }

        $value = $this->filter('code', 'str');
        $repo = $this->repository('WarextStudios\ReferralSystem:Referral');
        $code = $repo->findActiveCode($value);

        if (!$code)
        {
            return $this->error(\XF::phrase('wrxt_referral_error_code_invalid'));
        }

        $visitor = \XF::visitor();
        if ((int)$visitor->user_id > 0)
        {
            if ((int)$visitor->user_id === (int)$code->user_id)
            {
                return $this->error(\XF::phrase('wrxt_referral_error_self_link'));
            }

            return $this->redirect($this->buildLink('davetler'));
        }

        $cookieCode = (string)\XF::app()->request()->getCookie('wrxt_referral');
        $existingCode = $cookieCode !== '' ? $repo->findActiveCode($cookieCode) : null;
        $attributionCode = $existingCode ?: $code;

        if (!$existingCode)
        {
            $days = max(1, min(90, (int)(\XF::options()->wrxtReferralCookieDays ?? 30)));
            \XF::app()->response()->setCookie('wrxt_referral', $code->code, \XF::$time + ($days * 86400));
        }

        return $this->redirect($this->buildLink('register', null, ['ref' => $attributionCode->code]));
    }

    public function actionManage()
    {
        $this->assertRegistrationRequired();
        $visitor = \XF::visitor();

        if (!$visitor->hasPermission('wrxtReferral', 'manageCodes'))
        {
            return $this->noPermission();
        }

        $userId = $this->filter('user_id', 'uint');
        $username = trim($this->filter('username', 'str'));
        $target = null;

        if ($userId)
        {
            $target = $this->em()->find('XF:User', $userId);
        }
        elseif ($username !== '')
        {
            $target = $this->finder('XF:User')
                ->where('username', $username)
                ->fetchOne();
        }

        $code = null;
        if ($target)
        {
            $code = $this->repository('WarextStudios\ReferralSystem:Referral')
                ->getCodeForUserId((int)$target->user_id, true);
        }

        return $this->view(
            'WarextStudios\ReferralSystem:Manage',
            'wrxt_referral_manage',
            [
                'target' => $target,
                'code' => $code,
                'username' => $username
            ]
        );
    }

    public function actionManageSave()
    {
        $this->assertPostOnly();
        $this->assertRegistrationRequired();

        $visitor = \XF::visitor();
        if (!$visitor->hasPermission('wrxtReferral', 'manageCodes'))
        {
            return $this->noPermission();
        }

        $userId = $this->filter('user_id', 'uint');
        $newCode = $this->filter('code', 'str');
        $active = (bool)$this->filter('is_active', 'bool');
        $reason = $this->filter('suspension_reason', 'str');
        $target = $this->em()->find('XF:User', $userId);

        if (!$target)
        {
            return $this->error(\XF::phrase('wrxt_referral_error_user_not_found'));
        }

        try
        {
            $this->service('WarextStudios\ReferralSystem:CodeManager')
                ->update($visitor, $target, $newCode, $active, $reason);
        }
        catch (\InvalidArgumentException | \LogicException | \RuntimeException $e)
        {
            return $this->error($e->getMessage());
        }

        return $this->redirect($this->buildLink('davetler/manage', null, ['user_id' => $target->user_id]));
    }

    public function actionReview()
    {
        $this->assertRegistrationRequired();
        $visitor = \XF::visitor();

        if (!$visitor->hasPermission('wrxtReferral', 'reviewReferrals'))
        {
            return $this->noPermission();
        }

        return $this->view(
            'WarextStudios\ReferralSystem:Review',
            'wrxt_referral_review',
            [
                'referrals' => $this->repository('WarextStudios\ReferralSystem:Referral')->getReviewQueue(100)
            ]
        );
    }

    public function actionReviewSave()
    {
        $this->assertPostOnly();
        $this->assertRegistrationRequired();

        $visitor = \XF::visitor();
        if (!$visitor->hasPermission('wrxtReferral', 'reviewReferrals'))
        {
            return $this->noPermission();
        }

        $referralId = $this->filter('referral_id', 'uint');
        $decision = $this->filter('decision', 'str');

        try
        {
            $this->service('WarextStudios\ReferralSystem:ReviewManager')
                ->review($visitor, $referralId, $decision);
        }
        catch (\InvalidArgumentException | \LogicException | \RuntimeException $e)
        {
            return $this->error($e->getMessage());
        }

        return $this->redirect($this->buildLink('davetler/review'));
    }
}
