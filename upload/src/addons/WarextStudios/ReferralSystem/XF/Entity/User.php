<?php

namespace WarextStudios\ReferralSystem\XF\Entity;

class User extends XFCP_User
{
    protected function _preSave()
    {
        parent::_preSave();

        if (
            !$this->isInsert()
            || !(\XF::app() instanceof \XF\Pub\App)
            || !(bool)(\XF::options()->wrxtReferralEnabled ?? true)
        )
        {
            return;
        }

        $requestCode = (string)\XF::app()->request()->filter('wrxt_referral_code', 'str');
        if ($requestCode === '')
        {
            return;
        }

        $repo = $this->repository('WarextStudios\ReferralSystem:Referral');
        if (!$repo->findActiveCode($requestCode))
        {
            $this->error(\XF::phrase('wrxt_referral_error_code_invalid'));
        }
    }

    protected function _postSave()
    {
        $wasInsert = $this->isInsert();
        parent::_postSave();

        if (!$wasInsert || (int)$this->user_id <= 0)
        {
            return;
        }

        $repo = $this->repository('WarextStudios\ReferralSystem:Referral');
        $isPublicApp = \XF::app() instanceof \XF\Pub\App;

        try
        {
            $repo->getCodeForUserId((int)$this->user_id, true, $isPublicApp);
        }
        catch (\Throwable $e)
        {
            \XF::logException($e, false, 'Warext Referral Code: ');
        }

        if (!$isPublicApp)
        {
            return;
        }

        if (!(bool)(\XF::options()->wrxtReferralEnabled ?? true))
        {
            \XF::app()->response()->setCookie('wrxt_referral', '', \XF::$time - 3600);
            return;
        }

        try
        {
            $explicitCode = (string)\XF::app()->request()->filter('wrxt_referral_code', 'str');
            $repo->attachReferral($this, $explicitCode);
            \XF::app()->response()->setCookie('wrxt_referral', '', \XF::$time - 3600);
        }
        catch (\Throwable $e)
        {
            \XF::logException($e, false, 'Warext Referral Attribution: ');
        }
    }

    protected function _postDelete()
    {
        $userId = (int)$this->user_id;
        parent::_postDelete();

        if ($userId <= 0)
        {
            return;
        }

        $db = $this->db();
        $affectedInviters = $db->fetchAllColumn(
            'SELECT DISTINCT inviter_user_id FROM xf_wrxt_referral WHERE invited_user_id = ? AND inviter_user_id <> ?',
            [$userId, $userId]
        );

        $db->query('DELETE FROM xf_wrxt_referral_reward WHERE user_id = ?', $userId);
        $db->query('DELETE FROM xf_wrxt_referral_code WHERE user_id = ?', $userId);
        $db->query(
            'DELETE FROM xf_wrxt_referral WHERE inviter_user_id = ? OR invited_user_id = ?',
            [$userId, $userId]
        );
        $db->query('DELETE FROM xf_wrxt_referral_code_log WHERE target_user_id = ?', $userId);
        $db->query(
            'UPDATE xf_wrxt_referral_code_log SET actor_user_id = 0 WHERE actor_user_id = ?',
            $userId
        );

        foreach ($affectedInviters as $inviterUserId)
        {
            try
            {
                \XF::app()->service('WarextStudios\ReferralSystem:RewardManager')
                    ->reconcileUser((int)$inviterUserId);
            }
            catch (\Throwable $e)
            {
                \XF::logException($e, false, 'Warext Referral Reward: ');
            }
        }
    }
}
