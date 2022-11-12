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
namespace poi\system\page\handler;

use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\page\handler\AbstractLookupPageHandler;
use wcf\system\page\handler\IOnlineLocationPageHandler;
use wcf\system\page\handler\TUserLookupPageHandler;
use wcf\system\page\handler\TUserOnlineLocationPageHandler;
use wcf\system\request\LinkHandler;

/**
 * Menu page handler for the user poi list page.
 */
class UserPoiListPageHandler extends AbstractLookupPageHandler implements IOnlineLocationPageHandler
{
    use TUserLookupPageHandler;
    use TUserOnlineLocationPageHandler;

    /**
     * @inheritDoc
     */
    public function getLink($objectID)
    {
        return LinkHandler::getInstance()->getLink('UserPoiList', [
            'application' => 'poi',
            'forceFrontend' => true,
            'object' => UserRuntimeCache::getInstance()->getObject($objectID),
        ]);
    }
}
