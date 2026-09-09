<?php

namespace WarextStudios\ReferralSystem\Admin\Controller;

class ReferralCompat extends Referral
{
    protected function db()
    {
        return \XF::db();
    }

    public function actionMilestoneSave()
    {
        $upload = $this->request->getFile('reward_image', false, false);

        if ($upload)
        {
            $upload->requireImage();
            $upload->setAllowedExtensions(['jpg', 'jpeg', 'jpe', 'png', 'gif', 'webp']);
            $errors = [];

            if (!$upload->isValid($errors))
            {
                $error = $errors ? reset($errors) : \XF::phrase('wrxt_referral_error_image_invalid');
                return $this->error($error);
            }
        }

        $response = parent::actionMilestoneSave();

        if (!$upload || !($response instanceof \XF\Mvc\Reply\Redirect))
        {
            return $response;
        }

        $milestoneId = $this->filter('milestone_id', 'uint');
        $milestone = null;

        if ($milestoneId > 0)
        {
            $milestone = $this->em()->find('WarextStudios\ReferralSystem:Milestone', $milestoneId);
        }
        else
        {
            $required = max(1, $this->filter('required_referrals', 'uint'));
            $milestone = $this->finder('WarextStudios\ReferralSystem:Milestone')
                ->where('required_referrals', $required)
                ->fetchOne();
        }

        if (!$milestone)
        {
            return $response;
        }

        $extension = strtolower((string)pathinfo($upload->getFileName(), PATHINFO_EXTENSION));
        if ($extension === 'jpeg' || $extension === 'jpe')
        {
            $extension = 'jpg';
        }

        $fileName = hash(
            'sha256',
            $milestone->milestone_id . ':' . \XF::$time . ':' . bin2hex(random_bytes(16))
        ) . '.' . $extension;
        $relativePath = 'warext-referral/milestones/' . $fileName;

        try
        {
            \XF\Util\File::copyFileToAbstractedPath(
                $upload->getTempFile(),
                'data://' . $relativePath
            );

            $milestone->image_url = \XF::app()->applyExternalDataUrl($relativePath);
            $milestone->save();
        }
        catch (\Throwable $e)
        {
            \XF::logException($e, false, 'Warext Referral Milestone Image: ');
            return $this->error('Ödül görseli kaydedilemedi. Lütfen farklı bir görsel ile tekrar deneyin.');
        }

        return $response;
    }

    public function actionInviteApprove()
    {
        return $this->runInviteTool('approve');
    }

    public function actionInvitePending()
    {
        return $this->runInviteTool('pending');
    }

    public function actionInviteReject()
    {
        return $this->runInviteTool('reject');
    }

    public function actionInviteDelete()
    {
        return $this->runInviteTool('delete');
    }

    protected function runInviteTool(string $action)
    {
        $this->assertPostOnly();

        $referralId = $this->filter('referral_id', 'uint');
        if ($referralId <= 0)
        {
            return $this->notFound();
        }

        $service = $this->service('WarextStudios\ReferralSystem:AdminReferralManager');
        $actor = \XF::visitor();

        try
        {
            if ($action === 'approve')
            {
                $service->approve($referralId, $actor);
            }
            elseif ($action === 'pending')
            {
                $service->setPending($referralId, $actor);
            }
            elseif ($action === 'reject')
            {
                $service->reject($referralId, $actor);
            }
            elseif ($action === 'delete')
            {
                $service->delete($referralId, $actor);
            }
            else
            {
                throw new \InvalidArgumentException('Geçersiz davet yönetim işlemi.');
            }
        }
        catch (\InvalidArgumentException | \LogicException $e)
        {
            return $this->error($e->getMessage());
        }

        return $this->redirect($this->buildLink('referral-system/invites'));
    }
}
