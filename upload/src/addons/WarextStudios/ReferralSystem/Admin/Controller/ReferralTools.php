<?php

namespace WarextStudios\ReferralSystem\Admin\Controller;

class ReferralTools extends XFCP_ReferralTools
{
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
