<?php

namespace WarextStudios\ReferralSystem\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class ReferralCode extends Entity
{
    protected function _preSave()
    {
        parent::_preSave();

        if ($this->isChanged('owner_ip_hash'))
        {
            $this->owner_ip_hash_date = (string)$this->owner_ip_hash === '' ? 0 : \XF::$time;
        }
    }

    public static function getStructure(Structure $structure): Structure
    {
        $structure->table = 'xf_wrxt_referral_code';
        $structure->shortName = 'WarextStudios\ReferralSystem:ReferralCode';
        $structure->primaryKey = 'user_id';
        $structure->columns = [
            'user_id' => ['type' => self::UINT, 'required' => true],
            'code' => ['type' => self::STR, 'maxLength' => 24, 'required' => true],
            'owner_ip_hash' => ['type' => self::STR, 'maxLength' => 64, 'default' => ''],
            'owner_ip_hash_date' => ['type' => self::UINT, 'default' => 0],
            'is_active' => ['type' => self::BOOL, 'default' => true],
            'created_date' => ['type' => self::UINT, 'default' => \XF::$time],
            'modified_date' => ['type' => self::UINT, 'default' => 0],
            'modified_by' => ['type' => self::UINT, 'default' => 0],
            'suspended_date' => ['type' => self::UINT, 'default' => 0],
            'suspended_by' => ['type' => self::UINT, 'default' => 0],
            'suspension_reason' => ['type' => self::STR, 'maxLength' => 255, 'default' => '']
        ];
        $structure->relations = [
            'User' => [
                'entity' => 'XF:User',
                'type' => self::TO_ONE,
                'conditions' => 'user_id',
                'primary' => true
            ],
            'ModifiedBy' => [
                'entity' => 'XF:User',
                'type' => self::TO_ONE,
                'conditions' => [['user_id', '=', '$modified_by']]
            ],
            'SuspendedBy' => [
                'entity' => 'XF:User',
                'type' => self::TO_ONE,
                'conditions' => [['user_id', '=', '$suspended_by']]
            ]
        ];

        return $structure;
    }
}
