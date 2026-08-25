<?php

namespace WarextStudios\ReferralSystem\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class Reward extends Entity
{
    protected function _postSave()
    {
        if (!$this->isChanged('status'))
        {
            return;
        }

        try
        {
            $service = \XF::app()->service('WarextStudios\ReferralSystem:NotificationManager');

            if ($this->status === 'granted')
            {
                $service->rewardGranted((int)$this->user_id, (string)$this->reward_title);
            }
            elseif ($this->status === 'revoked')
            {
                $service->rewardRevoked((int)$this->user_id, (string)$this->reward_title);
            }
        }
        catch (\Throwable $e)
        {
            \XF::logException($e, false, 'Warext Referral Notification: ');
        }
    }

    public static function getStructure(Structure $structure): Structure
    {
        $structure->table = 'xf_wrxt_referral_reward';
        $structure->shortName = 'WarextStudios\ReferralSystem:Reward';
        $structure->primaryKey = 'reward_id';
        $structure->columns = [
            'reward_id' => ['type' => self::UINT, 'autoIncrement' => true],
            'user_id' => ['type' => self::UINT, 'required' => true],
            'milestone_id' => ['type' => self::UINT, 'required' => true],
            'milestone_required_referrals' => ['type' => self::UINT, 'required' => true],
            'reward_type' => ['type' => self::STR, 'maxLength' => 20, 'default' => 'display'],
            'reward_value' => ['type' => self::UINT, 'default' => 0],
            'reward_title' => ['type' => self::STR, 'maxLength' => 100, 'default' => ''],
            'status' => ['type' => self::STR, 'maxLength' => 16, 'default' => 'pending'],
            'created_date' => ['type' => self::UINT, 'default' => \XF::$time],
            'granted_date' => ['type' => self::UINT, 'default' => 0],
            'revoked_date' => ['type' => self::UINT, 'default' => 0],
            'last_attempt_date' => ['type' => self::UINT, 'default' => 0],
            'attempts' => ['type' => self::UINT, 'default' => 0],
            'error_code' => ['type' => self::STR, 'maxLength' => 100, 'default' => '']
        ];
        $structure->relations = [
            'User' => [
                'entity' => 'XF:User',
                'type' => self::TO_ONE,
                'conditions' => 'user_id',
                'primary' => true
            ],
            'Milestone' => [
                'entity' => 'WarextStudios\ReferralSystem:Milestone',
                'type' => self::TO_ONE,
                'conditions' => 'milestone_id'
            ]
        ];

        return $structure;
    }
}
