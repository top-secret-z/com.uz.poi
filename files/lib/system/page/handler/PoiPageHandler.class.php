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

use poi\data\poi\ViewablePoiList;
use poi\system\cache\runtime\ViewablePoiRuntimeCache;
use wcf\data\page\Page;
use wcf\data\user\online\UserOnline;
use wcf\system\page\handler\AbstractLookupPageHandler;
use wcf\system\page\handler\IOnlineLocationPageHandler;
use wcf\system\page\handler\TOnlineLocationPageHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Menu page handler for the poi page.
 */
class PoiPageHandler extends AbstractLookupPageHandler implements IOnlineLocationPageHandler
{
    use TOnlineLocationPageHandler;

    /**
     * @inheritDoc
     */
    public function isValid($objectID)
    {
        return ViewablePoiRuntimeCache::getInstance()->getObject($objectID) !== null;
    }

    /**
     * @inheritDoc
     */
    public function isVisible($objectID = null)
    {
        return ViewablePoiRuntimeCache::getInstance()->getObject($objectID)->canRead();
    }

    /**
     * @inheritDoc
     */
    public function getLink($objectID)
    {
        return ViewablePoiRuntimeCache::getInstance()->getObject($objectID)->getLink();
    }

    /**
     * @inheritDoc
     */
    public function lookup($searchString)
    {
        $poiList = new ViewablePoiList();
        $poiList->getConditionBuilder()->add('poi.subject LIKE ?', ['%' . $searchString . '%']);
        $poiList->sqlLimit = 10;
        $poiList->sqlOrderBy = 'poi.subject';
        $poiList->readObjects();

        $results = [];
        foreach ($poiList->getObjects() as $poi) {
            $results[] = [
                'description' => StringUtil::encodeHTML($poi->getTeaser()),
                'link' => $poi->getLink(),
                'objectID' => $poi->poiID,
                'title' => $poi->getTitle(),
            ];
        }

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function getOnlineLocation(Page $page, UserOnline $user)
    {
        if ($user->pageObjectID === null) {
            return '';
        }

        $poi = ViewablePoiRuntimeCache::getInstance()->getObject($user->pageObjectID);
        if ($poi === null || !$poi->canRead()) {
            return '';
        }

        return WCF::getLanguage()->getDynamicVariable('wcf.page.onlineLocation.' . $page->identifier, ['poi' => $poi]);
    }

    /**
     * @inheritDoc
     */
    public function prepareOnlineLocation(/** @noinspection PhpUnusedParameterInspection */Page $page, UserOnline $user)
    {
        if ($user->pageObjectID !== null) {
            ViewablePoiRuntimeCache::getInstance()->cacheObjectID($user->pageObjectID);
        }
    }
}
