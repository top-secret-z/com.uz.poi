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
namespace poi\system\event\listener;

use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\WCF;

/**
 * Updates users' poi counter.
 */
class UserRebuildDataWorkerListener implements IParameterizedEventListener
{
    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        $userIDs = [];
        foreach ($eventObj->getObjectList() as $user) {
            $userIDs[] = $user->userID;
        }

        if (!empty($userIDs)) {
            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('user_table.userID IN (?)', [$userIDs]);
            $sql = "UPDATE    wcf" . WCF_N . "_user user_table
                    SET    poiPois = (
                        SELECT    COUNT(*)
                        FROM    poi" . WCF_N . "_poi poi
                        WHERE    poi.userID = user_table.userID AND poi.isDisabled = 0
                    )
                    " . $conditionBuilder;
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute($conditionBuilder->getParameters());
        }
    }
}
