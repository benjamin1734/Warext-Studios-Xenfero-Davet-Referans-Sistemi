<?php

namespace WarextStudios\ReferralSystem\Service;

use WarextStudios\ReferralSystem\Entity\ReferralCode;
use XF\Entity\User;
use XF\Service\AbstractService;

class CodeManager extends AbstractService
{
    public function update(User $actor, User $target, string $newCode, bool $active, string $reason = ''): ReferralCode
    {
        if (!$actor->hasPermission('wrxtReferral', 'manageCodes'))
        {
            throw new \LogicException((string)\XF::phrase('wrxt_referral_error_no_permission'));
        }

        $repo = $this->app->repository('WarextStudios\ReferralSystem:Referral');
        $repo->getCodeForUserId((int)$target->user_id, true);

        $newCode = $repo->normalizeCode($newCode);
        $reason = mb_substr(trim($reason), 0, 255);

        if (!$repo->isCodeValid($newCode))
        {
            throw new \InvalidArgumentException((string)\XF::phrase('wrxt_referral_error_code_format'));
        }

        if (!$active && $reason === '')
        {
            throw new \InvalidArgumentException((string)\XF::phrase('wrxt_referral_error_suspension_reason_required'));
        }

        $db = $this->app->db();
        $db->beginTransaction();

        try
        {
            $lockedUserId = (int)$db->fetchOne(
                'SELECT user_id FROM xf_wrxt_referral_code WHERE user_id = ? FOR UPDATE',
                (int)$target->user_id
            );

            if (!$lockedUserId)
            {
                throw new \RuntimeException((string)\XF::phrase('wrxt_referral_error_code_missing'));
            }

            $this->app->em()->clearEntityCache('WarextStudios\ReferralSystem:ReferralCode');
            $code = $this->app->em()->find(
                'WarextStudios\ReferralSystem:ReferralCode',
                (int)$target->user_id
            );

            if (!$code)
            {
                throw new \RuntimeException((string)\XF::phrase('wrxt_referral_error_code_missing'));
            }

            $oldCode = (string)$code->code;
            $oldActive = (bool)$code->is_active;
            $repo->reserveCode($oldCode, (int)$target->user_id);

            if ($newCode !== $oldCode)
            {
                if (!$repo->reserveCode($newCode, (int)$target->user_id))
                {
                    throw new \InvalidArgumentException((string)\XF::phrase('wrxt_referral_error_code_reserved'));
                }

                $duplicate = $this->app->finder('WarextStudios\ReferralSystem:ReferralCode')
                    ->where('code', $newCode)
                    ->where('user_id', '<>', $target->user_id)
                    ->fetchOne();

                if ($duplicate)
                {
                    throw new \InvalidArgumentException((string)\XF::phrase('wrxt_referral_error_code_reserved'));
                }

                $code->code = $newCode;
            }

            $code->is_active = $active;
            $code->modified_date = \XF::$time;
            $code->modified_by = $actor->user_id;

            if ($active)
            {
                $code->suspended_date = 0;
                $code->suspended_by = 0;
                $code->suspension_reason = '';
            }
            else
            {
                $code->suspended_date = \XF::$time;
                $code->suspended_by = $actor->user_id;
                $code->suspension_reason = $reason;
            }

            $code->save();

            if ($oldActive && !$active)
            {
                $db->query(
                    "UPDATE xf_wrxt_referral
                    SET status = 'review', risk_reason = 'code_suspended', reviewed_date = 0, reviewed_by = 0
                    WHERE inviter_user_id = ? AND status = 'pending'",
                    (int)$target->user_id
                );
            }

            if ($oldCode !== (string)$code->code || $oldActive !== (bool)$code->is_active)
            {
                $db->insert('xf_wrxt_referral_code_log', [
                    'target_user_id' => (int)$target->user_id,
                    'actor_user_id' => (int)$actor->user_id,
                    'old_code' => $oldCode,
                    'new_code' => (string)$code->code,
                    'old_active' => $oldActive ? 1 : 0,
                    'new_active' => $code->is_active ? 1 : 0,
                    'reason' => $reason,
                    'log_date' => \XF::$time
                ]);
            }

            $db->commit();
            return $code;
        }
        catch (\XF\Db\DuplicateKeyException $e)
        {
            $db->rollback();
            throw new \InvalidArgumentException((string)\XF::phrase('wrxt_referral_error_code_reserved'));
        }
        catch (\Throwable $e)
        {
            $db->rollback();
            throw $e;
        }
    }
}
