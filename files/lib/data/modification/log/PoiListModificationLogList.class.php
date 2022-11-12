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

use poi\system\log\modification\PoiModificationLogHandler;
use wcf\data\modification\log\ModificationLogList;

/**
 * Represents a list of modification logs for poi list page.
 */
class PoiListModificationLogList extends ModificationLogList
{
    /**
     * @inheritDoc
     */
    public $decoratorClassName = ViewablePoiListPoiModificationLog::class;

    /**
     * Initializes the poi list modification log list.
     */
    public function setPoiData(array $poiIDs, $action = '')
    {
        $this->getConditionBuilder()->add("objectTypeID = ?", [PoiModificationLogHandler::getInstance()->getObjectType()->objectTypeID]);
        $this->getConditionBuilder()->add("objectID IN (?)", [$poiIDs]);
        if (!empty($action)) {
            $this->getConditionBuilder()->add("action = ?", [$action]);
        }
    }
}
