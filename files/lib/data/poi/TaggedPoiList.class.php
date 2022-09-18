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
namespace poi\data\poi;

use PDO;
use wcf\data\tag\Tag;
use wcf\system\tagging\TagEngine;
use wcf\system\tagging\TTaggedObjectList;
use wcf\system\WCF;

/**
 * Represents a list of tagged pois.
 */
class TaggedPoiList extends AccessiblePoiList
{
    use TTaggedObjectList;

    /**
     * Creates a new TaggedPoiList object.
     */
    public function __construct($tags)
    {
        parent::__construct();

        $this->tags = ($tags instanceof Tag) ? [$tags] : $tags;

        if (!WCF::getSession()->getPermission('user.poi.canViewPoi')) {
            $this->getConditionBuilder()->add('1=0');
        }

        $this->getConditionBuilder()->add('tag_to_object.objectTypeID = ? AND tag_to_object.tagID IN (?)', [
            TagEngine::getInstance()->getObjectTypeID('com.uz.poi.poi'),
            TagEngine::getInstance()->getTagIDs($this->tags),
        ]);
        $this->getConditionBuilder()->add('poi.poiID  = tag_to_object.objectID');
    }

    /**
     * @inheritDoc
     */
    public function countObjects()
    {
        $sql = "SELECT    COUNT(*)
                FROM    (
                    SELECT    tag_to_object.objectID
                    FROM    wcf" . WCF_N . "_tag_to_object tag_to_object,
                            poi" . WCF_N . "_poi poi
                    " . $this->sqlConditionJoins . "
                    " . $this->getConditionBuilder() . "
                    GROUP BY tag_to_object.objectID
                    HAVING COUNT(tag_to_object.objectID) = ?
            ) AS t";
        $statement = WCF::getDB()->prepareStatement($sql);

        $parameters = $this->getConditionBuilder()->getParameters();
        $parameters[] = \count($this->tags);
        $statement->execute($parameters);

        return $statement->fetchSingleColumn();
    }

    /**
     * @inheritDoc
     */
    public function readObjectIDs()
    {
        $sql = "SELECT    tag_to_object.objectID
                FROM    wcf" . WCF_N . "_tag_to_object tag_to_object,
                            poi" . WCF_N . "_poi poi
                " . $this->sqlConditionJoins . "
                " . $this->getConditionBuilder() . "
                " . $this->getGroupByFromOrderBy('tag_to_object.objectID', $this->sqlOrderBy) . "
                HAVING COUNT(tag_to_object.objectID) = ?
            " . (!empty($this->sqlOrderBy) ? "ORDER BY " . $this->sqlOrderBy : '');
        $statement = WCF::getDB()->prepareStatement($sql, $this->sqlLimit, $this->sqlOffset);

        $parameters = $this->getConditionBuilder()->getParameters();
        $parameters[] = \count($this->tags);
        $statement->execute($parameters);
        $this->objectIDs = $statement->fetchAll(PDO::FETCH_COLUMN);
    }
}
