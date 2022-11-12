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
namespace poi\system\box;

use poi\data\poi\AccessiblePoiList;
use wcf\system\box\AbstractDatabaseObjectListBoxController;
use wcf\system\WCF;

/**
 * Box for poi list.
 */
class PoiListBoxController extends AbstractDatabaseObjectListBoxController
{
    /**
     * @inheritDoc
     */
    protected static $supportedPositions = ['sidebarLeft', 'sidebarRight', 'contentTop', 'contentBottom', 'top', 'bottom', 'footerBoxes'];

    /**
     * @inheritDoc
     */
    protected $sortFieldLanguageItemPrefix = 'poi.poi';

    /**
     * @var string[]
     */
    protected static $limitedPoiStats = [
        'cumulativeLikes' => 'latestCumulativeLikes',
    ];

    /**
     * @inheritDoc
     */
    public $defaultLimit = 6;

    /**
     * @inheritDoc
     */
    protected $conditionDefinition = 'com.uz.poi.box.poiList.condition';

    /**
     * @inheritDoc
     */
    public $validSortFields = [
        'time',
        'comments',
        'cumulativeLikes',
        'random',
    ];

    /**
     * @inheritDoc
     */
    protected function getObjectList()
    {
        $objectList = new AccessiblePoiList();

        switch ($this->sortField) {
            case 'comments':
                $objectList->getConditionBuilder()->add('poi.comments > ?', [0]);
                break;
        }

        if ($this->sortField == 'random') {
            $this->sortField = 'RAND()';
            $this->sortOrder = ' ';
        }

        return $objectList;
    }

    /**
     * @inheritDoc
     */
    protected function getTemplate()
    {
        return WCF::getTPL()->fetch('boxPoiList', 'poi', [
            'boxPoiList' => $this->objectList,
            'boxSortField' => $this->sortField,
            'boxPosition' => $this->box->position,
        ], true);
    }

    /**
     * @inheritDoc
     */
    protected function readObjects()
    {
        $sortField = $this->box->sortField;

        if ($sortField === 'cumulativeLikes') {
            $this->objectList->sqlOrderBy = 'poi.' . $this->objectList->sqlOrderBy;
        }

        parent::readObjects();
    }
}
