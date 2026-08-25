<?php

namespace WarextStudios\ReferralSystem\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class Milestone extends Entity
{
    public static function getStructure(Structure $structure): Structure
    {
        $structure->table = 'xf_wrxt_referral_milestone';
        $structure->shortName = 'WarextStudios\ReferralSystem:Milestone';
        $structure->primaryKey = 'milestone_id';
        $structure->columns = [
            'milestone_id' => ['type' => self::UINT, 'autoIncrement' => true],
            'required_referrals' => ['type' => self::UINT, 'required' => true],
            'title' => ['type' => self::STR, 'maxLength' => 100, 'required' => true],
            'description' => ['type' => self::STR, 'maxLength' => 255, 'default' => ''],
            'icon' => ['type' => self::STR, 'maxLength' => 100, 'default' => 'fa-gift'],
            'image_url' => ['type' => self::STR, 'maxLength' => 255, 'default' => ''],
            'reward_type' => ['type' => self::STR, 'maxLength' => 20, 'default' => 'display'],
            'reward_user_group_id' => ['type' => self::UINT, 'default' => 0],
            'revoke_on_loss' => ['type' => self::BOOL, 'default' => true],
            'display_order' => ['type' => self::UINT, 'default' => 10],
            'is_active' => ['type' => self::BOOL, 'default' => true]
        ];
        $structure->relations = [
            'RewardUserGroup' => [
                'entity' => 'XF:UserGroup',
                'type' => self::TO_ONE,
                'conditions' => [['user_group_id', '=', '$reward_user_group_id']]
            ]
        ];

        return $structure;
    }
}
