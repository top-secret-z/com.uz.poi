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
namespace poi\page;

use poi\data\poi\AccessiblePoiList;
use poi\system\cache\builder\StatsCacheBuilder;
use poi\system\POICore;
use wcf\page\SortablePage;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\WCF;

/**
 * Shows a list of pois.
 */
class PoiListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $itemsPerPage = POI_POIS_PER_PAGE;

    /**
     * @inheritDoc
     */
    public $objectListClassName = AccessiblePoiList::class;

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'lastChangeTime';

    /**
     * @inheritDoc
     */
    public $defaultSortOrder = 'DESC';

    /**
     * @inheritDoc
     */
    public $validSortFields = ['username', 'subject', 'lastChangeTime', 'cumulativeLikes', 'comments', 'views', 'visits'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['user.poi.canViewPoi'];

    /**
     * statistics
     */
    public $stats = [];

    /**
     * controller name
     */
    public $controllerName = 'PoiList';

    /**
     * app parameters
     */
    public $controllerParameters = ['application' => 'poi'];

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        // stats
        if (POI_INDEX_ENABLE_STATS) {
            $this->stats = StatsCacheBuilder::getInstance()->getData();
        }

        // add breadcrumbs
        POICore::getInstance()->setLocation();
    }

    /**
     * @inheritDoc
     */
    protected function readObjects()
    {
        $this->sqlOrderBy = 'poi.' . $this->sqlOrderBy;

        parent::readObjects();
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'hasMarkedItems' => ClipboardHandler::getInstance()->hasMarkedItems(ClipboardHandler::getInstance()->getObjectTypeID('com.uz.poi.poi')),
            'allowSpidersToIndexThisPage' => true,
            'feedControllerName' => 'PoiListFeed',
            'controllerName' => $this->controllerName,
            'controllerObject' => null,
            'stats' => $this->stats,
        ]);
    }
}
