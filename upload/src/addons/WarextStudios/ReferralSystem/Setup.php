<?php

namespace WarextStudios\ReferralSystem;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;
use XF\Db\Schema\Create;

class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    public function installStep1(): void
    {
        $this->schemaManager()->createTable('xf_wrxt_referral_code', function(Create $table)
        {
            $table->addColumn('user_id', 'int')->unsigned();
            $table->addColumn('code', 'varchar', 24);
            $table->addColumn('owner_ip_hash', 'char', 64)->setDefault('');
            $table->addColumn('owner_ip_hash_date', 'int')->unsigned()->setDefault(0);
            $table->addColumn('is_active', 'tinyint')->setDefault(1);
            $table->addColumn('created_date', 'int')->unsigned();
            $table->addColumn('modified_date', 'int')->unsigned()->setDefault(0);
            $table->addColumn('modified_by', 'int')->unsigned()->setDefault(0);
            $table->addColumn('suspended_date', 'int')->unsigned()->setDefault(0);
            $table->addColumn('suspended_by', 'int')->unsigned()->setDefault(0);
            $table->addColumn('suspension_reason', 'varchar', 255)->setDefault('');
            $table->addPrimaryKey('user_id');
            $table->addUniqueKey('code');
            $table->addKey('is_active');
            $table->addKey('owner_ip_hash_date');
        });

        $this->createCodeReservationTable();
    }

    public function installStep2(): void
    {
        $this->schemaManager()->createTable('xf_wrxt_referral', function(Create $table)
        {
            $table->addColumn('referral_id', 'int')->unsigned()->autoIncrement();
            $table->addColumn('inviter_user_id', 'int')->unsigned();
            $table->addColumn('invited_user_id', 'int')->unsigned();
            $table->addColumn('referral_code', 'varchar', 24);
            $table->addColumn('status', 'varchar', 16)->setDefault('pending');
            $table->addColumn('created_date', 'int')->unsigned();
            $table->addColumn('validated_date', 'int')->unsigned()->setDefault(0);
            $table->addColumn('reviewed_date', 'int')->unsigned()->setDefault(0);
            $table->addColumn('reviewed_by', 'int')->unsigned()->setDefault(0);
            $table->addColumn('ip_hash', 'char', 64)->setDefault('');
            $table->addColumn('risk_reason', 'varchar', 100)->setDefault('');
            $table->addPrimaryKey('referral_id');
            $table->addUniqueKey('invited_user_id');
            $table->addKey(['inviter_user_id', 'status']);
            $table->addKey(['inviter_user_id', 'created_date']);
            $table->addKey(['ip_hash', 'created_date']);
            $table->addKey(['status', 'referral_id'], 'status_referral');
        });
    }

    public function installStep3(): void
    {
        $this->schemaManager()->createTable('xf_wrxt_referral_milestone', function(Create $table)
        {
            $table->addColumn('milestone_id', 'int')->unsigned()->autoIncrement();
            $table->addColumn('required_referrals', 'int')->unsigned();
            $table->addColumn('title', 'varchar', 100);
            $table->addColumn('description', 'varchar', 255)->setDefault('');
            $table->addColumn('icon', 'varchar', 100)->setDefault('fa-gift');
            $table->addColumn('image_url', 'varchar', 255)->setDefault('');
            $table->addColumn('reward_type', 'varchar', 20)->setDefault('display');
            $table->addColumn('reward_user_group_id', 'int')->unsigned()->setDefault(0);
            $table->addColumn('revoke_on_loss', 'tinyint')->setDefault(1);
            $table->addColumn('display_order', 'int')->unsigned()->setDefault(10);
            $table->addColumn('is_active', 'tinyint')->setDefault(1);
            $table->addPrimaryKey('milestone_id');
            $table->addUniqueKey('required_referrals');
            $table->addKey(['is_active', 'display_order']);
        });

        $this->db()->insertBulk('xf_wrxt_referral_milestone', [
            ['required_referrals' => 1, 'title' => 'İlk Davet', 'description' => 'İlk geçerli davet ödülü', 'icon' => 'fa-gift', 'image_url' => '', 'reward_type' => 'display', 'reward_user_group_id' => 0, 'revoke_on_loss' => 1, 'display_order' => 10, 'is_active' => 1],
            ['required_referrals' => 5, 'title' => 'Davetçi', 'description' => '5 geçerli davet ödülü', 'icon' => 'fa-award', 'image_url' => '', 'reward_type' => 'display', 'reward_user_group_id' => 0, 'revoke_on_loss' => 1, 'display_order' => 20, 'is_active' => 1],
            ['required_referrals' => 10, 'title' => 'Aktif Davetçi', 'description' => '10 geçerli davet ödülü', 'icon' => 'fa-medal', 'image_url' => '', 'reward_type' => 'display', 'reward_user_group_id' => 0, 'revoke_on_loss' => 1, 'display_order' => 30, 'is_active' => 1],
            ['required_referrals' => 25, 'title' => 'Topluluk Elçisi', 'description' => '25 geçerli davet ödülü', 'icon' => 'fa-trophy', 'image_url' => '', 'reward_type' => 'display', 'reward_user_group_id' => 0, 'revoke_on_loss' => 1, 'display_order' => 40, 'is_active' => 1]
        ]);
    }

    public function installStep4(): void
    {
        $this->schemaManager()->createTable('xf_wrxt_referral_code_log', function(Create $table)
        {
            $table->addColumn('log_id', 'int')->unsigned()->autoIncrement();
            $table->addColumn('target_user_id', 'int')->unsigned();
            $table->addColumn('actor_user_id', 'int')->unsigned();
            $table->addColumn('old_code', 'varchar', 24)->setDefault('');
            $table->addColumn('new_code', 'varchar', 24)->setDefault('');
            $table->addColumn('old_active', 'tinyint')->setDefault(1);
            $table->addColumn('new_active', 'tinyint')->setDefault(1);
            $table->addColumn('reason', 'varchar', 255)->setDefault('');
            $table->addColumn('log_date', 'int')->unsigned();
            $table->addPrimaryKey('log_id');
            $table->addKey(['target_user_id', 'log_date']);
            $table->addKey(['actor_user_id', 'log_date']);
        });
    }

    public function installStep5(): void
    {
        $this->createRewardTable();
    }

    public function installStep6(): void
    {
        \XF::app()->jobManager()->enqueue(
            'WarextStudios\ReferralSystem:GenerateCodes',
            []
        );
    }

    public function upgrade1000020Step1(): void
    {
        $this->schemaManager()->alterTable('xf_wrxt_referral', function(Alter $table)
        {
            $table->addKey(['status', 'referral_id'], 'status_referral');
        });
    }

    public function upgrade1000020Step2(): void
    {
        \XF::app()->jobManager()->enqueueUnique(
            'wrxtReferralRefreshPending',
            'WarextStudios\ReferralSystem:RefreshPending',
            [],
            false
        );
    }

    public function upgrade1000030Step1(): void
    {
        $this->schemaManager()->alterTable('xf_wrxt_referral_milestone', function(Alter $table)
        {
            $table->addColumn('reward_type', 'varchar', 20)->setDefault('display');
            $table->addColumn('reward_user_group_id', 'int')->unsigned()->setDefault(0);
            $table->addColumn('revoke_on_loss', 'tinyint')->setDefault(1);
        });
    }

    public function upgrade1000030Step2(): void
    {
        $this->createRewardTable();
    }

    public function upgrade1000030Step3(): void
    {
        \XF::app()->jobManager()->enqueueUnique(
            'wrxtReferralReconcileRewards',
            'WarextStudios\ReferralSystem:ReconcileRewards',
            [],
            false
        );
    }

    public function upgrade1000040Step1(): void
    {
        $this->createCodeReservationTable();
    }

    public function upgrade1000040Step2(): void
    {
        $db = $this->db();
        $db->query(
            'INSERT IGNORE INTO xf_wrxt_referral_code_reservation (code, user_id, reserved_date)
            SELECT code, user_id, created_date FROM xf_wrxt_referral_code WHERE code <> \'\''
        );
        $db->query(
            'INSERT IGNORE INTO xf_wrxt_referral_code_reservation (code, user_id, reserved_date)
            SELECT old_code, target_user_id, log_date FROM xf_wrxt_referral_code_log WHERE old_code <> \'\''
        );
        $db->query(
            'INSERT IGNORE INTO xf_wrxt_referral_code_reservation (code, user_id, reserved_date)
            SELECT new_code, target_user_id, log_date FROM xf_wrxt_referral_code_log WHERE new_code <> \'\''
        );
    }

    public function upgrade1000041Step1(): void
    {
        $this->schemaManager()->alterTable('xf_wrxt_referral_code', function(Alter $table)
        {
            $table->addColumn('owner_ip_hash_date', 'int')->unsigned()->setDefault(0)->after('owner_ip_hash');
            $table->addKey('owner_ip_hash_date');
        });
    }

    public function upgrade1000041Step2(): void
    {
        $this->db()->query(
            'UPDATE xf_wrxt_referral_code SET owner_ip_hash_date = ? WHERE owner_ip_hash <> \'\' AND owner_ip_hash_date = 0',
            \XF::$time
        );
    }

    public function upgrade1000100Step1(): void
    {
        \XF::app()->jobManager()->enqueueUnique(
            'wrxtReferralGenerateCodes',
            'WarextStudios\ReferralSystem:GenerateCodes',
            [],
            false
        );
        \XF::app()->jobManager()->enqueueUnique(
            'wrxtReferralReconcileRewards',
            'WarextStudios\ReferralSystem:ReconcileRewards',
            [],
            false
        );
    }

    protected function createCodeReservationTable(): void
    {
        $this->schemaManager()->createTable('xf_wrxt_referral_code_reservation', function(Create $table)
        {
            $table->addColumn('code', 'varchar', 24);
            $table->addColumn('user_id', 'int')->unsigned();
            $table->addColumn('reserved_date', 'int')->unsigned();
            $table->addPrimaryKey('code');
            $table->addKey(['user_id', 'reserved_date']);
        });
    }

    protected function createRewardTable(): void
    {
        $this->schemaManager()->createTable('xf_wrxt_referral_reward', function(Create $table)
        {
            $table->addColumn('reward_id', 'int')->unsigned()->autoIncrement();
            $table->addColumn('user_id', 'int')->unsigned();
            $table->addColumn('milestone_id', 'int')->unsigned();
            $table->addColumn('milestone_required_referrals', 'int')->unsigned();
            $table->addColumn('reward_type', 'varchar', 20)->setDefault('display');
            $table->addColumn('reward_value', 'int')->unsigned()->setDefault(0);
            $table->addColumn('reward_title', 'varchar', 100)->setDefault('');
            $table->addColumn('status', 'varchar', 16)->setDefault('pending');
            $table->addColumn('created_date', 'int')->unsigned();
            $table->addColumn('granted_date', 'int')->unsigned()->setDefault(0);
            $table->addColumn('revoked_date', 'int')->unsigned()->setDefault(0);
            $table->addColumn('last_attempt_date', 'int')->unsigned()->setDefault(0);
            $table->addColumn('attempts', 'int')->unsigned()->setDefault(0);
            $table->addColumn('error_code', 'varchar', 100)->setDefault('');
            $table->addPrimaryKey('reward_id');
            $table->addUniqueKey(['user_id', 'milestone_id']);
            $table->addKey(['user_id', 'status']);
            $table->addKey(['status', 'reward_id']);
        });
    }

    public function uninstallStep1(): void
    {
        $this->schemaManager()->dropTable('xf_wrxt_referral_reward');
        $this->schemaManager()->dropTable('xf_wrxt_referral_code_log');
        $this->schemaManager()->dropTable('xf_wrxt_referral_milestone');
        $this->schemaManager()->dropTable('xf_wrxt_referral');
        $this->schemaManager()->dropTable('xf_wrxt_referral_code');
        $this->schemaManager()->dropTable('xf_wrxt_referral_code_reservation');
    }
}
