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

use poi\data\modification\log\PoiLogModificationLogList;
use poi\data\poi\ViewablePoi;
use poi\system\POICore;
use wcf\page\SortablePage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the poi log page.
 */
class PoiLogPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $defaultSortField = 'time';

    /**
     * @inheritDoc
     */
    public $defaultSortOrder = 'DESC';

    /**
     * @inheritDoc
     */
    public $objectListClassName = PoiLogModificationLogList::class;

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['mod.poi.canEditPoi'];

    /**
     * poi id
     */
    public $poiID = 0;

    /**
     * poi object
     */
    public $poi;

    /**
     * @inheritDoc
     */
    public $validSortFields = ['logID', 'time', 'username'];

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['id'])) {
            $this->poiID = \intval($_REQUEST['id']);
        }
        $this->poi = ViewablePoi::getPoi($this->poiID);
        if ($this->poi === null) {
            throw new IllegalLinkException();
        }
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->setPoi($this->poi->getDecoratedObject());
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        // add breadcrumbs
        POICore::getInstance()->setLocation($this->poi->getCategory()->getParentCategories(), $this->poi->getCategory(), $this->poi->getDecoratedObject());
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'poi' => $this->poi,
        ]);
    }
}
