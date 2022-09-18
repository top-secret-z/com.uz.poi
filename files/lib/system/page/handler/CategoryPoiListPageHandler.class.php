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

use poi\data\category\PoiCategory;
use poi\data\category\PoiCategoryCache;
use wcf\system\page\handler\AbstractLookupPageHandler;
use wcf\system\page\handler\IOnlineLocationPageHandler;
use wcf\system\page\handler\TDecoratedCategoryLookupPageHandler;
use wcf\system\page\handler\TDecoratedCategoryOnlineLocationLookupPageHandler;

/**
 * Menu page handler for the category poi list page.
 */
class CategoryPoiListPageHandler extends AbstractLookupPageHandler implements IOnlineLocationPageHandler
{
    use TDecoratedCategoryOnlineLocationLookupPageHandler;

    /**
     * @see    TDecoratedCategoryLookupPageHandler::getDecoratedCategoryClass()
     */
    protected function getDecoratedCategoryClass()
    {
        return PoiCategory::class;
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    /**
     * @inheritDoc
     */
    public function getOutstandingItemCount($objectID = null)
    {
        return PoiCategoryCache::getInstance()->getUnreadPois($objectID);
    }
}
