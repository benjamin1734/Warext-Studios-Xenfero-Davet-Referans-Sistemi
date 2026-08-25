<?php

namespace WarextStudios\ReferralSystem\XF\Entity;

class UserGroup extends XFCP_UserGroup
{
    protected function _preDelete()
    {
        parent::_preDelete();

        if ((int)$this->user_group_id <= 0)
        {
            return;
        }

        $milestoneId = (int)$this->db()->fetchOne(
            "SELECT milestone_id
            FROM xf_wrxt_referral_milestone
            WHERE reward_type = 'user_group' AND reward_user_group_id = ?
            LIMIT 1",
            (int)$this->user_group_id
        );

        if ($milestoneId)
        {
            $this->error(\XF::phrase('wrxt_referral_error_reward_group_in_use'));
        }
    }
}
