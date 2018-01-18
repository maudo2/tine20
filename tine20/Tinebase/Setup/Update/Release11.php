<?php
/**
 * Tine 2.0
 *
 * @package     Tinebase
 * @subpackage  Setup
 * @license     http://www.gnu.org/licenses/agpl.html AGPL3
 * @copyright   Copyright (c) 2016-2017 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Philipp Schüle <p.schuele@metaways.de>
 */
class Tinebase_Setup_Update_Release11 extends Setup_Update_Abstract
{
    /**
     * update to 11.1
     *
     * change configuration column to xprops in accounts
     */
    public function update_0()
    {
        $release10 = new Tinebase_Setup_Update_Release10($this->_backend);
        $release10->update_42();
        $this->setApplicationVersion('Tinebase', '11.1');
    }

    /**
     * update to 11.2
     *
     * add deleted_time to unique index for groups, roles, tree_nodes
     */
    public function update_1()
    {
        $release10 = new Tinebase_Setup_Update_Release10($this->_backend);
        $release10->update_43();
        $this->setApplicationVersion('Tinebase', '11.2');
    }

    /**
     * update to 11.3
     *
     * add do_acl to record_observer
     */
    public function update_2()
    {
        $release10 = new Tinebase_Setup_Update_Release10($this->_backend);
        $release10->update_44();
        $this->setApplicationVersion('Tinebase', '11.3');
    }

    /**
     * update to 11.4
     *
     * fix pgsql index creation issue
     */
    public function update_3()
    {
        $release9 = new Tinebase_Setup_Update_Release9($this->_backend);
        $release9->update_13();
        $this->setApplicationVersion('Tinebase', '11.4');
    }

    /**
     * update to 11.5
     *
     * add acl table cleanup task
     */
    public function update_4()
    {
        if (version_compare($this->getApplicationVersion('Tinebase'), '11.12') === -1) {
            return;
        }
        $release9 = new Tinebase_Setup_Update_Release10($this->_backend);
        $release9->update_46();
        $this->setApplicationVersion('Tinebase', '11.5');
    }

    /**
     * update to 11.6
     *
     * fix pgsql index creation issue (again! as it did not work in the previous CE release)
     */
    public function update_5()
    {
        $release9 = new Tinebase_Setup_Update_Release9($this->_backend);
        $release9->update_13();
        $this->setApplicationVersion('Tinebase', '11.6');
    }

    /**
     * update to 11.7
     *
     * add full text index to customfield
     */
    public function update_6()
    {
        $release10 = new Tinebase_Setup_Update_Release10($this->_backend);
        $release10->update_47();
        $this->setApplicationVersion('Tinebase', '11.7');
    }

    /**
     * update to 11.8
     *
     * add index(255) on customfield.value
     */
    public function update_7()
    {
        $release10 = new Tinebase_Setup_Update_Release10($this->_backend);
        $release10->update_48();
        $this->setApplicationVersion('Tinebase', '11.8');
    }

    /**
     * update to 11.9
     *
     * addFileSystemSanitizePreviewsTask
     */
    public function update_8()
    {
        if (version_compare($this->getApplicationVersion('Tinebase'), '11.12') === -1) {
            return;
        }
        $release10 = new Tinebase_Setup_Update_Release10($this->_backend);
        $release10->update_49();
        $this->setApplicationVersion('Tinebase', '11.9');
    }

    /**
     * update to 11.10
     *
     * reimport all template files
     */
    public function update_9()
    {
        $release10 = new Tinebase_Setup_Update_Release10($this->_backend);
        $release10->update_50();
        $this->setApplicationVersion('Tinebase', '11.10');
    }

    /**
     * update to 11.11
     *
     * remove timemachine_modlog_bkp if it exists
     */
    public function update_10()
    {
        $this->_backend->dropTable('timemachine_modlog_bkp');

        $this->setApplicationVersion('Tinebase', '11.11');
    }

