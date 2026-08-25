<?php

namespace WarextStudios\ReferralSystem\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class Referral extends Entity
{
    protected function _postSave()
    {
        if (!$this->isChanged('status') || $this->status !== 'valid')
        {
            return;
        }

        $invited = $this->Invited ?: $this->em()->find('XF:User', (int)$this->invited_user_id);
        $username = $invited ? (string)$invited->username : '';

        try
        {
            \XF::app()->service('WarextStudios\ReferralSystem:NotificationManager')
                ->referralValid((int)$this->inviter_user_id, $username);
        }
        catch (\Throwable $e)
        {
            \XF::logException($e, false, 'Warext Referral Notification: ');
        }
    }

    public static function getStructure(Structure $structure): Structure
    {
        $structure->table = 'xf_wrxt_referral';
        $structure->shortName = 'WarextStudios\ReferralSystem:Referral';
        $structure->primaryKey = 'referral_id';
        $structure->columns = [
            'referral_id' => ['type' => self::UINT, 'autoIncrement' => true],
            'inviter_user_id' => ['type' => self::UINT, 'required' => true],
            'invited_user_id' => ['type' => self::UINT, 'required' => true],
            'referral_code' => ['type' => self::STR, 'maxLength' => 24, 'required' => true],
            'status' => ['type' => self::STR, 'maxLength' => 16, 'default' => 'pending'],
            'created_date' => ['type' => self::UINT, 'default' => \XF::$time],
            'validated_date' => ['type' => self::UINT, 'default' => 0],
            'reviewed_date' => ['type' => self::UINT, 'default' => 0],
            'reviewed_by' => ['type' => self::UINT, 'default' => 0],
            'ip_hash' => ['type' => self::STR, 'maxLength' => 64, 'default' => ''],
            'risk_reason' => ['type' => self::STR, 'maxLength' => 100, 'default' => '']
        ];
        $structure->relations = [
            'Inviter' => [
                'entity' => 'XF:User',
                'type' => self::TO_ONE,
                'conditions' => [['user_id', '=', '$inviter_user_id']],
                'primary' => true
            ],
            'Invited' => [
                'entity' => 'XF:User',
                'type' => self::TO_ONE,
                'conditions' => [['user_id', '=', '$invited_user_id']],
                'primary' => true
            ],
            'ReviewedBy' => [
                'entity' => 'XF:User',
                'type' => self::TO_ONE,
                'conditions' => [['user_id', '=', '$reviewed_by']]
            ]
        ];

        return $structure;
    }
}
