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
namespace poi\data\category;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\category\CategoryEditor;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Executes poi category-related actions.
 */
class PoiCategoryAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = CategoryEditor::class;

    /**
     * @inheritDoc
     */
    protected $allowGuestAccess = ['markAllAsRead'];

    /**
     * Validates the mark all as read action.
     */
    public function validateMarkAllAsRead()
    {
        // nothing so far
    }

    /**
     * Marks all categories as read.
     */
    public function markAllAsRead()
    {
        VisitTracker::getInstance()->trackTypeVisit('com.uz.poi.poi');

        // reset storage and notifications
        if (WCF::getUser()->userID) {
            UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'poiUnreadPois');
            UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'poiUnreadWatchedPois');
        }
    }
}
