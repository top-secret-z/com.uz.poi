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
namespace poi\data\modification\log;

use poi\data\poi\Poi;
use poi\system\log\modification\PoiModificationLogHandler;
use wcf\data\modification\log\ModificationLogList;
use wcf\system\WCF;

/**
 * Represents a list of modification logs for poi log page.
 */
class PoiLogModificationLogList extends ModificationLogList
{
    /**
     * poi data
     */
    public $poiObjectTypeID = 0;

    public $poi;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();

        // get object type
        $this->poiObjectTypeID = PoiModificationLogHandler::getInstance()->getObjectType()->objectTypeID;
    }

    /**
     * @inheritDoc
     */
    public function getDatabaseTableAlias()
    {
        return '';
    }

    /**
     * Initializes the log list for this poi.
     */
    public function setPoi(Poi $poi)
    {
        $this->poi = $poi;
    }

    /**
     * @inheritDoc
     */
    public function countObjects()
    {
        $sql = "SELECT        COUNT(modification_log.logID) AS count
                FROM        wcf" . WCF_N . "_modification_log modification_log
                WHERE        modification_log.objectTypeID = ? AND modification_log.objectID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->poiObjectTypeID, $this->poi->poiID]);
        $count = 0;
        while ($row = $statement->fetchArray()) {
            $count += $row['count'];
        }

        return $count;
    }

    /**
     * @inheritDoc
     */
    public function readObjects()
    {
        $sql = "SELECT        user_avatar.*, user_table.email, user_table.disableAvatar, user_table.enableGravatar, user_table.gravatarFileExtension, modification_log.*
                FROM        wcf" . WCF_N . "_modification_log modification_log
                LEFT JOIN    wcf" . WCF_N . "_user user_table
                ON        (user_table.userID = modification_log.userID)
                LEFT JOIN    wcf" . WCF_N . "_user_avatar user_avatar
                ON        (user_avatar.avatarID = user_table.avatarID)
                WHERE        modification_log.objectTypeID = ? AND modification_log.objectID = ?
                " . (!empty($this->sqlOrderBy) ? "ORDER BY " . $this->sqlOrderBy : '');
        $statement = WCF::getDB()->prepareStatement($sql, $this->sqlLimit, $this->sqlOffset);
        $statement->execute([$this->poiObjectTypeID, $this->poi->poiID]);
        $this->objects = $statement->fetchObjects(($this->objectClassName ?: $this->className));

        // table index -> array index
        $objects = [];
        foreach ($this->objects as $object) {
            $objectID = $object->{$this->getDatabaseTableIndexName()};
            $objects[$objectID] = $object;

            $this->indexToObject[] = $objectID;
        }
        $this->objectIDs = $this->indexToObject;
        $this->objects = $objects;

        $versionIDs = [];
        foreach ($this->objects as &$object) {
            $object = new ViewablePoiModificationLog($object);
        }
        unset($object);
    }
}
