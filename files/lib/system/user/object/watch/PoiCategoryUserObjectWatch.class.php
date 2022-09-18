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
namespace poi\system\user\object\watch;

use poi\data\category\PoiCategory;
use wcf\data\object\type\AbstractObjectTypeProcessor;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\user\object\watch\IUserObjectWatch;
use wcf\system\user\storage\UserStorageHandler;

/**
 * Implementation of IUserObjectWatch for watched categories.
 */
class PoiCategoryUserObjectWatch extends AbstractObjectTypeProcessor implements IUserObjectWatch
{
    /**
     * @inheritDoc
     */
    public function validateObjectID($objectID)
    {
        $category = PoiCategory::getCategory($objectID);
        if ($category === null) {
            throw new IllegalLinkException();
        }
        if (!$category->isAccessible()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @inheritDoc
     */
    public function resetUserStorage(array $userIDs)
    {
        UserStorageHandler::getInstance()->reset($userIDs, 'poiUnreadWatchedPois');
        UserStorageHandler::getInstance()->reset($userIDs, 'poiSubscribedCategories');
    }
}
