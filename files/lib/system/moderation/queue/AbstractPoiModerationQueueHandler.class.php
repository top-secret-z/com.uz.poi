<?php

/*
 * Copyright by Udo Zaydowicz.
 * Modified by SoftCreatR.dev.
 *
 * License: http://opensource.org/licenses/lgpl-license.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace poi\system\moderation\queue;

use poi\data\poi\Poi;
use poi\data\poi\PoiAction;
use poi\data\poi\PoiList;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\system\moderation\queue\AbstractModerationQueueHandler;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\WCF;

/**
 * An abstract implementation of IModerationQueueHandler for pois.
 */
abstract class AbstractPoiModerationQueueHandler extends AbstractModerationQueueHandler
{
    /**
     * @inheritDoc
     */
    protected $className = Poi::class;

    /**
     * list of poi objects
     */
    protected static $pois = [];

    /**
     * @inheritDoc
     */
    protected $requiredPermission = 'mod.poi.canModeratePoi';

    /**
     * @inheritDoc
     */
    public function assignQueues(array $queues)
    {
        $assignments = [];
        foreach ($queues as $queue) {
            $assignUser = false;
            if (WCF::getSession()->getPermission('mod.poi.canModeratePoi')) {
                $assignUser = true;
            }

            $assignments[$queue->queueID] = $assignUser;
        }

        ModerationQueueManager::getInstance()->setAssignment($assignments);
    }

    /**
     * @inheritDoc
     */
    public function getContainerID($objectID)
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function isValid($objectID)
    {
        if ($this->getPoi($objectID) === null) {
            return false;
        }

        return true;
    }

    /**
     * Returns a poi object by poiID or null if poiID is invalid.
     */
    protected function getPoi($objectID)
    {
        if (!\array_key_exists($objectID, self::$pois)) {
            self::$pois[$objectID] = new Poi($objectID);
            if (!self::$pois[$objectID]->poiID) {
                self::$pois[$objectID] = null;
            }
        }

        return self::$pois[$objectID];
    }

    /**
     * @inheritDoc
     */
    public function populate(array $queues)
    {
        $objectIDs = [];
        foreach ($queues as $object) {
            $objectIDs[] = $object->objectID;
        }

        // fetch pois
        $poiList = new PoiList();
        $poiList->setObjectIDs($objectIDs);
        $poiList->readObjects();
        $pois = $poiList->getObjects();

        foreach ($queues as $object) {
            if (isset($pois[$object->objectID])) {
                $object->setAffectedObject($pois[$object->objectID]);
            } else {
                $object->setIsOrphaned();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function removeContent(ModerationQueue $queue, $message)
    {
        if ($this->isValid($queue->objectID) && !$this->getPoi($queue->objectID)->isDeleted) {
            $action = new PoiAction([$this->getPoi($queue->objectID)], 'trash', ['data' => ['reason' => $message]]);
            $action->executeAction();
        }
    }
}