    /**
     * update to 11.12
     *
     * remove scheduler table
     * remove async_job table
     * recreate scheduler tasks
     * @throws Setup_Exception_NotFound
     */
    public function update_11()
    {
        $this->_backend->dropTable('async_job', Tinebase_Core::getTinebaseId());
        $this->_backend->dropTable('scheduler', Tinebase_Core::getTinebaseId());
        $this->updateSchema('Tinebase', array(Tinebase_Model_SchedulerTask::class));

        $scheduler = Tinebase_Core::getScheduler();
        // TODO create methods for fetching and creating all known application tasks (maybe with existence check)
        Tinebase_Scheduler_Task::addAlarmTask($scheduler);
        Tinebase_Scheduler_Task::addCacheCleanupTask($scheduler);
        Tinebase_Scheduler_Task::addCredentialCacheCleanupTask($scheduler);
        Tinebase_Scheduler_Task::addTempFileCleanupTask($scheduler);
        Tinebase_Scheduler_Task::addDeletedFileCleanupTask($scheduler);
        Tinebase_Scheduler_Task::addSessionsCleanupTask($scheduler);
        Tinebase_Scheduler_Task::addAccessLogCleanupTask($scheduler);
        Tinebase_Scheduler_Task::addImportTask($scheduler);
        Tinebase_Scheduler_Task::addAccountSyncTask($scheduler);
        Tinebase_Scheduler_Task::addReplicationTask($scheduler);
        Tinebase_Scheduler_Task::addFileRevisionCleanupTask($scheduler);
        Tinebase_Scheduler_Task::addFileSystemSizeRecalculation($scheduler);
        Tinebase_Scheduler_Task::addFileSystemCheckIndexTask($scheduler);
        Tinebase_Scheduler_Task::addFileSystemSanitizePreviewsTask($scheduler);
        Tinebase_Scheduler_Task::addFileSystemNotifyQuotaTask($scheduler);
        if (! $scheduler->hasTask('Tinebase_AclTablesCleanup')) {
            Tinebase_Scheduler_Task::addAclTableCleanupTask($scheduler);
        }

        if (Tinebase_Application::getInstance()->isInstalled('Calendar')) {
            Calendar_Scheduler_Task::addUpdateConstraintsExdatesTask($scheduler);
            Calendar_Scheduler_Task::addTentativeNotificationTask($scheduler);
        }

        if (Tinebase_Application::getInstance()->isInstalled('Sales')) {
            Sales_Scheduler_Task::addUpdateProductLifespanTask($scheduler);
        }

        $this->setApplicationVersion('Tinebase', '11.12');
    }

    /**
     * update to 11.13
     *
     * rerun update 4 + 8 as we don't want them to run before update_11
     */
    public function update_12()
    {
        $this->update_4();
        $this->update_8();

        $this->setApplicationVersion('Tinebase', '11.13');
    }

    /**
     * update to 11.14
     *
     * add anonymous group and user
     * @throws Tinebase_Exception_InvalidArgument
     */
    public function update_13()
    {
        $defaultAnonymousGroupName =
            Tinebase_User::getBackendConfiguration(Tinebase_User::DEFAULT_ANONYMOUS_GROUP_NAME_KEY)
                ? Tinebase_User::getBackendConfiguration(Tinebase_User::DEFAULT_ANONYMOUS_GROUP_NAME_KEY)
                : Tinebase_Group::DEFAULT_ANONYMOUS_GROUP;
        $anonymousGroup = new Tinebase_Model_Group(array(
            'name'          => $defaultAnonymousGroupName,
            'description'   => 'Group of anonymous user accounts',
            'visibility'    => Tinebase_Model_Group::VISIBILITY_HIDDEN
        ));
        Tinebase_Timemachine_ModificationLog::setRecordMetaData($anonymousGroup, 'create');
        /** @var Tinebase_Model_Group $group */
        /** @noinspection PhpUndefinedMethodInspection */
        $group = Tinebase_Group::getInstance()->addGroupInSqlBackend($anonymousGroup);

        Tinebase_User::createSystemUser(Tinebase_User::SYSTEM_USER_ANONYMOUS, $group);

        $this->setApplicationVersion('Tinebase', '11.14');
    }

    /**
     * update to 11.15
     *
     * update relations of type SITE
     */
    public function update_14()
    {
        $filter = new Tinebase_Model_RelationFilter([
            ['field' => 'type', 'operator' => 'equals', 'value' => 'SITE'],
        ]);
        $relationController = Tinebase_Relations::getInstance();
        $relationBackend = new Tinebase_Relation_Backend_Sql();
        $childModels = [
            Addressbook_Model_List::class,
            Calendar_Model_Resource::class,
        ];

        /** @var Tinebase_Model_Relation $relation */
        foreach ($relationController->search($filter) as $relation) {
            if (in_array($relation->own_model, $childModels)) {
                if ($relation->related_degree === Tinebase_Model_Relation::DEGREE_PARENT) {
                    continue;
                }
                $relation->related_degree = Tinebase_Model_Relation::DEGREE_PARENT;
            } elseif (in_array($relation->related_model, $childModels)) {
                if ($relation->related_degree === Tinebase_Model_Relation::DEGREE_CHILD) {
                    continue;
                }
                $relation->related_degree = Tinebase_Model_Relation::DEGREE_CHILD;
            } elseif ($relation->related_degree !== Tinebase_Model_Relation::DEGREE_SIBLING) {
                $relation->related_degree = Tinebase_Model_Relation::DEGREE_SIBLING;
            } else {
                continue;
            }
            try {
                $relationBackend->updateRelation($relation);
            } catch (Tinebase_Exception_Record_NotDefined $e) {}
        }

        $this->setApplicationVersion('Tinebase', '11.15');
    }

    /**
     * update to 11.16
     *
     * make sure setup user has admin rights
     */
    public function update_15()
    {
        $update = new Tinebase_Setup_Update_Release9($this->_backend);
        $update->update_14();

        $this->setApplicationVersion('Tinebase', '11.16');
    }
}